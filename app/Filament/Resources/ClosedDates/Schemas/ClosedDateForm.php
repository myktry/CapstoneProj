<?php

namespace App\Filament\Resources\ClosedDates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClosedDateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('date')
                    ->required()
                    ->native(false)
                    ->unique(ignoreRecord: true),
                Select::make('type')
                    ->options([
                        'closed' => 'Closed',
                        'holiday' => 'Holiday',
                    ])
                    ->required()
                    ->default('closed'),
                TextInput::make('note')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
