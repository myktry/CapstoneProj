<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AdminProfile extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament.pages.admin-profile';

    protected static ?string $navigationLabel = 'My Profile';

    protected static ?string $title = 'My Profile';

    protected static ?int $navigationSort = 9;

    public function getHeading(): string
    {
        return 'My Profile';
    }

    public function getSubheading(): ?string
    {
        return 'Manage your account settings and preferences';
    }
}
