<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login;
use Illuminate\Contracts\Support\Htmlable;

class AdminLogin extends Login
{
    public function getSubheading(): string|Htmlable|null
    {
        return filled($this->userUndertakingMultiFactorAuthentication)
            ? parent::getSubheading()
            : 'Admin access uses email OTP verification after password sign-in.';
    }
}
