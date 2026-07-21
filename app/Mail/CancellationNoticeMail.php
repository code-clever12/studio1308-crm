<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Salon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CancellationNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your appointment on :date has been cancelled', [
                'date' => $this->appointment->appointment_date->toFormattedDateString(),
            ]),
        );
    }

    public function content(): Content
    {
        $refundAmount = max(round(
            (float) $this->appointment->deposit_paid - (float) ($this->appointment->cancellation_fee ?? 0),
            2
        ), 0);

        return new Content(
            view: 'emails.cancellation-notice',
            with: [
                'salon' => Salon::query()->first(),
                'refundAmount' => $refundAmount,
            ],
        );
    }
}
