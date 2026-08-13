# Таблица соответствий полей CRM и SA (Field Mapping)

В этом документе описано соответствие между полями в полезной нагрузке webhook'ов SA и таблицами/сощностями базовой CRM-системы.

## 1. Лиды и Заказы (Leads / Orders)
Сущность `lead` в SA напрямую сопоставляется с таблицей `orders` в CRM.

| Параметр SA | Поле CRM (`orders`) | Описание (Тип) |
| --- | --- | --- |
| `lead_id` | `id` | Первичный ключ заказа. Синхронизируется 1 к 1. |
| `client.phone` | `sa_client_phone` / `user.phone` | Номер телефона клиента. |
| `client.name` | `user.first_name` | Имя клиента. В `orders` пишется через связь с `user_id` или `checkoutParams`. |
| `fields.email` | `user.email` | Email клиента. |
| `pipeline.id` | - | В текущем MVP не хранится в отдельном поле БД, привязывается опосредованно. |
| `stage.id` | `status` | Совпадает с реальным `orders.status` (`watching`, `pegging`, `in_production`, `sended`, `send_lubanas`, `completed`). Старые `STG-*` больше не принимаются входным контрактом API. |
| `service_request.service_id` | `basket` | Трансформируется в корзину (`basket`) с 1 товаром/услугой при срабатывании `Orders::saveOrder`. |
| `service_request.notes` | `comment` | Комментарий к заказу. |
| `external_ids.conversation_id` | `sa_conversation_id` | ID текущего чата WhatsApp (переписка). |

## 2. Чаты и Сообщения (Conversations / Messages)
Архитектура поддерживает две параллельные ветки хранения: нативные таблицы SA (`sa_messages`, `sa_conversations`) и гибридную синхронизацию в CRM (`order_user_comments`).

| Параметр SA | Поле/Таблица CRM | Описание (Тип) |
| --- | --- | --- |
| `conversation_id` | `sa_conversations.conversation_id` | Идентификатор диалога. В `orders` также дублируется как `sa_conversation_id`. |
| `message.message_id` | `sa_messages.message_id` | Уникальный ID сообщения от провайдера SA. |
| `message.text` | `sa_messages.text` <br> `order_user_comments.comment` | Текст сообщения. Синхронизируется в карточку заказа для отображения в ленте. |
| `message.direction` | `sa_messages.direction` <br> `order_user_comments.is_admin` | Направление. `inbound` -> `is_admin = 0`. `outbound` -> `is_admin = 1`. |
| `message.status` | `sa_messages.status` | Статусы WhatsApp: `received`, `sent`, `delivered`, `read`, `failed`. |
| `message.attachments` | `sa_messages.attachments_json` | Файлы/вложения скачиваются физически в `storage/app/public/sa/` и пути пишутся в этот JSON. |
| `message.sent_at` | `order_user_comments.created_at` | Конвертируется в формат `Y-m-d H:i:s` (UTC/серверное время). |

## 3. Эскалации и Задачи (Escalations)
| Параметр SA | Поле/Таблица CRM | Описание (Тип) |
| --- | --- | --- |
| `escalation.priority` | `sa_escalations.priority` | Уровень эскалации (`normal`, `high`, `urgent`). |
| `escalation.reason_code`| `sa_escalations.reason_code` | Машинный код причины эскалации. |
| `escalation.dialog` | `sa_escalations.dialog_json` | JSON-слепок крайних сообщений для передачи оператору. |
| _Generated Task ID_ | `sa_escalations.task_ref` | Сгенерированный или привязанный идентификатор задачи менеджеру в CRM. |

## 4. Управление ботом (Bot Control)
| Параметр SA | Поле/Таблица CRM | Описание (Тип) |
| --- | --- | --- |
| `data.bot_mode` | `orders.sa_bot_mode` | Режим работы бота на текущий заказ: `active`, `paused`, `handoff_to_manager`. |
| `data.action` | `sa_bot_controls.action` | Выполненное администратором действие (`pause_bot`, `resume_bot` и т.д.). |
