<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Short model name, e.g. "Service" instead of "App\Models\Service" */
    public function getModelNameAttribute(): string
    {
        return class_basename($this->model_type);
    }
}
