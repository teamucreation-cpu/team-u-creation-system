<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            // Beyond the literal spec field list, added so Module B's "Define package
            // inclusions" and "Manage package availability" features have somewhere to live.
            $table->text('inclusions')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_packages');
    }
};
