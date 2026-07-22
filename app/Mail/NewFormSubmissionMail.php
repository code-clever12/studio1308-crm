<?php

namespace App\Mail;

use App\Models\FormSubmission;
use App\Models\Salon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewFormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public FormSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New lead: :form', ['form' => $this->submission->form->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-form-submission',
            with: ['salon' => Salon::query()->first()],
        );
    }
}
