<?php

namespace App\Filament\Resources\ClosedDates;

use App\Filament\Resources\ClosedDates\Pages\ListClosedDates;
use App\Filament\Resources\ClosedDates\Schemas\ClosedDateForm;
use App\Filament\Resources\ClosedDates\Tables\ClosedDatesTable;
use App\Models\ClosedDate;
use BackedEnum;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClosedDateResource extends Resource
{
    protected static ?string $model = ClosedDate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Calendar Dates';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getModelLabel(): string
    {
        return 'Calendar Date';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Calendar Dates';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'closed-dates';
    }

    public static function form(Schema $schema): Schema
    {
        return ClosedDateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClosedDatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClosedDates::route('/'),
        ];
    }
}
