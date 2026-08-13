# 43. L3-G06: анализ LTM R7 и group-level Batch R8

Дата: 16.07.2026. Статус: **R7 и R8 выполнены; 45/46 groups совпали полностью; `header_footer_new` передан в targeted R9**.

## 1. Итог R7

| Проверка | Результат |
|---|---|
| Locale cohorts | 8 из 8 получены |
| Rows / NULL | совпадают для каждого locale |
| Logical key root | совпадает для каждого locale |
| Key + value root | отличается для каждого locale |
| Full root | отличается для каждого locale; отдельный status delta из этого не следует автоматически |

Exact production/local matrix: [appendix-ltm-locale-delta-r7.csv](appendix-ltm-locale-delta-r7.csv).

Вывод: во всех `de|ee|en|et|lt|lv|pl|ru` сохранён одинаковый набор `(group,key)`, но значения в production и local semantic-identical не являются. Нельзя выбрать local dump автоматическим winner.

## 2. Erratum локального baseline R7

Первоначальная версия `appendix-ltm-locale-baseline-r7.csv` была рассчитана локальным dry-run, в котором при переходе от group cohort к locale cohort из identity ошибочно выпал компонент `group`. Production-команда R7 содержала правильную identity `locale + group + key` и была выполнена корректно.

После обнаружения:

1. local baseline пересчитан тем же кодом, который выполнялся на production;
2. все восемь locale key roots совпали с уже полученным production JSON;
3. повторный запуск production R7 не требуется;
4. ошибочный baseline заменён, а correction зафиксирован в документации;
5. full corrected roots сохранены в [appendix-ltm-locale-baseline-r7.csv](appendix-ltm-locale-baseline-r7.csv).

## 3. Назначение R8

R8 группирует 21 450 строк по 46 уникальным `group` и сравнивает aggregate roots через все locale. Это позволит определить, какие функциональные словари изменены, не выводя ключи или значения и не создавая сразу 308 locale/group records.

После R8:

- groups с совпавшим value root считаются semantic-equal;
- groups с value mismatch переходят в точечный locale×group R9;
- отдельная status-проверка выполняется только после локализации value delta;
- publish/import/write остаются выключенными.

## 4. Batch R8 — LTM group canonical roots

Выполнить на production от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LTM GROUP CANONICAL HASH R8 ==='
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
        'group' => $current['group'],
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
    ->orderBy('group')
    ->orderBy('locale')
    ->orderBy('key')
    ->orderBy('id')
    ->cursor();

foreach ($cursor as $row) {
    $cohort = canonical_part($row->group);

    if ($current === null || $current['canonical'] !== $cohort) {
        finish_cohort($result, $current, $keyHash, $valueHash, $fullHash, $count, $nulls);
        $current = [
            'canonical' => $cohort,
            'group' => $row->group,
        ];
        $keyHash = hash_init('sha256');
        $valueHash = hash_init('sha256');
        $fullHash = hash_init('sha256');
        $count = 0;
        $nulls = 0;
    }

    $identity = canonical_part($row->locale) . $cohort . canonical_part($row->key);
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
    'algorithm' => 'LTM-GROUP-CANONICAL-v1',
    'cohort_count' => count($result),
    'row_count' => array_sum(array_column($result, 'row_count')),
    'cohorts' => $result,
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;
PHP
```

Local dry-run: 46 groups, 21 450 rows, около 250 ms, peak memory 40 MiB, без writes. Полные local roots: [appendix-ltm-group-baseline-r8.csv](appendix-ltm-group-baseline-r8.csv).

## 5. Передача результата

Прислать полный JSON. Если terminal/chat обрежет 46 cohorts, сохранить output в файл и приложить его без изменения содержимого. Не передавать `.env`, DB credentials или raw translation values.

## 6. Stop conditions

Остановить выполнение при exception, заметной DB load/lock/wait или memory pressure. R8 не выполняет publish, update, import/export, cache/queue/Git/file actions.

## 7. Фактический результат R8

Production: 46 groups, 21 450 rows, elapsed=235 ms, peak memory=40 MiB. Count/NULL/key roots совпали для 46/46 groups; value/full roots совпали для 45/46. Единственная divergent group — `header_footer_new` (240 rows, keys match). Exact analysis и следующий R9: [44-l3g06-r8-analysis-and-r9-header-footer-locale.md](44-l3g06-r8-analysis-and-r9-header-footer-locale.md).
