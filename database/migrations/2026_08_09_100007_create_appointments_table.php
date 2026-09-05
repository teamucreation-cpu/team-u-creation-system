<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('purpose', [
                'Initial Consultation',
                'Event Planning Meeting',
                'Package Discussion',
                'Event Coordination Meeting',
            ]);
            $table->enum('status', ['Pending', 'Approved', 'Declined', 'Rescheduled'])->default('Pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
