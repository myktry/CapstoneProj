<?php

use App\Models\User;
use App\Mail\OtpCodeMail;
use App\Services\OtpService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

Artisan::command('mail:test {to}', function (string $to) {
    try {
        Mail::mailer((string) config('mail.default', 'smtp'))->raw('Black Ember mail test.', function ($message) use ($to) {
            $message->to($to)->subject('Black Ember mail test');
        });

        $this->info('Test mail sent successfully to ' . $to . '.');

        return self::SUCCESS;
    } catch (Throwable $exception) {
        $this->error('Mail test failed: ' . $exception->getMessage());

        return self::FAILURE;
    }
})->purpose('Send a test email using the configured mailer');

Artisan::command('otp:test {email}', function (string $email, OtpService $otpService) {
    try {
        $result = $otpService->issueCode(
            purpose: 'register',
            channel: 'email',
            recipient: $email,
            context: ['stage' => 'registration-test'],
        );

        $this->info('OTP issued successfully.');
        $this->line('Challenge ID: ' . $result['challenge_id']);
        $this->line('Expires in: ' . $result['expires_in'] . ' minutes');

        return self::SUCCESS;
    } catch (ValidationException $exception) {
        $messages = $exception->errors()['otp'] ?? [$exception->getMessage()];
        $this->error('OTP test validation failed: ' . implode(' | ', $messages));

        return self::FAILURE;
    } catch (Throwable $exception) {
        $this->error('OTP test failed: ' . $exception->getMessage());

        return self::FAILURE;
    }
})->purpose('Test the registration OTP issuance flow');

Artisan::command('mail:otp-test {to}', function (string $to) {
    try {
        Mail::mailer((string) config('mail.default', 'smtp'))->to($to)->send(
            new OtpCodeMail(
                code: '123456',
                purpose: 'register',
                expiresInMinutes: 5,
            )
        );

        $this->info('OTP mailable sent successfully to ' . $to . '.');

        return self::SUCCESS;
    } catch (Throwable $exception) {
        $this->error('OTP mailable failed: ' . $exception->getMessage());

        return self::FAILURE;
    }
})->purpose('Send the OtpCodeMail mailable using the configured mailer');
