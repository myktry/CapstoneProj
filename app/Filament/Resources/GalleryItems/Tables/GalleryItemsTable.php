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
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        $columns = [
            ImageColumn::make('image')
                ->disk('public')
                ->square(),
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('description')
                ->limit(50)
                ->toggleable(),
        ];

        if (DatabaseSchema::hasColumn('gallery_items', 'price')) {
            $columns[] = TextColumn::make('price')
                ->label('Price')
                ->money('PHP')
                ->sortable();
        }

        if (DatabaseSchema::hasColumn('gallery_items', 'featured_on_home')) {
            $columns[] = IconColumn::make('featured_on_home')
                ->boolean()
                ->label('Featured on Home');
        }

        $filters = [];

        if (DatabaseSchema::hasColumn('gallery_items', 'featured_on_home')) {
            $filters[] = TernaryFilter::make('featured_on_home')
                ->label('Featured on Home');
        }

        $bulkActions = [
            DeleteBulkAction::make(),
        ];

        if (DatabaseSchema::hasColumn('gallery_items', 'featured_on_home')) {
            $bulkActions = [
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
            ];
        }

        return $table
            ->columns($columns)
            ->filters($filters)
            ->defaultSort('created_at')
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make($bulkActions)->label('Actions'),
            ]);
    }
}
