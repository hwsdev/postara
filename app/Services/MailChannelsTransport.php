<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;

/**
 * Custom Symfony Mailer transport for MailChannels Email API.
 *
 * API docs: https://docs.mailchannels.net/email-api/sending-email/email-intro
 * Auth: X-Api-Key header
 * Endpoint: POST https://api.mailchannels.net/tx/v1/send
 */
class MailChannelsTransport extends AbstractTransport
{
    private const ENDPOINT = 'https://api.mailchannels.net/tx/v1/send';

    public function __construct(
        private readonly string $apiKey
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        // Build recipients
        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = array_filter([
                'email' => $address->getAddress(),
                'name'  => $address->getName() ?: null,
            ]);
        }

        // Build from
        $fromAddresses = $email->getFrom();
        $fromAddress = reset($fromAddresses);
        $from = array_filter([
            'email' => $fromAddress->getAddress(),
            'name'  => $fromAddress->getName() ?: null,
        ]);

        // Build content
        $content = [];
        if ($email->getTextBody()) {
            $content[] = ['type' => 'text/plain', 'value' => $email->getTextBody()];
        }
        if ($email->getHtmlBody()) {
            $content[] = ['type' => 'text/html', 'value' => $email->getHtmlBody()];
        }

        $payload = [
            'from'    => $from,
            'to'      => $to,
            'subject' => $email->getSubject(),
            'content' => $content,
        ];

        // CC / BCC
        if ($cc = $email->getCc()) {
            $payload['cc'] = array_map(fn ($a) => array_filter([
                'email' => $a->getAddress(),
                'name'  => $a->getName() ?: null,
            ]), $cc);
        }

        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = array_map(fn ($a) => array_filter([
                'email' => $a->getAddress(),
                'name'  => $a->getName() ?: null,
            ]), $bcc);
        }

        // Reply-To
        if ($replyTo = $email->getReplyTo()) {
            $rt = reset($replyTo);
            $payload['reply_to'] = array_filter([
                'email' => $rt->getAddress(),
                'name'  => $rt->getName() ?: null,
            ]);
        }

        $response = Http::withHeaders([
            'X-Api-Key'    => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post(self::ENDPOINT, $payload);

        if ($response->failed()) {
            $error = $response->json('errors.0.message')
                ?? $response->json('message')
                ?? $response->body();

            throw new \RuntimeException("MailChannels API error ({$response->status()}): {$error}");
        }
    }

    public function __toString(): string
    {
        return 'mailchannels';
    }
}
