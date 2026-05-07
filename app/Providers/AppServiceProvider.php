<?php

namespace App\Providers;

use App\Filament\Pages\Auth\AdminLogin;
use App\Filament\Auth\Responses\LogoutResponse;
use App\Models\Appointment;
use App\Models\ClosedDate;
use App\Models\ContactSetting;
use App\Models\GalleryItem;
use App\Models\SecurityAuditLog;
use App\Models\Service;
use App\Filament\Widgets\AdminOverview;
use App\Filament\Widgets\BookingScheduleWidget;
use App\Filament\Widgets\ContactInformationWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Observers\ModelActivityObserver;
use App\Services\Sms\LogSmsSender;
use App\Services\Sms\SmsSender;
use App\Services\Sms\TextBeeSmsSender;
use App\Services\Sms\VonageSmsSender;
use Filament\Livewire\DatabaseNotifications as FilamentDatabaseNotifications;
use Filament\Livewire\Notifications as FilamentNotifications;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Observers\GalleryItemServiceObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->bind(SmsSender::class, function () {
            return match ((string) config('services.sms.driver', 'log')) {
                'textbee' => new TextBeeSmsSender(),
                'vonage' => new VonageSmsSender(),
                default => new LogSmsSender(),
            };
        });
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Livewire::component('app.filament.pages.auth.admin-login', AdminLogin::class);
        Livewire::component('filament.livewire.notifications', FilamentNotifications::class);
        Livewire::component('filament.livewire.database-notifications', FilamentDatabaseNotifications::class);
        Livewire::component('app.filament.widgets.admin-overview', AdminOverview::class);
        Livewire::component('app.filament.widgets.recent-activity-widget', RecentActivityWidget::class);
        Livewire::component('app.filament.widgets.contact-information-widget', ContactInformationWidget::class);
        Livewire::component('app.filament.widgets.booking-schedule-widget', BookingScheduleWidget::class);

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
