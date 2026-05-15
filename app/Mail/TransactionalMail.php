<?php

namespace App\Mail;

use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
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
            from: $this->parseAddress($this->emailModel->from),
            subject: $this->emailModel->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderedHtml ?: $this->emailModel->html,
            text: $this->emailModel->text ?? null,
        );
    }

    /**
     * Parse "Name <email@domain.com>" or plain "email@domain.com" into an Address.
     */
    private function parseAddress(string $raw): Address
    {
        if (preg_match('/^(.*?)\s*<([^>]+)>\s*$/', $raw, $m)) {
            return new Address(trim($m[2]), trim($m[1]));
        }

        return new Address(trim($raw));
    }
}
