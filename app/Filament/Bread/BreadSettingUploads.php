<?php

namespace App\Filament\Bread;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BreadSettingUploads
{
    public function store(UploadedFile $upload, object $setting, \Closure $created, string $statePath = 'data.replacement'): string
    {
        $details = app(BreadSettingFields::class)->details($setting);
        $directory = 'settings/'.date('FY');
        if ($setting->type === 'image') {
            return app(BreadImageUpload::class)->store($upload, 'public', $directory, $details, $statePath,
                fn (string $disk, array $paths) => $created($paths));
        }
        $name = $upload->getClientOriginalName();
        $extension = $upload->getClientOriginalExtension();
        if ($name !== basename($name) || preg_match('/[\\\\\/\x00-\x1F\x7F:]/', $name) || str_starts_with($name, '.')
            || in_array(strtolower($extension), ['php', 'phtml', 'phar', 'cgi', 'pl', 'asp', 'aspx', 'jsp', 'exe', 'bat', 'cmd', 'com', 'ps1', 'htaccess'], true)) {
            throw ValidationException::withMessages([$statePath => 'Недопустимое имя или тип файла.']);
        }
        return Cache::lock('setting-file:'.sha1($directory), 120)->block(5, function () use ($upload, $details, $name, $extension, $directory, $created, $statePath): string {
            $disk = Storage::disk('public');
            $base = pathinfo($name, PATHINFO_FILENAME);
            $number = 0;
            do {
                $stem = ! empty($details['preserveFileUploadName']) ? $base.($number ?: '') : Str::random(20);
                $filename = $stem.($extension === '' ? '' : '.'.$extension);
                $number++;
                $path = $directory.'/'.$filename;
            } while ($disk->exists($path));
            $created([$path]);
            if (! $upload->storeAs($directory, $filename, 'public')) {
                throw ValidationException::withMessages([$statePath => 'Не удалось сохранить файл.']);
            }
            return json_encode([['download_link' => $path, 'original_name' => $name]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });
    }
}
