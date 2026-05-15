<div>
    {{-- Top bar --}}
    <div class="flex items-center gap-4 mb-4">

        {{-- Back --}}
        <a href="/templates"
           class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-black transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Templates
        </a>

        <div class="flex-1 flex items-center gap-3 min-w-0">
            {{-- Name --}}
            <input wire:model="name" type="text" placeholder="Template name…"
                   class="flex-1 min-w-0 px-3.5 py-2 border rounded-lg text-sm font-semibold focus:outline-none transition-colors
                          {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">

            {{-- Subject --}}
            <input wire:model="subject" type="text" placeholder="Default subject (optional)"
                   class="flex-1 min-w-0 px-3.5 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-black transition-colors">

            {{-- Type toggle --}}
            <div class="flex gap-1 flex-shrink-0">
                @foreach (['transactional' => 'Transactional', 'campaign' => 'Campaign'] as $val => $lbl)
                    <button type="button" wire:click="$set('type', '{{ $val }}')"
                            @class([
                                'px-3 py-2 rounded-lg border text-xs font-semibold transition-all',
                                'border-black bg-black text-white' => $type === $val,
                                'border-gray-200 text-gray-500 hover:border-gray-400' => $type !== $val,
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
                class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-5 py-2 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60 flex-shrink-0">
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
        <div class="mb-3 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
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
            class="mb-3 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-lg"
        >
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Template saved.
        </div>
    @endif

    {{-- GrapesJS container --}}
    <div id="gjs" class="rounded-xl overflow-hidden border border-gray-200 shadow-sm"
         style="height: calc(100vh - 200px); min-height: 600px;">
    </div>

    @push('scripts')
    @vite('resources/js/template-editor.js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const initialHtml = @json($html);
            const initialCss  = @json($css);

            const editor = window.initTemplateEditor({
                containerId: 'gjs',
                initialHtml,
                initialCss,
                onSave(html, css) {
                    // Push to Livewire then trigger save
                    @this.set('html', html);
                    @this.set('css', css);
                    @this.call('save');
                },
            });

            // Wire the top-bar save button to GrapesJS save command
            document.getElementById('gjs-save-btn').addEventListener('click', function () {
                editor.runCommand('save-template');
            });
        });
    </script>
    @endpush
</div>
