<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    Volt::route('admin/bootstrap', 'pages.auth.admin-bootstrap')
        ->name('admin.bootstrap');

    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('register/verify-otp', 'pages.auth.register-verify-otp')
        ->name('register.verify-otp');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('login/mfa-challenge', 'pages.auth.mfa-challenge')
        ->name('login.mfa-challenge');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
