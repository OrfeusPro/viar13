# SA Simulator: Детальный план тестирования

Дата: `2026-03-03`  
URL: `https://viarcanvas.loc/admin/sa-simulator`

## 1. Цель

Покрыть проверками все возможности `sa-simulator`:
1. Корректность пресетов и payload.
2. Корректность маршрутизации методов (`GET/POST/PATCH`) в `simulatorTrigger`.
3. Проверка обязательных данных из БД (`simulatorDefaults`) и блокировок submit при их отсутствии.
4. Проверка API-контрактов, идемпотентности и фактических изменений в БД/CRM-UI.

## 2. Перечень функций simulator

### 2.1 UI/JS функции
1. `buildPresets()` - формирует 12 пресетов.
2. `loadPreset(key)` - подставляет endpoint и payload в форму.
3. Submit-обработчик формы:
   - проверяет обязательные defaults из БД;
   - вызывает `admin.sa.simulator.trigger`;
   - отображает ответ;
   - обновляет `idempotency_key` и `event_id` после отправки.

### 2.2 Backend функции (AdminSaIntegrationController)
1. `simulator()` - отдает страницу simulator + `simulatorDefaults`.
2. `resolveSimulatorDefaults()` - поднимает дефолты из БД:
   - `lead_id`, `conversation_id`, `message_id`,
   - `client_phone`, `client_name`,
   - `service_id`, `sample_size`.
3. `simulatorTrigger()` - проксирует запрос во внутренний API:
   - выбирает метод `GET` для `/sa/services-catalog` и `/sa/services/HM-*/(sizes|price-by-size)`,
   - выбирает `PATCH` для `/api/sa/leads/{lead_id}`,
   - иначе `POST`,
   - передает `X-Api-Key`.

## 3. Глобальные данные, необходимые для тестов

1. В `.env`:
   - `APP_URL` (валидный базовый URL),
   - `SA_API_KEY` (актуальный ключ для middleware).
2. В БД минимум одна связка:
   - `orders.id` (как `lead_id`),
   - `sa_conversations` (conversation + client phone/name),
   - `sa_messages.message_id` (для `message.status`),
   - `header_menu` (для `service_id`),
   - `canvas_header` (для `sample_size`).
3. Файл для медиа-проверки:
   - `public/storage/temp_test_image.jpg` (или рабочий URL вложения).

## 4. Матрица пресетов simulator (12/12)

## 4.1 Preset 1: `new_lead`
1. Endpoint: `POST /api/sa/leads`
2. Минимальные данные:
   - `idempotency_key`, `source=SA`, `lead.client.phone`, `lead.channel`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`, есть `data.lead_id`.
   - Создан реальный `orders` (если `lead_id` был новый).
4. Негатив:
   - убрать `lead.client.phone` -> `VALIDATION_ERROR`.
   - повтор с тем же `idempotency_key` -> `status=duplicate`.

## 4.2 Preset 2: `inbound_msg`
1. Endpoint: `POST /api/sa/webhooks/messages` (`message.created`, inbound).
2. Данные:
   - `event_id`, `idempotency_key`,
   - `lead_id`, `conversation_id`, `message.message_id`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`.
   - Запись в `sa_messages`.
   - Комментарий в `order_user_comments` (чат клиента).
4. Негатив:
   - невалидный `event_type` -> `VALIDATION_ERROR`.
   - повтор `event_id` -> `status=duplicate`.

## 4.3 Preset 3: `inbound_media`
1. Endpoint: `POST /api/sa/webhooks/messages` (`message.created`, attachment).
2. Данные:
   - как в Preset 2 + `attachments[0].url`.
3. Что проверяем:
   - Вложение скачано/сохранено локально.
   - В комментарии есть ссылка/упоминание вложения.
4. Негатив:
   - MIME/size вне ограничений -> контролируемая ошибка (или пропуск вложения с логом).
   - недоступный URL вложения -> ошибка обработки/лог, без падения endpoint.

## 4.4 Preset 4: `bot_handoff`
1. Endpoint: `POST /api/crm/webhooks/bot-control`
2. Данные:
   - `data.action` (`handoff_to_manager|pause_bot|resume_bot`),
   - `lead_id` или `conversation_id`,
   - `changed_by`;
   - если заказ еще не создан, используется `conversation_id`; если это обычный заказ сайта без SA-диалога, используется `lead_id`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`.
   - Обновление bot mode у заказа (`orders.sa_bot_mode`) или standalone диалога (`sa_conversations.bot_mode`).
   - Для lead-only сценария `conversation_id` может быть пустым, но `orders.sa_bot_mode` должен обновиться.
   - Запись в `sa_bot_controls`.
4. Негатив:
   - невалидный `action` -> `VALIDATION_ERROR`.
   - повтор idempotency -> `duplicate`.

## 4.5 Preset 5: `update_stage`
1. Endpoint: `PATCH /api/sa/leads/{lead_id}`
2. Данные:
   - `update.stage.id` (+ желательно `from.id`).
3. Что проверяем:
   - `HTTP 200`, `status=ok`.
   - Статус `orders.status` изменен по stage mapping.
4. Негатив:
   - запрещенный transition -> `409 STAGE_TRANSITION_NOT_ALLOWED`.
   - неизвестный `lead_id` -> `404 LEAD_NOT_FOUND`.

## 4.6 Preset 6: `message_status`
1. Endpoint: `POST /api/sa/webhooks/messages` (`message.status`).
2. Данные:
   - `message_id` существующего сообщения,
   - `status`, `status_at`.
3. Что проверяем:
   - `HTTP 200`, `result.status_updated=true`.
   - В `sa_messages` обновился статус/время статуса.
4. Негатив:
   - неизвестный `message_id` -> предсказуемый ответ (без падения).

## 4.7 Preset 7: `send_message`
1. Endpoint: `POST /api/crm/webhooks/send-message`
2. Данные:
   - `lead_id`, `conversation_id`, `client.phone`,
   - `message.text`, `manager.id`, `channel=whatsapp`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`.
   - Outbound запись в `sa_messages` и комментарий в CRM чате.
4. Негатив:
   - пустой `message.text` -> `VALIDATION_ERROR`.

## 4.8 Preset 8: `services_catalog`
1. Endpoint: `GET /api/sa/services-catalog`
2. Query:
   - `lang`, `include_inactive`, `country_code`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`.
   - Корректная локализация названий.
   - Корректные цены по стране.

## 4.9 Preset 9: `pipeline_changed`
1. Endpoint: `POST /api/crm/webhooks/pipeline-changed`
2. Данные:
   - `stage.from.id`, `stage.to.id`, `lead_id`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`.
   - Изменение `orders.status`.

## 4.10 Preset 10: `escalation`
1. Endpoint: `POST /api/sa/escalations`
2. Данные:
   - `escalation.lead_id`, `conversation_id`,
   - `priority`, `reason_code`,
   - `dialog.messages[]`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`, есть `task_id`.
   - Запись в `sa_escalations`.
   - Bot mode изменен, если передан `set_mode`.

## 4.11 Preset 11: `service_sizes`
1. Endpoint: `GET /api/sa/services/{service_id}/sizes`
2. Query:
   - `country_code`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`, непустой `data.sizes[]`.
   - Источник размеров соответствует типу услуги (`canvas/collage/modular/gallery`).

## 4.12 Preset 12: `service_price_by_size`
1. Endpoint: `GET /api/sa/services/{service_id}/price-by-size`
2. Query:
   - `size`, `country_code`.
3. Что проверяем:
   - `HTTP 200`, `status=ok`, `data.price.amount`.
   - Цена совпадает с размерной матрицей источника.
4. Негатив:
   - неизвестный размер -> `404`/контролируемая ошибка.

## 5. Проверки ограничений UI (real-data-only)

1. При пустом `lead_id|conversation_id|client_phone|client_name` submit блокируется для пресетов стадий/escalation/update; для сообщений достаточно `conversation_id`; для bot-control достаточно `lead_id` или `conversation_id`.
2. При пустом `message_id` submit блокируется для `message_status`.
3. При пустом `service_id` submit блокируется для `service_sizes|price_by_size`.
4. При пустом `sample_size` submit блокируется для `price-by-size`.

## 6. План выполнения (по шагам)

1. Подготовка окружения:
   - проверить `APP_URL`, `SA_API_KEY`,
   - проверить `simulatorDefaults` на странице.
2. Базовый smoke пресетов:
   - прогнать все 12 пресетов в happy-path.
3. Контрактные негативные проверки:
   - по 1-2 ошибки валидации/duplicate на каждый критичный endpoint.
4. Проверки БД/CRM side effects:
   - `orders`, `sa_messages`, `order_user_comments`, `sa_escalations`, `sa_bot_controls`.
5. E2E связки:
   - минимум один полный цикл на одном заказе: lead -> msg in/out -> stage -> completed.
6. Документирование результатов:
   - фиксировать `order_id`, payload, ответ, SQL-факт, итог `pass/fail`.

## 7. Формат отчета по каждому тесту

1. `Test ID`
2. `Preset/endpoint`
3. `Input payload/query`
4. `Expected result`
5. `Actual API response`
6. `DB/UI verification`
7. `Status (PASS/FAIL)`
8. `Notes / follow-up`

## 8. Критерий завершения

1. Все 12 пресетов прошли happy-path.
2. Для каждого критичного endpoint проверены duplicate и минимум 1 validation-case.
3. Все side effects подтверждены в БД.
4. Неразрешенные расхождения задокументированы с owner/next action.
