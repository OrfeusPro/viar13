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
  заявок: email, phone, количество, MIME и размер файлов.
- [x] Сохранить совместимость загрузок быстрых заявок с обоими frontend-
  форматами: `file[]` и legacy `file`, `file2` ... `file10`.
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
  устаревшего контракта `SynvolveWebhookService`.
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

## Текущая задача

- [~] Проверить корзину и сохранение session state.
