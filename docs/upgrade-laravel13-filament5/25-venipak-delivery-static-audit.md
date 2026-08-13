# 25. Статический аудит Venipak и доставки

Дата среза: 13.07.2026. Статус: **FN-08 static L2 завершён; sandbox/provider, HTTP ACL, concurrency и production reconciliation L3 открыты**.

## 1. Объём и ограничения

Проверены:

- публичный выбор страны, доставки до двери, Venipak pickup, самовывоза VIAR и доставки по городу;
- `BasketController::setdelivery()`, `get_citys()`, `get_warehouse()` и дублирующие provider readers в `PageController`;
- `VinepakApiController`: pickup lookup, вызов курьера, создание и печать label;
- admin Blade/JS формы, XML payload, pack/manifest identifiers и запись `orders.labels`;
- `venipak_data`, `country_tels`, `delivery_pickup_at_viar_workshop` и агрегаты `orders.delivery` локальной БД;
- существующие тесты и запуск узкого Venipak test suite под PHP 7.4.

Внешние endpoint-ы Venipak не вызывались, credentials и персональные данные не выводились. Выполнялись чтение кода, локальные `SELECT` и тесты с fake response. Детальная матрица: [appendix-venipak-delivery-contracts.csv](appendix-venipak-delivery-contracts.csv).

### Решения владельца от 17.07.2026

- сохранить все текущие варианты доставки и Venipak;
- адаптировать intended courier/create/print/reprint permissions по существующим ролям;
- сохранить пользовательское поведение при provider outage после его runtime characterization.

Эти решения не разрешают переносить отсутствие ACL, browser-authoritative delivery price, бесконечные provider calls или перезапись label без ledger. Без sandbox Venipak остаётся legacy-owned до fake/contract tests и controlled live smoke.

## 2. Карта потока

### 2.1 Public checkout

1. `GET /cart/delivery` вызывает Venipak pickup endpoint дважды через `BasketController::get_citys()` и `get_warehouse()`.
2. При смене страны `POST /get_towns?country=...` снова вызывает provider и возвращает HTML городов/pickup points плюс цены из `country_tels`.
3. Browser отправляет в `POST /cart/setdelivery` выбранные `country`, `delivery_type`, отображаемую `price`, город/адрес либо текст pickup point.
4. `setdelivery()` почти без проверки сохраняет эти значения в session `cart_delivery`.
5. `OrdersController::save_order_and_pay()` переносит session fields в checkout parameters; `Orders::saveOrder()` сохраняет их в JSON `orders.delivery`.

### 2.2 Admin shipment

1. Admin list выводит courier form и label form из Voyager override.
2. `POST /admin/send_courier` собирает XML type 3 и сразу отправляет provider request.
3. `POST /admin/create_label` читает order, собирает XML type 1, отправляет provider request и перезаписывает `orders.labels` ответом.
4. `POST /admin/print_label` принимает произвольный `label_code`, запрашивает provider PDF и отдаёт binary браузеру.

Локального shipment/manifest/label ledger, audit trail, void/cancel endpoint и локального архива Venipak PDF не найдено.

## 3. Маршруты и ACL

| Endpoint | Middleware по статическому registry | Side effect | Вывод |
|---|---|---|---|
| `POST /admin/send_courier` | `web` | вызов курьера | нет `admin.user`, controller auth и role/permission |
| `POST /admin/create_label` | `web` | provider label + `orders.labels` | нет `admin.user`, controller auth и order permission |
| `POST /admin/print_label` | `web` | credentialed provider PDF request | нет `admin.user`, controller auth и ownership |
| `POST /{locale?}/get_towns` | locale web group | credentialed provider lookup | публичен, без throttle/input allowlist/cache |
| `POST /{locale?}/get_towns_for_admin` | locale web group | credentialed provider lookup | несмотря на имя, публичен |
| `GET /{locale?}/get_warehouse` | locale web group | provider lookup | публичен; query input без validation |
| `POST /{locale?}/cart/setdelivery` | web + locale + `preventBackHistory` | session delivery state | цена и доставка принимаются от browser |

CSRF присутствует у POST через `web`, но CSRF не доказывает admin authorization. `artisan route:list` не выполняется из-за существующего missing `App\Http\Controllers\ImageController`; middleware подтверждены независимым route registry и source structure.

## 4. Credentials и configuration

`VinepakApiController::__construct()` пять раз читает первую active row из `venipak_data`. User/password/login ID хранятся в открытых строковых колонках и mass-assignable model. В tracked comments и initial migration присутствуют исторические/default credential values. Их необходимо считать скомпрометированными и ротировать; значения в отчёте не воспроизводятся.

Локально: три config rows, одна active; во всех трёх credential fields непустые, active import/print URLs используют HTTPS и `go.venipak.lt`. Это не доказывает production rotation или безопасное резервное копирование.

Риски configuration:

- секреты находятся в operational DB/backups/admin CRUD, а не в secret manager;
- `import_url` и `print_url` изменяемы из БД без HTTPS/host allowlist;
- отсутствие active row приводит к пустым credentials и fallback URLs вместо fail-closed health error;
- нет environment/sandbox marker, поэтому staging может обратиться к live host;
- нет единого typed client: lookup, import и print дублируют настройки cURL.

## 5. Pickup lookup и доступность checkout

Во всех пяти provider readers отсутствуют connect/request timeout, retry budget, circuit breaker, HTTP status и `curl_errno` handling. TLS verification явно не отключена и поэтому зависит от cURL defaults/CA среды, но отдельная проверяемая TLS policy не задана. JSON shape не валидируется до `collect()`/индексации.

Дублирование:

- `BasketController` и `PageController` вызывают pickup endpoint без credentials;
- `VinepakApiController` отправляет credentials;
- mapping Estonia различается: часть методов преобразует `EE → ET`, часть отправляет `EE` как есть;
- `get_towns()` вычисляет `$filtered`, но выводит исходный `$response`; исключается только type 3 на уровне Blade;
- один рендер delivery page выполняет как минимум два последовательных provider requests без cache.

Provider outage способен задержать/сломать страницу checkout. Target adapter должен иметь короткие timeouts, cache последнего успешного справочника с `fetched_at`, controlled stale fallback, metrics и явный ответ `503/422`, не подменяя доставку нулевой ценой.

## 6. Выбор доставки и цена

`BasketController::setdelivery()` доверяет browser fields `country`, `price`, `delivery_type`, `city`, `index`, `address`, `pickup`, `pickup_workshop_id`, `delivery_town_id`. Server не доказывает:

- что delivery method разрешён для страны;
- что price совпадает с `country_tels` и coupon/payment rules;
- что pickup point получен от Venipak и доступен;
- что workshop/town ID существует, visible и соответствует стране;
- что обязательные адрес, postcode и contact fields заполнены.

Эта session price затем становится `deliv_price` заказа и входит в payment total. Это пересекается с FN-01/R-36, но для FN-08 является самостоятельным delivery authority gap.

Особенно важно: public Venipak pickup сохраняет только отображаемые `city` и `display_name + address`; `data-id` пункта присутствует в HTML, но JS не отправляет и заказ не сохраняет provider point ID, postcode, company code или snapshot working hours. Историческую точку нельзя однозначно восстановить после изменения provider directory.

## 7. Локальный DB-срез

| Метрика | Значение |
|---|---:|
| orders exact | 14 423 |
| `delivery.sposob=venipak` | 4 072 |
| `pickup_at_viar_workshop` | 1 916 |
| legacy `pickup_Riga` | 456 |
| legacy `pickup_Daugavplis` | 208 |
| orders с непустым `labels` | 3 274 |
| distinct label codes после parsing | 3 274 |
| label code, разделяемый разными orders | 0 |
| pickup workshop rows / visible | 10 / 8 |

У `venipak` country заполнена в 4 072/4 072, address в 3 870, city в 3 391, а `postal_index` только в 47. Для workshop/legacy pickup новый `pickup_workshop_id` заполнен лишь у части исторических rows: 203/1 916, 8/456 и 13/208. Это ожидаемо для поздно добавленного поля, но требует compatibility resolver и production reconciliation.

Отсутствие shared label codes в локальном snapshot не доказывает exactly-once: хранится только последнее строковое значение, нет attempt history и provider receipt table.

## 8. Создание label: validation и XML

Положительно: текущий код читает `orders.delivery`, принудительно выбирает pickup destination для четырёх pickup methods и отклоняет pickup без city/address/postcode/name/code. Два существующих теста подтверждают только это поведение.

Критические пробелы:

1. Нет Laravel validator для sender/receiver, страны, postcode, email/phone, веса, объёма, pallet count, COD и enum delivery modes.
2. Address mode отклоняется лишь когда одновременно пусты address, city и postcode; одна заполненная строка проходит.
3. Все XML values конкатенируются без XML escaping/DOM; `&`, `<`, quotes и Unicode edge cases способны сделать XML невалидным или изменить структуру.
4. `generate_label_packs()` предполагает согласованные arrays; отсутствуют проверки count, numeric/range и aggregate weight.
5. `man_no` передаётся, но игнорируется; manifest suffix всегда `001`.
6. Pack number детерминирован как login ID + order ID offset; повтор/concurrency используют тот же номер без локальной claim/receipt state machine.
7. В XML дважды выводится `<delivery_type>`.
8. Provider XML parse выполняется без safe error handling; на provider error вызывается `dd()`, после которого нормальный error response недостижим.
9. Multi-label branch вызывает `rtrim()` без присвоения результата.
10. Успех перезаписывает `orders.labels`, теряя предыдущую попытку/label history.

Pickup order не связывается с сохранённым provider point: admin AJAX загружает текущий список, оператор может выбрать другой пункт, а server проверяет только непустые hidden fields.

## 9. Идемпотентность, повтор, concurrency и lifecycle

Не найдены:

- unique client request/idempotency key;
- shipment/manifest/pack attempt table;
- статусы `prepared → submitted → accepted/rejected → printed → voided`;
- atomic claim до provider call;
- request/response hash и provider receipt;
- retry classification и dead-letter/manual reconcile;
- void/cancel shipment/label;
- courier request ledger;
- optimistic lock при обновлении `orders.labels`.

Сбой после принятия provider, но до DB update приводит к неизвестному результату: безопасно повторить нельзя. Параллельные клики могут отправить одинаковый shipment дважды либо получить конфликт provider. Target должен сначала записать durable attempt с unique business key, затем отправить provider request и фиксировать outcome; неизвестный outcome уходит в reconciliation, а не в автоматический повтор.

## 10. Courier call

`send_courier()` доверяет всем form fields и разбивает дату по `-` без проверки числа частей/валидности/рабочего окна. XML также не экранируется. Отсутствуют duplicate guard, локальный request ID, operator audit, outcome storage и cancel. Provider response parse не защищён от transport/HTML/empty body.

Кнопка должна создавать courier pickup request ровно один раз на approved warehouse/date/time window; изменение окна создаёт новую версию с явной отменой предыдущей, а не скрытый повтор.

## 11. Print label и PDF

`print_label()` принимает `label_code` без validation и без связи с `orders.labels`. Любой пользователь с CSRF token способен инициировать credentialed lookup произвольного кода, потому что route не требует admin auth.

Любой непустой body, первые пять символов которого не `Error`, объявляется `application/pdf`. Не проверяются HTTP status, provider content-type, `%PDF-` signature, max bytes или body hash. `Content-Disposition` формируется из raw code и не закрывает quote. Binary не сохраняется локально и не имеет artifact receipt/audit.

Reprint допустим как read-only операция только для label, принадлежащего доступному order. Нужны Policy, safe filename, byte/MIME/signature validation, `no-store`, audit event и возможность получить ту же provider label без создания новой shipment.

## 12. Наблюдаемость и PII

Логи есть только для отсутствующей страны, rejected label conflict и prepared payload. Нет correlation/request ID, latency/status/attempt metrics и provider error classification. Один info log включает pickup title; это может быть адрес/операционное название и требует data classification/retention.

Нельзя логировать credentials, полный XML, телефоны/email/address или PDF body. Допустимы: internal attempt UUID, order ID, provider operation, sanitized provider code, HTTP class, latency, retryability и response hash.

## 13. Тесты

Запуск:

`C:\OSPanel\modules\php\PHP_7.4\php.exe vendor\bin\phpunit tests\Feature\Admin\VenipakLabelDestinationTest.php`

Результат: **OK, 2 tests, 13 assertions**. Они используют fake label response и проверяют pickup destination/missing point identity.

Не покрыты: anonymous/user/role ACL, `setdelivery` tampering, country/method/price matrix, provider timeout/error/malformed JSON/XML, XML special characters, pack arrays/ranges, duplicate/concurrent create, crash-after-provider, courier duplicate/cancel, print ownership/non-PDF/oversize, config fail-closed и stale pickup cache.

## 14. Target contract

Рекомендуемый модуль `Delivery/Venipak`:

- `DeliveryQuoteService` — server-authoritative method/price/country/coupon rules;
- `PickupPointDirectory` — normalized provider point snapshot, cache, freshness и stable ID;
- `ShipmentService` — validated immutable shipment draft из order snapshot;
- `VenipakClient` — typed lookup/import/print/void adapters, timeout/TLS/host policy;
- `shipment_attempts` и `shipment_packages` — idempotency, provider IDs, hashes, statuses;
- `shipment_artifacts` — label metadata/hash/size, private cache при разрешении;
- Policy/audit/metrics/outbox/reconciliation jobs.

Order JSON сохраняется для backward compatibility, но новые structured fields/receipts добавляются рядом. Старые `orders.id`, delivery strings и `orders.labels` не переписываются массово до доказанного dual-read.

## 15. Минимальная схема receipt

| Поле | Назначение |
|---|---|
| `id`, `order_id`, `provider` | internal identity |
| `operation`, `idempotency_key` | label/courier/print/void exactly-once key |
| `request_hash`, `order_delivery_hash` | immutable input snapshot |
| `provider_manifest_no`, `provider_pack_no`, `provider_label_code` | external receipt |
| `status`, `http_status`, `provider_code` | lifecycle/result |
| `attempt_count`, `last_error_class`, `next_retry_at` | controlled retry |
| `created_by`, `created_at`, `accepted_at`, `voided_at` | audit |
| `response_hash`, `pdf_hash`, `pdf_bytes` | evidence without PII body |

Unique constraints нужны минимум на `(provider, operation, idempotency_key)` и provider label/pack identity, если contract гарантирует уникальность.

## 16. Sandbox/characterization matrix

1. Все delivery methods × supported country × coupon × payment method.
2. Price tampering: negative, zero, foreign country, comma/dot, stale browser price.
3. Pickup: valid/removed/renamed point, wrong country, missing ID, stale cache, provider outage.
4. Address: empty/partial/long/Unicode/`&<>"'`, postcode variants, invalid phone/email.
5. Packages: zero/negative/decimal/oversize, array length mismatch, 1/N packages.
6. Label: success/business reject/HTTP 4xx/5xx/timeout/TLS/empty/malformed XML.
7. Replay/concurrency: double click, same idempotency key, crash before/after provider accept and before DB commit.
8. Courier: invalid/past date, window inversion, duplicate, change/cancel.
9. Print: foreign/missing label, error text, HTML, fake PDF, oversized PDF, safe filename.
10. Roles: anonymous, client, painter, printing, manager, Admin 2, admin; direct URL and missing order.

## 17. Production-safe reconciliation перед реализацией

Без выгрузки PII получить:

- counts delivery methods/countries/years и null quality;
- counts/dedup of label codes, package cardinality и label creation dates из provider/audit logs;
- active/inactive credential rotation metadata без secret values;
- provider supported countries/methods, point IDs и current API contract;
- courier/label error classes, timeouts и manual retry frequency;
- orders, где current pickup point уже отсутствует/переименован;
- finance reconciliation delivery price vs country/method/coupon rule;
- roles, фактически использующие courier/create/print.

## 18. DoR / exit gate FN-08

До реализации Laravel 13/Filament 5 по FN-08 обязательны:

- закрытый ACL для трёх admin operations и публичный rate-limited directory endpoint;
- ротация обнаруженных tracked/default credentials и secret-store decision;
- server-authoritative delivery quote и стабильный pickup point snapshot;
- утверждённые XML/field/country/postcode/package contracts;
- sandbox credentials/fixtures без live side effects;
- durable idempotency/attempt/reconciliation design;
- repeat/reprint/void/courier cancel policy;
- timeout/TLS/allowed-host/cache/stale fallback policy;
- тесты из раздела 16;
- production metadata reconciliation и UAT owner logistics/finance/security.

Пока эти пункты не закрыты, legacy production остаётся единственным write-owner доставки и Venipak. Новый код может работать только в shadow/fake режиме; public frontend и готовые assets не пересобираются.

## 19. Новые риски

- **R-50** — browser управляет delivery method/price/point fields, а order/payment доверяют session snapshot.
- **R-51** — provider lookup без timeout/cache/error contract блокирует checkout и допускает credentialed abuse.
- **R-52** — XML без escaping и строгой validation повреждает/подменяет shipment payload.
- **R-53** — create/courier не имеют durable idempotency/receipt/void lifecycle; неизвестный outcome нельзя безопасно повторить.
- **R-54** — plaintext/tracked/default credentials и изменяемые provider URLs требуют ротации/secret store/host allowlist.
- **R-55** — print endpoint не проверяет ACL/ownership/PDF, а selected pickup point не имеет immutable provider identity.

## 20. Следующее действие

Следующий пакет аудита: **FN-09 — authentication, Socialite, account ownership и четыре chat stream**. FN-08 возвращается к L3 только после sandbox data и владельца логистической приёмки.
