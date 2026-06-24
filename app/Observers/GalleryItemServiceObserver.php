<?php

namespace App\Observers;

use App\Models\GalleryItem;
use App\Models\Service;

class GalleryItemServiceObserver
{
    public function created(GalleryItem $galleryItem): void
    {
        try {
            $this->syncService($galleryItem);
        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }

    public function updated(GalleryItem $galleryItem): void
    {
        try {
            $this->syncService($galleryItem);
        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }

    public function deleted(GalleryItem $galleryItem): void
    {
        try {
            Service::query()
                ->where('gallery_item_id', $galleryItem->id)
                ->get()
                ->each
                ->delete();
        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }

    private function syncService(GalleryItem $galleryItem): void
    {
        $service = Service::query()
            ->firstOrNew(['gallery_item_id' => $galleryItem->id]);

        $service->fill([
            'name' => $galleryItem->name,
            'price' => $galleryItem->price,
            'description' => $galleryItem->description,
            'is_active' => $galleryItem->is_active,
            'sort_order' => $service->exists ? $service->sort_order : 0,
            'duration_minutes' => $service->exists ? $service->duration_minutes : 30,
        ]);

        $service->gallery_item_id = $galleryItem->id;
        $service->save();
    }
}