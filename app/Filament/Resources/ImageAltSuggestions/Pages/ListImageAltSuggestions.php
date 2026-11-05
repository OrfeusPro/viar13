<?php

namespace App\Filament\Resources\ImageAltSuggestions\Pages;

use App\Filament\Resources\ImageAltSuggestions\ImageAltSuggestionResource;
use Filament\Resources\Pages\ListRecords;

class ListImageAltSuggestions extends ListRecords
{
    protected static string $resource = ImageAltSuggestionResource::class;

    public array $altDrafts = [];

    #[\Livewire\Attributes\Locked]
    public array $altDraftSources = [];

    public function draftFor(\App\Models\ImageAltSuggestion $record): array
    {
        $source = [
            'alt' => $record->approved_alt ?: ($record->suggested_alt ?: ''),
            'title' => $record->approved_title ?: ($record->suggested_title ?: ''),
        ];
        if (! isset($this->altDrafts[$record->id])
            || $this->altDrafts[$record->id] === ($this->altDraftSources[$record->id] ?? null)) {
            $this->altDrafts[$record->id] = $source;
            $this->altDraftSources[$record->id] = $source;
        }
        return $this->altDrafts[$record->id];
    }

    public function approveDraft(int $id): void
    {
        abort_unless(ImageAltSuggestionResource::canViewAny() && ImageAltSuggestionResource::permitted('edit'), 403);
        $record = \App\Models\ImageAltSuggestion::findOrFail($id);
        abort_if($record->status === \App\Models\ImageAltSuggestion::STATUS_APPLIED, 422);
        $draft = $this->draftFor($record);
        $this->validate([
            'altDrafts.'.$id.'.alt' => 'nullable|string|max:'.(int) config('alt_generation.limits.alt_max', 125),
            'altDrafts.'.$id.'.title' => 'nullable|string|max:'.(int) config('alt_generation.limits.title_max', 70),
        ]);
        $record->approved_alt = $draft['alt'] ?? null;
        $record->approved_title = $draft['title'] ?? null;
        $record->setStatus(\App\Models\ImageAltSuggestion::STATUS_APPROVED);
        $record->reviewed_by = auth()->id();
        $record->reviewed_at = now();
        $record->save();
        unset($this->altDrafts[$id], $this->altDraftSources[$id]);
        \Filament\Notifications\Notification::make()->title('ALT одобрен')->success()->send();
    }

    public function selectStatus(?string $status = null): void
    {
        abort_unless(ImageAltSuggestionResource::canViewAny(), 403);
        abort_unless($status === null || in_array($status, \App\Models\ImageAltSuggestion::statuses(), true), 422);
        $this->tableFilters['status']['value'] = $status;
        $this->updatedTableFilters();
    }
}
