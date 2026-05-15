<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Template Editor' }} — Postara</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=jetbrains-mono:500" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/template-editor.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased overflow-hidden">

<div class="flex flex-col h-screen">
    {{ $slot }}
</div>

@livewireScripts
</body>
</html>
