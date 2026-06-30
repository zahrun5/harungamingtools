<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class Leaderboard extends Component
{
    public $topUsers;

    public function mount()
    {
        $this->topUsers = User::withCount('calculatorUsages')
            ->orderByDesc('calculator_usages_count')
            ->get()->filter(fn($u) => $u->calculator_usages_count > 0)->take(10);
    }

    public function render()
    {
        return view('livewire.leaderboard');
    }
}
