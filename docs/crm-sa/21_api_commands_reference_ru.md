# CRM <-> SA: Справочник команд API (рабочий)

Дата актуальности: `2026-05-01`
Источник истины по коду: `routes/api.php`, `app/Http/Controllers/Api/SaIntegrationController.php`

## 1. Общие правила

1. Базовый URL: `{{APP_URL}}/api`
2. Auth для всех команд: заголовок `X-Api-Key: {{SA_API_KEY}}`
3. Формат: `application/json`, UTF-8
4. Даты: ISO-8601 (`2026-03-03T12:00:00Z`)
5. Идемпотентность:
   - дубли по `event_id` или `idempotency_key` возвращают `200 {"status":"duplicate"}`
6. Общий формат ошибок:
```json
{
  "status": "error",
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": [{"field":"...", "issue":"..."}]
  }
}
```

## 2. Карта команд (как в SA Simulator)

1. Новый лид: `POST /sa/leads`
2. Сообщение (входящее): `POST /sa/webhooks/messages` (`event_type=message.created`)
3. Сообщение со статусом: `POST /sa/webhooks/messages` (`event_type=message.status`)
4. CRM -> SA send message: `POST /crm/webhooks/send-message`
5. Смена стадии лида (SA): `PATCH /sa/leads/{lead_id}`
6. Pipeline changed (CRM): `POST /crm/webhooks/pipeline-changed`
7. Эскалация: `POST /sa/escalations`
8. Bot control: `POST /crm/webhooks/bot-control`
9. Каталог услуг: `GET /sa/services-catalog`
10. Полный каталог: `GET /sa/catalog-full`
11. Размеры услуги: `GET /sa/services/{service_id}/sizes`
12. Цена услуги по размеру: `GET /sa/services/{service_id}/price-by-size`
13. Поиск заказов: `GET /sa/orders/lookup`

## 3. Команды подробно

### 3.1 `POST /sa/leads`

Назначение: создать/резолвить лид в CRM (`lead_id` = `orders.id`).

Обязательные поля:
1. `idempotency_key`
2. `source` = `SA`
3. `lead.client.phone`
4. `lead.channel` (`whatsapp|telegram|viber|facebook|instagram|webchat`)

Опциональные поля:
1. `lead.external_ids.conversation_id`
2. `lead.client.name`
3. `lead.initial_message`
4. `lead.pipeline.id`
5. `lead.stage.id`
6. `lead.service_request.*`
7. `lead.delivery.*`
8. `lead.fields`

Поля по умолчанию:
1. `data.pipeline.id` в ответе: `PIPE-1`
2. `data.stage.id` в ответе: `watching`
3. Если `lead.delivery.deliv_price` не передан, стоимость доставки берется из страны/дефолтов CRM.
4. Если `lead.service_request` не передан, создается минимальная сущность заказа.

Минимальный пример request:
```json
{
  "idempotency_key": "create_lead:example:001",
  "source": "SA",
  "lead": {
    "client": { "phone": "+37129999999" },
    "channel": "whatsapp"
  }
}
```

Успешный response:
```json
{
  "status": "ok",
  "data": {
    "lead_id": "16950",
    "contact_id": "CRM-CONTACT-16950",
    "deal_id": "CRM-DEAL-16950",
    "pipeline": {"id":"PIPE-1"},
    "stage": {"id":"watching"}
  }
}
```

### 3.2 `POST /sa/webhooks/messages`

Назначение: прием входящих/исходящих сообщений и статусов сообщений.

Общие обязательные поля:
1. `event_id` (UUID)
2. `event_type` (`message.created` или `message.status`)
3. `idempotency_key`
4. `occurred_at`
5. `source` = `SA`
6. `data` (object)

Для `message.created` дополнительно обязательно:
1. `data.message.message_id`
2. `data.message.direction` (`inbound|outbound`)
3. `data.message.from`
4. `data.message.to`
5. `data.message.sent_at`
6. `data.message.status` (`received|sent|delivered|read|failed`)
7. `data.conversation_id`
8. `data.channel` = `whatsapp`

Для `message.status` дополнительно обязательно:
1. `data.message_id`
2. `data.status`
3. `data.status_at`

Поля по умолчанию:
1. `data.lead_id` опционален: сообщение может быть принято до создания заказа.
2. `data.conversation_id` остается основной связующей сущностью для pre-order сообщений.
3. `data.message.text` опционально (может быть пустым при вложениях).
4. `data.message.attachments` опционально, при наличии файлы копируются локально.
5. Если позже вызывается `POST /sa/leads` с тем же `lead.external_ids.conversation_id`, накопленные `sa_messages` привязываются к новому `orders.id`.

Успешный response:
```json
{"status":"ok","result":{"stored":true}}
```

### 3.3 `POST /crm/webhooks/send-message`

Назначение: команда от CRM на исходящее сообщение в SA.

Обязательные поля:
1. `event_id` (UUID)
2. `event_type` = `crm.message.send`
3. `idempotency_key`
4. `occurred_at`
5. `source` = `CRM`
6. `data.conversation_id`
8. `data.channel` = `whatsapp`
9. `data.client.phone`
10. `data.manager.id`
11. `data.message.client_visible_sender` (`manager|agent|system`)
12. `data.message.text`

Опционально:
1. `data.lead_id`
2. `data.bot_control.mode_after_send` (`active|paused|handoff_to_manager`)

Поля по умолчанию:
1. `data.lead_id` опционален: можно отправлять сообщение в уже существующую `conversation` до создания заказа.
2. `mode_after_send` по умолчанию `null` (если не передан).

Успешный response:
```json
{
  "status":"ok",
  "result":{
    "accepted":true,
    "queued":true,
    "bot_mode":"paused"
  }
}
```

### 3.4 `PATCH /sa/leads/{lead_id}`

Назначение: обновление полей/стадии лида.

Обязательные поля:
1. `idempotency_key`
2. `source` = `SA`
3. `update` (object)

Опционально:
1. `update.fields`
2. `update.stage.id`
3. `update.stage.from.id`
4. `update.tags_add[]`
5. `update.tags_remove[]`
6. `update.notes_append[]`

Поля по умолчанию:
1. Если `update.stage.from.id` не передан, система пытается определить его из текущего `orders.status`.
2. Если `lead_id` не найден, текущая политика: `404 LEAD_NOT_FOUND`.

Ошибки:
1. Неизвестный или legacy stage -> `400 VALIDATION_ERROR`.

### 3.5 `POST /crm/webhooks/pipeline-changed`

Назначение: уведомление CRM о смене стадии лида.

Обязательные поля:
1. `event_id` (UUID)
2. `event_type` = `crm.lead.stage_changed`
3. `idempotency_key`
4. `occurred_at`
5. `source` = `CRM`
6. `data.lead_id`
7. `data.pipeline.id`
8. `data.stage.to.id`
9. `data.changed_by.type` (`manager|system|agent`)
10. `data.changed_by.id`

Опционально:
1. `data.stage.from.id`
2. `data.bot_control.mode` (`active|paused|handoff_to_manager`)

Поля по умолчанию:
1. `data.stage.from.id` может отсутствовать.
2. При валидном обновлении `orders.status` устанавливается в переданный статус.
3. Последовательность переходов не обязательна: можно сразу ставить любой допустимый статус.
4. Старые `STG-*` больше не поддерживаются; используйте только реальные статусы `watching`, `pegging`, `in_production`, `sended`, `send_lubanas`, `completed`.

### 3.6 `POST /sa/escalations`

Назначение: эскалация диалога от SA к менеджеру CRM.

Обязательные поля:
1. `idempotency_key`
2. `source` = `SA`
3. `escalation.lead_id`
4. `escalation.conversation_id`
5. `escalation.priority` (`low|normal|high|urgent`)
6. `escalation.reason_code`
7. `escalation.dialog.channel`
8. `escalation.dialog.client`
9. `escalation.dialog.messages` (min 1)

Опционально:
1. `escalation.reason_text`
2. `escalation.confidence` (0..1)
3. `escalation.suggested_next`
4. `escalation.bot_control.set_mode` (`active|paused|handoff_to_manager`)
5. `escalation.bot_control.allow_manager_takeover` (bool)

Поля по умолчанию:
1. Внутренний `task_id` генерируется автоматически.

### 3.7 `POST /crm/webhooks/bot-control`

Назначение: управление режимом бота из CRM.

Обязательные поля:
1. `event_id` (UUID)
2. `event_type` = `crm.bot_control`
3. `idempotency_key`
4. `occurred_at`
5. `source` = `CRM`
6. `data.lead_id` или `data.conversation_id`
7. `data.action` (`pause_bot|resume_bot|handoff_to_manager`)
8. `data.changed_by.type` (`manager|system|agent`)
9. `data.changed_by.id`

Опционально:
1. `data.lead_id`: если заказ уже создан, передавайте `orders.id`.
2. `data.conversation_id`: если заказ еще не создан, управляйте ботом по dialog id.
3. `data.changed_by.name`

Поля по умолчанию:
1. Если `data.lead_id` не передан, API обновит `sa_conversations.bot_mode` по `data.conversation_id`.
2. Если `data.conversation_id` не передан, API обновит `orders.sa_bot_mode` по `data.lead_id`.
3. Если `lead_id` уже связан с conversation, API вернет `data.conversation_id`.
4. Если `conversation_id` уже связан с заказом, API вернет `data.resolved_lead_id`.
5. `action=pause_bot` -> `bot_mode=paused`
6. `action=resume_bot` -> `bot_mode=active`
7. `action=handoff_to_manager` -> `bot_mode=handoff_to_manager`

### 3.8 `GET /sa/services-catalog`

Назначение: получить каталог услуг для SA.

Query params:
1. `lang` (optional): `ru|uk|en`, default `ru`
2. `updated_since` (optional): ISO date
3. `include_inactive` (optional): `true|false|1|0`, default `false`
4. `country` (optional)
5. `country_code` (optional)
6. `client_phone` / `phone` (optional): если `country_code` не передан, pricing country определяется по префиксу номера

Поля по умолчанию:
1. Валюта: `EUR`
2. Если страна не передана, используется `client_phone` / `phone`, а если их нет - дефолтный country context (обычно `LV`).

### 3.9 `GET /sa/catalog-full`

Назначение: получить полный каталог для SA одним запросом.

В ответе endpoint уже возвращает:
1. `categories`
2. `services`
3. `services[*].sizes`
4. `services[*].photo`
5. `services[*].photos`
6. `bundles`

Query params:
1. `lang` (optional): `ru|uk|en`, default `ru`
2. `updated_since` (optional): ISO date
3. `include_inactive` (optional): `true|false|1|0`, default `false`
4. `country` (optional)
5. `country_code` (optional)
6. `client_phone` / `phone` (optional): если `country_code` не передан, pricing country определяется по префиксу номера

Поля по умолчанию:
1. Валюта: `EUR`
2. Если страна не передана, используется `client_phone` / `phone`, а если их нет - дефолтный country context (обычно `LV`).
3. Для услуги без найденного изображения возвращается `photo=null`, `photos=[]`.

### 3.10 `GET /sa/services/{service_id}/sizes`

Назначение: размеры и цены по услуге.

Path params:
1. `service_id` формата `HM-<number>`

Query:
1. `country` optional
2. `country_code` optional

Поля по умолчанию:
1. Если для услуги нет размерной матрицы, возвращается `sizes: []` с `status=ok`.

### 3.11 `GET /sa/services/{service_id}/price-by-size`

Назначение: цена конкретной услуги по размеру.

Path params:
1. `service_id` формата `HM-<number>`

Query:
1. `size` required, формат `WxH` (пример `60x80`)
2. `country` optional
3. `country_code` optional

Ошибки:
1. неизвестная услуга -> `404 SERVICE_NOT_FOUND`
2. неизвестный размер -> `404 SIZE_NOT_FOUND`

### 3.12 `GET /sa/orders/lookup`

Назначение: получить информацию по заказу, когда бот знает номер заказа или старый номер телефона клиента. Если по телефону найдено несколько заказов, API возвращает все найденные заказы от новых к старым.

Query:
1. `order_id` optional: номер заказа (`orders.id`)
2. `lead_id` optional: alias к `order_id`
3. `phone` optional: телефон клиента/получателя
4. `client_phone` optional: alias к `phone`
5. `lang` optional: язык человекочитаемых подписей, default `ru`

Правила:
1. Должен быть передан хотя бы один идентификатор: `order_id`, `lead_id`, `phone` или `client_phone`.
2. Телефон очищается от пробелов, скобок, дефисов и других лишних символов, затем валидируется как `7-15` цифр с опциональным `+`.
3. По телефону поиск идет по `users.phone`, `orders.delivery` (`phone`, `payer_phone`) и `orders.sa_client_phone`, если поле есть в БД.
4. Endpoint не событийный, поэтому `event_id` и `idempotency_key` не нужны.
5. `lang` влияет только на подписи `status.title` и `payment.status_title`; коды `status.id` и `payment.status` остаются стабильными.
6. Для `uk` используется fallback на русские подписи.

Ответ содержит:
1. `status`, `payment`, `pricing`
2. `client`, `recipient`, `delivery`, `billing`
3. `comments` (`order_comment`, `client_comment`, `delivery_comment`, `item_comments`)
4. `products[]`
5. `sa` (`conversation_id`, `client_phone`, `bot_mode`)

Блок `billing`:
1. `is_company`: `true`, если заказ оформлен на юр. лицо или заполнены юр. реквизиты.
2. `invoice_uuid`: UUID счета, если был создан.
3. `company.name`: название юр. лица из `orders.ur_name_l`.
4. `company.registration_number`: регистрационный номер из `orders.ur_reg_num`.
5. `company.legal_address`: юридический адрес из `orders.ur_legal_addr`.
6. `company.vat_number`: VAT/PVN номер из `orders.ur_pnr_nr`.
7. `company.bank_name`, `company.bank_code`, `company.bank_account_code`: банковские реквизиты.

Особенности `products[]`:
1. `image` берется из реального поля корзины `activeImage`, если оно есть.
2. `service_id` добирается из legacy basket `pid`: `1 -> HM-2`, `2 -> HM-3`, `5 -> GC-5`, для остальных numeric `pid` проверяется `HM-{pid}`.
3. Для старых portrait/caricature позиций без `pid` есть fallback по названию, например `Caricature -> HM-27`.
4. `price` = итоговая цена позиции из `sumPrice`.
5. `unit_price` и `original_item_price` возвращают исходные цены позиции, если они есть.
6. Значения `"undefined"`/`"null"` в options очищаются до `null`.

Для заказов, где уже работает художник, дополнительно возвращается блок `artist`:
1. `painter_images`
2. `painter_sketch_images`
3. `painter_images_status`, `painter_images_status_date`
4. `painter_sketch_images_status`, `painter_sketch_images_status_date`
5. `painter_comment`
6. `is_show_painter_images`

Пример:
```bash
curl -X GET "{{APP_URL}}/api/sa/orders/lookup?phone=%2B37129999999&lang=lv" \
  -H "X-Api-Key: {{SA_API_KEY}}" \
  -H "Accept: application/json"
```

Примеры поиска:
```bash
curl -X GET "{{APP_URL}}/api/sa/orders/lookup?order_id=17335&lang=ru" \
  -H "X-Api-Key: {{SA_API_KEY}}" \
  -H "Accept: application/json"
```

```bash
curl -X GET "{{APP_URL}}/api/sa/orders/lookup?phone=%2B37125618028&lang=ru" \
  -H "X-Api-Key: {{SA_API_KEY}}" \
  -H "Accept: application/json"
```

Ошибки:
1. нет ключа поиска -> `400 VALIDATION_ERROR`
2. некорректный телефон -> `400 VALIDATION_ERROR`

## 4. Готовые cURL шаблоны

```bash
curl -X GET "{{APP_URL}}/api/sa/services-catalog?lang=en&country_code=LV&include_inactive=false" \
  -H "X-Api-Key: {{SA_API_KEY}}" \
  -H "Accept: application/json"
```

```bash
curl -X GET "{{APP_URL}}/api/sa/catalog-full?lang=en&country_code=LV&include_inactive=false" \
  -H "X-Api-Key: {{SA_API_KEY}}" \
  -H "Accept: application/json"
```

```bash
curl -X POST "{{APP_URL}}/api/sa/leads" \
  -H "X-Api-Key: {{SA_API_KEY}}" \
  -H "Content-Type: application/json" \
  -d '{"idempotency_key":"create_lead:demo:1","source":"SA","lead":{"client":{"phone":"+37129999999"},"channel":"whatsapp"}}'
```

```bash
curl -X PATCH "{{APP_URL}}/api/sa/leads/16950" \
  -H "X-Api-Key: {{SA_API_KEY}}" \
  -H "Content-Type: application/json" \
  -d '{"idempotency_key":"lead_update:demo:1","source":"SA","update":{"stage":{"id":"pegging"}}}'
```
