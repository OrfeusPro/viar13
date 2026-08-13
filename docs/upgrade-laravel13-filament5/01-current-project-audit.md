# 01. Текущее состояние проекта

## Срез и воспроизводимость

Диагностика Artisan выполнена только командой вида:

```powershell
& 'C:\OSPanel\modules\php\PHP_7.4\php.exe' artisan <command>
```

`artisan list --format=json` подтверждает Laravel 6.20.40. `migrate:status` выполняется; 71 migration-файл отмечен применённым до `2026_07_08_120000_create_seo_meta_suggestions_table` (batch 56). `route:list --json` не выполняется из-за отсутствующего `App\Http\Controllers\ImageController`, импортированного в `routes/web.php:8` и вызванного в `routes/web.php:519`.

## Размер и структура

| Область | Факт | Источник |
|---|---:|---|
| Models | 159 файлов | `app/Models`, плюс legacy-модели непосредственно в `app/` |
| Controllers | 61 | `app/Http/Controllers` |
| Middleware | 19 | `app/Http/Middleware` |
| Services / Repositories | 30 / 7 | `app/Services`, `app/Repositories` |
| Commands | 13 | `app/Console/Commands` |
| Jobs / Observers | 1 / 1 | `GenerateImageAltJob`, `AutoAltObserver` |
| Mail / Notifications | 33 / 3 | `app/Mail`, `app/Notifications` |
| Views | 713 | `resources/views` |
| Tests | 23 | `tests` |
| Migrations | 71 | `database/migrations` |
| Route declarations | admin 68, api 26, web 249; active `redirect.php` содержит 256 macro rules → 2 048 routes; `redirect_old.php` 550 declarations не подключён | `routes/*.php`; FN-13 parser + route snapshot |

## Доменные контуры

### Заказы, корзина и CRM

- **[Код]** `app/Models/Orders.php` — перегруженная доменная модель (создание заказа, файлы, статусы, платежные данные, доставка, Synvolve notify); `notifyOrderSnapshotById()` вызывается после создания.
- **[Код]** `app/Repositories/BasketRepository.php` и `app/Http/Controllers/BasketController.php` содержат pricing/upload/checkout логику.
- **[Код]** `app/Http/Controllers/OrdersController.php` и `app/Http/Controllers/Admin/OrdersController.php` реализуют большую кастомную админку поверх Voyager.
- **[Код]** клиентский чат читается/пишется напрямую через `DB::table('order_user_comments')` в `Account\AccountController`; отправка менеджерского сообщения вызывает `SynvolveWebhookService`.
- **[Код]** транзакции найдены в blog/SEO/ALT, но не в основных checkout/payment flows. Это не доказывает ошибку, но создаёт риск частичных записей.
- **[БД]** exact `COUNT(*)` 13.07.2026: `orders` 14 423, `order_user_comments` 3 835, `order_payment_requests` 22. Ранние 13 646/3 856 были InnoDB `TABLE_ROWS` estimates; размеры снимка — 32.06/1.52/0.13 MiB.
- **[БД]** payment statuses: `payed` 12 252, `not_payed` 2 082, `prepayment` 89. Это локальный снимок, не доказательство production.
- **[Код/БД]** FN-04: 123 файловых sink-вызова в 26 PHP-файлах; `order_painter_images` 7 199, из них 28 rows без существующего order. `renameUploadsPhoto()` меняет несколько storage roots и DB references без общего rollback journal.

### Админка

- **[БД]** 133 `data_types`, 1 997 `data_rows`, 166 menu items (admin 138, footer 14, header 14), 8 roles, 675 permissions, 2 080 role links.
- **[Код]** 40 переопределённых Voyager Blade-файлов в `resources/views/vendor/voyager`; особенно orders/chat, users, gallery, blog, mail sender, SEO/ALT.
- **[Код]** `routes/admin.php` содержит custom actions для заказов, Venipak, оплаты, email, SA conversations/simulator и SEO.
- **[Код]** собственный content type загрузки изображений: `app/Http/Controllers/ContentTypes/Image.php`.

### API и интеграции

- **[Код]** `routes/api.php` защищает рабочие integration routes middleware `integration.api_key`; реализация `VerifyIntegrationApiKey` сравнивает `X-Api-Key`.
- **[Код]** `SaIntegrationController.php` — очень крупный контроллер (>7 300 строк), совмещает webhooks, каталог, leads, pipeline, messages, attachment copy и dedupe.
- **[Код]** `SynvolveWebhookService.php` настраивает connect/request timeout и SSL verify, логирует результат; retry/outbox не обнаружен.
- **[Код]** Paysera — локально vendored WebToPay implementation; signing credential обнаружен в tracked controller и требует ротации. PayPal — `paypal/paypal-checkout-sdk 1.0.2`, помеченный abandoned в `composer.lock`.
- **[Код]** Venipak использует pickup endpoint и cURL import/print label API; credentials читаются из таблицы `venipak_data`, а не только ENV.
- **[Код/БД]** FN-08: три state-changing admin Venipak routes имеют только `web`; browser delivery price/method/point не пересчитываются server-side. В локальной БД 4 072 `venipak` orders и 3 274 orders с label, но provider pickup ID/receipt history не хранится. XML строится без escaping, transport не имеет timeout/status/error contract, create/courier не имеют idempotency/void lifecycle, print не проверяет ownership/PDF signature.
- **[Код]** SA attachment downloader принимает произвольный URL, следует redirects, отключает TLS peer/name verification и проверяет размер только после полного чтения body. Файл сохраняется на public disk; нужен SSRF-safe streamed private-storage adapter.

## Runtime и инфраструктура

- OSPanel PHP 7.4.30; загружен `C:\OSPanel\modules\php\PHP_7.4\php.ini`.
- Расширения включают `bcmath`, `curl`, `dom`, `fileinfo`, `gd`, `imagick`, `mbstring`, `mysqli`, `pdo_mysql`, `openssl`, `soap`, `sockets`, `xml`, `xsl`, `zip` и др.
- Composer 2.9.5; Node 24.13.0; npm 11.6.2. Модульного Webpack-приложения нет: Mix вызывает Webpack как внутренний механизм `styles/scripts/version`. Публичные assets не пересобирать в ходе миграции; аварийное воспроизведение допускается только в зафиксированном Node LTS container.
- Docker, CI pipeline, Supervisor и deployment manifests в репозитории не найдены. **[Проверить]** они могут быть вне репозитория.
- `public/storage` — junction на `storage/app/public`.
- Invoice PDF пишется в `public/storage/pdf/{orders.id}.pdf`; локально `has_pdf=1` у 6 907 orders, но binary snapshot содержит только 6 PDF. Локальный filesystem неполон и не может считаться production backup.
- логи включают `laravel.log` около 24 MiB и отдельные mail/paypal/synvolve logs; политика production rotation не подтверждена.

## Очереди и scheduler

`app/Console/Kernel.php`:

- `UserNotify:cron` daily;
- `orders:notify-overdue-delay` daily at 18:00;
- два abandoned-cart mail commands hourly;
- `GenSmallImages:run` daily;
- все с `withoutOverlapping()`.

Queue-aware: `GenerateImageAltJob`, `AdminMailNotification`, `ThanksForBuyNotification`, abandoned-cart Mailables. `.env.example` задаёт `QUEUE_CONNECTION=sync`, поэтому фактическая асинхронность production требует проверки. При переходе нужен отдельный scheduler lock store, worker manager и retry/failed-jobs policy.

## Мультиязычность

- `config/laravellocalization.php`: `lv`, `lt`, `pl`, `ru`, `de`, `en`, `ee`; `uk` закомментирован.
- `config/voyager.php`: multilingual enabled с теми же семью locale.
- `translations`: 64 779 строк, 111 таблиц; de 9 392, ee 10 408, en 2 062, et 6, lt 10 433, lv 10 408, pl 9 409, ru 12 661.
- `newhome_services`: 11 базовых строк и 264 translation rows: `desc/link/name/title` × 11 × 6 (`de|ee|lt|lv|pl|ru`); base columns, вероятно, представляют English — подтвердить контент-сверкой.
- `header_menu`: 19 строк и 227 переводов; для `ee.title` 18 вместо 19 — один потенциальный пробел данных.

## Frontend

`webpack.mix.js` не описывает модульное Webpack-приложение: CSS/JS поддерживаются вручную в `public/`, а Mix только конкатенирует их в versioned bundles (`combine.js`, `combine_canvas.js`, `collage_combine.js`, `family_combine.js`, `gallery_combine.js`, `graph_combine.js`, `graph_child_combine.js`, `modular_combine.js`) и формирует `mix-manifest.json`. `PhotoEditor.js` включён дважды в collage bundle. Порядок jQuery/plugins, состав файлов и уже сгенерированные CSS/JS являются контрактом поведения. Решение аудита: публичный frontend не переводить на Vite и не перегенерировать в рамках backend/админ-миграции; отдельную сборку Filament изолировать от `public` assets.

## Технический долг с высоким влиянием

1. отсутствующий `ImageController` блокирует полную регистрацию маршрутов;
2. гигантские `SaIntegrationController`, `Orders`, `BasketController`, `OrdersController` усложняют characterization;
3. секретоподобные fallback (`test-key`) в `.env.example`/config;
4. 27 мест raw query patterns и всего семь явно найденных DB transactions;
5. SwiftMailer API в `AppServiceProvider.php:29` несовместим с Laravel 9+;
6. credentials Venipak хранятся в БД — требуется отдельный security decision;
7. нет воспроизводимого CI/deploy/worker описания;
8. Voyager media разрешает MIME `*`; перенос должен усилить validation без поломки допустимых файлов;
9. активный `redirect.php` разворачивает 256 правил в 2 048 closure routes; `redirect_old.php` с 550 declarations не подключён и хранится только как исторический источник;
10. локальная SA БД пуста, поэтому нужна production-safe статистика и реальные контракты.
11. файлы заказов, счета и SA attachments смешаны с public media; authorization/retention/classification не централизованы;
12. base64/raw SVG/audio и multipart uploads используют разные MIME/size/name правила, включая практически неограниченный `max:9000000`;
13. Dompdf 0.8.6 настроен с remote access и JavaScript, а invoice renderer способен создавать user/менять order во время render.
14. Venipak credentials исторически/default присутствуют в tracked code/migration и лежат plaintext в БД; требуется ротация, secret store и allowed-host policy.

## Авторизация, аккаунт и чаты — FN-09

Laravel standard auth работает параллельно с custom AJAX login/register и Socialite Facebook/Google. Custom registration защищён reCAPTCHA, но standard `/register` остаётся альтернативным контрактом; исходный пароль передаётся в registration Mailable. Socialite callbacks используют `stateless()`, связывают аккаунт только по email и не сохраняют provider subject как уникальный идентификатор. Email verification фактически не используется.

Локальный read-only DB snapshot: 23 801 users, все password hashes имеют bcrypt prefix; точных и normalized duplicate email groups нет; `email_verified_at` заполнен у 0 users, `remember_token` — у 1 340. Это не является production-снимком.

Подтверждены четыре независимых legacy chat streams: `order_user_comments` 3 835 rows, `orders_chats` 2 840, `order_admin_comments` 106, `order_painter_comments` 25. Есть orphan/order attribution gaps, а account mutations в основном принимают client-supplied order/chat/image IDs без ownership scope. Отдельные native `sa_messages`/`sa_conversations` в локальной БД пусты и не должны сливаться с legacy streams без явной mapping/retention политики.

## Почта, queues и scheduler — FN-10

Подтверждены 33 Mailables, 3 Notifications, 13 console commands и 5 scheduled commands. Локальный queue default — `sync`, cache — `file`; `jobs` и `failed_jobs` пусты. Поэтому direct mail и queued Notifications фактически выполняются в caller process, а `withoutOverlapping()` не доказывает multi-host locking.

SMTP peer/name verification отключена и разрешён self-signed certificate. `AppServiceProvider` напрямую использует SwiftMailer transport — blocker Laravel 9+. Публичные mail preview routes включают DB-mutating recovery/coupon paths. Marketing consent/unsubscribe, provider delivery receipt/bounce и transactional outbox отсутствуют.

Локальный read-only snapshot: 209 abandoned carts; 59 overdue orders считаются due текущим алгоритмом при нуле markers; 252 active two-date coupons, из них 162 связаны с `users.news=NO`. Ничего не отправлялось и команды не запускались.

## Публичный каталог и конструкторы — FN-11

Подтверждены 72 route/function/data contracts для gallery, canvas, collage, modular, family, portraits, caricature/Simpsons, sizes/prices и SA catalogue projection. Локальный snapshot: 1 038 gallery items, 41 categories, 80 sizes и 64 779 Voyager translations.

Критические дефекты: публичные GET `/set_sizes|set_genre|set_style` записывают DB без auth; новая item route статически abort 404 из-за `getBy(false)`; route registry по-прежнему блокируется missing `ImageController`. Найдены 28/37/31/66 orphan item links в четырёх pivot tables и отсутствие рабочих foreign-key indexes у этих pivots.

Public locales `lv|lt|pl|ru|de|en|ee`, дополнительные DB/lang данные `et` и CRM locale `uk` сохраняются без переименования/потери. 12 текущих Mix outputs зафиксированы размерами и SHA-256; Vite и пересборка public assets исключены.

## FN-12: Voyager/custom admin

L2-аудит подтвердил 133 BREAD, 1 997 DataRows, 675 permissions, 2 080 role links, 40 custom Voyager views и 52 custom route declarations после `Voyager::routes()` вне `admin.user`. Рабочих model/table pairs 131; `canvas` и typo `a_collag_faq` stale. Custom orders controller не реализует полный resource contract. Из 64 779 translations: 51 416 относятся к BREAD content, 13 003 — к Voyager metadata/menu, 360 — к отсутствующим legacy tables. Всё сохраняется/архивируется до owner decision; target включается волнами с одним writer.

## FN-13: SEO, feeds и auxiliary surface

Активная redirect map: 256 rules / 2 048 generated routes, 248 unique sources, 7 duplicate groups, 4 conflicting source groups, 3 chains, 13 rules с пробелами и 72 с non-ASCII. Все 256 `/pl` variants находятся после широкого `/pl/{any?}`; `/pl/canvas` подтверждённо уходит на homepage отдельного домена.

Локально XML well-formed: main sitemap 7 locale files; product/image sitemaps 1 174/1 140 URL; feeds 7 125 offers и 4.9–11.1 MB. Но `/robots.txt` application route делает self-redirect, feed templates не сбрасывают sale-price variables, public `/translate_item`/`set_meta` массово пишут переводы/SEO, review upload не имеет effective validation/ownership, а gift-card route имеет несовместимую signature. Route cache блокируют 2 003 closures и missing `ImageController`.

L2 code/local snapshot по FN-01…FN-13 завершён. До migration start открыты P0 containment и L3 production topology/logs/provider/browser/UAT/cutover evidence.
