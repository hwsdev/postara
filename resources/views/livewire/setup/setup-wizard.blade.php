<div class="min-h-screen flex flex-col">

    {{-- ── Top bar ──────────────────────────────────────────────────── --}}
    <header class="bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-6 h-6 bg-black rounded flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2 2h12v2H2V2zm0 4h8v2H2V6zm0 4h10v2H2v-2z"/>
                </svg>
            </div>
            <span class="font-bold text-base tracking-tight">Postara</span>
            <span class="text-gray-300 text-sm">·</span>
            <span class="text-sm text-gray-400">Initial Setup</span>
        </div>
        <span class="text-xs text-gray-400 font-mono">Step {{ $step }} of {{ $totalSteps }}</span>
    </header>

    {{-- ── Progress bar ─────────────────────────────────────────────── --}}
    <div class="h-0.5 bg-gray-100">
        <div class="h-full bg-black transition-all duration-500 ease-out"
             style="width: {{ round(($step / $totalSteps) * 100) }}%"></div>
    </div>

    {{-- ── Content ──────────────────────────────────────────────────── --}}
    <div class="flex-1 flex items-start justify-center px-4 py-12">
        <div class="w-full max-w-xl">

            {{-- Step indicators --}}
            <div class="flex items-center gap-0 mb-10">
                @foreach ([1 => 'App', 2 => 'Mail', 3 => 'Account'] as $n => $label)
                    <div class="flex items-center {{ $n < $totalSteps ? 'flex-1' : '' }}">
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <div @class([
                                'w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all',
                                'bg-black text-white' => $step === $n,
                                'bg-emerald-500 text-white' => $step > $n,
                                'bg-gray-100 text-gray-400' => $step < $n,
                            ])>
                                @if ($step > $n)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @else
                                    {{ $n }}
                                @endif
                            </div>
                            <span @class([
                                'text-sm font-medium',
                                'text-gray-900' => $step === $n,
                                'text-emerald-600' => $step > $n,
                                'text-gray-400' => $step < $n,
                            ])>{{ $label }}</span>
                        </div>
                        @if ($n < $totalSteps)
                            <div class="flex-1 h-px mx-3 {{ $step > $n ? 'bg-emerald-300' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- ── Step 1: App ──────────────────────────────────────── --}}
            @if ($step === 1)
                <div>
                    <h2 class="text-2xl font-bold tracking-tight mb-1">Configure your app</h2>
                    <p class="text-gray-400 text-sm mb-8">Basic settings for your Postara instance.</p>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">App name</label>
                            <input wire:model="appName" type="text" placeholder="Postara"
                                   class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                          {{ $errors->has('appName') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                            @error('appName') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            <p class="mt-1.5 text-xs text-gray-400">Shown in the UI and email headers.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">App URL</label>
                            <input wire:model="appUrl" type="url" placeholder="https://mail.yourdomain.com"
                                   class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                          {{ $errors->has('appUrl') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                            @error('appUrl') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            <p class="mt-1.5 text-xs text-gray-400">Used for tracking links and unsubscribe URLs. Include <code class="font-mono bg-gray-100 px-1 rounded">https://</code>.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Timezone</label>
                            <select wire:model="appTimezone"
                                    class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:border-black transition-colors">
                                @foreach (timezone_identifiers_list() as $tz)
                                    <option value="{{ $tz }}" {{ $appTimezone === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                            @error('appTimezone') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Step 2: Mail ─────────────────────────────────────── --}}
            @if ($step === 2)
                <div>
                    <h2 class="text-2xl font-bold tracking-tight mb-1">Configure mail sending</h2>
                    <p class="text-gray-400 text-sm mb-8">Choose how Postara sends emails. You can change this later.</p>

                    <div class="space-y-5">

                        {{-- Mode selector --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mail transport</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ([
                                    'postfix'      => ['label' => 'Self-hosted',     'sub' => 'Postfix on your VPS'],
                                    'smtp'         => ['label' => 'SMTP relay',      'sub' => 'Brevo, Mailgun, SES…'],
                                    'mailchannels' => ['label' => 'MailChannels',    'sub' => 'HTTP API, free 100/day'],
                                    'log'          => ['label' => 'Log only',        'sub' => 'Dev / testing'],
                                ] as $val => $meta)
                                    <button type="button" wire:click="$set('mailMode', '{{ $val }}')"
                                            @class([
                                                'px-3 py-3 rounded-xl border text-left transition-all',
                                                'border-black bg-black text-white' => $mailMode === $val,
                                                'border-gray-200 hover:border-gray-400 text-gray-700' => $mailMode !== $val,
                                            ])>
                                        <p class="text-sm font-semibold">{{ $meta['label'] }}</p>
                                        <p @class([
                                            'text-xs mt-0.5',
                                            'text-white/60' => $mailMode === $val,
                                            'text-gray-400' => $mailMode !== $val,
                                        ])>{{ $meta['sub'] }}</p>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Postfix (self-hosted) --}}
                        @if ($mailMode === 'postfix')
                            <div class="rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800 space-y-1">
                                <p class="font-semibold">Self-hosted Postfix</p>
                                <p class="text-xs text-blue-600 leading-relaxed">
                                    Postara will connect to Postfix running on your server directly — no external provider needed.
                                    Make sure port 25 outbound is open on your VPS and your IP has a valid PTR/rDNS record.
                                </p>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Postfix host</label>
                                    <input wire:model="postfixHost" type="text"
                                           placeholder="127.0.0.1 or postfix (Docker service name)"
                                           class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                                  {{ $errors->has('postfixHost') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                    @error('postfixHost') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    <p class="mt-1.5 text-xs text-gray-400">Use <code class="font-mono bg-gray-100 px-1 rounded">127.0.0.1</code> if Postfix is on the same host, or the Docker service name (e.g. <code class="font-mono bg-gray-100 px-1 rounded">postfix</code>) if containerised.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Port</label>
                                    <input wire:model="postfixPort" type="number"
                                           placeholder="25"
                                           class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                                  {{ $errors->has('postfixPort') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                    @error('postfixPort') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                    <p class="mt-1.5 text-xs text-gray-400">Usually <code class="font-mono bg-gray-100 px-1 rounded">25</code> (no auth) or <code class="font-mono bg-gray-100 px-1 rounded">587</code> (submission).</p>
                                </div>
                            </div>

                            <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800 space-y-1">
                                <p class="font-semibold">Checklist before going live</p>
                                <ul class="list-disc list-inside space-y-0.5 text-amber-700">
                                    <li>VPS provider allows outbound port 25 (many block it by default)</li>
                                    <li>PTR / rDNS record set for your server IP</li>
                                    <li>SPF, DKIM, DMARC configured on your sending domain</li>
                                    <li>IP not on any blocklist (check mxtoolbox.com)</li>
                                </ul>
                            </div>
                        @endif

                        {{-- MailChannels --}}
                        @if ($mailMode === 'mailchannels')
                            <div class="rounded-xl bg-orange-50 border border-orange-200 px-4 py-3 text-sm text-orange-800 space-y-1">
                                <p class="font-semibold flex items-center gap-2">
                                    MailChannels Email API
                                    <span class="text-xs font-normal bg-orange-200 text-orange-800 px-2 py-0.5 rounded-full">Free 100/day</span>
                                </p>
                                <p class="text-xs text-orange-700 leading-relaxed">
                                    Sends via MailChannels' HTTP API — no SMTP needed. Works great with Cloudflare DNS.
                                    Free plan includes 100 emails/day. Requires a Domain Lockdown TXT record on your sending domain.
                                </p>
                                <a href="https://www.mailchannels.com" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-orange-600 hover:text-orange-900 underline mt-1">
                                    Get API key at mailchannels.com →
                                </a>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">MailChannels API key</label>
                                <input wire:model="mailChannelsApiKey" type="password"
                                       placeholder="mc_api_key_..."
                                       class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                              {{ $errors->has('mailChannelsApiKey') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                @error('mailChannelsApiKey') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="rounded-xl bg-gray-50 border border-gray-200 px-4 py-3 text-xs text-gray-600 space-y-1.5">
                                <p class="font-semibold text-gray-700">Required DNS record on your sending domain</p>
                                <p class="text-gray-500">Add this TXT record to authorize MailChannels to send on your behalf:</p>
                                <div class="flex gap-3 items-start">
                                    <span class="text-gray-400 w-8 flex-shrink-0 pt-0.5">Host</span>
                                    <code class="font-mono bg-white border border-gray-200 px-2 py-1 rounded flex-1">_mailchannels.yourdomain.com</code>
                                </div>
                                <div class="flex gap-3 items-start">
                                    <span class="text-gray-400 w-8 flex-shrink-0 pt-0.5">Value</span>
                                    <code class="font-mono bg-white border border-gray-200 px-2 py-1 rounded flex-1">v=mc1 auth=your_account_id</code>
                                </div>
                                <p class="text-gray-400 pt-1">Your account ID is shown in the MailChannels dashboard after signup.</p>
                            </div>
                        @endif

                        {{-- SMTP relay --}}
                        @if ($mailMode === 'smtp')
                            <div class="grid grid-cols-3 gap-3">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">SMTP host</label>
                                    <input wire:model="mailHost" type="text" placeholder="smtp.brevo.com"
                                           class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                                  {{ $errors->has('mailHost') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                    @error('mailHost') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Port</label>
                                    <input wire:model="mailPort" type="number" placeholder="587"
                                           class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                                  {{ $errors->has('mailPort') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                                    <input wire:model="mailUsername" type="text" placeholder="Optional"
                                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-black transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password / API key</label>
                                    <input wire:model="mailPassword" type="password" placeholder="Optional"
                                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-black transition-colors">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Encryption</label>
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

                            {{-- Quick-fill presets --}}
                            <div>
                                <p class="text-xs text-gray-400 mb-2">Quick fill:</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ([
                                        'Brevo'   => ['smtp-relay.brevo.com', '587', 'tls'],
                                        'Mailgun' => ['smtp.mailgun.org', '587', 'tls'],
                                        'SES'     => ['email-smtp.us-east-1.amazonaws.com', '587', 'tls'],
                                        'Postmark'=> ['smtp.postmarkapp.com', '587', 'tls'],
                                        'Resend'  => ['smtp.resend.com', '587', 'tls'],
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

                        {{-- Log mode --}}
                        @if ($mailMode === 'log')
                            <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                                <p class="font-semibold">Development / testing mode</p>
                                <p class="text-xs mt-1 text-amber-700 leading-relaxed">
                                    Emails will be written to <code class="font-mono bg-amber-100 px-1 rounded">storage/logs/laravel.log</code> instead of sent.
                                    No emails will actually be delivered. Switch to Self-hosted or SMTP relay for production.
                                </p>
                            </div>
                        @endif

                        {{-- Test connection (not for log mode) --}}
                        @if ($mailMode !== 'log')
                            <div class="rounded-xl border border-gray-200 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-medium">Test connection</p>
                                        <p class="text-xs text-gray-400 mt-0.5">Verify the connection works before continuing.</p>
                                    </div>
                                    <button type="button" wire:click="testMailConnection"
                                            wire:loading.attr="disabled"
                                            wire:target="testMailConnection"
                                            class="flex-shrink-0 border border-gray-200 text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-60">
                                        <span wire:loading wire:target="testMailConnection">Testing…</span>
                                        <span wire:loading.remove wire:target="testMailConnection">Test connection</span>
                                    </button>
                                </div>

                                @if ($mailTestResult)
                                    @if ($mailTestResult === 'success')
                                        <div class="mt-3 flex items-center gap-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Connection successful.
                                        </div>
                                    @else
                                        <div class="mt-3 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                            <p class="font-medium">Connection failed</p>
                                            <p class="text-xs mt-0.5 font-mono break-all opacity-80">{{ str_replace('error:', '', $mailTestResult) }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif

                        {{-- From address (always shown) --}}
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">From name</label>
                                <input wire:model="mailFromName" type="text" placeholder="Postara"
                                       class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                              {{ $errors->has('mailFromName') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                @error('mailFromName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">From address</label>
                                <input wire:model="mailFromAddress" type="email" placeholder="noreply@yourdomain.com"
                                       class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono focus:outline-none transition-colors
                                              {{ $errors->has('mailFromAddress') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                @error('mailFromAddress') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Cloudflare (optional, collapsible) --}}
                        <details class="group">
                            <summary class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors list-none select-none">
                                <svg class="w-4 h-4 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                                Cloudflare DNS auto-provision <span class="text-gray-400 font-normal">(optional)</span>
                            </summary>
                            <div class="mt-3 pl-6 space-y-2">
                                <p class="text-xs text-gray-400 leading-relaxed">
                                    Provide a Cloudflare API token to automatically add SPF, DKIM, and DMARC records when you add a sending domain.
                                    Requires <code class="font-mono bg-gray-100 px-1 rounded">Zone:DNS:Edit</code> + <code class="font-mono bg-gray-100 px-1 rounded">Zone:Zone:Read</code> permissions.
                                    <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank" class="text-black underline">Create token →</a>
                                </p>
                                <input wire:model="cloudflareToken" type="password" placeholder="cf_token_..."
                                       class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm font-mono focus:outline-none focus:border-black transition-colors">
                            </div>
                        </details>

                    </div>
                </div>
            @endif

            {{-- ── Step 3: Account ──────────────────────────────────── --}}
            @if ($step === 3)
                <div>
                    <h2 class="text-2xl font-bold tracking-tight mb-1">Create your account</h2>
                    <p class="text-gray-400 text-sm mb-8">This will be the owner account for this Postara instance.</p>

                    <div class="space-y-5">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                                <input wire:model="userName" type="text" placeholder="Your name"
                                       class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                              {{ $errors->has('userName') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                @error('userName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Workspace name</label>
                                <input wire:model="workspaceName" type="text" placeholder="Acme Inc"
                                       class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                              {{ $errors->has('workspaceName') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                @error('workspaceName') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                            <input wire:model="userEmail" type="email" placeholder="you@yourdomain.com"
                                   class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                          {{ $errors->has('userEmail') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                            @error('userEmail') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                                <input wire:model="userPassword" type="password"
                                       class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                              {{ $errors->has('userPassword') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                @error('userPassword') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                                <input wire:model="userPasswordConfirmation" type="password"
                                       class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                              {{ $errors->has('userPasswordConfirmation') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                                @error('userPasswordConfirmation') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Summary --}}
                        <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Setup summary</p>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">App name</span>
                                <span class="font-medium">{{ $appName ?: '—' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">App URL</span>
                                <span class="font-mono text-xs text-gray-700">{{ $appUrl ?: '—' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Mail transport</span>
                                <span class="font-medium text-xs">
                                    @if ($mailMode === 'postfix') Self-hosted Postfix ({{ $postfixHost }}:{{ $postfixPort }})
                                    @elseif ($mailMode === 'smtp') SMTP relay ({{ $mailHost ?: '—' }}:{{ $mailPort }})
                                    @elseif ($mailMode === 'mailchannels') MailChannels API
                                    @else Log only (dev)
                                    @endif
                                </span>
                            </div>                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">From address</span>
                                <span class="font-mono text-xs text-gray-700">{{ $mailFromAddress ?: '—' }}</span>
                            </div>
                            @if ($cloudflareToken)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Cloudflare</span>
                                    <span class="text-emerald-600 text-xs font-medium">Configured</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- ── Navigation ───────────────────────────────────────── --}}
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-100">
                <button type="button"
                        wire:click="prevStep"
                        @class([
                            'flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-black transition-colors',
                            'invisible' => $step === 1,
                        ])>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </button>

                @if ($step < $totalSteps)
                    <button type="button"
                            wire:click="nextStep"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-2 bg-black text-white font-semibold px-6 py-2.5 rounded-lg text-sm hover:bg-gray-900 active:scale-[0.99] transition-all disabled:opacity-60">
                        <span wire:loading wire:target="nextStep">Validating…</span>
                        <span wire:loading.remove wire:target="nextStep">Continue</span>
                        <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                @else
                    <button type="button"
                            wire:click="finish"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-2 bg-black text-white font-semibold px-6 py-2.5 rounded-lg text-sm hover:bg-gray-900 active:scale-[0.99] transition-all disabled:opacity-60">
                        <span wire:loading wire:target="finish">Setting up…</span>
                        <span wire:loading.remove wire:target="finish">Finish setup</span>
                        <svg wire:loading.remove wire:target="finish" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                @endif
            </div>

        </div>
    </div>

    {{-- ── Footer ───────────────────────────────────────────────────── --}}
    <footer class="text-center py-6 text-xs text-gray-300">
        Postara · AGPL-3.0 · <a href="https://github.com/hwsdev/postara" class="hover:text-gray-500 transition-colors">GitHub</a>
    </footer>

</div>
