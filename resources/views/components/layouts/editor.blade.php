@props(['title' => 'Template Editor'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — Postara</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&family=jetbrains-mono:500" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/template-editor.js'])
    @livewireStyles
    <style>
        html, body { height: 100%; margin: 0; overflow: hidden; }

        /* Make Livewire root div fill height */
        body > div[wire\:id],
        [wire\:id] { height: 100%; display: flex; flex-direction: column; }

        /* GrapesJS canvas */
        .gjs-cv-canvas { background: #f6f6f6 !important; }
        .gjs-editor { background: #1a1a1a; }
        .gjs-pn-panels { background: #0A0A0A; }
        .gjs-pn-panel { background: #0A0A0A; padding: 4px 6px; }
        .gjs-pn-views-container { background: #111; border-left: 1px solid rgba(255,255,255,0.08); }
        .gjs-pn-views { background: #0A0A0A; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .gjs-pn-btn { color: rgba(255,255,255,0.5); border-radius: 4px; padding: 4px 6px; }
        .gjs-pn-btn:hover, .gjs-pn-btn.gjs-pn-active { color: #fff; background: rgba(255,255,255,0.1); }
        .gjs-block { background: #1a1a1a; border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; color: rgba(255,255,255,0.7); }
        .gjs-block:hover { background: #222; border-color: rgba(255,255,255,0.2); color: #fff; }
        .gjs-sm-sector-title, .gjs-trt-header { background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5); font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; }
        .gjs-field { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; color: #fff; }
        .gjs-field input, .gjs-field select { color: #fff; background: transparent; }
        .gjs-layer { background: transparent; border-bottom: 1px solid rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); font-size: 12px; }
        .gjs-layer.gjs-selected { background: rgba(255,255,255,0.08); color: #fff; }
        .gjs-mdl-dialog { background: #fff; border-radius: 12px; }
        .gjs-mdl-header { background: #0A0A0A; color: #fff; border-radius: 12px 12px 0 0; padding: 16px 20px; font-weight: 600; font-size: 14px; }
        .gjs-mdl-btn-close { color: rgba(255,255,255,0.5); }
        .gjs-toolbar { background: #0A0A0A; border-radius: 6px; }
        .gjs-toolbar-item { color: rgba(255,255,255,0.7); }
        .gjs-selected { outline: 2px solid #000 !important; outline-offset: -2px; }
    </style>
</head>
<body>
{{ $slot }}
@livewireScripts
</body>
</html>
