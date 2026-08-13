# POST /api/crm/webhooks/bot-control

## Purpose

Управление режимом бота со стороны CRM.

## Field Contract

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `event_id` | yes | string(UUID) | `f2e44a55-...` | Event ID |
| `event_type` | yes | string | `crm.bot_control` | Event type |
| `idempotency_key` | yes | string | `crm.bot_control:CONV-...:resume` | Dedup |
| `occurred_at` | yes | datetime | `2026-02-12T18:00:00Z` | UTC |
| `source` | yes | string | `CRM` | Source |
| `data.lead_id` | conditional | string/int | `CRM-LEAD-100045` | `orders.id`; required when `conversation_id` is absent |
| `data.conversation_id` | conditional | string | `CONV-778899` | Conversation; required when `lead_id` is absent |
| `data.action` | yes | enum | `resume_bot` | `pause_bot|resume_bot|handoff_to_manager` |
| `data.changed_by` | yes | object | `{type,id,name}` | Actor |

## Expected Result

1. Обновлен режим бота для диалога.
2. Записан аудит (кто/когда/какое действие).

## Test cases

1. `action=resume_bot` -> бот возвращается в active режим.
2. `action=pause_bot` -> бот переводится в paused.
3. `action=handoff_to_manager` -> приоритет менеджера включен.
4. Повтор по `event_id` -> duplicate без повторного эффекта.
5. `data.lead_id` can be omitted: bot mode is updated by `data.conversation_id`.
6. `data.conversation_id` can be omitted: bot mode is updated in `orders.sa_bot_mode` by `data.lead_id`.
7. Отсутствуют и `data.lead_id`, и `data.conversation_id` -> `400 VALIDATION_ERROR`.
8. Неизвестный `conversation_id` -> ожидаемое поведение по политике (создание/обновление dialog), не `500`.
9. Отсутствует `changed_by.id` -> `400 VALIDATION_ERROR`.
10. Неавторизованный запрос -> `401/403`.
