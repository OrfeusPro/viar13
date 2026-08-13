# APP-005A — Перенос существующего frontend-каркаса, публичных маршрутов, шаблонов, assets и мультиязычности в Laravel 13 с подготовкой полнофункциональных транзакционных сценариев

```text
TASK_ID: APP-005A
STATUS: DONE
PRIORITY: Critical
OWNER: программист
BRANCH: codex/app-005a-frontend-foundation
CREATED_AT: 2026-07-22
UPDATED_AT: 2026-07-27
DEPENDS_ON: FE-002=VERIFY; AUD-005=DONE
PARENT: APP-005
NEXT_ACTION: foundation закрыт; начать APP-005B-PAGES-01
```

## Цель

Перенести в Laravel 13 существующий публичный frontend без редизайна и без
перегенерации legacy CSS/JS/images/fonts. Создать совместимый routing/layout/
locale shell, на который последовательно переносятся как страницы чтения, так
и реальные транзакционные сценарии.

## Обязательные границы

- Production и server `test` не изменяются; работа выполняется в
  `G:\OSPanel\home\viar_filament`.
- Legacy public assets переносятся как frozen corpus; Vite/Mix/Webpack их не
  пересобирает, не минифицирует и не меняет порядок подключения.
- Все языки, locale prefixes, translation sources, canonical/hreflang и
  существующие публичные URL сохраняются.
- Конечный frontend scope включает auth/account, session/basket, generators,
  order creation/update, uploads, chats, payments/callbacks, delivery, mail,
  Socialite и CRM/SA effects.
- До cutover transactional writers работают только с isolated local/staging DB
  и sandbox/sink integrations. Production write-owner остаётся legacy.

## План

- [x] снять машиночитаемый aggregate inventory public routes, methods, names, middleware и handlers;
- [x] связать все routes с controllers, Blade/layouts, language middleware и assets; route-level manifest и client callsites закрыты в APP-005A-ROUTE-01;
- [x] отдельно выделить non-GET и state-changing GET cohorts; поштучная disposition ещё впереди;
- [x] зафиксировать frozen asset manifest и доказать отсутствие пересборки;
- [x] реализовать Laravel 13 compatibility reader/audit для legacy asset manifest;
- [x] перенести совместимый базовый theme layout, header/footer, menus/settings/model translations и первый содержательный URL contract;
- [x] перенести первый минимальный vertical slice `/robots.txt` с characterization tests;
- [x] выполнить recursive HTML/CSS dependency crawl `/condition`, перенести вложенные static assets и DB-driven menu media, закрепить bytes/SHA-256 baseline;
- [x] восстановить legacy JS order, desktop/mobile menu и общий modal layer `/condition`; подтвердить interactions в браузере без JS/network errors;
- [x] перенести публичную главную `/`, её DB/content/locale dependencies, критические inline styles, frozen CSS/JS и standard Laravel public-storage contract;
- [x] подтвердить точное совпадение геометрии главной на desktop `2560 × 1249` и mobile `390 × 844`, включая все основные секции;
- [x] вернуть popup-вход, регистрацию и восстановление пароля с реальными Laravel 13 endpoints, mobile trigger, isolated writer gate и failure-path tests;
- [x] закрыть [APP-005A-FORM-01](APP-005A-FORM-01-homepage-photo-lead.md): safe upload, lead-order, idempotency, локализованные письма, account setup, gated Synvolve и ручная browser/mailbox приёмка;
- [x] закрыть [APP-005A-FORM-02](APP-005A-FORM-02-homepage-quiz.md): четырёхшаговый quiz, localized aliases, user/order/file, idempotency, admin+client mail, messenger preference и ручная LT-приёмка;
- [x] составить child-task map всех transactional flows и их integration gates;
- [x] выполнить route/response/locale/asset/security/zero-delta checks;
- [x] обновить control center, catalog и running log.

## Definition of Done

- inventory покрывает все legacy public routes и не скрывает state-changing GET;
- каждый route имеет disposition и целевую задачу;
- target shell отвечает на выбранных URL с прежними locale/SEO/session contracts;
- legacy asset corpus не пересобран и не изменён неявно;
- первый vertical slice проходит legacy/target differential tests;
- транзакционные функции не объявлены готовыми без writer, failure и sandbox evidence;
- production/server не изменены, unintended DB delta отсутствует;
- evidence и одно точное следующее действие записаны в документацию.

## Evidence

- Target commit: `899eb2b` (`APP-005A — Frontend foundation, языки и frozen assets`).
- Target commit: `5a51517` (`APP-005A — Первая рабочая frontend-страница condition`).
- Target commit: `1077451` (`APP-005A — Закрытие зависимостей страницы condition`).
- Target commit: `d843b87` (`APP-005A — Интерактивное меню и popups страницы condition`).
- Target commit: `4a481a5` (`APP-005A — Восстановление стилей condition`).
- Target commit: `6681951` (`APP-005A — Возвращение sale и блока связи`).
- Target commit: `25c02cd` (`APP-005A — Google rating badge и безопасный read API`).
- Target commit: `b31499b` (`APP-005A — Главная страница и production parity`).
- Target commit: `674759c` (`APP-005A — Рабочая popup-авторизация главной`).
- Target commit: `cf03ec9` (`APP-005A-FORM-01 — Рабочая homepage photo lead форма`).
- Target commits `a31f9bd`, `c976c63` зафиксированы как отменённая попытка preview/redesign; 27.07 восстановлен точный legacy file UI и работа выбора страны.
- Target commit: `09cb1bf` (`APP-005A-FORM-01 — возврат точного legacy file UI и исправление выбора страны`).
- Target commits: `97b4431`, `f487995` (`APP-005A-ASSET-02 — полный legacy theme corpus и full-tree baseline`).
- Target commit: `d324ae2` (`APP-005A-PARITY-REBASE — исходный локализованный client mail и отдельное admin письмо`).
- Target commit: `de45c62` (`APP-005A-FORM-01 — безопасная account-setup ссылка, gated Synvolve snapshot и раздельные delivery statuses`); targeted 15 / 108 и full 102 / 2 150 PASS.
- Legacy route baseline: 5 311 total routes; 2 231 public; 2 000 public closures;
  97 public non-GET/ANY routes; 16 state-changing GET candidates; canonical
  SHA-256 `728be9ca0b5055e6894b591a1a7717b0d90fb38ece563fe57c0b11afbde13df9`.
- Перенесены 334 PHP dictionaries и все десять каталогов `cn/de/ee/en/et/jp/lt/lv/pl/ru`;
  source/target tree SHA-256 `2fd4fb83a6d947bed0d63b2d3708fd704560c1c6cce992af2eb0a83b27b84077`.
- Перенесены 12 exact Mix bundles и `mix-manifest.json`; bytes/SHA-256 audit PASS.
- `.gitattributes` помечает frozen dictionaries/assets как `-text`, поэтому Git
  не меняет CRLF/LF; выборочные index/raw blob comparisons PASS.
- Locale routes `/robots.txt` и `/{locale}/robots.txt` работают для семи public
  locales; `fr` возвращает 404. Local HTTPS smoke PASS.
- `/condition` перенесён как реальная Laravel 13 страница на существующей local DB:
  сохранены видимые header/content/FAQ/advantages/footer, SEO structured data,
  canonical/hreflang, семь public locales и default `ru` без префикса; `/ru/condition`
  даёт 301 на canonical, unsupported `fr` — 404.
- Добавлен совместимый с Voyager read adapter для base-locale `en` и таблицы
  `translations`, а также adapters для settings и legacy media/menu data. Все
  content queries покрыты zero-count-delta tests; public/Filament/outbound writes false.
- В первый page commit вошли 332 функциональных статических файла; неиспользуемые
  `.DS_Store` и Windows shortcut `active-projects.lnk → D:\active-projects`
  сознательно исключены. Первичная проверка 33 прямых HTML assets оказалась
  неполной и не считается больше доказательством dependency closure.
- Recursive HTML + CSS `url(...)` crawl выявил ещё 24 legacy static files и 25
  DB-driven изображений header menu. Они добавлены без пересборки в commit
  `1077451`; первоначально menu media были изолированы во временном
  `/legacy-storage`. Это решение заменено в `b31499b` стандартной схемой Laravel:
  media находятся в `storage/app/public`, а `public/storage` указывает на этот
  каталог. После заполнения владельцем local legacy `storage/header-menu` все
  25 используемых файлов дали exact SHA-256 match с target; HTTP production
  остаётся дополнительным подтверждением.
- Зафиксирован автоматический baseline: 49 файлов, 8 780 505 bytes, canonical
  tree SHA-256 `3b6177088d73f11aba742c24c2703e67e676191fbfd1f14bd6cc49a4111e6ba8`.
  Текущий recursive crawl: 95 same-origin URL, из них 91 HTTP 200; оставшиеся
  4 stale CSS ссылки дают тот же 404 на production и не подменялись фиктивными файлами.
- Local HTTPS: `/condition`, `/lv`, `/lt`, `/pl`, `/de`, `/en`, `/ee` variants —
  HTTP 200; все 25 menu media URL — HTTP 200. После modal layer baseline расширен
  до 55 файлов / 9 043 973 bytes / tree SHA-256
  `2461e6ba76be70f46e05ad4047e802f2f7f15abba83e3b58c9a1d5b561059b2d`.
- Headless Chrome interaction evidence: desktop dropdown получает `js-active`;
  mobile burger display=`block`; sticky coupon закрывается в `offer-hidden`;
  login→registration, login→forgot и repair-basket popups display=`block`;
  JavaScript errors=0, requested same-origin HTTP errors=0.
- Повторное сравнение production/target HTML выявило пропущенный legacy
  `pages.condition.head`: target не подключал `question-all.css` и
  `question-all-media.css`. В commit `4a481a5` добавлен расширяемый
  `page_styles` stack и восстановлен исходный порядок трёх page-specific links,
  включая повторный `overall.css`, как на production. После изменения набор
  stylesheet URL production/target совпадает; содержимое всех девяти CSS
  совпадает после нормализации только CRLF/LF.
- Повторный structural class diff после CSS fix выявил отсутствие двух общих
  layout-блоков до header: условной `.top-sale` и
  `.phone-mobile-btn.js-phone-mobile-btn`. В commit `6681951` они восстановлены
  с прежней настройкой `site.top_sale`, локализованным текстом, WhatsApp/Viber
  links из `settings`, исходным JS handler и byte-identical `sale.webp`/
  `sprite.svg`. Asset baseline: 57 files / 9 067 702 bytes / SHA-256
  `f0c68874175ff0b28998d4098fe2534dc67f6685edf75e8b48624bd8a305649e`.
- Targeted: 10 tests / 135 assertions; full suite: 70 tests / 1 743 assertions
  PASS. Pint и `git diff --check` PASS.
- В commit `25c02cd` возвращён production `google-rating-badge` и добавлен
  `/api/google-rating`. При `STAGE0_OUTBOUND_ENABLED=false` endpoint не выполняет
  внешний HTTP и возвращает зафиксированный fallback `4.8 / 225`; успешный
  Google Places ответ при явном включении outbound кэшируется, ошибки безопасно
  откатываются на fallback без логирования API key. Endpoint и cache/failure
  boundaries покрыты feature-тестами.
- После badge structural class-set diff production/target не показывает
  отсутствующих common-layout классов. Остаются только два `fa-google` внутри
  legacy login/register Socialite links; они сознательно не имитируются до
  отдельного auth/Socialite transactional slice и callback contract.
- Local HTTP smoke: `/condition`, `/lv/condition`, `/lt/condition`,
  `/pl/condition`, `/de/condition`, `/en/condition`, `/ee/condition` и
  `/api/google-rating` отвечают 200; `/ru/condition` штатно перенаправляет 301
  на canonical `/condition`; `et` является storage-only locale и не имеет
  отдельного public route. Full suite: 73 tests / 1 757 assertions; Pint и
  `git diff --check` PASS.
- Popup submit forms отображаются, но отсутствующие target routes
  `custom_login_ajax`, `custom_register_ajax`, `forget_email_reset`,
  `cart.set-email`, Socialite и callback не объявлены рабочими: формы помечены
  `data-endpoint-ready=0` и блокируются capture-handler до отдельного
  transactional auth/account slice.
- Подтверждена исходная data anomaly, не ошибка adapter: в local legacy
  `pages.id=9` DB metadata для `de` содержит польский текст, для `pl` — немецкий;
  отдельной `en` translation row нет, потому English корректно берётся из base columns.
  Данные не исправлялись автоматически; перед production reconciliation требуется
  отдельное решение о content cleanup.
- Одинаковый production/target screenshot-check выполнен 22.07.2026 в Chrome:
  desktop viewport `2174 × 1297`, mobile viewport `390 × 848`. После захвата
  фактические PNG имеют одинаковые парные размеры `2168 × 1294` и `384 × 835`.
  Геометрия header, breadcrumbs, заголовка, контента, widgets и responsive layout
  совпадает. Изменённые pixels составляют `0.938%` desktop и `2.594%` mobile;
  визуально они объясняются только данными: production показывает
  `Распродажа Лето 2026`, local snapshot — `Весенняя распродажа 2026`, а Google
  badge в target сразу выводит безопасный fallback `4.8 / 225`, пока production
  в момент desktop-снимка оставался в placeholder state. Это не layout gap.
- Comparison evidence:
  `C:\Users\Валентин\.codex\visualizations\2026\07\13\019f5a7f-9ebe-7673-a050-8c4d058e3753\condition-parity-20260722\06-desktop-comparison.png`
  и `07-mobile-comparison.png`. Mobile header показывает burger, coupon и
  phone widget; дополнительный browser click меняет burger state
  `burger-open → burger-close`, то есть handler подключён.
- Production и server `test` не изменялись; public/Filament/outbound writes false.
- В `b31499b` перенесена главная `/`: controller/data adapters, homepage Blade
  sections, локализованный контент, Google Reviews fallback и необходимые
  frozen assets. Обнаружены и восстановлены отсутствовавшие production
  `head_styles` (около 98 KB inline critical CSS), исходный порядок CSS и
  актуальные production-версии общих CSS/JS без запуска Mix/Webpack/Vite.
- В standard public storage перенесено 209 media-файлов / 28 541 395 bytes;
  `LEGACY_MEDIA_BASE_URL` по умолчанию равен `/storage`, а WebP-проверки читают
  `storage/app/public`. Проверочные media URL отвечают HTTP 200.
- Главная проверена попарно с production. Desktop `2560 × 1249`: document
  height обеих страниц `13 079`, позиции и высоты всех основных секций
  совпадают. Mobile `390 × 844`: document height обеих страниц `15 011`,
  совпадает геометрия 14 проверенных секций. Отличие hero-слайда во время
  снимка обусловлено фазой autoplay, а не layout/CSS.
- Clean browser state: broken images=0, application JavaScript errors=0;
  extension-only MetaMask warnings к приложению не относятся. HTTP smoke `/`,
  `/lv`, `/lt`, `/pl`, `/de`, `/en`, `/ee`, `/api/google-reviews` — 200.
- Targeted homepage/condition/foundation: 15 tests / 364 assertions; full suite:
  78 tests / 1 984 assertions. PHP lint, Pint и `git diff --check` PASS.
- Известный source-data gap: два пути `our_works.avatar` в RU snapshot отсутствуют
  в доступном legacy media corpus; фиктивные изображения не создавались, перед
  production reconciliation их нужно получить из финального production storage.

## Следующее действие

`APP-005A-FORM-01` и `APP-005A-FORM-02` закрыты `DONE` после автоматических
проверок и ручной приёмки владельцем. Следующий готовый child slice —
`APP-005A-ROUTE-01`: завершить поштучную disposition всех public routes,
связать handlers/views/locales/assets/state effects и назначить отдельный task
каждому ещё не перенесённому writer. После карты выбирается следующий
полнофункциональный поток; предварительный кандидат —
catalogue/generator → basket → order. Quick order, cart/email и Socialite
остаются отдельными контрактами.

`APP-005A-ROUTE-01` завершила foundation: 2 236 legacy public rows, 27 target
public registrations и 217 client callsites получили воспроизводимые
manifests/disposition/task references. Parent `APP-005A` закрыт `DONE`.
Следующая задача — [APP-005B-PAGES-01](APP-005B-PAGES-01-public-content-pages.md).
