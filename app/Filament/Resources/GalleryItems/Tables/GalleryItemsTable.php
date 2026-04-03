<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('public')
                    ->square(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money('PHP')
                    ->sortable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                IconColumn::make('featured_on_home')
                    ->boolean()
                    ->label('Featured on Home'),
            ])
            ->filters([
                TernaryFilter::make('featured_on_home')
                    ->label('Featured on Home'),
            ])
            ->defaultSort('created_at')
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('featured')
                        ->label('Mark as Featured')
                        ->icon('heroicon-m-star')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->dispatch('gallery-featured-updated')
                        ->action(fn (Collection $records) => $records->each->update(['featured_on_home' => true]))
                        ->successNotificationTitle('Items marked as featured'),

                    BulkAction::make('unfeatured')
                        ->label('Remove from Featured')
                        ->icon('heroicon-m-x-mark')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->dispatch('gallery-featured-updated')
                        ->action(fn (Collection $records) => $records->each->update(['featured_on_home' => false]))
                        ->successNotificationTitle('Items removed from featured'),

                    DeleteBulkAction::make()
                        ->dispatch('gallery-featured-updated'),
                ])->label('Actions'),
            ]);
    }
}
