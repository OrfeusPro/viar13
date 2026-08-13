# 47. L3-G06: финальное доказательство R11 и PHP-array Batch R12

Дата: 16.07.2026. Статус: **DB technical reconciliation завершён; R12 файлового runtime-слоя выполнен; продолжение — targeted R13**.

## 1. Exact R11 result

Production: `LTM-OPAQUE-KEY-LOCALE-ROW-v1`, 8 rows, elapsed=77 ms, peak memory=32 MiB, no writes.

| Проверка | Результат |
|---|---:|
| Locale rows | 8 |
| Identity root match | 8/8 |
| NULL-state match | 8/8 |
| Status root match | 8/8 |
| Key+value root match | 0/8 |
| Full root match | 0/8 |

Exact matrix: [appendix-ltm-header-top-sale-locale-delta-r11.csv](appendix-ltm-header-top-sale-locale-delta-r11.csv).

## 2. Вывод по DB Translation Manager

- все 21 450 logical identities сохранены;
- status всех 21 450 строк совпадает;
- 21 442 value совпадают;
- ровно 8 value `header.top_sale` расходятся — по одному для `de|ee|en|et|lt|lv|pl|ru`;
- NULL-state drift в целевых восьми строках отсутствует;
- это контентный конфликт, а не потеря данных или повреждение структуры.

DB technical gate закрыт. Для восьми raw values остаётся owner disposition: `production wins|local wins|manual translation`. Решение выполняется в защищённом staging/backup artifact, не через production terminal и не массовым импортом таблицы.

## 3. Почему аудит переводов ещё не завершён

R11 относится только к таблице `ltm_translations`. R5 независимо обнаружил 23 изменённых PHP-файла:

- `account_new.php` — 8 locale;
- `header_footer_new.php` — 8 locale;
- `mail.php` — 7 locale, без `ru`;
- дополнительно local-only `cn/jp google_reviews.php` сохраняются до owner decision.

Совпадение DB-групп `account_new` и `mail` не доказывает равенство файлов, которые Laravel может загружать непосредственно в runtime. Поэтому следующий gate сравнивает возвращаемые PHP-массивы, а не текст, форматирование или порядок строк файлов.

## 4. Назначение R12

R12 для каждого из 23 changed files канонически сравнивает:

1. количество top-level и leaf keys;
2. вложенность и количество array nodes;
3. полный logical key set;
4. тип каждого leaf value;
5. semantic key+value corpus.

В output нет raw keys и переводов. Команда не запускает Laravel, не очищает cache, не пишет в файлы и не выполняет publish/import/export.

Local baseline: [appendix-language-php-array-baseline-r12.csv](appendix-language-php-array-baseline-r12.csv).

## 5. Batch R12 — canonical runtime arrays 23 changed files

Выполнить на production от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LANGUAGE PHP ARRAY CANONICAL R12 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

sudo -u admin "$PHP" <<'PHP'
<?php

$root = getcwd();
$localesWithMail = ['de', 'ee', 'en', 'et', 'lt', 'lv', 'pl'];
$paths = [];

foreach ($localesWithMail as $locale) {
    $paths[] = "resources/lang/{$locale}/account_new.php";
    $paths[] = "resources/lang/{$locale}/header_footer_new.php";
    $paths[] = "resources/lang/{$locale}/mail.php";
}

$paths[] = 'resources/lang/ru/account_new.php';
$paths[] = 'resources/lang/ru/header_footer_new.php';
sort($paths, SORT_STRING);

function canonical_part($value)
{
    if ($value === null) return "N;";
    if (is_bool($value)) return $value ? "B1;" : "B0;";
    if (is_int($value)) return "I" . $value . ";";
    if (is_float($value)) return "F" . sprintf('%.17g', $value) . ";";
    if (is_string($value)) return "S" . strlen($value) . ":" . $value . ";";
    throw new RuntimeException('Unsupported translation leaf type: ' . gettype($value));
}

function type_code($value)
{
    if ($value === null) return 'null';
    if (is_bool($value)) return 'bool';
    if (is_int($value)) return 'int';
    if (is_float($value)) return 'float';
    if (is_string($value)) return 'string';
    throw new RuntimeException('Unsupported translation leaf type: ' . gettype($value));
}

function walk_translation_array(array $source, array $path, array &$leaves, &$arrayNodes, &$maxDepth)
{
    $arrayNodes++;
    $maxDepth = max($maxDepth, count($path));

    if ($source === []) {
        $identity = canonical_part(count($path));
        foreach ($path as $segment) $identity .= canonical_part($segment);
        $leaves[] = [$identity, 'empty_array', 'A0;'];
        return;
    }

    foreach ($source as $key => $value) {
        $childPath = $path;
        $childPath[] = $key;

        if (is_array($value)) {
            walk_translation_array($value, $childPath, $leaves, $arrayNodes, $maxDepth);
            continue;
        }

        $identity = canonical_part(count($childPath));
        foreach ($childPath as $segment) $identity .= canonical_part($segment);
        $leaves[] = [$identity, type_code($value), canonical_part($value)];
        $maxDepth = max($maxDepth, count($childPath));
    }
}

$started = microtime(true);
$cohorts = [];

foreach ($paths as $relativePath) {
    $absolutePath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($absolutePath)) throw new RuntimeException('Required file not found: ' . $relativePath);

    $translations = require $absolutePath;
    if (!is_array($translations)) throw new RuntimeException('Translation file did not return array: ' . $relativePath);

    $leaves = [];
    $arrayNodes = 0;
    $maxDepth = 0;
    walk_translation_array($translations, [], $leaves, $arrayNodes, $maxDepth);
    usort($leaves, function ($a, $b) { return strcmp($a[0], $b[0]); });

    $keys = hash_init('sha256');
    $types = hash_init('sha256');
    $values = hash_init('sha256');
    $nulls = 0;

    foreach ($leaves as $leaf) {
        list($identity, $type, $value) = $leaf;
        hash_update($keys, $identity);
        hash_update($types, $identity . canonical_part($type));
        hash_update($values, $identity . $value);
        $nulls += $type === 'null' ? 1 : 0;
    }

    $cohorts[] = [
        'path' => $relativePath,
        'top_level_count' => count($translations),
        'leaf_count' => count($leaves),
        'null_values' => $nulls,
        'array_nodes' => $arrayNodes,
        'max_depth' => $maxDepth,
        'key_root_sha256' => hash_final($keys),
        'type_root_sha256' => hash_final($types),
        'key_value_root_sha256' => hash_final($values),
    ];
}

echo json_encode([
    'algorithm' => 'LANG-PHP-ARRAY-CANONICAL-v1',
    'file_count' => count($cohorts),
    'cohorts' => $cohorts,
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

Local dry-run: 23 files, elapsed=24 ms, peak memory=2 MiB, PHP 7.4 lint passed.

## 6. Интерпретация R12

- counts/key/type/value roots совпали — R5 difference был formatting/order-only;
- key root совпал, value root отличается — структура сохранена, отличаются тексты;
- key root отличается — есть missing/extra/nested key drift, требуется key-level R13;
- type root отличается — возможен runtime contract drift (`string|array|bool|null`);
- exception или missing file — остановить этап и ничего не исправлять автоматически.

## 7. Stop conditions

Остановить при exception, неожиданном типе leaf, отсутствующем файле или заметной нагрузке. Не выполнять cache clear, Translation Manager publish, copy, formatter или генератор языковых файлов.

## 8. Фактический результат R12

Production: 23 files, elapsed=14 ms, peak memory=2 MiB, no writes. Семь `mail.php` полностью semantic-equal; восемь `header_footer_new.php` имеют одинаковые keys/types и разные values; восемь `account_new.php` отличаются одним local-only leaf `orders.not_specified`, а дополнительный common-value mismatch после его исключения остаётся только в `ru`. Exact анализ и R13: [48-l3g06-r12-analysis-and-r13-targeted-files.md](48-l3g06-r12-analysis-and-r13-targeted-files.md).
