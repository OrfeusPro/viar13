<?php

namespace App\Livewire\Admin;

use App\Filament\Resources\SaConversations\SaConversationResource;
use App\Models\SaConversation;
use Livewire\Component;

class SaInboxBadge extends Component
{
    public function render()
    {
        $allowed = SaConversationResource::canViewAny();
        $count = $allowed ? SaConversation::where('unread_for_manager', true)->count() : 0;
        if ($allowed) { $this->dispatch('sa-inbox-count', count: $count); }
        return view('filament.components.sa-inbox-badge', ['allowed' => $allowed,
            'count' => $count]);
    }
}
