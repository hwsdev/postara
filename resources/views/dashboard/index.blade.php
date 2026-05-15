<x-app-layout>
    <x-slot name="title">Overview</x-slot>

    {{-- Stats grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-gray-100 p-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Sent today</p>
            <p class="text-3xl font-bold">{{ number_format($stats['emails_sent_today']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 p-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Sent this month</p>
            <p class="text-3xl font-bold">{{ number_format($stats['emails_sent_month']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 p-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Delivery rate</p>
            <p class="text-3xl font-bold">{{ $stats['delivered_rate'] }}%</p>
        </div>
        <div class="bg-white border border-gray-100 p-6">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Active campaigns</p>
            <p class="text-3xl font-bold">{{ $stats['active_campaigns'] }}</p>
        </div>
    </div>

    {{-- Recent emails --}}
    <div class="bg-white border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-sm">Recent emails</h2>
        </div>

        @if ($recentEmails->isEmpty())
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                No emails sent yet. Use the API to send your first email.
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">To</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Sent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentEmails as $email)
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs text-gray-400">em_{{ $email->id }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ implode(', ', (array) $email->to) }}</td>
                            <td class="px-6 py-3 text-gray-700 max-w-xs truncate">{{ $email->subject }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $statusColors = [
                                        'delivered' => 'text-green-700 bg-green-50',
                                        'queued'    => 'text-yellow-700 bg-yellow-50',
                                        'sending'   => 'text-blue-700 bg-blue-50',
                                        'bounced'   => 'text-red-700 bg-red-50',
                                        'failed'    => 'text-red-700 bg-red-50',
                                        'complained'=> 'text-orange-700 bg-orange-50',
                                    ];
                                    $color = $statusColors[$email->status] ?? 'text-gray-700 bg-gray-100';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded {{ $color }}">
                                    {{ $email->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-400 text-xs">{{ $email->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
