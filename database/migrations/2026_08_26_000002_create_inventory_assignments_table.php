<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_staff')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('quantity_assigned');
            $table->enum('status', ['In Use', 'Returned', 'Damaged'])->default('In Use');
            // Staff use this to describe damage/missing items when reporting a problem.
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['booking_id']);
            $table->index(['assigned_staff']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_assignments');
    }
};
