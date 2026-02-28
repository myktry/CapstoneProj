<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Service::query()->updateOrCreate([
            'name' => 'Midnight Fade',
        ], [
            'price' => 450,
            'duration_minutes' => 45,
            'description' => 'Clean skin fade with textured top and matte finish.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Service::query()->updateOrCreate([
            'name' => 'Classic Gentleman',
        ], [
            'price' => 400,
            'duration_minutes' => 40,
            'description' => 'Scissor cut with natural volume and polished edges.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Service::query()->updateOrCreate([
            'name' => 'Beard Ritual',
        ], [
            'price' => 300,
            'duration_minutes' => 30,
            'description' => 'Hot towel, detailed trim, and sharp line-up.',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        GalleryItem::query()->updateOrCreate([
            'name' => 'Midnight Fade',
        ], [
            'description' => 'Clean skin fade with textured top and matte finish.',
            'image' => 'https://images.unsplash.com/photo-1503951458645-643d53bfd90f?q=80&w=1200&auto=format&fit=crop',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        GalleryItem::query()->updateOrCreate([
            'name' => 'Classic Gentleman',
        ], [
            'description' => 'Scissor cut with natural volume and polished edges.',
            'image' => 'https://images.unsplash.com/photo-1519699047748-de8e457a634e?q=80&w=1200&auto=format&fit=crop',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        GalleryItem::query()->updateOrCreate([
            'name' => 'Beard Ritual',
        ], [
            'description' => 'Hot towel, detailed trim, and sharp line-up.',
            'image' => 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=1200&auto=format&fit=crop',
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
