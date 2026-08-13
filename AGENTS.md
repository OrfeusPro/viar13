# AGENTS.md

## Цель
Правила для работы AI-агента в проекте `viar` (Laravel 6 + Voyager).

## Базовые принципы
1. Не ломать существующую бизнес-логику `orders` и текущие админ-чаты.
2. Делать изменения итеративно: сначала MVP, затем расширения.
3. Любые интеграционные endpoint'ы покрывать валидацией, логами и идемпотентностью.
4. Не удалять и не откатывать чужие изменения без явного запроса.

## Текущий контекст по задаче CRM <-> SA
1. `lead_id` из ТЗ = `orders.id`.
2. Auth для интеграции: `X-Api-Key`.
3. Неизвестный `lead_id`: создавать временную сущность.
4. Вложения: физически копировать и хранить локально.
5. Мультиязычность каталога услуг обязательна: `ru|uk|en`.
6. Основной план и открытые вопросы ведутся в:
   - `docs/crm_sa_implementation_plan.md`

## Где вносить код
1. API-роуты: `routes/api.php` (или отдельный файл, подключенный из `RouteServiceProvider`).
2. Контроллеры интеграции: `app/Http/Controllers/Api/...`.
3. Middleware (auth/signature/idempotency): `app/Http/Middleware/...`.
4. Миграции и БД-схема: `database/migrations/...`.
5. Eloquent-модели интеграции: `app/Models/...`.
6. UI в админке/Voyager: существующие `resources/views/admin/...` и соответствующие контроллеры.

## Требования к интеграционным endpoint'ам
1. Формат: JSON, UTF-8, даты в ISO-8601 UTC.
2. Ошибки: единый JSON-формат (`status=error`, `code`, `message`, `details`).
3. Идемпотентность:
   - хранить `event_id` и `idempotency_key`;
   - повтор события возвращает `200` + `{"status":"duplicate"}`.
4. Логирование:
   - фиксировать отклоненные запросы и ошибки обработки;
   - не логировать секреты (`X-Api-Key`, токены).

## Качество и проверки
1. На каждый новый endpoint: минимум один feature-тест (успех + валидация + duplicate).
2. Перед сдачей:
   - запустить релевантные тесты;
   - проверить формат ответов и коды HTTP;
   - кратко задокументировать изменения в `docs/crm_sa_implementation_plan.md`.

## Ограничения безопасности
1. Секреты хранить только в `.env`, не хардкодить.
2. Для входящих webhook'ов использовать allowlist IP (когда список IP согласован).
3. Для вложений ограничивать MIME/размер и сохранять в `storage/app/public/...`.

## Формат работы с пользователем
1. Отвечать кратко и по делу.
2. Если есть блокер или неоднозначность, фиксировать вопрос в плане и сразу предлагать рабочий fallback.
3. Для крупных задач сначала обновлять план, потом реализацию.

## Ведение документации (обязательно)
1. Документация ведется постоянно, не только в конце задачи.
2. Основной файл по CRM<->SA: `docs/crm_sa_implementation_plan.md`.
3. После каждого заметного шага обновлять:
   - текущий статус этапа;
   - что реализовано;
   - что в работе;
   - открытые вопросы/риски;
   - следующее действие.
4. Для изменений API фиксировать:
   - endpoint, метод, назначение;
   - формат request/response;
   - коды ошибок;
   - правила идемпотентности и auth.
5. Для изменений БД фиксировать:
   - какие таблицы/поля добавлены или изменены;
   - зачем это нужно;
   - обратную совместимость/миграцию данных.
6. При закрытии этапа добавлять короткий changelog за этап (что сделано и что осталось).

## Definition of Done
Задача считается завершенной, если выполнены все пункты ниже:

1. Код:
   - реализованы все согласованные endpoint'ы/изменения по текущему этапу;
   - добавлены миграции и модели (если требуются);
   - нет явных регрессий в существующих сценариях `orders` и админ-чата.
2. Контракты API:
   - запросы/ответы соответствуют ТЗ;
   - коды HTTP корректны;
   - обработка duplicate работает по `event_id`/`idempotency_key`.
3. Безопасность:
   - включена проверка `X-Api-Key`;
   - секреты не утекли в код/логи;
   - для вложений действуют ограничения по типам/размеру.
4. Логирование и наблюдаемость:
   - ошибки и отклоненные webhook'и логируются;
   - есть возможность отследить обработку события по `event_id`.
5. Тесты:
   - добавлены/обновлены автоматические тесты для нового функционала;
   - тесты выполнены локально (или зафиксировано, что именно не удалось запустить).
6. Документация:
   - обновлен `docs/crm_sa_implementation_plan.md` (статус, что сделано, что дальше);
   - открытые вопросы явно перечислены.
7. Сдача этапа:
   - подготовлен короткий итог: что реализовано, что осталось, какие риски;
   - при наличии следующих шагов они оформлены списком.

## Operational Memory (Do Not Lose Context)
1. Keep source-of-truth notes for CRM<->SA in:
   - `docs/crm_sa_implementation_plan.md` (main running log)
   - `docs/crm-sa/09_real_sources_audit.md` (real DB/code sources)
   - `docs/upgrade-laravel13-filament5/64-migration-task-control-center.md` (migration status, active/next task and cross-branch resume point)
   - `docs/upgrade-laravel13-filament5/65-migration-executable-task-catalog.md` (normalized full migration backlog and task dependencies)
   - `docs/upgrade-laravel13-filament5/66-full-document-task-traceability-audit.md` (coverage proof and legacy-ID/evidence classification)
   - `G:\OSPanel\home\viar_filament` (canonical Laravel 13 / Filament 5 Git working tree; read its `STAGE0.md` before target work)
2. Before replacing any static mapping/data, verify existing DB/code source first.
3. Current confirmed real sources (2026-03-02):
   - lead id: `orders.id`
   - client chat stream: `order_user_comments`
   - service catalog base: `newhome_services` + `translations`
   - status domain: `orders.status` (`new`, `watching`, `pegging`, `in_production`, `sended`, `send_lubanas`, `completed`)
4. `sa_service_catalog*` and `sa_stage_mappings` are NOT existing tables in current DB snapshot.
5. After each meaningful implementation step, update `docs/crm_sa_implementation_plan.md` with:
   - what changed,
   - what is still static,
   - next exact action.
6. Before starting or resuming any Laravel/Filament migration task in any branch:
   - read `64-migration-task-control-center.md` from the latest `main`;
   - verify the selected task, status and dependencies in `65-migration-executable-task-catalog.md`;
   - use `66-full-document-task-traceability-audit.md` when an older document, risk, gate or alternate task ID must be traced;
   - continue `ACTIVE_TASK` or explicitly claim `NEXT_TASK`;
   - update the task card, control center and running log after every meaningful step;
   - never mark a task `DONE` without an evidence link and verified Definition of Done.
7. Laravel 13 / Filament 5 target location (2026-07-21):
   - canonical Git working tree: `G:\OSPanel\home\viar_filament`;
   - old `C:\OSPanel\domains\asoft\viar-next` was removed after verified relocation;
   - verified S0-02B archive remains in `C:\OSPanel\domains\asoft\viar-next-artifacts`.
