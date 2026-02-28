<?php

namespace App\Filament\Resources\ClosedDates\Pages;

use App\Filament\Resources\ClosedDates\ClosedDateResource;
use App\Filament\Widgets\CalendarManagementWidget;
use Filament\Resources\Pages\ListRecords;

class ListClosedDates extends ListRecords
{
    protected static string $resource = ClosedDateResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarManagementWidget::class,
        ];
    }
}
