# 49. L3-G06: анализ targeted R13 и final file Batch R14

Дата: 16.07.2026. Статус: **R14 выполнен; translation technical reconciliation закрыт; owner disposition и staging UAT остаются открытыми**.

## 1. Exact R13 result

Production: `LANG-PHP-TARGETED-CANONICAL-v1`, elapsed=3 ms, peak memory=2 MiB, no writes.

### Header/footer

| Проверка | Результат |
|---|---:|
| Opaque leaf cohorts | 30 |
| Locale rows | 240 |
| Count/key/type match | 30/30 |
| Value match | 29/30 |

Единственный divergent opaque ID `5eb761cf…b23470` локально однозначно сопоставлен с `header.top_sale`. С учётом R12, где каждый из восьми locale-файлов имел value mismatch, это доказывает ровно восемь файловых value-расхождений `header.top_sale`; остальные 232 header/footer values совпадают.

Exact matrix: [appendix-language-header-opaque-key-delta-r13.csv](appendix-language-header-opaque-key-delta-r13.csv).

### RU account

| Проверка | Результат |
|---|---:|
| Opaque top-level groups | 36 |
| Fully equal groups | 35 |
| Divergent groups | 1 |
| Production/local rows in divergent group | 44 / 45 |

Единственный divergent opaque group `94647cf3…ddfb3` локально однозначно сопоставлен с `orders`. Эта группа содержит known local-only `orders.not_specified`; R12 также доказал, что после его исключения value corpus общих ключей всё ещё отличается.

Exact matrix: [appendix-language-ru-account-group-delta-r13.csv](appendix-language-ru-account-group-delta-r13.csv).

## 2. Что уже не требует production-проверок

- семь `mail.php` semantic-equal;
- 29/30 header/footer keys semantic-equal через 8 locale;
- единственный header/footer conflict — восемь values `header.top_sale`;
- 35/36 RU account groups semantic-equal;
- `de|ee|en|et|lt|lv|pl account_new.php` после known local-only key имеют полностью равный common corpus.

## 3. Назначение R14

R14 является последним production hash batch для текущего файлового translation scope. Он сравнивает только leaf keys группы `orders` в `resources/lang/ru/account_new.php`:

- production ожидаемо 44 leaf cohorts;
- local baseline содержит 45 cohorts;
- production output не содержит raw key names или values;
- после сравнения будут определены local-only ID и точные common value mismatches.

Local baseline: [appendix-language-ru-account-orders-leaf-baseline-r14.csv](appendix-language-ru-account-orders-leaf-baseline-r14.csv).

## 4. Batch R14 — RU account/orders leaf fingerprints

Выполнить на production от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LANGUAGE RU ACCOUNT ORDERS LEAF R14 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

sudo -u admin "$PHP" <<'PHP'
<?php

$root = getcwd();
$path = $root . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang'
    . DIRECTORY_SEPARATOR . 'ru' . DIRECTORY_SEPARATOR . 'account_new.php';

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
        $identity = canonical_part('ru') . $pathIdentity;
        $leaves[] = [
            'opaque_leaf_id_sha256' => hash('sha256', canonical_part('account_new') . $pathIdentity),
            'row_count' => 1,
            'key_root_sha256' => hash('sha256', $identity),
            'type_root_sha256' => hash('sha256', $identity . canonical_part(type_code($value))),
            'key_value_root_sha256' => hash('sha256', $identity . canonical_part($value)),
        ];
    }
}

$started = microtime(true);
if (!is_file($path)) throw new RuntimeException('Required file not found: ' . $path);
$translations = require $path;
if (!is_array($translations) || !isset($translations['orders']) || !is_array($translations['orders'])) {
    throw new RuntimeException('Required array group not found: orders');
}

$leaves = [];
flatten_array($translations['orders'], ['orders'], $leaves);
usort($leaves, function ($a, $b) {
    return strcmp($a['opaque_leaf_id_sha256'], $b['opaque_leaf_id_sha256']);
});

echo json_encode([
    'algorithm' => 'LANG-PHP-RU-ACCOUNT-ORDERS-LEAF-v1',
    'target_file_opaque_sha256' => hash('sha256', 'resources/lang/ru/account_new.php'),
    'target_group_opaque_sha256' => hash('sha256', canonical_part('account_new') . canonical_part('orders')),
    'leaf_count' => count($leaves),
    'cohorts' => $leaves,
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

Local dry-run: 45 cohorts, elapsed=1 ms, peak memory=2 MiB, PHP 7.4 lint passed.

## 5. После R14

R14 выполнен 16.07.2026: production=44, local=45, common=44, production-only=0, local-only=1. Все 44 common rows совпали по count/key/type; 43/44 совпали по value.

Локальный deterministic mapping установил:

- local-only: `account_new.orders.not_specified`;
- common value mismatch: `account_new.orders.user_status.print_text`.

`orders.user_status.print_text` имеет прямой runtime consumer в `resources/views/theme/viar/account/order.blade.php:508`. Для `orders.not_specified` прямой статический consumer не найден, но dynamic lookup возможен, поэтому удаление без usage proof запрещено.

Отдельно сохраняются два local-only файла `cn/jp google_reviews.php`: группа `google_reviews` используется публичными views/controller, а CN/JP routing остаётся отдельным owner decision.

Exact delta: [appendix-language-ru-account-orders-leaf-delta-r14.csv](appendix-language-ru-account-orders-leaf-delta-r14.csv). Итог и owner matrix: [50-l3g06-r14-final-translation-reconciliation.md](50-l3g06-r14-final-translation-reconciliation.md).

Дополнительные production hash batch по текущему translation scope не нужны. Следующий этап — content owner disposition, versioned artifact и staging UAT.

## 6. Stop conditions

Остановить при exception, missing group/file, unexpected type или заметной нагрузке. Не выполнять copy, formatter, cache clear, Translation Manager publish, import/export или regeneration.
