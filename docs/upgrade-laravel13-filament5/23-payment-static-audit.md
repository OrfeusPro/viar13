# 23. Статический аудит платежей: Paysera, PayPal и payment requests

Дата среза: 13.07.2026. Статус: **FN-03 static L2 завершён; sandbox, concurrency и production reconciliation L3 открыты**.

## 1. Объём и правило безопасности

Проверены:

- legacy checkout Paysera: create/accept/cancel/callback;
- legacy checkout PayPal: create/capture/cancel;
- повторная оплата клиентом из кабинета;
- частичные/дополнительные `order_payment_requests`;
- ручное изменение `orders.payment_status` в Voyager;
- письмо об оплате на публичной странице thanks;
- provider identifiers, суммы, статусы, retry и Synvolve side effects.

Проверка не выполняла provider calls и не изменяла заказы. Секреты в документ не копируются. Обнаруженный credential в коде должен считаться скомпрометированным и подлежит ротации.

Построчная матрица: [appendix-payment-contracts.csv](appendix-payment-contracts.csv).

### Решения владельца от 17.07.2026

- сохранить PayPal, Paysera/WebToPay и ручные payment requests;
- сохранить фактическое legacy-поведение partial payment/overpayment/refund до его точной characterization;
- сохранить bonus/coupon/gift-card/mail/Synvolve business effects;
- сделать provider callbacks, manual transitions и page refresh идемпотентными: один provider outcome создаёт не более одного подтверждённого эффекта.

Решение о parity не разрешает копировать выявленные дефекты cancel/success, amount/currency/status verification или повторные effects. Без sandbox provider adapters остаются legacy-owned до contract fixtures, controlled live smoke и reconciliation.

## 2. Точки входа и владельцы

| Контур | Route/trigger | Authority сейчас | Главная проблема |
|---|---|---|---|
| Legacy Paysera | `GET pay_accept`, `pay_cancel`, `pay_callback` | все три signed response path меняют/могут менять состояние | `cancel` копирует success logic; amount/currency не сверяются |
| Legacy PayPal | `Route::any paypal/pay_accept|pay_cancel` | browser return вызывает capture | capture response не проверяется по status/amount/currency/reference/receipt |
| Account retry | auth GET/POST `/new/orders/{order}/payment` | owner order → provider create | сумма рассчитывается отдельным алгоритмом и provider ID перезаписывается |
| Payment request | public token + Paysera/PayPal callbacks | signed/capture return меняет request | нет expiry/ledger/receipt; parent order intentionally не синхронизирован |
| Manual status | `POST /orders/change/payment` | любой web request с CSRF session | route не имеет auth/role; повтор выполняет бонусы, gift cards и mail повторно |
| Thanks | public `GET /thanks?order_id=...` | читает order; при `payed` отправляет mail | refresh повторно отправляет `Payment_successful` |

## 3. Исправление DB baseline

Раннее число `orders = 13 646` было взято из `information_schema.TABLES.TABLE_ROWS`. Для InnoDB это оценка, а не exact count.

Read-only сверка локальной БД 13.07.2026 под явным PHP 7.4 дала:

| Показатель | Exact result | Ограничение |
|---|---:|---|
| `orders` | 14 423 | локальный снимок, не production proof |
| `MAX(orders.id)` | 17 875 | gaps допустимы; ID не перенумеровывать |
| `order_user_comments` | 3 835 | прежние 3 856 также были estimate |
| `translations` | 64 779 | exact count подтверждён повторно |
| payment status: `payed` | 12 252 | строковое legacy-значение сохраняется |
| payment status: `not_payed` | 2 082 | строковое legacy-значение сохраняется |
| payment status: `prepayment` | 89 | строковое legacy-значение сохраняется |
| `order_payment_requests` | 22: 16 paid, 6 pending | 16 paid requests = 841.82 EUR в локальном снимке |

Онлайн-методы представлены реальными локальными rows: Paysera/card/wallet — тысячи заказов; PayPal — 49 заказов, из них 46 `payed`; `billing_invoice_uuid` заполнен у 51 orders, duplicate groups не найдено. Отсутствие дубликатов в снимке не заменяет unique constraint и provider reconciliation.

## 4. Paysera/WebToPay

### 4.1. Создание платежа

`PayseraController::index()`:

- содержит project ID и signing password как константы в tracked PHP-коде;
- принудительно отправляет `test=0`;
- получает сумму уже в minor units (`12.34 → "1234"`) и приводит её к `int`;
- строит callback URL из server globals/locale либо получает URL из payment-request payload;
- при ошибке использует `dd()`; затем выполняет direct header/`exit()`.

В `.env.example` Paysera/PayPal contract полностью не описан. Для target credentials должны идти только через typed config/secret store; после обнаружения hardcode обязательна ротация, а не простое перемещение строки в `.env`.

### 4.2. Accept, cancel и callback

Подпись проверяется `WebToPay::validateAndParseData()`. Но:

- `isPaymentValid()` присутствует, однако ни один handler его не вызывает;
- ожидаемая сумма и валюта из order/payment request с callback не сравниваются;
- `pay_accept()` и `pay_cancel()` практически идентичны: при status `1|3` обе ветки ставят `orders.payment_status = payed`;
- browser accept и server callback оба являются write-authority;
- повтор accept/callback снова сохраняет order и снова отправляет Synvolve snapshot;
- нет provider receipt/transaction table и atomic unique claim;
- callback error выводит class/message наружу;
- order-not-found не обработан отдельным безопасным контрактом.

Официальная документация Paysera прямо требует после signature/status сверить amount/currency и проверить, что заказ ещё не подтверждён; для duplicate callback рекомендуется idempotency check. Текущий код содержит TODO из этого примера, но не реализует проверки: [Paysera — Processing Callback](https://developers.paysera.com/guides/checkout-classic/getting-started/making-your-first-payment/processing-callback).

## 5. PayPal

### 5.1. SDK и credentials

`composer.lock` фиксирует `paypal/paypal-checkout-sdk 1.0.2` и помечает пакет `abandoned: paypal/paypal-server-sdk`. Target spike должен использовать поддерживаемый adapter, но SDK replacement не должен одновременно менять money/status behavior. Текущий официальный PHP Server SDK работает с Orders API v2 и имеет явную retry/timeout configuration: [PayPal PHP Server SDK](https://github.com/paypal/PayPal-PHP-Server-SDK/blob/main/README.md).

Credentials читаются через прямой `env()` внутри service; соответствующие переменные отсутствуют в `.env.example`. Sandbox/production выбор также происходит внутри service, а не через проверяемый config object.

### 5.2. Create/capture

`getRequisites()` создаёт CAPTURE order с `reference_id = local order/public request number`, EUR amount и return/cancel URL. В БД сохраняется только PayPal order ID в `billing_invoice_uuid`.

Подтверждённые пробелы:

- поле `billing_invoice_uuid` индексировано, но не unique;
- новый start для того же order/request перезаписывает предыдущий provider ID; история attempts теряется;
- не сохраняются capture ID, payer/provider status, captured amount/currency, raw event hash и timestamps;
- `checkPayment()` возвращает `true` при любом успешном SDK HTTP call и игнорирует тело capture response;
- controller не проверяет `status=COMPLETED`, reference ID, amount, currency и capture receipt;
- повторный return пытается capture повторно; provider error превращается в локальный error даже если первый capture уже прошёл;
- отсутствует webhook/reconciliation path для случая «деньги списаны, browser return потерян»;
- `Route::any` разрешает лишние методы; POST callbacks при web CSRF могут получить 419, GET остаётся state-changing;
- неизвестный/перезаписанный token приводит к null/404 веткам без reconciliation.

Официальный PayPal flow возвращает в capture response status и purchase-unit payment data; server должен рассматривать их как проверяемый provider receipt, а не только факт отсутствия exception: [PayPal server-side capture flow](https://developer.paypal.com/sdk/js/reference/#onapprove).

## 6. Повторная оплата из кабинета

Положительные свойства:

- route находится под `auth`;
- order выбирается по `id + Auth::id()`; painter блокируется;
- уже `payed` order не отправляется на provider;
- payment method ограничен allowlist.

Риски расчёта и состояния:

- `ClientOrderPaymentService` повторяет money calculation отдельно от checkout;
- `sale_price=0` не признаётся скидочной суммой из-за условия `> 0`, поэтому 100% discount может превратиться обратно в полную цену;
- `sale_eur` и `sale_percent` применяются поверх `sale_price`; надо подтвердить, являются ли они дополнительными или альтернативными скидками;
- payment method сохраняется в order/delivery до успешного создания provider order; failure оставляет изменённый method;
- parsing локализованной строки с несколькими separators не имеет отдельного контракта;
- каждый повторный PayPal start перезаписывает `billing_invoice_uuid`.

Начальный checkout и account retry должны получить один общий `PayableAmount` contract: subtotal after all discounts + terms + delivery, с minor units/rounding и immutable attempt snapshot.

## 7. Payment requests

Положительные свойства:

- создание разрешено method-level только authenticated `admin|manager`;
- amount валидируется `0.01..999999.99`, currency фиксирована EUR;
- token и public number имеют unique constraints;
- `markAsPaid()` не меняет `paid_at` при обычном последовательном повторе; существующий unit test это проверяет.

Открытые риски:

- status domain содержит только `pending|paid`; нет expiry/cancelled/failed/refunded;
- публичный token не имеет срока действия;
- можно создать несколько requests на один order без balance/overpayment rule;
- нет immutable payment attempt/receipt table;
- Paysera callback не сравнивает expected amount/currency request-а;
- PayPal response не проверяется и provider ID может быть перезаписан;
- `markAsPaid()` не использует atomic conditional update/lock и не сохраняет receipt;
- paid request не обновляет parent `orders.payment_status` и не ведёт collected/balance aggregate. Для частичной доплаты это может быть правильно, но правило должно быть утверждено Finance/Operations;
- callback errors выводятся наружу; отдельной reconciliation queue нет.

## 8. Ручное изменение payment status — Critical

`POST /orders/change/payment` зарегистрирован вне admin/auth group и не выполняет `Auth::check()`, role или permission check. CSRF требует web session token, но не заменяет авторизацию: анонимный посетитель может получить собственную session/CSRF token и отправить запрос к произвольному `order_id`.

Метод принимает любой `status_name` типа string и при `payed` до обновления статуса выполняет:

- payment-success mail;
- friend bonus/mail;
- начисление bonus покупателю;
- создание gift-card coupons и PDF для каждой позиции/count;
- gift-card mail;
- затем обновляет `payment_status/use_bonus`;
- затем отправляет Synvolve snapshot.

Previous status не проверяется. Повтор `payed → payed` повторяет mail, bonuses, coupons/PDF и Synvolve. Общей transaction/idempotency boundary нет. Это одновременно security gate и financial idempotency gate; до framework migration endpoint нужно закрыть deny-by-default policy и охарактеризовать существующие side effects.

## 9. Public thanks side effect

`GET /thanks?order_id=...` загружает order по числовому ID и при `payment_status=payed` отправляет `Payment_successful`. Refresh, crawler, preview и повтор provider redirect повторно отправляют письмо. Mail должен быть event/outbox side effect уникального перехода оплаты, а thanks — read-only presentation.

Дополнительно требуется отдельный ownership/PII review страницы thanks, так как order выбирается по query ID без token/owner guard.

## 10. Отсутствующее тестовое покрытие

Существующие тесты покрывают:

- owner/foreign order и allowlist метода в account retry;
- создание payment request;
- последовательную идемпотентность `markAsPaid()`;
- gateway dispatch и восстановление customer snapshot.

Не найдены tests для legacy Paysera callbacks, PayPal capture verification, amount/currency mismatch, duplicate/concurrent callback, manual payment status endpoint, gift-card/bonus duplication, thanks refresh, provider timeout и reconciliation.

## 11. Обязательные characterization/contract fixtures

1. Exact payable amount: no discount; `sale_price`; `sale_price=0`; `sale_eur`; `sale_percent`; combinations; terms; delivery; fractional cents.
2. Paysera: valid/invalid signature, wrong order, wrong amount, wrong currency, status 0/1/3, `payamount/paycurrency`, accept-before-callback, callback-before-accept, cancel with success-shaped payload.
3. PayPal: CREATED/APPROVED/COMPLETED, declined capture, amount/currency/reference mismatch, duplicate return, overwritten provider order ID, lost browser return.
4. Manual: anonymous, user, painter, manager, admin; invalid status; `payed→payed`; `not_payed→payed`; concurrent transition.
5. Side effects: one earned bonus, one friend bonus, one gift-card coupon per unit, one PDF, one payment mail, one Synvolve event.
6. Payment requests: multiple requests per order, partial/overpayment, expired link, sequential/concurrent duplicate, Paysera/PayPal mismatch, refund/cancel policy.
7. Reconciliation: provider paid/local pending, local paid/provider missing, amount mismatch, duplicate receipt, orphan provider order.
8. Locale: all seven public locales in return/cancel URLs and mail; CRM fallback remains separate.

## 12. Target contract и Definition of Ready FN-03

Перед переносом payment module должны быть утверждены:

- immutable `payment_attempts`/receipts с provider, local target type/id, expected amount/currency, provider order/transaction IDs, status, request hash и timestamps;
- unique provider receipt/transaction constraint;
- callback как единственный automatic write-authority; accept/thanks только показывают результат или запускают reconciliation;
- exact server-side amount/currency/reference verification;
- atomic transition `unpaid → processing → paid` и outbox side effects после commit;
- explicit state machine для order и payment request, включая prepayment/partial/failed/cancelled/refunded/expired;
- retry policy, timeout, webhook/callback acknowledgment и reconciliation job;
- secret rotation, typed config, sandbox gates и redacted logs;
- Finance rule, когда partial requests меняют order balance/status;
- provider vs DB reconciliation на production-like data;
- negative ACL tests для manual payment endpoint.

FN-03 остаётся незакрытым до sandbox L3. Никакой target callback не подключается к production, пока replay/concurrency tests не докажут один receipt, один local transition и однократные side effects.
