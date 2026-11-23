<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->date('original_appointment_date')->nullable();
            $table->time('original_appointment_time')->nullable();
            $table->date('proposed_appointment_date')->nullable();
            $table->time('proposed_appointment_time')->nullable();
            $table->text('reschedule_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'original_appointment_date',
                'original_appointment_time',
                'proposed_appointment_date',
                'proposed_appointment_time',
                'reschedule_reason',
            ]);
        });
    }
};
