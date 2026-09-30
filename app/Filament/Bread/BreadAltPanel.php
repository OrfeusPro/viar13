<?php

namespace App\Filament\Bread;

use App\Models\ImageAltSuggestion;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

trait BreadAltPanel
{
    public bool $altPanelOpen = false;

    public string $altLocale = 'en';

    public array $altValues = [];

    public function altPermitted(string $action): bool
    {
        $user = auth('filament')->user();

        return $user && method_exists($user, 'hasPermission')
            && ($user->hasPermission($action . '_alt_suggestions')
                || $user->hasPermission($action . '_image_alt_suggestions'));
    }

    private function altSuggestions()
    {
        $bread = $this->requireBread('edit');
        abort_unless($this->editing && $this->recordId !== null && $this->altPermitted('browse'), 403);
        $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->recordId);
        abort_unless($record, 404);
        $fields = collect($this->registry()->rows($bread))
            ->filter(fn ($row): bool => (bool) $row->edit && in_array($row->type, ['image', 'multiple_images', 'media_picker', 'adv_media_files'], true))
            ->pluck('field')->all();

        return ImageAltSuggestion::query()
            ->whereIn('imageable_type', array_unique([get_class($record), $record->getMorphClass()]))
            ->where('imageable_id', $record->getKey())
            ->where(function ($query) use ($fields): void {
                $query->whereIn('field', $fields);
                foreach ($fields as $field) {
                    $query->orWhere('field', 'media:' . $field);
                }
            });
    }

    public function altGroups(): array
    {
        if (! $this->editing || $this->recordId === null || ! $this->altPermitted('browse') || ! Schema::hasTable('image_alt_suggestions')) {
            return [];
        }

        return $this->altSuggestions()->orderByDesc('id')->get()
            ->unique(fn ($row): string => $row->field . "\0" . $row->image_path . "\0" . $row->locale)
            ->groupBy(fn ($row): string => $row->field . "\0" . $row->image_path)
            ->map(fn ($rows): array => [
                'id' => $rows->first()->id,
                'field' => $rows->first()->field,
                'path' => $rows->first()->image_path,
                'rows' => $rows->keyBy('locale'),
            ])->values()->all();
    }

    public function openAltPanel(): void
    {
        $this->altValues = [];
        foreach ($this->altGroups() as $group) {
            foreach ($group['rows'] as $row) {
                $this->altValues[$row->id] = [
                    'alt' => $row->approved_alt ?? $row->suggested_alt ?? '',
                    'title' => $row->approved_title ?? $row->suggested_title ?? '',
                ];
            }
        }
        $this->altLocale = $this->locale;
        $this->altPanelOpen = true;
        $this->resetValidation('altValues');
    }

    public function changeAltLocale(string $locale): void
    {
        abort_unless(in_array($locale, $this->registry()->locales(), true), 422);
        $this->altLocale = $locale;
    }

    // groupId identifies an image through a scoped suggestion, never a trusted path.
    public function saveAltPanel(string $scope = 'all', ?int $groupId = null): void
    {
        foreach ($this->getErrorBag()->keys() as $key) {
            if ($key === 'altValues' || str_starts_with($key, 'altValues.')) {
                $this->resetValidation($key);
            }
        }
        abort_unless($this->altPanelOpen && $this->altPermitted('edit'), 403);
        abort_unless(in_array($scope, ['language', 'image', 'all'], true), 422);
        $query = $this->altSuggestions();
        if ($scope !== 'all') {
            $image = (clone $query)->find($groupId);
            abort_unless($image, 404);
            $query->where('field', $image->field)->where('image_path', $image->image_path);
        }
        if ($scope === 'language') {
            abort_unless(in_array($this->altLocale, $this->registry()->locales(), true), 422);
            $query->where('locale', $this->altLocale);
        }
        $rows = $query->get();
        abort_if($rows->isEmpty() || $rows->count() > 500, 422);
        $payload = [];
        foreach ($rows as $row) {
            if (! array_key_exists($row->id, $this->altValues)) {
                throw ValidationException::withMessages(['altValues' => 'Откройте панель заново: состав изображений изменился.']);
            }
            $payload[$row->id] = $this->altValues[$row->id];
        }
        validator(['altValues' => $payload], [
            'altValues.*' => 'array',
            'altValues.*.alt' => 'nullable|string|max:' . (int) config('alt_generation.limits.alt_max', 125),
            'altValues.*.title' => 'nullable|string|max:' . (int) config('alt_generation.limits.title_max', 70),
        ])->validate();
        DB::transaction(function () use ($rows, $payload): void {
            foreach ($rows as $row) {
                $row = ImageAltSuggestion::query()->lockForUpdate()->findOrFail($row->id);
                $row->approved_alt = $payload[$row->id]['alt'] ?? null;
                $row->approved_title = $payload[$row->id]['title'] ?? null;
                $row->setStatus(ImageAltSuggestion::STATUS_APPROVED);
                $row->reviewed_by = auth('filament')->id();
                $row->reviewed_at = now();
                $row->error = null;
                $row->save();
            }
        });
        $ids = $rows->modelKeys();
        // Reuse the original application workflow and its audit logs and failure states.
        $appLocale = app()->getLocale();
        try {
            $code = Artisan::call('alt:apply', [
                '--ids' => implode(',', $ids), '--source' => 'admin',
                '--applied-by' => (string) auth('filament')->id(),
            ]);
        } finally {
            app()->setLocale($appLocale);
        }
        $applied = ImageAltSuggestion::query()->whereIn('id', $ids)->where('status', ImageAltSuggestion::STATUS_APPLIED)->count();
        Notification::make()->title('Применено ALT/TITLE: ' . $applied . ' из ' . count($ids))
            ->color($code === 0 && $applied === count($ids) ? 'success' : 'warning')->send();
    }
}
