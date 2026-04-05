<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\AdminLogin;
use App\Filament\Widgets\AdminOverview;
use App\Filament\Widgets\BookingScheduleWidget;
use App\Filament\Widgets\ContactInformationWidget;
use App\Filament\Widgets\RecentActivityWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(AdminLogin::class)
            ->brandName('Black Ember Admin')
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Zinc,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <style>
                        .fi-topbar .fi-user-menu .fi-dropdown-panel {
                            max-width: min(20rem, calc(100vw - 1rem));
                            margin-inline-end: 0.25rem;
                        }

                        @media (max-width: 640px) {
                            .fi-topbar .fi-user-menu .fi-dropdown-panel {
                                margin-inline-end: 0;
                                max-width: calc(100vw - 0.75rem);
                            }
                        }
                    </style>

                    <script>
                        document.addEventListener('livewire:init', () => {
                            if (!('BroadcastChannel' in window) || typeof window.Livewire === 'undefined') {
                                return;
                            }

                            const channel = new BroadcastChannel('gallery-featured-sync');

                            window.Livewire.on('gallery-featured-updated', () => {
                                channel.postMessage({
                                    type: 'gallery-featured-updated',
                                    timestamp: Date.now(),
                                });
                            });
                        });
                    </script>
                HTML),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AdminOverview::class,
                RecentActivityWidget::class,
                ContactInformationWidget::class,
                BookingScheduleWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
