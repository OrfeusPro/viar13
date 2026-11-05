<?php

namespace App\Filament\Resources\ImageAltSuggestions;

use App\Filament\Resources\ImageAltSuggestions\Pages\ListImageAltSuggestions;
use App\Jobs\GenerateImageAltJob;
use App\Models\ImageAltSuggestion;
use App\Services\AltGeneration\AltSuggestionReverter;
use App\Services\AltGeneration\AltWebpInfo;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ImageAltSuggestionResource extends Resource
{
    protected static ?string $model = ImageAltSuggestion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'ALT изображений';

    protected static ?string $modelLabel = 'ALT-предложение';

    protected static ?string $pluralModelLabel = 'ALT-предложения';

    protected static ?int $navigationSort = 40;

    public static function canViewAny(): bool
    {
        return static::permitted('browse');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'viar-admin-table'])
            ->defaultPaginationPageOption(25)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(['default' => 1, 'md' => 3, 'xl' => 6])
            ->recordActionsColumnLabel('Действия')
            ->header(fn ($livewire) => view('filament.tables.alt-webp-stats', [
                'stats' => app(AltWebpInfo::class)->stats($livewire->getFilteredTableQuery()),
                'statusCounts' => array_replace(array_fill_keys(ImageAltSuggestion::statuses(), 0),
                    ImageAltSuggestion::query()->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status')->all()),
            ]))
            ->defaultSort(fn ($query) => $query->orderByDesc('updated_at')->orderByDesc('id'))
            ->searchUsing(function ($query, string $search): void {
                $search = trim($search);
                if ($search === '') { return; }
                $columns = ['page_url', 'image_path', 'current_alt', 'current_title', 'suggested_alt',
                    'suggested_title', 'approved_alt', 'approved_title', 'error'];
                if (\Illuminate\Support\Facades\Schema::hasColumn('image_alt_suggestions', 'error_message')) {
                    $columns[] = 'error_message';
                }
                $query->where(function ($query) use ($columns, $search): void {
                    foreach ($columns as $column) { $query->orWhere($column, 'like', '%' . $search . '%'); }
                });
            })
            ->columns([
                TextColumn::make('id')->label('ID')->sortable()->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('preview_url')->label('Изображение')->square()
                    ->url(fn ($record) => preg_match('~^https?://~i', (string) $record->preview_url) ? $record->preview_url : null)->openUrlInNewTab(),
                TextColumn::make('image_path')->label('Файл')->searchable()->limit(50)->copyable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('page_url')->label('Страница')->searchable()->limit(40)
                    ->url(fn ($record) => preg_match('~^https?://~i', (string) $record->page_url) ? $record->page_url : null)->openUrlInNewTab(),
                TextColumn::make('locale')->label('Язык')->sortable(),
                TextColumn::make('status')->label('Статус')->badge()->sortable(),
                TextColumn::make('imageable_type')->label('Источник')->formatStateUsing(fn ($state) => class_basename($state ?: 'Неизвестно'))
                    ->description(fn ($record) => '#'.$record->imageable_id.' · '.$record->field),
                ViewColumn::make('webp_info')->label('WebP')->view('filament.tables.columns.alt-webp'),
                ViewColumn::make('current_values')->label('Оригинал')->view('filament.tables.columns.alt-current'),
                ViewColumn::make('proposal')->label('Предложение')->view('filament.tables.columns.alt-proposal'),
                TextColumn::make('error')->label('Ошибка')->limit(40)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Обновлено')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('generated_at')->label('Сгенерировано')->dateTime('Y-m-d H:i')->placeholder('Никогда')->sortable(),
            ])
            ->filters([
                SelectFilter::make('webp')->label('WebP')->options(['1' => 'Есть WebP', '0' => 'Нет WebP'])
                    ->query(function ($query, array $data) {
                        if (in_array((string) ($data['value'] ?? ''), ['0', '1'], true)) {
                            app(AltWebpInfo::class)->filter($query, (string) $data['value'] === '1');
                        }
                        return $query;
                    }),
                SelectFilter::make('status')->label('Статус')->options(array_combine(ImageAltSuggestion::statuses(), ImageAltSuggestion::statuses())),
                SelectFilter::make('locale')->label('Язык')->options(fn (): array => array_combine(
                    app(\App\Services\AltGeneration\LocaleResolver::class)->supportedLocales(),
                    app(\App\Services\AltGeneration\LocaleResolver::class)->supportedLocales())),
                SelectFilter::make('imageable_type')->label('Модель')->options(fn (): array => ImageAltSuggestion::query()
                    ->whereNotNull('imageable_type')->distinct()->orderBy('imageable_type')->pluck('imageable_type', 'imageable_type')
                    ->map(fn ($class) => class_basename($class))->all()),
                Filter::make('page_url')->schema([TextInput::make('value')->label('URL содержит')])
                    ->query(fn ($query, array $data) => $query->when(filled($data['value'] ?? null),
                        fn ($query) => $query->where('page_url', 'like', '%' . trim($data['value']) . '%'))),
                SelectFilter::make('has_error')->label('Ошибки')->options(['1' => 'С ошибками', '0' => 'Без ошибок'])
                    ->query(fn ($query, array $data) => match ((string) ($data['value'] ?? '')) {
                        '1' => $query->whereNotNull('error')->where('error', '<>', ''),
                        '0' => $query->where(fn ($q) => $q->whereNull('error')->orWhere('error', '')),
                        default => $query,
                    }),
            ])
            ->recordActions([
                Action::make('details')->label('Детали')->icon('heroicon-o-eye')
                    ->modal()
                    ->modalHeading('Подробности ALT/TITLE')->modalSubmitAction(false)->modalCancelActionLabel('Закрыть')
                    ->modalContent(fn (ImageAltSuggestion $record) => view('filament.tables.alt-details', ['record' => $record])),
                Action::make('revert')->label('Откатить')->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn (ImageAltSuggestion $record): bool => static::permitted('edit') && $record->applyLogs()->exists())
                    ->requiresConfirmation()->modalDescription('Восстановить прежние ALT/TITLE по журналу применения?')
                    ->action(function (ImageAltSuggestion $record): void {
                        abort_unless(static::permitted('edit'), 403);
                        try {
                            app(AltSuggestionReverter::class)->revert($record);
                            Notification::make()->title('Прежние значения восстановлены')->success()->send();
                        } catch (\Illuminate\Validation\ValidationException $exception) {
                            Notification::make()->title(collect($exception->errors())->flatten()->first())->danger()->send();
                        }
                    }),
                Action::make('approve')
                    ->label('Одобрить')
                    ->view('filament.actions.alt-approve')
                    ->visible(fn (ImageAltSuggestion $record): bool => static::permitted('edit') && $record->status !== ImageAltSuggestion::STATUS_APPLIED)
                    ->fillForm(fn (ImageAltSuggestion $record): array => [
                        'alt' => $record->approved_alt ?? $record->suggested_alt,
                        'title' => $record->approved_title ?? $record->suggested_title,
                    ])
                    ->schema([
                        TextInput::make('alt')->label('ALT')->maxLength((int) config('alt_generation.limits.alt_max', 125)),
                        TextInput::make('title')->label('Title')->maxLength((int) config('alt_generation.limits.title_max', 70)),
                    ])
                    ->action(function (ImageAltSuggestion $record, array $data): void {
                        abort_unless(static::permitted('edit'), 403);
                        $record->approved_alt = $data['alt'] ?? null;
                        $record->approved_title = $data['title'] ?? null;
                        $record->setStatus(ImageAltSuggestion::STATUS_APPROVED);
                        $record->reviewed_by = auth()->id();
                        $record->reviewed_at = now();
                        $record->save();
                        Notification::make()->title('ALT одобрен')->success()->send();
                    }),
                Action::make('apply')
                    ->label('Применить')
                    ->visible(fn (ImageAltSuggestion $record): bool => static::permitted('edit') && $record->status === ImageAltSuggestion::STATUS_APPROVED)
                    ->requiresConfirmation()
                    ->action(function (ImageAltSuggestion $record): void {
                        abort_unless(static::permitted('edit'), 403);
                        $code = Artisan::call('alt:apply', [
                            '--ids' => (string) $record->id,
                            '--source' => 'admin',
                            '--applied-by' => (string) auth()->id(),
                        ]);
                        $record->refresh();
                        Notification::make()
                            ->title($code === 0 && $record->status === ImageAltSuggestion::STATUS_APPLIED
                                ? 'ALT применён' : 'ALT не применён — проверьте ошибку предложения')
                            ->color($code === 0 && $record->status === ImageAltSuggestion::STATUS_APPLIED ? 'success' : 'danger')
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Отклонить')
                    ->visible(fn (): bool => static::permitted('edit'))
                    ->requiresConfirmation()
                    ->action(function (ImageAltSuggestion $record): void {
                        abort_unless(static::permitted('edit'), 403);
                        $record->setStatus(ImageAltSuggestion::STATUS_REJECTED);
                        $record->reviewed_by = auth()->id();
                        $record->reviewed_at = now();
                        $record->save();
                    }),
                Action::make('regenerate')
                    ->label('Перегенерировать')
                    ->visible(fn (): bool => static::permitted('edit'))
                    ->requiresConfirmation()
                    ->action(function (ImageAltSuggestion $record): void {
                        abort_unless(static::permitted('edit'), 403);
                        if (!$record->imageable_type || !$record->imageable_id
                            || !is_subclass_of($record->imageable_type, Model::class)
                            || !$record->imageable_type::query()->whereKey($record->imageable_id)->exists()) {
                            Notification::make()->title('Не указан источник изображения')->danger()->send();
                            return;
                        }
                        $record->setStatus(ImageAltSuggestion::STATUS_NEW);
                        $record->error = null;
                        $record->save();
                        GenerateImageAltJob::dispatch((int) $record->id);
                    }),
            ])
            ->toolbarActions(collect([
                'approve' => 'Одобрить выбранное',
                'reject' => 'Отклонить выбранное',
                'regenerate' => 'Сгенерировать выбранное заново',
                'apply' => 'Применить выбранное',
            ])->map(fn (string $label, string $operation) => BulkAction::make($operation)
                ->label($label)->visible(fn (): bool => static::permitted('edit'))
                ->requiresConfirmation()->deselectRecordsAfterCompletion()
                ->action(function (Collection $records) use ($operation): void {
                    $done = static::performBulk($records, $operation);
                    $skipped = $records->count() - $done;
                    Notification::make()->title("Обработано: {$done}; пропущено: {$skipped}")
                        ->color($skipped ? 'warning' : 'success')->send();
                }))->values()->all());
    }

    public static function performBulk(Collection $records, string $operation): int
    {
        abort_unless(static::permitted('edit'), 403);
        if (! in_array($operation, ['approve', 'reject', 'regenerate', 'apply'], true)) {
            throw new \InvalidArgumentException('Неизвестное действие ALT.');
        }
        $ids = $records->pluck('id')->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();
        if ($ids === []) {
            return 0;
        }
        if ($operation === 'apply') {
            Artisan::call('alt:apply', [
                '--ids' => implode(',', $ids),
                '--status' => ImageAltSuggestion::STATUS_APPROVED,
                '--source' => 'admin',
                '--applied-by' => auth()->id(),
                '--limit' => count($ids),
            ]);
            return ImageAltSuggestion::query()->whereKey($ids)
                ->where('status', ImageAltSuggestion::STATUS_APPLIED)->count();
        }
        $done = 0;
        foreach (ImageAltSuggestion::query()->whereKey($ids)->get() as $record) {
            try {
                $record->setStatus(match ($operation) {
                    'regenerate' => ImageAltSuggestion::STATUS_NEW,
                    'approve' => ImageAltSuggestion::STATUS_APPROVED,
                    'reject' => ImageAltSuggestion::STATUS_REJECTED,
                });
            } catch (\InvalidArgumentException) {
                continue;
            }
            if ($operation === 'regenerate') {
                if (! $record->imageable_type || ! $record->imageable_id || ! $record->image_path
                    || ! is_subclass_of($record->imageable_type, Model::class)
                    || ! $record->imageable_type::query()->whereKey($record->imageable_id)->exists()) {
                    continue;
                }
                $record->error = null;
                $record->save();
                GenerateImageAltJob::dispatch((int) $record->id);
            } else {
                if ($operation === 'approve') {
                    $record->approved_alt = $record->suggested_alt;
                    $record->approved_title = $record->suggested_title;
                }
                $record->reviewed_by = auth()->id();
                $record->reviewed_at = now();
                $record->save();
            }
            $done++;
        }
        return $done;
    }

    public static function getPages(): array
    {
        return ['index' => ListImageAltSuggestions::route('/')];
    }

    public static function permitted(string $action): bool
    {
        $user = auth()->user();
        return $user && method_exists($user, 'hasPermission')
            && ($user->hasPermission($action . '_alt_suggestions')
                || $user->hasPermission($action . '_image_alt_suggestions'));
    }
}
