# План миграции viar13 на Laravel 13

Обновлено: 2026-08-31.

## Название задачи

**Миграция viar13 на Laravel 13 — запуск и стабилизация публичного фронтенда**

## Этап админки Filament 5

### ADM-FIL-003 — Чаты: приёмка прочтения и авторов

- [DONE: автоматизированный подэтап; полная приёмка IN PROGRESS] Проверить заполненные client image/general/orphan ветки,
  стабильность длинной истории, авторов и изоляцию прочтения.
- Проверить реальный SA ingress → stale snapshot → fresh read и повтор ingress
  в SQLite с fake внешних вызовов. Не выдавать последовательный SQLite-тест
  за многопроцессную проверку блокировок MySQL.
- Проверено 13 новых cases: изоляция general/painting/sketch/orphan/foreign,
  три закрытых статуса, 120 сообщений с одинаковым timestamp и адресное read,
  шесть вариантов автора, PDF/PSD/hidden, реальный SA webhook + stale/fresh/duplicate.
- Regression: 227 passed / 1432 assertions / 1 прежний skip (228 total).
  Только SQLite :memory: и fake внешних операций; общая БД не использовалась.
  Browser populated/mobile пока не принят. Следующий шаг — атомарность SA ниже.

### ADM-FIL-003 — SA: атомарность входящих сообщений и прочтения

- [TODO, риск по аудиту кода] `SaIntegrationController::persistMessagePayload`
  сначала вызывает `upsertSaConversation` (unread=1), затем отдельно сохраняет
  sa_messages и клиентское зеркало. Общей транзакции нет. Между этими записями
  read может сбросить unread, особенно при совпадении timestamp до секунды.
- Событие dedupe сейчас получает processed_at до persist (status=received); при ошибке записи
  повтор может получить duplicate без восстановленного сообщения. Учесть вместе
  с атомарностью, не ограничиваться только lock в `OrderSaChatService`.
- Следующее: единая транзакция для receipt/состояния/сообщения/зеркала и общий
  порядок блокировок с read; внешнюю загрузку вложений не держать под DB lock.
  Нужны rollback/retry/duplicate tests и изолированный concurrency-тест MySQL.
  В текущей приёмке проверены только последовательные ingress/read сценарии.

### ADM-FIL-003 — Чаты: внешний вид и поведение попапов

- [DONE: UI-подблок, 2026-08-31; полная parity чатов не принята] По запросу пользователя приблизить текущие Filament-попапы
  к Voyager: client/SA 1100px, admin/painter 600px, компактный shell, история
  и форма в одном контейнере, SA bot toolbar сверху, строка ответа снизу.
- Сохранить accessible close/focus, permissions, UAT, read snapshot, idempotency;
  не переносить legacy AJAX/GET mutations и raw HTML. Внутренний автор: имя+email.
- Восстановить явное прочтение кликом/клавиатурой по непрочитанному тексту
  client/painter, сохранив отдельную кнопку. Общий режим художника остаётся
  единственным (как закомментированные image options в оригинале).
- Проверки: screenshots всех 4 окон, SQLite/Livewire, browser только №18451,
  без записей/отправок. Полная image/read/SA mode-after-send parity отдельно.
- Evidence: 214 passed / 1100 assertions / 1 прежний skip (215 total).
  Парные screenshots Voyager/Filament в `storage/app/chat-popup-20260831`;
  SA draft сохраняется при polling, пустой painter send отклонён, DB hash/counts
  прежние. Скриншоты не включаются в git: содержат данные общей локальной БД.
- Следующее: ADM-FIL-003 — Чаты: приёмка прочтения и авторов. Populated image
  threads, длинная история, mobile и ingress/read race остаются в приёмке;
  отправка наружу не проверялась. К другим колонкам/модулям не переходить.

### ADM-FIL-003 — Чаты: автообновление SA и кликабельные ссылки

- [DONE: реализация, 2026-08-31; полная parity не принята] Обновлять открытый SA-чат каждые 5 секунд, как legacy,
  отдельным Livewire-компонентом без перерисовки всей таблицы заказов.
- Перечитывать новые сообщения, delivery status, bot mode и unread; polling
  не выполняет read/send/bot actions. Сохранять черновики вложенных composer.
- Вернуть HTTP(S) links в клиентском тексте, экранируя весь HTML и атрибуты;
  не разрешать javascript/data URLs. Текст и пути вложений в БД не менять.
- Проверки: SQLite/Livewire права/refresh/read/draft, XSS/linkify, regression;
  Chrome только №18451 без отправок. Полная populated browser-приёмка отдельно.
- Evidence: 213 passed / 1081 assertions / 1 прежний skip (214 total).
  Chrome: время 16:29:51 → 16:30:16 → 16:30:42, draft сохранён, затем очищен;
  counts/hash заказа неизменны. Снимок `storage/app/chat-poll-20260831`.
- Следующее: ADM-FIL-003 — Чаты: приёмка прочтения и авторов.
  Проверить race ingress/read, идентификацию внутреннего автора и права
  read-only; длинную историю/нагрузку и populated browser оставить в приёмке.

### ADM-FIL-003 — Чаты: ответ рядом с историей и доступные команды SA

- [DONE: UI-подблок, 2026-08-31; полная parity ещё IN PROGRESS] Встроенные composer для общего клиентского чата, каждого
  изображения, общего чата художника и каждого подтверждённого SA-диалога.
  История остаётся видна, отдельный выбор отсутствующих изображений не нужен.
- Прямые видимые Pause/Resume/Handoff с подтверждением; текст и Handoff рядом
  с историей. Существующие сервисы/permissions/CSRF/UAT/idempotency сохраняются.
- Обновлять историю после действия, не терять draft при ошибке. Без вложенных
  HTML forms и без автоматической отправки. Только №18451 для browser UAT,
  запись/внешние операции — изолированные SQLite/HTTP-fake тесты.
- Не менять в этом подблоке SA mode-after-send, polling, linkify, ingress/read:
  эти расхождения остаются отдельными задачами до полной приёмки колонки.
- Проверки: 196 passed / 939 assertions / 1 прежний skip; targeted Pint,
  Blade compile и diff-check. Browser №18451: три inline формы, ввод/очистка,
  пустая отправка отклонена, SA-команды видимы. Реальные bot/send не вызывались.
  Снимки: `storage/app/chat-inline-20260831`. Counts/hash заказа неизменны.
- Следующее: ADM-FIL-003 — Чаты: автообновление SA и кликабельные ссылки;
  затем populated browser-приёмка обновления истории и сохранения других drafts.

### ADM-FIL-003 — Сравнение всех чатов с оригиналом

- [DONE: аудит, 2026-08-31; parity НЕ принята] Browser audit четырёх чатов
  на №18451: open/form/cancel, без отправок и изменения прочтения.
  Отчёт: `docs/crm-sa/10_orders_chat_browser_comparison.md`; 13 локальных снимков.
  Код приложения не менялся. Колонка «Комментарии» остаётся IN PROGRESS.
- [DONE] Клиент/художник/SA: ответ перенесён внутрь истории в UI-подблоке выше;
  внутренний чат уже inline. Legacy hidden actions пока сохранены.
- [DONE] В видимом UI нет пустого select изображения: отдельная форма
  закреплена только за существующей картиной/наброском.
- [DONE: реализация] SA polling 5s в отдельном авторизованном Livewire history;
  новые сообщения/статусы проверены SQLite, таймер/draft — Chrome №18451.
  Сквозной внешний ingress и нагрузочная приёмка длинной истории ещё открыты.
- [DONE: реализация] Безопасные HTTP(S)-ссылки в клиентском сообщении,
  включая ветки изображений. XSS/escaping/query/Unicode — тесты; populated
  browser на пустой истории №18451 не выполнялся.
- [TODO] Явно согласовать отличие отправки SA без Handoff: legacy задаёт
  active, новый сервис сохраняет текущий режим; не считать это полной parity.
- [TODO] Внутренний чат: legacy показывает имя + email, новый email только
  при отсутствии имени; сверить идентификацию менеджеров на populated fixtures.
- [TODO] Внутренний чат: отдельно сверить read-only доступ (новый history
  вместе с формой требует update); не считать permissions parity проверенной.
- [DONE] Inline форма художника уточняет: запись в общий чат заказа,
  доступ художника зависит от назначения.
- [TODO] Полная populated-приёмка остаётся открытой: на тестовом №18451 нет
  client/admin/painter messages и artwork, только одно ранее созданное UAT SA.
  Не заполнять тестовыми сообщениями реальный клиентский кабинет без отдельного
  согласования безопасного сценария.

### ADM-FIL-003 — WhatsApp/SA: отправка и управление ботом

- [DONE: реализация и безопасный UAT; внешняя доставка не принималась]
  Ответ, pause/resume/handoff и опциональный handoff при ответе.
  Только подтверждённый WhatsApp-диалог этого заказа и его сохранённый телефон;
  никаких CONV-* или +0000000 fallback. Проверки только на тестовых заказах.
- Команда получает защищённый токен и UUID, запись в существующий sa_events
  служит журналом идемпотентности; повтор не отправляет сообщение заново.
  Сначала транзакционная подготовка, затем внешний вызов, отдельная фиксация
  результата. 2xx означает приём интеграцией, не доставку WhatsApp.
- UAT по умолчанию блокирует внешние вызовы: сообщения маркируются тестовыми,
  не зеркалируются клиенту; команды бота не изменяют реальный режим.
  В тестах enabled-ветка проверяется только fake/mock.
- Сохранить legacy client-chat mirror для принятой реальной отправки и журнал
  sa_bot_controls; синхронизировать bot mode conversation/order после принятия.
- Проверено: 20 новых SQLite/Livewire/HTTP-fake тестов / 161 assertions;
  admin/invoice/API regression — 188 passed / 870 assertions / 1 прежний skip.
  Browser UAT только на указанном пользователем заказе 18451: тестовый диалог
  ADM-FIL-UAT-18451 (row 39), ответ с Handoff и отдельный Resume сохранены как
  uat_suppressed. Order row и клиентский чат не изменены, bot mode = paused.
  Истёкший токен формы теперь даёт видимое уведомление, а не скрытую ошибку.
- [TODO до внешнего включения] Подтвердить delivery callback и upstream dedupe;
  uncertain/dispatching требуют сверки результата, автоматического resend нет.
- Далее: ADM-FIL-003 — Приёмка чатов и синхронизация прочтения. Закрыть
  атомарность ingress/read, затем завершить сверку колонки «Комментарии».
  ADM-FIL-003 целиком остаётся IN PROGRESS.

### ADM-FIL-003 — WhatsApp/SA-чат: история и прочтение

- [IN PROGRESS: код и автотесты готовы; история проверена на UAT-диалоге 18451,
  итоговая приёмка ingress/read и импортированных связей ещё открыта]
  Перенесены sender labels, sent_at, delivery status, вложения,
  bot mode и явное прочтение. Источник unread —
  `sa_conversations.unread_for_manager`, не `sa_messages.status`.
- [DONE] Восстановлено выполнение `CrmWebhooksSendMessageTest` и
  `SaWebhooksMessagesTest` под PHPUnit 12 на SQLite с fake/mock: 19 passed.
  Новые service/Livewire/media — 16 tests; совместный regression 168 passed /
  709 assertions / 1 прежний skip. Без изменений API и реальных отправок.
- [TODO] Аудит связности импортированных SA-данных: 38 conversations и 139
  messages; JOIN с существующими orders возвращает 0. Нельзя переносить ссылки
  на другие заказы автоматически. Созданный UAT-диалог 18451 позволяет проверить
  UI, но не подтверждает корректность связей исходных импортированных данных.
- [TODO] Перед итоговой приёмкой закрыть гонку ingress/read: legacy webhook
  обновляет conversation и message отдельными операциями. Новый snapshot
  защищает от уже сохранённых изменений, но не заменяет атомарный ingress.
- Ответы и pause/resume/handoff реализованы отдельным подблоком выше с транзакцией,
  идемпотентностью и UAT-изоляцией. Аудит обнаружил: legacy пишет `sent` до
  внешней доставки, подставляет `CONV-*`/`+0000000`, а send/handoff обновляет
  conversation, но не обязательно `orders.sa_bot_mode`. Сначала зафиксировать
  действующие контракты тестами; не выдавать локальное сохранение за доставку.
- Browser-записи допустимы только в тестовом диалоге указанного заказа 18451,
  без внешних отправок и клиентского зеркала. Не загружать
  файлы с production в локальное хранилище. ADM-FIL-003 остаётся IN PROGRESS.

### ADM-FIL-003 — Чат с художником: история, ответы и прочтение

- [DONE] Общий ответ и явное прочтение по `admin/order_chat` и
  `update_order_chat_ajax`; сохранить `orders_chats`, типы старых сообщений,
  независимые read flags. Добавить unread на кнопку в колонке.
- Запись с `edit_orders`, просмотр с `read_orders`. Email художнику и Synvolve
  webhook выключены отдельным UAT-флагом. SQLite fake/mock и browser open/cancel.
- В реальной `orders_chats` нет `order_painter_image_id`/`user_id`; Voyager
  composer отправляет только общие ответы. Не менять schema и не выдумывать
  image threads. [TODO] Отдельно сверить публичный painter image-specific
  handler, который обращается к отсутствующему image FK.
- Проверено: 15 новых тестов / 88 assertions; admin + invoice feature/unit
  regression — 133 passed / 461 assertions / 1 прежний skip. Browser: 18380
  empty/open/reply/cancel; 17946 existing history/unread, read flags неизменны.
- Далее: SA чат в этой же колонке, сначала PHPUnit 12 legacy API-тесты.
  ADM-FIL-003 целиком остаётся IN PROGRESS; внешняя доставка не принималась.

### Основной backlog

- Файлы заказов/картин читаются с production `https://viarcanvas.com`, как в
  Voyager (подтверждено пользователем 2026-08-31). Не создавать локальное зеркало
  файлов и не менять сохранённые DB paths; разрешение URL — в admin-слое.

- [x] [DONE] **ADM-FIL-000 — Инвентаризация и roadmap**: frontend зафиксирован
  как завершённый RC; legacy Voyager routes остаются выключенными; принят
  временный URL `/filament`; составлена матрица переноса ниже.
- [x] [DONE] **ADM-FIL-001 — Установка и базовая панель**: Filament 5.7.6 и
  Livewire 4 установлены, panel доступен на `/filament`, Voyager выключен.
- [x] [DONE] **ADM-FIL-002 — Авторизация и Voyager permissions**: panel access
  использует `browse_admin`, поддержаны основная и дополнительные роли.
- [~] [IN PROGRESS] **ADM-FIL-003 — Список и фильтры заказов**: после прямого
  сравнения с Voyager добавлены операционные вкладки, 13 строк на страницу,
  страна/канал, VR/payment request, получатель, исполнители, chat/unread и
  основные legacy-фильтры. Rotation/express-порядок воспроизведён тем же
  компаратором. Осталось перенести категории, итоговую сумму и остальные
  данные/действия восьми Voyager-групп по связанным ADM-FIL задачам.
  Панель переведена на полноширинный layout для операционной таблицы.
  В строке заказа дополнительно доступны способ доставки/оплаты, краткий состав
  товаров, предоплата, этикетки, комментарии и дедлайн художника; длинные
  вторичные данные управляются через переключатель колонок.
  Быстрые вкладки показывают live-счётчики; фильтры категории и менеджера
  используют те же справочники/роли, что Voyager, включая «Админ / без
  менеджера»; размер ищется по JSON-полям первых десяти позиций.
  ADM-FIL-003 нельзя закрывать и нельзя переходить к следующей задаче, пока
  восемь колонок Voyager не совпадут одновременно по выводу и функциям:
  - «Номер»: VR/накладная, фирма, счёт и payment requests;
  - «Оплата»: статус, предоплата, этикетки и печать;
  - «Пользователь»: контакты, язык, канал, категория, менеджер, статус клиента;
  - «Получатель»: контакты, адрес, доставка, Viber/WhatsApp/email;
  - «Товар»: все позиции, изображения, опции, цены, скидки и review request;
  - «Комментарии»: client/admin/SA/painter потоки и unread;
  - «Художник»: художник/печатник, изображения, дедлайны, paid/show flags;
  - «Заказ»: статусы/даты, желаемая доставка, клиент/история, create/edit/delete.
  Таблица уже зафиксирована в этом точном порядке без девятой колонки действий.
  Первый функциональный подблок «Номер» оформлен по Voyager: номер, select
  накладной, карточка «Заявка на оплату», PDF, фирма и последние payment
  requests. Изменение/замена/удаление VR00/BAW/VRR445/DS020 выполняется через
  permission и транзакционный сервис; создание payment request открывается
  отдельным Filament action. Генерация/подтверждение счёта и редактирование
  фирмы перенесены в этой же задаче. PDF-подблок остаётся частью ADM-FIL-003:
  визуальная совместимость legacy-шаблона с dompdf 3.x проверяется по PDF
  Voyager для заказа 18451.
  Источником parity для «Номер» зафиксированы legacy Blade
  `voyager/orders/id.blade.php`, `invoice.blade.php`,
  `payment_request.blade.php` и `partials/orders/styles.blade.php`; ширина
  первого блока — 180px (минимум 170px), а не расширенная карточка Filament.
  Подблок выполнен: legacy `generate_checkout`, `approve_user_checkout` и
  `update_order_firm` заменены внутри таблицы на авторизованные Filament actions
  через `OrderInvoiceService`; изменяющие GET routes этими actions не вызываются.
  Отдельным следующим аудитом требуется закрыть сами legacy GET endpoints после
  подтверждения отсутствия публичных потребителей.
  На период UAT внешняя отправка invoice email выключена по умолчанию через
  `ADMIN_INVOICE_EMAIL_ENABLED=false`; полный mailable рендерится в log-mailer.
  Подблок «Оплата» перенесён строго по `payment.blade.php`: дата заказа,
  статус, предоплата, логотип/кнопка Venipak, номера этикеток и печать находятся
  в одной вертикальной колонке. Создание и печать этикеток выполняются через
  авторизованные Filament actions и `OrderVenipakLabelService`; pickup-заказы
  нельзя ошибочно отправить в адресном режиме, ответы Venipak валидируются, а
  номер для печати должен принадлежать заказу. Внешние Venipak HTTP-запросы в
  тестах заменены `Http::fake`; payment email/webhook изолированы до завершения
  UAT. Включение внешних операций выполняется только отдельным шагом после
  приёмки. Следующий parity-подблок ADM-FIL-003 — колонка «Пользователь»; сама
  ADM-FIL-003 остаётся IN PROGRESS до совпадения всех восьми колонок.
  Для «Пользователь» источником зафиксированы `voyager/orders/user.blade.php`
  и соответствующий блок `orders.blade.php`: вертикально перенесены данные
  клиента, число заказов, язык пользователя/счёта, канал, категория, менеджер
  и статус клиента. Изменяемые язык PDF-счёта и статус клиента выполняются
  авторизованными Filament actions через транзакционный `OrderUserService`, без
  legacy AJAX. «Получатель» перенесён по `user_data.blade.php`: раздельные
  контакты заказчика/получателя, Viber/WhatsApp/email, адрес, дата/язык,
  изображение, способы доставки/оплаты и их иконки. Legacy email sender заменён
  одноадресным permission-aware action; во время UAT внешняя отправка отключена
  `ADMIN_RECIPIENT_EMAIL_ENABLED=false`. Следующий parity-подблок — «Товар».
  Для «Товар» источником parity являются `voyager/orders/items.blade.php` и
  `partials/all_basket_order_items.blade.php`. Подблок включает итоговый расчёт
  (исходная/акционная цена, скидки, экспресс и доставка), все coupon/bonus flags,
  произвольное содержимое заказа, позиции всех legacy-типов, активное и исходное
  изображения, фон/отступы, полный набор параметров товара, цену позиции,
  подарок и комментарий доставки. Действие «Запрос отзыва» переносится как
  отдельный permission-aware Filament action; реальная отправка во время UAT
  выключена по умолчанию через `ADMIN_REVIEW_REQUEST_ENABLED=false`.
  Подблок «Товар» перенесён и проверен на общей БД по заказу 18451: вывод
  использует тот же полный legacy partial, сохранены правила canvas preview и
  ограничение ширины/переноса длинного комментария. Browser-сверка с открытым
  Voyager подтвердила одинаковые данные, суммы и placeholder отсутствующего
  изображения. Следующий parity-подблок — «Комментарии»; ADM-FIL-003 остаётся
  IN PROGRESS.
  Для «Комментарии» источником parity зафиксированы
  `voyager/orders/comments.blade.php` и partials `admin/client_chat.blade.php`,
  `admin/admin_chat.blade.php`, `admin/sa_chat.blade.php`,
  `admin/order_chat.blade.php`. Перенос делится на четыре проверяемых действия:
  клиентский поток с эскизами/картинами и read state; внутренний поток админов;
  WhatsApp SA с unread/bot mode; поток художника с типами сообщений и read
  state. В самой ячейке должны остаться комментарии order/admin/painter,
  client/painter previews, четыре Voyager-подобные кнопки и их счётчики.
  Legacy forms/routes напрямую не переиспользуются: DB-only внутренний чат
  переносится первым, а действия с письмами, уведомлениями и SA API получают
  отдельные permission-aware сервисы, внешние операции и fake-тесты.
  Первый comments-подблок выполнен: ячейка повторяет порядок Voyager,
  показывает order/admin/painter и отдельные client/painter previews, четыре
  кнопки со счётчиками/unread; все четыре истории открываются в Filament modal.
  Внутренний admin chat уже поддерживает запись через транзакционный
  `OrderAdminChatService`. Painter/SA пока read-only: письма, webhooks и bot
  control требуют отдельных проверяемых подблоков.
  2026-08-31: DONE — подблок «Клиентский чат: сообщения, изображения и read
  state»: общий ответ и ответы к существующим эскизам/картинам, группировка
  истории по image ID, явное прочтение одного входящего сообщения. Проверяются
  `edit_orders`, принадлежность изображения заказу и ограничения завершённых
  image-потоков. Открытие истории ничего не меняет; email/Synvolve по умолчанию
  выключены через `ADMIN_CLIENT_CHAT_NOTIFICATIONS_ENABLED=false`. Сообщение
  всё равно видно в кабинете клиента, поэтому запись тестируется лишь на SQLite.
  22 новых service/Livewire теста, 101 assertions; admin/invoice baseline:
  66 tests / 257 assertions / 1 skip. Загрузка новых файлов и изменение статусов
  изображений этим подблоком не закрыты; хранилище не синхронизировалось.
  DONE (2026-08-31) — «Поиск по номеру и данные клиента»: searchable ID перенесён
  на видимый ViewColumn, поиск больше не зависит от скрытого TextColumn.
  В compatibility User восстановлен legacy getter `locale` из `settings.locale`;
  язык пользователя/счёта №18380 снова `ru`. Пустой client_status отображает
  первый справочный option, как Voyager («Новый»), без записи default в БД.
  Явные pdf_locale/client_status имеют приоритет. 9 новых тестов / 35 assertions;
  admin/invoice/auth/delay regression: 93 tests / 424 assertions / 1 skip.
  Browser UAT подтвердил поиск №18380 без ID-фильтра и отображение ru/Новый.
  DONE (2026-08-31) — «Изображения с production-хранилища»: пользователь
  подтвердил использование файлов с production, локальные копии не нужны из-за
  объёма. Источник legacy `admin/o_chat_img.blade.php` —
  `https://viarcanvas.com/{image}`. В клиентском чате восстановлены admin-only URL
  для оригиналов и small_image через `OrderMediaUrl`, без проверки локального
  файла и без изменения DB paths. PDF/PSD используют иконки, изображения — lazy
  loading. Адрес задаётся `ADMIN_ORDER_MEDIA_BASE_URL`, по умолчанию production.
  Не скачивать, не синхронизировать и не кэшировать эти файлы в репозитории/storage.
  Browser UAT №18380: три картины №7356–7358 загружены с production, ссылки на
  оригиналы корректны. Media suite: 17 tests / 41 assertions; admin/invoice:
  92 tests / 334 assertions / 1 skip. Следующий шаг — поток художника в колонке
  «Комментарии» и применение того же media-правила при переносе его файлов.
  TODO — перед SA-подблоком восстановить discovery legacy API-тестов:
  `CrmWebhooksSendMessageTest` и `SaWebhooksMessagesTest` используют `@test`,
  который PHPUnit 12 не распознаёт; сейчас эти два файла не выполняют тесты.
  Затем продолжить «Комментарии»: поток художника и SA; ADM-FIL-003 остаётся
  IN PROGRESS до приёмки всех восьми колонок.
- [x] [DONE] **ADM-FIL-004 — Карточка и редактирование заказа**: добавлены
  read-only секции основных данных, доставки и позиций; редактирование основных
  административных полей выполняется валидируемым транзакционным сервисом.
- [ ] [TODO] **ADM-FIL-005 — Позиции и файлы заказа**: продолжить после
  завершения parity-аудита ADM-FIL-003/004 по открытой Voyager админке.
- [ ] [TODO] **ADM-FIL-006 — Художники и печать**.
- [ ] [TODO] **ADM-FIL-007 — Чаты заказа**.
- [ ] [TODO] **ADM-FIL-008 — Оплата и платёжные ссылки**.
- [ ] [TODO] **ADM-FIL-009 — Доставка и Venipak**.
- [ ] [TODO] **ADM-FIL-010 — Создание заказа менеджером**.
- [ ] [TODO] **ADM-FIL-011 — Приёмка модуля заказов**.
- [ ] [TODO] **ADM-FIL-020 — CRM-SA inbox**.
- [ ] [TODO] **ADM-FIL-021 — Пользователи, роли и скидки**.
- [ ] [TODO] **ADM-FIL-022 — Контент и мультиязычные CRUD**.
- [ ] [TODO] **ADM-FIL-023 — SEO и ALT suggestions**.
- [ ] [TODO] **ADM-FIL-024 — Рассылки, купоны и инструменты**.
- [ ] [TODO] **ADM-FIL-025 — Dashboard и индикаторы**.
- [ ] [TODO] **ADM-FIL-030 — Переключение на `/admin`**.
- [ ] [TODO] **ADM-FIL-031 — Удаление зависимостей Voyager**.

### Матрица переноса legacy admin

| Legacy-контур | Целевой Filament-контур | Задача |
|---|---|---|
| Orders BREAD и кастомная карточка | Orders Resource + custom pages/actions | ADM-FIL-003—010 |
| `painter_orders`, `printing_orders` | Relation managers/actions заказа | ADM-FIL-006 |
| Три потока order chat | Order chat components | ADM-FIL-007 |
| Payment requests | Order relation/action | ADM-FIL-008 |
| Venipak admin API | Авторизованные order actions | ADM-FIL-009 |
| SA conversations | Custom inbox pages | ADM-FIL-020 |
| Users/roles/permissions | Resources + Laravel policies | ADM-FIL-002, ADM-FIL-021 |
| Voyager BREAD content | Приоритетные Filament resources | ADM-FIL-022 |
| SEO/ALT controllers | Custom pages and bulk actions | ADM-FIL-023 |
| Mail/coupons/cache/TinyMCE | Custom pages/actions | ADM-FIL-024 |

## Статус frontend release candidate

- [x] [DONE] Основной публичный frontend RC поднят на Laravel 13: страницы,
  каталог, загрузки, auth/account, корзина, checkout, письма и payment contracts
  проверены.
- [x] [DONE] Production runbook подготовлен в
  `docs/production-frontend-runbook.md`.
- [ ] [DEPLOYMENT] На реальном публичном сервере выполнить checklist runbook,
  установить `APP_DEBUG=false` и подтвердить production smoke.
- Оставшиеся TODO ниже считаются post-RC backlog и не отменяют готовность
  основного frontend-контура; их нельзя автоматически считать выполненными.

## Правила ведения

- `[ ]` — TODO;
- `[~]` — IN PROGRESS;
- `[x]` — DONE, результат подтверждён проверкой;
- `[!]` — BLOCKED, причина записана рядом;
- каждую найденную во время работ задачу добавлять в подходящий этап;
- выполненные изменения и evidence подробно записывать в
  `MIGRATION_PROGRESS.md`;
- frontend RC завершён; текущий этап — Filament 5, Voyager runtime выключен;
- `G:\OSPanel\home\viar_filament` не использовать и не изменять.

## Этап 1. Laravel 13 boot и изоляция админки

- [x] Обновить PHP/Composer runtime до Laravel 13.
- [x] Отключить Voyager admin routes и providers от публичного runtime.
- [x] Добавить минимальный frontend compatibility layer Voyager.
- [x] Восстановить `artisan`, package discovery и `route:list`.
- [x] Обеспечить компиляцию всех Blade-шаблонов.
- [x] Добавить базовый Laravel 13 frontend boot test.

## Этап 2. Основные публичные страницы

- [x] Проверить `/` и `/en` локальным HTTP smoke.
- [x] Проверить FAQ, About и Contacts в поддерживаемых локалях.
- [x] Определить фактические публичные маршруты каталога и проверить
  репрезентативные разделы/карточки.
- [x] Исправить ошибки compatibility layer, найденные в текущем frontend-срезе.
- [ ] Добавить автоматические smoke-тесты для стабильных route contracts.
- [x] Проверить ответы 404 и безопасную обработку неизвестной локали/slug.

## Этап 3. Публичная авторизация и кабинет

- [x] Убрать зависимость auth controllers от удалённых Laravel UI traits.
- [x] Проверить login/logout и защиту гостевых/авторизованных маршрутов.
- [x] Проверить доступность login/register/reset форм, guest route protection и
  валидацию пустых auth-запросов без обращения к БД.
- [x] Проверить успешный login/logout с тестовым пользователем.
- [x] Проверить успешную регистрацию с изоляцией отправки почты.
- [x] Проверить полный password reset token flow и password confirm.
- [x] Сохранить legacy-контракт: email verification routes отключены, так как
  старый `Auth::routes()` не включал verify, а User его не реализует.
- [x] Проверить клиентские страницы account, settings, empty orders и bonuses.
- [x] [DONE] Проверить кабинет с наполненными заказами и страницу оплаты заказа:
  реальный пользователь видит четыре заказа; `Pay now` открывает заказ `18449`
  с корректной суммой `82 €` и пятью online-методами; Laravel/browser ошибок и
  битых изображений в проверенном пути нет, платёж не запускался.
- [ ] [TODO] Уточнить допустимость оплаты завершённого, но формально
  `not_payed` заказа: список скрывает `Pay now` при `status=completed`, однако
  прямые GET/POST payment routes сейчас проверяют владельца и `payment_status`,
  но не запрещают `completed`; без согласования бизнес-логику не менять.
- [~] Проверить отдельные painter-сценарии кабинета: browser UAT клиентской
  стороны чата/загрузок и feature smoke пустого кабинета роли `painter`
  выполнены; сценарии painter с назначенным заказом и реальные POST-действия
  остаются отдельной проверкой с тестовыми данными.

## Этап 4. Формы и пользовательские сценарии

- [x] Создать первичную инвентаризацию публичных POST/AJAX endpoints в
  `docs/frontend-mutating-routes-audit.md`.
- [ ] Проверить CSRF, валидацию и единый вывод ошибок.
- [~] Проверить формы контактов/заявок: review, photo/portrait/all-styles и
  frontend подарочной карты проверены; промо-формы акций доступны, но их
  реальные отправки писем/изменения купонов не выполнялись и остаются в
  backlog.
- [x] Добавить серверную валидацию формы отзыва, файлов и base64-аудио.
- [x] Добавить общий validation contract для быстрых portrait/all-styles
  заявок: email, phone, количество и MIME файлов; размер печатного фото на
  уровне Laravel не ограничивать.
- [x] Сохранить совместимость загрузок быстрых заявок с обоими frontend-
  форматами: `file[]` и legacy `file`, `file2` ... `file10`.
- [x] Убрать Laravel size limits для исходников печати: быстрые заявки,
  canvas `userImage` и клиентские файлы художнику.
- [ ] Проверить загрузку публичных файлов и изображений.
- [DONE] Исправить HTTP 500 на локализованной HTML-карте сайта
  `/de/sitemap`: подкатегории передаются в Blade как Eloquent-модели;
  проверены все включённые публичные локали.

## Этап 5. Каталог, корзина и checkout

- [x] [DONE] Проверить категории, услуги, фильтры и мультиязычные данные:
  photo/module/reproduction и category routes работают; валидные фильтры
  применяются, повреждённые игнорируются без `500`; локали `ru/en/lv/lt/de/ee`
  отдают локализованные данные, `/pl/*` штатно перенаправляется на `viar-art.pl`.
- [x] [DONE] Исправить ссылки верхних gallery-слайдов: известные внутренние
  `photo|module|reproduction` больше не уводят на `viarcanvas.com` и не теряют
  текущую локаль; прочие настраиваемые ссылки остаются без изменений.
- [x] [DONE] Проверить корзину и сохранение состояния: через реальный UI
  проверены три типа товаров, AJAX-пересчёт количества, reload, удаление и
  переход к checkout; ошибок Laravel не обнаружено.
- [x] Защитить удаление позиции от отсутствующего/некорректного `basketId`.
- [x] Ограничить количество позиции диапазоном 1--99 и проверять индекс.
- [x] [DONE] Проверить создание заказа через полный реальный checkout без
  изменения бизнес-логики `orders`: reproduction `item/35` за `50 €`, курьер
  `5 €`, банковский перевод. Создан заказ `18450`, открыта страница «Спасибо»,
  клиентское и административное письма отправлены; итог в БД — `55 €`.
- [x] [NOT REQUIRED] Ошибку локального окружения
  `View [pages.index.footer] not found` не воспроизводить и не исправлять:
  отдельные View могут временно не загружаться из-за локального сервера;
  по решению пользователя это не дефект frontend и не блокер миграции.
- [ ] Проверить checkout и payment callbacks в sandbox.
- [ ] Запланировать замену abandoned `paypal/paypal-checkout-sdk`.

## Этап 6. Frontend assets и визуальная совместимость

- [x] [NOT REQUIRED] Node/Mix build не запускать: активный frontend использует
  готовые статические CSS/JS и не зависит от сборки для запуска.
- [x] [NOT REQUIRED] Frontend build dependencies не обновлять и не исправлять,
  пока они не участвуют в активном статическом frontend runtime.
- [x] [NOT REQUIRED] Отдельное визуальное сравнение не выполнять: дизайн и
  frontend-разметка в рамках миграции не менялись.
- [ ] Проверять console errors и отсутствующие статические assets только в ходе
  функционального UAT затронутых frontend-сценариев.

## Этап 7. Регрессия и готовность к UAT

- [x] [DONE] Устранить оставшиеся Composer/autoload warnings: регистр файлов
  `Sale_30_40_new.php` и `Paymentinfo.php` приведён к именам классов; повторный
  optimized autoload создаётся без PSR-4 предупреждений.
- [x] [DONE] Сформировать стабильный frontend regression suite: публичный
  frontend/checkout/auth/account/payment набор — `103 passed / 570 assertions`.
- [x] [DONE] Выполнить полный доступный набор тестов и классифицировать legacy
  contracts: финальный прогон 2026-08-27 — `180 passed / 1226 assertions`,
  `5 skipped`, `0 failed`. Один skip относится к отключённым admin routes,
  четыре — к отсутствующим в текущем откатанном Synvolve service методам.
- [x] [DONE] Выполнить frontend UAT без переключения production/test: пройдены
  gallery/canvas/cart/auth/account/checkout и реальное создание заказа `18450`.
- [x] [DONE] Зафиксировать известные ограничения и критерии следующего этапа:
  внешний Synvolve `503`, отключённая admin-панель, отложенный аудит цен и
  mutating GET/ANY routes остаются отдельными задачами.

## Отдельный будущий этап

- [ ] Спроектировать и реализовать новую админку на Filament 5 после приёмки
  публичного frontend.

## Найденные задачи

- [x] Восстановить Voyager-compatible связь `User::role()` для публичного
  меню личного кабинета.
- [x] Добавить безопасные значения frontend tracker variables для
  test/CLI-контекста общего layout.
- [x] Убрать глобальную функцию `isActiveRoute()` из account Blade, чтобы
  повторный рендер не завершался `Cannot redeclare`.
- [ ] Убрать изменение `inv_sale_code` из GET-страниц кабинета или оформить
  его отдельным идемпотентным действием после проверки legacy-поведения.
- [x] Восстановить frontend-helper `str_trans()` для локализованных строк
  Voyager Extension (`{{en}}...` и `[[en]]...`).
- [x] Защитить compatibility translator от пустых имён переводимых атрибутов,
  найденных в legacy-модели `GalleryPage`.
- [x] [DONE] Исправить PSR-4/autoload предупреждения для `Sale_30_40_new` и
  `Paymentinfo` case-only переименованием файлов без изменения классов.
- [ ] Решить судьбу legacy public endpoint `/admin/check-user`, который сейчас
  остаётся доступен вне отключённой Voyager route group.
- [x] Проверить совместимость email verification с фактической моделью User.
- [x] Передавать обычную строку в `Hash::check()` из
  `ConfirmPasswordController` под Laravel 13.
- [x] [DONE] Классифицировать legacy-тесты, зависящие от отсутствующей
  SQLite-схемы и устаревшего контракта `SynvolveWebhookService`: добавлена
  изолированная `users/orders` схема для действующего order-phone контракта;
  четыре теста отсутствующих conversation/lead-update методов явно skipped.
  Production Synvolve logic не восстанавливалась и не изменялась.
- [ ] Провести отдельный аудит публичных служебных и изменяющих состояние
  GET-маршрутов (`mail/*`, генераторы, checkout helpers, admin controllers).
- [ ] Проверить controller-level authorization для 20 `orders/*` POST routes,
  у которых нет явного route-level `auth` middleware.
- [x] Устранить рассинхронизацию guest registration: frontend требовал
  отсутствующее CAPTCHA-поле, а backend выполнял обязательный внешний запрос.
- [ ] Спроектировать и подключить рабочую CAPTCHA/Turnstile для регистрации;
  до этого endpoint защищён строгой валидацией и rate limit.
- [ ] Убрать изменяющие состояние GET/ANY basket routes после проверки
  frontend-вызовов: `submitbonuses`, `clear_coupon`, `coupon_use`, gift card.
- [ ] Добавить validation contracts для каждой группы `basket/add/*` payloads
  перед success-path тестами загрузок и session state.
- [x] Подключить существующий `BasketStoreRequest` к `/basket/add` и вернуть
  JSON 422 для неверного canvas payload.
- [x] Добавить validation contract portrait uploads без size limit.
- [x] Исправить modular payload mismatch: frontend отправляет файл `image`,
  repository проверяет и читает `activeImage`.
- [ ] [REVIEW] Не внедрять универсальный серверный пересчёт цены без отдельного
  аудита каждого JS-конструктора: разные формы передают собственные параметры
  и используют разные расчёты. До согласования источника цены для каждого типа
  товара существующую бизнес-логику не менять.
- [x] Создать матрицу восьми basket add-endpoints и известных JS/Blade payload
  families в `docs/basket-add-payload-audit.md`.
- [x] Классифицировать reachability legacy `graph_portrait.blade.php`: прямых
  render/include нет, а активные graphic portrait routes используют другой
  Blade и `/basket/add/portrait`; шаблон признан orphan.
- [x] Проверить legacy canvas и canvas-new inline FormData builders: оба
  находятся только в недостижимых top-level Blade, активный `/new/canvas`
  использует theme canvas и global `new_bot_scripts.js`.
- [x] Покрыть simple legacy и current wizard portrait payload families.
- [x] Исправить active graphic/oil crash-path: `pid=undefined` с
  `orig_images[]`, но без base64 `image` приводил к `strpos(null)`.
- [~] Добавить отдельные contracts для `add/inter`, `add/module`,
  `add/construct`, `add/future_art` и двух recommendation endpoints:
  active endpoints готовы; orphan inter/module явно retired с JSON 410.
- [x] Убрать frontend-лимит 20 MiB в future-art для печатных исходников.
- [x] Сузить basket/cart CSRF exceptions после проверки callers: активные AJAX
  передают `X-CSRF-TOKEN`, формы — `_token`; широкие basket/cart patterns
  удалены, оставлено независимое исключение TinyMCE upload.
- [x] Защитить три recommendation add-endpoints: серверная цена, session offer
  для gallery и обязательный базовый товар для canvas.
- [ ] Исправить recommendation item-card contract: текущий отдельный endpoint
  сохраняет базовый gallery-товар и теряет выбранные size/frame/options; до
  server-side pricing нельзя безопасно принимать их JS-цену.
- [ ] После frontend UAT физически удалить недостижимые реализации
  `addToBasketInterier()` и `addToBasketModule()`; публичные маршруты уже
  безопасно переведены на compatibility JSON 410.
- [ ] После frontend UAT физически удалить orphan-шаблоны `canvas.blade.php`,
  `canvas_new.blade.php`, `graph_portrait.blade.php` и их недостижимые partials
  после контрольного поиска динамических вызовов.
- [x] Исправить gallery item add: не отправлять `image=null`, очищать legacy
  sentinel-значения `undefined` и не падать на отсутствующем decoration.
- [x] Убрать runtime `env('THEME_RESOURCES')` из checkout views и защищать
  прямой переход на payment без сохранённого шага delivery.

## Текущая задача

- [x] [DONE] Выполнить финальный полный PHPUnit regression после
  painter/account authorization: начать с чистых logs, разобрать каждый
  failure по exception, отделить известные внешние Synvolve failures от новых
  frontend-регрессий и обновить итоговый evidence. Результат:
  `180 passed / 1226 assertions`, `5 skipped`, `0 failed`.
- [x] [NOT REQUIRED] Не изменять legacy maintenance GET
  `/new/set_all_painter_images` на текущем frontend-этапе по решению
  пользователя. Route/controller существуют и проходят route/lint-проверку,
  активных frontend callers нет; метод массово синхронизирует legacy поля всех
  заказов в `order_painter_images` и завершает выполнение через `dd("ок")`.
  На реальной БД не запускался из-за глобальной записи. Вернуться к переносу в
  CLI/job только при отдельной задаче миграции данных.
- [x] [DONE] Защитить account mutations с `chatId` и `imageId`:
  сверить активные JS payloads и роли, запретить изменение чужих сообщений и
  painter-изображений, сохранить текущие status/read contracts и покрыть
  success/forbidden/validation feature-тестами. `message_read` теперь сверяет
  заказ клиента/назначение painter, image status доступен только владельцу
  заказа и статусам `4|5`, а image-chat проверяет принадлежность изображения
  тому же заказу. Публичные роли закрыты от двух legacy admin read endpoints.
- [x] [DONE] Проверить painter-кабинет с назначенным синтетическим
  заказом: рендер списка, загрузку рисунка/эскиза и чат через изолированные
  feature-тесты с fake storage/mail, без изменения production БД и без
  реальных отправок. Назначенный заказ отображается, обе upload-формы и чат
  присутствуют, JSON-контракты picture/sketch и запись painter-chat проходят;
  сервис сохранения принял синтетический исходник 100 MiB. Laravel `max`
  удалён из обеих painter-загрузок.
- [x] [DONE] Защитить painter mutation endpoints от подмены `order_id`:
  `new/update_painter_order_images`, sketch и chat сейчас находятся под
  `auth`; controller теперь подтверждает роль `painter` и назначение заказа
  текущему пользователю в `painter_orders`. Тем же guard защищены два
  дополнительных chat endpoint, найденных в активном JS; клиентский вариант
  разрешён только владельцу заказа или назначенному художнику.
- [x] [DONE] Нормализовать ошибку painter upload: services при неверном MIME
  возвращают `RedirectResponse`, хотя AJAX-controller ожидает строку/null;
  теперь возвращается JSON 422, письма/файлы/статусы заказа не изменяются,
  а существующие формы показывают текст ошибки и снова включают кнопку.
- [x] [DONE] Проверить оставшиеся публичные формы и painter-сценарии
  кабинета: сначала составить точную матрицу достижимых frontend forms/routes,
  затем выполнить безопасный browser/feature UAT без отправки реальных заявок,
  писем или платежей без отдельного подтверждения. Матрица и границы проверки
  записаны в `docs/frontend-mutating-routes-audit.md`.
- [x] [DONE] Исправить порядок загрузки JS на `/new/gift-card`:
  `jcf*.js` и `gift-card.min.js` сейчас выполняются до общего jQuery и падают
  с `jQuery/$ is not defined`; перенести только подключение скриптов после
  базового frontend runtime, не меняя разметку, дизайн или basket-логику.
  Дополнительно подключена отсутствовавшая зависимость `matchHeight`; в
  browser UAT JCF-элементы инициализированы без внутренних JS errors.
- [x] [DONE] Исправить `500 View [pages.stocks.modals] not found` на
  `/new/settings` и страницах акций: runtime-выбор Blade theme брать из
  `config('theme.resource')`, а не из прямого `env()` внутри controller;
  также исправлен основной view `pages.stocks.stocks`. Обе страницы повторно
  открыты по HTTPS без Laravel 500.
- [x] [DONE] Создать системную ссылку Laravel `public/storage` →
  `storage/app/public`: к моменту выполнения прежняя физическая директория уже
  отсутствовала, целевое дерево было цело (`12980` файлов, `3.25 GB`); создана
  Windows Junction, запись/видимость/HTTPS и удаление test probe проверены.
- [x] [DONE] Подготовить production-инструкцию Laravel 13 frontend и закрыть
  frontend RC: environment, install/update, cache policy, storage, smoke-check,
  rollback и известные ограничения описаны в
  `docs/production-frontend-runbook.md`; post-RC backlog сохранён в плане.
- [x] [DONE] Выполнить итоговую проверку готовности публичного frontend к
  запуску: Laravel/PHP environment, Composer, caches, public storage и ключевые
  guest URL проверены. Дизайн, цены, basket semantics и admin не менялись.
- [x] [DONE] Устранить все duplicate route names, блокировавшие сборку route
  cache: уникализированы только внутренние имена четырёх более ранних routes;
  URL, HTTP-методы, controllers и основные `route(...)` контракты сохранены.
- [x] [DONE] Проверить публикацию новых файлов public disk: первоначально
  использовался fallback `GET /storage/{path}`; затем создана стандартная
  системная ссылка `public/storage` → `storage/app/public`. Новый runtime-probe
  получен Apache по HTTPS с `200` и точным содержимым, затем удалён.
- [x] [KNOWN LIMITATION] Не включать `route:cache`/полный `artisan optimize` в
  текущем deployment: cache сохраняет только неперефиксованные routes, поэтому
  `/lv/*` отвечает `404`. Рабочий fallback — config/events/routes без cache,
  Blade views можно кэшировать отдельно через `php artisan view:cache`.
- [ ] [DEPLOYMENT] Перед реальным публичным запуском установить
  `APP_DEBUG=false`: текущая локальная `.env` использует `APP_ENV=production`,
  но debug включён для миграционной диагностики.
- [x] [DONE] Провести финальную регрессию публичного frontend: полный suite и
  отдельные frontend/checkout/auth/account/payment проверки выполнены;
  frontend полностью зелёный, legacy failures классифицированы, Blade cache и
  Composer autoload проверены. Дизайн и бизнес-логика не менялись.
- [x] [DONE] Исправить реальный checkout-сбой `/cart/setuser`: новый
  пользователь сохраняется, но прямое регистрационное письмо при локальной
  SMTP TLS-ошибке обрывает AJAX до авторизации и шага доставки. Перевести этот
  вызов на существующую best-effort отправку и покрыть regression-тестом;
  вернуть `CheckoutInlineLoginTest` в реальный запуск PHPUnit 12.
- [x] [DONE] Перенести из `C:\OSPanel\domains\asoft\viar` коммит
  `dabb75f0`: заменить знак свёрнутого предложения с `+` на `%` и выровнять
  его по высоте в compact overlay; проверить Blade, diff и frontend assets.
- [x] [DONE] Досинхронизировать все server files commit `72af3357`,
  включая ALT/Voyager исходники и assets; сохранить admin routes отключёнными
  в Laravel 13 runtime и не потерять frontend compatibility helpers.
- [x] [DONE] Синхронизировать публичные изменения server commit
  `72af3357` из `C:\OSPanel\domains\asoft\viar`: перенести frontend-переводы,
  публичные assets и совместимые web-server настройки; admin/Voyager ALT-код
  классифицировать и отложить до отдельного Filament 5 этапа.
- [x] [DONE] Устранить cache-зависимость account feature-тестов:
  тестовая схема не создавала обязательные `header_menu`/`footer_menu`, поэтому
  результат ошибочно зависел от ранее прогретого cache.
- [x] Провести browser UAT основного публичного frontend-контура: desktop-сценарии
  gallery item → cart, Canvas → cart и delivery → payment пройдены; отдельный
  mobile-аудит не требуется, так как дизайн и frontend-разметка не менялись.

## Browser smoke

- [x] Проверить HTTP/DOM main, gallery, item card, canvas, portrait, basket,
  cart, login и register на `viar13.loc`.
- [x] Исправить найденные 500 legacy basket/cart layouts.
- [x] Устранить JS crash `InteriorGenerator` на module item card без canvas.
- [x] Пройти интерактивный gallery item → cart сценарий с проверкой сессии.
- [x] [DONE] Пройти интерактивный canvas → cart сценарий с реальным тестовым файлом:
  проверить фактический POST, ответ, Laravel log, JS console и session cart.
- [x] [DONE] Исправить найденные при Canvas UAT ошибки: перенаправлять HTTP на HTTPS,
  чтобы браузер не блокировал HTTPS-шрифты по CORS; не писать
  `Image3d`/загруженные файлы целиком в Laravel log; защитить `Canvas3D` mouse
  events после очистки редактора.
- [x] [DONE] Проверить checkout: валидная курьерская доставка сохранена,
  payment view открыт с корректной суммой `63 €`, заказ не создавался.
- [x] [DONE] Защитить `/cart/setdelivery`: покрыть все способы доставки условной
  серверной валидацией, не принимать стоимость доставки из браузера и
  показывать JSON 422 в активном frontend JS.
- [x] [DONE] Исправить AJAX-загрузку городов/пунктов Venipak: имена Blade view
  не получают лишнюю точку перед `cart`; поздний AJAX-response больше не
  переключает выбранную пользователем доставку и не очищает все `.js-active`.
- [x] [NOT REQUIRED] Повторную инициализацию Ahrefs Analytics оставить без
  изменений по решению пользователя; остальные счётчики также не менять.
- [x] [NOT REQUIRED] Отдельную визуальную проверку публичных страниц в mobile
  viewport исключить из текущего этапа: дизайн и frontend-разметка не менялись;
  проверять только функциональные ошибки в затронутых сценариях.
- [x] [DONE] Проверить все публичные способы оплаты без реального списания: frontend
  contract, создание заказа, redirect/form провайдера, ошибки и отмена;
  внешние production-запросы заменить тестовыми doubles.
- [x] [DONE] Убрать Paysera credentials из `PayseraController` в `.env`/config и
  заменить `dd()`/`exit()` контролируемыми redirect/error responses.
- [x] [NOT REQUIRED] Sandbox-транзакции Paysera/PayPal исключены из приёмки:
  sandbox недоступен; production redirect и ошибки проверяются без отправки
  платежа, реальная транзакция выполняется только штатным заказом после запуска.
- [x] [DONE] Проверять сумму, валюту, Paysera-метод и допустимый переход статуса
  во всех публичных Paysera callback: checkout и ссылки на доплату защищены,
  повтор успешного callback идемпотентен.
- [x] [DONE] Исправить найденные рядом payment-регрессии: callback ссылки на
  доплату больше не использует удалённые PHP-константы credentials, а
  `pay_cancel` никогда не меняет заказ на `payed`.
- [x] [DONE] Проверить подтверждение PayPal на том же уровне, что Paysera: принимать
  только capture со статусом `COMPLETED`, сверять сумму, EUR и `reference_id`,
  разрешать только допустимый переход статуса и исключить повторный webhook;
  применить к checkout и публичным ссылкам на доплату без реального списания.
- [x] [DONE] Не допускать `500` после сохранения заказа при недоступном
  SMTP: перевести mail config на Laravel 13, изолировать клиентское и
  административное письмо от checkout и покрыть отказ транспорта тестом.
- [x] [DONE] Сверить частично обработанный заказ `18448`: запись действительно
  создана до SMTP-ошибки со статусами `watching` / `not_payed`; повторно
  отправлять сохранённую checkout-сессию нельзя, чтобы не создать дубликат.
- [x] [DONE] После отключения локального TLS-перехвата вернуть
  `MAIL_MAILER=smtp` и проверить реальное SMTP-подключение и авторизацию без
  отключения проверки сертификата.
- [x] [DONE] Реальным checkout-заказом `18449` проверить доставку клиентского
  письма через SMTP с включённым `MAIL_VERIFY_PEER`: пользователь подтвердил
  получение и переход на страницу «Спасибо».
- [x] [DONE] Исправить административное письмо о новом заказе под Symfony
  Mailer: legacy-вызов `setBody(string, 'text/html')` падает с `TypeError`;
  повторное уведомление заказа `18449` принято реальным SMTP без исключения.
- [x] [DONE] Провести общий аудит механизмов отправки писем в публичном
  runtime (`Mail::send/to`, Mailable, Notification), заменить остальные
  несовместимые с Symfony Mailer вызовы и покрыть общий контракт тестом.
- [x] [DONE] Устранить предупреждения PHP 8.4, найденные реальной
  повторной отправкой письма заказа `18449`: объявить зависимости
  `OrdersController` и не передавать `null` в `strpos()` генератора PDF.
- [x] [DONE] Вернуть в PHPUnit 12 два почтовых regression-набора,
  которые незаметно давали `No tests found` из-за legacy `/** @test */`:
  password reset notification и overdue-order email.
- [ ] [TODO] Уточнить контракт PDF в административном уведомлении: legacy-код
  генерирует `/storage/pdf/{order}.pdf`, но не прикладывает файл и не добавляет
  ссылку в письмо; до подтверждения не менять историческое содержание письма.
- [DONE] Перенести локализованную генерацию PDF-счёта из исходного проекта,
  коммит `f2feb24d`: добавлены расчёт НДС/итогов, сумма прописью, электронное
  уведомление и переводы; код адаптирован к Laravel 13/PHPUnit 12.
- [ ] [BLOCKED] Восстановить приём Synvolve webhook `POST NewOrder`: ранее
  именованный и UUID URL отвечали `404` из-за неактивного workflow; при создании
  заказа `18450` текущий endpoint уже принят, но вернул `503 Database is not
  ready`. Требуется исправить доступность БД/workflow на стороне Synvolve;
  существующую логику отправки в приложении не менять.
- [ ] После frontend interaction UAT вернуться к mutating GET/ANY basket routes.

## Задачи, найденные при server sync `72af3357`

- [x] [DONE] Удалить blanket Apache-блокировку gallery query
  `color|order|size`: правило из production commit возвращает 403 для реальных
  параметров, которые читает `GalleryController::hb_type_render()`; штатные
  фильтры возвращают 200, bot-блокировка Semrush/PetalBot сохранена.
- [x] [DONE] Защитить gallery filter parser от повреждённых query:
  `size=broken` после снятия Apache-блока выявил `500 Undefined array key 1`;
  некорректные и массивные `size`/`color`/`order` теперь удаляются до модели и
  Blade, возвращают 200 без новых ошибок в Laravel log.
- [x] [DONE] Синхронизировать 10 admin-only файлов `72af3357` как
  неактивный source corpus; функциональный перенос ALT UI на Filament 5 всё
  равно остаётся отдельным будущим этапом.
- [ ] [TODO] На этапе Filament 5 функционально адаптировать перенесённый ALT UI:
  сейчас исходники, команды и assets синхронизированы, но Voyager admin routes
  по-прежнему не загружаются в Laravel 13 runtime.
