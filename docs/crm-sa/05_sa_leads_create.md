# POST /api/sa/leads

## Purpose

Создание лида SA -> CRM.

## Field Contract

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `idempotency_key` | yes | string | `create_lead:+380...` | Dedup |
| `source` | yes | string | `SA` | Source |
| `lead.external_ids.conversation_id` | no | string | `CONV-778899` | External link |
| `lead.client.phone` | yes | string | `+380686914307` | Client phone |
| `lead.client.name` | no | string | `Иван` | Name |
| `lead.channel` | yes | string | `whatsapp` | Channel |
| `lead.initial_message` | no | string | `Добрый день...` | Initial message |
| `lead.pipeline.id` | no | string | `PIPE-1` | Pipeline |
| `lead.stage.id` | no | string | `watching` | Stage (`watching`, `pegging`, `in_production`, `sended`, `send_lubanas`, `completed`) |
| `lead.service_request` | no | object | `{service_id,...}` | Service context |
| `lead.delivery` | no | object | `{method,payment,...}` | Delivery/payment override for order creation |
| `lead.fields` | no | object | `{email,city}` | Custom fields |

### `lead.delivery` object (optional)

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `lead.delivery.method` | no | string | `pickup_at_viar_workshop` | Allowed: `to_the_door`, `pickup_at_viar_workshop`, `city_delivery`, `venipak`, `pickup_Riga`, `pickup_Daugavplis`, `pickup_Daugavpils`, `email` |
| `lead.delivery.payment` | no | string | `transfer` | Allowed current values: `on_delivery`, `online_paysera`, `creditcart`, `google_pay`, `apple_pay`, `paypalOnetimePayment`, `transfer`, `prepayment`, `cash_in_office` |
| `lead.delivery.deliv_price` | no | number | `0` | Explicit delivery price override |
| `lead.delivery.country` | no | string | `LV` | ISO country code |
| `lead.delivery.city` | no | string | `Riga` | Delivery city |
| `lead.delivery.address` | no | string | `Lubanas iela 65` | Delivery address |
| `lead.delivery.postal_index` | no | string | `LV-1019` | Postal/ZIP code |
| `lead.delivery.pickup_workshop_id` | no | integer | `1` | For pickup method |
| `lead.delivery.delivery_town_id` | no | integer | `12` | For city delivery method |
| `lead.delivery.delivery_photo_short_code` | no | string | `PICKUP-TEST` | Optional delivery profile short code |

Legacy aliases accepted by API and normalized before persistence:

- `online` -> `online_paysera`
- `online_banking` -> `online_paysera`
- `bank` -> `transfer`

## Response

```json
{
  "status": "ok",
  "data": {
    "lead_id": "CRM-LEAD-100045",
    "contact_id": "CRM-CONTACT-9001",
    "deal_id": "CRM-DEAL-7001",
    "pipeline": { "id": "PIPE-1" },
    "stage": { "id": "watching" },
    "links": {
      "lead_url": "https://crm.example.com/leads/CRM-LEAD-100045"
    }
  }
}
```

## Test cases

1. Минимальный валидный payload -> `201/200`, создан `lead_id`.
2. Полный payload (pipeline/stage/service_request) -> все поля сохранены.
3. Повтор с тем же `idempotency_key` -> duplicate без повторного создания.
4. Отсутствует `lead.client.phone` -> `400 VALIDATION_ERROR`.
5. Некорректный `channel` -> `400 VALIDATION_ERROR`.
6. Неавторизованный запрос -> `401/403`.
7. Конфликт уникальности внешнего `conversation_id` -> предсказуемый upsert/ошибка (не `500`).

## Notes

1. Реальные статусы API теперь совпадают с `orders.status`.
2. Входной контракт принимает только реальные статусы (`watching`, `pegging`, `in_production`, `sended`, `send_lubanas`, `completed`); старые `STG-*` возвращают `400 VALIDATION_ERROR`.
