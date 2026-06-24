<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'gallery_item_id',
        'name',
        'price',
        'description',
        'image',
        'metadata_stego_png_base64',
        'duration_minutes',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function galleryItem(): BelongsTo
    {
        return $this->belongsTo(GalleryItem::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! filled($this->image)) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return url('storage/'.ltrim($this->image, '/'));
    }

    /** Preferred carrier URL when embedding metadata (gallery image if `image` empty). */
    public function carrierImagePublicUrl(): ?string
    {
        if ($this->image_url) {
            return $this->image_url;
        }

        return $this->galleryItem?->image_url;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');
    }
}
