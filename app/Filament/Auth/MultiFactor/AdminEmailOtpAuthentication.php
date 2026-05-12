<?php

namespace App\Filament\Auth\MultiFactor;

use App\Models\User;
use App\Services\OtpService;
use Closure;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\Contracts\HasBeforeChallengeHook;
use Filament\Auth\MultiFactor\Contracts\MultiFactorAuthenticationProvider;
use Filament\Forms\Components\OneTimeCodeInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminEmailOtpAuthentication implements HasBeforeChallengeHook, MultiFactorAuthenticationProvider
{
    public function __construct(
        protected OtpService $otpService,
    ) {}

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'admin_email_otp';
    }

    public function getLoginFormLabel(): string
    {
        return 'Email OTP';
    }

    public function isEnabled(Authenticatable $user): bool
    {
        return $user instanceof User && $user->isAdmin() && filled($user->email);
    }

    public function beforeChallenge(Authenticatable $user): void
    {
        if (! $user instanceof User) {
            return;
        }

        try {
            $this->sendCode($user);
        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }

    public function getManagementSchemaComponents(): array
    {
        return [];
    }

    public function getChallengeFormComponents(Authenticatable $user): array
    {
        if (! $user instanceof User) {
            return [];
        }

        return [
            Select::make('mfa_channel')
                ->label('Verification method')
                ->options([
                    'email' => filled($user->email) ? 'Email' : 'Email unavailable',
                    'sms' => filled($user->phone) ? 'SMS' : 'SMS unavailable',
                ])
                ->default('email')
                ->required(),

            OneTimeCodeInput::make('code')
                ->label('Verification code')
                ->validationAttribute('code')
                ->belowContent(Action::make('resend')
                    ->label('Resend code')
                    ->link()
                    ->action(function () use ($user): void {
                        $channel = request()->input('data.mfa_channel', 'email');

                        try {
                            $this->sendCode($user, $channel);
                        } catch (\Throwable $throwable) {
                            report($throwable);

                            Notification::make()
                                ->title('Unable to resend the verification code right now.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Verification code resent.')
                            ->success()
                            ->send();
                    }))
                ->required()
                ->rule(function () use ($user): Closure {
                    return function (string $attribute, mixed $value, Closure $fail) use ($user): void {
                        $channel = request()->input('data.mfa_channel', 'email');

                        $recipient = $channel === 'sms'
                            ? trim((string) $user->phone)
                            : strtolower(trim((string) $user->email));

                        if ($this->otpService->verifyCode(
                            purpose: 'admin_login',
                            channel: $channel,
                            recipient: $recipient,
                            code: (string) $value,
                            userId: (int) $user->id,
                        )) {
                            return;
                        }

                        $fail('Invalid or expired verification code.');
                    };
                }),
        ];
    }

    protected function sendCode(User $user, string $channel = 'email'): void
    {
        $recipient = $channel === 'sms'
            ? trim((string) $user->phone)
            : strtolower(trim((string) $user->email));

        if ($recipient === '') {
            throw new \RuntimeException('No recipient is available for the selected verification method.');
        }

        $this->otpService->issueCode(
            purpose: 'admin_login',
            channel: $channel,
            recipient: $recipient,
            userId: (int) $user->id,
            context: ['stage' => 'admin-login-mfa', 'method' => $channel],
        );
    }
}
