<x-app-layout>
    <x-slot name="title">Overview</x-slot>

    {{-- Stats grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-gray-100 p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Sent today</p>
            <p class="text-3xl font-bold">{{ number_format($stats['emails_sent_today']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Sent this month</p>
            <p class="text-3xl font-bold">{{ number_format($stats['emails_sent_month']) }}</p>
        </div>
        <div class="bg-white border border-gray-100 p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Delivery rate</p>
            <p class="text-3xl font-bold">{{ $stats['delivered_rate'] }}%</p>
        </div>
        <div class="bg-white border border-gray-100 p-5 rounded-xl">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-2">Total contacts</p>
            <p class="text-3xl font-bold">{{ number_format($stats['total_contacts']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- 7-day send volume chart --}}
        <div class="lg:col-span-2 bg-white border border-gray-100 rounded-xl p-5">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="font-semibold text-sm">Send volume</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Last 7 days</p>
                </div>
                <span class="text-xs text-gray-400 font-mono">
                    {{ number_format(collect($chartData)->sum('count')) }} total
                </span>
            </div>

            @php $maxCount = max(collect($chartData)->max('count'), 1); @endphp

            <div class="flex items-end gap-2 h-28">
                @foreach ($chartData as $day)
                    @php $pct = round(($day['count'] / $maxCount) * 100); @endphp
                    <div class="flex-1 flex flex-col items-center gap-1.5">
                        <span class="text-xs text-gray-400 font-mono {{ $day['count'] > 0 ? 'text-gray-700' : '' }}">
                            {{ $day['count'] > 0 ? $day['count'] : '' }}
                        </span>
                        <div class="w-full rounded-t-sm transition-all"
                             style="height: {{ max($pct, 4) }}%; background: {{ $day['count'] > 0 ? '#0A0A0A' : '#EEEEEE' }}">
                        </div>
                        <span class="text-xs text-gray-400">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5 space-y-4">
            <h2 class="font-semibold text-sm">Today</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Emails sent</span>
                    <span class="font-semibold text-sm">{{ number_format($stats['emails_sent_today']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Opens</span>
                    <span class="font-semibold text-sm">{{ number_format($stats['opens_today']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Active campaigns</span>
                    <span class="font-semibold text-sm">{{ number_format($stats['active_campaigns']) }}</span>
                </div>
                <div class="pt-3 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs text-gray-400">Delivery rate (month)</span>
                        <span class="text-xs font-semibold">{{ $stats['delivered_rate'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-black h-1.5 rounded-full transition-all"
                             style="width: {{ $stats['delivered_rate'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent campaigns --}}
    @if ($recentCampaigns->isNotEmpty())
        <div class="bg-white border border-gray-100 rounded-xl overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-sm">Recent campaigns</h2>
                <a href="{{ route('campaigns.index') }}" class="text-xs text-gray-400 hover:text-black transition-colors">View all →</a>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Sent</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Delivered</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Opened</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Clicked</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Bounced</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentCampaigns as $campaign)
                        @php $s = $campaign->getStats(); @endphp
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3">
                                <p class="font-medium text-sm">{{ $campaign->name }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $campaign->sent_at?->format('M d, Y') ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-3 font-mono text-sm">{{ number_format($s['sent']) }}</td>
                            <td class="px-6 py-3">
                                <span class="text-sm font-mono">{{ number_format($s['delivered']) }}</span>
                                @if ($s['sent'] > 0)
                                    <span class="text-xs text-gray-400 ml-1">{{ round(($s['delivered'] / $s['sent']) * 100) }}%</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm font-mono">{{ number_format($s['opened']) }}</span>
                                @if ($s['delivered'] > 0)
                                    <span class="text-xs text-gray-400 ml-1">{{ round(($s['opened'] / $s['delivered']) * 100) }}%</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm font-mono">{{ number_format($s['clicked']) }}</span>
                                @if ($s['delivered'] > 0)
                                    <span class="text-xs text-gray-400 ml-1">{{ round(($s['clicked'] / $s['delivered']) * 100) }}%</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-sm font-mono {{ $s['bounced'] > 0 ? 'text-red-600' : '' }}">{{ number_format($s['bounced']) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Recent emails --}}
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
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
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 font-mono text-xs text-gray-400">em_{{ $email->id }}</td>
                            <td class="px-6 py-3 text-gray-700 text-xs font-mono">{{ implode(', ', (array) $email->to) }}</td>
                            <td class="px-6 py-3 text-gray-700 max-w-xs truncate">{{ $email->subject }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $statusColors = [
                                        'delivered'  => 'text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200',
                                        'queued'     => 'text-amber-700 bg-amber-50 ring-1 ring-amber-200',
                                        'sending'    => 'text-blue-700 bg-blue-50 ring-1 ring-blue-200',
                                        'bounced'    => 'text-red-700 bg-red-50 ring-1 ring-red-200',
                                        'failed'     => 'text-red-700 bg-red-50 ring-1 ring-red-200',
                                        'complained' => 'text-orange-700 bg-orange-50 ring-1 ring-orange-200',
                                    ];
                                    $color = $statusColors[$email->status] ?? 'text-gray-700 bg-gray-100';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold uppercase tracking-wide rounded-full {{ $color }}">
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
