<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class CompletedAppointmentsHistoryWidget extends TableWidget
{
    protected static ?string $heading = 'Completed Appointments History';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = true;

    protected ?string $placeholderHeight = '260px';

    public static function canView(): bool
    {
        $routeName = request()->route()?->getName();

        return is_string($routeName)
            && str_contains($routeName, 'filament.admin.resources.appointments.');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->where('status', 'completed')
                    ->with(['service:id,name'])
                    ->orderByDesc('appointment_date')
                    ->orderByDesc('appointment_time')
            )
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('service.name')
                    ->label('Service')
                    ->placeholder('-'),
                TextColumn::make('appointment_date')
                    ->label('Date')
                    ->date('F j, Y'),
                TextColumn::make('appointment_time')
                    ->label('Time')
                    ->formatStateUsing(fn (string $state): string => date('g:i A', strtotime($state))),
                TextColumn::make('amount_paid')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state): string => '₱' . number_format($state / 100, 2)),
                TextColumn::make('updated_at')
                    ->label('Completed At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25]);
    }
}
