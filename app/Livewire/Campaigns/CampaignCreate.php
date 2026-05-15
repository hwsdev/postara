<?php

namespace App\Livewire\Campaigns;

use App\Models\Campaign;
use App\Models\ContactList;
use App\Models\Template;
use Illuminate\View\View;
use Livewire\Component;

class CampaignCreate extends Component
{
    public string $name = '';
    public string $subject = '';
    public string $previewText = '';
    public string $fromName = '';
    public string $fromEmail = '';
    public ?int $templateId = null;
    public ?int $contactListId = null;
    public string $scheduledAt = '';
    public bool $scheduleForLater = false;

    protected function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:255'],
            'subject'       => ['required', 'string', 'max:998'],
            'previewText'   => ['sometimes', 'string', 'max:255'],
            'fromName'      => ['required', 'string', 'max:255'],
            'fromEmail'     => ['required', 'email'],
            'templateId'    => ['required', 'exists:templates,id'],
            'contactListId' => ['required', 'exists:contact_lists,id'],
            'scheduledAt'   => ['required_if:scheduleForLater,true', 'nullable', 'date', 'after:now'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $workspaceId = session('current_workspace_id');

        $campaign = Campaign::create([
            'workspace_id'   => $workspaceId,
            'name'           => $this->name,
            'subject'        => $this->subject,
            'preview_text'   => $this->previewText,
            'from_name'      => $this->fromName,
            'from_email'     => $this->fromEmail,
            'template_id'    => $this->templateId,
            'contact_list_id' => $this->contactListId,
            'status'         => $this->scheduleForLater ? 'scheduled' : 'draft',
            'scheduled_at'   => $this->scheduleForLater ? $this->scheduledAt : null,
        ]);

        $this->redirect(route('campaigns.index'));
    }

    public function render(): View
    {
        $workspaceId = session('current_workspace_id');

        return view('livewire.campaigns.campaign-create', [
            'templates'    => Template::where('workspace_id', $workspaceId)->get(),
            'contactLists' => ContactList::where('workspace_id', $workspaceId)->get(),
        ]);
    }
}
