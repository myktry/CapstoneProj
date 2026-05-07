<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Throwable;

class RecentActivityWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Activity';

    protected static bool $isLazy = true;

    protected ?string $placeholderHeight = '420px';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        try {
            $query = ActivityLog::query()->latest();
        } catch (Throwable $throwable) {
            report($throwable);

            $query = User::query()->whereRaw('1 = 0');
        }

        return $table
            ->query($query)
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
