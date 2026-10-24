<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('status')->default('Pending')->change();
        });

        DB::table('appointments')->where('status', 'Approved')->update(['status' => 'Confirmed']);

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('appointment_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('appointment_id');
        });

        DB::table('appointments')
            ->whereNotIn('status', ['Pending', 'Approved', 'Declined', 'Rescheduled'])
            ->update(['status' => 'Approved']);
        DB::table('appointments')->where('status', 'Confirmed')->update(['status' => 'Approved']);

        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Approved', 'Declined', 'Rescheduled'])->default('Pending')->change();
        });
    }
};