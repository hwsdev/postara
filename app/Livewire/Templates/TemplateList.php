<?php

namespace App\Livewire\Templates;

use App\Models\Template;
use Illuminate\View\View;
use Livewire\Component;

class TemplateList extends Component
{
    public function delete(int $id): void
    {
        Template::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($id)
            ->delete();
    }

    public function render(): View
    {
        $templates = Template::where('workspace_id', session('current_workspace_id'))
            ->latest()
            ->get();

        return view('livewire.templates.template-list', compact('templates'));
    }
}
