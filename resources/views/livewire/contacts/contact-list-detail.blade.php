<div>
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="/contacts/lists"
           class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-black transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Lists
        </a>
        <span class="text-gray-300">/</span>
        <div class="flex-1">
            <h2 class="font-semibold">{{ $list->name }}</h2>
            @if ($list->description)
                <p class="text-xs text-gray-400 mt-0.5">{{ $list->description }}</p>
            @endif
        </div>
        <span class="text-xs text-gray-400 font-mono">{{ number_format($list->contacts_count) }} contacts</span>
        <button wire:click="$set('showAddPanel', true)"
                class="bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
            + Add contacts
        </button>
    </div>

    {{-- Add contacts panel --}}
    @if ($showAddPanel)
        <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold">Add contacts to "{{ $list->name }}"</p>
                <button wire:click="$set('showAddPanel', false)"
                        class="text-gray-400 hover:text-black transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <input wire:model.live.debounce.300ms="addSearch" type="search"
                   placeholder="Search contacts not in this list…"
                   autofocus
                   class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-black text-sm transition-colors mb-3">

            @if ($addCandidates->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">
                    {{ $addSearch ? 'No contacts found.' : 'All contacts are already in this list.' }}
                </p>
            @else
                <div class="border border-gray-100 rounded-lg overflow-hidden mb-3 max-h-64 overflow-y-auto">
                    @foreach ($addCandidates as $contact)
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                            <input type="checkbox"
                                   wire:model.live="selectedContactIds"
                                   value="{{ $contact->id }}"
                                   class="rounded border-gray-300 text-black focus:ring-black">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-mono text-gray-800 truncate">{{ $contact->email }}</p>
                                @if ($contact->name)
                                    <p class="text-xs text-gray-400">{{ $contact->name }}</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">
                        {{ count($selectedContactIds) }} selected
                    </span>
                    <div class="flex gap-2">
                        <button type="button" wire:click="$set('showAddPanel', false)"
                                class="border border-gray-200 text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="addSelected"
                                wire:loading.attr="disabled"
                                wire:target="addSelected"
                                class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-40">
                            <svg wire:loading wire:target="addSelected" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <span wire:loading wire:target="addSelected">Adding…</span>
                            <span wire:loading.remove wire:target="addSelected">
                                Add
                                @if (count($selectedContactIds) > 0)
                                    {{ count($selectedContactIds) }}
                                @endif
                                to list
                            </span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Search members --}}
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="search"
               placeholder="Search members…"
               class="w-full max-w-sm px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-black text-sm transition-colors">
    </div>

    {{-- Members table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        @if ($members->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-700">No contacts in this list</p>
                <p class="text-xs text-gray-400 mt-1">Click "Add contacts" to add members.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Added to list</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($members as $contact)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs">{{ $contact->email }}</td>
                            <td class="px-5 py-3.5 text-gray-700 text-sm">{{ $contact->name ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                @if ($contact->subscribed)
                                    <span class="text-xs font-semibold uppercase tracking-wide text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Subscribed</span>
                                @else
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">Unsubscribed</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs">
                                {{ $contact->pivot->created_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <button wire:click="removeContact({{ $contact->id }})"
                                        wire:confirm="Remove {{ $contact->email }} from this list?"
                                        class="text-xs font-medium text-gray-400 hover:text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
