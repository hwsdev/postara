<div>
    {{-- Filters --}}
    <div class="flex gap-3 mb-6">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search campaigns..."
               class="w-full max-w-xs px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors">
        <select wire:model.live="statusFilter"
                class="px-4 py-2.5 border border-gray-200 rounded focus:outline-none focus:border-black text-sm transition-colors bg-white">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="scheduled">Scheduled</option>
            <option value="sending">Sending</option>
            <option value="sent">Sent</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <div class="bg-white border border-gray-100">
        @if ($campaigns->isEmpty())
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                No campaigns yet.
            <a href="/campaigns/create"
               class="text-black font-medium hover:underline">Create your first campaign</a>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Scheduled</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($campaigns as $campaign)
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3">
                                <p class="font-medium">{{ $campaign->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $campaign->subject }}</p>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $colors = [
                                        'draft'     => 'text-gray-600 bg-gray-100',
                                        'scheduled' => 'text-blue-700 bg-blue-50',
                                        'sending'   => 'text-yellow-700 bg-yellow-50',
                                        'sent'      => 'text-green-700 bg-green-50',
                                        'cancelled' => 'text-red-700 bg-red-50',
                                    ];
                                    $color = $colors[$campaign->status] ?? 'text-gray-600 bg-gray-100';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded {{ $color }}">
                                    {{ $campaign->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-400 text-xs">
                                {{ $campaign->sent_at ? $campaign->sent_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-6 py-3 text-gray-400 text-xs">
                                {{ $campaign->scheduled_at ? $campaign->scheduled_at->format('M d, Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    @if (in_array($campaign->status, ['draft', 'scheduled']))
                                        <button wire:click="send({{ $campaign->id }})"
                                                wire:confirm="Send this campaign now?"
                                                class="text-xs font-medium text-black hover:underline">
                                            Send now
                                        </button>
                                    @endif
                                    @if ($campaign->status === 'scheduled')
                                        <button wire:click="cancel({{ $campaign->id }})"
                                                class="text-xs text-gray-400 hover:text-red-600 transition-colors">
                                            Cancel
                                        </button>
                                    @endif
                                    @if ($campaign->status === 'draft')
                                        <button wire:click="delete({{ $campaign->id }})"
                                                wire:confirm="Delete this campaign?"
                                                class="text-xs text-gray-400 hover:text-red-600 transition-colors">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
</div>
