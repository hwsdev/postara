<div>
    {{-- Flash messages --}}
    @if ($importStatus)
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $importStatus }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold">Contacts</h2>
            <p class="text-sm text-gray-400 mt-0.5">{{ $contacts->total() }} total contacts</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="$set('showAddForm', true); $set('showImport', false)"
                    class="border border-gray-200 text-sm font-medium px-4 py-2 rounded hover:bg-gray-50 transition-colors">
                + Add contact
            </button>
            <button wire:click="$set('showImport', true); $set('showAddForm', false)"
                    class="border border-gray-200 text-sm font-medium px-4 py-2 rounded hover:bg-gray-50 transition-colors">
                Import CSV
            </button>
        </div>
    </div>

    {{-- Add contact form --}}
    @if ($showAddForm)
        <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
            <p class="text-sm font-semibold mb-4">Add a contact</p>
            <form wire:submit="addContact" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                        <input wire:model="newEmail" type="email" placeholder="user@example.com"
                               autofocus
                               class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-black text-sm transition-colors font-mono
                                      {{ $errors->has('newEmail') ? 'border-red-400 bg-red-50' : '' }}">
                        @error('newEmail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Name <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input wire:model="newName" type="text" placeholder="Full name"
                               class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-black text-sm transition-colors
                                      {{ $errors->has('newName') ? 'border-red-400 bg-red-50' : '' }}">
                        @error('newName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex gap-2 pt-1">
                    <button type="submit"
                            wire:loading.attr="disabled"
                            wire:target="addContact"
                            class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60">
                        <svg wire:loading wire:target="addContact" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span wire:loading wire:target="addContact">Adding…</span>
                        <span wire:loading.remove wire:target="addContact">Add contact</span>
                    </button>
                    <button type="button" wire:click="$set('showAddForm', false)"
                            class="border border-gray-200 text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- CSV import --}}
    @if ($showImport)
        <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
            <h3 class="font-semibold text-sm mb-1">Import contacts from CSV</h3>
            <p class="text-xs text-gray-400 mb-4">CSV must have an <code class="font-mono">email</code> column. Optional: <code class="font-mono">name</code>.</p>
            <form wire:submit="importCsv" class="flex gap-3 items-start">
                <input wire:model="csvFile" type="file" accept=".csv,.txt"
                       class="flex-1 text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:opacity-90">
                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="importCsv"
                        class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-4 py-2.5 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60">
                    <svg wire:loading wire:target="importCsv" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading wire:target="importCsv">Importing…</span>
                    <span wire:loading.remove wire:target="importCsv">Import</span>
                </button>
                <button type="button" wire:click="$set('showImport', false)"
                        class="border border-gray-200 text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </form>
            @error('csvFile')
                <p class="mt-2 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>
    @endif

    {{-- Search --}}
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by email or name..."
               class="w-full max-w-sm px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-100">
        @if ($contacts->isEmpty())
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                No contacts found.
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Added</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contacts as $contact)
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs">{{ $contact->email }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $contact->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @if ($contact->subscribed)
                                    <span class="text-xs font-semibold uppercase tracking-wide text-green-700 bg-green-50 px-2 py-0.5 rounded">Subscribed</span>
                                @else
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 bg-gray-100 px-2 py-0.5 rounded">Unsubscribed</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-400 text-xs">{{ $contact->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    @if ($contact->subscribed)
                                        <button wire:click="unsubscribe({{ $contact->id }})"
                                                class="text-xs text-gray-400 hover:text-black transition-colors">
                                            Unsubscribe
                                        </button>
                                    @endif
                                    <button wire:click="delete({{ $contact->id }})"
                                            wire:confirm="Delete this contact?"
                                            class="text-xs text-gray-400 hover:text-red-600 transition-colors">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>
</div>
