# 35. L3-G06: каталог, контент, переводы, SEO и media — write-owner и cutover

Дата: 16.07.2026. Статус: **контракт аудита и планирования**; production и application code не изменялись.

## 1. Решение

Каталог, контент и переводы нельзя переносить как один Voyager CRUD или как одну таблицу `translations`. Для первого production cutover Laravel 13/Filament должен работать с той же актуальной MariaDB и тем же файловым storage, сначала в read-only/shadow режиме. Право записи передаётся отдельно по capability и всегда атомарно вместе с зависимыми переводами, URL/SEO и файлами.

Базовая миграция не включает переход Mix/Webpack → Vite и не включает регенерацию handcrafted frontend assets. Текущий Mix manifest и public assets переносятся как versioned byte-identical artifact до отдельного согласованного проекта.

## 2. Реальные хранилища переводов

Фактически существуют четыре взаимосвязанных слоя:

1. базовые поля родительских таблиц — `name`, `title`, `body`, `slug`, SEO-поля и другие исходные значения;
2. Voyager-совместимая таблица `translations` с ключом `table_name,column_name,foreign_key,locale`;
3. рабочая таблица Translation Manager `ltm_translations` с ключом `locale,group,key`, значением и статусом;
4. runtime PHP-словари `resources/lang/{locale}/{group}.php`.

Перенос только одного слоя создаёт риск частичного интерфейса, старых текстов, потери языка или последующей перезаписи целевых данных legacy-публикацией.

### 2.1 Локальные exact facts

| Источник | Exact snapshot | Важный факт |
|---|---:|---|
| `translations` | 64 779 строк | 111 таблиц; локально есть unique composite index `(table_name,column_name,foreign_key,locale)` |
| `ltm_translations` | 21 450 строк | 46 groups; 20 386 status=0, 1 064 status=1; только primary index |
| `resources/lang/*` | 10 каталогов / 334 PHP-файла | кроме public-языков присутствуют `cn`, `jp`, `et`; удалять автоматически нельзя |
| production language Git delta | 16 modified PHP-файлов | `header_footer_new.php` и `mail.php` для `de|ee|en|et|lt|lv|pl|ru` |

Локально в `translations`: `de=9 392`, `ee=10 408`, `en=2 062`, `et=6`, `lt=10 433`, `lv=10 408`, `pl=9 409`, `ru=12 661`. Все строки имеют `updated_at`, но этот timestamp не считается единственным доказательством дельты.

Локально в `ltm_translations`: `de=2 960`, `ee=3 088`, `en=2 960`, `et=2 519`, `lt=2 431`, `lv=2 473`, `pl=2 431`, `ru=2 588`; 211 значений `NULL`. Дубликатов `(locale,group,key)` в локальном snapshot нет, однако БД это не запрещает. Production duplicate audit обязателен до добавления unique constraint.

Production table-size audit оценивал `translations` примерно в 58 498 строк и 39.03 MiB, `ltm_translations` — примерно в 19 664 строки и 3.52 MiB. Эти значения являются оценкой metadata, а не exact production count.

### 2.2 Production exact baseline R1/R2 — 16.07.2026

Read-only Batch R1 подтвердил exact production counts. Для основной content/translation выборки production и локальный snapshot совпали по `COUNT/MIN(id)/MAX(id)`: `translations=64779`, `ltm_translations=21450`, `gallery_items=1038`, categories=41, sizes=80, category pivot=1249, size pivot=10662, color pivot=1467, room pivot=2973, services=11, header menu=19, menus/items=3/166, settings=27, pages=7, blog posts/categories=47/8, data types/rows=133/1997, media=1120.

Production `translations`:

- те же exact locale counts: `de=9392`, `ee=10408`, `en=2062`, `et=6`, `lt=10433`, `lv=10408`, `pl=9409`, `ru=12661`;
- duplicate composite keys = 0;
- `NULL value=0`, `NULL updated_at=0`;
- unique composite index подтверждён непосредственно на production.

Production `ltm_translations`:

- exact count=21 450, 46 groups, 211 `NULL value`, duplicate `(locale,group,key)` groups=0;
- locale/status distributions совпали с локальным snapshot;
- schema всё ещё имеет только primary index;
- production newest update `2026-06-27 16:07:20`, локальный snapshot `2026-06-03 16:32:14`. Одинаковые counts/status не доказывают одинаковые values: content-HMAC/full diff остаётся обязательным.

Четыре known orphan cohorts также совпали exactly: category→item=28, size→item=37, color→item=31, room→item=66. Это доказывает стабильный baseline, но не разрешает автоматическое удаление.

Suggestion queues являются production-only drift: `image_alt_suggestions=13131` (`failed=11297`, `pending=1834`), `seo_meta_suggestions=8456` (`new=1208` на каждый из семи public locales). Их нельзя считать частью статичного snapshot и нельзя blind retry/apply.

Batch R2 подтвердил 10 language directories и 332 PHP-файла, `lint_non_success_lines=0`, raw production tree SHA-256 `f92e8eafad568a2e0299f48410929105103452c8a7560658c9658b75010b07f5`. В локальном snapshot 334 файла: все семь public directories и `et` имеют те же file counts, но локально на один файл больше в `cn` и `jp`. Разница bytes всех locale может быть CRLF/LF, поэтому до normalized hash/path diff запрещены copy/delete/overwrite conclusions.

Batch R3 опроверг гипотезу «только CRLF»: на production raw=normalized для всех locale, а production/local normalized roots различаются. Точные path differences: production не имеет `cn/google_reviews.php` и `jp/google_reviews.php`, присутствующих локально. Для восьми остальных locale область различий проверяется R4 против ранее подтверждённых production hot edits `header_footer_new.php|mail.php`. R2 shell root и R3 PHP root используют разные manifest algorithms и не сравниваются напрямую.

R4 подтвердил: три общих `cn/jp` files совпадают, а два `google_reviews.php` остаются local-only. Из 16 известных hot-edit paths 15 отличаются semantic normalized hash; совпал только `ru/mail.php`. Excluded roots всех восьми основных locale также различаются, то есть hot edits шире известного Git-status списка. До полного R5 path/hash manifest минимальная доказанная область — не менее 25 file-level discrepancies.

R5 закрыл exact path/hash scope: 309 same, 23 changed, 0 production-only, 2 local-only. Для `de|ee|en|et|lt|lv|pl` изменены `account_new.php|header_footer_new.php|mail.php`; для `ru` — `account_new.php|header_footer_new.php`. Local-only: `cn/jp google_reviews.php`. Полная matrix: [appendix-language-file-delta-r5.csv](appendix-language-file-delta-r5.csv). Production changed versions сохраняются до per-key manual merge и Content/Mail UAT.

R6 подтвердил, что `ltm_translations` имеет одинаковые 21 450 logical keys и 211 NULL values, но отличается по canonical key+value и key+value+status roots. Это уже доказанная semantic DB divergence, а не только timestamp drift. Local dump не является заменой production corpus; следующий read-only шаг — locale-level R7 из [42-l3g06-r6-analysis-and-r7-ltm-locale.md](42-l3g06-r6-analysis-and-r7-ltm-locale.md).

Corrected R7 сравнил одинаковым production/local алгоритмом восемь locale: для каждого совпали rows, NULL и logical key root, но value/full roots отличаются во всех `de|ee|en|et|lt|lv|pl|ru`. Следующий safe narrowing — 46 group roots R8; initial local R7 baseline erratum и команда зафиксированы в [43-l3g06-r7-analysis-and-r8-ltm-group.md](43-l3g06-r7-analysis-and-r8-ltm-group.md).

R8 локализовал DB divergence: 45/46 LTM groups (21 210 rows) полностью semantic-equal, отличается только `header_footer_new` (240 rows, key set совпадает). `account_new` и `mail` в DB совпадают, хотя соответствующие PHP files входят в R5 file delta — DB и runtime file layer должны reconciliate отдельно. Targeted locale R9: [44-l3g06-r8-analysis-and-r9-header-footer-locale.md](44-l3g06-r8-analysis-and-r9-header-footer-locale.md).

R9 подтвердил `header_footer_new` value mismatch во всех 8 locale при одинаковых 30 keys на locale, 0 NULL и одинаковых counts. Это не доказывает изменение всех 240 values; следующий R10 группирует по 30 opaque key IDs без вывода raw keys/values: [45-l3g06-r9-analysis-and-r10-opaque-key.md](45-l3g06-r9-analysis-and-r10-opaque-key.md).

R10 установил exact DB value scope: 29/30 keys полностью совпадают, divergent только `header.top_sale`. R11 завершил доказательство: identity/status/NULL-state совпали 8/8, а value/full отличаются 8/8. Следовательно, 21 442/21 450 LTM values semantic-equal, все identities/status сохранены, а восемь raw values требуют только owner disposition. Независимый файловый runtime-контур продолжается в R12: [47-l3g06-r11-final-proof-and-r12-php-array.md](47-l3g06-r11-final-proof-and-r12-php-array.md).

R12 закрыл семь `mail.php` как semantic-equal, подтвердил value-only delta восьми `header_footer_new.php` и локализовал `account_new.php`: один local-only `orders.not_specified` во всех восьми locale, остальные common values совпадают в семи locale и расходятся только в `ru`. Targeted R13 ограничен 30 header keys и 36 RU account groups: [48-l3g06-r12-analysis-and-r13-targeted-files.md](48-l3g06-r12-analysis-and-r13-targeted-files.md).

R14 завершил exact file scope: в header/footer divergent только `header.top_sale` через восемь locale; в account local-only `orders.not_specified` через восемь locale; единственный дополнительный common value mismatch — RU `orders.user_status.print_text`. Все 44 production RU account/orders identities/types присутствуют локально, 43 values совпадают. Итог: [50-l3g06-r14-final-translation-reconciliation.md](50-l3g06-r14-final-translation-reconciliation.md).

## 3. Языковой контракт

1. Public locales сохраняются: `lv|lt|pl|ru|de|en|ee`.
2. `ee` остаётся действующим public identifier с regional mapping `et_EE`.
3. `et` не объединяется с `ee`: он существует в БД/файлах и сохраняется как отдельный storage identifier до решения владельца.
4. Каталоги `cn` и `jp` сохраняются byte-identical, даже если сейчас не входят в public routing.
5. `uk` не включается автоматически как язык сайта: он остаётся согласованным API/CRM fallback `uk -> ru`, пока бизнес не утвердит иное.
6. Нельзя нормализовать язык только по ISO-коду без таблицы соответствий source identifier → public locale → regional locale → fallback.
7. Для каждого public locale проверяются UI labels, контент, slug/URL, SEO meta, mail templates и fallback; отсутствие перевода не должно молча затирать базовое значение.

## 4. Текущие writers и опасные пересечения

### 4.1 Voyager и custom admin

В проекте 133 BREAD definitions и 1 997 `data_rows`; их миграция не сводится к автоматической генерации Filament Resources. Generic BREAD и custom controllers могут создавать, изменять и физически удалять строки. Право записи назначается по модулю и действию, а не по общему `/admin`.

### 4.2 Blog API

`BlogIntegrationController` пишет родительские blog-строки и делает `updateOrInsert` в `translations` по составному ключу. Этот writer должен переключаться вместе с blog capability либо оставаться единственным legacy API-writer; параллельная запись Filament и API без optimistic/idempotency contract запрещена.

### 4.3 Translation Manager

Установлен `barryvdh/laravel-translation-manager` v0.5.10. Его маршруты доступны под `admin/translations` с middleware `web,auth`; конфигурация разрешает delete, import/find, publish, add/remove locale и auto-translate. Статическая конфигурация не доказывает role/permission-level ACL.

Publish экспортирует значения из `ltm_translations` в `resources/lang/{locale}/{group}.php`, то есть способен перезаписать runtime-файлы. Delete удаляет ключ по locales, remove-locale меняет языковой набор. До cutover требуются deny-by-default ACL, audit receipt и versioned artifact publish вне live directory.

### 4.4 Custom PHP dictionary editor

`AdminLocaleController` читает request-параметры для выбора locale/template, делает `include`, `fopen(...,'w')` и может создавать каталоги с mode `0777`. Это второй независимый writer тех же PHP-словарей. Одновременно Translation Manager и custom editor оставлять write-enabled нельзя.

Целевой вариант: один typed editor формирует versioned dictionary artifact во временном каталоге, выполняет PHP lint и manifest/hash comparison, затем атомарно активирует artifact. Прямую перезапись live `resources/lang` следует вывести из эксплуатации до передачи права записи.

## 5. Каталог и контент

### 5.1 Подтверждённые локальные объёмы

| Контур | Exact snapshot |
|---|---:|
| `gallery_items` | 1 038 |
| `gallery_categories` | 41 |
| `gallery_sizes` | 80 |
| `gallery_category_gallery_item` | 1 249 |
| `gallery_items_ gallery_tag_sizes` | 10 662 |
| `newhome_services` | 11 |
| `header_menu` | 19 |
| `menus` / `menu_items` | 3 / 166 |
| `settings` | 27 |
| `pages` | 7 |
| `blog_posts` / `blog_categories` | 47 / 8 |

Имя таблицы `gallery_items_ gallery_tag_sizes` содержит пробел и должно переноситься/экранироваться буквально либо заменяться только отдельной additive migration с доказанной совместимостью.

Часть pivot/settings/Voyager metadata таблиц не имеет `created_at/updated_at`; для них дельта строится только по полному PK/key set, canonical hash и anti-join. Известные локальные orphan-связи до миграции не удаляются автоматически: category pivot→item 28, size pivot→item 37, colors pivot→item 31, rooms pivot→item 66. Нужен business disposition: сохранить как legacy anomaly, исправить отдельно или исключить с подписанным отчётом.

### 5.2 Цена и варианты

Цена карточки зависит не от одного поля: участвуют item/options/sizes, country multiplier, session/country rules и отдельная SA-проекция. Legacy и target не могут одновременно рассчитывать/сохранять цену разными алгоритмами. До переключения создаётся characterization corpus по странам, языкам, размерам и опциям; результат target должен совпасть с согласованным legacy baseline.

### 5.3 Legacy public mutating GET

Маршруты `/set_sizes`, `/set_genre`, `/set_style`, `/translate_item`, `/set_meta` выполняют изменения через GET. Их нельзя воспроизводить как public target behavior. Они должны быть закрыты/защищены и заменены authenticated POST/command workflow с CSRF, ACL, validation, audit и idempotency.

## 6. SEO, URL, redirect, sitemap и feed

Slug и SEO не являются вспомогательной копией контента. Они формируют публичный контракт URL и должны переключаться атомарно вместе с соответствующим модулем и его переводами.

Минимальная сверка:

- полный per-locale URL inventory до и после;
- slug uniqueness и collision report;
- status, canonical, hreflang, title/description hash;
- redirect source/target/status, precedence, chains, loops и conflicts;
- sitemap/feed normalized URL set и locale coverage;
- 404/redirect smoke для top routes и historical URLs.

DB-таблицы `redirects`, `seo_redirects`, `redirect_urls` локально не обнаружены: активная redirect-карта находится в routes/code/static mapping. Поэтому её переключают как versioned release artifact, а не как data migration.

## 7. Delta и reconciliation contract

### 7.1 Shared DB — рекомендуемый первый этап

Laravel 13/Filament читает ту же MariaDB. Это убирает необходимость двусторонней копии production rows, но не решает split-brain: legacy route/action данного capability должен стать read-only или disabled до включения target writer.

На каждый cutover unit фиксируются:

1. owner и точные routes/actions/fields;
2. exact PK/key set и canonical row hash;
3. parent→translation→pivot→media dependency counts;
4. orphan/duplicate/delete report;
5. public locale/URL/SEO smoke matrix;
6. file manifest `relative_path,size,sha256` и PHP lint для словарей;
7. release ID, DB checkpoint coordinates, timestamp UTC и sign-off.

### 7.2 `translations`

Ключ: `(table_name,column_name,foreign_key,locale)`. Сверяются key set, canonical value hash, родительская строка и ожидаемая locale matrix. `updated_at` допускается только для overlapping scan; финальная проверка — full key/hash/anti-join. Удаление parent или перевода требует tombstone/change receipt либо финального freeze.

### 7.3 `ltm_translations`

Логический ключ: `(locale,group,key)`. До unique migration production проверяется на duplicates и `NULL`. Сверяются value/status hash и соответствующий published PHP artifact. `status` не доказывает, что live-файл содержит ту же версию.

### 7.4 PHP dictionaries

mtime не используется как доказательство. Manifest содержит relative path, locale, group, size, SHA-256 и PHP syntax result. Publish выполняется сначала во versioned staging directory; live activation — atomic pointer/artifact deployment. Любая production hot edit должна попасть в ledger и новый artifact до следующего deploy.

### 7.5 Таблицы без timestamps

Для `settings`, части `data_rows`, pivot и других timestamp-less источников выполняется полный key/hash comparison на каждом rehearsal/final checkpoint. `MAX(id)` и incremental timestamp export запрещены как единственная стратегия.

## 8. Матрица владельцев записи

Полная машиночитаемая версия: [appendix-l3g06-content-write-owner-matrix.csv](appendix-l3g06-content-write-owner-matrix.csv).

Главное правило: базовая строка, её переводы, relation/pivot, media references и URL/SEO принадлежат одному cutover unit. Исключение допустимо только при явном API/transaction contract и signed owner matrix.

## 9. Рекомендуемые волны

| Волна | Capability | Gate |
|---:|---|---|
| 0 | Target read-only, locale/URL/file manifests | ноль target writes; parity по всем public locales |
| 1 | Voyager metadata/settings и простые singleton pages | полный hash для timestamp-less tables; consumer inventory |
| 2 | Blog admin/API и страницы по одному модулю | parent+translations+slug переключаются вместе |
| 3 | Меню, header/footer и service catalogue | hierarchy, locale, mail/UI label parity |
| 4 | Catalogue classifications и pivots | orphan disposition и relation reconciliation подписаны |
| 5 | Catalogue items, media и pricing/options | price corpus, file manifest, public card/constructor UAT |
| 6 | Translation Manager/LTM/PHP dictionary writer | один writer, ACL, versioned publish, lint/hash, ee/et retention |
| 7 | SEO redirect artifact, sitemap и feeds | полный URL/redirect/canonical/hreflang regression |
| 8 | Старые admin writers окончательно закрываются | observation window и forward rollback rehearsal пройдены |

DB translations конкретного модуля не ждут общей волны 6: они переключаются атомарно с родительским модулем. Волна 6 относится к общим PHP-словарям и Translation Manager workflow.

## 10. Runbook одной волны

1. Подписать capability owner, routes/actions/fields и зависимые translation/media/SEO sources.
2. Снять ручной или автоматический off-host DB+files checkpoint и доказать isolated restore.
3. Зафиксировать baseline key/hash/file/URL manifest.
4. Остановить только legacy writers выбранного capability; остальные production writes продолжаются.
5. Дождаться in-flight requests/jobs и сохранить final watermark/receipt boundary.
6. Выполнить full key/hash/anti-join, translation/file/URL reconciliation.
7. Включить target writer и оставить legacy UI read-only/disabled для этой capability.
8. Провести allowed/denied/concurrent/duplicate tests и public smoke по `lv|lt|pl|ru|de|en|ee`.
9. Проверить `ee/et`, inactive `cn/jp`, API `uk->ru`, slug/SEO, mail/UI dictionaries и files.
10. Наблюдать ошибки, latency, queue age, duplicate writes, 404 и missing translation/file alerts; подписать волну либо выполнить forward rollback.

## 11. Stop/no-go conditions

- два интерфейса могут писать один capability или один PHP dictionary set;
- не сходятся parent/translation/pivot/media key sets или hashes;
- target меняет public locale identifier, URL, ID или price без подписанного mapping;
- потерян `ee`, автоматически объединены `ee/et`, удалены `cn/jp` или `uk` включён как public locale без решения;
- Translation Manager/custom editor может публиковать поверх target artifact;
- PHP lint/file manifest не проходит;
- orphan/duplicate/delete delta не классифицирована;
- redirect loop/chain/collision, массовые 404 или sitemap/feed расхождение;
- frontend требует Vite/rebuild/regeneration handcrafted assets;
- отсутствуют restore evidence, owner sign-off, observation и forward rollback rehearsal.

## 12. Forward rollback

Rollback возвращает capability owner, но не откатывает всю production-БД:

1. target writer/publisher выключается;
2. in-flight writes/jobs останавливаются и классифицируются;
3. committed rows, translations и новые media/files сохраняются;
4. выполняется forward reconciliation к общей DB/storage;
5. legacy writer возвращается только после совместимости и точного diff;
6. для PHP dictionaries активируется предыдущий versioned artifact, но новые изменения сначала сохраняются в ledger/current artifact branch;
7. redirect/sitemap/feed возвращаются переключением versioned release artifact;
8. старый DB/files dump не разворачивается поверх новых production orders/content/translations.

## 13. Gate acceptance criteria

L3-G06 для каталога/контента/переводов считается утверждённым после:

- подписанной capability matrix и per-module field/action manifest;
- production exact counts/duplicate/orphan audit, включая обе translation tables;
- manifest всех `resources/lang` и production hot edits;
- единственного dictionary writer с deny-by-default ACL и versioned publish;
- сохранения `lv|lt|pl|ru|de|en|ee`, отдельного `ee/et` и retention decision для `cn/jp/uk`;
- reconciliation tooling для timestamp и timestamp-less sources;
- catalogue price/relations/media characterization и UAT;
- complete URL/redirect/sitemap/feed regression;
- isolated restore и минимум одной low-risk и одной catalogue/translation wave rehearsal;
- доказанного forward rollback без потери новых production writes;
- Business/Content/SEO/CRM/QA/DevOps sign-off.

## 14. Текущий вердикт

Архитектура безопасного переноса зафиксирована, а техническая translation discovery/reconciliation R5–R14 закрыта. Target writes остаются **no-go**: можно проектировать Laravel 13 read models, Filament read-only resources, translation adapters и versioned artifact tooling, но нельзя включать Filament CRUD/publish до owner disposition, staging UAT, единственного dictionary writer, свежего cutover checkpoint, restore rehearsal и per-wave rollback evidence.

Owner decision и 14-case staging UAT конкретизированы в [51-l3g06-translation-owner-decision-and-staging-uat.md](51-l3g06-translation-owner-decision-and-staging-uat.md). Они учитывают feature flag `site.top_sale`, отдельный delivery layout, точную ветку RU `in_production + painter assigned`, dormant `orders.not_specified`, Google Reviews section/sitemap и deny-by-default publisher test.
