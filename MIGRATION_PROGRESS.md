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

### 2026-08-13 — recommendation endpoints и server price

- кроме двух известных basket endpoints найден активный третий
  `/cart/add-recommended`; он добавлен в scope и документацию;
- добавлены `RecommendedBasketItemRequest` и
  `CanvasRecommendationRequest` с единым JSON 422 contract;
- frontend `price` сохранён только для обратной совместимости и больше не
  участвует в расчёте;
- gallery recommendation рассчитывается из `gallery_items.price_from`, country
  multiplier и скидки 30%, а также требует одноразовый session offer;
- canvas recommendation рассчитывается по `canvas_header.sizes_30x40`, требует
  существующий базовый товар и принимает исходник 100 MiB без size limit;
- исправлено чтение несуществующего `GalleryItem::image`: используется реальное
  поле `images`;
- подмена browser price на `0.01` не влияет на сохранённую цену во всех трёх
  сценариях;
- targeted basket result: 23 tests / 110 assertions PASS;
- frontend regression: 50 tests / 246 assertions PASS; `view:cache`,
  recommendation routes и `git diff --check` также PASS;
- найден следующий риск: item-card recommendation теряет выбранную
  конфигурацию и сохраняет только базовый товар; задача добавлена в backlog;
- следующий точный шаг: reachability/contracts `add/inter` и `add/module`.

### 2026-08-13 — retired legacy inter/module endpoints

- полный source search не нашёл активных callers `/basket/add/inter` и
  `/basket/add/module`;
- inter-form существует только в закомментированном canvas tab, а её
  click-handler перенаправлял submit на другой calculator action;
- active modular page использует `/basket/add/construct`; одноимённые CSS/JS
  классы лишь триггерят основной submit и не вызывают add/module;
- route names сохранены, оба POST URL переведены на единый JSON 410
  `legacy_endpoint_retired` с указанием актуальной замены;
- тест подтверждает HTTP 410 и отсутствие записи файлов/session mutation;
- targeted basket result: 24 tests / 121 assertions PASS;
- frontend regression: 51 tests / 257 assertions PASS; `view:cache`, полный
  список basket add routes и `git diff --check` также PASS;
- физическое удаление двух недостижимых controller methods оставлено после UAT;
- следующий точный шаг: общий `/basket/add`, legacy canvas builders и
  reachability `graph_portrait.blade.php`.

### 2026-08-13 — общий canvas payload и orphan builders

- активный `/new/canvas` подтверждён как `theme.viar.pages.canvas` с одним
  global `public/theme/viar/js/new_bot_scripts.js` submit builder;
- `canvas.blade.php`, `canvas_new.blade.php` и `graph_portrait.blade.php` не
  имеют активного controller render/include и классифицированы как orphan;
- активные graphic portrait routes используют `graphical-portrait-buy` и
  отдельный `/basket/add/portrait` contract;
- `BasketStoreRequest` теперь проверяет обязательные canvas IDs, JSON-массив
  положительных `boxIds`, строгий base64-формат preview и
  `terms_price <= price`;
- `userImage`, `orig_images[]` и `photo_ex` сохраняют MIME-проверку без
  application-level size limit; файлы по 100 MiB подтверждены тестом;
- active JS и min-копия показывают Laravel validation message и не сбрасывают
  выбранные файлы при 422;
- targeted basket result: 26 tests / 130 assertions PASS;
- frontend regression: 53 tests / 266 assertions PASS; `view:cache`, проверка
  обоих JS-файлов и список восьми basket add routes также PASS;
- физическое удаление orphan Blade/partials оставлено после frontend UAT;
- следующий точный шаг: аудит и безопасное сужение basket/cart CSRF exceptions.

### 2026-08-13 — basket/cart CSRF protection

- проверены активные basket/cart AJAX, FormData и обычные form callers;
- AJAX передают `X-CSRF-TOKEN`, canvas recommendation — `_token`, форма
  `cart/set_email` содержит `@csrf`;
- из `VerifyCsrfToken::$except` удалены `basket/add`, `*/basket/*`, cart root и
  все широкие `cart/*`/`*/cart/*` patterns;
- независимое исключение `/admin/upload/tinyimage` оставлено без изменений;
- добавлен structural contract для обычных и locale-prefixed basket/cart URL;
- targeted basket result: 27 tests / 137 assertions PASS;
- frontend regression: 54 tests / 273 assertions PASS; `view:cache`, PHP syntax
  и inventory basket/cart routes также PASS;
- следующий точный шаг: удалить mutating GET/ANY варианты basket routes после
  проверки callers и сохранить только POST contracts.

### 2026-08-13 — initial browser smoke основных страниц

- реальный HTTP/Chrome smoke выполнен на `https://viar13.loc` с текущей MySQL;
- main, module gallery, реальная item card `id=31`, canvas, portrait, basket,
  cart, login и register возвращают HTTP 200;
- исправлен 500 legacy basket: базовый Voyager `MenuItem` получил совместимый
  `link()`, включая route parameters и fallback для отсутствующего route;
- рекурсия frontend menu больше не зависит от отключённого
  `voyager::menu.bootstrap`;
- исправлен 500 cart: header widget использует `config('theme.resource')`
  вместо runtime `env('THEME_RESOURCES')`;
- устранён JS crash module item card: `interiorGallery.js` не запускается без
  обязательного `#canvas_interior`;
- повторная Chrome-проверка item card: title/H1/DOM присутствуют, project JS
  errors отсутствуют;
- frontend regression: 56 tests / 278 assertions PASS; JS/PHP syntax,
  `view:cache` и повторный реальный browser smoke также PASS;
- отдельная матрица и наблюдения сохранены в
  `docs/frontend-browser-smoke.md`;
- следующий точный шаг: интерактивный canvas/gallery → cart smoke без создания
  заказа.

### 2026-08-13 — interactive gallery/cart и checkout error audit

- прежняя browser-оценка исправлена: скрытый текст popup больше не считается
  доказательством успешного POST; проверяются response outcome, Laravel error,
  итоговый URL и фактическое содержимое корзины;
- найден реальный JSON 422: gallery item JS отправлял `image` как строку
  `"null"`; пустое generated image теперь исключается из FormData, а validation
  error показывается пользователю;
- найден следующий 500 после успешного POST: optional `decor_id=undefined`
  приводил к `translate()` на `null`; request очищает legacy sentinel-строки,
  а cart reader проверяет положительный integer ID и наличие decoration;
- повторный Chrome smoke: success popup видим, `/cart` показывает два реальных
  товара по 50 €, project console errors от `viar13.loc` отсутствуют;
- `/cart/data` успешно отображает пользователя и сумму; на `/cart/delivery`
  отдельно обнаружены отсутствующие view и вложенный `cart.citys` partial;
  runtime env заменён на theme config во всём controller/cart Blade контуре;
- прямой `/cart/payment` без delivery session больше не падает и возвращает на
  `/cart/delivery`; добавлен regression test;
- frontend regression: 58 tests / 292 assertions PASS; `view:cache`, JS/PHP
  syntax и `git diff --check` PASS;
- внешняя локальная ошибка CookieYes и предупреждения browser extensions не
  классифицируются как project JS errors;
- следующий точный шаг: canvas upload → cart, затем валидный delivery → payment
  без отправки заказа.

### 2026-08-14 — синхронизация server commit `72af3357`

- source проверен напрямую в `C:\OSPanel\domains\asoft\viar`; commit содержит
  68 файлов;
- 47 language-файлов уже побайтно совпадали с состоянием commit и повторно не
  перезаписывались;
- перенесены 11 публичных изменений: localhost-safe image URL normalization,
  Apache bot/gallery protection, `.user.ini`/`php.ini`, legacy password-reset
  payload, public CSS, Google verification и три contact WebP;
- `app/Helpers/functions.php` не заменялся целиком: сохранены Laravel 13
  compatibility helpers `str_trans`, `setting`, `menu`, `voyager_asset`, а
  серверная localhost-правка интегрирована отдельно;
- SHA-256 трёх WebP совпадает с server source; HTTP каждого изображения —
  `200 image/webp`; Google verification возвращает точное содержимое commit;
- при первой синхронизации 10 admin/Voyager ALT-файлов были ошибочно оставлены
  только в backlog из-за frontend scope; после уточнения пользователя все они
  физически перенесены в `viar13` как source corpus;
- 67 из 68 commit paths побайтно совпадают с server commit; единственное
  намеренное отличие — `app/Helpers/functions.php`, где серверная правка
  объединена с обязательными Laravel 13 compatibility helpers;
- синхронизированы `AltApprovePendingCommand`, `AltGenerateCommand`,
  `AltSuggestionController`, `GenerateImageAltJob`, `AltGenerator`, config,
  два admin assets, Voyager master и `routes/admin.php`;
- PHP/JS syntax всех добавленных ALT-файлов проходит; Artisan видит команды
  `alt:approve-pending` и `alt:generate`, их `--help` загружается без ошибок;
- Voyager ALT routes не активировались: `route:list --path=alt-suggestions`
  пуст, что соответствует отключённой админке текущего этапа;
- Apache smoke: главная 200, обычная gallery после locale redirect 200,
  SemrushBot 403, gallery с `size=` 403, verification 200;
- обнаружен риск: production `.htaccess` блокирует реальные gallery filters
  `color|order|size`, которые обслуживает `GalleryController`; правило пока
  сохранено точно по server commit, решение вынесено в план;
- первый auth regression выявил старую cache-зависимость тестов: без прогретого
  cache отсутствовали SQLite-таблицы `header_menu`/`footer_menu`; тестовая схема
  дополнена, cache очищается перед каждым тестом;
- итог: frontend regression 59 tests / 295 assertions PASS; `view:cache`, PHP/
  JS syntax и `git diff --check` PASS;
- следующий точный шаг: решить судьбу blanket gallery filter block либо
  продолжить запланированный canvas upload → cart smoke.

### 2026-08-17 — восстановление gallery query filters

- из `public/.htaccess` удалена blanket-блокировка `color|order|size`, которая
  запрещала штатные ссылки frontend-галереи; блокировка Semrush/PetalBot
  сохранена и проверена ответом 403;
- подтверждено, что gallery filters используют параметризованный Laravel Query
  Builder, а сортировка ограничена значениями `new|cheap|expensive`;
- реальные HTTP-проверки `color=1`, `size=30x40_over`,
  `size=30x40_smaller`, всех сортировок и комбинированного запроса на `/en/`
  возвращают 200; выдача меняется для color и разных сортировок;
- негативная проверка обнаружила `500 Undefined array key 1` для
  `size=broken`, затем второй 500 в Blade при массивном `size[]`;
- `GalleryController` теперь удаляет некорректные `color`, `size`, `order` из
  query до передачи в модель и Blade; `GalleryItem` дополнительно защищён от
  некорректных типов и формата размера;
- повторные запросы с `size=broken`, `size[]=...`, `color=abc`, `color[]=1` и
  `order[]=cheap` возвращают 200; после них Laravel log не изменился;
- PHP syntax PASS; frontend regression — 59 tests / 295 assertions PASS;
- полный suite проверен отдельно: 72 passed / 5 failed. Все пять failures
  относятся к уже известному backlog `SynvolveWebhookServiceTest`: отсутствует
  таблица `orders` в SQLite и четыре ожидаемых метода сервиса;
- следующий точный шаг: интерактивный canvas upload → cart с реальным тестовым
  изображением и проверкой HTTP-ответа, Laravel log, JS console и session cart.

### 2026-08-17 — интерактивный Canvas → cart

- пользователь прошёл полный Canvas-конфигуратор и отправил реальный файл;
  Laravel log подтверждает успешный `/basket/add`: исходник сохранён в
  `uploads`, preview — в `user_images`, `boxIds=[3]`, итоговый `new_count=1`;
- ответ не был `422`: repository и controller завершили добавление успешно;
- причина отсутствующих иконок установлена по browser console: Canvas был
  открыт по HTTP, а шрифты запрашивались по HTTPS и блокировались CORS;
- включён постоянный HTTP → HTTPS redirect с учётом
  `X-Forwarded-Proto: https`; HTTP Canvas возвращает `301` на тот же HTTPS URL,
  прокси-запрос не зацикливается, WOFF по HTTPS возвращает `200 font/woff`;
- на HTTPS браузер подтверждает `icons-tools`, загруженный font и glyph content
  для `undo/redo/zoom/turn/delete`; отдельные файлы иконок не требуются;
- найденная browser error `Canvas3D.PosTo3dBoxPos: reading layers` защищена от
  событий после очистки `box`/`camera`;
- стартовый лог `/basket/add` больше не пишет полный `Image3d` base64 и
  содержимое загруженных файлов: остаются только поля и безопасные признаки;
- ограничение размера загружаемых изображений не добавлялось; контракт файла
  100 MiB по-прежнему проходит;
- проверки: PHP syntax, `node --check`, `view:cache`, `git diff --check` PASS;
  basket regression — 29 tests / 151 assertions PASS;
- следующий точный шаг: сохранить валидную доставку и открыть payment view без
  отправки реального заказа.

### 2026-08-17 — checkout delivery → payment

- создан отдельный `SetDeliveryRequest`: условно проверяются courier, Venipak,
  Viar workshop, city delivery и email/no-delivery; ошибки возвращаются JSON
  `422`, активный frontend показывает первое сообщение пользователю;
- стоимость больше не принимается из браузера: courier/Venipak берутся из
  `country_tels`, city delivery — из `a_delivery_towns`, workshop/email равны
  нулю, купон `free_delivery` сохраняет нулевую доставку;
- ограничения размера вложенного к комментарию изображения не добавлялись;
  проверяются формат data URL и корректность base64;
- browser UAT выявил и исправил отсутствующие Blade view из-за лишней точки в
  `VinepakApiController` (`.cart.citys`/`.cart.warehouses`);
- исправлена гонка Venipak AJAX: поздний ответ больше не сбрасывает выбранного
  courier обратно на Venipak и не удаляет глобально все `.js-active`;
- повторный HTTPS-проход сохранил courier `Riga / LV-1001 / Testa iela 1` с
  серверной ценой `5 €` и открыл `/en/cart/payment`; итог `63.00 €`, заказ не
  создавался;
- regression: `PublicBasketSessionContractTest` — 30 tests / 174 assertions
  PASS, включая все пять вариантов, подмену frontend price и обязательные поля;
  расширенный frontend regression — 60 tests / 318 assertions PASS;
  PHP/JS syntax, `view:cache` и `git diff --check` PASS;
- найден отдельный TODO: Ahrefs Analytics инициализируется дважды; CookieYes,
  Meta и extension warnings относятся к локальному домену/браузерному окружению;
- следующий точный шаг: проверить основные frontend-страницы в mobile viewport.

### 2026-08-17 — безопасная проверка способов оплаты

- `/cart/setpay` принимает только 8 методов, реально показанных checkout:
  `on_delivery`, четыре метода Paysera, PayPal, transfer и prepayment;
  произвольные значения и наложенный платёж для несовместимой доставки дают
  JSON `422`, отсутствующая delivery session также отклоняется;
- финальное создание заказа переведено с GET на CSRF-защищённый POST; frontend
  отправляет скрытую POST-форму и блокирует повторный клик, что снижает риск
  дублирования заказа;
- `save_order_and_pay` больше не обращается к отсутствующим delivery/payment
  session keys: пользователь возвращается на точный недостающий шаг checkout;
- Paysera start больше не использует `dd()`/`exit()` и возвращает Laravel
  redirect либо контролируемую ошибку; URL шлюза строится без внешнего запроса;
- PayPal start больше не использует `header()/exit()`; redirect и ошибка
  возвращаются вызывающему controller, поэтому ошибка шлюза не маскируется
  ложной страницей успешного заказа;
- callbacks PayPal без token/заказа безопасно возвращают в корзину; повреждённые
  Paysera accept/cancel/callback не дают 500, callback отвечает `400 ERROR`;
- Paysera project/sign secret удалены из PHP и перенесены в `config/paysera.php`
  + локальный `.env`; `.env.example` содержит только пустые placeholders;
- два legacy payment test-файла снова включены в PHPUnit 12: методы с устаревшим
  `@test` переименованы в `test_*`; admin-only тест ожидаемо skipped, поскольку
  admin routes исключены из текущего frontend runtime;
- проверки: расширенный frontend/payment regression — 76 passed / 397
  assertions, 1 admin-only skipped; PHP/JS syntax, config cache, `view:cache` и
  `git diff --check` PASS;
- реальные запросы Paysera/PayPal не выполнялись: локальный `.env` использует
  production endpoints. По решению пользователя sandbox исключён из приёмки,
  так как недоступен; реальная транзакция остаётся только штатной проверкой
  после запуска. Отдельная техническая задача — проверка суммы, валюты и
  перехода статуса Paysera callback.
