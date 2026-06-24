<?php

namespace App\Filament\Resources\ClosedDates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;

class ClosedDateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('metadata_stego_png_base64')
                    ->hidden()
                    ->rows(1)
                    ->default('')
                    ->extraAttributes(['class' => 'hidden']),
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
                SchemaView::make('filament.forms.closed-date-metadata-stego')
                    ->columnSpanFull()
                    ->viewData(fn (): array => [
                        'heheCarrierUrl' => url('img/hehe.png'),
                    ]),
            ]);
    }
}
