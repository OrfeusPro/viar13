# 33. L3-01: production readiness, runtime и deployment

Дата среза: 16.07.2026. Статус: локальный read-only baseline завершён; production evidence batches 1–5 получены частично; реализация и deployment не выполнялись.

## 1. Назначение и границы

Этот пакет отвечает на вопрос, можно ли уже начинать migration implementation и планировать cutover. Ответ: **реализацию на изолированном staging начинать можно после создания воспроизводимой среды, production cutover планировать нельзя до закрытия evidence gates ниже**.

Scope clarification 15.07.2026: `test.viarcanvas.com` был исключён пользователем из аудита, поэтому прежние production conclusions его не учитывали. Execution update 17.07.2026: пользователь разрешил полностью заменить test и использовать его как disposable target staging. TEST-S02-A подтвердил отдельные DB/storage и отсутствие worker/cron, но выявил общий APP_KEY, общие SMTP/PayPal/Google credentials и `PAYPAL_SANDBOX=false`; до replacement обязателен backup+suspend по документу 60. Это не меняет production conclusions.

Проверялись локальная OSPanel-среда, tracked config проекта, scheduler/queue/cache/session/filesystem/logging contracts, наличие deploy/worker/backup definitions и безопасные метаданные локальной MariaDB. Значения `.env`, credentials/tokens, PII, тексты заказов, чатов, отзывов и переводов не извлекались. Код приложения, бизнес-данные и frontend assets не изменялись.

Обозначения:

- **[Локально]** — подтверждено только на рабочем компьютере;
- **[Код]** — подтверждено tracked source/config;
- **[Не подтверждено]** — требуется production evidence от DevOps/владельца;
- **Gate** — обязательное условие до соответствующего этапа.

## 2. Подтверждённый локальный runtime baseline

| Область | Факт | Вывод |
|---|---|---|
| ОС | Windows 11 Pro x64, build 26200 | Не является целевой production-спецификацией. |
| PHP | `C:\OSPanel\modules\php\PHP_7.4\php.exe`, PHP 7.4.30, ZTS, VC++ x64 | Это корректный legacy CLI baseline. Laravel 13 требует отдельный новый runtime; in-place replacement запрещён. |
| PHP extensions | Есть основные `bcmath`, `curl`, `dom`, `fileinfo`, `gd`, `imagick`, `mbstring`, `mysqli/mysqlnd`, `openssl`, `PDO/pdo_mysql`, `soap`, `sockets`, `zip`; Redis PHP extension не используется | Для current app Redis доступен через `predis`; target extension matrix нужно доказать отдельно. |
| Web | Apache 2.4.54 Win64; vhosts HTTP/HTTPS | Локальный web runtime не доказывает production FPM/Apache/Nginx topology. |
| Vhost | `viarcanvas.loc` → `C:/OSPanel/domains/asoft/viar/public` на 80/443; local TLS включён | Document root локально корректно направлен в `public`; production certificate/proxy/HSTS неизвестны. |
| `.htaccess` | `AllowOverride All`; HTTPS redirect в `public/.htaccess` закомментирован; есть hardcoded `viarcanvas.com` и большой locale redirect dataset | Redirect contract — часть приложения/deployment; переносить без golden crawl нельзя. |
| MariaDB | 10.6.9; локальная схема `viar` доступна | Версия и настройки production неизвестны. |
| Redis | Redis 5.0.14.1 локально, loopback | Фактическое использование production и HA/eviction/persistence неизвестны. |
| Services | Apache/MariaDB/Redis запущены OSPanel-процессами, не Windows services | Нет автоматического доказательства restart/startup policy. |
| Ports | 80/443/3306/6379 локально привязаны к loopback | Это безопасный local факт; network/WAF/LB production не проверялись. |

## 3. PHP и timezone contract

Локальный `php.ini`: `memory_limit=1536M`, `max_execution_time=60`, `post_max_size=150M`, `upload_max_filesize=150M`, `opcache.enable=off`, `opcache.enable_cli=off`, `date.timezone=Europe/Moscow`. В `config/app.php` timezone приложения — `Europe/Riga`. MariaDB сообщает `SYSTEM_TIME_ZONE=Europe/Kiev`, session/global `TIME_ZONE=Etc/GMT-3`.

Это три разных временных контекста. До staging нужно ввести единый контракт:

1. хранение событий и integration timestamps — ISO-8601 UTC;
2. явно выбранная business timezone для scheduler и пользовательского отображения;
3. одинаковая tzdata на web/CLI/workers/DB;
4. тесты DST для уведомлений, overdue, payments, delivery и cutover watermark;
5. сравнение timestamps выполняется в UTC, а не по server-local строкам.

`opcache` локально выключен. Это не дефект разработки, но performance/load evidence без production-like OPcache/JIT/process settings недействительно.

## 4. Безопасные метаданные локальной MariaDB

Срез после запуска локальной БД:

| Метрика | Значение | Значение для плана |
|---|---:|---|
| Tables | 185 | Все InnoDB; schema manifest нужен повторно на production snapshot. |
| Data + indexes | 111 230 976 bytes (~106 MiB) | Небольшой local snapshot не доказывает production volume/growth. |
| `jobs` | 0 | Нельзя заключать, что production queue пуста или не используется. |
| `failed_jobs` | 0 | Нет локального failure corpus/операционного процесса retry. |
| `sessions` | 18 на момент точного `COUNT(*)` | DB session table существует, но `.env.example` предлагает file sessions. |
| `cache`, `notifications` | таблицы не найдены | Driver/schema contract нельзя угадывать. |
| Triggers/routines/events | 0/0/0 | Production нужно проверить отдельно перед schema/data migration. |
| Charset/collation | `utf8mb4` / `utf8mb4_unicode_ci` | Нужен per-column/collation diff, особенно для locale/slug/emoji. |
| SQL mode | strict trans tables + division/no-auto-user/no-engine-substitution | Target/staging обязаны повторять production, не local. |
| Isolation | `REPEATABLE-READ` | Нужны concurrency tests orders/payments/status/outbox. |
| `max_allowed_packet` runtime | 16 MiB | Ниже local config-файла 32 MiB и PHP upload 150 MiB; большие payload/file paths могут расходиться. |
| Binary log | `log_bin=OFF`, `sync_binlog=0`, GTID strict off | Для local допустимо; production PITR/replication evidence отсутствует. |

Отдельный риск: значение `max_allowed_packet` активного сервера (16 MiB) отличается от найденного OSPanel config template (32 MiB). Поэтому в evidence всегда фиксируются **runtime values**, а не только содержимое конфигурационного файла.

## 5. Scheduler, queue, cache и sessions

### 5.1 Scheduler в коде

`app/Console/Kernel.php` регистрирует:

- `UserNotify:cron` — ежедневно, `withoutOverlapping`;
- `orders:notify-overdue-delay` — ежедневно в 18:00, `withoutOverlapping`;
- `cart:send-recovery-emails` — ежечасно, `withoutOverlapping`;
- `cart:send-recovery-emails-coupons` — ежечасно, `withoutOverlapping`;
- `GenSmallImages:run` — ежедневно, `withoutOverlapping`.

Однако локальный OSPanel cron пуст, подходящей Windows Scheduled Task нет, а в репозитории нет invocation `artisan schedule:run`. Следовательно, **расписание описано, но его runtime owner локально не доказан**. На multi-host `withoutOverlapping` безопасен только при общем lock-store и корректном TTL/clock contract.

### 5.2 Queue

`.env.example` задаёт `QUEUE_CONNECTION=sync`; `config/queue.php` по умолчанию также `sync`. Database/Redis connections существуют в config, но worker/Supervisor/systemd/service manifests, queue purpose map, retry/backoff/timeout/restart policy и monitoring отсутствуют.

До переноса mail/webhook/file jobs необходимы отдельные очереди по назначению, durable idempotency/outbox, `failed_jobs` runbook, rolling-deploy compatibility и controlled drain. Нельзя считать `jobs=0` доказательством отсутствия async workloads.

### 5.3 Cache и sessions

- `.env.example`: `CACHE_DRIVER=file`, `SESSION_DRIVER=file`, `SESSION_LIFETIME=120`;
- `config/cache.php`: default `file`;
- `config/session.php`: fallback `database`, lifetime `720`;
- локальная DB содержит `sessions`.

Это config drift. До staging требуется подписанная матрица driver/lifetime/cookie domain/Secure/HttpOnly/SameSite/encryption/locking для web и admin. File driver несовместим с несколькими stateless web hosts без sticky sessions; database/Redis требуют capacity, expiry, failover и privacy policy.

## 6. Storage, uploads и logs

### 6.1 Storage

Локально `public/storage` — Junction на `storage/app/public`. В репозитории нет deployment step, который создаёт и проверяет link/permissions. На target Linux Junction не переносится как объект; нужен явный `storage:link`/mount/object-storage contract и smoke test read/write/delete/download.

Локальный metadata snapshot:

- `storage/app`: 72 files, 8 471 216 bytes;
- `storage/app/public`: 69 files, 8 454 901 bytes;
- `storage/framework`: 78 files, 1 247 688 bytes;
- `storage/logs`: 6 files, 25 541 133 bytes.

Эти числа не являются production inventory и не заменяют file corpus/hash/orphan audit из AUD-008.

### 6.2 Logging

`config/logging.php` по умолчанию использует `stack → single`, то есть один `storage/logs/laravel.log`; channel `daily` определён, но не выбран по умолчанию. Metadata показала `laravel.log` около 25 MB. Есть отдельные mail/PayPal/Synvolve/alt-generation logs. Содержимое логов не читалось.

Нужны rotation/retention/central aggregation, correlation IDs (`order_id`, безопасный `event_id`, provider receipt), redaction и запрет secrets/PII/full payload. `config/dompdf.php` использует `storage/logs/` как tempDir — временные PDF-файлы должны быть вынесены в отдельный ограниченный каталог с cleanup.

### 6.3 Artifact hygiene

Tracked repository содержит `.tmp_order_dump.php` и `resources/views/theme/viar/pages/simpsons/modal.blade_backup.php`. Содержимое первого намеренно не читалось; само наличие deployable temp/dump и backup-копии требует owner review, secret/PII scan и решения `remove|quarantine|document`. До решения эти файлы нельзя автоматически включать в новый release artifact.

## 7. Deployment и restore readiness

В tracked project не найдены:

- Docker/Compose или другой immutable environment definition;
- CI/CD pipeline;
- deploy/release script и atomic symlink/current-release contract;
- Supervisor/systemd/Windows service definitions;
- scheduler/worker owner definitions;
- backup/restore scripts;
- documented `artisan down/up`, cache warmup/clear или `storage:link` release sequence.

Это не означает, что production автоматизации нет вне репозитория. Это означает, что она **не предоставлена аудиту**, поэтому release и rollback сейчас невоспроизводимы.

Для действующего магазина rollback приложения нельзя строить как restore старого DB dump: пока production принимает заказы, такой restore уничтожит новые `orders`, платежи, сообщения, статусы, контент и переводы. Rollback должен возвращать application/read-write ownership на legacy, сохраняя forward-only DB compatibility и replay/reconciliation journal.

## 8. Production evidence request

Полный машинно-обрабатываемый запрос находится в `appendix-production-evidence-request.csv`. Минимальный пакет:

1. topology diagram: LB/proxy/web/CLI/workers/DB/Redis/storage/mail/integrations и количество hosts;
2. runtime version/extension/config sheet без secrets;
3. sanitized production `.env` **key presence/type only**, effective config/cache state;
4. scheduler owner, последние успешные/ошибочные executions и overlap/lock evidence;
5. worker manifests, queue depth/age/failures/retry/restart evidence;
6. DB metadata, size/growth, binlog/PITR/replica/retention and restore drill;
7. storage volumes/permissions/free space/backups/orphan counts;
8. central logs/metrics/alerts/redaction/retention and incident contacts;
9. deployment artifact/build/healthcheck/cache/migration/rollback runbook;
10. 30–90 day anonymized traffic/error/latency/cron/queue/provider volume summaries.

Evidence передаётся без credentials, tokens, cookies, email/phone/address, message/order text и raw provider payload. При необходимости используются counts, buckets, hashes и redacted samples.

## 9. Go/no-go gates

| Gate | До чего | Условие закрытия |
|---|---|---|
| L3-G01 | Начало target staging | Воспроизводимый PHP 8.3/8.4 runtime, extensions, DB/Redis/storage topology и sanitized config manifest. |
| L3-G02 | Перенос scheduler/mail/jobs | Один scheduler owner, purpose queues, workers, locks, retry/failed/drain/restart/monitoring протестированы. |
| L3-G03 | Data rehearsal | Проверенный encrypted backup, PITR/replica facts, restore в изолированную среду, RPO/RTO измерены. |
| L3-G04 | File/PDF rehearsal | Production metadata corpus, permissions, free-space/capacity, link/mount creation и orphan/reference report приняты. |
| L3-G05 | Canary | Central logs/metrics/alerts, correlation/redaction и rollback triggers работают. |
| L3-G06 | Cutover | Final delta/reconciliation, one-writer matrix, orders/payments/chats/translations invariants, forward-compatible rollback rehearsal подписаны. |

## 10. Рекомендованная последовательность до начала разработки

1. DevOps заполняет production evidence CSV и прикладывает redacted artifacts.
2. Создаётся изолированный staging, максимально повторяющий production topology, но без live provider credentials.
3. Выполняется restore drill production backup в staging; фиксируются RPO/RTO и checksum/count manifests.
4. Legacy PHP 7.4 baseline запускается на staging и проходит characterization smoke suite.
5. Создаётся новый Laravel 13 runtime рядом, не заменяя legacy PHP/web/DB.
6. Вводятся CI, immutable artifact, health checks, scheduler/worker manifests и централизованная наблюдаемость.
7. Только после L3-G01…G05 запускаются shadow/read-only waves и component cutover rehearsals.

## 11. Итог

Статический аудит функций FN-01…FN-13 достаточен для планирования работ, но production readiness пока не доказана. Критические пробелы: неизвестная production topology, отсутствующие в репозитории deployment/cron/worker/backup contracts, config drift queue/session/timezone, неограниченный single log, недоказанный storage/restore/PITR и live-write rollback.

Следующее точное действие: получить redacted evidence pack по `appendix-production-evidence-request.csv`, провести restore drill и закрыть L3-G01…G03 до начала migration implementation, затрагивающей данные или фоновые процессы.

## 12. Production evidence — batch 1 от 14.07.2026

Статус: **частично подтверждено пользователем через read-only команды**. Секреты, `.env`, содержимое логов и business rows не передавались. IP-адреса из исходного вывода в документации не сохраняются.

### 12.1 Подтверждённые факты

| Область | Production evidence | Влияние |
|---|---|---|
| ОС | Ubuntu 24.04.4 LTS, kernel 6.8, x86_64; uptime ~70 дней | Linux target подтверждён, но patch/reboot policy и monitoring ещё неизвестны. |
| Ресурсы | 4 vCPU, 7.6 GiB RAM, ~4.1 GiB available; swap отсутствует | Image/PDF, queue, Composer/deploy и DB peaks требуют memory/load rehearsal; OOM не имеет swap buffer. |
| Диск | Один ext4 root volume 75 GiB; использовано 54 GiB, доступно ~19 GiB, 75% | Backup/restore, logs, uploads и parallel release могут заполнить общий диск; нужны growth/quota/alert и off-host backup. |
| PHP CLI | PHP 7.4.33 NTS + ionCube; `/etc/php/7.4/cli/php.ini`; memory 512M, execution unlimited, upload/post 100M | Legacy CLI baseline подтверждён. CLI settings не доказывают FPM settings. Unlimited CLI execution требует timeout у cron/commands. |
| PHP extensions | CLI имеет mysql/PDO, intl, imagick, gd, mbstring, curl, openssl, soap, sockets, zip, APCu и др. | Legacy extension set подтверждён частично; нужен отдельный FPM 7.4 и target 8.3 effective matrix. |
| PHP-FPM | Одновременно запущены `php7.4-fpm` и `php8.3-fpm`; PHP 8.3.30 FPM имеет OPcache | Фактический socket/version production vhost пока не установлен; dual runtime полезен для staging, но требует строгой привязки vhost/service/release. |
| Web | Nginx 1.29.8; Apache отсутствует | Production web owner установлен. Package source/update policy и active vhost includes ещё нужны. |
| Document root | `viarcanvas.com` → `/home/admin/web/viarcanvas.com/public_html/public` | Laravel public-root contract production подтверждён. |
| HTTP limits | Global Nginx `client_max_body_size=1024m`, body timeout 180s, FastCGI/proxy read timeout 300s; PHP CLI post/upload 100M | Nginx способен буферизовать значительно больше, чем приложение/PHP принимает; slow/large body DoS и inconsistent rejection являются production-confirmed частью R-138. |
| TLS/HSTS | HTTP/HTTPS vhosts есть; HSTS directive не обнаружена в полном `nginx -T` filter | Отсутствие HSTS на уровне Nginx вероятно, но response/CDN/app header ещё нужно проверить безопасным HEAD-запросом. |
| Logs | Domain access/bytes/error logs настроены; глобальный access log выключен, но vhost logs включены | Наличие файлов подтверждено; rotation, retention, central aggregation, redaction и alerts ещё неизвестны. |
| DB/cache | MariaDB 10.11.16 и Redis запущены; оба слушают loopback | Network exposure ограничен локально. Schema metadata, binlog/PITR/replica, Redis persistence/eviction и actual Laravel drivers ещё нужны. |
| Cron/timers | `cron.service` запущен; systemd timer для Laravel/scheduler не найден; виден только системный dpkg DB backup timer | Это не доказывает отсутствие user/root/Hestia cron. Нужны `crontab`/`/etc/cron.d` и exact scheduler owner. |
| Host coupling | На том же host доступны production web, mail services, DNS, FTP, SSH и Hestia panel; DB/Redis локальны | Один host и расширенный service perimeter создают общий availability/security blast radius; hardening/backup evidence обязательно. |

### 12.2 Уточнённые риски

- **R-128/R-134:** production OS/web и dual PHP services частично подтверждены, но active FPM socket, FPM ini/pool/OPcache и target release binding ещё неизвестны.
- **R-131/R-136:** 19 GiB свободного места — недостаточное доказательство capacity; uploads/logs/backups/releases разделяют один root volume.
- **R-133:** system clock — UTC, PHP CLI timezone пустой; app/FPM/DB/scheduler timezone contract пока не доказан.
- **R-138:** production mismatch подтверждён: Nginx 1024 MiB против PHP 100 MiB, плюс длинные timeouts.
- **R-139:** production document root корректен; Nginx HSTS не найден, TLS/proxy/redirect response contract остаётся открытым.
- **R-140/R-141:** добавлены shared-host perimeter и no-swap/capacity risks.

### 12.3 Что ещё требуется от production

1. Active `viarcanvas.com` FPM socket/version и selected FPM 7.4/8.3 effective limits/OPcache/pool settings.
2. User/root/Hestia cron, Supervisor/systemd workers и `schedule:run` owner.
3. Safe ENV driver values, config-cache fingerprint, storage/log metadata.
4. MariaDB variables/schema/volume/binlog/PITR/replica и Redis persistence/eviction.
5. TLS certificate/protocol/HSTS response, logrotate/retention/alerts.
6. Deployment method, backups и isolated restore evidence.

### 12.4 Production evidence — batch 2

| Область | Подтверждённый факт | Вывод / gate |
|---|---|---|
| Active runtime | Production `viarcanvas.com` HTTP/HTTPS vhosts используют `unix:/run/php/php7.4-fpm-viarcanvas.com.sock` | Legacy production однозначно работает на PHP 7.4.33 FPM; PHP 8.3 нельзя считать target app staging до создания отдельного vhost/pool/release. |
| PHP 7.4 FPM | NTS + ionCube; отдельный pool `viarcanvas.com`; 512M memory, 300s execution, 100M post/upload, 20 files, file sessions; timezone не задана | FPM baseline подтверждён. OPcache в whitelisted `phpinfo` не появился и требует явной проверки module/config; pool sizing/slowlog/timeout ещё нужны. |
| PHP 8.3 FPM | 8.3.30 + ionCube + OPcache; generic pool `www`; 512M memory, 300s execution, 100M post/upload; timezone UTC; file sessions | Наличие binary не доказывает Laravel 13 readiness. Нужны dedicated pool, extension/composer platform check, isolation и target load rehearsal. |
| FPM load | PHP 7.4 service обработал >500k requests с момента restart, peak memory ~924 MiB; PHP 8.3 service peak memory ~3 GiB | На host без swap memory peak подтверждает R-141; нужны pool limits, причина peak, OOM/journal/metrics и alert thresholds. |
| Session runtime | Production PHP 7.4 FPM использует `/var/lib/php/sessions` | Нужны permissions, cookie-domain/name, cleanup/retention и continuity-проверка production sessions. |
| HTTPS | Отдельный SSL vhost и force-SSL include существуют | Содержимое force-SSL и фактический response chain/HSTS ещё не доказаны. |
| DB admin surface | Production vhost подключает `phpmyadmin.inc*` и `phppgadmin.inc*`; service journal содержит внешние rejected login attempts | Публичная достижимость, rate limit/fail2ban/IP allowlist/VPN и необходимость этих tools должны быть проверены немедленно; raw email/IP в документацию не включаются. |

Batch 2 закрывает неопределённость active FPM: legacy production использует PHP 7.4. Evidence L3-E02 остаётся `partial`, потому что target dedicated pool/extension/config parity и signed compatibility matrix ещё отсутствуют.

### 12.5 Production evidence — batch 3

| Область | Подтверждённый факт | Вывод / gate |
|---|---|---|
| PHP 7.4 OPcache | Module/config не обнаружены в FPM `phpinfo` и `/etc/php/7.4/fpm/conf.d` | Legacy production выполняет PHP без opcode cache. Это объясняет часть CPU overhead; performance baseline нельзя переносить на target, а включение OPcache на legacy требует отдельного compatibility/canary test. |
| Production pool | `viarcanvas.com`: PHP 7.4, `ondemand`, `max_children=8`, `max_requests=4000`, idle timeout 10s, socket 0660, user/group `admin` | При memory limit 512M теоретический PHP budget pool около 4 GiB; нужны RSS/p95/load/OOM metrics и explicit pool budget. |
| PHP 8.3 dev pool | `dev.viarcanvas.com`: dedicated PHP 8.3 pool, `ondemand`, `max_children=8`, user/group `admin`; ранее Nginx показывал suspended root | Это возможная основа staging, но сейчас не является доказанным target environment: нужны активный app root, отдельные secrets/DB/Redis/storage/provider sandbox и resource limit. |
| Shared PHP services | Помимо production pool на host запущены другие PHP-FPM services | Их суммарная фактическая конкуренция за ресурсы production-host и cgroup/systemd limits не заданы в полученном evidence. |
| Slow request controls | В pool whitelist не найдены `request_terminate_timeout`, `request_slowlog_timeout`, `slowlog` | PHP `max_execution_time=300` есть, но отдельный FPM kill/slowlog contract не доказан; диагностируемость зависаний ограничена. |

Batch 3 усиливает R-141 и добавляет R-144. Для production необходимо получить только фактические **несекретные** effective flags/drivers; значения APP_KEY, паролей, OAuth/payment/mail credentials не извлекаются и не передаются.

### 12.6 Production evidence — batch 4: production permissions и perimeter

Переданное сравнение production/test исключено из оценки по уточнённой границе аудита. Ниже сохранены только самостоятельно значимые production-факты.

| Контур | Evidence | Вывод |
|---|---|---|
| Production filesystem | Project root и storage принадлежат `admin:admin`; `public/storage` указывает в production `storage/app/public` | Link target production подтверждён; нужны capacity, backup и read/write/delete/download smoke tests. |
| Production `.env` permissions | `/home/admin/web/viarcanvas.com/public_html/.env`: `-rw-rw-r-- admin:admin` (mode 664) | Файл с production secrets читается любым локальным пользователем и доступен на запись группе `admin`. Это P0 permissions defect независимо от test. |
| PHP sessions directory | `/var/lib/php/sessions`: sticky writable directory; production pool работает как `admin` | Нужны проверка ownership отдельных session files, cleanup/retention, cookie policy и continuity; содержимое sessions не извлекать. |
| HTTPS probe | Force-SSL include возвращает 301 на HTTPS; curl с самого host к публичному адресу не проходит | Вероятна hairpin/firewall/network peculiarity, не downtime: внешний read-only probe открыл public homepage. HSTS/security headers всё ещё не доказаны. |
| DB admin tool | Пользователь подтвердил публичную доступность phpMyAdmin | R-143 считается production-confirmed; phpPgAdmin reachability/controls ещё проверить извне или через local vhost resolve. |

### 12.7 P0 production hardening без остановки магазина

1. Не менять production `APP_KEY`, provider credentials или session settings в рамках аудита.
2. Закрыть public phpMyAdmin через VPN/IP allowlist либо отдельный admin host; проверить rate limit/fail2ban и удалить неиспользуемый phpPgAdmin include.
3. После проверки runtime owner изменить production `.env` на least privilege (`600` либо обоснованный `640`) и включить контроль прав файла.
4. Получить фактические production nonsecret flags/drivers, cron/workers, DB/Redis operational settings и backup/restore evidence.
5. Проверить production security headers, log rotation/redaction/alerts, session cleanup и storage smoke/capacity.

### 12.8 Production evidence — batch 5: effective app, scheduler, worker и admin surface

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| Application | Laravel 6.20.40; `APP_ENV=production`, `APP_DEBUG=false`, canonical APP_URL HTTPS | Environment/debug baseline корректен; target migration всё равно требует воспроизводимого config manifest. |
| Effective drivers | cache=file, queue=redis, session=database, Secure cookie=true, session domain=`viarcanvas.com`, lifetime=2880 min | Queue ранее не была `sync`; session живёт 48 часов. Нужны queue lifecycle и owner-approved session/admin TTL, locking, cleanup/capacity tests. |
| Config cache | `bootstrap/cache/config.php` отсутствует; присутствуют generated packages/services manifests | Config-cache drift сейчас отсутствует, но release artifact/config warmup contract не задан; package/service manifests имеют mode 755 и требуют воспроизводимого ownership/permission шага. |
| Scheduler | Admin crontab содержит только `MAILTO`; root crontab пуст; в cron/Hestia/systemd timers нет `schedule:run`/`schedule:work` | Для пяти jobs из `Console\\Kernel` автоматический production owner не обнаружен. Нельзя запускать накопившиеся задачи массово без оценки побочных эффектов и reconciliation. R-129 подтверждён. |
| Queue worker | `QUEUE_CONNECTION=redis`; активен systemd `viar-alt-worker.service`: queues `alt-gen,default`, tries=2, delay=60, timeout=180, sleep=3, memory=256 | Worker существует, но unit restart/kill/working-directory/log contract, queue depth/age/failures и drain/redeploy не доказаны. R-130 уточнён. |
| Synvolve receiver | `SYNVOLVE_TEST_RECEIVER_ENABLED=true`; source routes публичны, только runtime flag gate; controller сохраняет полный request и отдаёт latest payload | Это production-confirmed P0 exposure R-120. Требуется немедленное согласованное flag-off containment и проверка сохранённых файлов/log/access evidence без раскрытия payload. |
| Synvolve TLS | `SYNVOLVE_WEBHOOK_VERIFY_SSL=false`; service явно отключает peer/hostname verification | Исходящие CRM webhook могут принять подменённый TLS endpoint. Добавлен R-148; до исправления не выполнять массовый replay. |
| SMTP | host `mail.viarcanvas.com`, port 25, encryption null | Нельзя утверждать шифрование транспорта. Нужны DNS/route и STARTTLS/certificate evidence; добавлен R-149. |
| Payments/OAuth | PayPal sandbox=false; Facebook login disabled | PayPal работает в live mode: любые проверки только read-only/provider sandbox, без payment calls. Facebook flag уменьшает текущую поверхность. |
| DB admin surface | `/phpmyadmin` без Nginx allowlist/auth_basic/limit_req; internal directories denied; phpMyAdmin работает через PHP 8.3 generic socket | Fail2ban `phpmyadmin-auth` — положительный компенсирующий контроль, но не заменяет network allowlist. R-143 подтверждён. phpPgAdmin include содержит placeholder alias; фактическая доступность не доказана. |

Batch 5 переводит L3-E05 и L3-E06 из `open` в `partial`: отсутствие scheduler owner доказано, worker найден, но acceptance criteria не выполнены. До следующего пакета никаких production config/flag/service изменений аудитом не выполняется.

### 12.9 Production evidence — batch 6A: DB volume и Redis runtime

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| Database volume | 185 tables; data 591.36 MiB, indexes 23.22 MiB, total 614.58 MiB | Production DB существенно больше local snapshot. Для L3-E07 ещё нужны engine/version/variables, growth history, binlog/PITR и signed metadata baseline. |
| Sessions | `sessions` — 357.02 MiB и приблизительно 34 922 rows по InnoDB estimate, то есть около 58% всей DB | При database sessions и lifetime 2880 минут нужны exact active/stale buckets, cleanup owner, admin TTL decision и capacity/locking tests. Добавлен R-150. |
| Failed jobs | `failed_jobs` — 58.59 MiB и приблизительно 7 520 rows по InnoDB estimate | Наличие крупного failure corpus подтверждено, но payload/exception не читались. Нужны exact counts/age/queue aggregates, owner disposition и безопасный retry/reconciliation runbook; массовый retry запрещён. R-130/R-150 уточнены. |
| Business tables | `translations` 39.03 MiB/~58 498 estimated rows; `orders` 38.27 MiB/~12 478; `image_alt_suggestions` 55.42 MiB/~9 745 | Final migration sizing должен опираться на production metadata и повторный watermark/delta snapshot; estimates не заменяют exact reconciliation counts. |
| Redis runtime | Redis 7.0.15; на проверенном logical DB `DBSIZE=0`; memory 1.21 MiB, peak 10.84 MiB; 2 clients, no blocked/rejected connections, no evictions | Пустой проверенный DB не доказывает, что `alt-gen/default` используют тот же logical DB: нужны queue-size output и connection/database mapping без credentials. |
| Redis limits/persistence | `maxmemory=0`, policy `noeviction`; AOF disabled; RDB schedule `3600 1 300 100 60 10000`; last successful save 2026-07-13 09:36:17 UTC | Redis может расти до host memory limit, а queue durability зависит от RDB/reconciliation. Нужны systemd limits, selected DB mapping, restart-loss contract и alerts. Добавлен R-151. |

Batch 6A расширяет L3-E06/L3-E07/L3-E18, но оставляет их `partial`: не получены начало probe с exact queue/failed/session counts, MariaDB variables и worker unit/lifecycle. Production data/config/services не изменялись.

### 12.10 Production evidence — batch 6B: exact queue/session/failure aggregates

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| Queue mapping | Laravel queue driver `redis`, connection `default`, logical DB 0; `default=0`, `alt-gen=0` на момент probe | Проверенный ранее Redis `DBSIZE=0` относится к тому же logical DB; текущего ready/delayed/reserved backlog через Laravel `size()` не обнаружено. Это не закрывает исторические failures и restart-loss contract. |
| Sessions | Exact 40 359 rows; 40 165 имеют `last_activity` в пределах 48 часов, 194 старше 48 часов, ни одной старше 7 дней; reported range 2026-07-14 07:53:45…2026-07-16 08:03:03 | Session lottery фактически удаляет старые rows; первоначальная гипотеза о долгом stale retention не подтверждена. Но 40k rows/357 MiB за 48-часовое окно означают высокий churn и требуют traffic/bot correlation, owner-approved TTL и DB capacity/index tests. Эти строки нельзя интерпретировать как 40k пользователей. |
| Failed jobs | Exact 11 214 rows; все `redis/alt-gen`, все возникли 2026-07-10 21:12:44…21:27:37; за последние 24 часа failures нет | Подтверждён единичный массовый failure burst, а не равномерный фон. Нельзя делать `queue:retry all`: сначала агрегировать exception class без текста/payload, связать с `image_alt_suggestions` statuses и определить идемпотентный reconciliation contract. |

Code correlation: `GenerateImageAltJob` использует queue `alt-gen`; `resolveSuggestion()` выполняется до общего `try`, а `DailyLimitReached` делает release на 3600 секунд. Worker запускается с `--tries=2`, тогда как job декларирует `$tries=3`. Возможный конфликт release/max-attempts является **гипотезой**, пока не получена безопасная агрегированная классификация exception и unit/log timing evidence.

L3-E06/L3-E18 остаются `partial`. Production queue/session/job rows не изменялись; payload, exception text, session IDs/data и suggestion paths не читались.

### 12.11 Production evidence — batch 6C: ALT failure classification и locale corpus

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| Failed exception class | Все 11 214 `failed_jobs` классифицированы как `MaxAttemptsExceededException` | Worker исчерпал допустимое число attempts для всего burst. Это подтверждает failure mechanism, но не первичную причину release/failure; bulk retry запрещён. |
| ALT statuses | Exact `image_alt_suggestions`: 13 131 rows, из них 1 834 `pending` и 11 297 `failed` | Failed suggestions на 83 больше failed-job rows: таблицы нельзя очищать или сопоставлять 1:1 без reconciliation по suggestion/receipt state. |
| Locales | В `pending` и `failed` представлены все семь production locale codes: `de`, `ee`, `en`, `lt`, `lv`, `pl`, `ru` | `ee` сохраняется как подтверждённый legacy identifier; автоматическая нормализация в `et` до owner-approved mapping запрещена. ALT reconciliation и migration golden tests обязательны для всех семи codes. |
| Timing | `pending` создавались/обновлялись 10.07.2026; основная часть `failed` появилась 10.07, но для шести локалей newest update достигает 13.07 | `failed_jobs` burst 10.07 не объясняет все поздние status updates. Нужна безопасная классификация `image_alt_suggestions.error` и producer/run history без вывода error text/path. |
| MariaDB version | `10.11.16-MariaDB-ubu2404-log` | Production engine version подтверждена. Variables probe не завершён: `SHOW VARIABLES ... ORDER BY` отвергнут MariaDB syntax; это read-only query error без изменений данных. |

Связка `MaxAttemptsExceededException` + worker `--tries=2` + job `$tries=3` подтверждает попыточный mismatch. Связь именно с `DailyLimitReached->release(3600)` остаётся гипотезой до safe error-category aggregates и worker unit/log timing evidence.

L3-E06/L3-E07 остаются `partial`; exception/payload/error text и бизнес-данные не выводились, production state не изменялся.

### 12.12 Production evidence — batch 6D: MariaDB variables, ALT categories и worker unit

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| MariaDB durability | MariaDB 10.11.16; `log_bin=OFF`, `binlog_format=MIXED`, `innodb_flush_log_at_trx_commit=1`, `sync_binlog=0` | InnoDB commit durability включена, но binlog/PITR отсутствует на этом server. L3-G03 закрыть нельзя до backup inventory и isolated restore; R-132 production-confirmed. |
| MariaDB runtime | UTC system/timezone, `REPEATABLE-READ`, strict/ONLY_FULL_GROUP_BY sql mode, utf8mb4/general_ci, event scheduler off | Target/staging должны воспроизвести timezone, isolation, SQL mode и collation либо пройти явные compatibility/golden tests. DB event scheduler не является скрытым owner Laravel jobs. |
| DB capacity | Buffer pool 128 MiB при DB 614.58 MiB; packet 32 MiB; max connections 500, peak 16; slow log on/5 s, 152 slow queries за uptime 6 282 137 s; aborted connects 64 | Connection headroom есть, но buffer/cache working set и 32 MiB cross-layer limit требуют metrics/load/boundary tests. Slow-log path/rotation/retention и query aggregates ещё нужны без SQL/PII. |
| ALT error categories | Failed suggestions: 11 212 `MAX_ATTEMPTS`, 50 `AUTH_ERROR`, 3 provider rate-limit, 32 `OTHER`; total 11 297 | Max-attempt burst доминирует, но отдельный provider-auth incident 13.07 и неизвестные errors требуют owner/provider credential state review без раскрытия secrets/error text. Bulk replay запрещён. |
| Worker unit | Enabled/active systemd unit, user/group `admin`, correct production cwd; queues `alt-gen,default`, tries 2, timeout 180, memory 256; `Restart=always`, 5 s | Runtime owner доказан. Unit не содержит explicit deploy drain/restart contract; attempts расходятся с job declaration. L3-E06 остаётся partial. |
| Worker stop/restarts | `TimeoutStopSec=90s`, `KillMode=control-group`, `NRestarts=132`, last/current main status 0; current memory ~50.8 MiB, peak ~100.9 MiB | Stop grace короче job timeout 180 s и способен оборвать in-flight job; restart counter 132 требует period/reason evidence — status 0 не доказывает причины всех предыдущих exits. Добавлен R-152. Memory ceiling сейчас не исчерпан. |
| Worker logs | stdout/stderr append в `storage/logs/queue-alt-worker.log` и `queue-alt-worker-error.log` | File ownership известен, но rotation/retention/redaction/size не доказаны; R-131 уточнён. |

Safe ALT classification не выводила `error`, payload, paths или provider response. MariaDB/systemd probes были read-only; production data/config/services не изменялись.

L3-E06/L3-E07 расширены, но остаются `partial`. Следующий обязательный gate — L3-E08/L3-E09: backup metadata, off-host/retention/encryption и isolated restore drill с RPO/RTO.

### 12.13 Production evidence — batch 7A: backup inventory

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| Hestia backups | `v-list-user-backups admin` возвращает пустой список | Для production user нет зарегистрированного Hestia backup artifact. Это не исключает provider/hypervisor backup, но требует отдельного доказательства. |
| Local backup directory | `/backup` существует, но `admin.*` artifacts отсутствуют во всех age buckets | Локальный application/database restore source не обнаружен. Не создавать ad-hoc archive до capacity/load/retention решения. |
| Failure domain | `/backup` и `/` находятся на одном `/dev/sda1` ext4 volume, 75 GiB, 75% used/~19 GiB free | Даже будущая копия в `/backup` не защищает от disk/host loss и может конкурировать с uploads/logs/releases за 19 GiB. Нужен off-host artifact. |
| Scheduled backup | В cron найден только файл `/var/spool/cron/crontabs/hestiaweb` с backup reference; exact command ещё не проверен. Из timers виден только `dpkg-db-backup.timer` | `dpkg-db-backup` не является backup MariaDB/application. Cron reference не доказывает successful artifact; нужны sanitized schedule/command и logs. |

На основании batch 7A L3-E08 и L3-E09 остаются `open`, а L3-G03 — `blocked for migration start`: нет доказанного encrypted/off-host backup, last success, retention, recoverable artifact или isolated restore drill. Формулировка не утверждает отсутствие provider-level snapshots, пока hosting owner не предоставил metadata evidence.

Production state не изменялся: backup не запускался, archives не читались и restore не выполнялся.

### 12.14 Production evidence — batch 7B: Hestia backup schedule

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| Queue processor | User `hestiaweb` cron запускает `v-update-sys-queue backup` каждые 5 минут | Backup queue owner/schedule существует, но успешность и наличие queued/completed jobs не доказаны. |
| Daily backup | User `hestiaweb` cron запускает `v-backup-users` ежедневно в 05:10 | При system timezone UTC ожидаемое расписание — 05:10 UTC, если cron не имеет отдельного timezone override. Это schedule evidence, не restore artifact. |
| Schedule/outcome gap | Несмотря на daily schedule, Hestia user backup list и `/backup/admin.*` пусты | Нужно проверить `admin` backup flag/retention/backend и только aggregate execution/error evidence. Возможны disabled backup, failed runs, zero/short retention или external backend; ни одна гипотеза пока не подтверждена. |

L3-E08 переведён из `open` в `partial` только по schedule owner. L3-E09 и L3-G03 остаются открыты/блокирующими: last success, artifact, off-host/encryption/retention и restore отсутствуют в evidence.

Cron не запускался вручную, production state не изменялся.

### 12.15 Production evidence — batch 7C: заявленный Google Drive backup

Пользователь сообщил, что backup, вероятно, хранится в Google Drive. Прямая проверка Drive, remote configuration и artifact metadata по решению пользователя пропущена. Поэтому факт учитывается как **owner-reported / unverified**, а не как recoverability evidence.

L3-E08 остаётся `partial`, L3-E09 — `open`, L3-G03 не закрыт. Для последующего закрытия достаточно будет предоставить без содержимого backup: timestamp последнего успеха, размер, retention, encryption/access owner и результат isolated restore. Токены, Drive credentials и содержимое архива не требуются.

### 12.16 Backup operating decision: owner-managed manual copies

Пользователь подтвердил готовность выполнять backup вручную. Для текущего проекта это допустимый временный operating control, если каждый migration checkpoint включает согласованный DB dump и файловый snapshot, копия физически уходит с production volume, фиксируются timestamp/size/checksum, а хотя бы один комплект проходит isolated restore drill.

Автоматический schedule/monitoring остаётся рекомендуемым улучшением, но отсутствие автоматизации само по себе не блокирует подготовку staging. L3-G03 закрывается не заявлением о ручном backup, а проверяемым artifact + restore result + принятыми RPO/RTO. Production backup нельзя создавать в пиковое время или оставлять единственной копией на `/dev/sda1`.

### 12.17 Production evidence — batch 8A: release, logs и monitoring

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| Release topology | Document root указывает прямо на `/home/admin/web/viarcanvas.com/public_html`; `current/releases/shared` не найдены | Atomic/symlink release и instant application rollback отсутствуют. Existing mutable directory остаётся единственным production artifact. |
| Git state | Production — Git repo branch `master`, HEAD `44bab2f627cf6a81697ab2ff5bc2539429ef3d4a`, commit time 15.07.2026; 21 changed/untracked paths, из них 3 untracked | HEAD не описывает фактический runtime tree. До artifact manifest/diff classification нельзя воспроизвести deploy или гарантировать rollback. Добавлен R-154. |
| Deploy tooling | Deploy/release/rollback/health scripts в проверенных project/admin paths не найдены | Release выполняется вне versioned runbook либо вручную; healthcheck, migration/cache/worker drain и rollback owner не доказаны. R-128 подтверждён production evidence. |
| Backend artifact | `vendor/autoload.php` обновлён 10.07.2026, тогда как composer files датированы 04.10.2025 | Vendor artifact мог меняться отдельно от lock/commit; нужен installed-package manifest/hash и clean staged build, без запуска Composer на production. |
| Frontend artifact | `webpack.mix.js`, package-lock и `public/mix-manifest.json` присутствуют; Vite config/build manifest отсутствуют. Mix manifest/lock датированы 26.03.2022, package.json — 17.03.2026 | Production подтверждает frozen legacy Mix artifact и package/lock/manifest drift. Автоматический Vite migration/rebuild запрещён; переносить байт-в-байт и проверять hashes/browser. R-13/R-86 уточнены. |
| Application logs | `storage/logs` = 323 MiB; current `laravel.log` ~125.3 MiB mode 755, compressed history ~132.2 MiB; `alt-gen-skipped.log` ~61.2 MiB, `mail.log` ~9.8 MiB | Laravel rotation существует, но current permissions и custom-log rotation/redaction/retention не приняты. Большие logs конкурируют за 19 GiB free disk. R-131/R-141 уточнены. |
| Worker logs | stdout ~9.5 MiB и stderr ~0.3 MiB, owner `root:root`, mode 644; unit process работает как `admin` | systemd открывает append targets с ownership, отличным от app owner; rotation и operational access contract не доказаны. |
| Nginx logs | Active access ~64.6 MiB, previous ~136.2 MiB плюс compressed history; error logs существенно меньше; nginx logrotate rule найден | Web log rotation существует. Retention, anonymization/access и aggregate traffic profile ещё не проверены. |
| MariaDB logs | Slow log ON, threshold 5 s, relative file `viarcanvas-slow.log`; error log `/var/log/mysql/error.log`; active MariaDB logrotate config найден | Фактический slow-log absolute path/size/rotation match ещё нужен; SQL text не читается в рамках evidence collection. |
| Worker journal | Unit active/current result success с 10.07.2026; `NRestarts=132`; за доступные 30 дней 529 lines, 132 warning lines и 264 failure markers | Counter подтверждает restart/failure storm в доступном окне, но timestamps/categories без raw job content ещё нужны. R-152 остаётся open. |
| Monitoring | Из common monitoring/telemetry agents найдены только generic LVM/MD services; app/HTTP/DB/queue/scheduler/disk alert owner не обнаружен. Journald использует 2.4 GiB | Отсутствие common agent не исключает external provider monitoring, но alert evidence нет. L3-E13 остаётся open; добавлен R-153. |

L3-E12 остаётся `partial`, L3-E13 — `open`, L3-E14 переведён `open -> partial` по фактической release topology. Production code/files/logs/services не изменялись; log contents и Git diff paths не выводились.

### 12.18 Production evidence — batch 8B: exact release delta и log capacity

| Контур | Подтверждённый production-факт | Вывод / gate |
|---|---|---|
| Git tracked delta | 18 modified: generated `bootstrap/cache/packages.php|services.php` и 16 PHP language files `header_footer_new.php|mail.php` для `de|ee|en|et|lt|lv|pl|ru` | Production HEAD не содержит актуальный translation/runtime state. Generated manifests должны воспроизводиться build step, а language files — сниматься, diff-review и переноситься как source data. Live `git reset/checkout` потеряет изменения. |
| Git untracked delta | Три contact images `public/theme/viar/images/contacts/d1.webp|d2.webp|d3.webp` | Media assets не входят в commit/artifact; до rollback/migration нужны hash manifest, owner/source decision и byte-identical copy. |
| Locale files | Одновременно существуют production file dictionaries `ee` и `et`, тогда как ALT production corpus использует public code `ee` | `ee` и `et` нельзя автоматически объединять. Нужно сохранить оба storage identifiers и отдельно утвердить public routing/source mapping. R-107 усилен. |
| MariaDB slow log | `/var/lib/mysql/viarcanvas-slow.log` = 357 017 545 bytes (~340.5 MiB), mode 660 mysql:mysql; last write 09.07.2026 | Active MariaDB logrotate paths, полученные ранее, не покрывают этот filename в `/var/lib/mysql`. SQL content не читался. Нужен explicit rotate/retention owner; добавлен R-155. |
| Worker restart window | Все 132 warning events пришлись на 10.07.2026 13:34:02…13:45:30 UTC; после 13:45 unit active/current result success | Это локализованный startup/restart storm, а не доказанный ongoing churn. Root cause и protection от повторения не доказаны; stop-grace mismatch остаётся. R-152 уточнён. |
| Journald | Использует default/unset limits и занимает 2.4 GiB | На общем 75 GiB volume требуется explicit SystemMaxUse/SystemKeepFree/retention decision и disk alert; R-153 уточнён. |

L3-E12/L3-E14 остаются `partial`, L3-E13 `open`. Production Git/logs/journald не изменялись; Git diff и log/SQL contents не читались.

### 12.19 Production evidence — batch 8C: effective logrotate policies

| Канал | Подтверждённая политика | Вывод |
|---|---|---|
| Laravel | Exact `laravel.log`, monthly, 12 rotations, compress, `create 755 admin admin`; state last rotation 01.07.2026 | Rotation существует, но monthly volume уже ~125 MiB и mode 755 делает log world-readable/executable. Нужны least-readable 640/600, size threshold и redaction/retention approval. |
| Nginx production domain | `/var/log/nginx/*log` и `domains/*log`: weekly, rotate 4, compress/delaycompress, USR1 reopen; production state last rotation 12.07.2026 | Базовая ротация подтверждена. Access retention/anonymization/central metrics owner остаются открыты. Другие domains исключены из выводов. |
| MariaDB configured paths | Monthly/rotate 6, max 500 MiB/min 50 MiB, compress/delaycompress и safe flush для `/var/lib/mysql/mysqld.log|mariadb.log` и `/var/log/mysql/*.log` | Активный блок технически корректен для перечисленных paths. |
| Actual production slow log | `/var/lib/mysql/viarcanvas-slow.log` отсутствует в rule и logrotate state | R-155 окончательно подтверждён: 357 MiB slow log не покрыт effective rotation. Изменение policy должно быть отдельным согласованным operational change; manual truncate запрещён. |

L3-E12 остаётся `partial`: custom app/worker/mail log coverage, redaction/access и central retention ещё не закрыты. Конфигурации и логи не изменялись.

### 12.20 Production evidence — batch 8D: custom log coverage и worker restart classification

| Контур | Подтверждённый production-факт | Вывод |
|---|---|---|
| Custom rotation | `alt-gen-skipped.log`, `alt-gen.log`, `queue-alt-worker.log`, `queue-alt-worker-error.log` и `viarcanvas-slow.log` не найдены ни в одном logrotate rule | Custom ALT/worker и DB slow channels не имеют автоматической rotation policy. Existing files нельзя вручную truncate/delete; нужен отдельный tested rule/change. |
| Application mail log | По basename `mail.log` найден `/etc/logrotate.d/rsyslog` | Это ещё не доказывает coverage `storage/logs/mail.log`: rsyslog обычно управляет `/var/log/mail.log`. До exact full-path match application mail channel считается unresolved/not proven. |
| Worker restart classification | В окне 10.07 13:30–13:50: `Main process exited=132`, `Failed with result=132`, `Scheduled restart job=132`; start-limit, permission, missing-file, 203/EXEC и OOM markers = 0 | Worker process завершался ошибкой, systemd автоматически перезапускал его 132 раза. Common executable/permission/OOM causes исключены агрегатами; primary PHP/Redis/provider error без чтения raw error log не установлен. |

Release/logging production evidence pass достаточен для планирования: L3-E12 и L3-E14 остаются `partial`, L3-E13 `open`. Для remediation нужны versioned clean artifact/runbook, custom rotation/permissions и metrics/alerts; это отдельные changes, не действия read-only аудита.

Production state не изменялся. Случайный ввод строки `alt-gen-skipped.log=NOT_COVERED` вернул shell `command not found` и не имел side effects.

### 12.21 L3-G06 design evidence — orders, payments и chats

Read-only code/schema pass оформлен в [34-l3g06-write-owner-delta-cutover.md](34-l3g06-write-owner-delta-cutover.md) и [appendix-l3g06-write-owner-matrix.csv](appendix-l3g06-write-owner-matrix.csv).

| Контур | Подтверждённый факт | Решение / gate |
|---|---|---|
| Delta | Raw orders/chat updates не всегда меняют `updated_at`; `Orders::deleteOrder()` делает physical delete без tombstone | Timestamp watermark недостаточен. Shared production DB — первый выбор; final PK/canonical-hash/delete reconciliation обязателен. |
| Ownership | Public, admin, account, payment и SA меняют одну `orders` разными paths | Owner назначается по capability+fields/actions, а не только по таблице. Один capability не имеет двух writers. |
| Payments | Mutable `orders.payment_status`, payment requests и side effects не образуют полного provider receipt ledger | Financial callbacks переключаются последними; нужны immutable receipts, amount/currency/reference checks и reconciliation. |
| Chats | Четыре legacy streams и SA ledger имеют разные keys/actor semantics; основной `order_user_comments` без unique external message ID | Streams сохраняются раздельно; target добавляет adapter/message-id/outbox, destructive merge запрещён. |
| Rollback | Production продолжает принимать live writes | Только forward rollback: target writer off, receipts/delta reconcile, exact legacy capability on; старый общий dump не восстанавливается поверх live DB. |

L3-E15 и L3-E16 переведены `open -> partial`: draft capability matrix и delta/reconciliation contract созданы, но ещё не подписаны и не прошли rehearsal. L3-E17/L3-E22 остаются `open`; L3-G06 остаётся `partial / no-go for target writes`.

### 12.22 L3-G06 design evidence — catalogue, content, translations, SEO и media

Read-only code/schema pass оформлен в [35-l3g06-catalog-content-translations-cutover.md](35-l3g06-catalog-content-translations-cutover.md) и [appendix-l3g06-content-write-owner-matrix.csv](appendix-l3g06-content-write-owner-matrix.csv).

| Контур | Подтверждённый факт | Решение / gate |
|---|---|---|
| Translation stores | Base fields, `translations=64779`, `ltm_translations=21450` и 334 PHP-файла являются разными связанными слоями | Переключать parent+DB translations вместе; PHP dictionaries — отдельным versioned artifact; одного table copy недостаточно. |
| Writers | Translation Manager и `AdminLocaleController` способны перезаписывать одни PHP-файлы | До cutover оставить один writer, deny-by-default ACL, publish receipt, PHP lint и SHA-256 manifest. |
| Locales | Public `lv|lt|pl|ru|de|en|ee`; отдельно существуют `et`, `cn`, `jp`; API fallback `uk->ru` | `ee/et` не объединять, `cn/jp` не удалять, `uk` не включать публично без signed decision. |
| Delta | `settings`, часть metadata/pivots не имеют timestamps; LTM schema не гарантирует unique `(locale,group,key)` | Full key/hash/anti-join, production duplicate audit и tombstone/freeze; timestamp только hint. |
| Catalogue | Цена, relations, media, translations, slug/SEO и SA projection связаны; локально есть orphan pivots | Один owner на capability, signed orphan disposition, price corpus и dependency reconciliation. |
| URL/SEO | Redirect dataset находится в routes/code, sitemap/feed зависят от live content/translations | URL/redirect/canonical/hreflang manifests переключаются atomically как versioned release artifacts. |
| Frontend | Текущий public Mix/handwritten assets заморожены | No Vite/no rebuild; byte-identical artifact до отдельного проекта. |

L3-E15/L3-E16 остаются `partial`: матрица и contract расширены на content/translations, но owner sign-off, production exact duplicate/orphan/file manifest и rehearsal отсутствуют. Target content/translation writes остаются `no-go`.

### 12.23 L3-G06 reconciliation design и production baseline commands

Подготовлены [36-l3g06-reconciliation-specification.md](36-l3g06-reconciliation-specification.md), [appendix-l3g06-reconciliation-datasets.csv](appendix-l3g06-reconciliation-datasets.csv) и [37-l3g06-production-baseline-command-pack.md](37-l3g06-production-baseline-command-pack.md).

Спецификация охватывает 22 datasets: schema, orders/actions/payments/chats/SA, оба DB translation stores, PHP dictionaries/locales, catalogue parents/lookups/pivots, content/Voyager metadata, media DB/files, URL/redirect/sitemap/feed, suggestions и frozen frontend.

Ключевые правила:

- shareable evidence содержит только counts/diff counts/aggregate roots/reason codes; raw PII, business values, private paths и secrets запрещены;
- restricted artifact использует per-row HMAC-SHA-256 и хранится отдельно с access/retention owner;
- `updated_at` — только hint; checkpoints `T1/T2/O1` требуют полного key/hash/anti-join;
- canonicalizer не исправляет legacy values: invalid JSON/serialized data остаются tagged opaque bytes;
- production Batch R1/R2 ограничен schema/count/duplicate/orphan/language aggregate checks; per-row/file-media scan сначала измеряется на restored staging;
- локальный dry-run PHP-блока Batch R1 успешно завершён на PHP 7.4/Laravel 6 без DML и подтвердил ожидаемую структуру вывода.

На момент подготовки этого раздела commands ещё не были выполнены; фактический результат зафиксирован ниже в 12.24. L3-E15/L3-E16 и CUT-002/CUT-009 остаются `partial/not implemented`: canonicalizer/HMAC runner не реализован, restored staging rehearsal отсутствует.

### 12.24 Production evidence — reconciliation Batch R1/R2

16.07.2026 пользователь выполнил подготовленные read-only batches на production PHP 7.4.33 / Laravel 6.20.40 / MariaDB 10.11.16.

| Контур | Exact production evidence | Вывод / gate |
|---|---|---|
| Catalogue/content | Counts и ID boundaries основной выборки совпали с локальным snapshot; media=1120 | Baseline пригоден для design, но не заменяет final key/hash delta. |
| `translations` | 64 779 rows; locale cohorts совпали; duplicates=0; NULL value/updated_at=0; composite unique index подтверждён | Aggregate gate пройден; value-HMAC и parent anti-join остаются. |
| `ltm_translations` | 21 450 rows, 46 groups, 211 NULL, duplicate logical keys=0; только primary index | Counts/status совпали, но newest production update 27.06 позже local 03.06: full value diff обязателен. |
| Catalogue orphans | category=28, size=37, color=31, room=66 | Стабильные baseline anomalies; не удалять, нужен signed disposition. |
| Suggestions | ALT=13 131: failed 11 297/pending 1 834; SEO meta=8 456 new | Live operational data; no blind retry/apply; отдельный owner/receipt gate. |
| Language files | 10 dirs/332 PHP files, lint PASS, raw tree hash зафиксирован; local=334 | Main file counts совпадают; по одному local-only path в `cn/jp`; normalized R3 до любых copy/delete conclusions. |

R1/R2 не изменяли production. L3-E15/L3-E16 остаются `partial`: aggregate baseline собран, но normalized file/path diff, value-HMAC, restored staging full anti-join и rehearsal не выполнены. Следующий безопасный batch: [38-l3g06-production-r1-r2-evidence-and-r3.md](38-l3g06-production-r1-r2-evidence-and-r3.md).

### 12.25 Production evidence — language Batch R3

R3 выполнен тем же PHP 7.4 bootstrap без записи и без вывода содержимого переводов.

| Проверка | Результат | Вывод |
|---|---|---|
| Line endings | raw=normalized hashes для всех 332 production files | Production language corpus LF-only; byte delta с local не объясняется только CRLF. |
| Normalized roots | Ни один locale root не совпал с local | Есть реальный content/path drift; repository overwrite запрещён. |
| `cn` | Production только `new_index.php`; local также `google_reviews.php` | Local-only file сохраняется до runtime/owner decision. |
| `jp` | Production `account.php|new_index.php`; local также `google_reviews.php` | Local-only file сохраняется до runtime/owner decision. |
| Hash algorithm | R2 shell root ≠ R3 PHP root при тех же counts/bytes | Roots разных manifest algorithms не сравниваются; canonical series=`LANG-MANIFEST-v1`. |
| Main locales | Ранее production Git показал 16 modified `header_footer_new.php|mail.php` | Targeted R4 проверяет, совпадает ли весь остальной corpus после LF normalization. |

REC-009 остаётся `partial`, R-154/R-161/R-164 открыты. Следующий read-only batch и local expected roots: [39-l3g06-r3-analysis-and-r4.md](39-l3g06-r3-analysis-and-r4.md).

### 12.26 Production evidence — targeted language Batch R4

| Проверка | Результат | Вывод |
|---|---|---|
| Common `cn/jp` | `cn/new_index.php`, `jp/account.php`, `jp/new_index.php` совпали normalized SHA | Local-only подтверждены ровно два известных path: `cn/jp google_reviews.php`. |
| Known 16 hot edits | 15 content hashes отличаются; только `ru/mail.php` совпадает | Production 15 values сохраняются как source-of-truth candidate; Git modified не всегда означает semantic difference. |
| Corpus без known files | Excluded roots различаются для `de|ee|en|et|lt|lv|pl|ru` | Есть дополнительные changed/missing/extra files в каждом locale. |
| Минимальная область | 15 known + 2 local-only + минимум 8 дополнительных = ≥25 discrepancies | Это lower bound; нельзя строить merge по Git status или списку из 16 paths. |

REC-009/R-154/R-161/R-164 остаются open/partial. Exact path classification требует полного безопасного manifest [40-l3g06-r4-analysis-and-r5-manifest.md](40-l3g06-r4-analysis-and-r5-manifest.md). До него publish/copy/delete/overwrite запрещены.

### 12.27 Production evidence — full language manifest R5

`LANG-FILE-MANIFEST-v1` содержит 332 production path/hash entries и логически сравнен с 334 local entries.

| Класс | Exact | Решение |
|---|---:|---|
| same | 309 | подтверждённо одинаковый normalized corpus |
| changed | 23 | production/local сохраняются; per-key merge и owner UAT |
| production-only | 0 | неизвестных local source paths на production нет |
| local-only | 2 | `cn/jp google_reviews.php`; preserve до owner/runtime decision |

Изменены `account_new|header_footer_new|mail` для семи locale и `account_new|header_footer_new` для `ru`; `ru/mail.php` совпадает. Exact hash/disposition matrix: [appendix-language-file-delta-r5.csv](appendix-language-file-delta-r5.csv).

Transport qualifier: server mode-600 JSON checksum указан, но pasted-text wrapper изменил byte representation, поэтому checksum исходного `/tmp` artifact независимо не воспроизведён. Logical JSON/algorithm/count/entries валидны; formal evidence closure требует raw JSON attachment или сохранённого server artifact.

REC-009 остаётся `partial`: exact scope закрыт, но merge/disposition, PHP-array key diff, Content/Mail UAT, versioned publish и rollback rehearsal отсутствуют. Следующий read-only check: LTM aggregate R6 в [41-l3g06-r5-exact-language-delta-and-r6-ltm.md](41-l3g06-r5-exact-language-delta-and-r6-ltm.md).

### 12.28 Production evidence — LTM canonical R6

Production R6 выполнен за 225 ms с peak memory 40 MiB и без writes:

- rows=21 450 и NULL values=211 совпали с local;
- canonical logical key root совпал полностью;
- key+value root и key+value+status root отличаются.

Следовательно, набор LTM logical keys одинаков, но DB-переводы/статусы semantic-identical не являются. Timestamp-only гипотеза отклонена. Запрещены local dump overwrite, Translation Manager publish и выбор одной среды как автоматического winner. Следующий read-only этап — 8 locale roots R7: [42-l3g06-r6-analysis-and-r7-ltm-locale.md](42-l3g06-r6-analysis-and-r7-ltm-locale.md).

### 12.29 Production evidence — corrected locale R7

Production R7 выполнен за 243 ms, peak memory 40 MiB, без writes. После corrected local recalculation exact результат:

- 8/8 locale совпали по rows, NULL и logical key roots;
- 0/8 locale совпали по key+value roots;
- 0/8 locale совпали по full roots; отдельный status delta пока не отделён от value delta.

Первоначальный local baseline ошибочно omitted `group` в identity; production command/output были корректны. Baseline заменён, correction документирован, повтор production R7 не требуется. Следующий этап — 46 group roots R8: [43-l3g06-r7-analysis-and-r8-ltm-group.md](43-l3g06-r7-analysis-and-r8-ltm-group.md).

### 12.30 Production evidence — LTM group R8

Production R8 выполнен за 235 ms, peak memory 40 MiB, no writes:

- 46/46 groups совпали по counts, NULL и logical key roots;
- 45/46 groups совпали по values и full records;
- единственная divergent group — `header_footer_new`: 240 rows, 0 NULL, keys match, values differ;
- остальные 21 210 LTM rows semantic-equal.

Отдельно важно: DB groups `account_new` и `mail` совпали, но их PHP files отличаются по R5. Translation Manager DB не описывает автоматически фактический runtime tree. Следующий read-only шаг — targeted 8-locale R9 только для `header_footer_new`: [44-l3g06-r8-analysis-and-r9-header-footer-locale.md](44-l3g06-r8-analysis-and-r9-header-footer-locale.md).

### 12.31 Production evidence — `header_footer_new` locale R9

Production R9 выполнен за 70 ms, peak memory 32 MiB, no writes:

- 8 locale, по 30 rows, всего 240, NULL=0;
- 8/8 locale совпали по key roots;
- 0/8 совпали по value/full roots.

Следовательно, каждый locale содержит тот же набор 30 keys, но хотя бы одно value в каждом locale отличается. Для определения точного key scope без раскрытия key/value подготовлен 30-cohort opaque-key R10: [45-l3g06-r9-analysis-and-r10-opaque-key.md](45-l3g06-r9-analysis-and-r10-opaque-key.md).

### 12.32 Production evidence — opaque key R10

Production R10 выполнен за 86 ms, peak memory 32 MiB, no writes:

- 30/30 opaque keys совпали по identity roots;
- 29/30 совпали по values/full records;
- единственный divergent opaque ID локально однозначно отображён в `header.top_sale`;
- совместно с R9 доказаны ровно 8 divergent DB values, по одному на `de|ee|en|et|lt|lv|pl|ru`;
- 21 442/21 450 LTM rows semantic-equal.

### 12.33 Production evidence — final row proof R11

Production R11 выполнен за 77 ms, peak memory 32 MiB, no writes:

- row_count=8, locale=`de|ee|en|et|lt|lv|pl|ru`;
- identity roots совпали 8/8;
- status roots и NULL-state совпали 8/8;
- key+value/full roots отличаются 8/8.

Следовательно, DB Translation Manager не имеет key/status/NULL-state drift: различаются только восемь values `header.top_sale`. DB technical reconciliation закрыт; content owner disposition и файловый PHP-array R12 остаются обязательными: [47-l3g06-r11-final-proof-and-r12-php-array.md](47-l3g06-r11-final-proof-and-r12-php-array.md).

### 12.34 Production evidence — PHP-array R12

Production R12 выполнен за 14 ms, peak memory 2 MiB, no writes:

- 7/23 `mail.php` полностью semantic-equal;
- 8/23 `header_footer_new.php`: counts/keys/types equal, values differ;
- 8/23 `account_new.php`: production=129 leaves, local=130, одинаковые top-level/array depth;
- deterministic local exclusion однозначно определил local-only `orders.not_specified` во всех 8 locale;
- после исключения этого leaf common value corpus совпадает в семи locale, дополнительный mismatch остаётся только в `ru`.

Файловый reconciliation scope теперь ограничен header leaf keys, local-only account key, RU account value group и двумя local-only `cn/jp google_reviews.php`. Следующий read-only Batch R13: [48-l3g06-r12-analysis-and-r13-targeted-files.md](48-l3g06-r12-analysis-and-r13-targeted-files.md).

### 12.35 Production evidence — targeted PHP-array R13

Production R13 выполнен за 3 ms, peak memory 2 MiB, no writes:

- header/footer: 30 cohorts/240 rows, count/key/type match=30/30, value match=29/30;
- единственный header mismatch локально отображён в `header.top_sale`;
- с учётом R12 exact file header delta=8 values, остальные 232 values equal;
- RU account: 36 top-level groups, 35 fully equal;
- единственная divergent group=`orders`, production=44 leaves, local=45.

Final production hash gate ограничен 44 RU account/orders leaves: [49-l3g06-r13-analysis-and-final-r14.md](49-l3g06-r13-analysis-and-final-r14.md).

### 12.36 Production evidence — final RU account/orders R14

Production R14 выполнен за 1 ms, peak memory 2 MiB, no writes:

- production=44 leaves, local=45, common=44;
- production-only=0, local-only=1;
- 44/44 common rows совпали по count/key/type roots;
- 43/44 common rows совпали по value root;
- local-only leaf локально отображён в `orders.not_specified`;
- единственный common value mismatch отображён в `orders.user_status.print_text`.

`orders.user_status.print_text` прямо используется в `resources/views/theme/viar/account/order.blade.php:508`, поэтому требует RU account UAT. Для `orders.not_specified` прямой static consumer не найден, но dynamic lookup возможен; ключ сохраняется до owner/usage decision.

Technical translation reconciliation R5–R14 завершён. Дополнительные production discovery hash batches по текущему scope не нужны. Owner/UAT/cutover остаются открытыми: [50-l3g06-r14-final-translation-reconciliation.md](50-l3g06-r14-final-translation-reconciliation.md).

### 12.37 Translation owner/UAT execution pack

Статический consumer audit после R14 установил точные staging preconditions:

- `header.top_sale` зависит от `site.top_sale` и используется в общем и delivery layout;
- RU `orders.user_status.print_text` виден только для non-painter customer, `in_production` order и назначенного painter;
- `orders.not_specified` не имеет прямого static consumer, поэтому сохраняется как dormant/fallback до usage proof;
- `google_reviews` используется в section partial и HTML sitemap, а `cn|jp` routing остаётся выключенным до отдельного решения.

Подготовлены 27-row owner matrix и 14 UAT cases с positive/negative fixtures, publisher containment, lint/manifest и atomic rollback: [51-l3g06-translation-owner-decision-and-staging-uat.md](51-l3g06-translation-owner-decision-and-staging-uat.md).
