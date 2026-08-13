# 39. L3-G06: анализ language R3 и targeted Batch R4

Дата: 16.07.2026. Статус: **R3/R4 выполнены read-only и проанализированы**. Следующий полный file manifest: [40-l3g06-r4-analysis-and-r5-manifest.md](40-l3g06-r4-analysis-and-r5-manifest.md).

## 1. Подтверждено R3

- production: 332 PHP language files;
- raw и line-ending-normalized hashes совпадают для каждого locale и всего дерева;
- следовательно, production corpus не содержит CRLF/CR-различий, влияющих на hash;
- local normalized hashes не совпали ни для одного locale, поэтому различие не сводится к переносам строк;
- production `cn` содержит только `new_index.php`; local дополнительно содержит `google_reviews.php`;
- production `jp` содержит `account.php` и `new_index.php`; local дополнительно содержит `google_reviews.php`.

Ни один файл не удаляется и не копируется: отсутствие двух `google_reviews.php` может быть intentional local-only development, неперенесённый production change или потерянный artifact. Нужен owner/code-history/runtime-use decision.

## 2. Почему R2 tree hash не равен R3 raw tree hash

R2 использовал shell pipeline `find|sort|sha256sum`, R3 — PHP path sorting и собственную canonical manifest line. При одинаковых production counts/bytes R2 дал `f92e...`, R3 — `a993...`. Эти roots принадлежат разным manifest algorithms и напрямую не сравниваются.

Это не доказательство production mutation между 10:30 и 10:38. Canonical evidence series отныне использует только PHP-алгоритм R3/R4 с явной версией `LANG-MANIFEST-v1`.

## 3. Гипотеза для восьми основных locale

Production Git evidence ранее показал ровно 16 modified language paths:

- `header_footer_new.php`;
- `mail.php`;
- для `de|ee|en|et|lt|lv|pl|ru`.

R4 вычисляет для каждого locale:

1. normalized aggregate root всего корпуса **без** этих двух basenames;
2. normalized SHA-256 отдельно для `header_footer_new.php` и `mail.php`;
3. normalized SHA-256 и basenames всех production `cn/jp` files.

Если excluded roots совпадут с local, область реального content drift будет доказанно ограничена 16 production hot edits и двумя local-only `google_reviews.php`. Если хотя бы один excluded root отличается, понадобится полный per-file hash manifest для этого locale.

## 4. Batch R4 — targeted language difference isolation

Выполнить от root. Команда читает только PHP language files и выводит paths/hashes, но не содержимое:

```bash
ROOT=/home/admin/web/viarcanvas.com/public_html
PHP=/usr/bin/php7.4

cd "$ROOT" || exit 1

echo '=== LANGUAGE TARGETED DIFFERENCE R4 ==='
date -u '+server_utc=%Y-%m-%dT%H:%M:%SZ'

sudo -u admin "$PHP" <<'PHP'
<?php

$base = 'resources/lang';
$excluded = ['header_footer_new.php', 'mail.php'];
$result = [
    'algorithm' => 'LANG-MANIFEST-v1',
    'locales' => [],
];

foreach (glob($base . '/*', GLOB_ONLYDIR) as $directory) {
    $locale = basename($directory);
    $paths = glob($directory . '/*.php') ?: [];
    sort($paths, SORT_STRING);

    $includedLines = '';
    $knownFiles = [];
    $allFiles = [];

    foreach ($paths as $path) {
        $raw = file_get_contents($path);
        if ($raw === false) {
            fwrite(STDERR, "READ_FAILED\n");
            exit(2);
        }

        $normalized = str_replace(["\r\n", "\r"], "\n", $raw);
        $basename = basename($path);
        $hash = hash('sha256', $normalized);
        $allFiles[$basename] = $hash;

        if (in_array($basename, $excluded, true)) {
            $knownFiles[$basename] = $hash;
            continue;
        }

        $includedLines .= $hash . '  ' . str_replace('\\', '/', $path) . "\n";
    }

    $entry = [
        'file_count' => count($paths),
        'excluding_known_count' => count($paths) - count($knownFiles),
        'excluding_known_normalized_sha256' => hash('sha256', $includedLines),
        'known_file_hashes' => $knownFiles,
    ];

    if ($locale === 'cn' || $locale === 'jp') {
        $entry['all_file_hashes'] = $allFiles;
    }

    $result['locales'][$locale] = $entry;
}

ksort($result['locales'], SORT_STRING);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
PHP
```

## 5. Локальные expected roots для сравнения

| Locale | Excluding `header_footer_new.php|mail.php` normalized root |
|---|---|
| `cn` | `380815fbc9a063ef70f67f430488107aed8c610e2f3054fcebbbb75758fb603e` |
| `de` | `7720d4a44aab991956f363d12a32f70e0879f72788dd64254ac0ff46eeb3b259` |
| `ee` | `da0df61452770cad05f342f7225e130680ea6a1b1a802dacc83b4d6e9dee8221` |
| `en` | `bff8d574702e186a152ce6476114f892a1d40e7ea4338ef4bcd8ca2e5d60b0a7` |
| `et` | `be54815233bb3cec00c321ebdfc0e8468e98e3ac8e143e2bd22376c0e01b04bc` |
| `jp` | `21e9a272eb59796b8ec08d24d781a2113c458987d513d8ee02ee8163113ba1d8` |
| `lt` | `6520817403ad70f9c36fca3750b5c088a507f0950dbfceba12a48afd7c2a4210` |
| `lv` | `13f94d66780e5684e5423cbebe5ba64cfe347b8b4fad994c0d9269579afa1ed8` |
| `pl` | `5d804ca8360c090ea927918ce31e228f2fdf8a87bff5c277b87f3c44931127f3` |
| `ru` | `35afd3e6e487c30a86533c4f0326554f021dce1c37b7330b9a51de1aa64a17f5` |

`cn/jp` excluded roots ожидаемо не совпадут, пока local содержит дополнительные `google_reviews.php`. Для них сравниваются hashes общих basenames отдельно.

## 6. Что прислать

Можно прислать весь JSON R4. Он содержит только basenames и SHA-256, без текстов переводов.

## 7. Следующее действие после R4

- если основные excluded roots совпали — зафиксировать exact 18-file/path delta и сохранить production 16 hot edits как source of truth;
- если не совпали — запросить per-file manifest только для несовпавших locale;
- затем перейти к `ltm_translations` value-HMAC на restored staging;
- до решения запрещены Translation Manager publish, direct locale editor writes и repository overwrite production files.
