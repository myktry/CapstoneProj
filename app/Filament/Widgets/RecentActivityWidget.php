<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivityWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Activity';

    protected static bool $isLazy = true;

    protected ?string $placeholderHeight = '420px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ActivityLog::query()->latest()
            )
            ->columns([
                TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default   => 'gray',
                    }),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50]);
    }
}
