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
# Дополнение: поведение глобальной кнопки (2026-10-08)

Предыдущая сверка глобального badge не учитывала плавающее размещение и drag оригинала. После замечания пользователя сверены CSS/JS Voyager master: вертикальное перетаскивание mouse/touch, localStorage позиции, ограничение окна, предотвращение перехода после drag и toast при росте count.

Эти возможности перенесены в Filament. Сохраняются существующие права, poll10s и переключатель звука. Chrome подтвердил наличие на roles/SEO/inbox, drag/reload/обычный переход, сохранение звука и размещение в мобильном viewport375. Badge/revocation: 2 tests / 16 assertions PASS; Blade cache PASS. Screenshot storage/app/testing/sa-floating-button-2026-10-08.png. Физический touch и toast/beep при реальном входящем требуют UAT; live данные не менялись.
## DONE — ADM-AUD-03: исправление свободного drag SA (2026-10-08)
- Исправлена причина прерывания drag: нативное перетаскивание ссылки браузером. Добавлены draggable=false и dragstart.prevent. Убран повторный pointer capture/lostpointercapture; движение и завершение отслеживаются на window.
- Предыдущая проверка движения на 17 px была недостаточной: она подтверждала начало движения, но не свободное перетаскивание.
- Chrome после исправления: полный drag вверх 425 → 128 px (−297), вниз 128 → 300 px (+172), reload сохраняет 300 px, URL roles не меняется. Blade view:cache PASS. Screenshot storage/app/testing/sa-floating-drag-fixed-2026-10-08.png.
- БД/API/transport не менялись, новых статических данных нет. Осталось ранее отмеченное UAT реального входящего toast/beep и физический touch.
## ADM-AUD-03 — повторная проверка полноты SA (2026-10-08)
Статус всей задачи: IN PROGRESS; исправления ниже DONE. Ранее DONE относилось к отдельным блокам, не полной приёмке SA.
- Исправлены модальные Reply/Bot в inbox и чате заказа: при uncertain/partial/state_conflict/ошибке token форма остаётся открытой с прежним ключом и черновиком. Повтор не создаёт вторую внешнюю отправку. Добавлены 2 Livewire regression tests на uncertain retry обеих форм.
- В карточку возвращены дата последнего сообщения (fallback из истории, если last_message_at пуст), номер заказа у сообщения, явный текст пустого сообщения и подтверждение bot control, как в оригинале.
- Проверки: Inbox + OrderSaCommand + OrderSaChat 69 tests / 616 assertions PASS; PHP lint, Blade view:cache PASS. Chrome: UAT-card /sa-conversations/39, сведения и confirmation dialog проверены, подтверждение отменено. Screenshot storage/app/testing/sa-card-completeness-2026-10-08.png. Внешних отправок и изменений live сообщений/read flags нет.
- Read-only текущая БД: 39 conversations / 140 messages; 1 связь с существующим заказом (UAT), 4 orders_id с отсутствующими orders. Это состояние локального снимка, а не основание для исправления production данных. Связи не менялись.
- Runtime config sa_commands_enabled=false: настоящая отправка/bot отключены. Настройки не изменялись. SA Simulator не перенесён (ADM-AUD-11).
- Старый TODO атомарного ingress устарел: SaMessageIngressService уже обеспечивает receipt и business writes в транзакции, контроллер блокирует order/conversation. Остаётся MySQL concurrency UAT; mock/SQLite не подтверждают production provider.
- Осталось: (1) повторный read-only аудит связей после импорта базы прода; (2) согласованный тестовый live send/callback/delivery/bot и входящее с toast/beep; (3) touch на физическом телефоне; (4) отдельный перенос нужных сценариев SA Simulator. Следующий шаг — SA UAT на тестовом контакте с проверкой настройки и endpoint, до перехода к Email Рассылке.
## DONE — ADM-AUD-11: перенос SA Simulator (2026-10-08)
- Страница /filament/sa-simulator, меню и ссылки из списка/карточки SA. Все 34 legacy presets, editor, docs/cURL, response/validation details; defaults из существующего DB resolver. API key server-only, URL allowlist, повтор сохраняет idempotency keys, POST/PATCH с подтверждением.
- Исполнение через существующие API handlers + VerifyIntegrationApiKey, без loopback HTTP. Права перепроверяются; Blog writes требуют отдельные разрешения. Текущий выключатель внешних SA-команд сохранён, ADM-FIL-UAT outgoing блокируются всегда.
- SaSimulatorTest 6 tests / 38 assertions PASS. Совместный Simulator/Inbox/OrderSaCommand до дополнительного guard test 52 tests / 452 assertions PASS. Lint/view:cache PASS. Chrome GET catalog 200 и переключение presets/editor/docs, JS errors 0. Рабочие POST/PATCH не запускались.
- Evidence/ограничения: docs/sa_simulator_migration_2026-10-08.md; storage/app/testing/sa-simulator-2026-10-08.png. Multipart Blog media остаётся cURL, как в оригинале. Схема БД/API контракты не менялись, новых статических mappings нет; legacy sample payloads/docs сохранены.
- Следующее: согласованный SA live UAT на тестовом контакте (ingress/reply/callback/delivery/bot), звук/touch и связи после импорта production DB. ADM-AUD-03 целиком остаётся IN PROGRESS; реализация Simulator завершена.
