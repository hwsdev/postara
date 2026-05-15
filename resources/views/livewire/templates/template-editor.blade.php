<div>
    {{-- Expose initial content for GrapesJS init script --}}
    <script>
        window.__templateEditorData = {
            html: @json($html),
            css:  @json($css),
        };
    </script>

    {{-- Top bar --}}
    <div class="flex items-center gap-4 px-5 py-3 bg-[#0A0A0A] border-b border-white/10 flex-shrink-0">

        {{-- Back --}}
        <a href="/templates"
           class="flex items-center gap-1.5 text-sm text-white/50 hover:text-white transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Templates
        </a>

        <div class="flex-1 flex items-center gap-3 min-w-0">
            {{-- Name --}}
            <input wire:model="name" type="text" placeholder="Template name…"
                   class="flex-1 min-w-0 px-3.5 py-2 bg-white/10 border rounded-lg text-sm font-semibold text-white placeholder-white/30 focus:outline-none transition-colors
                          {{ $errors->has('name') ? 'border-red-400' : 'border-white/20 focus:border-white/60' }}">

            {{-- Subject --}}
            <input wire:model="subject" type="text" placeholder="Default subject (optional)"
                   class="flex-1 min-w-0 px-3.5 py-2 bg-white/10 border border-white/20 rounded-lg text-sm text-white placeholder-white/30 focus:outline-none focus:border-white/60 transition-colors">

            {{-- Type toggle --}}
            <div class="flex gap-1 flex-shrink-0">
                @foreach (['transactional' => 'Transactional', 'campaign' => 'Campaign'] as $val => $lbl)
                    <button type="button" wire:click="$set('type', '{{ $val }}')"
                            @class([
                                'px-3 py-2 rounded-lg border text-xs font-semibold transition-all',
                                'border-white bg-white text-black' => $type === $val,
                                'border-white/20 text-white/50 hover:border-white/50 hover:text-white' => $type !== $val,
                            ])>
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Save button --}}
        <button type="button" id="gjs-save-btn"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex items-center gap-2 bg-white text-black text-sm font-semibold px-5 py-2 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60 flex-shrink-0">
            <svg wire:loading wire:target="save" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span wire:loading wire:target="save">Saving…</span>
            <span wire:loading.remove wire:target="save">
                {{ $templateId ? 'Save changes' : 'Create template' }}
            </span>
        </button>
    </div>

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="px-5 py-2 bg-red-900/50 border-b border-red-700 text-red-300 text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Saved flash --}}
    @if ($saved)
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition
            class="px-5 py-2 flex items-center gap-2 bg-emerald-900/50 border-b border-emerald-700 text-emerald-300 text-sm"
        >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Template saved.
        </div>
    @endif

    {{-- GrapesJS container — fills remaining height --}}
    <div id="gjs" class="flex-1" style="height: calc(100vh - 57px);"></div>
</div>
