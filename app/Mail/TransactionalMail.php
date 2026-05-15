<?php

namespace App\Mail;

use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Email $emailModel,
        public readonly string $renderedHtml
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->emailModel->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderedHtml ?: $this->emailModel->html,
        );
    }
}
