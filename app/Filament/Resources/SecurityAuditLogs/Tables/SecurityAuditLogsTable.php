<?php

namespace App\Filament\Resources\SecurityAuditLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SecurityAuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->select([
                        'id',
                        'admin_id',
                        'event',
                        'status',
                        'ip_address',
                        'transaction_id',
                        'message',
                        'context',
                        'created_at',
                    ])
                    ->with(['admin:id,name']);
            })
            ->columns([
                TextColumn::make('event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'security_alert' => 'danger',
                        'decryption_failed' => 'danger',
                        'decryption_success' => 'success',
                        'decryption_attempt' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('transaction_id')
                    ->label('Transaction')
                    ->searchable()
                    ->copyable()
                    ->placeholder('—'),
                TextColumn::make('admin.name')
                    ->label('Admin')
                    ->default('System')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('message')
                    ->searchable()
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('context')
                    ->formatStateUsing(fn ($state): string => empty($state) ? '—' : json_encode($state, JSON_UNESCAPED_SLASHES))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('M j, Y g:i:s A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'decryption_attempt' => 'Decryption Attempt',
                        'decryption_success' => 'Decryption Success',
                        'decryption_failed' => 'Decryption Failed',
                        'security_alert' => 'Security Alert',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),
            ]);
    }
}
