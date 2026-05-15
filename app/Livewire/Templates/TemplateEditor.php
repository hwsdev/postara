<?php

namespace App\Livewire\Templates;

use App\Models\Template;
use Illuminate\View\View;
use Livewire\Component;

class TemplateEditor extends Component
{
    public ?int $templateId = null;

    public string $name = '';
    public string $subject = '';
    public string $html = '';
    public string $type = 'transactional';

    public function mount(?int $templateId = null): void
    {
        if ($templateId) {
            $template = Template::where('workspace_id', session('current_workspace_id'))
                ->findOrFail($templateId);

            $this->templateId = $template->id;
            $this->name       = $template->name;
            $this->subject    = $template->subject ?? '';
            $this->html       = $template->html ?? '';
            $this->type       = $template->type ?? 'transactional';
        } else {
            // Default starter HTML
            $this->html = $this->starterHtml();
        }
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

        if ($this->templateId) {
            Template::where('workspace_id', $workspaceId)
                ->findOrFail($this->templateId)
                ->update([
                    'name'    => $this->name,
                    'subject' => $this->subject,
                    'html'    => $this->html,
                    'type'    => $this->type,
                ]);
        } else {
            $template = Template::create([
                'workspace_id' => $workspaceId,
                'name'         => $this->name,
                'subject'      => $this->subject,
                'html'         => $this->html,
                'type'         => $this->type,
            ]);

            $this->templateId = $template->id;
        }

        $this->dispatch('template-saved');
    }

    private function starterHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $subject }}</title>
  <style>
    body { margin: 0; padding: 0; background: #f6f6f6; font-family: Inter, -apple-system, sans-serif; }
    .wrapper { max-width: 560px; margin: 32px auto; background: #ffffff; border: 1px solid #eeeeee; }
    .header { background: #0A0A0A; padding: 24px 32px; }
    .header h1 { margin: 0; color: #ffffff; font-size: 18px; font-weight: 700; }
    .body { padding: 32px; }
    .body h2 { margin: 0 0 12px; font-size: 20px; font-weight: 700; color: #0A0A0A; }
    .body p { margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #454545; }
    .btn { display: inline-block; background: #0A0A0A; color: #ffffff; text-decoration: none; padding: 12px 24px; font-size: 14px; font-weight: 600; border-radius: 4px; }
    .footer { padding: 20px 32px; border-top: 1px solid #eeeeee; }
    .footer p { margin: 0; font-size: 12px; color: #c9c9c9; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>{{ $appName }}</h1>
    </div>
    <div class="body">
      <h2>Hello, {{ $name }}!</h2>
      <p>This is your email content. Edit this template to match your needs.</p>
      <p>
        <a href="{{ $actionUrl }}" class="btn">{{ $actionLabel }}</a>
      </p>
      <p>If you have any questions, reply to this email.</p>
    </div>
    <div class="footer">
      <p>You received this email because you signed up for {{ $appName }}. <a href="{{ $unsubscribeUrl }}" style="color: #757575;">Unsubscribe</a></p>
    </div>
  </div>
</body>
</html>
HTML;
    }

    public function render(): View
    {
        return view('livewire.templates.template-editor');
    }
}
