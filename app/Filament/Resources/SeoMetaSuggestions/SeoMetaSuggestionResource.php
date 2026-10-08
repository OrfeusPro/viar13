<?php

namespace App\Filament\Resources\SeoMetaSuggestions;

use App\Filament\Pages\VoyagerBreadEdit;
use App\Filament\Resources\SeoMetaSuggestions\Pages\ListSeoMetaSuggestions;
use App\Jobs\GenerateSeoMetaSuggestion;
use App\Models\SeoMetaSuggestion;
use App\Services\SeoMetaGeneration\SeoMetaContextResolver;
use App\Services\SeoMetaGeneration\SeoMetaModerationService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SeoMetaSuggestionResource extends Resource
{
    protected static ?string $model = SeoMetaSuggestion::class;
    protected static ?string $slug = 'seo-meta-suggestions';
    protected static ?string $navigationLabel = 'SEO Meta';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $pluralModelLabel = 'SEO Meta';

    public static function permitted(string $ability): bool
    {
        return auth('filament')->user()?->hasPermission($ability . '_seo_meta_suggestions') ?? false;
    }

    public static function canViewAny(): bool { return static::permitted('browse'); }
    public static function canCreate(): bool { return false; }

    public static function requireEditPermission(): void { abort_unless(static::permitted('edit'), 403); }

    public static function statuses(): array
    {
        return ['new' => 'Новая', 'generated' => 'Сгенерирована', 'pending' => 'Ожидает проверки',
            'approved' => 'Одобрена', 'applied' => 'Применена', 'rejected' => 'Отклонена', 'failed' => 'Ошибка'];
    }

    public static function targets(): array
    {
        return collect(config('seo_meta_generation.targets', []))->mapWithKeys(
            fn ($target, $class) => [$class => $target['label'] ?? class_basename($class)])->all();
    }

    public static function editUrl(SeoMetaSuggestion $record): ?string
    {
        $target = app(SeoMetaContextResolver::class)->targetForModel((string) $record->metaable_type);
        $slug = $target['voyager_slug'] ?? null;
        $bread = $slug ? app(\App\Filament\Bread\BreadRegistry::class)->type($slug) : null;
        if (! $bread || ! app(\App\Filament\Bread\BreadRegistry::class)->permitted($bread, 'edit')) {
            return null;
        }
        return VoyagerBreadEdit::getUrl(['type' => $slug, 'record' => $record->metaable_id]);
    }

    public static function table(Table $table): Table
    {
        return $table->extraAttributes(['class' => 'viar-admin-table viar-seo-table'])->recordActionsColumnLabel('Действия')->filtersLayout(FiltersLayout::AboveContent)
            ->header(fn () => view('filament.tables.seo-bulk'))
            ->filtersFormColumns(['default' => 1, 'md' => 3, 'xl' => 6])->defaultSort(fn (Builder $query) => $query->orderByDesc('updated_at')->orderByDesc('id'))->poll('15s')->columns([
            TextColumn::make('entity_title')->label('Сущность')->description(fn ($record) => $record->entity_label . ' #' . $record->metaable_id . ' · ' . $record->locale)
                ->searchable(['entity_title', 'entity_label', 'current_meta_title', 'current_meta_description',
                    'suggested_meta_title', 'suggested_meta_description', 'approved_meta_title',
                    'approved_meta_description', 'seo_keywords', 'error'])
                ->wrap()->color('primary')->url(fn ($record) => static::editUrl($record)),
            TextColumn::make('page_url')->label('Страница')->searchable()->wrap()->limit(80)->color('primary')
                ->getStateUsing(fn ($record) => $record->page_url ?: 'Без URL страницы')
                ->description(fn ($record) => $record->title_field.' / '.$record->description_field)
                ->url(fn ($record) => preg_match('~^https?://~i', (string) $record->page_url) ? $record->page_url : null)->openUrlInNewTab(),
            ViewColumn::make('current_meta')->label('Текущие meta')->view('filament.tables.columns.seo-current'),
            ViewColumn::make('proposal')->label('Предложение')->view('filament.tables.columns.seo-proposal'),
            TextColumn::make('status')->label('Статус')->badge()->formatStateUsing(fn ($state) => static::statuses()[$state] ?? $state)
                ->description(fn ($record) => $record->generated_at?->format('d.m.Y H:i')),
            TextColumn::make('updated_at')->label('Обновлено')->dateTime('d.m.Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
        ])->filters([
            SelectFilter::make('status')->label('Статус')->options(static::statuses()),
            SelectFilter::make('metaable_type')->label('Модель')->options(static::targets()),
            SelectFilter::make('locale')->label('Язык')->options(array_combine(app(SeoMetaContextResolver::class)->supportedLocales(), app(SeoMetaContextResolver::class)->supportedLocales())),
            SelectFilter::make('missing')->label('Пустые поля')->options(['title' => 'Без Meta Title', 'description' => 'Без Meta Description', 'any' => 'Без любого поля', 'both' => 'Без обоих полей'])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;
                    $empty = fn ($q, $field) => $q->whereNull($field)->orWhere($field, '');
                    return match ($value) {
                        'title' => $query->where(fn ($q) => $empty($q, 'current_meta_title')),
                        'description' => $query->where(fn ($q) => $empty($q, 'current_meta_description')),
                        'both' => $query->where(fn ($q) => $empty($q, 'current_meta_title'))->where(fn ($q) => $empty($q, 'current_meta_description')),
                        'any' => $query->where(fn ($q) => $q->where(fn ($q) => $empty($q, 'current_meta_title'))->orWhere(fn ($q) => $empty($q, 'current_meta_description'))),
                        default => $query,
                    };
                }),
            SelectFilter::make('has_error')->label('Ошибки')->options(['yes' => 'С ошибками', 'no' => 'Без ошибок'])
                ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                    'yes' => $query->whereNotNull('error')->where('error', '<>', ''),
                    'no' => $query->where(fn ($q) => $q->whereNull('error')->orWhere('error', '')), default => $query,
                }),
            Filter::make('url')->schema([TextInput::make('value')->label('URL содержит')])
                ->query(fn ($query, $data) => $query->when(filled($data['value'] ?? null), fn ($q) => $q->where('page_url', 'like', '%' . $data['value'] . '%'))),
        ])->recordActions([
            Action::make('generate')->label('Сгенерировать')->icon('heroicon-o-sparkles')->iconButton()->tooltip('Сгенерировать')->visible(fn () => static::permitted('edit'))
                ->view('filament.actions.seo-draft')
                ->schema([Textarea::make('seo_keywords')->label('Ключевые слова')->maxLength(1000)])
                ->fillForm(fn ($record, $livewire) => ['seo_keywords' => $livewire->draftFor($record)['seo_keywords']])
                ->action(function ($record, $data): void {
                    static::requireEditPermission();
                    $record->update(['seo_keywords' => trim((string) ($data['seo_keywords'] ?? '')) ?: null]);
                    static::perform($record, 'generate');
                }),
            Action::make('approve')->label('Редактировать и одобрить')->icon('heroicon-o-pencil-square')->iconButton()->tooltip('Редактировать и одобрить')->visible(fn ($record) => static::permitted('edit') && in_array($record->status, ['pending', 'generated', 'approved'], true))
                ->view('filament.actions.seo-draft')
                ->schema([
                    Textarea::make('meta_title')->label('Meta Title')->maxLength((int) config('seo_meta_generation.limits.meta_title_max', 60)),
                    Textarea::make('meta_description')->label('Meta Description')->maxLength((int) config('seo_meta_generation.limits.meta_description_max', 155)),
                    Textarea::make('seo_keywords')->label('Ключевые слова')->maxLength(1000),
                ])->fillForm(fn ($record, $livewire) => $livewire->draftFor($record))
                ->action(function ($record, $data): void { static::requireEditPermission(); app(SeoMetaModerationService::class)->approve($record, $data, auth('filament')->id()); }),
            Action::make('apply')->label('Применить')->icon('heroicon-o-arrow-down-tray')->iconButton()->tooltip('Применить')->color('success')->requiresConfirmation()
                ->visible(fn ($record) => static::permitted('edit') && $record->status === 'approved')
                ->action(function ($record, $livewire): void {
                    static::saveDraftKeywords($record, $livewire);
                    static::perform($record, 'apply');
                }),
            Action::make('reject')->label('Отклонить')->icon('heroicon-o-x-mark')->iconButton()->tooltip('Отклонить')->color('danger')->requiresConfirmation()
                ->visible(fn ($record) => static::permitted('edit') && in_array($record->status, ['pending', 'generated', 'approved', 'failed'], true))
                ->action(function ($record, $livewire): void {
                    static::saveDraftKeywords($record, $livewire);
                    static::perform($record, 'reject');
                }),
        ])->toolbarActions(collect(['generate' => 'Сгенерировать', 'approve' => 'Одобрить', 'apply' => 'Применить', 'reject' => 'Отклонить'])
            ->map(fn ($label, $operation) => BulkAction::make($operation)->label($label)->requiresConfirmation()->visible(fn () => static::permitted('edit'))
                ->extraAttributes(['class' => 'viar-seo-native-bulk'])
                ->deselectRecordsAfterCompletion()->action(function (Collection $records) use ($operation): void {
                    static::requireEditPermission(); $done = 0; $failed = 0;
                    foreach ($records as $record) {
                        $record = $record->fresh();
                        if ($operation === 'approve' && ! in_array($record->status, ['pending', 'generated'], true)) { $failed++; continue; }
                        if (static::perform($record, $operation, false)) { $done++; } else { $failed++; }
                    }
                    Notification::make()->title("Обработано: {$done}; пропущено или с ошибкой: {$failed}")->color($failed ? 'warning' : 'success')->send();
                }))->values()->all());
    }

    private static function saveDraftKeywords(SeoMetaSuggestion $record, ListSeoMetaSuggestions $livewire): void
    {
        static::requireEditPermission();
        $draft = $livewire->draftFor($record);
        $livewire->validate(['seoDrafts.'.$record->id.'.seo_keywords' => 'nullable|string|max:1000']);
        $record->update(['seo_keywords' => trim((string) ($draft['seo_keywords'] ?? '')) ?: null]);
    }

    public static function perform(SeoMetaSuggestion $record, string $operation, bool $notify = true): bool
    {
        static::requireEditPermission();
        try {
            $service = app(SeoMetaModerationService::class);
            match ($operation) {
                'generate' => GenerateSeoMetaSuggestion::dispatch((int) $record->id),
                'approve' => $service->approve($record, [], auth('filament')->id()),
                'apply' => $service->apply($record, auth('filament')->id()),
                'reject' => $service->reject($record, auth('filament')->id()),
                default => throw new \InvalidArgumentException('Неизвестное действие.'),
            };
            if ($operation === 'generate' && config('queue.default') === 'sync' && $record->fresh()?->status === 'failed') {
                if ($notify) { Notification::make()->title('Генерация не завершилась. Проверьте ошибку записи.')->danger()->send(); }
                return false;
            }
            $message = $operation === 'generate'
                ? (config('queue.default') === 'sync' ? 'Генерация выполнена' : 'Генерация отправлена в очередь') : 'Выполнено';
            if ($notify) { Notification::make()->title($message)->success()->send(); }
            return true;
        } catch (\Throwable $exception) {
            if (! $exception instanceof ValidationException) { report($exception); }
            $message = $exception instanceof ValidationException ? collect($exception->errors())->flatten()->first() : 'Не удалось выполнить действие. Проверьте источник и журнал ошибок.';
            if (! $exception instanceof ValidationException) { $record->update(['error' => $message]); }
            if ($notify) { Notification::make()->title($message)->danger()->send(); }
            return false;
        }
    }

    public static function getPages(): array { return ['index' => ListSeoMetaSuggestions::route('/')]; }
}
