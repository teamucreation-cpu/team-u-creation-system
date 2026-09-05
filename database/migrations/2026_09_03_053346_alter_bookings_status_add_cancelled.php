<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 'Cancelled' is distinct from 'Declined': Cancelled is the client
        // withdrawing their own still-Pending request; Declined is the admin
        // rejecting it. Keeping them separate preserves who took the action.
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('Pending', 'Approved', 'Declined', 'Rescheduled', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('Pending', 'Approved', 'Declined', 'Rescheduled', 'Completed') NOT NULL DEFAULT 'Pending'");
    }
};
