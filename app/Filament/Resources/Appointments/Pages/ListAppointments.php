<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Widgets\CompletedAppointmentsHistoryWidget;
use Filament\Resources\Pages\ListRecords;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CompletedAppointmentsHistoryWidget::class,
        ];
    }
}
