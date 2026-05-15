<?php

namespace App\Services;

use App\Models\Domain;

class DkimService
{
    /**
     * Generate a DKIM RSA 2048-bit keypair and assign to domain.
     */
    public function generateKeypair(Domain $domain): Domain
    {
        $config = [
            'digest_alg'       => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'config'           => $this->opensslConfig(),
        ];

        $resource = openssl_pkey_new($config);

        openssl_pkey_export($resource, $privateKey, null, ['config' => $this->opensslConfig()]);
        $details = openssl_pkey_get_details($resource);
        $publicKey = $details['key'];

        $domain->update([
            'dkim_private_key' => $privateKey,
            'dkim_public_key'  => $publicKey,
            'dkim_selector'    => 'postara',
        ]);

        return $domain->fresh();
    }

    /**
     * Build a complete DKIM-Signature header value for a message.
     *
     * This follows RFC 6376 simple/simple canonicalization.
     *
     * @param  string  $headers  Raw headers string (e.g. "From: ...\r\nTo: ...\r\n...")
     * @param  string  $body     Raw message body
     * @param  Domain  $domain   Domain with dkim_private_key and dkim_selector
     * @param  array   $signHeaders  Header names to sign (order matters)
     * @return string  The full DKIM-Signature header value
     */
    public function buildSignatureHeader(
        string $headers,
        string $body,
        Domain $domain,
        array $signHeaders = ['From', 'To', 'Subject', 'Date', 'Message-ID']
    ): string {
        $selector = $domain->dkim_selector ?? 'postara';
        $domainName = $domain->domain;

        // --- Body canonicalization (simple) ---
        // Normalize CRLF, strip trailing blank lines, ensure single trailing CRLF
        $body = str_replace("\r\n", "\n", $body);
        $body = str_replace("\n", "\r\n", $body);
        $body = rtrim($body) . "\r\n";

        $bodyHash = base64_encode(hash('sha256', $body, true));

        // --- Header canonicalization (simple) ---
        // Extract the headers we want to sign in the specified order
        $canonHeaders = '';
        $signedHeaderNames = [];

        foreach ($signHeaders as $name) {
            $value = $this->extractHeader($headers, $name);
            if ($value !== null) {
                $canonHeaders .= $name . ': ' . $value . "\r\n";
                $signedHeaderNames[] = strtolower($name);
            }
        }

        // Build the DKIM-Signature header (without the b= value)
        $dkimHeaderValue = sprintf(
            'v=1; a=rsa-sha256; c=simple/simple; d=%s; s=%s; h=%s; bh=%s; b=',
            $domainName,
            $selector,
            implode(':', $signedHeaderNames),
            $bodyHash
        );

        // Append the DKIM-Signature header itself to the signed headers
        $canonHeaders .= 'DKIM-Signature: ' . $dkimHeaderValue;

        // --- Sign ---
        $privateKey = openssl_pkey_get_private($domain->dkim_private_key);
        openssl_sign($canonHeaders, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $b = base64_encode($signature);

        // Fold the b= value at 72 chars per line
        $folded = rtrim(chunk_split($b, 72, "\r\n\t"));

        return $dkimHeaderValue . $folded;
    }

    /**
     * Extract a header value from a raw headers string.
     * Handles folded (multi-line) headers.
     */
    private function extractHeader(string $headers, string $name): ?string
    {
        $pattern = '/^' . preg_quote($name, '/') . ':\s*(.*(?:\r?\n[ \t].*)*)/im';

        if (preg_match($pattern, $headers, $m)) {
            // Unfold: replace CRLF + whitespace with a single space
            return preg_replace('/\r?\n[ \t]+/', ' ', trim($m[1]));
        }

        return null;
    }

    /**
     * Return an openssl.cnf path that exists.
     * Falls back to a minimal inline config written to storage.
     */
    private function opensslConfig(): string
    {
        $candidates = [
            '/etc/ssl/openssl.cnf',
            '/usr/lib/ssl/openssl.cnf',
            '/usr/local/etc/openssl/openssl.cnf',
            '/etc/pki/tls/openssl.cnf',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        $fallback = storage_path('app/openssl.cnf');

        if (! file_exists($fallback)) {
            file_put_contents($fallback, "[req]\ndistinguished_name=req\n[req_ext]\n");
        }

        return $fallback;
    }
}
