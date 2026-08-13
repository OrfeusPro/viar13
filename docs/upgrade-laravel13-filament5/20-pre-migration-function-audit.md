# 20. Реестр незавершённого аудита функций перед миграцией

Дата фиксации: 13.07.2026. Статус: рабочий реестр этапа 0; реализация Laravel 13/Filament 5 не начата.

## Назначение

Документ отвечает на два вопроса:

1. какие функции уже попали только в статический реестр или общую L2-карту, но ещё не разобраны до исполняемого L3-контракта;
2. какие проверки должны быть завершены до начала переноса соответствующего модуля.

Полный машинный перечень находится в `appendix-functions.csv`. Фактические цепочки критических потоков описаны в `19-critical-flow-traces.md`. Этот файл является очередью ручного аудита и Definition of Ready для начала миграции.

## Текущая полнота аудита

| Показатель | Состояние |
|---|---:|
| Именованные методы/функции в L0-реестре | 1 770 |
| Методы LOC ≥100 | 81 |
| Методы с `risk_signal ≥8` | 33 |
| Методы с DB write signal | 105 |
| DB write без статически обнаруженного transaction signal | 99 |
| Ручные L2-карты критических потоков | 10 |
| Полностью закрытые L3 work packages | 0 |

Статический флаг не доказывает дефект. Например, отсутствие слова `transaction` в методе не исключает транзакцию выше по call stack. Поэтому каждое P0-решение подтверждается runtime/characterization-тестом, а не только чтением кода.

## Правило допуска к разработке

- Разрешено начинать этап 0: production inventory, snapshots, fixture corpus, characterization tests, ACL matrix и restore rehearsal.
- Нельзя переносить P0-модуль в Laravel 13, пока его пакет ниже не имеет утверждённого L3-контракта и зелёного legacy baseline.
- Нельзя одновременно менять framework и обнаруженное бизнес-поведение.
- Неиспользуемая функция не удаляется без route/caller inventory и runtime evidence.
- Public frontend не мигрируется и не пересобирается; проверяется только неизменность готовых assets и серверных данных.

## Очередь пакетов

| ID | Приоритет | Пакет | Текущий уровень | Блокирует |
|---|---|---|---|---|
| FN-01 | P0 | Корзина, цены, скидки и доставка | static L2; runtime L3 открыт | public order/checkout |
| FN-02 | P0 | Три пути создания и изменения заказа | static L2; runtime L3 открыт | order domain |
| FN-03 | P0 | Paysera, PayPal и payment request | static L2; sandbox L3 открыт | payment cutover |
| FN-04 | P0 | Файлы, изображения, PDF и file serving | static L2 завершён; L3 открыт | storage/media/orders |
| FN-05 | P0 | Переводы и content writers | L2 по storage, L1 по функциям | content/Filament |
| FN-06 | P0 | ACL 58 административных endpoints | static L2; runtime L3 открыт | любой admin перенос |
| FN-07 | P0 | CRM/SA concurrency и basket builders | endpoint L2, internals L1 | CRM cutover |
| FN-08 | P0 | Venipak и доставка | static L2 завершён; sandbox/runtime L3 открыт | delivery cutover |
| FN-09 | P1 | Auth, Socialite, account и chat ownership | обзор L2 | identity/account |
| FN-10 | P1 | Mail, queue, jobs и scheduler | L1 | background processing |
| FN-11 | P1 | Public catalogue и генераторы | L1 | public-site parity |
| FN-12 | P1 | 133 Voyager BREAD и custom admin | static/data L2; production/browser L3 открыт | Filament module waves |
| FN-13 | P1 | SEO redirects, previews и auxiliary APIs | static/data L2 + limited read-only runtime; production L3 открыт | SEO/security/release |

## FN-01. Корзина, цены, скидки и доставка — P0

### Функции для ручного разбора

- `app/Repositories/BasketRepository.php:407 getBasketProperties()` — 271 LOC;
- `app/Repositories/BasketRepository.php:680 caclSales()` — 171 LOC;
- `addToBasket()`, `findAlternativeSizeForCanvas()`, `findAlternativeSizeForGalleryItem()`;
- `formCanvasTypeProperties()`, `formCanvasTypeData()`, `formCollageTypeData()`;
- `app/Http/Controllers/BasketController.php:321 setdelivery()`;
- `addToBasketPortrait()`, `addToBasketConstruct()`, `addToBasketInterier()`;
- `coupon_use()`, `replaceItemSize()`, `cart_step2()`, `cart_step3()`, `cart_step4()`.

### Подтверждено статическим L2

- единой формулы нет: multiplier/count/rounding различаются по family;
- modular-interior и generic component-price могут получить multiplier дважды;
- canvas-interior не учитывает count, portrait отбрасывает дробную часть unit price;
- `totalPrice` и `sale_price` имеют разный смысл и местами разные numeric/string types;
- date/friend/30_40/40_60/1free имеют boundary/count semantics, требующие golden fixtures;
- state меняют GET/ANY routes (`submitbonuses`, `clear_coupon`, `save_order_and_pay`, `coupon_use`).

Полная карта и fixture matrix: [22-basket-order-static-audit.md](22-basket-order-static-audit.md).

### Обязательные тесты и критерий выхода

- golden fixtures всех product families, locale, currency, delivery и discount combinations;
- одинаковые totals на legacy UI, order row, payment request и CRM payload;
- double submit/concurrent tab не создаёт расхождение корзины;
- утверждённая таблица расчёта и сохранённый fixture corpus;
- L3-карточка фиксирует inputs, reads, session writes, errors и target owner.

## FN-02. Создание и изменение заказа — P0

### Уже частично трассировано

- `app/Http/Controllers/OrdersController.php:2082 makeOrder()`;
- `app/Models/Orders.php:329 saveOrder()` — 458 LOC;
- `app/Http/Controllers/Admin/OrdersController.php:510 create_admin_order_action()`;
- `Api\SaIntegrationController::createLead()`.

Общая цепочка и основные различия закрыты статическим L2: public использует session/DB calculator, Admin — manual `basket_price[]`, SA — builders и собственный coupon calculator. Не закрыты runtime/failure/concurrency contracts.

### Функции, требующие отдельной L3-карточки

- `OrdersController.php:124 index()` — 578 LOC;
- `OrdersController.php:1604 changeOrderPayment()`;
- `OrdersController.php:1901 save_order_and_pay()`;
- `OrdersController.php:2317 approve_user_checkout()`;
- `OrdersController.php:2421 add_order_item()`;
- `OrdersController::changeOrder()`, `add_client_images()`, `remove_painter_image()`;
- `Admin/OrdersController.php:758 create_admin_order()`;
- `Admin/OrdersController.php:924 update_order_item_price()`;
- `Admin/OrdersController.php:1044 update_admin_order()`;
- `Admin/OrdersController::edit_admin_order()`.

### Обязательные решения и тесты

- differential fixtures трёх creation paths: fields, totals, default status, timestamps, author/source;
- side-effect matrix: email, Synvolve/SA, coupon, bonus, files, session;
- утверждённая state transition table для семи строковых статусов;
- idempotency и failure injection после каждого локального write/внешнего side effect;
- DB transaction boundary только для local state и durable outbox после commit;
- критерий: ни один ID/статус/side effect не изменён без бизнес-решения.

Дополнительный gate: direct unauthenticated `POST /orders/make` должен быть охарактеризован отдельно — controller передаёт array в `saveOrder()`, а guest-ветка вызывает object-style `validate()`.

## FN-03. Платежи — P0

### Функции

- `Libwebtopay/PayseraController.php:92 pay_accept()`;
- `Libwebtopay/PayseraController.php:149 pay_cancel()`;
- `Libwebtopay/PayseraController.php:210 pay_callback()`;
- PayPal create/capture service и `OneTimePayPalController::pay_accept/pay_cancel`;
- `OrdersController::changeOrderPayment()`;
- payment request create/open/expire/success/cancel handlers;
- account-side `buildClientPaymentOrderData()`.

### Подтверждено статическим L2

- Paysera signature есть, но expected amount/currency не сверяются; `pay_cancel` содержит success write;
- accept/callback — два write-authority без receipt claim; повторяет Synvolve;
- PayPal capture body игнорируется, capture/provider receipt не сохраняется, token перезаписывается при новом start;
- public manual payment endpoint не имеет auth/role и повторяет bonus/gift-card/mail side effects;
- thanks GET отправляет payment mail повторно;
- payment requests не имеют expiry/refund/balance ledger, а parent order status не агрегируется.

Полная route/side-effect/fixture matrix: [23-payment-static-audit.md](23-payment-static-audit.md).

### Критерий выхода

Sandbox fixture corpus утверждён Finance/Dev; повтор callback всегда детерминирован; двойное списание/двойной status невозможны; rollback приложения не требует старого DB dump.

Дополнительный gate: hardcoded Paysera signing credential ротирован; callback secrets/config не находятся в tracked code; manual status endpoint закрыт policy до любых sandbox tests.

## FN-04. Файлы, изображения и PDF — P0

### Функции и endpoints

- `app/Models/Orders.php:788 renameUploadsPhoto()` — 392 LOC;
- `OrdersController::add_client_images/remove_painter_image/remove_painter_sketch_image`;
- account/admin chat attachment methods;
- `AdminLocaleController::remove_media()`;
- TinyMCE/image generation upload handlers;
- `storage/{path}`, `uploads/{path}`, `orders/{path}` и admin file closures;
- `app/Http/Controllers/DynamicPDFController.php:26 getOrderDataInHtml()` — 657 LOC.

### Результат static L2

Созданы [24-files-pdf-static-audit.md](24-files-pdf-static-audit.md) и матрица [appendix-file-pdf-contracts.csv](appendix-file-pdf-contracts.csv): 40 route/function/storage contracts. Подтверждены public/foreign-order write paths, account IDOR, public predictable invoice URL, raw SVG/non-strict base64, разрозненные upload limits, неатомарный `renameUploadsPhoto`, PDF renderer с DB side effects, gift-card route arity mismatch и SSRF/MITM/memory-DoS в SA attachment downloader.

Read-only baseline: 14 423 orders; 7 199 `order_painter_images`, из них 28 без order; локальные binary directories являются sparse copy. В `app/` найдено 123 файловых sink-вызова в 26 PHP-файлах. Runtime HTTP, production corpus и failpoint L3 ещё не выполнены.

### Критерий выхода

Есть production metadata-only file corpus, negative security suite и golden PDF/image outputs на всех локалях; private files выдаются только через ownership Policy; failpoint tests доказывают, что ни один файл не теряется, не перезаписывается и не становится публичнее.

## FN-05. Переводы и content writers — P0

### Функции

- `Api/BlogIntegrationController.php:283 storePost()`;
- `Api/BlogIntegrationController.php:430 updatePost()`;
- `Admin/Controller.php:46 insertUpdateData()`;
- `AdminLocaleController::index/show/edit/relation/remove_media`;
- Voyager BREAD write paths для 106 translatable models;
- SA catalogue builders и Blog API writers;
- SEO/ALT apply commands, mail/PDF/public readers.

### Проверить

- base English field и `translations` для каждой table/column;
- PHP dictionaries `resources/lang/{locale}/*.php` и их production deploy semantics;
- insert/update/delete, slug/URL, ordering, parent delete и cache invalidation;
- `lv|lt|pl|ru|de|en|ee`, отдельную классификацию шести `et` rows и запрет автоматического включения `uk`;
- write-owner Voyager/Filament/API и delta reconciliation при живом production.

### Критерий выхода

Утверждён manifest `model/table → fields → locales → writers/readers → URL/SEO → target Resource`; locale/table/column/file hashes совпадают после shadow run и переключения владельца.

## FN-06. ACL административных endpoints — P0

### Объём

58 `admin/*` routes имеют на route/group уровне только `web`. Проверяются контроллеры заказов, SA, Venipak, image generation, email sender, files, cache и custom Voyager actions. Наличие controller-level проверки фиксируется отдельно и не предполагается по имени route.

L2 static pass завершён в [21-admin-acl-static-audit.md](21-admin-acl-static-audit.md), построчная матрица — [appendix-admin-acl-routes.csv](appendix-admin-acl-routes.csv). Зафиксировано: 38 routes без обнаруженного auth enforcement; 14 с controller `auth` (только три из них дополнительно проверяют `admin|manager`); один payment route с явным method auth+role; один price route с неуправляемым `Auth::user()` dereference; три ожидаемо публичных; один unresolved handler. Это не заменяет HTTP-тесты.

### Матрица тестов

- anonymous;
- authenticated user без permission;
- каждая разрешённая из восьми ролей;
- ownership своего/чужого order/file/chat;
- disabled/deleted user и устаревшая session;
- CSRF для web, API key/signature для integration routes;
- bulk/custom actions, прямой URL без navigation item.

### Критерий выхода

Каждый endpoint имеет утверждённый owner, role/action mapping, ожидаемый HTTP status и negative feature test. Target использует deny-by-default middleware/Policies; доступ не наследуется из URL или меню.

Дополнительно закрыты как отдельные решения: 15 GET-capable routes с side effects переведены на безопасные HTTP methods; file-serving closures проходят canonical-path/traversal tests; production mail/Venipak/SA вызовы блокируются fake/sandbox при ACL-тестировании.

## FN-07. CRM/SA concurrency и builders — P0

### Уже известно

12 API routes, `lead_id=orders.id`, temporary entity для неизвестного lead, `X-Api-Key`, локальная копия attachments и event/idempotency fields.

### Не разобрано до L3

- `SaIntegrationController::createLead()` и `resolveOrCreateLeadId()` по всем race/error веткам;
- `createTemporaryOrder()`, `createSaLeadUser()` и linking temporary → real order;
- `resolveServiceToBasket()`, `buildCanvasBasketFromServiceRequest()`, `buildModularBasketFromServiceRequest()`;
- pipeline/message/escalation/bot event ordering;
- `AdminSaIntegrationController::resolveSimulatorDefaults()`;
- atomic event claim, crash-after-claim, outbox/retry/dead-letter.

### Критерий выхода

Два одновременных запроса с одинаковым key дают один side effect; DB unique/transaction boundary доказаны; production payload variants обезличены; replay восстанавливает delivery без второго order/message/file.

## FN-08. Venipak и доставка — P0

### Функции

- `Admin/Api/VinepakApiController::get_towns/get_warehouse`;
- `create_label()`, `print_label()`;
- `buildLabelPayload()`, `generate_label_xml()`, `generate_courier_xml()`;
- basket/page delivery selection и admin courier action.

### Результат static L2

Полная карта: [25-venipak-delivery-static-audit.md](25-venipak-delivery-static-audit.md), 40 контрактов: [appendix-venipak-delivery-contracts.csv](appendix-venipak-delivery-contracts.csv). Подтверждены route-level `web` у трёх admin operations; client-authoritative delivery price/method/point; отсутствие stable provider point ID в public orders; duplicated lookup без timeout/cache/error mapping; plaintext/tracked credentials; XML без escaping/strict validation; отсутствие attempt ledger/idempotency/void/reconciliation; arbitrary print code без ownership/PDF validation.

### Критерий выхода L3

ACL/owner matrix, production metadata и credential rotation; server quote и directory snapshot; address/country/postcode/package contract; typed client with timeout/TLS/allowed-host/cache; durable unique receipt; crash/replay/concurrency reconciliation; courier cancel и label void/reprint; sandbox/fake/PDF suite. До gate legacy остаётся sole write-owner.

## FN-09. Auth, account, Socialite и chats — P1

### Функции

- Laravel/Voyager login, reset, remember и guard behavior;
- Facebook/Google callback и account linking;
- `AccountController::orders()` — 238 LOC;
- `send_client_painter_comments()`, `send_admin_to_client_painter_comments()`;
- `update_order_chat()`, painter image/read-state methods;
- client payment order data.

### Проверить

Existing password hashes, provider collision/no-email/unverified email, locale/session, foreign-order ownership, четыре chat streams, read flags, duplicate submission, attachment ownership и Synvolve notification. Критерий — role/ownership matrix и deterministic message mapping без слияния таблиц.

## FN-10. Mail, queue, jobs и scheduler — P1

Проверяются 33 Mailables, три Notifications, `GenerateImageAltJob`, abandoned-cart commands, overdue notifier, `GenSmallImages`, `withoutOverlapping`, timezone и фактический production `QUEUE_CONNECTION`.

Критерий: классификация transactional/marketing, consent/rate/bounce policy, fixture каждого шаблона на семи locale, retry/failed/restart tests, стабильная serialization shape между releases и подтверждённый worker/lock owner.

### 14.07.2026 — FN-10 static/data L2 pass

- создана матрица 78 mail/queue/scheduler contracts;
- подтверждены 33 Mailables, 3 Notifications, 5 scheduled commands, queue=sync, cache=file и отсутствие worker manifests;
- обнаружены public mail previews с token/coupon writes и hardcoded private data paths;
- подтверждены SMTP TLS verification off и SwiftMailer boot blocker;
- рассчитаны без запуска команд локальные cohorts: 108 first-cart candidates до cleanup, 2 coupon candidates, 59 overdue due, 162/252 active campaign coupons с `news=NO`;
- зафиксированы consent/unsubscribe, outbox/idempotency, locale/remote-assets, PII logging и alt/media retry gaps;
- FN-10 оставлен открытым до containment R-66/R-67/R-72, production inventory и staging/provider/worker L3 tests.

## FN-11. Public catalogue и генераторы — P1

### Функции

- `GalleryController::hb_item_render()` — 412 LOC;
- `GalleryController::hb_type_render()` — 316 LOC;
- `GalleryItem::getItems()`;
- `SharjController::index()`, `SimpsonsController::index()`;
- portrait/oil/royal и module generator controllers;
- catalogue/services/sizes/media builders.

Public CSS/JS остаются замороженными, но backend HTML/JSON, порядок данных, URL и изображения должны совпасть. Критерий — golden response corpus, desktop/mobile screenshots и browser-console diff для каждого генератора и locale.

## FN-12. Voyager BREAD и custom admin — P1

Для каждого из 133 `data_types` фиксируются model/table, fields, validation, relations, scopes, ordering, filters, translations, uploads, custom Blade/controller/actions, roles, write-owner и решение `migrate|retire|read-only archive`.

Критерий: подписанный владельцем module manifest; Filament Resource/Page не считается готовым только по CRUD — требуется паритет custom actions, ACL, translations и files.

### 14.07.2026 — FN-12 static/data L2 pass

- создана построчная матрица всех 133 BREAD с model/table/controller/fields/relations/permissions/rows/translations;
- подтверждены 131 рабочая model/table пара, 2 stale definitions, 6 custom controllers, 7 server-side modules и 40 custom Voyager views;
- подтверждено 52 custom admin route declarations после `Voyager::routes()` вне `admin.user`;
- доказан неполный resource contract custom `orders` controller;
- разобраны 64 779 translations: 51 416 BREAD content, 13 003 Voyager metadata/menu и 360 legacy/orphan table rows;
- зафиксированы `ee/et/uk`, PHP lang writer, media/Compass/BREAD builder и one-writer cutover risks;
- FN-12 оставлен открытым до production logs/snapshot, owner-signed decisions всех 133 модулей, staging role/browser parity и per-module cutover rehearsal.

## FN-13. SEO redirects, previews и auxiliary APIs — P1

Активный dataset — 256 `multiLangRedirect` rules → 2 048 routes; исторический `redirect_old.php` с 550 declarations не подключён. Проверены duplicates/chains/PL catch-all, image debug, sitemap/feeds/robots, public translation/meta/review/mail/gift-card, Google/Synvolve и route cache/missing handlers.

### 14.07.2026 — FN-13 static/data L2 pass

- созданы 256-row redirect map и 47-row route contract matrix;
- подтверждены 7 duplicate groups, 4 conflicting source groups, 3 chains, 13 space и 72 non-ASCII rules;
- локально XML valid: 7 sitemap files, 1 174/1 140 product/image URL, 7 125 feed offers; application robots self-redirect;
- обнаружены public bulk translation/meta writes, review upload/ownership gap, gift-card signature mismatch, mail/test/debug exposure и feed sale-variable leak;
- route cache блокируют 2 003 closures; registry — missing `ImageController`; duplicate route-name groups = 5;
- FN-13 оставлен открытым до P0 containment, production access/Search Console/merchant evidence, signed PL/redirect contract, golden parity и canary/rollback rehearsal.

Критерий: crawl/status/location/hash/price parity, access-log решение retain/retire, закрытые preview/test/mutating endpoints вне authorized context и отсутствие closure/missing handler blockers перед route cache gate.

## Production evidence до начала переноса P0

| Evidence | Владелец | Gate |
|---|---|---|
| PHP CLI/FPM, extensions, DB/web server topology | DevOps | AUD-001 |
| cron/workers/queue/session/cache/locks | DevOps | AUD-003 |
| backup, restore drill, RTO/RPO, binlog/PITR | DevOps/DBA | DB-001 |
| anonymized order/payment/SA/Venipak payload corpus | Finance/CRM/Dev | TST-001/002 |
| access logs для missing/unused routes и BREAD | DevOps/Business | AUD-002/VOY-001 |
| 8-role ACL owner matrix | Security/Business | AUD-006 |
| translation/content writers и write-owner schedule | Content/SEO | AUD-007/DB-005 |
| frozen public asset URL/manifest/SHA-256/screenshots | Frontend/QA | FE-002 |
| UAT owners для orders, payments, CRM, content и admin | Product | DEP-002 |

## Definition of Ready для Laravel 13 skeleton

Skeleton можно создавать как рабочую target-ветку после выполнения условий ниже. Production traffic к нему не подключается до последующих gates.

- AUD-001/002: подтверждены production topology и рабочий route/cache baseline;
- DB-001: restore rehearsal успешен и измерены RTO/RPO;
- FN-01/FN-02: golden basket/order fixtures проходят на PHP 7.4;
- FN-03: payment sandbox/duplicate/concurrency suite согласована;
- FN-04: file/PDF security и golden corpus зафиксированы;
- FN-05: manifest переводов и write-owner утверждены;
- FN-06: ACL matrix 58 routes завершена;
- FN-07/FN-08: provider contract fixtures и retry/idempotency доказаны;
- FE-002: public assets заморожены и сверены по URL/hash/screenshots;
- открытые P0-риски имеют владельца, срок и явно принятый fallback.

## Порядок выполнения

1. FN-06 ACL и route baseline — немедленный security gate.
2. FN-01 корзина/цены и FN-02 orders — основной бизнес baseline.
3. FN-03 payments и FN-07 CRM/SA — деньги и внешние события.
4. FN-04 files/PDF и FN-08 Venipak — необратимые внешние/файловые side effects.
5. FN-05 translations/content — непрерывные production-изменения.
6. FN-09/FN-10 identity/chat/mail/jobs.
7. FN-11/FN-12/FN-13 public/admin/SEO parity.

После каждого пакета обновляются: текущий уровень L1/L2/L3, evidence links, тесты, открытые вопросы, migration action и rollback owner.

## Changelog

### 13.07.2026 — первоначальная фиксация

- сведены незавершённые проверки из route/function inventory и critical flow traces;
- выделены восемь P0 и пять P1 function-audit packages;
- добавлены точные функции, тесты и критерии допуска;
- зафиксировано, что ни один L3 work package пока не закрыт.

### 13.07.2026 — FN-06 static L2 pass

- создана полная ACL-матрица 58 административных routes;
- проверены route group, constructor и method-level guards;
- выделены 38 routes без обнаруженного auth enforcement и 15 GET-capable side-effect routes;
- подтверждён bypass одиночного `str_replace` в двух file-serving closures;
- FN-06 оставлен открытым до staging HTTP matrix и owner/role approval.

### 13.07.2026 — FN-01/FN-02 static L2 pass

- создана детальная карта public/Admin/SA путей создания заказа и 30 route/function contracts;
- зафиксированы family-specific multiplier/count/rounding правила и coupon boundaries;
- подтверждены ручной Admin price authority, отдельный SA calculator и minimal-order fallback;
- описаны 23 группы golden/differential/failpoint fixtures;
- FN-01/FN-02 оставлены открытыми до runtime corpus под PHP 7.4 и бизнес-утверждения money rules.

### 13.07.2026 — FN-03 static L2 pass

- создана матрица 30 payment routes/services/data/side-effect contracts;
- подтверждены Paysera amount/currency gap и ошибочная success logic в cancel;
- подтверждены неполная PayPal capture verification и отсутствие immutable receipt ledger;
- обнаружен public manual payment-status endpoint без auth/role и повторяемые financial side effects;
- исправлен DB baseline: 13 646 было InnoDB estimate, exact orders count — 14 423;
- FN-03 оставлен открытым до secret rotation, ACL fix, sandbox/concurrency fixtures и provider reconciliation.

### 13.07.2026 — FN-04 static L2 pass

- создана матрица 40 file/image/PDF/attachment contracts;
- зафиксированы 123 файловых sink-вызова в 26 PHP-файлах и sparse локальный corpus;
- подтверждены public invoice/attachment paths, ACL/ownership gaps, raw SVG/base64 и upload policy drift;
- доказана неатомарность filesystem/DB switch в `renameUploadsPhoto()` и DB side effects PDF renderer;
- обнаружены gift-card route arity mismatch, 28 orphan painter-image rows и SSRF/TLS/streaming gaps SA attachments;
- FN-04 оставлен открытым до production corpus, HTTP security, failpoint и seven-locale golden PDF tests.

### 14.07.2026 — FN-09 static L2 pass

- создана матрица 56 auth/Socialite/account/chat contracts;
- подтверждены parallel standard/custom auth, CAPTCHA bypass path и plaintext password в registration mail path;
- обнаружены public raw user lookup, auth-only IDOR и возможность обычного user записать admin message с mail/Synvolve side effects;
- разобраны четыре legacy chat streams и local DB aggregates без чтения message text/PII;
- подтверждены unsafe Socialite state/linking/redirect semantics, отсутствие message idempotency/outbox и DOM/HTML injection paths;
- FN-09 оставлен открытым до немедленного security containment, production-safe aggregates, staging role×ownership/OAuth/session tests и утверждения chat mapping/retention.

### 14.07.2026 — FN-11 static/data L2 pass

- создана матрица 72 public catalogue/generator/SA/asset contracts;
- подтверждены public DB-write utility GET routes и signature mismatch `/set_sizes`;
- доказан current 404 новой item route из-за category lookup по `false`;
- сняты read-only counts: 1 038 items, 41 categories, 80 sizes, 64 779 translations;
- найдены 28/37/31/66 orphan item links и отсутствие рабочих foreign indexes у четырёх pivots;
- зафиксированы 7 public locales, `ee/et/uk` boundary, pricing/session/SEO risks и SHA-256 12 public outputs без rebuild;
- FN-11 оставлен открытым до security hotfix, production traffic/data/query plans, browser golden matrix, delta reconciliation и component cutover rehearsal.

### 14.07.2026 — FN-12 static/data L2 pass

- добавлены `29-voyager-bread-custom-admin-static-audit.md` и `appendix-voyager-bread-modules.csv`;
- подтверждены exact DB metadata counts и фактическая 8-role permission matrix;
- классифицированы 133 modules, custom controllers/views/routes, dynamic BREAD routing и operational tools;
- отдельно зафиксированы content, Voyager metadata, PHP dictionaries и legacy/orphan translations;
- определены 8 шагов переноса, per-module test contract, risks R-92–R-107 и one-writer rollback model;
- production/browser/UAT L3 остаётся открытым.

### 14.07.2026 — FN-13 static/data L2 pass

- добавлены `30-seo-redirect-preview-auxiliary-static-audit.md`, `appendix-seo-redirect-map.csv` и `appendix-fn13-route-contracts.csv`;
- исправлена документация: активны 256 macro rules/2 048 routes, а `redirect_old.php` 550 не подключён;
- выполнены read-only DB aggregates и XML validity/count snapshot без вызова mutating/provider endpoints;
- добавлены risks R-108–R-124, security/SEO/route-cache work packages и live-production delta/rollback gate;
- основные L2 пакеты FN-01…FN-13 завершены; production L3 readiness остаётся открытой.

### 14.07.2026 — P0 auxiliary containment plan

- подтверждены routes и side effects, для которых нужен default-off gate;
- подготовлен проект production double opt-in и порядка middleware;
- временный прототип и его тесты удалены после уточнения scope; application code не изменён;
- SEO-001 и R-111/R-114/R-115/R-117/R-120 остаются открытыми до отдельной реализации и production evidence.

### 14.07.2026 — P0 review submission security plan

- прослежены два реальных POST: current `reviews` и legacy `our_works`; сняты только обезличенные DB aggregates;
- подтверждены email/order ownership, upload/base64, spam/replay и stored-XSS gaps;
- проект auth/throttle, MIME/size/WebM/locale, cleanup и escaped-render проверялся временным прототипом, который удалён;
- R-116/R-126/R-127 и SEO-006 остаются открытыми до реализации, quarantine/AV/retention/translated UX и production evidence.
