# 48. L3-G06: анализ PHP-array R12 и targeted Batch R13

Дата: 16.07.2026. Статус: **R12–R14 выполнены; technical translation scope закрыт; owner disposition и staging UAT остаются открытыми**.

## 1. Exact R12 result

Production: `LANG-PHP-ARRAY-CANONICAL-v1`, 23 files, elapsed=14 ms, peak memory=2 MiB, no writes.

| Класс | Файлы | Результат |
|---|---:|---|
| `mail.php` | 7 | полностью semantic-equal |
| `header_footer_new.php` | 8 | keys/types/counts equal, values differ |
| `account_new.php` | 8 | production=129 leaves, local=130; key/type/value roots differ |

Exact matrix: [appendix-language-php-array-delta-r12.csv](appendix-language-php-array-delta-r12.csv).

## 2. Что закрыто

- семь `mail.php` не требуют content merge: R5 file hash отличался из-за formatting/order representation;
- восемь `header_footer_new.php` имеют одинаковые 30 leaf paths, одинаковую вложенность и типы;
- все восемь `account_new.php` имеют одинаковые 36 top-level sections и одинаковую вложенность, но local содержит на один leaf больше;
- raw keys/values на production не выводились;
- копирование файлов, publish и regeneration не выполнялись.

## 3. Детерминированный анализ `account_new.php`

Для каждого locale локально перебраны 130 возможных single-leaf exclusions. Ровно один candidate на locale воспроизвёл production key root:

```text
local-only key path=orders.not_specified
opaque_key_id_sha256=c259ea975df99934d8c15582e33c3e68725c2896c37fb3e0bfc75c393f5815bb
```

После исключения `orders.not_specified`:

- production/local key root совпадает для всех 8 locale;
- remaining value root совпадает для `de|ee|en|et|lt|lv|pl`;
- remaining value root всё ещё отличается только для `ru`.

Exact matrix: [appendix-language-account-local-extra-analysis-r12.csv](appendix-language-account-local-extra-analysis-r12.csv).

Следовательно, семь account-файлов отличаются только наличием одного локального ключа; `ru/account_new.php` дополнительно имеет минимум одно value-расхождение среди общих 129 keys.

## 4. Назначение R13

R13 выполняет два независимых targeted сравнения:

1. 30 opaque leaf-key cohorts `header_footer_new.php` через 8 locale;
2. 36 opaque top-level group cohorts только `ru/account_new.php`.

Это ограничивает output 66 агрегатами вместо выгрузки 1 272 leaf rows. Raw key paths и values не выводятся.

Local baselines:

- [appendix-language-header-opaque-key-baseline-r13.csv](appendix-language-header-opaque-key-baseline-r13.csv);
- [appendix-language-ru-account-group-baseline-r13.csv](appendix-language-ru-account-group-baseline-r13.csv).

## 5. Batch R13 — header leaf keys и RU account groups

Выполнить на production от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LANGUAGE PHP TARGETED CANONICAL R13 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

sudo -u admin "$PHP" <<'PHP'
<?php

$root = getcwd();
$locales = ['de', 'ee', 'en', 'et', 'lt', 'lv', 'pl', 'ru'];

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

function flatten_array(array $source, array $path, array &$leaves)
{
    foreach ($source as $key => $value) {
        $child = $path;
        $child[] = $key;
        if (is_array($value)) {
            flatten_array($value, $child, $leaves);
            continue;
        }

        $pathIdentity = canonical_part(count($child));
        foreach ($child as $segment) $pathIdentity .= canonical_part($segment);
        $leaves[] = [
            'segments' => $child,
            'path_identity' => $pathIdentity,
            'type' => type_code($value),
            'value' => canonical_part($value),
        ];
    }
}

function finalize_cohorts(array $cohorts)
{
    $result = [];
    ksort($cohorts, SORT_STRING);
    foreach ($cohorts as $opaqueId => $rows) {
        usort($rows, function ($a, $b) { return strcmp($a['identity'], $b['identity']); });
        $keys = hash_init('sha256');
        $types = hash_init('sha256');
        $values = hash_init('sha256');
        foreach ($rows as $row) {
            hash_update($keys, $row['identity']);
            hash_update($types, $row['identity'] . canonical_part($row['type']));
            hash_update($values, $row['identity'] . $row['value']);
        }
        $result[] = [
            'opaque_id_sha256' => $opaqueId,
            'row_count' => count($rows),
            'key_root_sha256' => hash_final($keys),
            'type_root_sha256' => hash_final($types),
            'key_value_root_sha256' => hash_final($values),
        ];
    }
    return $result;
}

$started = microtime(true);
$headerCohorts = [];
foreach ($locales as $locale) {
    $path = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang'
        . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . 'header_footer_new.php';
    if (!is_file($path)) throw new RuntimeException('Required file not found: ' . $path);
    $translations = require $path;
    if (!is_array($translations)) throw new RuntimeException('Translation file did not return array: ' . $path);
    $leaves = [];
    flatten_array($translations, [], $leaves);
    foreach ($leaves as $leaf) {
        $opaque = hash('sha256', canonical_part('header_footer_new') . $leaf['path_identity']);
        $headerCohorts[$opaque][] = [
            'identity' => canonical_part($locale) . $leaf['path_identity'],
            'type' => $leaf['type'],
            'value' => $leaf['value'],
        ];
    }
}

$accountPath = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang'
    . DIRECTORY_SEPARATOR . 'ru' . DIRECTORY_SEPARATOR . 'account_new.php';
if (!is_file($accountPath)) throw new RuntimeException('Required file not found: ' . $accountPath);
$account = require $accountPath;
if (!is_array($account)) throw new RuntimeException('Translation file did not return array: ' . $accountPath);
$accountLeaves = [];
flatten_array($account, [], $accountLeaves);
$accountCohorts = [];
foreach ($accountLeaves as $leaf) {
    $top = (string) $leaf['segments'][0];
    $opaque = hash('sha256', canonical_part('account_new') . canonical_part($top));
    $accountCohorts[$opaque][] = [
        'identity' => canonical_part('ru') . $leaf['path_identity'],
        'type' => $leaf['type'],
        'value' => $leaf['value'],
    ];
}

$headers = finalize_cohorts($headerCohorts);
$accountGroups = finalize_cohorts($accountCohorts);
echo json_encode([
    'algorithm' => 'LANG-PHP-TARGETED-CANONICAL-v1',
    'header_footer_opaque_key_count' => count($headers),
    'header_footer_rows' => array_sum(array_column($headers, 'row_count')),
    'header_footer_cohorts' => $headers,
    'ru_account_opaque_group_count' => count($accountGroups),
    'ru_account_rows' => array_sum(array_column($accountGroups, 'row_count')),
    'ru_account_cohorts' => $accountGroups,
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

Local dry-run: 30 header cohorts/240 rows + 36 RU account cohorts/130 rows, elapsed=6 ms, peak memory=2 MiB, PHP 7.4 lint passed.

## 6. Интерпретация

- header cohort roots equal — ключ полностью совпадает во всех восьми locale;
- header key/type equal, value differs — конкретный leaf требует locale-level решения;
- RU account group roots equal — вся top-level section совпадает;
- RU account key root differs только в known `orders` group — объясняется `orders.not_specified`, затем требуется отдельная проверка common values этой группы;
- дополнительная RU account group с value mismatch — только она переходит в leaf-level R14.

## 7. Stop conditions

Остановить при exception, missing file, unexpected type или заметной нагрузке. Не выполнять copy, formatter, cache clear, Translation Manager publish, import/export или regeneration.

## 8. Фактический результат R13

Production: elapsed=3 ms, peak memory=2 MiB, no writes. Header/footer: 30/30 key/type/count matches, 29/30 value matches; единственный mismatch=`header.top_sale`. RU account: 35/36 groups fully equal; единственная divergent group=`orders`, production/local=44/45 rows. R14 затем доказал 44/44 identity/type matches, 43/44 value matches, local-only `orders.not_specified` и единственный common mismatch `orders.user_status.print_text`. Итог: [50-l3g06-r14-final-translation-reconciliation.md](50-l3g06-r14-final-translation-reconciliation.md).
