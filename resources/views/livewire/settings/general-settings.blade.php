<div class="space-y-6">

    {{-- Saved banner --}}
    @if ($saved)
        <div class="flex items-center gap-2.5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Settings saved.
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">

        {{-- Mail transport --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="font-semibold text-sm">Mail transport</h3>
                <p class="text-xs text-gray-400 mt-0.5">How Postara sends emails.</p>
            </div>
            <div class="p-6 space-y-5">

                {{-- Mode selector --}}
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach ([
                        'postfix'      => ['label' => 'Self-hosted',  'sub' => 'Postfix on VPS'],
                        'smtp'         => ['label' => 'SMTP relay',   'sub' => 'Brevo, SES, etc.'],
                        'mailchannels' => ['label' => 'MailChannels', 'sub' => 'HTTP API'],
                        'log'          => ['label' => 'Log only',     'sub' => 'Dev / testing'],
                    ] as $val => $meta)
                        <button type="button" wire:click="$set('mailMode', '{{ $val }}')"
                                @class([
                                    'px-3 py-3 rounded-xl border text-left transition-all',
                                    'border-black bg-black text-white' => $mailMode === $val,
                                    'border-gray-200 hover:border-gray-400 text-gray-700' => $mailMode !== $val,
                                ])>
                            <p class="text-sm font-semibold">{{ $meta['label'] }}</p>
                            <p @class(['text-xs mt-0.5', 'text-white/60' => $mailMode === $val, 'text-gray-400' => $mailMode !== $val])>{{ $meta['sub'] }}</p>
                        </button>
                    @endforeach
                </div>

                {{-- SMTP --}}
                @if ($mailMode === 'smtp')
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">SMTP host</label>
                            <input wire:model="mailHost" type="text" placeholder="smtp.brevo.com"
                                   class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:border-black transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Port</label>
                            <input wire:model="mailPort" type="number" placeholder="587"
                                   class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:border-black transition-colors">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Username</label>
                            <input wire:model="mailUsername" type="text" placeholder="Optional"
                                   class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-black transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Password / API key</label>
                            <input wire:model="mailPassword" type="password"
                                   placeholder="{{ $mailPassword ? '••••••••' : 'Optional' }}"
                                   class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-black transition-colors">
                            @if ($mailPassword)
                                <p class="mt-1 text-xs text-gray-400">Password saved. Enter a new one to change it.</p>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Encryption</label>
                        <div class="flex gap-2">
                            @foreach (['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', '' => 'None (25)'] as $val => $lbl)
                                <button type="button" wire:click="$set('mailEncryption', '{{ $val }}')"
                                        @class([
                                            'px-3 py-2 rounded-lg border text-sm font-medium transition-all',
                                            'border-black bg-black text-white' => $mailEncryption === $val,
                                            'border-gray-200 text-gray-600 hover:border-gray-400' => $mailEncryption !== $val,
                                        ])>
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-2">Quick fill:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ([
                                'Brevo'    => ['smtp-relay.brevo.com', '587', 'tls'],
                                'Mailgun'  => ['smtp.mailgun.org', '587', 'tls'],
                                'SES'      => ['email-smtp.us-east-1.amazonaws.com', '587', 'tls'],
                                'Postmark' => ['smtp.postmarkapp.com', '587', 'tls'],
                                'Resend'   => ['smtp.resend.com', '587', 'tls'],
                            ] as $name => [$h, $p, $enc])
                                <button type="button"
                                        wire:click="$set('mailHost', '{{ $h }}'); $set('mailPort', '{{ $p }}'); $set('mailEncryption', '{{ $enc }}')"
                                        class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 text-gray-500 hover:border-gray-400 hover:text-gray-800 transition-colors font-mono">
                                    {{ $name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Postfix --}}
                @if ($mailMode === 'postfix')
                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Postfix host</label>
                            <input wire:model="postfixHost" type="text" placeholder="127.0.0.1"
                                   class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:border-black transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Port</label>
                            <input wire:model="postfixPort" type="number" placeholder="25"
                                   class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:border-black transition-colors">
                        </div>
                    </div>
                @endif

                {{-- MailChannels --}}
                @if ($mailMode === 'mailchannels')
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">MailChannels API key</label>
                        <input wire:model="mailChannelsApiKey" type="password" placeholder="mc_api_key_..."
                               class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:border-black transition-colors">
                    </div>
                @endif

                {{-- Log mode notice --}}
                @if ($mailMode === 'log')
                    <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                        <p class="font-semibold">Development / testing mode</p>
                        <p class="text-xs mt-1 text-amber-700">Emails will be written to <code class="font-mono bg-amber-100 px-1 rounded">storage/logs/laravel.log</code> — not sent.</p>
                    </div>
                @endif

                {{-- From address (always shown) --}}
                <div class="grid grid-cols-2 gap-3 pt-1 border-t border-gray-100">
                    <div class="pt-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">From name</label>
                        <input wire:model="mailFromName" type="text" placeholder="Postara"
                               class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                      {{ $errors->has('mailFromName') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                        @error('mailFromName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="pt-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">From address</label>
                        <input wire:model="mailFromAddress" type="email" placeholder="noreply@yourdomain.com"
                               class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                      {{ $errors->has('mailFromAddress') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                        @error('mailFromAddress') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

            </div>
        </div>

        {{-- Cloudflare --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-5 border-b border-gray-100">
                <h3 class="font-semibold text-sm">Cloudflare DNS</h3>
                <p class="text-xs text-gray-400 mt-0.5">Auto-provision SPF, DKIM, and DMARC records when adding a domain.</p>
            </div>
            <div class="p-6">
                <label class="block text-xs font-medium text-gray-600 mb-1.5">API token</label>
                <input wire:model="cloudflareToken" type="password" placeholder="cf_token_..."
                       class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:border-black transition-colors">
                <p class="mt-1.5 text-xs text-gray-400">
                    Requires <code class="font-mono bg-gray-100 px-1 rounded">Zone:DNS:Edit</code> + <code class="font-mono bg-gray-100 px-1 rounded">Zone:Zone:Read</code> permissions.
                    <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank" class="text-black underline">Create token →</a>
                </p>
            </div>
        </div>

        {{-- Save --}}
        <div>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60">
                <svg wire:loading wire:target="save" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span wire:loading wire:target="save">Saving…</span>
                <span wire:loading.remove wire:target="save">Save settings</span>
            </button>
        </div>

    </form>
</div>
