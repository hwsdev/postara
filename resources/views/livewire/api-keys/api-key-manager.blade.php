<div>
    {{-- Generated key banner --}}
    @if ($generatedKey)
        <div class="bg-black text-white p-5 rounded mb-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <p class="text-sm font-semibold mb-1">Save your API key — it won't be shown again.</p>
                    <code class="font-mono text-sm bg-white/10 px-3 py-1.5 rounded block break-all">{{ $generatedKey }}</code>
                </div>
                <button wire:click="dismissKey" class="text-white/60 hover:text-white transition-colors flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold">API Keys</h2>
            <p class="text-sm text-gray-400 mt-0.5">Use these keys to authenticate API requests.</p>
        </div>
        <button wire:click="$toggle('showCreateForm')"
                class="bg-black text-white text-sm font-semibold px-4 py-2 rounded hover:opacity-90 transition-opacity">
            Create key
        </button>
    </div>

    {{-- Create form --}}
    @if ($showCreateForm)
        <div class="bg-white border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-sm mb-4">New API key</h3>
            <form wire:submit="create" class="flex gap-3">
                <input wire:model="newKeyName" type="text" placeholder="e.g. Production, Staging"
                       class="flex-1 px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
                <button type="submit"
                        class="bg-black text-white text-sm font-semibold px-4 py-2.5 rounded hover:opacity-90 transition-opacity">
                    Generate
                </button>
                <button type="button" wire:click="$toggle('showCreateForm')"
                        class="border border-gray-200 text-sm font-medium px-4 py-2.5 rounded hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </form>
            @error('newKeyName')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    {{-- Keys table --}}
    <div class="bg-white border border-gray-100">
        @if ($apiKeys->isEmpty())
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                No API keys yet. Create one to start sending emails.
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Last used</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Created</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($apiKeys as $key)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-6 py-3 font-medium">{{ $key->name }}</td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-400">{{ $key->key_prefix }}••••••••••••••••</td>
                            <td class="px-6 py-3 text-gray-400 text-xs">
                                {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="px-6 py-3 text-gray-400 text-xs">{{ $key->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-3 text-right">
                                <button wire:click="revoke({{ $key->id }})"
                                        wire:confirm="Revoke this API key? This cannot be undone."
                                        class="text-xs font-medium text-gray-400 hover:text-red-600 transition-colors">
                                    Revoke
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
