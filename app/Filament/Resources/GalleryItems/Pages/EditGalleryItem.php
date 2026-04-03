<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Resources\GalleryItems\GalleryItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGalleryItem extends EditRecord
{
    protected static string $resource = GalleryItemResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['image'] ?? null)) {
            $data['image'] = $this->record->image;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
