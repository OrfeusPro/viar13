<?php

namespace App\Livewire\Admin;

use App\Filament\Bread\BreadMenuTree;
use App\Models\User;
use App\Services\Admin\MenuPreferences as Preferences;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MenuPreferences extends Component
{
    public array $selected = [];

    public bool $showAll = true;

    private function user(): User
    {
        $user = filament()->auth()->user();
        abort_unless($user instanceof User && $user->canAccessPanel(filament()->getCurrentPanel()), 403);

        return $user;
    }

    private function tree(): array
    {
        $this->user();

        return BreadMenuTree::make(filament()->getNavigation());
    }

    private function availableIds(): array
    {
        return array_keys(array_merge([], ...array_column(Preferences::groups($this->tree()), 'items')));
    }

    public function mount(): void
    {
        $this->loadPreferences();
    }

    private function loadPreferences(): void
    {
        $preferences = app(Preferences::class)->read($this->user());
        $this->showAll = $preferences['show_all'];
        $this->selected = array_values(array_diff($this->availableIds(), $preferences['hidden']));
    }

    public function openSettings(): void
    {
        $this->loadPreferences();
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'admin-menu-preferences');
    }

    public function updatedShowAll(): void
    {
        app(Preferences::class)->save($this->user(), ['show_all' => $this->showAll]);
    }

    public function resetSelection(): void
    {
        $this->selected = $this->availableIds();
    }

    public function save(): void
    {
        $this->user();
        $this->validate(['selected' => ['array'], 'selected.*' => ['string', 'distinct', Rule::in($this->availableIds())]]);
        app(Preferences::class)->save($this->user(), [
            'show_all' => false,
            'hidden' => array_values(array_diff($this->availableIds(), $this->selected)),
        ]);
        $this->showAll = false;
        $this->dispatch('close-modal', id: 'admin-menu-preferences');
    }

    public function render()
    {
        $tree = $this->tree();
        $preferences = app(Preferences::class)->read($this->user());

        return view('livewire.admin.menu-preferences', [
            'tree' => $preferences['show_all'] ? $tree : Preferences::filter($tree, $preferences['hidden']),
            'groups' => Preferences::groups($tree),
        ]);
    }
}
