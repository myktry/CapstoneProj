<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Models\Service;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('image')
                    ->hidden()
                    ->maxLength(255),
                Textarea::make('metadata_stego_png_base64')
                    ->hidden()
                    ->rows(1)
                    ->default('')
                    ->extraAttributes(['class' => 'hidden']),
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
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true),
                Toggle::make('use_hehe_for_stego_carrier')
                    ->label('Use mascot (hehe.png) as steganography carrier')
                    ->helperText('When off, uses the stored service or linked gallery image URL when available; otherwise falls back to the mascot.')
                    ->default(false)
                    ->dehydrated(false),
                SchemaView::make('filament.forms.service-metadata-stego')
                    ->columnSpanFull()
                    ->viewData(function ($livewire): array {
                        $record = method_exists($livewire, 'getRecord') ? $livewire->getRecord() : null;
                        if ($record instanceof Service) {
                            $record->loadMissing('galleryItem');
                        } else {
                            $record = null;
                        }

                        return [
                            'galleryCarrierUrl' => $record?->carrierImagePublicUrl(),
                            'heheCarrierUrl' => url('img/hehe.png'),
                            'galleryName' => $record?->galleryItem?->name ?? '',
                            'galleryImagePath' => $record?->galleryItem?->image ?? '',
                        ];
                    }),
            ]);
    }
}
