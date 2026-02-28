<?php

namespace App\Filament\Resources\ClosedDates\Pages;

use App\Filament\Resources\ClosedDates\ClosedDateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClosedDate extends EditRecord
{
    protected static string $resource = ClosedDateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
