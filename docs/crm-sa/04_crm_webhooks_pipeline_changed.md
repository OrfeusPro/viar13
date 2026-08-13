# POST /api/crm/webhooks/pipeline-changed

## Purpose

Передача изменения pipeline/stage из CRM в SA.

## Field Contract

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `event_id` | yes | string(UUID) | `7f3c2c2b-...` | Event ID |
| `event_type` | yes | string | `crm.lead.stage_changed` | Type |
| `idempotency_key` | yes | string | `crm.lead.stage_changed:...` | Dedup |
| `occurred_at` | yes | datetime | `2026-02-12T16:55:00Z` | UTC |
| `source` | yes | string | `CRM` | Source |
| `data.lead_id` | yes | string/int | `CRM-LEAD-100045` | `orders.id` |
| `data.pipeline.id` | yes | string | `PIPE-1` | Pipeline ID |
| `data.pipeline.name` | no | string | `Продажи` | Pipeline name |
| `data.stage.from.id` | no | string | `watching` | Previous stage (`watching`, `pegging`, `in_production`, `sended`, `send_lubanas`, `completed`) |
| `data.stage.to.id` | yes | string | `in_production` | New stage |
| `data.changed_by` | yes | object | `{type,id,name}` | Actor |
| `data.reason` | no | string | `Клиент ответил...` | Optional reason |
| `data.bot_control.mode` | no | enum | `paused` | Bot mode sync |

## Test cases

1. Валидная смена stage -> `200`, stage обновлен в CRM/SA.
2. Повтор по `event_id` -> `200`, duplicate без повторного эффекта.
3. Неизвестный `lead_id` -> поведение по политике (создать/ошибка) зафиксировано.
4. `stage.to.id` отсутствует -> `400 VALIDATION_ERROR`.
5. Неизвестный статус (`archived`, `STG-30` и т.д.) -> `400 VALIDATION_ERROR`.
6. `bot_control.mode=paused` -> режим бота обновлен.
7. Неавторизованный запрос -> `401/403`.

## Transition policy

1. Допустимые статусы:
   - `watching -> pegging -> in_production -> sended -> send_lubanas -> completed`
2. Последовательность переходов не требуется: CRM может сразу установить любой допустимый статус.
3. Входной контракт принимает только реальные статусы `orders.status`; старые `STG-*` и неизвестные значения возвращают `400 VALIDATION_ERROR`.
