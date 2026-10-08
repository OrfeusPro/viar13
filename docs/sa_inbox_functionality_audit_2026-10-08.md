# ADM-AUD-03 — отдельные SA-диалоги: аудит кода

Дата: 2026-10-08. Кодовая сверка и исправления завершены; внешнее UAT остаётся открытым.

## Источники

- Оригинал: AdminSaIntegrationController, admin/sa_conversations_index, admin/partials/sa_conversations_table, admin/sa_conversation_show и глобальный badge Voyager master.
- Новая панель: SaConversationResource, ViewSaConversation, SaInboxService, OrderSaCommandService/OrderSaTransport, SaInboxBadge, SaChatAttachment.
- Интеграция: существующие createLead, входящие message.created/message.status и локальное сохранение вложений SaIntegrationController. Новых endpoints нет.

## Сценарии

| Функционал | Результат сверки |
| --- | --- |
| Отдельные диалоги без заказа и связанные диалоги | Один список/карточка; история выбирается по conversation_id, включая сообщения без orders_id. |
| Клиент/телефон/канал/режим/последнее сообщение/дата/заказ/количество | Присутствуют; восстановлен пропущенный статус заказа. Пустой текст отмечается явно. |
| Поиск и unread/unlinked/awaiting_reply/recent | Присутствуют, все scope проверены; последняя direction определяется по COALESCE(sent_at,created_at), затем ID. Дополнительно channel/bot_mode. |
| Polling списка, истории и глобальный unread | Реализован; badge и звук при увеличении count сохраняют логику оригинала. Реальное воспроизведение звука этим кодовым аудитом не проверялось. |
| Просмотр истории, отправитель, дата, статус, вложения | Сохранены клиент/бот/система/менеджер, безопасный вывод текста и компактные изображения; исправлен original_name/fallback basename. |
| Локальные вложения новых webhook | Исправлен URL: существующий файл с disk=public открывается на локальном public disk, отсутствующий импортированный файл остаётся на configured media base. Rejected/небезопасные пути не выдаются. Исходные файлы/БД не менялись. |
| Прочтение | Явная команда вместо автоматического read при просмотре/привязке/ответе. Token теперь содержит последний ID именно показанной истории; новое сообщение между чтением истории и выпуском token не подтверждается. |
| Привязка заказа | Права, блокировки, конфликт другой привязки, перенос сообщений/escalations/bot_controls, отсутствие дублей в order_user_comments. Привязка не обнуляет unread. |
| Создание заказа | Существующая createLead логика через внутренний бизнес-вызов; постоянный idempotency key, транзакция и bind. Повтор не создаёт второй заказ; invalid phone откатывает запись. |
| Ответ без заказа и с заказом | Transport адресует телефон выбранного диалога; linked история зеркалируется в order_user_comments после acceptance. Исправлена активность: accepted response обновляет last_message_at, сохраняя более поздний callback. |
| WhatsApp и другие каналы | Composer и модальные Reply/Bot видны только WhatsApp; прямые неподдерживаемые команды отклоняются сервисом. |
| Bot pause/resume/handoff | Состояние меняется только после acceptance; UAT не меняет боевой mode. Отправка без handoff сохраняет текущий режим, как ранее согласовано для новой панели. |
| Idempotency, timeout, partial, callbacks | Signed author/conversation/order/phone/mode/expiry token, fingerprint и unique event. При uncertain/partial composer сохраняет token/draft; повтор не вызывает transport снова. Callback статуса не откатывается до queued. |
| Права и отзыв доступа | Browse/read отдельно от edit; create требует add_orders. Прямые вызовы/query/history повторно проверяют права; подмена conversation/автора/заказа не принимается. |
| SA Simulator и служебные инструменты | Отдельный backlog ADM-AUD-11, не функция inbox. Ссылка оригинала не означает перенесённый симулятор. |

## Исправления и проверки

- Изменены Resource/View, readToken/view snapshot, manager activity update и attachment URL/name. Schema, API-контракты, X-Api-Key и конфигурация внешней отправки не менялись.
- Добавлено 6 meaningful regression tests: показанная история vs новое сообщение; activity и одновременный callback; статус/legacy attachments/channel; partial retry; local/imported attachment URL; все scope/channel/bot filters.
- `php -d memory_limit=512M vendor/bin/phpunit tests/Feature/Admin/SaInboxTest.php tests/Feature/Admin/OrderSaCommandTest.php tests/Feature/Admin/OrderSaChatTest.php --colors=never`: **67 tests / 587 assertions PASS**.
- PHP lint пяти изменённых PHP файлов, Blade view:cache и git diff --check PASS.
- Все тесты SQLite :memory:, fake HTTP/mail/storage; реальные клиенты, заказы, файлы и read flags не изменялись.
- Это проверка кода и изолированных сценариев, не подтверждение работы реального provider. Нужен отдельный тестовый контур для live send -> callback -> delivery status, bot command и production transport/config. Старые legacy API annotation tests остаются задачей MIG-TEST-SA-LEGACY.

## Следующее

ADM-AUD-04 — массовая Email Рассылка. Для SA отдельно остаётся согласованное внешнее UAT и проверка MySQL concurrency на тестовом контуре: SQLite проверки не моделируют конкурентные блокировки production.
