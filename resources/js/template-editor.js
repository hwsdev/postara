import grapesjs from 'grapesjs';
import newsletterPlugin from 'grapesjs-preset-newsletter';
import 'grapesjs/dist/css/grapes.min.css';

window.initTemplateEditor = function (options = {}) {
    const {
        containerId = 'gjs',
        initialHtml = '',
        initialCss = '',
        onSave = null,
    } = options;

    const editor = grapesjs.init({
        container: `#${containerId}`,
        height: '100%',
        width: 'auto',
        storageManager: false,
        undoManager: { trackChanges: true },

        plugins: [newsletterPlugin],
        pluginsOpts: {
            [newsletterPlugin]: {
                modalLabelImport: 'Paste HTML here',
                modalLabelExport: 'Copy HTML',
                codeViewerTheme: 'material',
                importPlaceholder: '<!-- Paste your HTML email here -->',
                inlineCss: true,
                cellStyle: {
                    'font-size': '14px',
                    'font-weight': 300,
                    'vertical-align': 'top',
                    color: 'rgb(111, 119, 125)',
                    margin: 0,
                    padding: 0,
                },
            },
        },

        // Uber-inspired panel layout
        panels: {
            defaults: [
                {
                    id: 'panel-top',
                    el: '.panel__top',
                    buttons: [],
                },
                {
                    id: 'basic-actions',
                    el: '.panel__basic-actions',
                    buttons: [
                        {
                            id: 'undo',
                            className: 'gjs-btn-prim',
                            label: '↩ Undo',
                            command: 'core:undo',
                        },
                        {
                            id: 'redo',
                            className: 'gjs-btn-prim',
                            label: '↪ Redo',
                            command: 'core:redo',
                        },
                        {
                            id: 'export',
                            className: 'gjs-btn-prim',
                            label: '&lt;/&gt; HTML',
                            command: 'export-template',
                        },
                        {
                            id: 'save-btn',
                            className: 'gjs-btn-prim gjs-btn-save',
                            label: '💾 Save',
                            command: 'save-template',
                        },
                    ],
                },
            ],
        },

        canvas: {
            styles: [
                'https://fonts.bunny.net/css?family=inter:400,500,600,700',
            ],
        },
    });

    // Load initial content
    if (initialHtml) {
        editor.setComponents(initialHtml);
    }
    if (initialCss) {
        editor.setStyle(initialCss);
    }

    // Save command — calls the onSave callback with HTML + CSS
    editor.Commands.add('save-template', {
        run(ed) {
            const html = ed.runCommand('gjs-get-inlined-html');
            const css  = ed.getCss();
            if (typeof onSave === 'function') {
                onSave(html, css);
            }
        },
    });

    // Style the editor to match Uber design system
    editor.on('load', () => {
        // Remove default GrapesJS branding
        const logo = document.querySelector('.gjs-logo-version');
        if (logo) logo.remove();
    });

    return editor;
};
