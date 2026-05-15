<?php

namespace App\Mail;

use App\Models\Domain;
use App\Models\Email;
use App\Services\DkimService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email as SymfonyEmail;

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
     * Build the message — inject DKIM-Signature header if the sending domain
     * is verified and has a keypair stored.
     */
    public function build(): static
    {
        $fromEmail = $this->extractEmail($this->emailModel->from);
        $fromDomain = $fromEmail ? substr(strrchr($fromEmail, '@'), 1) : null;

        if ($fromDomain) {
            $domain = Domain::where('domain', $fromDomain)
                ->where('status', 'verified')
                ->whereNotNull('dkim_private_key')
                ->first();

            if ($domain) {
                $this->withSymfonyMessage(function (SymfonyEmail $message) use ($domain) {
                    $this->injectDkim($message, $domain);
                });
            }
        }

        return $this;
    }

    /**
     * Inject a DKIM-Signature header into the Symfony message.
     *
     * We sign after Symfony has assembled the message so we have the real
     * headers and body available.
     */
    private function injectDkim(SymfonyEmail $message, Domain $domain): void
    {
        try {
            $dkimService = app(DkimService::class);

            // Build a raw headers string from the Symfony message
            // so DkimService can parse and canonicalize them
            $signHeaderNames = ['From', 'To', 'Subject', 'Date', 'Message-ID'];
            $rawHeaders = '';

            foreach ($signHeaderNames as $name) {
                $header = $message->getHeaders()->get($name);
                if ($header) {
                    $rawHeaders .= $name . ': ' . $header->getBodyAsString() . "\r\n";
                }
            }

            // Get the serialized body (MIME part)
            $body = $message->getBody()?->toString() ?? '';

            $dkimHeaderValue = $dkimService->buildSignatureHeader(
                $rawHeaders,
                $body,
                $domain,
                $signHeaderNames
            );

            $message->getHeaders()->addTextHeader('DKIM-Signature', $dkimHeaderValue);
        } catch (\Throwable) {
            // Never block delivery because of a DKIM failure — just skip signing
        }
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

    /**
     * Extract plain email address from "Name <email>" or "email" format.
     */
    private function extractEmail(string $from): string
    {
        if (preg_match('/<([^>]+)>/', $from, $m)) {
            return trim($m[1]);
        }

        return trim($from);
    }
}
