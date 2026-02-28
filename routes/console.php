<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:user', function () {
    $email = $this->ask('Admin email');

    if (! $email) {
        $this->error('Email is required.');

        return self::FAILURE;
    }

    $name = $this->ask('Admin name', 'Black Ember Admin');
    $password = $this->secret('Admin password');
    $confirmPassword = $this->secret('Confirm admin password');

    if (! $password || ! $confirmPassword) {
        $this->error('Password is required.');

        return self::FAILURE;
    }

    if ($password !== $confirmPassword) {
        $this->error('Passwords do not match.');

        return self::FAILURE;
    }

    $admin = User::query()->firstOrNew(['email' => $email]);
    $admin->name = $name ?: 'Black Ember Admin';
    $admin->password = Hash::make($password);
    $admin->role = 'admin';
    $admin->save();

    $this->info('Admin account saved successfully.');
    $this->line('Email: ' . $admin->email);
    $this->line('Role: ' . $admin->role);

    return self::SUCCESS;
})->purpose('Create or update an admin user in the database');
