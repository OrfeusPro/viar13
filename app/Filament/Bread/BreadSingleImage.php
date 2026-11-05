<?php

namespace App\Filament\Bread;

use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\HasMedia;

trait BreadSingleImage
{
    public array $singleImageUploads = [];
    public array $singleImageProperties = [];

    public function deleteSingleImage(string $field, int $mediaId): void
    {
        abort_unless($this->editing && $this->recordId !== null, 403);
        $section = $this->singleImageSection($field);
        abort_unless($section && $section['media'] && (int) $section['media']->id === $mediaId, 404);
        $record = $this->registry()->model($this->bread())->newQuery()->findOrFail($this->recordId);
        $record->getConnection()->transaction(function () use ($record, $field, $mediaId): void {
            $record->newQuery()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
            $media = $record->media()->where('collection_name', $field)->orderBy('order_column')->orderBy('id')->lockForUpdate()->first();
            abort_unless($media && (int) $media->id === $mediaId, 404);
            $journal = new BreadMediaJournal($record->getConnection());
            $journal->retire($media);
            $record->getConnection()->afterCommit(function () use ($field): void {
                $this->singleImageProperties[$field] = ['title' => null, 'alt' => null];
            });
        });
    }

    public function singleImageSection(string $field): ?array
    {
        $bread = $this->requireBread($this->recordId === null ? 'add' : 'edit');
        $row = collect($this->registry()->rows($bread))->first(fn ($row) => $row->type === 'adv_image' && $row->field === $field && $row->{$this->recordId === null ? 'add' : 'edit'});
        if (! $row) { return null; }
        $model = $this->registry()->model($bread);
        if (! $model instanceof HasMedia) { return null; }
        $media = $this->recordId === null ? null : $model->newQuery()->findOrFail($this->recordId)->getFirstMedia($field);
        return ['label' => $row->display_name ?: $field, 'media' => $media];
    }

    private function fillSingleImages($record): void
    {
        $this->singleImageUploads = [];
        $this->singleImageProperties = [];
        if (! $record instanceof HasMedia) { return; }
        foreach ($this->registry()->rows($this->bread()) as $row) {
            if ($row->type !== 'adv_image' || ! $row->edit) { continue; }
            $media = $record->getFirstMedia($row->field);
            $this->singleImageProperties[$row->field] = ['title' => $media?->getCustomProperty('title'), 'alt' => $media?->getCustomProperty('alt')];
        }
    }

    private function validatedSingleImages($bread, $model): array
    {
        if (! $model instanceof HasMedia) { return []; }
        $operation = $this->recordId === null ? 'add' : 'edit';
        $record = $this->recordId === null ? null : $model->newQuery()->findOrFail($this->recordId);
        $images = [];
        foreach ($this->registry()->rows($bread) as $row) {
            if ($row->type !== 'adv_image' || ! $row->{$operation}) { continue; }
            $field = $row->field;
            $this->resetValidation(['singleImageUploads.'.$field, 'singleImageProperties.'.$field.'.title', 'singleImageProperties.'.$field.'.alt']);
            $file = $this->singleImageUploads[$field] ?? null;
            $properties = $this->singleImageProperties[$field] ?? [];
            try { validator(['file' => $file, 'title' => $properties['title'] ?? null, 'alt' => $properties['alt'] ?? null], [
                'file' => [$row->required && ! $record?->getFirstMedia($field) ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
                'title' => 'nullable|string|max:2000', 'alt' => 'nullable|string|max:2000',
            ])->validate(); } catch (ValidationException $exception) {
                $messages = [];
                foreach ($exception->errors() as $key => $errors) {
                    $messages[$key === 'file' ? 'singleImageUploads.'.$field : 'singleImageProperties.'.$field.'.'.$key] = $errors;
                }
                throw ValidationException::withMessages($messages);
            }
            if ($file !== null && ! $file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                throw ValidationException::withMessages(['singleImageUploads.'.$field => 'Выберите загруженное изображение.']);
            }
            $images[$field] = ['file' => $file, 'properties' => array_intersect_key($properties, array_flip(['title', 'alt']))];
        }
        return $images;
    }

    private function persistSingleImages($record, array $images): void
    {
        $connection = $record->getConnection();
        $journal = new BreadMediaJournal($connection);
        $connection->afterCommit(function (): void { $this->singleImageUploads = []; });
        foreach ($images as $field => $image) {
            $media = $record->getFirstMedia($field);
            if ($image['file']) {
                $old = $record->getMedia($field)->all();
                $file = $image['file'];
                $media = $journal->add($record, $file, $field, $image['properties']);
                foreach ($old as $previous) {
                    if ($previous->id !== $media->id) {
                        // Suppress Spatie's filesystem observer until DB commit.
                        $journal->retire($previous);
                    }
                }
            } elseif ($media) {
                foreach ($image['properties'] as $key => $value) { $media->setCustomProperty($key, $value); }
                $media->save();
            }
        }
    }
}
