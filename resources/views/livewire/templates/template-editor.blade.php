<div style="display:flex; flex-direction:column; height:100vh; overflow:hidden;">

    {{-- Expose initial content for GrapesJS --}}
    <script>
        window.__templateEditorData = {
            html: @json($html),
        };
    </script>

    {{-- ── Top bar ──────────────────────────────────────────────── --}}
    <div style="flex-shrink:0;" class="flex items-center gap-3 px-4 py-2.5 bg-[#0A0A0A] border-b border-white/10">

        <a href="/templates"
           class="flex items-center gap-1.5 text-sm text-white/50 hover:text-white transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Templates
        </a>

        <div class="flex-1 flex items-center gap-2 min-w-0">
            <input wire:model="name" type="text" placeholder="Template name…"
                   class="w-48 px-3 py-1.5 bg-white/10 border rounded-lg text-sm font-semibold text-white placeholder-white/30 focus:outline-none border-white/20 focus:border-white/60 transition-colors">

            <input wire:model="subject" type="text" placeholder="Subject (optional)"
                   class="flex-1 min-w-0 px-3 py-1.5 bg-white/10 border border-white/20 rounded-lg text-sm text-white placeholder-white/30 focus:outline-none focus:border-white/60 transition-colors">

            @foreach (['transactional' => 'Transactional', 'campaign' => 'Campaign'] as $val => $lbl)
                <button type="button" wire:click="$set('type', '{{ $val }}')"
                        class="px-3 py-1.5 rounded-lg border text-xs font-semibold transition-all flex-shrink-0
                               {{ $type === $val ? 'border-white bg-white text-black' : 'border-white/20 text-white/50 hover:border-white/50 hover:text-white' }}">
                    {{ $lbl }}
                </button>
            @endforeach
        </div>

        <button type="button" id="gjs-save-btn"
                wire:loading.attr="disabled" wire:target="save"
                class="inline-flex items-center gap-2 bg-white text-black text-sm font-semibold px-4 py-1.5 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60 flex-shrink-0">
            <svg wire:loading wire:target="save" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span wire:loading wire:target="save">Saving…</span>
            <span wire:loading.remove wire:target="save">{{ $templateId ? 'Save' : 'Create' }}</span>
        </button>
    </div>

    {{-- Validation / saved flash --}}
    @if ($errors->has('name'))
        <div style="flex-shrink:0;" class="px-4 py-2 bg-red-900/60 text-red-300 text-xs border-b border-red-800">
            {{ $errors->first('name') }}
        </div>
    @endif

    @if ($saved)
        <div style="flex-shrink:0;"
             x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)"
             x-show="show" x-transition
             class="px-4 py-2 flex items-center gap-2 bg-emerald-900/60 text-emerald-300 text-xs border-b border-emerald-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Template saved.
        </div>
    @endif

    {{-- ── GrapesJS canvas — fills all remaining space ─────────── --}}
    <div id="gjs" style="flex:1; min-height:0;"></div>

</div>
