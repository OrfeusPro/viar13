# Release Candidate Checklist (2026-03-02)

## 1) Changelog (current integration increment)
- `services-catalog` switched from static data to real menu/db sources (`header_menu`, route-aware price resolvers).
- Country-aware pricing enabled via `country_tels.price_country_mltpr` with fallback country behavior.
- `services-catalog` response meta extended: `country_code`, `country_multiplier`, `currency=EUR`.
- Menu-link price mapping refined (`/new/caricature`, `/simpsons` exact slug handling).
- Feature tests expanded for `services-catalog` including country matrix coverage.
- Full API feature suite validated green (`53 tests`).
- Real-data E2E smoke completed (messages, pipeline, escalations, chat binding, attachments).

## 2) Environment checklist
Required:
- `APP_URL` (must be real public/local base URL used for attachment links)
- `SA_API_KEY`

Recommended:
- `SA_ATTACHMENT_MAX_SIZE`
- `SA_ATTACHMENT_ALLOWED_MIME`

Operational:
- Ensure `storage:link` exists for public attachment access.
- Ensure writable `storage/app/public/sa/attachments` path.

## 3) Pre-release validation
- Run: `vendor/bin/phpunit tests/Feature/Api`
- Smoke endpoints:
  - `/api/sa/webhooks/messages`
  - `/api/crm/webhooks/send-message`
  - `/api/sa/services-catalog`
  - `/api/crm/webhooks/pipeline-changed`
  - `/api/sa/escalations`
- Verify in DB:
  - `sa_events`, `sa_messages`, `sa_escalations`
  - `orders.sa_conversation_id`, `orders.sa_client_phone`, `orders.sa_bot_mode`
  - client chat records in `order_user_comments`

## 4) Rollback plan
Code rollback:
- revert deployment to previous release tag/commit.

DB rollback policy:
- do not drop integration tables in emergency rollback.
- keep data; disable only API routes/middleware binding via deployment rollback.

Runtime rollback:
- rotate `SA_API_KEY` if needed.
- temporarily block incoming integration traffic at gateway/reverse proxy.

Post-rollback checks:
- verify legacy order/admin flows unaffected.
- verify no new writes to `sa_*` tables after rollback point.

## 5) Known follow-ups
- `Orders::saveOrder` path notice: `Undefined index: terms_price` (non-blocking but should be fixed).
- external attachment download reliability depends on runtime network/SSL accessibility.
