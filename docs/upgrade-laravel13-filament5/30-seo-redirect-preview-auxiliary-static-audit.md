# FN-13: SEO redirects, sitemap/feeds, previews, reviews и auxiliary endpoints

Дата аудита: 14.07.2026. Уровень: static/data L2 + ограниченная локальная runtime-проверка read-only GET. Production traffic/runtime L3, browser/UAT и внешние provider calls не выполнялись.

## 1. Граница и безопасный режим

Проверены:

- `routes/redirect.php`, неподключённый `routes/redirect_old.php`, порядок подключения `routes/web.php`;
- redirect helper, image aliases/debug, польский catch-all и все вызовы `multiLangRedirect()`;
- sitemap, image sitemap, HTML sitemap, Google/Kurpirkt/Salidzini feeds и `robots.txt`;
- публичные mail preview, translation/meta utilities, review upload, gift-card preview/PDF;
- Google rating/reviews API, Synvolve test receiver, admin sitemap и missing `ImageController`;
- Laravel 6 route-cache implementation, route registry snapshot, duplicate names и missing handlers;
- локальные DB aggregates для источников sitemap/feed/review/translation без чтения PII, текстов, файлов и секретов.

Безопасными локальными GET были сгенерированы только read-only XML/redirect responses. `/translate_item`, `/set_meta`, review POST, mail preview, gift-card PDF, Google Places API и Synvolve capture не вызывались. Код приложения, business DB и public assets не менялись. Рабочий runtime: `C:\OSPanel\modules\php\PHP_7.4\php.exe`.

Полные приложения:

- `appendix-seo-redirect-map.csv` — 256 исходных redirect rules с дубликатами, цепочками и locale flags;
- `appendix-fn13-route-contracts.csv` — 47 auxiliary route/contracts с severity, required action и acceptance test;
- `appendix-routes.csv` — полный снимок 5 311 зарегистрированных routes.

## 2. Главный вывод

FN-13 нельзя свести к переносу нескольких XML-контроллеров. Эта поверхность одновременно содержит SEO-контракт, массовые изменения переводов/metadata, публичные diagnostic endpoints, file/PDF generation и route-boot side effects.

До Laravel 13 cutover обязательны четыре независимых gate:

1. **Security containment:** закрыть public mail previews, `/translate_item`, `/set_meta`, image debug, gift-card PDF и test receiver; review upload перевести на validated/verified flow.
2. **SEO contract:** утвердить versioned redirect map, польскую domain/path policy, canonical/locale/robots/sitemap URLs и production-log delta.
3. **Deterministic output:** зафиксировать golden XML/price/URL samples и устранить per-item state leak в feeds без frontend rebuild.
4. **Route registry/cache:** убрать closures и route-file `header()+exit`, восстановить/удалить missing `ImageController`, сделать route names уникальными.

Legacy production продолжает принимать заказы, отзывы, каталог и переводы. Новая система не становится writer этих данных до final delta reconciliation и отдельного переключения owner. SEO redirects можно переключать только после сравнения production access logs и crawler/merchant contracts.

## 3. Redirect inventory

### 3.1. Что реально активно

| Факт | Подтверждено |
|---|---:|
| Активный файл | `routes/redirect.php` |
| Вызовы `multiLangRedirect()` | 256 |
| Locale на правило | base + `en|pl|lv|lt|ee|de|ru` |
| Генерируемые route declarations | 2 048 |
| Уникальные base sources | 248 |
| Closure routes в итоговом route snapshot | 2 003 |
| `redirect_old.php` | не подключён |
| Исторические declarations в `redirect_old.php` | 550 |

Прежняя формулировка «активны 550 redirects из `redirect_old.php`» неверна. Этот файл является историческим источником и не должен автоматически объединяться с активной картой.

`uk` не входит в public redirect locales. Это не дефект само по себе: public site сохраняет `lv|lt|pl|ru|de|en|ee`, а CRM обязательные `ru|uk|en` остаются отдельным API/fallback contract. Нельзя добавлять `uk` в public URL map без SEO/domain решения.

### 3.2. Качество активной карты

| Проверка | Результат |
|---|---:|
| Группы duplicate source | 7 |
| Лишние duplicate rows | 8 |
| Sources с разными targets | 4 |
| Redirect chains | 3 |
| Cycles | 0 |
| Self redirects | 0 |
| Rules с пробелом | 13 |
| Rules с non-ASCII | 72 |
| External targets внутри `multiLangRedirect` | 0 |

Exact duplicate sources с одинаковым target:

- `/graphic-portrait/graphic-portrait/buy`;
- `/graphic-portrait/love-is-portrait`;
- `/graphic-portrait/low-poly-portrait`.

Conflicting sources:

- `/blog/-12` имеет три разных target;
- `/blog/-14`, `/blog/-4`, `/blog/-9` имеют по два разных target.

Три one-more-hop chain:

- `/graphic-portrait/Grafik-Porträt`;
- `/graphic-portrait/portrait-caricature`;
- `/graphic-portrait/portrait-caricature/buy`.

При повторной регистрации одинакового method+URI фактический winner зависит от RouteCollection/order. Поэтому CSV хранит все исходные строки, а не только «последний target».

### 3.3. Route-file side effects

В начале `redirect.php` при загрузке route file вызывается `URL::current()`, затем для substring `politics.html` и `/page/terms-sale` выполняются raw `header()` + `exit`. Это:

- происходит до controller/middleware;
- использует substring, а не нормализованный exact path;
- не даёт стандартный Laravel response/log/trace;
- может завершить HTTP/console/route discovery boot;
- плохо тестируется и не переносится в route cache.

Target contract: exact GET/HEAD controller redirects, утверждённые status/query rules и negative near-match tests.

### 3.4. Польский catch-all

`Route::any('/pl/{any?}')` зарегистрирован до 256 generated `/pl/...` redirect routes и отправляет запросы на `https://viar-art.pl/` с 301, теряя path/query. Локально подтверждено: `/pl/canvas` matched `pl/{any?}` и ушёл на homepage вместо `/pl/new/canvas`.

Также обнаружена неоднородная precedence для routes, добавленных позже localized group: основной `/pl/sitemap/sitemap.xml` попадает в catch-all, image sitemap может достигать своего controller, `/pl/review` достигает page controller. Это запрещает перенос «как есть» без executable route matrix.

Нужны утверждённые правила:

- какие `/pl/*` остаются на `viarcanvas.com`, а какие уходят на `viar-art.pl`;
- сохраняются ли path/query;
- какие legacy redirects выполняются до cross-domain transfer;
- canonical/hreflang и sitemap ownership польского домена;
- только GET/HEAD, не `ANY`.

## 4. Image aliases и public debug

`redirect.php` содержит closure routes для `/images_single/*`, DB/media lookup по canvas/collage/gallery/portrait и fallback images.

Критические свойства:

1. если локальный файл не найден, код делает `file_get_contents($storageUrl)` во время HTTP request;
2. URL часто генерируется самим приложением, поэтому возможны loopback/recursive fetch и worker exhaustion;
3. `?debug=1` и `/images_single_debug/{slug}` вызывают `get_headers()` и возвращают `source`, filesystem paths, `public_path` и `storage_path`;
4. route не имеет auth/rate limit/URL allowlist;
5. DB lookup и outbound fetch находятся прямо в route helper/closure.

Target: controller + image resolver service, local-disk-first, host allowlist, no loopback/private IP, time/size limit, cache/ETag и одинаковый fallback. Public debug удаляется; admin diagnostics не возвращает absolute paths.

## 5. Sitemap, robots и locale contracts

### 5.1. Локальный runtime snapshot

| Endpoint | Status | Размер | XML | Nodes |
|---|---:|---:|---|---:|
| `/sitemap.xml` | 200 | 1 069 B | valid | 7 sitemap |
| `/sitemap/sitemap.xml` | 200 | 258 561 B | valid | 1 174 URL |
| `/sitemap/sitemap-images.xml` | 200 | 887 146 B | valid | 1 140 URL / 3 581 images |
| `/sitemap_products.xml` | 200 | 216 248 B | valid | 980 URL |
| `/google-ads.xml` | 200 | 11 054 224 B | valid | 7 125 items |
| `/kurpirkt.xml` | 200 | 4 917 673 B | valid | 7 125 items |
| `/salidzini.xml` | 200 | 6 075 709 B | valid | 7 125 items |
| `/robots.txt` через Laravel | 302 | 378 B | n/a | Location указывает на тот же `/robots.txt` |

Это локальный snapshot, не production SLA. XML well-formed не доказывает корректность URL, цены, canonical, доступность images и merchant acceptance.

### 5.2. Подтверждённые defects/gaps

1. `RobotsController::mainRobots()` редиректит `/robots.txt` на тот же URL. Сейчас production может скрывать дефект статическим `public/robots.txt`, но при изменении web-server precedence возникает redirect loop.
2. В localized group есть второй route `robots.txt`; на default locale он перезаписывается global route. Должен остаться один deterministic owner.
3. Sitemap index ставит `lastmod=now()` при каждом запросе, создавая ложный сигнал изменения.
4. Index формирует `https://viarcanvas.com/{locale}/sitemap/...` для всех 7 locales, включая скрытый default `ru` и `pl`, хотя польский catch-all/domain policy противоречива.
5. `SitemapController::sitemap()` выполняет три catalogue query, результаты которых не передаются в index view.
6. Product/image sitemap загружает большие коллекции, translations/taxonomies без cache/chunking; production memory/query plan не измерен.
7. Image sitemap использует тот же controller, но template меняет output по route name. Имя route становится частью data contract.
8. Canonical host жёстко зафиксирован в templates/robots, а environment/proxy/HTTPS policy не централизована.

Target не обязан менять URL. Сначала создаётся parity service/cache, затем old/new XML сравниваются на свежем snapshot. Stable `lastmod` берётся из реального max content timestamp.

## 6. Product feeds

Локальные источники:

| Source | Rows |
|---|---:|
| `gallery_items` | 1 038 |
| active | 1 037 |
| с `custom_size_prices` | 1 019 |
| с `custom_size_prices_sale` | 43 |
| active без images | 24 |
| active без base name | 4 |
| `country_tels` | 14 |

Feeds разворачивают item × size в 7 125 offers и вычисляют country multiplier в request.

Критический pricing defect: `item.blade.php`, `item_kurpirkt.blade.php` и `item_salidzini.blade.php` задают `$custom_sizes_saved`/`$price_saved`, но не сбрасывают их в начале каждого item/size. Значение предыдущего товара/размера может попасть в следующий offer. Golden tests должны сравнить base price, old/sale price и multiplier на товарах:

- с sale и без sale рядом;
- с разным числом размеров;
- с отсутствующим sale index;
- для RU/default, LV, LT, EE и public locale URLs.

`kurpirkt.xml` и `salidzini.xml` имеют одно route name `kurpirkt`. Output зависит от `route()`/current route name в нескольких SEO templates, поэтому names нужно сделать уникальными до cache/cutover.

Target: одна typed feed projection, deterministic per-offer variables, chunk/cursor read, cache/ETag, snapshot timestamp, schema validation и merchant golden samples. Нельзя менять цену/округление одновременно с framework migration.

## 7. Public DB-write utilities

### 7.1. `/translate_item`

Anonymous GET запускает external Google Translate calls и сохраняет пустующие names/descriptions для `en|lv|pl|ee|de|lt`. SSL peer/host verification отключена.

SQL condition не сгруппирован: `active=1 AND reproduction OR module OR photo`. Поэтому active constraint фактически относится только к первой ветке; inactive module/photo также могут попасть в обработку.

Это особенно опасно при работающем production, где каталог/переводы продолжают изменяться. Endpoint нельзя переносить как public route.

### 7.2. `/set_meta`

Anonymous GET проходит по всем active gallery items с `id_type` и сохраняет `meta_title`/`meta_desc` для семи public locales, включая `ru`. Нет auth, dry-run, transaction/batch receipt, version или rollback journal.

Target обоих utilities:

1. authenticated command/Filament action с explicit ability;
2. mandatory dry-run diff и affected-row count;
3. snapshot/backup + run ID + actor + locale scope;
4. batches, retry/idempotency и per-record error report;
5. translation/SEO delta reconciliation с legacy writer;
6. provider TLS verification и approved API contract.

## 8. Reviews

`GET /review` использует `pages`, `newhome_services`, `page_faq` и configured Google aggregate rating. `POST /review` объявлен с обычным Request, хотя `CreateReviewRequest` импортирован, но не применяется.

Подтверждённые defects:

- нет effective validation/rate limit/CAPTCHA в controller path;
- base64 audio декодируется без strict flag, MIME/duration/size limit и пишется напрямую;
- multipart file MIME/size/quantity не ограничены;
- file keys ожидаются как `1` и `2`, типичный first key `0` игнорируется;
- `$audioDB` не инициализируется, если audio отсутствует;
- anonymous email lookup может связать review с существующим user и его последним completed order без ownership proof;
- response — plain `success`, error/moderation/idempotency contract отсутствует.

Локальный snapshot: 4 reviews, active=0, все 4 связаны с user, ни одна с order; locales `ru=3`, `ee=1`; audio reference есть у 4, image у 4, avatar у 3. Это не production volume.

Target: verified order/review token или authenticated ownership, FormRequest, upload quarantine, central MIME/size policy, rate/CAPTCHA, malware scan decision, moderation/audit, idempotency и safe storage. Все public locales и existing files/references сохраняются; автоматическое удаление запрещено.

Google structured data сейчас может брать static fallback rating, а API — live Places data. Нужен один утверждённый source-of-truth и тест против расхождения schema/UI.

## 9. Mail preview, gift card и test receivers

### 9.1. Public mail preview

Доступны `/mail/test`, gift/cart/order preview и `/mail/1..7`. В `MailController` есть hardcoded email/order IDs; abandoned-cart preview генерирует recovery token, а 24-hour path способен создавать coupon. Это production side effects и PII coupling под видом preview.

Решение: удалить routes из production. Preview возможен только в local/staging, с synthetic fixture, auth и гарантией no DB/provider writes.

### 9.2. Gift card

Route `ANY /giftcard/pdf/{price}/{locale}` передаёт два параметра, но `generateGiftCardPDF($user_id, $order_id, $coupon_id, ...)` требует три других обязательных ID. Метод пишет predictable public path `order_user_coupon.pdf`, включает Dompdf remote resources и не проверяет authorization.

Target: internal application service или authenticated POST с typed input, ownership, collision-safe immutable artifact, private/public classification, audit и exact signature. GET/ANY generation запрещается.

### 9.3. Synvolve test receiver

При default `SYNVOLVE_TEST_RECEIVER_ENABLED=false` routes отвечают 404. Если flag включён, три public `/api/test/synvolve/*` endpoint не требуют API key: сохраняют full payload, дописывают отдельный log и `latest` возвращает последний order/message payload.

Production gate: flag off + deployment assertion. Если receiver нужен в staging — API key/IP allowlist, max body, field redaction, retention, access audit и отдельное storage namespace.

### 9.4. Google rating/reviews

Public API имеет provider timeout и positive cache. Но failures не negative-cache: каждый public request может снова обращаться к Google Places, создавая amplification/cost/outage coupling. При `APP_DEBUG` наружу возвращаются provider status/error/hint.

Нужны rate limit, stale-if-error/negative cache/backoff, fixed response schema, monitoring quota и запрет debug details в production.

## 10. Route registry/cache gate

Laravel 6 `Route::prepareForSerialization()` прямо выбрасывает `LogicException` для Closure. В текущем route snapshot 2 003 closure routes. Поэтому `route:cache` не является «опциональной оптимизацией, которую можно просто включить после upgrade» — сначала архитектурно убираются closures.

Дополнительные blockers:

- 5 duplicate route-name groups: `edit_admin_order`, `hb.gallery.ram_search`, `kurpirkt`, `send_photo_form`, `submit_bonuses`;
- 11 missing handler rows в snapshot;
- `App\Http\Controllers\ImageController@show` отсутствует и блокирует `artisan route:list`;
- часть missing Voyager order actions относится к incomplete custom resource contract FN-12;
- unprotected `admin/sitemap` — no-op closure вне `admin.user`.

Gate считается закрытым, когда:

1. `route:list` и fresh route boot проходят на PHP 7.4 baseline и target PHP;
2. route names уникальны либо duplicate намеренно versioned/tested;
3. missing handlers = 0 или routes удалены после traffic/owner decision;
4. closure routes = 0 для cacheable target;
5. `route:cache`/`route:clear` проходят на release artifact;
6. cached/uncached route parity проверена по locale, redirect, admin, API и provider callback matrix.

## 11. Риски FN-13

| ID | Риск | Уровень | Обязательная мера |
|---|---|---|---|
| R-108 | `header()+exit` во время route boot | critical | exact controller redirects, console/HTTP boot tests |
| R-109 | duplicate/conflicting/chained redirect rules | critical | versioned unique map, one-hop/cycle validation |
| R-110 | `/pl/*` теряет path/query и shadow generated routes | high | signed cross-domain mapping и golden URLs |
| R-111 | image debug раскрывает paths и делает outbound probe | critical | удалить public debug, SSRF/allowlist policy |
| R-112 | 2 003 closures блокируют route cache | high | controller conversion и cache parity gate |
| R-113 | missing `ImageController` блокирует registry | critical | restore/remove после reference+traffic audit |
| R-114 | anonymous `/translate_item` и `/set_meta` массово пишут DB | critical | немедленно закрыть; command/dry-run/audit/rollback |
| R-115 | public mail previews меняют recovery/coupon state и используют private references | critical | удалить с production, synthetic preview only |
| R-116 | review upload без validation/ownership | critical | verified token, upload policy, moderation/rate |
| R-117 | gift-card route signature/authorization/storage несовместимы | critical | typed internal POST/service и artifact policy |
| R-118 | robots self-loop и locale/sitemap domain drift | high | один environment-aware robots/canonical contract |
| R-119 | feed price state leak и большие uncached responses | critical | deterministic projection, golden prices, cache/stream |
| R-120 | Synvolve test payload capture/disclosure при ошибочном flag | critical | deploy assertion, auth/redaction/retention |
| R-121 | Google Places failure amplification/debug leakage | medium | rate/backoff/negative cache/no prod debug |
| R-122 | duplicate route names/precedence меняют `route()` output | high | unique names и cached/uncached route tests |
| R-123 | production продолжает менять URLs/catalogue/translations во время проекта | critical | watermarks, delta, one writer, cutover freeze/reconcile |
| R-124 | нет production access/crawler/merchant/runtime evidence | high | anonymized logs, Search Console/feed owner/UAT sign-off |

## 12. План работ

### P0 — до начала framework migration

1. Закрыть production access к `/translate_item`, `/set_meta`, mail previews, image debug и gift-card generation.
2. Зафиксировать `SYNVOLVE_TEST_RECEIVER_ENABLED=false` deployment assertion.
3. Ввести review validation/size/rate/ownership containment.
4. Исправить/удалить missing `ImageController` только после reference/access-log evidence.
5. Не запускать public asset rebuild и не менять orders/CRM contracts.

### P1 — evidence и signed contracts

1. Получить anonymized 30–90 day access logs: redirect sources, 404, sitemap/feed/robots, mail/test/debug utilities.
2. Выгрузить Search Console sitemap status и merchant feed diagnostics без credentials/PII.
3. Утвердить PL domain mapping, public locales и `ee|et|uk` boundary.
4. Подписать redirect dataset и 47-row auxiliary route disposition: preserve/restrict/replace/remove.
5. Зафиксировать production DB/file watermarks и latest translation/catalogue updates.

### P2 — characterization

1. Redirect golden set: duplicates, non-ASCII, spaces, encoded URLs, query, locale, cross-domain, 404.
2. XML golden set: exact endpoint/status/content type, schema, counts, canonical, locale, lastmod, images.
3. Feed golden set: representative item/size/sale/multiplier and merchant-required fields.
4. Review negative/security/upload/moderation tests.
5. Cached/uncached route registry parity and missing-handler checks.

### P3 — target implementation

1. RedirectMap validator + controller/middleware responder без closures.
2. Cached/chunked SitemapService/FeedService с unchanged URL/output contract.
3. Authenticated maintenance commands с dry-run/run ledger.
4. ReviewSubmission service и central upload policy.
5. Non-production preview tools на synthetic fixtures.
6. Google/Synvolve resilience/security adapters.

### P4 — shadow и cutover

1. Target генерирует XML/redirect decisions в shadow на fresh snapshot.
2. Differential compares route/status/target/body hashes/counts/prices без public ownership.
3. Перед cutover снимаются final logs и DB/translation/catalogue delta.
4. Переключаются SEO/feeds отдельной волной; orders/catalogue/admin writers не переключаются вместе.
5. Мониторятся 404/5xx/redirect hops, crawl/index, feed rejects, latency/memory и cache hit rate.

### P5 — rollback

Rollback возвращает route/SEO/feed ownership на legacy artifact/config. DB dump не откатывается. Новые review/translation/meta writes до owner switch запрещены; после switch они имеют additive journal/outbox и reconciled watermarks. Redirect dataset version и cached XML artifacts сохраняются для мгновенного возврата.

## 13. Definition of Ready / Done

FN-13 готов к реализации, когда есть:

- owner решения по PL, sitemap/feeds, review, preview/test routes;
- production traffic/crawler/merchant evidence;
- signed redirect/route/feed contracts;
- security containment P0;
- fresh data/file/translation watermarks;
- golden fixtures без PII и provider secrets.

FN-13 завершён только если:

- public mutating/debug/test routes закрыты или формально защищены;
- all redirect sources имеют deterministic one-hop result;
- seven-locale/public-domain SEO parity подтверждена;
- XML/feed schema, counts и golden prices совпадают;
- route registry/cache проходят без Closure/missing handler;
- production canary observability и rollback rehearsal подписаны SEO/content/operations owners.

## 14. Статус общего аудита

Этим документом закрыт запланированный **L2 static/data pass FN-13**. Это завершает основные функциональные аудитные пакеты FN-01…FN-13, но не означает готовность начать необратимую миграцию или дать fixed-price без условий.

Открытым остаётся общий L3 контур:

- production topology/PHP-FPM/extensions/cron/workers/cache/storage/backup/PITR;
- anonymized production access/error/slow logs и provider/merchant evidence;
- production DB triggers/events/volumes/watermarks;
- staging browser/device/seven-locale/UAT;
- owners/sign-off по orders, finance, CRM, SEO/content и 8-role ACL;
- cutover/rollback rehearsals при продолжающихся production orders и translation/catalogue changes.

То есть аудит кода и локального snapshot выполнен достаточно детально для составления work packages; production readiness audit ещё должен быть выполнен перед переключением.

## 15. P0 auxiliary containment — рекомендуемый план 14.07.2026

Public translation/meta maintenance, mail previews, image debug, gift-card preview/HTTP generation и Synvolve test receiver требуется закрыть default-off gates с production double opt-in; `admin/sitemap` — `admin.user`. Gate должен выполняться до localization redirect и не менять seven-locale contract. Временный прототип использовался только для проверки реализуемости и удалён; текущий application code не содержит этих мер.

До закрытия нужны реализация, повторные tests, deployment ENV/cache probes и zero-side-effect evidence. Review upload, missing `ImageController`, Closure/cache conversion, robots/feed/redirect defects и production SEO evidence открыты. R-125: общий legacy 404 view может вернуть 500 из-за undefined `$trackers_body`.

## 16. P0 review security — рекомендуемый план 14.07.2026

Для current и legacy review POST нужны auth+rate limits и server-side validation. Current association должна определять user/order/pid только от authenticated user, сохраняя `file[1]/file[2]`; обязательны MIME/size/WebM/locale limits, cleanup failpoint и escaped review text в 6 templates. Эти меры не внедрены. R-126 stored XSS и R-127 dual pipeline drift открыты.

Quarantine/AV/metadata/dimensions, duplicate/retention policy, translated AJAX errors, решение `reviews|our_works` и production evidence также остаются открытыми.
