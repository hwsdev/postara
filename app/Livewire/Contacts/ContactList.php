<?php

namespace App\Livewire\Contacts;

use App\Models\Contact;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ContactList extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';
    public $csvFile = null;
    public bool $showImport = false;
    public bool $showAddForm = false;
    public string $importStatus = '';

    // Manual add form
    public string $newEmail = '';
    public string $newName = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function addContact(): void
    {
        $this->validate([
            'newEmail' => ['required', 'email', 'max:255'],
            'newName'  => ['nullable', 'string', 'max:255'],
        ]);

        $workspaceId = session('current_workspace_id');

        Contact::updateOrCreate(
            ['workspace_id' => $workspaceId, 'email' => strtolower(trim($this->newEmail))],
            ['name' => $this->newName ?: null, 'subscribed' => true]
        );

        $this->importStatus = "Contact {$this->newEmail} added.";
        $this->reset(['newEmail', 'newName', 'showAddForm']);
    }

    public function importCsv(): void
    {
        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $workspaceId = session('current_workspace_id');
        $path = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');

        $headers = fgetcsv($handle);
        $emailIndex = array_search('email', array_map('strtolower', $headers));

        if ($emailIndex === false) {
            $this->importStatus = 'CSV must have an "email" column.';
            fclose($handle);

            return;
        }

        $nameIndex = array_search('name', array_map('strtolower', $headers));
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $email = trim($row[$emailIndex] ?? '');
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            Contact::updateOrCreate(
                ['workspace_id' => $workspaceId, 'email' => $email],
                ['name' => $nameIndex !== false ? ($row[$nameIndex] ?? null) : null]
            );

            $imported++;
        }

        fclose($handle);

        $this->importStatus = "Imported {$imported} contacts.";
        $this->csvFile = null;
        $this->showImport = false;
    }

    public function unsubscribe(int $contactId): void
    {
        Contact::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($contactId)
            ->update(['subscribed' => false]);
    }

    public function delete(int $contactId): void
    {
        Contact::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($contactId)
            ->delete();
    }

    public function render(): View
    {
        $contacts = Contact::where('workspace_id', session('current_workspace_id'))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('email', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(50);

        return view('livewire.contacts.contact-list', compact('contacts'));
    }
}
