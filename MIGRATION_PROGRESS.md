# Миграция на Laravel 13 — текущий статус

## ADM-FIL-003 — Чат с художником: история, ответы и прочтение (2026-08-31)

- DONE (подблок): сверены `admin/order_chat`, AJAX send/read и реальная схема
  `orders_chats`. Общий ответ перенесён в OrderPainterChatService и отдельные
  Filament actions. История сохраняет авторов, даты и flags набросок/картина;
  unread подсвечен в истории и на кнопке колонки. Opening не меняет прочтение.
- Запись требует `edit_orders`, просмотр — `read_orders`; read относится только
  к выбранному заказу, идемпотентен и не меняет painter `is_read`/timestamps.
  Нет новых таблиц, image FK или изменений frontend. Legacy разрешает общий
  ответ без назначения и на закрытом заказе — это поведение сохранено.
- Email использует текущее назначение, locale/перевод `admin_user_chat_title`,
  текст экранируется. Mail/webhook после сохранения, ошибка не теряет текст.
  `ADMIN_PAINTER_CHAT_NOTIFICATIONS_ENABLED=false` проверен в local runtime.
- 15 новых SQLite/service/Livewire тестов / 88 assertions; итоговый regression:
  `tests/Feature/Admin`, `tests/Feature/Invoice`, оба unit Invoice набора —
  **133 passed / 461 assertions / 1 skipped** (прежний disabled legacy route).
  Pint целевых файлов, `view:cache` и `git diff --check` прошли.
- Chrome: заказ 18380 — empty history, nested reply/open/cancel, предупреждение
  UAT; заказ 17946 — сообщение 2845, дата, unread badge и отдельная read-кнопка.
  После просмотра `is_read=1`, `admin_is_read=0`, updated_at прежний;
  у 18380 по-прежнему 0 painter messages. Реальных отправок/мутаций не было.
- Риск: публичный image-specific handler ссылается на отсутствующий image FK;
  отдельный TODO в плане. Далее — SA чат (пока read-only), восстановление
  обнаружения legacy API-тестов PHPUnit 12. ADM-FIL-003 остаётся IN PROGRESS.

## ADM-FIL-000 — Инвентаризация и roadmap (DONE, 2026-08-27)

- frontend принят как завершённый RC и остаётся regression baseline;
- единственная база реализации — `G:\OSPanel\home\viar13`;
- новая панель до приёмки работает на `/filament`, Voyager routes не включаются;
- legacy admin классифицирован по модулям; backlog записан в
  `MIGRATION_PLAN.md`;
- Filament в Composer пока отсутствует, compatibility-модель пользователя
  содержит только `role()` без permission relations;
- следующий точный шаг: **ADM-FIL-001 — Установка и базовая панель**.

## ADM-FIL-001 — Установка и базовая панель (DONE, 2026-08-27)

- установлены Filament `5.7.6` и Livewire `4.4.2`; Laravel обновлён в рамках
  совместимого `^13.20` с `13.25.0` до `13.29.0`;
- создан `AdminPanelProvider`, panel работает на `/filament`, локаль `ru`,
  опубликованы автономные Filament assets; frontend build не изменён;
- `/filament`, `/filament/login` зарегистрированы, Voyager routes отсутствуют;
- `FilamentPanelBootTest`: 3 passed / 5 assertions.
- после реального browser smoke исправлен конфликт общей frontend-сессии:
  Filament переведён на отдельный session guard `filament`, поэтому вошедший
  клиент больше не получает `403` вместо формы admin login;
- повторный browser smoke показывает форму «Войдите в свой аккаунт»; целевой
  admin-набор после исправления: 9 passed / 18 assertions.

## ADM-FIL-002 — Авторизация и Voyager permissions (DONE, 2026-08-27)

- добавлены first-party `Role` и `Permission` поверх существующих таблиц;
- `User` реализует Filament contracts, `browse_admin` проверяется для основной
  `role_id` и дополнительных ролей `user_roles`;
- добавлен `OrdersPolicy` для `browse/read/add/edit/delete_orders`;
- тесты panel + authorization: 6 passed / 10 assertions;
- browser smoke существующей сессией обычного пользователя подтвердил `403`.

## ADM-FIL-003 — Список и фильтры заказов (IN PROGRESS, 2026-08-28)

- 2026-08-31: DONE подблок «Изображения с production-хранилища». Пользователь
  подтвердил legacy-правило: изображения читаются с production, локальная
  синхронизация не нужна. В клиентском чате локальная exists-проверка заменена
  admin-only `OrderMediaUrl`: production base + сохранённый путь, существующие
  HTTP(S)-ссылки сохраняются, небезопасные schemes/traversal отклоняются.
  Превью использует small_image либо оригинал; PDF/PSD — локальные иконки,
  ссылки открывают production-файл. Добавлены lazy loading и placeholder при
  ошибке загрузки; отсутствие локального файла больше не считается ошибкой.
  PHP не делает HTTP probes/download/cache, DB paths и public frontend не менялись.
- Browser UAT через открытый Chrome: три изображения №7356–7358 заказа №18380
  загружены с `viarcanvas.com` (`complete=true`, `naturalWidth=5500`), оригиналы
  ведут на те же production paths. Чат открыт/закрыт без отправки/read mutation.
- `OrderMediaUrlTest`: 17 tests / 41 assertions, включая small_image, original,
  PDF/PSD, конфигурацию host, unsafe URLs и отсутствие HTTP-запросов из PHP.
  Admin/invoice regression: 92 tests / 334 assertions / 1 skip; Pint, Blade cache
  и `git diff --check` прошли. Следующий точный шаг ADM-FIL-003 — чат художника;
  production-media правило используется дальше без локального зеркалирования.

- 2026-08-31: DONE подблок «Поиск по номеру и данные клиента». Подтверждено
  чтением legacy `TCG/Voyager/Models/User.php`: `locale` хранится в settings,
  accessor отсутствует в compatibility-модели. У пользователя заказа №18380
  settings.locale=ru, pdf_locale=null, client_status=null. «Новый» в Voyager —
  первый option при пустом статусе, а не записанный статус. Общий поиск потерял
  ID после скрытия TextColumn. Добавлен `searchable(['id'])` видимой колонке
  «Номер», restored getter locale в compatibility User, display-only default
  статуса из первого элемента справочника; явные сохранённые значения остаются
  приоритетными. Реальные данные не менялись.
- `OrdersDisplayParityTest`: 9 tests / 35 assertions — Livewire поиск по номеру
  и части номера, очистка поиска, сохранение query scope, порядок восьми колонок,
  settings locale и отображение PDF/status без DB writes. Admin/invoice/auth/
  delay regression: 93 tests / 424 assertions / 1 skip; Blade cache и Pint passed.
- Дополнительно public auth/basket и Symfony mailer regression: 64 tests /
  808 assertions, все прошли. `git diff --check` прошёл. Публичные views/routes
  не менялись; восстановлено только прежнее чтение языка из settings.
- Browser UAT на открытой вкладке: №18380 найден общим поиском без ID-фильтра,
  пользователь/счёт показывают ru, статус — «Новый». Read-only повторная проверка
  БД подтвердила pdf_locale=null и client_status=null; никаких писем/сообщений.
- Media-аудит №7356–7358: пути сохранены как `orders/...jpg`, файлы отсутствуют
  в public и viar13, и legacy `C:/OSPanel/domains/asoft/viar`. Исходный чат
  строит remote URL через `https://viarcanvas.com/{image}`. Синхронизация и
  загрузка не выполнялись; следующий шаг ADM-FIL-003 — remote media resolution,
  затем оставшиеся функции колонок/чатов. Весь ADM-FIL-003 не закрыт.

- 2026-08-31: DONE подблок «Клиентский чат: сообщения, изображения и read state».
  Добавлены `OrderClientChatService` и три production Filament actions: история,
  ответ и явное прочтение одного сообщения. История разделена на общий поток,
  картины и наброски, показывает статусы из существующего справочника, даты,
  видимость, авторов и независимые client/admin read flags. Переписка удалённых
  изображений не теряется; HTML сообщений экранируется.
- Запись требует `edit_orders` и транзакции; чужое изображение, несоответствие
  типа, image ID в общем потоке, пустой/слишком длинный текст отклоняются.
  Для `completed/sended/send_lubanas` сохранён запрет ответа по изображению.
  Read state меняется только для выбранного входящего сообщения своего заказа,
  повтор безопасен, timestamps и клиентский `is_read` не изменяются.
- Email и Synvolve webhook отключены по умолчанию новым UAT-флагом; форма
  предупреждает, что сохранённое сообщение видно в клиентском кабинете даже
  без уведомлений. При включении используются существующие mailable/service;
  сбой webhook не откатывает сообщение. Тесты заменяют внешние вызовы fake/mock.
- `OrderClientChatTest`: 22 tests / 101 assertions, включая реальные Voyager
  permissions, Livewire вложенные reply/read actions, read-only историю,
  ownership, terminal statuses, HTML escaping и email/webhook on/off.
  Исправлены найденные тестами Blade-компиляция и регистрация вложенных modal
  actions. Admin/invoice regression: 66 tests / 257 assertions / 1 skip;
  `php artisan view:cache` и `git diff --check` прошли.
- Дополнительный запуск `CrmWebhooksSendMessageTest` и `SaWebhooksMessagesTest`
  не выполнил тестов: старые `@test` не распознаются PHPUnit 12. Эти файлы не
  изменялись; исправление discovery записано перед SA-подблоком. Это не зелёный
  CRM regression run и не включено в 66 выполненных admin/invoice тестов.
- Browser UAT: на №18451 открыты история, общий ответ и отмена с возвратом
  в историю; на №18380 сверены два клиентских сообщения/счётчики с Voyager.
  Реальные сообщения, письма, webhooks и изменения прочтения не выполнялись.
  Загрузка новых изображений/смена их статуса и синхронизация файлов не входят
  в закрытый подблок. Painter/SA остаются read-only.
- Найдена регрессия общего поиска №18380 (фильтр ID работает); задача добавлена
  в `MIGRATION_PLAN.md`. Следующий точный шаг внутри ADM-FIL-003 — восстановить
  поиск по номеру, затем продолжить painter/SA-потоки и оставшиеся колонки.
- На №18380 новая история показала картины №7356–7358, два входящих сообщения
  в соответствующих image-потоках и translated statuses. Форма ответа выбирает
  картину №7357; отправка отменена. Локальные изображения отсутствуют: добавлена
  явная подпись вместо ошибочного «Открыть файл», ведущего на placeholder.
  Найдено расхождение языка/статуса клиента в колонке «Пользователь» (ru/Новый
  против lv/пусто), повторный аудит источников добавлен в план; БД не менялась.

- 2026-08-28: начат функциональный parity-подблок колонки «Оплата» по
  `voyager/orders/payment.blade.php`: зафиксированы дата заказа, три статуса,
  отдельная сумма предоплаты, создание и печать Venipak-этикеток;
- добавлен транзакционный `OrderPaymentService` для статуса и суммы предоплаты;
  сохранены legacy-бонусы клиента и пригласившего пользователя, а также
  генерация купонов/PDF подарочных карт при переводе заказа в «Оплачено»;
- на период UAT payment email и Synvolve webhook отключены по умолчанию через
  `ADMIN_PAYMENT_NOTIFICATIONS_ENABLED=false`, полный payment mailable
  рендерится в log-mailer;
- колонка перестроена по вертикальному layout Voyager: фиксированная ширина
  190px, дата и время, полноширинный цветной статус, подпись и select;
- browser smoke заказа 18451 подтвердил открытие modal со значением
  «Предоплата»; окно закрыто через «Отменить», БД осталась `not_payed`;
- Blade cache и admin/invoice regression suite прошли: 28 passed /
  112 assertions;
- добавлен `OrderVenipakLabelService`: валидирует получателя, отправителя,
  сроки/услуги и все посылки, формирует экранированный XML, использует активные
  реквизиты `venipak_data`, проверяет HTTP/XML-ответ и транзакционно сохраняет
  номера этикеток в `orders.labels`;
- pickup-заказы сохраняют ограничение Voyager: адресный режим отклоняется, а
  для отделения обязательны название и код пункта Venipak;
- в колонке добавлены рабочие Filament actions создания и печати: форма
  содержит получателя, отправителя, тип/скорость доставки, COD, дополнительные
  услуги и repeater посылок; печатается только номер из текущего заказа;
- визуальный browser smoke общей БД подтвердил вертикальный блок Voyager с
  логотипом, подписью `СОЗДАНИЕ ЭТИКЕТКИ` и постоянным списком номеров; реальное
  создание/печать не запускались, персональные данные в Venipak не передавались;
- `OrderVenipakLabelServiceTest` проверяет создание двух этикеток, pickup
  conflict, печать и ownership только через `Http::fake`; старые Venipak/CRM-SA
  тесты приведены к синтаксису PHPUnit 12, поэтому больше не пропускаются как
  `No tests found`;
- `php artisan view:cache` прошёл; admin/invoice regression suite: 34 tests,
  139 assertions, 1 ожидаемый skip; внешний Venipak HTTP и письма клиентам не
  выполнялись;
- колонка «Оплата» закрыта как parity-подблок, но ADM-FIL-003 остаётся
  IN PROGRESS; следующий точный подблок — «Пользователь»;
- начат аудит «Пользователь» по `user.blade.php` и `orders.blade.php`:
  обязательный состав — имя/телефон/email/status, количество заказов, locale,
  язык PDF, канал продаж, категория, менеджер и справочник статусов клиента;
  изменяются только `users.pdf_locale` и `users.client_status`;
- колонка «Пользователь» перестроена в отдельный вертикальный ViewColumn с
  точным fallback Voyager: при пустом `client_status` имя берётся из delivery,
  а телефон/email — прежде всего из связанного пользователя; добавлен
  eager-loaded `orders_count`, чтобы не выполнять count для каждой строки;
- канал берётся из `a_order_from`, категория — из существующего каталога
  `get_styles_for_quiz('ru')`, менеджер сохраняет правило Voyager «role_id=4,
  иначе Админ», статусы клиента — из реальной таблицы `user_types`;
- добавлен транзакционный `OrderUserService` и два permission-aware actions для
  `users.pdf_locale` и `users.client_status`; locale ограничен активным списком
  `laravellocalization.supportedLocales`, client status — FK-справочником;
- browser smoke заказа 18451 подтвердил поля и layout; modal статуса открыт и
  закрыт через «Отменить», после проверки `pdf_locale` и `client_status` остались
  `null`, внешние операции не выполнялись;
- `OrderUserServiceTest` проверяет оба обновления и отклонение неизвестных
  locale/status/пользователя; итоговый admin/invoice suite: 37 tests,
  143 assertions, 1 ожидаемый skip; Blade cache собран;
- колонка «Получатель» перенесена в отдельный ViewColumn по
  `user_data.blade.php`: сохранено различение payer/recipient phone, добавлены
  Viber/WhatsApp, страна/город/адрес/индекс, валидная или красная дата доставки,
  delivery locale, order image, подписи и существующие иконки оплаты/доставки;
- неработающая без Voyager ссылка `/admin/email-sender` заменена Filament modal,
  направленной только связанному пользователю заказа; исправлен риск legacy
  controller, который игнорировал `users[]` и рассылал всем подписчикам;
- добавлен `OrderRecipientEmailService`; до UAT письма подавляются через
  `ADMIN_RECIPIENT_EMAIL_ENABLED=false` и логируются без адреса/текста, а тесты
  используют `Notification::fake`;
- browser smoke заказа 18451 подтвердил данные, PayPal/pickup icons и email
  modal на `.invalid`; форма закрыта через «Отменить», отправки не было;
- `OrderRecipientEmailServiceTest` проверяет UAT suppression, одноадресное
  уведомление и отсутствие пользователя; admin/invoice suite: 40 tests,
  148 assertions, 1 ожидаемый skip; Blade cache собран;
- начат parity-подблок «Товар»: аудит `items.blade.php` и
  `all_basket_order_items.blade.php` подтвердил, что текущая Filament-ячейка
  теряет итоговый расчёт, акции/купоны, большую часть параметров legacy-типов,
  дополнительные изображения/фоны и действие запроса отзыва;
- точный объём переноса зафиксирован: расчёт заказа, coupon/bonus flags,
  content-блок, все позиции с изображениями и параметрами, цена/подарок,
  комментарий доставки и безопасный review request без реального письма во
  время UAT;
- колонка «Товар» перестроена по вертикальному layout Voyager: восстановлены
  исходная/акционная стоимость, скидки, экспресс, доставка и итог, legacy
  coupon/bonus flags, content-блок и все позиции, распознаваемые по `sumPrice`;
- для каждой позиции снова выводятся active/saved/offset/background images,
  подарок и полный `all_basket_order_items` с типовыми параметрами canvas,
  портрета, модульной картины, подарочной карты и других legacy-типов; правило
  Voyager не показывает дублирующий saved preview у canvas-like товаров;
- добавлен permission-aware action «Запрос отзыва» через
  `OrderReviewRequestService`; `ADMIN_REVIEW_REQUEST_ENABLED=false` подавляет
  письмо до UAT, а логи содержат только order/user ID без адреса клиента;
- browser-сверка общей БД по заказу 18451 с открытым Voyager подтвердила тот же
  расчёт `22 + 5 + 0 = 27`, параметры Canvas/40x40/Foto Kanvas/Interjers,
  placeholder отсутствующего изображения, комментарий и цену позиции;
- визуально восстановлены центрирование и компактная ширина 235px, длинный
  комментарий больше не заходит в «Комментарии»; лишний знак EUR у базовой
  стоимости и дублирующий broken saved preview устранены;
- modal запроса отзыва открыт для заказа 18451 и закрыт через «Отменить»;
  отправки клиенту не было. `OrderReviewRequestServiceTest` проверяет UAT
  suppression и отсутствие пользователя; Blade cache прошёл, admin/invoice
  regression suite: 42 tests, 151 assertions, 1 ожидаемый skip;
- parity-подблок «Товар» закрыт; ADM-FIL-003 остаётся IN PROGRESS, следующий
  точный подблок — «Комментарии»;
- начат аудит «Комментариев» по `comments.blade.php` и четырём chat partials:
  подтверждены отдельные client/admin/SA/painter потоки, статические комментарии
  order/admin/painter, previews клиентских и художественных комментариев,
  вложения эскизов/картин, unread/read state и SA bot control;
- текущая Filament-ячейка показывает только три счётчика и краткий текст, поэтому
  не считается parity. Точный порядок реализации: read-only layout и previews,
  DB-only admin chat, client/painter потоки с вложениями, затем SA WhatsApp/bot
  actions; изменяющие legacy POST/AJAX routes напрямую вызываться не будут;
- колонка «Комментарии» перестроена по Voyager: вертикально выводятся
  `orders.comment`, `admin_comment`, `painter_comment`, `client_comment`, три
  последних client и painter comments, затем четыре синие/зелёные chat-кнопки
  с общими и unread-счётчиками;
- добавлена отдельная связь с реальным `order_painter_comments`; истории
  client/admin/SA/painter eager-load без N+1 и открываются в Filament modal,
  сохраняя автора, дату, тип «набросок/картина», SA status/attachments и bot mode;
- внутренний чат больше не вызывает `update_admin_chat_ajax`: новый
  permission-aware action валидирует сообщение и транзакционно сохраняет его
  через `OrderAdminChatService` с текущим автором; остальные три потока пока
  преднамеренно read-only;
- browser smoke заказа 18451 подтвердил все четыре кнопки и совпадающее с
  Voyager размещение восьми колонок на одной ширине; client/admin modal открыты,
  admin modal закрыт без сохранения, количество сообщений заказа осталось 0;
- реальная история заказа 18421 отрендерена read-only напрямую из общей БД и
  содержит legacy-сообщение `order_admin_comments`; внешние письма, webhook и
  SA API не вызывались;
- `OrderAdminChatServiceTest` проверяет order/author binding, trim и валидацию;
  Blade cache и PHP syntax прошли, admin/invoice suite: 44 tests,
  156 assertions, 1 ожидаемый skip;
- 2026-08-28: выполнено точное сравнение PDF заказа 18451 из Voyager и
  Filament; данные совпадают, но dompdf 3.1.6 иначе обработал невалидный
  `align` в шапке и `border` на строках таблицы legacy-шаблона, созданного для
  dompdf 0.8.6;
- исправлена совместимость шаблона: шапка переведена на валидную
  трёхколоночную таблицу, а границы строк нормализованы до горизонтальных линий
  исходного PDF; повторный рендер заказа 18451 — A4, 1 страница, 931228 bytes;
- визуальная сверка PNG при 144 DPI подтвердила прежнюю структуру без внешней
  рамки; горизонтальные линии таблицы товаров совпадают с Voyager с точностью
  0–1 px, данные и суммы не изменились;
- PHP syntax прошёл; `tests/Feature/Admin` + `tests/Feature/Invoice`:
  24 passed / 97 assertions; генерация PDF не вызывает отправку email;
- 2026-08-28: начат подблок колонки «Номер» — безопасные Filament actions для
  данных фирмы, генерации/обновления и подтверждения счёта; исходная логика
  найдена в `OrdersController`, включая PDF, email и referral bonus side effects;
- добавлен транзакционный `OrderInvoiceService`: формирует PDF, обновляет
  `has_pdf/pdf_link`, валидирует наличие накладной и логирует операции;
- редактирование восьми реквизитов фирмы выполняется в Filament modal и
  перегенерирует PDF без вызова legacy POST/GET-контроллера;
- подтверждение счёта сохраняет legacy `watching → pegging`, дату и
  `pdf_approved`, отправляет `ApproveUserCheckoutMail` через логируемый
  `BestEffortMailService`; referral-бонус защищён от повторного начисления;
- добавлены подтверждающие модалки для генерации/обновления и отправки счёта;
  browser smoke заказа 18425 проверил все три окна и закрыл их без submit;
- `OrderInvoiceServiceTest`: успешная генерация после создания PDF, первое и
  повторное подтверждение, rollback состояния при ошибке PDF, fake PDF/mail,
  однократный referral-бонус — 3 passed / 12 assertions;
- итоговый admin regression suite: 21 passed / 52 assertions, 1 legacy test
  skipped по ранее зафиксированной причине отключённых admin routes;
- добавлен UAT-предохранитель `ADMIN_INVOICE_EMAIL_ENABLED=false`: подтверждение
  полностью рендерит письмо через Laravel log-mailer, но не вызывает внешний
  транспорт; Filament явно предупреждает оператору об отключённой отправке;
- создан изолированный тестовый заказ `18451` и пользователь
  `filament-test-20260828002724@example.invalid`; на общей БД успешно проверены
  `VR00999999`, PDF 931738 bytes, custom firm data, payment request
  `OPR-000043`, первое и повторное подтверждение, `watching → pegging`;
- локальный mail log содержит только тестовый адрес `.invalid`; реальные
  клиенты в smoke-тесте не использовались и внешние письма не отправлялись;
- browser smoke по поиску `18451` подтвердил полный итоговый вывод колонки:
  VR actions, счёт, повторное подтверждение, OPR-номер/сумма/назначение/status;
- устранены PHP deprecation warnings старого `ApproveUserCheckoutMail` за счёт
  объявления его runtime properties;
- тест защиты внешней почты подтверждает отсутствие вызова `send`; текущий
  admin regression suite: 22 passed / 58 assertions, 1 legacy test skipped;
- риск: legacy mutating GET routes пока остаются зарегистрированы для старого
  runtime-контракта; новые Filament actions их не используют, их закрытие нужно
  выполнить после отдельной проверки публичных потребителей;

- добавлен Orders Resource: ID, клиент, менеджер, status/payment badges, сумма,
  признак admin order, дата; поиск, сортировка и фильтры;
- добавлены Eloquent relations заказа к клиенту, менеджеру и назначениям;
- незавершённые create/edit/delete actions не публикуются до следующих задач;
- PHP syntax, routes и permission smoke прошли;
- выполнено прямое browser-сравнение с действующим Voyager
  `viarcanvas.loc/admin/orders`: legacy-экран содержит отдельные режимы текущих,
  завершённых, неоплаченных, производственных, печатных и отправленных заказов,
  расширенный поиск клиента/оплаты и производственные индикаторы;
- в Filament добавлены вкладки «Текущие», «Завершённые», «Неоплаченные»,
  «В производстве», «У печатника», «Отправлены сегодня», «Отправленные»,
  «Новые сегодня/вчера» и «Все»; default теперь эквивалентен legacy текущим
  заказам (`status != completed`);
- добавлены фильтры ID заказа, номера payment request, клиента, телефона,
  статуса, оплаты, менеджера, канала продаж, страны, точной суммы, размера и
  диапазона дат; пагинация по умолчанию — 13;
- browser smoke подтвердил отрисовку вкладок и 76 текущих заказов без ошибки;
  admin feature suite: 9 passed / 18 assertions;
- остаётся parity backlog: express-сортировка, категория, художник, VR-поиск,
  итоговая сумма, unread/chat и производственные индикаторы;
- после подключения общей БД воспроизведён legacy rotation comparator для
  текущих, неоплаченных, печатных и express-заказов; browser-сверка первых 13
  ID Voyager/Filament подтверждает одинаковые группы и порядок;
- в список добавлены VR/BAW/VRR/DS номера, последние payment requests,
  получатель и телефон, желаемая дата доставки, художник/печатник и счётчики
  клиентского, административного, художнического и SA-чатов с unread marker;
- VR-поиск добавлен в общий фильтр клиента; добавлен фильтр художника;
- panel layout переключён на `Width::Full`, desktop sidebar сделан сворачиваемым;
  канал продаж выведен по умолчанию,
  добавлены категория и итоговая сумма `orders.price` по активной выборке;
- перенесены дополнительные поля legacy-строки: способ доставки и город,
  краткий состав товаров/размер/количество, способ оплаты и сумма предоплаты;
  этикетки, сводка комментариев и дедлайн художника доступны как toggleable
  columns, чтобы оператор мог настраивать плотность таблицы;
- browser smoke на общей БД подтвердил отображение заказа `18440`: самовывоз
  Viar, дата доставки, `Canvas · 40x40`, PayPal и сумма 22 €; Livewire ошибок нет;
- добавлены live-счётчики быстрых вкладок; на общей БД подтверждены: текущие
  76, завершённые 14817, неоплаченные 16, в производстве 5, отправленные 1;
- фильтр категорий получает 21 переведённое значение из действующего
  `IndexController::get_styles_for_quiz('ru')`; фильтр менеджера повторяет
  Voyager role_id=4 и вариант «Админ / без менеджера»;
- поиск размера больше не использует широкий `LIKE`: проверяются
  `items->0..9->sizeId|size_name`, как в legacy-контроллере;
- browser smoke подтвердил открытие полного набора фильтров без Livewire ошибок;
- список перегруппирован в восемь смысловых колонок Voyager: номер, оплата,
  пользователь, получатель, товар, комментарии, художник и заказ; дублирующие
  отдельные страна/доставка скрыты, их значения находятся внутри получателя;
- по требованию приёмки ADM-FIL-003 остаётся IN PROGRESS до переноса не только
  данных, но и всех разрешённых операций внутри каждой из восьми колонок;
- в колонке «Номер» добавлено модальное управление VR00/BAW/VRR445/DS020:
  формат валидируется, смена очищает поля другого типа, пустое значение удаляет
  запись, запись выполняется в транзакции и только при `edit_orders`;
- `UpdateOrderVrNumberServiceTest`: create/replace/delete и invalid format;
  admin suite теперь 11 passed / 22 assertions;
- browser smoke открыл окно «Накладная заказа №18440»; проверка завершена через
  «Отменить», данные общей БД не изменялись;
- повторная сверка зафиксировала точные восемь видимых колонок и их порядок:
  «Номер», «Оплата», «Пользователь», «Получатель», «Товар», «Комментарии»,
  «Художник», «Заказ»; техническая колонка Filament actions удалена из строки;
- для «Товар», «Комментарии» и «Художник» добавлены отдельные составные ячейки,
  чтобы данные каждой Voyager-группы не распадались на дополнительные колонки;
- колонка «Номер» визуально приведена к образцу Voyager: центрированный номер,
  полноразмерный select накладной и отдельная bordered-карточка «Заявка на
  оплату» с синей кнопкой «Создать платеж»;
- выполнен повторный аудит непосредственно по Voyager Blade: подтверждены
  четыре значения select (`D-Art-Solutions SIA`, `D-Art-Solutions UAB`,
  `SIA ViarStudia`, `SIA VR-Technology`), отдельное состояние существующего
  VR-номера, данные фирмы, условный блок счёта/юрлица и состав payment request;
- ширина содержимого «Номер» уменьшена с 260px до legacy 180px (фактическая
  ячейка Filament 192px с внутренними padding), заголовок — 14px, payment card
  использует legacy margin/padding/font и компактную кнопку;
- payment request показывает те же поля Voyager: номер, сумму/валюту,
  назначение, локализованное название метода, paid/pending и копирование ссылки;
- browser smoke общей БД подтвердил состояние без накладной для заказа 18440
  и состояние `VRR4451183` со счётом для заказа 18425;
- browser smoke подтвердил модалки «Создать платёж для заказа №18440» и
  «Накладная заказа №18440»; обе закрыты без сохранения и изменения общей БД;
- `php artisan view:cache` прошёл; `php artisan test --filter=Admin` вместе с
  связанным baseline: 18 passed / 40 assertions, 1 legacy test skipped;
- следующий точный шаг: завершить этот parity backlog в
  **ADM-FIL-003 — Список и фильтры заказов**, затем продолжить ADM-FIL-005.

## ADM-FIL-004 — Карточка и редактирование заказа (DONE, 2026-08-27)

- карточка заказа показывает клиента, менеджера, статусы, суммы, комментарии,
  JSON-снимок доставки и полный legacy JSON состава заказа;
- Edit page меняет менеджера, статус заказа, оплату, цены и admin comment через
  `UpdateOrderService`, validation и DB transaction;
- при смене статуса заполняются `status_date` и соответствующая статусная дата;
- стандартный Filament delete action удалён: legacy deletion имеет обязательную
  бизнес-логику возврата бонусов и будет переноситься отдельно;
- `UpdateOrderServiceTest`: 2 passed / 5 assertions;
- `composer validate` и `view:cache` прошли; общий suite: 188 passed / 1254
  assertions, 5 skipped, 0 failed tests. Команда вернула exit code 1 при наличии
  skipped legacy contracts; четыре skip — отсутствующие Synvolve contracts,
  один — ещё не перенесённое создание admin payment request;
- следующий точный шаг: **ADM-FIL-005 — Позиции и файлы заказа**.

Обновлено: 2026-08-13.

## Цель

Перевести этот репозиторий `G:\OSPanel\home\viar13` с Laravel 6 на Laravel 13.
Первый приоритет — полностью поднять публичный frontend. Админка временно вне
scope; Filament 5 будет рассматриваться отдельным этапом.

Подробный backlog, статусы этапов и все найденные задачи ведутся в
`MIGRATION_PLAN.md`. Этот файл хранит выполненные изменения и evidence.

## Принятые решения

- миграция выполняется на основе текущего проекта, без использования
  `G:\OSPanel\home\viar_filament`;
- существующую публичную разметку и assets сохраняем, без редизайна;
- Voyager и admin routes не должны блокировать запуск публичной части;
- обновление выполняется итеративно, с сохранением поведения и тестами;
- production/test не переключаются до локального frontend UAT.

## Исходное состояние

- Laravel: `^6.2`;
- PHP constraint: `^7.3`;
- frontend build: Laravel Mix 6 / Webpack;
- админка: Voyager 1.4 и voyager-extension;
- публичные routes, controllers и Blade находятся в этом репозитории;
- текущий legacy runtime не загружается под активным PHP 8.3 из-за
  несовместимости Laravel 6 с современными сигнатурами PHP.

## Что сделано

- направление миграции и границы frontend-first зафиксированы;
- обновлён `AGENTS.md`;
- создан этот краткий журнал работ.
- создана рабочая ветка `codex/laravel13-frontend`;
- Composer runtime обновлён до PHP `^8.3` и Laravel `13.25.0`;
- Voyager, voyager-extension и translation-manager исключены из обязательного
  runtime; admin routes выключены константой конфигурации;
- совместимые публичные пакеты обновлены: localization, breadcrumbs, DomPDF,
  Socialite, enum, sluggable, location и media library;
- добавлен локальный compatibility layer для используемых frontend-контрактов
  Voyager: translations, `Voyager::image`, settings, menu models и resize path;
- исправлены Laravel 13/PHP 8.3 несовместимости exception handler,
  trusted proxies, breadcrumbs и slug return types;
- public auth controllers переписаны без удалённых `laravel/ui` traits:
  login/logout, register, password reset/confirm и email verification;
- исправлена компиляция JSON-LD: literal `@context` экранирован для нового
  Blade compiler;
- подтверждено: `php artisan --version` выводит Laravel 13.25.0;
- подтверждено: локальный HTTP `GET /` возвращает `200`, 498 864 bytes HTML,
  время smoke-запроса 2.82 s.
- `route:list` снова работает после замены public auth traits и добавления
  отсутствовавшего legacy `ImageController`;
- зарегистрировано 2 260 application routes; Voyager admin route group не
  подключается (legacy public helper `/admin/check-user` пока сохранён);
- HTTP smoke: `/` и `/en` возвращают 200 с RU/EN HTML;
- targeted regression: 5 tests / 12 assertions PASS;
- `composer validate`, package discovery, полный Blade `view:cache` и
  `git diff --check` PASS.

## В работе

- полный аудит восьми basket add-endpoints; active и legacy portrait payload
  families.

## Следующие действия

1. Покрыть active graphic portrait payload и simple legacy portrait family.
2. Проверить legacy/current canvas и reachability orphan-шаблонов.
3. Затем пройти inter/module/construct/future-art/recommendations.

## Риски и открытые вопросы

- часть legacy-пакетов не поддерживает Laravel 13 и потребует замены;
- старые controllers/models могут содержать несовместимые PHP-сигнатуры;
- admin/Voyager providers и routes нужно изолировать, не удаляя бизнес-данные;
- перед изменением схемы БД требуется аудит существующих таблиц и миграций.
- `paypal/paypal-checkout-sdk` Composer помечает abandoned; до payment-этапа
  требуется перенос на PayPal Server SDK с sandbox evidence;
- текущий compatibility translator покрывает frontend read-path; translation
  write-path должен быть реализован и протестирован отдельно до Filament.

## Журнал

### 2026-08-13 — старт нового направления

- подтверждено: основа — `G:\OSPanel\home\viar13`;
- старый отдельный target исключён из работы;
- текущий scope: публичный frontend; админка отложена.

### 2026-08-13 — первый Laravel 13 frontend boot

- обновлены Composer dependencies и lock;
- Laravel 13.25.0 успешно загружается через Artisan;
- главная страница текущего проекта отвечает HTTP 200 на локальном PHP 8.3;
- БД-схема и production/test не изменялись;
- следующий точный шаг: public auth compatibility и автоматический smoke.

### 2026-08-13 — основные страницы и первый каталог-срез

- создан `MIGRATION_PLAN.md`: этапы, чек-листы, найденные задачи и текущий
  статус теперь ведутся отдельно от этого журнала evidence;
- `AGENTS.md` требует сразу добавлять найденные задачи в план и обновлять оба
  migration-файла после заметного шага;
- восстановлен legacy frontend-helper `str_trans()` с поддержкой форматов
  `{{en}}...` и `[[en]]...`; контракт подтверждён unit-тестами;
- compatibility translator фильтрует пустые имена атрибутов, из-за которых
  Laravel 13 падал при переводе `GalleryPage`;
- HTTP smoke: FAQ возвращает `200` для `lv`, `lt`, `de`, `en`, `ee`; `ru`
  возвращает ожидаемый redirect на URL без locale, `pl` — на отдельный домен;
- HTTP smoke: `/about` и `/page/contacts` возвращают `200`;
- HTTP smoke: `/new/gallery`, `/en/new/gallery`, module/photo/reproduction,
  их репрезентативные категории, portrait card и `/en/new/canvas` возвращают
  `200`;
- неизвестная страница, locale, gallery category и portrait slug возвращают
  `404`, без runtime exception;
- regression после исправлений: 6 tests / 14 assertions PASS;
- auth forms `/login`, `/register`, `/password/reset` возвращают `200`, а
  `/account` и `/new/account` для гостя сохраняют legacy redirect на `/`;
- email verification routes отключены: старый `Auth::routes()` не включал
  verify, а фактическая User model не реализует этот контракт;
- `ConfirmPasswordController` передаёт в `Hash::check()` обычную строку вместо
  Laravel `Stringable`;
- добавлены изолированные SQLite feature-тесты auth: доступность форм,
  валидация, guest redirects, успешные login/logout и регистрация без отправки
  реальной почты, password reset token flow и password confirmation;
- итоговая targeted regression текущего шага: 17 tests / 72 assertions PASS;
- после auth-изменений повторно прошли `route:list`, полный `view:cache` и
  `git diff --check`;
- следующий точный шаг: authenticated account pages и их data dependencies.

### 2026-08-13 — клиентский личный кабинет

- compatibility User дополнен связью `role()` и read-only моделью Role;
- общий layout получает безопасные пустые tracker variables в test/CLI, при
  обычном HTTP они переопределяются реальными настройками;
- глобальная Blade-функция `isActiveRoute()` заменена локальным callback, что
  устранило fatal error при повторном account-render в одном PHP-процессе;
- на изолированной SQLite-схеме подтверждены authenticated customer routes:
  legacy `/account` redirect, `/new/account`, `/new/settings`, пустой
  `/new/orders` и `/new/mystocks` без активных акций;
- найден отдельный долг: account GET-страницы генерируют и сохраняют
  `inv_sale_code`; менять это без проверки legacy-сценария пока нельзя;
- наполненные orders/payment и painter account остаются отдельными задачами;
- следующий точный шаг: inventory публичных форм и AJAX endpoints.

### 2026-08-13 — route inventory и форма отзыва

- создан `docs/frontend-mutating-routes-audit.md`;
- зафиксировано 112 mutating route records: 99 web и 13 API;
- 81 web record не имеет явного route-level `auth`; это не признано
  уязвимостью без проверки controller/service guards;
- отдельно отмечены 20 `orders/*` POST routes и 3 публичных route records,
  ведущих в Admin controllers;
- review endpoint использует актуализированный `CreateReviewRequest`;
- обязательные текстовые поля, два изображения до 10 MiB и audio data URL
  получили серверную валидацию;
- исправлена неинициализированная переменная отзыва без аудио;
- image/audio отзывов сохраняются явно на public disk;
- validation, text-only и image/audio success paths покрыты feature-тестами;
- следующий точный шаг: photo/portrait/all-styles request forms.

### 2026-08-13 — photo/portrait/all-styles request forms

- быстрые заявки portrait и all-styles переведены на общий
  `QuickOrderRequest`;
- email проверяется как RFC email, телефон нормализуется и допускает от 7 до
  15 цифр с необязательным `+`;
- загрузка обязательна и ограничена 10 файлами; разрешённые MIME/расширения
  перечислены явно, application-level ограничения размера нет;
- сохранена совместимость двух реально найденных Blade-контрактов: массив
  `file[]` и legacy-поля `file`, `file2` ... `file10`;
- валидация обычной photo calculation form также подтверждена регрессионным
  тестом;
- targeted result: 4 tests / 25 assertions PASS;
- следующий точный шаг: guest AJAX login/register contract.

### 2026-08-13 — большие исходники для печати

- по уточнению владельца сняты Laravel size limits с quick order файлов,
  canvas `userImage` и клиентских файлов художнику;
- файл 100 MiB проходит validation contract в автоматическом тесте;
- ограничения количества и формата сохранены;
- `public/php.ini` уже содержит `upload_max_filesize=256M` и
  `post_max_size=256M`; активный CLI PHP использует другой ini с 2M/8M, что
  необходимо учитывать при локальном HTTP/UAT.

### 2026-08-13 — guest AJAX login/register

- добавлен отдельный `LoginAjaxRequest`: обязательные RFC email/password,
  нормализация email и JSON 422 validation contract;
- `RegisterStoreRequest` нормализует email/телефон и валидирует email, длины,
  формат телефона и подтверждение пароля;
- устранён блокер регистрации: активный frontend JS ожидал отсутствующее
  CAPTCHA-поле, а backend требовал его и выполнял синхронный внешний запрос;
- login/register endpoints получили rate limit `10/min` и `5/min`;
- ошибки Laravel 422 теперь выводятся в AJAX login UI; оба фактически
  используемых `add.js` проходят `node --check`;
- success login сохраняет legacy JSON `{"status":true}`, success register —
  JSON `true`; регистрация без реальной почты и нормализация данных покрыты
  feature-тестами;
- полноценная CAPTCHA/Turnstile оставлена явной отдельной задачей; фиктивная
  проверка site key не используется;
- совместная regression: 33 tests / 147 assertions PASS;
- `route:list`, полный `view:cache`, JS syntax и `git diff --check` PASS;
- следующий точный шаг: корзина и session state.

### 2026-08-13 — корзина, первый session-срез

- инвентаризированы 17 `basket/*` routes и основные `cart/*` routes;
- `basket/remove` переведён на `RemoveBasketItemRequest`: `basketId`
  обязателен, является целым индексом и не может быть отрицательным;
- устранён риск удаления нулевой позиции отсутствующим `basketId` из-за
  нестрогого сравнения legacy PHP;
- `basket/update/count` использует `UpdateBasketCountRequest` и принимает
  только целое количество 1--99 и неотрицательный индекс;
- validation errors этих AJAX endpoints всегда возвращаются JSON 422, а
  успешный legacy response contract сохранён;
- убрано лишнее двойное сохранение abandoned cart при одном update count;
- session tests подтверждают отклонение неверных payloads без мутации,
  корректное изменение количества и удаление позиции;
- targeted result: 4 tests / 24 assertions PASS;
- текущая совместная regression: 37 tests / 171 assertions PASS; `route:list`,
  полный `view:cache` и `git diff --check` также PASS;
- найдены отдельные задачи: state-changing GET/ANY basket routes и отсутствие
  общих validation contracts у `basket/add/*`.

### 2026-08-13 — basket add: canvas, portrait и modular

- ранее неиспользуемый `BasketStoreRequest` подключён к `/basket/add`;
- неполный canvas payload возвращает единый JSON 422 вместо попадания в
  repository с частичными данными;
- canvas contract проверяет тип корзины, цену, исходник и обязательные
  параметры конфигурации без ограничения размера файла;
- добавлен `PortraitBasketRequest` для цены, комментария, `orig_images` и
  `photo_ex`; MIME проверяется, size limit отсутствует;
- исправлен реальный modular mismatch: frontend-поле `image` нормализуется в
  ожидаемое repository-поле `activeImage`;
- тестовые canvas, portrait и modular файлы по 100 MiB проходят validation;
- targeted basket result: 8 tests / 39 assertions PASS;
- текущая совместная regression: 41 tests / 186 assertions PASS; `route:list`,
  полный `view:cache`, JS syntax и `git diff --check` PASS;
- следующий точный шаг: collage payload и storage success paths.

### 2026-08-13 — полный JS/Blade-аудит basket add

- подтверждено, что прежнее утверждение о проверке всех вариантов было бы
  неверным: опубликовано 8 отдельных basket add-endpoints;
- создан `docs/basket-add-payload-audit.md` с endpoint/source/status matrix;
- `/basket/add/portrait` вызывается минимум шестью JS-семействами и имеет как
  минимум simple legacy и current wizard payload families;
- общий `/basket/add` собирается global JS и несколькими inline Blade-копиями,
  которые отличаются по выбору полей и обработке JSON;
- `add/inter`, `add/module`, `add/construct`, `add/future_art` и recommendation
  endpoints пока не имеют полного Form Request/test coverage;
- найден legacy `graph_portrait.blade.php` без поля `price`, но активные
  `/graphic-portrait*` используют другой Blade и portrait endpoint; legacy-файл
  помечен как вероятный orphan до reachability-аудита;
- найдено глобальное отключение CSRF для `basket/add`, `*/basket/*` и
  `/cart/*`; изменение отложено до проверки всех callers;
- найден frontend-лимит future-art 20 MiB, противоречащий принятому контракту
  больших печатных исходников;
- текущий порядок изменён: сначала полная совместимость всех payload families,
  затем storage/checkout.

### 2026-08-13 — portrait families и future-art contract

- для portrait endpoint проверены simple generated, active oil и current
  wizard payload families;
- oil payload с `pid=undefined`, `orig_images[]` и без base64 preview больше
  не падает на `strpos(null)`: первый сохранённый оригинал становится active;
- загрузка оригинала portrait больше не дублируется, generated data URL
  проходит строгую проверку и сохраняется через uploads disk;
- добавлен `FutureArtRequest`: обязательные и нормализованные контакты, от 1
  до 15 изображений, явные MIME, JSON 422 и отсутствие size limit;
- удалена legacy frontend-проверка future-art `> 20 MiB`; активный footer
  такого лимита уже не содержал;
- исправлено вводящее в заблуждение серверное сообщение `Invalid filesize`:
  `max:15` ограничивал количество элементов массива, а не размер;
- targeted basket result: 15 tests / 76 assertions PASS;
- следующий точный шаг: аудит file/base64 веток `/basket/add/construct`.

### 2026-08-13 — construct payload families

- подтверждены collage/family base64 и modular original-file payload families;
- добавлен `ConstructBasketRequest` с условным требованием `image_offset` либо
  пары original `image` + безопасный `collageSvgImage_hash`;
- MIME для `image`, `photo_ex`, `fon[]`, `orig_images[]` проверяется без
  application-level size limit; original-файл 100 MiB проходит правила;
- устранена ветка, повторно сохранявшая отсутствующий `image_offset`;
- generated base64 строго декодируется и сохраняется через uploads disk;
- session/storage success и оба invalid-source режима покрыты тестами;
- targeted basket result: 18 tests / 91 assertions PASS;
- frontend regression: 45 tests / 227 assertions PASS;
- `route:list --path=basket/add`, `view:cache` и `git diff --check` PASS;
- следующий точный шаг: recommendation endpoints и серверный источник цены.

### 2026-08-13 — recommendation endpoints и server price

- кроме двух известных basket endpoints найден активный третий
  `/cart/add-recommended`; он добавлен в scope и документацию;
- добавлены `RecommendedBasketItemRequest` и
  `CanvasRecommendationRequest` с единым JSON 422 contract;
- frontend `price` сохранён только для обратной совместимости и больше не
  участвует в расчёте;
- gallery recommendation рассчитывается из `gallery_items.price_from`, country
  multiplier и скидки 30%, а также требует одноразовый session offer;
- canvas recommendation рассчитывается по `canvas_header.sizes_30x40`, требует
  существующий базовый товар и принимает исходник 100 MiB без size limit;
- исправлено чтение несуществующего `GalleryItem::image`: используется реальное
  поле `images`;
- подмена browser price на `0.01` не влияет на сохранённую цену во всех трёх
  сценариях;
- targeted basket result: 23 tests / 110 assertions PASS;
- frontend regression: 50 tests / 246 assertions PASS; `view:cache`,
  recommendation routes и `git diff --check` также PASS;
- найден следующий риск: item-card recommendation теряет выбранную
  конфигурацию и сохраняет только базовый товар; задача добавлена в backlog;
- следующий точный шаг: reachability/contracts `add/inter` и `add/module`.

### 2026-08-13 — retired legacy inter/module endpoints

- полный source search не нашёл активных callers `/basket/add/inter` и
  `/basket/add/module`;
- inter-form существует только в закомментированном canvas tab, а её
  click-handler перенаправлял submit на другой calculator action;
- active modular page использует `/basket/add/construct`; одноимённые CSS/JS
  классы лишь триггерят основной submit и не вызывают add/module;
- route names сохранены, оба POST URL переведены на единый JSON 410
  `legacy_endpoint_retired` с указанием актуальной замены;
- тест подтверждает HTTP 410 и отсутствие записи файлов/session mutation;
- targeted basket result: 24 tests / 121 assertions PASS;
- frontend regression: 51 tests / 257 assertions PASS; `view:cache`, полный
  список basket add routes и `git diff --check` также PASS;
- физическое удаление двух недостижимых controller methods оставлено после UAT;
- следующий точный шаг: общий `/basket/add`, legacy canvas builders и
  reachability `graph_portrait.blade.php`.

### 2026-08-13 — общий canvas payload и orphan builders

- активный `/new/canvas` подтверждён как `theme.viar.pages.canvas` с одним
  global `public/theme/viar/js/new_bot_scripts.js` submit builder;
- `canvas.blade.php`, `canvas_new.blade.php` и `graph_portrait.blade.php` не
  имеют активного controller render/include и классифицированы как orphan;
- активные graphic portrait routes используют `graphical-portrait-buy` и
  отдельный `/basket/add/portrait` contract;
- `BasketStoreRequest` теперь проверяет обязательные canvas IDs, JSON-массив
  положительных `boxIds`, строгий base64-формат preview и
  `terms_price <= price`;
- `userImage`, `orig_images[]` и `photo_ex` сохраняют MIME-проверку без
  application-level size limit; файлы по 100 MiB подтверждены тестом;
- active JS и min-копия показывают Laravel validation message и не сбрасывают
  выбранные файлы при 422;
- targeted basket result: 26 tests / 130 assertions PASS;
- frontend regression: 53 tests / 266 assertions PASS; `view:cache`, проверка
  обоих JS-файлов и список восьми basket add routes также PASS;
- физическое удаление orphan Blade/partials оставлено после frontend UAT;
- следующий точный шаг: аудит и безопасное сужение basket/cart CSRF exceptions.

### 2026-08-13 — basket/cart CSRF protection

- проверены активные basket/cart AJAX, FormData и обычные form callers;
- AJAX передают `X-CSRF-TOKEN`, canvas recommendation — `_token`, форма
  `cart/set_email` содержит `@csrf`;
- из `VerifyCsrfToken::$except` удалены `basket/add`, `*/basket/*`, cart root и
  все широкие `cart/*`/`*/cart/*` patterns;
- независимое исключение `/admin/upload/tinyimage` оставлено без изменений;
- добавлен structural contract для обычных и locale-prefixed basket/cart URL;
- targeted basket result: 27 tests / 137 assertions PASS;
- frontend regression: 54 tests / 273 assertions PASS; `view:cache`, PHP syntax
  и inventory basket/cart routes также PASS;
- следующий точный шаг: удалить mutating GET/ANY варианты basket routes после
  проверки callers и сохранить только POST contracts.

### 2026-08-13 — initial browser smoke основных страниц

- реальный HTTP/Chrome smoke выполнен на `https://viar13.loc` с текущей MySQL;
- main, module gallery, реальная item card `id=31`, canvas, portrait, basket,
  cart, login и register возвращают HTTP 200;
- исправлен 500 legacy basket: базовый Voyager `MenuItem` получил совместимый
  `link()`, включая route parameters и fallback для отсутствующего route;
- рекурсия frontend menu больше не зависит от отключённого
  `voyager::menu.bootstrap`;
- исправлен 500 cart: header widget использует `config('theme.resource')`
  вместо runtime `env('THEME_RESOURCES')`;
- устранён JS crash module item card: `interiorGallery.js` не запускается без
  обязательного `#canvas_interior`;
- повторная Chrome-проверка item card: title/H1/DOM присутствуют, project JS
  errors отсутствуют;
- frontend regression: 56 tests / 278 assertions PASS; JS/PHP syntax,
  `view:cache` и повторный реальный browser smoke также PASS;
- отдельная матрица и наблюдения сохранены в
  `docs/frontend-browser-smoke.md`;
- следующий точный шаг: интерактивный canvas/gallery → cart smoke без создания
  заказа.

### 2026-08-13 — interactive gallery/cart и checkout error audit

- прежняя browser-оценка исправлена: скрытый текст popup больше не считается
  доказательством успешного POST; проверяются response outcome, Laravel error,
  итоговый URL и фактическое содержимое корзины;
- найден реальный JSON 422: gallery item JS отправлял `image` как строку
  `"null"`; пустое generated image теперь исключается из FormData, а validation
  error показывается пользователю;
- найден следующий 500 после успешного POST: optional `decor_id=undefined`
  приводил к `translate()` на `null`; request очищает legacy sentinel-строки,
  а cart reader проверяет положительный integer ID и наличие decoration;
- повторный Chrome smoke: success popup видим, `/cart` показывает два реальных
  товара по 50 €, project console errors от `viar13.loc` отсутствуют;
- `/cart/data` успешно отображает пользователя и сумму; на `/cart/delivery`
  отдельно обнаружены отсутствующие view и вложенный `cart.citys` partial;
  runtime env заменён на theme config во всём controller/cart Blade контуре;
- прямой `/cart/payment` без delivery session больше не падает и возвращает на
  `/cart/delivery`; добавлен regression test;
- frontend regression: 58 tests / 292 assertions PASS; `view:cache`, JS/PHP
  syntax и `git diff --check` PASS;
- внешняя локальная ошибка CookieYes и предупреждения browser extensions не
  классифицируются как project JS errors;
- следующий точный шаг: canvas upload → cart, затем валидный delivery → payment
  без отправки заказа.

### 2026-08-14 — синхронизация server commit `72af3357`

- source проверен напрямую в `C:\OSPanel\domains\asoft\viar`; commit содержит
  68 файлов;
- 47 language-файлов уже побайтно совпадали с состоянием commit и повторно не
  перезаписывались;
- перенесены 11 публичных изменений: localhost-safe image URL normalization,
  Apache bot/gallery protection, `.user.ini`/`php.ini`, legacy password-reset
  payload, public CSS, Google verification и три contact WebP;
- `app/Helpers/functions.php` не заменялся целиком: сохранены Laravel 13
  compatibility helpers `str_trans`, `setting`, `menu`, `voyager_asset`, а
  серверная localhost-правка интегрирована отдельно;
- SHA-256 трёх WebP совпадает с server source; HTTP каждого изображения —
  `200 image/webp`; Google verification возвращает точное содержимое commit;
- при первой синхронизации 10 admin/Voyager ALT-файлов были ошибочно оставлены
  только в backlog из-за frontend scope; после уточнения пользователя все они
  физически перенесены в `viar13` как source corpus;
- 67 из 68 commit paths побайтно совпадают с server commit; единственное
  намеренное отличие — `app/Helpers/functions.php`, где серверная правка
  объединена с обязательными Laravel 13 compatibility helpers;
- синхронизированы `AltApprovePendingCommand`, `AltGenerateCommand`,
  `AltSuggestionController`, `GenerateImageAltJob`, `AltGenerator`, config,
  два admin assets, Voyager master и `routes/admin.php`;
- PHP/JS syntax всех добавленных ALT-файлов проходит; Artisan видит команды
  `alt:approve-pending` и `alt:generate`, их `--help` загружается без ошибок;
- Voyager ALT routes не активировались: `route:list --path=alt-suggestions`
  пуст, что соответствует отключённой админке текущего этапа;
- Apache smoke: главная 200, обычная gallery после locale redirect 200,
  SemrushBot 403, gallery с `size=` 403, verification 200;
- обнаружен риск: production `.htaccess` блокирует реальные gallery filters
  `color|order|size`, которые обслуживает `GalleryController`; правило пока
  сохранено точно по server commit, решение вынесено в план;
- первый auth regression выявил старую cache-зависимость тестов: без прогретого
  cache отсутствовали SQLite-таблицы `header_menu`/`footer_menu`; тестовая схема
  дополнена, cache очищается перед каждым тестом;
- итог: frontend regression 59 tests / 295 assertions PASS; `view:cache`, PHP/
  JS syntax и `git diff --check` PASS;
- следующий точный шаг: решить судьбу blanket gallery filter block либо
  продолжить запланированный canvas upload → cart smoke.

### 2026-08-17 — восстановление gallery query filters

- из `public/.htaccess` удалена blanket-блокировка `color|order|size`, которая
  запрещала штатные ссылки frontend-галереи; блокировка Semrush/PetalBot
  сохранена и проверена ответом 403;
- подтверждено, что gallery filters используют параметризованный Laravel Query
  Builder, а сортировка ограничена значениями `new|cheap|expensive`;
- реальные HTTP-проверки `color=1`, `size=30x40_over`,
  `size=30x40_smaller`, всех сортировок и комбинированного запроса на `/en/`
  возвращают 200; выдача меняется для color и разных сортировок;
- негативная проверка обнаружила `500 Undefined array key 1` для
  `size=broken`, затем второй 500 в Blade при массивном `size[]`;
- `GalleryController` теперь удаляет некорректные `color`, `size`, `order` из
  query до передачи в модель и Blade; `GalleryItem` дополнительно защищён от
  некорректных типов и формата размера;
- повторные запросы с `size=broken`, `size[]=...`, `color=abc`, `color[]=1` и
  `order[]=cheap` возвращают 200; после них Laravel log не изменился;
- PHP syntax PASS; frontend regression — 59 tests / 295 assertions PASS;
- полный suite проверен отдельно: 72 passed / 5 failed. Все пять failures
  относятся к уже известному backlog `SynvolveWebhookServiceTest`: отсутствует
  таблица `orders` в SQLite и четыре ожидаемых метода сервиса;
- следующий точный шаг: интерактивный canvas upload → cart с реальным тестовым
  изображением и проверкой HTTP-ответа, Laravel log, JS console и session cart.

### 2026-08-17 — интерактивный Canvas → cart

- пользователь прошёл полный Canvas-конфигуратор и отправил реальный файл;
  Laravel log подтверждает успешный `/basket/add`: исходник сохранён в
  `uploads`, preview — в `user_images`, `boxIds=[3]`, итоговый `new_count=1`;
- ответ не был `422`: repository и controller завершили добавление успешно;
- причина отсутствующих иконок установлена по browser console: Canvas был
  открыт по HTTP, а шрифты запрашивались по HTTPS и блокировались CORS;
- включён постоянный HTTP → HTTPS redirect с учётом
  `X-Forwarded-Proto: https`; HTTP Canvas возвращает `301` на тот же HTTPS URL,
  прокси-запрос не зацикливается, WOFF по HTTPS возвращает `200 font/woff`;
- на HTTPS браузер подтверждает `icons-tools`, загруженный font и glyph content
  для `undo/redo/zoom/turn/delete`; отдельные файлы иконок не требуются;
- найденная browser error `Canvas3D.PosTo3dBoxPos: reading layers` защищена от
  событий после очистки `box`/`camera`;
- стартовый лог `/basket/add` больше не пишет полный `Image3d` base64 и
  содержимое загруженных файлов: остаются только поля и безопасные признаки;
- ограничение размера загружаемых изображений не добавлялось; контракт файла
  100 MiB по-прежнему проходит;
- проверки: PHP syntax, `node --check`, `view:cache`, `git diff --check` PASS;
  basket regression — 29 tests / 151 assertions PASS;
- следующий точный шаг: сохранить валидную доставку и открыть payment view без
  отправки реального заказа.

### 2026-08-17 — checkout delivery → payment

- создан отдельный `SetDeliveryRequest`: условно проверяются courier, Venipak,
  Viar workshop, city delivery и email/no-delivery; ошибки возвращаются JSON
  `422`, активный frontend показывает первое сообщение пользователю;
- стоимость больше не принимается из браузера: courier/Venipak берутся из
  `country_tels`, city delivery — из `a_delivery_towns`, workshop/email равны
  нулю, купон `free_delivery` сохраняет нулевую доставку;
- ограничения размера вложенного к комментарию изображения не добавлялись;
  проверяются формат data URL и корректность base64;
- browser UAT выявил и исправил отсутствующие Blade view из-за лишней точки в
  `VinepakApiController` (`.cart.citys`/`.cart.warehouses`);
- исправлена гонка Venipak AJAX: поздний ответ больше не сбрасывает выбранного
  courier обратно на Venipak и не удаляет глобально все `.js-active`;
- повторный HTTPS-проход сохранил courier `Riga / LV-1001 / Testa iela 1` с
  серверной ценой `5 €` и открыл `/en/cart/payment`; итог `63.00 €`, заказ не
  создавался;
- regression: `PublicBasketSessionContractTest` — 30 tests / 174 assertions
  PASS, включая все пять вариантов, подмену frontend price и обязательные поля;
  расширенный frontend regression — 60 tests / 318 assertions PASS;
  PHP/JS syntax, `view:cache` и `git diff --check` PASS;
- найден отдельный TODO: Ahrefs Analytics инициализируется дважды; CookieYes,
  Meta и extension warnings относятся к локальному домену/браузерному окружению;
- ранее запланированный mobile viewport аудит позднее исключён как
  `NOT REQUIRED`: дизайн и frontend-разметка не менялись.

### 2026-08-17 — безопасная проверка способов оплаты

- `/cart/setpay` принимает только 8 методов, реально показанных checkout:
  `on_delivery`, четыре метода Paysera, PayPal, transfer и prepayment;
  произвольные значения и наложенный платёж для несовместимой доставки дают
  JSON `422`, отсутствующая delivery session также отклоняется;
- финальное создание заказа переведено с GET на CSRF-защищённый POST; frontend
  отправляет скрытую POST-форму и блокирует повторный клик, что снижает риск
  дублирования заказа;
- `save_order_and_pay` больше не обращается к отсутствующим delivery/payment
  session keys: пользователь возвращается на точный недостающий шаг checkout;
- Paysera start больше не использует `dd()`/`exit()` и возвращает Laravel
  redirect либо контролируемую ошибку; URL шлюза строится без внешнего запроса;
- PayPal start больше не использует `header()/exit()`; redirect и ошибка
  возвращаются вызывающему controller, поэтому ошибка шлюза не маскируется
  ложной страницей успешного заказа;
- callbacks PayPal без token/заказа безопасно возвращают в корзину; повреждённые
  Paysera accept/cancel/callback не дают 500, callback отвечает `400 ERROR`;
- Paysera project/sign secret удалены из PHP и перенесены в `config/paysera.php`
  + локальный `.env`; `.env.example` содержит только пустые placeholders;
- два legacy payment test-файла снова включены в PHPUnit 12: методы с устаревшим
  `@test` переименованы в `test_*`; admin-only тест ожидаемо skipped, поскольку
  admin routes исключены из текущего frontend runtime;
- проверки: расширенный frontend/payment regression — 76 passed / 397
  assertions, 1 admin-only skipped; PHP/JS syntax, config cache, `view:cache` и
  `git diff --check` PASS;
- реальные запросы Paysera/PayPal не выполнялись: локальный `.env` использует
  production endpoints. По решению пользователя sandbox исключён из приёмки,
  так как недоступен; реальная транзакция остаётся только штатной проверкой
  после запуска.

### 2026-08-17 — защита Paysera callback

- добавлен единый `PayseraCallbackService` для checkout-заказов и публичных
  ссылок на доплату;
- перед оплатой checkout сумма восстанавливается только из сохранённых данных:
  `sale_price` (либо `price`) + `items.total_terms_price` +
  `delivery.deliv_price`; входящая сумма Paysera сравнивается в евроцентах;
- проверяются успешный Paysera status, EUR/сохранённая валюта, реальный
  Paysera-метод заказа и допустимый переход `not_payed -> payed`; уже оплаченный
  заказ обрабатывается идемпотентно без повторного webhook;
- для ссылки на доплату разрешён только переход `pending -> paid`, сумма и
  валюта сверяются с `order_payment_requests`, повторный callback безопасен;
- `pay_cancel` больше не способен пометить заказ оплаченным; callbacks ссылок
  на доплату используют `config/paysera.php` вместо удалённых констант и не
  раскрывают текст внутренних исключений в HTTP-response;
- реальные запросы и списания не выполнялись;
- проверки: PHP syntax PASS; payment regression — 14 passed / 38 assertions,
  1 admin-only test ожидаемо skipped; проверены неверная сумма, валюта,
  запрещённый переход и повторный callback.
- последующий аудит PayPal подтвердил только базовое покрытие start/error/cancel:
  текущий Capture handler ещё не сверяет `COMPLETED`, сумму, валюту и
  `reference_id`, поэтому полная проверка PayPal вынесена в следующий TODO.

### 2026-08-17 — защита подтверждения PayPal

- Capture API теперь возвращает контроллеру фактический provider result, а не
  только `true/false`; заказ не меняется по данным браузера;
- добавлен `PayPalCaptureService`: обязательны верхний статус `COMPLETED`, один
  purchase unit с ожидаемым `reference_id`, один завершённый capture, точная
  сумма и сохранённая валюта EUR;
- для checkout дополнительно проверяется метод `paypalOnetimePayment` и переход
  `not_payed -> payed`; повторный возврат для уже оплаченного заказа не вызывает
  новый Capture API и не отправляет повторный Synvolve webhook;
- аналогичные проверки применены к публичным ссылкам на доплату с переходом
  `pending -> paid`;
- выделен единый `OrderPaymentAmountCalculator` для PayPal/Paysera callback и
  повторного запуска оплаты из кабинета; учитываются сохранённые `sale_price`,
  `sale_eur`, `sale_percent`, срочность и доставка;
- реальные запросы к PayPal и списания не выполнялись; ответы Create/Capture и
  ошибки провайдера проверены test doubles;
- проверки: payment/account regression — 26 passed / 66 assertions,
  1 admin-only test ожидаемо skipped; PHP syntax, Pint новых сервисов и
  `git diff --check` PASS.

### 2026-08-20 — checkout не зависит от доступности SMTP

- воспроизведена и разобрана ошибка после оплаты перечислением: заказ `18448`
  уже сохранился (`watching` / `not_payed`), а `500` возник при отправке письма
  покупателю; повторное оформление этого заказа создаст дубликат;
- причина локального SMTP-сбоя — недоверенная для PHP цепочка сертификатов от
  перехватывающего почтовый TLS Avast Web/Mail Shield; отключение проверки TLS
  не использовалось;
- `config/mail.php` переведён с формата Laravel 6 на mailers-конфигурацию
  Laravel 13; локально выбран `MAIL_MAILER=log`, а проверка сертификата SMTP
  остаётся включённой через `MAIL_VERIFY_PEER=true`;
- регистрационное письмо, подтверждение заказа и административное PDF-письмо
  выполняются best effort: ошибка транспорта логируется и больше не прерывает
  сохранение заказа, webhook и дальнейшие действия checkout;
- проверки: PHP syntax PASS, config clear PASS; 53 связанных checkout/payment
  теста, 265 assertions PASS, включая принудительный отказ mail transport;
- перед production-запуском остаётся проверить реальную SMTP-доставку с
  доверенной цепочкой сертификатов и `MAIL_MAILER=smtp`.

### 2026-08-20 — реальный SMTP восстановлен после отключения антивируса

- локальный `.env` и `.env.example` возвращены на `MAIL_MAILER=smtp`;
- Symfony SMTP transport успешно выполнил реальное подключение к серверу без
  отправки тестового письма и без исключения авторизации;
- TLS-проверка включена: сервер предъявил сертификат для
  `mail.viarcanvas.com`, выданный `YR1`, действующий до 2026-10-01;
- локальный сертификат Avast в соединении больше не присутствует;
- best-effort защита checkout сохранена: письма отправляются как раньше, но
  временная ошибка почты больше не превращает уже сохранённый заказ в `500`.

### 2026-08-20 — реальный UAT оплаты перечислением

- пользователь полностью оформил новый заказ и подтвердил переход на страницу
  «Спасибо» и получение клиентского письма;
- в БД подтверждён заказ `18449`: `payment=transfer`,
  `payment_status=not_payed`, `status=watching`, пользователь `10233`;
- SMTP/TLS и клиентское письмо работают в реальном checkout-сценарии;
- проверка свежего production-лога выявила две отдельные post-order задачи:
  административное письмо падает на несовместимом с Symfony Mailer вызове
  `setBody(string, 'text/html')`, а Synvolve `POST NewOrder` отвечает `404`,
  потому что workflow для production URL не зарегистрирован/не активирован;
- благодаря best-effort обработке эти ошибки не сорвали заказ, но post-order
  поток пока нельзя считать полностью завершённым;
- следующий точный шаг: исправить административное письмо, покрыть его тестом и
  затем повторно проверить лог; Synvolve требует отдельной проверки внешнего
  workflow/URL.

### 2026-08-20 — аудит отправки писем под Symfony Mailer

- проверены прямые отправки `Mail::send/to`, 33 Mailable-класса и четыре
  Notification-механизма; PHP syntax всех 37 файлов `app/Mail` и
  `app/Notifications` проходит;
- найдены 25 вызовов устаревшего `setBody(string, 'text/html')` в checkout,
  корзине, двух вариантах кабинета, UserManage и неактивном Voyager-корпусе;
  все заменены на совместимый с Symfony Mime вызов `html(string)`;
- добавлен общий regression-контракт: прямое HTML-письмо собирается через
  `array` transport, а в `app` запрещено повторное появление `->setBody(`;
- административное уведомление о заказе `18449` повторно отправлено отдельно:
  реальный SMTP принял его, прежний `TypeError` отсутствует, клиентское письмо
  повторно не отправлялось;
- попутно устранены предупреждения PHP 8.4 в этом пути: объявлены свойства
  `payseraController`/`gfc`, nullable `order_vr_id` нормализуется до строки перед
  `strpos()` в генераторе PDF; повторная генерация PDF прошла без предупреждений;
- два существующих почтовых набора снова распознаются PHPUnit 12: 18 тестов
  password reset/overdue email теперь реально выполняются вместо
  `No tests found`;
- итоговая связанная регрессия: 44 passed / 587 assertions; PHP syntax и
  повторная генерация PDF PASS;
- открытый вопрос: административный метод исторически генерирует PDF, но не
  прикладывает его и не вставляет ссылку; поведение не менялось без согласования.

### 2026-08-21 — диагностика Synvolve `NewOrder` 404

- текущий именованный endpoint `POST .../webhook/NewOrder` и ранее
  использовавшийся UUID endpoint проверены по одному разу: оба возвращают `404`
  с сообщением Synvolve, что workflow не зарегистрирован/не активирован;
  адрес приложения не менялся на неподтверждённый URL;
- добавленные без согласования outbox, retry, миграция и расширение payload
  полностью удалены; созданная служебная таблица откатана;
- существующая бизнес-логика `SynvolveWebhookService` возвращена без изменений;
- внешний блокер: в Synvolve нужно активировать workflow `POST NewOrder` либо
  сообщить актуальный production URL. После этого повторить штатный вызов и
  подтвердить ответ `2xx` по журналу приложения.

### 2026-08-21 — mobile-визуальная проверка исключена

- отдельный mobile viewport аудит отмечен как `NOT REQUIRED`, поскольку дизайн
  и frontend-разметка в рамках миграции не менялись;
- визуальные правки без обнаруженной функциональной ошибки не выполняются;
- следующий функциональный этап: проверить кабинет пользователя с наполненными
  заказами и штатную страницу оплаты заказа.

### 2026-08-21 — кабинет с заказами и страница оплаты

- в реальной авторизованной браузерной сессии открыт `/en/new/orders`: корректно
  показаны четыре заказа (`18449`, `18448`, `18447`, `6824`), их статусы,
  позиции, доставка, суммы и ссылки оплаты для активных неоплаченных заказов;
- ссылка `Pay now` заказа `18449` открыла штатную GET-страницу оплаты без
  запуска транзакции; отображены сумма `82.00 €`, доставка `0.00 €`, стандартное
  производство и пять методов: online Paysera, карта, Google Pay, Apple Pay и
  PayPal;
- все пять форм используют `POST /en/new/orders/18449/payment`; отправка формы
  не выполнялась, внешнее списание и создание платежа не запускались;
- битых изображений, новых ошибок Laravel и ошибок приложения в browser console
  на проверенном пути нет; Ahrefs duplicate, CookieYes local-domain, Meta и
  browser-extension сообщения уже классифицированы отдельно;
- regression: `PublicAuthRouteContractTest` и `ClientOrderPaymentTest` —
  `20 passed / 91 assertions`, включая владельца заказа, валидацию метода,
  запрет чужого заказа и расчёт скидочной суммы;
- найден вопрос контракта, код не менялся: UI скрывает оплату завершённого заказа,
  но прямые payment routes не проверяют `status=completed`. До подтверждения
  существующую бизнес-логику оставить без изменений.

### 2026-08-21 — Ahrefs duplicate оставлен без изменений

- подтверждено, что Ahrefs одновременно находится в `glob_config.analytics` и
  `glob_config.analytics_body`, из-за чего внешний скрипт сообщает о повторной
  инициализации;
- по решению пользователя исправление не требуется; начатая дедупликация
  полностью отменена, код и данные аналитики не изменены.

### 2026-08-21 — Node/Mix исключён из frontend scope

- подтверждено, что активный frontend работает на готовых статических CSS/JS и
  не требует Node/Mix-сборки для запуска;
- production build и обновление build-зависимостей отмечены как `NOT REQUIRED`;
- статические assets и browser console проверяются только вместе с реальными
  функциональными сценариями; дизайн отдельно не пересматривается.

### 2026-08-21 — каталог, фильтры и локализации

- в браузере проверены главная gallery, типы `photo`, `module`, `reproduction`
  и категория `photo/canvas-landscape`; страницы показывают товары и не имеют
  битых изображений;
- валидная комбинация `color=1&size=30x40_over&order=cheap` отфильтровала список
  и сохранила canonical без query + `noindex`; массивный `color[]`, повреждённый
  `size` и неизвестный `order` безопасно проигнорированы без `500`;
- подтверждены локализованные URL, `html lang`, заголовки и canonical для
  `ru/en/lv/lt/de/ee`; польские `/pl/*` по существующему контракту
  перенаправляются на отдельный `https://viar-art.pl/`;
- обнаружены три DB-ссылки slider `photo|module|reproduction` на production
  `viarcanvas.com`; в английской версии две также теряли `/en`;
- Blade теперь распознаёт только эти три известных внутренних gallery path и
  строит для них текущий локализованный route. Неизвестные/custom slider links
  продолжают выводиться из БД без изменений;
- повторная browser-проверка: все три `View Catalog` ведут на
  `viar13.loc/en/new/gallery/{type}`; новых production Laravel errors нет;
- проверки: `GallerySliderLinkContractTest` — `1 passed / 4 assertions`,
  `php artisan view:cache` и `git diff --check` проходят.

### 2026-08-21 — HTML sitemap и локализованные подкатегории

- воспроизведён `500` на `/de/sitemap`: Blade ожидал Eloquent-объект
  подкатегории (`$subcategory->slug`/`name`), но контроллер после перевода
  преобразовывал коллекцию в массив через `toArray()`;
- в `PageController::generate_sitemap_html()` убрано только лишнее
  преобразование в массив; маршруты, содержимое sitemap, бизнес-логика и
  внешний вид не менялись;
- browser-проверка `/de/sitemap`: HTTP `200`, заголовок `Lageplan`, 264 ссылки,
  страница Laravel-ошибки отсутствует;
- HTTP-проверка включённых локалей: `ru`, `lv`, `lt`, `de`, `en`, `ee` — `200`;
  `/pl/sitemap` сохраняет существующее перенаправление на `viar-art.pl`;
- добавлен `SitemapHtmlContractTest`: `1 passed / 2 assertions`; также прошли
  `php artisan view:cache`, PHP syntax controller/test.

### 2026-08-24 — локализованная генерация PDF-счёта (`f2feb24d`)

- из `C:\OSPanel\domains\asoft\viar`, коммит `f2feb24d`, перенесены изменения
  `DynamicPDFController` и четыре сервиса `App\Services\Invoice` для определения
  НДС продавца, расчёта итогов, суммы прописью и формирования итогового блока;
- добавлены переводы invoice для `lv`, `lt`, `pl`, `ru`, `de`, `en`, `et` и
  alias `ee`; исторические CRM/Filament-документы исходного проекта не
  переносились;
- сохранена Laravel 13/PHP 8.4-защита: nullable `order_vr_id` преобразуется в
  строку до строковых операций;
- три перенесённых набора тестов адаптированы с docblock data providers на
  атрибуты PHPUnit 12: `36 passed / 95 assertions`;
- реальная проверка заказа `18449`: Dompdf 3.1.6 создал одностраничный A4 PDF
  размером 931480 байт; визуально таблица, `Amount payable`, сумма прописью и
  электронное уведомление отображаются полностью, без обрезки;
- PHP syntax всех перенесённых классов/тестов и `php artisan view:cache` — PASS;
- полный suite: PDF и frontend-тесты проходят, итог `162 passed / 1114
  assertions`, `1 skipped`; остаются 5 ранее существующих несвязанных ошибок
  `SynvolveWebhookServiceTest` (нет тестовой таблицы `orders` и методов,
  отсутствующих в текущем откатанном Synvolve-сервисе).

### 2026-08-24 — корзина и сохранение состояния

- через реальный Latvian frontend добавлены три типа позиций: reproduction
  `item/35`, photo `item/1076` и module `item/974`;
- количество reproduction изменено `1 -> 2 -> 1`: AJAX-пересчёт обновил сумму
  `50 -> 100 -> 50 €`, общий счётчик и итоговую сумму;
- после перезагрузки сохранились три позиции по одной штуке и итог `117.00 €`;
- переход с шага товаров на `/lv/cart/data` работает, возврат на `/lv/cart`
  сохраняет весь состав корзины;
- новых `production.ERROR/CRITICAL` в Laravel-логе за время проверки нет;
  browser console содержит только ранее классифицированные внешние CookieYes,
  Ahrefs/Meta и extension-сообщения;
- с подтверждения пользователя через UI удалена тестовая позиция photo за
  `17 €`; после reload остались reproduction и module по `50 €`, счётчик `2`,
  итог `100.00 €` — удаление и пересчёт сохранились в сессии;
- связанные regression-проверки количества, удаления и защиты payment-step:
  `3 passed / 15 assertions`;
- задача закрыта без изменений кода, дизайна или бизнес-логики; следующий
  frontend-этап — полный checkout до создания тестового заказа либо проверка
  оставшихся публичных upload/forms.

### 2026-08-24 — полный checkout до финального подтверждения

- реальная checkout-сессия проведена через `/lv/cart/data`, курьерскую доставку
  и `/lv/cart/payment`; пустая форма данных показала штатную browser validation;
- создан и авторизован тестовый пользователь `43198` с адресом
  `codex-checkout-20260824@invalid.test`; использованы только синтетические
  имя, телефон и адрес;
- в заказ подготовлены две позиции по `50 €`, курьерская доставка `5 €`, итог
  `105.00 €`; штатным UI выбран банковский перевод (`transfer`);
- проверен последний шаг: frontend сначала отправляет `/cart/setpay`, после
  успеха делает POST `/save_order_and_pay`; этот POST создаёт заказ, очищает
  корзину, пытается отправить клиентское и административное письма и вызывает
  Synvolve webhook. Финальная кнопка ещё не нажималась, максимальный заказ в БД
  до действия — `18449`;
- в свежем логе отдельно обнаружен `View [pages.index.footer] not found` для
  пользователя `43198`; payment DOM остаётся рабочим. По последующему решению
  пользователя это локальная особенность сервера: воспроизведение и исправление
  не требуются, блокером frontend ошибка не считается;
- следующее точное действие: после явного подтверждения нажать `TĀLĀK`, затем
  проверить redirect, созданную запись, суммы/статусы, письма, webhook и новые
  ошибки Laravel.

### 2026-08-26 — перенос frontend-коммита `dabb75f0`

- из `C:\OSPanel\domains\asoft\viar` перенесены обе правки коммита: в
  `main.min.css` знак свёрнутого блока предложения изменён с `+` на `%`, его
  размер уменьшен с `32px` до `24px`; в compact overlay добавлен
  `line-height: 44px` для вертикального центрирования;
- Laravel/PHP-код, бизнес-логика и admin/Voyager-файлы не затрагивались;
- SHA-1 обоих итоговых Git blobs полностью совпадают с исходным коммитом:
  `bbb5be0305d79ba08b85f2fe0f9e25c96f4d6572` и
  `b9b19196174fc75a05e4ad78740b02ced5f42d2f`;
- `php artisan view:cache` и `git diff --check` прошли; CSS и главная страница
  отдаются через `https://viar13.loc` с HTTP `200`, новые Laravel errors не
  зарегистрированы.

### 2026-08-26 — решение по серверному пересчёту цен

- универсальный серверный пересчёт цен не реализуется: активные JS-формы и
  конструкторы используют разные наборы параметров и собственные расчёты;
- существующую бизнес-логику корзины не менять без отдельной матрицы источников
  цены и подтверждённого контракта для каждого типа товара;
- пункт сохранён в плане как `REVIEW`, а не как готовая задача на исправление.

### 2026-08-26 — checkout: отказ SMTP на шаге создания пользователя

- в новой реальной checkout-сессии добавлена reproduction `item/35` за `50 €`;
  корзина и переход на `/lv/cart/data` работают;
- POST `/cart/setuser` создал тестового пользователя `43199`, после чего
  завершился ошибкой Symfony Mailer: локальный TLS не подтвердил сертификат
  `ssl://mail.viarcanvas.com:465`; из-за прямого `Mail::to()->send()` AJAX не
  успел авторизовать пользователя и перейти к доставке;
- регистрационное письмо этого checkout-пути переведено на существующий
  `BestEffortMailService`: ошибка транспорта логируется, но авторизация и
  оформление продолжаются; бизнес-данные и содержание письма не менялись;
- обнаружено, что `CheckoutInlineLoginTest` не исполнялся PHPUnit 12 из-за
  legacy `/** @test */`; три метода возвращены в suite через префикс `test_`;
- regression: `CheckoutInlineLoginTest` и `BestEffortMailServiceTest` —
  `5 passed / 28 assertions`, включая симуляцию отказа SMTP;
- для повторной browser-проверки требуется удалить только незавершённого
  синтетического пользователя `43199` либо создать ещё одну тестовую учётную
  запись; заказ для пользователя `43199` не создавался;
- с подтверждения пользователя проверено отсутствие связанных заказов, после
  чего удалена только синтетическая запись `43199`; повторный checkout создал
  пользователя `43200`, авторизовал его и перешёл на `/lv/cart/delivery`;
- курьерская доставка с синтетическим адресом сохранена, итог рассчитан как
  `50 € + 5 € = 55 €`; открыт `/lv/cart/payment`, выбран `transfer`;
- после явного подтверждения пользователя выполнено финальное действие:
  создан заказ `18450`, браузер перешёл на `/lv/thanks?order_id=18450`;
- БД подтверждает пользователя `43200`, товар `50 €`, доставку `5 €`, итог
  `55 €`, `payment=transfer`, `payment_status=not_payed`, `status=watching`;
- регистрационное, клиентское и административное письма успешно отправились;
  SMTP-ошибка после внесённого исправления не повторилась;
- Synvolve webhook не сорвал заказ, но внешний сервис вернул `503` с ответом
  `Database is not ready`; задача внешней интеграции обновлена в плане как
  `BLOCKED`, бизнес-логика отправки не менялась;
- после фиксации evidence очищены активные журналы `storage/logs/laravel.log`
  и `storage/logs/mail.log`; оба файла проверены — размер `0` байт, поэтому
  следующие проверки не будут смешиваться со старыми ошибками;
- прямое открытие `/lv/cart` после заказа показывает пустой checkout без
  товарных позиций — корзина очищена;
- финальная regression-проверка: `5 passed / 28 assertions`, `git diff --check`
  проходит (только уведомления Git о будущем LF → CRLF).

### 2026-08-26 — финальная регрессия публичного frontend

- полный PHPUnit suite: `165 passed / 1137 assertions`, `1 skipped`, `5
  failed`; новых frontend failures нет;
- пять failures полностью локализованы в legacy
  `Tests\\Unit\\SynvolveWebhookServiceTest`: один тест пытается записать заказ
  без таблицы `orders` в SQLite, четыре ожидают conversation/lead-update методы,
  отсутствующие в текущей откатанной реализации Synvolve; production-код ради
  рассинхронизированных тестов не менялся;
- единственный skipped-тест — создание payment request через admin route,
  которая намеренно отключена до этапа Filament 5;
- отдельный публичный набор frontend/checkout/auth/account/payment прошёл:
  `103 passed / 570 assertions`; в него входят загрузки 100 MiB без
  application size limit, корзина, формы, кабинет и payment callbacks;
- `php artisan view:cache` — PASS; PHP syntax затронутых checkout-файлов — PASS;
- Composer обнаружил два PSR-4 предупреждения из-за регистра путей; файлы
  переименованы в `app/Mail/Sale_30_40_new.php` и
  `app/Models/Paymentinfo.php` без изменения классов/логики; повторный
  `composer dump-autoload --optimize` — PASS без предупреждений;
- свежие записи Laravel после тестов имеют только окружение `testing` и
  ожидаемые сценарии симуляции ошибок Paysera/SMTP; production runtime errors
  во время регрессии не появились; после фиксации результата `laravel.log` и
  `mail.log` снова очищены для следующей проверки.

### 2026-08-26 — готовность публичного frontend к запуску

- окружение: Laravel `13.25.0`, PHP `8.3.30`, production environment, MySQL,
  database sessions, SMTP; Composer manifest валиден;
- гостевые `/`, `/lv`, gallery reproduction, cart, login, register и sitemap
  без framework cache отвечают `200`; storage/framework, storage/logs и
  bootstrap/cache существуют и не имеют read-only атрибута;
- `public/storage` не является Windows reparse point, но проект явно обслуживает
  public disk через fallback route `/storage/{path}`. Тестовый файл записан в
  `storage/app/public`, получен по HTTPS как `text/plain` с содержимым `ok` и
  после проверки удалён;
- первый `artisan optimize` выявил четыре duplicate route names:
  `send_photo_form`, `hb.gallery.ram_search`, `kurpirkt`, `submit_bonuses`;
  ранним дублям назначены уникальные внутренние имена без изменения URL,
  методов, controllers и текущего разрешения основных route names;
- после исправления полный `artisan optimize` технически собирает config,
  events, routes и views; duplicate route names — `0`;
- runtime под route cache непригоден: локализованные `/lv/*` routes не попадают
  в cache и отвечают `404`. Route cache очищен; рабочая конфигурация оставлена
  без config/events/routes cache, Blade cache собран отдельно и URL снова `200`;
- `View [pages.index.footer] not found`, появившийся при полном optimize smoke,
  оставлен без исправления по ранее принятому решению пользователя о локальных
  View-сбоях; в рабочей uncached-конфигурации страницы открываются;
- добавлен regression-контракт уникальности route names и сохранения основных
  legacy URL; `Laravel13FrontendBootTest`, quick-order и basket contracts —
  `44 passed / 265 assertions`;
- deployment-риск: локальная среда объявлена `production`, но `APP_DEBUG=true`.
  Значение не менялось автоматически; перед реальным публичным запуском debug
  должен быть выключен.

### 2026-08-26 — production runbook и закрытие frontend RC

- создан `docs/production-frontend-runbook.md`: требования к PHP/Composer и
  web root, backup, `.env`, установка, миграции, cache policy, storage, HTTPS
  smoke, функциональная приёмка, логи и rollback;
- отдельно зафиксировано: сохранять production `APP_KEY`, не выводить секреты,
  не удалять существующий `public/storage`, не выполнять автоматический rollback
  БД и не создавать реальный заказ/платёж без подтверждения;
- production cache policy соответствует проверенному runtime: перед релизом
  `optimize:clear`, затем только `view:cache`; `route:cache`, `config:cache` и
  полный `optimize` пока запрещены;
- в `docs/README.md` добавлена ссылка на новый активный документ; исторический
  `docs/upgrade-laravel13-filament5` не использовался как control center;
- preflight evidence: Composer manifest валиден, platform requirements PASS,
  все миграции текущей БД имеют статус `Ran`, Laravel logs остаются пустыми;
- основной публичный frontend получает статус release candidate. Реальное
  production-развёртывание закрывается только после серверного checklist,
  `APP_DEBUG=false` и повторного smoke; незавершённые формы/painter/security
  задачи сохранены как post-RC backlog и не помечены выполненными.

### 2026-08-26 — системная ссылка public storage

- перед изменением проверены точные пути: `public/storage` уже отсутствовал,
  поэтому существующее дерево не удалялось и не перемещалось;
- `storage/app/public` сохранён: `12980` файлов, общий размер `3251020550`
  байт;
- выполнен `php artisan storage:link`; создана Windows Junction
  `G:\\OSPanel\\home\\viar13\\public\\storage` →
  `G:\\OSPanel\\home\\viar13\\storage\\app\\public`;
- junction подтверждена через PowerShell и `fsutil`: `ReparsePoint`, target
  совпадает с ожидаемым абсолютным каталогом;
- тестовый файл создан через `Storage::disk('public')`, одновременно виден в
  target и через `public/storage`, получен Apache по HTTPS с `200` и содержимым
  `storage-link-ok`; после проверки файл удалён;
- production runbook обновлён: штатное состояние — системная ссылка, fallback
  route остаётся только совместимостью.

### 2026-08-27 — оставшиеся публичные формы и painter UAT

- составлена фактическая матрица публичных форм и account/painter endpoints в
  `docs/frontend-mutating-routes-audit.md`; внешние письма, заявки, файлы,
  купоны и платежи во время безопасного browser UAT не отправлялись;
- `/lv/review`, `/lv/new/gift-card`, `/lv/stocks`, `/lv/new/account`,
  `/lv/new/orders` и `/lv/new/settings` проверены в авторизованной HTTPS-сессии;
- на подарочной карте обнаружено раннее подключение `jcf*.js` и
  `gift-card.min.js` до общего jQuery. Скрипты перенесены в общий layout после
  базового runtime, добавлена существующая зависимость
  `jquery.matchHeight.min.js`; повторный browser UAT видит 4 JCF-виджета и не
  фиксирует внутренних JS errors;
- обнаружены реальные `500 View [pages.stocks.modals] not found` на settings и
  `View [pages.stocks.stocks] not found` на stocks. Account/Stocks controllers
  переведены с прямого `env('THEME_RESOURCES')` на
  `config('theme.resource')`; обе страницы после исправления отвечают рабочим
  DOM без 500;
- клиентская страница заказа `18450` отображает чат и форму добавления файлов;
  отправка не выполнялась. Для роли `painter` добавлен изолированный feature
  smoke пустого `/new/orders`, который проходит без изменения production БД;
- итоговый релевантный набор `Laravel13FrontendBootTest`,
  `PublicQuickOrderValidationTest`, `PublicAuthRouteContractTest` —
  `28 passed / 130 assertions`; Blade cache успешно пересобран,
  `git diff --check` не обнаружил ошибок;
- внешний CookieYes пишет ожидаемую ошибку несовпадения зарегистрированного
  домена на `viar13.loc`; она не связана с Laravel и production URL.
- после фиксации разобранных browser/test ошибок активные `laravel.log` и
  `mail.log` очищены; следующая проверка начнётся с чистых журналов.

### 2026-08-27 — назначенный заказ в painter-кабинете

- в SQLite feature-контракт добавлен полностью синтетический клиент, painter,
  назначение `painter_orders` и заказ; production БД не изменялась;
- `/new/orders` для назначенного painter отвечает `200`, показывает номер
  заказа, формы рисунка/эскиза и чат художника с администратором;
- при fake mail проверены POST picture/sketch: оба сохраняют прежний JSON
  contract и переводят соответствующие статусы заказа в `2`;
- painter chat принимает фактический frontend payload `msg`, сохраняет его в
  `orders_chats` и возвращает успешный JSON без реального письма;
- из `UpdatePainterImageService` и `UpdatePainterSketchImageService` удалён
  Laravel-лимит `max:9000000`; допустимые MIME сохранены. Fake disk подтвердил
  сохранение JPEG с заявленным размером `100 MiB` и запись
  `order_painter_images`;
- public `php.ini` уже разрешает `256M`, то есть пример с исходником 100 MiB
  проходит web-server предел; CLI показывает собственные 2M/8M и не является
  HTTP-конфигурацией проекта;
- релевантный frontend-набор: `32 passed / 150 assertions`;
- найдено для следующего шага: painter POST-контроллеры пока не проверяют, что
  переданный `order_id` назначен текущему painter; invalid MIME также должен
  возвращаться как JSON 422, а не `RedirectResponse` внутри AJAX-flow.
- первый тестовый fixture дал разобранный `role=null` из-за mass assignment;
  fixture переведён на явную DB-вставку, повторный suite зелёный, а созданный
  этим прогоном `laravel.log` удалён. `mail.log` остаётся пустым.

### 2026-08-27 — authorization и ошибки painter mutations

- добавлен единый guard назначенного painter: роль должна быть `painter`,
  `order_id` — существующим integer, а пара user/order — присутствовать в
  `painter_orders`;
- guard применён к загрузке рисунка, загрузке эскиза и чату с администратором;
- при сверке активного Blade/JS обнаружены ещё два обходных chat-вызова:
  `new_send_client_painter_comments` и
  `new_send_admin_to_client_painter_comments`. Они также защищены; общий
  endpoint сохраняет клиенту доступ только к заказу с его `orders.user_id`;
- отрицательные тесты подтверждают `403` для неназначенного painter и чужого
  клиента до вызова storage, mail или DB insert; владелец заказа сохраняет
  прежний успешный JSON-контракт комментария;
- painter upload services больше не возвращают несовместимый
  `RedirectResponse`: обязательный массив файлов и MIME валидируются с JSON
  `422`, storage error также преобразуется в безопасную validation error;
- JS обеих upload-форм повторно включает кнопку и показывает полученное
  сообщение при `403/422`, без изменения разметки или дизайна;
- размер исходников не ограничен Laravel validation; ранее проверенный файл
  100 MiB продолжает проходить;
- итоговый релевантный набор: `36 passed / 182 assertions`; PHP lint трёх
  изменённых backend-файлов прошёл.

### 2026-08-27 — защита chatId и painter imageId

- `new/message_read` получил обязательный integer `chatId` и проверку области:
  клиент — `order_user_comments` только своего `orders.user_id`, painter —
  `orders_chats` только заказов из его `painter_orders`;
- `new/changeOrderPainterImageStatus` разрешён только роли `user`, владельцу
  заказа и изображению, реально принадлежащему этому заказу; допустимые статусы
  ограничены фактическими кнопками UI `4` (принять) и `5` (на доработку);
- подтверждён реальный jQuery-формат строковых параметров, включая
  `check_comment=true`; требование свежего комментария для статуса `5`
  сохранено;
- для трёх image-chat обработчиков добавлена сверка
  `order_painter_image_id -> order_id`, поэтому ID изображения другого заказа
  больше нельзя связать с доступным заказом;
- неиспользуемые публичным кабинетом `admin_to_client_message_read` и
  `admin_message_read` вызываются только legacy Voyager assets; до Filament 5
  роли `user/painter` явно получают `403`, backoffice permissions не
  переопределялись;
- JS status/read actions теперь показывают JSON error и не меняют DOM при
  отказе; внешний вид и status semantics не менялись;
- regression evidence: собственные сообщения/изображения обновляются, чужие
  остаются неизменными, mismatch image ID не создаёт комментариев и писем;
  релевантный набор — `40 passed / 210 assertions`, Blade cache собран,
  `git diff --check` без ошибок (только ожидаемые предупреждения LF/CRLF).

### 2026-08-27 — legacy set_all_painter_images пропущен

- подтверждён route `GET|HEAD new/set_all_painter_images` под `auth` и валидный
  PHP syntax контроллера;
- поиск по проекту не нашёл активных frontend callers: упоминания есть только
  в route и исторических audit-документах;
- метод является глобальным backfill: читает все `orders`, нормализует legacy
  URL рисунков/эскизов, создаёт или обновляет `order_painter_images`, затем
  вызывает `dd("ок")`;
- из-за массовой записи production/local real DB не использовалась для smoke;
  код не менялся по решению пользователя, задача исключена из обязательного
  frontend scope и зафиксирована как `NOT REQUIRED`;
- если механизм понадобится позже, точное следующее действие — отдельная
  идемпотентная CLI-команда/job с dry-run, правами и без HTTP GET.

### 2026-08-27 — финальный полный PHPUnit regression

- первый полный прогон после painter/account authorization:
  `179 passed / 1223 assertions`, `1 skipped`, `5 failed`;
- все failures просмотрены по exception и находились только в
  `Tests\Unit\SynvolveWebhookServiceTest`: один `no such table: orders`, четыре
  вызова методов conversation bot-status/lead-update, отсутствующих в текущем
  откатанном `SynvolveWebhookService`;
- unit test получил минимальную SQLite-схему `users/orders`, после чего
  действующий `notifyManagerMessageForOrderOrPhone` проходит и подтверждает
  приоритет телефона заказа;
- четыре теста отсутствующих методов теперь используют условный
  `markTestSkipped` с точной причиной. Методы не были искусственно добавлены,
  production Synvolve payload/delivery logic не менялась;
- повторный полный suite завершён без failures:
  **`180 passed / 1226 assertions`, `5 skipped`, `0 failed`**;
- skipped: четыре отсутствующих контракта откатанного Synvolve service и один
  admin payment-request test, route которого намеренно отключён до Filament 5;
- созданные suite записи в `laravel.log`/`mail.log` просмотрены: только
  `testing` evidence ожидаемых Paysera/SMTP/basket/Synvolve сценариев, новых
  production/frontend exceptions нет; после фиксации журналы очищены.
