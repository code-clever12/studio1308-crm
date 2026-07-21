<?php

namespace App\Mail;

use App\Models\Salon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminDailySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{date: string, appointments_count: int, no_shows_count: int, cancellations_count: int, revenue: float}  $stats
     */
    public function __construct(public array $stats) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Daily summary for :date', ['date' => $this->stats['date']]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-daily-summary',
            with: ['salon' => Salon::query()->first()],
        );
    }
}
