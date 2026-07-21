<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Salon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NoShowFeeChargedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment, public float $feeAmount) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Receipt: no-show fee charged'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.no-show-fee-charged',
            with: ['salon' => Salon::query()->first()],
        );
    }
}
