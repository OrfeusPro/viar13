# 22. Статический аудит корзины, цен и трёх путей создания заказа

Дата среза: 13.07.2026. Статус: **L2 static pass завершён; L3 characterization/runtime остаётся открытым**.

## Цель и границы

Проверены функции, которые формируют корзину, цену, скидку, доставку и заказ в трёх независимых сценариях:

1. публичный checkout;
2. ручное создание/изменение заказа в Voyager;
3. создание lead/order через SA API.

Это аудит существующего поведения, а не предложение немедленно «исправить формулы». Любое спорное legacy-правило сначала фиксируется golden fixture и подтверждается владельцем бизнеса. Production продолжает принимать заказы, поэтому `orders.id`, `price`, `sale_price`, `items`, `delivery`, купоны, бонусы и внешние уведомления считаются критическими инвариантами.

Построчная карта routes/functions: [appendix-basket-order-contracts.csv](appendix-basket-order-contracts.csv).

### Решения владельца от 17.07.2026

- сохранить все текущие order status values и intended transitions без переименования;
- сохранить три пути создания заказа: public, admin и Synvolve/SA;
- сохранить текущие бизнес-эффекты bonus/coupon/gift-card/mail/Synvolve;
- считать `orders.id` неизменным номером заказа и `lead_id` CRM;
- duplicate request/callback не имеет права повторять order или side effect.

«Сохранить как сейчас» означает functional outcome parity после characterization, а не перенос различающихся формул, отсутствующей transaction/outbox boundary или duplicate side effects. До differential fixtures legacy остаётся write-owner.

## 1. Фактическая архитектура

| Контур | Вход | Расчёт/сохранение | Результат |
|---|---|---|---|
| Public | session `basket`, `basket_country`, coupon/bonus и `cart_delivery` | `BasketRepository::getBasketProperties()` → `OrdersController::makeOrder()` → `Orders::saveOrder()` | новый `orders.id`, mail, Synvolve, coupon/bonus mutations, очистка session |
| Admin | POST-поля формы и файлы | `Admin\OrdersController::create_admin_order()` → `create_admin_order_action()` | новый `orders.id`, mail, Synvolve; цена собирается из присланных `basket_price[]` |
| SA | JSON `lead.service_request`, `pricing`, `delivery` | builders → `applyLeadPricingToOrderPayload()` → `saveOrderAsUser()` → тот же `Orders::saveOrder()`; при ошибке возможен minimal order | реальный или временный/minimal order; API `lead_id = orders.id` |

Единого канонического `PricingService` нет. Public использует session и `BasketRepository`; Admin доверяет ручным ценам; SA имеет собственные builders и копию coupon-алгоритмов. Поэтому равенство итогов между тремя путями сейчас не гарантируется кодовой архитектурой и должно доказываться differential fixtures.

## 2. Public cart/checkout: route и state machine

### 2.1. Изменение корзины

- `POST /basket/add` и специализированные `add/portrait|inter|module|construct|future_art|recommended` формируют неодинаковые item shapes.
- `POST /basket/update/count` принимает `count` как `intval`, явного `min:1` в методе нет; сохраняет session и abandoned cart.
- `POST /cart/replace-size` меняет размер/цену в session; источник цены зависит от family.
- `POST /basket/update_prices` только записывает `basket_country`; фактический перерасчёт выполняется позднее при чтении корзины.
- `POST` и `GET /basket/submitbonuses` меняют `spend_bonus`; GET является state-changing.
- `Route::any /basket/coupon_use` меняет user/session; допустимы GET и другие методы.
- `GET /cart/clear_coupon` очищает coupon/bonus state и изменяет user.

### 2.2. Шаги checkout

| Шаг | Handler | Проверки/side effects |
|---|---|---|
| Данные | `cart_step2()` | требует непустую корзину; читает переводы/страны/акции; может записать session-флаги recommendation modal |
| Доставка | `cart_step3()` | требует корзину и authenticated user; синхронно вызывает Venipak дважды через `get_citys()` и `get_warehouse()` без зафиксированных timeout/error contracts |
| Оплата | `cart_step4()` | требует корзину/user; ожидает `cart_delivery['country']` без полной локальной проверки наличия структуры |
| Сохранение/оплата | `GET /save_order_and_pay` | собирает Request из session, вызывает `makeOrder()`, затем payment adapters; GET создаёт заказ и внешние side effects |
| Прямое сохранение | `POST /orders/make` | валидирует набор ключей вручную, пересчитывает basket, вызывает `Orders::saveOrder()` |

## 3. Подтверждённые правила расчёта

### 3.1. Базовая цена

`BasketRepository::getBasketProperties($basket, $contry_mult)` рассчитывает `sumPrice`, `totalPrice`, `totalPriceWithLabels`, `totalPriceWithoutLabels` и при наличии акции — `sale_price`.

| Family/ветка | Фактическое правило | Что зафиксировать fixture |
|---|---|---|
| Gallery/default | `gallery_items.price_from × country multiplier × count`; DB может перезаписать цену из session | DB price является authority, кроме recommendation-веток |
| Canvas interior | `add_price × country multiplier`; `count` в этой ветке не участвует | количество 1/2 и multiplier 1/1.3 |
| Modular interior | `(add_price + ram_price) × multiplier`, затем `sumPrice = price × multiplier` | множитель применяется дважды при multiplier ≠ 1; сохранить как подтверждённый legacy baseline до решения |
| Portrait | `(int) price × multiplier × count` | дробная часть цены отбрасывается до умножения |
| Generic с готовой `price` | `price × multiplier × count` | число/строка/запятая и count 0/1/2 |
| Generic без `price` | `calcPrice()` уже умножает компоненты на multiplier, после чего `sumPrice` снова умножается на multiplier | возможное двойное применение multiplier |

Дополнительно:

- `totalPrice` остаётся суммой до скидки; итог после скидки хранится отдельно в `sale_price`.
- `sale_price` часто возвращается строкой `number_format(..., '.', '')`, тогда как промежуточные суммы — числа.
- `formatedTotalPrice` форматирует именно `totalPrice`, а не обязательно оплачиваемый `sale_price`.
- `terms_price` и delivery price не входят в `totalPrice`; они добавляются отдельными путями при платеже/сохранении.
- товары со специальной меткой отделяются в `totalPriceWithLabels` и для части купонов не получают скидку.

Эти особенности нельзя нормализовать «по здравому смыслу» во время framework migration: сначала требуется одобренный money contract и golden corpus.

### 3.2. Бонусы и купоны

- Public coupon calculation выполняется только при `Auth::user()`; session coupon без авторизации не даёт скидку.
- Бонусы уменьшают товарную сумму до нуля; остаток пишется в session `bonus`.
- Date coupon применяет к eligible subtotal: `(0,21) → 30%`, `[21,31) → 20%`, `[31,100) → 10%`, `[100,+∞) → 5%`. Отдельно тестируются 20, 21, 30, 31, 99, 100.
- Friend coupon вычитает фиксированные 5 из eligible subtotal без floor в обеих реализациях расчёта; при subtotal < 5 возможен отрицательный `sale_price`.
- Facebook coupon даёт 2% на eligible subtotal.
- `30_40` вычитает минимальную unit price одного Canvas 30x40; count явно не участвует.
- `40_60` требует наличие 80x120/120x80 и вычитает минимальную unit price 40x60/60x40.
- `1free` при суммарном count ≥ 4 вычитает одну минимальную unit price.
- universal/abandoned/giftcard поддерживают процент или фиксированное значение; public helper ставит floor 0, если discounted subtotal < 1.
- free_delivery не меняет товарный `sale_price`; нулевая доставка явно реализована в SA, а public contract должен быть подтверждён checkout fixture.
- После public/SA `Orders::saveOrder()` часть купонов удаляется, friend начисляет бонус пригласившему, universal фиксирует дату, user coupon state очищается.

SA повторяет coupon-алгоритмы в `calculateCouponSalePrice()`, а не вызывает public `caclSales()`. Это дублирование уже расходится по окружению/session side effects и создаёт риск будущего расхождения.

## 4. Сохранение public заказа

`Orders::saveOrder()` — 458 LOC и одновременно выполняет:

1. нормализацию телефонов;
2. обновление или создание user;
3. login нового user и регистрационное письмо;
4. сохранение загруженного photo;
5. сборку JSON `delivery`;
6. переименование/перемещение изображений корзины;
7. списание бонусов;
8. `insertGetId` в `orders`;
9. переименование файлов уже по `order_id`;
10. письмо о заказе;
11. синхронный Synvolve snapshot;
12. coupon/friend/universal mutations и `order_action`;
13. очистку session и user coupon state.

Общей DB transaction, checkout idempotency key и durable outbox в функции нет. Ошибка после `orders` insert может оставить заказ созданным, но письмо/Synvolve/coupon/session в промежуточном состоянии; повтор HTTP-запроса может создать второй заказ.

Отдельно подтверждён dormant-risk: controller передаёт в `saveOrder()` массив `$request->all()`, но unauthenticated-ветка вызывает `$checkoutParams->validate(...)` как у объекта Request. Нормальный UI до шага доставки требует user, однако прямой `POST /orders/make` должен получить negative/runtime test.

`makeOrder()` после сохранения отдельно отправляет PDF admin mail и меняет бонусные/user flags — также вне общей транзакции с `saveOrder()`.

## 5. Admin order path

`create_admin_order_action()` строит упрощённый basket вручную:

- item count принудительно `1`;
- `basket_price[]` является входом цены;
- итог повторно суммируется на сервере из `basket_price[]`;
- bonus вычитается из `price` и `sale_price`, но для нового user отдельная логика balance отличается от existing user;
- order получает `is_admin_order=1`, `status=watching`;
- после insert выполняются file rename, mail и Synvolve без общей transaction/outbox.

`update_admin_order()` принимает и сохраняет `price`, `sale_price`, скидки, payment/payment_status и delivery из формы; затем генерирует PDF. Для item-edit существует отдельный перерасчёт. Это означает, что admin является разрешённым manual override контуром, но его полномочия, audit trail и money rounding должны быть утверждены отдельно.

## 6. SA order path

`POST /api/sa/leads` имеет `X-Api-Key`, validation и event/idempotency record, но order creation состоит из нескольких ветвей:

1. поиск/создание user по email/phone;
2. service-specific basket builder;
3. собственная нормализация totals/coupons/bonus;
4. временная подмена web guard и вызов `Orders::saveOrder()`;
5. fallback minimal order с пустым `items` и нулевой ценой, если mapping/save не сработал;
6. дополнительный `ensureOrderExistsForLead()` и linking conversation/status.

Риски:

- claim idempotency события и создание заказа не показаны как одна атомарная transaction;
- crash после claim, но до order/link требует replay contract;
- minimal order внешне имеет настоящий `orders.id`, поэтому downstream должен различать полноценный и временный заказ;
- builders имеют собственные price/multiplier rules; равенство public item shape не доказано;
- `saveOrder()` очищает session и меняет global auth guard, что для API является скрытой зависимостью;
- синхронные mail/Synvolve side effects наследуются из public model.

## 7. Матрица обязательных characterization fixtures

Каждый fixture хранит вход, locale, country multiplier, item JSON, `price`, `sale_price`, terms, delivery, order JSON и ожидаемые local/external side effects.

### Корзина/цена

1. Gallery item: count 1/2, DB price изменён после добавления.
2. Canvas interior: count 1/2, multiplier 1/1.3.
3. Modular interior: ram 0/>0, multiplier 1/1.3.
4. Portrait: price `10.99`, count 1/2, multiplier 1/1.3.
5. Generic item с готовой price и без price/component array.
6. Special-label + ordinary item.
7. Production terms standard/express; gift card без terms.
8. Delivery: door, parcel/pickup, city delivery, workshop, email gift card.
9. count 0, negative, nonnumeric и отсутствующий item index — ожидаемый rejection contract.

### Скидки

10. Date boundaries: 0, 20, 21, 30, 31, 99, 100.
11. Friend eligible subtotal 3/5/10 и special-label mix.
12. Facebook, universal `%`, universal fixed, abandoned, giftcard.
13. 30x40: один item, count 2, несколько кандидатов.
14. 40x60: с/без большого Canvas, count 2.
15. 1free: counts 3/4/5 и cheapest item count >1.
16. free_delivery: товарная и полная payable сумма.
17. bonus меньше/равен/больше total; coupon + bonus rejection.

### Заказ и side effects

18. Один и тот же коммерческий заказ через public/Admin/SA: differential comparison с явно разрешёнными отличиями.
19. Authenticated public, direct unauthenticated POST, expired session.
20. Failpoints до/после user save, file move, order insert, mail, Synvolve, coupon delete, bonus decrement и session clear.
21. Два параллельных public submits; два SA запроса с одним/разными keys.
22. Payment redirect retry/back/refresh для `GET /save_order_and_pay`.
23. Текущие семь locale `lv|lt|pl|ru|de|en|ee`, включая тексты писем/названия опций; CRM `uk → ru` fallback отдельно.

## 8. Definition of Ready FN-01/FN-02

Статический L2 не разрешает начинать рефакторинг money/order ядра. Пакет готов к реализации, когда:

- golden fixtures сняты на отдельной копии production-like БД под явным PHP 7.4;
- бизнес утвердил спорные multiplier/count/rounding/coupon правила;
- public/Admin/SA differential matrix имеет список допустимых отличий;
- все money values нормализованы в контракте (precision, rounding, numeric/string boundary);
- checkout idempotency key и unique receipt определены;
- local DB transaction boundary отделена от durable outbox;
- mail/Synvolve/payment/Venipak выполняются через fake/sandbox в тестах;
- failpoint/retry tests доказывают один `orders.id` и однократные coupon/bonus mutations;
- production delta/reconciliation учитывает новые заказы, созданные во время миграции;
- сохранены все locale и переводы; public assets не пересобираются.

## 9. Решения до начала кодовой миграции

| Решение | Владелец | Безопасный fallback |
|---|---|---|
| Двойной multiplier и пропущенный count: bug или действующий тариф | Product/Finance | сохранить legacy output в compatibility adapter |
| Источник цены по family | Product/Finance | DB/session/manual/SA authority фиксируется в manifest |
| `price` vs `sale_price` vs payable total | Finance | хранить legacy columns, добавить вычисляемый Money DTO без backfill |
| Купоны и отрицательный friend subtotal | Marketing/Finance | сохранить legacy до подписанного правила, заблокировать новые типы |
| Admin manual override | Operations/Security | оставить Voyager write-owner до policy+audit trail |
| Minimal SA order | CRM/Operations | маркировать/фильтровать, не считать полноценным production order без UAT |
| Retry после частичного сбоя | Architecture/Operations | не повторять insert вручную; reconciliation по order/user/time/provider evidence |

## 10. Итог

FN-01 и FN-02 переведены из общего списка неизвестного в **L2 static pass**. Код подтверждает, что критический риск — не только обновление Laravel, а расхождение трёх калькуляторов/входов и неатомарный набор side effects вокруг `orders`.

Следующее точное действие: снять обезличенные production-like basket/order fixtures и выполнить их на staging-клоне под `C:\OSPanel\modules\php\PHP_7.4\php.exe`. До этого формулы и порядок side effects не менять.
