<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->enum('category', [
                'Chairs',
                'Tables',
                'Artificial Flowers',
                'Backdrops',
                'Tents',
                'Lights',
                'Decorations',
                'Other Event Equipment',
            ]);
            $table->unsignedInteger('quantity_total')->default(0);
            $table->unsignedInteger('quantity_available')->default(0);
            $table->unsignedInteger('quantity_in_use')->default(0);
            $table->unsignedInteger('quantity_damaged')->default(0);
            // Lifetime counter of units returned over time — not a current-state field,
            // so it is never decremented once incremented.
            $table->unsignedInteger('quantity_returned')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
