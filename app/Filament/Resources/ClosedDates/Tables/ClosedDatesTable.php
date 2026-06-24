<?php

namespace App\Filament\Resources\ClosedDates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ClosedDatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'danger' => 'closed',
                        'warning' => 'holiday',
                    ]),
                TextColumn::make('note')
                    ->limit(40)
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                IconColumn::make('metadata_stego_png_base64')
                    ->boolean()
                    ->label('Stego meta')
                    ->getStateUsing(fn ($record): bool => filled($record->metadata_stego_png_base64)),
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'closed' => 'Closed',
                        'holiday' => 'Holiday',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
