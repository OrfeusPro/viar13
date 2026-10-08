<?php

namespace App\Filament\Bread;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait BreadRolePermissions
{
    public array $rolePermissionIds = [];

    public function rolePermissionGroups(): array
    {
        if ($this->bread()?->name !== 'roles' || ! $this->editing) {
            return [];
        }

        $titles = collect($this->registry()->types())->pluck('display_name_plural', 'name');
        $operations = ['browse' => 'Просмотр списка', 'read' => 'Просмотр записи',
            'edit' => 'Редактирование', 'add' => 'Создание', 'delete' => 'Удаление'];

        return DB::table('permissions')->orderBy('table_name')->orderBy('id')->get()
            ->groupBy(fn ($permission): string => (string) ($permission->table_name ?: ''))
            ->map(function ($permissions, $table) use ($titles, $operations): array {
                return [
                    'title' => $table === '' ? 'Система' : ($titles->get($table) ?: $table),
                    'table' => $table,
                    'permissions' => $permissions->map(function ($permission) use ($table, $operations): array {
                        $operation = $table !== '' ? str_replace('_' . $table, '', $permission->key) : '';

                        return ['id' => (string) $permission->id, 'key' => $permission->key,
                            'label' => $operations[$operation] ?? $permission->key];
                    })->all(),
                ];
            })->values()->all();
    }

    public function selectRolePermissions(?string $table, bool $selected): void
    {
        $bread = $this->requireBread($this->recordId ? 'edit' : 'add');
        abort_unless($bread->name === 'roles' && $this->editing, 409);
        $query = DB::table('permissions');
        if ($table !== null) {
            $table === '' ? $query->where(fn ($query) => $query->whereNull('table_name')->orWhere('table_name', ''))
                : $query->where('table_name', $table);
        }
        $ids = $query->pluck('id')->map(fn ($id): string => (string) $id)->all();
        $this->rolePermissionIds = $selected
            ? array_values(array_unique(array_merge($this->rolePermissionIds, $ids)))
            : array_values(array_diff($this->rolePermissionIds, $ids));
    }

    private function validatedRolePermissions(): ?array
    {
        if ($this->bread()?->name !== 'roles') {
            return null;
        }

        Validator::make(['rolePermissionIds' => $this->rolePermissionIds], [
            'rolePermissionIds' => ['array'],
            'rolePermissionIds.*' => ['integer', Rule::exists('permissions', 'id')],
        ], ['rolePermissionIds.*.exists' => 'Разрешение не найдено.',
            'rolePermissionIds.*.integer' => 'Некорректное разрешение.'])->validate();

        return array_values(array_unique(array_map('intval', $this->rolePermissionIds)));
    }
}
