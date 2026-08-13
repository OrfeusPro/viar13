# Test Matrix (Endpoint -> Test ID)

Статусы:
- `planned` - еще не реализован в автотестах
- `in_progress` - в работе
- `done` - реализован и прогоняется

## T01: POST `/api/sa/webhooks/messages`

| Test ID | Scenario | Type | Expected | Status |
|---|---|---|---|---|
| T01-001 | `message.created` valid payload | positive | `200`, stored | done |
| T01-002 | duplicate by `event_id` | idempotency | `200`, `status=duplicate` | done |
| T01-003 | `message.status` for known message | positive | `200`, status updated | done |
| T01-004 | missing `lead_id` | validation | `400 VALIDATION_ERROR` | done |
| T01-005 | invalid datetime | validation | `400 VALIDATION_ERROR` | done |
| T01-006 | invalid/missing `X-Api-Key` | security | `401/403` | done |

## T02: POST `/api/crm/webhooks/send-message`

| Test ID | Scenario | Type | Expected | Status |
|---|---|---|---|---|
| T02-001 | valid send request | positive | `200`, `accepted=true` | done |
| T02-002 | duplicate by `idempotency_key` | idempotency | no re-send | done |
| T02-003 | `mode_after_send=handoff_to_manager` | business | bot mode updated | done |
| T02-004 | missing `client.phone` | validation | `400` | done |
| T02-005 | missing `message.text` | validation | `400` | done |
| T02-006 | invalid/missing `X-Api-Key` | security | `401/403` | done |

## T03: GET `/api/sa/services-catalog`

| Test ID | Scenario | Type | Expected | Status |
|---|---|---|---|---|
| T03-001 | request without params | positive | `200`, full active catalog | done |
| T03-002 | `lang=ru` | localization | RU fields returned | done |
| T03-003 | `lang=en` | localization | localized content | done |
| T03-004 | `updated_since` filter | business | only changed records | done |
| T03-005 | `include_inactive=false` | business | inactive hidden | done |
| T03-006 | invalid `lang` | validation | `400` | done |
| T03-007 | invalid/missing `X-Api-Key` | security | `401/403` | done |

## T04: POST `/api/crm/webhooks/pipeline-changed`

| Test ID | Scenario | Type | Expected | Status |
|---|---|---|---|---|
| T04-001 | valid stage change | positive | `200`, stage updated | done |
| T04-002 | duplicate event | idempotency | `200`, no second update | done |
| T04-003 | invalid stage transition | business | `409 STAGE_TRANSITION_NOT_ALLOWED` | done |
| T04-004 | missing `stage.to.id` | validation | `400` | done |
| T04-005 | `bot_control.mode=paused` | business | bot paused | done |
| T04-006 | invalid/missing `X-Api-Key` | security | `401/403` | done |

## T05: POST `/api/sa/leads`

| Test ID | Scenario | Type | Expected | Status |
|---|---|---|---|---|
| T05-001 | minimal valid payload | positive | `201/200`, lead created | done |
| T05-002 | full payload with service fields | positive | fields persisted | done |
| T05-003 | duplicate `idempotency_key` | idempotency | no duplicate lead | done |
| T05-004 | missing `client.phone` | validation | `400` | done |
| T05-005 | invalid `channel` | validation | `400` | done |
| T05-006 | invalid/missing `X-Api-Key` | security | `401/403` | done |
| T05-007 | `HM-27` item completeness (portrait fields) | business | `200` + enriched `orders.items` | done |
| T05-008 | `HM-44` item completeness (gallery fields) | business | `200` + enriched `orders.items` | done |

## T06: PATCH `/api/sa/leads/{lead_id}`

| Test ID | Scenario | Type | Expected | Status |
|---|---|---|---|---|
| T06-001 | update fields valid | positive | `200`, fields updated | done |
| T06-002 | update stage valid | positive | `200`, stage changed | done |
| T06-003 | forbidden stage transition | business | `409` | done |
| T06-004 | tags add/remove | business | tags updated | done |
| T06-005 | duplicate `idempotency_key` | idempotency | no repeated mutation | done |
| T06-006 | lead not found | business | `404` (or policy-based behavior) | done |
| T06-007 | invalid/missing `X-Api-Key` | security | `401/403` | done |

## T07: POST `/api/sa/escalations`

| Test ID | Scenario | Type | Expected | Status |
|---|---|---|---|---|
| T07-001 | valid escalation | positive | `201/200`, task created | done |
| T07-002 | duplicate request | idempotency | no duplicate task | done |
| T07-003 | missing `reason_code` | validation | `400` | done |
| T07-004 | missing dialog messages | validation | `400` | done |
| T07-005 | set `handoff_to_manager` | business | mode changed | done |
| T07-006 | invalid/missing `X-Api-Key` | security | `401/403` | done |

## T08: POST `/api/crm/webhooks/bot-control`

| Test ID | Scenario | Type | Expected | Status |
|---|---|---|---|---|
| T08-001 | `resume_bot` | positive | `200`, active mode | done |
| T08-002 | `pause_bot` | positive | `200`, paused mode | done |
| T08-003 | `handoff_to_manager` | positive | `200`, handoff mode | done |
| T08-004 | duplicate event | idempotency | no repeated action | done |
| T08-005 | missing `changed_by.id` | validation | `400` | done |
| T08-006 | invalid/missing `X-Api-Key` | security | `401/403` | done |

