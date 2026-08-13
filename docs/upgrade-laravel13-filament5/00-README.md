# Аудит и план миграции VIAR: Laravel 6 / Voyager → Laravel 13 / Filament 5

Дата среза: 17.07.2026. Статус: аудит завершён; S0-01 evidence/dependency freeze выполнен с `CONDITIONAL GO` только на provisioning isolated staging; application implementation не начата.

## Как читать выводы

- **[Код]** — подтверждено файлами проекта.
- **[БД]** — подтверждено безопасными `SELECT` из локальной БД `viar`.
- **[Официально]** — подтверждено документацией Laravel/Filament или автора пакета.
- **[Проверить]** — локальный снимок не доказывает состояние production.

## Исправленная исходная точка

| Область | Подтверждённый факт | Значение для миграции |
|---|---|---|
| PHP | **Текущий локальный runtime проекта — `C:\OSPanel\modules\php\PHP_7.4\php.exe`, PHP 7.4.30**. PHP 8.3.30 находится отдельно в `C:\dev\php\php8.3\php.exe` и попадает в `PATH`. | Все диагностики текущего приложения выполнять явным PHP 7.4. Ранее сделанный вывод «текущий CLI PHP 8.3» неверен. |
| Backend | Laravel 6.20.40, Voyager 1.5.2. | Нельзя безопасно обновить одним Composer-запуском. |
| Цель | Laravel 13 требует PHP ≥8.3; Filament 5 требует PHP ≥8.2, Laravel ≥11.28, Livewire ≥4.0 и Tailwind CSS ≥4.0. | Целевая платформа: PHP 8.3/8.4, Laravel 13, Filament 5; `ext-intl` обязателен. |
| Frontend | CSS/JS ведутся вручную в `public/`; Mix 6.0.31 только конкатенирует и версионирует файлы. Самостоятельного модульного Webpack-приложения нет. | Публичный frontend замораживается без миграции и пересборки. Vite/Tailwind допускается только в изолированном контуре Filament. |
| БД | MariaDB 10.6.9, 185 таблиц, 107.58 MiB, 10 FK, нет triggers/routines/events. | Схема слабо защищена FK; целостность проверять собственными сверками. |
| Заказы | Exact `COUNT(*)` локального снимка — **14 423**, `MAX(id)=17 875`; прежние 13 646 были InnoDB estimate из `information_schema.TABLE_ROWS`. `lead_id = orders.id`. | ID и строковые статусы — неприкосновенные инварианты; baseline counts брать только exact queries. |
| Работающий production | Текущий сайт продолжает принимать реальные заказы, а редакторы — изменять контент и переводы на протяжении всей миграции. | Legacy production остаётся единственным источником истины до контролируемого переключения; длительная заморозка БД и одноразовый экспорт переводов недопустимы. |
| Voyager | 133 BREAD, 1 997 `data_rows`, 138 admin menu items, 675 permissions, 2 080 связей permission-role, 8 ролей. | Это полноценная CMS/CRM-платформа, а не типовая CRUD-админка. |
| Языки | Публичные локали `lv|lt|pl|ru|de|en|ee`; в `translations` 64 779 строк по 111 таблицам. Есть 6 записей `et`; `uk` не включён публично, для CRM зафиксирован fallback `uk → ru`. | Все текущие языки, URL и переводы сохраняются. `uk` автоматически не включать. |
| Интеграции | PayPal, Paysera/WebToPay, Synvolve/SA, SMTP, Facebook/Google Socialite и **Venipak**. | Каждая интеграция получает отдельный контрактный стенд и rollback. |

## Главные расхождения

1. **[Код]** Venipak — реальная внешняя интеграция: `BasketController`, `PageController`, `Admin\Api\VinepakApiController`, `venipak_data`. Предварительное утверждение «внешний API перевозчика не выявлен» опровергнуто.
2. **[Код]** `artisan route:list --json` на правильном PHP 7.4 падает: `routes/web.php:8,519` ссылается на отсутствующий `App\Http\Controllers\ImageController`. Это текущий дефект baseline, не следствие миграции.
3. **[БД]** `data_types` содержит две неразрешимые модели: `App\Models\Canva` и `App\Models\ACollagFaq`.
4. **[БД]** локальные таблицы SA существуют, но пусты. Это не подтверждает production-поток и объём.
5. **[Код]** `.env.example` не покрывает часть используемых переменных (Facebook, PayPal, attachment limits); fallback `SA_API_KEY=test-key` нельзя переносить в production.
6. **[Код]** зарегистрировано 5 311 routes; 11 handlers неразрешимы, 5 route names дублируются. Полный реестр создан независимо от падающего `artisan route:list`.
7. **[Код]** у 58 `admin/*` routes на route/group уровне присутствует только `web`. Статический L2-аудит подтвердил: 14 получают controller `auth`, 1 явно проверяет auth+роль, 1 небезопасно разыменовывает `Auth::user()`, 3 ожидаемо публичны, 1 handler не разрешается, а у 38 auth enforcement не обнаружен. Требуется Critical ACL test matrix на staging.
8. **[Код]** в `app/` зафиксировано 1 770 методов/функций; 81 метод длиннее 100 строк. `Orders::saveOrder()` объединяет DB writes, uploads, mail, Synvolve, coupon/bonus и session без доказанной общей transaction/outbox boundary.
9. **[Код]** L2-аудит корзины подтвердил разные price contracts по product families: для modular-interior и generic component-price веток country multiplier может применяться дважды; canvas-interior не использует count; portrait отбрасывает дробную часть через `(int)`.
10. **[Код]** public, Admin и SA не используют единый калькулятор: Admin принимает ручные `basket_price[]`, SA содержит собственные builders и копию coupon logic, а public зависит от session/DB. До переноса обязательны differential golden fixtures.
11. **[Код]** FN-03 payment L2 подтвердил: Paysera amount/currency helper не вызывается, `pay_cancel` содержит success write, PayPal capture response не проверяется, а публичный `POST /orders/change/payment` не имеет auth/role и повторяет bonus/gift-card/mail side effects.
12. **[Код]** Paysera signing credential находится в tracked controller; после этого аудита требуется немедленная ротация. `paypal/paypal-checkout-sdk 1.0.2` в lock помечен abandoned в пользу Server SDK.
13. **[Код/БД]** FN-04 file/PDF L2 выявил 123 файловых sink-вызова в 26 PHP-файлах, 40 контрактов и 28 orphan `order_painter_images`; `renameUploadsPhoto()` меняет файлы и DB без rollback journal.
14. **[Код]** Invoice PDF публикуется по предсказуемому `orders.id`; часть file actions не имеет auth/ownership, account uploads не связывают user с order/painter assignment, а SA attachments скачиваются по произвольному URL с отключённой TLS-проверкой и проверкой размера только после полного чтения.
15. **[Код/БД]** FN-08 Venipak/delivery L2 подтвердил: browser задаёт delivery price/method/point, три admin provider operations имеют только `web`, public pickup сохраняется без stable provider point ID, XML не экранируется, transport не имеет timeout/status/error contract, а label/courier не имеют attempt ledger/idempotency/void/reconciliation. Локально 4 072 `venipak` orders и 3 274 stored label codes.
16. **[Код]** Venipak credentials исторически/default присутствуют в tracked comments/migration и хранятся plaintext в operational DB; требуется немедленная ротация, secret manager, live/sandbox gate и HTTPS allowed-host policy.

## Рекомендация

Рекомендуется **гибридный перенос в чистый Laravel 13 skeleton**:

1. заморозить поведение Laravel 6 characterization-тестами;
2. подготовить отдельную PHP 8.3/8.4 staging-среду и новый skeleton;
3. переносить доменные модули без изменения существующей БД и ID;
4. сначала публичный сайт и API, затем Filament параллельно Voyager;
5. переключать модули админки волнами после паритетных тестов;
6. удалить Voyager только после полного паритета, резервного окна и подтверждения бизнеса.

### Обязательное правило для работающего магазина

Миграция выполняется рядом с действующим production, а не на однажды снятой «финальной» копии. Новые заказы, платежи, сообщения, изменения статусов, контента и переводов продолжают записываться старым приложением. До переключения legacy production является system of record. Staging-копии регулярно обновляются и используются только для тестов; они не становятся источником production-данных. На первом cutover Laravel 13 должен работать с той же актуальной БД либо с заранее доказанным механизмом синхронизации. Отдельная новая production-БД без CDC/reconciliation не рекомендуется.

Для переводов предпочтителен общий storage: Filament adapter и Voyager читают ту же `translations`, но в каждый момент только один UI имеет право записи для конкретного модуля. Если используется отдельная БД, синхронизируются не только новые строки, но update/delete, base English fields, ordering, slugs, menu/service records и связанные SEO-поля; перед переключением выполняется полная locale/table/column reconciliation.

Последовательный апгрейд полезен как диагностическая ветка для выявления breaking changes, но как production-маршрут слишком связывает семь framework-major переходов с заменой админки. Полный rewrite опасен потерей неявной бизнес-логики. Гибрид сохраняет данные и поведение, но использует современный skeleton.

## Блокеры до реализации

- production: PHP, MariaDB/MySQL, web server, extensions, cron, queue worker/Supervisor, storage, backups и release topology не подтверждены;
- нет полного тестового baseline для checkout, статусов, callback оплаты, SMTP и OAuth;
- стандартный `artisan route:list` сейчас не собирается из-за отсутствующего `ImageController`; полный диагностический registry получен, но baseline defect не исправлен;
- требуется закрыть Critical ACL-аудит 58 административных routes и доступность test/mail/file endpoints;
- требуется владелец бизнес-приёмки для 133 BREAD и матрицы 675 permissions;
- нужны sandbox/test credentials и контрактные примеры PayPal, Paysera, Synvolve/SA, Venipak, Facebook и SMTP;
- необходимо решить срок параллельной работы `/admin` Voyager и Filament.
- требуется согласовать почти нулевое окно переключения: кто и как контролирует заказы, платежные callback, webhooks и очереди, поступающие непосредственно во время deploy.
- до mail/queue migration закрыть public mail preview side effects, SMTP TLS verification и marketing consent/unsubscribe; production cron/workers/locks остаются неподтверждёнными.

## Этапы и ориентир

| Этап | Результат | Оценка |
|---|---|---:|
| 0. Baseline и стенды | production inventory, backup/restore drill, characterization tests | 3–5 недель |
| 1. Платформа | чистый Laravel 13, PHP 8.3/8.4, CI, изолированная сборка Filament | 2–4 недели |
| 2. Домен и публичный сайт | models/services/controllers/routes/views без Voyager | 6–10 недель |
| 3. Интеграции | contract tests и перенос 6 интеграционных контуров | 4–7 недель |
| 4. Filament | 133 BREAD, кастомные CRM/заказы/SEO/медиа | 10–18 недель |
| 5. Frontend | заморозка текущих assets, совместимость URL/manifest, visual regression | 1–3 недели |
| 6. UAT/cutover | нагрузка, безопасность, переключение и наблюдение | 3–5 недель |

Итого: ориентировочно **29–52 инженерных недель**; календарно 6–10 месяцев для команды 2–3 разработчика + QA/DevOps, после уточнения production и бизнес-приоритетов.

## Навигация

Текущая техническая клиентская версия: `План_миграции_Laravel13_Filament5_для_клиента_v2.36.docx` (ответы клиента 1–68, оценки/риски, полное покрытие Voyager → Filament и Stage 0 на 160–280 часов; content/structure/a11y checks выполнены; визуальный render/PNG review намеренно не выполнялся по указанию пользователя). Предыдущая версия: `План_миграции_Laravel13_Filament5_для_клиента_v2.35.docx`; последняя полностью визуально проверенная техническая версия — v2.34 (67 страниц).

Отдельная понятная дорожная карта для отправки клиенту: `Дорожная_карта_перехода_на_Filament_для_клиента_v1.0.docx` (7 страниц; отдельно объяснён большой объём работ по Filament, приведены этапы, оценки, результаты, критерии приёмки, риски и следующие шаги; все страницы визуально проверены 20.07.2026).

- [01 — текущий проект](01-current-project-audit.md)
- [02 — зависимости и сервер](02-dependency-compatibility.md)
- [03 — Laravel 6→13](03-laravel-upgrade-path.md)
- [04 — Voyager](04-voyager-inventory.md)
- [05 — архитектура Filament](05-filament-architecture.md)
- [06 — код приложения](06-application-code-migration.md)
- [07 — интеграции](07-integrations-migration.md)
- [08 — БД и данные](08-database-migration-plan.md)
- [09 — тестирование](09-testing-strategy.md)
- [10 — deployment/rollback](10-deployment-and-rollback.md)
- [11 — пошаговый roadmap](11-step-by-step-roadmap.md)
- [12 — реестр рисков](12-risk-register.md)
- [13 — ручная приёмка](13-manual-checklist.md)
- [14 — открытые вопросы](14-open-questions.md)
- [15 — общий task checklist](15-task-checklist.md)
- [64 — центр управления задачами и текущая точка остановки](64-migration-task-control-center.md)
- [65 — полный нормализованный каталог исполняемых задач](65-migration-executable-task-catalog.md)
- [66 — полный аудит трассируемости 124 технических файлов](66-full-document-task-traceability-audit.md)
- [Приложение — построчная матрица «файл → задачи»](appendix-document-task-traceability.csv)
- [16 — план traceability-аудита](16-traceability-audit-plan.md)
- [17 — полный реестр маршрутов](17-route-inventory.md)
- [18 — реестр функций и hotspots](18-function-inventory.md)
- [19 — критические бизнес-потоки](19-critical-flow-traces.md)
- [20 — незавершённый аудит функций перед миграцией](20-pre-migration-function-audit.md)
- [21 — статический аудит admin ACL](21-admin-acl-static-audit.md)
- [22 — корзина, цены и три пути создания заказа](22-basket-order-static-audit.md)
- [Appendix — route/function contracts корзины и заказов](appendix-basket-order-contracts.csv)
- [23 — Paysera, PayPal и payment requests](23-payment-static-audit.md)
- [Appendix — payment routes/contracts](appendix-payment-contracts.csv)
- [24 — файлы, изображения, PDF и SA attachments](24-files-pdf-static-audit.md)
- [Appendix — file/image/PDF contracts](appendix-file-pdf-contracts.csv)
- [25 — Venipak, доставка, courier, labels и print](25-venipak-delivery-static-audit.md)
- [Appendix — Venipak/delivery contracts](appendix-venipak-delivery-contracts.csv)
- [26 — авторизация, Socialite, личный кабинет и четыре chat streams](26-auth-account-chat-static-audit.md)
- [Appendix — auth/account/chat contracts](appendix-auth-account-chat-contracts.csv)
- [27 — почта, очереди, jobs и scheduler](27-mail-queue-scheduler-static-audit.md)
- [Appendix — mail/queue/scheduler contracts](appendix-mail-queue-scheduler-contracts.csv)
- [28 — публичный каталог, карточки и конструкторы](28-public-catalog-generators-static-audit.md)
- [Appendix — public catalogue/generator contracts](appendix-public-catalog-generator-contracts.csv)
- [29 — Voyager BREAD, custom admin CRUD, каталог и переводы](29-voyager-bread-custom-admin-static-audit.md)
- [Appendix — все 133 Voyager BREAD modules](appendix-voyager-bread-modules.csv)
- [30 — SEO redirects, sitemap/feeds, previews, reviews и auxiliary endpoints](30-seo-redirect-preview-auxiliary-static-audit.md)
- [Appendix — активная карта 256 SEO redirects](appendix-seo-redirect-map.csv)
- [Appendix — 47 FN-13 route/contracts](appendix-fn13-route-contracts.csv)
- [31 — P0 containment public auxiliary routes](31-p0-auxiliary-containment.md)
- [32 — P0 review submission security](32-p0-review-submission-security.md)
- [33 — L3 production readiness, runtime и deployment](33-production-readiness-audit.md)
- [34 — L3-G06 write-owner, delta, reconciliation и cutover](34-l3g06-write-owner-delta-cutover.md)
- [CSV — L3-G06 capability write-owner matrix](appendix-l3g06-write-owner-matrix.csv)
- [35 — L3-G06 каталог, контент, переводы, SEO и media](35-l3g06-catalog-content-translations-cutover.md)
- [CSV — L3-G06 content/translation write-owner matrix](appendix-l3g06-content-write-owner-matrix.csv)
- [36 — L3-G06 canonical reconciliation specification](36-l3g06-reconciliation-specification.md)
- [CSV — 22 reconciliation datasets](appendix-l3g06-reconciliation-datasets.csv)
- [37 — read-only production baseline command pack](37-l3g06-production-baseline-command-pack.md)
- [38 — production R1/R2 evidence и normalized language Batch R3](38-l3g06-production-r1-r2-evidence-and-r3.md)
- [39 — анализ language R3 и targeted Batch R4](39-l3g06-r3-analysis-and-r4.md)
- [40 — анализ language R4 и полный hash manifest R5](40-l3g06-r4-analysis-and-r5-manifest.md)
- [41 — exact language R5 delta и LTM canonical Batch R6](41-l3g06-r5-exact-language-delta-and-r6-ltm.md)
- [42 — анализ LTM R6 и locale-level Batch R7](42-l3g06-r6-analysis-and-r7-ltm-locale.md)
- [R7 — full local locale baseline roots](appendix-ltm-locale-baseline-r7.csv)
- [R7 — exact production/local locale delta](appendix-ltm-locale-delta-r7.csv)
- [43 — corrected R7 и group-level Batch R8](43-l3g06-r7-analysis-and-r8-ltm-group.md)
- [R8 — full local group baseline roots](appendix-ltm-group-baseline-r8.csv)
- [R8 — exact production/local group delta](appendix-ltm-group-delta-r8.csv)
- [44 — R8 result и targeted `header_footer_new` R9](44-l3g06-r8-analysis-and-r9-header-footer-locale.md)
- [R9 — local `header_footer_new` locale baseline](appendix-ltm-header-footer-locale-baseline-r9.csv)
- [R9 — exact production/local locale delta](appendix-ltm-header-footer-locale-delta-r9.csv)
- [45 — R9 result и opaque-key Batch R10](45-l3g06-r9-analysis-and-r10-opaque-key.md)
- [R10 — local opaque-key baseline](appendix-ltm-header-footer-opaque-key-baseline-r10.csv)
- [R10 — exact opaque-key delta](appendix-ltm-header-footer-opaque-key-delta-r10.csv)
- [46 — R10 result и final row-level Batch R11](46-l3g06-r10-analysis-and-r11-final-row-proof.md)
- [R11 — local `header.top_sale` locale baseline](appendix-ltm-header-top-sale-locale-baseline-r11.csv)
- [R11 — exact locale row delta](appendix-ltm-header-top-sale-locale-delta-r11.csv)
- [47 — R11 final proof и PHP-array Batch R12](47-l3g06-r11-final-proof-and-r12-php-array.md)
- [R12 — local PHP-array baseline](appendix-language-php-array-baseline-r12.csv)
- [R12 — exact PHP-array delta](appendix-language-php-array-delta-r12.csv)
- [R12 — account local-only key analysis](appendix-language-account-local-extra-analysis-r12.csv)
- [48 — R12 result и targeted Batch R13](48-l3g06-r12-analysis-and-r13-targeted-files.md)
- [R13 — local header opaque-key baseline](appendix-language-header-opaque-key-baseline-r13.csv)
- [R13 — local RU account group baseline](appendix-language-ru-account-group-baseline-r13.csv)
- [R13 — exact header opaque-key delta](appendix-language-header-opaque-key-delta-r13.csv)
- [R13 — exact RU account group delta](appendix-language-ru-account-group-delta-r13.csv)
- [49 — R13 result и final file Batch R14](49-l3g06-r13-analysis-and-final-r14.md)
- [R14 — local RU account/orders leaf baseline](appendix-language-ru-account-orders-leaf-baseline-r14.csv)
- [R14 — exact production/local RU account/orders delta](appendix-language-ru-account-orders-leaf-delta-r14.csv)
- [50 — R14 final translation reconciliation и следующий owner/UAT gate](50-l3g06-r14-final-translation-reconciliation.md)
- [R14 — 27-row content owner disposition matrix](appendix-language-owner-disposition-matrix-r14.csv)
- [R14 — подтверждённые решения владельца по 27 строкам](appendix-language-owner-decisions-r14.csv)
- [51 — translation owner decision и staging UAT execution pack](51-l3g06-translation-owner-decision-and-staging-uat.md)
- [52 — реестр подтверждённых решений клиента](52-client-decisions-register.md)
- [53 — Filament 5 Implementation Blueprint](53-filament-implementation-blueprint.md)
- [54 — Filament Stage 0 Execution Pack](54-filament-stage0-execution-pack.md)
- [55 — Stage 0 S0-01 evidence/dependency freeze](55-filament-stage0-s01-evidence-freeze.md)
- [56 — Stage 0 S0-02 server provisioning preflight](56-filament-stage0-s02-provisioning-preflight.md)
- [57 — S0-02 preflight result и следующий read-only gate](57-filament-stage0-s02-preflight-result.md)
- [58 — S02-D result, DNS finding и final pre-activation S02-E](58-filament-stage0-s02-d-result-and-e.md)
- [59 — disposable test.viarcanvas.com staging preflight](59-filament-stage0-test-disposable-preflight.md)
- [60 — test preflight result, provider isolation и quarantine](60-filament-stage0-test-preflight-result-and-quarantine.md)
- [61 — clean test root, rollback tree и PHP 8.3 backend](61-filament-stage0-test-clean-root-and-php83.md)
- [62 — offline Laravel 13 / Filament 5 target build result](62-filament-stage0-offline-target-build-result.md)
- [63 — offline server release upload/install без переключения test](63-filament-stage0-offline-server-release-install.md)
- [JSON — offline target build/artifact evidence](appendix-stage0-offline-target-build.json)
- [JSON — exact target versions и gates](stage0-target-versions.json)
- [CSV — hashes legacy locks/build manifests](appendix-stage0-legacy-lock-hashes.csv)
- [CSV — hashes 12 frozen public assets](appendix-stage0-public-asset-hashes.csv)
- [CSV — local/production/target runtime baseline](appendix-stage0-runtime-baseline.csv)
- [CSV — optional Filament plugin decisions](appendix-stage0-plugin-decisions.csv)
- [CSV — S0-02 preflight evidence request](appendix-stage0-s02-preflight-evidence-request.csv)
- [CSV — disposable test staging evidence](appendix-stage0-test-disposable-evidence.csv)
- [CSV — 133 Voyager BREAD → Filament Resources/Pages](appendix-filament-resource-map.csv)
- [CSV — 1 997 BREAD fields → Filament components](appendix-filament-field-map.csv)
- [CSV — 138 admin menu items → Filament navigation](appendix-filament-navigation-map.csv)
- [CSV — 8 legacy roles → Filament panel/resource access](appendix-filament-role-map.csv)
- [CSV — 675 permissions / 2 080 assignments → Filament abilities](appendix-filament-permission-map.csv)
- [CSV — 58 custom admin routes → Filament Pages/Actions/Jobs](appendix-filament-custom-route-map.csv)
- [CSV — 40 Voyager overrides → Filament target surfaces](appendix-filament-custom-view-map.csv)
- [CSV — 14-case translation staging UAT protocol](appendix-language-owner-uat-protocol-after-r14.csv)
- [CSV — exact 25-row production/local language delta](appendix-language-file-delta-r5.csv)
- [CSV — запрос production evidence L3-E01…E22](appendix-production-evidence-request.csv)
- [CSV — все зарегистрированные routes](appendix-routes.csv)
- [CSV — routes приложения без redirect dataset](appendix-application-routes.csv)
- [CSV — все методы и функции `app/`](appendix-functions.csv)
- [CSV — построчная ACL-матрица 58 admin routes](appendix-admin-acl-routes.csv)

Официальные опорные источники: [Laravel 13 release notes](https://laravel.com/docs/13.x/releases), [Laravel 13 upgrade](https://laravel.com/docs/13.x/upgrade), [Filament 5 installation](https://filamentphp.com/docs/5.x/introduction/installation).
