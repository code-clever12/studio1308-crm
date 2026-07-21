<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Salon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("You're confirmed: :service on :date", [
                'service' => $this->appointment->service->name,
                'date' => $this->appointment->appointment_date->toFormattedDateString(),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: ['salon' => Salon::query()->first()],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->appointment->toIcsContent(), 'appointment.ics')
                ->withMime('text/calendar'),
        ];
    }
}
