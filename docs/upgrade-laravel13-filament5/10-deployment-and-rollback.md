# 10. Deployment, cutover и rollback

## Production discovery — до оценки даты

Подтвердить командами/панелью сервера без вывода секретов: OS/disk/RAM/CPU; web server; PHP CLI/FPM versions и modules; MariaDB version/size/replication/binlog; Redis; cron; workers/Supervisor/systemd; current release path/symlinks; shared storage; TLS/CDN/WAF; log rotation/APM; backup schedule и restore history. Репозиторий не содержит CI/Docker/Supervisor manifests.

## Целевая staging

- отдельные PHP 8.3 и 8.4 jobs; production выбирает подтверждённую версию;
- anonymized DB/files copy с такими же volumes/SQL mode/collation;
- sandbox endpoints и безопасный mail sink;
- запрет outbound production webhook/email/payment по network allowlist;
- worker, cron, filesystem permissions, symlink и opcache как production;
- Vite artifacts собираются один раз в CI только для Filament; проверенные public CSS/JS копируются без пересборки и сверяются по manifest/hash.

## Release pipeline

1. immutable artifact из reviewed commit и lock files;
2. Composer production install и platform check;
3. npm clean install/build, manifest verification;
4. static/security/tests и artifact checksum;
5. deploy new release directory;
6. config/route/view/event cache в новой release;
7. additive DB migrations с preflight backup и lock/duration plan;
8. атомарное переключение symlink/traffic;
9. graceful worker restart; verify scheduler single execution;
10. smoke + metrics/logs/business counters.

## ENV checklist

Сверять имена, не значения: APP/DB/LOG, CACHE/SESSION/QUEUE/REDIS, MAIL, filesystem/AWS, Facebook/Google, PayPal, SA attachment/API, Synvolve URLs/timeouts/SSL, Google Places/reviews, Pusher/recaptcha. Удалить production fallback `test-key`; secrets поступают из secret manager/deploy environment. Rotation plan нужен для API/OAuth/payment/SMTP/Venipak credentials.

## Health и smoke

- health endpoint проверяет app boot и безопасную DB/cache connectivity, но не вызывает оплату/почту;
- homepage и по одному URL каждой locale;
- login/account/order detail/chat read;
- admin/Filament login и role-limited page;
- queue test job, failed job counter, scheduler heartbeat;
- storage read/write/delete test в выделенном health prefix;
- private artifact smoke: authorized invoice/attachment доступен, anonymous/foreign owner получает 403/404; canonical traversal отклоняется;
- integration synthetic tests только sandbox.

## Наблюдаемость

Метрики: HTTP 5xx/4xx/p95, PHP errors, DB connections/slow queries/locks, queue depth/age/fails/retries, scheduler heartbeat, mail failures, payment callback count/success/duplicate, SA inbound/outbound success/age, Venipak errors, login/OAuth errors, order creation/payment/status/chat counts. Логи structured с `order_id`, `event_id`, provider reference; без keys/tokens/passwords/payment payload/PII.

## Стратегия rollout

1. dark deployment + read-only checks;
2. internal/admin allowlist;
3. 1–5% public traffic при совместимой session/cache;
4. постепенное увеличение;
5. отдельное включение каждой интеграции;
6. Filament modules переключаются feature flags по волнам, Voyager остаётся rollback UI.

## Переключение работающего магазина

Production продолжает принимать заказы до, во время и после deploy. Поэтому:

- не использовать многочасовой maintenance mode и не планировать «финальный dump» как основной способ переноса;
- применить expand/contract schema: сначала совместимые additions, затем оба релиза могут работать с одной схемой, удаление legacy — значительно позже;
- перед переключением остановить только несовместимые workers на короткое контролируемое окно, дождаться/зафиксировать in-flight jobs и затем запустить workers новой версии;
- payment callback и CRM webhook endpoints должны всё время иметь активного владельца; переключать routing атомарно, не полагаться только на медленное DNS propagation;
- сохранить idempotency storage и provider references между релизами, чтобы retry попал в тот же dedupe domain;
- зафиксировать cutover watermark: последний order ID, временную границу, queue depth, последний обработанный payment/SA event, manifest переводов/content updates и file artifact manifest/hash;
- сразу после переключения выполнить delta-reconciliation и наблюдать новые orders/payment/chat counters, а также изменения переводов/контента в реальном времени;
- при rollback вернуть старый application release к актуальной БД, не восстанавливать старый dump поверх новых заказов.

Если backward-compatible схема невозможна, требуется краткое согласованное write-pause только для конкретного контура, буферизация webhook/callback и последующий replay. Общая остановка магазина является крайним вариантом и должна иметь отдельное бизнес-согласование.

Для контентных модулей допускается короткая точечная пауза редактирования конкретного Resource на время смены write-owner, но публичный сайт продолжает читать актуальные данные. Нельзя вводить общую многодневную «редакционную заморозку» как замену синхронизации переводов.

## Rollback

Application rollback — атомарно вернуть предыдущий release и перезапустить workers. Assets — manifest/versioned files предыдущего release. DB — старое приложение должно понимать additive schema; destructive migration запрещена. Integrations — feature flag/endpoint adapter на legacy, но уже принятые provider события не отправлять повторно. Files/PDF — не восстанавливать старый filesystem snapshot отдельно от актуальной DB: использовать artifact journal/hash, сохранить source до after-commit cleanup и forward-fix references. При corruption — maintenance/read-only, остановить workers/webhooks, восстановление DB+files по одному watermark и reconciliation.

## Stop criteria

Немедленный stop/rollback при: изменении IDs/status strings; duplicate charge/payment transition; росте 5xx или queue age выше согласованного порога; SA duplicate/lost events; missing translations/locale redirects; permission escalation; private file/PDF доступен anonymous/foreign owner; ссылка указывает на отсутствующий artifact; duplicate/partial rename; Venipak label/courier outcome неизвестен либо повтор создаёт второй attempt; pickup directory stale сверх SLA; label PDF не проходит ownership/signature; DB migration lock/duration сверх окна; невозможности восстановить предыдущую release.

## Auth/chat cutover gate

Legacy остаётся единственным write-owner для users/sessions и каждого chat stream до завершения role×ownership и replay tests. OAuth provider, session store, login contract и chat writer переключаются отдельными волнами. Новая версия сначала работает в shadow-read/read-only режиме; mail/Synvolve/SA outbound остаются выключенными до сверки message IDs/outbox.

Дополнительные stop criteria: доступ к raw user rows; чужой order/chat/image mutation; admin impersonation; OAuth state/link collision; session fixation; duplicate/lost chat message; несанкционированный mail/webhook; расхождение actor/read flags. Rollback возвращает route/module owner на legacy без восстановления старого DB dump и без удаления новых production rows; неизвестные provider/message outcomes сначала reconciled, а не повторяются вслепую.

## Mail/queue/scheduler cutover gate

Transactional mail, marketing, scheduler и queue driver переключаются отдельными волнами. Target сначала создаёт shadow intents без outbound. Old/new scheduler и workers не принимают один business event одновременно; перед deploy фиксируются queue depth, reserved/failed, outbox watermark и last-success каждого command.

Stop: TLS verification off; public preview mutation/data exposure; duplicate order/payment/chat mail; consent violation; recipient/locale mismatch; unknown provider outcome replay; queue age/failed rate или scheduler heartbeat вне SLA. Rollback возвращает dispatcher owner, но сохраняет outbox/receipts и reconciles provider state до retry.

## Public catalogue/translation/assets cutover gate

Legacy остаётся единственным writer каталога и переводов после T0 snapshot. Target получает initial copy и repeatable delta/CDC; перед переключением конкретного component выполняются final delta, counts/hashes по table+locale, orphan cohort, URL/SEO и cross-channel price reconciliation. Заказы не останавливаются глобально.

Public CSS/JS переносятся как byte-identical artifacts вместе с текущим `mix-manifest.json`; build/Vite/re-minify запрещены. Stop: потеря locale/translation/slug, новая 404/500, display/cart/order/SA price drift, asset hash/load-order mismatch, broken generator state или SEO redirect/canonical diff. Rollback возвращает route/read owner на legacy и не откатывает DB backup поверх новых production writes.

### Решения владельца от 17.07.2026

- public frontend не переводится на Vite, не пересобирается и не получает redesign;
- URL/redirect/canonical/sitemap/feed contracts сохраняются и принимаются программистом с Google Search Console/merchant evidence;
- каждый этап имеет письменный seven-locale PASS/FAIL protocol;
- минимальное observation window — 24 часа, усиленный мониторинг — не менее 48 часов;
- Voyager write конкретного модуля отключается в момент Filament write cutover, а не через сутки; в observation он остаётся read-only и rollback-capable.

Для редких событий 24 часа не являются достаточным sample сами по себе: provider/payment/delivery/scheduler gates закрываются только после required scenario counts и reconciliation.

## FN-12 admin module cutover

Переключение идёт по одному Resource/Cluster: Voyager write gate → final row/translation/media/permission delta → Filament write owner → observation. Остальные 132 модуля и production orders продолжают работать. Rollback отключает target writes и возвращает legacy UI на актуальную БД; полный restore старого dump поверх новых заказов/переводов запрещён. Stop при permission escalation, lost update, missing locale/media, duplicate action или route/cache mismatch.

## FN-13 SEO/feed route cutover

Redirect dataset, sitemap/feeds и auxiliary security меняются отдельной release wave после anonymized access/Search Console/merchant evidence. Перед wave: final route/log/catalogue/translation watermarks, cached golden XML, PL mapping, unique route registry и rollback artifact. Legacy orders/admin/catalogue writers не переключаются вместе с SEO.

Stop: 404/5xx/redirect-hop spike, robots self-loop, locale/domain/canonical loss, feed reject/price drift, route-cache mismatch, mutating/debug endpoint exposure. Rollback возвращает legacy route/read owner и предыдущую redirect/XML cache version без DB restore; новые production orders/translations сохраняются.

### Auxiliary P0 release gate

Это рекомендуемый будущий gate, а не описание текущей реализации: перечисленных `LEGACY_*` flags в текущем codebase нет. После отдельной реализации containment перед deployment сохранить ENV/config-cache fingerprint и убедиться, что production opt-in выключен. После controlled config-cache refresh проверить blank 404 для translation/meta, mail preview, image debug и gift-card HTTP routes, а также отсутствие DB/file/provider diff. Synvolve production test receiver должен быть выключен. Break-glass требует owner, ограниченного окна и последующего возврата flags в `false`. Rollback — предыдущий code artifact + прежний ENV snapshot; DB dump поверх новых orders/translations не восстанавливать.

## L3 production readiness gate

Локальный L3-01 baseline не заменяет production evidence. До первого target staging и тем более cutover должны быть закрыты L3-G01…L3-G06 из документа 33:

1. подписанная production topology и effective runtime/config matrix;
2. exactly-one scheduler owner и проверенные purpose queues/workers/locks/retry/drain/restart;
3. encrypted backup/PITR facts и успешный изолированный restore drill с измеренными RPO/RTO;
4. storage public/private/capacity/permissions/link-mount и file/PDF smoke;
5. central logs/metrics/alerts/redaction/correlation и alert test;
6. one-writer/final-delta/reconciliation/cutover/rollback game day без потери новых orders, payments, chats, content и translations.

Evidence ведётся по `appendix-production-evidence-request.csv`. До закрытия L3-G03 любые DB migration/cutover работы имеют статус no-go; наличие local dump или успешного локального запуска не считается restore evidence.
