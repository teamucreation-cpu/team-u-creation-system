<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            // Nullable: spec links a booking to a package "when a package is selected",
            // implying a booking can exist without one (e.g. fully custom event).
            $table->foreignId('package_id')->nullable()->constrained('event_packages')->nullOnDelete();
            $table->date('event_date');
            $table->string('event_type');
            $table->string('venue');
            $table->enum('status', ['Pending', 'Approved', 'Declined', 'Rescheduled'])->default('Pending');
            $table->timestamps();

            // Indexed for the schedule-conflict-detection queries in the Booking module.
            $table->index(['event_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
