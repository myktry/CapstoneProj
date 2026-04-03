<?php

use App\Models\GalleryItem;
use App\Models\Service;
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
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('gallery_item_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->constrained('gallery_items')
                ->nullOnDelete();
        });

        GalleryItem::query()
            ->orderBy('id')
            ->chunkById(100, function ($galleryItems): void {
                foreach ($galleryItems as $galleryItem) {
                    Service::query()->updateOrCreate(
                        ['gallery_item_id' => $galleryItem->id],
                        [
                            'name' => $galleryItem->name,
                            'price' => $galleryItem->price,
                            'description' => $galleryItem->description,
                            'image' => $galleryItem->image,
                            'duration_minutes' => 30,
                            'is_active' => $galleryItem->is_active,
                            'sort_order' => 0,
                            'gallery_item_id' => $galleryItem->id,
                        ]
                    );
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gallery_item_id');
        });
    }
};