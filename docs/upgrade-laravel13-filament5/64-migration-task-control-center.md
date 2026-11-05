# 64. Центр управления задачами миграции

> **АРХИВ с 2026-08-13.** Этот control center относится к отменённому
> направлению с отдельным target. Актуальная основа — текущий репозиторий
> `G:\OSPanel\home\viar13`; активный журнал — корневой
> `MIGRATION_PROGRESS.md`. Записи ниже сохранены только как история/evidence.

Этот файл — **единственная оперативная точка входа** для продолжения миграции в новой задаче Codex или Git-ветке. Полный нормализованный список из 131 исполняемой задачи находится в [65-migration-executable-task-catalog.md](65-migration-executable-task-catalog.md), проверка покрытия 124 технических источников — в [66-full-document-task-traceability-audit.md](66-full-document-task-traceability-audit.md), исходный технический backlog — в [15-task-checklist.md](15-task-checklist.md), а порядок Stage 0 — в [54-filament-stage0-execution-pack.md](54-filament-stage0-execution-pack.md).

```text
WORKBOARD_VERSION: 61
UPDATED_AT: 2026-07-28
MIGRATION_PHASE: FRONTEND_FUNCTIONAL_MIGRATION
ACTIVE_TASK: APP-005B-PAGES-01
ACTIVE_SLICE: APP-005B-BASKET-02
NEXT_TASK: APP-005B-PAGES-01
LAST_COMPLETED: APP-005B-GIFT-CARD-01
PRODUCTION_WRITE_OWNER: LEGACY
TARGET_STATE: FULL_FUNCTIONAL_PARITY
TEST_STATE: ACTIVE_LEGACY_PHP_7_4
EXECUTION_MODE: LOCAL_FIRST_SERVER_DEFERRED
```

## 1. Текущая точка остановки

- Аудит и клиентские решения завершены; технический backlog и карты Voyager → Filament подготовлены.
- `S0-01` завершена: версии, зависимости, плагины и legacy frontend-assets зафиксированы.
- `S0-02A` завершена локально: канонический Laravel 13 / Filament 5 Git working tree находится в `G:\OSPanel\home\viar_filament`; проверенный архив остаётся в `C:\OSPanel\domains\asoft\viar-next-artifacts`.
- Перенос working tree подтверждён: 16 361 файл / 97 385 517 bytes; последние 6 977 файлов сверены по size и SHA-256 без пропусков/расхождений. Старый `C:\OSPanel\domains\asoft\viar-next` удалён после успешной проверки.
- OpenServer подключён к `G:\OSPanel\home\viar_filament` как `viar-filament.loc`, окружение `System PHP-8.3`. PHP 8.3.14, Laravel boot и platform requirements PASS. После S0-03 локальная `/admin` включена за `browse_admin`; login/reset отвечают 200, guest panel даёт 302 на login; write/outbound остаются false. Старый `/backoffice-next` больше не является target route.
- OpenServer PHP online Composer audit пока заблокирован `curl error 60`/Schannel revocation check. TLS verification не отключать; исправить CA/revocation connectivity и повторить audit до dependency changes или release acceptance.
- Локальный `.env` target объединён с данными `C:\OSPanel\domains\asoft\viar\.env`: 60 значений перенесены/добавлены без вывода секретов, новый `APP_KEY` сохранён, safety flags и отдельные namespaces принудительно сохранены. Local MySQL `SELECT 1`, config clear и 6/6 tests PASS; `.env` и `.env.backup` игнорируются Git.
- На сервер новый проект **ещё не загружался и не устанавливался**. `test.viarcanvas.com` остаётся активным на legacy Laravel / PHP 7.4.
- Решение владельца от 21.07.2026: перенос на сервер выполняется только после готовности рабочей локальной админ-панели. `S0-02B`, `S0-02C` и `S0-02D` переведены в `HOLD`; production и `test` не затрагиваются.
- Решение владельца от 22.07.2026: Laravel 13 / Filament 5 строится как полная замена production, а не как постоянный read-only интерфейс. Термин `read-only` допустим только для ранних Stage 0 spikes и safety-проверок; итоговый scope включает все операции создания, изменения и удаления, предусмотренные текущей бизнес-логикой.
- В обязательный target scope входят транзакционные сценарии frontend и админки: auth/account, корзина и генераторы, создание и изменение заказов, статусы, файлы, комментарии и чаты, платежи и callbacks, доставка, письма, Socialite, Synvolve/SA/CRM, переводы и административные actions с прежней моделью ролей.
- До финального переключения эти writers реализуются и проверяются только на отдельной local/staging БД, с sandbox/sink credentials и без реальных outbound side effects. Production writer остаётся legacy; в конце обязательны delta reconciliation, один write-owner на capability и репетиция переключения без плановой паузы.
- `S0-03` закрыта `DONE`: пользователь подтвердил успешный вход существующей admin-учётной записью через `https://viar-filament.loc/admin/login`; автоматические 22 tests / 55 assertions и zero-count-delta PASS.
- `S0-04` закрыта: deny-by-default RBAC/navigation adapter, 8 ролей, 138 пунктов меню и direct URL/Livewire probes подтверждены.
- `S0-05` переведена в `VERIFY`: local read projection, R5–R14 и полный artifact/rollback rehearsal PASS; staging UAT/fresh production checkpoint отложены до серверного этапа.
- `S0-06` была локально технически готова, но пользовательская проверка выявила отсутствие visual/information parity: Filament list показывает только малую сводку против восьмизонного legacy списка. Задача возвращена в `IN_PROGRESS`, ветка `codex/s0-06-orders-readonly-safe-action`.
- `S0-07` имеет сохранённый baseline R1, но возвращена в `BACKLOG` до закрытия reopened S0-06. Сервер и production не затрагиваются.
- Решение владельца от 22.07.2026: незавершённый `S0-06` переведён в `HOLD`, его commits/evidence и parity gap сохранены; `S0-07` остаётся `BACKLOG`. Активной стала `APP-005A` — первый frontend foundation slice в ветке `codex/app-005a-frontend-foundation`.
- `APP-005A-FORM-01` закрыта `DONE`: полный legacy call graph формы перенесён до двух разных писем, регистрации нового пользователя и `admin_order_created` Synvolve snapshot. Target `d324ae2` восстановил исходный локализованный client mail; `de45c62` заменил plaintext-пароль безопасной setup-ссылкой, разделил четыре delivery status и закрыл production Synvolve URL отдельным false gate. Targeted 15 / 108 и full OpenServer PHP 8.3 102 / 2 150 PASS; владелец подтвердил ручную приёмку словами «все отлично».
- Решение владельца от 27.07.2026 для frontend migration: сначала переносится полный функционал legacy как есть, затем выполняется адаптация/улучшение под Laravel 13. Это относится к UI, request contracts, файлам, заказам, письмам, redirect/success behavior и всем вложенным side effects; redesign/refactor не закрывает задачу без parity baseline.
- `APP-005A-FORM-02` закрыта `DONE`: владелец повторил LT quiz и подтвердил полностью локализованное клиентское письмо словами «отлично, работает». AJAX submit, aliases, receipt/idempotency, user/order/file, admin+client mail, validation, messenger preference и local gates подтверждены. Выбор E-mail/WhatsApp не меняет recipients: как в legacy, admin и client mail отправляются всегда; `dest` задаёт предпочтительный канал ответа менеджера. Full 108 / 2 212 PASS; target evidence `5963029`.
- `APP-005A-ROUTE-01` и parent `APP-005A` закрыты `DONE`. Полная карта содержит 2 236 public rows: 1 990 redirects, 246 функциональных routes; `PARITY_DONE=12`, `SHELL_ONLY=9`, `MISSING_TARGET=189`, `SECURITY_REPLACE=2 026` (из них 36 non-redirect). Target: 27 public registrations, 24 legacy-mapped и 3 RU compatibility aliases. Статический client scan: 217 callsites, два unresolved route names переданы в `AUTH-001` и checkout. Три воспроизводимых CSV и builder находятся в [67](67-public-route-disposition-and-frontend-backlog.md). Повторные hashes стабильны; DB delta=0; full 108 / 2 212 PASS.
- `APP-005B-PAGES-01` начата. Первый foundation slice закрыт target commit `39b5171`: Laravel 13 base controller получил `renderPublic(view, path, pageData)`, а `PublicFrontendViewData` централизует menu/footer, locale URLs, canonical и locale contract. Главная, condition и thanks переведены без изменения маршрутов/HTML; лишние service queries condition/thanks удалены. Targeted 27 / 237 и full 109 / 2 219 PASS; Pint/Composer validate/diff-check PASS.
- `APP-005B-PAGES-01`, FAQ slice закрыт target commit `4c0f2b3`: `/faq` и локализованные aliases читают исходные meta/FAQ/services/site-images, используют общий controller contract и сохраняют legacy blocks/assets/FAQPage schema. RU/EN/EE differential подтверждает по 5 FAQ и одну schema; arbitrary catch-all и непроверенные runtime routes не добавлены. Targeted 42 / 600, full 113 / 2 258, Pint/Composer/diff-check PASS.
- `APP-005B-ABOUT-01` закрыт target commit `0617fac`: явные `/about` + locale routes перенесли translated page, team/process, services, видео, site-image и Organization schema без Voyager runtime. RU/EN/EE differential совпадает по 6 team blocks, 5 process tabs, 16 service-class occurrences и одной Organization schema; public content 8 / 79, full 117 / 2 298, Pint/Composer/diff-check PASS.
- `APP-005B-CONTACTS-01` закрыт target commit `0d2ec7c`: явные `/page/contacts` + locale routes перенесли translated page, 5 адресов, 14 телефонных стран, 5 FAQ, офисные вкладки, 5 карт и ContactPage/Organization schema. Собственных writers у страницы нет. RU/EN/EE differential совпадает по ключевым блокам; targeted 12 / 120, full 121 / 2 130, Pint/Composer/diff-check PASS. Устаревший тест `/condition` исправлен: immutable baseline содержит 32 явных release-файла, а изменяемый стандартный `public/storage` из него исключён.
- `APP-005B-DELIVERY-01` закрыт target commit `b602ec0`: явные `/page/delivery` + locale routes, translated content, 3 тарифа городов, 10 workshop-pickup строк, 14 стран/тарифов, FAQ/services/images и используемый `POST /get_towns` перенесены с исходным JSON/HTML contract. Venipak изолирован enable-gate, TLS verification, timeout и secret-free logging; опасный maintenance GET `change_delivery_phones` не перенесён. RU/EN/EE differential совпадает по 4 блокам, 6 шагам, 27 delivery-item occurrences и двум schema. Asset crawl 34/34 PASS; targeted 17 / 173, full 126 / 2 183, Pint/Composer/diff-check PASS.
- Ручная сверка обнаружила, что видимые delivery breadcrumbs перекрывались шапкой, хотя DOM и JSON-LD были на месте. Target `5e54b52` вернул исходные `.wrapper`, page-owned `<main>` и CSS contract (`css/style.css`, `mod.css`, `fontello.css`, `css/overall/overall.css`). После исправления legacy/target совпадают по геометрии header `y=71`, breadcrumbs `y=181`; targeted 17 / 180 и full 126 / 2 190 PASS.
- Повторная source/browser сверка нашла пропущенные inline CSS и interaction script исходного `delivery.blade.php`. Target `d0fd0eb` вернул полный `.delivery-section__intro`/mobile/title/list style block и подключил существующий `theme/viar/js/delivery/delivery.js`. Computed styles intro совпадают с legacy; targeted 17 / 183, full 126 / 2 193 PASS.
- Chrome выявил font/sprite 404 после восстановления исходных CSS. Target `b8c7a83` направил delivery/overall/fontello/mod CSS на сохранённый `public/theme/viar/fonts`, добавил byte-identical root compatibility `public/sprite.svg` и regression на assets. Свежая вкладка: console errors=0, sampled URLs=HTTP 200; targeted 18 / 203, full 127 / 2 213 PASS.
- Сверка третьего delivery-блока с оригиналом выявила отсутствующий `.half-grid` и упрощённую правую колонку. Target `d5f6dc3` восстановил две колонки, три courier image, отдельный `.d-text` и WhatsApp link с локальными WebP/PNG assets. Browser geometry совпадает с оригиналом: две колонки по `455.5px`, gap `50px`; console errors=0. Delivery tests 6 / 97, full 127 / 2 221 PASS.
- Сверка delivery-блока `Our services` выявила неправильный shared partial: он игнорировал `spt` и оставлял `.lozad`-изображения прозрачными. Target `e04a934` вернул исходный delivery-specific partial, прямые local image `src` и typography override `Inter`. Browser geometry совпадает с оригиналом: section `1543px`, title `Trajan`, copy `Inter`, все 8 изображений видимы. Delivery tests 8 / 106, full 127 / 2 227 PASS.
- Ручная проверка delivery pickup списка выявила, что target выводил пункты без `working_hours` и включал provider rows `type=3`, из-за чего видимый список и счётчик расходились с production. Target `390270f` вернул legacy-format `span.warehouse` с расписанием и фильтрацию `type=3` для initial page/AJAX. Targeted 18 / 219 и full 127 / 2 229 PASS.
- 32 исходных route разложены на common content, promotions, blog, catalog/style, SEO/export, runtime/support и dynamic fallback. Это техническая выборка, не разрешение включить всё: active footer snapshot напрямую подтверждает `new/gift-card`, `page/delivery`, `stocks`, `blog`, `page/contacts`, `faq`, `about`, `sitemap`. Следующее действие `APP-005B-PAGES-01`: завершить `APP-005B-STOCKS-01`; generic `/{slug}`/`page/{page}` и непроверенные служебные routes не переносить. Затем `new/gift-card`, blog и связанные `APP-005B-FORM-03`, `CAT-001/CAT-002`, `APP-001`, `APP-005B-CHECKOUT-01`.
- `APP-005B-STOCKS-01` закрыт target commit `d14ac49`: перенесены исходные Blade `theme/viar/pages/stocks/*`, explicit routes `/stocks`, `/{locale}/stocks`, redirect `/ru/stocks` → `/stocks`, модели `stocks`, `gallery_page`, `gallery_items` и controller data contract. Закрыта вся подтверждённая business logic страницы: `/stocks/create_coupon` для `is_40_60`/`is_1free`, `/user/send_screen` для `facebook`/`free`, `/user/send_dates`, `/send_frend_email`, popup frame, 30x40 translations, root `/css`, `/js`, `/img` assets и SMTP-failure resilience. Evidence: authenticated `/en/stocks` 200, обе `.js-popup-activation` кнопки работают по legacy JSON contract, full suite 138 / 2 287 PASS; Pint и diff-check PASS. Следующий slice: `APP-005B-GIFT-CARD-01`.
- Auth-only render bug на `/stocks` исправлен: target log показывал `Class "Carbon" not found` в stock modals, которые подключаются только при `Auth::check()`. Blade оставлен legacy-equivalent, два `Carbon::now()` заменены на `\Carbon\Carbon::now()`. Real local authenticated smoke user `10233`: `/stocks` HTTP 200 / 243197 bytes; `PublicContentPagesTest` 18 / 219, Pint и `git diff --check` PASS.
- `APP-005B-GIFT-CARD-01` закрыт target commit `a031778`, assets follow-up `0e8ca36`: `/new/gift-card`, locale aliases, legacy gift-card Blade/data/assets и popup add-to-cart перенесены. Browser submit `/en/new/gift-card` даёт один `POST /basket/send_gift_card`, HTTP 200 и открывает `.popup-cart`; native denomination select restored через `jcf-ignore`. Targeted public tests 31 / 306 PASS. После ручного замечания владельца выявлено, что текущий target `send_gift_card` был narrow writer, а production использует общий `BasketController`/`BasketRepository`; активирован prerequisite slice `APP-005B-BASKET-01`.
- `APP-005B-BASKET-01` начат: переносится общий public basket layer до продолжения товарных страниц. Первый target pass вводит `PublicBasketService` с legacy-equivalent `normalizeBasket`, `pushItem`, gift-card item contract и abandoned-cart side effect (`session('email')`/auth user only). Evidence: browser `/en/new/gift-card` 200, `POST /en/basket/send_gift_card` 200, popup frame/cart visible, bad resource responses=0; `PublicGiftCardTest` 3 / 33 PASS. Второй target pass переносит `/basket/add/portrait`; третий pass переносит `/basket/add/module`. Четвёртый pass закрывает generic canvas `/basket/add` (`userImage`, optional `Image3d`, `orig_images`, `photo_ex`, `boxIds`, labels, `terms_price` subtraction, recommendation discount, abandoned cart) и `/basket/coupon_use` по legacy auth/session/user contract (`coupon_id`, `coupon_val`, `coupon_type`, `users.active_coupon`, friend invalid `-2`, missing `-1`, guest `finded=user`). Пятый pass закрывает `/basket/add/construct`: `image_offset`, `is_orig_file`, SVG collage file, `photo_ex`, `fon`, `orig_images`, portrait/construct flags, packaging fallback, `terms_price`, recommendation discount, special labels, normalized basket and abandoned cart. Evidence: construct 2 / 54 PASS; full basket/public targeted 43 / 483 PASS.
- `APP-005B-BASKET-02` начат: первый target pass переносит расчётный слой `BasketRepository::getBasketProperties`/`caclSales` в `PublicBasketService::getBasketProperties` без подключения cart UI. Сохранены totals, `totalPriceWithLabels`, `totalPriceWithoutLabels`, `formatedTotalPrice`, authenticated-only coupon sale, `date`, `friend`, `facebook`, `30_40`, `40_60`, `1free`, `universal`, `abandoned_basket`, `giftcard`, percent/fixed coupon conversion and modular ram price when `canvas_rams` exists. Evidence: totals 4 / 12 PASS; full basket/public targeted 47 / 495 PASS. Следующее действие: подключить legacy cart render/update/remove endpoints к этому расчётному слою и только затем checkout steps.
- `APP-005B-BASKET-02` продолжен: второй target pass подключает legacy cart render и первые AJAX actions к перенесённому totals layer. Добавлены `/cart`, localized `/cart`, `/get_cart`, `/basket/update/count`, `/basket/remove`, `/basket/submitbonuses`, `/cart/clear_coupon`, `/basket/update_prices`; скопированы недостающие legacy partials `theme/viar/cart/*` без перезаписи уже target-adapted delivery partials. Сохранены session keys `basket`, `coupon_id`, `coupon_val`, `coupon_type`, `spend_bonus`, `basket_country`, abandoned-cart side effect, formatted price strings and legacy JSON-string responses. Evidence: cart page/actions 4 / 33 PASS; full basket/public targeted 51 / 528 PASS. Следующее действие: переносить checkout steps/writers (`setuser`, `setdelivery`, `setpay`, order creation/payment callbacks) отдельно, сверяя с legacy `BasketController`.
- `APP-005B-BASKET-02` продолжен: третий target pass переносит checkout session/image writers из legacy `BasketController`: `/cart/delimage`, `/cart/updateimg`, `/cart/setmaking`, `/cart/setcoupon`, `/cart/setdelivery`, `/cart/setuser`, `/cart/setpay` and localized aliases. Сохранены image upload mutations, `orig_images` delete, production terms from `a_production_time`, `spend_coupon`, `cart_delivery`, legal-person session fields, `phone_rec`, checkout email sync, existing-user guard and `cart_pay_type`. `cart_popup.blade.php` переведён через существующие `cart_new.alt_modal_*` keys и переведён на target legacy asset path. Evidence: cart page/actions 8 / 70 PASS; full basket/public targeted 55 / 565 PASS. Следующее действие: переносить создание заказа/payment callbacks/order mails отдельно, с outbound gates и source-level сверкой legacy `BasketController`.
- Исправлен ручной blocker владельца: `/cart/data` визуально оставался на первом шаге, потому что target routes `/cart/data`, `/cart/delivery`, `/cart/payment` временно указывали на `PublicCartController@index`. Теперь они рендерят реальные legacy partials `step2_data`, `step3_delivery`, `step4_payment`; `cart_send_products` после `/cart/setmaking` переходит на форму данных, далее на доставку и оплату. Missing partial includes заменены на target view names, отсутствующие recommendation/alternative modals подключаются через `includeIf`, direct `env('THEME')` asset refs на этих шагах заменены на target legacy theme path, а отсутствующий final `save_order_and_pay` route временно fallback'ится на `cart.step4` до отдельного order-creation pass. Evidence: cart page/step navigation 10 / 80 PASS; full basket/public targeted 57 / 575 PASS. Следующее действие остаётся final checkout/order writer.
- Исправлен ручной blocker владельца на `/cart/delivery`: выбор страны падал в `cart.min.js` из-за fragile parent-chain (`previousElementSibling`) и отсутствующего optional `.cart-data-page__input.point input`, хотя `/get_towns?country=...` отвечал успешно. Select handler переведён на `closest('.select__content')`, optional point input guarded, trigger сохраняет выбранный option HTML. В delivery country select добавлены флаги из `/images/flag/{country}.svg` без изменения `data-value`/delivery AJAX business flow. Evidence: cart delivery/select regression 11 / 84 PASS; full basket/public targeted 58 / 579 PASS. Следующее действие остаётся final checkout/order writer.
- Legacy upload-writer `POST /user/send_screen` перенесён для stock modals: auth-only, image validation `png|bmp|jpg|jpeg|heic|heif` max `20240 KB`, storage `public/uploads`, `screen=facebook` -> `users.screenshot`, `screen=free` -> `users.screenshot2`, `order_action` audit и legacy JSON `message=Success`/validation first error. Writes gated by `PUBLIC_STOCKS_SCREEN_UPLOADS_ENABLED`, admin mail gated by `PUBLIC_STOCKS_SCREEN_OUTBOUND_ENABLED`; routes `/user/send_screen` и `/{locale}/user/send_screen`. Evidence: `PublicStockScreenTest` 5 / 29 PASS; combined stock/content targeted 23 / 248 PASS; full suite 132 / 2 258 PASS; Pint/diff-check PASS.
- `Write two dates` и `Invite a friend` business logic перенесены: `POST /user/send_dates` удаляет старые `is_dates_sale` купоны, пишет `users.date1/date2/torj1/torj2`, создаёт два date-coupon row и возвращает legacy `response[1|2].suxess/message/code`; `POST /send_frend_email` отправляет два legacy invite шаблона через target Mailable. Gates: `PUBLIC_STOCKS_DATE_WRITES_ENABLED`, `PUBLIC_STOCKS_FRIEND_EMAIL_ENABLED`. Блок `Canvas 30x40 cm FREE OF CHARGE!` теперь guest -> login popup, auth -> `.stock-screen-bonus`; upload идёт через `/user/send_screen` с `screen=free`. Evidence: `PublicStockActionsTest` 4 / 19 PASS; stock targeted 27 / 267 PASS; full 136 / 2 277 PASS; real auth smoke `/stocks` HTTP 200 and `stock-screen-bonus/send_dates/send_frend_email=yes`; Pint/diff-check PASS.
- Заявка `18234` подтвердила `/en/thanks`, но первый SMTP вызов был отклонён из-за TLS interception Avast; legacy скрывал проблему через unconditional `verify_peer=false`. После отключения Avast Symfony verified SMTP auth PASS, оба штатных сообщения повторены один раз и приняты сервером, receipt=`sent/sent`. Target `7ca2856` добавил `MAIL_VERIFY_PEER` с безопасным default=true; локальный bypass не используется. Осталось подтверждение письма пользователем во входящих/спаме.
- Regression после исправления: targeted 20 tests / 421 assertions и full 97 / 2 128 PASS; Pint, JavaScript syntax и `git diff --check` PASS.
- Начата `APP-005A-ASSET-02`: после browser `404` для `/theme/viar/img/flags.webp` полный filesystem diff выявил 1 662 отсутствующих файла / 126 924 244 bytes в target `public/theme/viar`. Выполняется missing-only перенос всего runtime-корпуса; 14 существующих production-frozen CSS/JS и три target-only adapter не перезаписываются, `.DS_Store`/`.lnk` исключаются.
- `APP-005A-ASSET-02` закрыта `DONE`: target `97b4431` добавил 1 661 отсутствующий runtime-файл / 126 923 399 bytes. Итоговый baseline — 1 943 файла / 169 975 077 bytes / SHA-256 `31e9582a7d841abb1492335898c78d60caa62343c997fc51c332c4a3d9379f33`. Missing=0; 1 926 source/target файлов byte-identical; 14 ранее проверенных production CSS/JS не перезаписаны; три target adapter сохранены. Chrome reload: `flags.webp`/generic resource errors=0; full suite 97 / 2 127 PASS.
- Проверка 27.07 уточнила, что order `18232` был отправлен через legacy `viarcanvas.loc`, а не target: временный `/uploads/1785136561_1.png` штатно перенесён legacy в `public/orders/N18232-...png`, файл существует. После refresh локальной БД отсутствовала target-only `public_form_submissions`; точная миграция повторно применена, table=true, writer gate=true. Это был промежуточный блокер; последующие target smoke и ручная приёмка закрыли его.
- При проверке `/condition` подтверждена исходная data anomaly в local legacy DB: metadata `de`/`pl` фактически поменяны языками, а English хранится в base columns без отдельной translation row. Adapter повторяет Voyager semantics; данные не исправлялись автоматически.

## 2. Статусы

Используются только следующие значения:

| Статус | Значение |
|---|---|
| `BACKLOG` | Задача известна, но зависимость или очередь ещё не позволяют начать |
| `READY` | Можно начинать после проверки актуального `main` |
| `IN_PROGRESS` | Работа реально начата; ID обязан совпадать с `ACTIVE_TASK` |
| `WAITING` | Нужен ответ пользователя, клиента, сервера или внешнего провайдера |
| `VERIFY` | Реализация закончена, но DoD/evidence ещё не подтверждены |
| `DONE` | DoD выполнен и приложена ссылка на evidence |
| `HOLD` | Задача сознательно отложена; начинать без нового решения нельзя |
| `CANCELLED` | Задача отменена с зафиксированной причиной |

Одновременно допускается только одна `IN_PROGRESS` задача для одного исполнителя. Статус `DONE` без evidence запрещён.

## 3. Как продолжать работу в другой ветке

1. Открыть этот файл и прочитать блок `WORKBOARD_VERSION`.
2. Синхронизироваться с последним `main`; не ориентироваться на старую копию реестра в давно созданной ветке.
3. Проверить задачу и её зависимости в [полном каталоге](65-migration-executable-task-catalog.md). Если `ACTIVE_TASK` не `NONE`, продолжать её по карточке задачи. Не брать следующую задачу без явного закрытия, ожидания или остановки текущей.
4. Перед началом задачи изменить её статус на `IN_PROGRESS`, заполнить `ACTIVE_TASK`, ветку и дату обновления.
5. Для задачи дольше четырёх часов, с серверными изменениями, БД, платежами, файлами или production создать отдельную карточку из [tasks/TEMPLATE.md](tasks/TEMPLATE.md).
6. После каждого значимого шага обновить карточку: что сделано, evidence, блокер и одно точное следующее действие.
7. При завершении поставить `VERIFY`, выполнить DoD, затем `DONE`; очистить `ACTIVE_TASK` и назначить `NEXT_TASK`.
8. Изменение статуса считается общим для разных веток только после попадания реестра в `main`. Для параллельной работы сначала резервировать задачу отдельным небольшим commit/merge.

Рекомендуемое имя ветки: `codex/<task-id>-<short-name>`, например `codex/s0-02b-server-release`. Само наличие ветки не означает, что задача начата: источник истины — статус в этом файле.

## 4. Оперативный реестр Stage 0

| ID | Статус | Приоритет | Задача | Зависит от | Детали / evidence | Следующее действие |
|---|---|---|---|---|---|---|
| GOV-01 | `DONE` | Critical | Создать межветочный центр управления задачами | — | этот документ | Использовать как первую точку входа |
| S0-01 | `DONE` | Critical | Зафиксировать baseline, зависимости, версии и public assets | — | [55-filament-stage0-s01-evidence-freeze.md](55-filament-stage0-s01-evidence-freeze.md) | Изменять baseline только отдельным решением |
| S0-02A | `DONE` | Critical | Собрать и проверить локальный Laravel 13 / Filament 5 skeleton и artifact | S0-01 | [62-filament-stage0-offline-target-build-result.md](62-filament-stage0-offline-target-build-result.md) | Сохранить artifact и SHA-256 без пересборки |
| S0-02L | `DONE` | Critical | OpenServer PHP 8.3, Git relocation, safe local `.env`/DB/runtime baseline | S0-02A | [62](62-filament-stage0-offline-target-build-result.md), `G:\OSPanel\home\viar_filament\STAGE0.md` | Сохранять safety flags и local-only контур |
| S0-02B | `HOLD` | Critical | Установить artifact в непубличный server release-каталог | рабочая локальная админка, owner GO | [карточка](tasks/S0-02B-server-offline-release.md), [команды](63-filament-stage0-offline-server-release-install.md) | Не выполнять до отдельного решения владельца |
| S0-02C | `HOLD` | Critical | Создать отдельные staging DB/user/env/namespaces и выполнить server tests без HTTP exposure | S0-02B | [63, следующий gate](63-filament-stage0-offline-server-release-install.md) | Выполнять после PASS S0-02B |
| S0-02D | `HOLD` | Critical | Поднять закрытый PHP 8.3 FPM preview и подготовить атомарное переключение test | S0-02C, review | [54-filament-stage0-execution-pack.md](54-filament-stage0-execution-pack.md) | Не менять active test до отдельного GO |
| S0-03 | `DONE` | Critical | Локальная совместимость users/auth/session/cart | S0-02L | [карточка](tasks/S0-03-local-auth-session-compatibility.md) | Сохранять проверенные auth/session contracts |
| S0-04 | `DONE` | Critical | RBAC adapter, 138 menu items и симуляция восьми ролей | S0-03 | [карточка](tasks/S0-04-rbac-navigation-role-simulation.md) | 36 tests / 1 470 assertions; 1 104 role-navigation decisions, mismatch=0 |
| S0-05 | `VERIFY` | Critical | Translation adapter и versioned publisher | S0-02L, S0-04 | [карточка](tasks/S0-05-translation-adapter-versioned-publisher.md) | Local PASS; staging I18N-UAT-001…014 и fresh production checkpoint отложены |
| S0-06 | `HOLD` | Critical | Read-only OrderResource и одна безопасная synthetic Action | S0-03, S0-04 | [карточка](tasks/S0-06-orders-readonly-safe-action.md) | Основа сохранена; после frontend priority вернуться к 8 зонам, cohorts, фильтрам и visual parity |
| S0-07 | `BACKLOG` | Critical | Chat и legacy-path file adapter spikes | S0-03, S0-04/S0-06 parity | [карточка](tasks/S0-07-chat-file-adapters.md) | R1 сохранён; возобновить после reopened S0-06 |
| S0-08 | `BACKLOG` | Critical | Feature flags, receipts, observability и deployment scaffold | S0-02L, S0-03…S0-07 | [54 §12](54-filament-stage0-execution-pack.md) | Включить независимые read/write/outbound gates |
| S0-09 | `BACKLOG` | Critical | CI evidence, server runbook и Stage 0 sign-off | S0-01, S0-02D, S0-03…S0-08 | [54 §13–14](54-filament-stage0-execution-pack.md) | Закрыть после локальной реализации и отложенного server rehearsal |
| APP-005A | `DONE` | Critical | Frontend foundation: legacy routes/layouts/assets/locales и карта transactional flows | FE-002, AUD-005 | [карточка](tasks/APP-005A-frontend-foundation.md), [ROUTE-01](tasks/APP-005A-ROUTE-01-public-route-disposition.md), [результат](67-public-route-disposition-and-frontend-backlog.md), target `5963029` | foundation закрыт; продолжение APP-005B-PAGES-01 |
| APP-005B-PAGES-01 | `IN_PROGRESS` | Critical | Подтверждённые public content/page families из исходной выборки 32 route | APP-005A-ROUTE-01 | [карточка](tasks/APP-005B-PAGES-01-public-content-pages.md), target `39b5171`, `4c0f2b3`, `0617fac`, `0d2ec7c`, `b602ec0`, fixes `5e54b52`, `d0fd0eb`, `b8c7a83`, `390270f`; stocks target `d14ac49`, docs `87717642`; gift-card `/new`, `/en`, `/ee` HTTP 200 with no asset/console errors; native denomination select parity; checkout/cart fixes through target `3349753`; cart asset parity added with legacy Select2/Swiper/datepicker/SimpleBar/intl-tel-input bundle; delivery select flags removed after source parity recheck; payment list parity restored via legacy `WebToPay::PAYSERA_METHODS_MAP`; checkout order writer MVP adds `/save_order_and_pay`; Paysera handoff/callback routes added; PayPal SDK/service ported; PHP/cURL CA trust fixed with local Avast root; PayPal browser smoke reaches `www.sandbox.paypal.com/checkoutnow`, latest order `not_payed`, PayPal id stored, no login/card/capture; basket/cart targeted tests 28 / 330 PASS | Paysera external handoff with sandbox/test mode; continue cart/provider parity gaps from UAT |

## 5. Обязательные параллельные условия

Эти задачи не меняют приоритет функциональных волн, но должны быть закрыты до соответствующего production gate.

| ID | Статус | Задача | Требуется до | Подробности |
|---|---|---|---|---|
| OPS-01 | `READY` | Проверенный backup/restore drill с измеренным RPO/RTO | первого production write/cutover | [33-production-readiness-audit.md](33-production-readiness-audit.md) |
| OPS-02 | `BACKLOG` | Отдельные staging DB, Redis/cache/session namespaces и provider credentials | S0-02D | [60-filament-stage0-test-preflight-result-and-quarantine.md](60-filament-stage0-test-preflight-result-and-quarantine.md) |
| OPS-03 | `BACKLOG` | Scheduler/worker ownership, failed jobs, deploy drain/restart и monitoring | Wave 1 writes | [27-mail-queue-scheduler-static-audit.md](27-mail-queue-scheduler-static-audit.md) |
| SEC-01 | `BACKLOG` | Ротация обнаруженных tracked/default provider credentials | provider tests | [12-risk-register.md](12-risk-register.md) |
| SEC-02 | `BACKLOG` | Закрытие критичных admin/file/payment/public auxiliary ACL рисков | соответствующей волны | [15-task-checklist.md](15-task-checklist.md) |
| QA-01 | `BACKLOG` | Characterization и differential baseline для orders/prices/statuses | S0-06 / Wave 1 | [09-testing-strategy.md](09-testing-strategy.md) |
| QA-02 | `BACKLOG` | Контрактные sandbox tests интеграций | Wave 6 | [07-integrations-migration.md](07-integrations-migration.md) |

## 6. Функциональные волны после Stage 0

Порядок подтверждён клиентом. Волна не переводится в `READY`, пока Stage 0 не получил письменный sign-off и не закрыты её Critical-зависимости из [15-task-checklist.md](15-task-checklist.md).

Каждая волна означает полнофункциональный перенос, а не только просмотр данных. Для применимых модулей DoD обязательно включает прежние create/update/delete/actions, права доступа, транзакции, идемпотентность, файлы, очереди и интеграционные side effects. До cutover все такие проверки выполняются в изолированном local/staging-контуре; факт наличия read projection не закрывает функциональную волну.

| ID | Статус | Приоритет | Результат | Оценка | Основные детали |
|---|---|---|---|---:|---|
| W1 | `BACKLOG` | 1 | Заказы и CRM | 200–360 ч | [22-basket-order-static-audit.md](22-basket-order-static-audit.md), [53-filament-implementation-blueprint.md](53-filament-implementation-blueprint.md) |
| W2 | `BACKLOG` | 2 | Пользователи и роли | 100–180 ч | [26-auth-account-chat-static-audit.md](26-auth-account-chat-static-audit.md), role/permission maps |
| W3 | `BACKLOG` | 3 | Чаты и вложения | 80–160 ч | [26-auth-account-chat-static-audit.md](26-auth-account-chat-static-audit.md), [24-files-pdf-static-audit.md](24-files-pdf-static-audit.md) |
| W4 | `BACKLOG` | 4 | Каталог и цены | 140–260 ч | [28-public-catalog-generators-static-audit.md](28-public-catalog-generators-static-audit.md), [29-voyager-bread-custom-admin-static-audit.md](29-voyager-bread-custom-admin-static-audit.md) |
| W5 | `BACKLOG` | 5 | Переводы | 80–140 ч | [50-l3g06-r14-final-translation-reconciliation.md](50-l3g06-r14-final-translation-reconciliation.md) |
| W6 | `BACKLOG` | 6 | Платежи, доставка и внешние сервисы | 120–220 ч | [23-payment-static-audit.md](23-payment-static-audit.md), [25-venipak-delivery-static-audit.md](25-venipak-delivery-static-audit.md) |
| W7 | `BACKLOG` | 7 | Контент, блог, галерея и SEO | 160–300 ч | [29-voyager-bread-custom-admin-static-audit.md](29-voyager-bread-custom-admin-static-audit.md), [30-seo-redirect-preview-auxiliary-static-audit.md](30-seo-redirect-preview-auxiliary-static-audit.md) |
| FINAL-01 | `BACKLOG` | Critical | Общая UAT, security/load/reconciliation и устранение блокеров | 60–100 ч из финального этапа | [13-manual-checklist.md](13-manual-checklist.md) |
| FINAL-02 | `BACKLOG` | Critical | Репетиция и модульное production-переключение с нулевой плановой паузой | 30–50 ч из финального этапа | [10-deployment-and-rollback.md](10-deployment-and-rollback.md), [34-l3g06-write-owner-delta-cutover.md](34-l3g06-write-owner-delta-cutover.md) |
| FINAL-03 | `BACKLOG` | High | Наблюдение 24/48 часов, Voyager read-only и последующий вывод | 30–50 ч из финального этапа | [10-deployment-and-rollback.md](10-deployment-and-rollback.md) |

## 7. Как добавлять новую задачу

Новая задача получает стабильный ID и добавляется в подходящую таблицу. Нельзя переиспользовать старый ID или менять смысл уже начатой задачи. Если объём изменился, создать дочернюю задачу (`W1-01`, `W1-02`) или новую версию карточки.

Минимально обязательные поля:

- ID и короткое название;
- статус и приоритет;
- зависимости;
- branch/owner после начала;
- ссылка на детальную карточку;
- измеримый DoD;
- evidence;
- одно точное следующее действие;
- дата последнего обновления.

## 8. Правило актуальности

После каждого заметного шага одновременно обновляются:

1. карточка текущей задачи;
2. этот управляющий реестр;
3. `docs/crm_sa_implementation_plan.md` как хронологический журнал;
4. детальный технический документ, если изменился контракт или решение.

Если документы расходятся, текущий статус и очередность берутся из этого файла, технические факты — из профильного аудита/evidence, а история решений — из `docs/crm_sa_implementation_plan.md`.
