# 45. L3-G06: анализ targeted R9 и opaque-key Batch R10

Дата: 16.07.2026. Статус: **R9 и R10 выполнены; 29/30 keys совпали, divergent только `header.top_sale`; продолжение — final R11**.

## 1. Exact R9 result

| Проверка | Результат |
|---|---:|
| Locale cohorts | 8 |
| Rows | 240 |
| Rows per locale | 30 |
| NULL values | 0 |
| Logical key root match | 8/8 |
| Key+value root match | 0/8 |
| Full root match | 0/8 |

Exact matrix: [appendix-ltm-header-footer-locale-delta-r9.csv](appendix-ltm-header-footer-locale-delta-r9.csv).

Production execution: 70 ms, peak memory 32 MiB, без writes.

## 2. Доказанный вывод

1. В `de|ee|en|et|lt|lv|pl|ru` присутствуют одинаковые 30 keys группы `header_footer_new`.
2. Во всех восьми locale aggregate values отличаются production/local.
3. Missing/extra keys, NULL drift и locale-specific row loss не обнаружены.
4. Не доказано, что отличаются все 240 значений: один изменённый key в каждом locale уже меняет все восемь locale roots. Нужна key-level локализация.
5. Отдельный status delta из full mismatch не следует автоматически, поскольку value mismatch уже доказан.

## 3. Назначение R10

R10 группирует 240 строк по 30 logical keys через все восемь locale. Production output не содержит raw key names или translation values. Вместо имени ключа выводится стабильный opaque SHA-256 идентификатор `group + key`, который сопоставляется с локальным baseline.

После R10 только opaque keys с value mismatch переходят в финальное locale×key сравнение на restored staging или в контролируемый owner merge.

## 4. Batch R10 — opaque keys `header_footer_new`

Выполнить на production от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LTM HEADER_FOOTER OPAQUE KEY HASH R10 ==='
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

function finish_cohort(&$result, &$current, &$identityHash, &$valueHash, &$fullHash, $count, $nulls)
{
    if ($current === null) {
        return;
    }

    $result[] = [
        'opaque_key_id_sha256' => $current['opaque_key_id_sha256'],
        'row_count' => $count,
        'null_values' => $nulls,
        'identity_root_sha256' => hash_final($identityHash),
        'key_value_root_sha256' => hash_final($valueHash),
        'key_value_status_root_sha256' => hash_final($fullHash),
    ];
}

$started = microtime(true);
$result = [];
$current = null;
$identityHash = null;
$valueHash = null;
$fullHash = null;
$count = 0;
$nulls = 0;

$cursor = DB::table('ltm_translations')
    ->select(['id', 'locale', 'group', 'key', 'value', 'status'])
    ->where('group', 'header_footer_new')
    ->orderBy('key')
    ->orderBy('locale')
    ->orderBy('id')
    ->cursor();

foreach ($cursor as $row) {
    $cohort = canonical_part($row->key);

    if ($current === null || $current['canonical'] !== $cohort) {
        finish_cohort($result, $current, $identityHash, $valueHash, $fullHash, $count, $nulls);
        $current = [
            'canonical' => $cohort,
            'opaque_key_id_sha256' => hash('sha256', canonical_part($row->group) . $cohort),
        ];
        $identityHash = hash_init('sha256');
        $valueHash = hash_init('sha256');
        $fullHash = hash_init('sha256');
        $count = 0;
        $nulls = 0;
    }

    $identity = canonical_part($row->locale) . canonical_part($row->group) . $cohort;
    $value = canonical_part($row->value);
    $status = canonical_part($row->status);

    hash_update($identityHash, $identity);
    hash_update($valueHash, $identity . $value);
    hash_update($fullHash, $identity . $value . $status);
    $count++;
    $nulls += $row->value === null ? 1 : 0;
}

finish_cohort($result, $current, $identityHash, $valueHash, $fullHash, $count, $nulls);

echo json_encode([
    'algorithm' => 'LTM-OPAQUE-KEY-CANONICAL-v1',
    'target_group' => 'header_footer_new',
    'cohort_count' => count($result),
    'row_count' => array_sum(array_column($result, 'row_count')),
    'cohorts' => $result,
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

Local dry-run: 30 opaque keys × 8 locale = 240 rows, 0 NULL, около 21 ms, peak memory 32 MiB, no writes. Full local roots: [appendix-ltm-header-footer-opaque-key-baseline-r10.csv](appendix-ltm-header-footer-opaque-key-baseline-r10.csv).

## 5. Безопасность и интерпретация

- raw `key` и `value` не выводятся;
- opaque ID стабилен только для сопоставления одинакового `group+key`;
- identity root подтверждает одинаковый locale/key cohort;
- value root mismatch отмечает хотя бы одно изменённое locale value этого key;
- full-only mismatch при совпавшем value root означает status-only delta;
- никаких DB/file/cache/queue writes нет.

## 6. Stop conditions

Остановить при exception, заметной DB load/lock/wait или memory pressure. Не запускать Translation Manager publish, import/export или filesystem sync.

## 7. Фактический результат R10

Production: 30 opaque keys, 240 rows, elapsed=86 ms, peak memory=32 MiB. Identity roots совпали 30/30; value/full roots совпали 29/30. Единственный mismatch `5ad95f1f…f509a` локально однозначно отображается в `header.top_sale`. В совокупности с R9 это доказывает восемь divergent values — по одному на каждый locale. Финальная status/value row proof: [46-l3g06-r10-analysis-and-r11-final-row-proof.md](46-l3g06-r10-analysis-and-r11-final-row-proof.md).
