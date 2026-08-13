# PATCH /api/sa/leads/{lead_id}

## Purpose

Обновление существующего лида SA -> CRM.

## Field Contract

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `idempotency_key` | yes | string | `lead_update:CRM-...` | Dedup |
| `source` | yes | string | `SA` | Source |
| `update.fields` | no | object | `{email,city,budget}` | Generic field updates |
| `update.stage.id` | no | string | `pegging` | New stage (`watching`, `pegging`, `in_production`, `sended`, `send_lubanas`, `completed`) |
| `update.tags_add` | no | array<string> | `["vip"]` | Add tags |
| `update.tags_remove` | no | array<string> | `["cold"]` | Remove tags |
| `update.notes_append` | no | array<object> | `[{text,created_at}]` | Append notes |

## Response

```json
{
  "status": "ok",
  "data": {
    "lead_id": "CRM-LEAD-100045",
    "updated": true,
    "stage": { "id": "pegging" }
  }
}
```

## Business Rule

При передаче неизвестного или legacy статуса вернуть `400 VALIDATION_ERROR`.

Допустимые статусы:
- `watching -> pegging -> in_production -> sended -> send_lubanas -> completed`

Правило переходов:
- последовательность не обязательна
- можно сразу установить любой допустимый статус

Старые `STG-*` больше не принимаются; при передаче legacy stage API вернет `400 VALIDATION_ERROR`.

## Test cases

1. Валидное обновление `fields` -> `200`, поля обновлены.
2. Валидное обновление stage -> `200`, stage изменен.
3. Неизвестный или legacy stage -> `400 VALIDATION_ERROR`.
4. `tags_add` и `tags_remove` применяются корректно.
5. `notes_append` добавляет заметку с timestamp.
6. Повтор по `idempotency_key` -> duplicate без повторного эффекта.
7. Несуществующий `lead_id` -> `404` (или авто-создание по политике, если включено).
8. Неавторизованный запрос -> `401/403`.
