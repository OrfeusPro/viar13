<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Illuminate\Validation\ValidationException;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\HasMedia;
use stdClass;

class VoyagerBread extends Page
{
    use WithPagination;
    use WithFileUploads;

    protected static ?string $slug = 'bread/{type}';

    protected static bool $shouldRegisterNavigation = true;

    protected string $view = 'filament.pages.voyager-bread';

    public string $type = '';

    public string $locale = 'en';

    public ?int $recordId = null;

    public bool $editing = false;

    public ?int $viewId = null;

    public array $data = [];

    public $mediaUpload = null;

    public array $mediaProperties = [];

    public string $search = '';

    public ?int $filterType = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public static function getNavigationItems(): array
    {
        $registry = app(BreadRegistry::class);
        if (! DatabaseSchema::hasTable('menu_items') || ! DatabaseSchema::hasTable('menus')) {
            return [];
        }
        $menuId = DB::table('menus')->where('name', 'admin')->value('id');
        if (! $menuId) {
            return [];
        }
        $items = DB::table('menu_items')->where('menu_id', $menuId)->where('status', 1)->orderBy('order')->get();
        $titles = $items->pluck('title', 'id');
        $types = collect($registry->types())->keyBy('slug');
        $navigation = [];
        foreach ($items as $item) {
            $slug = null;
            if (preg_match('/^voyager\.([a-z0-9_-]+)\.(?:index|edit)$/', (string) $item->route, $match)) {
                $slug = $match[1];
            } elseif (preg_match('~^/admin/([a-z0-9_-]+)(?:[/?].*)?$~', (string) $item->url, $match)) {
                $slug = $match[1];
            }
            $type = $slug ? $types->get($slug) : null;
            if (! $type || $registry->isDedicated($type) || ! $registry->permitted($type, 'browse')) {
                continue;
            }
            $group = $item->parent_id ? trim((string) ($titles->get($item->parent_id) ?: 'Контент')) : 'Контент Voyager';
            $url = static::getUrl(['type' => $type->slug]);
            if ($type->name === 'menus') {
                $menu = preg_match('~^/admin/menus/(\d+)/builder$~', (string) $item->url, $match)
                    ? (int) $match[1] : null;
                $url = VoyagerMenuItems::getUrl() . ($menu ? '?menu=' . $menu : '');
            }
            if (preg_match('~(?:\?|&)type=(\d+)~', (string) $item->url, $filter)) {
                $url .= '?type=' . $filter[1];
            }
            $navigation[] = NavigationItem::make(trim((string) $item->title) ?: $type->display_name_plural)
                ->key('bread-' . $item->id)
                ->group($group)
                ->sort((int) $item->order)
                ->url($url);
        }

        return $navigation;
    }

    public function mount(string $type): void
    {
        $this->type = $type;
        $bread = $this->bread();
        abort_unless($bread && ! $this->registry()->isDedicated($bread) && $this->registry()->permitted($bread, 'browse'), 403);
        $this->locale = (string) config('voyager.multilingual.default', 'en');
        $filter = request()->query('type');
        $this->filterType = is_scalar($filter) && ctype_digit((string) $filter) ? (int) $filter : null;
    }

    public function hydrate(): void
    {
        $bread = $this->bread();
        abort_unless($bread && ! $this->registry()->isDedicated($bread) && $this->registry()->permitted($bread, 'browse'), 403);
    }

    public function getTitle(): string | Htmlable
    {
        return (string) ($this->bread()?->display_name_plural ?: $this->type);
    }

    public function bread(): ?stdClass
    {
        return $this->registry()->type($this->type);
    }

    public function form(Schema $schema): Schema
    {
        $bread = $this->bread();
        if (! $bread || ! $this->editing) {
            return $schema->statePath('data')->components([]);
        }

        $operation = $this->recordId ? 'edit' : 'add';
        $model = $this->registry()->model($bread);
        if (! $model) {
            return $schema->statePath('data')->components([]);
        }

        $translated = method_exists($model, 'getTranslatableAttributes') ? $model->getTranslatableAttributes() : [];
        $relationships = collect($this->registry()->belongsToRows($bread, $operation))
            ->mapWithKeys(function (stdClass $row): array {
                $details = json_decode($row->details ?: '{}', true) ?: [];

                return [$details['column'] => [$row, $details]];
            });
        $components = [];
        foreach ($this->registry()->editableRows($bread, $operation) as $row) {
            if ($this->locale !== config('voyager.multilingual.default', 'en') && ! in_array($row->field, $translated, true)) {
                continue;
            }
            $details = json_decode($row->details ?: '{}', true) ?: [];
            $label = $row->display_name ?: $row->field;
            if ($relationships->has($row->field)) {
                continue;
            }
            $component = match ($row->type) {
                'text_area' => Textarea::make($row->field),
                'code_editor' => Textarea::make($row->field)->rows(16),
                'rich_text_box' => RichEditor::make($row->field),
                'number' => TextInput::make($row->field)->numeric(),
                'checkbox' => Toggle::make($row->field),
                'multiple_checkbox' => CheckboxList::make($row->field)->options($details['options'] ?? []),
                'select_dropdown' => Select::make($row->field)->options($details['options'] ?? []),
                'date' => DatePicker::make($row->field),
                'timestamp' => DateTimePicker::make($row->field),
                'color' => ColorPicker::make($row->field),
                'image' => FileUpload::make($row->field)->image()->disk('public')->directory($bread->name . '/' . date('FY'))->maxSize(10240),
                'file' => FileUpload::make($row->field)->disk('public')->directory($bread->name . '/' . date('FY'))->maxSize(102400),
                'multiple_images' => FileUpload::make($row->field)->image()->multiple()->disk('public')->directory($bread->name . '/' . date('FY'))->maxSize(10240),
                'media_picker' => FileUpload::make($row->field)->image()->multiple()->disk('public')->directory($bread->name . '/' . date('FY'))->maxSize(10240)->maxFiles((int) ($details['max'] ?? 100)),
                'password' => TextInput::make($row->field)->password()->dehydrated(fn ($state): bool => filled($state)),
                default => TextInput::make($row->field),
            };
            if ($component) {
                $component->label($label);
                if ($this->locale === config('voyager.multilingual.default', 'en') && $row->required && ($row->type !== 'password' || $this->recordId === null)) {
                    $component->required();
                }
                $components[] = $component;
            }
        }
        if ($this->locale === config('voyager.multilingual.default', 'en')) {
            foreach ($relationships as $column => [$row, $details]) {
                $table = $details['table'];
                $key = $details['key'];
                $name = $details['label'];
                $components[] = Select::make($column)
                    ->label($row->display_name ?: $column)
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => DB::table($table)
                        ->where($name, 'like', '%' . $search . '%')->limit(50)->pluck($name, $key)->all())
                    ->getOptionLabelUsing(fn ($value): ?string => $value === null ? null : DB::table($table)->where($key, $value)->value($name));
            }
            if ($this->recordId !== null) {
                foreach ($this->registry()->manyToManyRows($bread, $operation) as $relation) {
                    $row = $relation['row'];
                    $details = $relation['details'];
                    $table = $details['table'];
                    $name = $details['label'];
                    $components[] = Select::make('__pivot_' . $row->id)
                        ->label($row->display_name ?: $table)
                        ->multiple()->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => DB::table($table)
                            ->where($name, 'like', '%' . $search . '%')->limit(50)->pluck($name, 'id')->all())
                        ->getOptionLabelsUsing(fn (array $values): array => DB::table($table)->whereIn('id', $values)->pluck($name, 'id')->all());
                }
            }
        }

        return $schema->statePath('data')->components($components);
    }

    public function openCreate(): void
    {
        $bread = $this->requireBread('add');
        abort_unless($this->registry()->model($bread), 409);
        $this->recordId = null;
        $this->viewId = null;
        $this->locale = config('voyager.multilingual.default', 'en');
        $this->editing = true;
        $this->form->fill();
    }

    public function openEdit(int $id): void
    {
        $bread = $this->requireBread('edit');
        $model = $this->registry()->model($bread);
        abort_unless($model, 409);
        $record = $model->newQuery()->findOrFail($id);
        $this->recordId = $id;
        $this->viewId = null;
        $this->editing = true;
        $this->fillRecord($record);
        $this->fillMediaProperties($record);
    }

    public function changeLocale(string $locale): void
    {
        abort_unless(in_array($locale, $this->registry()->locales(), true), 422);
        if ($this->viewId !== null) {
            $this->requireBread('read');
            $this->locale = $locale;

            return;
        }
        if ($this->recordId !== null) {
            $bread = $this->requireBread('edit');
            $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->recordId);
            abort_unless($record, 409);
            $this->locale = $locale;
            $this->fillRecord($record);
        } else {
            $this->locale = $locale;
        }
    }

    public function save(): void
    {
        $bread = $this->requireBread($this->recordId ? 'edit' : 'add');
        abort_unless($this->editing && $this->registry()->model($bread), 409);
        $default = config('voyager.multilingual.default', 'en');
        abort_unless(in_array($this->locale, $this->registry()->locales(), true), 422);
        abort_if($this->recordId === null && $this->locale !== $default, 422);

        $model = $this->registry()->model($bread);
        $allowed = collect($this->registry()->editableRows($bread, $this->recordId ? 'edit' : 'add'));
        $translated = method_exists($model, 'getTranslatableAttributes') ? $model->getTranslatableAttributes() : [];
        $values = $this->form->getState();
        $pivots = [];
        if ($this->recordId !== null && $this->locale === $default) {
            foreach ($this->registry()->manyToManyRows($bread, 'edit') as $relation) {
                $key = '__pivot_' . $relation['row']->id;
                $pivots[] = [$relation, array_values(array_unique(array_map('intval', (array) ($values[$key] ?? []))))];
            }
        }
        $relationshipColumns = collect($this->registry()->belongsToRows($bread, $this->recordId ? 'edit' : 'add'))
            ->map(fn (stdClass $row): ?string => (json_decode($row->details ?: '{}', true) ?: [])['column'] ?? null)
            ->filter()->all();
        $values = array_intersect_key($values, array_flip(array_merge($allowed->pluck('field')->all(), $relationshipColumns)));
        if ($this->locale !== $default) {
            $values = array_intersect_key($values, array_flip($translated));
        } else {
            foreach ($this->registry()->belongsToRows($bread, $this->recordId ? 'edit' : 'add') as $relation) {
                $details = json_decode($relation->details ?: '{}', true) ?: [];
                $column = $details['column'] ?? null;
                if ($column && filled($values[$column] ?? null)
                    && ! DB::table($details['table'])->where($details['key'], $values[$column])->exists()) {
                    throw ValidationException::withMessages(['data.' . $column => 'Связанная запись не найдена.']);
                }
            }
        }

        DB::transaction(function () use ($bread, $model, $values, $default, $allowed, $pivots): void {
            $record = $this->recordId ? $model->newQuery()->lockForUpdate()->findOrFail($this->recordId) : $model->newInstance();
            if ($this->locale === $default) {
                foreach ($values as $field => $value) {
                    $row = $allowed->firstWhere('field', $field);
                    if ($row?->type === 'password') {
                        $value = Hash::make($value);
                    }
                    if (in_array($row?->type, ['multiple_images', 'media_picker'], true)) {
                        $value = json_encode(array_values($value ?? []), JSON_UNESCAPED_SLASHES);
                    } elseif ($row?->type === 'multiple_checkbox') {
                        $value = json_encode(array_combine((array) $value, (array) $value), JSON_UNESCAPED_SLASHES);
                    }
                    $record->setAttribute($field, $value);
                }
                $record->save();
                $this->recordId = (int) $record->getKey();
                foreach ($pivots as [$relation, $ids]) {
                    $details = $relation['details'];
                    $target = $details['table'];
                    if ($ids !== [] && DB::table($target)->whereIn('id', $ids)->count() !== count($ids)) {
                        throw ValidationException::withMessages(['data.__pivot_' . $relation['row']->id => 'Связанные записи не найдены.']);
                    }
                    $pivot = $details['pivot_table'];
                    $sourceKey = $relation['sourceKey'];
                    $targetKey = $relation['targetKey'];
                    DB::table($pivot)->where($sourceKey, $record->getKey())->delete();
                    foreach ($ids as $id) {
                        $attributes = [$sourceKey => $record->getKey(), $targetKey => $id];
                        if (DatabaseSchema::hasColumn($pivot, 'created_at')) {
                            $attributes['created_at'] = now();
                        }
                        if (DatabaseSchema::hasColumn($pivot, 'updated_at')) {
                            $attributes['updated_at'] = now();
                        }
                        DB::table($pivot)->insert($attributes);
                    }
                }
            } else {
                foreach ($values as $field => $value) {
                    $key = ['table_name' => $bread->name, 'column_name' => $field, 'foreign_key' => $record->getKey(), 'locale' => $this->locale];
                    if (blank($value)) {
                        DB::table('translations')->where($key)->delete();
                    } else {
                        DB::table('translations')->updateOrInsert($key, ['value' => $value]);
                    }
                }
            }
        });
        Notification::make()->title('Сохранено')->success()->send();
        $this->editing = false;
    }

    public function cancel(): void
    {
        $this->editing = false;
        $this->recordId = null;
        $this->viewId = null;
        $this->mediaUpload = null;
    }

    public function mediaSections(): array
    {
        if (! $this->editing || $this->recordId === null) {
            return [];
        }
        $bread = $this->requireBread('edit');
        $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->recordId);
        if (! $record instanceof HasMedia) {
            return [];
        }

        $sections = [];
        foreach ($this->registry()->rows($bread) as $row) {
            if ($row->type !== 'adv_media_files' || ! $row->edit) {
                continue;
            }
            $details = json_decode($row->details ?: '{}', true) ?: [];
            $sections[] = [
                'field' => $row->field,
                'label' => $row->display_name ?: $row->field,
                'extra' => $details['extra_fields'] ?? [],
                'files' => $record->getMedia($row->field),
            ];
        }

        return $sections;
    }

    public function uploadMedia(string $field): void
    {
        $record = $this->mediaRecord($field);
        $this->validate(['mediaUpload' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:10240']);
        $record->addMedia($this->mediaUpload->getRealPath())
            ->usingFileName($this->mediaUpload->getClientOriginalName())
            ->toMediaCollection($field, 'public');
        $this->mediaUpload = null;
        Notification::make()->title('Файл добавлен')->success()->send();
    }

    public function saveMediaProperties(string $field, int $mediaId): void
    {
        $record = $this->mediaRecord($field);
        $row = collect($this->registry()->rows($this->bread()))->first(fn (stdClass $row): bool => $row->field === $field && $row->type === 'adv_media_files');
        $details = json_decode($row->details ?: '{}', true) ?: [];
        $keys = array_merge(['title', 'alt'], array_keys($details['extra_fields'] ?? []));
        $media = $record->getMedia($field)->firstWhere('id', $mediaId);
        abort_unless($media, 404);
        foreach ($keys as $key) {
            $value = $this->mediaProperties[$mediaId][$key] ?? null;
            if ($value !== null && (! is_string($value) || mb_strlen($value) > 2000)) {
                throw ValidationException::withMessages(['mediaProperties' => 'Некорректное значение поля медиа.']);
            }
            $media->setCustomProperty($key, $value);
        }
        $media->save();
        Notification::make()->title('Подписи сохранены')->success()->send();
    }

    public function deleteMedia(string $field, int $mediaId): void
    {
        $record = $this->mediaRecord($field);
        $media = $record->getMedia($field)->firstWhere('id', $mediaId);
        abort_unless($media, 404);
        $media->delete();
        unset($this->mediaProperties[$mediaId]);
        Notification::make()->title('Файл удалён')->success()->send();
    }

    public function openView(int $id): void
    {
        $bread = $this->requireBread('read');
        abort_unless($this->registry()->model($bread)?->newQuery()->find($id), 404);
        $this->viewId = $id;
        $this->editing = false;
        $this->recordId = null;
    }

    public function viewedFields(): array
    {
        if ($this->viewId === null) {
            return [];
        }
        $bread = $this->requireBread('read');
        $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->viewId);
        abort_unless($record, 404);
        $columns = $this->registry()->columns($bread);
        $translated = method_exists($record, 'getTranslatableAttributes') ? $record->getTranslatableAttributes() : [];
        $belongs = collect($this->registry()->belongsToRows($bread, 'read'))->keyBy('field');
        $many = collect($this->registry()->manyToManyRows($bread, 'read'))->keyBy(fn (array $relation): string => $relation['row']->field);
        $fields = [];
        foreach ($this->registry()->rows($bread) as $row) {
            if (! $row->read || $row->type === 'password') {
                continue;
            }
            if ($row->type === 'relationship' && $belongs->has($row->field)) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $value = DB::table($details['table'])->where($details['key'], $record->getAttribute($details['column']))
                    ->value($details['label']);
                $fields[] = ['label' => $row->display_name ?: $row->field, 'value' => $value];

                continue;
            }
            if ($row->type === 'relationship' && $many->has($row->field)) {
                $relation = $many->get($row->field);
                $details = $relation['details'];
                $value = DB::table($details['pivot_table'] . ' as pivot')
                    ->join($details['table'] . ' as related', 'related.id', '=', 'pivot.' . $relation['targetKey'])
                    ->where('pivot.' . $relation['sourceKey'], $record->getKey())
                    ->pluck('related.' . $details['label'])->implode(', ');
                $fields[] = ['label' => $row->display_name ?: $row->field, 'value' => $value];

                continue;
            }
            if ($row->type === 'adv_media_files' && $record instanceof HasMedia) {
                $fields[] = ['label' => $row->display_name ?: $row->field,
                    'type' => 'adv_media_files', 'value' => $record->getMedia($row->field)->map->getUrl()->all()];

                continue;
            }
            if (! in_array($row->field, $columns, true)) {
                continue;
            }
            $value = $this->locale === config('voyager.multilingual.default', 'en') || ! in_array($row->field, $translated, true)
                ? $record->getAttribute($row->field)
                : DB::table('translations')->where([
                    'table_name' => $bread->name, 'column_name' => $row->field,
                    'foreign_key' => $record->getKey(), 'locale' => $this->locale,
                ])->value('value');
            $fields[] = ['label' => $row->display_name ?: $row->field, 'type' => $row->type, 'value' => $value];
        }

        return $fields;
    }

    public function deleteRecord(int $id): void
    {
        $bread = $this->requireBread('delete');
        $model = $this->registry()->model($bread);
        abort_unless($model, 409);
        $model->newQuery()->findOrFail($id)->delete();
        Notification::make()->title('Удалено')->success()->send();
    }

    public function records(): array
    {
        $bread = $this->bread();
        $model = $bread ? $this->registry()->model($bread) : null;
        if (! $bread || ! $this->registry()->permitted($bread, 'browse') || ! $model) {
            return [];
        }
        $physical = $this->registry()->columns($bread);
        $relations = collect($this->registry()->belongsToRows($bread, 'browse'))->keyBy('field');
        $many = collect($this->registry()->manyToManyRows($bread, 'browse'))->keyBy(fn (array $relation): string => $relation['row']->field);
        $columns = [];
        $select = ['id'];
        foreach ($this->registry()->rows($bread) as $row) {
            if (! $row->browse || $row->type === 'password') {
                continue;
            }
            if (in_array($row->field, $physical, true)) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => $row->type];
                $select[] = $row->field;
            } elseif ($row->type === 'relationship' && $relations->has($row->field)) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'relationship'];
                $select[] = $details['column'];
            } elseif ($row->type === 'relationship' && $many->has($row->field)) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'relationship'];
            } elseif ($row->type === 'adv_media_files' && $model instanceof HasMedia) {
                $columns[] = ['field' => $row->field, 'label' => $row->display_name ?: $row->field, 'type' => 'adv_media_files'];
            }
        }
        if (! collect($columns)->contains(fn (array $column): bool => $column['field'] === 'id')) {
            array_unshift($columns, ['field' => 'id', 'label' => 'ID', 'type' => 'text']);
        }

        $query = DB::table($bread->name)->select(array_values(array_unique($select)));
        if ($this->filterType !== null && in_array('id_type', $physical, true)) {
            $query->where('id_type', $this->filterType);
        }
        $searchable = collect($this->registry()->rows($bread))
            ->filter(fn (stdClass $row): bool => $row->browse && in_array($row->field, $physical, true) && in_array($row->type, ['text', 'text_area'], true))
            ->take(4)->pluck('field')->all();
        if ($this->search !== '' && $searchable !== []) {
            $search = $this->search;
            $query->where(function ($builder) use ($searchable, $search): void {
                foreach ($searchable as $field) {
                    $builder->orWhere($field, 'like', '%' . $search . '%');
                }
            });
        }

        $records = $query->orderByDesc('id')->paginate(25);
        $ids = $records->getCollection()->pluck('id')->all();
        if ($ids !== []) {
            $translated = method_exists($model, 'getTranslatableAttributes') ? $model->getTranslatableAttributes() : [];
            $visibleTranslated = collect($columns)->pluck('field')->intersect($translated)->all();
            if ($this->locale !== config('voyager.multilingual.default', 'en') && $visibleTranslated !== []) {
                $translations = DB::table('translations')->where('table_name', $bread->name)
                    ->where('locale', $this->locale)->whereIn('foreign_key', $ids)
                    ->whereIn('column_name', $visibleTranslated)->get();
                $lookup = $records->getCollection()->keyBy('id');
                foreach ($translations as $translation) {
                    $record = $lookup->get($translation->foreign_key);
                    if ($record) {
                        $record->{$translation->column_name} = $translation->value;
                    }
                }
            }
            foreach ($relations as $field => $row) {
                if (! collect($columns)->contains(fn (array $column): bool => $column['field'] === $field)) {
                    continue;
                }
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $keys = $records->getCollection()->pluck($details['column'])->filter()->unique()->all();
                $labels = $keys === [] ? collect() : DB::table($details['table'])->whereIn($details['key'], $keys)
                    ->pluck($details['label'], $details['key']);
                foreach ($records as $record) {
                    $record->{$field} = $labels->get($record->{$details['column']});
                }
            }
            foreach ($many as $field => $relation) {
                if (! collect($columns)->contains(fn (array $column): bool => $column['field'] === $field)) {
                    continue;
                }
                $details = $relation['details'];
                $labels = DB::table($details['pivot_table'] . ' as pivot')
                    ->join($details['table'] . ' as related', 'related.id', '=', 'pivot.' . $relation['targetKey'])
                    ->whereIn('pivot.' . $relation['sourceKey'], $ids)
                    ->get(['pivot.' . $relation['sourceKey'] . ' as source_id', 'related.' . $details['label'] . ' as label'])
                    ->groupBy('source_id');
                foreach ($records as $record) {
                    $record->{$field} = $labels->get($record->id)?->pluck('label')->implode(', ') ?: null;
                }
            }
            if ($model instanceof HasMedia && DatabaseSchema::hasTable('media')) {
                $mediaColumns = collect($columns)->where('type', 'adv_media_files')->pluck('field')->all();
                if ($mediaColumns !== []) {
                    $mediaRecords = $model->newQuery()->with('media')->whereKey($ids)->get()->keyBy($model->getKeyName());
                    foreach ($records as $record) {
                        foreach ($mediaColumns as $field) {
                            $record->{$field} = $mediaRecords->get($record->id)?->getMedia($field)->map->getUrl()->all() ?: [];
                        }
                    }
                }
            }
        }

        return ['columns' => $columns, 'rows' => $records];
    }

    private function fillRecord($record): void
    {
        $bread = $this->bread();
        $values = [];
        foreach ($this->registry()->editableRows($bread, 'edit') as $row) {
            if ($this->locale === config('voyager.multilingual.default', 'en')) {
                $value = $record->getAttribute($row->field);
                $values[$row->field] = match ($row->type) {
                    'password' => null,
                    'multiple_images', 'media_picker' => json_decode((string) $value, true) ?: [],
                    'multiple_checkbox' => array_keys(json_decode((string) $value, true) ?: []),
                    default => $value,
                };
            } elseif (method_exists($record, 'getTranslatableAttributes') && in_array($row->field, $record->getTranslatableAttributes(), true)) {
                $values[$row->field] = DB::table('translations')->where([
                    'table_name' => $bread->name, 'column_name' => $row->field,
                    'foreign_key' => $record->getKey(), 'locale' => $this->locale,
                ])->value('value');
            }
        }
        if ($this->locale === config('voyager.multilingual.default', 'en')) {
            foreach ($this->registry()->belongsToRows($bread, 'edit') as $row) {
                $details = json_decode($row->details ?: '{}', true) ?: [];
                $values[$details['column']] = $record->getAttribute($details['column']);
            }
            foreach ($this->registry()->manyToManyRows($bread, 'edit') as $relation) {
                $details = $relation['details'];
                $values['__pivot_' . $relation['row']->id] = DB::table($details['pivot_table'])
                    ->where($relation['sourceKey'], $record->getKey())
                    ->pluck($relation['targetKey'])->all();
            }
        }
        $this->form->fill($values);
    }

    private function fillMediaProperties($record): void
    {
        $this->mediaProperties = [];
        if (! $record instanceof HasMedia) {
            return;
        }
        foreach ($this->registry()->rows($this->bread()) as $row) {
            if ($row->type !== 'adv_media_files' || ! $row->edit) {
                continue;
            }
            $details = json_decode($row->details ?: '{}', true) ?: [];
            $keys = array_merge(['title', 'alt'], array_keys($details['extra_fields'] ?? []));
            foreach ($record->getMedia($row->field) as $media) {
                foreach ($keys as $key) {
                    $this->mediaProperties[$media->id][$key] = $media->getCustomProperty($key);
                }
            }
        }
    }

    private function mediaRecord(string $field): HasMedia
    {
        $bread = $this->requireBread('edit');
        abort_unless($this->editing && $this->recordId !== null, 409);
        $allowed = collect($this->registry()->rows($bread))->contains(fn (stdClass $row): bool => $row->type === 'adv_media_files' && $row->edit && $row->field === $field);
        abort_unless($allowed, 403);
        $record = $this->registry()->model($bread)?->newQuery()->findOrFail($this->recordId);
        abort_unless($record instanceof HasMedia, 409);

        return $record;
    }

    private function requireBread(string $action): stdClass
    {
        $bread = $this->bread();
        abort_unless($bread && ! $this->registry()->isDedicated($bread) && $this->registry()->permitted($bread, $action), 403);

        return $bread;
    }

    private function registry(): BreadRegistry
    {
        return app(BreadRegistry::class);
    }
}
