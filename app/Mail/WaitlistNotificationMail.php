<?php

namespace App\Mail;

use App\Models\Salon;
use App\Models\Waitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Waitlist $waitlist) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('A spot just opened up!'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.waitlist-notification',
            with: ['salon' => Salon::query()->first()],
        );
    }
}
