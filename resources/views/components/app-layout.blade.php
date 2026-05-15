@props(['title' => null, 'actions' => null])

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
<body class="bg-gray-50 text-gray-900 antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- ── Sidebar ──────────────────────────────────────────────────── --}}
    <aside class="w-56 bg-[#0A0A0A] text-white flex flex-col flex-shrink-0">

        {{-- Logo --}}
        <div class="px-5 py-4 flex items-center gap-2.5">
            <div class="w-6 h-6 bg-white rounded flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 text-black" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2 2h12v2H2V2zm0 4h8v2H2V6zm0 4h10v2H2v-2z"/>
                </svg>
            </div>
            <a href="/" class="text-white font-bold text-base tracking-tight">Postara</a>
        </div>

        {{-- Workspace badge --}}
        @php $ws = \App\Models\Workspace::find(session('current_workspace_id')); @endphp
        @if ($ws)
            <div class="mx-3 mb-3 px-3 py-2 bg-white/5 rounded-lg">
                <p class="text-xs text-white/40 uppercase tracking-widest font-semibold mb-0.5">Workspace</p>
                <p class="text-sm text-white/80 font-medium truncate">{{ $ws->name }}</p>
            </div>
        @endif

        {{-- Nav --}}
        <nav class="flex-1 px-2 space-y-0.5 overflow-y-auto pb-2">
            @php
                $navItems = [
                    ['url' => '/',              'match' => '/',                'label' => 'Overview',    'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['url' => '/campaigns',     'match' => 'campaigns*',       'label' => 'Campaigns',   'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['url' => '/templates',     'match' => 'templates*',       'label' => 'Templates',   'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                    ['url' => '/contacts',      'match' => 'contacts*',        'label' => 'Contacts',    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['url' => '/domains',       'match' => 'domains*',         'label' => 'Domains',     'icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9'],
                    ['url' => '/api-keys',      'match' => 'api-keys*',        'label' => 'API Keys',    'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
                    ['url' => '/webhooks',      'match' => 'webhooks*',        'label' => 'Webhooks',    'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                    ['url' => '/suppressions',  'match' => 'suppressions*',    'label' => 'Suppression', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                    ['url' => '/settings',      'match' => 'settings*',        'label' => 'Settings',    'icon' => 'M12 15a3 3 0 100-6 3 3 0 000 6z M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z'],
                ];
            @endphp

            @foreach ($navItems as $item)
                @php $active = request()->is($item['match']); @endphp
                <a href="{{ $item['url'] }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-all
                          {{ $active ? 'bg-white text-black' : 'text-white/50 hover:text-white hover:bg-white/8' }}">
                    <svg class="w-4 h-4 flex-shrink-0 {{ $active ? 'text-black' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- User --}}
        <div class="px-2 py-3 border-t border-white/8">
            <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg">
                <div class="w-7 h-7 rounded-full bg-white/15 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-white/35 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs text-white/40 hover:text-white hover:bg-white/8 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main ─────────────────────────────────────────────────────── --}}
    <main class="flex-1 overflow-y-auto flex flex-col min-w-0">

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 px-7 py-4 flex items-center justify-between sticky top-0 z-10 flex-shrink-0">
            <h1 class="text-lg font-bold tracking-tight">{{ $title ?? 'Dashboard' }}</h1>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </header>

        {{-- Content --}}
        <div class="flex-1 px-7 py-6">
            @if (session('success'))
                <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>
</div>

@livewireScripts
</body>
</html>
