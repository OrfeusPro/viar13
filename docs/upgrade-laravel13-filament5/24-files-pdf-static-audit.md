# 24. Статический аудит файлов, изображений и PDF

Дата среза: 13.07.2026. Статус: **FN-04 static L2 завершён; production corpus, HTTP security, failpoint и load L3 открыты**.

## 1. Объём и ограничения

Проверены:

- публичная отдача `/storage`, `/uploads`, `/orders` и admin-looking image routes;
- изображения корзины, заказа, клиента и художника;
- multipart, base64, raw SVG, audio и SA attachments;
- `Orders::renameUploadsPhoto()` и legacy rename/delete paths;
- счета заказа, locale счета и gift-card PDF;
- фактические metadata локальной БД и локальных каталогов;
- существующее тестовое покрытие и невозможность штатного `artisan route:list`.

Ни один заказ, файл или DB row не изменялся. Выполнены только чтение файлов, `SELECT`, Reflection и PHP lint под явным `C:\OSPanel\modules\php\PHP_7.4\php.exe`. Локальный corpus мал и не доказывает production. Построчная матрица: [appendix-file-pdf-contracts.csv](appendix-file-pdf-contracts.csv).

### Решения владельца от 17.07.2026

- сохранить intended file visibility по ролям: client own order, painter assigned order, manager/admin allowed scope;
- сохранить текущую совместимость форматов, размеров и отсутствие автоматического удаления;
- не вводить antivirus scanning;
- владелец отклонил обязательную private/authenticated delivery в базовой миграции; сохраняются текущие публичные URL, а residual confidentiality risk требует отдельного sign-off.

Отсутствие AV не отменяет real MIME/decode/size/dimension/path validation и quarantine. «Форматы как сейчас» не означает перенос `MIME *`, raw active SVG или проверки размера после полного чтения. Решение сохранить public delivery не разрешает path traversal, symlink escape, directory listing или расширение публичного file scope; predictable URL/ownership risk остаётся принятым ограничением до formal production sign-off.

## 2. Масштаб файловой подсистемы

Поиск sink-вызовов в `app/` дал 123 вхождения в 26 PHP-файлах: `file_put_contents`, `base64_decode`, `move`, `putFileAs`, Storage `put/store/storeAs`. Это не 123 независимых endpoint-а, но подтверждает, что files нельзя мигрировать одним generic uploader.

| Класс данных | Текущий storage/reference | Важный инвариант |
|---|---|---|
| checkout и form uploads | `public/uploads/*` и URL в JSON/полях | старые URL должны продолжить открываться до controlled rewrite |
| production order images | `public/orders/*` | имя используется производством и содержит код заказа/размера/опций |
| collage intermediates | `storage/app/public/user_images/*` | `renameUploadsPhoto()` переносит их в `public/orders` |
| painter/client media | legacy comma fields + `order_painter_images`/comments | обе legacy и row-based модели пока читаются UI |
| invoices | `public/storage/pdf/{orders.id}.pdf` | ID/VR/locale и точное содержимое счета неизменны |
| gift-card PDF | `public/storage/pdf-gift-card/*` | coupon создаётся вместе с payment side effects |
| SA attachments | `storage/app/public/sa/attachments/YYYY/MM/*` | физическая локальная копия обязательна по текущему ТЗ |

Target должен сначала классифицировать `public catalogue`, `private order/customer`, `temporary`, `generated document` и `integration attachment`; один public disk для всех классов недопустим.

## 3. Public file serving и confidentiality

Три анонимных closures очищают path одним `str_replace(['../', '..\\'], '', $path)`. Строка вида `....//secret.svg` после одного прохода превращается в `../secret.svg`; canonical root containment отсутствует. Это уже риск R-35 и касается `/storage`, `/uploads`, `/orders`.

`/admin/user_images/{path}` и `/admin/gallery-items/{path}` находятся до группы `admin.user`, разрешают SVG и применяют ту же неканоническую очистку. Префикс `/admin` не создаёт авторизацию.

Счета сохраняются как `public/storage/pdf/{orders.id}.pdf`. Числовой order ID предсказуем, а PDF содержит клиентские и платёжные реквизиты. В локальном снимке `has_pdf=1` у 6 907 заказов, `pdf_approved=1` у 6 661, хотя локально присутствуют только 6 PDF-файлов. Это одновременно confidentiality gap и доказательство, что локальный file corpus не является production backup.

Целевой контракт: private object key, authorization/ownership download controller, `Content-Disposition`, audit log и короткоживущая signed URL при необходимости. Legacy public URLs сохраняются только через контролируемый compatibility gateway и deny-by-default classification.

## 4. ACL и ownership

Подтверждены четыре разных класса проблемы:

1. Custom `/admin/*` routes после `Voyager::routes()` не наследуют `admin.user`. У `Admin\\OrdersController`, `ImageGenController` и public `OrdersController` нет constructor middleware.
2. `VoyagerAdminController` требует только `auth`, поэтому обычный authenticated user достигает `tiny_upload`/audio upload без admin permission.
3. Public localized order actions `add_client_images`, remove/status/show routes не проверяют auth, role или ownership.
4. Account routes имеют `auth`, но методы painter/client upload принимают произвольный `order_id` и не доказывают, что текущий user — владелец заказа или назначенный painter.

Следствие: файловая Policy должна проверять не только role, но `order.user_id`, painter assignment, разрешённый тип вложения и состояние заказа. CSRF не заменяет authorization.

## 5. Upload validation

Валидация фрагментирована:

- `Orders::saveOrder()` проверяет image/MIME, но не задаёт max;
- painter services задают `max:9000000`, что в Laravel измеряется в KiB и практически снимает лимит;
- client chat validator сочетает `image` с `pdf|fig`, поэтому декларация противоречива;
- `add_painter_images`, `add_client_images`, Admin order uploads и several page/product flows сохраняют файлы без MIME/size policy;
- TinyMCE использует original filename, не проверяет тип/размер и может перезаписать существующий public file;
- public forms часто ограничивают extensions, но не aggregate bytes/count и пишут прямо в `public/uploads`;
- review audio принимает base64 и сохраняет `.webm` без проверки MIME/size/duration.

Обязательная target policy: upload intent, allowlist по реальному `finfo`/decode, per-file и request aggregate limit, pixel/page/duration budget, random server filename, quarantine/antivirus для документов, запрет executable/active content, private storage и cleanup journal.

## 6. Base64, SVG и image bombs

`BasketController::updateimg()`, product-family controllers и `ImageSaverService::base64Decode()`:

- используют non-strict `base64_decode`;
- определяют расширение по строковому prefix либо всегда ставят `.png`;
- не проверяют реальный MIME и успешный image decode;
- не ограничивают encoded/decoded bytes, размеры изображения или число collage layers;
- в некоторых ветках записывают файл до проверки, будет ли ссылка принята basket state.

`ImageSaverService::saveSvgImage()` сохраняет raw SVG из `collageSvgImage` в public storage. Нужен отдельный бизнес-вопрос: SVG является производственным source или только временным layout. Если он нужен, его нельзя inline-serve без sanitizer; внешние references/scripts/foreignObject должны быть запрещены или SVG хранится как download-only private artifact.

## 7. Переименование и согласованность DB/files

`Orders::renameUploadsPhoto()` — 391 строка и реальный migration hotspot. Он:

- собирает paths из `items.activeImage`, `savedImage`, `orig_images`, `allImages`, `allBackgrounds`;
- перемещает `public/uploads|orders` и копирует+удаляет `storage/user_images`;
- обновляет legacy painter/client fields, `order_painter_images`, `order_user_images`, затем `orders.items`;
- использует производственное имя с order ID, total items, size, quantity, express/delivery/country/canvas/lac/gift/brushstrokes/baguette/orientation;
- предотвращает часть filename traversal через `sanitizeFilenamePart()` и имеет один unit test только на slash removal.

Файловые move/copy/delete и DB updates не обёрнуты общей transaction/journal. При exception после удаления source, но до финального `$order->save()`, DB может остаться со старым URL, а исходного файла уже нет. Обычная DB transaction не откатывает filesystem; нужен двухфазный protocol: copy to immutable target → verify size/hash/decode → DB transaction switch references → after-commit cleanup source. Повтор должен быть идемпотентным.

Отдельно `deleteOrder()` удаляет order/VR rows и меняет bonuses, но не очищает PDF/images/child image rows. В локальной БД найдено 28 `order_painter_images` без соответствующего `orders.id`; FK отсутствуют. Автоматически удалять эти rows/files нельзя до production reconciliation и retention approval.

## 8. PDF счета

`DynamicPDFController::getPDFFromOrder()` пишет непосредственно в окончательный `public/storage/pdf/{id}.pdf` и перезаписывает его. Нет temp file, atomic rename, версии, hash/size receipt или проверки открытия PDF после записи.

`getOrderDataInHtml()` не является pure renderer: если user заказа отсутствует, он может создать нового user и изменить `orders.user_id`. В HTML конкатенируются поля заказа, delivery, items и переводы без единого escaping contract. Dompdf 0.8.6 настроен с `enable_remote=true` и `enable_javascript=true`; это усиливает необходимость fixture для HTML injection, remote resource/SSRF и dependency upgrade, даже если обычный invoice template сам использует локальный logo.

`approve_user_checkout` и `generate_checkout` проверяют admin/manager внутри метода, но остаются GET side effects. Первый принимает отдельно `user_id` и `order_id`, не подтверждая их связь; второй ставит `has_pdf=1` до успешной записи файла. Повтор может снова генерировать PDF/отправлять mail.

Target artifact receipt должен хранить: `order_id`, invoice/VR number, locale snapshot, template/version, content hash, file hash/size, generated_at, generated_by и статус. Генерация идёт во временный private file, затем атомарно публикуется только после validation; DB status меняется в одной локальной transaction с outbox.

## 9. Языки и переводы PDF

Сохраняются все публичные локали `lv|lt|pl|ru|de|en|ee`. Для счета действует отдельный contract:

- базовый язык берётся из `users.locale`;
- `users.pdf_locale` переопределяет его;
- preferred `ee` вручную отображается в translation locale `et`;
- пустой locale падает в `en`;
- `OrderString` одновременно читается из DB translation layer, остальные подписи — из PHP lang files.

`changePdfLocale()` допускает любую строку до 10 символов и меняет настройку user целиком, а не immutable locale конкретного счета. Локальный срез: `pdf_locale` пуст у 23 737 users; явно `lv=35`, `ee=17`, `lt=8`, `en=3`, `pl=1`. Отсутствие explicit `ru/de` не означает, что счета на этих языках не используются: они идут через preferred locale.

Перед переносом нужен golden PDF corpus для всех семи публичных локалей плюс `ee→et`, пустого и недопустимого locale; сравниваются текст, font glyphs, page breaks, decimal/date/address и file hash/visual pages. DB translations и PHP lang files переносятся вместе и продолжают получать production delta до cutover. CRM `ru|uk|en` остаётся отдельным API-контрактом; `uk` не добавляется автоматически в публичные URL.

## 10. Gift-card PDF

Route `ANY /giftcard/pdf/{price}/{locale}` передаёт два параметра методу с тремя обязательными (`user_id`, `order_id`, `coupon_id`). Reflection под PHP 7.4 подтвердил `required=3`, `total=6`; route не соответствует handler contract. Рабочий production вызов найден только как прямой method call из manual payment-status side effect.

Файл имеет предсказуемое имя `{order_id}_{user_id}_{coupon_id}.pdf`, лежит в public, перезаписывается без receipt и создаётся в цепочке coupon/PDF/mail без transaction/outbox. Directory creation не видна. Повтор `payed→payed` из FN-03 способен создать новые coupons/PDF/mail.

Нужно решить: public preview удалить/закрыть или превратить в admin-only preview без write. Реальная выдача gift card — private tokenized download, один artifact на unique coupon, immutable code/locale/price snapshot и идемпотентный outbox.

**P0 plan 14.07.2026:** public preview и HTTP generation route требуется закрыть отдельными default-off gates, сохранив internal direct call из order/payment flow. В текущем codebase gate не внедрён. Signature/authorization/artifact contract также не исправлен.

## 11. SA attachments

Положительно: binary проверяется через `finfo`; allowlist по умолчанию `jpeg|png|webp|pdf`; есть max-size после download; сохраняется локальная копия и metadata.

Критические пробелы:

- произвольный payload URL читается `file_get_contents`;
- разрешены redirects;
- TLS peer/name verification отключены;
- private/reserved IP, DNS rebinding, scheme и redirect target не фильтруются;
- весь body читается в память до проверки размера;
- attachment сохраняется на public disk, URL вставляется в chat text.

Это SSRF/MITM/memory-DoS и confidentiality risk даже при `X-Api-Key`: compromised integration credential не должен давать доступ к metadata services/LAN. Target downloader разрешает только `https`, утверждённые host/signed URLs или строгий egress proxy, проверяет DNS/IP на каждом redirect, включает TLS, stream-limit, timeout, MIME/decode и пишет в private storage. Повтор одного `message_id` должен ссылаться на один file hash, а не создавать копии по `time()`.

## 12. Локальный corpus и data quality

Read-only срез:

| Метрика | Значение | Ограничение |
|---|---:|---|
| orders exact | 14 423 | локальный снимок |
| orders с `painter_images` | 2 430 | legacy comma field |
| orders с `painter_sketch_images` | 1 888 | legacy comma field |
| orders с `client_images` | 352 | legacy comma field |
| `order_painter_images` | 7 199 | 28 rows без order |
| `order_user_images` | 0 | таблица не доказывает отсутствие chat attachments |
| `public/uploads` | 23 файла / 21 777 547 bytes | sparse local copy |
| `public/orders` | 8 файлов / 7 976 181 bytes | sparse local copy |
| `public/storage/pdf` | 6 файлов / 5 587 130 bytes | sparse local copy |
| `storage/app/public/user_images` | 3 файла / 797 536 bytes | sparse local copy |

Production inventory должен быть metadata-only: canonical relative path, classification, bytes, MIME/finfo, dimensions/pages, hash, DB reference count, order/owner presence, created/modified time и orphan reason. Имена/PII/contents в аудит-отчёт не выгружаются.

## 13. Тестовое покрытие и baseline defect

Найден только узкий `OrderFilenameSanitizationTest`: он доказывает отсутствие `/` и `\\` в generated filename. Нет tests для ownership, MIME/size, base64/SVG, traversal, rename failpoints, private PDF, locale matrix, gift-card route, SA SSRF и orphan cleanup.

`artisan route:list` под правильным PHP 7.4 продолжает падать из-за отсутствующего `App\\Http\\Controllers\\ImageController`. Поэтому middleware contract проверен по source/собственному registry, но L3 обязан сначала восстановить или официально удалить этот handler и снять cached/uncached framework route snapshot.

## 14. Обязательный characterization/security corpus

1. ACL: anonymous, user, painter assigned/unassigned, manager, admin; own/foreign/missing order.
2. Paths: plain/encoded/double traversal, mixed slash, symlink, null byte, very long name, Unicode/confusable.
3. Upload: valid formats; fake extension; double extension; HTML/PHP/SVG; polyglot; zero byte; 1 byte below/at/above limit; aggregate count/bytes.
4. Decode: corrupt image, extreme dimensions, decompression bomb, EXIF orientation/metadata, CMYK, HEIC/PSD/FIG/PDF business allowlist.
5. Base64/SVG: invalid alphabet/padding, wrong data URI, decoded MIME mismatch, script/event/foreignObject/external URL.
6. Rename: every source path shape, duplicate reference, collision, missing source, target exists, fail after each file/DB step, replay/concurrency.
7. PDF: seven public locales, `ee→et`, empty/invalid locale, long Unicode, HTML-like customer fields, disk full, parallel generation, deterministic content.
8. Gift card: route arity, invalid price/locale, duplicate paid event, coupon/PDF/mail exactly once.
9. SA: private/loopback/link-local/IPv6/DNS rebinding, redirect to private host, invalid TLS, slow/chunked/oversize, MIME mismatch, duplicate message.
10. Retention: order deletion, chargeback/GDPR/legal hold, orphan row/file, backup restore and compatibility URL.

## 15. Failpoint protocol

Для upload/rename/PDF test harness должен уметь падать:

- после temp write;
- после first/last copy;
- после source delete;
- до/после DB reference switch;
- после DB commit, до outbox/cleanup;
- при mail/SA/provider failure;
- при disk full/permission denied/timeout.

После каждого падения проверяются: ни одной ссылки на отсутствующий file, ни одного общедоступного temporary/private artifact, исходник либо сохранён, либо восстановим, повтор безопасен, cleanup job не удаляет новый owner file.

## 16. Definition of Ready FN-04

До переноса files/PDF должны быть утверждены:

- production metadata corpus и классификация public/private/temporary/generated;
- route/action/role/ownership matrix и negative HTTP tests;
- единая upload policy с реальным MIME/decode, size/count/pixel/page limits и quarantine;
- private order/SA/PDF storage с контролируемой выдачей;
- двухфазный file move protocol и artifact journal/receipt;
- immutable production filename compatibility manifest;
- PDF locale/template/content/file receipt и golden corpus на всех языках;
- retention/GDPR/legal-hold/orphan policy;
- SSRF-safe streamed downloader для SA;
- backup/restore rehearsal для DB **и файлов одного watermark**;
- observability: event/order/file correlation без PII и секретов.

FN-04 остаётся открытым до L3. Сначала закрываются публичные write/download и ownership gaps, затем снимается production corpus, после чего выполняются rename/PDF failpoint и locale fixtures. Перегенерация существующих public assets в этот этап не входит.

## 17. P0 review media plan 14.07.2026

Для обоих review upload flow требуется auth/rate, explicit slots, MIME/size/audio validation, generated filenames и удаление созданных файлов при DB insert failure. Эти меры не внедрены: временный прототип удалён. Media сейчас попадает на public disk до moderation; private quarantine/promotion, AV/CDR, HEIC dimension/metadata checks, retention и orphan reconciliation обязательны до production closure.
