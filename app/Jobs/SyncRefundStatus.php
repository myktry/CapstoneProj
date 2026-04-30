<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\RefundStatusSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncRefundStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $appointmentId,
        public readonly int $attempt = 1,
    ) {
    }

    public function handle(RefundStatusSyncService $refundStatusSyncService): void
    {
        $appointment = Appointment::query()->with('service')->find($this->appointmentId);

        if (! $appointment) {
            return;
        }

        $status = $refundStatusSyncService->sync($appointment);

        if ($status === 'pending' && $this->attempt < 5) {
            self::dispatch($this->appointmentId, $this->attempt + 1)
                ->delay(now()->addMinutes(2));
        }
    }
}
