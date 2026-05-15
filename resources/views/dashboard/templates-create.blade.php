<x-layouts.editor title="New Template">

    {{-- Livewire component handles all state & save logic --}}
    <livewire:templates.template-editor />

    {{-- GrapesJS init — inline here (NOT via @push) so it renders in the page --}}
    <script>
        document.addEventListener('livewire:initialized', function () {
            // Find the Livewire component instance
            const componentEl = document.querySelector('[wire\\:id]');
            if (!componentEl) return;
            const componentId = componentEl.getAttribute('wire:id');

            const data = window.__templateEditorData || {};

            const editor = window.initTemplateEditor({
                containerId: 'gjs',
                initialHtml: data.html || '',
                initialCss:  data.css  || '',
                onSave(html, css) {
                    Livewire.find(componentId).saveFromEditor(html, css);
                },
            });

            // Wire the top-bar save button to GrapesJS save command
            const saveBtn = document.getElementById('gjs-save-btn');
            if (saveBtn) {
                saveBtn.addEventListener('click', function () {
                    editor.runCommand('save-template');
                });
            }
        });
    </script>

</x-layouts.editor>
