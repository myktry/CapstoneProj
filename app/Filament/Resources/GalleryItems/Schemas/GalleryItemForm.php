<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Models\GalleryItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->numeric()
                    ->prefix('₱')
                    ->required()
                    ->minValue(0)
                    ->step(0.01),
                FileUpload::make('image')
                    ->disk('public')
                    ->directory('gallery')
                    ->image()
                    ->default(fn (?GalleryItem $record): ?string => $record?->image)
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
