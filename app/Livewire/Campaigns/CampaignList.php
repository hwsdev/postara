<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Jobs\ProcessCampaignJob;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function send(int $campaignId): void
    {
        $campaign = Campaign::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($campaignId);

        if (! in_array($campaign->status, ['draft', 'scheduled'])) {
            return;
        }

        ProcessCampaignJob::dispatch($campaign);

        $this->dispatch('campaign-sent');
    }

    public function cancel(int $campaignId): void
    {
        Campaign::where('workspace_id', session('current_workspace_id'))
            ->where('status', 'scheduled')
            ->findOrFail($campaignId)
            ->update(['status' => 'cancelled']);
    }

    public function delete(int $campaignId): void
    {
        Campaign::where('workspace_id', session('current_workspace_id'))
            ->where('status', 'draft')
            ->findOrFail($campaignId)
            ->delete();
    }

    public function render(): View
    {
        $query = Campaign::where('workspace_id', session('current_workspace_id'))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest();

        return view('livewire.campaigns.campaign-list', [
            'campaigns' => $query->paginate(20),
        ]);
    }
}
