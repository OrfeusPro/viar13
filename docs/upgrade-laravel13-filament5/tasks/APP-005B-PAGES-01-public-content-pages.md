# APP-005B-PAGES-01 — Полнофункциональный перенос публичных контентных страниц

```text
TASK_ID: APP-005B-PAGES-01
STATUS: IN_PROGRESS
PRIORITY: Critical
OWNER: программист
BRANCH: codex/app-005a-frontend-foundation
CREATED_AT: 2026-07-27
UPDATED_AT: 2026-07-28
DEPENDS_ON: APP-005A-ROUTE-01=DONE
PARENT: APP-005
NEXT_ACTION: продолжить APP-005B-BASKET-02: переносить order creation, payment callbacks and order mails after checkout session writers
```

## Scope

32 route из `appendix-public-route-disposition.csv` с task
`APP-005B-PAGES-01`: common pages, blog/content, stocks/read pages,
advertising pages и runtime widget caller. Формы и writers этих страниц
закрываются отдельно в `APP-005B-FORM-03`.

## Definition of Done

- каждый URL и locale alias отвечает с legacy-equivalent status/redirect;
- controller data, Blade/layout, SEO/canonical/hreflang и assets сопоставлены;
- shared menus/popups/auth продолжают работать;
- формы не маскируются заглушками: endpoint либо готов, либо явно gated и
  связан с `APP-005B-FORM-03`;
- desktop/mobile differential smoke и zero DB delta PASS;
- route-level rows получают evidence commit.

## Выполнено

### APP-005B-FOUNDATION-01 — общий контракт публичных страниц

- target commit: `39b5171`;
- базовый Laravel 13 `Controller` предоставляет
  `renderPublic(view, path, pageData)`;
- `PublicFrontendViewData` централизует header/footer menu, список локалей,
  localized URL, canonical и текущую локаль;
- `PublicHomepageController`, `PublicConditionController` и
  `PublicThanksController` передают только данные своих страниц;
- неиспользуемые запросы `NewhomeService` удалены из condition/thanks;
- маршруты, публичные assets, writers и схема БД не менялись;
- targeted: 27 tests / 237 assertions PASS;
- full: 109 tests / 2 219 assertions PASS;
- Pint, Composer validate и `git diff --check` PASS.

### APP-005B-FAQ-01 — FAQ как первый content parity slice

- target commit: `4c0f2b3`;
- зарегистрированы только `/faq`, `/{locale}/faq` и redirect
  совместимости `/ru/faq` → `/faq`;
- сохранены источники `pages`, `page_faq`, `newhome_services`,
  `site_images`, общий legacy layout, services/contact blocks, CSS и
  responsive image fallbacks;
- FAQPage JSON-LD формируется как controller data и выводится общей оболочкой;
- arbitrary slug, неподдерживаемая locale, `change_phones` и
  `arrilot/load-widget` остаются недоступны;
- RU/EN/EE HTTP differential: по 5 FAQ items и одна FAQPage schema на legacy
  и target, content-table counts до/после GET не изменились;
- targeted frontend: 42 tests / 600 assertions PASS;
- full: 113 tests / 2 258 assertions PASS;
- Pint, Composer validate и `git diff --check` PASS.

### APP-005B-ABOUT-01 — страница «О нас»

- target commit: `0617fac`;
- зарегистрированы только `/about`, `/{locale}/about` и canonical redirect
  `/ru/about` → `/about`;
- перенесены исходная translated page, 5 team records, 5 working-process
  records, 8 services, видео, site-image override, services и optional FAQ;
- добавлены Laravel 13 translation models для `our_team` и
  `our_working_process`, Voyager runtime не требуется;
- сохранены исходная DOM-структура и page asset contract: 10 local CSS,
  Swiper CSS, fancybox/swal/gallery/contacts JS;
- RU/EN/EE differential на legacy и target одинаков: 6 team blocks
  (5 сотрудников + logo), 5 process tabs, 16 service-class occurrences и
  одна Organization schema;
- public content: 8 tests / 79 assertions PASS;
- full: 117 tests / 2 298 assertions PASS;
- Pint, Composer validate и `git diff --check` PASS.

### APP-005B-CONTACTS-01 — страница «Контакты»

- target commit: `0d2ec7c`;
- зарегистрированы только `/page/contacts`, `/{locale}/page/contacts` и
  canonical redirect `/ru/page/contacts` → `/page/contacts`;
- перенесены translated page, 5 активных адресов, 14 телефонных стран и
  5 FAQ страницы из исходных legacy-таблиц;
- сохранены полный исходный DOM, вкладки офисов, телефоны/e-mail, 5 карт,
  слайдеры, FAQ и исходный page asset contract;
- ContactPage/Organization JSON-LD строится контроллером из тех же данных,
  которые отображаются на странице;
- собственных writer/AJAX endpoint у страницы нет; shared layout forms
  остаются частью уже перенесённого frontend foundation;
- RU/EN/EE differential совпадает: 5 office-map blocks, 5 FAQ и один
  JSON-LD graph;
- targeted: 12 tests / 120 assertions PASS;
- full: 121 tests / 2 130 assertions PASS;
- Pint, Composer validate и `git diff --check` PASS.
- immutable baseline `/condition` ограничен 32 явными release-файлами;
  изменяемый стандартный `public/storage` не считается статическим asset
  страницы.

### APP-005B-DELIVERY-01 — страница «Доставка»

- target commit: `b602ec0`;
- зарегистрированы только `/page/delivery`, locale routes, canonical redirect
  `/ru/page/delivery` → `/page/delivery` и подтверждённый frontend endpoint
  `POST /get_towns`;
- перенесены translated page, 3 тарифа городов, 10 workshop-pickup строк,
  14 country/rate rows, delivery FAQ, services и site-image overrides;
- Venipak lookup читает активные credentials из исходной таблицы, защищён
  отдельным enable-gate, TLS verification, connect/total timeout и
  secret-free failure logging; EE нормализуется в provider-код ET;
- `POST /get_towns` проверяет двухбуквенную страну по `country_tels`,
  возвращает исходные city/pickup HTML fragments и тарифы, invalid country
  получает JSON 422; endpoint не пишет в БД;
- unsafe legacy GET `change_delivery_phones`, изменявший существующие заказы,
  не зарегистрирован;
- RU/EN/EE differential совпадает: 4 delivery blocks, 6 numbered steps,
  27 delivery-item class occurrences, WebPage и BreadcrumbList schemas;
- 22 статических файла перенесены с совпадающими SHA-256; HTTP asset crawl:
  34 URL, 0 ошибок 404/403;
- targeted: 17 tests / 173 assertions PASS;

### APP-005B-BASKET-01 — общий public basket слой перед product pages

- Стартовал после ручной проверки `/new/gift-card`: владелец явно потребовал
  сверять все товарные actions с production business logic, чтобы запросы не
  уходили в узкие временные endpoints.
- Legacy source of truth:
  `BasketController@send_gift_card` → `BasketRepository::normalizeBasket()` →
  `session()->push('basket', $data)` →
  `saveBasketToAbandonedCartModel($data, true)`.
- Target first pass:
  - добавлен `PublicBasketService` с legacy-compatible `normalizeBasket`;
  - `PublicGiftCardBasketController` больше не пушит session вручную, а
    использует общий сервис;
  - добавлена модель/миграция `abandoned_carts` по legacy-схеме;
  - abandoned cart сохраняется только при `session('email')` или auth user,
    как в production;
  - при отсутствующей таблице side effect безопасно пропускается с log warning,
    чтобы frontend page не падала во время поэтапной миграции.
- Business parity tests:
  - JSON/string session basket нормализуется, новый gift-card item добавляется
    без затирания старых элементов;
  - item fields совпадают с legacy: `pid=5`, `basketType=5`, `price`,
    `count=1`, `terms=0`, `terms_price=0`, `name`, `whom`, `card_type`;
  - localized `/en/basket/send_gift_card` пишет `abandoned_carts.cart_data`
    и `locale=en`, если в session есть email.
- Evidence: browser `/en/new/gift-card` 200, `POST /en/basket/send_gift_card`
  200, popup frame/cart visible, bad resource responses=0;
  `PublicGiftCardTest` 3 / 33 PASS; combined public targeted
  `PublicGiftCardTest + PublicStockActionsTest + PublicStockScreenTest +
  PublicContentPagesTest` 32 / 310 PASS; Pint PASS.
- Next: переносить `/basket/add`, `/basket/add/portrait`, `/basket/add/module`,
  `/basket/add/construct` только с source-level сверкой request/session
  contracts для каждой товарной страницы.

#### APP-005B-BASKET-01 / portrait endpoint

- Legacy source of truth:
  `BasketController@addToBasketPortrait` saves optional generated/uploaded
  image, resolves gallery item first image from `gallery_items.images`, builds
  a portrait item, subtracts `terms_price` from `price`, pushes to normalized
  `session('basket')`, then updates abandoned cart with `$is_push=true`.
- Target implementation:
  - added `PublicBasketController@addPortrait`;
  - added explicit `/basket/add/portrait` and `/{locale}/basket/add/portrait`
    routes with legacy route name `add_item_to_basket_portrait`;
  - `PublicBasketService::addPortrait()` preserves legacy session keys:
    `is_port_product`, `is_gall_with_img`, `pid`, `activeImage`,
    `photo_ex`, `name`, `price`, `is_def_product`, `count`, `pack`,
    `terms`, `forma_id`, `size_name`, `users_count`, `type`, `holst_id`,
    `hud_of`, `decor_id`, `ram_id`, `compl_id`, `userComment`,
    `terms_price`, `basket_type`, optional `fon/obraz/*`, canvas-collage
    marker and price labels;
  - recommendation discount side effect and abandoned-cart push are routed
    through the same common service as gift-card.
- Evidence: `PublicBasketPortraitTest + PublicGiftCardTest` 5 / 71 PASS;
  combined public targeted set 34 / 348 PASS; Pint and `git diff --check`
  PASS.
- Not done yet: generic canvas `/basket/add`, modular `/basket/add/module`
  and constructor `/basket/add/construct`.

#### APP-005B-BASKET-01 / module endpoint

- Legacy source of truth:
  `BasketController@addToBasketModule` decodes the posted base64 preview into
  `public/uploads`, stores optional `photo_ex`, builds a modular-inter item,
  pushes it to normalized basket and saves abandoned cart with `$is_push=true`.
- Target implementation:
  - added explicit `/basket/add/module` and
    `/{locale}/basket/add/module` routes with legacy route name
    `add_item_to_basket_module`;
  - `PublicBasketService::addModule()` preserves legacy session keys:
    `photo_ex`, `is_modular_inter=1`, `name`, `basketType=2`, `size`,
    `executionId`, `canvasId`, `decorationId`, `boxIds`, `userComment`,
    `add_to_price`, `formId`, `_url=modular_inter`, `terms`,
    `terms_price`, `activeImage`, `count=1`, `ram_id`, `wall_size`,
    optional `is_canvas_collage`, special labels and recommendation discount;
  - base64 preview is physically written to `public/uploads` and referenced
    as `/uploads/{random}.png|jpeg`, matching legacy behavior.
- Evidence: `PublicBasketModuleTest + PublicBasketPortraitTest +
  PublicGiftCardTest` 7 / 106 PASS; combined public targeted set
  36 / 383 PASS; Pint and `git diff --check` PASS.
- Not done yet: generic canvas `/basket/add` and constructor
  `/basket/add/construct`.

#### APP-005B-BASKET-01 / generic canvas add and coupon state

- Legacy source of truth:
  `BasketRepository::addToBasket()` with `BasketType::CANVAS_TYPE` calls
  `formCanvasTypeData()`, then applies shared count, `terms_price`
  subtraction, recommendation discount, canvas-collage marker, special labels,
  normalized basket push and abandoned cart. Promo code state is handled by
  `BasketController@coupon_use`.
- Target implementation:
  - added explicit `/basket/add` and localized alias with legacy route name
    `add_item_to_basket`;
  - `PublicBasketService::addCanvas()` preserves legacy handling for
    `userImage`, optional `Image3d`, `orig_images`, `photo_ex`, `boxIds`,
    `priceLabel`, `improve_photo`, `price`, `terms_price`, recommendation
    discount and `is_canvas_collage`;
  - added `/basket/coupon_use` and localized alias with legacy route name
    `coupon_use`;
  - coupon endpoint preserves legacy auth/session/user side effects:
    guest returns `{"finded":"user"}`, auth user sets `coupon_id`,
    `coupon_val`, `coupon_type`, updates `users.active_coupon`, uses `-2`
    for invalid friend coupon and `-1` for missing coupon.
- Evidence: `PublicBasketCanvasTest + PublicBasketCouponTest` 5 / 46 PASS;
  full basket/public targeted set 41 / 429 PASS; Pint and `git diff --check`
  PASS.
- Not done yet: full cart render/total calculation pages. Promo calculation
  in final cart total must continue with legacy `coupon_type` semantics.

#### APP-005B-BASKET-01 / constructor endpoint

- Legacy source of truth:
  `BasketController@addToBasketConstruct` saves `image_offset` or original
  modular/collage file, writes optional SVG, `photo_ex`, `fon` and
  `orig_images`, builds a portrait-compatible construct item, subtracts
  `terms_price`, applies recommendation discount, special labels, normalized
  basket push and abandoned cart.
- Target implementation:
  - added explicit `/basket/add/construct` and localized alias with legacy
    route name `add_item_to_basket_construct`;
  - `PublicBasketService::addConstruct()` preserves legacy session keys:
    `image_uploads`, `activeImage`, `orig_file_name`, `file_hash`,
    `collageSvgImage`, `fon_images`, `photo_ex`, `name`, `price`,
    `count=1`, `pid`, `size_name`, `ram_id`, `userComment`, `terms`,
    `terms_price`, `holst_id`, `formId`, `type`, `hud_of`, `pack`,
    `boxIds`, `compl_id`, `wall_size_mod`, `basketType=1`,
    `is_port_product=1`, `is_def_product=1`, `is_construct=1`,
    optional canvas-collage `sizeId`, special labels and recommendation
    discount;
  - original file branch keeps legacy filename normalization and writes
    `/uploads/{hash}{filename}` plus matching SVG file.
- Evidence: `PublicBasketConstructTest` 2 / 54 PASS; full basket/public
  targeted set 43 / 483 PASS; Pint and `git diff --check` PASS.
- Not done yet: cart render/update/remove endpoints and checkout steps.

### APP-005B-BASKET-02 — cart totals and coupon calculation layer

- Legacy source of truth:
  `BasketRepository::getBasketProperties()` and private `caclSales()` compute
  item sums, formatted totals, label-protected totals and final coupon sale
  from session keys `coupon_id`, `coupon_val`, `coupon_type`,
  `spend_bonus`.
- Target implementation:
  - `PublicBasketService::getBasketProperties()` now computes
    `sumPrice`, `sumFormatedPrice`, `formatedPrice`, `totalPrice`,
    `totalPriceWithLabels`, `totalPriceWithoutLabels`,
    `formatedTotalPrice`, session coupon fields and `sale_price`;
  - percentage/fixed coupons are applied only to items without
    `has_special_label`, then label-protected items are added back;
  - legacy authenticated-only sale behavior is preserved;
  - coupon types preserved: `date`, `friend`, `facebook`, `30_40`, `40_60`,
    `1free`, `universal`, `abandoned_basket`, `giftcard`;
  - modular-inter ram price is included when `canvas_rams` exists.
- Evidence: `PublicBasketTotalsTest` 4 / 12 PASS; full basket/public
  targeted set 47 / 495 PASS; Pint and `git diff --check` PASS.
- Cart render and first AJAX actions:
  - `/cart` and localized `/cart` now render legacy step 1 through the shared
    public layout and migrated totals layer;
  - `/get_cart` returns legacy JSON-string payload with sidebar HTML, terms and
    total item count;
  - `/basket/update/count`, `/basket/remove`, `/basket/submitbonuses`,
    `/cart/clear_coupon` and `/basket/update_prices` preserve legacy session
    keys, abandoned-cart side effect and JSON/redirect contracts;
  - missing legacy `theme/viar/cart/*` partials were copied without replacing
    already target-adapted delivery partials.
- Evidence: `PublicCartPageTest` 4 / 33 PASS; full basket/public targeted set
  51 / 528 PASS; Pint and `git diff --check` PASS.
- Checkout session/image writers:
  - `/cart/delimage` removes matching `orig_images` entries and persists the
    abandoned-cart side effect;
  - `/cart/updateimg` writes inline image data to `/uploads`, then updates
    `activeImage`, `orig_images` or `savedImage` like legacy;
  - `/cart/setmaking` reads `a_production_time` plus translations when present
    and updates `terms`, `terms_price` and `total_item_price`;
  - `/cart/setcoupon` preserves legacy `spend_coupon`;
  - `/cart/setdelivery` preserves `cart_delivery`, comment image upload,
    pickup/workshop/city-delivery normalization and express-date `setmaking`;
  - `/cart/setuser` preserves phone normalization, `phone_rec`, legal-person
    session fields, checkout email sync, user creation/login and existing-user
    guard;
  - `/cart/setpay` preserves `cart_pay_type`;
  - `cart_popup.blade.php` now uses existing `cart_new.alt_modal_*`
    translations and target legacy asset path instead of Russian hardcode.
- Evidence: `PublicCartPageTest` 8 / 70 PASS; full basket/public targeted set
  55 / 565 PASS; Pint and `git diff --check` PASS.
- Step navigation fix:
  - `/cart/data`, `/cart/delivery` and `/cart/payment` no longer point to the
    step-1 `index()` renderer;
  - the routes now render legacy `step2_data`, `step3_delivery` and
    `step4_payment`, so `.cart_send_products` advances from step 1 to user
    data after `/cart/setmaking`;
  - copied partials now use target view names instead of
    `env('THEME_RESOURCES')`;
  - direct step asset refs use `config('legacy_frontend.theme_public_path')`;
  - missing alternative/recommendation modal partials are guarded with
    `includeIf` until that discount modal layer is ported;
  - payment step temporarily falls back from missing `save_order_and_pay` route
    to `cart.step4` because final order creation is the next gated pass.
- Evidence: `PublicCartPageTest` 10 / 80 PASS; full basket/public targeted set
  57 / 575 PASS; Pint and `git diff --check` PASS.
- Delivery country select fix:
  - owner reported that `/cart/delivery` could not change country and flags
    were not visible;
  - `/get_towns?country=...` already returned the legacy JSON/HTML payload, but
    the shared select script failed on a fragile parent-chain lookup;
  - `cart.min.js` now uses `closest('.select__content')`, guards the optional
    pickup point input and preserves selected option HTML in the trigger;
  - delivery country options/triggers now render `/images/flag/{country}.svg`
    while keeping the original `data-value` contract used by AJAX delivery
    recalculation.
- Evidence: `PublicCartPageTest` 11 / 84 PASS; full basket/public targeted set
  58 / 579 PASS; Pint and `git diff --check` PASS.
- Not done yet: final checkout/order writers:
  order creation, delivery persistence into `orders`, payment callbacks,
  invoice/order emails, `order_action`/chat side effects and payment-provider
  redirects. These must be ported from legacy `BasketController` source with
  outbound gates.
- full: 126 tests / 2 183 assertions PASS;
- Pint, Composer validate, route inspection и `git diff --check` PASS.
- visual correction target `5e54b52`: исходная `.wrapper`, page-owned
  `<main>` и delivery CSS order восстановлены после browser differential;
  header/visible breadcrumbs теперь совпадают с legacy по позициям
  `y=71`/`y=181`;
- четыре отсутствовавших common CSS файла перенесены byte-identically;
  targeted после исправления: 17 / 180, full: 126 / 2 190 PASS.
- inline correction target `d0fd0eb`: полный исходный
  `.delivery-section__intro`/title/list/mobile style block восстановлен,
  `theme/viar/js/delivery/delivery.js` подключён;
- computed styles intro совпадают с legacy; targeted: 17 / 183,
  full: 126 / 2 193 PASS.
- asset correction target `b8c7a83`: CSS font URLs используют
  `/theme/viar/fonts`, добавлен byte-identical root `/sprite.svg`;
- fresh Chrome console errors=0, sampled font/sprite URLs=HTTP 200;
  targeted: 18 / 203, full: 127 / 2 213 PASS.
- third-block correction target `d5f6dc3`: пропущенный `.half-grid`
  восстановлен, поэтому delivery form и courier/contact block снова
  отображаются двумя колонками;
- правая колонка повторяет исходную структуру: три courier image, отдельный
  `.d-text` и кликабельный `.d-whatsapp` с локальными WebP/PNG sources;
- browser geometry target/legacy совпадает: две колонки по `455.5px`,
  grid-gap `50px`; все изображения загрузились, console errors=0;
- delivery tests: 6 / 97, full: 127 / 2 221; Pint, Composer validate и
  diff-check PASS.
- services correction target `e04a934`: delivery снова использует исходный
  `partials.index_new.services2` с классом `services spt`, прямыми local
  image `src` и без скрывающего изображения `.lozad` placeholder;
- восстановлен исходный page typography override: service copy — `Inter`,
  заголовок — `Trajan`; browser section height совпадает (`1543px`), все
  восемь изображений видимы и загружены;
- delivery tests: 8 / 106, full: 127 / 2 227; Pint, Composer validate и
  diff-check PASS.
- pickup data correction target `390270f`: список Venipak снова выводит
  `working_hours` в legacy-формате `span.warehouse`, а provider rows
  `type=3` не участвуют в initial page/AJAX output и счётчике;
- regression покрывает расписание `09:00 - 21:00` и исключение скрытого
  `type=3` пункта; targeted: 18 / 219, full: 127 / 2 229; Pint и diff-check
  PASS.

## Декомпозиция 32 исходных route

Число 32 — это техническая выборка route-аудита, а не разрешение механически
включить все URL. Проверка активных `header_menu`/`footer_menu` в локальном
production snapshot подтвердила footer-ссылки на `new/gift-card`,
`page/delivery`, `stocks`, `blog`, `page/contacts`, `faq`, `about` и
`sitemap`. Для остальных URL перед регистрацией требуется отдельный callsite
или runtime evidence.

| Семейство | Route из исходной выборки | Решение |
|---|---|---|
| Общие контентные страницы | `faq`, `about`, `page/contacts`, `page/delivery`, `page/partnership` | `faq/about/contacts/delivery` DONE; partnership только после callsite evidence |
| Акции и подарки | `gift-card`, `new/gift-card`, `new/stocks`, `stocks` | подтверждены footer `new/gift-card` и `stocks`; старые redirect/alias проверять отдельно |
| Блог | `blog`, `blog/{slug}`, `blog/category/{slug}`, `blog/category/all`, `blog_article/{slug}` | переносить одним семейством после фиксации slug/redirect contracts |
| Стили и каталог | `all_styles`, `stylization-paintings*` | не смешивать с простыми страницами; согласовать с `CAT-001/CAT-002` и writers покупки |
| SEO/export | `sitemap`, `sitemap_products.xml`, `google-ads.xml`, `*-google-ads.xml`, `kurpirkt.xml`, `salidzini.xml` | `sitemap` подтверждён footer; XML проверять как отдельные contracts |
| Runtime/support | `arrilot/load-widget`, `change_phones`, `change_delivery_phones`, `why_are_you_leaving_questions` | не регистрировать без доказанного browser caller; routes с write/redirect side effects требуют отдельной характеристики |
| Динамические fallback | `{slug}`, `page/{page}` | generic catch-all запрещён; каждый фактически используемый slug регистрируется явно |

## Ограничение маршрутов

Переносятся только подтверждённые маршруты, реально используемые публичным
фронтом. Legacy catch-all `/{slug}` и неиспользуемые legacy routes не являются
частью автоматического переноса; каждый новый URL должен присутствовать в
route disposition/callsite evidence.

### APP-005B-STOCKS-01 — страница «Акции»

- status: `IN_PROGRESS`;
- начато: 2026-07-28;
- название задачи: `APP-005B-STOCKS-01 — перенос страницы /stocks с полной сверкой исходной логики, данных, маршрутов, assets и локалей`;
- исходный GET `/stocks` подтверждён через `StaticPagesController::hb_render_stocks`;
- перенесены explicit routes `/stocks`, `/{locale}/stocks` и canonical redirect
  `/ru/stocks` → `/stocks`; generic `/new/stocks` и fallback routes пока не
  регистрировались без отдельного evidence;
- перенесены исходные Blade `theme/viar/pages/stocks/stocks.blade.php`,
  `modals.blade.php`, `breads.blade.php`, а также нижний `gallery.zpart_trybuy`
  без изменения структуры блоков;
- добавлены Laravel 13 models для исходных таблиц `stocks`, `gallery_page` и
  `gallery_items`;
- controller data contract повторяет legacy sources: stock page meta,
  top-mail offers из `a_mail_top_sale` + `translations`, `page_faq` для
  `page->stocks`, site images и sale collections;
- авторизованный legacy side effect генерации `users.inv_sale_code`/coupon
  сохранён только за gate `PUBLIC_STOCKS_WRITES_ENABLED`, чтобы локальная
  миграция не писала в production случайно;
- `/stocks/create_coupon` перенесён с тем же request ключом `coloumn`
  (`is_1free`, `is_40_60`), удалением предыдущего купона пользователя и
  созданием нового coupon row; отправка писем купона gated через
  `PUBLIC_STOCKS_OUTBOUND_ENABLED`;
- local HTTP evidence: `/stocks` 200, `/en/stocks` 200,
  `/ru/stocks` → `/stocks` 200;
- auth-only render fix 2026-07-28: target log showed `Class "Carbon" not
  found` in `theme/viar/pages/stocks/modals.blade.php`; modals are only
  rendered for authenticated users, so guest smoke missed it. Two legacy
  `Carbon::now()` calls were made fully qualified as `\Carbon\Carbon::now()`;
  real local authenticated smoke user `10233` returned `/stocks` HTTP 200 /
  243197 bytes. `PublicContentPagesTest` 18 / 219, Pint and `git diff --check`
  PASS;
- legacy upload-writer `POST /user/send_screen` перенесён для stock modals:
  auth-only, `image` validation `png|bmp|jpg|jpeg|heic|heif` max `20240 KB`,
  storage `public/uploads`, `screen=facebook` writes `users.screenshot`,
  `screen=free` writes `users.screenshot2`, creates `order_action` and returns
  legacy JSON `message=Success` or first validation error. Writes are gated by
  `PUBLIC_STOCKS_SCREEN_UPLOADS_ENABLED`; admin mail is gated by
  `PUBLIC_STOCKS_SCREEN_OUTBOUND_ENABLED`. Routes `/user/send_screen` and
  `/{locale}/user/send_screen` are explicit;
- evidence: `PublicStockScreenTest` 5 / 29 PASS; combined
  `PublicContentPagesTest + PublicStockScreenTest` 23 / 248 PASS; full suite
  132 / 2 258 PASS; Pint and `git diff --check` PASS.
- `Write two dates` business logic перенесена в `POST /user/send_dates`:
  auth-only, deletes previous `is_dates_sale` coupons, writes
  `users.date1/date2/torj1/torj2`, creates two date coupon rows and returns
  legacy `response[1|2].suxess/message/code`; gated by
  `PUBLIC_STOCKS_DATE_WRITES_ENABLED`;
- `Invite a friend to order` email action перенесён в `POST /send_frend_email`:
  auth-only, sends legacy `mail.invite_friend_self` and `mail.invite_friend`
  templates through target Mailable; gated by
  `PUBLIC_STOCKS_FRIEND_EMAIL_ENABLED`;
- `Canvas 30x40 cm FREE OF CHARGE!` popup fixed: guest click opens login popup,
  authenticated click opens `.stock-screen-bonus`; submit continues through
  `/user/send_screen` with `screen=free`;
- evidence after dates/friend/30x40 popup: `PublicStockActionsTest` 4 / 19
  PASS; stock targeted 27 / 267 PASS; full suite 136 / 2 277 PASS; real local
  authenticated `/stocks` smoke returned HTTP 200 and confirmed
  `stock-screen-bonus`, `/user/send_dates`, `/send_frend_email` in HTML; Pint
  and `git diff --check` PASS.
- popup frame parity fix 2026-07-28: stock modals are now pushed into layout
  `@stack('modals')` rendered inside `.popup-frame`; this fixes authenticated
  click on `.js-popup-screen_bonus`, where the handler showed `.popup-frame`
  but `.stock-screen-bonus` was previously outside it. Stock modal JS now uses
  local legacy `jquery-3.6.0.min.js` instead of CDN jQuery;
- evidence after popup frame fix: real local authenticated `/stocks` render
  HTTP 200, `bonus_inside_frame=yes`, `local_jquery_before_handler=yes`; stock
  targeted `PublicStockActionsTest + PublicStockScreenTest +
  PublicContentPagesTest` 27 / 267 PASS; Pint and `git diff --check` PASS.
- legacy root asset parity fix 2026-07-28: copied legacy
  `public/css/forms.css`, `public/css/forms2.css`, and `public/js/utils.js`
  into target root public paths because `/stocks` and `intlTelInput` request
  absolute `/css/...` and `/js/utils.js?...` URLs; local HTTPS smoke for all
  three URLs returned HTTP 200. Stock targeted tests remain 27 / 267 PASS and
  `git diff --check` PASS.
- legacy root image parity fix 2026-07-28: copied legacy `public/img` into
  target root `public/img` without deleting target-only files; this restores
  absolute popup/CSS image URLs such as `/img/popup-after.png`. Local HTTPS
  smoke for `/img/popup-after.png` returned HTTP 200; stock targeted tests
  remain 27 / 267 PASS and `git diff --check` PASS.
- local stock gates 2026-07-28: `/en/user/send_screen` browser POST 503 was
  expected gate behavior with `PUBLIC_STOCKS_SCREEN_UPLOADS_ENABLED=false` or
  missing. Target local `.env` now enables stock write/upload/date gates for
  parity checks while keeping stock outbound/friend-email gates disabled.
  Config cache cleared; runtime config verified; stock action/screen tests
  9 / 48 PASS.
- owner decision 2026-07-28: all local stock gates are enabled, including
  outbound coupon/screen/friend emails. Config cache cleared; runtime bootstrap
  verified all six `legacy_frontend.stocks.*` gates as `true`; stock
  action/screen tests 9 / 48 PASS.
- stock mail runtime fix 2026-07-28: `/en/user/send_screen` reached SMTP and
  failed on certificate verification for `mail.viarcanvas.com:465`. Stock
  screen admin mail now logs transport failures instead of returning HTTP 500,
  matching homepage form resilience. Local target also sets
  `MAIL_VERIFY_PEER=false` while keeping SMTP and stock outbound gates enabled.
  Runtime config verified; stock action/screen tests 9 / 48 PASS; Pint and
  `git diff --check` PASS.
- 30x40 popup translation fix 2026-07-28: `.stock-screen-bonus` upload block
  no longer hardcodes RU strings; it uses `stock.*` translations for the title,
  example label, gift text, upload labels/hint, uploaded state and submit.
  Added `en` and `ru` keys; project fallback locale is `en` for missing locale
  keys. Authenticated `/en/stocks` render returned HTTP 200, English strings
  present and old RU strings absent. Stock targeted tests 27 / 267 PASS; Pint
  and `git diff --check` PASS.
- 40x60 activation fix 2026-07-28: `.js-popup-activation` for
  `coloumn=is_40_60` keeps the legacy JSON payload shape (`JSON.stringify` with
  `user_id` and `coloumn`) but now sends it with `contentType='application/json'`
  instead of `contentType=false`, so Laravel 13 receives the fields.
  Coupon mail transport failures are logged instead of returning HTTP 500 after
  coupon creation. Direct local JSON POST `/en/stocks/create_coupon` with
  CSRF/session returned HTTP 200 `{"message":"Success"}`. Added regression for
  40x60 coupon side effects and mail dispatch; stock targeted tests
  28 / 272 PASS; Pint and `git diff --check` PASS.
- `is_1free` activation parity 2026-07-28: second `.js-popup-activation`
  branch with `coloumn=is_1free` verified against legacy logic: delete previous
  user `is_1free` coupon, generate 8-char code, send `mail.onefree`, insert new
  active coupon and return `{"message":"Success"}`. Direct local JSON POST
  `/en/stocks/create_coupon` returned HTTP 200. Added regression for `is_1free`
  side effects and mail dispatch; stock targeted tests 29 / 277 PASS; Pint and
  `git diff --check` PASS.
- final verification 2026-07-28: authenticated `/en/stocks` render HTTP 200,
  activation buttons for `is_40_60` and `is_1free` present, modals are inside
  `.popup-frame`, JSON coupon POSTs for both activation columns return
  `{"message":"Success"}`. `PublicFrontendFoundationTest +
  PublicStockActionsTest + PublicStockScreenTest + PublicContentPagesTest`
  34 / 343 PASS; stock targeted 29 / 277 PASS; Pint and `git diff --check`
  PASS.
- full suite closure 2026-07-28: enabled local PHP CLI `extension=intl` in
  `C:\dev\php\php8.3\php.ini`; full `php artisan test` now passes
  138 / 2 287.

### APP-005B-GIFT-CARD-01 — страница `/new/gift-card`

- status: `IN_PROGRESS`;
- начато: 2026-07-28;
- название задачи: `APP-005B-GIFT-CARD-01 — перенос страницы /new/gift-card с полной сверкой исходной логики, данных, маршрутов, assets и локалей`;
- причина выбора: active footer snapshot напрямую подтверждает `new/gift-card`;
- стартовое правило: сначала сверить legacy route/controller/Blade, все формы,
  AJAX, письма, купоны/заказы/файлы и locale aliases; затем переносить только
  подтверждённые contracts без generic fallback routes.
- first implementation evidence 2026-07-28:
  - legacy route `/new/gift-card` подтверждён через
    `PageController::render_gift_new`;
  - перенесены explicit routes `/new/gift-card`, `/{locale}/new/gift-card`
    и redirect `/ru/new/gift-card` → `/new/gift-card`;
  - добавлен узкий basket writer `POST /basket/send_gift_card` с legacy
    session item contract (`pid=5`, `basketType=5`, `price`, `count`,
    `terms`, `terms_price`, `name`, `whom`, `card_type`);
  - перенесены models `GiftCard`, `GiftCardNom`, исходный Blade
    `gift_card/gift_card.blade.php` как target partial и root JS assets
    `jcf*.js`, `gift-card.min.js`;
  - real local HTTPS smoke `/new/gift-card` returned HTTP 200 / 109598 bytes,
    `gift-card-form=yes`, `data-send_gift_card=yes`;
  - asset crawl after fallback correction checked 67 directly referenced
    CSS/JS/image/font URLs and found 0 bad responses; local fallback for
    `gift_card_gift_card` uses existing legacy `certificate_{locale}.png`
    when DB `site_images` override is absent;
  - after owner copied the complete legacy root `public/css` and `public/js`
    folders into target, smoke crawl checked `/new/gift-card`,
    `/en/new/gift-card`, `/stocks`, `/en/stocks`: all pages returned HTTP 200
    and all 83/83, 83/83, 102/102 and 97/97 directly referenced assets
    returned non-error responses. `.DS_Store` and duplicate
    `canvas.min — копия.js` were removed from target before commit scope;
  - visual mismatch fix 2026-07-28: target page now loads the full legacy
    page CSS contract after common styles: jQuery UI base,
    duplicate `theme/viar/style/main.min.css`, duplicate `media.css`,
    `theme/viar/style/gift/gift.css`, `gift-media.css` and root
    `/css/gift-card.css`. Body class includes legacy `blog-frame`;
  - missing legacy `theme/viar/images/gift/{ru,en,ee,lv,lt,de,pl}.jpg`
    files were copied into target and the gift-card image fallback was restored
    from `certificate_{locale}.png` to `{locale}.jpg`, matching source Blade.
    Chrome layout comparison for `/en/new/gift-card` and `/ee/new/gift-card`
    matched legacy exactly for document height and key block y/height metrics;
  - `PublicGiftCardTest` 2 / 25 PASS; combined
    `PublicGiftCardTest + PublicContentPagesTest` 20 / 244 PASS; Pint and
    `git diff --check` PASS.
  - gift-card add-to-cart popup fix 2026-07-28: target now includes the legacy
    `.popup-cart` modal inside `.popup-frame`, but its cart link uses
    `public_url('/cart')` because the checkout route is a separate not-yet
    migrated slice. Gift-card page scripts are pushed after jQuery, the
    legacy `jquery.matchHeight.min.js` dependency is loaded before
    `gift-card.min.js`, and the relative ellipse icon path was replaced with
    a theme asset URL. Missing `images/canvas/canvas-popup.{png,webp}` popup
    assets were copied from legacy. Real Chrome submit on
    `/en/new/gift-card` produced exactly one `POST /basket/send_gift_card`,
    HTTP 200 `{"count":1,"success":"Товар успешно добавлен в корзину"}`,
    `.popup-frame` display `flex`, `.popup-cart` display `block`, and no
    4xx/5xx/console errors. Targeted public tests
    `PublicGiftCardTest + PublicStockActionsTest + PublicStockScreenTest +
    PublicContentPagesTest` 31 / 306 PASS.
  - final verification 2026-07-28: `/new/gift-card`,
    `/en/new/gift-card`, `/ee/new/gift-card` all returned HTTP 200 with no
    4xx/5xx resource responses, request failures, or console errors. The
    denomination select matches legacy DOM/metrics: native `select.jcf-ignore`,
    no `.jcf-select` wrapper, `.select-wrapper` 260x52 and select 258x50 at
    768px viewport. Browser submit on `/en/new/gift-card` still produces one
    POST 200, opens `.popup-cart`, and leaves console clean. Targeted public
    tests 31 / 306 PASS.

### APP-005B-BASKET-02 — checkout/cart parity

- status: `IN_PROGRESS`;
- cart checkout asset parity 2026-07-28:
  - legacy `cart.index`, `cart.step2`, `cart.step3`, `cart.step4` and
    `page.delivery` asset conditions were compared against the target public
    cart page;
  - target `resources/views/public/cart.blade.php` now pushes the same
    cart-step support bundle used by the source layout: Inter, Select2,
    Swiper, datepicker, SimpleBar, jQuery UI theme, intl-tel-input and utils;
  - no server-side cart business routes were changed. Existing session/cart
    contracts for totals, coupons/promocodes, setmaking, setuser, setdelivery,
    setpay and `/get_towns` remain the source of behavior.
  - `PublicCartPageTest` 11 / 88 PASS; basket/cart targeted tests
    26 / 273 PASS.
- delivery flag parity correction 2026-07-28:
  - owner screenshot showed an oversized country flag in `/cart/delivery`;
  - legacy `step3_delivery.blade.php` was rechecked and confirmed that delivery
    country select renders only country names, while flags are used in
    `step2_data` phone-country UI;
  - target delivery select flag markup and temporary flag CSS were removed;
  - `PublicCartPageTest` 11 / 89 PASS; Pint PASS.
- payment method list parity 2026-07-28:
  - target missed `App\Http\Controllers\Libwebtopay\WebToPay`, so the guarded
    payment loop skipped legacy Paysera methods;
  - ported legacy `WebToPay.php` to restore original payment keys
    `online_paysera`, `creditcart`, `google_pay`, `apple_pay`;
  - verified visible payment list plus `setpay` session storage for all seven
    checkout methods: Paysera keys, PayPal, transfer and prepayment;
  - final order creation/payment redirects remain the next gated writer slice.
  - `PublicCartPageTest` 11 / 114 PASS; basket/cart targeted tests
    26 / 299 PASS; `php -l WebToPay.php` PASS.
- checkout order writer MVP 2026-07-28:
  - added named GET route `/save_order_and_pay`, matching the legacy
    `.cart_send_pay` navigation after `/cart/setpay`;
  - added adaptive target writer that requires basket, auth user, delivery
    session and payment session, then inserts an `orders` row using existing
    columns only;
  - stored legacy-compatible `items` JSON and `delivery` JSON with selected
    payment, delivery method, address/pickup/workshop fields and contact data;
  - cleared basket/checkout/coupon/legal-person sessions after order creation
    and stored `last_order_id`;
  - provider redirects/callbacks for Paysera/PayPal remain the next gated pass.
  - `PublicCartPageTest` save-order test PASS; basket/cart targeted tests
    27 / 316 PASS; Pint PASS; `php -l PublicCartController.php` PASS.
- Paysera/PayPal provider handoff 2026-07-28:
  - task name: `APP-005B-BASKET-04 — Paysera/PayPal provider handoff and callbacks`;
  - added `ClientOrderPaymentService` and wired `saveOrderAndPay` to call it
    after order creation;
  - Paysera keys from `WebToPay::PAYSERA_METHODS_MAP` now build
    legacy-compatible gateway payload from order subtotal/sale, item terms and
    delivery price;
  - added target `PayseraController` and routes `/paysera`, `/pay_accept`,
    `/pay_cancel`, `/pay_callback`;
  - Paysera callbacks validate through legacy `WebToPay` and mark target
    `orders.payment_status=payed`;
  - PayPal remains guarded because the PayPal SDK package is absent in target.
  - `PublicCartPageTest` 12 / 132 PASS; basket/cart targeted tests
    27 / 317 PASS; Pint PASS; syntax checks PASS.
- PayPal SDK/service port 2026-07-28:
  - task name: `APP-005B-BASKET-05 — PayPal SDK/service port`;
  - Composer network install failed with local Packagist TLS `curl error 60`;
  - copied legacy SDK source for `PayPalCheckoutSdk\` and `PayPalHttp\` into
    target `app/Legacy` and added PSR-4 autoload mappings;
  - added target `OneTimePayPalService`, `OneTimePayPalController`,
    `/paypal/pay_accept` and `/paypal/pay_cancel`;
  - `ClientOrderPaymentService` now calls real PayPal service for
    `paypalOnetimePayment`;
  - PayPal callback marks `orders.payment_status=payed` after successful
    capture; `billing_invoice_uuid` is schema-guarded.
  - SDK autoload check PASS; PayPal regression PASS; basket/cart targeted tests
    28 / 325 PASS; Pint PASS; `git diff --check` PASS.
- payment sandbox smoke readiness 2026-07-28:
  - task name: `APP-005B-BASKET-06 — payment sandbox smoke readiness`;
  - verified default and localized Paysera/PayPal checkout routes;
  - verified PayPal sandbox credentials are present through Laravel bootstrap
    without exposing secret values; Paysera env values are missing and legacy
    fallback values remain active;
  - added `services.paysera` and `services.paypal` config entries;
  - switched PayPal service to config reads;
  - added CA bundle at `storage/certs/cacert.pem` and configured PayPal HTTP
    client hook;
  - PayPal external sandbox create-order smoke is still blocked locally by
    PHP/cURL trust error `SSL certificate problem: unable to get local issuer
    certificate`, matching the earlier Composer `curl error 60`.
  - PayPal regression 1 / 13 PASS; basket/cart targeted tests
    28 / 330 PASS; Pint PASS.
- payment sandbox unblock 2026-07-28:
  - task name: `APP-005B-BASKET-07 — payment sandbox unblock and checkout browser smoke`;
  - configured active PHP CLI `C:\dev\php\php8.3\php.ini` with
    `curl.cainfo` and `openssl.cafile` pointing to target CA bundle;
  - exported local Windows trust-store `Avast Web/Mail Shield Root` and appended
    it to `storage/certs/cacert.pem`, because local TLS is intercepted by Avast;
  - Composer/Packagist lookup no longer fails with SSL `curl error 60`;
  - PayPal sandbox create-order smoke succeeded: HTTP `201`, approval link
    present;
  - PayPal TCP reachability to `api-m.sandbox.paypal.com:443` passed.
  - basket/cart targeted tests 28 / 330 PASS; `git diff --check` PASS.
- safe PayPal approval smoke 2026-07-28:
  - task name remains `APP-005B-BASKET-07 — payment sandbox unblock and
    checkout browser smoke`;
  - no real card data was used, no PayPal approval/login was completed, and no
    capture request was executed;
  - initial manual smoke using production env keys was rejected by PayPal with
    `invalid_client`, confirming the check must follow the target service's
    sandbox credential selection;
  - rerun through the same sandbox/production selection as
    `OneTimePayPalService` created a PayPal sandbox order with HTTP `201`;
  - approval link was present for `www.sandbox.paypal.com`;
  - TCP reachability to `www.sandbox.paypal.com:443` passed;
  - target working tree remained clean and the temporary approval URL file was
    removed.
- browser PayPal boundary smoke 2026-07-28:
  - reused the open authenticated local Chrome tab on `/cart/payment`;
  - verified all seven payment methods are visible;
  - selected `paypalOnetimePayment` and submitted through the page JS, keeping
    the original `/cart/setpay` then `/save_order_and_pay` flow;
  - fresh sidebar `data-action` resolved to `/save_order_and_pay`;
  - browser reached `www.sandbox.paypal.com/checkoutnow` and displayed the
    PayPal sandbox login page;
  - latest local order was created with `payment_status=not_payed` and a PayPal
    order id stored in `billing_invoice_uuid`;
  - no PayPal login, real card data, approval, purchase confirmation or capture
    request was performed;
  - target working tree remained clean.

## В работе

Текущий slice — `APP-005B-BASKET-07`. Следующее действие:
повторить Paysera external handoff с sandbox/test mode; затем продолжить
cart/provider parity gaps from UAT.
