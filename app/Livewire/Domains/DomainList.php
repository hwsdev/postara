<?php

namespace App\Livewire\Domains;

use App\Models\Domain;
use App\Services\CloudflareDnsService;
use App\Services\DkimService;
use App\Services\DomainVerifier;
use Illuminate\View\View;
use Livewire\Component;

class DomainList extends Component
{
    public string $newDomain = '';
    public bool $showAddForm = false;
    public ?array $dnsRecords = null;
    public ?int $verifyingId = null;
    public ?int $provisioningId = null;
    public ?array $provisionResult = null;

    protected array $rules = [
        'newDomain' => ['required', 'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
    ];

    public function addDomain(DkimService $dkimService): void
    {
        $this->validate();

        $workspaceId = session('current_workspace_id');

        // Prevent duplicate
        if (Domain::where('workspace_id', $workspaceId)->where('domain', strtolower($this->newDomain))->exists()) {
            $this->addError('newDomain', 'This domain is already added.');
            return;
        }

        $domain = Domain::create([
            'workspace_id' => $workspaceId,
            'domain'       => strtolower($this->newDomain),
            'status'       => 'pending',
        ]);

        $domain = $dkimService->generateKeypair($domain);

        $this->dnsRecords = [
            'id'    => $domain->id,
            'domain' => $domain->domain,
            'spf'   => ['type' => 'TXT', 'host' => $domain->domain,                                    'value' => $domain->getSpfRecord()],
            'dkim'  => ['type' => 'TXT', 'host' => "{$domain->dkim_selector}._domainkey.{$domain->domain}", 'value' => $domain->getDkimRecord()],
            'dmarc' => ['type' => 'TXT', 'host' => "_dmarc.{$domain->domain}",                         'value' => $domain->getDmarcRecord()],
        ];

        $this->newDomain = '';
        $this->showAddForm = false;
    }

    public function showDnsRecords(int $domainId): void
    {
        $domain = Domain::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($domainId);

        $this->dnsRecords = [
            'id'    => $domain->id,
            'domain' => $domain->domain,
            'spf'   => ['type' => 'TXT', 'host' => $domain->domain,                                    'value' => $domain->getSpfRecord()],
            'dkim'  => ['type' => 'TXT', 'host' => "{$domain->dkim_selector}._domainkey.{$domain->domain}", 'value' => $domain->getDkimRecord()],
            'dmarc' => ['type' => 'TXT', 'host' => "_dmarc.{$domain->domain}",                         'value' => $domain->getDmarcRecord()],
        ];

        $this->provisionResult = null;
    }

    public function provisionCloudflare(int $domainId, CloudflareDnsService $cf): void
    {
        $this->provisioningId = $domainId;

        $domain = Domain::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($domainId);

        $this->provisionResult = $cf->provisionRecords($domain);
        $this->provisioningId = null;
    }

    public function verify(int $domainId, DomainVerifier $verifier): void
    {
        $this->verifyingId = $domainId;

        $domain = Domain::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($domainId);

        $verifier->verify($domain);

        $this->verifyingId = null;
    }

    public function delete(int $domainId): void
    {
        Domain::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($domainId)
            ->delete();

        if ($this->dnsRecords && ($this->dnsRecords['id'] ?? null) === $domainId) {
            $this->dnsRecords = null;
        }
    }

    public function render(): View
    {
        $domains = Domain::where('workspace_id', session('current_workspace_id'))
            ->latest()
            ->get();

        $cfConfigured = app(CloudflareDnsService::class)->isConfigured();

        return view('livewire.domains.domain-list', compact('domains', 'cfConfigured'));
    }
}
