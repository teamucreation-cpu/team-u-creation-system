<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('phone', 30);
            $table->string('email');
            $table->string('facebook_url', 2048);
            $table->string('business_days', 255);
            $table->string('opening_time', 5);
            $table->string('closing_time', 5);
            $table->timestamps();
        });

        DB::table('business_settings')->insert([
            'id' => 1,
            'location' => 'Iriga City, Camarines Sur',
            'phone' => '+63 912 345 6789',
            'email' => 'teamucreation@gmail.com',
            'facebook_url' => 'https://www.facebook.com/TEAMUCreations',
            'business_days' => 'Monday – Sunday',
            'opening_time' => '08:00',
            'closing_time' => '20:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};
