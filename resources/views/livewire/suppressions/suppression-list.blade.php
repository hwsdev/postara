<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500 mt-0.5">Emails to these addresses are blocked automatically.</p>
        </div>
        <div class="text-xs text-gray-400 font-mono">{{ $suppressions->total() }} total</div>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by email..."
               class="w-full max-w-sm px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-black text-sm transition-colors font-mono">
    </div>

    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        @if ($suppressions->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-700">No suppressed addresses</p>
                <p class="text-xs text-gray-400 mt-1">Hard bounces, complaints, and unsubscribes appear here.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Reason</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Added</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($suppressions as $suppression)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs">{{ $suppression->email }}</td>
                            <td class="px-5 py-3.5">
                                @php
                                    $reasonColors = [
                                        'hard_bounce' => 'text-red-700 bg-red-50 ring-1 ring-red-200',
                                        'soft_bounce' => 'text-orange-700 bg-orange-50 ring-1 ring-orange-200',
                                        'complaint'   => 'text-red-700 bg-red-50 ring-1 ring-red-200',
                                        'unsubscribe' => 'text-gray-600 bg-gray-100',
                                        'manual'      => 'text-gray-600 bg-gray-100',
                                    ];
                                    $rc = $reasonColors[$suppression->reason] ?? 'text-gray-600 bg-gray-100';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded-full {{ $rc }}">
                                    {{ str_replace('_', ' ', $suppression->reason) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $suppression->created_at->diffForHumans() }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <button wire:click="remove({{ $suppression->id }})"
                                        wire:confirm="Remove {{ $suppression->email }} from suppression list? They will be able to receive emails again."
                                        class="text-xs font-medium text-gray-400 hover:text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $suppressions->links() }}
            </div>
        @endif
    </div>
</div>
