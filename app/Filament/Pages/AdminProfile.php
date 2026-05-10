<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AdminProfile extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $slug = 'admin-profile';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.admin-profile';

    public function getHeading(): string
    {
        return 'My Profile';
    }

    public function getSubheading(): ?string
    {
        return 'Manage your account settings and preferences';
    }
}
