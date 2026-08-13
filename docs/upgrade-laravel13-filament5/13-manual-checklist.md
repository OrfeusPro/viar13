# 13. Чек-лист ручной приёмки

Отмечать отдельно на legacy baseline, target staging и production smoke. Для каждого пункта сохранять URL/locale/role/order ID, ожидаемый и фактический результат, screenshot/log correlation без PII.

## Runtime, deployment и восстановление

- [ ] Production topology и effective PHP/web/DB/Redis/storage versions/config подтверждены redacted evidence, а не локальной OSPanel.
- [ ] Ровно один scheduler owner; все пять команд имеют heartbeat, last success/failure, timezone и overlap/lock evidence.
- [ ] Purpose queues/workers проходят enqueue/process/fail/retry/drain/restart/rolling-deploy smoke без live provider calls.
- [ ] Session/cart/admin сохраняются при переключении между web hosts; cookie, TTL, locking и failover соответствуют подписанному контракту.
- [ ] Из encrypted backup выполнен restore в изолированную среду; RPO/RTO, table counts/hashes и schema objects сверены.
- [ ] Storage link/mount создаётся fresh release; synthetic public/private file и PDF проходят read/write/delete/download/cleanup на каждом host.
- [ ] Log rotation/redaction/correlation и disk quota работают; тестовая ошибка видна в central logs без PII/secret.
- [ ] Metrics/alerts охватывают HTTP/DB/queue/scheduler/disk/providers; тестовый alert доставлен ответственному.
- [ ] Atomic deploy и application rollback воспроизведены; rollback не восстанавливает stale DB dump поверх новых live writes.
- [ ] PHP/web/CLI/workers/DB используют согласованный UTC/business timezone contract; DST fixtures проходят.

## Публичный сайт и SEO

- [ ] Homepage открывается на `lv|lt|pl|ru|de|en|ee`, locale switch сохраняет смысл страницы.
- [ ] Header/footer/service catalogue: порядок, изображения, ссылки и 19/11 базовых записей совпадают.
- [ ] Нет потери переводов; base English отображается корректно; `et` orphan и `uk→ru` CRM fallback не меняют public locales.
- [ ] Категории, gallery, canvas/collage/modular/portrait pages и blog открываются.
- [ ] 550 legacy redirects и текущие redirects дают прежний status/location; canonical/hreflang/sitemap не регрессировали.
- [ ] 404/500 страницы, cookie/consent, search, формы, mobile/tablet/desktop.
- [ ] Public CSS/JS/images/fonts загружены по прежним URL и совпадают с frozen manifest/hash corpus; browser console чиста; Filament assets изолированы и не меняют public pages.

## Корзина и checkout

- [ ] Guest и authenticated cart; add/update/remove; coupon/bonus/Facebook gift cases.
- [ ] Все product families, price/discount/delivery totals, countries/currencies/rounding.
- [ ] Upload photo/example, preview/generator, allowed/forbidden/oversize file.
- [ ] Anonymous/wrong-role/foreign-order не может upload/remove/status/download order files или PDF.
- [ ] Base64/SVG/audio: corrupt, fake MIME, active content, image bomb и aggregate oversize отклоняются до publish.
- [ ] Invoice/gift-card PDF: все `lv|lt|pl|ru|de|en|ee`, `ee→et`, long Unicode, повтор и параллельная генерация.
- [ ] Rename failpoints: после copy/delete/DB switch повтор не теряет файл и не оставляет broken reference.
- [ ] SA attachment URL: private IP/redirect/DNS rebinding/invalid TLS/slow oversize отклоняются; authorized file остаётся private.
- [ ] Delivery: pickup/home и Venipak list; browser price/method/country tampering не меняет server quote; wrong-country/removed/stale point отклоняется или даёт approved stale fallback.
- [ ] Venipak label/courier: `&<>`/Unicode address, N packages, double-click, timeout/unknown outcome, reconcile, cancel/void и reprint не создают второй shipment.
- [ ] Venipak print: anonymous/wrong-role/foreign label/fake PDF/oversize body заблокированы; filename безопасен; artifact audit содержит order/attempt без PII/secret.
- [ ] Создан один order с прежними `items`, payment/delivery/status; confirmation/account видят тот же ID.
- [ ] Double-click/reload/back не создают duplicate order/payment.

## Оплата

- [ ] PayPal success/cancel/decline/retry; повтор callback/capture не удваивает состояние.
- [ ] Paysera accept/cancel/server callback; invalid signature/replay/out-of-order.
- [ ] Payment request create/open/expire/success/cancel/replay.
- [ ] Amount/currency/order/provider reference совпадают; Synvolve получает ровно одно итоговое изменение.

## Регистрация и кабинет

- [ ] Registration/login/logout/reset/remember, existing password hashes.
- [ ] Facebook и Google: new/existing/no-email/deny/collision; локализованный callback.
- [ ] Profile/address/consents; order list/detail/PDF; permissions to foreign order blocked.
- [ ] Client chat: text, each attachment type, ordering/read flags, duplicate submission.

## CRM/SA

- [ ] `lead_id=orders.id`; все семь status strings сохранены.
- [ ] valid/invalid/missing X-Api-Key; secret отсутствует в logs.
- [ ] lead create/update/unknown temporary, order lookup, services/sizes/prices.
- [ ] message inbound/outbound, bot control, escalation, pipeline; `event_id` duplicate → HTTP 200 status duplicate.
- [ ] Attachment physically stored, MIME/size enforced, download authorized.
- [ ] timeout/retry/outage recovery и correlation event/order ID.

## Почта и фоновые процессы

- [ ] Все transactional templates на нужных locales; subject/from/reply-to/links/assets.
- [ ] Marketing consent respected; batch/rate/failure/retry/bounce behavior.
- [ ] Scheduler jobs в ожидаемой timezone и один раз; `withoutOverlapping` работает.
- [ ] Queue worker restart, failed job и retry; release не оставляет jobs старого class shape.

## Filament

- [ ] Login/logout/session/2FA decision; mobile navigation.
- [ ] Каждый из 8 roles видит только разрешённые navigation/resources/records/actions.
- [ ] 133-module manifest: CRUD/search/filter/sort/order/relationships/translation/upload/custom actions.
- [ ] Orders/chat/payment/Venipak/SA custom Pages имеют explicit authorization и audit.
- [ ] Bulk delete/restore/export не доступны без согласованного policy.
- [ ] Parallel Voyager module read/write ownership соблюдается.

## Ошибки, безопасность, производительность

- [ ] Validation понятна и не раскрывает stack/SQL/secrets.
- [ ] CSRF/origin, XSS, IDOR, upload, rate limit manual probes пройдены.
- [ ] p95, SQL count, memory, page/bundle size не хуже gate; нет N+1.
- [ ] 5xx, queue, payment, SA, mail, Venipak dashboards/alerts видят synthetic events.
- [ ] Rollback rehearsal вернул предыдущую release без потери созданного test order/event.
- [ ] Во время rehearsal непрерывно создаются заказы/чат-сообщения и повторяются callback; после переключения/rollback нет потерь и дублей.
- [ ] Во время rehearsal редактор изменяет перевод, base English field, slug и ordering; target получает изменения без потери других locale.
- [ ] После переключения Voyager не может писать в уже переданный Filament модуль, но остаётся доступным read-only в rollback window.
- [ ] Старый application release после rollback читает все заказы, созданные новой версией в рамках backward-compatible schema.

## FN-09: авторизация, Socialite, кабинет и чаты

- [ ] Проверены standard/custom login/register/reset/logout, throttle/CAPTCHA, session rotation и отсутствие пароля в письме.
- [ ] Проверены Facebook/Google: success/cancel/state expiry, existing/new/collision account, missing/unverified email, redirect только на разрешённый origin.
- [ ] Для client/painter/admin/staff проверены свой и чужой order, chat, image, read flag, profile/password.
- [ ] Обычный user не может создать admin message, запустить staff mail/Synvolve или получить список users.
- [ ] Проверены все четыре chat streams: автор, порядок, unread flags, attachments, reload/AJAX view, HTML/script corpus, double click/offline retry.
- [ ] Utility GET не меняет данные, unsubscribe подписан, а logs/mail не содержат secrets/hash/token/лишний PII.
- [ ] Во время UAT legacy продолжает принимать production-заказы, сообщения и переводы; зафиксированы watermark и delta reconciliation.

## FN-10: почта, queues и scheduler

- [ ] Все `/mail/*` previews недоступны anonymous в production и не меняют DB из GET.
- [ ] SMTP проверяет certificate/host; staging использует sink и не может отправить production recipients.
- [ ] Для каждого template проверены семь locale, subject/body/links/assets/unsubscribe и deterministic retry.
- [ ] `news=NO`, bounce/complaint/suppression действительно блокируют marketing; transactional classification утверждена.
- [ ] Order/payment/chat сохраняются при mail outage без duplicate при повторе.
- [ ] Queue retry/failed/restart и scheduler stale-lock/two-host/DST scenarios видны в dashboard/runbook.
- [ ] Abandoned/overdue commands сначала показывают dry-run cohort, max batch и не отправляют без owner approval.
- [ ] PDF attachment берётся по immutable receipt/hash с `application/pdf`; logs не содержат recipient/body PII.

## FN-11: публичный каталог и конструкторы

- [ ] Anonymous `/set_sizes|set_genre|set_style` даёт 404/403 и не меняет ни одной catalogue row.
- [ ] Существующий item открывается по правильному type URL; invalid type canonicalizes или 404 по утверждённому contract.
- [ ] Для `lv|lt|pl|ru|de|en|ee` совпадают title/content/price/slug/canonical/schema; `ee/et/uk` fallback утверждён.
- [ ] Filters/search/order/page дают те же item IDs и pagination links; invalid input не даёт 500/slow query.
- [ ] Canvas/collage/modular/family/portrait/caricature/Simpsons проходят start→options→upload→preview→basket/order.
- [ ] Display, basket, order и SA API совпадают по цене для item/size/options/country/time.
- [ ] Old/new URLs, sitemap/feed и 301 map проходят crawl без chain/loop/locale loss.
- [ ] Все 12 public bundles имеют исходный SHA-256, load order и browser-console behavior; сборка не запускалась.
- [ ] Orphan rows сохранены в quarantine report; translation/catalogue delta после T0 полностью reconciled.

## FN-12 Voyager/Filament

- [ ] Все 133 module decisions и владельцы подписаны; IDs 27/129 имеют отдельное решение.
- [ ] 8 ролей × Resource/Page/Action проверены, включая forbidden navigation/direct URL/bulk action.
- [ ] Все 64 779 translations, PHP dictionaries, metadata/menu labels и `ee/et/uk` boundary сверены.
- [ ] 40 custom views/actions имеют screenshot/action parity либо formal retire decision.
- [ ] 52 custom routes после `Voyager::routes()` закрыты единым auth+ability perimeter.
- [ ] Для активной волны ровно один writer; conflict/delta/rollback rehearsal пройден.

## FN-13 SEO/auxiliary

- [ ] Все 256 redirect rules имеют один утверждённый target/status; duplicates/chains/encoded URLs проверены.
- [ ] PL path/query/domain mapping и seven-locale sitemap/canonical/hreflang подписаны SEO owner.
- [ ] `/robots.txt` через application возвращает 200 text/plain без self-redirect.
- [ ] Main/product/image sitemap и три feeds schema-valid; counts, prices и sale samples совпадают.
- [ ] Реализовать default-off gate для `/translate_item`, `/set_meta`, mail preview, image debug, gift-card HTTP writer и Synvolve test receiver; временный прототип не является частью текущего codebase.
- [ ] После deployment подтверждено, что production flags/cache оставлены `false`, anonymous probes дают пустой 404 и нет DB/file/provider diff.
- [ ] Реализовать и повторно прогнать review auth/rate/email ownership/MIME-size/WebM/slot/cleanup/XSS tests; прежние результаты относились к удалённому прототипу.
- [ ] На staging/production-like проверены private quarantine/promotion, AV/metadata/dimensions, translated inline errors, duplicate window и seven-locale browser recording.
- [ ] `route:list`/fresh boot/cache проходят; missing handlers и unintended duplicate names отсутствуют.
- [ ] Access/Search Console/merchant canary и rollback evidence приложены.
