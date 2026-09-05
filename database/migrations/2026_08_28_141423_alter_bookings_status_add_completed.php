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
        // Adds "Completed" alongside the spec's four required statuses — an
        // Admin marks a past Approved event Completed, which is what makes
        // it eligible for the client to leave a review.
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('Pending', 'Approved', 'Declined', 'Rescheduled', 'Completed') NOT NULL DEFAULT 'Pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY status ENUM('Pending', 'Approved', 'Declined', 'Rescheduled') NOT NULL DEFAULT 'Pending'");
    }
};
