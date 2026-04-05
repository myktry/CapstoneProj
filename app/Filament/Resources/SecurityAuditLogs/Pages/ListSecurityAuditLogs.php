<?php

namespace App\Filament\Resources\SecurityAuditLogs\Pages;

use App\Filament\Resources\SecurityAuditLogs\SecurityAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSecurityAuditLogs extends ListRecords
{
    protected static string $resource = SecurityAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
