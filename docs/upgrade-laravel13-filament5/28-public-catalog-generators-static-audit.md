# FN-11: публичный каталог, карточки и конструкторы

Дата аудита: 14.07.2026. Уровень: static/data L2. Runtime production L3 и визуальная проверка страниц не выполнялись.

## 1. Граница и безопасность аудита

Проверены локализованные public routes каталога, старые и новые gallery pages, карточка товара, фильтры, рамки, canvas, collage, modular, family, graphic/oil portrait, caricature/Simpsons, sizes/prices, связанный SA catalog API, модели, Blade-шаблоны, translation sources, SEO/URL contracts и текущая Mix asset topology.

Локальная БД использовалась только для exact aggregates, schema/index metadata и orphan checks. Тексты переводов, изображения, отзывы и пользовательские данные не читались. GET-маршруты приложения не вызывались, потому что часть из них меняет БД/сессию. Frontend assets не пересобирались, файлы каталога и БД не изменялись.

Рабочий runtime baseline: `C:\OSPanel\modules\php\PHP_7.4\php.exe`.

Проверки: PHP 7.4 syntax lint 16 релевантных route/controller/model files — 0 ошибок. `SaServicesCatalogTest` + `SaServiceSizesTest` — `OK (21 tests, 108 assertions)`. Специализированных browser/HTTP tests public gallery и generator flows не найдено.

## 2. Главный вывод

Публичная витрина не является одним модулем. Это набор параллельных old/new routes, 1 038 gallery items, десятков singleton/content tables, Voyager translations, PHP lang files, country multipliers, session state и вручную собранных JS/CSS bundles. Перенос только controllers или только Voyager BREAD потеряет цены, URL, переводы, SEO, генераторное состояние и совместимость корзины.

До framework migration обязательны два отдельных шага:

1. security stabilization: закрыть public write-utility GET routes и исправить подтверждённый 404 нового item route;
2. contract capture: для каждого public path снять golden response/DOM/price/basket/locale/SEO fixture и привязать его к DB sources и frozen asset hashes.

Production продолжает принимать заказы и изменения каталога/переводов. Legacy остаётся единственным write-owner до delta reconciliation и покомпонентного cutover.

## 3. Подтверждённый inventory

| Область | Подтверждено |
|---|---:|
| Public routes в основном локализованном блоке | 211 route declarations |
| FN-11 route/function contracts | 72 строки в приложении |
| Gallery items | 1 038, из них 1 037 active |
| Gallery types | 7: IDs 2–8 |
| Gallery categories | 41 |
| Gallery sizes | 80 |
| Gallery item/category links | 1 249 |
| Gallery item/size links | 10 662 |
| Voyager translations | 64 779 |
| Активные public locales | `lv|lt|pl|ru|de|en|ee` |
| Дополнительные translation locale rows | `et` — 6 строк; это не активный URL locale |
| Mix manifest outputs | 12 versioned bundles |
| Размер frozen outputs | около 2.95 MB суммарно |
| Специализированные public catalogue UI tests | не найдены |
| Связанные SA catalogue tests | 2 feature test files |

Детальная матрица: `appendix-public-catalog-generator-contracts.csv`.

## 4. Карта публичных сценариев

### 4.1. Галерея

- `/new/gallery`, `/new/gallery/{type}`, `/new/gallery/{type}/{category}` — новая витрина;
- `/gallery` редиректит на новую главную, но old type/category/item handlers остаются зарегистрированы;
- `/gallery/{type}/item/{item}` — новая карточка по numeric item ID;
- `/gallery/{type}/{category}/item/{item}` — старая карточка;
- painter/style/genre/age/nationality routes используют отдельные выборки и translation search;
- `/get/ram_search` зарегистрирован одновременно GET и POST с одинаковым route name;
- `sitemap_products.xml`, sitemap pages и advertising feeds зависят от тех же IDs/slugs/images/prices.

### 4.2. Конструкторы и продуктовые страницы

- `/new/canvas`, `/kanvas/apdruka`;
- `/collage`, `/collage-js`, `/collage-constructor`;
- `/modular-generator`;
- `/family-constructor`;
- `/graphic-portrait`, `/new/graphic-portrait/{slug}`, oil/royal/stylization variants;
- `/new/caricature`, `/new/caricature/{pageslug}`, `/simpsons`;
- `/sizesprices` объединяет `newhome_services`, gallery items type 5, canvas и collage sources;
- добавление в корзину выполняется отдельными basket endpoints и должно сверяться с FN-02.

### 4.3. CRM↔SA каталог

`/api/sa/services-catalog`, `/api/sa/catalog-full`, service sizes и price-by-size строят отдельный API projection из реальных site sources. API добавляет обязательные `ru|uk|en` к public locale config, использует country multiplier и собственный service ID/path mapping. Он не является автоматически эквивалентным DOM каталога и требует parity fixtures.

## 5. Подтверждённые дефекты текущего кода

### 5.1. Public GET routes меняют БД

`GET /set_sizes`, `/set_genre`, `/set_style` находятся внутри публичного localized route group без auth/permission/CSRF write contract.

- `hb_set_sizes()` создаёт/обновляет `gallery_sizes` и добавляет pivot rows;
- `hb_set_genre()` и `hb_set_style()` добавляют classification links;
- методы обходят весь каталог;
- genre/style branches используют `dd()` на неизвестном значении;
- операции не имеют transaction, idempotency key, audit actor, dry-run и bounded batch.

Дополнительно `/set_sizes` не передаёт обязательный `$type` в `hb_set_sizes(Request $request, $type, ...)`, поэтому текущая route signature несовместима с method signature. Эти routes нельзя переносить как public behavior. Их следует убрать из public routing, а нужную нормализацию оформить authenticated CLI/admin command с dry-run и receipt.

### 5.2. Новая карточка товара статически ведёт к 404

`hb_item_render()` сначала подтверждает item и type, затем устанавливает `$category=false`, вызывает `GalleryCategory::getBy(false)` и abort 404 при `null`. В локальной БД у всех 41 categories непустой URL; category с URL `false/0` нет. Следовательно, `/gallery/{type}/item/{item}` не доходит до render для существующего item.

Это не следует «чинить по догадке» внутри framework migration. Нужен отдельный hotfix с определённым contract: категория карточки нужна или нет, как выбирается primary category при нескольких links, какой canonical URL и что происходит с query parameters.

### 5.3. Route registry не собирается полностью

`php artisan route:list` на PHP 7.4 блокируется `Target class [App\Http\Controllers\ImageController] does not exist`; route зарегистрирован как `/image/{filename}`. Поэтому полный runtime route inventory пока нельзя считать подтверждённым. Static route snapshot остаётся evidence, а missing controller должен быть закрыт до framework upgrade.

### 5.4. Type/category lookup допускает некорректные состояния

- `GalleryType::getType()` возвращает `null`, но `hb_type_render()` обращается к `$gallery->id` до явной проверки;
- `GalleryCategory::getBy($category)` ищет только по `url`, не ограничивая `id_type`; при будущих одинаковых slug у разных типов возможна неверная категория;
- новая item route принимает numeric item ID, а old routes смешивают type/category/item parameters;
- item type canonicalization выполняется 301, но дальнейший category lookup сейчас ломает flow.

## 6. Данные и целостность локального snapshot

### 6.1. Основные counts

- `gallery_items`: 1 038; active 1 037; inactive 1; blank slug 0; duplicate base slug groups 0;
- items без `id_type`: 3 active rows;
- item distribution: type 2 — 72, type 3 — 737, type 4 — 189, type 5 — 19, type 6 — 9, type 7 — 1, type 8 — 8;
- `gallery_categories`: 41; blank URL 0; duplicate `(id_type,url)` groups 0;
- `gallery_sizes`: 80; 18 items имеют пустой `custom_size_prices`;
- content tables: canvas slider 16, collage slider 6, collage colors 20, collage stickers 13, modular heads 1, family constructor 1, Sharj examples 16/8.

### 6.2. Orphan links

| Pivot | Подтверждённые orphan rows |
|---|---:|
| `gallery_category_gallery_item` → item | 28 |
| `gallery_items_ gallery_tag_sizes` → item | 37 |
| `gallery_items_ gallery_tag_colors` → item | 31 |
| `gallery_items_gallery_tag_rooms` → item | 66 |

Parent category/size/color/tag orphans в этих проверках равны 0. Автоматически удалять orphan history нельзя: сначала выгружается ID-only quarantine report, определяется происхождение, влияние на production pages и retention owner.

### 6.3. Индексы

У `gallery_items` есть рабочие indexes для type/active, sale и slug. У проверенных pivot tables в основном только surrogate primary `id`; индексов на `gallery_item_id` и filter foreign keys не подтверждено. Текущие `whereIn`, `whereHas`, category lookup и filter joins способны деградировать на production объёме.

Target migrations добавляют индексы после production query plan/duplicate audit. Unique constraints нельзя вводить до классификации существующих duplicate semantics.

## 7. Переводы и языки — обязательный инвариант

Public URL locales: `lv|lt|pl|ru|de|en|ee`. В `resources/lang` также существуют `et`, `cn`, `jp`; DB translation locales: `de|ee|en|et|lt|lv|pl|ru`. `et` закомментирован в route localization, а `ee` используется как application code для Estonian с regional `et_EE`.

Это означает:

1. нельзя переименовать `ee→et` простой миграцией;
2. нужно сохранить и PHP lang files, и Voyager `translations`, и base model columns;
3. fallback `requested locale → ru` и местами `→lv` является частью текущего поведения;
4. translated slug/SEO/title/HTML fields переносятся как данные, не генерируются заново;
5. SA `uk` — дополнительный API locale; в локальном Voyager translation snapshot `uk` отсутствует, поэтому текущий fallback должен быть явно зафиксирован;
6. translation edits на production продолжаются — нужен `updated_at/ID` delta ledger либо короткое controlled write freeze на финальной сверке.

Exact translation baseline: 64 779 rows: `de 9 392`, `ee 10 408`, `en 2 062`, `et 6`, `lt 10 433`, `lv 10 408`, `pl 9 409`, `ru 12 661`.

## 8. Цена и продуктовый contract

Цена собирается не из одного столбца:

- `gallery_items.custom_size_prices`, sale variants и `sale_end`;
- gallery sizes/holsts/boxes/decorations/executions;
- `canvas_header`, `a_collage_head`, portrait/caricature person-price fields;
- production time standard/express price;
- `country_tels.price_country_mltpr`, определяемый по IP/phone/country;
- basket-side normalization и recommendation discount session state;
- SA path/service mapping и price-by-size projection.

`hb_item_render()` принимает `recommendation_discount` из query, затем заменяет его фиксированным 30 и пишет session key, если в basket нет recommendation item. Это state change на GET и часть cross-page basket contract.

`SimpsonsController` и `SharjController` содержат собственные `addToBasket()` implementations, которые принимают цену/terms и base64/file payload из request без найденной method-level validation; routes на эти методы сейчас не найдены. Это dead/reachable-unknown code: его нельзя случайно зарегистрировать при переносе. Единственный target pricing authority должен пересчитывать цену server-side по immutable option IDs/version.

Parity matrix обязана сравнивать display price, cart line, order total и SA API для одинаковых `service/item/size/options/country/time/locale`.

## 9. Сессия и недетерминированность

- карточка пишет списки recently viewed в session на GET;
- recommendation query пишет discount session state;
- `inRandomOrder()` используется для bestseller/popular/similar/collage order block;
- random content делает HTML snapshot и retry недетерминированными;
- pagination — 15 items, query parameters appends; filter contract включает tag/color/size/order/search/shape;
- size parser ожидает специальный формат с `_over`/другим suffix и не имеет FormRequest validation.

Для golden tests random selection фиксируется seed/fixture или исключается из semantic hash. Session keys и TTL/cookie attributes должны сохраниться до отдельно согласованного изменения.

## 10. Frontend assets: только перенос, без пересборки

Текущий `webpack.mix.js` не компилирует application source. Он конкатенирует уже написанные public CSS/JS через `mix.styles()` и `mix.scripts()` и versionирует outputs. Пользовательское ограничение подтверждено: эти assets нельзя перегенерировать в рамках backend migration.

Frozen outputs и SHA-256 baseline:

| Output | Bytes | SHA-256 |
|---|---:|---|
| `css/home_combine.css` | 77 312 | `E8A225576284EC72B54878E677E4D834C53CFD43E6E4D97BAC42575C060E2042` |
| `css/combine.css` | 137 224 | `EBE84DCCC6DEC9CBF58A55BA46D419F11DF55F95C75D0C1A1BE9C9F8F4625999` |
| `js/combine.js` | 149 108 | `A739C7CEC35697E90093FF5A131BC20D217AA7E2BB44A296A721F08602010267` |
| `css/combine_canvas.css` | 232 318 | `0888CCCAACAB330AAB48461377AD1B773B55F035368ED1D0C0971F68E21635D5` |
| `js/combine_canvas.js` | 186 703 | `4CDDB8B514D005084819BD6DAD53728B6FE4F378F853664464070D6F2506D8C1` |
| `js/collage_combine.js` | 581 101 | `DF6D7EE926409909A16B852544B83B8F41CD56BD81764A0909770599946D1FF9` |
| `css/collage_combine.css` | 101 532 | `9929EE843733605654AEF74F2707D96333A4368EB7B850A46AE9F3FF8901B7D2` |
| `js/family_combine.js` | 647 711 | `03A1DB2D1F6162E938B7649F529A8428340967993C0E256A5A51A7A805E88A83` |
| `js/gallery_combine.js` | 79 989 | `7CAD360A28E5C0BB4DB56A6716C376578E72D18251CB8BFF92AFBABC23C96328` |
| `js/graph_combine.js` | 191 630 | `917FE4931C28F699F85FEBC925216D0EE61F9365A37F092A5FAA9AD7843992BB` |
| `js/graph_child_combine.js` | 177 455 | `3864890BC2B20F40DCABB49E679575CC2BD2AD68C5009D294E04BE5D1D119A01` |
| `js/modular_combine.js` | 483 647 | `69728B3C5A409E0072F5AF0F8BAF919742F43B59BA0E74BAA52EBA5D666C151F` |

`PhotoEditor.js` дважды включён подряд в collage bundle. Это выглядит как дефект, но удалять duplicate при migration нельзя без browser parity: повторное выполнение может иметь side effects, на которые случайно опирается страница.

Target deployment копирует byte-identical files и manifest. Vite не вводится. Любая будущая frontend modernization — отдельный проект после visual/functional baseline.

## 11. Внешние зависимости и hardcoded production host

Blade содержит hardcoded `https://viarcanvas.com` для картинок карточек и декоративных assets. Modular generator загружает CSS с jsDelivr/cdnjs; collage JS динамически подключает Google Fonts. Это создаёт environment coupling, CSP/privacy/availability и offline staging risks.

На первом этапе URL сохраняются для parity, но инвентаризируются. Затем внешние assets либо pin/self-host, либо утверждаются CSP/SRI/timeout/fallback. Нельзя массово менять URLs одновременно с framework cutover.

## 12. HTML/JS injection boundary

В каталожных Blade широко используются raw `{!! ... !!}` для PHP translations, Voyager fields, slider text, hints, reviews и values внутри HTML attributes/inline JS. Часть полей намеренно содержит HTML, поэтому глобальная замена на escaped output сломает layout; оставлять всё raw также нельзя.

Нужен field-level registry:

- plain text → escaped;
- trusted curated rich text → allowlist sanitizer at write/import boundary;
- attribute/JSON/JS context → `@json`/context encoder;
- review/user content → never trusted HTML;
- migration copy → byte-preserving, затем sanitize through versioned cleanup with report.

Особое внимание: collage price strings вставляются в single-quoted inline JS, а translated names используются в `data-*` attributes.

## 13. SEO и URL continuity

Сохраняются:

- все public paths и locale prefixes;
- base и translated slugs;
- canonical, breadcrumbs, Product/WebPage schema;
- sitemap/feed item IDs и image URLs;
- 301 type canonicalization и old→new redirects;
- query semantics pagination/filter/recommendation;
- HTTP status для unknown type/category/item.

Old/new handlers нельзя удалять по наличию redirect в коде: production access logs должны показать реальные paths, locale distribution, bots/feeds и external backlinks. Для каждого изменённого URL нужен explicit redirect map без chain/loop и с сохранением locale.

## 14. Performance и cache contract

Подтверждены N+1 patterns: отдельные запросы размеров/холстов/рам для каждого ID, translation search через `LIKE`, много `inRandomOrder()`, повторные sale/category/content queries и большие неиндексированные pivots. `hb_type_render()` собирает несколько глобальных наборов даже для одной category page.

До оптимизации снимаются production-safe p50/p95/p99, query count/slow plan, rows examined, memory и cache hit. Оптимизация проводится query-by-query с semantic response hash; нельзя одновременно менять sort/random/pagination/fallback.

## 15. Cutover при работающем production

Пока идёт разработка, production создаёт orders и меняет catalog/translations. План:

1. T0 snapshot: exact counts, max IDs, per-table hashes, translations grouped by table/locale, asset hashes;
2. initial copy без изменения legacy writer;
3. repeatable delta по `updated_at` плюс delete/tombstone strategy; таблицы без надёжного watermark получают trigger/binlog CDC либо controlled freeze;
4. shadow read/API/HTML comparison по representative matrix;
5. перед cutover короткий write gate только для соответствующего component, не глобальная остановка заказов;
6. final delta, count/hash/orphan/URL/price/translation reconciliation;
7. переключение reads, legacy remains rollback target; new writes включаются только после component gate;
8. post-cutover observation и reverse/forward reconciliation policy.

Translation admin и catalogue admin нельзя одновременно оставить двумя независимыми write owners.

## 16. Обязательная golden matrix

Минимум:

- 7 public locales × gallery main/type/category/item;
- active/inactive/missing type/category/item;
- each type 2–8 and item with/without custom prices/sale/multiple categories;
- filters color/tag/size/shape/search/order/page and invalid formats;
- canvas/collage/modular/family/portrait/caricature/Simpsons generator start→options→upload→preview→cart;
- country multiplier 1/default/FI→FIN/unknown;
- standard/express, frame/holst/box/decoration and recommendation;
- basket/order parity and SA catalog/size/price parity;
- canonical/hreflang/schema/sitemap/feed/image status;
- anonymous/session/new session/repeat GET/retry/multi-tab;
- XSS payloads in translated rich/plain/attribute/JS contexts;
- frozen asset 200/content-type/hash/load order and external dependency failure.

## 17. Production evidence, которого ещё нет

- path/query/status/locale traffic top and 404/500 distribution without PII;
- actual type/category/item cardinality and orphan report from production;
- translation rows/counts/max updated time per table/locale, including `ee/et/uk` policy;
- price source/version and country multiplier usage distribution;
- browser/device matrix and real generator completion/abandon/error rates;
- asset CDN/cache headers, missing files and hardcoded host behavior;
- slow query plans and response latency;
- catalogue/admin editors, write frequency and required freeze SLA;
- sitemap/feed consumers and SEO baseline.

## 18. Реестр рисков FN-11

| ID | Риск | Severity | Gate/меры |
|---|---|---|---|
| R-78 | Public GET utility меняет catalogue DB без auth/transaction | critical | немедленно закрыть/CLI dry-run; negative HTTP tests |
| R-79 | Новая item route всегда abort 404; type/category scope неоднозначен | critical | отдельный hotfix, primary-category/canonical contract, route tests |
| R-80 | Полный route registry блокируется missing `ImageController`; duplicate route name для ram search | high | восстановить route boot, unique names/method contract, snapshot diff |
| R-81 | Потеря/слияние языков и `ee/et/uk` semantics | critical | preserve all stores/codes, locale matrix, count/hash/delta reconciliation |
| R-82 | Public display/cart/order/SA price drift | critical | единый pricing authority, versioned inputs, cross-channel parity tests |
| R-83 | Невалидированный generator upload/base64/client price code случайно станет reachable | high | route reachability registry, server pricing, upload limits, negative tests |
| R-84 | Orphan pivots и отсутствие рабочих indexes дают потерю данных/slow filters | high | quarantine report, owner decision, explain plans, additive indexes |
| R-85 | N+1/random/unbounded content queries меняют latency и nondeterministic output | high | production profiling, semantic hashes, deterministic test fixtures |
| R-86 | Пересборка/смена порядка вручную написанных assets ломает UI | critical | byte-identical copy, manifest+SHA gate, no Vite/no rebuild |
| R-87 | Hardcoded production host/CDN/fonts создают environment/CSP outage | medium | inventory, pin/SRI/self-host decision, failure fixtures |
| R-88 | Raw translated/Voyager/user HTML и inline JS допускают XSS/context break | critical | field/context registry, sanitizer, encoding and payload tests |
| R-89 | GET карточки меняет session viewed/discount state | high | зафиксировать compatibility, убрать price authority из query, repeat tests |
| R-90 | Old/new URL, slug, canonical, sitemap/feed regression теряет SEO/трафик | high | access logs, redirect map, SEO golden diff, no chains |
| R-91 | Production edits каталога/переводов расходятся во время миграции | critical | one writer, CDC/delta/tombstones, final freeze/reconciliation/rollback |

## 19. Definition of Done FN-11 migration package

FN-11 готов к implementation/cutover только когда:

1. R-78/R-79/R-80 закрыты отдельными проверяемыми hotfixes;
2. route/function/data/asset/locale/price contract matrix утверждена;
3. production aggregates, traffic и performance evidence получены;
4. все 7 public locales и дополнительные translation data сохранены count/hash tests;
5. public/cart/order/SA price parity проходит;
6. asset files byte-identical, manifest/hash/load order проходят browser smoke;
7. orphan rows не потеряны и имеют quarantine/owner decision;
8. old/new URL, SEO, sitemap/feed diff проходит;
9. generator uploads/state/retry/XSS/limits покрыты negative tests;
10. delta sync/final reconciliation/rollback rehearsal выполнены.

## 20. Следующее действие

1. Отдельным security hotfix закрыть `/set_sizes|set_genre|set_style`, не выполняя их на production.
2. Исправить item route только после утверждения primary category/canonical behavior и добавить route tests.
3. Получить production-safe traffic/catalog/translation/performance metadata.
4. Автоматизировать golden capture без пересборки frontend assets.
5. Следующий аудитный пакет: FN-12 — Voyager BREAD и custom admin CRUD, включая catalogue/translation write ownership.
