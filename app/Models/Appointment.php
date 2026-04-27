<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'customer_name',
        'customer_email',
        'customer_phone',
        'status',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'amount_paid',
        'refund_status',
        'refund_amount',
        'refund_deduction_amount',
        'refund_reference',
        'refund_processed_at',
        'cancelled_by',
        'cancelled_at',
        'cancellation_note',
        'seen_at',
    ];

    protected function casts(): array
    {
        return [
            'refund_processed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'seen_at' => 'datetime',
        ];
    }

    public function getRefundableAmountAttribute(): int
    {
        return max(0, (int) $this->amount_paid - (int) $this->refund_deduction_amount);
    }

    public function getReferenceNumberAttribute(): string
    {
        return (string) ($this->stripe_payment_intent_id
            ?: $this->stripe_session_id
            ?: $this->id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
