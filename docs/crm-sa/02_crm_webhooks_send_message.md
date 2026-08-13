# POST /api/crm/webhooks/send-message

## Purpose

Отправка сообщения клиенту из CRM через SA.

## Field Contract

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `event_id` | yes | string(UUID) | `2d2c5e6a-...` | Event ID |
| `event_type` | yes | string | `crm.message.send` | Event type |
| `idempotency_key` | yes | string | `crm.message.send:...` | Dedup |
| `occurred_at` | yes | datetime | `2026-02-12T16:40:00Z` | UTC |
| `source` | yes | string | `CRM` | Source |
| `data.lead_id` | no | string/int | `CRM-LEAD-100045` | `orders.id`; optional before order/lead creation |
| `data.conversation_id` | yes | string | `CONV-778899` | Conversation |
| `data.channel` | yes | string | `whatsapp` | Channel |
| `data.client.phone` | yes | string | `+380686914307` | Client phone |
| `data.manager.id` | yes | string | `CRM-U-12` | Manager ID |
| `data.manager.name` | no | string | `Ольга` | Manager name |
| `data.message.client_visible_sender` | yes | enum | `manager` | Sender type |
| `data.message.text` | yes | string | `Здравствуйте...` | Text |
| `data.bot_control.mode_after_send` | no | enum | `handoff_to_manager` | `active|paused|handoff_to_manager` |

## Response

```json
{
  "status": "ok",
  "result": {
    "accepted": true,
    "sa_message_id": "SA-MSG-555",
    "provider_message_id": "wamid.HBgM...",
    "queued": true
  }
}
```

## Test cases

1. Валидный запрос на отправку -> `200`, `accepted=true`, возвращены IDs сообщения.
2. Повтор с тем же `idempotency_key` -> дедуп без повторной отправки.
3. `bot_control.mode_after_send=handoff_to_manager` -> режим диалога переключен.
4. `bot_control.mode_after_send=paused` -> бот замолкает до ручного resume.
5. Отсутствует `client.phone` -> `400 VALIDATION_ERROR`.
6. Отсутствует `message.text` -> `400 VALIDATION_ERROR`.
7. Неавторизованный запрос (`X-Api-Key`) -> `401/403`.
