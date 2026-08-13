# 08. План миграции БД и данных

## Подтверждённый baseline

- MariaDB 10.6.9-MariaDB-log; DB `viar`; 185 tables; 107.58 MiB.
- SQL mode: `ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION`.
- 10 foreign keys; triggers, stored routines и DB events отсутствуют.
- Ключевые таблицы InnoDB.

| Контур | Таблицы / объём | Инварианты |
|---|---|---|
| Заказы | exact: `orders` 14 423, `order_user_comments` 3 835, `order_payment_requests` 22; size snapshot 32.06/1.52/0.13 MiB | `orders.id` сохраняется; `lead_id=orders.id`; status strings неизменны; counts только через exact queries |
| Пользователи | `users` 23 441 / 11.06 MiB | IDs/password hashes/email uniqueness/role links/Facebook ID |
| Voyager | `data_types` 133, `data_rows` 1 997, roles 8, permissions 675, links 2 080, menu_items 166, settings 27 | источник паритета, не удалять до decommission |
| Переводы | `translations` 64 779 / 111 таблиц; `ltm_translations` 21 450; `resources/lang` 334 PHP-файла | все locale rows, base-language semantics, Translation Manager state и runtime dictionaries; URLs/SEO |
| Каталог | `header_menu` 19; `newhome_services` 11 | ordering/show flags/translation completeness |
| SA | `sa_events`, `sa_conversations`, `sa_messages`, `sa_escalations`, `sa_bot_controls`: локально 0 | production объём неизвестен; unique dedupe/message IDs |
| Файлы/PDF | `order_painter_images` 7 199 (28 orphan), `order_user_images` 0; `has_pdf=1` у 6 907 orders | DB reference и binary должны мигрировать одним watermark; private/public classification обязательна |

## Особенности схемы

- `orders.status` — `varchar(255)` с индексом, не enum; значения задаются бизнес-кодом.
- `orders.items`, `delivery`, labels/images/comments и множество SA payload fields — text/longtext с сериализованными/JSON-подобными данными. Их нельзя автоматически cast в JSON до анализа всех строк.
- `users.news`, `ad`, `client_data` — `enum('YES','NO')`; остальные бизнес-флаги смешивают int/varchar/text.
- `translations` содержит `table_name,column_name,foreign_key,locale,value`; повторная локальная schema-проверка подтвердила unique composite index `(table_name,column_name,foreign_key,locale)`. Production index/duplicate evidence всё равно снимается отдельно перед DDL/cutover.
- `ltm_translations` — отдельное рабочее хранилище Translation Manager с логическим ключом `(locale,group,key)`, но локально только с primary index. Локально дубликатов этого ключа нет, schema их не запрещает; до unique migration нужен production duplicate/NULL audit.
- Всего 10 FK на 185 таблиц: orphan detection является обязательным, Eloquent relations не заменяют DB integrity.
- Raw SQL/query expressions найдены минимум в 27 местах; проверить MariaDB behavior и Laravel 10 Expression changes.
- Для InnoDB `information_schema.TABLES.TABLE_ROWS` является estimate: именно поэтому ранее было записано 13 646 orders вместо exact 14 423. Release reconciliation использует `COUNT(*)`, `MAX(id)`, status distributions и контрольные hashes.
- `orders.billing_invoice_uuid` и `order_payment_requests.billing_invoice_uuid` не являются payment ledger: provider attempt может быть перезаписан, capture/receipt ID не хранится, unique provider receipt constraint отсутствует.
- Файловая целостность не обеспечена FK или artifact ledger: локально 28 `order_painter_images` с отсутствующим order. Автоматическая очистка запрещена до production metadata corpus и retention approval.
- `users.pdf_locale` — user-wide override без allowlist/order snapshot; invoice artifact не хранит locale/template/content/file hash receipt.

## Миграционная стратегия

Основной вариант — **не переносить данные в новую предметную схему на первом cutover**. Laravel 13 подключается к восстановленной копии той же схемы; модели/adapters обеспечивают совместимость. Schema evolution выполняется additive:

1. nullable/new tables/indexes;
2. dual-read или backfill малыми batch;
3. проверка counts/checksums;
4. переключение reads;
5. только через 1–2 стабильных релиза удаление legacy columns/tables.

Никогда не reseed/reinsert `orders` и `users`: это изменит IDs. Не нормализовать опечаточные статусы (`sended`, `send_lubanas`, `pegging`).

## Работа с непрерывно меняющимся production

**Подтверждено бизнесом:** сайт продолжает работать, и production будет принимать заказы во время всей программы миграции.

Правила:

1. legacy production остаётся единственным system of record до официального cutover;
2. тестовая/staging БД — временный снимок, который неизбежно устаревает; результаты тестов нельзя применять обратно в production;
3. первый target release предпочтительно подключается к той же production БД после additive backward-compatible migrations;
4. если отдельная target БД всё же обязательна, до разработки нужен отдельный проект CDC/replication с обработкой create/update/delete, порядком событий, retry, lag monitoring и reconciliation; простого «финального dump» недостаточно;
5. backfill выполняется онлайн идемпотентными batch с watermark, повторным проходом по изменившимся строкам и контролем нагрузки;
6. до переключения новые order/payment/chat/SA writes и изменения переводов/контента выполняет legacy-код; одновременный dual-write двумя приложениями запрещён без outbox и доказанного conflict resolution;
7. каждый день/релиз сверяются `COUNT`, `MAX(id)`, заказы по времени/статусу, платежные references, chat/SA events, attachments, а также translation counts/hashes по locale/table/column;
8. непосредственно перед и после cutover выполняется delta-reconciliation. Критерий: ноль потерянных и ноль необъяснимых дублированных заказов/платежей/сообщений/переводов.

Для изменений существующих заказов одного `MAX(id)` недостаточно. Нужны `updated_at`/domain timestamps, provider references и повторная сверка контрольного временного окна. Если timestamp ненадёжен, использовать журнал изменений/outbox, а не предположение.

Аналогично для переводов: одного `MAX(translations.id)` недостаточно, потому что редактор меняет существующую строку или базовое поле родительской таблицы. Нужны `updated_at` как hint, composite key `(table_name,column_name,foreign_key,locale)`, хеш значения, deleted-parent audit и полный anti-join. Отдельно сверяются `ltm_translations` по `(locale,group,key)` и PHP dictionaries по `relative_path,size,SHA-256,PHP lint`; mtime/status publish не доказывают совпадение runtime-файла. Полный контракт: [35-l3g06-catalog-content-translations-cutover.md](35-l3g06-catalog-content-translations-cutover.md).

## Backup и restore drill

До первого изменения production:

- consistent logical dump с routines/triggers/events flags, даже если локально их нет;
- filesystem snapshot `storage/app`, upload disks, `public/storage` target и PDF/labels;
- снимать DB и files на одном согласованном watermark: для каждого private/generated artifact фиксировать relative path, bytes, MIME, hash и reference count без PII/содержимого;
- encrypted off-host copy и срок retention;
- записать GTID/binlog/replication state, если используется;
- восстановить в отдельную БД, проверить login/order/chat/file/payment и сравнить контрольные суммы;
- измерить RTO/RPO и получить sign-off.

Команды определяются DevOps по production-топологии; слепой `mysqldump` без lock/replica strategy не считать подтверждённым backup.

## Сверки данных

До/после каждого этапа:

- exact count и `MAX(id)` по ключевым таблицам;
- count по каждому `orders.status`, `payment_status`, locale и role;
- orphan queries для order→user, comment→order/user, permission_role→role/permission, translations→foreign records;
- duplicate detection для email, `(table,column,foreign_key,locale)`, SA `event_id/dedupe_key/message_id`, payment provider references;
- hash/sample serialized payloads, attachment existence/hash, permission matrix;
- anti-join `order_painter_images|order_user_images → orders`, invoice `has_pdf/pdf_approved → binary`, duplicate path/hash и unreferenced file report;
- 100 контрольных orders: новые/старые, все статусы/оплаты/доставки/локали.
- инкрементальная сверка всех заказов, созданных или изменённых после последнего staging snapshot;
- инкрементальная и полная финальная сверка переводов, base English fields, slugs, ordering и SEO, изменённых после snapshot;

## Индексы и производительность

Любой новый index сначала проверить `EXPLAIN` на production-like copy. Особое внимание: order filters по status/manager/user/created_at, chat order/sent time, SA dedupe/conversation/message, translations composite lookup. Создание индекса на production планировать с online DDL/maintenance window и disk-space headroom.

## Venipak и доставка: additive receipt schema

`orders.delivery` и `orders.labels` остаются legacy compatibility projection. Не нормализовать и не переписывать 14 423 rows до production reconciliation: в локальном снимке 4 072 `venipak` orders, у большинства отсутствует stable provider point ID/postcode snapshot, а 3 274 label codes не имеют attempt timestamps/history.

Additive target tables должны хранить delivery quote/point snapshot, shipment/package attempts, provider manifest/pack/label IDs, idempotency/request/order hashes, lifecycle/error/retry/void timestamps и artifact metadata. Unique claim создаётся до provider call. Backfill старых rows маркируется `legacy_unverified`; он не вызывает provider и не изобретает отсутствующий point ID.

## Rollback и forward fix

Additive migrations должны иметь tested `down` только если откат не теряет новые writes. После dual-write предпочтительнее forward fix или возврат старого app, который игнорирует новые nullable fields. Destructive migration запрещена до истечения rollback window. Stop: count/hash/orphan discrepancy, altered IDs/statuses, missing files, duplicate payment/SA events.

Rollback приложения не должен откатывать production БД к старому backup, если после backup уже появились реальные заказы. При исправной БД возвращается предыдущая версия приложения, а новые записи сохраняются. Восстановление БД допустимо только как аварийный сценарий с point-in-time recovery и последующей reconciliation.

## Identity и четыре chat streams: additive migration

Не объединять `order_user_comments`, `orders_chats`, `order_admin_comments`, `order_painter_comments` и native SA ledger destructively. Сначала построить явную таблицу назначения/actor/source/read semantics, сохранить исходные IDs и добавить nullable compatibility fields либо отдельную нормализованную projection. Исторические orphan rows не удалять автоматически.

Перед DDL получить production counts/orphans/null authors/index usage. Для новых writes нужны immutable message/event ID, actor type/id, source stream, order/conversation link и outbox receipt. Индексы order/time/read добавлять только после `EXPLAIN` и online-DDL оценки. Users migration должна сохранить ID, role, password hash и remember/session compatibility на утверждённое окно; provider identity хранить отдельно с unique provider subject, не заменяя email.

## Mail/outbox/queue additive schema

Добавить immutable domain-event/outbox и mail-delivery ledger: event/message ID, aggregate type/id, template key/version, locale/consent snapshot, recipient reference, artifact receipt, idempotency key, queue/attempt/provider IDs, accepted/delivered/bounced/complained/suppressed/failed/unknown timestamps. PII и body не дублировать без retention/encryption решения.

Abandoned-cart campaign, overdue notification и marketing coupon получают unique claim до dispatch. Existing flags остаются compatibility projection до reconciliation. `jobs/failed_jobs` не являются business receipt. Backfill не отправляет письма и не ставит invented delivered status; historical ambiguity сохраняется.

## Catalogue/translations data migration — FN-11

Локальный exact baseline: `gallery_items=1038`, `gallery_categories=41`, `gallery_sizes=80`, `translations=64779`, `ltm_translations=21450`, `resources/lang=334 PHP-файла в 10 каталогах`. Base IDs, type IDs 2–8, slugs, order/active flags, custom price strings, sale dates, media paths, base fields, Translation Manager rows и все runtime dictionaries сохраняются.

До constraint/index DDL формируется ID-only quarantine: подтверждены 28 orphan item links в category pivot, 37 в size pivot, 31 в color pivot и 66 в room-tag pivot. Автоматическое удаление запрещено. У pivot tables нет подтверждённых indexes на рабочие foreign keys; добавление выполняется additive после duplicate audit и `EXPLAIN` на production-like copy.

Production catalogue/translation edits после T0 переносятся repeatable delta/CDC с tombstones. Таблицы без надёжного `updated_at` получают полный key/hash/anti-join plan. Все четыре слоя — base fields, `translations`, `ltm_translations`, PHP dictionaries — входят в один manifest; два filesystem writers не могут быть активны одновременно. Final gate сверяет counts, key sets, per-table/locale hashes, file lint/SHA-256, orphan cohorts, URL/price fixtures и frozen asset hashes.

## Voyager metadata/data split — FN-12

Snapshot включает `data_types=133`, `data_rows=1997`, `permissions=675`, `permission_role=2080`, `roles=8`, `menus=3`, `menu_items=166`, `settings=27`. Из `translations=64779`: BREAD tables 51 416; `data_rows/data_types/menu_items` 13 003; missing legacy `modular_pics/graph_port_pages/style` 360. Последние две группы не смешиваются с content import: metadata сохраняется как immutable source/mapping, orphan rows — в quarantine. IDs 27/129 stale definitions получают owner decision до target registry.

## SEO/review/feed data continuity — FN-13

Redirect map version, XML snapshot timestamp, catalogue/translation watermark и review/file references входят в cutover manifest. `/translate_item` и `/set_meta` не имеют права писать после T0 вне run-ledger/delta protocol. Local baseline: 1 037 active gallery items, 1 019 с size prices, 43 с sale prices; 4 reviews, все moderation-inactive, order links отсутствуют. Production counts снимаются заново без PII.

Feed/sitemap target остаётся read-only projection. Review writes выполняются additive с immutable upload IDs; rollback приложения не удаляет новые reviews/files. Legacy translation/catalogue writer сохраняется до final delta и отдельного owner switch.
