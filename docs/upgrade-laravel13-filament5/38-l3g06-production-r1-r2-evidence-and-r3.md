# 38. L3-G06: production R1/R2 evidence и следующий Batch R3

Дата: 16.07.2026. Статус: **R1/R2/R3 выполнены read-only и проанализированы**. Следующий targeted R4: [39-l3g06-r3-analysis-and-r4.md](39-l3g06-r3-analysis-and-r4.md).

## 1. Вердикт R1

Production PHP 7.4.33 / Laravel 6.20.40 / MariaDB 10.11.16. DB name `admin_main`; секреты и row values не выводились.

### 1.1 Совпало с локальным snapshot

- все exact counts и ID boundaries основной catalogue/content/translation выборки;
- все восемь locale cohorts в `translations`;
- `translations` duplicate/null checks и composite unique index;
- `ltm_translations` count, locale/status distributions, groups, NULL и duplicate counts;
- catalogue pivot indexes и четыре orphan cohorts;
- Voyager metadata/settings/menu/content counts.

Совпадение counts означает хороший restored/local baseline, но не доказывает equality values/files.

### 1.2 Обнаруженный delta

- `ltm_translations.newest_updated`: production `2026-06-27 16:07:20`, local `2026-06-03 16:32:14`;
- production suggestions значительно отличаются от локального snapshot: ALT 13 131 и SEO meta 8 456;
- timestamps `translations` отличаются от локального вывода ровно на 3 часа, что похоже на session timezone representation и требует type/timezone canonicalization, а не вывода о data drift.

## 2. Вердикт R2

| Locale | Production files | Local files | Production bytes | Local bytes |
|---|---:|---:|---:|---:|
| `cn` | 1 | 2 | 20 114 | 20 508 |
| `de` | 42 | 42 | 259 303 | 263 364 |
| `ee` | 41 | 41 | 250 318 | 254 449 |
| `en` | 45 | 45 | 248 899 | 253 305 |
| `et` | 31 | 31 | 198 941 | 202 275 |
| `jp` | 2 | 3 | 23 346 | 23 853 |
| `lt` | 42 | 42 | 259 924 | 264 113 |
| `lv` | 43 | 43 | 243 290 | 247 278 |
| `pl` | 42 | 42 | 260 388 | 264 499 |
| `ru` | 43 | 43 | 336 963 | 341 309 |

Production total=332 files, local total=334. PHP lint production=PASS. Production raw tree SHA-256: `f92e8eafad568a2e0299f48410929105103452c8a7560658c9658b75010b07f5`; local raw tree SHA-256: `69a059cb5e7776cc7cea336a7e862525fd5247c10f9dc05581578e22291295f4`.

Все production byte totals меньше локальных при одинаковом количестве основных файлов. Это согласуется с возможным LF production vs CRLF local, но пока является гипотезой. R3 сравнивает raw и line-ending-normalized hashes, не раскрывая file content.

Локальные дополнительные paths:

- `resources/lang/cn/google_reviews.php` и `new_index.php` — production имеет только один из них;
- `resources/lang/jp/account.php`, `google_reviews.php`, `new_index.php` — production имеет только два из них.

До R3 неизвестно, какие именно два файла отсутствуют на production. Ничего не удалять и не копировать.

## 3. Batch R3 — normalized language hashes и `cn/jp` paths

Выполнить от root одним блоком. Команда читает только `resources/lang`, не выводит содержимое и не создаёт файлы:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LANGUAGE NORMALIZED HASH BASELINE R3 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

sudo -u admin "$PHP" <<'PHP'
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
$locales = [];
$allRawLines = '';
$allNormalizedLines = '';

foreach ($paths as $path) {
    $raw = file_get_contents($path);
    if ($raw === false) {
        fwrite(STDERR, "READ_FAILED\n");
        exit(2);
    }

    $normalized = str_replace(["\r\n", "\r"], "\n", $raw);
    $parts = explode('/', $path);
    $locale = $parts[2] ?? 'UNKNOWN';

    if (!isset($locales[$locale])) {
        $locales[$locale] = [
            'file_count' => 0,
            'bytes' => 0,
            'raw_lines' => '',
            'normalized_lines' => '',
            'paths' => [],
        ];
    }

    $rawLine = hash('sha256', $raw) . '  ' . $path . "\n";
    $normalizedLine = hash('sha256', $normalized) . '  ' . $path . "\n";

    $locales[$locale]['file_count']++;
    $locales[$locale]['bytes'] += strlen($raw);
    $locales[$locale]['raw_lines'] .= $rawLine;
    $locales[$locale]['normalized_lines'] .= $normalizedLine;
    $locales[$locale]['paths'][] = basename($path);
    $allRawLines .= $rawLine;
    $allNormalizedLines .= $normalizedLine;
}

$output = [
    'total_files' => count($paths),
    'raw_tree_sha256' => hash('sha256', $allRawLines),
    'normalized_tree_sha256' => hash('sha256', $allNormalizedLines),
    'locales' => [],
    'cn_paths' => [],
    'jp_paths' => [],
];

ksort($locales, SORT_STRING);
foreach ($locales as $locale => $data) {
    $output['locales'][$locale] = [
        'file_count' => $data['file_count'],
        'bytes' => $data['bytes'],
        'raw_tree_sha256' => hash('sha256', $data['raw_lines']),
        'normalized_tree_sha256' => hash('sha256', $data['normalized_lines']),
    ];

    if ($locale === 'cn' || $locale === 'jp') {
        sort($data['paths'], SORT_STRING);
        $output[$locale . '_paths'] = $data['paths'];
    }
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

## 4. Что прислать

Вывод R3 можно прислать полностью: он содержит только locale, counts, bytes, aggregate hashes и basenames `cn/jp`. Если неожиданно появится содержимое файлов или private path, вывод не отправлять.

## 5. Что будет после R3

1. Определить, объясняется ли main-locale byte/hash drift только CRLF/LF.
2. Классифицировать два local-only `cn/jp` path как `preserve source|production obsolete|target-only draft`, без удаления.
3. Зафиксировать production language artifact manifest.
4. Перейти к value-HMAC для `ltm_translations` на restored staging и найти реальные changes после локального snapshot.
