<div
    x-data="{
        preview: false,
        updatePreview() {
            const iframe = document.getElementById('template-preview');
            if (iframe) {
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                doc.write($wire.html);
                doc.close();
            }
        }
    }"
    x-init="$watch('preview', val => { if (val) $nextTick(() => updatePreview()) })"
>
    {{-- Saved flash --}}
    <div
        x-data="{ show: false }"
        x-on:template-saved.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="mb-4 flex items-center gap-2.5 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl"
    >
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Template saved.
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Left: editor --}}
        <div class="space-y-4">

            {{-- Meta --}}
            <div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm space-y-4">
                <h3 class="font-semibold text-sm border-b border-gray-100 pb-3">Template details</h3>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Template name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. Welcome email"
                           class="w-full px-3.5 py-2.5 border rounded-lg text-sm focus:outline-none transition-colors
                                  {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-200 focus:border-black' }}">
                    @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Default subject <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input wire:model="subject" type="text" placeholder="Your email subject"
                           class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-black transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-2">Type</label>
                    <div class="flex gap-2">
                        @foreach (['transactional' => 'Transactional', 'campaign' => 'Campaign'] as $val => $lbl)
                            <button type="button" wire:click="$set('type', '{{ $val }}')"
                                    @class([
                                        'px-3 py-2 rounded-lg border text-sm font-medium transition-all',
                                        'border-black bg-black text-white' => $type === $val,
                                        'border-gray-200 text-gray-600 hover:border-gray-400' => $type !== $val,
                                    ])>
                                {{ $lbl }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- HTML editor --}}
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-sm">HTML</h3>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">Variables: <code class="font-mono bg-gray-100 px-1 rounded">{{ '{{ $name }}' }}</code></span>
                        <button type="button" x-on:click="preview = !preview"
                                class="text-xs font-medium text-gray-500 hover:text-black px-3 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                            <span x-text="preview ? 'Hide preview' : 'Preview'"></span>
                        </button>
                    </div>
                </div>
                <textarea
                    wire:model.lazy="html"
                    x-on:change="if (preview) updatePreview()"
                    rows="24"
                    placeholder="Paste or write your HTML email here..."
                    class="w-full px-4 py-3 text-xs font-mono text-gray-800 bg-gray-50 focus:outline-none focus:bg-white transition-colors resize-none border-0"
                    spellcheck="false"
                ></textarea>
                @error('html') <p class="px-4 pb-3 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                <button type="button" wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="inline-flex items-center gap-2 bg-black text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:opacity-90 transition-opacity disabled:opacity-60">
                    <svg wire:loading wire:target="save" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span wire:loading wire:target="save">Saving…</span>
                    <span wire:loading.remove wire:target="save">{{ $templateId ? 'Save changes' : 'Create template' }}</span>
                </button>
                <a href="{{ route('templates.index') }}"
                   class="border border-gray-200 text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </div>

        {{-- Right: preview --}}
        <div x-show="preview" x-transition class="hidden lg:block">
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm sticky top-6">
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-sm">Preview</h3>
                    <span class="text-xs text-gray-400">Rendered HTML</span>
                </div>
                <iframe
                    id="template-preview"
                    class="w-full border-0"
                    style="height: 600px;"
                    sandbox="allow-same-origin"
                ></iframe>
            </div>
        </div>

        {{-- Mobile preview toggle hint --}}
        <div x-show="!preview" class="lg:hidden text-center py-4">
            <button type="button" x-on:click="preview = true"
                    class="text-sm text-gray-400 hover:text-black transition-colors">
                Show preview →
            </button>
        </div>

    </div>
</div>
