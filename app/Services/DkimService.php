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
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $resource = openssl_pkey_new($config);

        openssl_pkey_export($resource, $privateKey);
        $details = openssl_pkey_get_details($resource);
        $publicKey = $details['key'];

        $domain->update([
            'dkim_private_key' => $privateKey,
            'dkim_public_key' => $publicKey,
            'dkim_selector' => 'postara',
        ]);

        return $domain->fresh();
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
