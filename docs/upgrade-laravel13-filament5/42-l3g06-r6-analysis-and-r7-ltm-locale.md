# 42. L3-G06: анализ LTM R6 и locale-level Batch R7

Дата: 16.07.2026. Статус: **R6 и R7 выполнены; corrected local baseline подтверждает value divergence во всех 8 locale; продолжение — R8**.

## 1. Результат R6

| Метрика | Production | Local | Результат |
|---|---:|---:|---|
| rows | 21 450 | 21 450 | совпадает |
| NULL values | 211 | 211 | совпадает |
| logical key root | `dfb2c66a…58676` | `dfb2c66a…58676` | совпадает |
| key + value root | `ec0b7747…374fd` | `507abf6a…9555b` | отличается |
| key + value + status root | `e53954fc…a5924` | `b16ab32f…af51` | отличается |

Production execution: 225 ms, peak memory 40 MiB, без writes.

```text
key_root_sha256=dfb2c66af0b557538797300f748a55e902e4946a6da05d3c4a4b5195a7258676
key_value_root_sha256=ec0b7747fc225d6e7562f20d50545ac5fd57c899af4fc63947619cf29f1374fd
key_value_status_root_sha256=e53954fcff86be4360c5b758db222e34a6904dd92799418a20d6edbe364a5924
```

## 2. Доказанный вывод

1. Полный набор `(locale, group, key)` одинаков: нет missing/extra logical keys.
2. Одинаковые count и NULL count не означают одинаковые переводы: canonical value root отличается.
3. Full root также отличается, но из-за уже доказанного value mismatch это само по себе не доказывает отдельные status changes. Для отделения value и status требуется декомпозиция.
4. Production corpus нельзя заменять локальным dump или автоматически публиковать из локального Translation Manager.
5. Production и local DB фиксируются как два источника; выбор значения выполняется только после locale/group diff и решения content owner.

REC-009 остаётся `PARTIAL`, а R-161/R-163/R-164 — открытыми.

## 3. Назначение R7

R7 делит тот же canonical stream на 8 locale cohorts (`de|ee|en|et|lt|lv|pl|ru`). Для каждого locale выводятся только count, NULL count и три SHA-256 root. Ключи и значения переводов не выводятся; БД, файлы, cache и queue не изменяются.

Если locale-level value root совпадает, его значения подтверждены. Если value root отличается — только этот locale переходит в следующий group-level R8. Это ограничивает объём дальнейшего анализа и не создаёт 308-строчный terminal dump без необходимости.

## 4. Batch R7 — LTM locale canonical roots

Выполнить на production от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LTM LOCALE CANONICAL HASH R7 ==='
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
    ->orderBy('locale')
    ->orderBy('group')
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
    'algorithm' => 'LTM-LOCALE-CANONICAL-v1',
    'cohort_count' => count($result),
    'row_count' => array_sum(array_column($result, 'row_count')),
    'cohorts' => $result,
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;
PHP
```

## 5. Local expected roots

| Locale | Rows / NULL | Key root | Key+value root | Full root |
|---|---:|---|---|---|
| de | 2960 / 0 | `792fe6f3…a89d1` | `b538df00…371be` | `700d5b55…aa1be` |
| ee | 3088 / 0 | `2e9c98e8…6ca66` | `f870daf8…efb54` | `ef4402a3…71928` |
| en | 2960 / 95 | `2df52ae2…9ea11` | `32efeb19…64a11` | `92a4f5a0…bfbd3` |
| et | 2519 / 1 | `39bbb515…7207f` | `1419872d…fead4` | `5452ab99…e11a1` |
| lt | 2431 / 0 | `df95d918…78fe3` | `d88dee3c…de192` | `3197ff8f…816ac` |
| lv | 2473 / 1 | `aaac45f5…dc41b` | `bb1cb135…4c5ff` | `6f1a5f81…0f5ec` |
| pl | 2431 / 0 | `f714c6c4…2445e` | `e8f856a5…76367a` | `41cd33b2…066ca` |
| ru | 2588 / 114 | `d7942dad…9466c` | `a2776812…ef906` | `803d39a1…18290` |

Полные roots: [appendix-ltm-locale-baseline-r7.csv](appendix-ltm-locale-baseline-r7.csv). Сравнение выполняется по полным 64 hex characters, не по сокращённому отображению таблицы.

## 5.1 Erratum и фактический результат R7

В первоначальном local baseline R7 component `group` ошибочно не вошёл в locale identity. Production-команда была корректной; после пересчёта local тем же production algorithm все 8 key roots совпали, а все 8 value/full roots отличаются. Повторный production R7 не нужен. Exact delta и следующий R8: [43-l3g06-r7-analysis-and-r8-ltm-group.md](43-l3g06-r7-analysis-and-r8-ltm-group.md).

## 6. Stop conditions

Остановить выполнение при exception, заметной DB load/lock/wait или memory pressure. Не запускать publish/import/export, не очищать cache/queue и не изменять `ltm_translations` до анализа результата R7.
