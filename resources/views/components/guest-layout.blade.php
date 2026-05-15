@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' — ' : '' }}Postara</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=jetbrains-mono:500" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white antialiased">

<div class="min-h-screen grid lg:grid-cols-2">

    {{-- Left: branding panel --}}
    <div class="hidden lg:flex flex-col bg-black relative overflow-hidden">
        {{-- Subtle grid texture --}}
        <div class="absolute inset-0 opacity-[0.03]"
             style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>

        <div class="relative z-10 flex flex-col h-full p-12">
            {{-- Logo --}}
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 bg-white rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-black" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2 2h12v2H2V2zm0 4h8v2H2V6zm0 4h10v2H2v-2z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-tight">Postara</span>
            </div>

            {{-- Center content --}}
            <div class="flex-1 flex flex-col justify-center">
                <p class="text-white/20 text-xs font-mono uppercase tracking-widest mb-4">Open source ESP</p>
                <h2 class="text-white text-4xl font-bold leading-tight tracking-tight">
                    Email infrastructure<br>you actually own.
                </h2>
                <p class="text-white/40 text-base mt-4 leading-relaxed max-w-sm">
                    Transactional API + marketing campaigns, self-hosted on your own VPS.
                </p>

                {{-- Feature list --}}
                <div class="mt-10 space-y-3">
                    @foreach (['REST API compatible with Resend', 'Drag-drop campaign builder', 'DKIM signing + open/click tracking', 'One-click deploy via Coolify'] as $feat)
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-white/50 text-sm">{{ $feat }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center gap-4">
                <span class="text-white/20 text-xs font-mono">AGPL-3.0</span>
                <span class="text-white/10">·</span>
                <a href="https://github.com/hwsdev/postara" class="text-white/20 text-xs hover:text-white/40 transition-colors">GitHub</a>
            </div>
        </div>
    </div>

    {{-- Right: form panel --}}
    <div class="flex items-center justify-center px-6 py-12 bg-white">
        <div class="w-full max-w-[360px]">
            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-2 mb-10">
                <div class="w-7 h-7 bg-black rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M2 2h12v2H2V2zm0 4h8v2H2V6zm0 4h10v2H2v-2z"/>
                    </svg>
                </div>
                <span class="font-bold text-lg tracking-tight">Postara</span>
            </div>

            {{ $slot }}
        </div>
    </div>
</div>

@livewireScripts
</body>
</html>
