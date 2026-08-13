# Common Rules

## Transport

1. Protocol: `HTTPS`.
2. Content type: `application/json; charset=utf-8`.
3. Time format: `ISO-8601 UTC` (example: `2026-02-12T16:30:25Z`).

## Auth

1. Header: `X-Api-Key: <shared_secret>`.
2. Optional hardening: IP allowlist.

## Idempotency

1. Each request includes:
   - `event_id` (UUID) for events,
   - `idempotency_key` for operation deduplication.
2. If event already processed:
   - return `200 OK`,
   - body: `{"status":"duplicate"}`.

## SLA / Retries

1. Webhook receiver should ACK quickly (target <= 3 sec).
2. Heavy processing should be async (queue/job).
3. Retry schedule on sender side for `5xx`: `1m, 5m, 15m, 60m`.
4. For `429` respect `Retry-After`.

## Standard Error

```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "phone is required",
    "details": [
      { "field": "phone", "issue": "missing" }
    ]
  }
}
```
