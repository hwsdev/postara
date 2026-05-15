<?php

namespace App\Livewire\ApiKeys;

use App\Models\ApiKey;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class ApiKeyManager extends Component
{
    public string $newKeyName = '';
    public ?string $generatedKey = null;
    public bool $showCreateForm = false;

    protected array $rules = [
        'newKeyName' => ['required', 'string', 'max:255'],
    ];

    public function create(): void
    {
        $this->validate();

        $workspaceId = session('current_workspace_id');

        // Generate a secure random key
        $plainKey = 'pk_'.Str::random(40);
        $prefix = substr($plainKey, 0, 8);

        ApiKey::create([
            'workspace_id' => $workspaceId,
            'name'         => $this->newKeyName,
            'key_hash'     => \Hash::make($plainKey),
            'key_prefix'   => $prefix,
        ]);

        $this->generatedKey = $plainKey;
        $this->newKeyName = '';
        $this->showCreateForm = false;
    }

    public function revoke(int $keyId): void
    {
        ApiKey::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($keyId)
            ->delete();
    }

    public function dismissKey(): void
    {
        $this->generatedKey = null;
    }

    public function render(): View
    {
        $apiKeys = ApiKey::where('workspace_id', session('current_workspace_id'))
            ->latest()
            ->get();

        return view('livewire.api-keys.api-key-manager', compact('apiKeys'));
    }
}
