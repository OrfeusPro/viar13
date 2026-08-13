# Release Handoff (PROD) - CRM <-> SA

Цель: быстрый post-deploy smoke check в production.

## Prerequisites

```bash
export BASE_URL="https://<your-prod-domain>/api"
export SA_API_KEY="<prod-x-api-key>"
```

## 1) Services catalog (base check)

```bash
curl -s -o /tmp/sa_01.json -w "%{http_code}\n" \
  -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services-catalog?lang=en&include_inactive=false&country_code=LV"
```

Ожидается:
- HTTP `200`
- `status=ok`
- `meta.country_code=LV`
- `data.services` не пустой

## 2) UK fallback contract (`uk -> ru`)

```bash
curl -s -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services-catalog?lang=ru&include_inactive=true&country_code=LV" > /tmp/sa_ru.json

curl -s -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services-catalog?lang=uk&include_inactive=true&country_code=LV" > /tmp/sa_uk.json
```

Ожидается:
- оба ответа `status=ok`
- для сервисов без `uk` перевода значения соответствуют `ru`

## 3) Sizes endpoint (canvas)

```bash
curl -s -o /tmp/sa_03.json -w "%{http_code}\n" \
  -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services/HM-2/sizes?country_code=LV"
```

Ожидается:
- HTTP `200`
- `status=ok`
- `data.sizes` не пустой
- есть поля `size,width,height,format,price.amount`

## 4) Price-by-size endpoint (exact size)

```bash
curl -s -o /tmp/sa_04.json -w "%{http_code}\n" \
  -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services/HM-2/price-by-size?size=40x60&country_code=FI"
```

Ожидается:
- HTTP `200`
- `status=ok`
- `data.size=40x60`
- `meta.country_code=FI`
- `data.price.amount > 0`

## 5) Price-by-size negative case

```bash
curl -s -o /tmp/sa_05.json -w "%{http_code}\n" \
  -H "X-Api-Key: $SA_API_KEY" \
  "$BASE_URL/sa/services/HM-2/price-by-size?size=999x999&country_code=LV"
```

Ожидается:
- HTTP `404`
- `status=error`
- `error.code=SIZE_NOT_FOUND`

## 6) Webhook message.created + idempotency duplicate

```bash
EVENT_ID="$(php -r 'echo sprintf("%04x%04x-%04x-%04x-%04x-%04x%04x%04x", mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff), mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000, mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));')"
IDEMP_KEY="postdeploy-msg-created-$(date +%s)"

cat >/tmp/sa_06_payload.json <<JSON
{
  "event_id": "$EVENT_ID",
  "event_type": "message.created",
  "idempotency_key": "$IDEMP_KEY",
  "occurred_at": "2026-03-02T12:00:00Z",
  "source": "SA",
  "data": {
    "lead_id": "POSTDEPLOY-TEST-1",
    "conversation_id": "POSTDEPLOY-CONV-1",
    "channel": "whatsapp",
    "message": {
      "message_id": "POSTDEPLOY-MSG-1",
      "direction": "inbound",
      "from": {"type":"client","phone":"+37120000001","name":"Postdeploy"},
      "to": {"type":"bot","id":"BOT-1"},
      "text": "postdeploy check",
      "attachments": [],
      "sent_at": "2026-03-02T12:00:00Z",
      "status": "received"
    }
  }
}
JSON

curl -s -o /tmp/sa_06_first.json -w "%{http_code}\n" \
  -H "X-Api-Key: $SA_API_KEY" -H "Content-Type: application/json" \
  -d @/tmp/sa_06_payload.json \
  "$BASE_URL/sa/webhooks/messages"

curl -s -o /tmp/sa_06_dup.json -w "%{http_code}\n" \
  -H "X-Api-Key: $SA_API_KEY" -H "Content-Type: application/json" \
  -d @/tmp/sa_06_payload.json \
  "$BASE_URL/sa/webhooks/messages"
```

Ожидается:
- первый вызов: HTTP `200`, `status=ok`
- второй вызов: HTTP `200`, `status=duplicate`

## 7) Bot-control validation check

```bash
curl -s -o /tmp/sa_07.json -w "%{http_code}\n" \
  -H "X-Api-Key: $SA_API_KEY" -H "Content-Type: application/json" \
  -d '{
    "event_id":"'"$(php -r 'echo sprintf("%04x%04x-%04x-%04x-%04x-%04x%04x%04x", mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff), mt_rand(0,0x0fff)|0x4000, mt_rand(0,0x3fff)|0x8000, mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));')"'" ,
    "event_type":"crm.bot_control",
    "idempotency_key":"postdeploy-bot-control-'"$(date +%s)"'",
    "occurred_at":"2026-03-02T12:10:00Z",
    "source":"CRM",
    "data":{
      "lead_id":"POSTDEPLOY-TEST-1",
      "conversation_id":"POSTDEPLOY-CONV-1",
      "action":"pause_bot",
      "changed_by":{"type":"manager","name":"No ID"}
    }
  }' \
  "$BASE_URL/crm/webhooks/bot-control"
```

Ожидается:
- HTTP `400`
- `status=error`
- `error.code=VALIDATION_ERROR`

## Completion Criteria

Release handoff считается успешным, если:
- все позитивные кейсы дают `200` + `status=ok`;
- duplicate кейс дает `200` + `status=duplicate`;
- негативные кейсы дают ожидаемые `400/404` и корректный `error.code`.
