<div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-5 border-b border-gray-100">
        <h3 class="font-semibold text-sm">Send a test email</h3>
        <p class="text-xs text-gray-400 mt-0.5">Verify your mail configuration is working correctly.</p>
    </div>

    <div class="p-6 space-y-4">
        <form wire:submit="send" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Recipient email <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="toEmail" type="email" placeholder="you@example.com"
                           class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                  {{ $errors->has('toEmail') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                    @error('toEmail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">
                        Recipient name <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input wire:model="toName" type="text" placeholder="Your name"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-black transition-colors">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="send"
                        class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60">
                    <svg wire:loading wire:target="send" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading wire:target="send">Sending…</span>
                    <span wire:loading.remove wire:target="send">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            Send test email
                        </span>
                    </span>
                </button>

                @if (!empty($debugLog))
                    <button type="button" wire:click="clearLog"
                            class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                        Clear log
                    </button>
                @endif
            </div>
        </form>

        {{-- Result banner --}}
        @if ($result)
            <div>
                @if ($result === 'success')
                    <div class="flex items-center gap-2.5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="font-medium">Accepted by mail server.</p>
                            <p class="text-xs text-emerald-600 mt-0.5">
                                Check <span class="font-mono">{{ $toEmail }}</span> — may take a minute.
                                If it doesn't arrive, check spam or your provider's activity log.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">
                        <p class="font-medium">Failed to send.</p>
                        <p class="text-xs font-mono mt-1 break-all opacity-80">{{ str_replace('error:', '', $result) }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Debug log --}}
        @if (!empty($debugLog))
            <div class="rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-widest text-gray-500">Debug log</span>
                    <span class="text-xs text-gray-400 font-mono">{{ count($debugLog) }} steps</span>
                </div>
                <div class="divide-y divide-gray-100 font-mono text-xs">
                    @foreach ($debugLog as $entry)
                        <div @class([
                            'flex items-start gap-3 px-4 py-2.5',
                            'bg-white'          => $entry['level'] === 'info',
                            'bg-emerald-50'     => $entry['level'] === 'success',
                            'bg-red-50'         => $entry['level'] === 'error',
                        ])>
                            {{-- Icon --}}
                            <span class="flex-shrink-0 mt-0.5">
                                @if ($entry['level'] === 'success')
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif ($entry['level'] === 'error')
                                    <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                @else
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </span>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <span @class([
                                    'font-semibold',
                                    'text-gray-800'   => $entry['level'] === 'info',
                                    'text-emerald-700' => $entry['level'] === 'success',
                                    'text-red-700'    => $entry['level'] === 'error',
                                ])>{{ $entry['message'] }}</span>

                                @if (!empty($entry['context']))
                                    <div class="mt-1 space-y-0.5">
                                        @foreach ($entry['context'] as $key => $value)
                                            <div class="flex gap-2">
                                                <span class="text-gray-400 flex-shrink-0">{{ $key }}:</span>
                                                <span @class([
                                                    'break-all',
                                                    'text-gray-600' => $entry['level'] === 'info',
                                                    'text-emerald-600' => $entry['level'] === 'success',
                                                    'text-red-600' => $entry['level'] === 'error',
                                                ])>{{ is_bool($value) ? ($value ? 'true' : 'false') : $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Timestamp --}}
                            <span class="flex-shrink-0 text-gray-300 text-xs">{{ $entry['time'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
