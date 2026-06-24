<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            if (! Schema::hasColumn('gallery_items', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('image');
            }

            if (! Schema::hasColumn('gallery_items', 'featured_on_home')) {
                $table->boolean('featured_on_home')->default(false)->after('is_active');
            }
        });

        Schema::table('contact_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_settings', 'booking_start_time')) {
                $table->time('booking_start_time')->default('10:00:00')->after('email');
            }

            if (! Schema::hasColumn('contact_settings', 'booking_end_time')) {
                $table->time('booking_end_time')->default('17:00:00')->after('booking_start_time');
            }

            if (! Schema::hasColumn('contact_settings', 'booking_interval_minutes')) {
                $table->unsignedSmallInteger('booking_interval_minutes')->default(60)->after('booking_end_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            if (Schema::hasColumn('gallery_items', 'featured_on_home')) {
                $table->dropColumn('featured_on_home');
            }

            if (Schema::hasColumn('gallery_items', 'price')) {
                $table->dropColumn('price');
            }
        });

        Schema::table('contact_settings', function (Blueprint $table) {
            if (Schema::hasColumn('contact_settings', 'booking_interval_minutes')) {
                $table->dropColumn('booking_interval_minutes');
            }

            if (Schema::hasColumn('contact_settings', 'booking_end_time')) {
                $table->dropColumn('booking_end_time');
            }

            if (Schema::hasColumn('contact_settings', 'booking_start_time')) {
                $table->dropColumn('booking_start_time');
            }
        });
    }
};