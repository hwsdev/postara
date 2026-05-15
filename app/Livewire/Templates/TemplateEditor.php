<?php

namespace App\Livewire\Templates;

use App\Models\Template;
use Illuminate\View\View;
use Livewire\Component;

class TemplateEditor extends Component
{
    public ?int $templateId = null;

    public string $name    = '';
    public string $subject = '';
    public string $html    = '';
    public string $css     = '';
    public string $type    = 'transactional';

    public bool $saved = false;

    public function mount(?int $templateId = null): void
    {
        if ($templateId) {
            $template = Template::where('workspace_id', session('current_workspace_id'))
                ->findOrFail($templateId);

            $this->templateId = $template->id;
            $this->name       = $template->name;
            $this->subject    = $template->subject ?? '';
            $this->html       = $template->html ?? '';
            $this->css        = $template->design_json['css'] ?? '';
            $this->type       = $template->type ?? 'transactional';
        }
    }

    /**
     * Called from JS via $wire.saveFromEditor(html, css)
     */
    public function saveFromEditor(string $html, string $css): void
    {
        $this->html = $html;
        $this->css  = $css;
        $this->save();
    }

    public function save(): void
    {
        $this->validate([
            'name'    => ['required', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:998'],
            'html'    => ['required', 'string'],
            'type'    => ['required', 'in:transactional,campaign'],
        ]);

        $workspaceId = session('current_workspace_id');

        $data = [
            'name'        => $this->name,
            'subject'     => $this->subject ?: null,
            'html'        => $this->html,
            'type'        => $this->type,
            'design_json' => ['css' => $this->css],
        ];

        if ($this->templateId) {
            Template::where('workspace_id', $workspaceId)
                ->findOrFail($this->templateId)
                ->update($data);
        } else {
            $template = Template::create(array_merge($data, [
                'workspace_id' => $workspaceId,
            ]));
            $this->templateId = $template->id;
        }

        $this->saved = true;
        $this->dispatch('template-saved');
    }

    public function render(): View
    {
        return view('livewire.templates.template-editor');
    }
}
