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
     * Return an openssl.cnf path that exists, suppressing the tempnam warning.
     * Falls back to a minimal inline config written to storage.
     */
    private function opensslConfig(): string
    {
        // Common locations on Ubuntu / Alpine / macOS
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

        // Write a minimal config to storage so openssl doesn't fall back to /tmp
        $fallback = storage_path('app/openssl.cnf');

        if (! file_exists($fallback)) {
            file_put_contents($fallback, "[req]\ndistinguished_name=req\n[req_ext]\n");
        }

        return $fallback;
    }

    /**
     * Sign an email message with DKIM.
     */
    public function sign(string $headers, string $body, Domain $domain): string
    {
        $privateKey = openssl_pkey_get_private($domain->dkim_private_key);

        openssl_sign($body, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return base64_encode($signature);
    }
}
