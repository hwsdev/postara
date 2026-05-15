<div>
    {{-- DNS Records modal --}}
    @if ($dnsRecords)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-end sm:items-center justify-center p-4"
             x-data x-on:keydown.escape.window="$wire.set('dnsRecords', null)">
            <div class="bg-white w-full max-w-2xl rounded-xl shadow-2xl overflow-hidden">

                {{-- Modal header --}}
                <div class="px-6 py-5 flex items-start justify-between gap-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-base">DNS Records</h3>
                        <p class="text-sm text-gray-400 mt-0.5 font-mono">{{ $dnsRecords['domain'] }}</p>
                    </div>
                    <button wire:click="$set('dnsRecords', null)"
                            class="text-gray-300 hover:text-black transition-colors mt-0.5 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-3 max-h-[60vh] overflow-y-auto">

                    {{-- Cloudflare auto-provision --}}
                    @if ($cfConfigured)
                        <div class="rounded-lg border border-orange-200 bg-orange-50 p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-orange-500 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-orange-900">Auto-provision via Cloudflare</p>
                                    <p class="text-xs text-orange-600 mt-0.5">Add all DNS records automatically to your Cloudflare zone.</p>
                                </div>
                            </div>
                            <button wire:click="provisionCloudflare({{ $dnsRecords['id'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="provisionCloudflare"
                                    class="flex-shrink-0 bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors disabled:opacity-60">
                                <span wire:loading wire:target="provisionCloudflare">Adding...</span>
                                <span wire:loading.remove wire:target="provisionCloudflare">Add to Cloudflare</span>
                            </button>
                        </div>

                        {{-- Provision result --}}
                        @if ($provisionResult)
                            @if ($provisionResult['success'])
                                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 flex items-center gap-2">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    All DNS records added to Cloudflare. Click <strong>Verify</strong> to confirm propagation.
                                </div>
                            @else
                                <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                                    {{ $provisionResult['error'] ?? 'Failed to provision records.' }}
                                </div>
                            @endif
                        @endif

                        <div class="border-t border-gray-100 pt-3">
                            <p class="text-xs text-gray-400 mb-3">Or add manually:</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Add these records to your DNS provider to enable sending.</p>
                    @endif

                    {{-- Manual DNS records --}}
                    @foreach (['spf' => 'SPF', 'dkim' => 'DKIM', 'dmarc' => 'DMARC'] as $key => $label)
                        <div class="rounded-lg border border-gray-100 bg-gray-50/50 overflow-hidden">
                            <div class="px-4 py-2.5 bg-gray-100/60 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-widest text-gray-500">{{ $label }}</span>
                                <span class="text-xs text-gray-400 font-mono">TXT</span>
                            </div>
                            <div class="px-4 py-3 space-y-2">
                                <div class="flex gap-3 items-start text-xs">
                                    <span class="text-gray-400 font-medium w-8 pt-0.5 flex-shrink-0">Host</span>
                                    <div class="flex-1 flex items-center gap-2 min-w-0">
                                        <code class="font-mono bg-white border border-gray-200 px-2 py-1 rounded text-gray-700 flex-1 break-all">{{ $dnsRecords[$key]['host'] }}</code>
                                        <button onclick="navigator.clipboard.writeText('{{ $dnsRecords[$key]['host'] }}')"
                                                class="flex-shrink-0 text-gray-300 hover:text-gray-600 transition-colors" title="Copy">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex gap-3 items-start text-xs">
                                    <span class="text-gray-400 font-medium w-8 pt-0.5 flex-shrink-0">Value</span>
                                    <div class="flex-1 flex items-center gap-2 min-w-0">
                                        <code class="font-mono bg-white border border-gray-200 px-2 py-1 rounded text-gray-700 flex-1 break-all">{{ $dnsRecords[$key]['value'] }}</code>
                                        <button onclick="navigator.clipboard.writeText('{{ addslashes($dnsRecords[$key]['value']) }}')"
                                                class="flex-shrink-0 text-gray-300 hover:text-gray-600 transition-colors" title="Copy">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-400">DNS changes can take up to 48h to propagate.</p>
                    <div class="flex gap-2">
                        <button wire:click="verify({{ $dnsRecords['id'] }})"
                                wire:loading.attr="disabled"
                                wire:target="verify"
                                class="border border-gray-200 text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-60">
                            <span wire:loading wire:target="verify">Checking...</span>
                            <span wire:loading.remove wire:target="verify">Verify now</span>
                        </button>
                        <button wire:click="$set('dnsRecords', null)"
                                class="bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500 mt-0.5">Verify your domain to start sending emails.</p>
        </div>
        <button wire:click="$toggle('showAddForm')"
                class="bg-black text-white text-sm font-semibold px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
            + Add domain
        </button>
    </div>

    {{-- Add form --}}
    @if ($showAddForm)
        <div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">
            <p class="text-sm font-semibold mb-3">Add a sending domain</p>
            <form wire:submit="addDomain" class="flex gap-2">
                <input wire:model="newDomain" type="text" placeholder="mail.yourdomain.com"
                       autofocus
                       class="flex-1 px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:border-black text-sm transition-colors font-mono">
                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="addDomain"
                        class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60">
                    <svg wire:loading wire:target="addDomain" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading wire:target="addDomain">Adding…</span>
                    <span wire:loading.remove wire:target="addDomain">Add</span>
                </button>
                <button type="button" wire:click="$toggle('showAddForm')"
                        class="border border-gray-200 text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
            </form>
            @error('newDomain')
                <p class="mt-2 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>
    @endif

    {{-- Domain list --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
        @if ($domains->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-700">No domains yet</p>
                <p class="text-xs text-gray-400 mt-1">Add a sending domain to get started.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Domain</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-widest text-gray-400">Verified</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($domains as $domain)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-sm font-medium">{{ $domain->domain }}</td>
                            <td class="px-5 py-3.5">
                                @php
                                    $badge = match($domain->status) {
                                        'verified' => 'text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200',
                                        'pending'  => 'text-amber-700 bg-amber-50 ring-1 ring-amber-200',
                                        'failed'   => 'text-red-700 bg-red-50 ring-1 ring-red-200',
                                        default    => 'text-gray-600 bg-gray-100',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full {{ $badge }}">
                                    @if ($domain->status === 'verified')
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    @elseif ($domain->status === 'pending')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    @else
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    @endif
                                    {{ ucfirst($domain->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs">
                                {{ $domain->verified_at ? $domain->verified_at->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="showDnsRecords({{ $domain->id }})"
                                            class="text-xs font-medium text-gray-500 hover:text-black px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                        DNS records
                                    </button>
                                    <button wire:click="verify({{ $domain->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="verify({{ $domain->id }})"
                                            class="text-xs font-medium text-gray-500 hover:text-black px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors disabled:opacity-50">
                                        <span wire:loading wire:target="verify({{ $domain->id }})">Checking…</span>
                                        <span wire:loading.remove wire:target="verify({{ $domain->id }})">Verify</span>
                                    </button>
                                    <button wire:click="delete({{ $domain->id }})"
                                            wire:confirm="Delete {{ $domain->domain }}?"
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
