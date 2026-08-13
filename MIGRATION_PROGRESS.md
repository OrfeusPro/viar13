# Миграция на Laravel 13 — текущий статус

Обновлено: 2026-08-13.

## Цель

Перевести этот репозиторий `G:\OSPanel\home\viar13` с Laravel 6 на Laravel 13.
Первый приоритет — полностью поднять публичный frontend. Админка временно вне
scope; Filament 5 будет рассматриваться отдельным этапом.

Подробный backlog, статусы этапов и все найденные задачи ведутся в
`MIGRATION_PLAN.md`. Этот файл хранит выполненные изменения и evidence.

## Принятые решения

- миграция выполняется на основе текущего проекта, без использования
  `G:\OSPanel\home\viar_filament`;
- существующую публичную разметку и assets сохраняем, без редизайна;
- Voyager и admin routes не должны блокировать запуск публичной части;
- обновление выполняется итеративно, с сохранением поведения и тестами;
- production/test не переключаются до локального frontend UAT.

## Исходное состояние

- Laravel: `^6.2`;
- PHP constraint: `^7.3`;
- frontend build: Laravel Mix 6 / Webpack;
- админка: Voyager 1.4 и voyager-extension;
- публичные routes, controllers и Blade находятся в этом репозитории;
- текущий legacy runtime не загружается под активным PHP 8.3 из-за
  несовместимости Laravel 6 с современными сигнатурами PHP.

## Что сделано

- направление миграции и границы frontend-first зафиксированы;
- обновлён `AGENTS.md`;
- создан этот краткий журнал работ.
- создана рабочая ветка `codex/laravel13-frontend`;
- Composer runtime обновлён до PHP `^8.3` и Laravel `13.25.0`;
- Voyager, voyager-extension и translation-manager исключены из обязательного
  runtime; admin routes выключены константой конфигурации;
- совместимые публичные пакеты обновлены: localization, breadcrumbs, DomPDF,
  Socialite, enum, sluggable, location и media library;
- добавлен локальный compatibility layer для используемых frontend-контрактов
  Voyager: translations, `Voyager::image`, settings, menu models и resize path;
- исправлены Laravel 13/PHP 8.3 несовместимости exception handler,
  trusted proxies, breadcrumbs и slug return types;
- public auth controllers переписаны без удалённых `laravel/ui` traits:
  login/logout, register, password reset/confirm и email verification;
- исправлена компиляция JSON-LD: literal `@context` экранирован для нового
  Blade compiler;
- подтверждено: `php artisan --version` выводит Laravel 13.25.0;
- подтверждено: локальный HTTP `GET /` возвращает `200`, 498 864 bytes HTML,
  время smoke-запроса 2.82 s.
- `route:list` снова работает после замены public auth traits и добавления
  отсутствовавшего legacy `ImageController`;
- зарегистрировано 2 260 application routes; Voyager admin route group не
  подключается (legacy public helper `/admin/check-user` пока сохранён);
- HTTP smoke: `/` и `/en` возвращают 200 с RU/EN HTML;
- targeted regression: 5 tests / 12 assertions PASS;
- `composer validate`, package discovery, полный Blade `view:cache` и
  `git diff --check` PASS.

## В работе

- полный аудит восьми basket add-endpoints; active и legacy portrait payload
  families.

## Следующие действия

1. Покрыть active graphic portrait payload и simple legacy portrait family.
2. Проверить legacy/current canvas и reachability orphan-шаблонов.
3. Затем пройти inter/module/construct/future-art/recommendations.

## Риски и открытые вопросы

- часть legacy-пакетов не поддерживает Laravel 13 и потребует замены;
- старые controllers/models могут содержать несовместимые PHP-сигнатуры;
- admin/Voyager providers и routes нужно изолировать, не удаляя бизнес-данные;
- перед изменением схемы БД требуется аудит существующих таблиц и миграций.
- `paypal/paypal-checkout-sdk` Composer помечает abandoned; до payment-этапа
  требуется перенос на PayPal Server SDK с sandbox evidence;
- текущий compatibility translator покрывает frontend read-path; translation
  write-path должен быть реализован и протестирован отдельно до Filament.

## Журнал

### 2026-08-13 — старт нового направления

- подтверждено: основа — `G:\OSPanel\home\viar13`;
- старый отдельный target исключён из работы;
- текущий scope: публичный frontend; админка отложена.

### 2026-08-13 — первый Laravel 13 frontend boot

- обновлены Composer dependencies и lock;
- Laravel 13.25.0 успешно загружается через Artisan;
- главная страница текущего проекта отвечает HTTP 200 на локальном PHP 8.3;
- БД-схема и production/test не изменялись;
- следующий точный шаг: public auth compatibility и автоматический smoke.

### 2026-08-13 — основные страницы и первый каталог-срез

- создан `MIGRATION_PLAN.md`: этапы, чек-листы, найденные задачи и текущий
  статус теперь ведутся отдельно от этого журнала evidence;
- `AGENTS.md` требует сразу добавлять найденные задачи в план и обновлять оба
  migration-файла после заметного шага;
- восстановлен legacy frontend-helper `str_trans()` с поддержкой форматов
  `{{en}}...` и `[[en]]...`; контракт подтверждён unit-тестами;
- compatibility translator фильтрует пустые имена атрибутов, из-за которых
  Laravel 13 падал при переводе `GalleryPage`;
- HTTP smoke: FAQ возвращает `200` для `lv`, `lt`, `de`, `en`, `ee`; `ru`
  возвращает ожидаемый redirect на URL без locale, `pl` — на отдельный домен;
- HTTP smoke: `/about` и `/page/contacts` возвращают `200`;
- HTTP smoke: `/new/gallery`, `/en/new/gallery`, module/photo/reproduction,
  их репрезентативные категории, portrait card и `/en/new/canvas` возвращают
  `200`;
- неизвестная страница, locale, gallery category и portrait slug возвращают
  `404`, без runtime exception;
- regression после исправлений: 6 tests / 14 assertions PASS;
- auth forms `/login`, `/register`, `/password/reset` возвращают `200`, а
  `/account` и `/new/account` для гостя сохраняют legacy redirect на `/`;
- email verification routes отключены: старый `Auth::routes()` не включал
  verify, а фактическая User model не реализует этот контракт;
- `ConfirmPasswordController` передаёт в `Hash::check()` обычную строку вместо
  Laravel `Stringable`;
- добавлены изолированные SQLite feature-тесты auth: доступность форм,
  валидация, guest redirects, успешные login/logout и регистрация без отправки
  реальной почты, password reset token flow и password confirmation;
- итоговая targeted regression текущего шага: 17 tests / 72 assertions PASS;
- после auth-изменений повторно прошли `route:list`, полный `view:cache` и
  `git diff --check`;
- следующий точный шаг: authenticated account pages и их data dependencies.

### 2026-08-13 — клиентский личный кабинет

- compatibility User дополнен связью `role()` и read-only моделью Role;
- общий layout получает безопасные пустые tracker variables в test/CLI, при
  обычном HTTP они переопределяются реальными настройками;
- глобальная Blade-функция `isActiveRoute()` заменена локальным callback, что
  устранило fatal error при повторном account-render в одном PHP-процессе;
- на изолированной SQLite-схеме подтверждены authenticated customer routes:
  legacy `/account` redirect, `/new/account`, `/new/settings`, пустой
  `/new/orders` и `/new/mystocks` без активных акций;
- найден отдельный долг: account GET-страницы генерируют и сохраняют
  `inv_sale_code`; менять это без проверки legacy-сценария пока нельзя;
- наполненные orders/payment и painter account остаются отдельными задачами;
- следующий точный шаг: inventory публичных форм и AJAX endpoints.

### 2026-08-13 — route inventory и форма отзыва

- создан `docs/frontend-mutating-routes-audit.md`;
- зафиксировано 112 mutating route records: 99 web и 13 API;
- 81 web record не имеет явного route-level `auth`; это не признано
  уязвимостью без проверки controller/service guards;
- отдельно отмечены 20 `orders/*` POST routes и 3 публичных route records,
  ведущих в Admin controllers;
- review endpoint использует актуализированный `CreateReviewRequest`;
- обязательные текстовые поля, два изображения до 10 MiB и audio data URL
  получили серверную валидацию;
- исправлена неинициализированная переменная отзыва без аудио;
- image/audio отзывов сохраняются явно на public disk;
- validation, text-only и image/audio success paths покрыты feature-тестами;
- следующий точный шаг: photo/portrait/all-styles request forms.

### 2026-08-13 — photo/portrait/all-styles request forms

- быстрые заявки portrait и all-styles переведены на общий
  `QuickOrderRequest`;
- email проверяется как RFC email, телефон нормализуется и допускает от 7 до
  15 цифр с необязательным `+`;
- загрузка обязательна и ограничена 10 файлами; разрешённые MIME/расширения
  перечислены явно, application-level ограничения размера нет;
- сохранена совместимость двух реально найденных Blade-контрактов: массив
  `file[]` и legacy-поля `file`, `file2` ... `file10`;
- валидация обычной photo calculation form также подтверждена регрессионным
  тестом;
- targeted result: 4 tests / 25 assertions PASS;
- следующий точный шаг: guest AJAX login/register contract.

### 2026-08-13 — большие исходники для печати

- по уточнению владельца сняты Laravel size limits с quick order файлов,
  canvas `userImage` и клиентских файлов художнику;
- файл 100 MiB проходит validation contract в автоматическом тесте;
- ограничения количества и формата сохранены;
- `public/php.ini` уже содержит `upload_max_filesize=256M` и
  `post_max_size=256M`; активный CLI PHP использует другой ini с 2M/8M, что
  необходимо учитывать при локальном HTTP/UAT.

### 2026-08-13 — guest AJAX login/register

- добавлен отдельный `LoginAjaxRequest`: обязательные RFC email/password,
  нормализация email и JSON 422 validation contract;
- `RegisterStoreRequest` нормализует email/телефон и валидирует email, длины,
  формат телефона и подтверждение пароля;
- устранён блокер регистрации: активный frontend JS ожидал отсутствующее
  CAPTCHA-поле, а backend требовал его и выполнял синхронный внешний запрос;
- login/register endpoints получили rate limit `10/min` и `5/min`;
- ошибки Laravel 422 теперь выводятся в AJAX login UI; оба фактически
  используемых `add.js` проходят `node --check`;
- success login сохраняет legacy JSON `{"status":true}`, success register —
  JSON `true`; регистрация без реальной почты и нормализация данных покрыты
  feature-тестами;
- полноценная CAPTCHA/Turnstile оставлена явной отдельной задачей; фиктивная
  проверка site key не используется;
- совместная regression: 33 tests / 147 assertions PASS;
- `route:list`, полный `view:cache`, JS syntax и `git diff --check` PASS;
- следующий точный шаг: корзина и session state.

### 2026-08-13 — корзина, первый session-срез

- инвентаризированы 17 `basket/*` routes и основные `cart/*` routes;
- `basket/remove` переведён на `RemoveBasketItemRequest`: `basketId`
  обязателен, является целым индексом и не может быть отрицательным;
- устранён риск удаления нулевой позиции отсутствующим `basketId` из-за
  нестрогого сравнения legacy PHP;
- `basket/update/count` использует `UpdateBasketCountRequest` и принимает
  только целое количество 1--99 и неотрицательный индекс;
- validation errors этих AJAX endpoints всегда возвращаются JSON 422, а
  успешный legacy response contract сохранён;
- убрано лишнее двойное сохранение abandoned cart при одном update count;
- session tests подтверждают отклонение неверных payloads без мутации,
  корректное изменение количества и удаление позиции;
- targeted result: 4 tests / 24 assertions PASS;
- текущая совместная regression: 37 tests / 171 assertions PASS; `route:list`,
  полный `view:cache` и `git diff --check` также PASS;
- найдены отдельные задачи: state-changing GET/ANY basket routes и отсутствие
  общих validation contracts у `basket/add/*`.

### 2026-08-13 — basket add: canvas, portrait и modular

- ранее неиспользуемый `BasketStoreRequest` подключён к `/basket/add`;
- неполный canvas payload возвращает единый JSON 422 вместо попадания в
  repository с частичными данными;
- canvas contract проверяет тип корзины, цену, исходник и обязательные
  параметры конфигурации без ограничения размера файла;
- добавлен `PortraitBasketRequest` для цены, комментария, `orig_images` и
  `photo_ex`; MIME проверяется, size limit отсутствует;
- исправлен реальный modular mismatch: frontend-поле `image` нормализуется в
  ожидаемое repository-поле `activeImage`;
- тестовые canvas, portrait и modular файлы по 100 MiB проходят validation;
- targeted basket result: 8 tests / 39 assertions PASS;
- текущая совместная regression: 41 tests / 186 assertions PASS; `route:list`,
  полный `view:cache`, JS syntax и `git diff --check` PASS;
- следующий точный шаг: collage payload и storage success paths.

### 2026-08-13 — полный JS/Blade-аудит basket add

- подтверждено, что прежнее утверждение о проверке всех вариантов было бы
  неверным: опубликовано 8 отдельных basket add-endpoints;
- создан `docs/basket-add-payload-audit.md` с endpoint/source/status matrix;
- `/basket/add/portrait` вызывается минимум шестью JS-семействами и имеет как
  минимум simple legacy и current wizard payload families;
- общий `/basket/add` собирается global JS и несколькими inline Blade-копиями,
  которые отличаются по выбору полей и обработке JSON;
- `add/inter`, `add/module`, `add/construct`, `add/future_art` и recommendation
  endpoints пока не имеют полного Form Request/test coverage;
- найден legacy `graph_portrait.blade.php` без поля `price`, но активные
  `/graphic-portrait*` используют другой Blade и portrait endpoint; legacy-файл
  помечен как вероятный orphan до reachability-аудита;
- найдено глобальное отключение CSRF для `basket/add`, `*/basket/*` и
  `/cart/*`; изменение отложено до проверки всех callers;
- найден frontend-лимит future-art 20 MiB, противоречащий принятому контракту
  больших печатных исходников;
- текущий порядок изменён: сначала полная совместимость всех payload families,
  затем storage/checkout.

### 2026-08-13 — portrait families и future-art contract

- для portrait endpoint проверены simple generated, active oil и current
  wizard payload families;
- oil payload с `pid=undefined`, `orig_images[]` и без base64 preview больше
  не падает на `strpos(null)`: первый сохранённый оригинал становится active;
- загрузка оригинала portrait больше не дублируется, generated data URL
  проходит строгую проверку и сохраняется через uploads disk;
- добавлен `FutureArtRequest`: обязательные и нормализованные контакты, от 1
  до 15 изображений, явные MIME, JSON 422 и отсутствие size limit;
- удалена legacy frontend-проверка future-art `> 20 MiB`; активный footer
  такого лимита уже не содержал;
- исправлено вводящее в заблуждение серверное сообщение `Invalid filesize`:
  `max:15` ограничивал количество элементов массива, а не размер;
- targeted basket result: 15 tests / 76 assertions PASS;
- следующий точный шаг: аудит file/base64 веток `/basket/add/construct`.

### 2026-08-13 — construct payload families

- подтверждены collage/family base64 и modular original-file payload families;
- добавлен `ConstructBasketRequest` с условным требованием `image_offset` либо
  пары original `image` + безопасный `collageSvgImage_hash`;
- MIME для `image`, `photo_ex`, `fon[]`, `orig_images[]` проверяется без
  application-level size limit; original-файл 100 MiB проходит правила;
- устранена ветка, повторно сохранявшая отсутствующий `image_offset`;
- generated base64 строго декодируется и сохраняется через uploads disk;
- session/storage success и оба invalid-source режима покрыты тестами;
- targeted basket result: 18 tests / 91 assertions PASS;
- frontend regression: 45 tests / 227 assertions PASS;
- `route:list --path=basket/add`, `view:cache` и `git diff --check` PASS;
- следующий точный шаг: recommendation endpoints и серверный источник цены.
