<?php

use App\Models\GalleryItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        GalleryItem::query()
            ->orderBy('id')
            ->chunkById(100, function ($galleryItems): void {
                foreach ($galleryItems as $galleryItem) {
                    DB::table('services')->updateOrInsert(
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
                            'created_at' => now(),
                            'updated_at' => now(),
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
        DB::table('services')->whereNotNull('gallery_item_id')->delete();
    }
};