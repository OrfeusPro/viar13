# 21. Статический ACL-аудит 58 административных маршрутов

Дата среза: 13.07.2026. Уровень: L2 static audit. Production/staging HTTP-проверки не выполнялись, поэтому FN-06 ещё не закрыт.

## Итог

Факт подтверждён по `routes/admin.php`, route registry и коду контроллеров: все 58 маршрутов из выборки действительно имеют только `web` на уровне route/group и находятся вне группы `admin.user`.

| Статический результат | Маршрутов | Вывод |
|---|---:|---|
| Ожидаемо публичные Voyager login/assets | 3 | оставить публичными после CSRF/throttling/assets tests |
| `VoyagerAdminController::__construct()` добавляет `auth` | 14 | anonymous закрыт; 11 действий не имеют отдельной role/permission проверки, 3 sale actions проверяют `admin|manager` |
| Метод явно проверяет auth и `admin|manager` | 1 | payment request; всё равно нужны negative/amount/idempotency tests |
| Метод получает `Auth::user()` и затем проверяет роль без middleware | 1 | update price: anonymous ожидаемо падает как ошибка приложения, а не отклоняется контрактом 401/403 |
| Не обнаружено route/controller/method auth enforcement | 38 | требуется немедленная staging-матрица; среди них PII, orders, files, mail, SA и Venipak |
| Handler не разрешается | 1 | `POST admin/set_sub_cats`; route указывает не на тот controller |
| **Всего** | **58** | FN-06 остаётся P0 security gate |

Полная построчная матрица: [appendix-admin-acl-routes.csv](appendix-admin-acl-routes.csv).

## Что подтверждено кодом

### 1. Route-level защита отсутствует

В `routes/admin.php:9` открыт общий group только с `prefix=admin` и namespace. Группа `admin.user` закрывается на строке 90; все исследуемые custom routes на строках 94–177 объявлены после неё. `web` обеспечивает session/CSRF, но не требует аутентификацию.

### 2. Контроллеры с подтверждённой защитой

- `Admin\VoyagerAdminController.php:26-29` добавляет controller middleware `auth` для 14 routes.
- В `give_user_sale`, `cancel_user_sale`, `set_sale30_40` дополнительно проверяются роли `admin|manager`.
- `Admin\OrderPaymentRequestController::store()` явно проверяет `Auth::check()` и `admin|manager`.
- `Admin\OrdersController::update_order_item_price()` обращается к `Auth::user()->role`; это не заменяет middleware: anonymous request должен получать управляемый 401/403, а не возможный 500.

### 3. Контроллеры без обнаруженного auth enforcement

На route, constructor и соответствующем method уровне защита не обнаружена у:

- `Admin\OrdersController`: создание/просмотр/изменение заказов, PDF locale, customer lists, coupons;
- корневого `OrdersController`: order items и customer/painter images, review email;
- `Admin\AdminSaIntegrationController`: conversations/messages, bot control, send message, create/bind order, simulator;
- `Admin\Api\VinepakApiController`: courier, create label, print label;
- `Admin\EmailSenderController`: PII list и массовая рассылка;
- `Admin\ImageGenController`: создание/перезапись файлов;
- `AccountController::ajax_check_user`: поиск пользователей по email;
- двух public file-serving closures и `admin/sitemap` closure.

Особенно важно: `AdminSaIntegrationController` не просто допускает отсутствие пользователя, а формирует fallback sender `id=1/name=Admin`; `resolveAdminSenderUserId()` при anonymous выбирает первого пользователя роли 1/4. Это подтверждает, что текущий код не рассматривает сессию как обязательную предпосылку этих действий.

## Подтверждённые критические группы

| Группа | Маршруты | Фактический side effect / disclosure | Статический риск |
|---|---|---|---|
| Orders | create/edit/update, item price/add item | PII, items, totals, delivery, files, PDF, order creation | Critical |
| CRM/SA | messages, bot control, create/bind order, simulator | customer messages, external/internal events, orders/chat writes | Critical |
| Venipak | courier/create/print label | внешний provider call с production credentials | Critical |
| Email/coupons | email sender, Facebook/30×40 actions, review request | массовые/персональные письма, coupons и user flags | Critical |
| Files | public closures, TinyMCE, order images, image generation, audio | раскрытие, загрузка, удаление и перезапись файлов | Critical |
| Admin operations | cache clear/set, notification command | изменение runtime cache и массовые side effects | High/Critical |

## HTTP method и CSRF semantics

Выявлено 15 route registrations, у которых GET разрешён и при этом handler меняет состояние или вызывает side effect:

- image generation и TinyMCE upload объявлены через `Route::any`;
- удаление painter/printing assignment и order images;
- выдача/отмена discount и создание 30×40 coupon;
- две отправки review email;
- cache clear/config+route cache/user notify;
- открытие SA conversation, которое сбрасывает `unread_for_manager`.

Это отдельный дефект контракта независимо от ACL: GET может быть вызван crawler/prefetch/link preview и не должен менять данные, отправлять письма или запускать команды. В target все изменения переводятся на POST/PATCH/DELETE с CSRF и policy; legacy поведение сначала фиксируется characterization tests.

## File-serving finding

Обе public closures удаляют `../` и `..\\` одиночным `str_replace`. Для строки `....//secret.svg` результатом текущей операции становится `../secret.svg`. Это подтверждённый bypass алгоритма нормализации; реальная HTTP-достижимость ещё зависит от Apache/proxy URL normalization и должна проверяться только на изолированном staging с тестовым файлом.

До проверки routes нельзя считать безопасными. Target должен использовать canonical path (`realpath`) и проверять, что результат остаётся внутри разрешённого root; private order/customer files должны дополнительно проверять ownership/permission.

## Route baseline finding

Команда

```text
C:\OSPanel\modules\php\PHP_7.4\php.exe artisan route:list --path=admin
```

13.07.2026 завершилась ошибкой `Target class [App\Http\Controllers\ImageController] does not exist` из-за `GET image/{filename}` в `routes/web.php:519`. После исправления этого blocker следующим известным unresolved route остаётся `POST admin/set_sub_cats`, который указывает на `Admin\OrdersController`, хотя одноимённый метод существует в `Admin\VoyagerAdminController`.

Это не разрешение автоматически перенаправить route: сначала нужен caller/access-log и контракт ответа, затем минимальный baseline fix и повторный cached/uncached route test.

## Обязательная staging-матрица

Для каждой строки CSV выполнить без production side effects:

1. anonymous session;
2. authenticated customer и каждая запрещённая роль;
3. каждая разрешённая роль из фактических восьми ролей;
4. свой/чужой order, chat, user и file;
5. валидный/отсутствующий/неверный CSRF token;
6. duplicate, concurrent и replay для order/mail/SA/Venipak действий;
7. GET/HEAD/OPTIONS не изменяют state;
8. response contract: ожидаемый 401/403/404/405, отсутствие PII и отсутствие provider call.

Для опасных endpoint'ов использовать fake/sandbox и тестовые записи. Не вызывать production Venipak, массовую рассылку, coupons, bot control, notification command или перезапись customer files.

## Definition of Ready FN-06

FN-06 можно перевести в L3/готово только когда:

- 58 строк имеют утверждённого business owner и mapping `route → action → roles → ownership`;
- anonymous/wrong-role tests зелёные и возвращают управляемые 401/403/404;
- все state-changing GET/ANY заменены либо формально приняты как временный legacy risk с WAF/route restriction;
- file routes прошли traversal/IDOR/MIME tests;
- SA/Venipak/mail tests используют fake/sandbox и доказывают отсутствие side effect при отказе ACL;
- unresolved handlers устранены или route удалены после access-log решения;
- cached и uncached route registry совпадают;
- target policies работают deny-by-default и не зависят от наличия пункта меню.

## Следующее действие

1. Получить production access logs за 30–90 дней для 58 routes, `image/{filename}` и `admin/set_sub_cats`.
2. Согласовать 8-role owner matrix с владельцем бизнеса.
3. Поднять изолированный staging snapshot с блокировкой исходящей почты и provider calls.
4. Реализовать legacy negative feature tests до изменения маршрутов.
5. Только после зелёного baseline исправлять ACL и HTTP methods отдельным security-релизом, не смешивая с Laravel/Filament migration.

## Changelog

### 13.07.2026 — L2 static audit

- проверен реальный group context `routes/admin.php`;
- разобраны constructor/method guards девяти затронутых controllers;
- создана построчная матрица 58 routes;
- подтверждены 15 GET-capable side-effect routes и traversal sanitizer bypass;
- повторно подтверждён route:list blocker на PHP 7.4;
- runtime ACL оставлен открытым P0 gate до staging tests.
