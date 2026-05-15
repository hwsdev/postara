<div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-5 border-b border-gray-100">
        <h3 class="font-semibold text-sm">Send a test email</h3>
        <p class="text-xs text-gray-400 mt-0.5">Verify your mail configuration is working correctly.</p>
    </div>

    <div class="p-6">
        <form wire:submit="send" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Recipient email <span class="text-red-500">*</span></label>
                    <input wire:model="toEmail" type="email" placeholder="you@example.com"
                           class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                  {{ $errors->has('toEmail') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                    @error('toEmail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Recipient name <span class="text-gray-400 font-normal">(optional)</span></label>
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

                @if ($result)
                    <button type="button" wire:click="$set('result', null)"
                            class="text-xs text-gray-400 hover:text-gray-600 transition-colors">
                        Dismiss
                    </button>
                @endif
            </div>
        </form>

        {{-- Result --}}
        @if ($result)
            <div class="mt-4">
                @if ($result === 'success')
                    <div class="flex items-center gap-2.5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <p class="font-medium">Test email sent successfully.</p>
                            <p class="text-xs text-emerald-600 mt-0.5">Check <span class="font-mono">{{ $toEmail }}</span> — it may take a minute to arrive.</p>
                        </div>
                    </div>
                @else
                    <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">
                        <p class="font-medium">Failed to send test email.</p>
                        <p class="text-xs font-mono mt-1 break-all opacity-80">{{ str_replace('error:', '', $result) }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
