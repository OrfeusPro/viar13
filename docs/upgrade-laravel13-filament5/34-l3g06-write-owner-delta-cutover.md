# 34. L3-G06: write-owner, delta, reconciliation и cutover для заказов, оплат и чатов

Дата: 16.07.2026. Статус: **контракт аудита и планирования**; production и application code не изменялись.

## 1. Решение

Первый production cutover рекомендуется выполнять на **той же актуальной MariaDB и том же файловом storage**, применяя только additive schema changes. Laravel 13/Filament сначала подключается в read-only/shadow режиме, а право записи передаётся по отдельным capability. Перенос в отдельную production-БД до появления доказанного CDC/change journal не рекомендуется.

Это решение вызвано продолжающимися production writes и фактическими особенностями legacy:

- `updated_at` нельзя считать полным watermark: часть `DB::table(...)->update()` не записывает его;
- `orders` физически удаляется без tombstone;
- заказы изменяются public, admin, account, payment и SA-контроллерами;
- payment fact и финансовые side effects распределены между `orders`, `order_payment_requests`, provider callbacks, бонусами, купонами, PDF, mail и Synvolve;
- четыре legacy chat streams имеют разные ключи, флаги и неполную actor semantics;
- основной client stream `order_user_comments` не имеет уникального external message ID в локальной схеме.

Следовательно, одноразовый dump, `MAX(id)` или выборка `WHERE updated_at > watermark` не являются достаточной delta-стратегией.

## 2. Область и границы

В этом gate рассматриваются:

1. создание и изменение заказа;
2. статус и производственные даты заказа;
3. payment status, Paysera, PayPal и отдельные payment requests;
4. четыре legacy chat streams и SA ledger;
5. связанные mail/Synvolve/files/coupon/bonus/PDF side effects;
6. component cutover и forward rollback при работающем магазине.

Не выполняются: изменение production routes/config, установка Filament, миграция данных, повтор payment callback, retry очередей, удаление/очистка строк или файлов.

## 3. Подтверждённые источники

### 3.1 Заказы и оплаты

| Контур | Источник | Локальный exact snapshot | Ключевой факт |
|---|---|---:|---|
| Заказ | `orders` | 14 423 | `orders.id = lead_id`; строковые статусы и ID сохраняются |
| История действий | `order_action` | 6 696 | свободный текст; не является полным mutation audit |
| Отдельные платёжные заявки | `order_payment_requests` | 22 | уникальны `token` и `public_number`; provider receipt/attempt ledger отсутствует |
| Основной payment state | `orders.payment_status` | входит в orders | меняется Paysera, PayPal и manual route; side effects неодинаковы |

Три независимых пути создания заказа:

- public: `OrdersController::makeOrder()` → `Orders::saveOrder()`;
- admin: `Admin\OrdersController::create_admin_order()` → `create_admin_order_action()`;
- SA: `SaIntegrationController::createLead()` и `saveOrderAsUser()`.

Дополнительные write-paths меняют статусы, payment status, items/price/delivery, изображения, PDF, labels, painter assignments и CRM-поля. `Orders::deleteOrder()` физически удаляет строку `orders`; отдельного delete ledger нет.

### 3.2 Чаты

| Stream | Таблица | FK-поле заказа | Локальный exact snapshot | Семантика |
|---|---|---|---:|---|
| client/manager/CRM | `order_user_comments` | `order_id` | 3 835 | client-visible source; `user_id`, `is_admin`, image/read flags; external unique ID отсутствует |
| painter/admin | `orders_chats` | `orders_id` | 2 840 | отдельный stream; SA columns есть, но `sa_message_id` индекс не unique |
| internal admin | `order_admin_comments` | `orders_id` | 106 | newer rows имеют `user_id`; legacy author может быть неизвестен |
| legacy painter note | `order_painter_comments` | `order_id` | 25 | нет sender/read/direction fields |
| SA conversation | `sa_conversations` | `orders_id` | 0 локально | unique `conversation_id`; production volume отдельно не доказан |
| SA message ledger | `sa_messages` | `orders_id` | 0 локально | unique `message_id`; не объединяется по text/time с legacy streams |
| SA event ledger | `sa_events` | event payload | 0 локально | unique `dedupe_key` и `event_id` |

У legacy chat tables нет foreign keys. В локальном snapshot отсутствуют order indexes для четырёх legacy streams. Поэтому orphan/duplicate/ownership reconciliation обязателен, а destructive merge запрещён.

## 4. Главные выводы аудита

### 4.1 `updated_at` не является надёжным watermark

Eloquent `save()` обычно меняет timestamps, но raw updates встречаются в `Orders`, `OrdersController`, account/admin/SA controllers. Например, `Orders::changeOrderStatus()` и `Orders::updateOrder()` не добавляют `updated_at`; read flags чатов также меняются raw update без timestamp. Delta только по времени пропустит часть изменений.

### 4.2 Физические удаления не видны инкрементальному экспорту

`Orders::deleteOrder()` удаляет `orders` и связанные данные отдельными операциями. Без tombstone target не узнает, что строка исчезла. До cutover удаление должно либо запрещаться/заменяться reversible archive, либо фиксироваться в immutable mutation ledger.

### 4.3 Один table owner недостаточен — нужен capability owner

Public checkout должен продолжать создавать заказы, пока мигрируется admin UI. Поэтому ownership делится не просто по таблице, а по capability и полям:

- `order.create.public`;
- `order.create.admin`;
- `order.create.sa`;
- `order.edit.core`;
- `order.status.transition`;
- `order.payment.transition`;
- `order.fulfilment/files`;
- каждый chat stream;
- provider callbacks.

Два интерфейса не должны одновременно менять один capability. Shared DB допустима только при подписанном field/action manifest, optimistic locking и deny-by-default routes.

### 4.4 Payment status не равен доказанному платежу

Paysera accept/cancel/callback и PayPal/manual handlers меняют `orders.payment_status` разными способами. Manual повтор может повторить mail, bonuses, coupons, gift-card PDF и Synvolve. `order_payment_requests` хранит status/paid_at, но не содержит полноценной истории provider attempts/receipts/refunds. Поэтому сверка `payment_status='payed'` сама по себе не доказывает exact-once money transition.

### 4.5 Чаты нельзя объединить одной таблицей во время миграции

`order_user_comments`, `orders_chats`, `order_admin_comments`, `order_painter_comments` и `sa_messages` остаются разными source streams. Target adapter добавляет нормализованное представление, но сохраняет `source_table`, `source_id`, actor ambiguity, timestamps, read flags, attachments и external IDs. Перенос по совпадению текста/времени запрещён.

## 5. Write-owner matrix

Полная машиночитаемая версия: [appendix-l3g06-write-owner-matrix.csv](appendix-l3g06-write-owner-matrix.csv).

| Capability | Shadow owner | Cutover owner | Особое правило |
|---|---|---|---|
| Public order creation | Legacy | Legacy до отдельного domain cutover | Filament не заменяет checkout |
| Admin order creation/edit | Legacy | Filament после parity/UAT | Legacy admin POST закрывается только для этого capability |
| SA order creation/update | Legacy API | Laravel 13 adapter после contract tests | `orders.id=lead_id`, event dedupe и temporary lead сохраняются |
| Order status | Legacy | Один target state service | те же строковые статусы; outbox после commit |
| Provider callbacks | Legacy | Последняя финансовая волна | один automatic authority; receipt/reconciliation обязательны |
| Manual payment status | Legacy | После deny-by-default ACL и idempotency | повтор не создаёт bonus/coupon/PDF/mail повторно |
| Payment requests | Legacy | Target service отдельной волной | `token/public_number` сохраняются; parent order не подменяется |
| Client chat | Legacy | Target adapter после message-id/outbox gate | source остаётся `order_user_comments` |
| Painter/admin chat | Legacy | Отдельная волна | source остаётся `orders_chats` |
| Internal admin notes | Legacy | Первая низкорисковая write-wave | source остаётся `order_admin_comments` |
| Legacy painter notes | Legacy/read-only | Read-only adapter до owner decision | неизвестного автора не угадывать |
| SA ledger | Legacy integration | Laravel 13 adapter после replay tests | не сливать с legacy chat rows |

## 6. Delta contract

### 6.1 Предпочтительный вариант: shared production DB

При первом переключении данные не копируются в другую production-БД. Target читает те же строки и файлы. Delta требуется для доказательства parity и для staging, но не для двусторонней production-синхронизации.

Для каждого capability создаётся checkpoint:

1. exact primary-key set;
2. canonical row hash по утверждённым колонкам;
3. status/payment/read-flag aggregates;
4. orphan/duplicate report;
5. file manifest `relative_path|size|sha256`;
6. timestamp сервера, DB snapshot coordinates и application artifact hash.

`updated_at` используется только как ускоряющий hint. Финальная сверка критичных таблиц выполняется по полному PK/hash set.

### 6.2 Если всё же выбирается отдельная production-БД

До начала записи обязательны:

- durable change journal или CDC для insert/update/delete;
- tombstones для физических удалений;
- idempotent apply по immutable event ID;
- ordering/late-event contract;
- replay from checkpoint;
- reconciliation полного PK/hash set;
- доказанный reverse/forward rollback.

Без этих пунктов отдельная production-БД является `no-go`.

### 6.3 Минимальный mutation envelope

Каждое новое target write должно давать receipt:

```text
mutation_id, occurred_at_utc, actor_id, actor_role,
capability, source_table, source_id, order_id,
operation, before_hash, after_hash,
idempotency_key, external_receipt_id, artifact_manifest_id,
outbox_status, release_id
```

Payload с PII, provider secrets и содержимым файлов в журнал не помещается.

## 7. Reconciliation contract

### 7.1 Заказы

- exact `COUNT`, `MAX(id)` и PK-set diff;
- canonical hash критичных полей: `id,user_id,items,price,sale_price,country,delivery,payment,payment_status,status,labels,created_at,updated_at` плюс согласованные dates/SA fields;
- status/payment distributions;
- неизменность `orders.id` и отсутствие повторного назначения ID;
- orphan links для chat/files/painter/payment requests;
- отдельная сверка JSON после canonical decode/encode, а не byte-string comparison.

### 7.2 Оплаты

- список `order_id/public_number/billing_invoice_uuid` без раскрытия token;
- expected amount/currency/status/paid_at;
- один accepted provider receipt на money transition;
- provider reconciliation для unknown/pending результатов;
- ноль повторных bonuses/coupons/gift PDFs/mails на replay;
- ручной payment transition имеет actor, reason и immutable receipt.

### 7.3 Чаты

- count и PK/hash по каждому source stream отдельно;
- orphan order IDs;
- source ID, order ID, actor/user ID, `is_admin`, direction/read/image flags и timestamps;
- duplicate external IDs там, где они существуют;
- attachment path/size/hash manifest;
- ноль destructive merge и ноль автоматически выдуманных authors;
- read receipt update проверяется отдельно от появления нового сообщения.

## 8. Порядок component cutover

| Волна | Компонент | Почему в таком порядке |
|---:|---|---|
| 0 | Filament shadow/read-only | route/query/ACL/visual parity без writes |
| 1 | Internal admin notes | нет provider money movement; небольшой isolated stream |
| 2 | Admin order read и нефинансовые поля | public/SA creation остаются legacy; нужен optimistic lock |
| 3 | Order status transitions | только после state matrix, mail/CRM outbox и replay tests |
| 4 | Painter/admin chat, затем client chat | каждый stream отдельно; attachments/read flags/SA effects проверяются отдельно |
| 5 | Payment request creation | без замены provider callbacks; status reconcile отдельно |
| 6 | Manual payment transition | после ACL, receipt ledger и exact-once side effects |
| 7 | Paysera/PayPal callbacks | последняя финансовая волна после sandbox/reconciliation |
| 8 | Public/SA order creation | отдельный domain cutover Laravel 13, не часть простой замены Voyager UI |

## 9. Runbook одной волны

1. Утвердить owner, routes/actions/fields, наблюдаемость и rollback owner.
2. Снять off-host DB+files checkpoint и доказать isolated restore.
3. Зафиксировать baseline PK/hash/file manifest и текущий release ID.
4. Остановить **только legacy writer данного capability**; public checkout и остальные модули продолжают работать.
5. Дождаться in-flight request/job/outbox drain и записать final watermark.
6. Выполнить полную final reconciliation, включая delete/PK-set diff.
7. Включить target writer; legacy экран этого capability оставить read-only либо закрыть.
8. Выполнить smoke: allowed actor, denied actor, duplicate request, failure injection, mail/CRM/provider fake.
9. Наблюдать agreed window: error rate, latency, DB writes, queue age, duplicates, unmatched receipts.
10. Зафиксировать sign-off либо выполнить forward rollback.

## 10. Stop/no-go conditions

Волна немедленно останавливается, если:

- legacy и target одновременно могут писать один capability;
- PK/hash/count или file manifest не сходятся;
- найден заказ с изменённым ID, неизвестным status или потерянной payment/chat связью;
- есть unknown provider outcome или callback без receipt;
- duplicate/replay повторяет financial, mail, CRM или file side effect;
- mutation/outbox/alert pipeline не наблюдается;
- checkpoint/restore evidence отсутствует;
- worker restart loop, DB/disk pressure или критическая ошибка остаются без owner;
- rollback требует восстановления старого dump поверх новых production writes.

## 11. Forward rollback

Rollback — это возврат права записи, а не возврат базы во времени:

1. target writer выключается feature flag/route gate;
2. target in-flight mutations/outbox останавливаются и классифицируются;
3. все уже committed target rows и artifacts сохраняются;
4. mutation receipts сверяются с общей DB/provider/files;
5. legacy writer возвращается только после forward reconciliation;
6. новые заказы, оплаты и сообщения не удаляются;
7. provider callback не повторяется без проверки receipt/unknown outcome;
8. общий старый DB dump никогда не разворачивается поверх live production.

Если shared DB и schema additive, legacy читает уже созданные target строки через compatibility contract. Если legacy не может их прочитать, такая schema/API change не допускается к cutover.

## 12. Gate acceptance criteria

L3-G06 можно считать утверждённым только после:

- подписанной capability write-owner matrix;
- field/action manifest для каждой волны;
- полного PK/hash/delete/file reconciliation script на sanitized staging;
- additive migration и legacy compatibility tests;
- payment receipt/reconciliation и chat message-id/outbox contracts;
- isolated restore с измеренными RPO/RTO;
- successful rehearsal минимум одной низкорисковой и одной critical wave;
- доказанного forward rollback без потери новых writes;
- Business/Finance/CRM/QA/DevOps sign-off для соответствующих capability.

## 13. Текущий вердикт

Архитектура безопасного переноса определена, но L3-G06 остаётся **partial / not ready for production cutover**. Можно начинать проектирование Laravel 13 compatibility layer, Filament read-only screens и reconciliation tooling. Включать target writes пока нельзя: отсутствуют restore rehearsal, mutation/tombstone ledger, payment receipts, chat dedupe/outbox, production alerts и подписанная owner matrix.
