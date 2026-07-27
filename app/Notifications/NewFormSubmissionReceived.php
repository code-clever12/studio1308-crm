<?php

namespace App\Notifications;

use App\Mail\NewFormSubmissionMail;
use App\Models\FormSubmission;
use App\Models\User;
use App\Notifications\Channels\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Sent to admin users the moment an external landing page posts a lead to
 * POST /api/v1/submit-form — see Api\FormSubmissionController.
 */
class NewFormSubmissionReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FormSubmission $submission) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Also sent to on-demand/anonymous notifiables (plain addresses from
        // Salon::leadNotificationEmails()) — those have no notifications()
        // relation or device tokens, so 'database' and the mobile push
        // channel only apply to real User (admin) accounts.
        if ($notifiable instanceof User) {
            return ['database', 'mail', FcmChannel::class];
        }

        return ['mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'New Lead',
            'body' => "New lead from {$this->submission->form->name}.",
            'data' => [
                'type' => 'new_lead',
                'submission_id' => (string) $this->submission->id,
            ],
        ];
    }

    public function toMail(object $notifiable): NewFormSubmissionMail
    {
        // routeNotificationFor('mail') rather than $notifiable->email directly,
        // since this is also sent to on-demand/anonymous notifiables (plain
        // addresses from Salon::leadNotificationEmails(), not User models) —
        // see NotificationService::sendNewFormSubmissionAlert().
        return (new NewFormSubmissionMail($this->submission))->to($notifiable->routeNotificationFor('mail'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'form_submission_id' => $this->submission->id,
            'form_name' => $this->submission->form->name,
            'message' => "New lead from {$this->submission->form->name}.",
        ];
    }
}
