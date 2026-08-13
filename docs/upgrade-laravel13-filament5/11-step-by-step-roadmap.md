# 11. Исполнимый пошаговый roadmap

Каждый шаг — отдельный PR/набор малых commits. Команды выполняются только на ветке/staging после утверждения, не в рамках аудита.

| ID | Цель / действия и файлы | Зависимости / риск | Тест и DoD | Rollback / commit |
|---|---|---|---|---|
| AUD-01 | production inventory, versions, cron/workers/storage/backups | доступ DevOps; Critical | подписанный inventory | docs-only; `docs: record production baseline` |
| AUD-01A | зафиксировать непрерывный production traffic и cutover ownership: orders/payments/chat/webhooks/translations/content | AUD-01; Critical | утверждённые system-of-record и write-owner | docs/runbook; `docs: define live migration boundaries` |
| AUD-01B | ревью 335 app routes по реестру: auth, validation, data, side effects, target/retire | route/function registries; Critical | каждая строка имеет owner/action/evidence | docs-only; `docs: trace application routes` |
| AUD-01C | staging ACL test для 58 `admin/*` routes с route-level `web` | AUD-01B; Critical | anonymous/wrong-role/allowed-role matrix | немедленно закрыть проблемный route; `test: lock admin route authorization` |
| AUD-01D | production metadata-only file/PDF corpus и public/private/temporary/generated classification | AUD-01; Critical | path/bytes/MIME/hash/reference/orphan manifest без PII | docs-only; `docs: record file artifact baseline` |
| AUD-01E | production metadata-only delivery/Venipak reconciliation: methods/prices/points/labels/errors/roles без PII/secret values | AUD-01; Critical | approved baseline + logistics/finance/security owners | docs-only; `docs: record delivery provider baseline` |
| AUD-02 | устранить baseline route defect `routes/web.php`, отсутствующий controller | owner route; High | `route:list`/cache зелёные | revert; `fix: restore image route registration` |
| TST-01 | characterization orders/chat/status in `tests/Feature` | stable fixtures; Critical | legacy tests green | revert tests only; `test: capture order invariants` |
| TST-01A | differential public/admin/SA order create + failure injection | TST-01/AUD-01B; Critical | нет partial/duplicate side effects | revert tests only; `test: characterize order creation channels` |
| TST-02 | payment/mail/OAuth/locale route tests | sandboxes; Critical | contract corpus green | fixtures remain; `test: capture external contracts` |
| TST-02A | files/PDF characterization: ACL, traversal/upload, rename failpoints, locale goldens, SA SSRF | AUD-01C/AUD-01D; Critical | zero leak/loss/overwrite, replay safe | tests only; `test: characterize file and pdf contracts` |
| TST-02B | Venipak/delivery characterization: quote tamper, point snapshot, XML, ACL, provider failures, replay/concurrency, courier/print | AUD-01C/AUD-01E; Critical | exactly-once attempts; no live calls | tests only; `test: characterize delivery and venipak contracts` |
| DEP-01 | CI + anonymized staging + restore drill | DevOps; Critical | RTO/RPO measured | discard staging; `ci: add legacy baseline pipeline` |
| PHP-01 | PHP 8.3/8.4 compatibility scan branch | package matrix; High | no fatal/deprecations in target tests | delete branch; `chore: prepare php 8 compatibility` |
| L7-01 | diagnostic Laravel 7 lab | TST-01 | guide checklist green | tag L6; `chore: lab laravel 7` |
| L8-01 | diagnostic Laravel 8 lab | L7 | factories/routes/queue/pagination | tag L7 |
| L9-01 | diagnostic Laravel 9 lab | L8 | Flysystem + Symfony Mailer | tag L8 |
| L10-01 | diagnostic Laravel 10 lab | L9 | Monolog/raw SQL/PHPUnit | tag L9 |
| L11-13 | fresh Laravel 13 skeleton, providers/config/routes | PHP/CI/tests | boot/cache/routes/tests | previous artifact; `feat: establish laravel 13 shell` |
| APP-01 | migrate models/base casts/date serialization | skeleton | model snapshot parity | revert module |
| APP-02 | migrate public routes/controllers/Blade by feature | APP-01 | route/visual/SEO parity | traffic flag |
| APP-03 | central upload/private artifact layer + two-phase rename journal | TST-02A | ownership/MIME/limit/failpoint green | legacy file adapter flag |
| APP-04 | pure invoice/gift-card renderer + locale/template/artifact receipt | APP-03/content owner | seven-locale golden PDF + atomic publish | legacy renderer flag |
| DB-01 | additive target indexes/schema adapters | restore drill | counts/explain/rollback rehearsed | down/forward fix |
| DB-02 | online backfill + watermark + delta reconciliation для новых/изменённых заказов | DB-01, production metrics | zero loss/duplicate report | остановить batch; продолжить legacy writes |
| DB-03 | translation/content manifest + incremental reconciliation insert/update/delete/base fields | DB-01, content owner | locale/table/column counts и hashes совпадают | Voyager остаётся write-owner |
| INT-01 | SA inbound/outbound adapters + outbox | contract tests | duplicate/retry/redaction | legacy adapter flag |
| INT-01A | SSRF-safe streamed SA attachment adapter | APP-03/CRM host policy | egress/TLS/size/MIME/duplicate suite | attachments feature flag |
| INT-02 | Paysera adapter/receipt idempotency | sandbox | signature/replay/concurrency | legacy endpoint |
| INT-03 | PayPal official adapter | provider decision | capture/replay/reconciliation | legacy adapter |
| INT-04 | Mail/Symfony + queues | SMTP sink/workers | all templates/failure retry | sync/legacy release |
| INT-05 | Facebook + Google Socialite | provider consoles | linking/no-email/callback | legacy auth route |
| INT-06 | Venipak typed client, secrets/host gate, quote/directory snapshot, shipment+courier ledger, label reprint/void | AUD-01E/TST-02B; sandbox only | pickup/price/XML/error/concurrency/reconcile/PDF suite | legacy controller write-owner flag |
| FIL-01 | Filament panel/auth/RBAC adapter | users/permissions | 8-role matrix | panel off |
| FIL-02 | simple reference/content resources | FIL-01 | per-resource parity | Voyager write owner |
| FIL-03 | translated catalogue/blog/gallery | translation adapter | counts/URLs/uploads | Voyager write owner |
| FIL-04 | orders/chat/payment/SA/Venipak cluster | all INT steps | full CRM UAT | route back to Voyager |
| FIL-05 | SEO/media/generators/marketing | jobs/storage | custom action UAT | module flags |
| FE-01 | отдельный Vite/Tailwind 4.1 pipeline только для Filament | skeleton | admin asset manifest/CSP | отключить Filament theme artifact |
| FE-02 | freeze 9 public asset groups без пересборки | visual harness + hashes | URL/hash/mobile parity | вернуть точный legacy asset snapshot |
| DEP-02 | load/security/UAT/cutover rehearsal | all modules | go/no-go signed | restore previous release |
| DEP-02A | live cutover rehearsal с orders, in-flight queue/callback и одновременным изменением перевода | DEP-02/DB-03 | orders/payments/chat/translations проходят switch | application rollback к актуальной DB |
| VOY-01 | freeze writes, read-only window, remove package later | 2 stable releases | no access/log calls | re-enable legacy release |

## Детализация обязательного шаблона шага

Для каждого ID в issue должны быть: цель; preconditions; точный file list; команды; migration/data impact; dependencies; risks; automated/manual tests; metrics; Definition of Done; rollback command/owner; recommended commit. Нельзя закрывать шаг только потому, что dependency resolution прошёл.

## Критический путь

`AUD/TST/DEP baseline → target skeleton → domain/public site → integrations → RBAC → simple Filament → CRM Filament → freeze public assets + isolated Filament theme → UAT/cutover → Voyager decommission`.

Filament CRM нельзя начинать как замену существующих controllers до characterization. Voyager package нельзя удалять, пока все 133 модуля не классифицированы как migrated/retired-by-business/read-only archive.

## FN-09 work package перед auth/chat migration

1. Немедленно локализовать R-56/R-58/R-64: закрыть public raw lookup, admin-message impersonation и state-changing utility routes после согласованного hotfix окна.
2. Зафиксировать production-safe aggregates users/sessions/resets и четырёх chat streams без PII/text; утвердить retention/orphan policy.
3. Написать characterization tests standard/custom auth, password mail, session rotation и role×ownership для каждой mutation.
4. Утвердить OAuth linking ADR и provider console redirects; создать collision/recovery fixtures.
5. Ввести additive message identity/outbox и actor/source mapping в shadow mode; legacy остаётся writer.
6. Провести staging differential/UAT, затем отдельно переключить login, OAuth и каждый chat writer с rollback owner.

## FN-10 work package перед mail/queue migration

1. Закрыть public mail preview side effects и включить SMTP TLS verification после provider check.
2. Получить production cron/worker/cache/provider inventory и last-run/backlog/bounce metadata без PII.
3. Утвердить registry 33 Mailables/3 Notifications: transactional или marketing, owner, locale, consent, template version.
4. Ввести additive event/outbox/delivery receipts и signed suppression; legacy dispatch остаётся owner.
5. Разделить purpose queues и настроить worker timeout/retry/backoff/failed/restart/metrics.
6. Перевести scheduled commands на distributed one-server locks, dry-run/max-batch/heartbeat.
7. Пройти crash/concurrency/provider/locale fixtures и переключать каждую mail/scheduler волну отдельно.

## FN-11 work package перед public catalogue migration

1. Закрыть public `/set_sizes|set_genre|set_style` и восстановить полный route registry.
2. Утвердить primary-category/canonical contract и отдельным hotfix исправить новую item route 404.
3. Получить production traffic/catalog/translation/query-plan metadata без PII/content.
4. Зафиксировать golden matrix семи locales, old/new URLs, filters, prices, generators, basket/order и SA API.
5. Перенести catalogue/translations additive с delta/tombstones и orphan quarantine, legacy writer сохраняется.
6. Скопировать 12 public bundles byte-identically; Vite/rebuild не выполнять.
7. Переключать gallery/canvas/collage/modular/portrait components отдельно после parity и rollback rehearsal.

## FN-12 ближайшие действия

1. Получить production snapshot и access logs по 133 BREAD/custom routes.
2. Подписать для каждой строки решение `Resource|custom Page|archive|repair|retire`, ACL/UAT/rollback owner.
3. Закрыть admin perimeter и writes через GET/ANY до переноса.
4. Реализовать translation/content adapter и optimistic locking без потери metadata/PHP dictionaries.
5. Переносить simple → localized → roles/settings → blog/gallery/SEO/media → orders/integrations → rare modules.
6. Отключать Voyager только после per-wave parity, one-writer rehearsal и retention window.

## FN-13 ближайшие действия

1. P0 закрыть public `/translate_item`, `/set_meta`, mail/debug/gift-card/test routes и усилить review upload.
2. Получить 30–90 day anonymized redirect/404/sitemap/feed logs, Search Console и merchant diagnostics.
3. Подписать 256-rule redirect map, PL domain/path/query policy и 47 auxiliary route dispositions.
4. Устранить missing handler/duplicate names/closures; добиться fresh route boot и cache parity.
5. Зафиксировать XML/feed/price golden corpus на свежем data watermark без frontend rebuild.
6. Реализовать shadow projection и выполнить отдельный SEO/feed canary + rollback rehearsal.
