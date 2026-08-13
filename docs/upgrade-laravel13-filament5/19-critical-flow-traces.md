# 19. Ручная трассировка критических потоков

## Как использовать документ

Это L2-карта фактического поведения, найденного в коде. Она не заменяет runtime fixtures: условные ветки, production config и данные подтверждаются characterization/contract tests. Любое изменение сначала сравнивается с этими инвариантами.

## CF-01. Публичное создание заказа

**Входы:** basket/checkout routes → `OrdersController::makeOrder()` → `App\Models\Orders::saveOrder()`.

**Подтверждённая цепочка:**

1. читаются basket/session/checkout параметры и текущий пользователь;
2. пользователь создаётся/обновляется; новый пользователь может быть немедленно авторизован;
3. файлы заказа сохраняются через Storage;
4. `orders` создаётся через `insertGetId`, полученный ID является `lead_id`;
5. синхронно формируются/отправляются письма и Synvolve snapshot;
6. изменяются/удаляются coupon и user bonus данные;
7. checkout/session state очищается.

**Главный риск:** общий workflow не обёрнут доказанной единой DB transaction, а внешние side effects выполняются рядом с DB writes. Ошибка после insert может оставить заказ без письма/CRM или повторно применить coupon/bonus при retry.

**Инварианты:** `orders.id` неизменен; сумма/валюта/позиции/locale/customer/files сохраняются; повтор браузера не создаёт второй заказ; отказ CRM/mail не откатывает уже принятый заказ молча.

**Target:** короткая транзакция только для локальных данных + durable outbox для mail/CRM; idempotency token checkout; после commit — асинхронные handlers. До cutover legacy остаётся write-owner.

**Тесты:** fixture matrix guest/user, все product types, coupon/bonus/gift, file/no-file, provider unavailable, retry, concurrent submit, seven locales.

**Уточнение L2 13.07.2026:** `getBasketProperties()` применяет multiplier/count неодинаково: modular-interior и generic component-price ветки допускают двойной multiplier; canvas-interior не умножается на count; portrait приводит unit price к `int`. `sale_price` часто является строкой, а delivery/terms добавляются отдельно. Полная матрица: [22-basket-order-static-audit.md](22-basket-order-static-audit.md).

## CF-02. Административное и SA-создание заказа

Есть минимум три независимых write-path: public `makeOrder/saveOrder`, `Admin\OrdersController::create_admin_order*` и `Api\SaIntegrationController::createLead`. Они не должны быть механически заменены одним новым CRUD save.

| Проверка | Почему |
|---|---|
| одинаковые обязательные поля и вычисление totals | иначе канал создаёт неполный/другой заказ |
| author/source/audit | различать client/admin/SA creation |
| `orders.id = lead_id` | CRM invariant |
| temporary lead linking | неизвестный lead не должен занять реальный ID ошибочно |
| status/default timestamps | разные каналы не должны обходить state rules |
| email/Synvolve/coupon/bonus side effects | определить намеренные различия, не унифицировать вслепую |

Решение принимается после differential fixtures всех трёх путей. Общий `OrderApplicationService` допустим только после фиксации различий политиками канала.

**Уточнение L2 13.07.2026:** public пересчитывает basket из session/DB; Admin суммирует присланные `basket_price[]` и использует manual overrides; SA строит basket своими builders и повторяет coupon logic. Все три пути выполняют mail/Synvolve рядом с order/files/user mutations без доказанной общей transaction/outbox. SA может создать minimal zero-price order с настоящим `orders.id`, если mapping реальной услуги не сработал.

## CF-03. Статусы заказа

`OrdersController::changeOrder()` изменяет `orders.status`, `status_date` и дополнительные даты для `watching`, `pegging`, `in_production`, `completed`; рядом находятся mail side effects. В домене также подтверждены `new`, `sended`, `send_lubanas`.

До переноса нужна утверждённая state table:

- допустимый `from → to` по ролям/каналам;
- timestamp, письмо, CRM/SA событие и delivery action на переход;
- повтор того же события;
- запоздавший/обратный webhook;
- terminal/non-terminal статус;
- что видят клиент, администратор, художник и CRM.

Target state machine сначала должна работать в compatibility mode с теми же строковыми значениями. Переименование статусов — отдельный последующий проект.

## CF-04. Paysera и PayPal

**Paysera:** accept/cancel/callback валидируют данные через WebToPay, обновляют заказ, уведомляют Synvolve; callback выводит `OK`. Обработка дублируется между методами. В exception path обнаружен вывод класса/сообщения исключения — это надо проверить на утечку внутренней информации.

**PayPal:** create/capture проходит через SDK service; credentials читаются через прямой `env()` внутри service; controller связывает token с `billing_invoice_uuid`, сохраняет payment status и вызывает Synvolve. Payment request имеет собственный token/status flow.

**Route finding:** PayPal accept/cancel и часть payment-request callbacks зарегистрированы через `Route::any`, то есть допускают GET/POST/PUT/PATCH/DELETE/OPTIONS. Это должно быть заменено на методы, которые реально требует provider, после sandbox evidence.

**P0 контракт:** signature/state; order ID; provider transaction/receipt ID; exact amount/currency; atomic claim; duplicate/concurrent callback; paid-after-cancel ordering; timeout; provider reconciliation; безопасный ответ без stack/class/secrets.

**Уточнение FN-03 L2 13.07.2026:** Paysera signature проверяется, но helper amount/currency нигде не вызывается; `pay_cancel()` повторяет success write; accept и callback оба меняют order. PayPal capture считается успешным по отсутствию exception, без проверки `COMPLETED`, amount/currency/reference/capture ID. `POST /orders/change/payment` не имеет auth/role и повторно создаёт bonuses/gift-card coupons/PDF/mail при `payed→payed`; public thanks повторяет payment mail на каждый GET. Полная карта: [23-payment-static-audit.md](23-payment-static-audit.md).

Rollback приложения не должен откатывать production DB на старый dump: платежи и заказы после dump восстанавливаются через PITR/provider reconciliation.

## CF-05. Чаты и вложения

Фактически используются несколько потоков:

| Таблица | Найденное назначение |
|---|---|
| `order_user_comments` | основной client/CRM stream; source of truth по AGENTS; SA копирует сообщения сюда |
| `orders_chats` | отдельный order chat, записи из account/admin controllers |
| `order_admin_comments` | admin comments |
| `order_painter_comments` | painter stream |

`Account\AccountController` создаёт client/painter/admin-to-client comments, обновляет image/read state и вызывает Synvolve для manager message. `Admin\VoyagerAdminController` пишет admin/order chat и также уведомляет Synvolve. В отдельных ветках есть временная duplicate-проверка, но она не является общим уникальным ключом всех streams.

Target не должен сводить эти таблицы в один stream без утверждённого mapping. Для каждого сообщения сохраняются original ID, order/lead, author/role/channel, timestamp, locale, read state, attachments, external message/event ID и delivery state. Вложения копируются локально с MIME/size/path policy; retry не создаёт вторую копию/комментарий.

## CF-06. CRM/SA

`Api\SaIntegrationController` содержит более **7 300 строк** и обслуживает 12 route. Подтверждены `messagesWebhook`, `sendMessageWebhook`, `pipelineChangedWebhook`, `createLead`, `updateLead`, `createEscalation`, `botControlWebhook`, а также catalog/order reads.

Текущая реализация использует `event_id`/`idempotency_key`, таблицы SA, deterministic outbound message ID, локальное хранение attachments и запись client-visible сообщений в `order_user_comments`. Неизвестный lead создаёт temporary entity согласно принятому контракту.

Оставшийся P0 риск — конкурентная обработка одного события: наличие dedupe-кода не доказывает atomic claim. Нужны два одновременных запроса с одинаковым key, DB unique constraints, transaction test, crash-after-claim и replay/outbox tests.

Локальные SA-таблицы пусты, поэтому production volumes, исторические payload variants и retry order должны быть получены в анонимизированном виде. До этого локальный happy path не считается полным контрактом.

## CF-07. Переводы и контент при работающем production

### Реальные источники

- публичные locale: `lv|lt|pl|ru|de|en|ee`;
- 64 779 rows в `translations`, 111 таблиц; отдельно проверяются 6 `et` rows;
- 21 450 rows в `ltm_translations`, 46 groups; это отдельное рабочее DB-хранилище Translation Manager;
- 334 PHP-файла в 10 каталогах `resources/lang`, включая public locales и отдельные `cn|jp|et`;
- не менее 114 файлов приложения используют/упоминают Translatable, из них 106 — модели в `app/Models`;
- default locale Voyager хранится в base field (конфигурационно `en`), остальные значения — в `translations` по `(table_name, column_name, foreign_key, locale)`;
- `AdminLocaleController` отдельно читает и **перезаписывает PHP-файлы** `resources/lang/{locale}/*.php`;
- Translation Manager редактирует `ltm_translations` и publish-процессом также перезаписывает PHP-файлы;
- Blog integration напрямую читает и делает `updateOrInsert` в `translations`;
- SA catalog напрямую читает `translations` и translatable catalog models;
- runtime translations используются в basket, gallery, pages, mail, PDF, SEO/ALT generation и account.

Следовательно, «переводы» — минимум четыре связанных слоя: base fields, Voyager `translations`, Translation Manager `ltm_translations` и filesystem UI dictionaries. Перенести только таблицу `translations` недостаточно; два PHP-file writers должны быть сведены к одному versioned publisher.

### Правило параллельной работы

Пока production принимает заказы и редакторы меняют тексты, legacy/Voyager является system of record. Для каждого module назначается один write-owner:

1. Filament сначала читает ту же актуальную DB/storage в read-only режиме;
2. перед передачей модуля выполняется baseline + delta reconciliation;
3. Voyager write для конкретного модуля блокируется, затем Filament становится owner;
4. остальные модули продолжают редактироваться в Voyager;
5. rollback возвращает owner, но не восстанавливает старый общий dump.

Если target использует отдельную DB, синхронизируются insert/update/delete для base fields, `translations`, slugs, SEO, ordering, menus/services и parent deletes. Нужны watermark/CDC или audit log, а не сравнение только row count.

### Проверка каждой переводимой сущности

`table/model → translatable fields → base locale → allowed locales → slug/URL → parent delete semantics → admin writer → API writer → public readers → mail/PDF readers → cache → target Resource → reconciliation query`.

`uk` автоматически не включается: в CRM действует утверждённый fallback `uk → ru`. `et` не смешивается с публичным `ee` без решения Content/SEO; `cn` и `jp` не удаляются только из-за отсутствия в public routing. Детальный owner/delta/cutover contract: [35-l3g06-catalog-content-translations-cutover.md](35-l3g06-catalog-content-translations-cutover.md).

## CF-08. Venipak и доставка

`GET /cart/delivery` читает directory минимум двумя provider calls; browser отправляет price/method/point в `setdelivery()`, session переносится в `orders.delivery` и payment total без server quote. Для public `venipak` сохраняются отображаемые city/address, но не stable provider point ID/postcode snapshot. Admin `send_courier/create_label/print_label` имеют только `web`; XML конкатенируется без escaping, cURL не имеет timeout/status/error contract, `orders.labels` перезаписывается без attempt ledger, а print не проверяет order ownership/PDF.

**Уточнение FN-08 L2 13.07.2026:** локально 4 072 `venipak` orders, 1 916 workshop pickup и 3 274 distinct stored label codes; отсутствие дублей в projection не доказывает idempotency. Credentials plaintext в DB и исторически/default tracked, нужна ротация. Полная карта и DoR: [25-venipak-delivery-static-audit.md](25-venipak-delivery-static-audit.md), строки: [appendix-venipak-delivery-contracts.csv](appendix-venipak-delivery-contracts.csv).

P0: deny-by-default admin Policy; server quote; stable point snapshot; typed allowed-host client; validated DTO/XMLWriter; durable unique shipment/courier attempt; unknown-outcome reconciliation; cancel/void/reprint; PDF validation/audit. Provider вызовы только sandbox/fake до go-live.

## CF-09. Files, images, PDF и preview endpoints

Closure routes напрямую обслуживают `storage`, `uploads`, `orders` и admin file paths. Есть отсутствующий `ImageController`, image generation, TinyMCE upload, удаление order images и крупная файловая функция `renameUploadsPhoto`. PDF renderer зависит от order data и locale.

**Уточнение FN-04 L2 13.07.2026:** составлена матрица 40 contracts. Подтверждены неканонический traversal, public predictable invoices, custom admin/public file writes без нужной Policy, account uploads без owner/painter assignment, raw SVG/non-strict base64, неатомарный file→DB switch, PDF renderer с DB writes, broken gift-card route и SSRF/TLS/stream-size gaps SA attachments. Локально 7 199 painter-image rows, 28 orphan; binary corpus sparse. Полная карта: [24-files-pdf-static-audit.md](24-files-pdf-static-audit.md).

Необходимы:

- inventory физического root/public URL/symlink/permissions;
- traversal (`..`, encoded separators), IDOR и executable upload tests;
- MIME по содержимому, size/image dimensions, filename normalization;
- hash manifest до/после migration;
- golden PDF/image outputs по типу заказа и всем `lv|lt|pl|ru|de|en|ee` с `ee→et` contract;
- failpoint suite copy/move/delete/DB switch и artifact receipt;
- SSRF-safe streamed SA attachment download и private delivery;
- закрытие `mail/1..7` preview routes вне local/testing.

## CF-10. Auth, admin ACL и Socialite

Существуют Laravel auth routes, Voyager auth/permissions, кастомные route/controller проверки и Facebook/Google Socialite. Target Filament не может заменить их простым `auth` middleware.

До переноса создаётся role-route-action matrix для 8 ролей:

- anonymous;
- authenticated without permission;
- каждая разрешённая роль;
- ownership (свой/чужой order/file/chat);
- disabled/deleted user;
- Socialite verified/unverified email и provider collision.

Особый gate — 58 `admin/*` routes с route-level `web`: их проверяют реальными HTTP tests на staging, после чего защита переносится в middleware/policies deny-by-default.

FN-09 уточнил поток: standard и custom auth contracts должны быть сведены к одной policy для throttle/CAPTCHA/password/session/verification; Socialite связывает только подтверждённый provider subject через stateful callback и explicit linking. Все account mutations получают scoped order/chat/image binding. Публичный raw user lookup и state-changing utility GET закрываются до миграционных работ.

Чаты переносятся как четыре явно размеченных legacy streams, а не одна условная таблица. На каждом message фиксируются actor, order/conversation, source stream, immutable client/event ID и outbox outcome. Legacy остаётся единственным write-owner, пока differential tests не подтвердят DB row, видимость для ролей, read flags, mail и Synvolve/SA side effects. Native SA ledger остаётся отдельным integration ledger.

## Сквозной release flow при продолжающихся заказах и переводах

1. Legacy остаётся production writer; staging обновляется snapshots/deltas.
2. Target модули проходят shadow read и differential tests на свежих данных.
3. Перед волной фиксируются DB/file watermarks и current write-owner.
4. Переключается только route/module ownership; callbacks имеют общий dedupe store или буфер.
5. Сверяются orders, payments, statuses, chats, files, integration events, translations/base fields/slugs/SEO.
6. При отклонении откатывается приложение/owner, не вся DB.
7. Durable events replay; provider/payment reconciliation выполняется отдельно.

## CF-11. Transactional mail, marketing и scheduler

Текущий flow часто выглядит как `DB/file/provider write → direct SMTP → следующий side effect`. При SMTP error browser/command видит failure после сохранённой business state; retry может повторить order/payment/chat/mail. Target flow: `business transaction + unique domain event → commit → outbox claim → provider attempt/receipt → delivery/bounce reconciliation`.

Marketing отделяется от transactional mail: consent/suppression проверяются при dispatch, unsubscribe signed и template-versioned. Scheduler имеет одного владельца, distributed lock, explicit timezone/TTL, dry-run/max batch и heartbeat. Legacy остаётся write-owner до shadow-outbox parity; неизвестный provider outcome не повторяется вслепую.

## CF-12. Public catalogue → generator → basket/order → SA projection

Текущий flow: localized route выбирает old/new controller, собирает base/Voyager/PHP translations, custom price/options/country multiplier и random content, пишет часть state в session, Blade/ручные JS формируют generator payload, а BasketController создаёт line. SA параллельно строит собственную service/path/size/price projection.

Target flow: stable route/item/service IDs → scoped catalogue query → explicit locale/fallback snapshot → server pricing version → validated generator artifact/options → idempotent basket line → order snapshot; тот же pricing source публикует SA projection. Legacy остаётся catalogue/translation writer до delta reconciliation. Public assets копируются byte-identically, old/new URL ownership переключается по component, не одним релизом.

Stop: public maintenance write, existing item 404, locale/slug/SEO loss, display/cart/order/SA price drift, orphan disappearance, generator upload/state failure либо asset hash/load-order mismatch.

## Что ещё остаётся подтвердить — общий список

- production topology, PHP-FPM/extensions, cron/queue/storage/backup/PITR;
- access logs для неиспользуемых routes/BREAD и missing handlers;
- реальные provider payloads, retries и sandbox credentials;
- production DB triggers/events и SA volumes;
- владельцы UAT по orders, finance, CRM, content/SEO и 8-role ACL.

После этих подтверждений L2-карты превращаются в L3 work packages с точными acceptance tests и оценкой. Очередь работ и Definition of Ready зафиксированы в [20-pre-migration-function-audit.md](20-pre-migration-function-audit.md). До этого оценка 29–52 инженерных недель остаётся диапазоном, а не fixed-price обещанием.

## 11. Voyager module write flow — FN-12

Текущий flow: request входит либо в core `admin.user` + policy, либо в custom `/admin/*` только с `web`/локальной проверкой; DB-driven DataType выбирает controller/fields/actions; custom Blade/JS инициирует CRUD, translation, media или business side effect; изменения могут затронуть DB, файлы, mail, Venipak, SA и cache. Target flow разделяет query/command, explicit ability, validation, transaction/outbox/artifact receipt и audit actor. Для каждого из 133 модулей legacy остаётся writer до final delta; Filament сначала read-only, затем получает ownership одной волной с rollback без восстановления старого DB dump.

## 12. SEO redirect → sitemap/feed → crawler/merchant — FN-13

Текущий flow: route-file boot может выполнить raw redirect; широкие closure/catch-all и localized routes конкурируют; sitemap/feed на каждый request загружают DB/translation/price data и Blade формирует URL/offers; public maintenance/test endpoints параллельно способны менять translations/files/tokens. Target flow: signed redirect dataset → deterministic route responder → versioned read-only SEO/feed projection на конкретном data watermark → cached artifact/ETag → crawler/merchant observation. Maintenance/review/preview отделены и защищены.

Legacy остаётся SEO/feed owner до shadow differential и final catalogue/translation delta. Stop: redirect chain/PL path loss, robots loop, locale/canonical drift, price/feed reject, route-cache mismatch, public writer/debug exposure. Rollback возвращает предыдущую route/map/XML artifact version, не откатывая live orders или DB snapshot.
