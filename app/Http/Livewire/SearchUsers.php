<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SearchUsers extends Component
{
    use WithPagination;

    public string $search = '';

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::query()
            ->search($this->search)
            ->orderBy('name')
            ->paginate();

        return view('livewire.search-users', compact('users'));
    }
}
