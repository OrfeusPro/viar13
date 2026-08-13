# 06. План переноса кода приложения

| Слой | Реальные файлы | Изменение | Проверка |
|---|---|---|---|
| Bootstrap | `bootstrap/app.php`, `public/index.php`, kernels, Handler | принять структуру Laravel 13 skeleton; переносить middleware/providers явно | boot, config/route cache, exception rendering |
| Routing | `web.php`, `api.php`, `admin.php`, `redirect*.php` | class routes, разделение public/admin/integration; сохранить names/URLs/locale prefixes/301 | route snapshot; сначала исправить missing ImageController baseline |
| Models | 159 files; `Orders`, `User`, `BaseModel` | native types/casts осторожно; вынести side effects в services; сохранить serialization | model serialization snapshots, dirty/cast tests |
| Orders/checkout | `Orders.php`, `BasketRepository`, `BasketController`, `OrdersController` | выделить Pricing, OrderCreation, StatusTransition, Attachment services; транзакционные границы | golden order fixtures, totals, status history, files |
| CRM/SA | `SaIntegrationController`, `AdminSaIntegrationController`, `SynvolveWebhookService` | разделить Requests/DTO/handlers/repositories/outbox; сохранить contracts | success/validation/duplicate/retry tests |
| Middleware | 19 files; `VerifyIntegrationApiKey`, locale, proxy/CSRF | target signatures; constant-time API key check; explicit callback exceptions | auth/locale/origin/CSRF matrix |
| Providers | 12 files | удалить SwiftMailer access; target Socialite providers; register observers/policies | container resolution, mail/OAuth smoke |
| Validation | inline rules в крупных controllers | перенос в FormRequests без изменения accepted payload | captured request corpus |
| Jobs/queues | `GenerateImageAltJob`, notifications, marketing mail | target serialization, tries/backoff/timeout/unique policy; production worker | retry/failed job/restart tests |
| Scheduler | `Console/Kernel.php` + 13 commands | перенести расписание; central timezone/locks/single-server | schedule list и controlled clock |
| Mail | 33 Mailables, 3 Notifications | Symfony Mailer, queued policy, failure events, template snapshots | SMTP sandbox, HTML/text/links/locale |
| Storage | uploads/public/local usages | Flysystem 3 semantics; explicit disk roots; streaming; preserve paths | upload/read/move/delete/rollback corpus |
| Private artifacts/PDF | order/client/painter/SA files, invoices, gift cards | classification + Policy download; pure renderer; artifact receipt; two-phase move/atomic publish | ACL/IDOR, failpoints, seven-locale golden PDF |
| Logging | `config/logging.php`, integration services | structured context, event/order IDs; redaction; rotation | no tokens/PII in logs, channel tests |
| Blade | 713 views | сохранить public Blade; заменить только Voyager views на Filament | screenshot/mobile/SEO snapshots |
| Tests | 23 files | PHPUnit 8→12, add characterization before refactor | same fixtures on legacy/target |

## Критические точечные изменения

1. `AppServiceProvider.php:29` вызывает `Mail::getSwiftMailer()->getTransport()`. В Laravel 9 SwiftMailer заменён Symfony Mailer; цель функции нужно установить и переписать через поддерживаемый transport/event API.
2. `BaseModel::serializeDate()` уже сохраняет legacy формат. Оставить до формального решения API contract, иначе Laravel 7 изменит JSON даты.
3. `TrustProxies` должен перейти с `fideloper/proxy` на framework middleware.
4. `FILESYSTEM_DRIVER` и `MAIL_DRIVER` нормализовать на target names, сохранив переходные aliases на deploy.
5. `Storage::disk('local')` задать явно: Laravel 12 меняет default root на `storage/app/private`.
6. SVG/image validation пересмотреть: Laravel 12 исключает SVG из `image` по умолчанию; Voyager media сейчас слишком широко допускает `*`.
7. 27 raw-query sites проверить на MariaDB и Laravel 10 Expression API; особенно `SaIntegrationController` и aggregate queries.
8. PHP 8.3 static scan: dynamic properties, incompatible signatures, nullable defaults, string/array offset warnings, deprecated functions.
9. Не переносить `renameUploadsPhoto()` механически: target protocol — immutable copy, hash/decode verify, DB reference switch, after-commit source cleanup и replay-safe journal.
10. Dompdf renderer отделить от user/order mutations; remote/JavaScript отключить по умолчанию, все order/customer strings проходят explicit escaping/resource policy.
11. Central upload policy обязана покрыть multipart, base64, raw SVG, audio, TinyMCE, PDF/PSD/FIG/HEIC и aggregate limits; public disk не является fallback для private customer content.

## Frontend: сохранить без миграции и пересборки

Решение: публичный frontend исключается из технологической миграции. CSS/JS ведутся вручную в `public/`; Mix не собирает модульное Webpack-приложение, а только конкатенирует и версионирует существующие файлы. Повторная генерация, изменение порядка или автоматическая оптимизация могут сломать UI и генераторы.

1. зафиксировать `mix-manifest.json`, URL, размеры и SHA-256 всех используемых CSS/JS/images/fonts;
2. сохранить готовые `home`, global, canvas, collage, family, gallery, graph, graph-child и modular assets без Vite-конвертации;
3. в Laravel 13 оставить совместимый `mix()`/manifest adapter либо прежние публичные URL;
4. не менять порядок файлов, не минифицировать заново и не удалять двойное включение `PhotoEditor.js` в рамках миграции;
5. выполнить visual regression, browser-console, cache-busting и CDN tests по неизменяемому asset corpus;
6. Filament theme собирать отдельным Vite entry с Tailwind 4.1, не подключая public legacy CSS/JS к его pipeline;
7. возможную модернизацию публичного frontend оформить отдельным проектом после стабильного cutover Laravel/Filament.

## Последовательность рефакторинга

Сначала characterization, затем adapters, затем перемещение кода без изменения результатов, затем framework idioms. Одновременная замена framework, админки, payment SDK и бизнес-логики запрещена. Каждый PR должен иметь один migration concern и обратимый commit.

## FN-11: граница публичного catalogue migration

Публичный каталог переносится vertical slices: route → lookup/filter → localized data → price/options → session/basket → Blade/SEO → frozen assets. Old/new handlers остаются compatibility layer до access-log и redirect-map gate. Public maintenance GET routes не переносятся; их возможная логика становится authorized CLI/admin command с dry-run, transaction/batch и receipt.

Gallery/constructor pricing получает единый server-side authority, используемый public basket/order и SA projection. Raw rich-text fields классифицируются по контексту; plain/attribute/JS output кодируется, разрешённый HTML проходит versioned allowlist sanitizer без массовой перезаписи при framework cutover.

## FN-12: custom admin extraction

Перед заменой Voyager каждый custom controller/view разбивается на application service, explicit authorization policy, validated command/query и thin Filament Page/Action. Сначала characterizes current result/side effects, затем переносится один use case. `AdminLocaleController` PHP-file writer, incomplete orders resource methods и public/admin image generation не регистрируются в target автоматически; их reachability и business owner подтверждаются отдельно.

## FN-13: SEO/auxiliary extraction

`routes/redirect.php` не переносится копированием: raw `header()+exit`, helper functions и 2 048 closure routes заменяются versioned redirect dataset + validator + controller/middleware responder. Source/target/status/query/locale/domain являются данными с one-hop/cycle/duplicate checks.

Sitemap/feeds выделяются в read-only projection services с cursor/chunk, cache/ETag и stable snapshot timestamp. Текущие URL и price/multiplier semantics сохраняются golden tests; sale variables сбрасываются на каждый offer. `/translate_item` и `/set_meta` становятся authenticated commands с dry-run/run ledger. Mail/image/gift-card/test previews остаются только synthetic non-production tooling. Review submission получает FormRequest, verified order ownership, central upload policy, moderation и idempotency.
