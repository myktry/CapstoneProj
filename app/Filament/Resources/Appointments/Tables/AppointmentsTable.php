<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('service.name')
                    ->label('Service')
                    ->sortable(),
                TextColumn::make('appointment_date')
                    ->label('Date')
                    ->date('F j, Y')
                    ->sortable(),
                TextColumn::make('appointment_time')
                    ->label('Time')
                    ->formatStateUsing(fn (string $state): string => date('g:i A', strtotime($state))),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid'      => 'success',
                        'pending'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('amount_paid')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state): string => '₱' . number_format($state / 100, 2))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Booked At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'paid'      => 'Paid',
                        'pending'   => 'Pending',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('appointment_date', 'asc')
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
