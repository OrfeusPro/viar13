# POST /api/sa/webhooks/messages

## Purpose

Прием сообщений и статусов доставки из SA в CRM.

## Event types

1. `message.created`
2. `message.status`

## Field Contract

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `event_id` | yes | string(UUID) | `4c1a0b1f-...` | Event ID |
| `event_type` | yes | enum | `message.created` / `message.status` | Event kind |
| `idempotency_key` | yes | string | `message.created:whatsapp:MSG_109223` | Dedup key |
| `occurred_at` | yes | datetime | `2026-02-12T16:30:25Z` | UTC |
| `source` | yes | string | `SA` | Source |
| `data.lead_id` | no | string/int | `CRM-LEAD-100045` | maps to `orders.id`; optional before order/lead creation |
| `data.conversation_id` | yes | string | `CONV-778899` | Conversation |
| `data.channel` | yes | string | `whatsapp` | Channel |

For `message.created`:

| Field | Required | Type | Example |
|---|---|---|---|
| `data.message.message_id` | yes | string | `MSG_109223` |
| `data.message.direction` | yes | enum | `inbound` |
| `data.message.from` | yes | object | `{type,phone,name}` |
| `data.message.to` | yes | object | `{type,id}` |
| `data.message.text` | no | string | `Добрый день...` |
| `data.message.attachments` | no | array | `[]` |
| `data.message.sent_at` | yes | datetime | `2026-02-12T16:30:24Z` |
| `data.message.status` | yes | enum | `received` |
| `data.message.provider_meta` | no | object | `{provider,wa_message_id}` |

For `message.status`:

| Field | Required | Type | Example |
|---|---|---|---|
| `data.message_id` | yes | string | `MSG_109224` |
| `data.status` | yes | enum | `delivered` |
| `data.status_at` | yes | datetime | `2026-02-12T16:41:09Z` |

## Responses

Success:
```json
{"status":"ok","result":{"stored":true}}
```

Duplicate:
```json
{"status":"duplicate"}
```

## Test cases

1. `message.created` с валидным payload -> `200`, сообщение сохранено.
2. Повтор того же `event_id` -> `200`, `status=duplicate`, без дубля в БД.
3. `message.status` для существующего `message_id` -> `200`, статус обновлен.
4. `message.status` с неизвестным `message_id` -> ожидаемое поведение зафиксировать (create-or-ignore), не `500`.
5. Не передан `lead_id` -> сообщение принимается по `conversation_id`.
6. Не передан/неверный `X-Api-Key` -> `401/403`.
7. Неверный формат даты `occurred_at` -> `400 VALIDATION_ERROR`.
8. Payload с вложением (URL файла) -> файл скачан/сохранен, запись корректна.
