<div>
    {{-- Import status --}}
    @if ($importStatus)
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded">
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
            <button wire:click="$toggle('showImport')"
                    class="border border-gray-200 text-sm font-medium px-4 py-2 rounded hover:bg-gray-50 transition-colors">
                Import CSV
            </button>
        </div>
    </div>

    {{-- CSV import --}}
    @if ($showImport)
        <div class="bg-white border border-gray-100 p-6 mb-6">
            <h3 class="font-semibold text-sm mb-1">Import contacts from CSV</h3>
            <p class="text-xs text-gray-400 mb-4">CSV must have an <code class="font-mono">email</code> column. Optional: <code class="font-mono">name</code>.</p>
            <form wire:submit="importCsv" class="flex gap-3 items-start">
                <input wire:model="csvFile" type="file" accept=".csv,.txt"
                       class="flex-1 text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:opacity-90">
                <button type="submit"
                        class="bg-black text-white text-sm font-semibold px-4 py-2.5 rounded hover:opacity-90 transition-opacity">
                    Import
                </button>
                <button type="button" wire:click="$toggle('showImport')"
                        class="border border-gray-200 text-sm font-medium px-4 py-2.5 rounded hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </form>
            @error('csvFile')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
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
