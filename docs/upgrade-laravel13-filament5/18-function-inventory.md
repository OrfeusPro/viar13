# 18. Реестр классов, методов и функций

## Методика

[appendix-functions.csv](appendix-functions.csv) построен статическим PHP tokenizer для всех PHP-файлов в `app/`. Зафиксированы class/method/function, visibility/static, строки, LOC, число параметров, сигналы сложности и признаки обращений к БД, storage, HTTP, mail, session/auth, переводам, Voyager, заказам, оплате, SA/CRM, Venipak, логам и `env()`.

Это навигационная карта, а не замена ручному анализу:

- keyword-флаг может быть ложноположительным;
- динамические вызовы, facades, observers и ветвление требуют просмотра;
- `complexity_signal` — счётчик конструкций ветвления, не формальная cyclomatic complexity;
- наличие операции в методе не доказывает её выполнение во всех ветках.

## Итог

| Метрика | Значение |
|---|---:|
| Именованные методы/функции | 1 770 |
| Глобальные функции | 40 |
| Class/trait/interface с методами | 225 |
| Методы LOC ≥100 | 81 |
| Методы с `risk_signal ≥8` | 33 |
| Методы с DB write signal | 105 |
| Методы с external HTTP signal | 28 |
| Методы с CRM/SA signal | 73 |

Флаги payment (129) и translation (495) заведомо широкие: они включают чтение/формирование данных, а не только запись денег или переводов.

## Наиболее опасные hotspots

| Файл и метод | LOC / complexity | Что подтверждено | Решение |
|---|---:|---|---|
| `app/Models/Orders.php:329 saveOrder` | 458 / 50 | user/order write, uploads, email, Synvolve, coupon/bonus, session | characterization first; выделить transaction boundary + outbox без изменения контракта |
| `Admin/OrdersController.php:510 create_admin_order_action` | 246 / 29 | административное создание заказа и side effects | отдельный admin-order work package и ACL tests |
| `OrdersController.php:1604 changeOrderPayment` | 137 / 13 | payment state, bonus/gift/mail/Synvolve | receipt idempotency и provider reconciliation |
| `Api/SaIntegrationController.php:7203 resolveOrCreateLeadId` | 101 / 13 | `orders.id`/temporary lead resolution | invariant tests и race test |
| `Admin/OrdersController.php:758 create_admin_order` | 165 / 21 | order writes из админки | сравнить с public/SA creation paths |
| `OrdersController.php:124 index` | 578 / 47 | крупный public order/account flow | разрезать по наблюдаемому поведению |
| `OrdersController.php:2082 makeOrder` | 149 / 15 | checkout → `saveOrder` + bonus/coupon/session | transaction/outbox test matrix |
| `OrdersController.php:788 renameUploadsPhoto` | 392 / 66 | файловая обработка | corpus, path/MIME/size/rollback tests |
| `Api/SaIntegrationController.php:1170 createLead` | 213 / высокий | SA creation, order domain, idempotency | contract + concurrency suite |
| `DynamicPDFController.php getOrderDataInHtml` | 657 / высокий | большой PDF/locale/order renderer | golden PDFs по locale/status/product type |

Дополнительно в P0/P1 входят Paysera `pay_accept/pay_cancel/pay_callback`, PayPal services/controllers, Blog `storePost/updatePost`, SA catalog builders, admin `update_admin_order/add_order_item/update_order_item_price`, account chat methods и payment request flow.

## Разбиение анализа по функциям

Полный последовательный просмотр 1 770 функций без приоритета даст много текста, но мало контроля. Рабочая единица — **route/command-triggered flow**, внутри которого функции рассматриваются до конечного side effect.

| Пакет | Входы | Что документировать |
|---|---|---|
| F-01 Orders/checkout | Basket + public/admin/SA order routes | validations, totals, IDs, statuses, transactions, email, session, coupon/bonus |
| F-02 Payments | Paysera/PayPal callbacks и payment request | signature, method, receipt, duplicate, amount/currency, reconciliation |
| F-03 Chats/files | account/admin/SA messages | stream/table mapping, read flags, attachments, dedupe, notifications |
| F-04 CRM/SA | 12 API + admin UI | event claim, temporary lead, status mapping, outbox/retry |
| F-05 Translations/content | Voyager BREAD, locale editor, blog/SA/catalog | base locale, `translations`, files, slug/SEO, deletes, write-owner |
| F-06 Delivery | basket delivery + Venipak | address rules, provider request, label idempotency |
| F-07 Identity | Auth/Socialite/account | guard, role, linking, session, locale |
| F-08 Mail/jobs/scheduler | mailables, commands, Kernel schedule | transactional/marketing, retry, timezone, locking |
| F-09 Media/PDF/SEO | upload/file routes, generators, redirects | access, MIME/path, golden outputs, URL preservation |
| F-10 Voyager/Filament | 133 BREAD + custom admin | field/action/filter/ACL/translation parity |

## Карточка функции

Для функции, попавшей в бизнес-поток, фиксируются:

1. callers и достижимые route/command/job;
2. входные типы, validation и доверенные/недоверенные значения;
3. reads/writes с ключами и условиями;
4. transaction/lock/idempotency boundary;
5. external calls, files, mail, queue, session/cache;
6. возврат, exception и HTTP interpretation;
7. текущие тесты и недостающие ветки;
8. target owner, порядок переноса и legacy fallback.

Методы, не достижимые из route/command/job/provider boot и не имеющие подтверждённых callers, не удаляются автоматически: сначала runtime access/log evidence и явное решение retain/retire.

## Минимальный тестовый baseline

В проекте 23 test files, но покрытие распределено неравномерно: SA/CRM получил заметный feature baseline, тогда как основной checkout/order lifecycle, большая часть admin ACL, Paysera/PayPal callback variants, переводимые BREAD и mail previews не покрыты сопоставимо. Следовательно, количество существующих тестов нельзя считать доказательством общего паритета.

До рефакторинга hotspots обязательны characterization tests на фактической PHP 7.4 ветке. После этого один и тот же fixture corpus запускается против legacy и target adapter с нормализацией только заведомо динамических полей.

Точная очередь незавершённого ручного анализа, функции, тесты и критерии допуска вынесены в [20-pre-migration-function-audit.md](20-pre-migration-function-audit.md).
