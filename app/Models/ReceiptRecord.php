<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceiptRecord extends Model
{
    protected $fillable = [
        'transaction_id',
        'filename',
        'relative_path',
        'sha256_hash',
        'mime_type',
        'size_bytes',
        'uploaded_by',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
