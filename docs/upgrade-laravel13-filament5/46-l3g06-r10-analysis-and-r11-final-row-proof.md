# 46. L3-G06: анализ R10 и final row-level Batch R11

Дата: 16.07.2026. Статус: **R10 и R11 выполнены; DB technical reconciliation завершён; восемь `header.top_sale` различаются только по value; продолжение — файловый R12**.

## 1. Exact R10 result

| Проверка | Результат |
|---|---:|
| Opaque key cohorts | 30 |
| Rows | 240 |
| Identity root match | 30/30 |
| Key+value root match | 29/30 |
| Full root match | 29/30 |
| Divergent opaque key | `5ad95f1f…f509a` |

Exact matrix: [appendix-ltm-header-footer-opaque-key-delta-r10.csv](appendix-ltm-header-footer-opaque-key-delta-r10.csv).

Production execution: 86 ms, peak memory 32 MiB, без writes.

## 2. Локальное сопоставление opaque ID

Детерминированный local lookup `SHA-256(canonical(group)+canonical(key))` сопоставил единственный divergent opaque ID ровно с одним key:

```text
group=header_footer_new
key=header.top_sale
opaque_key_id_sha256=5ad95f1fe1e525892d5792d675aa34d2cbd47b6e8e1f76c37785efba804f509a
```

Поскольку R9 доказал value mismatch в каждом из 8 locale, а R10 доказал, что остальные 29 keys совпадают через все locale, DB value delta составляет ровно 8 записей `header.top_sale` — по одной для `de|ee|en|et|lt|lv|pl|ru`.

## 3. Что уже закрыто

- 21 442 из 21 450 LTM rows подтверждены semantic-equal по values;
- все 21 450 logical keys сохранены;
- DB value reconciliation scope равен восьми строкам одного key;
- raw production values не выводились;
- PHP file delta R5 остаётся отдельным и не закрывается DB equality.

## 4. Назначение R11

R11 является финальным read-only доказательством по восьми строкам. Он выводит для каждого locale отдельные opaque fingerprints value и status. Это позволяет подтвердить:

1. value mismatch для каждой строки;
2. совпадает ли status отдельно от value;
3. отсутствие NULL и identity drift.

## 5. Batch R11 — final opaque locale rows

Выполнить на production от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LTM HEADER TOP SALE FINAL ROW HASH R11 ==='
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

$started = microtime(true);
$target = '5ad95f1fe1e525892d5792d675aa34d2cbd47b6e8e1f76c37785efba804f509a';
$result = [];

$cursor = DB::table('ltm_translations')
    ->select(['id', 'locale', 'group', 'key', 'value', 'status'])
    ->where('group', 'header_footer_new')
    ->orderBy('locale')
    ->orderBy('key')
    ->orderBy('id')
    ->cursor();

foreach ($cursor as $row) {
    $opaque = hash('sha256', canonical_part($row->group) . canonical_part($row->key));
    if (!hash_equals($target, $opaque)) {
        continue;
    }

    $identity = canonical_part($row->locale) . canonical_part($row->group) . canonical_part($row->key);
    $value = canonical_part($row->value);
    $status = canonical_part($row->status);

    $result[] = [
        'locale' => $row->locale,
        'opaque_key_id_sha256' => $opaque,
        'value_is_null' => $row->value === null,
        'identity_root_sha256' => hash('sha256', $identity),
        'key_value_root_sha256' => hash('sha256', $identity . $value),
        'status_root_sha256' => hash('sha256', $status),
        'key_value_status_root_sha256' => hash('sha256', $identity . $value . $status),
    ];
}

echo json_encode([
    'algorithm' => 'LTM-OPAQUE-KEY-LOCALE-ROW-v1',
    'target_group' => 'header_footer_new',
    'target_opaque_key_id_sha256' => $target,
    'row_count' => count($result),
    'rows' => $result,
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

Local dry-run: 8 rows, non-NULL, около 4 ms, peak memory 32 MiB. Full local roots: [appendix-ltm-header-top-sale-locale-baseline-r11.csv](appendix-ltm-header-top-sale-locale-baseline-r11.csv).

## 6. Фактический результат R11

Production: 8 rows, elapsed=77 ms, peak memory=32 MiB, no writes. Identity roots совпали 8/8, status roots совпали 8/8, NULL-state совпал 8/8; key+value/full roots отличаются 8/8. Следовательно, для всех `de|ee|en|et|lt|lv|pl|ru` различается только value, без identity/status/type drift.

Exact matrix: [appendix-ltm-header-top-sale-locale-delta-r11.csv](appendix-ltm-header-top-sale-locale-delta-r11.csv).

DB technical gate закрыт: 21 442/21 450 values совпадают, все 21 450 identities/status сохранены. Следующий независимый runtime gate и команда R12: [47-l3g06-r11-final-proof-and-r12-php-array.md](47-l3g06-r11-final-proof-and-r12-php-array.md).

## 7. После R11

Если identity/status roots совпадут, DB technical reconciliation считается локализованным полностью: только восемь values требуют owner decision. Raw values сравниваются уже не на production terminal, а в защищённом staging/backup artifact с журналом решения `production wins|local wins|manual translation`.

## 8. Stop conditions

Остановить при exception. R11 читает 240 строк, возвращает максимум 8 fingerprints и не выполняет writes/publish/import/export/cache/queue/file actions.
