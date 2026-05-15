<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Template;

class TemplateController extends Controller
{
    public function create()
    {
        return view('dashboard.templates-create');
    }

    public function edit(int $id)
    {
        $template = Template::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($id);

        return view('dashboard.templates-edit', [
            'templateId' => $template->id,
        ]);
    }
}
