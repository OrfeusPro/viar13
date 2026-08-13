# 41. L3-G06: exact language delta R5 и LTM canonical Batch R6

Дата: 16.07.2026. Статус: **R5 и R6 выполнены; R6 подтвердил semantic divergence; продолжение — R7**.

## 1. Exact R5 result

Production manifest: `LANG-FILE-MANIFEST-v1`, 332 entries. Local manifest: 334 entries.

| Класс | Exact count | Смысл |
|---|---:|---|
| Same path + same normalized content | 309 | можно переносить как подтверждённо одинаковый corpus |
| Same path + different normalized content | 23 | требуется production-preserving manual merge/owner decision |
| Production-only path | 0 | production не содержит неизвестных local source paths |
| Local-only path | 2 | `cn/jp google_reviews.php`; сохранить до owner decision |
| Union | 334 | полный known language path set |

Exact 25-row machine-readable matrix: [appendix-language-file-delta-r5.csv](appendix-language-file-delta-r5.csv).

### 1.1 Changed paths

По `de|ee|en|et|lt|lv|pl` отличаются ровно три файла на locale:

- `account_new.php`;
- `header_footer_new.php`;
- `mail.php`.

По `ru` отличаются два:

- `account_new.php`;
- `header_footer_new.php`.

`ru/mail.php` semantic-equal после LF normalization.

### 1.2 Local-only paths

- `resources/lang/cn/google_reviews.php`;
- `resources/lang/jp/google_reviews.php`.

Production-only paths отсутствуют.

### 1.3 Evidence integrity qualifier

Server сообщил mode-600 artifact size=36 733 и SHA-256 `5ba2d0ee...`. В чат был передан terminal/pasted-text wrapper; его byte representation отличается от исходного `/tmp` JSON, поэтому server file checksum не был независимо воспроизведён из attachment. Логическое содержимое JSON корректно распарсилось, algorithm/count=332 и все entries сравнились. Для formal evidence closure предпочтительно приложить исходный JSON-файл без terminal wrapper либо сохранить server artifact/checksum до sign-off.

## 2. Обязательная merge/disposition policy

1. Production versions 23 changed files сохраняются отдельным immutable artifact.
2. Local versions сохраняются вторым source; автоматический `git checkout`, copy или publish запрещён.
3. Content owner выбирает для каждого файла: `production wins|local wins|manual key merge|archive|retire`.
4. Для `mail.php` нужен Mail/Business UAT; для header/account — public/account UI UAT по locale.
5. Merge сравнивает PHP array keys и values, а не строки/порядок/formatting.
6. Итоговый artifact проходит PHP lint, duplicate/missing-key report и visual/functional locale UAT.
7. Один versioned publisher активирует artifact атомарно; Translation Manager/custom editor одновременно не пишут.

## 3. Следующая причина для R6

`ltm_translations` имеет одинаковые counts/status cohorts локально и на production, но production newest update позже на 24 дня. R6 сравнивает logical key set, key+value и key+value+status без вывода key/value и без timestamps.

Локальный dry-run на PHP 7.4/Laravel 6:

- rows=21 450;
- elapsed≈188 ms;
- peak memory≈40 MiB;
- никаких writes.

## 4. Batch R6 — LTM canonical aggregate roots

Выполнить от root одним блоком:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LTM CANONICAL HASH R6 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

sudo -u admin "$PHP" <<'PHP'
<?php

$root = getcwd();
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function canonical_part($value) {
    if ($value === null) {
        return "N;";
    }

    $string = (string) $value;
    return "S" . strlen($string) . ":" . $string . ";";
}

$started = microtime(true);
$all = hash_init('sha256');
$values = hash_init('sha256');
$keys = hash_init('sha256');
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
    $identity = canonical_part($row->locale) . canonical_part($row->group) . canonical_part($row->key);
    $value = canonical_part($row->value);
    $status = canonical_part($row->status);

    hash_update($keys, $identity);
    hash_update($values, $identity . $value);
    hash_update($all, $identity . $value . $status);
    $count++;
    $nulls += $row->value === null ? 1 : 0;
}

echo json_encode([
    'algorithm' => 'LTM-CANONICAL-v1',
    'row_count' => $count,
    'null_values' => $nulls,
    'key_root_sha256' => hash_final($keys),
    'key_value_root_sha256' => hash_final($values),
    'key_value_status_root_sha256' => hash_final($all),
    'elapsed_ms' => (int) round((microtime(true) - $started) * 1000),
    'peak_memory_bytes' => memory_get_peak_usage(true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

## 5. Local expected roots

```text
algorithm=LTM-CANONICAL-v1
row_count=21450
null_values=211
key_root_sha256=dfb2c66af0b557538797300f748a55e902e4946a6da05d3c4a4b5195a7258676
key_value_root_sha256=507abf6a71cd5397a3ca6b2636e30798e3d4350b7e10755f0ee849eb04b9555b
key_value_status_root_sha256=b16ab32f5e00bc066993658c477a6e4be2e081f1285cfdb2b5786b003298af51
```

## 6. Интерпретация

- key root совпал, value root отличается → те же logical keys, изменены values;
- key root отличается → missing/extra/different logical keys несмотря на одинаковый count;
- value root совпал, full root отличается → отличается только status;
- все три совпали → semantic LTM corpus одинаков, более поздний `updated_at` отражает resave/timestamp-only changes;
- любой mismatch требует per-key HMAC diff на restored staging, не вывода values на production.

## 7. Stop conditions

Остановить, если query создаёт заметную нагрузку, memory pressure, lock/wait или exception с raw SQL values. R6 не выполняет publish, update, cache/queue/Git/file actions.

## 8. Фактический результат R6

Production: rows=21 450, NULL=211, elapsed=225 ms, peak memory=40 MiB. `key_root_sha256` совпал с local, а `key_value_root_sha256` и `key_value_status_root_sha256` отличаются. Следовательно, logical key set одинаков, но semantic corpus не одинаков. Анализ и следующий безопасный Batch R7: [42-l3g06-r6-analysis-and-r7-ltm-locale.md](42-l3g06-r6-analysis-and-r7-ltm-locale.md).
