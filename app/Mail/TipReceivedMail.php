<?php

namespace App\Mail;

use App\Models\Salon;
use App\Models\Tip;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TipReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Tip $tip) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You received a $:amount tip!', ['amount' => number_format((float) $this->tip->amount, 2)]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tip-received',
            with: ['salon' => Salon::query()->first()],
        );
    }
}
