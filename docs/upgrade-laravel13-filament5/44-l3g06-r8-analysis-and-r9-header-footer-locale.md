# 44. L3-G06: анализ LTM R8 и targeted Batch R9

Дата: 16.07.2026. Статус: **R8 и R9 выполнены; `header_footer_new` value mismatch подтверждён во всех 8 locale; продолжение — opaque-key R10**.

## 1. Exact R8 result

| Проверка | Результат |
|---|---:|
| Groups | 46 |
| Rows | 21 450 |
| Count/NULL match | 46/46 |
| Logical key root match | 46/46 |
| Key+value root match | 45/46 |
| Full root match | 45/46 |
| Единственная divergent group | `header_footer_new` |

Exact matrix: [appendix-ltm-group-delta-r8.csv](appendix-ltm-group-delta-r8.csv).

Production execution: 235 ms, peak memory 40 MiB, без writes.

## 2. Доказанный вывод

1. В 45 группах, содержащих 21 210 строк, production/local совпадают по logical keys, values и statuses.
2. В `header_footer_new` совпадают 240 rows, 0 NULL и весь logical key set, но value/full roots различаются.
3. Поэтому DB reconciliation сужен с 21 450 строк до одной функциональной группы из 240 строк.
4. Совпадение DB-групп `account_new` и `mail` не отменяет R5 file delta: PHP-файлы этих групп отличаются на live filesystem. Это независимые слои и признак того, что DB Translation Manager не является единственным/автоматическим source of truth для runtime-файлов.
5. Publish/import/overwrite остаются запрещены до сопоставления DB и PHP file layers и owner decision.

## 3. Назначение R9

R9 разбивает только `header_footer_new` на восемь locale cohorts. Он покажет, в каких языках из `de|ee|en|et|lt|lv|pl|ru` действительно отличаются values. Ключи и тексты не выводятся; запрос читает только 240 строк.

## 4. Batch R9 — `header_footer_new` по locale

Выполнить на production от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LTM HEADER_FOOTER LOCALE HASH R9 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

sudo -u admin "$PHP" <<'PHP'
<?php

$root = getcwd();
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function canonical_part($value)
{
    if ($value === null) {
        return "N;";
    }

    $string = (string) $value;
    return "S" . strlen($string) . ":" . $string . ";";
}

function finish_cohort(&$result, &$current, &$keyHash, &$valueHash, &$fullHash, $count, $nulls)
{
    if ($current === null) {
        return;
    }

    $result[] = [
        'locale' => $current['locale'],
        'group' => 'header_footer_new',
        'row_count' => $count,
        'null_values' => $nulls,
        'key_root_sha256' => hash_final($keyHash),
        'key_value_root_sha256' => hash_final($valueHash),
        'key_value_status_root_sha256' => hash_final($fullHash),
    ];
}

$started = microtime(true);
$result = [];
$current = null;
$keyHash = null;
$valueHash = null;
$fullHash = null;
$count = 0;
$nulls = 0;

$cursor = DB::table('ltm_translations')
    ->select(['id', 'locale', 'group', 'key', 'value', 'status'])
    ->where('group', 'header_footer_new')
    ->orderBy('locale')
    ->orderBy('key')
    ->orderBy('id')
    ->cursor();

foreach ($cursor as $row) {
    $cohort = canonical_part($row->locale);

    if ($current === null || $current['canonical'] !== $cohort) {
        finish_cohort($result, $current, $keyHash, $valueHash, $fullHash, $count, $nulls);
        $current = [
            'canonical' => $cohort,
            'locale' => $row->locale,
        ];
        $keyHash = hash_init('sha256');
        $valueHash = hash_init('sha256');
        $fullHash = hash_init('sha256');
        $count = 0;
        $nulls = 0;
    }

    $identity = $cohort . canonical_part($row->group) . canonical_part($row->key);
    $value = canonical_part($row->value);
    $status = canonical_part($row->status);

    hash_update($keyHash, $identity);
    hash_update($valueHash, $identity . $value);
    hash_update($fullHash, $identity . $value . $status);
    $count++;
    $nulls += $row->value === null ? 1 : 0;
}

finish_cohort($result, $current, $keyHash, $valueHash, $fullHash, $count, $nulls);

echo json_encode([
    'algorithm' => 'LTM-LOCALE-GROUP-CANONICAL-v1',
    'target_group' => 'header_footer_new',
    'cohort_count' => count($result),
    'row_count' => array_sum(array_column($result, 'row_count')),
    'cohorts' => $result,
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;
PHP
```

Local dry-run: 8 cohorts × 30 rows = 240, 0 NULL, около 23 ms, peak memory 32 MiB, no writes. Full local roots: [appendix-ltm-header-footer-locale-baseline-r9.csv](appendix-ltm-header-footer-locale-baseline-r9.csv).

## 5. Интерпретация после R9

- key/value/full roots совпадают — locale DB cohort semantic-equal;
- key root совпадает, value root отличается — одинаковые keys, изменены values;
- value root совпадает, full root отличается — status-only delta;
- после локализации divergent locale следующий шаг использует per-key opaque fingerprints на restored staging или безопасный manifest без вывода raw values.

## 6. Stop conditions

Остановить при exception, заметной DB load/lock/wait или memory pressure. R9 не выполняет publish, update, import/export, cache/queue/Git/file actions.

## 7. Фактический результат R9

Production: 8 locale × 30 rows = 240, 0 NULL, elapsed=70 ms, peak memory=32 MiB. Key roots совпали для 8/8 locale; value/full roots отличаются для 8/8. Это доказывает одинаковый key set и value divergence в каждом locale, но не означает, что изменены все 30 keys. Exact key-level narrowing выполняет [45-l3g06-r9-analysis-and-r10-opaque-key.md](45-l3g06-r9-analysis-and-r10-opaque-key.md).
