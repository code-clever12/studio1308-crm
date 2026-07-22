<?php

namespace App\Notifications;

use App\Mail\NewFormSubmissionMail;
use App\Models\FormSubmission;
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): NewFormSubmissionMail
    {
        return (new NewFormSubmissionMail($this->submission))->to($notifiable->email);
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
