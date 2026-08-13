# S0-06 — Read-only OrderResource, безопасная синтетическая Action и проверка инвариантов заказов/CRM

```text
TASK_ID: S0-06
STATUS: IN_PROGRESS
PRIORITY: Critical
OWNER: программист
BRANCH: codex/s0-06-orders-readonly-safe-action
CREATED_AT: 2026-07-21
UPDATED_AT: 2026-07-21
DEPENDS_ON: S0-03=DONE; S0-04=DONE
NEXT_ACTION: получить одинаковые screenshots legacy/new, зафиксировать field/filter/action parity и расширить read-only OrderResource без включения writers
```

## Цель

Создать первый безопасный OrderResource в Laravel 13 / Filament 5, который
читает существующие заказы без изменения их данных и сохраняет все подтверждённые
CRM-инварианты. После read-only parity реализовать одну изолированную синтетическую
Action с явной авторизацией, идемпотентностью, receipt, failpoint и rollback.

`Read-only` в этой карточке — только временная граница Stage 0 spike. Она не
является конечной границей миграции: полноценные writers заказов, статусов,
чатов, файлов, платежей, доставки и интеграций входят в последующие W1–W7/CUT
задачи и должны быть готовы в изолированном контуре до финального переключения.

## Неприкосновенные инварианты

- `lead_id` равен `orders.id` и не получает отдельную перенумерацию;
- существующие status/payment/delivery codes не нормализуются автоматически;
- production writer остаётся legacy до отдельного cutover;
- суммы, валюты, скидки и состав заказа не пересчитываются generic Filament CRUD;
- связи клиента, художника, печати, файлов, комментариев и SA receipts сохраняются;
- PII не попадает в audit output, fixtures или документацию.

## Входит в задачу

- schema/model/accessor/mutator/cast/route/action inventory;
- read projection ID/status/payment/delivery/price/assignment/file/chat summaries;
- eager loading, pagination, search и role/query scopes;
- deny-by-default Resource/record/action authorization;
- одна reversible synthetic Action без real mail/payment/delivery/webhook;
- idempotency key, transaction/receipt, failpoints и rollback proof;
- differential/zero-delta checks и negative direct URL/Livewire tests.

## Не входит

- production/test writes;
- generic create/delete заказа;
- реальная смена статуса, оплата, доставка, отправка письма или webhook;
- перенос чатов/вложений как writer — это S0-07;
- изменение frontend или расчёта корзины/цены.

## План выполнения

- [x] инвентаризировать `orders` и зависимые таблицы/модели/маршруты/actions;
- [x] зафиксировать exact schema/count/status/relationship baseline без PII;
- [x] определить минимальный read projection и ownership/query scopes;
- [x] реализовать read-only Order model/Resource/list/view;
- [x] покрыть 8-role navigation/direct URL/Livewire/record scope tests;
- [x] выбрать и реализовать одну synthetic reversible Action;
- [x] покрыть idempotency/concurrency/failpoint/rollback/receipt;
- [x] подтвердить zero delta, полный suite и обновить evidence/docs.

## Definition of Done

- [x] `orders.id` сохранён как CRM `lead_id`;
- [ ] значения и связи read projection совпадают с legacy baseline;
- [x] запрещённые роли/records/actions дают DENY/403;
- [x] нет N+1 на выбранных list/view scenarios;
- [x] write gate=false исключает любые target mutations;
- [x] synthetic Action не вызывает real outbound side effects;
- [x] duplicate action возвращает детерминированный duplicate result;
- [x] failpoint/rollback не оставляют partial state;
- [x] local business tables имеют zero unintended delta;
- [ ] tests/evidence/docs PASS.

## Reopened gap: list information and visual parity

После проверки пользователем 21.07.2026 текущий Filament list признан неполным:
это был технический read-only slice, а не эквивалент рабочего legacy экрана.
S0-06 возвращена из `VERIFY` в `IN_PROGRESS`; переход к S0-07 приостановлен.

Legacy `voyager::orders` содержит восемь рабочих зон в строке:

1. номер заказа, VR/BAW/VRR/DS code, invoice/payment-request context;
2. дата, payment status/prepayment, Venipak labels;
3. клиент: имя, телефон, email, status, order count, locale/PDF locale;
4. получатель: контакты, адрес, country/city/postal, delivery date/method и payment method;
5. items: тип/размер/цена/изображения/gift/comment;
6. client/admin/painter/SA comments и unread/image indicators;
7. painter/printing assignments, image previews/statuses, deadline/payment/visibility;
8. order status, связанные активные заказы, send dates, clone/edit/user/history/delete/file actions.

Legacy list также имеет quick cohorts `current/completed/express/in_production/printing/sent_today/not_payed`
и фильтры order ID, payment request, customer composite search, phone, price,
payment status, sales channel, category, country, painter, manager, size и date range.

### Parity checklist

- [ ] получить screenshots legacy и target в одинаковом viewport/state;
- [ ] создать signed field/filter/action matrix с disposition `read now / deferred writer / retired`;
- [ ] перенести безопасные read-only данные зон 1–8 с defensive JSON/orphan handling;
- [ ] перенести quick cohorts и все безопасные фильтры без client-side full scans;
- [ ] сохранить важные color/status/image indicators средствами Filament;
- [ ] проверить responsive/desktop density, horizontal navigation и отсутствие скрытой критичной информации;
- [ ] пройти screenshot comparison/design QA;
- [ ] обновить tests/query budget и повторить zero-delta.

## Отложенные VERIFY-gates

- Миграция receipt-таблицы проверена через `migrate --pretend`, но намеренно не применялась к общей локальной/серверной БД.
- Browser/UAT самой Action выполняется только после отдельной staging DB и явного включения обоих write flags.
- Fresh production counts/hash checkpoint и повтор zero-delta выполняются перед Wave 1; production/test сейчас не затрагивались.

## Реализованный read-only контракт

- Target использует отдельную `App\Models\Order`; legacy `App\Models\Orders` не переносится, потому что её constructor и методы смешивают чтение с записью, файлами, почтой и webhook.
- Зарегистрированы только `/admin/orders` и `/admin/orders/{record}`; create/edit/delete/bulk routes и actions отсутствуют, а `OrderPolicy` явно запрещает все CRUD mutation abilities.
- List projection выбирает только ID, связи пользователей, исходные `items/delivery`, цену, страну, payment/status и timestamps; сумма и JSON не нормализуются и не пересчитываются.
- `admin`, `manager`, `lite_manager` с реальными `browse_orders/read_orders` получают общий read scope; `printing` — только строки из `printing_orders` для текущего пользователя; остальные роли и неизвестные комбинации получают deny-by-default.
- Связи клиента/менеджера eager-loaded, а painter/printing/comments/images/payment-request/VR summaries считаются correlated subqueries. Policy использует уже вычисленный visibility marker, поэтому table actions не порождают per-row authorization query.
- Invalid `items/delivery` не cast-ятся Eloquent автоматически: UI показывает `valid/invalid`, не падая на исторических данных.

## Классификация legacy writers

| Поверхность | Подтверждённое поведение | Решение S0-06 |
|---|---|---|
| `Admin\OrdersController` | создание/обновление заказа, items/price/manager, PDF locale/payment request | не вызывать; только read projection |
| `OrdersController` | статусы, painter/printing assignments, images, payment/VR, bonuses, mail | не вызывать; writers остаются legacy |
| `Account\AccountController` | painter/client status и comment/image updates | не вызывать; writer переносится отдельными задачами |
| `VoyagerAdminController` | assignments, чаты, письма, coupons, файловые операции | не вызывать; чат/file writer относится к S0-07 |
| SA/PayPal/Paysera/Venipak | CRM/webhook/payment/delivery side effects | никаких вызовов из read-only Resource или synthetic Action |

## Evidence

| Дата | Evidence | Результат |
|---|---|---|
| 2026-07-21 | S0-03 auth/session compatibility и S0-04 RBAC/direct URL/Livewire | prerequisites PASS |
| 2026-07-21 | Confirmed audit invariant: CRM `lead_id = orders.id`; production orders continue during migration | frozen contract |
| 2026-07-21 | Local `orders`: 14 423 rows, ID 482…17 875, 66 columns; `price` varchar, `items/delivery` text, table collation latin1 with mixed column collations | compatibility risk captured |
| 2026-07-21 | Current statuses: completed=14 363, in_production=6, pegging=25, watching=29; payment: not_payed=2 082, payed=12 252, prepayment=89 | exact local baseline |
| 2026-07-21 | JSON/parent anomalies: invalid items=0, invalid delivery=227, missing user parent=251 при null user_id=0 | nullable/defensive projection required |
| 2026-07-21 | Child orphans: comments=120, painter images=28, painter assignments=40, payment requests=1, VR numbers=3; printing/images=0 | preserve/report, no auto-delete |
| 2026-07-21 | Target local DB credentials can enumerate unrelated schemas; staging must use database-scoped least-privilege credentials | security gate |
| 2026-07-21 | Production role/permission snapshot: order read access у role IDs 1/admin, 4/manager, 6/printing, 7/lite_manager; printing сохраняет assignment scope | policy contract |
| 2026-07-21 | Target `OrderResource`: only index/view routes; deny-all mutation policy; defensive JSON, eager users + six relationship counts | local implementation PASS |
| 2026-07-21 | `OrderReadOnlyResourceTest`: 6 tests / 32 assertions PASS; все 8 ролей, printing record scope, direct URL, Livewire, invalid JSON, no extra per-record policy queries | automated evidence PASS |
| 2026-07-21 | Real local MySQL projection: 25 rows, 7 load queries and still 7 after 25 policy/relationship reads; invalid delivery row loaded and reported invalid | no-N+1/compatibility smoke PASS |
| 2026-07-21 | Commits `0e9fb0e` (read-only Resource) и `7ef6a11` (receipt/action/rollback) | isolated review points |
| 2026-07-21 | Synthetic service/UI: dual gate, edit permission + record scope, unique key, transaction, receipt, duplicate/conflict, two failpoints, idempotent rollback | local Action contract PASS |
| 2026-07-21 | Full target suite 60 tests / 1 608 assertions; Pint PASS; migration SQL `--pretend` PASS | automated evidence PASS |
| 2026-07-21 | Local shared DB после проверок: orders=14 423, receipt table absent, write gate=0, action gate=0 | zero mutation confirmed |
| 2026-07-21 | Пользовательская проверка `/admin/orders`: внешний вид отличается, большая часть информации legacy list отсутствует | S0-06 reopened; previous list не считать parity |
| 2026-07-21 | Static legacy template inventory: 8 row zones, 7 quick cohorts, 13 filter groups и набор contextual actions | implementation scope expanded |

## Журнал работы

| Дата | Что сделано | Блокер | Следующее действие |
|---|---|---|---|
| 2026-07-21 | S0-06 открыта после перевода local S0-05 в VERIFY | нет | read-only orders inventory и baseline |
| 2026-07-21 | Сняты первые schema/count/status/payment/JSON/orphan baselines без PII | route/action/query-scope inventory ещё не завершён | классифицировать readers/writers и role/record scopes |
| 2026-07-21 | Завершены inventory и read-only slice: отдельные target models, scoped query, deny-all mutation policy, list/view, filters и relation summaries | synthetic Action ещё не реализована | создать dedicated receipt и доказать duplicate/failpoint/rollback |
| 2026-07-21 | Добавлена скрытая по умолчанию synthetic Action с target-owned receipt, idempotency, conflict detection, failpoints и rollback; полный suite PASS | staging DB/action UAT отложены по решению владельца | S0-06 → VERIFY; начать S0-07 локально |
| 2026-07-21 | Пользователь выявил отсутствие visual/information parity; просмотр legacy Blade подтвердил существенный разрыв | browser capture integration недоступна | S0-06 → IN_PROGRESS; получить screenshots и расширить list |

## Stop conditions

- target меняет production/test или включает real outbound;
- `orders.id`/status/payment/delivery semantics заменяются догадкой;
- Resource использует unrestricted generic CRUD;
- query возвращает record вне подтверждённого role/ownership scope;
- Action не имеет idempotency/receipt/failpoint/rollback;
- audit выводит PII, секреты, полные контакты или содержимое файлов/чатов.
