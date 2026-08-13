# CRM <-> SA API Spec

Этот раздел содержит детальные контракты по каждому endpoint из ТЗ.

## Структура

1. `00_common.md` - общие требования (auth, idempotency, ошибки, время, формат).
2. `01_sa_webhooks_messages.md` - `POST /api/sa/webhooks/messages`.
3. `02_crm_webhooks_send_message.md` - `POST /api/crm/webhooks/send-message`.
4. `03_sa_services_catalog.md` - `GET /api/sa/services-catalog`.
5. `04_crm_webhooks_pipeline_changed.md` - `POST /api/crm/webhooks/pipeline-changed`.
6. `05_sa_leads_create.md` - `POST /api/sa/leads`.
7. `06_sa_leads_update.md` - `PATCH /api/sa/leads/{lead_id}`.
8. `07_sa_escalations.md` - `POST /api/sa/escalations`.
9. `08_crm_webhooks_bot_control.md` - `POST /api/crm/webhooks/bot-control`.
10. `test-matrix.md` - матрица тест-кейсов для Feature/API тестов.
11. `14_sa_service_sizes.md` - `GET /api/sa/services/{service_id}/sizes` и `GET /api/sa/services/{service_id}/price-by-size`.
12. `15_release_handoff_prod.md` - post-deploy smoke checklist (5-7 команд) для production.
13. `21_api_commands_reference_ru.md` - единый рабочий справочник по всем командам (URL, метод, обязательные поля, defaults, примеры).
14. `22_release_handoff_prod_ru_2026-03-03.md` - актуальный post-deploy handoff (RU, 7 команд, ожидаемые ответы).
15. `24_sa_catalog_full.md` - `GET /api/sa/catalog-full` (полный каталог: категории, сервисы, размеры, цены, фото).

## Принцип ведения

1. Любое изменение контракта сначала обновляется в соответствующем файле endpoint.
2. После этого обновляется `docs/crm_sa_implementation_plan.md` (статус и влияние на этапы).
3. По каждому endpoint должны быть:
   - таблица полей (`обязательность`, `тип`, `пример`),
   - примеры request/response,
   - ошибки и edge-cases.
