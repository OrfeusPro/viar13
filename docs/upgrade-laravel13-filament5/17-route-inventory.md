# 17. Полный реестр маршрутов

Дата среза: 13.07.2026. Инвентаризация выполнена загрузкой Laravel Router через явный `C:\OSPanel\modules\php\PHP_7.4\php.exe`. Приложение и БД не изменялись.

## Результат

В [appendix-routes.csv](appendix-routes.csv) зафиксированы **все 5 311 зарегистрированных маршрутов**. Для ежедневной работы выделен сокращённый [appendix-application-routes.csv](appendix-application-routes.csv): **335** application routes и route closures без массива SEO redirect closures.

| Класс | Маршрутов | Назначение |
|---|---:|---|
| Redirect closures | 1 990 | legacy/SEO redirects и locale aliases |
| Voyager package | 1 745 | package routes BREAD/admin |
| Voyager extension | 1 201 | локальные расширения Voyager |
| Application handler | 322 | controllers проекта |
| Other vendor | 29 | Debugbar/package endpoints |
| Route closures | 13 | files, sitemap, mail preview и др. |
| Unresolved handler | 11 | зарегистрированная точка входа без callable handler |
| **Всего** | **5 311** | один route с несколькими HTTP methods считается одной строкой |

HTTP method occurrences: GET — 3 275, POST — 1 773, PUT — 153, PATCH — 151, DELETE — 153, OPTIONS — 15. `Route::any` увеличивает каждое применимое значение.

## Что хранит CSV

`methods`, `uri`, `name`, `handler`, `controller`, объявленные route/group `middleware`, `domain`, факт наличия handler, `source_file`, `source_line`.

Ограничение: поле `middleware` отражает middleware маршрута и его групп. Middleware, добавленный в `controller::__construct()`, в это поле не входит и проверяется вручную. Поэтому значение `web` означает «на route/group уровне только web», но само по себе ещё не доказывает доступ без авторизации.

## API-контур

Зарегистрированы 24 API route:

- 5 route без `integration.api_key`: Google rating/reviews и три Synvolve test receiver route;
- 7 blog integration route под `integration.api_key`;
- 12 SA/CRM route под `integration.api_key`: сообщения, каталог, размеры/цены, lookup заказов, pipeline, leads, escalation и bot control.

Перед production обязательны отдельные решения по доступности test receiver endpoints, IP allowlist и rate limiting. `X-Api-Key` нельзя логировать.

## Критический вывод по `/admin`

У **58 `admin/*` routes** на уровне route/group зафиксирован только `web`. В список входят нормальные исключения (`admin/login`, package assets), endpoints контроллеров с собственной авторизацией, а также чувствительные кастомные операции:

- просмотр/изменение/создание заказов и изменение цены позиций;
- Venipak: courier/label/print;
- order/chat operations и customer files;
- email sender, coupon/sale и cache operations;
- SA conversations, bot control, create/bind order и simulator;
- image generation/uploads.

Ручная проверка показала неодинаковое поведение:

- `Admin\VoyagerAdminController` добавляет `auth` в конструкторе;
- `Admin\OrderPaymentRequestController::store()` выполняет собственную проверку `Auth` и роли;
- у `Admin\OrdersController`, `Admin\AdminSaIntegrationController`, `Admin\ImageGenController`, `Admin\EmailSenderController` и `Admin\Api\VinepakApiController` аналогичная защита на верхнем уровне не подтверждена.

Это **Critical finding на аудит авторизации**, а не утверждение, что все 58 routes уязвимы. На изолированном staging необходимо для каждого route проверить anonymous, authenticated wrong-role и allowed-role сценарии. До такой проверки переносить ACL «как кажется» нельзя.

Подробный L2-разбор guards, side effects, GET mutations и обязательных тестов находится в [21-admin-acl-static-audit.md](21-admin-acl-static-audit.md), построчная матрица — в [appendix-admin-acl-routes.csv](appendix-admin-acl-routes.csv). Статический итог: 38 routes без обнаруженного auth enforcement, 14 с controller `auth`, 3 ожидаемо публичных, 1 с явным method auth+role, 1 с неуправляемым `Auth::user()` dereference и 1 unresolved handler.

## Неразрешимые handlers

| URI / группа | Проблема | Действие |
|---|---|---|
| `GET image/{filename}` | отсутствует `App\Http\Controllers\ImageController@show` | выяснить назначение URL; реализовать/удалить только после access-log и SEO проверки |
| `POST admin/set_sub_cats` | отсутствует `Admin\OrdersController@set_sub_cats` | проверить UI caller; исправить baseline или зафиксировать retire |
| 9 `admin/orders*` Voyager BREAD actions | `App\Http\Controllers\OrdersController` не реализует ожидаемые `order`, `action`, `update_order`, `restore`, `relation`, `remove_media`, `create`, `store`, `destroy` | разобрать конфликт BREAD controller и custom order routes |

Из-за первого отсутствующего класса стандартный `artisan route:list` падает. Реестр собран через Router без разрешения controller middleware; исправление baseline остаётся задачей `AUD-002`.

## Дубли имён

Найдены 5 route names с двумя регистрациями: `edit_admin_order`, `send_photo_form`, `hb.gallery.ram_search`, `kurpirkt`, `submit_bonuses`. Часть дублей намеренна (GET/POST или alias), но `route()` выбирает только одну регистрацию, а cache/порядок загрузки может изменить результат. Для target каждый alias должен получить уникальное имя или явный compatibility test.

## Route closures, требующие решения

- file serving: `storage/{path}`, `uploads/{path}`, `orders/{path}`, а также admin user/gallery files;
- `admin/sitemap`;
- публичные `mail/1` … `mail/7` previews;
- массив legacy redirect closures.

Особенно проверить traversal/IDOR/private file policy и закрыть mail previews вне local/testing. Redirect dataset переносить как данные с crawl/hash проверкой, а не переписывать вручную по одной строке.

## Приоритет переноса

1. **P0 Security/baseline:** отсутствующие handlers, `/admin` ACL, test/mail endpoints, file serving.
2. **P0 Money/orders:** checkout, order create/update/status, callbacks, payment requests.
3. **P0 Integrations:** SA/Synvolve, Venipak, SMTP, OAuth.
4. **P1 Content/locale:** Voyager BREAD, locale editor, blog integration, slugs/SEO.
5. **P1 Public:** catalog, gallery, pages, account, basket.
6. **P2 Compatibility:** redirects, aliases, low-use admin modules after access-log evidence.

## Definition of Done по каждому route

Route можно перенести только когда зафиксированы: owner; auth/role/CSRF/rate-limit; request validation; response/status/redirect; tables/files; external calls; mail/queue/session/cache side effects; idempotency; negative tests; legacy-vs-target parity; rollback owner. Полный список строк для такой работы находится в CSV, а критические цепочки — в `19-critical-flow-traces.md`.

## FN-09 route confirmation

Статически подтверждены standard auth routes, custom AJAX login/register, четыре Socialite routes, old/new account mutation groups и admin chat actions. `POST /admin/check-user` зарегистрирован только с `web`; account mutations преимущественно имеют только `auth`. Live `artisan route:list --path=auth` после запуска БД всё ещё блокируется отсутствующим `App\\Http\\Controllers\\ImageController`, поэтому registry snapshot остаётся рабочим evidence до исправления AUD-002.

## FN-10 mail route confirmation

Public route group содержит `/mail/test`, gift/cart/order previews и семь static mail previews. `MailController::abandoned_cart()` пишет recovery token, `abandoned_cart24()` способен создать coupon, а order/gift previews используют hardcoded private references. Эти routes классифицированы как R-66 и должны быть local-only/admin synthetic без production data и side effects.

## FN-11 catalogue route confirmation

Статически разобраны 72 contracts: parallel old/new gallery routes, painter/taxonomy filters, item pages, canvas/collage/modular/family/portrait/caricature/Simpsons и четыре SA catalogue reads. `/set_sizes|set_genre|set_style` являются public DB-write utilities; `/set_sizes` также не соответствует обязательной method signature. GET/POST ram search имеют одинаковое route name.

Новая `/gallery/{type}/item/{item}` после type canonicalization вызывает category lookup с `false` и abort 404 при текущих данных. Runtime `route:list` снова подтверждённо блокируется missing `ImageController`; до исправления route boot FN-11 остаётся L2 static evidence, а не L3 HTTP inventory.

## FN-13 SEO/auxiliary route confirmation

`redirect.php` активен и создаёт 2 048 routes из 256 rules; `redirect_old.php` (550 declarations) не подключён. В map 248 unique sources, 7 duplicate groups, 4 conflicting groups и 3 chains. Широкий `ANY pl/{any?}` перехватывает generated `/pl` redirects; `/pl/canvas` runtime matched catch-all.

Полный snapshot содержит 2 003 Closures, а Laravel 6 route serializer отклоняет Closure. Пять duplicate route-name groups и 11 missing handlers сохраняются; `ImageController` блокирует live `route:list`. Отдельная 47-row matrix фиксирует sitemap/feed/robots, mail/review/gift-card, Google/Synvolve и image/debug contracts.

### P0 containment — план по итогам аудита 14.07.2026

Sensitive auxiliary routes в текущем codebase всё ещё не закрыты единым gate. Рекомендуется `legacy.aux:*` default-off middleware, `admin.user` для `admin/sitemap` и приоритет gate до localization middleware, чтобы locale redirect/catch-all не обходил deny. Временная проверка концепции была удалена и не считается реализацией. `route:list` блокирует отсутствующий `ImageController`; `require_once` подключения `admin.php`/`redirect.php` требуют отдельной fresh-boot/cache lifecycle проверки.
