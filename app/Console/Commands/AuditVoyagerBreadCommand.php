<?php

namespace App\Console\Commands;

use App\Filament\Bread\BreadRegistry;
use App\Filament\Pages\VoyagerBread;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Throwable;

class AuditVoyagerBreadCommand extends Command
{
    protected $signature = 'bread:audit {--report= : Markdown report path within the project}';

    protected $description = 'Read-only audit of every Voyager BREAD type against the current database';

    public function handle(BreadRegistry $registry): int
    {
        $types = $registry->types();
        $roleId = DB::table('permissions')->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permissions.key', 'browse_admin')->value('permission_role.role_id');
        $user = $roleId ? User::query()->where('role_id', $roleId)->first() : null;
        if (! $user) {
            $this->error('No existing Voyager administrator with browse_admin was found.');

            return self::FAILURE;
        }
        auth('filament')->setUser($user);

        $lines = [
            '# Проверка всех типов Voyager BREAD',
            '',
            'Дата: ' . now()->toDateTimeString() . '. Источник: текущая БД viar13. Аудит только читает данные.',
            '',
            'Проверяются модель/таблица, поля и связи, разрешённые операции, фактический список и карточка первой записи.',
            'Создание, сохранение и удаление этим аудитом не выполняются.',
            '',
            '| Slug | Таблица | Записей | Список | Карточка | Добавление | Редактирование | Формы | Проблемы |',
            '| --- | --- | ---: | --- | --- | --- | --- | --- | --- |',
        ];
        $problems = 0;
        foreach ($types as $type) {
            $issues = [];
            try {
                $model = $registry->model($type);
                if (! $model) {
                    $issues[] = 'нет модели/таблицы';
                }
                $columns = $registry->columns($type);
                $rows = $registry->rows($type);
                $editable = $model ? $registry->editableRows($type, 'edit') : [];
                $addable = $model ? $registry->editableRows($type, 'add') : [];
                $validRelations = [];
                if ($model) {
                    foreach (['browse', 'read', 'add', 'edit'] as $operation) {
                        $validRelations[$operation] = array_merge(
                            array_map(static fn ($row): int => (int) $row->id, $registry->belongsToRows($type, $operation)),
                            array_map(static fn (array $relation): int => (int) $relation['row']->id, $registry->manyToManyRows($type, $operation)),
                        );
                    }
                }
                foreach ($rows as $row) {
                    if ($row->type !== 'relationship' && $row->type !== 'adv_media_files'
                        && ($row->browse || $row->read || $row->edit || $row->add)
                        && ! in_array($row->field, $columns, true)) {
                        $issues[] = 'нет колонки ' . $row->field;
                    }
                    if ($row->type === 'relationship' && $model && ! $registry->isDedicated($type)) {
                        foreach (['browse', 'read', 'add', 'edit'] as $operation) {
                            if ($row->{$operation} && ! in_array((int) $row->id, $validRelations[$operation], true)) {
                                $issues[] = 'недоступна связь ' . $row->field . ' (' . $operation . ')';
                            }
                        }
                    }
                    if ($row->type === 'adv_media_files' && $model && ! $model instanceof \Spatie\MediaLibrary\HasMedia) {
                        $issues[] = 'медиа-коллекция без HasMedia: ' . $row->field;
                    }
                }
                $count = $model ? DB::table($type->name)->count() : 0;
                $browse = $read = '—';
                $forms = [];
                $page = new VoyagerBread();
                $page->type = $type->slug;
                $page->locale = (string) config('voyager.multilingual.default', 'en');
                if ($model && ! $registry->isDedicated($type) && $registry->permitted($type, 'browse')) {
                    try {
                        $result = $page->records();
                        $browse = count($result['columns'] ?? []) . ' колонок';
                    } catch (Throwable $exception) {
                        $browse = 'ОШИБКА';
                        $issues[] = 'список: ' . $this->shortError($exception);
                    }
                }
                if ($model && $count > 0 && ! $registry->isDedicated($type) && $registry->permitted($type, 'read')) {
                    try {
                        $page->viewId = (int) DB::table($type->name)->orderByDesc('id')->value('id');
                        $read = count($page->viewedFields()) . ' полей';
                    } catch (Throwable $exception) {
                        $read = 'ОШИБКА';
                        $issues[] = 'карточка: ' . $this->shortError($exception);
                    }
                }
                if ($model && ! $registry->isDedicated($type) && $registry->permitted($type, 'add') && ! $registry->hasFormFields($type, 'add')) {
                    $issues[] = 'нет настроенных полей для создания; действие заблокировано';
                }
                if ($model && ! $registry->isDedicated($type) && $registry->permitted($type, 'edit') && ! $registry->hasFormFields($type, 'edit')) {
                    $issues[] = 'нет настроенных полей для изменения; действие заблокировано';
                }
                $add = $registry->permitted($type, 'add')
                    ? ($registry->hasFormFields($type, 'add') ? count($addable) . ' полей' : 'заблокировано') : 'нет права';
                $edit = $registry->permitted($type, 'edit')
                    ? ($registry->hasFormFields($type, 'edit') ? count($editable) . ' полей' : 'заблокировано') : 'нет права';
                if ($model && ! $registry->isDedicated($type) && $registry->permitted($type, 'browse')) {
                    if ($registry->permitted($type, 'add') && $registry->hasFormFields($type, 'add')) {
                        try {
                            Livewire::test(VoyagerBread::class, ['type' => $type->slug])->call('openCreate')->html();
                            $forms[] = 'add OK';
                        } catch (Throwable $exception) {
                            $forms[] = 'add ОШИБКА';
                            $issues[] = 'форма add: ' . $this->shortError($exception);
                        }
                    }
                    if ($count > 0 && $registry->permitted($type, 'edit') && $registry->hasFormFields($type, 'edit')) {
                        try {
                            $sampleId = (int) DB::table($type->name)->orderByDesc('id')->value('id');
                            Livewire::test(VoyagerBread::class, ['type' => $type->slug])->call('openEdit', $sampleId)->html();
                            $forms[] = 'edit OK';
                        } catch (Throwable $exception) {
                            $forms[] = 'edit ОШИБКА';
                            $issues[] = 'форма edit: ' . $this->shortError($exception);
                        }
                    }
                }
                if ($registry->isDedicated($type)) {
                    $browse = $read = $add = $edit = 'отдельный ресурс';
                }
            } catch (Throwable $exception) {
                $count = 0;
                $browse = $read = $add = $edit = 'ОШИБКА';
                $forms = [];
                $issues[] = $this->shortError($exception);
            }
            $issues = array_values(array_unique($issues));
            if ($issues !== []) {
                $problems++;
            }
            $lines[] = '| `' . $type->slug . '` | `' . $type->name . '` | ' . $count . ' | '
                . $browse . ' | ' . $read . ' | ' . $add . ' | ' . $edit . ' | ' . implode(', ', $forms) . ' | '
                . str_replace('|', '/', implode('; ', $issues)) . ' |';
            $this->line($type->slug . ': ' . ($issues === [] ? 'OK' : implode('; ', $issues)));
        }
        $lines[] = '';
        $lines[] = 'Итого: ' . count($types) . ' типов; ' . $problems . ' с проблемами/ограничениями.';
        $lines[] = '«OK» означает успешный read-only проход доступного списка/карточки и проверку метаданных, но не подтверждает сохранение записи.';

        if ($path = $this->option('report')) {
            $target = base_path($path);
            file_put_contents($target, implode(PHP_EOL, $lines) . PHP_EOL);
            $this->info('Report: ' . $target);
        }
        $this->info('Checked ' . count($types) . ' types; ' . $problems . ' with issues.');

        return $problems === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function shortError(Throwable $exception): string
    {
        return mb_substr(preg_replace('/\s+/', ' ', $exception->getMessage()), 0, 150);
    }
}
