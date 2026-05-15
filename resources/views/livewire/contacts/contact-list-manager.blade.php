<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500 mt-0.5">Organize contacts into lists for campaigns.</p>
        </div>
        <button wire:click="$toggle('showCreateForm')"
                class="bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
            + New list
        </button>
    </div>

    {{-- Create form --}}
    @if ($showCreateForm)
        <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
            <p class="text-sm font-semibold mb-4">Create a contact list</p>
            <form wire:submit="create" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">List name <span class="text-red-500">*</span></label>
                    <input wire:model="newListName" type="text" placeholder="e.g. Newsletter subscribers"
                           autofocus
                           class="w-full px-3.5 py-2.5 border rounded-lg focus:outline-none text-sm transition-colors
                                  {{ $errors->has('newListName') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                    @error('newListName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Description <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input wire:model="newListDescription" type="text" placeholder="Short description of this list"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-black text-sm transition-colors">
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="create"
                            class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60">
                        <svg wire:loading wire:target="create" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span wire:loading wire:target="create">Creating…</span>
                        <span wire:loading.remove wire:target="create">Create list</span>
                    </button>
                    <button type="button" wire:click="$toggle('showCreateForm')"
                            class="border border-gray-200 text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Lists table --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        @if ($lists->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-700">No lists yet</p>
                <p class="text-xs text-gray-400 mt-1">Create a list to organize contacts for campaigns.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Description</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Contacts</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Created</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($lists as $list)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5">
                                <a href="/contacts/lists/{{ $list->id }}"
                                   class="font-medium hover:underline">{{ $list->name }}</a>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs max-w-xs truncate">{{ $list->description ?? '—' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-700 bg-gray-100 px-2.5 py-1 rounded-full">
                                    {{ number_format($list->contacts_count) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $list->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="/contacts/lists/{{ $list->id }}"
                                       class="text-xs font-medium text-gray-500 hover:text-black px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                        Manage
                                    </a>
                                    <button wire:click="delete({{ $list->id }})"
                                            wire:confirm="Delete list &quot;{{ $list->name }}&quot;? This won't delete the contacts themselves."
                                            class="text-xs font-medium text-gray-400 hover:text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
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
