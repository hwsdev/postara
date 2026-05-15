<?php

namespace App\Services;

use App\Models\Domain;
use Illuminate\Support\Facades\Http;

class CloudflareDnsService
{
    private string $apiToken;
    private string $baseUrl = 'https://api.cloudflare.com/client/v4';

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiToken);
    }

    /**
     * Find the Cloudflare Zone ID for a given domain.
     */
    public function findZoneId(string $domain): ?string
    {
        // Try the domain itself, then the apex
        $apex = $this->getApexDomain($domain);

        $response = $this->request('GET', '/zones', ['name' => $apex]);

        if (! $response->successful()) {
            return null;
        }

        $zones = $response->json('result', []);

        return $zones[0]['id'] ?? null;
    }

    /**
     * Provision all required DNS records for a domain.
     * Returns array of results per record type.
     */
    public function provisionRecords(Domain $domain): array
    {
        $zoneId = $this->findZoneId($domain->domain);

        if (! $zoneId) {
            return [
                'success' => false,
                'error' => "Could not find Cloudflare zone for {$domain->domain}. Make sure the domain is added to your Cloudflare account.",
            ];
        }

        $results = [];

        // SPF
        $results['spf'] = $this->upsertRecord($zoneId, [
            'type'    => 'TXT',
            'name'    => $domain->domain,
            'content' => $domain->getSpfRecord(),
            'ttl'     => 1, // auto
        ]);

        // DKIM
        $results['dkim'] = $this->upsertRecord($zoneId, [
            'type'    => 'TXT',
            'name'    => "{$domain->dkim_selector}._domainkey.{$domain->domain}",
            'content' => $domain->getDkimRecord(),
            'ttl'     => 1,
        ]);

        // DMARC
        $results['dmarc'] = $this->upsertRecord($zoneId, [
            'type'    => 'TXT',
            'name'    => "_dmarc.{$domain->domain}",
            'content' => $domain->getDmarcRecord(),
            'ttl'     => 1,
        ]);

        $allOk = collect($results)->every(fn ($r) => $r['success']);

        return [
            'success' => $allOk,
            'zone_id' => $zoneId,
            'records' => $results,
        ];
    }

    /**
     * Create or update a DNS record (upsert by name+type).
     */
    private function upsertRecord(string $zoneId, array $record): array
    {
        // Check if record already exists
        $existing = $this->request('GET', "/zones/{$zoneId}/dns_records", [
            'type' => $record['type'],
            'name' => $record['name'],
        ]);

        $existingRecords = $existing->json('result', []);

        if (! empty($existingRecords)) {
            // Update existing
            $recordId = $existingRecords[0]['id'];
            $response = $this->request('PUT', "/zones/{$zoneId}/dns_records/{$recordId}", $record);
        } else {
            // Create new
            $response = $this->request('POST', "/zones/{$zoneId}/dns_records", $record);
        }

        if ($response->successful() && $response->json('success')) {
            return [
                'success' => true,
                'action'  => ! empty($existingRecords) ? 'updated' : 'created',
                'name'    => $record['name'],
            ];
        }

        return [
            'success' => false,
            'name'    => $record['name'],
            'error'   => $response->json('errors.0.message', 'Unknown error'),
        ];
    }

    private function request(string $method, string $path, array $data = [])
    {
        $http = Http::withToken($this->apiToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->timeout(10);

        return match (strtoupper($method)) {
            'GET'  => $http->get($this->baseUrl.$path, $data),
            'POST' => $http->post($this->baseUrl.$path, $data),
            'PUT'  => $http->put($this->baseUrl.$path, $data),
            default => $http->get($this->baseUrl.$path, $data),
        };
    }

    private function getApexDomain(string $domain): string
    {
        $parts = explode('.', $domain);
        if (count($parts) > 2) {
            return implode('.', array_slice($parts, -2));
        }

        return $domain;
    }
}
