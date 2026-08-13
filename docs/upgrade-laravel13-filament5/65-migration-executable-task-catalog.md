# 65. Полный исполняемый каталог задач миграции

Этот каталог нормализует технический backlog из [15-task-checklist.md](15-task-checklist.md) и самостоятельные работы, выявленные [полным аудитом 124 технических файлов](66-full-document-task-traceability-audit.md). Текущая точка остановки и единственная следующая задача ведутся в [64-migration-task-control-center.md](64-migration-task-control-center.md).

```text
CATALOG_VERSION: 25
UPDATED_AT: 2026-07-27
TASK_COUNT: 132
READY_TASK: NONE
STATUS_COUNTS: BACKLOG=91; CANCELLED=6; DONE=16; HOLD=4; IN_PROGRESS=1; READY=0; VERIFY=7; WAITING=8
```

## 1. Правила использования

- Это полный список исполняемых задач, а не календарный график. Оценки разных строк могут пересекаться и не суммируются автоматически.
- Брать в работу можно только задачу со статусом `READY`, указанную как `NEXT_TASK` в документе 64.
- Перед началом статус меняется на `IN_PROGRESS`, указывается ветка и создаётся карточка из [шаблона](tasks/TEMPLATE.md), если задача длится более четырёх часов или затрагивает сервер, БД, платежи, файлы либо production.
- `DONE` означает наличие evidence и выполненный DoD. `VERIFY` означает, что материалы есть, но формальная проверка/подпись не завершена. `WAITING` требует внешнего решения или доступа.
- После изменения статуса обновляются документы 64, карточка задачи и хронологический журнал.
- Конечная цель — полная функциональная замена production. `read-only` в Stage 0 является временным safety-режимом, но не исключает из scope последующий перенос create/update/delete/actions и всех транзакционных сценариев.
- Target writers разрабатываются и тестируются на отдельной local/staging БД с sandbox/sink интеграциями. До финального cutover единственным production write-owner остаётся legacy Laravel 6.
- Для frontend-сценариев текущий метод — сначала полный перенос legacy-функционала и side effects, затем Laravel 13 refactor/UX-улучшения отдельными решениями после parity baseline.

## 2. Нормализация старых ID

| Старый ID | Решение | Причина |
|---|---|---|
| второй `AUD-009` | `AUD-012` | конфликт с Venipak-аудитом; это auth/account/chat audit |
| второй `VOY-002` | `VOY-009` | production BREAD snapshot отделён от parity specification |
| второй `VOY-003` | `VOY-010` | 133 module decisions отделены от decommission policy |
| `S0-02` | `S0-02A…S0-02D` | локальная сборка, server install, isolated config и preview являются разными gates |
| `DB-001` | объединён с `L3-002` | обе строки описывали один backup/restore drill; зависимости перенаправлены |

## 3. Обоснование начальных статусов

Статусы выставлены консервативно по evidence на 21.07.2026. Выполненными отмечены только подтверждённые аудиты, локальный target build и translation baseline. Самостоятельные задачи последовательного Laravel 6→7→…→13 помечены `CANCELLED`: выбран чистый Laravel 13 skeleton, а применимые breaking changes проверяются через characterization/differential tests.

## 4. Предварительные аудиты и production readiness

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| AUD-001 | `VERIFY` | Critical | M | production inventory | — | signed server sheet | [33](33-production-readiness-audit.md) |
| AUD-002 | `BACKLOG` | Critical | S | route registry baseline исправлен/решён | — | route list/cache | [17](17-route-inventory.md) |
| AUD-003 | `DONE` | High | M | DB/files/cron/queue/integration inventory | AUD-001 | audit evidence | [33](33-production-readiness-audit.md) |
| AUD-004 | `DONE` | Critical | M | live-production write ownership для orders/integrations/translations/content | AUD-001 | approved runbook | [34](34-l3g06-write-owner-delta-cutover.md) |
| AUD-005 | `DONE` | Critical | L | route→handler→data/side-effect traceability для 335 app routes; redirect/package coverage отдельно | AUD-002 | registries + reviewed flow cards | [17](17-route-inventory.md) |
| AUD-006 | `BACKLOG` | Critical | M | anonymous/wrong-role/allowed-role matrix для 58 admin route-level `web` endpoints | AUD-005 | staging security test report | [21](21-admin-acl-static-audit.md) |
| AUD-007 | `DONE` | High | M | оба translation storage-контура и все DB/API/filesystem writers | content owner | writer/reader/reconciliation manifest | [50](50-l3g06-r14-final-translation-reconciliation.md) |
| AUD-008 | `WAITING` | Critical | L | production metadata-only file/PDF corpus, classification и orphan/reference report | AUD-001/DB access | signed counts/hash manifest без PII | [24](24-files-pdf-static-audit.md) |
| AUD-009 | `WAITING` | Critical | M | production metadata-only Venipak methods/prices/points/labels/errors/roles + credential rotation metadata | AUD-001/provider owner | approved baseline без PII/secrets | [25](25-venipak-delivery-static-audit.md) |
| L3-001 | `WAITING` | Critical | M | L3 production topology/effective runtime/config evidence pack | DevOps/DBA | все L3-E01…E22 имеют evidence/owner/status | [33](33-production-readiness-audit.md) |
| L3-002 | `WAITING` | Critical | M | encrypted backup/PITR inventory + isolated restore drill | L3-001/DBA | measured RPO/RTO and count/hash manifest signed | [33](33-production-readiness-audit.md) |
| L3-003 | `WAITING` | Critical | M | scheduler/worker/queue/cache/session ownership baseline | L3-001 | exactly-one scheduler; drain/restart/failover tests pass | [33](33-production-readiness-audit.md) |
| L3-004 | `BACKLOG` | Critical | M | deployment artifact/health/observability/rollback game day | L3-001 | atomic release and live-write-safe rollback demonstrated | [33](33-production-readiness-audit.md) |
| AUD-012 | `DONE` | Critical | L | FN-09 auth/account/chat static+data inventory | DB read access | 56 contracts + owner/risk map | [26](26-auth-account-chat-static-audit.md) |
| AUD-010 | `DONE` | Critical | L | FN-10 mail/queue/scheduler static+data inventory | DB/config read access | 78 contracts + cohort/owner map | [27](27-mail-queue-scheduler-static-audit.md) |
| AUD-011 | `DONE` | Critical | L | FN-11 public catalogue/generator static+data inventory | DB/config read access | 72 contracts + asset/data/locale map | [28](28-public-catalog-generators-static-audit.md) |

## 4.1. P0 operational readiness

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| SEC-001 | `WAITING` | Critical | M | ротировать обнаруженный в tracked code Paysera signing credential, перенести provider secrets в typed config/secret store и выполнить history/artifact scan | Finance/provider owner | старый credential отозван; live/sandbox разделены; secret scan PASS; значения не попали в docs/logs | [23](23-payment-static-audit.md) |
| SEC-002 | `BACKLOG` | Critical | M | production perimeter hardening: least-readable `.env`, закрытый phpMyAdmin, удалённый/закрытый phpPgAdmin и выключенный Synvolve test receiver | DevOps/SEC-001 | mode/owner проверены; DB admin доступен только через утверждённый контур; anonymous probes и zero-diff PASS | [33](33-production-readiness-audit.md) |
| OPS-001 | `BACKLOG` | Critical | M | exact-path rotation, permissions, retention, redaction, quotas и alerts для Laravel/custom worker/mail/MariaDB slow logs | L3-001/DevOps | все активные log paths покрыты tested policy; 600/640; synthetic alert PASS; ручной truncate не использован | [33](33-production-readiness-audit.md) |
| OPS-002 | `BACKLOG` | Critical | L | ALT failed-job/suggestion reconciliation и безопасный worker lifecycle: attempts/timeouts/drain/restart-loop alert/idempotent recovery | L3-003/QUE-001/provider owner | 11 214 jobs и 13 131 suggestions классифицированы без blind retry; recovery rehearsal не создаёт дублей | [33](33-production-readiness-audit.md) |
| OPS-003 | `BACKLOG` | High | M | production session TTL/capacity/index/locking/cleanup/cookie continuity contract | L3-001/business owner | 48-hour policy подписана; load/failover/cleanup/switch tests PASS без принудительного logout сверх решения | [33](33-production-readiness-audit.md) |
| OPS-004 | `BACKLOG` | Critical | M | storage link/permissions/capacity/growth/cleanup и public/private synthetic smoke для fresh release | L3-001/AUD-008 | fresh link создаётся воспроизводимо; read/write/delete/download PASS; disk thresholds и owner утверждены | [33](33-production-readiness-audit.md) |

## 5. Stage 0 — платформа и безопасная основа

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| PHP-001 | `DONE` | Critical | L | PHP 8.3/8.4 compatibility report | dependency matrix | static/runtime scan | [02](02-dependency-compatibility.md) |
| PHP-002 | `VERIFY` | High | M | extensions parity | PHP-001 | platform check | [02](02-dependency-compatibility.md) |
| L7-001 | `CANCELLED` | High | M | applicable 6→7 changes resolved | TST-001 | guide gate | [03](03-laravel-upgrade-path.md) |
| L8-001 | `CANCELLED` | High | M | factories/routes/queue parity | L7-001 | test gate | [03](03-laravel-upgrade-path.md) |
| L9-001 | `CANCELLED` | Critical | L | Flysystem/Symfony Mailer migration proven | L8-001 | file/mail tests | [03](03-laravel-upgrade-path.md) |
| L10-001 | `CANCELLED` | High | M | Monolog/raw SQL/test tooling proven | L9-001 | test/log/query gate | [03](03-laravel-upgrade-path.md) |
| L11-001 | `CANCELLED` | High | L | target skeleton structure | L10 knowledge | boot/cache gate | [03](03-laravel-upgrade-path.md) |
| L12-001 | `CANCELLED` | High | M | Carbon/storage/image rules | L11-001 | contract gate | [03](03-laravel-upgrade-path.md) |
| L13-001 | `DONE` | Critical | L | Laravel 13 shell | S0-01 | full CI | [62](62-filament-stage0-offline-target-build-result.md) |
| FIL-001 | `BACKLOG` | Critical | L | panel/auth/guard | L13-001/S0-04 | login/security tests | [53](53-filament-implementation-blueprint.md) |
| S0-01 | `DONE` | Critical | 12–20 | evidence freeze и exact target dependency locks | approved staging | version/hash/plugin manifest signed | [54](54-filament-stage0-execution-pack.md) |
| S0-02A | `DONE` | Critical | 8–12 ч | Локальный Laravel 13 / Filament 5 skeleton и проверенный artifact | S0-01 | clean build/install/platform/audit/tests PASS; public assets unchanged | [62](62-filament-stage0-offline-target-build-result.md) |
| S0-02L | `DONE` | Critical | 4–8 ч | Локальный OpenServer PHP 8.3 environment, Git relocation, safe `.env` merge и DB/runtime baseline | S0-02A | PHP/extensions/platform/DB/HTTP/tests PASS; APP_KEY/safety flags/namespaces сохранены | [62](62-filament-stage0-offline-target-build-result.md) |
| S0-02B | `HOLD` | Critical | 2–4 ч | Непубличная установка artifact в server release-каталог | S0-02A/S0-03…S0-08/owner GO | TEST-S02-D PASS; active test/root/backend unchanged | [карточка](tasks/S0-02B-server-offline-release.md) |
| S0-02C | `HOLD` | Critical | 6–10 ч | Изолированные staging DB/user/env/Redis/session/provider namespaces | S0-02B | server tests PASS без HTTP exposure и production credentials | [54](54-filament-stage0-execution-pack.md) |
| S0-02D | `HOLD` | Critical | 4–6 ч | Закрытый PHP 8.3 preview и подготовка атомарного test switch | S0-02C/review | private preview PASS; rollback rehearsed; отдельный GO получен | [54](54-filament-stage0-execution-pack.md) |
| S0-03 | `DONE` | Critical | 16–28 | локальная user/auth/session compatibility | S0-02L | login/session/cart matrix PASS; no automatic access for zero-permission roles | [карточка](tasks/S0-03-local-auth-session-compatibility.md) |
| S0-04 | `DONE` | Critical | 28–50 | RBAC adapter, navigation и 8-role simulation | S0-03 | 138 menu/675 permission/2 080 link matrix without escalation | [карточка](tasks/S0-04-rbac-navigation-role-simulation.md) |
| S0-05 | `VERIFY` | Critical | 20–36 | translation adapter/versioned publisher spike | S0-02L/S0-04/DB-005 | R5–R14 hashes + 14-case UAT repeatable; one writer | [карточка](tasks/S0-05-translation-adapter-versioned-publisher.md) |
| S0-06 | `HOLD` | Critical | 24–42 | read-only OrderResource и одна safe synthetic Action | S0-03/S0-04 | ID/data/query/ACL/idempotency/failpoint/rollback + information/visual parity PASS | [карточка](tasks/S0-06-orders-readonly-safe-action.md) |
| S0-07 | `BACKLOG` | Critical | 20–40 | chat и legacy-path file adapter spikes | S0-03/S0-04/S0-06 parity | ownership/dedupe/MIME/path/failpoint suite PASS | [карточка](tasks/S0-07-chat-file-adapters.md) |
| S0-08 | `BACKLOG` | Critical | 12–20 | feature flags, receipts, observability и deployment scaffold | S0-02L/S0-03/S0-04/S0-05/S0-06/S0-07 | write flags independent of UI; alerts/evidence available | [54](54-filament-stage0-execution-pack.md) |
| S0-09 | `BACKLOG` | Critical | 8–12 | CI evidence, server runbook и Stage 0 sign-off | S0-01/S0-02D/S0-03/S0-04/S0-05/S0-06/S0-07/S0-08 | written GO/CONDITIONAL GO/NO-GO for wave 1 | [54](54-filament-stage0-execution-pack.md) |

## 6. Wave 1 — Заказы и CRM

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| TST-001 | `BACKLOG` | Critical | XL | orders/status/chat characterization | AUD-002 | legacy CI green | [09](09-testing-strategy.md) |
| TST-005 | `BACKLOG` | Critical | XL | differential fixtures public/admin/SA order creation и failure injection | AUD-005 | no partial/duplicate order effects | [09](09-testing-strategy.md) |
| FIL-004 | `BACKLOG` | Critical | XL | order/chat CRM cluster | INT-001/APP-001 | full CRM UAT | [53](53-filament-implementation-blueprint.md) |
| INT-001 | `BACKLOG` | Critical | XL | SA inbound/outbox | TST-002 | duplicate/retry suite | [07](07-integrations-migration.md) |

## 7. Wave 2 — Пользователи, роли и авторизация

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| TST-004 | `BACKLOG` | Critical | L | admin ACL/ownership/file negative tests | AUD-006 | 401/403/404 matrix green | [09](09-testing-strategy.md) |
| FIL-002 | `BACKLOG` | Critical | XL | role/permission adapter | VOY-002 | 8-role matrix | [53](53-filament-implementation-blueprint.md) |
| INT-005 | `BACKLOG` | High | M | Facebook+Google auth | provider consoles | linking suite | [26](26-auth-account-chat-static-audit.md) |
| SEC-009 | `BACKLOG` | Critical | M | закрыть raw user lookup, auth-only IDOR/admin impersonation и state-changing GET | AUD-012/business owner | negative HTTP matrix + access-log review | [26](26-auth-account-chat-static-audit.md) |
| AUTH-001 | `BACKLOG` | Critical | L | единый standard/custom auth, password/session/verification contract | SEC-009/Q-A01/Q-A02 | fixation/throttle/CAPTCHA/reset suite | [26](26-auth-account-chat-static-audit.md) |
| AUTH-002 | `BACKLOG` | Critical | L | stateful Socialite linking и exact redirect allowlist | provider consoles/Q-A04/Q-A05 | collision/link/unlink/recovery suite | [26](26-auth-account-chat-static-audit.md) |

## 8. Wave 3 — Чаты, файлы и вложения

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| TST-006 | `BACKLOG` | Critical | XL | file/PDF ACL, upload, traversal, rename failpoint, locale golden и SA SSRF suite | AUD-006/AUD-008 | zero leak/loss/overwrite/duplicate | [09](09-testing-strategy.md) |
| APP-001 | `BACKLOG` | Critical | XL | private artifact/download layer и central upload policy | TST-006 | ownership/MIME/limit suite green | [24](24-files-pdf-static-audit.md) |
| APP-002 | `BACKLOG` | Critical | L | two-phase rename/copy journal + after-commit cleanup | APP-001 | every failpoint replay-safe | [24](24-files-pdf-static-audit.md) |
| APP-003 | `BACKLOG` | Critical | L | pure PDF renderer, locale/template snapshot и artifact receipt | APP-001/content owner | seven-locale golden corpus | [24](24-files-pdf-static-audit.md) |
| INT-007 | `BACKLOG` | Critical | M | SSRF-safe streamed SA attachment adapter | Q-B14/APP-001 | egress/TLS/limit/duplicate suite | [07](07-integrations-migration.md) |
| CHAT-001 | `BACKLOG` | Critical | XL | actor/source mapping четырёх streams и ownership policies | SEC-009/Q-A03/Q-A06 | role×ownership/read parity | [26](26-auth-account-chat-static-audit.md) |
| CHAT-002 | `BACKLOG` | Critical | L | message idempotency + durable outbox/reconciliation | CHAT-001/queue | duplicate/concurrency/failpoint suite | [26](26-auth-account-chat-static-audit.md) |
| CUT-003 | `BACKLOG` | Critical | L | client message ID, unique claim и outbox receipt | CHAT-001/CHAT-002 | duplicate/concurrency/crash tests дают одну row и один side effect | [34](34-l3g06-write-owner-delta-cutover.md) |

## 9. Wave 4 — Каталог, цены и генераторы

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| FIL-003 | `BACKLOG` | High | XL | content/catalog Resources | FIL-002 | module parity | [53](53-filament-implementation-blueprint.md) |
| SEC-011 | `BACKLOG` | Critical | M | закрыть public catalogue maintenance GET и item-route 404 | AUD-011 | anonymous zero-write + existing-item route suite | [28](28-public-catalog-generators-static-audit.md) |
| CAT-001 | `BACKLOG` | Critical | XL | единый route/data/filter/price/session catalogue contract | SEC-011/business owners | seven-locale old/new differential suite | [28](28-public-catalog-generators-static-audit.md) |
| CAT-002 | `BACKLOG` | Critical | XL | canvas/collage/modular/family/portrait generators | CAT-001/APP-001 | start→upload→preview→basket/order parity | [28](28-public-catalog-generators-static-audit.md) |
| DB-011 | `BACKLOG` | Critical | L | orphan quarantine + catalogue/translation delta migration | production copy/content owner | counts/hashes/tombstone/rollback rehearsal | [36](36-l3g06-reconciliation-specification.md) |
| TST-011 | `BACKLOG` | Critical | XL | catalogue golden/browser/security/performance corpus | CAT-001/CAT-002/DB-011/SEO-011/FE-002 | zero price/locale/asset/SEO regression | [09](09-testing-strategy.md) |
| CUT-010 | `BACKLOG` | Critical | L | catalogue price/relation/media characterization corpus | CAT-001/CUT-009 | страны/локали/sizes/options и известные orphan cohorts имеют signed expected result | [35](35-l3g06-catalog-content-translations-cutover.md) |

## 10. Wave 5 — Переводы и контентные данные

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| DB-005 | `BACKLOG` | Critical | L | translation/content insert-update-delete manifest и reconciliation | DB-004/content owner | locale/table/column hash parity | [36](36-l3g06-reconciliation-specification.md) |
| CUT-007 | `DONE` | Critical | L | production exact translation inventory для base/`translations`/`ltm_translations`/PHP files | CUT-001/CUT-002 | key/hash/file manifests, duplicates/NULL/deletes и `ee/et/cn/jp/uk` mapping подписаны | [35](35-l3g06-catalog-content-translations-cutover.md) |
| ACL-001 | `BACKLOG` | Critical | M | deny-by-default ACL и один writer для публикации/возврата переводов | S0-04/S0-05/CUT-007 | только согласованные роли могут publish/rollback; отрицательная role matrix PASS | [51](51-l3g06-translation-owner-decision-and-staging-uat.md) |
| CUT-008 | `BACKLOG` | Critical | L | единый versioned PHP dictionary publisher вместо двух live writers | CUT-007/ACL-001 | deny-by-default ACL; PHP lint/SHA-256; publish/rollback receipt; live overwrite исключён | [35](35-l3g06-catalog-content-translations-cutover.md) |
| CUT-009 | `BACKLOG` | Critical | XL | per-module content+translation reconciliation tooling | CUT-002/CUT-007 | parent/base/translation/pivot/media/SEO full key/hash/anti-join на restored staging | [35](35-l3g06-catalog-content-translations-cutover.md) |

## 11. Wave 6 — Платежи, доставка, почта и интеграции

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| TST-002 | `BACKLOG` | Critical | XL | payment/SA/mail/OAuth contracts | sandboxes | fixture suite | [09](09-testing-strategy.md) |
| TST-007 | `BACKLOG` | Critical | XL | delivery quote/point/XML/ACL/provider-failure/replay/concurrency/courier/print suite | AUD-006/AUD-009 | exactly-once; no live calls | [09](09-testing-strategy.md) |
| INT-002 | `BACKLOG` | Critical | L | Paysera adapter | sandbox | signature/replay suite | [23](23-payment-static-audit.md) |
| INT-003 | `BACKLOG` | Critical | L | PayPal adapter | provider decision | capture/reconcile suite | [23](23-payment-static-audit.md) |
| INT-004 | `BACKLOG` | High | L | mail + marketing queues | QUE-001/SEC-010 | template/failure suite | [27](27-mail-queue-scheduler-static-audit.md) |
| INT-006 | `BACKLOG` | Critical | XL | typed Venipak client, server quote, directory snapshot, shipment/courier ledger, reprint/void/reconcile | Q-B15/Q-B16/TST-007 | replay/concurrency/error/PDF suite | [25](25-venipak-delivery-static-audit.md) |
| SEC-010 | `BACKLOG` | Critical | M | закрыть public mail previews и включить SMTP TLS verification | AUD-010/DevOps | anonymous 404/no DB diff + TLS tests | [27](27-mail-queue-scheduler-static-audit.md) |
| MAIL-001 | `BACKLOG` | Critical | XL | transactional event/outbox/delivery ledger | SEC-010/business registry | crash/replay/provider reconciliation | [27](27-mail-queue-scheduler-static-audit.md) |
| MAIL-002 | `BACKLOG` | Critical | L | consent, signed unsubscribe, suppression, bounce/complaint | Legal/Marketing | all marketing templates compliant | [27](27-mail-queue-scheduler-static-audit.md) |
| TST-010 | `BACKLOG` | Critical | XL | seven-locale template and failpoint corpus | MAIL-001/MAIL-002/QUE-001/CRON-001 | zero duplicate/lost/consent violation | [09](09-testing-strategy.md) |
| CUT-004 | `BACKLOG` | Critical | XL | payment attempt/receipt/reconciliation ledger | INT-002/INT-003/RBK-002 | amount/currency/reference/unknown outcome и replay проходят sandbox suite | [34](34-l3g06-write-owner-delta-cutover.md) |

## 12. Wave 7 — Контент, SEO, redirects и feeds

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| TST-003 | `BACKLOG` | High | L | locale/SEO/redirect manifest | content owner | crawl diff | [09](09-testing-strategy.md) |
| FIL-005 | `BACKLOG` | High | XL | media/SEO/generator pages | APP-001/QUE-001 | custom action UAT | [53](53-filament-implementation-blueprint.md) |
| SEO-011 | `BACKLOG` | High | L | URL/slug/canonical/sitemap/feed continuity | access logs/SEO owner | crawl/schema/redirect diff | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| SEO-001 | `BACKLOG` | Critical | S | закрыть public mutating/debug/preview/test routes | SEC owner | anonymous 404/403; zero DB/file/provider diff | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| SEO-002 | `BACKLOG` | Critical | M | production redirect/404/crawler/merchant evidence | AUD-002 | anonymized 30–90 day pack attached | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| SEO-003 | `BACKLOG` | Critical | M | signed 256-rule redirect + PL domain map | SEO-002 | one target/status/query policy per source | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| SEO-004 | `BACKLOG` | Critical | L | redirect responder без closures/header-exit | SEO-003 | one-hop/cycle/encoded/locale tests pass | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| SEO-005 | `BACKLOG` | High | L | cached sitemap/feed projection | DB-005 | schema/count/canonical/price golden parity | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| SEO-006 | `BACKLOG` | Critical | M | verified review submission | upload/security | MIME/size/ownership/rate/moderation tests pass | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| SEO-007 | `BACKLOG` | Critical | M | route registry/cache gate | AUD-002/SEO-004 | 0 closures/missing handlers; names resolved | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| SEO-008 | `BACKLOG` | Critical | M | SEO/feed canary + rollback rehearsal | SEO-003/SEO-005/SEO-007 | owner sign-off and monitored rollback evidence | [30](30-seo-redirect-preview-auxiliary-static-audit.md) |
| CUT-011 | `BACKLOG` | Critical | L | URL/redirect/canonical/hreflang/sitemap/feed regression artifact | SEO-001/CUT-009 | ноль unexplained 404/loop/collision; все public locales покрыты | [35](35-l3g06-catalog-content-translations-cutover.md) |

## 13. Сквозные задачи архитектуры, БД, Voyager и frontend

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| VOY-001 | `DONE` | Critical | L | 133 BREAD manifest | DB access | every module classified | [29](29-voyager-bread-custom-admin-static-audit.md) |
| VOY-002 | `WAITING` | Critical | XL | field/action/translation/upload parity specs | business owner | UAT sheets | [29](29-voyager-bread-custom-admin-static-audit.md) |
| VOY-003 | `BACKLOG` | High | M | decommission data policy | VOY-001 | approved ADR | [29](29-voyager-bread-custom-admin-static-audit.md) |
| DB-002 | `BACKLOG` | Critical | L | counts/orphans/duplicates baseline | L3-002 | signed report | [36](36-l3g06-reconciliation-specification.md) |
| DB-003 | `BACKLOG` | High | L | additive indexes/schema | query profiles | EXPLAIN + rollback | [36](36-l3g06-reconciliation-specification.md) |
| DB-004 | `BACKLOG` | Critical | L | online backfill, watermark и delta reconciliation | L3-002/AUD-004 | zero loss/duplicate report | [36](36-l3g06-reconciliation-specification.md) |
| QUE-001 | `BACKLOG` | Critical | L | purpose queues + workers/retry/failed/restart/metrics | L3-001 | backlog/poison/rolling-deploy suite | [27](27-mail-queue-scheduler-static-audit.md) |
| CRON-001 | `BACKLOG` | Critical | L | one-server scheduler locks, TTL/timezone/heartbeat/max batch | L3-003 | two-host/crash/DST rehearsal | [27](27-mail-queue-scheduler-static-audit.md) |
| FE-001 | `BACKLOG` | High | M | изолированный Vite + Filament Tailwind без public assets | L13-001 | admin build manifest | [02](02-dependency-compatibility.md) |
| FE-002 | `VERIFY` | Critical | M | 12 public asset outputs заморожены без пересборки | visual harness | URL/hash/load-order/visual parity | [02](02-dependency-compatibility.md) |
| VOY-009 | `VERIFY` | Critical | M | production BREAD/access/translation snapshot | AUD-001/VOY-001 | counts/hashes/log usage attached | [29](29-voyager-bread-custom-admin-static-audit.md) |
| VOY-010 | `WAITING` | Critical | L | 133 signed module decisions + owners | VOY-009/VOY-002 | every CSV row classified | [29](29-voyager-bread-custom-admin-static-audit.md) |
| VOY-004 | `BACKLOG` | Critical | L | 8-role resource/custom-action matrix | AUD-006 | deny-by-default tests pass | [29](29-voyager-bread-custom-admin-static-audit.md) |
| VOY-005 | `BACKLOG` | Critical | L | translation/content adapter + conflict policy | DB-005 | all locale/source reconciliation passes | [29](29-voyager-bread-custom-admin-static-audit.md) |
| VOY-006 | `BACKLOG` | High | M | stale/incomplete route decisions | VOY-010 | IDs 27/129/orders routes resolved | [29](29-voyager-bread-custom-admin-static-audit.md) |
| VOY-007 | `BACKLOG` | Critical | XL | Filament waves with specialized Pages | VOY-010/VOY-004/VOY-005 | per-module parity/UAT/rollback evidence | [29](29-voyager-bread-custom-admin-static-audit.md) |
| APP-004 | `BACKLOG` | Critical | XL | перенос общих моделей, casts/accessors/mutators, date/serialization и domain services в Laravel 13 compatibility layer | TST-001/L13-001 | model snapshots, money/date/status/ID invariants и backward-compatible schema PASS | [06](06-application-code-migration.md) |
| APP-005 | `BACKLOG` | Critical | XL | пофункциональный перенос frontend routes/controllers/Blade и транзакционных пользовательских сценариев без пересборки или редизайна public frontend | APP-004/FE-002/AUD-005/TST-005 | route/response/SEO/session/visual parity и применимые auth/account/cart/generator/order/upload/chat write-effects проходят differential tests в isolated DB; byte-identical public assets сохранены | [06](06-application-code-migration.md) |
| APP-005A | `DONE` | Critical | XL | frontend foundation: exact public route/layout/asset/locale inventory, compatibility shell и карта transactional flows | FE-002/AUD-005 | shell/forms/assets PASS; full route disposition, child backlog, client callsites, hashes и zero-delta evidence закрыты | [карточка](tasks/APP-005A-frontend-foundation.md), [результат](67-public-route-disposition-and-frontend-backlog.md) |
| APP-005A-FORM-02 | `DONE` | Critical | L | parity-first перенос четырёхшагового homepage quiz и aliases `POST /send_photo_form`, `POST /user/send_photo_form` | APP-005A-FORM-01=DONE | target `5963029`; 108 / 2 212 PASS; владелец подтвердил LT submit, localized client mail и рабочий результат | [карточка](tasks/APP-005A-FORM-02-homepage-quiz.md) |
| APP-005A-ROUTE-01 | `DONE` | Critical | M | завершить disposition всех public routes и child-task map всех frontend transactional flows | APP-005A-FORM-02=DONE | 2 236 legacy public rows + 27 target rows + 217 client callsites; disposition/task ID покрытие 100%; hashes/zero-delta/full suite PASS | [карточка](tasks/APP-005A-ROUTE-01-public-route-disposition.md), [67](67-public-route-disposition-and-frontend-backlog.md) |
| APP-005B-PAGES-01 | `IN_PROGRESS` | Critical | XL | parity-first перенос 32 common public content/page/widget routes | APP-005A-ROUTE-01=DONE | URL/locale/data/Blade/SEO/assets/desktop/mobile parity; forms имеют готовый endpoint либо явный child gate; zero-delta PASS | [карточка](tasks/APP-005B-PAGES-01-public-content-pages.md), foundation target `39b5171` |
| APP-005B-FORM-03 | `BACKLOG` | Critical | L | шесть оставшихся public form writers: portrait photo, screen, dates, free image, stock coupon, friend email | APP-005B-PAGES-01 | exact request/file/DB/order/mail/session/provider effects, idempotency, locale, gates и acceptance PASS | [карточка](tasks/APP-005B-FORM-03-auxiliary-public-forms.md) |
| APP-005B-CHECKOUT-01 | `BACKLOG` | Critical | XL | 59 route basket → checkout → order, включая семь unsafe GET mutations | CAT-001/CAT-002/APP-001 | item/money/session/coupon/order parity; exactly-once order; transaction/outbox/receipts; failure/replay suite | [карточка](tasks/APP-005B-CHECKOUT-01-basket-checkout-order.md) |

`APP-005` — сквозной frontend-контур, а не замена профильных задач W1–W7. Реальные платежи/callbacks, доставка, mail/OAuth, Synvolve/SA/CRM, файловая безопасность, чаты, переводы и административные writers закрываются соответствующими `INT-*`, `APP-*`, `CHAT-*`, `ACL-*`, `FIL-*` и `CUT-*` задачами. Ни публичный, ни административный модуль нельзя считать полностью перенесённым только по read projection.

## 14. Финальная приёмка, deployment, cutover и rollback

| ID | Статус | P | Size | Задача | Зависимости | Definition of Done | Детали |
|---|---|---|---:|---|---|---|---|
| DEP-001 | `BACKLOG` | Critical | L | CI immutable artifacts | AUD-001 | clean build | [10](10-deployment-and-rollback.md) |
| DEP-002 | `BACKLOG` | Critical | XL | staging/UAT/load/security | all Critical | go/no-go | [10](10-deployment-and-rollback.md) |
| DEP-003 | `BACKLOG` | Critical | L | cutover runbook/observability | DEP-002 | rehearsal | [10](10-deployment-and-rollback.md) |
| DEP-004 | `BACKLOG` | Critical | L | live atomic switch rehearsal | DEP-003/DB-004/DB-005 | orders/callbacks/translations survive switch | [10](10-deployment-and-rollback.md) |
| RBK-001 | `BACKLOG` | Critical | L | app/assets/worker rollback | L3-002/DEP-003 | timed rehearsal | [10](10-deployment-and-rollback.md) |
| RBK-002 | `BACKLOG` | Critical | L | provider reconciliation rollback | INT-001/INT-002/INT-003/INT-004/INT-005/INT-006/INT-007 | simulated incident | [10](10-deployment-and-rollback.md) |
| RBK-003 | `BACKLOG` | High | M | Voyager read-only/decommission | stable releases | no runtime usage | [10](10-deployment-and-rollback.md) |
| VOY-008 | `BACKLOG` | High | M | Voyager read-only/decommission | VOY-007 | no writer/runtime dependency after retention | [29](29-voyager-bread-custom-admin-static-audit.md) |
| CUT-001 | `VERIFY` | Critical | M | подписать capability+field/action write-owner matrix | Business/Tech owners | каждый public/admin/SA/payment/chat writer имеет одну фазу и одного owner | [34](34-l3g06-write-owner-delta-cutover.md) |
| CUT-002 | `BACKLOG` | Critical | L | canonical PK/hash/delete/file reconciliation tooling | L3-002/DB-002 | full restored-snapshot diff находит insert/update/delete/orphan без PII output | [34](34-l3g06-write-owner-delta-cutover.md) |
| CUT-005 | `BACKLOG` | Critical | M | low-risk internal-note component rehearsal | CUT-001/CUT-002 | timed cutover+forward rollback без lost/duplicate rows | [34](34-l3g06-write-owner-delta-cutover.md) |
| CUT-006 | `BACKLOG` | Critical | XL | orders/status/chat/payment game day | CUT-003/CUT-004/L3-G01…G05 | owner sign-off; alerts and stop triggers work; live writes preserved | [34](34-l3g06-write-owner-delta-cutover.md) |
| CUT-012 | `BACKLOG` | Critical | XL | catalogue/content/translation game day | CUT-008/CUT-009/CUT-010/CUT-011/L3-G01…G05 | один writer на capability; frozen assets; forward rollback сохраняет новые edits/files | [35](35-l3g06-catalog-content-translations-cutover.md) |
| GOV-001 | `BACKLOG` | High | S | еженедельный отчёт, фактические часы, risk/decision/change log и письменный PASS/FAIL каждого этапа | project start | раз в неделю обновлены статус/evidence/прогноз; scope changes отдельно оценены; sign-off приложен | [52](52-client-decisions-register.md) |
| DOC-001 | `BACKLOG` | High | M | финальная клиентская редакция плана после актуализации аудита и визуальная постраничная проверка | DEP-002/client scope | содержание синхронизировано с реестрами; draft markers отсутствуют; render/PNG QA и клиентская версия зафиксированы | [52](52-client-decisions-register.md) |

## 15. Порядок подготовки карточек

Карточки заранее создаются не для всех 131 задач, а только для ближайшей `READY` и фактически начатой `IN_PROGRESS` задачи. Это предотвращает устаревание десятков детальных файлов. После закрытия задачи управляющий реестр назначает следующую, и для неё создаётся карточка с точным scope, DoD, stop conditions, evidence и rollback.

Текущая карточка: [S0-03-local-auth-session-compatibility.md](tasks/S0-03-local-auth-session-compatibility.md). Server card [S0-02B](tasks/S0-02B-server-offline-release.md) сохранена в `HOLD` до отдельного owner GO.
