# FN-10: почта, очереди, jobs и scheduler

Дата аудита: 14.07.2026. Уровень: static/data L2. Runtime production L3 не выполнялся.

## 1. Граница и безопасность аудита

Проверены 33 Mailables, 3 Notifications, 13 console command classes, `GenerateImageAltJob`, пять активных scheduled commands, mail/queue/cache config, mail events/logging, 68 mail view files, migrations `jobs|failed_jobs`, локальные aggregate-данные и связанные route/controller side effects.

Письма не отправлялись, queue workers и scheduled commands не запускались, SMTP/provider endpoints не вызывались. Email, message bodies, queue payload, credentials и содержимое `mail.log` не читались. Код приложения и БД не изменялись.

Проверки baseline: PHP 7.4 syntax lint 53 релевантных PHP-файлов — 0 ошибок; `OrderOverdueDelayNotifierTest` — `OK (9 tests, 19 assertions)`. Специализированных tests для abandoned-cart, consent/unsubscribe, public previews, queue workers и scheduler locks не найдено.

Рабочий runtime baseline: `C:\OSPanel\modules\php\PHP_7.4\php.exe`.

## 2. Главный вывод

Почта сейчас является частью request/command business transaction, а не независимым доставочным контуром. Локальный `QUEUE_CONNECTION=sync`; поэтому даже два вызова `Mail::queue()` и две queued Notifications фактически выполняются синхронно. Ошибка SMTP способна оставить частично созданного user/order/chat/payment state либо вернуть 500 после успешной DB-записи, а повтор запроса — создать duplicate business side effects.

Нельзя одновременно менять Laravel mailer, queue driver, scheduler topology и email templates. Сначала нужны message/outbox identity, transactional/marketing classification, consent, provider receipts, retry/reconciliation и production inventory.

## 3. Подтверждённый inventory

| Объект | Количество | Комментарий |
|---|---:|---|
| Mailables | 33 | ни один не реализует `ShouldQueue` |
| Notifications | 3 | две реализуют `ShouldQueue`, одна synchronous |
| Console commands | 13 | пять включены в scheduler |
| Scheduled commands | 5 | daily/hourly; все с `withoutOverlapping()` |
| Queue jobs | 1 основной | `GenerateImageAltJob`, queue `alt-gen` |
| Mail view files | 68 | `resources/views/mail*`, включая preview/old variants |
| Строки direct-send/static call sites | до 81 | 76 direct/raw send patterns, 2 queue, 2 notify; часть строк может быть legacy/commented |
| Raw HTML `setBody(..., text/html)` patterns | 25 | требуют source/escaping classification |
| Mailables с `App::setLocale()` | 19 вызовов | глобальный locale меняется во время render |

## 4. Runtime/config snapshot — только локальная среда

- `APP_ENV=production`, `APP_DEBUG=true`, timezone `Europe/Riga`.
- Queue default: `sync`; `jobs=0`, `failed_jobs=0`.
- Failed driver настроен на database; наличие пустой таблицы не доказывает worker/retry policy.
- Cache default: `file`; таблицы `cache` нет.
- Mail driver: SMTP; host/username/password/from настроены, значения не читались.
- SMTP TLS verification отключена: `verify_peer=false`, `verify_peer_name=false`, `allow_self_signed=true`.
- `notifications` table отсутствует; текущие Notifications используют только mail channel.
- Репозиторий не содержит Supervisor/systemd/Docker/CI/cron deployment manifest.

Это локальный snapshot. Фактические production queue/cron/cache/worker/mail настройки остаются неизвестными.

## 5. SwiftMailer и Laravel 13 blocker

`AppServiceProvider::boot()` напрямую вызывает `Mail::getSwiftMailer()->getTransport()` и меняет `Swift_SmtpTransport::setLocalDomain()`. SwiftMailer удалён из нового Laravel mail stack; этот boot-код не переносится автоматически и блокирует Laravel 9+.

`localDomain` устанавливается значением SMTP host, хотя это отдельный SMTP/EHLO contract. До замены нужно выяснить, зачем он добавлен, получить provider requirement и delivery evidence. Target использует Symfony Mailer, typed mailer config и environment-specific TLS policy; self-signed/disabled verification не допускается в production.

## 6. Scheduler

Активное расписание:

| Команда | Частота | Side effects |
|---|---|---|
| `UserNotify:cron` | daily | marketing email + `order_action` |
| `orders:notify-overdue-delay` | daily 18:00 | transactional email + orders marker + logs |
| `cart:send-recovery-emails` | hourly | delete carts, token write, queued/sync mail |
| `cart:send-recovery-emails-coupons` | hourly | coupon creation, queued/sync mail, sent flag |
| `GenSmallImages:run` | daily | filesystem image write + DB flags/path |

Все используют `withoutOverlapping()` без явного TTL. В Laravel 6 default lock lifetime — 1440 минут. При `CACHE_DRIVER=file` lock локален filesystem-host; он не доказывает `onOneServer` в multi-node topology. У каждой команды отдельный mutex: две abandoned-cart команды не имеют общего business lock.

Не заданы `onOneServer`, explicit timezone, success/failure callbacks, output path, alert, duration SLA и per-command lock TTL. Cron owner, число scheduler hosts, deploy/restart policy и фактические последние запуски не подтверждены.

## 7. Abandoned-cart pipeline

### 7.1. Локальный snapshot

- 209 carts; все 209 старше 7 дней.
- 190 старше 12 часов с `recovery_token IS NULL`.
- Read-only business filter оставляет 108 кандидатов на первое письмо.
- 19 rows имеют first-mail flag, 16 — coupon-sent flag.
- 19 tokens истекли; 3 rows подходят по форме coupon query, read-only paid-order filter оставляет 2.

Если запустить текущую первую команду на этом snapshot, она сначала удалит все 209 rows как старше 7 дней и только затем выполнит email query. Письма не запускались; это вычисленный side-effect preview, а не результат выполнения.

### 7.2. Нарушения delivery contract

1. First command генерирует/saves recovery token до `Mail::queue()`. При enqueue/send failure token и `is_send_email_twelve_hours=true` остаются, а first query больше не подхватит row.
2. Coupon command создаёт coupon до `try`; enqueue success немедленно ставит `is_coupon_sent=true`, хотя async provider delivery ещё не произошла.
3. При `sync` обе `queue()` выполняют SMTP в scheduler process; при переходе на real queue semantics резко меняются.
4. Coupon query не проверяет `token_expires_at`; локально все 19 token rows истекли.
5. Cleanup удаляет rows до delivery/reconciliation и не различает `unsent|queued|sent|failed|converted`.
6. Нет immutable campaign/message attempt, provider receipt, retry count, bounce/complaint и dead-letter state.
7. Нет общего claim/transaction между двумя commands; multi-host и manual launch допускают race.
8. Consent `users.news` и suppression list не проверяются; email может не иметь user row.

Target: durable cart campaign state machine (`eligible→claimed→queued→sent|failed|suppressed|converted|expired`), unique campaign+cart claim, after-commit outbox, provider receipt, consent snapshot, signed recovery link и cleanup только после retention gate.

## 8. `UserNotify:cron`

Локально 252 active two-date coupons; 162 принадлежат users с `news=NO`. На дату snapshot один coupon попадает в `sale_date=today+5`; он не относится к `news=NO`.

Команда использует абсолютный `Carbon::diffInDays() == 5`, поэтому один coupon может соответствовать и за пять дней до, и через пять дней после sale date. Отдельного sent marker/campaign receipt нет. Consent `users.news` не проверяется.

`Mail::send()` трактуется как boolean `$sended`, хотя успешная отправка не является надёжным provider receipt. Команда пишет email и HTML activity в `order_action`, не ловит исключение на уровне одного recipient и может оборвать весь batch.

## 9. Overdue-delay notifier

Локально `overdue_delay_email_sent_at` заполнен у 0 orders. Из 60 non-final/unmarked orders read-only расчёт текущего алгоритма считает 59 due now. Первый реальный запуск на подобном snapshot способен создать заметный batch; production counts и бизнес-утверждение нужны до включения.

Положительно: есть locale allowlist, invalid-email skip, chunking, final status exclusion, exception isolation по order и marker после send.

Риски:

- send происходит до marker save; crash между ними создаёт duplicate при следующем запуске;
- два scheduler hosts могут выбрать один order до marker;
- marker означает локальный SMTP success, а не provider delivery;
- rule `when_send 18:00` либо 3/8 business days не имеет holiday calendar/business sign-off;
- logs содержат recipient email, locale и exception text;
- нет attempt/outbox/provider message ID, bounce/complaint/retry lifecycle.

До включения: dry-run report, max batch/rate, owner approval, atomic claim/outbox, unique template version, provider receipt/reconciliation и one-server scheduler lock.

## 10. Transactional mail внутри request

Order creation, payment/status, registration, chat, upload, review и admin actions используют direct SMTP. Примеры подтверждённых failure windows:

- новый user сохраняется и login выполняется до registration email; SMTP failure может остановить order creation;
- order сохраняется до confirmation email/Synvolve; SMTP failure возвращает error после существующей order row, а browser retry может создать новый order;
- payment/status actions способны повторить mail вместе с bonus/coupon/PDF side effects;
- chat row и files могут сохраниться до email/Synvolve failure либо наоборот;
- status email отправляется без общей outbox identity для transition.

Target contract: DB transaction сохраняет только business state и unique domain event. После commit отдельный outbox worker создаёт deterministic mail message. Повтор обрабатывается по event/template/recipient key без повторного order/payment/chat transition.

## 11. Public previews и mail-producing forms

Публично зарегистрированы `/mail/test`, `/mail/gift_cart`, `/mail/abandoned_cart`, `/mail/abandoned_cart24`, `/mail/order` и `/mail/1..7`.

Критично:

- `/mail/abandoned_cart` вызывает `generateRecoveryToken()` и меняет DB из GET;
- `/mail/abandoned_cart24` способен вызвать coupon generation во время preview;
- `/mail/order` и gift-card preview используют hardcoded user/order references и могут раскрыть private order/template data;

**P0 plan 14.07.2026:** все перечисленные mail preview routes требуется закрыть default-off gate с отдельным production break-glass. В текущем codebase это не внедрено; временный прототип удалён. Synthetic authenticated preview без production data и устранение side effects остаются отдельной задачей.
- `/mail/test` выполняет `dd()` конфигурационных значений;
- preview routes не ограничены local/testing/admin permission.

Также public/auth-light forms отправляют admin/customer/friend emails синхронно. Для произвольного friend recipient нет подтверждённого rate limit/verification. Raw HTML bodies собираются из request/user/order fields и требуют escaping, link signing и zero-side-effect negative tests.

## 12. Consent, unsubscribe и delivery compliance

- `users.news`: YES 10 030, NO 13 771.
- `UserNotify:cron` не фильтрует `news`; 162 из 252 active campaign coupons принадлежат `news=NO`.
- Abandoned-cart pipeline не имеет suppression/consent contract.
- Многие marketing templates показывают unsubscribe, но link равен `#`; общий footer ведёт на внешний placeholder, а не на unsubscribe action.
- Единственный dynamic unsubscribe в `AdminMailNotification` использует unsigned numeric user ID; этот риск уже зафиксирован в FN-09.
- Нет provider bounce/complaint webhook, global suppression list, reason/source/timestamp consent history и legal transactional-vs-marketing registry.

До production migration владелец Legal/Marketing утверждает классификацию каждого template, lawful basis, unsubscribe SLA, re-subscribe policy и retention.

## 13. Mail content, locale и assets

- Все семь публичных locale `lv|lt|pl|ru|de|en|ee` и оба translation storage contours сохраняются. CRM `ru|uk|en` остаётся отдельным контрактом.
- 19 вызовов `App::setLocale()` внутри Mailables меняют global application state. В long-running worker locale может протечь в следующий message, если framework/caller не восстановит context.
- Часть Notifications использует `HasLocalePreference`, часть Mailables получает locale вручную, часть использует текущий global locale; fallback semantics неодинаковы.
- Некоторые templates выбирают random promotional records при build; retry может отправить другой content для того же business event.
- В 68 view files найдено 785 remote URL references. Главные hosts — production site, external image storage, font/social providers. Email render зависит от внешней доступности и privacy/image-loading policy.
- Есть три raw Blade echo patterns; quiz/request content и product fields требуют source classification/sanitization.
- 25 raw HTML `setBody()` patterns требуют contextual escaping. Chat HTML injection уже связана с R-63.
- Три PDF attachment paths используют MIME `text/pdf` вместо `application/pdf`; queued attachment может исчезнуть до worker execution, если path не immutable.
- Активные mail events логируют recipient addresses и subjects в single `storage/logs/mail.log` без rotation/retention; локальный файл существует, его содержимое не читалось.

Target: immutable template/version/locale/recipient payload, explicit locale context with restore, deterministic content, asset inventory, HTML sanitizer/escaping tests, private artifact receipt/hash and PII-minimized structured telemetry.

## 14. Queue jobs и retries

`GenerateImageAltJob` задаёт `tries=3`, backoff `60/300/900` и queue `alt-gen`. Но default queue локально `sync`, поэтому dispatch из observer/controller/command выполняет external generation в request/CLI process.

Job ловит общий `Throwable`, сохраняет failed status и не rethrow — queue считает attempt успешным, поэтому normal transient failures не используют tries/backoff и не попадают в `failed_jobs`. `DailyLimitReached` release работает только при реальном queue job; sync path сразу помечает failure. Auto observer при включении может запускать external call из model lifecycle.

Для target нужны отдельные queues `transactional-mail`, `marketing-mail`, `integrations`, `media/alt`; worker timeouts должны быть меньше `retry_after`, jobs должны иметь stable serialized IDs, unique claims, retryable/non-retryable exception taxonomy, failed callback, metrics и release-safe restart.

## 15. `GenSmallImages:run`

Команда загружает все rows без `small_image`, пишет файл и затем DB path. Проверяет writable original file, а не target directory; transient missing/unwritable сразу ставит `img_error=1`, после чего автоматический retry исключён. File write и DB save неатомарны, overwrite включён, checkpoint/rate/limit/receipt отсутствуют.

Target: chunking, target-path validation, temp+atomic publish, image decode/limits, content hash/receipt, retryable error state, metrics и maintenance lock. Public assets миграция не должна массово перегенерировать.

## 16. Наблюдаемость

Положительно: `MessageSending` и `MessageSent` логируются, overdue notifier считает checked/sent/skipped/failed, alt generation имеет отдельный log channel.

Недостаточно: `MessageSent` означает transport acceptance, не inbox delivery; нет stable message/event ID, provider ID, template/version, attempt, latency, suppression/bounce/complaint; recipient email/subject пишутся как PII. `mail.log` — single file без rotation. Нет dashboard/alert для scheduler last-success, queue age, failed jobs, bounce rate и duplicate suppression.

## 17. Target architecture

1. `Domain event/outbox`: unique event ID сохраняется в той же DB transaction, что order/payment/chat state.
2. `Mail intent`: recipient reference, template key/version, locale snapshot, consent class, artifact receipt; без plaintext password и arbitrary HTML.
3. `Dispatcher`: after-commit claim, deterministic idempotency key, per-purpose queue/rate.
4. `Provider adapter`: TLS verify, timeout, provider message ID, sanitized response metadata.
5. `Delivery ledger`: queued/accepted/delivered/bounced/complained/suppressed/failed/unknown.
6. `Scheduler`: one-server distributed lock, explicit timezone/TTL, dry-run/max batch, last-success heartbeat.
7. `Reconciliation`: stuck outbox, unknown provider outcome, bounce/suppression и safe replay.

## 18. Test matrix L3

- каждый Mailable/Notification: семь public locales, CRM locale contract где применимо, subject/body/link/asset/glyph snapshot;
- transactional flows: DB commit + mail failure, crash before/after outbox, duplicate browser/provider request, worker retry after deploy;
- scheduler: one/two hosts, stale lock, crash, manual overlap, DST/timezone, max batch, last-success alert;
- abandoned carts: paid/guest/NO-consent/expired token/cleanup/race/queue failure/bounce/conversion;
- overdue: 59-row local-like dry run, boundary 18:00, weekend/holiday decision, concurrent claim, crash after provider accept;
- templates: XSS/HTML/control chars, unsubscribe signing, remote asset outage, missing translation, deterministic retry;
- queue: worker timeout vs retry_after, poison message, serialization across old/new release, failed/retry/forget, queue restart;
- attachments: missing/replaced/oversize/non-PDF path, hash mismatch, correct MIME and private access.

Live SMTP recipients запрещены в CI/staging: mail sink/allowlist и blocked production outbound обязательны.

## 19. Cutover и rollback

1. Legacy остаётся mail/scheduler write-owner; target сначала пишет shadow outbox без dispatch.
2. Сверить deterministic intents по event/template/recipient/locale/artifact, не сравнивая PII в отчёте.
3. Переключать отдельно: transactional mail, marketing mail, alt/media jobs и каждую scheduled command.
4. Один provider/queue owner на волну; old/new workers не должны одновременно принимать один queue serialization format без compatibility gate.
5. Rollback возвращает dispatcher/route owner на legacy, но не удаляет outbox/receipts и не повторяет unknown provider outcome.
6. Production продолжает принимать orders, chats и translation edits; rollback DB к старому dump запрещён.

Stop: duplicate order/payment/chat email; mail до rollback DB commit; recipient/locale mismatch; consent violation; public preview mutation/data exposure; queue age/failed rate выше SLA; scheduler heartbeat missing; TLS verification off; unknown provider outcome replayed.

## 20. Риски FN-10

- **R-66 Critical** — public mail preview GET меняет tokens/coupons или раскрывает private order/user template data.
- **R-67 Critical** — SMTP TLS verification отключена; SwiftMailer boot customization несовместима с Laravel 9+.
- **R-68 Critical** — synchronous mail внутри order/payment/chat request создаёт partial state и duplicates при retry.
- **R-69 High** — queue=sync, worker/failed/restart topology не доказана; пустые tables не являются delivery evidence.
- **R-70 High** — file-cache scheduler locks не обеспечивают one-server contract; TTL/timezone/heartbeat не управляются.
- **R-71 High** — abandoned-cart cleanup/token/coupon flags не соответствуют enqueue/delivery и допускают lost/expired/duplicate campaign.
- **R-72 Critical** — marketing consent игнорируется, unsubscribe placeholder/unsigned; нет suppression/bounce/complaint lifecycle.
- **R-73 High** — overdue first run может создать batch, а send-before-marker/concurrency — duplicate.
- **R-74 High** — global locale, random content и remote assets делают mail nondeterministic и могут смешать язык.
- **R-75 High** — raw HTML и PII recipient/subject logging создают injection/privacy risk.
- **R-76 High** — queued PDF path не immutable, MIME неверен, artifact может исчезнуть/замениться до send.
- **R-77 High** — alt job ловит transient errors как success; sync observer блокирует request и bypass retry/failed semantics.

## 21. Definition of Ready FN-10

- закрыты/изолированы public mail preview side effects;
- production inventory подтверждает cron hosts, last runs, queue driver/workers, cache lock store, SMTP provider и bounce handling;
- утверждены transactional/marketing registry, consent/unsubscribe/suppression policy и семь locale fixtures;
- введены stable event/message IDs, outbox/receipt schema и reconciliation runbook;
- SMTP TLS verification включена, SwiftMailer customization заменена подтверждённым Symfony Mailer contract;
- пройдены duplicate/concurrency/crash/worker-restart/scheduler-lock tests;
- legacy остаётся rollback-compatible writer, production orders/chats/translations не теряются.

Следующий пакет после FN-10: **FN-11 — public catalogue и генераторы**. FN-10 возвращается к реализации сначала как отдельный security/operational stabilization этап, а не как побочный эффект обновления Laravel.
