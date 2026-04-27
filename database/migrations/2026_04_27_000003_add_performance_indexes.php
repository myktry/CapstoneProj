<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->index(['is_active', 'featured_on_home', 'created_at'], 'gallery_items_home_featured_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['user_id', 'seen_at', 'created_at'], 'appointments_user_seen_created_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('created_at', 'activity_logs_created_at_idx');
            $table->index('user_id', 'activity_logs_user_id_idx');
            $table->index(['action', 'created_at'], 'activity_logs_action_created_idx');
            $table->index(['model_type', 'model_id'], 'activity_logs_model_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_created_at_idx');
            $table->dropIndex('activity_logs_user_id_idx');
            $table->dropIndex('activity_logs_action_created_idx');
            $table->dropIndex('activity_logs_model_lookup_idx');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_user_seen_created_idx');
        });

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropIndex('gallery_items_home_featured_idx');
        });
    }
};
