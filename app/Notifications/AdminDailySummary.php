<?php

namespace App\Notifications;

use App\Mail\AdminDailySummaryMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to admin users with today's appointments/no-shows/cancellations/revenue.
 */
class AdminDailySummary extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array{date: string, appointments_count: int, no_shows_count: int, cancellations_count: int, revenue: float}  $stats
     */
    public function __construct(public array $stats) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): AdminDailySummaryMail
    {
        return (new AdminDailySummaryMail($this->stats))->to($notifiable->email);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            ...$this->stats,
            'message' => "Today ({$this->stats['date']}): {$this->stats['appointments_count']} appointments, {$this->stats['no_shows_count']} no-shows, {$this->stats['cancellations_count']} cancellations, \${$this->stats['revenue']} revenue.",
        ];
    }
}
