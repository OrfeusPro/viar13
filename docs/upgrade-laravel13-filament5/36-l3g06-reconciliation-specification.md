# 36. L3-G06: canonical reconciliation specification

Дата: 16.07.2026. Статус: **design specification**; production/staging не изменялись, reconciliation script ещё не реализован и не прошёл rehearsal.

## 1. Цель

Определить единый доказуемый формат сверки до/после каждой migration wave. Сверка должна находить insert, update, delete, duplicate, orphan, file/URL drift и не выводить в чат/CI персональные данные, секреты, тексты заказов, сообщений или переводов.

Полный список datasets: [appendix-l3g06-reconciliation-datasets.csv](appendix-l3g06-reconciliation-datasets.csv).

## 2. Режимы хранения evidence

### 2.1 Restricted artifact

Хранится в закрытом audit storage с доступом DBA/Tech owner. Может содержать source primary keys, relative private paths и per-row HMAC, но не raw payload. Не прикладывается в чат, issue или публичный CI artifact.

### 2.2 Shareable summary

Содержит только:

- dataset ID и schema/release/checkpoint ID;
- exact count, duplicate/orphan/missing/changed counts;
- aggregate HMAC/Merkle root;
- earliest/latest timestamp там, где это безопасно;
- pass/fail и reason code;
- никаких raw row values, emails, phones, addresses, tokens, chat/order/translation text и private paths.

## 3. Checkpoint IDs

| ID | Момент | Назначение |
|---|---|---|
| `B0` | restored staging baseline | доказать, что DB+files backup восстанавливается и baseline воспроизводим |
| `S0` | до начала shadow tests | зафиксировать schema/data/file/URL baseline target environment |
| `T0` | перед остановкой legacy writer capability | baseline текущей волны при продолжающихся остальных production writes |
| `T1` | после drain legacy writer | final delta boundary и полный manifest до включения target writer |
| `T2` | сразу после target smoke | доказать отсутствие потери/дубликата и непредусмотренных side effects |
| `O1` | конец observation window | принять capability либо запустить forward rollback |
| `R1` | после forward rollback rehearsal/operation | доказать сохранение target committed writes и корректный возврат owner |

Каждый checkpoint связывается с `environment`, UTC server/DB time, DB server/version, schema hash, application release hash, file artifact ID, capability owner и предыдущим checkpoint.

## 4. Canonical value format

Canonicalizer обязан работать одинаково в legacy PHP 7.4 baseline tooling и Laravel 13 tooling.

| Тип | Canonical representation |
|---|---|
| `NULL` | отдельный typed marker, не пустая строка |
| integer/decimal | decimal string без locale formatting; scale сохраняется по schema contract |
| boolean/tinyint flag | исходное numeric/string значение до отдельной signed normalization |
| date/datetime/timestamp | исходная DB semantic + normalized UTC ISO-8601; zero/invalid date маркируется отдельно, не исправляется |
| text/blob | exact byte sequence → HMAC; для UTF-8 дополнительно validity/NFC diagnostic, но исходник не меняется |
| valid JSON | recursive key sort для object, array order сохраняется, number/string types сохраняются |
| invalid/legacy serialized text | tagged opaque bytes; автоматический unserialize/re-encode запрещён |
| path | normalized separator и relative root; case не меняется |
| row | ordered list `column name + type marker + canonical value` по подписанному schema manifest |

Hash primitive: HMAC-SHA-256 с отдельным audit key. Audit key не хранится в repository, `.env`, output или чате; одинаковый key используется только в пределах одной rehearsal/cutover series. Для публичных immutable assets допустим обычный SHA-256.

## 5. Aggregate proof

Для каждого dataset формируются:

1. schema manifest и ordered key definition;
2. exact key set;
3. per-row HMAC restricted artifact;
4. deterministic aggregate root по отсортированным `key + row_hmac`;
5. count и min/max key только если они не раскрывают PII;
6. duplicate logical-key report;
7. left/right anti-join counts: missing, extra, changed;
8. orphan report по подписанным relationships;
9. статус `PASS|EXPECTED_DELTA|FAIL|NOT_RUN`.

Один aggregate root не заменяет diff: при несовпадении restricted artifact должен показать dataset/key/reason, но не raw value.

## 6. Watermark и delta

`updated_at` используется только для ускоренного overlapping scan. Полная финальная сверка `T1/T2/O1` выполняется по key set и hashes.

Минимальный delta contract:

- серверное время MariaDB и application фиксируются до/после запроса;
- overlap window покрывает clock skew, long request/job и retry;
- insert/update/delete имеют mutation receipt либо выявляются full anti-join;
- hard delete блокируется на capability во время final drain либо пишет tombstone;
- таблицы без timestamps всегда сравниваются полностью;
- file mtime не считается доказательством: используется size+SHA-256+path;
- позднее событие/очередь после `T1` либо относится к прежнему owner и drain, либо блокирует cutover.

## 7. Ключи, подтверждённые локальной схемой

| Source | Physical/logical key | Аудиторское решение |
|---|---|---|
| `translations` | PK `id`; unique `(table_name,column_name,foreign_key,locale)` | reconciliation identity — composite key; production index/duplicates проверяются повторно |
| `ltm_translations` | PK `id`; schema unique для `(locale,group,key)` отсутствует | reconciliation identity — logical key; duplicates/NULL обязательны до DDL |
| `settings` | PK `id`; unique `key` | сверять PK и logical key; full diff, timestamps отсутствуют |
| `data_types` | PK `id`; unique `name`, unique `slug` | все три key domains входят в duplicate/anti-join report |
| `data_rows` | PK `id`; non-unique `data_type_id` | PK/hash + per-data_type ordering/field manifest; full diff |
| catalogue/content parents | обычно PK `id` | ID сохраняется; slug используется как diagnostic/business key, но не заменяет PK |
| catalogue pivots | локально часто только PK `id` | дополнительно сравнить logical FK tuple и duplicates/orphans |
| `image_alt_suggestions` | PK `id`; unique image identity+field+locale | не retry/apply; identity/status/target mutation receipt сверяются отдельно |
| `seo_meta_suggestions` | PK `id`; unique metaable type+id+locale | не retry/apply; target before/after receipt обязателен |

## 8. Четыре translation layers

### 8.1 Parent/base fields

Сверяются в dataset родительского модуля. Base field и соответствующие `translations` rows принадлежат одному cutover unit. Parent delete проверяется против child translations полным anti-join.

### 8.2 Voyager `translations`

- identity: `table_name,column_name,foreign_key,locale`;
- payload: HMAC `value`, `created_at`, `updated_at`;
- отчёты: per-table/per-column/per-locale exact counts, duplicates, missing parent, missing expected locale;
- raw value не выводится;
- `et` rows и legacy metadata/orphan translation cohorts сохраняются до signed disposition.

### 8.3 Translation Manager `ltm_translations`

- identity: `locale,group,key`;
- payload: HMAC `value`, status и timestamps;
- до unique DDL: exact duplicate logical keys, `NULL value`, status distribution и file publish parity;
- status=published/saved сам по себе не доказывает содержимое PHP-файла.

### 8.4 PHP dictionaries

Manifest: `relative_path,locale,group,size,SHA-256,PHP lint result`. Сохраняются все каталоги, в том числе `cn|jp|et`. Production modified language files сначала входят в source artifact; их нельзя затереть repository copy.

## 9. Catalogue/content dependencies

Для каждого parent module формируется один dependency bundle:

`parent rows → DB translations → pivots/children → media DB references → files → slug/SEO → public URLs → sitemap/feed projection`.

Переключение запрещено, если каждый dataset отдельно даёт count, но bundle содержит missing parent, orphan, inconsistent locale, changed price/URL или missing file.

Известные локальные orphan cohorts считаются baseline anomalies, а не разрешением на новые orphans. Они должны иметь стабильный cohort hash и signed disposition.

## 10. URL/SEO reconciliation

Для каждого public locale `lv|lt|pl|ru|de|en|ee`:

- normalized URL set и HTTP expected status;
- slug owner/source ID;
- canonical и hreflang target set;
- redirect source/target/status/precedence;
- loop, conflict и chain length;
- sitemap/feed membership и lastmod/offer projection;
- top historical URL smoke и unexplained 404 count.

`ee` и `et` сравниваются как разные identifiers. `cn/jp` остаются retained files, но не добавляются в public URL set автоматически. `uk` проверяется в API fallback suite, а не в public routing suite.

## 11. Files/media reconciliation

1. DB references экспортируются без raw private path в shareable summary.
2. Restricted file manifest содержит approved root-relative path, size и SHA-256.
3. Проверяются missing referenced files, changed hash, duplicate content/path, unreferenced/new files и symlink target boundary.
4. MIME/visibility/retention классифицируются отдельно; hash не делает private файл public.
5. Новые файлы target сохраняются при rollback и привязываются после forward reconciliation.
6. Frozen frontend dataset должен совпасть byte-identical; `Vite`/asset regeneration не запускаются.

## 12. Алгоритм одной rehearsal/cutover wave

1. Проверить restore evidence, owner matrix, capability routes/actions/fields и audit key handling.
2. Снять schema manifest и `T0` full baseline.
3. Включить target shadow reads; сравнить read projections без writes.
4. Остановить только legacy writer capability; дождаться request/job/outbox drain.
5. Снять `T1`: full key/hash/anti-join + file/URL manifests.
6. Если `T1` не PASS/approved expected delta — target writer не включать.
7. Включить target writer и выполнить synthetic allowed/denied/duplicate/concurrent/failure smoke.
8. Снять `T2`, сравнить target mutation receipts и все dependent datasets.
9. Наблюдать agreed window; снять `O1`.
10. Подписать capability либо выполнить forward rollback и доказать `R1`.

## 13. Stop/no-go reason codes

| Code | Условие |
|---|---|
| `REC-SCHEMA` | обнаружено destructive/unapproved schema difference |
| `REC-OWNER` | legacy и target могут писать один capability |
| `REC-KEY` | missing/extra/duplicate logical or physical key не объяснён |
| `REC-HASH` | changed row/file hash не связан с approved mutation receipt |
| `REC-DELETE` | delete не имеет tombstone/freeze evidence |
| `REC-ORPHAN` | появился новый orphan или изменился baseline cohort без решения |
| `REC-I18N` | потерян locale/key/file, нарушен `ee/et/cn/jp/uk` contract |
| `REC-PRICE` | price/options/country fixture отличается |
| `REC-FILE` | missing/changed/private-path boundary или PHP lint failure |
| `REC-URL` | unexplained 404, slug collision, redirect loop/conflict, canonical/feed gap |
| `REC-SIDEFX` | повторился mail/webhook/payment/file/job side effect |
| `REC-EVIDENCE` | checkpoint, restore, audit key, release/file ID или sign-off отсутствует |

## 14. Что можно выполнять на production read-only

Допустимы небольшие aggregate/schema/count/duplicate/orphan запросы с timeout и без raw values. Полные per-row HMAC/file tree scans сначала профилируются на restored staging; на production запускаются только в согласованное окно с DBA, IO/CPU/disk monitoring и output в закрытый artifact.

Не выполняются в рамках evidence pass: DDL, update/delete, Translation Manager publish, queue retry/flush, cache clear, asset build, full public-storage content copy, DB dump без backup runbook и любые provider callbacks.

## 15. Acceptance для CUT-002/CUT-009

- canonicalizer имеет fixtures для NULL/empty, Unicode, decimals, dates, valid/invalid JSON и opaque serialized payloads;
- одинаковый restored snapshot даёт одинаковые roots на PHP 7.4 и target runtime;
- injected insert/update/delete/duplicate/orphan/file/URL drift обнаруживается с правильным reason code;
- shareable output не содержит PII/secrets/raw content/private paths;
- restricted artifacts имеют encryption, access owner и retention;
- full staging run имеет измеренное время/IO/размер;
- low-risk module cutover и forward rollback дают ноль unexplained differences;
- production command pack одобрен DBA/Security/Business owner.

## 16. Полученный production baseline R1/R2

16.07.2026 выполнены безопасные aggregates из документа 37:

- основная content/catalogue/translation выборка совпала с локальным snapshot по exact counts и ID boundaries;
- production подтвердил unique composite index и ноль duplicate/NULL в `translations`;
- production `ltm_translations` подтвердил count/groups/status/NULL/duplicates, но newest update позже локального snapshot на 24 дня;
- четыре catalogue orphan cohorts совпали exactly;
- production language tree: 332 lint-valid PHP files; локально 334, разница path count сосредоточена в `cn/jp`;
- raw file bytes/hashes пока нельзя сравнивать как semantic content из-за вероятного CRLF/LF drift.

Это переводит REC-007/REC-008/REC-009 из `NOT_RUN` в `aggregate baseline collected`, но не в `PASS`: full value-HMAC, normalized file hash/path diff и restored-staging rehearsal ещё не выполнены.

R3 уточнил REC-009: production corpus полностью LF-normalized, но local/production normalized roots различаются. Два path differences установлены (`cn/jp google_reviews.php` local-only); остальные locale проверяются targeted exclusion manifest R4. Hash roots разных algorithms (`shell R2` и `PHP R3`) не смешиваются; canonical file evidence закреплён как `LANG-MANIFEST-v1`.

R4 показал, что REC-009 нельзя ограничить 16 известными Git paths: 15 из них content-different, `ru/mail.php` semantic-equal, а excluded roots всех основных locale всё ещё различаются. Для exact diff введён `LANG-FILE-MANIFEST-v1` R5 с полным `relative_path → normalized SHA-256`; raw values не экспортируются.

R5 завершил aggregate/path classification REC-009: `same=309`, `changed=23`, `production-only=0`, `local-only=2`. File manifest gate теперь имеет exact scope, но REC-009 ещё не PASS: исходный server JSON checksum не воспроизведён из pasted-text transport, per-key PHP-array merge/disposition/UAT и versioned publish rehearsal отсутствуют.

R6 уточнил DB-часть REC-009: `ltm_translations` production/local совпадают по row count, NULL count и canonical logical key root, но отличаются по key+value и key+value+status roots. Потери/добавления logical keys не обнаружены; value corpus различается, а отдельный status delta ещё не локализован. REC-009 остаётся `PARTIAL`, target writes/publish запрещены. Locale-level декомпозиция выполняется Batch R7 из [42-l3g06-r6-analysis-and-r7-ltm-locale.md](42-l3g06-r6-analysis-and-r7-ltm-locale.md).

R7 подтвердил ту же картину отдельно по всем 8 locale: key set совпадает в каждом, values различаются в каждом. Первоначальный local R7 baseline имел tooling defect (omitted `group`); production output был корректным, local baseline пересчитан тем же algorithm, повтор production не нужен. Exact matrix: [appendix-ltm-locale-delta-r7.csv](appendix-ltm-locale-delta-r7.csv). Следующая декомпозиция — 46 groups R8.

R8 показал, что aggregate locale mismatch создаётся одной group: 45/46 groups полностью совпадают, divergent только `header_footer_new`. REC-009 DB scope теперь равен 240 строкам с одинаковыми keys и различными aggregate values; остальные 21 210 LTM rows semantic-equal. File REC-009 остаётся отдельным: R5 PHP-file delta шире DB delta. Exact matrix: [appendix-ltm-group-delta-r8.csv](appendix-ltm-group-delta-r8.csv).

R9 сузил DB scope до 8 одинаковых 30-key locale cohorts: key roots совпали во всех, value roots отличаются во всех. Следующий REC-009 subgate — 30 opaque key aggregates R10; только mismatched keys допускаются к final locale×key/staging merge analysis.

R10 закрыл key-level scope: один opaque mismatch из 30 локально отображается в `header.top_sale`; остальные 29 keys совпадают. R11 подтвердил 8/8 identity/status/NULL-state matches и 0/8 value matches. DB technical reconciliation завершён; REC-009 остаётся partial только из-за owner disposition восьми raw values и независимого PHP-array/file gate R12.

R12 классифицировал 23 changed PHP files по runtime semantics: 7 mail files equal, 8 header files имеют value-only delta, 8 account files содержат один local-only `orders.not_specified`; среди 129 common account values дополнительный mismatch остаётся только в `ru`. REC-009 file scope сузился до header leaf keys, одного local-only account key и RU account value group; R13 подготовлен.

R13 закрыл header key scope (`header.top_sale` only) и сузил RU account до единственной group `orders` (44 production / 45 local leaves).

R14 закрыл file technical scope: 44/44 production identities/counts/types присутствуют локально, 43/44 common values совпадают; local-only=`orders.not_specified`, единственный common value mismatch=`ru/account_new.orders.user_status.print_text`. Два local-only `cn/jp google_reviews.php` сохраняются до locale decision. REC-009 теперь имеет статус `technical evidence complete / owner and cutover pending`; дополнительные discovery hash batches по текущему scope не требуются.

## 17. Текущий вердикт

DB Translation Manager и PHP translation files технически reconciled: DB identities/status совпадают полностью, DB/PHP header value delta равен восьми `header.top_sale`; account delta состоит из восьми local-only `orders.not_specified` и одного RU `orders.user_status.print_text`; два `cn/jp google_reviews.php` local-only. Однако CUT-002/CUT-009 остаются **not implemented / no-go**, пока content owner не подпишет disposition, не создан versioned artifact и не пройдены staging UAT/rollback rehearsal. Следующее действие — owner review по [appendix-language-owner-disposition-matrix-r14.csv](appendix-language-owner-disposition-matrix-r14.csv), а не новый production hash batch.

Условия runtime и полный 14-case protocol закреплены в [51-l3g06-translation-owner-decision-and-staging-uat.md](51-l3g06-translation-owner-decision-and-staging-uat.md) и [appendix-language-owner-uat-protocol-after-r14.csv](appendix-language-owner-uat-protocol-after-r14.csv).
