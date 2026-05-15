<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold">Webhooks</h2>
            <p class="text-sm text-gray-400 mt-0.5">Receive real-time notifications for email events.</p>
        </div>
        <button wire:click="$toggle('showCreateForm')"
                class="bg-black text-white text-sm font-semibold px-4 py-2 rounded hover:opacity-90 transition-opacity">
            Add webhook
        </button>
    </div>

    {{-- Create form --}}
    @if ($showCreateForm)
        <div class="bg-white border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-sm mb-4">New webhook</h3>
            <form wire:submit="create" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input wire:model="name" type="text" placeholder="e.g. Slack notifications"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Endpoint URL</label>
                        <input wire:model="url" type="url" placeholder="https://your-app.com/webhooks"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
                        @error('url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Events</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach (\App\Livewire\Webhooks\WebhookList::AVAILABLE_EVENTS as $event)
                            <label class="flex items-center gap-2 text-sm cursor-pointer">
                                <input type="checkbox" wire:model="selectedEvents" value="{{ $event }}"
                                       class="rounded border-gray-300">
                                <code class="font-mono text-xs">{{ $event }}</code>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedEvents') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-black text-white text-sm font-semibold px-4 py-2.5 rounded hover:opacity-90 transition-opacity">
                        Create webhook
                    </button>
                    <button type="button" wire:click="$toggle('showCreateForm')"
                            class="border border-gray-200 text-sm font-medium px-4 py-2.5 rounded hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Webhooks list --}}
    <div class="bg-white border border-gray-100">
        @if ($webhooks->isEmpty())
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                No webhooks configured yet.
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Events</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($webhooks as $webhook)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="px-6 py-3 font-medium">{{ $webhook->name }}</td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-500 max-w-xs truncate">{{ $webhook->url }}</td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($webhook->events as $event)
                                        <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono">{{ $event }}</code>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                @if ($webhook->active)
                                    <span class="text-xs font-semibold uppercase tracking-wide text-green-700 bg-green-50 px-2 py-0.5 rounded">Active</span>
                                @else
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="toggle({{ $webhook->id }})"
                                            class="text-xs text-gray-400 hover:text-black transition-colors">
                                        {{ $webhook->active ? 'Disable' : 'Enable' }}
                                    </button>
                                    <button wire:click="rotateSecret({{ $webhook->id }})"
                                            wire:confirm="Rotate the signing secret? Update your endpoint to use the new secret."
                                            class="text-xs text-gray-400 hover:text-black transition-colors">
                                        Rotate secret
                                    </button>
                                    <button wire:click="delete({{ $webhook->id }})"
                                            wire:confirm="Delete this webhook?"
                                            class="text-xs text-gray-400 hover:text-red-600 transition-colors">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
