<?php

namespace App\Providers;

use App\Filament\Auth\Responses\LogoutResponse;
use App\Models\Appointment;
use App\Models\ClosedDate;
use App\Models\ContactSetting;
use App\Models\GalleryItem;
use App\Models\SecurityAuditLog;
use App\Models\Service;
use App\Observers\ModelActivityObserver;
use App\Services\Sms\LogSmsSender;
use App\Services\Sms\SmsSender;
use App\Services\Sms\VonageSmsSender;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use App\Observers\GalleryItemServiceObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->bind(SmsSender::class, function () {
            return match ((string) config('services.sms.driver', 'log')) {
                'vonage' => new VonageSmsSender(),
                default => new LogSmsSender(),
            };
        });
    }

    public function boot(): void
    {
        RateLimiter::for('receipt-decrypt', function (Request $request): array {
            $ip = (string) $request->ip();
            $adminId = (string) ($request->user()?->id ?? 'guest');

            $throttleResponse = function (string $scope) use ($request, $ip): \Illuminate\Http\JsonResponse {
                SecurityAuditLog::query()->create([
                    'admin_id' => $request->user()?->id,
                    'event' => 'security_alert',
                    'status' => 'failed',
                    'ip_address' => $ip,
                    'transaction_id' => $request->input('transaction_id'),
                    'message' => 'Receipt decrypt throttle exceeded by ' . $scope . '.',
                    'context' => [
                        'scope' => $scope,
                    ],
                ]);

                return response()->json([
                    'ok' => false,
                    'message' => 'Too many decryption attempts. Please try again later.',
                ], 429);
            };

            return [
                Limit::perMinute(3)
                    ->by('receipt-decrypt-ip:' . $ip)
                    ->response(fn () => $throttleResponse('ip')),
                Limit::perMinute(3)
                    ->by('receipt-decrypt-admin:' . $adminId)
                    ->response(fn () => $throttleResponse('admin')),
            ];
        });

        $observer = ModelActivityObserver::class;

        Service::observe($observer);
        GalleryItem::observe($observer);
        GalleryItem::observe(GalleryItemServiceObserver::class);
        Appointment::observe($observer);
        ClosedDate::observe($observer);
        ContactSetting::observe($observer);
    }
}
