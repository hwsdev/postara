<?php

namespace App\Livewire\Webhooks;

use App\Models\Webhook;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class WebhookList extends Component
{
    public bool $showCreateForm = false;
    public string $name = '';
    public string $url = '';
    public array $selectedEvents = [];

    public const AVAILABLE_EVENTS = [
        'email.delivered',
        'email.opened',
        'email.clicked',
        'email.bounced',
        'email.complained',
    ];

    protected function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'url'             => ['required', 'url'],
            'selectedEvents'  => ['required', 'array', 'min:1'],
        ];
    }

    public function create(): void
    {
        $this->validate();

        Webhook::create([
            'workspace_id' => session('current_workspace_id'),
            'name'         => $this->name,
            'url'          => $this->url,
            'events'       => $this->selectedEvents,
            'secret'       => Str::random(32),
            'active'       => true,
        ]);

        $this->reset(['name', 'url', 'selectedEvents', 'showCreateForm']);
    }

    public function toggle(int $webhookId): void
    {
        $webhook = Webhook::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($webhookId);

        $webhook->update(['active' => ! $webhook->active]);
    }

    public function rotateSecret(int $webhookId): void
    {
        Webhook::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($webhookId)
            ->update(['secret' => Str::random(32)]);
    }

    public function delete(int $webhookId): void
    {
        Webhook::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($webhookId)
            ->delete();
    }

    public function render(): View
    {
        $webhooks = Webhook::where('workspace_id', session('current_workspace_id'))
            ->latest()
            ->get();

        return view('livewire.webhooks.webhook-list', compact('webhooks'));
    }
}
