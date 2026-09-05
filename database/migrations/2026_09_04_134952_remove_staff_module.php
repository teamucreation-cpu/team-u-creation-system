<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the entire Staff module from the database layer: the
 * staff-assignment tables, the staff link on inventory assignments, any
 * staff user accounts, and 'staff' as a valid role.
 *
 * Deleting the staff User row(s) cascades away their notifications
 * (notifications.user_id is cascadeOnDelete) — nothing else needs to be
 * cleaned up manually for that.
 */
return new class extends Migration
{
    public function up(): void
    {
        // These tables existed only to link a booking to a staff/coordinator
        // (schedules) or a task to a staff assignee (tasks) — both entirely
        // staff-specific, so they're dropped outright rather than altered.
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('schedules');

        // Inventory Assignments is a preserved feature (item <-> booking,
        // with quantity/status/notes) — only the "who on staff is
        // responsible for it" link is removed.
        if (Schema::hasColumn('inventory_assignments', 'assigned_staff')) {
            Schema::table('inventory_assignments', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_staff');
            });
        }

        // Existing staff accounts are no longer reachable through any route
        // or nav item — delete them so the role enum below can safely drop
        // 'staff' as an allowed value.
        DB::table('users')->where('role', 'staff')->delete();

        DB::statement("ALTER TABLE users MODIFY role ENUM('client', 'admin') NOT NULL DEFAULT 'client'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('client', 'staff', 'admin') NOT NULL DEFAULT 'client'");

        if (! Schema::hasColumn('inventory_assignments', 'assigned_staff')) {
            Schema::table('inventory_assignments', function (Blueprint $table) {
                $table->foreignId('assigned_staff')->nullable()->after('booking_id')->constrained('users')->nullOnDelete();
            });
        }

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_staff')->nullable()->constrained('users')->nullOnDelete();
            $table->date('schedule_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();

            $table->index('schedule_date');
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_staff')->nullable()->constrained('users')->nullOnDelete();
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Cancelled'])->default('Pending');
            $table->timestamps();
        });

        // Note: the staff user accounts and their data deleted in up() are
        // not restored — down() only reverses the schema, not the data loss.
    }
};
