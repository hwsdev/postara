<?php

namespace App\Services;

use App\Models\Domain;
use Illuminate\Support\Facades\Http;

class DomainVerifier
{
    private const DOH_URL = 'https://cloudflare-dns.com/dns-query';

    public function verify(Domain $domain): array
    {
        $results = [
            'spf' => $this->checkSpf($domain->domain),
            'dkim' => $this->checkDkim($domain->domain, $domain->dkim_selector),
            'dmarc' => $this->checkDmarc($domain->domain),
        ];

        $allVerified = collect($results)->every(fn ($r) => $r['verified']);

        $domain->update([
            'status' => $allVerified ? 'verified' : 'failed',
            'verified_at' => $allVerified ? now() : null,
        ]);

        return $results;
    }

    private function checkSpf(string $domain): array
    {
        $records = $this->queryDns($domain, 'TXT');

        $spfRecord = collect($records)->first(fn ($r) => str_starts_with($r, 'v=spf1'));

        return [
            'verified' => $spfRecord !== null,
            'record' => $spfRecord,
            'expected' => 'v=spf1 include:_spf.postara.dev ~all',
        ];
    }

    private function checkDkim(string $domain, string $selector): array
    {
        $dkimDomain = "{$selector}._domainkey.{$domain}";
        $records = $this->queryDns($dkimDomain, 'TXT');

        $dkimRecord = collect($records)->first(fn ($r) => str_contains($r, 'v=DKIM1'));

        return [
            'verified' => $dkimRecord !== null,
            'record' => $dkimRecord,
        ];
    }

    private function checkDmarc(string $domain): array
    {
        $dmarcDomain = "_dmarc.{$domain}";
        $records = $this->queryDns($dmarcDomain, 'TXT');

        $dmarcRecord = collect($records)->first(fn ($r) => str_starts_with($r, 'v=DMARC1'));

        return [
            'verified' => $dmarcRecord !== null,
            'record' => $dmarcRecord,
        ];
    }

    private function queryDns(string $name, string $type): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/dns-json',
            ])->get(self::DOH_URL, [
                'name' => $name,
                'type' => $type,
            ]);

            if ($response->failed()) {
                return [];
            }

            $data = $response->json();
            $answers = $data['Answer'] ?? [];

            return collect($answers)
                ->pluck('data')
                ->map(fn ($r) => trim($r, '"'))
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
