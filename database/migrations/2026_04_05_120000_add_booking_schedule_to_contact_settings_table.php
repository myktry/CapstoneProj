<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_settings', function (Blueprint $table) {
            $table->time('booking_start_time')->default('10:00:00')->after('email');
            $table->time('booking_end_time')->default('17:00:00')->after('booking_start_time');
            $table->unsignedSmallInteger('booking_interval_minutes')->default(60)->after('booking_end_time');
        });
    }

    public function down(): void
    {
        Schema::table('contact_settings', function (Blueprint $table) {
            $table->dropColumn([
                'booking_start_time',
                'booking_end_time',
                'booking_interval_minutes',
            ]);
        });
    }
};
