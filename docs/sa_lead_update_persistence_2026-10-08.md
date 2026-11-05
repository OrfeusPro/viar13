# ADM-SA-UPDATE-01 — запись PATCH lead (DONE, 2026-10-08)

## Проверенные источники

Read-only DESCRIBE локальной viar_laravel13 подтвердил orders.delivery TEXT,
orders.admin_comment TEXT, orders.sa_client_phone, sa_conversation_id и status.
Отдельного хранилища тегов lead/order нет; найденные gallery_tag_* относятся
к каталогу. order_admin_comments — поток админ-чата с user attribution; внешние
заметки не выдаются за сообщения авторизованного администратора в этом потоке.

Реализация использует существующий JSON доставки и внутренний комментарий
заказа. Миграции/новые таблицы/перенос существующих строк не нужны.

## Контракт и хранение

PATCH `/api/sa/leads/{id}`, JSON UTF-8, прежняя авторизация `X-Api-Key`.
`source=SA`, обязательные `idempotency_key` и `update`; сохраняется совместимость
ответа `status=ok`, `data.fields_updated/tags/notes_appended/order_updated`.

| Вход | Место хранения / поведение |
|---|---|
| fields.email/city/address/postal_index/first_name/last_name/comment | Одноимённые ключи orders.delivery; остальные delivery keys сохраняются |
| fields.phone | Нормализованный телефон клиента: delivery.payer_phone, orders.sa_client_phone, client_phone связанного диалога; отдельный delivery.phone получателя сохраняется |
| Все fields, включая budget и другие дополнительные поля | orders.delivery.sa_lead.fields; merge по имени, null хранится как явное значение |
| tags_add / tags_remove | orders.delivery.sa_lead.tags; уникальные значения, удаление имеет приоритет, остальные теги сохраняются |
| notes_append | orders.delivery.sa_lead.notes с ISO-8601 UTC created_at; одновременно append `[SA timestamp] text` к orders.admin_comment для просмотра в админке |
| stage.id | orders.status, существующий домен стадий |

Поля действуют в рамках заказа. Глобальный users профиль и остальные заказы
клиента не изменяются. Дополнительные поля не выполняют mass assignment в
колонки orders: например budget не меняет price. GET orders/lookup возвращает
обновлённые контактные значения этого заказа с приоритетом над users, включая
явное очищение; дополнительные данные доступны в delivery.raw.sa_lead.

Валидация: fields — максимум 100 scalar/null значений, имя из букв/цифр/_
(начинается с буквы, максимум 100 символов), строковое значение до 10000 байт.
Email/phone и известные адресные поля проверяются отдельно. Tags — максимум
100 на операцию, непустые строки до 100 символов. Notes — максимум 100,
обязательный text до 10000 символов, дата валидируется. Итоговые delivery JSON
и admin_comment ограничены 60000 байт для совместимости с MySQL TEXT.
Некорректный существующий JSON не перезаписывается.

## Атомарность и ошибки

SaLeadUpdateService выполняет lockForUpdate заказа, merge, receipt sa_events,
обновление заказа/диалога и status=processed в одной транзакции. Используется
прежний dedupe namespace `sa:leads:update:key:` + SHA1 ключа — старые receipts
продолжают распознаваться. Уникальный dedupe_key защищает равные ключи;
duplicate возвращается только при наличии именно этого receipt. Cache fallback
для PATCH убран: при ошибке хранения запрос должен оставаться повторяемым.

- 200 ok — запись завершена; 200 duplicate — этот ключ уже зарегистрирован.
- 400 VALIDATION_ERROR — неверные значения/превышение размера; receipt не создан.
- 404 LEAD_NOT_FOUND — прежний PATCH контракт для неизвестного заказа; receipt
  не создан. Создание временных сущностей входящих сообщений не изменено.
- 401 — API key отсутствует/неверен (существующее middleware).
- 503 PERSISTENCE_ERROR — изменения и receipt откатились; повтор тем же ключом
  допустим. Логи содержат lead_id, тип исключения и hash ключа, без payload,
  секретов и текста контактов/заметок. Отклонения также логируются без payload.

Legacy receipt, созданный старой версией до её неудачной записи, невозможно
надёжно отличить от успешного исторического запроса. Такие рабочие receipts
автоматически не удаляются/не переигрываются; при необходимости требуется
отдельная сверка источника и новый ключ исправляющего запроса.

## Evidence

- Новые SaLeadUpdatePersistenceTest: 18 tests / 152 assertions PASS; вместе с
  Simulator: 25 tests / 199 assertions PASS. Persistence + lookup, merge/null,
  validation/retry, 4 места внедрения ошибки/rollback/retry, auth, unknown lead,
  corrupt JSON и legacy receipt проверены на изолированной SQLite.
- Общий релевантный запуск до последних дополнительных validation/legacy
  cases: 101 tests / 928 assertions, 1 legacy skipped (нет fixture schema).
- Повторный local MySQL harness: 23/23 write presets PASS, 11 validation probes,
  pause/resume; новый fields/tags/notes persistence + duplicate + lookup PASS.
  Намеренная ошибка после SQL UPDATE orders: HTTP 503, snapshots бизнес-таблиц
  прежние, повтор тем же ключом ok — PASS.
- Рабочая БД только читалась для схемы/справочников. Тестовая схема удалена;
  картинки/рабочие записи/.env не менялись. HTTP/Synvolve/Mail/Queue/Storage
  изолированы как в предыдущем прогоне. Report:
  storage/app/testing/sa-simulator-local/report.json.
- PHP lint PASS; внешняя доставка и конкурентные запросы из независимых MySQL
  процессов этим прогоном не подтверждаются. Общая SA приёмка IN PROGRESS.

Следующее действие: ранее открытые независимые SA UAT проверки; данный пропуск
записи закрыт. Новых статических catalog/status mappings не добавлено.
