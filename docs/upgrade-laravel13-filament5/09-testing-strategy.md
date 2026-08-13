# 09. Стратегия тестирования

## Текущий baseline

В репозитории 23 test-файла. Сильнее всего покрыты недавние CRM/SA и blog endpoints: leads create/update, orders lookup, service catalog/sizes, messages, pipeline, escalations, bot control, Synvolve receiver/service, payment request, Venipak label destination. Недостаточно покрыты основной checkout, legacy PayPal/Paysera callback, SMTP templates/marketing, Socialite, большинство 133 BREAD и public multilingual UI.

## Пирамида

1. **Characterization legacy** — фиксирует фактическое поведение Laravel 6, включая странности.
2. **Unit/domain** — pricing, status transitions, locale resolution, dedupe keys, permission mapping.
3. **Feature/API** — routes, validation, auth, DB effects, files, queue events.
4. **Contract** — recorded/sandbox PayPal, Paysera, Synvolve/SA, Venipak, SMTP, OAuth.
5. **E2E/UAT** — browser public site + Filament по role matrix.
6. **Non-functional** — performance, security, backup/restore, queue/retry, accessibility.

## Обязательная матрица

| Контур | Минимальные сценарии |
|---|---|
| Order creation | каждый product family, coupon/bonus, locale/country, upload, totals, transaction failure |
| Statuses | все семь значений, разрешённые/запрещённые переходы, timestamps, mail/SA side effects, replay |
| lead_id | всегда равен `orders.id`; unknown lead temporary entity; existing ID не меняется |
| Chat | client/admin/SA directions, ordering, read flags, duplicate message/event, attachments, missing order |
| PayPal | create/capture/success/cancel/decline/timeout/replay/concurrency/wrong amount |
| Paysera | valid/invalid signature, accept/cancel/callback, replay, out-of-order, wrong project/amount |
| Synvolve/SA | X-Api-Key, validation, 200 duplicate, idempotency race, retry, logging redaction, bot/escalation |
| Mail | 33 Mailables rendering, every locale, links, queued/sync, SMTP failure/retry, marketing consent |
| OAuth | Facebook + Google new/existing/no-email/collision/denied/revoked/callback URI |
| Catalogue/i18n | seven public locales, base English, all translations, fallback, localized slugs, hreflang/canonical/301 |
| Voyager parity | CRUD/filter/search/sort/order/relationship/upload/translation for each of 133 BREAD |
| Filament RBAC | 8 roles × navigation/resources/actions; custom Pages/Actions explicit authorization |
| Queue/scheduler | tries/backoff/timeout/failed jobs, duplicate worker, locks/timezone, worker restart |
| Checkout | desktop/mobile, guest/user, delivery incl. Venipak, payment return, confirmation/account |
| Venipak FN-08 | ACL всех ролей; server quote tampering; stable/stale pickup point; XML escaping/schema; package limits; timeout/4xx/5xx/malformed; duplicate/concurrency/crash-after-provider; courier cancel; label ownership/reprint/fake PDF |
| Files/PDF | ACL+ownership; traversal/symlink; real MIME/decode/limits; base64/SVG/audio; rename failpoints; private invoice; seven-locale golden PDF; retention/orphans |
| SA attachments | HTTPS/host/IP/redirect/TLS/stream limit, MIME mismatch, duplicate message/file hash, private download |

## Golden datasets

- обезличенная production-like DB copy;
- 100 representative orders и chat threads;
- translation manifest counts per locale/table/column;
- route list + response status/redirect/canonical map;
- payment/webhook payload fixtures с заменёнными секретами и PII;
- rendered HTML/email/PDF/image paths;
- metadata-only file corpus: classification/path/bytes/MIME/dimensions-or-pages/hash/reference count; private filenames/PII не публикуются;
- Voyager permission/navigation snapshot.

Снимок данных должен регулярно обновляться из production с анонимизацией. Для каждого снимка фиксируется watermark (`captured_at`, `MAX(orders.id)`, counts и hashes переводов по locale/table/column). Отдельный набор тестов проверяет заказы, контент и переводы, появившиеся или изменившиеся после предыдущего снимка, чтобы миграция не была проверена только на устаревших данных.

## Cross-version harness

Одинаковые fixtures запускаются на legacy и target. Сравниваются HTTP status/headers/body normalized fields, DB diff, emitted jobs/mail/events/log metadata и files. Допустимые различия оформляются explicit allowlist с бизнес-sign-off, а не обновлением snapshots без анализа.

## Performance gates

Baseline p50/p95/p99: homepage, category/search, product/generator, cart/checkout, account orders/chat, admin order list/detail, translation resource. Фиксировать SQL count/N+1, memory, bundle size. Release отклоняется при >20% p95 regression на критическом пути без согласованной причины.

## Security tests

IDOR на order/chat/files/payment requests; upload polyglot/double extension/oversize/image bomb; raw SVG active content; base64 MIME mismatch; canonical traversal/symlink; private PDF guessing; CSRF/origin для browser callbacks/actions; XSS в translations/HTML/PDF fields; SSRF/private-IP/redirect/DNS-rebinding через attachment URLs; API key timing/log leakage; brute force/rate limits; permission escalation; signed callback replay; secret scan.

## Exit criteria

Все Critical/High tests зелёные в CI и staging, flaky tests отсутствуют, sandbox contracts подтверждены, restore drill успешен, UAT подписан владельцами commerce/CRM/content, наблюдаемость и rollback rehearsal завершены.

Venipak sandbox/fake suite отдельно доказывает exactly-once shipment/courier attempt и safe reconciliation unknown outcome; live provider в CI и обычном staging заблокирован network policy.

Перед cutover дополнительно: synthetic/order traffic не блокирует реальные callback; редактор меняет контрольный перевод во время rehearsal; reconciliation за согласованное окно показывает ноль потерянных заказов, платежей, сообщений и переводов; target способен читать самые новые строки production, а legacy release остаётся доступным для мгновенного возврата.

## FN-09: auth/account/chat test matrix

- standard и custom login/register: throttle, CAPTCHA policy, session rotation, fixation, disabled user, password reset/expiry и отсутствие plaintext password в mail/log;
- Socialite: valid/expired/missing state, exact redirect origin, provider without/unverified email, existing local email collision, unique provider subject, link/unlink/recovery и session rotation;
- account ownership: anonymous, обычный user, painter, staff и каждая разрешённая роль × свой/чужой order/chat/image; произвольный ID всегда даёт 403/404 без side effects;
- четыре chat streams: author/source/read semantics, ordering, attachments, XSS corpus, length/encoding, double click/retry/concurrency, DB failure до/после outbox, mail/Synvolve/SA reconciliation;
- security containment: `/admin/check-user`, utility GET и unsigned unsubscribe недоступны; response/log snapshot не содержит hash/token/PII.

Локально прошёл существующий `ClientOrderPaymentTest`: 3 tests, 7 assertions на PHP 7.4. Специализированных auth/chat feature tests в baseline не найдено; это обязательный L3 gate.

## FN-10: mail/queue/scheduler matrix

- каждый Mailable/Notification: template key/version, семь locale, links/assets/glyphs, deterministic retry, correct recipient и suppression;
- order/payment/chat: SMTP fail до/после commit, outbox crash points, duplicate browser/callback, provider accepted-but-local-unknown;
- scheduler: one/two hosts, file/distributed locks, stale TTL, DST/timezone, missing cron, max batch и heartbeat;
- abandoned/overdue: paid/consent/expired token/cleanup, local-like 59 due dry-run, concurrent claim и crash-after-send;
- queue: sync/managed driver parity, timeout < retry_after, release/retry/failed/restart и old/new serialization;
- templates/security: raw HTML/XSS, signed unsubscribe, PII log redaction, remote asset outage и PDF artifact hash/MIME.

CI/staging используют mail sink/recipient allowlist и блокируют production SMTP. Live delivery не является тестовым transport.

FN-10 baseline на PHP 7.4: syntax lint 53 mail/notification/command/job/provider/service files — 0 ошибок; `OrderOverdueDelayNotifierTest` — 9 tests, 19 assertions. Остальные перечисленные L3 группы отсутствуют.

## FN-11: public catalogue/generator matrix

Обязательны differential tests для семи public locales, old/new gallery URLs, type/category/item 2–8, filters/search/pagination, price/country/options, session viewed/recommendation, generator start→upload→preview→basket, SA catalog/size/price parity, canonical/schema/sitemap/feed и frozen asset hash/load order.

Security negatives: anonymous maintenance GET must be 404/403 and make zero DB diff; invalid type/category/item; malformed size/tag/color lists; client price tampering; base64/multipart MIME/size/decode; translated/user XSS in HTML/attribute/JS contexts; repeat/retry/multi-tab. Random selections фиксируются fixture/seed либо исключаются из semantic hash.

## FN-12: Voyager/Filament parity matrix

Для каждой из 133 module rows обязательны role×action negatives, browse/filter/sort/search/pagination, field visibility/validation, relationship null/orphan, all locales/base/fallback, upload/replace/remove, restore/reorder/bulk concurrency, custom action receipt и optimistic-lock conflict. Отдельно проверяются dynamic-vs-static route registry, stale definitions, incomplete orders methods, metadata labels/menu/settings и `ee/et/uk`. Текущие 23 test files не содержат специализированного BREAD/admin writer/permission/browser coverage.

## FN-13: SEO/auxiliary contract matrix

Redirect suite строится из всех 256 CSV rows и production-log top/404 URLs: exact status/location/query, duplicate winner, one-hop, no cycle, space/non-ASCII/encoded, seven locales и cross-domain PL. Route suite сравнивает cached/uncached registry, unique names, missing handlers и console boot.

XML suite проверяет schema, content type, stable lastmod, canonical/domain/locale, URL/image/item counts и golden hashes на fresh snapshot. Feed suite обязательно ставит рядом items с/без sale и разным числом sizes, чтобы поймать `$price_saved` state leak. Security negatives: anonymous translation/meta/mail/debug/gift-card/test receiver, review MIME/size/base64/ownership/rate/moderation, Google failure/backoff. Local baseline XML valid, но production/browser/merchant L3 отсутствует.
