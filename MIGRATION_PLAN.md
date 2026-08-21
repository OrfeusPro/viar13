# План миграции viar13 на Laravel 13

Обновлено: 2026-08-13.

## Название задачи

**Миграция viar13 на Laravel 13 — запуск и стабилизация публичного фронтенда**

## Правила ведения

- `[ ]` — TODO;
- `[~]` — IN PROGRESS;
- `[x]` — DONE, результат подтверждён проверкой;
- `[!]` — BLOCKED, причина записана рядом;
- каждую найденную во время работ задачу добавлять в подходящий этап;
- выполненные изменения и evidence подробно записывать в
  `MIGRATION_PROGRESS.md`;
- админка/Voyager сейчас вне scope; переход на Filament 5 — отдельный этап;
- `G:\OSPanel\home\viar_filament` не использовать и не изменять.

## Этап 1. Laravel 13 boot и изоляция админки

- [x] Обновить PHP/Composer runtime до Laravel 13.
- [x] Отключить Voyager admin routes и providers от публичного runtime.
- [x] Добавить минимальный frontend compatibility layer Voyager.
- [x] Восстановить `artisan`, package discovery и `route:list`.
- [x] Обеспечить компиляцию всех Blade-шаблонов.
- [x] Добавить базовый Laravel 13 frontend boot test.

## Этап 2. Основные публичные страницы

- [x] Проверить `/` и `/en` локальным HTTP smoke.
- [x] Проверить FAQ, About и Contacts в поддерживаемых локалях.
- [x] Определить фактические публичные маршруты каталога и проверить
  репрезентативные разделы/карточки.
- [x] Исправить ошибки compatibility layer, найденные в текущем frontend-срезе.
- [ ] Добавить автоматические smoke-тесты для стабильных route contracts.
- [x] Проверить ответы 404 и безопасную обработку неизвестной локали/slug.

## Этап 3. Публичная авторизация и кабинет

- [x] Убрать зависимость auth controllers от удалённых Laravel UI traits.
- [x] Проверить login/logout и защиту гостевых/авторизованных маршрутов.
- [x] Проверить доступность login/register/reset форм, guest route protection и
  валидацию пустых auth-запросов без обращения к БД.
- [x] Проверить успешный login/logout с тестовым пользователем.
- [x] Проверить успешную регистрацию с изоляцией отправки почты.
- [x] Проверить полный password reset token flow и password confirm.
- [x] Сохранить legacy-контракт: email verification routes отключены, так как
  старый `Auth::routes()` не включал verify, а User его не реализует.
- [x] Проверить клиентские страницы account, settings, empty orders и bonuses.
- [ ] Проверить кабинет с наполненными заказами и страницу оплаты заказа.
- [ ] Проверить отдельные painter-сценарии кабинета.

## Этап 4. Формы и пользовательские сценарии

- [x] Создать первичную инвентаризацию публичных POST/AJAX endpoints в
  `docs/frontend-mutating-routes-audit.md`.
- [ ] Проверить CSRF, валидацию и единый вывод ошибок.
- [~] Проверить формы контактов/заявок: review и photo/portrait/all-styles
  проверены, остальные публичные формы остаются в backlog.
- [x] Добавить серверную валидацию формы отзыва, файлов и base64-аудио.
- [x] Добавить общий validation contract для быстрых portrait/all-styles
  заявок: email, phone, количество и MIME файлов; размер печатного фото на
  уровне Laravel не ограничивать.
- [x] Сохранить совместимость загрузок быстрых заявок с обоими frontend-
  форматами: `file[]` и legacy `file`, `file2` ... `file10`.
- [x] Убрать Laravel size limits для исходников печати: быстрые заявки,
  canvas `userImage` и клиентские файлы художнику.
- [ ] Проверить загрузку публичных файлов и изображений.

## Этап 5. Каталог, корзина и checkout

- [ ] Проверить категории, услуги, фильтры и мультиязычные данные.
- [ ] Проверить корзину и сохранение состояния.
- [x] Защитить удаление позиции от отсутствующего/некорректного `basketId`.
- [x] Ограничить количество позиции диапазоном 1--99 и проверять индекс.
- [ ] Проверить создание заказа без изменения бизнес-логики `orders`.
- [ ] Проверить checkout и payment callbacks в sandbox.
- [ ] Запланировать замену abandoned `paypal/paypal-checkout-sdk`.

## Этап 6. Frontend assets и визуальная совместимость

- [ ] Проверить актуальный Node/Mix build на чистой установке.
- [ ] Исправить несовместимые frontend dependencies.
- [ ] Сравнить ключевые страницы визуально с текущим frontend.
- [ ] Проверить desktop/mobile, console errors и отсутствующие assets.

## Этап 7. Регрессия и готовность к UAT

- [ ] Устранить оставшиеся Composer/autoload warnings.
- [ ] Сформировать стабильный frontend regression suite.
- [ ] Выполнить полный доступный набор тестов и классифицировать legacy failures.
- [ ] Выполнить frontend UAT без переключения production/test.
- [ ] Зафиксировать известные ограничения и критерии следующего этапа.

## Отдельный будущий этап

- [ ] Спроектировать и реализовать новую админку на Filament 5 после приёмки
  публичного frontend.

## Найденные задачи

- [x] Восстановить Voyager-compatible связь `User::role()` для публичного
  меню личного кабинета.
- [x] Добавить безопасные значения frontend tracker variables для
  test/CLI-контекста общего layout.
- [x] Убрать глобальную функцию `isActiveRoute()` из account Blade, чтобы
  повторный рендер не завершался `Cannot redeclare`.
- [ ] Убрать изменение `inv_sale_code` из GET-страниц кабинета или оформить
  его отдельным идемпотентным действием после проверки legacy-поведения.
- [x] Восстановить frontend-helper `str_trans()` для локализованных строк
  Voyager Extension (`{{en}}...` и `[[en]]...`).
- [x] Защитить compatibility translator от пустых имён переводимых атрибутов,
  найденных в legacy-модели `GalleryPage`.
- [ ] Исправить PSR-4/autoload предупреждения для `Sale_30_40_new` и
  `Paymentinfo`.
- [ ] Решить судьбу legacy public endpoint `/admin/check-user`, который сейчас
  остаётся доступен вне отключённой Voyager route group.
- [x] Проверить совместимость email verification с фактической моделью User.
- [x] Передавать обычную строку в `Hash::check()` из
  `ConfirmPasswordController` под Laravel 13.
- [ ] Классифицировать legacy-тесты, зависящие от отсутствующей SQLite-схемы и
  устаревшего контракта `SynvolveWebhookService`: полный прогон 2026-08-17 —
  72 passed / 5 failed; один failure из-за отсутствующей `orders` в SQLite и
  четыре из-за отсутствующих методов conversation/lead update в сервисе.
- [ ] Провести отдельный аудит публичных служебных и изменяющих состояние
  GET-маршрутов (`mail/*`, генераторы, checkout helpers, admin controllers).
- [ ] Проверить controller-level authorization для 20 `orders/*` POST routes,
  у которых нет явного route-level `auth` middleware.
- [x] Устранить рассинхронизацию guest registration: frontend требовал
  отсутствующее CAPTCHA-поле, а backend выполнял обязательный внешний запрос.
- [ ] Спроектировать и подключить рабочую CAPTCHA/Turnstile для регистрации;
  до этого endpoint защищён строгой валидацией и rate limit.
- [ ] Убрать изменяющие состояние GET/ANY basket routes после проверки
  frontend-вызовов: `submitbonuses`, `clear_coupon`, `coupon_use`, gift card.
- [ ] Добавить validation contracts для каждой группы `basket/add/*` payloads
  перед success-path тестами загрузок и session state.
- [x] Подключить существующий `BasketStoreRequest` к `/basket/add` и вернуть
  JSON 422 для неверного canvas payload.
- [x] Добавить validation contract portrait uploads без size limit.
- [x] Исправить modular payload mismatch: frontend отправляет файл `image`,
  repository проверяет и читает `activeImage`.
- [ ] Исключить доверие к переданной frontend-цене: пересчитывать basket price
  по серверным данным для каждого типа товара.
- [x] Создать матрицу восьми basket add-endpoints и известных JS/Blade payload
  families в `docs/basket-add-payload-audit.md`.
- [x] Классифицировать reachability legacy `graph_portrait.blade.php`: прямых
  render/include нет, а активные graphic portrait routes используют другой
  Blade и `/basket/add/portrait`; шаблон признан orphan.
- [x] Проверить legacy canvas и canvas-new inline FormData builders: оба
  находятся только в недостижимых top-level Blade, активный `/new/canvas`
  использует theme canvas и global `new_bot_scripts.js`.
- [x] Покрыть simple legacy и current wizard portrait payload families.
- [x] Исправить active graphic/oil crash-path: `pid=undefined` с
  `orig_images[]`, но без base64 `image` приводил к `strpos(null)`.
- [~] Добавить отдельные contracts для `add/inter`, `add/module`,
  `add/construct`, `add/future_art` и двух recommendation endpoints:
  active endpoints готовы; orphan inter/module явно retired с JSON 410.
- [x] Убрать frontend-лимит 20 MiB в future-art для печатных исходников.
- [x] Сузить basket/cart CSRF exceptions после проверки callers: активные AJAX
  передают `X-CSRF-TOKEN`, формы — `_token`; широкие basket/cart patterns
  удалены, оставлено независимое исключение TinyMCE upload.
- [x] Защитить три recommendation add-endpoints: серверная цена, session offer
  для gallery и обязательный базовый товар для canvas.
- [ ] Исправить recommendation item-card contract: текущий отдельный endpoint
  сохраняет базовый gallery-товар и теряет выбранные size/frame/options; до
  server-side pricing нельзя безопасно принимать их JS-цену.
- [ ] После frontend UAT физически удалить недостижимые реализации
  `addToBasketInterier()` и `addToBasketModule()`; публичные маршруты уже
  безопасно переведены на compatibility JSON 410.
- [ ] После frontend UAT физически удалить orphan-шаблоны `canvas.blade.php`,
  `canvas_new.blade.php`, `graph_portrait.blade.php` и их недостижимые partials
  после контрольного поиска динамических вызовов.
- [x] Исправить gallery item add: не отправлять `image=null`, очищать legacy
  sentinel-значения `undefined` и не падать на отсутствующем decoration.
- [x] Убрать runtime `env('THEME_RESOURCES')` из checkout views и защищать
  прямой переход на payment без сохранённого шага delivery.

## Текущая задача

- [x] [DONE] Досинхронизировать все server files commit `72af3357`,
  включая ALT/Voyager исходники и assets; сохранить admin routes отключёнными
  в Laravel 13 runtime и не потерять frontend compatibility helpers.
- [x] [DONE] Синхронизировать публичные изменения server commit
  `72af3357` из `C:\OSPanel\domains\asoft\viar`: перенести frontend-переводы,
  публичные assets и совместимые web-server настройки; admin/Voyager ALT-код
  классифицировать и отложить до отдельного Filament 5 этапа.
- [x] [DONE] Устранить cache-зависимость account feature-тестов:
  тестовая схема не создавала обязательные `header_menu`/`footer_menu`, поэтому
  результат ошибочно зависел от ранее прогретого cache.
- [~] Провести browser UAT основного frontend-контура; desktop-сценарии gallery
  item → cart, Canvas → cart и delivery → payment пройдены без создания заказа;
  следующая точка — основные страницы в mobile viewport.

## Browser smoke

- [x] Проверить HTTP/DOM main, gallery, item card, canvas, portrait, basket,
  cart, login и register на `viar13.loc`.
- [x] Исправить найденные 500 legacy basket/cart layouts.
- [x] Устранить JS crash `InteriorGenerator` на module item card без canvas.
- [x] Пройти интерактивный gallery item → cart сценарий с проверкой сессии.
- [x] [DONE] Пройти интерактивный canvas → cart сценарий с реальным тестовым файлом:
  проверить фактический POST, ответ, Laravel log, JS console и session cart.
- [x] [DONE] Исправить найденные при Canvas UAT ошибки: перенаправлять HTTP на HTTPS,
  чтобы браузер не блокировал HTTPS-шрифты по CORS; не писать
  `Image3d`/загруженные файлы целиком в Laravel log; защитить `Canvas3D` mouse
  events после очистки редактора.
- [x] [DONE] Проверить checkout: валидная курьерская доставка сохранена,
  payment view открыт с корректной суммой `63 €`, заказ не создавался.
- [x] [DONE] Защитить `/cart/setdelivery`: покрыть все способы доставки условной
  серверной валидацией, не принимать стоимость доставки из браузера и
  показывать JSON 422 в активном frontend JS.
- [x] [DONE] Исправить AJAX-загрузку городов/пунктов Venipak: имена Blade view
  не получают лишнюю точку перед `cart`; поздний AJAX-response больше не
  переключает выбранную пользователем доставку и не очищает все `.js-active`.
- [ ] Убрать повторную инициализацию Ahrefs Analytics, обнаруженную в browser
  console checkout; внешние CookieYes/Meta и extension warnings вести отдельно.
- [ ] [IN PROGRESS] Проверить основные публичные страницы в mobile viewport:
  визуальная компоновка, горизонтальный overflow, меню, основные действия и
  console/network ошибки.
- [x] [DONE] Проверить все публичные способы оплаты без реального списания: frontend
  contract, создание заказа, redirect/form провайдера, ошибки и отмена;
  внешние production-запросы заменить тестовыми doubles.
- [x] [DONE] Убрать Paysera credentials из `PayseraController` в `.env`/config и
  заменить `dd()`/`exit()` контролируемыми redirect/error responses.
- [x] [NOT REQUIRED] Sandbox-транзакции Paysera/PayPal исключены из приёмки:
  sandbox недоступен; production redirect и ошибки проверяются без отправки
  платежа, реальная транзакция выполняется только штатным заказом после запуска.
- [x] [DONE] Проверять сумму, валюту, Paysera-метод и допустимый переход статуса
  во всех публичных Paysera callback: checkout и ссылки на доплату защищены,
  повтор успешного callback идемпотентен.
- [x] [DONE] Исправить найденные рядом payment-регрессии: callback ссылки на
  доплату больше не использует удалённые PHP-константы credentials, а
  `pay_cancel` никогда не меняет заказ на `payed`.
- [x] [DONE] Проверить подтверждение PayPal на том же уровне, что Paysera: принимать
  только capture со статусом `COMPLETED`, сверять сумму, EUR и `reference_id`,
  разрешать только допустимый переход статуса и исключить повторный webhook;
  применить к checkout и публичным ссылкам на доплату без реального списания.
- [x] [DONE] Не допускать `500` после сохранения заказа при недоступном
  SMTP: перевести mail config на Laravel 13, изолировать клиентское и
  административное письмо от checkout и покрыть отказ транспорта тестом.
- [x] [DONE] Сверить частично обработанный заказ `18448`: запись действительно
  создана до SMTP-ошибки со статусами `watching` / `not_payed`; повторно
  отправлять сохранённую checkout-сессию нельзя, чтобы не создать дубликат.
- [x] [DONE] После отключения локального TLS-перехвата вернуть
  `MAIL_MAILER=smtp` и проверить реальное SMTP-подключение и авторизацию без
  отключения проверки сертификата.
- [x] [DONE] Реальным checkout-заказом `18449` проверить доставку клиентского
  письма через SMTP с включённым `MAIL_VERIFY_PEER`: пользователь подтвердил
  получение и переход на страницу «Спасибо».
- [x] [DONE] Исправить административное письмо о новом заказе под Symfony
  Mailer: legacy-вызов `setBody(string, 'text/html')` падает с `TypeError`;
  повторное уведомление заказа `18449` принято реальным SMTP без исключения.
- [x] [DONE] Провести общий аудит механизмов отправки писем в публичном
  runtime (`Mail::send/to`, Mailable, Notification), заменить остальные
  несовместимые с Symfony Mailer вызовы и покрыть общий контракт тестом.
- [x] [DONE] Устранить предупреждения PHP 8.4, найденные реальной
  повторной отправкой письма заказа `18449`: объявить зависимости
  `OrdersController` и не передавать `null` в `strpos()` генератора PDF.
- [x] [DONE] Вернуть в PHPUnit 12 два почтовых regression-набора,
  которые незаметно давали `No tests found` из-за legacy `/** @test */`:
  password reset notification и overdue-order email.
- [ ] [TODO] Уточнить контракт PDF в административном уведомлении: legacy-код
  генерирует `/storage/pdf/{order}.pdf`, но не прикладывает файл и не добавляет
  ссылку в письмо; до подтверждения не менять историческое содержание письма.
- [ ] [TODO] Разобраться с `404` Synvolve webhook `POST NewOrder`: production
  workflow по текущему URL не зарегистрирован/не активирован; заказ сохраняется,
  но внешнее уведомление не принимается.
- [ ] После frontend interaction UAT вернуться к mutating GET/ANY basket routes.

## Задачи, найденные при server sync `72af3357`

- [x] [DONE] Удалить blanket Apache-блокировку gallery query
  `color|order|size`: правило из production commit возвращает 403 для реальных
  параметров, которые читает `GalleryController::hb_type_render()`; штатные
  фильтры возвращают 200, bot-блокировка Semrush/PetalBot сохранена.
- [x] [DONE] Защитить gallery filter parser от повреждённых query:
  `size=broken` после снятия Apache-блока выявил `500 Undefined array key 1`;
  некорректные и массивные `size`/`color`/`order` теперь удаляются до модели и
  Blade, возвращают 200 без новых ошибок в Laravel log.
- [x] [DONE] Синхронизировать 10 admin-only файлов `72af3357` как
  неактивный source corpus; функциональный перенос ALT UI на Filament 5 всё
  равно остаётся отдельным будущим этапом.
- [ ] [TODO] На этапе Filament 5 функционально адаптировать перенесённый ALT UI:
  сейчас исходники, команды и assets синхронизированы, но Voyager admin routes
  по-прежнему не загружаются в Laravel 13 runtime.
