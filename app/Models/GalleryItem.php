<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GalleryItem extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'price',
        'is_active',
        'featured_on_home',
    ];

    protected $attributes = [
        'is_active' => true,
        'featured_on_home' => false,
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'featured_on_home' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderBy('created_at');
    }

    public function scopeFeaturedOnHome(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('featured_on_home', true)
            ->orderBy('created_at');
    }

    public function service(): HasOne
    {
        return $this->hasOne(Service::class);
    }
}
