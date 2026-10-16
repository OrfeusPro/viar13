<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VoyagerSettings extends Page
{
    protected static ?string $slug = 'voyager-settings';

    protected static ?string $navigationLabel = 'Настройки Voyager';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'filament.pages.voyager-settings';

    public ?int $settingId = null;

    public array $data = [];
    public bool $creating = false;
    public string $activeGroup = '';
    public ?int $pendingId = null;
    public bool $clearingValue = false;
    public bool $richSource = false;
    public bool $editingAll = false;
    #[\Livewire\Attributes\Locked]
    public array $editingIds = [];
    public array $richSources = [];

    /** @var array<string, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    private array $newRichAttachments = [];

    public const TYPES = ['text' => 'Текст', 'text_area' => 'Многострочный текст', 'rich_text_box' => 'Визуальный редактор',
        'code_editor' => 'Код', 'checkbox' => 'Флажок', 'radio_btn' => 'Переключатели',
        'select_dropdown' => 'Выпадающий список', 'file' => 'Файл', 'image' => 'Изображение'];

    public static function canAccess(): bool
    {
        return static::permitted('browse');
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $groups = $this->groups();
        $this->activeGroup = array_key_exists('Site', $groups) ? 'Site' : (string) array_key_first($groups);
    }

    public function hydrate(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getTitle(): string | Htmlable
    {
        return 'Настройки Voyager';
    }

    public function groups(): array
    {
        return $this->settingGroups();
    }

    private function settingGroups(?array $ids = null): array
    {
        $groups = DB::table('settings')->when($ids !== null, fn ($query) => $query->whereIn('id', $ids))
            ->orderBy('order')->orderBy('id')->get()->groupBy('group')->all();
        // Voyager places the ungrouped settings first, then uses first occurrence.
        if (isset($groups[''])) { $groups = ['' => $groups['']] + $groups; }
        return $groups;
    }

    public function form(Schema $schema): Schema
    {
        if ($this->editingAll) {
            if (! static::permitted('edit')) { return $schema->statePath('data')->components([]); }
            $tabs = [];
            $tabGroups = [];
            $activeTab = 1;
            $fields = app(\App\Filament\Bread\BreadSettingFields::class);
            foreach ($this->settingGroups($this->editingIds) as $group => $settings) {
                $sections = [];
                foreach ($settings as $setting) {
                    $id = $setting->id;
                    $sections[] = \Filament\Schemas\Components\Section::make($setting->display_name ?: $setting->key)->description($setting->key)->components([
                        $this->trackRichAttachments($fields->component($setting, (bool) ($this->richSources[$id] ?? false), 'value_'.$id, 'replacement_'.$id))->label($setting->display_name ?: $setting->key),
                        ... (in_array($setting->type, ['image', 'file'], true) ? [ViewField::make('storedMedia_'.$id)->view('filament.components.setting-stored-media')
                            ->viewData(['type' => $setting->type, 'value' => $setting->value])->dehydrated(false)] : []),
                        TextInput::make('group_'.$id)->label('Группа')->maxLength(255),
                    ]);
                }
                $tabId = 'settings-group-'.sha1((string) $group);
                $tabGroups[$tabId] = (string) $group;
                $tabs[] = \Filament\Schemas\Components\Tabs\Tab::make($group ?: 'Общие')->id($tabId)->key($tabId, isInheritable: false)->components($sections);
                if ((string) $group === $this->activeGroup) { $activeTab = count($tabs); }
            }
            return $schema->statePath('data')->components([
                \Filament\Schemas\Components\Tabs::make('Настройки')->tabs($tabs)->contained(false)->activeTab($activeTab)
                    ->extraAlpineAttributes(['x-effect' => 'if (tab && Object.hasOwn('.\Illuminate\Support\Js::from($tabGroups).', tab)) { $wire.set(\'activeGroup\', ('.\Illuminate\Support\Js::from($tabGroups).')[tab], false) }']),
            ]);
        }
        if ($this->creating) {
            if (! static::permitted('add')) { return $schema->statePath('data')->components([]); }
            return $schema->statePath('data')->components([
                TextInput::make('display_name')->label('Название')->required()->maxLength(255),
                TextInput::make('key')->label('Ключ')->required()->maxLength(255),
                Select::make('type')->label('Тип')->options(self::TYPES)->required(),
                TextInput::make('group')->label('Группа')->maxLength(255)->helperText('Название существующей или новой группы.'),
                Textarea::make('details')->label('Параметры JSON')->rows(5)->nullable()->rules(['nullable', 'json']),
            ]);
        }
        $setting = $this->setting();
        if (! $setting || ! static::permitted('edit')) {
            return $schema->statePath('data')->components([]);
        }
        $component = $this->trackRichAttachments(app(\App\Filament\Bread\BreadSettingFields::class)->component($setting, $this->richSource));

        return $schema->statePath('data')->components([
            $component->label($setting->display_name ?: $setting->key),
            ... (in_array($setting->type, ['image', 'file'], true) ? [ViewField::make('storedMedia')->view('filament.components.setting-stored-media')
                ->viewData(['type' => $setting->type, 'value' => $setting->value])->dehydrated(false)] : []),
            TextInput::make('group')->label('Группа')->maxLength(255),
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(static::permitted('add'), 403);
        $this->editingAll = false;
        $this->settingId = null;
        $this->creating = true;
        $this->resetValidation();
        $this->form->fill(['display_name' => '', 'key' => '', 'type' => 'text', 'group' => '', 'details' => null]);
    }

    public function createSetting(): void
    {
        abort_unless($this->creating && static::permitted('add'), 403);
        $values = $this->form->getState();
        $group = (string) ($values['group'] ?? '');
        $key = Str::slug($group).'.'.$values['key'];
        validator(['data' => ['key' => $key]], ['data.key' => ['required', 'max:255', Rule::unique('settings', 'key')]])->validate();
        DB::transaction(function () use ($values, $group, $key): void {
            $last = DB::table('settings')->orderByDesc('order')->lockForUpdate()->first();
            DB::table('settings')->insert(['key' => $key, 'display_name' => $values['display_name'], 'type' => $values['type'],
                'group' => $group, 'details' => $values['details'] ?? null, 'value' => '', 'order' => $last ? (int) $last->order + 1 : 0]);
        });
        $this->activeGroup = $group;
        $this->creating = false;
        Notification::make()->title('Настройка создана')->success()->send();
    }

    public function openEdit(int $id): void
    {
        abort_unless(static::permitted('edit'), 403);
        $this->editingAll = false;
        $setting = DB::table('settings')->where('id', $id)->first();
        abort_unless($setting, 404);
        $this->settingId = $id;
        $this->creating = false;
        $this->richSource = $setting->type === 'rich_text_box' && app(\App\Filament\Bread\BreadRichEditor::class)->requiresSource($setting->value, 'settings');
        $this->resetValidation();
        $this->form->fill(['value' => app(\App\Filament\Bread\BreadSettingFields::class)->state($setting), 'replacement' => null, 'group' => $setting->group]);
    }

    public function enableRichSource(string $field): void
    {
        abort_unless(static::permitted('edit'), 403);
        if ($this->editingAll) {
            abort_unless(preg_match('/^value_(\d+)$/D', $field, $matches) && in_array((int) $matches[1], $this->editingIds, true), 403);
            abort_unless(DB::table('settings')->where('id', $matches[1])->where('type', 'rich_text_box')->exists(), 403);
            $this->richSources[(int) $matches[1]] = true;
            unset($this->cachedSchemas['form']);
            return;
        }
        abort_unless($field === 'value', 403);
        $this->richSource = true;
        unset($this->cachedSchemas['form']);
    }

    public function openEditAll(): void
    {
        abort_unless(static::permitted('edit'), 403);
        $this->settingId = null;
        $this->creating = false;
        $this->editingAll = true;
        $this->richSources = [];
        $settings = DB::table('settings')->orderBy('id')->get();
        $this->editingIds = $settings->pluck('id')->map(fn ($id) => (int) $id)->all();
        $state = [];
        foreach ($settings as $setting) {
            $id = $setting->id;
            $state['value_'.$id] = app(\App\Filament\Bread\BreadSettingFields::class)->state($setting);
            $state['replacement_'.$id] = null;
            $state['group_'.$id] = $setting->group;
            $this->richSources[$id] = $setting->type === 'rich_text_box' && app(\App\Filament\Bread\BreadRichEditor::class)->requiresSource($setting->value, 'settings');
        }
        $this->resetValidation();
        $this->form->fill($state);
    }

    public function saveAll(): void
    {
        abort_unless(static::permitted('edit') && $this->editingAll, 403);
        $draft = $this->data;
        $this->newRichAttachments = [];
        $created = [];
        $uploads = [];
        try {
            $values = $this->form->getState();
            DB::transaction(function () use ($values, &$created, &$uploads): void {
                $settings = DB::table('settings')->whereIn('id', $this->editingIds)->orderBy('id')->lockForUpdate()->get();
                abort_unless($settings->count() === count($this->editingIds), 409, 'Список настроек изменился. Откройте редактор заново.');
                $updates = [];
                $keys = [];
                foreach ($settings as $setting) {
                    $id = $setting->id;
                    $group = (string) ($values['group_'.$id] ?? '');
                    $prefix = Str::slug((string) $setting->group).'.';
                    $suffix = strncasecmp($setting->key, $prefix, strlen($prefix)) === 0 ? substr($setting->key, strlen($prefix)) : $setting->key;
                    $key = Str::slug($group).'.'.$suffix;
                    validator(['data' => ['group_'.$id => $key]], ['data.group_'.$id => ['max:255', Rule::unique('settings', 'key')->ignore($id)]])->validate();
                    if (isset($keys[$key])) { throw ValidationException::withMessages(['data.group_'.$id => 'Ключ настройки уже используется.']); }
                    $keys[$key] = true;
                    $updates[$id] = ['key' => $key, 'group' => $group];
                }
                foreach ($settings as $setting) {
                    $id = $setting->id;
                    $media = in_array($setting->type, ['image', 'file'], true);
                    $value = $media ? $setting->value : ($values['value_'.$id] ?? null);
                    $replacement = $values['replacement_'.$id] ?? null;
                    if ($media && $replacement instanceof \Illuminate\Http\UploadedFile) {
                        $value = app(\App\Filament\Bread\BreadSettingUploads::class)->store($replacement, $setting,
                            function (array $paths) use (&$created): void { $created = [...$created, ...$paths]; }, 'data.replacement_'.$id);
                        $uploads[] = $replacement;
                    }
                    DB::table('settings')->where('id', $id)->update([...$updates[$id], 'value' => $setting->type === 'checkbox' ? ($value ? '1' : '0') : $value]);
                }
            });
        } catch (\Throwable $error) {
            foreach ($created as $path) { Storage::disk('public')->delete($path); }
            $this->restoreRichDraft($draft);
            throw $error;
        }
        foreach ($uploads as $upload) { if ($upload instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) { $upload->delete(); } }
        $this->finishRichAttachments();
        $this->cancel();
        Notification::make()->title('Настройки сохранены')->success()->send();
    }

    public function save(): void
    {
        abort_unless(static::permitted('edit'), 403);
        $setting = $this->setting();
        abort_unless($setting, 404);
        $created = [];
        $replacement = null;
        $draft = $this->data;
        $this->newRichAttachments = [];
        try {
            $values = $this->form->getState();
            $value = in_array($setting->type, ['image', 'file'], true) ? $setting->value : ($values['value'] ?? null);
            $group = (string) ($values['group'] ?? '');
            $prefix = Str::slug((string) $setting->group).'.';
            $suffix = strncasecmp($setting->key, $prefix, strlen($prefix)) === 0 ? substr($setting->key, strlen($prefix)) : $setting->key;
            $key = Str::slug($group).'.'.$suffix;
            validator(['data' => ['group' => $key]], ['data.group' => ['max:255', Rule::unique('settings', 'key')->ignore($setting->id)]])->validate();
            $replacement = $values['replacement'] ?? null;
            if (in_array($setting->type, ['image', 'file'], true) && $replacement instanceof \Illuminate\Http\UploadedFile) {
                $value = app(\App\Filament\Bread\BreadSettingUploads::class)->store($replacement, $setting,
                    function (array $paths) use (&$created): void { $created = [...$created, ...$paths]; });
            }
            DB::table('settings')->where('id', $setting->id)->update([
                'value' => $setting->type === 'checkbox' ? ($value ? '1' : '0') : $value,
                'group' => $group, 'key' => $key,
            ]);
        } catch (\Throwable $error) {
            foreach ($created as $path) { Storage::disk('public')->delete($path); }
            $this->restoreRichDraft($draft);
            throw $error;
        }
        if ($replacement instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) { $replacement->delete(); }
        $this->finishRichAttachments();
        $this->activeGroup = $group;
        $this->settingId = null;
        Notification::make()->title('Настройка сохранена')->success()->send();
    }

    public function cancel(): void
    {
        $this->editingAll = false;
        $this->editingIds = [];
        $this->richSources = [];
        $this->settingId = null;
        $this->creating = false;
        $this->resetValidation();
        $groups = $this->groups();
        if (! array_key_exists($this->activeGroup, $groups)) { $this->activeGroup = (string) array_key_first($groups); }
    }

    public function moveSetting(int $id, string $direction): void
    {
        abort_unless(static::permitted('edit'), 403);
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        $moved = DB::transaction(function () use ($id, $direction): bool {
            $setting = DB::table('settings')->where('id', $id)->lockForUpdate()->first();
            abort_unless($setting, 404);
            $this->activeGroup = (string) $setting->group;
            $query = DB::table('settings')->where('group', $setting->group)
                ->where('order', $direction === 'up' ? '<' : '>', $setting->order);
            $neighbor = $query->orderBy('order', $direction === 'up' ? 'desc' : 'asc')->orderBy('id')->lockForUpdate()->first();
            if (! $neighbor) { return false; }
            DB::table('settings')->where('id', $id)->update(['order' => $neighbor->order]);
            DB::table('settings')->where('id', $neighbor->id)->update(['order' => $setting->order]);
            return true;
        });
        Notification::make()->title($moved ? 'Порядок изменён' : 'Настройка уже на краю списка')->status($moved ? 'success' : 'warning')->send();
    }

    public function requestRemove(int $id, bool $clearValue = false): void
    {
        abort_unless(static::permitted('delete'), 403);
        abort_unless(DB::table('settings')->where('id', $id)->exists(), 404);
        $this->pendingId = $id;
        $this->clearingValue = $clearValue;
        $this->resetValidation('settingDelete');
        $this->dispatch('open-modal', id: 'remove-setting');
    }

    public function pendingSetting(): ?object
    {
        return $this->pendingId ? DB::table('settings')->where('id', $this->pendingId)->first() : null;
    }

    public function confirmRemove(): void
    {
        abort_unless(static::permitted('delete'), 403);
        abort_unless($this->pendingId !== null, 409);
        $backup = null;
        $disk = Storage::disk('public');
        try {
            DB::transaction(function () use (&$backup, $disk): void {
                $setting = DB::table('settings')->where('id', $this->pendingId)->lockForUpdate()->first();
                abort_unless($setting, 404);
                $this->activeGroup = (string) $setting->group;
                if (! $this->clearingValue) {
                    DB::table('settings')->where('id', $setting->id)->delete();
                    return;
                }
                if ($setting->type === 'image' && $setting->value && $disk->exists($setting->value)) {
                    app(\App\Filament\Bread\BreadLibraryFiles::class)->checkedFile($setting->value);
                    $backup = ['path' => $setting->value, 'bytes' => $disk->get($setting->value)];
                    if (! $disk->delete($setting->value)) {
                        throw ValidationException::withMessages(['settingDelete' => 'Не удалось удалить изображение.']);
                    }
                }
                DB::table('settings')->where('id', $setting->id)->update(['value' => '']);
            });
        } catch (\Throwable $error) {
            if ($backup !== null && ! $disk->exists($backup['path'])) { $disk->put($backup['path'], $backup['bytes']); }
            throw $error;
        }
        $this->pendingId = null;
        $this->settingId = null;
        $this->creating = false;
        $groups = $this->groups();
        if (! array_key_exists($this->activeGroup, $groups)) { $this->activeGroup = (string) array_key_first($groups); }
        $this->dispatch('close-modal', id: 'remove-setting');
        Notification::make()->title($this->clearingValue ? 'Значение очищено' : 'Настройка удалена')->success()->send();
    }

    private function setting(): ?object
    {
        return $this->settingId ? DB::table('settings')->where('id', $this->settingId)->first() : null;
    }

    private function trackRichAttachments(\Filament\Forms\Components\Field $field): \Filament\Forms\Components\Field
    {
        if (! $field instanceof \Filament\Forms\Components\RichEditor) { return $field; }
        return $field->saveUploadedFileAttachmentUsing(function (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file,
            \Filament\Forms\Components\RichEditor $component): string {
            $directory = $component->getFileAttachmentsDirectory();
            $name = Str::uuid().'.'.$file->guessExtension();
            $path = $directory.'/'.$name;
            $this->newRichAttachments[$path] = $file;
            if (! $file->storeAs($directory, $name, 'public')) {
                throw ValidationException::withMessages([$component->getStatePath() => 'Не удалось сохранить вложение.']);
            }
            return $path;
        });
    }

    private function restoreRichDraft(array $draft): void
    {
        foreach ($this->newRichAttachments as $path => $file) { Storage::disk('public')->delete($path); }
        $this->newRichAttachments = [];
        $this->data = $draft;
    }

    private function finishRichAttachments(): void
    {
        foreach ($this->newRichAttachments as $file) { $file->delete(); }
        $this->newRichAttachments = [];
        $this->componentFileAttachments = [];
    }

    private static function permitted(string $action): bool
    {
        if (! DatabaseSchema::hasTable('settings')) {
            return false;
        }
        $user = auth('filament')->user();

        return $user && method_exists($user, 'hasPermission') && $user->hasPermission($action . '_settings');
    }
}
