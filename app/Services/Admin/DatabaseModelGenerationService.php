<?php

namespace App\Services\Admin;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DatabaseModelGenerationService
{
    public function preview(string $table): array
    {
        $namespace = trim((string) config('voyager.models.namespace', 'App\\Models\\'), '\\');
        $class = Str::studly(Str::singular($table));
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $table)
            || ! preg_match('/^App(?:\\\\[A-Za-z_][A-Za-z0-9_]*)+$/D', $namespace)
            || ! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $class)) {
            $this->fail('Недопустимое имя модели или namespace.');
        }
        $relative = str_replace('\\', '/', substr($namespace, 4)).'/'.$class.'.php';
        $path = app_path($relative);
        $source = "<?php\n\nnamespace {$namespace};\n\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass {$class} extends Model\n{\n}\n";
        try { token_get_all($source, TOKEN_PARSE); }
        catch (\ParseError) { $this->fail('Имя таблицы образует недопустимое имя PHP-модели.'); }
        if (file_exists($path) || is_link($path) || class_exists($namespace.'\\'.$class)) {
            $this->fail('Модель уже существует. Снимите флажок создания модели; существующий файл не будет заменён.');
        }
        for ($directory = dirname($path); $directory !== dirname(app_path()); $directory = dirname($directory)) {
            if (is_link($directory)) { $this->fail('Каталог модели не должен быть символической ссылкой.'); }
            if (file_exists($directory) && ! is_dir($directory)) { $this->fail('Путь модели занят файлом.'); }
            if (is_dir($directory) && ! is_writable($directory)) { $this->fail('Каталог модели недоступен для записи.'); }
            if ($directory === app_path()) { break; }
        }
        return ['class' => $namespace.'\\'.$class, 'path' => $path, 'source' => $source];
    }

    public function generate(string $table, array $expected): string
    {
        $plan = $this->preview($table);
        if ($plan !== $expected) { $this->fail('Настройки генерации изменились. Проверьте предварительный просмотр.'); }
        $directory = dirname($plan['path']);
        if (! is_dir($directory) && ! @mkdir($directory, 0755, true) && ! is_dir($directory)) {
            $this->fail('Не удалось создать каталог модели.');
        }
        // Exclusive creation also protects a file created after preview from being overwritten.
        $file = @fopen($plan['path'], 'x');
        if ($file === false) { $this->fail('Не удалось создать файл модели; существующие файлы не заменяются.'); }
        try {
            $written = fwrite($file, $plan['source']);
            if ($written !== strlen($plan['source'])) { $this->fail('Не удалось полностью записать модель. Проверьте созданный файл.'); }
        } finally { fclose($file); }
        return $plan['class'];
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['data.create_model' => $message]);
    }
}
