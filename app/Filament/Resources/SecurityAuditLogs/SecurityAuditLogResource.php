<?php

namespace App\Filament\Resources\SecurityAuditLogs;

use App\Filament\Resources\SecurityAuditLogs\Pages\ListSecurityAuditLogs;
use App\Filament\Resources\SecurityAuditLogs\Tables\SecurityAuditLogsTable;
use App\Models\SecurityAuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SecurityAuditLogResource extends Resource
{
    protected static ?string $model = SecurityAuditLog::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    protected static ?string $navigationLabel = 'Security Audit Logs';

    protected static ?int $navigationSort = 6;

    public static function getModelLabel(): string
    {
        return 'Security Audit Log';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Security Audit Logs';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return SecurityAuditLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurityAuditLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
