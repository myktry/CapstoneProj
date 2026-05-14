<?php

namespace App\Providers\Filament;

use App\Filament\Auth\MultiFactor\AdminEmailOtpAuthentication;
use App\Filament\Pages\AdminProfile;
use App\Filament\Pages\Auth\AdminLogin;
use App\Filament\Widgets\AdminOverview;
use App\Filament\Widgets\BookingScheduleWidget;
use App\Filament\Widgets\ClosedDatesManagementWidget;
use App\Filament\Widgets\ContactInformationWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
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
            ->multiFactorAuthentication([
                AdminEmailOtpAuthentication::make(),
            ])
            ->profile(AdminProfile::class)
            ->brandName('Black Ember Admin')
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Zinc,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
                    <style>
                        .fi-topbar .fi-user-menu .fi-user-menu-trigger-enhanced {
                            backdrop-filter: blur(8px);
                            background-image: linear-gradient(135deg, rgba(251, 191, 36, 0.1), rgba(24, 24, 27, 0.92) 42%);
                            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.24);
                        }

                        .fi-topbar .fi-user-menu .fi-user-menu-trigger-enhanced:hover {
                            border-color: rgba(251, 191, 36, 0.38);
                            box-shadow: 0 12px 26px rgba(0, 0, 0, 0.32);
                            transform: translateY(-1px);
                        }

                        .fi-topbar .fi-user-menu .fi-user-menu-trigger-enhanced:focus-visible {
                            outline: 2px solid rgba(251, 191, 36, 0.65);
                            outline-offset: 2px;
                        }

                        .fi-topbar .fi-user-menu .fi-user-menu-trigger-avatar {
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 1.9rem;
                            height: 1.9rem;
                            border-radius: 999px;
                            font-weight: 800;
                            font-size: 0.78rem;
                            color: #111827;
                            background: linear-gradient(135deg, #fbbf24, #f59e0b);
                            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.45);
                            flex-shrink: 0;
                        }

                        .fi-topbar .fi-user-menu .fi-user-menu-trigger-text {
                            display: flex;
                            flex-direction: column;
                            align-items: flex-start;
                            line-height: 1.1;
                            min-width: 0;
                        }

                        .fi-topbar .fi-user-menu .fi-user-menu-trigger-title {
                            color: #ffffff;
                            font-size: 0.84rem;
                            font-weight: 700;
                            white-space: nowrap;
                        }

                        .fi-topbar .fi-user-menu .fi-user-menu-trigger-subtitle {
                            color: rgba(255, 255, 255, 0.66);
                            font-size: 0.72rem;
                            max-width: 9rem;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        }

                        .fi-topbar .fi-user-menu .fi-user-menu-trigger-chevron {
                            color: rgba(255, 255, 255, 0.68);
                            transition: transform 160ms ease;
                            flex-shrink: 0;
                        }

                        .fi-topbar .fi-user-menu .fi-dropdown[aria-expanded='true'] .fi-user-menu-trigger-chevron {
                            transform: rotate(180deg);
                        }

                        .fi-topbar .fi-user-menu .fi-dropdown-panel {
                            max-width: min(20rem, calc(100vw - 1rem));
                            margin-inline-end: 0.25rem;
                        }

                        @media (max-width: 640px) {
                            .fi-topbar .fi-user-menu .fi-user-menu-trigger-text {
                                display: none;
                            }

                            .fi-topbar .fi-user-menu .fi-user-menu-trigger-enhanced {
                                gap: 0.5rem;
                                padding-inline: 0.65rem;
                            }

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
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                function (): HtmlString {
                    try {
                        if (User::query()->where('role', 'admin')->exists()) {
                            return new HtmlString('');
                        }

                        $url = route('admin.register');

                        return new HtmlString(<<<HTML
                            <div style="margin-top: 1.5rem; border-radius: 0.75rem; border: 1px solid rgba(251, 191, 36, 0.22); background: rgba(251, 191, 36, 0.08); padding: 1rem; color: #fff7ed;">
                                <p style="font-size: 0.875rem; font-weight: 700; color: #fcd34d;">No admin account exists yet.</p>
                                <p style="margin-top: 0.25rem; font-size: 0.875rem; color: rgba(255, 247, 237, 0.8);">Set up your admin account to start managing the platform.</p>
                                <a href="{$url}" style="display: inline-flex; margin-top: 1rem; align-items: center; border-radius: 0.5rem; background: #f59e0b; padding: 0.75rem 1rem; font-weight: 700; color: #111827; text-decoration: none;">
                                    Create Admin Account
                                </a>
                            </div>
                        HTML);
                    } catch (\Exception $e) {
                        return new HtmlString('');
                    }
                }
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AdminOverview::class,
                ClosedDatesManagementWidget::class,
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
