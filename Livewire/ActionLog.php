<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;

class ActionLog extends Component
{
    public string $relationId = '';
    public string $relationType = '';

    public Collection $actionLogs;

    public bool $showHistoryDrawer = false;

    public function mount()
    {
        $this->actionLogs = \App\Models\ActionLog::latest()
            ->where('relation_id', $this->relationId)
            ->where('relation_type', $this->relationType)
            ->get();
    }

    public function render()
    {
        return view('livewire.action-log');
    }
}
