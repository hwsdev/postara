<?php

namespace App\Livewire\Contacts;

use App\Models\ContactList;
use Illuminate\View\View;
use Livewire\Component;

class ContactListManager extends Component
{
    public bool $showCreateForm = false;
    public string $newListName = '';
    public string $newListDescription = '';

    protected function rules(): array
    {
        return [
            'newListName'        => ['required', 'string', 'max:255'],
            'newListDescription' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function create(): void
    {
        $this->validate();

        ContactList::create([
            'workspace_id' => session('current_workspace_id'),
            'name'         => $this->newListName,
            'description'  => $this->newListDescription ?: null,
        ]);

        $this->reset(['newListName', 'newListDescription', 'showCreateForm']);
    }

    public function delete(int $listId): void
    {
        ContactList::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($listId)
            ->delete();
    }

    public function render(): View
    {
        $lists = ContactList::where('workspace_id', session('current_workspace_id'))
            ->withCount('contacts')
            ->latest()
            ->get();

        return view('livewire.contacts.contact-list-manager', compact('lists'));
    }
}
