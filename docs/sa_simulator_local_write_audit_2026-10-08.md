# ADM-AUD-11 — локальная проверка записи, 2026-10-08

Статус проверки: DONE. Полная приёмка SA остаётся IN PROGRESS.

## Контур и воспроизведение

`tests/Fixtures/sa-simulator-local-run.php` создаёт отдельную MySQL схему на localhost,
копирует структуру и справочники, создаёт синтетические users/orders/chats/coupon.
Рабочая `viar_laravel13` только читается; существующие пользователи, заказы и чаты
не копируются. Изображения рабочей базы/диска не меняются. Созданная этим процессом
схема удаляется при завершении; промежуточные схемы этой проверки также удалены.

```powershell
& c:/dev/php/php8.3/php.exe -d memory_limit=1024M tests/Fixtures/sa-simulator-local-run.php
```

Payloads получаются выполнением настоящего JS buildPresets через Node, а не
ручной переписью примеров. Запуск идёт через SaSimulatorService, API key middleware
и настоящие API controllers. UI подтверждение/Livewire не входят в этот прогон.
Сессия SQL mode источника временно разрешает клонирование legacy zero-date defaults;
бизнес-запросы в тестовой схеме используют исходную конфигурацию strict.

HTTP заблокирован; Synvolve заменён fake; native HTTP/HTTPS streams принимают
только локальный PNG fixture. Mail/Queue/Storage public заменены fake. Шесть
уведомлений о заказе перехвачены Mail fake. Внешняя доставка не проверялась.
sa_commands_enabled включён только в памяти тестового процесса; .env/config
рабочего приложения не изменялись. API key тестовый, не реальный секрет.

## Результаты

23/23 write presets PASS (HTTP 200/201, бизнес-записи и повторная отправка):

| Группа | Сценарии |
|---|---|
| Заказы — 7 | new_lead, coupon, bonus, gift_card, family_constructor, HM-44 exact, HM-43 exact |
| Входящие — 5 | inbound_msg, preorder, existing_conversation, bot_message, inbound_media |
| Команды/события — 7 | bot_handoff, update_stage, message_status, send_message, send_message_preorder, pipeline_changed, escalation |
| Блог — 4 | author_create, post_create, media_upload, post_update |

Повтор 21 идемпотентного сценария возвращает duplicate без роста счётчиков;
PATCH статьи повторно возвращает ok и не создаёт новую статью. Multipart media
проверен через настоящий storeMedia с локальным UploadedFile; JSON-кнопка
симулятора по-прежнему не выполняет multipart загрузку.

Дополнительные проверки:

- 11 endpoint проверок валидации: HTTP 400/422, error; SHA256 снимки orders,
  users, events, messages, conversations, escalations, blog_posts/authors,
  translations не меняются.
- pause_bot/resume_bot сохраняют paused/active в orders и sa_conversations.
- Картинка сообщения имеет status=stored и существует в fake storage;
  медиа блога также физически существует в fake storage.
- Этап заказа pegging, статус сообщения delivered, handoff/paused режима бота
  и эскалация действительно сохранены.
- Купон: базовая стоимость 38 EUR, sale_price 28.00; бонусы: sale_price 0.00.
- PATCH статьи сохраняет изображение и русский заголовок в translations.

Машинный отчёт: `storage/app/testing/sa-simulator-local/report.json`.
Регрессия: Simulator + Inbox + OrderSaCommand + OrderSaChat +
SaConversationBotControl + SaMessageIngressAtomicity: **86 tests / 795 assertions,
1 skipped** (legacy SaConversationBotControlTest не поднимает свою SQLite схему).
Simulator отдельно: **7 tests / 47 assertions PASS**, включая новую регрессию.

## Исправление

`POST /api/crm/webhooks/pipeline-changed`: data.bot_control.mode раньше только
возвращался в ответе. Теперь сохраняется через существующий persistBotControl
в orders, conversation и sa_bot_controls. Повтор event_id не создаёт вторую
запись bot control. Auth, request/response, схема и mappings не меняются.

## Найдено — ADM-SA-UPDATE-01 (TODO)

Дополнительный PATCH /api/sa/leads/1 с update.fields.email, tags_add и notes_append
возвращает updated=true, fields_updated/tags/notes_appended, но бизнес-колонки
заказа не меняются. Код обрабатывает эти значения только в ответе и payload
sa_events; применения в заказ/контакт/теги/заметки нет. Это существующий пробел
API, не регрессия переноса UI. Встроенный update_stage сохраняет этап правильно.

Следующий точный шаг: определить по реальным orders/users источникам хранение
каждого разрешённого поля, тегов и заметок; реализовать применение транзакционно
с receipt и persistence/duplicate/rollback tests. Не считать этот контракт DONE.
Live provider send/callback, конкурентный MySQL ingress, touch/toast/beep UAT
остаются отдельными открытыми проверками.
# Обновление: ADM-SA-UPDATE-01 закрыт

Пропуск fields/tags/notes, найденный исходным аудитом ниже, исправлен и проверен
повторным локальным MySQL прогоном. Persistence/duplicate/lookup и SQL
rollback/retry PASS. Контракт и результаты:
docs/sa_lead_update_persistence_2026-10-08.md. Нижняя секция TODO описывает
исходное состояние до исправления, а не текущий backlog.
