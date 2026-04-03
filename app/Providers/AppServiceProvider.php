<?php

namespace App\Providers;

use App\Filament\Auth\Responses\LogoutResponse;
use App\Models\Appointment;
use App\Models\ClosedDate;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Observers\ModelActivityObserver;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\ServiceProvider;
use App\Observers\GalleryItemServiceObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        $observer = ModelActivityObserver::class;

        Service::observe($observer);
        GalleryItem::observe($observer);
        GalleryItem::observe(GalleryItemServiceObserver::class);
        Appointment::observe($observer);
        ClosedDate::observe($observer);
    }
}
