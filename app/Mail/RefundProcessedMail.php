<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundProcessedMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Black Ember refund has been processed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.refund-processed',
        );
    }
}