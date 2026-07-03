<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class Leaderboard extends Component
{
    public $topUsers;

public function mount()
{
    $this->topUsers = User::where('points', '>', 0)
        ->orderByDesc('points')
        ->take(10)
        ->get();
}
    public function render()
    {
        return view('livewire.leaderboard');
    }
}
