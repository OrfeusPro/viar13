# Release Handoff PROD (RU) - 2026-03-03

Цель: быстрый post-deploy smoke-check интеграции CRM <-> SA (7 команд).

## 0) Переменные окружения

```bash
export BASE_URL="https://<your-prod-domain>/api"
export SA_API_KEY="<prod-x-api-key>"
```

## 1) Каталог услуг (базовая доступность)

```bash
curl -sS -X GET \
  -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services-catalog?lang=ru&include_inactive=false&country_code=LV"
```

Ожидается:
- HTTP `200`
- `status=ok`
- `meta.country_code=LV`
- `data.services` не пустой

## 2) Размеры сервиса (HM-2 Canvas)

```bash
curl -sS -X GET \
  -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services/HM-2/sizes?country_code=LV"
```

Ожидается:
- HTTP `200`
- `status=ok`
- есть `data.sizes[]`
- у элементов есть `size` и `price.amount`

## 3) Цена по размеру (позитивный кейс)

```bash
curl -sS -X GET \
  -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services/HM-2/price-by-size?size=60x80&country_code=LV"
```

Ожидается:
- HTTP `200`
- `status=ok`
- `data.size=60x80`
- `data.price.amount > 0`

## 4) Цена по размеру (негативный кейс)

```bash
curl -sS -X GET \
  -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services/HM-2/price-by-size?size=999x999&country_code=LV"
```

Ожидается:
- HTTP `404`
- `status=error`
- `error.code=SIZE_NOT_FOUND`

## 5) Создание лида/заказа (минимальный валидный payload)

```bash
curl -sS -X POST \
  -H "X-Api-Key: $SA_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "idempotency_key":"postdeploy-lead-20260303-1",
    "source":"SA",
    "lead":{
      "channel":"whatsapp",
      "client":{"phone":"+37120000001","name":"Postdeploy Smoke"},
      "service_request":{"service_id":"HM-2","country_code":"LV","options":{"size":"60x80"}}
    }
  }' \
  "$BASE_URL/sa/leads"
```

Ожидается:
- HTTP `200`
- `status=ok`
- в ответе есть `data.lead_id`

## 6) Message webhook + duplicate идемпотентность

```bash
curl -sS -X POST \
  -H "X-Api-Key: $SA_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "event_id":"11111111-1111-4111-8111-111111111111",
    "event_type":"message.created",
    "idempotency_key":"postdeploy-msg-created-20260303-1",
    "occurred_at":"2026-03-03T09:00:00Z",
    "source":"SA",
    "data":{
      "lead_id":"17022",
      "conversation_id":"E2E-CANVAS-LV-1772527692491",
      "channel":"whatsapp",
      "message":{
        "message_id":"POSTDEPLOY-MSG-1",
        "direction":"inbound",
        "from":{"type":"client","phone":"+37120000001","name":"Postdeploy"},
        "to":{"type":"bot","id":"BOT-1"},
        "text":"postdeploy message.created",
        "attachments":[],
        "sent_at":"2026-03-03T09:00:00Z",
        "status":"received"
      }
    }
  }' \
  "$BASE_URL/sa/webhooks/messages"
```

Запустить команду выше второй раз без изменений.

Ожидается:
- 1-й вызов: HTTP `200`, `status=ok`
- 2-й вызов: HTTP `200`, `status=duplicate`

## 7) Pipeline transition check (валидный + защита)

```bash
curl -sS -X POST \
  -H "X-Api-Key: $SA_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "event_id":"22222222-2222-4222-8222-222222222222",
    "event_type":"crm.lead.stage_changed",
    "idempotency_key":"postdeploy-pipeline-20260303-1",
    "occurred_at":"2026-03-03T09:05:00Z",
    "source":"CRM",
    "data":{
      "lead_id":"17022",
      "pipeline":{"id":"PIPE-1"},
      "stage":{"from":{"id":"STG-40"},"to":{"id":"STG-50"}},
      "changed_by":{"type":"manager","id":"1","name":"Admin"}
    }
  }' \
  "$BASE_URL/crm/webhooks/pipeline-changed"
```

Ожидается:
- HTTP `200`, `status=ok` для валидного перехода
- при невалидном переходе: HTTP `409`, `error.code=STAGE_TRANSITION_NOT_ALLOWED`

## Критерий успешного handoff

Handoff успешен, если:
- позитивные кейсы возвращают `200 + status=ok`,
- duplicate возвращает `200 + status=duplicate`,
- негативные кейсы возвращают ожидаемые `404/409` и корректный `error.code`.
