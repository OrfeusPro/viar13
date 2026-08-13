# 14. Технические execution gates и исторический реестр вопросов

Статус на 17.07.2026: общий клиентский опрос завершён, ответы 1–68 находятся в `52-client-decisions-register.md`. Разделы ниже сохраняются как исторический журнал исходных неизвестных и не должны отправляться клиенту как актуальный список вопросов.

Текущие незакрытые пункты являются техническими gates конкретных этапов:

1. зафиксировать точные Composer/Node locks Laravel 13 / Filament 5 / Livewire 4 target;
2. выполнить FIL-SPK-01…08 из `53-filament-implementation-blueprint.md`;
3. сверить 1 997 BREAD fields с casts/accessors/mutators и DB constraints;
4. получить production usage evidence для двух stale BREAD и одного unresolved custom navigation item;
5. получить stage-0 sign-off на замену BREAD/Database/Compass reviewed migrations/dev tooling вместо production editors;
6. подписывать UAT/go-no-go и конкретный runbook перед каждой волной.

Неизвестные данные не заменяются предположениями. Fallback применяется только для проектирования/staging, не как разрешение production cutover.

## Исторические исходные блокирующие вопросы

| ID | Вопрос / владелец | Почему блокирует | Рабочий fallback |
|---|---|---|---|
| Q-B01 | Production PHP CLI/FPM, extensions, MariaDB, web server? DevOps | целевая платформа/совместимость | отдельная PHP 8.3/8.4 staging, release не назначать |
| Q-B02 | Где cron/workers/Supervisor и каков `QUEUE_CONNECTION` production? DevOps | mail/webhook/jobs delivery | проектировать managed workers, не считать local `sync` production |
| Q-B03 | Как делаются backup и когда последний успешный restore? DevOps | rollback невозможен без drill | обязательный restore rehearsal |
| Q-B04 | Реальные Paysera/PayPal contracts, sandbox accounts, callback methods и provider reconciliation? Finance/Dev | amount/receipt/retry нельзя доказать локальным кодом | не переключать payment adapters |
| Q-B05 | Synvolve/SA production volumes/payload variants/retry rules/IP allowlist? CRM owner | local SA tables пусты | anonymized stats + fixture corpus |
| Q-B06 | Кто утверждает 133 BREAD и 8-role permission parity? Business owner | Filament DoD | назначить владельцев по clusters |
| Q-B07 | Срок допустимой параллельной работы Voyager/Filament и write ownership? Product | lost updates/cutover | по одному write-owner на module |
| Q-B08 | Политика public locales: сохраняем семь; нужен ли `uk`, что делать с 6 `et` rows? Content/SEO | URL/data model | семь locale без изменений; `uk` не включать; `et` quarantine audit |
| Q-B09 | Какое максимальное окно ограничения writes допустимо при cutover работающего магазина и при передаче отдельных переводимых модулей? Business/DevOps/Content | заказы и изменения переводов продолжают поступать | проектировать zero/near-zero downtime; legacy остаётся system of record; пауза только на конкретный Resource |
| Q-B10 | Какие из 58 `admin/*` routes с route-level `web` фактически доступны anonymous/wrong-role и какие роли должны их вызывать? Security/Business | возможны операции с заказами, файлами, Venipak, email и SA без единообразной route-защиты | немедленная staging ACL matrix; до результата deny-by-default в target |
| Q-B11 | Когда partial `order_payment_request` меняет balance и `orders.payment_status`, как определяются overpayment/refund/expiry? Finance/Operations | сейчас request paid не агрегируется в order, несколько requests разрешены | вести отдельный ledger; не менять parent status автоматически |
| Q-B12 | Какие side effects обязаны выполняться ровно один раз на переход оплаты: bonus, friend bonus, gift-card coupon/PDF, mail, Synvolve? Finance/Marketing/CRM | текущие handlers выполняют разные наборы и допускают повтор | frozen side-effect matrix + unique outbox event |
| Q-B13 | Какие order/customer/PDF/SA файлы обязаны быть private, кто может скачивать и каков retention/legal hold? Security/Operations/DPO | сейчас private data смешана с public disks и predictable URLs | считать все order/PDF/SA private; выдавать только через owner/role Policy |
| Q-B14 | Какие hosts/schemes разрешены для SA attachment source URL и может ли SA передавать signed upload вместо server-side fetch? CRM/Security | текущий downloader допускает SSRF/MITM и читает body до size check | временно HTTPS allowlist + egress proxy; attachments не включать без L3 tests |
| Q-B15 | Каков утверждённый Venipak contract: sandbox/live hosts, supported country/method/postcode/package limits, stable pickup ID, duplicate semantics, void/courier cancel и reconciliation? Logistics/Provider | нельзя безопасно реализовать retry/idempotency и исторический point mapping | legacy остаётся sole writer; target только fake/shadow |
| Q-B16 | Кто имеет право вызывать courier/create/reprint/void и кто принимает delivery price/point parity? Logistics/Finance/Security | текущие три admin routes не имеют auth/role; browser задаёт цену | deny-by-default; owner matrix до target write |

## Важные

- Q-H01: назначение `Mail::getSwiftMailer()->getTransport()` в `AppServiceProvider` и ожидаемый transport behavior?
- Q-H02: что должен делать отсутствующий `ImageController` route и используется ли URL внешне?
- Q-H03: являются ли `App\Models\Canva` и `ACollagFaq` опечатками/удалёнными сущностями или живыми BREAD?
- Q-H04: English хранится в base fields для всех translatable tables или есть исключения?
- Q-H05: разрешены ли rich HTML/SVG/file types в media и кто утверждает security tightening?
- Q-H06: где хранятся Venipak credentials production и можно ли перенести в secret manager?
- Q-H07: какой supported PayPal Server SDK/REST adapter утверждается для target и как переносится без изменения capture contract?
- Q-H08: допустимые order status transitions и side effects — есть ли утверждённая state diagram?
- Q-H09: какие mailings являются marketing, какие transactional; consent/bounce/retention policy?
- Q-H10: какие public/Admin performance SLO и пиковая нагрузка?
- Q-H11: есть ли CDN/object storage и shared filesystem между instances?
- Q-H12: production DB содержит triggers/routines/events, которых нет локально?
- Q-H13: можно ли target Laravel 13 на первом cutover подключить к той же production БД, или инфраструктура требует отдельной БД/кластера?
- Q-H14: есть ли binlog/replication/PITR, позволяющие восстановить БД без потери заказов после backup?
- Q-H15: кто редактирует переводы во время миграции, какие модули меняются ежедневно и можно ли переключать их write-owner по волнам?
- Q-H16: есть ли audit/history удалений и изменений `translations`, base English fields, slugs и SEO metadata?
- Q-H17: используются ли публичные `mail/1..7` preview и Synvolve test receiver routes вне local/testing?
- Q-H18: являются ли PHP dictionaries `resources/lang/*` редактируемым production-контентом и как deploy сохраняет изменения, сделанные через `AdminLocaleController`?
- Q-H19: какие из трёх order creation paths (public/admin/SA) намеренно отличаются по email, bonus/coupon, status и CRM side effects?
- Q-H20: `sale_price`, `sale_eur` и `sale_percent` являются накопительными или взаимоисключающими; допустим ли payable subtotal = 0?
- Q-H21: какие реальные MIME/размер/count/pixel/page/duration нужны для customer, painter, admin, TinyMCE, audio, PSD/FIG/HEIC/PDF?
- Q-H22: нужен ли raw SVG как производственный artifact; допустим ли только sanitized download-only SVG?
- Q-H23: locale счета фиксируется на order/artifact или всегда следует текущему `users.pdf_locale`; кто утверждает `ee→et` и fallback?
- Q-H24: нужно ли хранить старые версии invoice/gift-card PDF или допустим overwrite; какой artifact является юридически значимым?
- Q-H25: можно ли немедленно ротировать все Venipak credentials, которые присутствовали в tracked comments/default migration и plaintext DB backups?
- Q-H26: какой срок freshness допускается для cached pickup directory и что показывать checkout при outage — stale points, только home delivery или stop?
- Q-H27: нужно ли backfill stable provider point ID для 4 072 legacy `venipak` orders, или хранить их как unverifiable text snapshot?

## Сервер/безопасность

- Кто управляет DNS/TLS/WAF/CDN и rollback TTL?
- Нужен ли zero-downtime; допустимое maintenance window; RTO/RPO?
- Где APM/log aggregation и какова retention/redaction?
- Есть ли Redis и можно ли использовать для cache/session/queue/locks?
- Как анонимизировать staging DB/files и ограничить outbound traffic?
- Нужны antivirus/DLP для customer attachments и срок хранения? До решения fallback: quarantine + private storage, без автоматического удаления.

## Бизнес и UX

- Какие Voyager modules реально используются и какие можно formally retire?
- Нужны два Panels или один Admin с CRM Cluster?
- Нужны импорт/export/bulk actions; кто имеет право?
- Как должен работать temporary lead и когда он связывается с order?
- Какие locale/role/content smoke cases подписывает каждый владелец?

## Необязательные/после паритета

- нормализация legacy status spelling;
- native PHP enums вместо package enums;
- новая normalized translation schema;
- перенос media в object storage;
- redesign публичного frontend;
- удаление Voyager metadata tables.

Эти пункты не следует включать в первую миграцию: они расширяют scope и усложняют rollback.

## FN-09: решения владельцев

- Q-A01: остаётся ли self-registration; какой из standard/custom contracts является единственным и где обязательны CAPTCHA/throttle?
- Q-A02: требуется ли email verification для новых/существующих users и как мигрировать 23 801 локальных rows с нулём verified timestamps?
- Q-A03: какие роли могут читать/писать каждый из четырёх chat streams; кто является actor для исторических rows без attribution?
- Q-A04: допускается ли Socialite auto-link по совпавшему email или требуется вход в local account и explicit confirmation?
- Q-A05: какие provider email claims считаются verified; как обрабатываются collision, unlink, provider loss и account recovery?
- Q-A06: каков retention/legal статус orphan chat rows, message text, attachments и security logs?
- Q-A07: является ли Synvolve/SA обязательным synchronous outcome для chat message или durable asynchronous outbox?
- Q-A08: какое окно совместимости sessions/remember tokens допустимо при переключении и нужно ли принудительно завершить все старые sessions?

## FN-10: решения владельцев

- Q-M01: какой SMTP/provider используется production, обязательна ли custom EHLO/localDomain и почему ранее отключили TLS verification?
- Q-M02: где и на скольких hosts запущены cron/workers; какой queue/cache driver, Supervisor config и restart policy?
- Q-M03: какие из 33 Mailables/3 Notifications transactional, marketing или operational; кто утверждает каждый template?
- Q-M04: какой lawful basis/consent history, suppression, unsubscribe SLA, bounce/complaint и re-subscribe policy?
- Q-M05: можно ли до отдельного одобрения включать overdue command; какой max batch и holiday/business-day rule?
- Q-M06: удалять ли abandoned carts старше 7 дней независимо от delivery state; каков retention/legal hold?
- Q-M07: какие provider delivery states считаются достаточными и как reconciled `accepted-but-unknown`?
- Q-M08: какие внешние mail assets разрешены; нужен ли self-hosted/CDN snapshot и image privacy policy?
- Q-M09: какое окно совместимости serialized jobs допускается при rolling deploy и rollback?

## FN-11: решения владельцев public catalogue

- Q-C01: какой primary category/canonical URL должен иметь item с несколькими categories; нужна ли category в item route?
- Q-C02: кто и зачем использует `/set_sizes|set_genre|set_style`; можно ли немедленно убрать их из public routing?
- Q-C03: какие old/new gallery/portrait/constructor URLs реально используются людьми, рекламой, feeds и ботами?
- Q-C04: являются ли `ee` и закомментированный `et` разными data contracts; что делать с 6 DB rows `et`?
- Q-C05: какой fallback обязателен для public locales и для SA `uk`, если перевода нет?
- Q-C06: какой source/version является authority для display/cart/order/SA price и country multiplier?
- Q-C07: какие orphan pivot rows можно quarantine/delete, кто утверждает retention и production reconciliation?
- Q-C08: какие DB fields являются trusted rich HTML, а какие должны быть plain/attribute/JSON encoded?
- Q-C09: разрешены ли hardcoded production/CDN/Google Fonts dependencies и какая CSP/self-host policy?
- Q-C10: как часто content managers меняют каталог/переводы и какое component write-freeze окно допустимо?

## FN-12 вопросы владельцам

- Q-V01: какое решение `migrate|custom|archive|repair|retire` принято по каждой из 133 строк?
- Q-V02: используются ли production BREAD IDs 27 `canvas` и 129 `a-collag-faq`, кто владеет их legacy translations?
- Q-V03: какие из 52 custom admin routes реально используются и какие roles/actions разрешены?
- Q-V04: нужен ли production editor PHP `resources/lang` или он должен быть retired/deploy-only?
- Q-V05: кто утверждает mapping 675 permissions/2 080 assignments и custom action abilities?
- Q-V06: допустимо ли полностью отключить BREAD builder/Database/Compass и unrestricted media tools?
- Q-V07: какое окно write gate и observation/rollback требуется для каждой Filament wave?

## FN-13 вопросы владельцам

- Q-S01: должен ли каждый `/pl/*` сохранять path/query на `viar-art.pl`, и кто владеет польским sitemap/canonical?
- Q-S02: какие conflicting `/blog/-12|-14|-4|-9` targets являются правильными?
- Q-S03: какие sitemap/feed URLs и merchant fields являются внешним контрактом; есть ли SLA по размеру/обновлению?
- Q-S04: можно ли немедленно закрыть public translation/meta/mail/debug/gift-card/test endpoints на production?
- Q-S05: кто имеет право запускать translation/meta batch, какой dry-run/approval/rollback обязателен?
- Q-S06: review разрешён anonymous или только по signed order token; какие MIME/size/retention/moderation правила?
- Q-S07: static configured или live Google Places rating является source-of-truth для UI/schema?
- Q-S08: какие legacy redirect sources реально посещались за 30–90 дней и сколько 404/loops сейчас?

## L3-01: production readiness

- Q-P01: какова фактическая production topology: LB/proxy, web/CLI/workers, DB/replica, Redis, storage и сколько hosts каждого типа?
- Q-P02: кто владеет deployment, scheduler и каждым queue worker; где хранятся versioned manifests/runbooks?
- Q-P03: какие effective `QUEUE_CONNECTION`, cache/session drivers, TTL/cookie/locking settings используются после `config:cache`?
- Q-P04: какой scheduler host запускает пять команд, в какой timezone, где видны последние success/failure/overlap и alerts?
- Q-P05: включены ли production binlog/GTID/replica/PITR, каковы RPO/RTO и когда последний раз выполнялся изолированный restore drill?
- Q-P06: где находятся public/private files и PDF, как создаётся storage link/mount, каковы permissions/free space/growth/backup/retention?
- Q-P07: где агрегируются logs/metrics, как выполняются rotation/redaction/retention, какие alert thresholds и incident owners?
- Q-P08: как собирается immutable release artifact, какие cache/migration/health steps и как rollback сохраняет новые live orders/payments/messages/translations?
- Q-P09: какой единый UTC/business-time contract принят для PHP/web/CLI/workers/DB и как тестируется DST?
- Q-P10: можно ли исключить из release `.tmp_order_dump.php` и `modal.blade_backup.php` после owner/secret/PII review?
- Q-P11: каковы effective proxy/PHP/app/DB body/file limits; допустим ли текущий разрыв 150 MiB PHP против 16 MiB DB packet локально?
- Q-P12: кто утверждает TLS/HSTS/trusted-proxy и 256-rule locale/domain redirect contract при новом deployment?
