<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\ClosedDate;
use App\Models\GalleryItem;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Business Overview';

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Appointments', Appointment::query()->count())
                ->description('Total bookings recorded')
                ->color('primary'),
            Stat::make('Active Services', Service::query()->where('is_active', true)->count())
                ->description('Visible in booking and website')
                ->color('success'),
            Stat::make('Gallery Items', GalleryItem::query()->where('is_active', true)->count())
                ->description('Visible in public gallery')
                ->color('warning'),
            Stat::make('Closed/Holiday Dates', ClosedDate::query()->where('is_active', true)->count())
                ->description('Blocked dates in booking calendar')
                ->color('danger'),
        ];
    }
}
