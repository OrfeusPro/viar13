# 15. Общий task checklist

Это исходный технический backlog и справочник зависимостей. Нормализованные уникальные ID, родительские волны, статусы и ссылки собраны в [65-migration-executable-task-catalog.md](65-migration-executable-task-catalog.md), включая задачи, найденные при [полной проверке 124 технических файлов](66-full-document-task-traceability-audit.md). **Актуальная точка остановки, следующая задача и ветка ведутся только в [64-migration-task-control-center.md](64-migration-task-control-center.md).**

Не пытайтесь определять прогресс по отсутствию галочек в этом файле: часть аудита и Stage 0 уже выполнена и отражена в управляющем реестре. `Priority`: Critical/High/Medium/Low; `Size`: S/M/L/XL.

| ID | P | Size | Зависимости | Результат | Проверка |
|---|---|---:|---|---|---|
| AUD-001 | Critical | M | — | production inventory | signed server sheet |
| AUD-002 | Critical | S | — | route registry baseline исправлен/решён | route list/cache |
| AUD-003 | High | M | AUD-001 | DB/files/cron/queue/integration inventory | audit evidence |
| AUD-004 | Critical | M | AUD-001 | live-production write ownership для orders/integrations/translations/content | approved runbook |
| AUD-005 | Critical | L | AUD-002 | route→handler→data/side-effect traceability для 335 app routes; redirect/package coverage отдельно | registries + reviewed flow cards |
| AUD-006 | Critical | M | AUD-005 | anonymous/wrong-role/allowed-role matrix для 58 admin route-level `web` endpoints | staging security test report |
| AUD-007 | High | M | content owner | оба translation storage-контура и все DB/API/filesystem writers | writer/reader/reconciliation manifest |
| AUD-008 | Critical | L | AUD-001/DB access | production metadata-only file/PDF corpus, classification и orphan/reference report | signed counts/hash manifest без PII |
| AUD-009 | Critical | M | AUD-001/provider owner | production metadata-only Venipak methods/prices/points/labels/errors/roles + credential rotation metadata | approved baseline без PII/secrets |
| L3-001 | Critical | M | DevOps/DBA | L3 production topology/effective runtime/config evidence pack | все L3-E01…E22 имеют evidence/owner/status |
| L3-002 | Critical | M | L3-001/DBA | encrypted backup/PITR inventory + isolated restore drill | measured RPO/RTO and count/hash manifest signed |
| L3-003 | Critical | M | L3-001 | scheduler/worker/queue/cache/session ownership baseline | exactly-one scheduler; drain/restart/failover tests pass |
| L3-004 | Critical | M | L3-001 | deployment artifact/health/observability/rollback game day | atomic release and live-write-safe rollback demonstrated |
| TST-001 | Critical | XL | AUD-002 | orders/status/chat characterization | legacy CI green |
| TST-002 | Critical | XL | sandboxes | payment/SA/mail/OAuth contracts | fixture suite |
| TST-003 | High | L | content owner | locale/SEO/redirect manifest | crawl diff |
| TST-004 | Critical | L | AUD-006 | admin ACL/ownership/file negative tests | 401/403/404 matrix green |
| TST-005 | Critical | XL | AUD-005 | differential fixtures public/admin/SA order creation и failure injection | no partial/duplicate order effects |
| TST-006 | Critical | XL | AUD-006/AUD-008 | file/PDF ACL, upload, traversal, rename failpoint, locale golden и SA SSRF suite | zero leak/loss/overwrite/duplicate |
| TST-007 | Critical | XL | AUD-006/AUD-009 | delivery quote/point/XML/ACL/provider-failure/replay/concurrency/courier/print suite | exactly-once; no live calls |
| PHP-001 | Critical | L | dependency matrix | PHP 8.3/8.4 compatibility report | static/runtime scan |
| PHP-002 | High | M | PHP-001 | extensions parity | platform check |
| L7-001 | High | M | TST-001 | applicable 6→7 changes resolved | guide gate |
| L8-001 | High | M | L7-001 | factories/routes/queue parity | test gate |
| L9-001 | Critical | L | L8-001 | Flysystem/Symfony Mailer migration proven | file/mail tests |
| APP-001 | Critical | XL | TST-006 | private artifact/download layer и central upload policy | ownership/MIME/limit suite green |
| APP-002 | Critical | L | APP-001 | two-phase rename/copy journal + after-commit cleanup | every failpoint replay-safe |
| APP-003 | Critical | L | APP-001/content owner | pure PDF renderer, locale/template snapshot и artifact receipt | seven-locale golden corpus |
| L10-001 | High | M | L9-001 | Monolog/raw SQL/test tooling proven | test/log/query gate |
| L11-001 | High | L | L10 knowledge | target skeleton structure | boot/cache gate |
| L12-001 | High | M | L11-001 | Carbon/storage/image rules | contract gate |
| L13-001 | Critical | L | all prior | Laravel 13 shell | full CI |
| VOY-001 | Critical | L | DB access | 133 BREAD manifest | every module classified |
| VOY-002 | Critical | XL | business owner | field/action/translation/upload parity specs | UAT sheets |
| VOY-003 | High | M | VOY-001 | decommission data policy | approved ADR |
| FIL-001 | Critical | L | L13/RBAC | panel/auth/guard | login/security tests |
| FIL-002 | Critical | XL | VOY-002 | role/permission adapter | 8-role matrix |
| FIL-003 | High | XL | FIL-002 | content/catalog Resources | module parity |
| FIL-004 | Critical | XL | INT/APP | order/chat CRM cluster | full CRM UAT |
| FIL-005 | High | XL | storage/jobs | media/SEO/generator pages | custom action UAT |
| DB-001 | Critical | L | restore access | backup/restore drill | measured RTO/RPO |
| DB-002 | Critical | L | DB-001 | counts/orphans/duplicates baseline | signed report |
| DB-003 | High | L | query profiles | additive indexes/schema | EXPLAIN + rollback |
| DB-004 | Critical | L | DB-001/AUD-004 | online backfill, watermark и delta reconciliation | zero loss/duplicate report |
| DB-005 | Critical | L | DB-004/content owner | translation/content insert-update-delete manifest и reconciliation | locale/table/column hash parity |
| INT-001 | Critical | XL | TST-002 | SA inbound/outbox | duplicate/retry suite |
| INT-002 | Critical | L | sandbox | Paysera adapter | signature/replay suite |
| INT-003 | Critical | L | provider decision | PayPal adapter | capture/reconcile suite |
| INT-004 | High | L | workers/SMTP | mail + marketing queues | template/failure suite |
| INT-005 | High | M | provider consoles | Facebook+Google auth | linking suite |
| INT-006 | Critical | XL | Q-B15/Q-B16/TST-007 | typed Venipak client, server quote, directory snapshot, shipment/courier ledger, reprint/void/reconcile | replay/concurrency/error/PDF suite |
| INT-007 | Critical | M | Q-B14/APP-001 | SSRF-safe streamed SA attachment adapter | egress/TLS/limit/duplicate suite |
| AUD-009 | Critical | L | DB read access | FN-09 auth/account/chat static+data inventory | 56 contracts + owner/risk map |
| SEC-009 | Critical | M | AUD-009/business owner | закрыть raw user lookup, auth-only IDOR/admin impersonation и state-changing GET | negative HTTP matrix + access-log review |
| AUTH-001 | Critical | L | SEC-009/Q-A01/Q-A02 | единый standard/custom auth, password/session/verification contract | fixation/throttle/CAPTCHA/reset suite |
| AUTH-002 | Critical | L | provider consoles/Q-A04/Q-A05 | stateful Socialite linking и exact redirect allowlist | collision/link/unlink/recovery suite |
| CHAT-001 | Critical | XL | SEC-009/Q-A03/Q-A06 | actor/source mapping четырёх streams и ownership policies | role×ownership/read parity |
| CHAT-002 | Critical | L | CHAT-001/queue | message idempotency + durable outbox/reconciliation | duplicate/concurrency/failpoint suite |
| AUD-010 | Critical | L | DB/config read access | FN-10 mail/queue/scheduler static+data inventory | 78 contracts + cohort/owner map |
| SEC-010 | Critical | M | AUD-010/DevOps | закрыть public mail previews и включить SMTP TLS verification | anonymous 404/no DB diff + TLS tests |
| MAIL-001 | Critical | XL | SEC-010/business registry | transactional event/outbox/delivery ledger | crash/replay/provider reconciliation |
| MAIL-002 | Critical | L | Legal/Marketing | consent, signed unsubscribe, suppression, bounce/complaint | all marketing templates compliant |
| QUE-001 | Critical | L | production topology | purpose queues + workers/retry/failed/restart/metrics | backlog/poison/rolling-deploy suite |
| CRON-001 | Critical | L | distributed cache/owners | one-server scheduler locks, TTL/timezone/heartbeat/max batch | two-host/crash/DST rehearsal |
| TST-010 | Critical | XL | MAIL/QUE/CRON | seven-locale template and failpoint corpus | zero duplicate/lost/consent violation |
| AUD-011 | Critical | L | DB/config read access | FN-11 public catalogue/generator static+data inventory | 72 contracts + asset/data/locale map |
| SEC-011 | Critical | M | AUD-011 | закрыть public catalogue maintenance GET и item-route 404 | anonymous zero-write + existing-item route suite |
| CAT-001 | Critical | XL | SEC-011/business owners | единый route/data/filter/price/session catalogue contract | seven-locale old/new differential suite |
| CAT-002 | Critical | XL | CAT-001/FILE | canvas/collage/modular/family/portrait generators | start→upload→preview→basket/order parity |
| DB-011 | Critical | L | production copy/content owner | orphan quarantine + catalogue/translation delta migration | counts/hashes/tombstone/rollback rehearsal |
| SEO-011 | High | L | access logs/SEO owner | URL/slug/canonical/sitemap/feed continuity | crawl/schema/redirect diff |
| TST-011 | Critical | XL | CAT/DB/SEO/FE | catalogue golden/browser/security/performance corpus | zero price/locale/asset/SEO regression |
| FE-001 | High | M | L13 | изолированный Vite + Filament Tailwind без public assets | admin build manifest |
| FE-002 | Critical | M | visual harness | 12 public asset outputs заморожены без пересборки | URL/hash/load-order/visual parity |
| DEP-001 | Critical | L | AUD-001 | CI immutable artifacts | clean build |
| DEP-002 | Critical | XL | all Critical | staging/UAT/load/security | go/no-go |
| DEP-003 | Critical | L | DEP-002 | cutover runbook/observability | rehearsal |
| DEP-004 | Critical | L | DEP-003/DB-004/DB-005 | live atomic switch rehearsal | orders/callbacks/translations survive switch |
| RBK-001 | Critical | L | DB-001/DEP | app/assets/worker rollback | timed rehearsal |
| RBK-002 | Critical | L | integrations | provider reconciliation rollback | simulated incident |
| RBK-003 | High | M | stable releases | Voyager read-only/decommission | no runtime usage |

## Filament Stage 0 tasks

| ID | Priority | Hours | Depends | Task | DoD |
|---|---|---:|---|---|---|
| S0-01 | Critical | 12–20 | approved staging | evidence freeze и exact target dependency locks | version/hash/plugin manifest signed |
| S0-02 | Critical | 20–32 | S0-01 | Laravel 13 skeleton, одна Panel `/backoffice-next`, isolated theme | panel boots; public hashes unchanged; no production outbound |
| S0-03 | Critical | 16–28 | S0-02 | user/auth/session compatibility | login/session/cart matrix PASS; no automatic access for zero-permission roles |
| S0-04 | Critical | 28–50 | S0-03 | RBAC adapter, navigation и 8-role simulation | 138 menu/675 permission/2 080 link matrix without escalation |
| S0-05 | Critical | 20–36 | S0-02/DB-005 | translation adapter/versioned publisher spike | R5–R14 hashes + 14-case UAT repeatable; one writer |
| S0-06 | Critical | 24–42 | S0-03/04 | read-only OrderResource и одна safe synthetic Action | ID/data/query/ACL/idempotency/failpoint/rollback PASS |
| S0-07 | Critical | 20–40 | S0-03/04 | chat и legacy-path file adapter spikes | ownership/dedupe/MIME/path/failpoint suite PASS |
| S0-08 | Critical | 12–20 | S0-02…07 | feature flags, receipts, observability и deployment scaffold | write flags independent of UI; alerts/evidence available |
| S0-09 | Critical | 8–12 | S0-01…08 | CI evidence, runbook и Stage 0 sign-off | written GO/CONDITIONAL GO/NO-GO for wave 1 |

Полный порядок, stop conditions и acceptance: `54-filament-stage0-execution-pack.md`.

Текущий факт 17.07.2026: S0-01 evidence/hash/exact core locks собраны; статус `CONDITIONAL GO` только на provisioning isolated staging. До S0-02 остаётся hard gate: dedicated PHP 8.3/8.4 CLI/FPM с одинаковыми extensions и обязательным `ext-intl`, затем `composer check-platform-reqs` без ignore flags. Evidence: `55-filament-stage0-s01-evidence-freeze.md`.

Для S0-02 подготовлен read-only preflight `56-filament-stage0-s02-provisioning-preflight.md` и evidence matrix S02-E01…E17. Статус S0-02 остаётся `NOT STARTED`, пока server output не подтвердит active dev vhost, dedicated FPM, `ext-intl`, public DNS plan и полную DB/Redis/session/storage/provider isolation.

S02-A/B/C result: PHP/dedicated pool/extensions PASS; Hestia domain и Nginx root BLOCKED из-за suspend; storage/env/isolation absent; HTTPS через loopback inconclusive. Выполнить только S02-D из `57-filament-stage0-s02-preflight-result.md` перед backup/unsuspend plan.

S02-D result: origin HTTPS/listener PASS через `65.108.243.50`, Laravel template найден, dev root пуст, disk available 18 GiB. `test.viarcanvas.com` выбран как future staging switch target. TEST-S02-B backup/suspend rehearsal PASS; test восстановлен на `PHP-7_4`, `SUSPENDED: no`. TEST-S02-C — HOLD. Offline Laravel 13/Filament 5 target и artifact PASS по документу 62: exact locks, safety flags, 0 advisories, 6/6 tests, clean extraction rehearsal. Следующий шаг: non-public server release install без переключения active test.

## Правило закрытия

Задача закрывается только с evidence link: commit/PR, test run, artifact, UAT или runbook. Dependency installation или созданный Resource без parity не являются завершением. Все Critical задачи должны быть закрыты либо иметь формально принятый остаточный риск до production.

## FN-12 tasks

| ID | Priority | Size | Depends | Task | DoD |
|---|---|---|---|---|---|
| VOY-002 | Critical | M | AUD-001/VOY-001 | production BREAD/access/translation snapshot | counts/hashes/log usage attached |
| VOY-003 | Critical | L | VOY-002 | 133 signed module decisions + owners | every CSV row classified |
| VOY-004 | Critical | L | AUD-006 | 8-role resource/custom-action matrix | deny-by-default tests pass |
| VOY-005 | Critical | L | DB-005 | translation/content adapter + conflict policy | all locale/source reconciliation passes |
| VOY-006 | High | M | VOY-003 | stale/incomplete route decisions | IDs 27/129/orders routes resolved |
| VOY-007 | Critical | XL | VOY-003/004/005 | Filament waves with specialized Pages | per-module parity/UAT/rollback evidence |
| VOY-008 | High | M | VOY-007 | Voyager read-only/decommission | no writer/runtime dependency after retention |

## FN-13 tasks

| ID | Priority | Size | Depends | Task | DoD |
|---|---|---|---|---|---|
| SEO-001 | Critical | S | SEC owner | закрыть public mutating/debug/preview/test routes | anonymous 404/403; zero DB/file/provider diff |
| SEO-002 | Critical | M | AUD-002 | production redirect/404/crawler/merchant evidence | anonymized 30–90 day pack attached |
| SEO-003 | Critical | M | SEO-002 | signed 256-rule redirect + PL domain map | one target/status/query policy per source |
| SEO-004 | Critical | L | SEO-003 | redirect responder без closures/header-exit | one-hop/cycle/encoded/locale tests pass |
| SEO-005 | High | L | DB-005 | cached sitemap/feed projection | schema/count/canonical/price golden parity |
| SEO-006 | Critical | M | upload/security | verified review submission | MIME/size/ownership/rate/moderation tests pass |
| SEO-007 | Critical | M | AUD-002/SEO-004 | route registry/cache gate | 0 closures/missing handlers; names resolved |
| SEO-008 | Critical | M | SEO-003/005/007 | SEO/feed canary + rollback rehearsal | owner sign-off and monitored rollback evidence |

Статус `SEO-001` на 14.07.2026: аудит и проект containment завершены, но application code не изменён. Временный прототип был удалён после уточнения scope. Задача **открыта** до реализации, повторных tests, deployment/ENV и zero-side-effect evidence.

Статус `SEO-006` на 14.07.2026: реальные формы, поля и риски прослежены; защитный контракт спроектирован, но не внедрён. Задача **открыта**: auth-bound ownership, MIME/size/WebM, rate limit, cleanup, escaped render, private quarantine/promotion, AV/metadata/dimensions, translated UX, duplicate/retention policy и production evidence должны быть реализованы отдельным этапом.

## L3-G06 cutover tasks

| ID | Priority | Size | Depends | Task | DoD |
|---|---|---|---|---|---|
| CUT-001 | Critical | M | Business/Tech owners | подписать capability+field/action write-owner matrix | каждый public/admin/SA/payment/chat writer имеет одну фазу и одного owner |
| CUT-002 | Critical | L | DB-001/DB-002 | canonical PK/hash/delete/file reconciliation tooling | full restored-snapshot diff находит insert/update/delete/orphan без PII output |
| CUT-003 | Critical | L | CHAT-001/CHAT-002 | client message ID, unique claim и outbox receipt | duplicate/concurrency/crash tests дают одну row и один side effect |
| CUT-004 | Critical | XL | INT-002/INT-003/RBK-002 | payment attempt/receipt/reconciliation ledger | amount/currency/reference/unknown outcome и replay проходят sandbox suite |
| CUT-005 | Critical | M | CUT-001/CUT-002 | low-risk internal-note component rehearsal | timed cutover+forward rollback без lost/duplicate rows |
| CUT-006 | Critical | XL | CUT-003/CUT-004/L3-G01…G05 | orders/status/chat/payment game day | owner sign-off; alerts and stop triggers work; live writes preserved |
| CUT-007 | Critical | L | CUT-001/CUT-002 | production exact translation inventory для base/`translations`/`ltm_translations`/PHP files | key/hash/file manifests, duplicates/NULL/deletes и `ee|et|cn|jp|uk` mapping подписаны |
| CUT-008 | Critical | L | CUT-007/ACL-001 | единый versioned PHP dictionary publisher вместо двух live writers | deny-by-default ACL; PHP lint/SHA-256; publish/rollback receipt; live overwrite исключён |
| CUT-009 | Critical | XL | CUT-002/CUT-007 | per-module content+translation reconciliation tooling | parent/base/translation/pivot/media/SEO full key/hash/anti-join на restored staging |
| CUT-010 | Critical | L | CAT-001/CUT-009 | catalogue price/relation/media characterization corpus | страны/локали/sizes/options и известные orphan cohorts имеют signed expected result |
| CUT-011 | Critical | L | SEO-001/CUT-009 | URL/redirect/canonical/hreflang/sitemap/feed regression artifact | ноль unexplained 404/loop/collision; все public locales покрыты |
| CUT-012 | Critical | XL | CUT-008/CUT-009/CUT-010/CUT-011/L3-G01…G05 | catalogue/content/translation game day | один writer на capability; frozen assets; forward rollback сохраняет новые edits/files |
