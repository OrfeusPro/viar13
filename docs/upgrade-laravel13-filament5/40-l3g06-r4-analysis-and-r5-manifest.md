# 40. L3-G06: анализ language R4 и полный hash manifest R5

Дата: 16.07.2026. Статус: **R4/R5 выполнены read-only и проанализированы**. Exact result и следующий LTM R6: [41-l3g06-r5-exact-language-delta-and-r6-ltm.md](41-l3g06-r5-exact-language-delta-and-r6-ltm.md).

## 1. Вердикт R4

### 1.1 `cn/jp`

Общие production/local files совпали по normalized SHA-256:

- `cn/new_index.php`;
- `jp/account.php`;
- `jp/new_index.php`.

Local-only paths подтверждены окончательно:

- `resources/lang/cn/google_reviews.php`;
- `resources/lang/jp/google_reviews.php`.

Их нельзя автоматически добавлять на production или удалять из target source. Нужны runtime reference/code-history/Content owner decision.

### 1.2 Известные 16 production Git paths

Для `header_footer_new.php|mail.php` по `de|ee|en|et|lt|lv|pl|ru`:

- 15 normalized hashes отличаются от local;
- только `ru/mail.php` совпадает normalized hash `4d6baa97...`;
- production Git modified status для `ru/mail.php` может объясняться line endings/working-tree representation, а не semantic content.

Production versions 15 отличающихся файлов считаются source-of-truth hot edits до business merge; repository overwrite запрещён.

### 1.3 Остальной corpus

Для всех восьми основных locale excluded aggregate root не совпал с local даже после исключения `header_footer_new.php|mail.php`. Следовательно, в каждом locale существует минимум один дополнительный changed/missing/extra path либо content difference.

Минимально доказанная область: 15 known content differences + 2 local-only paths + минимум 8 дополнительных locale differences = не менее 25 file-level discrepancies. Это нижняя граница, не exact count.

## 2. Зачем нужен R5

R5 выводит полный normalized manifest `relative_path → SHA-256` для 332 production files. Raw content, PHP arrays и значения переводов не выводятся. Локальный manifest формируется тем же алгоритмом, после чего diff точно классифицирует:

- same path/same content;
- same path/different content;
- production-only path;
- local-only path.

## 3. Batch R5 — полный normalized file hash manifest

Команда создаёт временный mode-600 JSON только в `/tmp`, приложение не изменяет:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4
OUT=/tmp/viar-language-manifest-r5.json

cd "$ROOT" || exit 1
umask 077

echo '=== LANGUAGE FILE MANIFEST R5 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

sudo -u admin "$PHP" <<'PHP' > "$OUT"
<?php

$base = 'resources/lang';
$paths = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        $paths[] = str_replace('\\', '/', $file->getPathname());
    }
}

sort($paths, SORT_STRING);
$files = [];

foreach ($paths as $path) {
    $raw = file_get_contents($path);
    if ($raw === false) {
        fwrite(STDERR, "READ_FAILED\n");
        exit(2);
    }

    $normalized = str_replace(["\r\n", "\r"], "\n", $raw);
    $files[$path] = hash('sha256', $normalized);
}

$output = [
    'algorithm' => 'LANG-FILE-MANIFEST-v1',
    'file_count' => count($files),
    'files' => $files,
];

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP

sudo chmod 600 "$OUT"
sudo chown root:root "$OUT"

echo '=== MANIFEST METADATA ==='
sudo stat -c 'mode=%a owner=%U:%G bytes=%s path=%n' "$OUT"
sudo sha256sum "$OUT" | awk '{print "manifest_file_sha256=" $1}'

echo '=== MANIFEST CONTENT ==='
sudo cat "$OUT"
```

## 4. Что прислать

Лучше сохранить вывод `MANIFEST CONTENT` в текстовый файл и приложить его к сообщению. JSON содержит только относительные language paths и SHA-256, без текстов переводов.

После успешной передачи временный файл можно удалить отдельной командой:

```bash
sudo rm -f -- /tmp/viar-language-manifest-r5.json
```

Удалять его до подтверждения, что attachment прочитан, не обязательно. Команда затрагивает только явно указанный `/tmp` artifact.

## 5. Следующее действие после R5

1. Автоматически сравнить production manifest с local manifest тем же algorithm.
2. Получить exact lists/counts `same|changed|production-only|local-only` по locale.
3. Зафиксировать production hot edits как отдельный immutable source artifact.
4. Составить merge/disposition matrix для Content owner без автоматического overwrite.
5. Обновить клиентский план exact language delta и перейти к LTM value-HMAC на restored staging.
