<?php

namespace App\Filament\Resources\Appointments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->select([
                        'id',
                        'service_id',
                        'appointment_date',
                        'appointment_time',
                        'customer_name',
                        'customer_phone',
                        'status',
                        'stripe_session_id',
                        'stripe_payment_intent_id',
                        'amount_paid',
                        'refund_status',
                        'cancelled_by',
                        'created_at',
                    ])
                    ->with(['service:id,name']);
            })
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
                TextColumn::make('reference_number')
                    ->label('Reference No.')
                    ->formatStateUsing(fn ($state, $record): string => $record->reference_number)
                    ->copyable()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->orWhere('stripe_payment_intent_id', 'like', "%{$search}%")
                            ->orWhere('stripe_session_id', 'like', "%{$search}%");
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cancelled_by')
                    ->label('Cancellation Tag')
                    ->formatStateUsing(fn ($state, $record): string => $record->status === 'cancelled' && $state === 'user'
                        ? 'User cancel booking'
                        : ($record->status === 'cancelled' ? 'Cancelled' : 'N/A'))
                    ->badge()
                    ->color(fn ($state, $record): string => $record->status === 'cancelled' && $state === 'user'
                        ? 'danger'
                        : 'gray'),
                TextColumn::make('refund_status')
                    ->label('Refund')
                    ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : 'N/A')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'processed' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('amount_paid')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state): string => '₱'.number_format($state / 100, 2))
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
                        'completed' => 'Completed',
                        'paid' => 'Paid',
                        'pending' => 'Pending',
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
