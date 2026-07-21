<?php

namespace App\Mail;

use App\Models\ACHPayout;
use App\Models\Salon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ACHPayout $payout) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Action needed: ACH payout failed'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payout-failed',
            with: ['salon' => Salon::query()->first()],
        );
    }
}
