# POST /api/sa/escalations

## Purpose

Эскалация диалога SA -> CRM с созданием задачи менеджеру.

## Field Contract

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `idempotency_key` | yes | string | `escalation:CONV-...` | Dedup |
| `source` | yes | string | `SA` | Source |
| `escalation.lead_id` | yes | string/int | `CRM-LEAD-100045` | `orders.id` |
| `escalation.conversation_id` | yes | string | `CONV-778899` | Conversation |
| `escalation.priority` | yes | enum | `urgent` | Priority |
| `escalation.reason_code` | yes | string | `NO_KB_ANSWER` | Reason code |
| `escalation.reason_text` | no | string | `Нужен менеджер...` | Reason text |
| `escalation.confidence` | no | number | `0.42` | Model confidence |
| `escalation.suggested_next` | no | object | `{manager_goal,draft_reply}` | Suggestion |
| `escalation.dialog` | yes | object | `{channel,client,messages[]}` | Conversation excerpt |
| `escalation.bot_control.set_mode` | no | enum | `handoff_to_manager` | Mode after escalation |
| `escalation.bot_control.allow_manager_takeover` | no | bool | `true` | Takeover |

## Response

```json
{
  "status": "ok",
  "data": {
    "task_id": "CRM-TASK-50077",
    "lead_id": "CRM-LEAD-100045",
    "links": {
      "task_url": "https://crm.example.com/tasks/CRM-TASK-50077",
      "lead_url": "https://crm.example.com/leads/CRM-LEAD-100045"
    }
  }
}
```

## Test cases

1. Валидная эскалация -> `201/200`, создан `task_id`.
2. Повтор с тем же `idempotency_key` -> duplicate без второй задачи.
3. Отсутствует `reason_code` -> `400 VALIDATION_ERROR`.
4. Отсутствует `dialog.messages[]` -> `400 VALIDATION_ERROR`.
5. `priority=urgent` -> SLA/приоритет задачи выставлен корректно.
6. `bot_control.set_mode=handoff_to_manager` -> режим применен.
7. Неавторизованный запрос -> `401/403`.
