<?php

namespace TCG\Voyager\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use TCG\Voyager\Models\Translation;

class Voyager
{
    public static function image(?string $path, ?string $default = ''): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return (string) $default;
        }
        if (Str::startsWith($path, ['http://', 'https://', '//', 'data:'])) {
            return $path;
        }

        return Storage::disk(config('voyager.storage.disk', 'public'))->url(ltrim(str_replace('\\', '/', $path), '/'));
    }

    public static function translatable(mixed $model): bool
    {
        return is_object($model) && method_exists($model, 'translatable') && $model->translatable();
    }

    public static function model(string $name): string
    {
        return self::modelClass($name);
    }

    public static function modelClass(string $name): string
    {
        if ($name === 'Translation') {
            return Translation::class;
        }

        $applicationModel = 'App\\Models\\'.$name;

        return class_exists($applicationModel) ? $applicationModel : 'TCG\\Voyager\\Models\\'.$name;
    }

    public static function setting(string $key, mixed $default = null): mixed
    {
        try {
            return \Illuminate\Support\Facades\DB::table('settings')->where('key', $key)->value('value') ?? $default;
        } catch (\Throwable) {
            return $default;
        }
    }
}
