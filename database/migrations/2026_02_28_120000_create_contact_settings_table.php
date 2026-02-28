<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('location_line_1');
            $table->string('location_line_2')->nullable();
            $table->string('hours_line_1');
            $table->string('hours_line_2')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->timestamps();
        });

        DB::table('contact_settings')->insert([
            'location_line_1' => '123 Ember Street',
            'location_line_2' => 'Downtown, PH 1000',
            'hours_line_1' => 'Mon - Sat: 10 AM - 8 PM',
            'hours_line_2' => 'Sun: 12 PM - 6 PM',
            'phone' => '+63 900 000 0000',
            'email' => 'hello@blackember.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};
