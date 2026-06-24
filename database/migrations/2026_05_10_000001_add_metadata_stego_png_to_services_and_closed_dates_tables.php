<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->longText('metadata_stego_png_base64')->nullable()->after('image');
        });

        Schema::table('closed_dates', function (Blueprint $table): void {
            $table->longText('metadata_stego_png_base64')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('metadata_stego_png_base64');
        });

        Schema::table('closed_dates', function (Blueprint $table): void {
            $table->dropColumn('metadata_stego_png_base64');
        });
    }
};
