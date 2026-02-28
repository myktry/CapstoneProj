<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('₱')
                    ->minValue(0),
                TextInput::make('duration_minutes')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(30),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                FileUpload::make('image')
                    ->directory('services')
                    ->image(),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
