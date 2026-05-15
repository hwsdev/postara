<?php

namespace App\Livewire\Suppressions;

use App\Models\Suppression;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SuppressionList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function remove(int $id): void
    {
        Suppression::where('workspace_id', session('current_workspace_id'))
            ->findOrFail($id)
            ->delete();
    }

    public function render(): View
    {
        $suppressions = Suppression::where('workspace_id', session('current_workspace_id'))
            ->when($this->search, fn ($q) => $q->where('email', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(50);

        return view('livewire.suppressions.suppression-list', compact('suppressions'));
    }
}
