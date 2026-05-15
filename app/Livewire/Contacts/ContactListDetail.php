<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use App\Models\ContactList;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ContactListDetail extends Component
{
    use WithPagination;

    public int $listId;
    public string $search = '';
    public bool $showAddPanel = false;
    public string $addSearch = '';
    public array $selectedContactIds = [];

    public function mount(int $listId): void
    {
        $this->listId = $listId;
        // Verify ownership
        ContactList::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($listId);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAddSearch(): void
    {
        $this->selectedContactIds = [];
    }

    public function addSelected(): void
    {
        if (empty($this->selectedContactIds)) {
            return;
        }

        $list = ContactList::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($this->listId);

        // Only attach contacts that belong to this workspace
        $validIds = Contact::where('workspace_id', session('current_workspace_id'))
            ->whereIn('id', $this->selectedContactIds)
            ->pluck('id')
            ->toArray();

        $list->contacts()->syncWithoutDetaching($validIds);

        $this->selectedContactIds = [];
        $this->addSearch = '';
        $this->showAddPanel = false;
    }

    public function removeContact(int $contactId): void
    {
        ContactList::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($this->listId)
            ->contacts()
            ->detach($contactId);
    }

    public function render(): View
    {
        $list = ContactList::where('workspace_id', session('current_workspace_id'))
            ->withCount('contacts')
            ->findOrFail($this->listId);

        // Members already in this list
        $members = $list->contacts()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('contacts.email', 'like', "%{$this->search}%")
                    ->orWhere('contacts.name', 'like', "%{$this->search}%");
            }))
            ->paginate(50);

        // Contacts NOT yet in this list (for the add panel)
        $addCandidates = collect();
        if ($this->showAddPanel) {
            $existingIds = $list->contacts()->pluck('contacts.id');

            $addCandidates = Contact::where('workspace_id', session('current_workspace_id'))
                ->whereNotIn('id', $existingIds)
                ->when($this->addSearch, fn ($q) => $q->where(function ($q) {
                    $q->where('email', 'like', "%{$this->addSearch}%")
                        ->orWhere('name', 'like', "%{$this->addSearch}%");
                }))
                ->limit(50)
                ->get();
        }

        return view('livewire.contacts.contact-list-detail', compact('list', 'members', 'addCandidates'));
    }
}
