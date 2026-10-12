<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class BreadDeletionService
{
    public function snapshot(string $table): array
    {
        $user = auth('filament')->user();
        abort_unless($user && $user->hasPermission('browse_admin') && $user->hasPermission('browse_bread'), 403);
        abort_unless(in_array($table, array_column(app(DatabaseInspectionService::class)->tables(), 'name'), true), 404);
        $type = DB::table('data_types')->where('name', $table)->first();
        abort_unless($type, 404);
        $rows = DB::table('data_rows')->where('data_type_id', $type->id)->orderBy('id')->get()->all();
        $translations = Schema::hasTable('translations') ? DB::table('translations')->where(function ($query) use ($type, $rows): void {
            $query->where(fn ($query) => $query->where('table_name', 'data_types')->where('foreign_key', $type->id))
                ->orWhere(fn ($query) => $query->where('table_name', 'data_rows')->whereIn('foreign_key', array_column($rows, 'id')));
        })->orderBy('id')->get()->all() : [];
        $permissions = DB::table('permissions')->where('table_name', $table)->orderBy('id')->get()->all();
        $roles = DB::table('permission_role')->whereIn('permission_id', array_column($permissions, 'id'))
            ->orderBy('permission_id')->orderBy('role_id')->get()->all();
        return compact('type', 'rows', 'translations', 'permissions', 'roles');
    }

    public function fingerprint(array $snapshot): string { return hash('sha256', serialize($snapshot)); }

    public function delete(string $table, string $fingerprint): void
    {
        DB::transaction(function () use ($table, $fingerprint): void {
            $snapshot = $this->snapshot($table);
            if (! hash_equals($fingerprint, $this->fingerprint($snapshot))) {
                throw ValidationException::withMessages(['breadConfirmation' => 'Настройки BREAD изменились. Откройте подтверждение заново.']);
            }
            // Remove configuration only; physical records, models and files remain intact.
            if ($snapshot['translations'] !== []) {
                DB::table('translations')->whereIn('id', array_column($snapshot['translations'], 'id'))->delete();
            }
            DB::table('data_rows')->where('data_type_id', $snapshot['type']->id)->delete();
            DB::table('data_types')->where('id', $snapshot['type']->id)->delete();
            $permissionIds = array_column($snapshot['permissions'], 'id');
            DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        });
    }
}
