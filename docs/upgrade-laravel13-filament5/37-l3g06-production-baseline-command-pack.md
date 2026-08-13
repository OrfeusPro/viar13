# 37. L3-G06: read-only production baseline command pack

Дата: 16.07.2026. Статус: **R1/R2 выполнены read-only и проанализированы**. Команды предназначались только для production `viarcanvas.com`; test/dev игнорировались по решению владельца. Результат и следующий Batch R3: [38-l3g06-production-r1-r2-evidence-and-r3.md](38-l3g06-production-r1-r2-evidence-and-r3.md).

## 1. Граница безопасности

Команды ниже:

- используют PHP 7.4 и Laravel bootstrap только для `SELECT`/metadata;
- не печатают `.env`, DB credentials, raw values переводов, заказов, чатов или PII;
- не выполняют DDL/DML, Artisan cache/config commands, queue retry/flush, publish, Git mutation или asset build;
- не создают файлы в application root;
- не хэшируют весь media/storage на первой итерации;
- вывод допускается прислать в чат полностью, кроме неожиданно появившихся raw values/paths — в таком случае вывод остановить и не отправлять.

## 2. Batch R1 — environment и DB/schema aggregates

Выполнить одним блоком от root:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== RECONCILIATION BASELINE R1 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

if [ ! -x "$PHP" ]; then
    echo 'PHP_7_4_NOT_FOUND'
    exit 1
fi

if [ ! -f artisan ] || [ ! -f vendor/autoload.php ] || [ ! -f bootstrap/app.php ]; then
    echo 'LARAVEL_ROOT_NOT_CONFIRMED'
    exit 1
fi

sudo -u admin "$PHP" -v | head -n 2
sudo -u admin "$PHP" artisan --version

sudo -u admin "$PHP" <<'PHP'
<?php

$root = getcwd();
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function one($sql, array $bindings = []) {
    $rows = DB::select($sql, $bindings);
    return $rows ? (array) $rows[0] : [];
}

function many($sql, array $bindings = []) {
    return array_map(function ($row) { return (array) $row; }, DB::select($sql, $bindings));
}

function quote_table($name) {
    return '`' . str_replace('`', '``', $name) . '`';
}

$tables = [
    'translations',
    'ltm_translations',
    'gallery_items',
    'gallery_categories',
    'gallery_sizes',
    'gallery_category_gallery_item',
    'gallery_items_ gallery_tag_sizes',
    'gallery_items_ gallery_tag_colors',
    'gallery_items_gallery_tag_rooms',
    'newhome_services',
    'header_menu',
    'menus',
    'menu_items',
    'settings',
    'pages',
    'blog_posts',
    'blog_categories',
    'data_types',
    'data_rows',
    'media',
    'image_alt_suggestions',
    'seo_meta_suggestions',
];

$result = [
    'meta' => one("SELECT VERSION() AS db_version, UTC_TIMESTAMP() AS db_utc, DATABASE() AS database_name"),
    'table_counts' => [],
];

foreach ($tables as $table) {
    $exists = one(
        "SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
        [$table]
    );

    if ((int) ($exists['n'] ?? 0) !== 1) {
        $result['table_counts'][] = ['table' => $table, 'status' => 'ABSENT'];
        continue;
    }

    $hasId = one(
        "SELECT COUNT(*) AS n FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'id'",
        [$table]
    );

    $select = ((int) ($hasId['n'] ?? 0) === 1)
        ? "COUNT(*) AS exact_count, MIN(id) AS min_id, MAX(id) AS max_id"
        : "COUNT(*) AS exact_count";

    $row = one("SELECT {$select} FROM " . quote_table($table));
    $row = ['table' => $table, 'status' => 'PRESENT'] + $row;
    $result['table_counts'][] = $row;
}

$result['translation_locales'] = many(
    "SELECT locale, COUNT(*) AS exact_count, MIN(created_at) AS oldest_created, MAX(updated_at) AS newest_updated " .
    "FROM translations GROUP BY locale ORDER BY locale"
);

$result['translation_duplicate_logical_keys'] = one(
    "SELECT COUNT(*) AS duplicate_groups FROM (" .
    "SELECT table_name, column_name, foreign_key, locale FROM translations " .
    "GROUP BY table_name, column_name, foreign_key, locale HAVING COUNT(*) > 1" .
    ") d"
);

$result['translation_nulls'] = one(
    "SELECT SUM(value IS NULL) AS null_values, SUM(updated_at IS NULL) AS null_updated_at FROM translations"
);

$result['ltm_locale_status'] = many(
    "SELECT locale, status, COUNT(*) AS exact_count FROM ltm_translations " .
    "GROUP BY locale, status ORDER BY locale, status"
);

$result['ltm_duplicate_logical_keys'] = one(
    "SELECT COUNT(*) AS duplicate_groups FROM (" .
    "SELECT locale, `group`, `key` FROM ltm_translations " .
    "GROUP BY locale, `group`, `key` HAVING COUNT(*) > 1" .
    ") d"
);

$result['ltm_nulls_and_groups'] = one(
    "SELECT SUM(value IS NULL) AS null_values, COUNT(DISTINCT `group`) AS exact_groups, " .
    "MIN(created_at) AS oldest_created, MAX(updated_at) AS newest_updated FROM ltm_translations"
);

$result['relevant_indexes'] = many(
    "SELECT TABLE_NAME AS table_name, INDEX_NAME AS index_name, NON_UNIQUE AS non_unique, " .
    "GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS columns_list " .
    "FROM information_schema.STATISTICS " .
    "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN " .
    "('translations','ltm_translations','settings','data_types','data_rows','gallery_items'," .
    "'gallery_category_gallery_item','gallery_items_ gallery_tag_sizes'," .
    "'gallery_items_ gallery_tag_colors','gallery_items_gallery_tag_rooms') " .
    "GROUP BY TABLE_NAME, INDEX_NAME, NON_UNIQUE ORDER BY TABLE_NAME, INDEX_NAME"
);

$orphanChecks = [
    'category_pivot_to_item' => [
        'gallery_category_gallery_item',
        "SELECT COUNT(*) AS exact_count FROM `gallery_category_gallery_item` p " .
        "LEFT JOIN `gallery_items` i ON i.id = p.gallery_item_id WHERE i.id IS NULL",
    ],
    'size_pivot_to_item' => [
        'gallery_items_ gallery_tag_sizes',
        "SELECT COUNT(*) AS exact_count FROM `gallery_items_ gallery_tag_sizes` p " .
        "LEFT JOIN `gallery_items` i ON i.id = p.gallery_item_id WHERE i.id IS NULL",
    ],
    'color_pivot_to_item' => [
        'gallery_items_ gallery_tag_colors',
        "SELECT COUNT(*) AS exact_count FROM `gallery_items_ gallery_tag_colors` p " .
        "LEFT JOIN `gallery_items` i ON i.id = p.gallery_item_id WHERE i.id IS NULL",
    ],
    'room_pivot_to_item' => [
        'gallery_items_gallery_tag_rooms',
        "SELECT COUNT(*) AS exact_count FROM `gallery_items_gallery_tag_rooms` p " .
        "LEFT JOIN `gallery_items` i ON i.id = p.gallery_item_id WHERE i.id IS NULL",
    ],
];

$result['catalogue_orphans'] = [];
foreach ($orphanChecks as $name => $check) {
    [$table, $sql] = $check;
    $exists = one(
        "SELECT COUNT(*) AS n FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
        [$table]
    );
    $result['catalogue_orphans'][$name] = ((int) ($exists['n'] ?? 0) === 1)
        ? one($sql)
        : ['status' => 'TABLE_ABSENT'];
}

$result['suggestion_status_locale'] = [
    'image_alt_suggestions' => many(
        "SELECT status, locale, COUNT(*) AS exact_count FROM image_alt_suggestions " .
        "GROUP BY status, locale ORDER BY status, locale"
    ),
    'seo_meta_suggestions' => many(
        "SELECT status, locale, COUNT(*) AS exact_count FROM seo_meta_suggestions " .
        "GROUP BY status, locale ORDER BY status, locale"
    ),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

## 3. Batch R2 — PHP language file summary

Этот блок читает только `resources/lang`, не выводит содержимое файлов и не создаёт manifest-файл:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LANGUAGE DIRECTORY COUNTS ==='
for dir in resources/lang/*; do
    [ -d "$dir" ] || continue
    locale=$(basename "$dir")
    files=$(find "$dir" -type f -name '*.php' | wc -l)
    bytes=$(find "$dir" -type f -name '*.php' -printf '%s\n' | awk '{s+=$1} END {print s+0}')
    printf '%s | files=%s | bytes=%s\n' "$locale" "$files" "$bytes"
done

echo '=== LANGUAGE TREE SHA256 ==='
find resources/lang -type f -name '*.php' -print0 \
  | sort -z \
  | xargs -0 sha256sum \
  | sha256sum \
  | awk '{print "language_tree_sha256=" $1}'

echo '=== PHP LINT SUMMARY ==='
TOTAL=$(find resources/lang -type f -name '*.php' | wc -l)
FAILED=$(find resources/lang -type f -name '*.php' -print0 \
  | xargs -0 -n1 "$PHP" -l 2>&1 \
  | grep -Evc '^No syntax errors detected in ')
printf 'php_files=%s\nlint_non_success_lines=%s\n' "$TOTAL" "$FAILED"
```

Примечание: `lint_non_success_lines=0` — ожидаемый результат. Если значение не ноль, не публиковать raw строки сразу: сначала проверить, не содержат ли пути клиентские/служебные данные; безопасно прислать только число и basename проблемного language file.

## 4. Как прислать результат

Можно прислать вывод Batch R1 и R2 одним текстом. Перед отправкой убедиться, что вывод содержит только:

- версии/UTC/database name;
- названия таблиц/indexes/locale/status;
- counts/min/max timestamps/IDs;
- orphan/duplicate/NULL counts;
- один aggregate SHA-256 языкового дерева;
- lint summary.

Не присылать `.env`, SQL row values, translation values, file contents, полный Git diff, private storage paths, provider payload или credentials.

## 5. Как будет использован результат

1. Production exact baseline заменит локальные/metadata estimates там, где они различаются.
2. Будут зафиксированы production duplicate/NULL/index/orphan gates.
3. Языковой manifest подтвердит все реально существующие directories и file corpus без чтения содержимого в чат.
4. После этого готовится staging runner для per-row HMAC/full anti-join; на production он не запускается до измерения IO/time на restored copy.

## 6. Stop conditions

Остановить блок и не продолжать, если:

- выбран не production root или не PHP 7.4;
- Laravel bootstrap выводит exception с credentials/raw SQL values;
- запрос заметно создаёт DB load/lock или сервер испытывает disk/memory pressure;
- команда неожиданно просит confirmation/изменяет cache/files;
- в выводе появляются raw business values/PII.
