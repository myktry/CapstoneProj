<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpCodeMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly string $purpose,
        public readonly int $expiresInMinutes,
    ) {
    }

   public function envelope(): Envelope
{
    return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name', 'Black Ember')),
            subject: 'Your account verification code',
    );
}

    public function content(): Content
    {
        return new Content(
            view: 'mail.otp-code',
        );
    }
}
