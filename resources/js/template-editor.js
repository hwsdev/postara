import grapesjs from 'grapesjs';
import newsletterPlugin from 'grapesjs-preset-newsletter';
import 'grapesjs/dist/css/grapes.min.css';

window.initTemplateEditor = function (options = {}) {
    const {
        containerId = 'gjs',
        initialHtml = '',
        onSave = null,
    } = options;

    const editor = grapesjs.init({
        container: `#${containerId}`,
        height: '100%',
        width: 'auto',
        storageManager: false,
        fromElement: false,

        plugins: [newsletterPlugin],
        pluginsOpts: {
            [newsletterPlugin]: {
                inlineCss: true,
                modalLabelImport: 'Paste HTML here',
                modalLabelExport: 'Copy HTML',
                importPlaceholder: '<!-- Paste your HTML email here -->',
                cellStyle: {
                    'font-size': '14px',
                    'font-weight': '300',
                    'vertical-align': 'top',
                    color: '#454545',
                    margin: 0,
                    padding: 0,
                },
            },
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

    // Add save command
    editor.Commands.add('save-db', {
        run(ed) {
            const html = ed.runCommand('gjs-get-inlined-html');
            const css  = ed.getCss();
            if (typeof onSave === 'function') {
                onSave(html, css);
            }
        },
    });

    // Add save button to existing panels
    editor.Panels.addButton('options', {
        id: 'save-db',
        className: 'fa fa-floppy-o',
        label: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>`,
        command: 'save-db',
        attributes: { title: 'Save template' },
    });

    return editor;
};
