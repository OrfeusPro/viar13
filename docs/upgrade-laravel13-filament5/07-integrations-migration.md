# 07. Интеграции: карта и план

## Сводка

| Интеграция | Входные точки / классы | ENV names / данные | Главный риск |
|---|---|---|---|
| PayPal | `routes/web.php` paypal и payment-request; `OneTimePayPalController`, `OneTimePayPalService`, `OrderPaymentRequestController` | `PAYPAL_SANDBOX`, `PAYPAL_SANDBOX_ID`, `PAYPAL_SANDBOX_SECRET`, `PAYPAL_ID`, `PAYPAL_SECRET` | устаревший SDK, capture replay, прямая связь token→order |
| Paysera/WebToPay | `paysera`, accept/cancel/callback и payment-request; `Libwebtopay/PayseraController`, vendored `WebToPay.php` | project/sign settings находятся в кодовом контуре — провести secret audit без вывода значений | подпись, повтор callback, GET/ANY endpoints, две payment flows |
| Synvolve outbound | `SynvolveWebhookService` из order/payment/chat/admin | `SYNVOLVE_*_WEBHOOK_URL`, timeout/connect timeout/verify SSL | нет подтверждённого outbox/retry; потеря события после локального commit |
| CRM/SA inbound | `routes/api.php`, `SaIntegrationController`, `VerifyIntegrationApiKey` | `SA_API_KEY`, `SA_ATTACHMENT_MAX_SIZE`, `SA_ATTACHMENT_ALLOWED_MIME` | огромный controller, duplicate/concurrency, временный lead |
| SMTP/marketing | 33 Mailables, 3 Notifications, 2 hourly cart commands, user notify | `MAIL_*`, provider vars | SwiftMailer removal, queue=sync, consent/retry/bounce |
| Facebook | `/auth/facebook/redirect|callback`, `SocialController`, Socialite provider | `FACEBOOK_ENABLE`, `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `FACEBOOK_REDIRECT_URI` | email отсутствует, account linking, callback URI/scopes |
| Google OAuth | тот же `SocialController` | `GOOGLE_CLIENT_ID/SECRET/REDIRECT_URI` | исходное ТЗ не выделяло, но код использует; сохранить |
| Venipak | checkout pickup endpoints, `VinepakApiController`, `venipak_data` | credentials/URLs в БД; commented `VENIPAK_API_KEY` не является runtime | credential storage, cURL errors, duplicate labels, XML |

Секреты и значения в аудит не читались и не выводились.

## PayPal

Текущий lock: `paypal/paypal-checkout-sdk 1.0.2`. Сервис создаёт order/capture и пишет `paypal` channel. План: зафиксировать create/capture request/response fixtures; проверить официальный статус SDK; обернуть provider interface; хранить provider order/capture ID и unique transition; проверять amount/currency/order ownership; повтор capture/callback должен возвращать уже достигнутый результат, не менять заказ повторно. Тесты: success, cancel, provider decline, timeout, duplicate token, wrong amount/currency, unknown order, concurrent requests. Rollback: переключение adapter на legacy endpoint при неизменной schema.

## Paysera/WebToPay

`PayseraController::pay_callback` и payment-request callback вызывают `WebToPay::validateAndParseData`, после чего обновляют payment и Synvolve. План: сохранить raw callback fixture без secrets; проверить подпись до любых writes; создать receipt/dedupe key по provider transaction/order; транзакционно защитить state transition; accept URL не считать доказательством оплаты; callback должен быть server-to-server authority. Тесты на replay, tampered signature, wrong project/amount/currency/status, out-of-order accept/callback. Rollback — legacy handler за feature flag с общей receipt table.

## CRM/SA и Synvolve

Подтверждённые правила: `lead_id=orders.id`; X-Api-Key; неизвестный lead создаёт временную сущность; attachment копируется локально; `event_id`/`idempotency_key` duplicate возвращает 200 status duplicate; статусы не переименовывать.

Таблицы `sa_events`, `sa_conversations`, `sa_messages`, `sa_escalations`, `sa_bot_controls` имеют unique/index поля, но локально по 0 строк. Перед переносом получить production cardinality/duplicates без payload/PII. В target: Request validation → authentication → dedupe claim в транзакции → handler → durable status → response. Для outbound использовать outbox с retry/backoff/dead-letter и correlation `event_id`; HTTP timeout/SSL остаются явными.

Вложения сохраняются физически локально, но текущий downloader небезопасен: arbitrary URL, redirects, отключённая TLS verification, body полностью читается до size check, результат public. Target: HTTPS/host allowlist или egress proxy, DNS/IP validation каждого redirect, streamed byte cap, real MIME/decode, hash/receipt/idempotency и private ownership download. Antivirus/quarantine и retention требуют отдельного решения владельца.

## Почта

`AppServiceProvider` зависит от SwiftMailer transport, что является подтверждённым blocker Laravel 9. До миграции описать назначение этого обращения. Разделить transactional и marketing queues, задать tries/backoff/timeout и rate limit. Проверить cron timezone, `withoutOverlapping` lock, failed jobs, worker restart, bounce/complaint handling и consent (`users.news` и связанные поля). Тестовый SMTP не должен отправлять production адресатам; использовать allowlist/sink.

## Socialite

Facebook callback использует `stateless()->user()`, логирует metadata, требует email и пишет `facebook_id`. Нужны тесты: существующий пользователь по email, существующий `facebook_id`, новый пользователь, provider без email, revoked/expired state, collision email/ID. Не хранить access token без необходимости; если хранится production — определить encryption/retention. Redirect URIs каждого locale/domain проверяются у provider. Google flow также входит в scope, хотя не был перечислен как критический.

## Venipak

Используются pickup directory, import/send и print-label endpoints. FN-08 L2 подробно разобран в [25-venipak-delivery-static-audit.md](25-venipak-delivery-static-audit.md): три admin operations имеют только `web`; lookup дублируется в трёх controllers без timeout/cache/error contract; browser задаёт delivery price/method/point; provider point ID не сохраняется; XML не экранируется; create/courier не имеют attempt receipt/idempotency/void; print не проверяет ownership и PDF signature. Tracked/default и plaintext DB credentials требуют ротации.

Перед переносом: typed `VenipakClient`, secret manager + live/sandbox/allowed-host gate, server-authoritative quote, cached point directory со stable ID/snapshot, validated DTO/XMLWriter, durable shipment/courier attempt ledger, reconciliation/void/reprint policy и PII-safe telemetry. Sandbox suite покрывает transport failures, XML special characters, double click/concurrency/crash-after-provider, stale point, foreign label и fake/oversize PDF.

## Общие release gates

Для каждой интеграции обязательны sandbox, contract fixtures, correlation ID, redaction test, timeout/retry/duplicate/concurrency test, dashboard/alert, runbook и переключаемый adapter. Cutover одной интеграции не совмещать с другой.

## FN-09: уточнённый Socialite/auth contract

Оба provider callback используют `stateless()`, account lookup выполняется по email, provider subject не закреплён unique constraint, а return URL проверяется строковым prefix. Target contract: state/PKCE где поддерживается, exact scheme/host/port allowlist, подтверждённый provider email, явное linking/re-auth для существующего local account, unique `(provider, provider_subject)`, session regeneration и security audit без PII/token payload. UI flag и route availability должны совпадать.

До cutover отдельно согласовать Facebook/Google console redirects для каждого production host/locale, правила account collision/unlink/recovery и поведение provider без email. OAuth нельзя переключать одновременно с session store и основным login contract.

## FN-10: SMTP, queues и scheduler

Local config подтверждает `queue=sync`, `cache=file` и SMTP без peer/name verification. Два `Mail::queue()` и queued Notifications не дают durability при таком driver. SwiftMailer boot customization заменяется только после provider/EHLO evidence; target — Symfony Mailer с TLS verification.

Transactional mail выводится из order/payment/chat request в after-commit outbox. Marketing получает отдельную queue/rate/suppression policy, signed unsubscribe и bounce/complaint ledger. Scheduler использует distributed one-server lock, explicit timezone/TTL и last-success heartbeat. Полный L2-разбор — `27-mail-queue-scheduler-static-audit.md`.
