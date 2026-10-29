# План миграции viar13 на Laravel 13

## ADM-AUD-13G — Мобильный индикатор SA и кнопки формы, 2026-10-29

- IN PROGRESS: зарезервировать место под плавающий SA-индикатор на мобильных
  страницах с доступным SA. Проверить footer EditOrders на 390px/320px и desktop
  на изолированной fixture под printing/lite_manager без сохранения заказов.
- Сохранить переход к диалогам, звук и перетаскивание. После проверки — журналы,
  копирование только изменённых non-md файлов с SHA256 и коммит без push.

## ADM-AUD-13G — Визуальная сверка полной карточки заказа, 2026-10-28

- DONE для read-only визуального поднабора: browser access восстановлен. Actual EditOrders fixture42,
  lite_manager/printing desktop и390px, sections/media/footer controls;
  просмотр без сохранения, затем snapshot9tables. Предыдущие тесты не повторять.
- Original full edit имеет общий доступ по edit_orders; payment request action
  дополнительно ограничен updateFinances. Все изменения только документации,
  если визуальная проверка не выявит дефектов. Коммит без push.
- Desktop2columns/mobile390singlecolumn, document375 без overflow; manager payment
  modal blank fields/close PASS, printing payment button0; gallery/footer просмотрены.
  Все9business tables неизменны, fixture остановлен. Скриншоты edit-* в roles-20261028.
- TODO ADM-AUD-13G — Мобильный индикатор SA и кнопки формы: плавающий badge
  частично перекрывает «Отмена» на edit footer390px. Следующее точное действие:
  обеспечить свободное место/размещение индикатора и повторить footer mobile.
  Общий13G IN PROGRESS, pixel parity всех states не заявляется.

## ADM-AUD-13G — Полная карточка: смена роли в открытой форме, 2026-10-28

- DONE автоматический поднабор: actual EditOrders printing↔lite_manager с сохранённым edit_orders:
  обычный save должен сохранить разрешённый draft и не повредить items/media.
- Shared payment request footer: после manager→printing открытое действие не
  должно вызвать сервис; прямое открытие под printing запрещено.
- Только SQLite :memory:/fake внешних интеграций. Документы, non-md export
  SHA256, commit текущей ветки без push. Browser visual остаётся незавершённой.
- Оба ordinary-save перехода сохраняют draft/items; открытый payment footer
  после manager→printing не вызывает сервис/не пишет order/request, новый mount
  не открывает action. 42tests789assertions PASS, Pint/diffcheck PASS.
  Только testPHP изменён, экспортирован с SHA256; production код прежний.
- Общий13G IN PROGRESS: следующий шаг — visual full edit desktop/mobile при
  доступном браузере; не заменять визуальную приёмку новыми повторными microgates.

## ADM-AUD-13G — Редактирование заказа под ролями печатника и менеджера, 2026-10-28

- IN PROGRESS: original order.blade.php/edit_admin_order/update_admin_order
  не содержат printing/lite_manager field guards. Не распространять ограничения
  финансовых inline actions списка на отдельную full edit форму без основания.
- Проверить actual EditOrders под обеими fixture ролями desktop/mobile, сохранение
  разрешённого комментария и отсутствие изменений соседних бизнес-данных;
  отзыв edit_orders после открытия должен блокировать save.
- Только isolated DB/disks + fake интеграций. Результаты/риски в журналы,
  non-md export с SHA256 при изменениях, commit текущей ветки без push.
- DONE автоматический поднабор: actual EditOrders printing/lite_manager save
  комментария, цены/оплата/items/media/messages прежние; revoked save/reopen403.
  57tests916assertions PASS, Pint/diffcheck PASS. Один testPHP экспортирован,
  SHA256 совпал. Production код не менялся.
- Browser визуальный прогон НЕ выполнен: инструмент сообщил Local browser use
  is disabled in Work settings. Стенд остановлен. Общий13G IN PROGRESS;
  следующий шаг — desktop/mobile EditOrders после восстановления browser access.

## ADM-AUD-13G — Фильтры и действия под ролями печатника и менеджера, 2026-10-28

- DONE для указанного поднабора: isolated browser сочетания tab+order filter для printing и
  lite_manager, несовместимые значения/сброс/смена вкладки; рабочие данные не менялись.
- Дополнить regression stale financial forms: роль изменена на printing после
  открытия, edit_orders сохранён, финансовые сервисы всё равно не вызываются.
- После проверки: журналы, экспорт только изменённых non-md с SHA256, коммит
  текущей ветки без push. Не закрывать всю матрицу13G несколькими комбинациями.
- Обнаружено: hidden financial actions имели общий update gate и не учитывали
  printing. Добавлен OrdersPolicy::updateFinances (edit_orders AND !printing),
  подключён к VR/payment/prepayment/invoice/payment request actions и их дублям.
  Production операции печатника сохраняют прежний update gate.
- Regression8open forms × permission revoked/role→printing с сохранённым
  edit_orders PASS: сервисы не вызываются, данные прежние. Gate73tests1242assertions,
  Pint/diffcheck PASS. ЧетыреPHP экспортированы, SHA256 совпали, md исключены.
- Browser printing ID43 заменяет tab scope по original order_filter; ID43+unpaid→0,
  ID42+unpaid→42, tab sent→43/reset. Manager completed→44, ID42+price999→0,
  ID42+price100→42, tab completed→44/reset. Все9business tables неизменны.
  Общий13G/ADM13 IN PROGRESS: остальная матрица/редактирование ещё не полностью приняты.

## ADM-AUD-13G — Заказы под ролями печатника и менеджера, 2026-10-28

- DONE для read-only ListOrders поднабора: отдельный full ListOrders стенд SQLite, actual printing/lite_manager
  role fixtures. Сверить original guards колонок/вкладок/финансов/действий,
  desktop/mobile390 и отсутствие writes. Рабочие роли не менять.
- После проверки: релевантный role/permission gate, evidence/журналы/коммит
  текущей ветки без push, экспорт изменённых non-md при необходимости.
- Printing:8колонок/6вкладок, без totals/цен/редактирования оплаты/назначений;
  labels/payment status/production controls сохранены. У печатника→только42.
  Lite_manager:8колонок/9вкладок/финансовые controls, без heading totals.
  Desktop/390px screenshot, document375 при viewport390, table/tabs scroll.
- 33tests829assertions PASS; все9fixture business tables неизменны.
  Production код не менялся, non-md export нет. Общий13G/ADM13 IN PROGRESS:
  ещё не все combinations/edit/actions/реальные роли приняты.

## ADM-AUD-13D — Настоящее перетаскивание файла в форму заказа, 2026-10-28

- DONE для single JPEG drag/attach: подготовлены синтетический PNG и отдельный snapshot SQLite/orders;
  ручной OS drag из Проводника выполняет пользователь (нет file-drag API).
- После фактического drop проверить FilePond/Livewire, сохранить во вторую
  позицию fixture904 и сравнить SHA256/соседние данные. Не заменять drop chooser.
- Fake HTTP/Mail, рабочие заказы не менять. Evidence/планы/коммит без push;
  копирование только изменённых tracked non-md с SHA256 при наличии.
- Пользователь перенёс свой JPEG11029bytes вместо PNG: trusted drop подтверждён,
  загрузка200/сохранениеfixture904 position2/немедленное превью PASS. SHA256
  сохранённого файла совпал с принятой temporary Livewire копией, исходный файл
  на диске пользователя отдельно не хешировался. Предыдущие пути, соседняя
  позиция, остальные поля/прочие orders неизменны; drag-verified.json PASS.
- Production код не менялся, tracked non-md для выгрузки нет. Общий13D/ADM13
  IN PROGRESS: этот JPEG attach не закрывает drag всех форматов/create/batch.
  Следующий шаг — оставшаяся browser матрица ролей13G.

## ADM-AUD-13D — Задержка перед началом загрузки файлов, 2026-10-28

- DONE для диагностики наблюдавшейся паузы: повторный isolated browser chooser PNG с временными видимыми
  метками input/FilePond/Livewire; сопоставить с серверным requests.jsonl.
- Production code не менять без воспроизведения/причины. Отдельные SQLite/disks,
  fake HTTP/Mail; новые файлы не сохранять в рабочие заказы.
- Следующее действие: измерить этапы addfile/process/startUpload и повторить
  без preceding invalid file. Уточнить, влияет ли ошибка очереди на паузу.
- Измерено: без preceding invalid file addfile→processfilestart86.057s,
  process→finish6.015s. При замедлении rAF5кадров/5s. После вывода Chrome
  пользователем вперёд: rAF~55–60fps, addfile→process1.684s, finish6.956s.
  Для этого стенда пауза связана с фоновым throttling/browser rendering,
  не с дополнительной задержкой сервиса orders. Production workaround не нужен.
- Сохранение в заказ отменено; verifier подтвердил прежнее состояние и SHA256.
  Evidence timing-first.txt/timing-foreground.txt/timing-foreground.png в
  storage/app/audit/upload-recovery-20261028. Общий13D IN PROGRESS:
  OS drag/drop ещё без evidence; следующие проверки только на isolated fixtures.

## ADM-AUD-13D — Ошибка загрузки и восстановление формы вложений, 2026-10-28

- DONE для отказа/recovery через chooser: уточнение ID — upload/drag относятся к13D,13G остаётся матрицей ролей.
  Отдельная SQLite/локальные disks, fake Mail/HTTP. Проверить browser reject
  неподдерживаемого файла и следующую успешную загрузку/сохранение PNG в нужную
  позицию; соседняя позиция и остальные заказы должны сохраниться.
- Реальный OS file drag/drop пока не подтверждён: extension API даёт chooser,
  read-only evaluate, без file-drag API. Не подменять этот сценарий DOM events.
- После выполнения: evidence, планы, копирование изменённых non-md с SHA256,
  коммит текущей ветки без push.
- Chrome: rejected.exe отклонён «Файл неверного типа», удалён из очереди;
  PNG177bytes загружен/сохранён во вторую позицию без reload. SHA256 совпал,
  первая позиция/остальные поля/прочие orders неизменны; changed columns items,
  updated_at. 28tests340assertions PASS. Код не менялся, non-md export нет.
- DONE диагностика, см. запись выше — задержка старта загрузки на isolated стенде: перед _startUpload была
  пауза; причина не установлена. Не объявлять исправленной/не переносить вывод
  на production. Evidence storage/app/audit/upload-recovery-20261028/requests.jsonl.
- Общий13D IN PROGRESS: пауза старта проверена сравнением foreground/throttling;
  OS drag/drop остаётся без browser evidence.

## ADM-AUD-13F — Заполненные чаты с изображениями: визуальная проверка на компьютере и телефоне, 2026-10-28

- DONE для browser read-only поднабора: отдельная SQLite-копия синтетического стенда, fake HTTP/Mail,
  локальные изображения. Проверить настоящие Filament client/painter окна:
  разные потоки, превью/ссылки, chronology, composer desktop/mobile390.
- Рабочая БД и внешние отправки исключены. Зафиксировать ограничения/evidence,
  обновить журналы, коммит текущей ветки без push; non-md экспорт при изменениях.
- Chrome desktop/390px: client general+painting903+sketch901, отдельные history
  и composer, оба локальных PNG loaded; painter3messages/type labels/General.
  Закрытие окон PASS; orders3/media2/clientmessages5/paintermessages3 неизменны,
  SHA256 двух PNG совпадают. Evidence storage/app/audit/chat-media-20261028/verified.json.
- Код не менялся, non-md для выгрузки нет. Общий ADM-AUD-13 IN PROGRESS:
  следующий шаг — оставшиеся browser upload/drag сценарии на isolated fixtures.

## ADM-AUD-13F — Чаты художника и вложения: проверка оставшихся сценариев, 2026-10-28

- DONE для автоматического поднабора: сверены заполненная история художника и поведение привязанных
  изображений с original order_chat/client_chat. В original painter composer
  активен только «Общий», labels наброска/картины сохранены для истории.
- Проверить isolated Livewire: изображение удалено, перенесено в другой заказ
  или изменило тип после открытия формы; draft сохраняется, сообщение/уведомления
  не создаются. История художника: chronology, author/type flags и отсутствие
  побочных read writes при открытии.
- Рабочую БД не менять. После проверок обновить evidence, скопировать только
  изменённые non-md файлы с SHA256 и сделать коммит без push.
- Результат: шесть stale-image случаев (sketch/painter × deleted/moved/retyped)
  блокируют запись, сохраняют draft и не отправляют chat-updated; история
  художника проверена по времени/ID/автору/type и полной неизменности строк.
  63tests/636assertions PASS, Pint/diffcheck PASS. Два изменённых test PHP
  скопированы в C:\OSPanel\domains\viar\viar13, SHA256 совпали.
- Production code не менялся. Browser populated-media прогон в этой задаче
  не выполнялся; общий ADM-AUD-13F IN PROGRESS. Далее — изолированный browser
  просмотр заполненных media threads desktop/mobile и оставшиеся upload/drag ветки.

## ADM-AUD-13F — Сверка окон чатов тестового заказа №18451, 2026-10-28

- DONE для read-only сверки №18451: парное открытие client/admin/painter/SA окон, без отправки,
  изменения bot mode или ручных отметок прочтения. Рабочие другие заказы не трогать.
- Client оба test/test2 с одинаковыми датами; admin/painter пустые. Mobile390:
  все четыре окна, закрытие/reopen и доступность controls проверены. SA composer
  доступен через внутреннюю прокрутку, Handoff выключен. Пустые ветки не закрывают populated UAT.
- SA история/кнопки/Handoff совпадают по данным, mode original N/A против new
  PAUSED объяснён source audit: original читает orders.sa_bot_mode=NULL,
  новая панель читает связанный sa_conversations.bot_mode=paused. Original
  conversation page также читает conversation. Корректное состояние не менять.
- Проверки: 106 tests / 992 assertions PASS, JUnit storage/logs/adm-aud-13f-chat-20261028.xml.
- Общий ADM-AUD-13F IN PROGRESS: этот прогон не подтверждает populated artist/media,
  все роли и реальные отправки. Следующее действие: оставшиеся ветки художника и
  вложений на изолированных fixtures с fake внешних интеграций.
- Сдача: документация/коммит без push; non-md файлы только при реальных изменениях
  экспортировать в C:\OSPanel\domains\viar\viar13 с SHA256.

## ADM-AUD-13B/E/G — Итоги вкладок и мобильная проверка, 2026-10-28

- IN PROGRESS: browser original express77/839 против new77/4101.56.
  Source OrdersController Laravel6:471–525,619–623 суммирует страницу для
  express/printing/sent_today, остальные вкладки — всю выборку. Перенести этот
  scope сводки, проверить pagination/sort/filter override, не менять цены заказов.
- Completed first13IDs совпали, счётчики original14863/new14817. Source original
  LEFT JOIN vr_numbers допускает дубликаты; точные46повторов требуют SELECT-аудита.
  Уникальные заказы новой панели сохранять, не размножать ради старого счётчика.
- Express последние2строки имеют одинаковый бизнес-приоритет; comparator original
  возвращает0. Не имитировать нестабильный usort PHP7 без бизнес-основания.
- Новая mobile вкладка реально390px/doc375px/table1663px/container343px overflow:auto.
  Проверить payment cancel/reopen и firm disclosure без сохранения. Role matrix
  и все товарные типы этим шагом не закрываются. Viewport сбросить после проверки.
- DONE для scope итогов и указанной mobile проверки: combined58tests/976assertions
  PASS, Pint/diff-check PASS; JUnit adm-aud-13-summary-20261028.xml. Пустая сумма
  payment modal18451 подтверждена screenshot, Cancel; firm disclosure открыт/закрыт
  без Save. Viewport reset и возврат обоих пользовательских tabs к current77/4101.56.
- Точные completed duplicates подтверждены SELECT:14817/702650.85 unique против
  14863/704630.62 legacy join.4PHPфайла экспортированы без.md, SHA256 совпали.
  Commit без push; общий ADM-AUD-13 IN PROGRESS (остальные роли/товары/окна).

## ADM-AUD-13B/E — Парная браузерная сверка с оригинальной логикой, 2026-10-28

- IN PROGRESS: обе авторизованные админки доступны. Current 77/4101.56 EUR,
  production 5/456 с одинаковыми ID/порядком; unpaid 17/1059.8, sent 1/65.
- Client email test fixture: один заказ18451/22; phone-only: одинаковые
  18451,15057,10307,6748, всего4/106. Пустые printing/sent_today/new_recently
  совпали; непустые ветки этими browser-проверками не подтверждены.
- Найдено отличие payment request: оригинальный quick modal не подставляет сумму
  заказа, ввод ручной. Привести только createPaymentRequest к пустым amount/purpose,
  сохранив обязательность и валидируемое создание. Предоплату не менять.
- Mobile original viewport390 проверен; новая вкладка осталась2560, поэтому
  мобильную новую страницу не считать проверенной. Viewport reset выполнен.
  Далее Windows Computer Use остановлен защитой URL confidence; UI прекращён.
- DONE для указанного browser/source gate и исправления initial state:
  actual row/edit createAction и column action пустые amount/purpose. Required/min
  и успешное создание с явной суммой проверены:53tests/812assertions PASS,
  Pint/diff-check PASS. Три PHP-файла экспортированы без .md с проверкой SHA256.
  Весь ADM-AUD-13 остаётся IN PROGRESS; push не выполняется.

## ADM-AUD-13 — Оставшаяся матрица заказов, 2026-10-28

- IN PROGRESS: повторная сверка фильтров/вкладок/ролей и всех товарных веток с
  оригиналом Laravel 6. Проверки записи только в isolated fixtures.
- Отдельно проверить доступность browser QA desktop/mobile и окон; фиксировать
  фактические ограничения, не закрывать весь этап только по PHP-тестам.
- Сдача: коммит в текущей ветке без push; файлы задачи кроме `.md` скопировать
  в `C:\OSPanel\domains\viar\viar13` и проверить SHA256. Правило добавлено в AGENTS.md.
- Подтверждены и исправлены: production default sort `created_at DESC` вместо
  `id DESC` (original OrdersController:430–440); gallery `is_gall_with_img` и
  gift `has_gift` требуют `== 1`, как original orders/items.blade.php.
- Добавлена непустая матрица 9 tab scopes × 3 роли × default/ASC/DESC/filter scope;
  отдельные role-presentation tests проверяют скрытые вкладки/финансовые поля.
  Товарная матрица расширена деталями портрета, canvas/modular, oil и gift.
- Browser QA не выполнена: Computer Use остановлен защитой URL confidence при
  получении состояния Chrome. Не повторять UI действия в этом проходе. Весь
  ADM-AUD-13 остаётся IN PROGRESS до desktop/mobile/modal visual acceptance.
- DONE для source/isolated gate: 75 tests / 1089 assertions (filters/tabs/roles/
  products) + 141 / 1361 (actions/chats/artist/lifecycle), оба exit 0.
  Всего 216 / 2450; Pint и diff-check PASS. Это целевой, не полный suite.
- Экспорт 4 изменённых non-Markdown файлов выполнен в указанную папку с SHA256
  каждого файла. Исходный destination git status был чистым, посторонние файлы
  не удалялись, `.md`/env/storage/vendor не копировались. Push не выполняется.

## DEV-AUTH-01 — вход на dev.viarcanvas.com, 2026-10-23

- IN PROGRESS: онлайн `/filament/login` возвращает 200, но script
  `/livewire-1fe4a532/livewire.js?id=4d123128` возвращает 404. Все восемь
  проверенных остальных JS возвращают 200; Cloudflare Rocket Loader меняет type scripts.
- Исправление: публиковать Livewire assets после Composer install/update, добавить
  нативный type=password в login с сохранением кнопки показа. Проверить feature tests.
- Следующий шаг: применить deployment-команды из docs/dev-login-deployment.md на
  сервере, исключить админку из Rocket Loader, проверить JS 200 и вход пользователем.
- Риск: без доступа к серверу конфигурация Nginx и успешный вход не подтверждены.

Обновлено: 2026-09-30.

## Сверка исходных коммитов с `9a72e4b9` по `8101a9df` (2026-09-25)

Статус переноса: **IN PROGRESS**. Работа ведётся по группам ниже; решение
`TODO` меняется на `DONE` только после реализации и проверки в viar13.

Источник: `C:\OSPanel\domains\asoft\viar` (`main`, HEAD `8101a9df`), цель:
этот репозиторий (`codex/laravel13-frontend`, HEAD при аудите `2d88b9e`).
Коммит `c1805f0b` уже адаптирован в `65271d6`. Следующие пункты относятся и к
публичному сайту, и к действующей Filament-панели, если затронуты оба.

| Исходный коммит | Решение | Задача |
| --- | --- | --- |
| `9a72e4b9` | DONE, админка | `OrderVenipakLabelService` берёт `delivery.phone` → `delivery.payer_phone` → профиль. Тест порядка fallback: `4 passed / 17 assertions` в suite Venipak. |
| `2d2807df` | НЕ ПЕРЕНОСИТЬ | Только запись результатов production crawl в legacy-документацию. |
| `f035b623`, `7acdac59` | НЕ ПЕРЕНОСИТЬ | Функция business analytics/payment-mail outbox полностью отменена следующим revert; итоговый diff пары пуст. |
| `98c8f6d9` | IN PROGRESS, сайт | GeoIP, устранение повторных запросов/N+1, приоритет hero image и отложенная HEIC-конверсия перенесены. Нужна browser/UAT-проверка загрузки HEIC и главной. CAPTCHA не включать: текущий серверный контракт регистрации её не требует. |
| `a084a89f`, `381ba34d` | IN PROGRESS, сайт + данные | Поле `canvas_slider.hero_subtitle`, модель, публичный шаблон и узкий Filament-редактор переводов перенесены; миграция применена. Livewire-тест записи EN/RU прошёл. Остаётся browser/UAT проверки отображения на живой странице. |
| `e19b2a38` | НЕ ПЕРЕНОСИТЬ ОТДЕЛЬНО | Кроме промежуточного `canvas.hero_subtitle`, изменения invoice-переводов — перестановка ключей/форматирование без нового бизнес-правила. |
| `1835e259` | NOT REQUIRED | Ленивая CAPTCHA несовместима с уже принятым контрактом Laravel 13: серверная регистрация не требует CAPTCHA. Возвращать её можно только отдельной согласованной задачей вместе с серверной проверкой. |
| `44982d52` | DONE, сайт | Неблокирующий Font Awesome и WebP изображения партнёра перенесены; HTTPS главной вернул 200, внешний WebP URL — 200 `image/webp`. |
| `0d831842` | IN PROGRESS, сайт | Inline-ошибка отсутствующего canvas-фото, фокус и переводы перенесены; нужен browser/UAT тест выбора большого файла и добавления в корзину. |
| `f100b837`, `315594c4`, `fe3bb616`, `2ba9f15a` | DONE, сайт | Финальные SEO Analytics pages/routes/локали перенесены; feature-тест 1 passed/9 assertions, Blade cache и route-list успешны. |
| `22028e9b`, `8101a9df` | IN PROGRESS, сайт + админка | Registry/inventory/model/миграция, финальные исключения `orders/`/`uploads/`, все 339 ALT Blade-патчей и Filament-очередь с правами/действиями перенесены; обе миграции применены. Одноязычный dry-run scan успешен. Нужны полное сканирование/применение и browser UAT. |

Порядок: (1) Venipak phone; (2) canvas upload + GeoIP/JS совместимость;
(3) slider schema/content; (4) SEO Analytics pages; (5) ALT frontend inventory;
(6) независимые performance-правки. Перед каждым этапом сверять фактические
таблицы/данные и текущий код Filament, не переписывать уже выполненные задачи.
Все пункты TODO требуют реализации и отдельного evidence; этот аудит не считает
их выполненными.

Текущие найденные задачи ALT: оптимизировать полный многоязычный runtime-скан
(одноязычный `--limit=1` на 128 МБ прошёл, полный `--limit=1` занял более
нескольких минут и был прерван без записи); проверить реальные права
`browse/edit_alt_suggestions` в БД; проверить применение ALT к записи на
изолированной БД; довести Filament ALT-очередь до паритета с прежним Voyager UI
(bulk moderation, WebP-фильтр/статистика, привязка к BREAD-записи там, где она
ещё актуальна). До этих проверок не считать ALT-этап завершённым.

## Название задачи

**Миграция viar13 на Laravel 13 — запуск и стабилизация публичного фронтенда**

## Этап админки Filament 5

### ADM-FIL-029 / ADM-FIL-022 — Динамический перенос Voyager BREAD (IN PROGRESS, 2026-09-25)

- [DONE, 2026-09-30] «Gallery Items: таблица и редактор»: сверены открытые Chrome-вкладки `gallery-items?type=2` и Voyager `/974/edit`. Под поиском добавлен отступ 24 px; горизонтальный скролл ограничен таблицей, действия закреплены справа внутри контейнера. Добавлен маршрут `/filament/bread/{type}/{record}/edit` с сохранением фильтра `?type=2` при возврате. Все 13 вкладок товара 974 формируются из реального `data_rows.details.tab_title`, включая отношения и Spatie media. В Chrome проверены открытие, цены/размеры, фильтры и медиа; реальные записи не изменялись. Изолированные тесты сохранения разных вкладок, EN/RU, связей и прав: 20 passed / 96 assertions.
- [DONE, 2026-09-30] «Gallery Items: клонирование, SEO и defaults»: добавлена иконка клонирования товара с правами add/edit и переходом в редактор копии. Поведение оригинального `monstrex/voyager-extension` сохранено: replicate, reset image/multiple_images/file, suffix title/name/slug/key; pivots, translations и Spatie media не копируются. SEO-кнопка использует существующий `SeoMetaGenerator`, edit permission и транзакционную запись всех поддерживаемых локалей; остальные несохранённые поля формы сохраняются в редакторе. Defaults читаются из `data_rows.details.default` только для NULL. Изолированные тесты: 26 passed / 120 assertions; Chrome: SEO-кнопка, index/follow и 25 иконок клонирования отображаются, все четыре действия внутри таблицы. Внешний API и запись реальных товаров не выполнялись.
- [DONE, 2026-09-30] «Медиа BREAD: сортировка, multiple upload и замена»: для adv_media_files добавлены стрелки порядка (с транзакцией и проверкой владельца/коллекции), загрузка до 20 изображений за раз (10 MB каждое), замена с сохранением свойств/порядка, размер файла и номер. Upload-состояние изолировано по полю; при неверной загрузке очищается для повторного выбора. Dropdown extra fields проверяются по metadata. Chrome: товар 13, вкладка «Таб - цены/размеры», 8 файлов и заполненные размер/цена, первый/последний пределы отключены. Изолированные тесты: 26 passed / 150 assertions, включая повтор после ошибки, неверный MIME, замену/очистку старого файла и защиту чужих медиа.
- [DONE, 2026-09-30] «Медиа BREAD: массовое удаление и выбор из библиотеки»: adv_media_files имеют чекбоксы, выбор всех/снятие выбора, счётчик и удаление выбранных с подтверждением. Все ID проверяются по текущей записи/коллекции до удаления. media_picker показывает папки/base_path и существующие изображения (24 на страницу), учитывает allowed MIME и min/max; выбор сохраняется в прежний JSON путей. Новые пути повторно проверяются при save, содержимое изображений проверяется фактически. Chrome: выбор/снятие 8 медиа, библиотека gallery-items → BeautyART и выбор существующего файла; реальная запись не сохранялась. Tests: 28 passed / 179 assertions, view:cache и diff-check успешны.
- [DONE, 2026-09-30] «Предпросмотр изображений BREAD»: BreadFileUpload кодирует сегменты публичного URL до sanitizeUrl Filament. Причина [object Object] — null URL у имён с пробелами. Исходные DB-пути не меняются. Chrome: товар 13, Общее, библиотека BeautyART, выбор файла, закрытие, Main/Общее: 43 карточки файлов, 0 placeholder. Tests: 29 passed / 188 assertions; view:cache и diff-check успешны. Снимок storage/app/testing/gallery-items-preview-2026-09-30.png.
- [TODO, 2026-09-30] «Медиа BREAD: оставшийся паритет»: аудит/очистка сохранённых исходников с проверкой ссылок других записей. Коллекция GalleryItem `new_work_photos` отсутствует в data_rows: проверить исходный UI/публичный код перед добавлением метаданных. Полный паритет BREAD остаётся IN PROGRESS.
- [IN PROGRESS, 2026-09-25] Подзадача «Проверка всех 133 типов»: поимённый read-only аудит реальной БД и рендеринга форм в `docs/filament_bread_audit_2026-09-25.md`. У 130 типов нет ошибок в применимых проверках; открытие общей формы проверено у 127, три типа используют отдельные ресурсы. Найдены 2 исторических типа без таблиц (`a-collag-faq`, `canvas`) и `payment-info` без единой настройки `data_rows`. Недоступные таблицы исключены из навигации; создание/изменение пустой формы заблокировано. Изолированные тесты CRUD, переводов, связей и медиа прошли. Остались browser-UAT и проверки специальных ресурсов; live-аудит не выполняет запись и не доказывает сохранение всех 133 типов.
- [DONE, 2026-09-30] Блокер доступа браузером снят: открытые вкладки Chrome `viar13.loc` и `viarcanvas.loc` доступны; сравнение Gallery Items выполнено. Ранее неудачная shell HTTP-проверка 2026-09-25 остаётся историческим evidence.
- Согласовано: все 133 `data_types`; типы полей, связи, меню и стандартные BREAD-действия читаются из реальной БД при работе панели. Настройки самих полей через Filament пока не редактируются. Новое поле существующей таблицы можно включить через `data_rows`; новый физический столбец требует миграции. Отдельная задача редактора структуры полей остаётся TODO и должна предусмотреть права, проверку схемы и защиту служебных/платёжных таблиц.
- Переводы: базовый EN в основной записи, RU/LV/EE/LT/PL/DE в `translations`; переключатель языка без перезаписи остальных. UK остаётся отдельным требованием CRM/SA.
- [DONE, 2026-09-25] Сверка с Voyager на `gallery-items?type=5`: переключатель EN/RU/LV/EE/LT/PL/DE добавлен над общей browse-таблицей; переводы текущей страницы загружаются пакетно. Колонки берутся из всех `data_rows.browse` с `display_name`, включая валидные отношения и коллекции медиа; ограничение `take(5)` убрано. В карточке при RU непереводимые поля остаются видимыми. Действия списка представлены иконками с доступными названиями, закреплены справа. Порядок колонок определяется текущим `data_rows.order` viar13.
- [DONE, 2026-09-25] Подзадача «Отступ кнопок сохранения»: в общей форме BREAD добавлен верхний отступ 24 px перед кнопками «Сохранить» и «Отмена».
- [DONE, 2026-09-25] Подзадача «Отступ после языков»: добавлен нижний отступ 24 px после переключателя языка в просмотре, редактировании и списке общей BREAD-страницы.
- [DONE, 2026-09-25] Подзадача «Пагинация BREAD»: вместо неоформленного вывода Laravel добавлены компактные кнопки Filament, номера страниц и диапазон записей; переход 1→2 проверен на блоге (47 записей).
- [DONE, 2026-09-25] Подзадача «Изображения BREAD»: по сравнению с Voyager для `blog-posts?page=2` общая таблица и просмотр показывают миниатюры полей `image`, `multiple_images`, `media_picker`, изображений в `file` и коллекций `adv_media_files`; данные Spatie для страницы загружаются пакетно.
- Подзадачи: [DONE для снимка 2026-09-25] инвентаризация типов и совместимости (131/133 доступны, 2 исторических типа без таблиц; матрица в `docs/filament_bread_inventory.md`); [IN PROGRESS] универсальный редактор и навигация (список, поиск, пагинация, просмотр, базовые поля, переводы); [IN PROGRESS] связи и медиа (12 `belongsTo`, 16 `belongsToMany`; `image`/`file`/`multiple_images`/`media_picker`, Spatie `adv_media_files` с загрузкой и extra properties); [DONE] редактор `menu_items` с EN/RU/LV/EE/LT/PL/DE, правами и защитой от циклов; [DONE] редактор `ltm_translations` с переключением языка и публикацией PHP/JSON-словарей, включая отсутствующие locale-ключи; [DONE] значения 27 `settings` с правами `browse/edit_settings`; [IN PROGRESS] тесты и browser-UAT; [TODO] прочие нестандартные Voyager actions.
- Уже существующие Filament Resources (`orders`, ALT, `canvas-slider`) имеют приоритет и не заменяются общим редактором. Не включать обход их бизнес-логики через BREAD.
- Текущие риски: часть `relationship.details.model` содержит legacy `App\\...`; в меню есть прямые `/admin/...` ссылки; metadata может ссылаться на несуществующие классы/поля. Каждую такую конфигурацию проверять до записи, фиксировать в отчёте и не открывать опасный write-path.
- Следующее действие: перенести оставшиеся специальные маршруты меню (медиа и служебные действия) по ADM-FIL-014/024/028; проверить записи и права остальных разделов; завершить browser-UAT создания/изменения/удаления на изолированной БД. 17 пунктов без URL уже представлены как группы; конструктор меню имеет собственный редактор.

### ADM-FIL-010 — Значения новых позиций и переключение скидок

- [DONE для этого подшага, 2026-09-05] Сверено с оригинальным order_create.blade.php:
  каждая новая позиция получает canvas=2, G0, decoration=5, V0, B0.
  Ввод положительной скидки EUR сбрасывает %, и наоборот.
- Перенести defaults компонентов Repeater и реактивное переключение скидок;
  серверный запрет одновременных скидок оставить. Проверки без общей БД.
- Реализовано: Livewire 2/16; targeted 19/133, exit 0; full PHPUnit 420/2929,
  6 skips, failures нет, exit 1 из-за прежних 11 discovery warnings.
  Pint/diff-check OK. Реальных заказов и отправок этот шаг не касался.
- Остальной backlog формы: фильтрация доставки по стране, отображение доступных
  бонусов, округление итогов и полноценный browser UAT; весь этап не закрывать.

### ADM-FIL-010 — Производственные имена исходников

- [DONE для новых загрузок, 2026-09-05] Оригинал Laravel 6 использует
  `Orders::generateImageName` после формирования корзины: количество, размер,
  экспресс, доставка/страна, холст, лак, упаковка, мазки, багет и ориентация.
- В новом сервисе сохранять новые загрузки сразу под итоговым именем через
  существующий генератор. Не переименовывать и не скачивать старые/удалённые файлы.
- Сохранить legacy суффикс `-1`, `-2` для нескольких файлов и обход занятых имён.
  Проверить коды, коллизии и rollback на SQLite/fake storage/mail/HTTP.
- Реализовано и проверено: targeted 17/117, exit 0; full PHPUnit 418/2913,
  6 skips, failures нет, exit 1 из-за прежних 11 discovery warnings.
  Pint/diff-check OK. Следующее: визуальная приёмка формы; весь ADM-FIL-010 открыт.

Оригинал Laravel 6: `C:\OSPanel\domains\asoft\viar` (путь подтверждён
пользователем 2026-09-05). Сверять бизнес-логику там, read-only; изменения
миграции вносить только в `G:\OSPanel\home\viar13`.

### ADM-FIL-010 — Соответствие копирования заказа бизнес-логике Voyager

- [DONE для client-only prefill, 2026-09-05] Оригинальный Laravel 6
  `OrdersController::create` передаёт в
  `vendor.voyager.order_create` только пользователя. Возвращаем client-only
  prefill: старые оплата, бонусы, позиции, скидки и реквизиты не переносятся.
  Это заменяет ранее записанное предположение о полном copy-prefill.
- Подтверждённый fallback `delivery.phone`: телефон получателя либо заказчика.
- Подтверждено в controller и Blade оригинала; targeted 16/107, exit 0;
  full 417/2903, 6 skips, без failures, exit 1 из-за 11 прежних discovery warnings.
- Проверки — только SQLite/fake integrations. Имена production-файлов требуют
  отдельного шага: legacy после создания вызывает `renameUploadsPhoto`, новый
  сервис пока использует предварительное имя. Удалённые файлы не скачивать.

### ADM-FIL-010 — Безопасное сохранение исходников при создании заказа

- [DONE, 2026-09-05] Устранена коллизия имён между позициями сквозным индексом
  исходника. Проверены отказ для `.php` и очистка файлов при откате транзакции.
- Сохранить каталог `orders`, legacy-префикс и `orig_images`; проверки только
  на SQLite in-memory и fake storage/mail/HTTP, без изменений общей базы.
- Отдельно остаются приёмка формы и сверка бизнес-семантики копирования с Voyager.
- Проверки: PHP 8.4.1 targeted 9/58 (exit 0); полный PHPUnit: 416 tests,
  2892 assertions, 6 skips, без test failures, но exit 1 из-за 11 warnings
  `No tests found` в существующих test classes. Pint и diff-check пройдены.
- [TODO] Отдельно восстановить discovery старых тестов PHPUnit (11 классов,
  включая BlogIntegrationReadApiTest с docblock `@test`); полный suite пока
  не считать полностью зелёным. Эти классы в текущем шаге не менялись.

### ADM-FIL-010 — Создание и копирование заказа менеджером

- [IN PROGRESS, 2026-09-01] Проверен legacy route/controller/Blade
  `create_admin_order`: форма содержит клиента, оплату, доставку, скидки,
  юридическое лицо и повторяемые позиции с файлами.
- Legacy `from_order_id` фактически предзаполняет только пользователя: передача
  и разбор заказа в Blade закомментированы. С 2026-09-05 в Filament также
  переносим только клиента; прежнее предположение о полном копировании отменено.
- Создание должно выполняться валидируемым сервисом и одной DB-транзакцией;
  пользователь, списание бонусов и заказ не могут остаться частично созданными.
- Регистрационное письмо, письмо о заказе и Synvolve webhook остаются только
  opt-in и выключены во время UAT. Browser-проверка — только предзаполнение из
  №18451; создание дополнительно проверяется на изолированной тестовой БД.
- [IMPLEMENTED, 2026-09-01] Добавлена custom create page, доступная по
  `add_orders`, поиск существующего клиента, полный набор legacy-полей,
  повторяемые позиции и загрузка исходников. Итоговые production filename-коды
  ещё требуют сверки с legacy renameUploadsPhoto.
- Общий `OrderItemPresentationService` теперь используется и Voyager, и
  Filament для canvas/box/decoration/express presentation; старые payload keys
  (`sizeId`, `savedImage`, `formId`, effect/execution и другие) не теряются.
- Реальный UAT: из №18451 временно создан №18452, обе админки подтвердили
  admin-order, PayPal, VIAR pickup, Canvas 40x40, исходник, V2 и express. Запись
  удалена, AUTO_INCREMENT возвращён к 18452, count/max/bonus восстановлены.
- [OPEN] Перед DONE визуально принять форму и отдельно проверить создание нового
  клиента и реальную загрузку файла; эти ветки уже покрыты isolated tests.
- Проверки шага: targeted 25/127; полный suite 402 passed / 2860 assertions /
  6 baseline skips. Pint, Blade cache и diff-check прошли.

### ADM-FIL-003 — Финальная попарная сверка восьми колонок

- [IN PROGRESS, 2026-09-01] После reload №18451 попарно сверены все восемь
  колонок. Data/actions parity закрыт для Номер, Оплата, Пользователь,
  Получатель, Товар, Комментарии и Художник; в «Заказе» остаётся create/copy.
- Исправлены верхнее выравнивание строки, payment/delivery indicators и цвета
  client/history actions. Пустая legacy-кнопка печати для отсутствующего label
  и повтор raw-date/address намеренно не воспроизводятся как нерабочие дубли.
- [DEPENDENCY] Для полного закрытия страницы выполнить **ADM-FIL-010 — Создание
  заказа менеджером**, включая корректное копирование из текущего заказа.
- Browser audit был read-only; №18451 и внешние системы не изменялись. Targeted
  18/87; full suite 395/2817, 6 baseline skips.

#### ADM-FIL-003 — Выравнивание содержимого строки по верхнему краю

- [DONE, 2026-09-01] Устранено вертикальное центрирование коротких
  Filament-ячеек; все восемь колонок должны начинаться сверху, как в Voyager.
- CSS ограничен строкой orders table через lifecycle marker и не затрагивает
  остальные Filament tables; browser screenshot подтвердил результат.

#### ADM-FIL-003 — Индикаторы оплаты и доставки в «Получателе»

- [DONE, 2026-09-01] Возвращено отдельное вертикальное размещение payment и
  delivery icons с legacy max-width 60px без общего ограничения высоты.
- PayPal + pickup заказа №18451 визуально проверены после reload.

### ADM-FIL-003 — Финальная сверка списка: сортировка колонки «Номер»

- [DONE, 2026-09-01] Видимая колонка «Номер» сортирует `orders.id` asc/desc,
  как заголовок Voyager; скрытая дублирующая колонка не используется.
- Targeted display tests: 11 passed / 43 assertions. Browser после reload
  подтвердил action-кнопку сортировки в заголовке; БД не изменялась. Полный
  suite: 393 passed / 2809 assertions / 6 baseline skips.

### ADM-FIL-003 — Колонка «Заказ»: статусы, сроки и действия

- [DONE, 2026-09-01] В одну parity-колонку перенесены status select/timeline,
  связанные активные заказы, номер/дата, delivery deadline/days/urgency,
  исходящая накладная и Voyager-набор действий.
- Поиск связанных активных заказов повторяет legacy matching: `user_id`, email,
  phone и payer_phone доставки; снимок активных заказов загружается один раз.
- Статус и дата доставки изменяются через `edit_orders`, валидацию, row lock и
  транзакцию. Legacy sent/pickup emails сохранены, но UAT закрыт opt-in
  `ADMIN_ORDER_STATUS_NOTIFICATIONS_ENABLED=false`.
- Удаление требует `delete_orders`, подтверждения и транзакционно возвращает
  использованные бонусы. Browser-delete намеренно не запускался. Edit, client
  modal, related/all-user filters и outgoing label работают без Voyager routes.
- «Создать заказ» теперь ведёт на custom page ADM-FIL-010; generic Filament
  create не используется, сохранение выполняет атомарный business service.
- Browser-UAT только №18451: status `pegging → watching → pegging`, delivery
  `2026-08-12 → 2026-09-21 → 2026-08-12`; baseline status dates восстановлен
  в null, письмо не отправлялось. Tests: lifecycle 5/36; полный suite 392 passed /
  2807 assertions / 6 baseline skips. Следующее: финальная приёмка всей страницы.

### ADM-FIL-003 — Колонка «Художник»: данные, назначения и действия

- [DONE, 2026-09-01] В ячейку возвращены художник, печатник, срок, оплата
  работы, видимость картин и previews набросков/картин/файлов клиента.
- Единое Filament-управление назначает, заменяет и снимает исполнителей, меняет
  срок/оплату/видимость и статусы изображений через валидируемую транзакцию.
  Роли исполнителей и принадлежность изображения заказу проверяются сервером.
- Внешние письма художнику и клиенту сохранены как opt-in business behavior;
  `ADMIN_ARTIST_NOTIFICATIONS_ENABLED=false` безопасно блокирует их на UAT.
- Browser-UAT только №18451: Filament → Voyager подтвердил художника, печатника,
  дату, оплату и видимость; затем восстановлен baseline `0/0/null/null/1`.
  Реальных писем не было. В заказе нет painter images, их status branch проверен
  изолированным service/Livewire test.
- Проверки: artist 6/29; полный suite 387 passed / 2769
  assertions / 6 baseline skips. Следующее: колонка «Заказ» и её inline actions;
  вся ADM-FIL-003 остаётся IN PROGRESS до приёмки страницы целиком.

### ADM-FIL-003 — Колонка «Комментарии»: финальная визуальная приёмка

- [DONE, 2026-09-01] После reload на общей БД попарно сверены Voyager и Filament
  для заказа №18451: порядок статических комментариев, полный client preview,
  четыре кнопки, счётчик и обычный client modal.
- Обе админки показывают существующие сообщения `test`/`test2` и счётчик `(2)`.
  Это строки `3992`/`3993`, созданные вне этого подэтапа; они не изменялись и не
  удалялись. Painter/admin/images остаются пустыми, SA message сохранён.
- Filament сохраняет Voyager-состав и порядок, но использует более ровную
  компактную компоновку; modal дополнительно показывает delivery/read state и
  безопасное UAT-предупреждение. Действия отправки не запускались.
- Evidence: парные screenshots основной колонки сохранены в ignored storage
  `storage/app/chat-column-acceptance-20260901`; modal DOM/accessibility содержит
  оба сообщения, даты, автора и composer. Chrome modal screenshot снова получил
  timeout, функциональная/семантическая проверка прошла без записи в БД.
- Колонка «Комментарии» принята как parity-подблок. Следующий подблок внутри
  ADM-FIL-003 — колонка «Художник»; вся ADM-FIL-003 остаётся IN PROGRESS.

### ADM-FIL-003 — Чаты: browser-UAT заполненных веток на №18451

- [DONE, 2026-09-01] На общей тестовой БД и только №18451 сравнены Voyager и
  Filament: general, painting, sketch, orphan, painter, internal admin и SA.
- Атомарно создано и затем удалено по точным ID: 29 client comments, 4 painter,
  2 internal и 2 images. Mail/HTTP/WhatsApp/webhook не вызывались. После cleanup:
  client/painter/admin/images `0/0/0/0`, UAT marker `0`, SA `1`, hash заказа
  `5375ac19709396c850912d986878a6042c64fc137aad9d7462dcca3df50ca70b`.
- Browser: все ветки и авторы совпадают; orphan дополнительно явно виден в
  Filament; targeted read изменил только message `3963`. SA не мутировался:
  Filament показывает фактический `PAUSED`, legacy Voyager — устаревший `N/A`.
- Найден и исправлен разрыв колонки: вместо последних трёх теперь выводятся все
  client/painter/internal previews, как в Voyager. Добавлен regression test.
- Проверки: browser DOM подтвердил 29/29 previews и mobile без горизонтального
  overflow; targeted chats `113 passed / 999 assertions`; полный suite —
  `381 passed / 2738 assertions / 6 skipped`, без failed tests. Встроенный Chrome
  screenshot capture завершился timeout, Windows fallback остановлен политикой
  из-за невозможности надёжно определить URL; данные/код при этом не менялись.
- Следующее: визуальная приёмка пользователем обычной колонки «Комментарии» после
  reload; общая ADM-FIL-003 остаётся IN PROGRESS до финальной приёмки всех колонок.

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

- [DONE, 2026-09-01] POST `/api/sa/webhooks/messages`: receipt, conversation,
  order integration fields, sa_message, client mirror и delivery status теперь
  коммитятся одной транзакцией. Receipt получает `processed` только после persist.
- Dedupe проверяет оба контрактных ключа: глобальный `event_id` и scoped
  `idempotency_key`; конкурентный unique конфликт возвращает duplicate только
  после проверки существующего receipt. Другие constraint ошибки не скрываются.
- Порядок блокировок ingress совпадает с командами: order → conversation; read
  блокирует conversation и после ожидания проверяет snapshot. Проверено двумя
  PHP-процессами на отдельной временной MariaDB: старый snapshot возвращает stale,
  новое сообщение остаётся unread. Тестовая БД удалена после проверки.
- Загрузка вложений происходит до транзакции. Для попытки создаётся уникальный
  путь; после rollback/проигранного duplicate удаляется только файл этой попытки.
  Ошибка cleanup логируется и не скрывает исходный ответ.
- Failure injection: после receipt/message/mirror/status — полный rollback, `503`
  `PERSISTENCE_ERROR`, повтор того же события успешен. SQLite: 9 tests / 132
  assertions; MariaDB: 1 / 11. Regression: 236 passed / 1564 assertions /
  1 прежний skip (237 total). API auth/lead_id/schema не менялись.
- Следующее: ADM-FIL-003 — Чаты: browser-UAT заполненных веток на тестовом №18451.
  Общая приёмка ADM-FIL-003 остаётся IN PROGRESS до визуальной проверки.

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
- [DONE: код атомарного ingress; production concurrency UAT открыто] Входящие
  сообщения обрабатываются SaMessageIngressService в транзакции с receipt и
  блокировками conversation/order; старый TODO о раздельных записях устарел.
  Подтверждение конкуренции на production MySQL остаётся отдельной проверкой.
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
  DONE — discovery legacy API-тестов и painter/SA/client/admin подблоки
  восстановлены и проверены. Финальная browser-сверка 2026-09-01 подтвердила
  обычное состояние, полные preview и четыре действия. Колонка «Комментарии»
  принята; далее колонка «Художник». ADM-FIL-003 остаётся IN PROGRESS до приёмки
  всех восьми колонок.
- [x] [DONE] **ADM-FIL-004 — Карточка и редактирование заказа**: добавлены
  read-only секции основных данных, доставки и позиций; редактирование основных
  административных полей выполняется валидируемым транзакционным сервисом.
- [ ] [TODO] **ADM-FIL-005 — Позиции и файлы заказа**: продолжить после
  завершения parity-аудита ADM-FIL-003/004 по открытой Voyager админке.
- [ ] [TODO] **ADM-FIL-006 — Художники и печать**.
- [ ] [TODO] **ADM-FIL-007 — Чаты заказа**.
- [ ] [TODO] **ADM-FIL-008 — Оплата и платёжные ссылки**.
- [ ] [TODO] **ADM-FIL-009 — Доставка и Venipak**.
- [ ] [IN PROGRESS] **ADM-FIL-010 — Создание заказа менеджером**.
- [ ] [TODO] **ADM-FIL-011 — Приёмка модуля заказов**.
- [ ] [TODO] **ADM-FIL-020 — CRM-SA inbox**.
- [ ] [TODO] **ADM-FIL-021 — Пользователи, роли и скидки**.
- [ ] [TODO] **ADM-FIL-022 — Контент и мультиязычные CRUD**.
- [ ] [TODO] **ADM-FIL-023 — SEO и ALT suggestions**.
- [ ] [TODO] **ADM-FIL-024 — Рассылки, купоны и инструменты**.
- [ ] [TODO] **ADM-FIL-025 — Dashboard и индикаторы**.
- [ ] [TODO] **ADM-FIL-030 — Переключение на `/admin`**.
- [ ] [TODO] **ADM-FIL-031 — Удаление зависимостей Voyager**.

### Полный backlog интеграции админ-панели (аудит 2026-09-25)

Этот перечень сводит переносимые функции кода и действующих legacy admin
маршрутов в проверяемые этапы. Он не меняет текущий приоритет публичного
frontend: админские задачи остаются отдельным post-RC этапом. `viar_filament` и
архив `docs/upgrade-laravel13-filament5` источниками не являются.

#### A. Базовая панель и безопасность

- [DONE] ADM-FIL-000—002: инвентаризация, Filament panel, вход и базовые роли.
- [TODO] ADM-FIL-012 — Единая матрица доступа: сопоставить все роли/permissions
  Voyager с Filament policies и action-level checks; закрыть каждую admin route
  аутентификацией и полномочием; добавить deny-by-default и тесты запрета для
  прямого URL, Livewire action и массовых операций.
- [TODO] ADM-FIL-013 — Безопасная замена legacy GET/ANY mutations на POST/PATCH/
  DELETE с CSRF, валидацией, подтверждением опасных действий, throttling где
  нужно и журналом критических изменений; отдельно пройти cache, coupon, sale,
  image delete, assignment, review и label endpoints.
- [TODO] ADM-FIL-014 — Админский UX foundation: локали, навигация, доступность,
  responsive, ошибки/empty states, загрузки, сообщения об успехе и единые
  критерии визуальной приёмки на desktop/mobile.

#### B. Заказы: список, карточка и рабочие операции

- [IN PROGRESS] ADM-FIL-003 — Orders list parity: все восемь legacy-колонок,
  быстрые вкладки, сортировка, поиск, фильтры, счётчики, actions и browser UAT.
- [DONE] ADM-FIL-004 — Карточка и основные редактируемые поля заказа.
- [TODO] ADM-FIL-005 — Позиции/файлы: добавить/удалить позицию и изображения,
  редактировать цену/параметры, проверять принадлежность файлов заказу,
  сохранять legacy JSON без потери неизвестных ключей, безопасно обрабатывать
  большие/удалённые файлы и rollback.
- [TODO] ADM-FIL-006 — Назначения художника/печатника, очереди `painter_orders` /
  `printing_orders`, сроки, статусы и изображения производства; права ролей,
  уведомления-gates и parity/UAT.
- [TODO] ADM-FIL-007 — Полный перенос клиентского, внутреннего и painter чатов:
  история, вложения, read/unread, authorship, ответы, ограничения закрытых
  заказов и регрессия client-facing потока.
- [TODO] ADM-FIL-008 — Счета, фирмы, payment requests и платёжные ссылки:
  генерация/подтверждение PDF, email gate, статусы/суммы, идемпотентность и
  запрет повторного/неверного списания.
- [TODO] ADM-FIL-009 — Venipak: адресная и pickup-доставка, создание/печать
  этикеток, повтор/ошибки провайдера, привязка номера к заказу и fake HTTP.
- [IN PROGRESS] ADM-FIL-010 — Создание заказа менеджером: parity полей и
  клиент-only copy semantics, транзакция пользователя/бонусов/заказа/файлов,
  filename codes, доставка по стране, бонусы, округления, browser UAT.
- [TODO] ADM-FIL-011 — Приёмка модуля заказов end-to-end: полный набор ролей,
  filters/actions, CRUD, чаты, вложения, оплаты/доставка; regression по `orders`
  и админ-чатам, проверка внешних отправок и runbook восстановления.

#### C. SA/CRM и административные коммуникации

- [TODO] ADM-FIL-020 — CRM-SA inbox: список/поиск/фильтры/непрочитанное,
  история и вложения, отправка/ответ, bind/create order, bot controls, аудит,
  права, идемпотентность и согласованная обработка временных сущностей.
- [TODO] ADM-FIL-026 — Уведомления и mail tools: `email-sender`, user notify,
  шаблоны/получатели, suppression/preview, безопасный тестовый режим, лимиты,
  лог доставки и запрет массовой отправки без подтверждения.
- [TODO] ADM-FIL-027 — Image generation и review workflow: генерация по заказу,
  thumbnails, аудио-отзывы, reviewer actions; очередь/ошибки/повтор и storage
  ownership, без вызова production AI/внешних сервисов в тестах.

#### D. Пользователи, контент и инструменты

- [TODO] ADM-FIL-021 — Пользователи/роли/скидки: поиск и карточка клиента,
  заказы/история, язык, категории и менеджер, статусы, бонусы/скидки, назначение
  и отмена акций; транзакции, permission checks и уведомления.
- [TODO] ADM-FIL-022 — Контент и мультиязычные CRUD: определить приоритетные
  BREAD-типы по фактической навигации; страницы, header/menu, gallery/services,
  категории и справочники; локали, переводы, медиа, сортировка, trash/restore,
  связи и сохранение legacy форматов.
- [TODO] ADM-FIL-023 — SEO/ALT suggestions: scan, generate, approve/reject,
  apply/revert и bulk; права, история изменений, локали, rate limits и тестовый
  режим генератора.
- [TODO] ADM-FIL-024 — Операционные инструменты: купоны/30×40/Facebook sales,
  cache/storage utilities, TinyMCE upload, media cleanup и прочие фактические
  Voyager actions; отдельно решить судьбу каждой функции, не переносить
  небезопасные legacy semantics.
- [TODO] ADM-FIL-025 — Dashboard: оперативные показатели и unread indicators,
  быстрые ссылки/очереди, корректность фильтров по ролям и производительность.
- [TODO] ADM-FIL-028 — Media/library и загрузки контента: найти все хранилища,
  URL и правила удаления; безопасный просмотр/загрузка/замена, валидация MIME,
  размеров и ownership, миграцию данных выполнять только при подтверждённой
  необходимости.
- [TODO] ADM-FIL-029 — Каталог BREAD и схема миграции контента: выгрузить из
  реальной БД типы, поля, связи, меню и permissions; утвердить keep/migrate/
  retire для каждого типа до реализации полного CRUD.

#### E. Переключение и вывод Voyager

- [TODO] ADM-FIL-030 — Cutover `/admin`: сверить route/menu parity, пользователей
  и роли, прямые ссылки и callbacks; staged smoke, feature flag/rollback,
  проверка сессий, файлов, почты и интеграций; переключать только после UAT.
- [TODO] ADM-FIL-031 — Удаление Voyager runtime/dependencies: сначала доказать
  отсутствие оставшихся routes/views/helpers/commands/jobs/public consumers,
  затем удалять пакет и compatibility code отдельными небольшими изменениями;
  legacy data и активные orders/chat не удалять.

#### F. Сквозные критерии для каждого этапа

- [TODO] Для каждого модуля: source inventory (routes, BREAD, роли, tables,
  внешние вызовы), контракт данных, owner каждой записи, безопасная транзакция
  и миграционная совместимость.
- [TODO] Для каждого изменяющего действия: feature tests успеха/валидации/
  запрета доступа/повтора/rollback; external HTTP/mail/storage подменять.
- [TODO] Для каждого модуля: browser parity/UAT на согласованной тестовой записи,
  screenshots/evidence, отсутствие неожиданных внешних отправок и запись итогов
  в `MIGRATION_PROGRESS.md`.
- [TODO] До cutover: полный permission matrix, backup/restore rehearsal,
  мониторинг ошибок и latency, сверка delta записей/файлов/чатов/платежей,
  rollback checklist и подтверждение write-owner для каждой capability.

Основание инвентаризации: `routes/admin.php`, `AdminOrdersController`,
`VoyagerAdminController`, `AdminSaIntegrationController`, `AdminLocaleController`,
SEO/ALT controllers, Filament `OrdersResource` и существующие services/tests.
Список покрывает найденные прикладные admin-контуры; точный перечень BREAD-типов,
полей и permissions требует чтения актуальной legacy БД и остаётся TODO
ADM-FIL-029, а не считается подтверждённым статическим списком.

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

- [x] [DONE] Проверить legacy source commits `c1805f0b` и `9a72e4b9`
  из `C:\OSPanel\domains\asoft\viar`:
  - frontend-часть `c1805f0b` перенесена и адаптирована: внутренние ссылки
    меню, карточек, breadcrumbs, модальных форм и trusted translated HTML
    получают текущий locale prefix; существующий route-based fallback слайдера
    gallery сохранён;
  - legacy SQL с точечными production-значениями `translations` не переносился:
    runtime-нормализация устраняет зависимость от захардкоженного домена и
    неправильного locale в данных без мутации БД;
  - `9a72e4b9` относится только к Voyager-форме курьерской этикетки и не влияет
    на публичный frontend/checkout, поэтому в текущем scope не переносился;
  - Blade cache успешно собран; focused regression — `39 passed / 208
    assertions`, locale unit — `5 passed / 13 assertions`; полный suite —
    `407 passed / 2873 assertions`, `6 skipped`, `0 failed`.
- [ ] [TODO][FILAMENT] При переносе/проверке курьерских этикеток применить
  правило из `9a72e4b9`: телефон получателя брать из `delivery.phone`, затем
  `delivery.payer_phone`, и только после этого из профиля пользователя.
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

- [DONE, 2026-09-30] «Подписи файлов и создание папок»: исходные читаемые подписи FilePond, включая кириллицу/пробелы, обновляются после рендеринга без изменения URL/state. Создание папок в пределах base_path с edit/add правами, show_folders/allow_create_folder, проверкой дубликатов/имени/Windows reserved names. Chrome: 43 подписи, 0 encoded кириллицы, 0 [object Object], 0 JS errors; попытка ../outside отклонена видимой ошибкой. Успешное создание и вложенная папка проверены только на fake disk. Tests: 30 passed / 208 assertions; view:cache/diff-check успешны. Следующее — rename/move с проверкой всех ссылок.

- [DONE для подзадачи, 2026-09-30] «Переименование/перемещение библиотеки»: UI «Изменить путь», новый basename/существующая папка в base_path; metadata allow_rename/allow_move; обновление точных ссылок metadata image/file/multiple_images/media_picker и translations в транзакции с edit правом каждого затронутого типа/поля. Исходник сохраняется для HTML/внешних ссылок, потребуется отдельная очистка. Рабочая БД/файлы не менялись. Chrome: товар 13 → Общее → BeautyART → 004 Коррекция.jpg → форма, без применения, JS errors 0. 32 tests / 234 assertions; после оптимизации lazy scan focused 2 tests / 26 assertions, view:cache/diff-check успешны. Новых DB migrations и привязок к текущим ID нет.

- [CONSTRAINT, 2026-09-30] Пользователь: картинки/пути в рабочей БД не изменять; будет подключена БД с production и запущены миграции. Проверки записи только SQLite/fake disk; browser-UAT без применения rename/move/save. Код должен читать актуальную metadata новой БД, не требовать текущих ID/путей.

- [DONE для подзадачи, 2026-09-30] «Обрезка библиотеки»: координаты x/y/width/height и рамка preview, JPEG/PNG/WebP/BMP через GD, новый UUID файл, выбор в текущей форме до Save, исходник/другие записи неизменны. Проверены границы, max, metadata, пиксели/размер/прозрачность PNG и повтор после ошибки. Tests 34 passed / 259 assertions; Chrome товар 13 → Общее → BeautyART → 004 Коррекция.jpg, рамка 150/200/1000/1400, без создания файла/Save. view:cache/diff-check успешны. Следующее — управление папками и UAT после production DB.

- [DONE для подзадачи, 2026-09-30] «Управление папками библиотеки»: UI rename/delete внутри дочерней папки; rename копирует дерево и пустые подпапки, обновляет structured metadata/translation paths по prefix boundary в транзакции с правами всех затронутых полей/типов. Исходная папка сохраняется для HTML/внешних ссылок. Delete только атомарный non-recursive rmdir, root/выход из public disk/symlink защищены, hidden files/subfolders блокируют. Лимит rename 500 объектов. Chrome проверен без применения/удаления/Save, JS errors 0; рабочая БД/картинки не менялись. 37 tests / 304 assertions, php lint/view:cache/diff-check успешны. Следующее — UAT после подключения production DB, аудит/очистка исходников и сверка new_work_photos.

## 2026-09-30 — Сравнение gallery-holsts/5/edit с Voyager

- [DONE] Read-only Chrome сравнение оригинала viarcanvas.loc/admin/gallery-holsts/5/edit и viar13.loc/filament/bread/gallery-holsts/5/edit: все семь полей EN и значения name/density/hint EN/RU/LV/EE/LT/DE/PL совпадают; изображение отображается. Save не нажимался, рабочая БД и файлы не изменялись.
- [DONE, 2026-09-30] Добавить в generic BREAD панель ALT/TITLE изображения: оригинал показывает одну картинку и семь языковых значений, статус applied, редактирование ALT/TITLE языка и действия сохранения языка/всех языков/применения всего. В Filament формы холста этой панели нет. Использовать существующий источник ALT, не создавать независимые значения.
- [DONE, 2026-09-30] Сохранить видимость общих полей при переключении языка формы: оригинал оставляет price/hit/image/default, Filament скрывает их вне EN. Проверить сохранение переводов без потери несохранённых общих полей.
- [DONE, 2026-09-30] Сделать компактный preview одиночного image: сейчас FilePond занимает всю ширину формы с большим чёрным фоном. Проверить просмотр полного изображения.
- [DONE, 2026-09-30] Унифицировать порядок языков (оригинал DE перед PL) и обозначение языка у переводимых полей; проверить обязательность полей по data_rows перед изменением звёздочек.
- Следующее точное действие: аудит существующего ALT/TITLE источника и контрактов применения, затем адаптация панели для generic image. Сравнение DONE; устранение расхождений TODO.

- [IN PROGRESS, 2026-09-30] «Форма холста — совместимость Voyager»: перенос ALT/TITLE из image_alt_suggestions с существующим alt:apply, видимость/сохранение общих полей при смене языка, черновики переводов, компактный image preview и порядок EN/RU/LV/EE/LT/DE/PL. Рабочая БД/картинки не изменяются; проверки записи только изолированные. Следующий шаг — regression-тесты и Chrome сравнение холста 5.

- [DONE для подзадачи, 2026-09-30] «Форма холста — совместимость Voyager»: все четыре согласованных пункта реализованы. ALT/TITLE image/галерей/медиа по существующим предложениям, отдельный язык/вся картинка/всё через alt:apply; BREAD/ALT/metadata scope и валидация. Общие поля доступны и сохраняются из любого поддерживаемого языка; черновики переводов и общих значений переживают переключение. Библиотека общих media_picker доступна и вне EN с прежними правами/проверками metadata/root. Image preview 360px/240px и openable; языки EN/RU/LV/EE/LT/DE/PL, подписи языка, блокировка кнопок на время смены языка. Required оставлен по metadata; false toggles проверены. Chrome холст 5 RU и ALT RU/DE проверены без Save/Apply. Tests 41 passed / 335 assertions, php lint/view:cache/diff-check успешны. Рабочая БД/файлы не изменялись; DB migrations для функции не добавлялись. Следующее — UAT после подключения production БД; весь BREAD этап остаётся IN PROGRESS.

- [IN PROGRESS, 2026-09-30] «Варианты связей в выпадающих списках»: gallery-items/974 Типы показывает только выбранный Modular canvas. Read-only metadata/DB audit: belongsTo gallery_types.id/name, 7 реальных вариантов (2–8). Generic Select имеет search callback без options, поэтому список пуст до ввода. Добавить начальные ограниченные options для belongsTo и belongsToMany, сохранить server search и проверку сохранения связи. Рабочую запись/БД не менять. Следующее — regression и Chrome dropdown.

- [DONE для подзадачи, 2026-09-30] «Варианты связей в выпадающих списках»: belongsTo/belongsToMany Select получает начальные 50 options из актуальной metadata, стабильный порядок по key/id; server search сохранён для всех записей, label выбранного значения разрешается отдельно (в том числе вне первых 50). Chrome gallery-items/974: 7 вариантов Типы без ввода, поиск Caricature возвращает один результат, очистка восстанавливает все 7. Данные товара и рабочая БД не менялись. Tests focused 3 passed / 21 assertions, php lint/view:cache/diff-check успешны. Следующее — общий UAT на копии production DB.

- [IN PROGRESS, 2026-09-30] «Связи при смене языка»: поле Типы скрывается вне EN, потому что schema и fillRecord отношений ограничены базовым языком. Убрать ограничение отображения/инициализации, сохранять общие belongsTo и pivot связи из любого языка с прежней проверкой существования целей. Проверить переключение/черновики/сохранение на SQLite; рабочий товар не менять.

- [DONE для подзадачи, 2026-09-30] «Связи при смене языка»: schema/fillRecord для belongsTo и belongsToMany доступны на всех языках, текущие значения и черновики переживают смену языка. Save валидирует FK вне EN и синхронизирует pivot в той же транзакции, перевод не подменяет базовые значения. Chrome товар 974 RU: Типы видно, Modular canvas выбран, все 7 вариантов открываются. Save не нажимался, рабочая БД не изменялась. Full regression 42 passed / 379 assertions, lint/view:cache/diff-check успешны. Следующее — UAT остальных типов на копии production DB.

- [IN PROGRESS, 2026-09-30] «Оформление полосы BREAD вкладок»: мягкий цветной фон, отдельные кнопки вкладок, контрастная активная вкладка, hover/focus и dark theme; проверить переключение и Chrome screenshot товара 974 без сохранения.

- [DONE для подзадачи, 2026-09-30] «Оформление полосы BREAD вкладок»: светлый голубовато-серый фон полосы, отдельные кнопки с рамкой и промежутками, активная синяя вкладка с белым текстом и тенью, hover/focus и dark CSS. Scoped viar-bread-tabs только schema BREAD. Chrome товар 974 Main→Фильтры→Main работает, selected активной вкладки и цвета подтверждены, screenshot storage/app/testing/gallery-items-tabs-2026-09-30.png. view:cache/lint/diff-check успешны. Данные/БД не менялись. Следующее — общий UAT на копии production DB.

- [IN PROGRESS, 2026-09-30] «Компактные изображения во всех BREAD формах»: сравнение Новые медиа товара 974 показало multiple_images (Картинка в корзине) как полноширинные полосы FilePond вместо маленьких карточек оригинала. Ввести единые compact previews для image/multiple_images/media_picker, grid для массивов и просмотр полного файла. Не менять физические размеры/DB paths. Следующее — Chrome сравнение и проверка рендера.

- [DONE для подзадачи, 2026-09-30] «Компактные изображения во всех BREAD формах»: image/multiple_images/media_picker используют общий BreadFileUpload::compactImagePreviews. Одиночное поле до 280px, preview 160px; массивы — grid до 720px, 3 карточки в ряд (2 на узком экране), просмотр полного файла. Chrome сравнение оригинала и новой вкладки Новые медиа товара 974: Картинка в корзине теперь три компактные карточки рядом. Existing focused tests 4 passed / 33 assertions, php lint/view:cache/diff-check успешны. Рабочая БД, пути и физические картинки не менялись. Следующее — общий UAT после production DB.
# DONE — Прокрутка вкладок BREAD (2026-09-30)
- Горизонтальная нативная прокрутка пальцем и перетаскивание мышью добавлены для общих вкладок форм BREAD.
- Chrome 390×844: полоса 295 px, содержимое 1826 px; drag изменил scrollLeft с 0 до 150 без смены Main, следующий клик выбрал Фильтры. Ошибок JS нет. Viewport восстановлен; запись не сохранялась.
- Следующее действие: пользовательская проверка свайпа на физическом телефоне (эмуляция размера проверена, ввод пальцем инструмент недоступен).
# DONE — Иконки меню Voyager в Filament (2026-09-30)
- Источник: menu_items.icon_class административного меню; 23 разных непустых класса Voyager, 105 пунктов без иконки.
- Добавлены SVG-аналоги через конфигурацию; для пустых/неизвестных классов — иконка по разделу. Данные БД не изменяются.
- Проверено: все 27 используемых SVG существуют; классы Voyager, Heroicon и неизвестный класс корректно разрешаются. Chrome: SVG 24×24 отображается у пункта Типы, иконки видны у общих страниц и галереи.
- Следующее действие: при необходимости подобрать индивидуальные иконки через Пункты меню → Иконка.
# DONE — Шапка таблиц BREAD (2026-10-01)
- На roles заголовки центрированы и сливаются с данными. Исправить общий шаблон: выравнивание слева, контрастный фон, рамка и отступы, включая закреплённые Действия и тёмную тему.
- Проверено в Chrome: roles — четыре заголовка слева, общий фон rgb(238,242,248), Действия закреплены. При viewport 390×844 контейнер 293 px / таблица 597 px с overflow auto; страница не расширяется (375 px). view:cache и diff --check прошли.
- Следующее действие: пользовательская оценка внешнего вида; обязательных задач по этому исправлению нет.
# DONE — Оформление меню и настроек (2026-10-01)
- Сравнение с /admin/settings: оригинал использует вкладки групп. По уточнению пользователя обе новые страницы оформляются таблицами в стиле BREAD: светлая шапка, рамки, отступы, закреплённые кнопки-иконки.
- Настройки: вкладки групп, компактные изображения, читаемые значения. Меню: иконки, статус, адаптивная прокрутка.
- Проверено в Chrome: рендер обеих таблиц, группы Site/Модульные картины, меню footer, открытие и отмена редакторов. Два теста меню/настроек прошли (12 assertions), view:cache пройден.
- Мобильный overflow предусмотрен CSS; viewport-инструмент в этой сессии не изменил размеры этих вкладок, поэтому физическую мобильную проверку не считать выполненной.
- Следующее действие: пользовательская оценка оформления; финальная UAT на телефоне.
# DONE — Отдельный менеджер переводов интерфейса (2026-10-01)
- По уточнению пользователя установлен стандартный barryvdh/laravel-translation-manager v0.6.9, совместимый с Laravel 13. Отдельный интерфейс: /filament/translations; в Filament добавлена ссылка, прежний /filament/ui-translations перенаправляет на него, включая выбранную группу.
- Используется существующая ltm_translations; миграции пакета не запускались. Рабочая БД и словари не импортировались, не публиковались и не изменялись.
- Все GET/POST маршруты пакета защищены сессией filament и browse_admin; гостя перенаправляет на вход, JSON получает 401, пользователь без права получает 403. Сохранена CSRF-защита web middleware.
- Проверено на SQLite и временных словарях: редактирование, append-импорт, публикация группы, добавление ключей, перенаправления и запрет доступа. Полный VoyagerBreadTest: 42 passed / 398 assertions. Composer validate, Pint и Chrome выполнены; стандартная группа homepage_new отображается. Снимок: storage/app/testing/standalone-translation-manager-2026-10-01.png.
- Следующее действие: пользовательская проверка стандартного интерфейса. Изменённые переводы применяются к сайту после Publish translations. Обязательных незавершённых задач по подключению нет.

# DONE — Иконки главных категорий меню (2026-10-01)
- Источник проверен: menu_items.icon_class уже содержит значки родительских категорий Voyager. Подключить их к группам Filament, сохранив значки подпунктов и не меняя БД.
- Реализовано: группы берут icon_class родителей административного меню через существующее сопоставление Voyager/Heroicons. CSS mask 24px сохраняет иконки подпунктов; дополнительные группы Контент/Переводы/Контент Voyager получают аналоги из конфигурации. БД не изменяется, импорт production DB подхватывается автоматически.
- Проверено: Chrome отображает 16 групп со значками; Пользователи раскрывается, четыре подпункта сохраняют свои SVG. PHP lint, view:cache, git diff --check прошли. Следующее действие: пользовательская оценка; обязательных задач по этому изменению нет.

# DONE — Навигация доступных разделов как в Voyager (2026-10-01)
- Сравнение page-faq-desc: свёрнутый Filament показывает все дочерние иконки без категорий; текущие BREAD ссылки не имеют active matcher; вложенные группы подняты в корень и порядок отличается от menu_items.order.
- Реализовать дерево menu_items с исходным порядком, сохранить существующие права/доступные адреса, главные значки с выпадающим списком в компактном режиме, раскрытие активной ветки и путь раздела.
- Реализовано: BreadMenuTree строит дерево из активных menu_items по parent_id/order/id и только из доступных Filament navigation items. В компактной панели видны корневые значки, категории открываются отдельным списком с вложенными ветками; Escape закрывает список и возвращает фокус. Активный пункт и его родитель подсвечены; путь раздела виден над контентом. Активная ветка раскрывается и прокручивается к пункту.
- Проверено: Chrome FAQ в двух состояниях, Общие страницы → БЛОГ → Статьи, фильтр gallery-items?type=2, Escape/focus и ограничение popup высотой viewport. Регрессия VoyagerBreadTest: 42 passed / 398 assertions; новые тесты навигации: 2 passed / 20 assertions. PHP lint/view:cache/diff-check прошли. Рабочая БД не менялась.
- Риск сопровождения: override livewire/sidebar.blade.php сохраняет штатную оболочку Filament и меняет только список навигации; при обновлении Filament сверять оболочку с upstream. Проверка физического телефона и полного screen reader остаётся UAT.
- Следующее действие: пользовательская оценка меню; перенос отсутствующих административных инструментов ведётся отдельно.

# TODO — Оставшиеся инструменты меню Voyager (2026-10-01)
- При сравнении подтверждено: Email Рассылка (/admin/email-sender) есть в старом меню, но route:list нового runtime не содержит этого маршрута. Код routes/admin.php исторический, маршрут не подключён.
- По очереди проверить остальные неподключённые инструменты (Медиа, База данных, Compass, BREAD, карта сайта) и зафиксировать нужные замены; не создавать ссылки на 404. В текущем меню отсутствующие адреса опускаются, порядок доступных пунктов сохраняется; инструменты Filament без старой записи собраны в Дополнительно в конце.
- Следующее действие: аудит переноса EmailSenderController и его зависимостей перед подключением маршрутов.

# DONE — Домик и расположение хлебных крошек (2026-10-01)
- Заменить наследуемый voyager-boat у Dashboard на домик; перенести путь раздела из начала контента в шапку над заголовком, согласовать отступы и разделители.
- Реализовано: Dashboard всегда отображает heroicon-o-home; путь перенесён в page.header.heading.before, выровнен с заголовком, отступ до заголовка 12px, иконки-разделители компактные. На ресурсных страницах сохранены штатные крошки без дублирования.
- Проверено в Chrome: FAQ — путь внутри fi-header, левый край совпадает с заголовком, 12px между ними; Заказы — один штатный путь и ноль дубликатов. 2 теста навигации / 20 assertions, view:cache, PHP lint и diff-check прошли. Следующее действие: пользовательская оценка шапки.

# DONE — Упрощение корзины и правила скидок Laravel 13 (2026-10-07)
- Перенести согласованный checkout Viar: guest email/coupons, предложения только на первом шаге, пропорциональный размер −15%, +1 холст −30%, исключения HIT/TOP/SUPER DEAL, подарочные карты и recovery.
- Сохранить Laravel 13 auth/payment/mail adaptations и чужие изменения Filament.
- Проверить новые и существующие checkout/payment тесты на PHP 8.4, затем браузер; исправить повторный email и строку изготовления.
- Реализовано и проверено: 65 tests / 387 assertions, Chrome viar13.loc, PHP lint, JS syntax, Blade cache и diff-check. Подробности и ограничения: docs/checkout/implementation.md.
- Следующее действие при выпуске: миграция, точечный импорт checkout_coupon и UAT оплаты в тестовом контуре.

# DONE — Аудит недостающего функционала двух админок (2026-10-08)
- Сравнены Chrome старой/новой панелей, меню, users, SEO Meta и роль №8; сверены зарегистрированные маршруты, Filament actions и реальные data_rows roles. Рабочая БД/изображения/разрешения не менялись.
- Отчёт и evidence: docs/filament_functionality_audit_2026-10-08.md. DONE относится к аудиту, не к полной миграции и UAT.
- TODO ADM-AUD-01/P1: редактор матрицы прав ролей; ADM-AUD-02/P1: специализированный SEO Meta workflow; ADM-AUD-03/P1: SA inbox и глобальные unread.
- TODO ADM-AUD-04/P2: массовая Email Рассылка; ADM-AUD-05/P2: клиентский список; ADM-AUD-06/P2: BREAD metadata editor для полей/типов/связей.
- TODO ADM-AUD-07/P2: ALT bulk/revert; ADM-AUD-08/P2: функции generic таблиц (field/operator search, сортировка, размер страницы, bulk); ADM-AUD-09/P2: отдельный вход в медиатеку.
- TODO ADM-AUD-10/P3: операции над самими меню/настройками и drag-and-drop; ADM-AUD-11/P3: нужные служебные инструменты; ADM-AUD-12/P3: перенос длинного URL в generic cell.
- Уже реализованные order actions (PDF/payment requests/Venipak/chats/email), embedded media library и Translation Manager не считаются отсутствующими. Реальные внешние операции и проверка всех типов — отдельный UAT.
- Следующее точное действие: реализовать ADM-AUD-01 на существующих permissions/permission_role и проверить изолированно под ролями admin/manager/seo_manager.

# DONE — ADM-AUD-01: матрица прав ролей (2026-10-08)
- Добавить группировку по реальным permissions.table_name, выбор разрешений роли, выбор всей группы и поиск.
- Сохранять permission_role вместе с ролью в одной транзакции; проверять разрешение операции и существование всех permission IDs. Переключение языка не должно сбрасывать выбранные права.
- Проверки выполнять на SQLite :memory:, браузер использовать только для просмотра. Следующее действие: реализация и тесты сохранения/валидации/доступа.
- Реализовано: группировка по существующим permissions.table_name, заголовки из data_types, поиск, выбор/снятие группы и всех прав, счётчик. Работает при создании и редактировании роли; права общие для языков и не сбрасываются переключением.
- Запись существующих permission_role выполняется в транзакции вместе с ролью, ID валидируются по permissions; неизвестное разрешение отклоняет все изменения. Доступ требует add_roles/edit_roles, прямые вызовы запрещены для других BREAD и пользователей без разрешения.
- Проверено: 5 новых тестов / 29 assertions; полная регрессия BREAD/navigation 49 tests / 447 assertions PASS на SQLite :memory:. PHP lint, view:cache, diff-check PASS.
- Chrome роль №8: 136 групп, 675 разрешений, 616 исходно выбрано; поиск pages показывает одну группу, горизонтального overflow нет. Скриншот storage/app/testing/role-permissions-after-2026-10-08.png. Рабочие права/данные/картинки не менялись; миграция не требуется.
- Следующее действие: ADM-AUD-02 — специализированная страница SEO Meta с scan/generate/approve/reject/apply/bulk и фильтрами. ADM-AUD-03..12 остаются TODO.

# DONE — ADM-AUD-02: SEO Meta workflow (2026-10-08)
- Создать специализированный Filament resource вместо generic CRUD: текущие/предложенные meta, счётчики длины, status/model/locale/missing/error/URL filters, scan и индивидуальные/массовые действия.
- Использовать существующие SeoMetaGenerator/ContextResolver и seo-meta:scan; сохранить таблицу seo_meta_suggestions, исходные поля и translations. Генерацию запускать через очередь, применение только после одобрения, авторизация по browse/edit_seo_meta_suggestions.
- Проверить переходы статусов, валидацию, локализованное применение, bulk и отказ без прав на отдельной SQLite базе. Рабочие SEO записи/изображения не менять.
- Реализован /filament/seo-meta-suggestions: статистика, вкладки статусов, текущие/предложенные meta и лимиты длины, поиск по meta/keywords/error/URL, фильтры статуса/модели/языка/пустых полей/ошибок/URL. Старый generic URL перенаправляется, меню ведёт в resource.
- Добавлены scan, generate, approve с редактированием, reject, apply и четыре bulk actions. Применение одобренных значений атомарно и требует approved; переводы сохраняются в translations без перезаписи базового языка. Доступ защищён browse/edit_seo_meta_suggestions, модели и языки проверяются по конфигурации.
- Очередь использует ShouldBeUnique job; текущее окружение sync выполняет генерацию сразу. Для фоновой массовой генерации остаётся настроить асинхронный драйвер и worker при развёртывании; .env не изменён. Реальный OpenAI не вызывался.
- Chrome: 8456 записей, счётчики 499/510/510, фильтры и форма scan (19 моделей, 7 языков). Операции сохранения/генерации/сканирования в рабочей БД не выполнялись. Снимок storage/app/testing/seo-meta-after-2026-10-08.png.
- Миграций и изменений рабочего schema/data_rows не требуется. Изображения и рабочие SEO-данные не менялись. PHPUnit CACHE_DRIVER=array добавлен для изоляции unique-job locks, поскольку config/cache.php использует этот ключ.
- Следующее точное действие: ADM-AUD-03 — SA inbox, непрочитанные, привязка/создание заказа. ADM-AUD-04..12 остаются TODO. Реальные внешние вызовы и production queue — отдельный UAT.
- Итоговая регрессия BREAD/navigation после расширения поиска: 56 tests / 518 assertions PASS; 7 новых SEO tests. PHP lint, view:cache, git diff --check PASS.

# DONE — ADM-AUD-03: SA inbox (2026-10-08)
- Перенести standalone список/карточку диалога, поиск, фильтры unread/unlinked/awaiting/recent, глобальный polling счётчика, историю и локальные вложения.
- Использовать существующие sa_conversations/sa_messages/sa_events и права browse_orders/read_orders/edit_orders/add_orders; новых статических role IDs не вводить.
- Привязка/создание заказа: транзакция, блокировка conversation, запрет конфликтующей связи, перенос сообщений/escalations/bot controls и клиентского mirror без дублей. Создание через существующий createLead business flow.
- Ответы и bot controls: расширить существующий OrderSaCommandService для standalone, сохранив signed token/idempotency/UAT guard/transport. Read — явный snapshot, без изменения unread при открытии страницы.
- Проверки на SQLite :memory: с fake HTTP/mail. В Chrome только просмотр; рабочие сообщения/заказы/read flags/картинки не менять. Следующее действие: сервисы, страницы и изолированные тесты.
- Найдено при ADM-AUD-03: исторический tests/Feature/Api/SaLeadsCreateTest.php использует /** @test */, PHPUnit 12 не обнаруживает тесты. [TODO] MIG-TEST-SA-LEGACY: перевести аннотации старых API tests на attributes/test_ и отдельно выполнить их с изолированной schema. Новые SaInboxTest реально проверяют createLead success/invalid/duplicate через бизнес-сервис; не считать старый API файл выполненным.

# 2026-10-08 — DONE: ADM-AUD-03, SA inbox
- /filament/sa-conversations и /filament/sa-conversations/{record}: поиск клиента/телефона/диалога/заказа; фильтры unread/unlinked/awaiting_reply/recent/channel/bot_mode; карточка с историей, компактными локальными вложениями, явным snapshot read, bind/create order, reply/bot и журналом команд. История/таблица/badge обновляются каждые 10 секунд. Глобальный SA badge и выключенный по умолчанию звук включаются из верхней панели.
- Auth: Filament session/CSRF и существующие browse_admin+browse_orders+read_orders; изменения требуют edit_orders, создание дополнительно add_orders. Права проверяются повторно на query/render/actions, токены проверяют выбранный conversation, автора, срок, phone/mode/order. Новых интеграционных API endpoints нет, X-Api-Key контракты не менялись.
- DB: новых таблиц/колонок/миграций нет. Используются sa_conversations/sa_messages/sa_events/sa_bot_controls/sa_escalations/orders/order_user_comments. bind/create транзакционны, запрещают конфликтующую связь и не сбрасывают unread. История зеркалируется по sa_message_id без дублей и без UAT/неподтверждённых исходящих; createLead вызывается внутри сервиса, HTTP loopback не требуется. Повтор create возвращает уже связанный заказ.
- Commands: общий OrderSaCommandService поддерживает standalone nullable order, прежние order actions сохранены; идентичный signed command возвращает duplicate без повторной отправки. UAT guard, pending/accepted/uncertain/partial/state_conflict, отсутствие автоматического resend и безопасные логи сохранены. Для standalone bot client_id=conversation_id, phone выбранного диалога; для заказа контракт прежний.
- Проверки: 11 новых inbox tests; SaInbox+OrderSaChat+OrderSaCommand+BreadMenuNavigation 59 tests/513 assertions PASS. Общая BREAD/SEO/roles/navigation/SA регрессия 113 tests/1011 assertions PASS (PHP 8.3.30, CLI memory_limit=512M). Дополнительная проверка отзыва прав на открытой странице — 1 test/8 assertions PASS. PHP lint, view:cache, diff-check PASS. SQLite :memory:, fake HTTP/mail; никакой реальной отправки.
- Первый совмещённый прогон с лимитом CLI 128M: 111 passed/2 crop tests failed из-за штатной memory guard при 120M накопленного расхода. С CLI 512M все 113 PASS; приложение и лимиты web runtime не менялись.
- Chrome: 39 диалогов/38 unread; карточка standalone №35 и bind modal без сохранения, unread остаётся 38. Карточка существующего UAT №39; мобильная ширина 390 px, устранено наложение badge на avatar, overflow нет, desktop viewport восстановлен. Evidence: storage/app/testing/sa-inbox-after-2026-10-08.png и sa-inbox-mobile-2026-10-08.png.
- Рабочие сообщения/заказы/read flags/изображения не изменялись. Реальная отправка в окружении выключена admin_migration.sa_commands_enabled=false; config/.env не менялись. Live bind/create/send/bot, реальный звук и provider callbacks требуют отдельной UAT при разрешённой отправке. Не обнаруживаемые PHPUnit12 старые API annotation tests вынесены в MIG-TEST-SA-LEGACY; не считаются выполненными.
- Следующее точное действие: ADM-AUD-04 — массовая Email Рассылка; ADM-AUD-05..12 и MIG-TEST-SA-LEGACY остаются открыты.

# TODO — Визуальное согласование SEO Meta и SA inbox (2026-10-08)
- Проведён сравнительный Chrome-аудит Voyager, новых SEO/SA страниц и оформленных BREAD roles. Совпадение дизайна частичное; функциональные ADM-AUD-02/03 остаются DONE в ранее описанном scope.
- TODO ADM-DES-01/P2: SEO — единая шапка/поиск/действия BREAD, компактные счётчики и вкладки, видимые основные фильтры, явные ссылки, согласование редактора предложений.
- TODO ADM-DES-02/P2: SA — единая таблица, объединение колонок, заметные unread/направления/ссылки заказа, компактная переписка и расположение формы ответа/управления ботом.
- Evidence и подробности: docs/filament_design_audit_2026-10-08.md. Код/рабочая БД не менялись; аудит desktop не закрывает мобильный UAT.
- Следующее точное действие по дизайну: ADM-DES-01, затем ADM-DES-02; реализация в рамках текущего аудита не выполнялась.

# IN PROGRESS — ADM-DES-01 / ADM-DES-02 (2026-10-08)
- Пользователь согласовал визуальную доработку. Реализуется единый BREAD стиль SEO/SA таблиц, видимые фильтры, компактные счётчики, действия-иконки и ссылки.
- SA: объединяются колонки, выделяются непрочитанные, сообщения располагаются слева/справа; форма ответа добавляется под историей с прежним signed-token/idempotency сервисом.
- Следующее: изолированные тесты формы и регрессии, затем Chrome desktop/mobile без отправки и изменений рабочей БД.

# DONE — ADM-DES-01 / ADM-DES-02: согласование оформления (2026-10-08)
- SEO/SA: единая светло-синяя шапка BREAD, границы/отступы, поиск слева, видимые фильтры, действия-иконки с названиями/подсказками и закреплением справа. Ссылки на сущность/страницу/заказ выделены синим.
- SEO: компактная статистика (2 колонки на мобильном, 4 на desktop), вкладки выровнены слева и прокручиваются. Редактор предложений согласован как явное действие «Редактировать и одобрить» (карандаш); штатная модальная валидация сохранена, inline редактор Voyager не копировался.
- SA: 7 основных колонок вместо 10 + unread-иконки; имя/телефон и диалог/дата объединены, непрочитанные строки выделены. Дополнительная сортируемая активность доступна через выбор колонок. Компактные входящие/исходящие слева/справа; кнопки бота в карточке, постоянная форма ответа под историей.
- Composer: подписанный Locked token сохраняется при polling и uncertain/partial результатах; подтверждённый ответ очищает черновик и создаёт токен следующего ответа. Прямые вызовы проверяют права; повтор неподтверждённого ответа не отправляет дубль. «Начать новый ответ» явно сбрасывает черновик.
- Проверки: BREAD/SEO + SA inbox + прежние команды — 92 tests/821 assertions PASS на PHP 8.3 с CLI memory_limit=512M. После добавления inline bot итоговый SaInboxTest — 15 tests/127 assertions PASS. PHP lint, view:cache и diff-check PASS. SQLite :memory:, fake HTTP/mail.
- Chrome desktop: SEO/SA список/карточка. Mobile 390px: SEO статистика/прокрутка вкладок, SA список/форма; document width 375px, overflow страницы нет, действие SA position:sticky и right=358px. Снимки design-*-updated и design-*-mobile в storage/app/testing.
- Подключение Chrome временно зависало при последнем снимке; восстановлено через свежую вкладку, финальный mobile список проверен, viewport reset выполнен. Рабочие данные/изображения/read flags не менялись; реальной отправки не было.
- DONE относится к согласованному оформлению новой панели, не к копии Voyager пиксель-в-пиксель. Следующее: ADM-AUD-04 Email Рассылка; внешний SA UAT, production queue и общий backlog остаются открыты.

# IN PROGRESS — ADM-DES-01: восстановить полный состав SEO полей (2026-10-08)
- Повторное сравнение оригинала выявило пропуск inline редактора keywords/title/description, полей источника, generated_at и ошибки в предложении. Прежний DONE по составу SEO интерфейса был преждевременным.
- Добавляются три редактируемых поля в каждой строке с подсказками/счётчиками, inline generate/approve, поля источника и дата/ошибка. Черновики сохраняются при polling. Следующее: изолированные тесты сохранения/прав/валидации и Chrome без изменений рабочей БД.

# DONE — ADM-DES-01: полный редактор SEO полей в строке (2026-10-08)
- Восстановлены постоянно видимые keywords/Meta Title/Meta Description в каждой строке, placeholder и подсказка keywords, счётчики длины. Возвращены title_field/description_field под URL, generated_at под статусом и ошибка рядом с редактором.
- Inline generate учитывает черновик keywords; inline approve сохраняет отредактированные meta/keywords через прежний сервис с проверкой статуса/прав/лимитов. Apply остаётся отдельным действием после одобрения. Модальные действия тоже загружают текущий черновик строки.
- Черновики не перезаписываются polling; после асинхронной генерации нетронутый черновик обновляется из результата. Read-only пользователь видит значения без редактора и прямую запись выполнить не может. Данных/миграций/БД-схемы не меняли.
- Проверки: 13 SEO tests/103 assertions PASS на SQLite :memory:, включая 3 новых теста inline approval, async refresh/keywords/limits и отказ без прав. PHP lint, view:cache, git diff --check PASS.
- Chrome: 10 редакторов и 30 полей (keywords1000/title60/description155), счётчик «Проверка»=8/60, тестовый черновик очищен. Операции generate/approve/apply в рабочей БД не выполнялись. Evidence storage/app/testing/seo-meta-fields-restored-2026-10-08.png.
- Уточнение предыдущего итога: сохранение только модального редактора не соответствовало оригиналу; этот пропуск исправлен. Следующее: внешний SEO UAT/production queue и прежний backlog ADM-AUD-04..12.

# IN PROGRESS — ADM-DES-01: компоновка SEO по before/after (2026-10-08)
- Убираются дубли генерации и лишние ID/язык/ошибка колонки; язык объединён с сущностью, ошибка остаётся в редакторе. Ячейки сверху, компактные поля с боковыми маркерами, действия справа.
- Возвращается постоянный select массового действия с подтверждением через штатный BulkAction. Следующее: SEO regression, Chrome desktop/mobile и evidence без записи рабочей БД.

# DONE — ADM-DES-01: компоновка SEO по before/after (2026-10-08)
- Строки выровнены сверху; keywords/title/description по 54px, обычные подписи и компактные счётчики. Блоки текущих meta с серыми маркерами, предложения — с голубыми.
- Генерация/одобрение перенесены в правую колонку и работают с текущим inline черновиком; нижние дубли удалены. Штатные модальные API, Apply/Reject и их подтверждения сохранены.
- Язык объединён с сущностью; отдельные ID/язык/ошибка убраны из компоновки, ошибка остаётся рядом с редактором. Поиск, фильтр языка, generated_at, поля источника и редактирование сущности сохранены.
- Постоянный select Generate/Approve/Apply/Reject + Применить использует штатные BulkAction/отбор/права/подтверждение. При нулевом выборе кнопка выключена; дубли штатных bulk-кнопок скрыты. Browser проверил выбор 1 записи и окно подтверждения, выполнение не подтверждалось.
- Проверки: 13 SEO tests / 103 assertions PASS (SQLite :memory:, Queue fake); PHP lint, view:cache и git diff --check PASS. Chrome desktop: 7 колонок вместе с checkbox, все TD vertical-align:top; первая строка около 324px. Mobile 390: document scrollWidth=clientWidth=375px, действия 32px; viewport reset.
- Evidence: storage/app/testing/seo-meta-layout-2026-10-08.png и seo-meta-layout-mobile-2026-10-08.png. Mobile снимок браузерного расширения масштабирован некорректно, размеры подтверждены DOM. Данные рабочей БД не редактировались, генерация/approve/apply не запускались.
- Следующее: внешний SEO UAT/production queue; задачи ADM-AUD-04..12 остаются в прежнем backlog.

# IN PROGRESS — ADM-AUD-02: аудит функциональной полноты SEO Meta (2026-10-08)
- Источники: legacy SeoMetaSuggestionController/browse, новый resource/List/service/job, общий scan/generator/config и BREAD generateSeoMeta.
- Найдено: entity_label отсутствовал в поиске; inline keywords не сохранялись в Apply/Reject; bulk approve допускал повторное одобрение approved с перезаписью редакции; null/пустое поле approve и draft fallback могли возвращать сгенерированный текст. Default scan only_empty отличался от Voyager.
- Следующее: исправления + изолированные тесты пустых полей/повторного bulk/keywords/поиска/default scan и сверка общей матрицы сценариев. Рабочие SEO/БД не изменять.

# DONE — ADM-AUD-02: аудит кода SEO Meta и исправление пропусков (2026-10-08)
- Сверены legacy controller/view и новый resource/page/service/job; общие scan/context/generator/client/config и генерация из BREAD сохранены. Матрица: docs/seo_meta_functionality_audit_2026-10-08.md.
- Исправлено: поиск entity_label и порядок updated_at/id; default scan only_empty=false; сохранение inline keywords при Apply/Reject с валидацией; bulk approve пропускает approved и не затирает ручную редакцию; пустые approved поля не возвращаются из generated. Snapshot после Apply читает фактические meta источника, включая поле, оставленное неизменным.
- 5 новых регрессионных тестов. Итог 18 SEO tests / 161 assertions PASS, SQLite :memory:, Queue fake/mock generator. PHP lint и diff-check PASS.
- Read-only сверка подключённой БД: 19 targets, все заданные title/description колонки существуют, 7 языков ru/en/lv/ee/lt/pl/de. Локальная очередь sync, API key настроен (значение не выводилось). Рабочие данные не изменялись.
- Осталось внешнее UAT: реальный вызов генератора и production queue/config. Наличие ключа и mock-тесты не доказывают внешний запрос. Реальный API в рамках аудита не вызывался. Следующее действие — согласованный сквозной тест на тестовой сущности; общий backlog остаётся открытым.

# IN PROGRESS — ADM-AUD-03: повторная сверка кода отдельных SA-диалогов (2026-10-08)
- Сверяются legacy AdminSaIntegrationController/views и новый resource/card/inbox/command/transport/badge/attachments.
- Найдено: отсутствует статус заказа; original_name старых вложений игнорируется; manager send не обновляет last_message_at; readToken вычисляет max после получения истории и может включить ещё не показанное сообщение. Модальные reply/bot видны на неподдерживаемых каналах.
- Следующее: исправить и проверить SQLite/fake transport, не отправлять клиентам и не менять рабочие read flags/orders/images.

# DONE — ADM-AUD-03: повторный аудит кода SA inbox (2026-10-08)
- Матрица сверки legacy/new: docs/sa_inbox_functionality_audit_2026-10-08.md. Проверены список/фильтры/карточка/создание и bind заказа/отправка/bot/read/attachments/права/idempotency.
- Исправлены статус заказа, original_name и fallback имени вложения, локальные public-disk URL для сохранённых новых файлов, обновление активности accepted send без перезаписи более позднего callback, snapshot read именно показанной истории. Modal Reply/Bot скрыты на non-WhatsApp.
- 6 новых regression tests. Inbox + общие command/chat: 67 tests / 587 assertions PASS, SQLite :memory:, HTTP/mail/storage fakes. PHP lint, view:cache, diff-check PASS. Рабочие данные и live отправка не менялись.
- Осталось внешнее SA UAT send/callback/delivery/bot и проверка MySQL concurrency/production transport; MIG-TEST-SA-LEGACY и ADM-AUD-11 simulator остаются открыты. Следующая задача админки ADM-AUD-04 — массовая Email Рассылка.

# IN PROGRESS — ADM-AUD-03: глобальная кнопка SA как в Voyager (2026-10-08)
- Global hook уже подключал badge на всех страницах, но показывал компактное синее SA. Возвращены полное название SA-диалоги, оранжевый фон и круглый красный unread counter; права/poll10s/звук сохранены.
- Следующее: Chrome несколько страниц/mobile, переход без открытия карточек и изменения read flags; проверка badge tests.

# DONE — ADM-AUD-03: плавающая кнопка SA и сохранение позиции (2026-10-08)
- Перенесены вертикальное перетаскивание мышью/Pointer Events для touch, сохранение позиции между страницами, ограничение положения границами окна и подавление перехода после drag. Полное название, оранжевый фон, unread badge, poll 10s, toast при росте count и сохраняемый переключатель звука.
- Chrome: кнопка на roles, SEO Meta и SA inbox; drag меняет top, reload сохраняет (70 → 87 → 104 px), обычный клик открывает inbox. Звук true сохраняется после reload; после проверки возвращён false. На мобильной ширине 375 px кнопка помещается в окно. Console errors отсутствуют.
- Badge/revocation: 2 tests / 16 assertions PASS; view:cache PASS. Screenshot: storage/app/testing/sa-floating-button-2026-10-08.png. Рабочие сообщения/read flags/заказы не менялись.
- Осталось UAT: реальное новое входящее с toast/звуком, физическое touch устройство и ранее открытые проверки production SA transport/MySQL concurrency. Следующая задача общего аудита — ADM-AUD-04.
## DONE — ADM-AUD-03: исправление свободного drag SA (2026-10-08)
- Исправлена причина прерывания drag: нативное перетаскивание ссылки браузером. Добавлены draggable=false и dragstart.prevent. Убран повторный pointer capture/lostpointercapture; движение и завершение отслеживаются на window.
- Предыдущая проверка движения на 17 px была недостаточной: она подтверждала начало движения, но не свободное перетаскивание.
- Chrome после исправления: полный drag вверх 425 → 128 px (−297), вниз 128 → 300 px (+172), reload сохраняет 300 px, URL roles не меняется. Blade view:cache PASS. Screenshot storage/app/testing/sa-floating-drag-fixed-2026-10-08.png.
- БД/API/transport не менялись, новых статических данных нет. Осталось ранее отмеченное UAT реального входящего toast/beep и физический touch.
# IN PROGRESS — ADM-AUD-03: полнота SA и сохранение модальных ответов (2026-10-08)
- Найдено: modal Reply/Bot в inbox и чате заказа закрываются при uncertain/partial/state_conflict, теряя token/черновик. Исправить сохранение открытой формы и проверить повтор без второго transport call.
- Сверить оставшиеся metadata карточки, подтверждение bot control, simulator и актуальность старых TODO ingress/read/imported data. Не включать внешнюю отправку и не перепривязывать рабочие данные.
## ADM-AUD-03 — повторная проверка полноты SA (2026-10-08)
Статус всей задачи: IN PROGRESS; исправления ниже DONE. Ранее DONE относилось к отдельным блокам, не полной приёмке SA.
- Исправлены модальные Reply/Bot в inbox и чате заказа: при uncertain/partial/state_conflict/ошибке token форма остаётся открытой с прежним ключом и черновиком. Повтор не создаёт вторую внешнюю отправку. Добавлены 2 Livewire regression tests на uncertain retry обеих форм.
- В карточку возвращены дата последнего сообщения (fallback из истории, если last_message_at пуст), номер заказа у сообщения, явный текст пустого сообщения и подтверждение bot control, как в оригинале.
- Проверки: Inbox + OrderSaCommand + OrderSaChat 69 tests / 616 assertions PASS; PHP lint, Blade view:cache PASS. Chrome: UAT-card /sa-conversations/39, сведения и confirmation dialog проверены, подтверждение отменено. Screenshot storage/app/testing/sa-card-completeness-2026-10-08.png. Внешних отправок и изменений live сообщений/read flags нет.
- Read-only текущая БД: 39 conversations / 140 messages; 1 связь с существующим заказом (UAT), 4 orders_id с отсутствующими orders. Это состояние локального снимка, а не основание для исправления production данных. Связи не менялись.
- Runtime config sa_commands_enabled=false: настоящая отправка/bot отключены. Настройки не изменялись. SA Simulator не перенесён (ADM-AUD-11).
- Старый TODO атомарного ingress устарел: SaMessageIngressService уже обеспечивает receipt и business writes в транзакции, контроллер блокирует order/conversation. Остаётся MySQL concurrency UAT; mock/SQLite не подтверждают production provider.
- Осталось: (1) повторный read-only аудит связей после импорта базы прода; (2) согласованный тестовый live send/callback/delivery/bot и входящее с toast/beep; (3) touch на физическом телефоне; (4) отдельный перенос нужных сценариев SA Simulator. Следующий шаг — SA UAT на тестовом контакте с проверкой настройки и endpoint, до перехода к Email Рассылке.
# IN PROGRESS — ADM-AUD-11: перенос SA Simulator (2026-10-08)
- Перенести все legacy presets, JSON editor, справку/cURL, validation details и результат API в Filament. Defaults брать из существующего DB resolver. API key остаётся только на сервере.
- Исполнение ограничить явным списком SA/Blog endpoints с проверкой прав; POST/PATCH подтверждаются пользователем. Не запускать сценарии над рабочей БД при проверке, исходящие CRM→SA сохраняют текущий выключатель отправки.
- Следующее: реализация страницы/dispatcher, изолированные success/validation/duplicate/permission tests и Chrome readonly UI.
## DONE — ADM-AUD-11: перенос SA Simulator (2026-10-08)
- Страница /filament/sa-simulator, меню и ссылки из списка/карточки SA. Все 34 legacy presets, editor, docs/cURL, response/validation details; defaults из существующего DB resolver. API key server-only, URL allowlist, повтор сохраняет idempotency keys, POST/PATCH с подтверждением.
- Исполнение через существующие API handlers + VerifyIntegrationApiKey, без loopback HTTP. Права перепроверяются; Blog writes требуют отдельные разрешения. Текущий выключатель внешних SA-команд сохранён, ADM-FIL-UAT outgoing блокируются всегда.
- SaSimulatorTest 6 tests / 38 assertions PASS. Совместный Simulator/Inbox/OrderSaCommand до дополнительного guard test 52 tests / 452 assertions PASS. Lint/view:cache PASS. Chrome GET catalog 200 и переключение presets/editor/docs, JS errors 0. Рабочие POST/PATCH не запускались.
- Evidence/ограничения: docs/sa_simulator_migration_2026-10-08.md; storage/app/testing/sa-simulator-2026-10-08.png. Multipart Blog media остаётся cURL, как в оригинале. Схема БД/API контракты не менялись, новых статических mappings нет; legacy sample payloads/docs сохранены.
- Следующее: согласованный SA live UAT на тестовом контакте (ingress/reply/callback/delivery/bot), звук/touch и связи после импорта production DB. ADM-AUD-03 целиком остаётся IN PROGRESS; реализация Simulator завершена.
# DONE — ADM-AUD-11: локальный прогон всех записывающих presets (2026-10-08)
- Подготовить отдельную временную MySQL БД с копией структуры и локальных справочников; рабочая БД только читается. Seed только тестовых клиентов/заказов/диалогов, внешние HTTP/mail/queue блокируются.
- Выполнить настоящие API handlers через simulator service для каждого write preset; проверить записи, duplicate и validation. Media проверить локальным файлом/изолированным storage. Результаты и найденные исправления фиксировать отдельно.
- Выполнено: 23/23 presets, 11 validation/no-mutation probes, pause/resume; отдельная локальная MySQL, Mail/Queue/HTTP/Storage fake. Исправлено сохранение data.bot_control.mode в pipeline-changed; 7 simulator tests / 47 assertions PASS. Полная регрессия 86 tests / 795 assertions, 1 legacy skipped. Evidence: docs/sa_simulator_local_write_audit_2026-10-08.md. Тестовые схемы удалены, рабочие данные/изображения не менялись.

## DONE — ADM-SA-UPDATE-01: реальная запись fields/tags/notes при PATCH lead
- Локальная проверка обнаружила: updateLead сообщает успех для update.fields/tags_add/tags_remove/notes_append, но сохраняет их только в event payload; бизнес-сущности не обновляются. Этап при update.stage сохраняется.
- Исходный план выполнен: сверены реальные источники, определено отображение полей, реализованы транзакционная запись и persistence/duplicate/rollback тесты. Полная приёмка SA остаётся IN PROGRESS по отдельным внешним/UAT задачам.
- Закрыто 2026-10-08: SaLeadUpdateService сохраняет поля/теги/структурированные notes в delivery.sa_lead, известные контакты — в существующие delivery keys, заметки — также append admin_comment; stage/phone синхронизируются с заказом/диалогом. Receipt и бизнес-запись транзакционны; lookup возвращает новый контакт, users профиль не меняется. Схема БД без изменений.
- Проверки: 18 новых tests / 152 assertions PASS; совместно Simulator 25 / 199 PASS. Общая регрессия до дополнительных cases 101 / 928, 1 legacy skipped. Local MySQL: 23 presets + fields/tags/notes persistence/duplicate/lookup и SQL failure rollback/retry PASS. Evidence docs/sa_lead_update_persistence_2026-10-08.md; рабочие данные/картинки не менялись. Следующий шаг — оставшиеся независимые SA UAT, общий SA IN PROGRESS.
# DONE — MIG-TEST-SA-LEGACY-01: PATCH lead, bot control и эскалации (2026-10-08)
- Восстановить обнаружение legacy SaLeadsUpdateTest в PHPUnit 12 и убрать зависимость SaConversationBotControlTest от рабочей схемы. Проверки SQLite :memory: с HTTP/Mail/Synvolve fakes; живые API/MySQL/browser write/UAT не запускать по просьбе пользователя.
- Следующее: поднять fixture schema, выполнить прежние сценарии, исправить устаревшие тестовые предположения и добавить проверки фактического сохранения. Остальные legacy suites учитывать отдельно, не считать весь backlog закрытым.
- Выполнено: SaLeadsUpdateTest (10 scenarios) и SaEscalationsCreateTest (8) используют PHPUnit attributes и CreatesSaChatSchema. Добавлены persistence assertions email/city/budget/tags/admin notes. Старый bot test заменён 4 актуальными сценариями OrderSaCommandService без заказа: pause/resume/handoff, повтор без второй отправки, отключённый transport.
- Общий запуск с messages/atomicity/persistence/Simulator/Inbox/OrderSaCommand/OrderSaChat: 136 tests / 1198 assertions PASS, без skipped. SQLite :memory:, HTTP/Mail/Synvolve fake; реальных отправок, MySQL прогонов и browser writes не было. Lint PASS.
- MIG-TEST-SA-LEGACY в целом IN PROGRESS: недоступные через /** @test */ остаются SaLeadsCreateTest, SaServicesCatalogTest, SaOrdersLookupTest, SaServiceSizesTest. Следующий точный шаг — изолированные fixtures/discovery для lookup/catalog/sizes, затем расширенный create suite. Live SA UAT отложен по просьбе пользователя.
# DONE — MIG-TEST-SA-LEGACY-02: оставшиеся SA API suites (2026-10-08)

- Исправлено: lookup пропускал товары SA-корзины с именованными ключами (`canvas_item` и др.). Проверка create → lookup воспроизвела дефект; теперь читаются товарные записи, метаданные корзины исключаются, SA service_id сохраняется для всех шести типов.
- Обнаружение PHPUnit 12 для lookup/catalog/sizes/create; fixture schema и синтетические данные вместо рабочей БД/skip. Внешние запросы/письма подменять, SQLite :memory: проверять явно.
- Evidence: четыре suites 81 tests / 886 assertions PASS; общий SA regression 217 tests / 2084 assertions PASS, 0 skipped. PHP lint и git diff --check PASS. JSON числа проверяются по числовому значению и типу, без зависимости от сериализации 22/22.0. MIG-TEST-SA-LEGACY закрыт в части восстановления всех обнаруженных suites; внешняя приёмка SA остаётся IN PROGRESS. Следующее — ADM-AUD-04 Email Рассылка; реальные отправки/MySQL/UAT не запускать.

# IN PROGRESS — ADM-AUD-04: аудит Email Рассылки по коду (2026-10-08)
- Сверить исходный EmailSenderController, UserRepository, AdminMailNotification и обе формы с текущим runtime; определить recipient filters, подписку/язык, preview, права, очередь и unsubscribe.
- Выполнять чтение кода/маршрутов; не отправлять письма, не запускать worker и не менять рабочую БД. После аудита зафиксировать точный объём переноса и проверки на SQLite/Notification fake.
- Аудит кода ADM-AUD-04: DONE; перенос рассылки IN PROGRESS. Отчёт: docs/email_sender_audit_2026-10-08.md. Подтверждены отсутствие Filament страницы/menu mapping/маршрута, прежнее пересечение recipient filters и news=YES.
- Найдены наследованные дефекты: json_decode(settings array) в персональной форме (TypeError воспроизведён без БД), salutation не используется, UserMessage пустая таблица не обработана; нет validation/подтверждения/защиты повтора и отдельного disabled guard массовой отправки. Не выдавать постановку в очередь за доставку.
- Проверки: SHA256 четыре legacy файла совпадают с исходником; router collection в testing/SQLite содержит unsubscribe, но не email-sender. Artisan route:list на пустой SQLite требует сторонние locales/venipak_data, использован безопасный fallback без создания контроллеров. Реальных HTTP/mail/worker/БД записей нет.
- Следующий точный шаг ADM-AUD-04A: Filament compose/recipient filters/search/count/preview, персональный вход, права, отдельный disabled guard, защита повторов/partial и Notification fake tests; подробный объём в отчёте. ADM-AUD-04 целиком не DONE.

# IN PROGRESS — ADM-AUD-04A: реализация Email Рассылки (2026-10-08)
- Страница /filament/email-sender и прежний порядок меню; пользователи/типы/языки, AND фильтры, news=YES, поиск, персональный ?id, фактический mail preview в sandbox.
- Использовать существующие browse_admin/browse_users/edit_users, повторно проверять доступ в действиях. Права/рабочие записи не менять.
- Новая таблица admin_email_campaigns: server-side snapshot аудитории/письма, token, владелец, состояние и результат. Транзакционная claim исключает повтор и автоматическую переотправку при uncertain/partial. Миграцию подготовить и проверить на SQLite, рабочую БД не мигрировать в этом прогоне.
- Отдельный bulk_email_enabled=false; для включённой массовой отправки требуется асинхронная очередь. Проверки только Notification fake, Mail fake и SQLite :memory:.

# ADM-AUD-04A — Email Рассылка: реализация и изолированные проверки (2026-10-08)
- Реализованы Filament /filament/email-sender, поиск/выбор пользователей, типы/языки, AND фильтры, подписка/email, ?id, sandbox preview фактического письма, подтверждение и точный queued/suppressed/uncertain результат. Пункт меню сопоставлен без изменения menu_items/порядка.
- Права существующие browse_admin/browse_users/edit_users перепроверяются; служебная миграция admin_email_campaigns хранит snapshot/token/owner/status/result. Claim транзакционный до queue calls; duplicate/partial/interrupted не переотправляются. Превью 30 минут, лимит 1000 адресатов.
- Отдельный bulk_email_enabled=false, .env и старые флаги не менялись. Включённая отправка требует async driver; Notification worker проверяет flag/news/email. Исправлены salutation, первая UserMessage и понятная ошибка отсутствующего uns_text с locale fallback ru; unsubscribe URL совместим.
- Проверки: 19 tests / 123 assertions PASS, 0 skipped (EmailCampaign + OrderRecipientEmailService + BreadMenuNavigation), SQLite/Notification/Mail fake/HTTP blocked. PHP lint восьми файлов и diff-check PASS. Миграция up/down проверена только на SQLite; рабочая БД/картинки/SMTP/worker не трогались.
- Evidence и ограничения: docs/email_sender_implementation_2026-10-08.md; storage/app/testing/email-campaign.xml. Код и офлайн проверки готовы. ADM-AUD-04 IN PROGRESS: осталось применить новую миграцию локально и проверить внешний вид без отправки, затем перейти к ADM-AUD-05 Клиенты. Реальный SMTP/queue UAT и MySQL concurrency остаются отложенными.

# DONE — ADM-AUD-04A / ADM-AUD-04: локальная Email Рассылка (2026-10-08)
- После изолированного прогона применена только новая служебная миграция к localhost/viar_laravel13; runtime database name и bulk flag проверены до миграции. Ранее указанное ожидание миграции закрыто. Существование browse_admin/browse_users/edit_users подтверждено read-only; права, users и изображения не изменены.
- Chrome read-only: /filament/email-sender открывается, все поля и тестовый режим видны, пункт меню на исходном месте с активной подсветкой, отступы/форма проверены визуально. Prepare/Confirm не нажимались, рабочие кампании не создавались, SMTP/worker не запускались. Вкладка оставлена открытой.
- Итог: локальный перенос готов; 19 tests / 123 assertions PASS, PHP lint/diff-check PASS. Отчёт docs/email_sender_implementation_2026-10-08.md. Реальный SMTP/production worker, MySQL concurrency и мобильная визуальная приёмка остаются отдельными проверками; настоящие отправки отключены.
- Следующее точное действие: ADM-AUD-05 — клиентский список, сравнение /admin/user_filter, колонки/подписка/страна и переход к заказам пользователя.

# IN PROGRESS — ADM-AUD-05: Клиенты (2026-10-08)
- Источник: legacy Admin/OrdersController::user_filter выбирает users.role_id=2; таблица содержит ID/email/имя/фамилию/телефон/адрес/индекс/страну/news и ссылку на заказы. Сохранить реальное правило роли, не заменять type_id.
- Добавить отдельную страницу, поиск/подписку/страну, пагинацию и сортировку; показать ссылку на существующий фильтр Orders user_id. Проверять browse_admin/browse_users, отдельно edit_users/browse_orders для ссылок. Бизнес-данные не менять; проверки SQLite.

# DONE — ADM-AUD-05: Клиенты (2026-10-08)
- Сверены legacy Admin/OrdersController::user_filter и user_filter.blade.php: реальный отбор users.role_id=2 и десять колонок. Новая /filament/clients сохраняет эти данные; добавлены поиск ID/email/имени/телефона/адреса/индекса/страны, пересечение фильтров подписки/страны, разрешённая сортировка и пагинация 10/25/50/100.
- Подсчёт orders_count подзапросом без N+1. Ссылка открывает filters[user_id][value] и tab=all, чтобы завершённые заказы тоже были видны. Используется существующая вкладка Все без изменения default current (исправлено по итогам browser QA). ID ведёт в существующий редактор users только при edit_users; ссылка заказов требует browse_orders. Страница/чтение/сортировка перепроверяют browse_admin+browse_users.
- Старый /admin/user_filter или route user_filter сопоставляется с /filament/clients; исходное место меню сохранено. Использовано существующее оформление management-table, горизонтальная прокрутка и Filament pagination. Новые DB поля/миграции не нужны.
- Проверки: Clients/BreadMenuNavigation/EmailCampaign/OrderRecipientEmailService 23 tests / 160 assertions PASS, 0 skipped; SQLite :memory:. Проверены role scope, фильтры, скрытие staff, количество и ссылки заказов, отсутствие статуса фильтра all, default current, пагинация/reset, сортировка allowlist и отзыв прав. PHP lint/diff-check PASS.
- Chrome read-only /filament/clients: десять колонок, 24 158 клиентов, фильтры, подсветка Пользователи → Клиенты, хлебные крошки и таблица проверены по AX/screenshot. Вкладка оставлена открытой. Рабочая БД/изображения не менялись, реальных отправок нет. Мобильный вид и реальные сохранения через редактор users этим этапом не проверялись.
- Следующее точное действие: ADM-AUD-06 — редактор BREAD metadata (поля/типы/связи/видимость); физические DB колонки добавляются миграциями, не автоматически через metadata.

# IN PROGRESS — ADM-AUD-05-QA: browser проверка клиентов
- Найдено и исправлено: Tab all ошибочно находился в getHeaderActions и давал 500 при переходе к заказам. Удалён из header actions; существующая вкладка all в getTabs сохранена; добавлена проверка типов header actions. Продолжить browser функциональные сценарии и узкий viewport.

# ADM-AUD-05-QA — локальная визуальная и функциональная проверка (2026-10-08)
- DONE: desktop и сценарии чтения Clients. Chrome: поиск codex-checkout находит 2 существующих тестовых клиента; NO даёт пустой результат, YES+LV оставляют 2; ID asc меняет порядок; 10 записей на странице; страница 2 показывает 11–20 из 24 158; поиск сбрасывает страницу на 1.
- Исправлен 500: Tabs\Tab ошибочно находился в getHeaderActions. Удалён из действий; существующая getTabs()['all'] сохранена. Добавлена проверка, что header actions являются Filament\Actions\Action.
- Исправлены URL: Filament принимает tab=all и filters, а не activeTab/tableFilters. Chrome подтвердил выбранную вкладку Все и единственный заказ №18450 клиента 43200. Такие же неверные параметры исправлены в ссылках order-lifecycle: Все заказы ранее и ID объединённых заказов; эти две ссылки отдельно в браузере не нажимались.
- Визуально: поле поиска расширено до 480px, добавлены интервалы между подписями и контролами; фильтры на узком экране занимают строки. У страны корректные стрелки/aria-sort. Принят и повторно просмотрен сохранённый desktop screenshot storage/app/testing/clients-browser-qa.png (только существующие тестовые клиенты).
- Проверки: Clients/BreadMenuNavigation/EmailCampaign/OrderRecipientEmailService/OrderLifecycleService — 29 tests / 199 assertions PASS; SQLite :memory:, внешние отправки не выполнялись. PHP lint Clients/ListOrders и git diff --check PASS. Данные users/orders и изображения не изменялись.
- TODO: мобильная визуальная приёмка — viewport override 390x844 не применился (DOM innerWidth=2560); временный override сброшен. Сравнение с открытой /admin/user_filter не завершено: legacy вкладка не отвечает в пределах timeout. Совместимость колонок/отбора подтверждена предыдущим чтением legacy кода, визуальное совпадение со старой страницей не заявляется. Сохранения в редакторе users не проверялись.
- Следующий точный шаг: завершить мобильную/legacy визуальную приёмку при доступном браузере; затем ADM-AUD-06 — редактор BREAD metadata.

# IN PROGRESS — ADM-AUD-05A: явное редактирование клиента
- Legacy user_filter: ID ведёт в users/{id}/edit; отдельного удаления/создания в этом списке нет. Новая ссылка ID уже существует, но действие незаметно.
- Добавить явную иконку редактирования с подписью/tooltip и edit_users guard; проверить существующую форму users без сохранения рабочих данных.

# DONE — ADM-AUD-05A: явное редактирование клиента (2026-10-08)
- Проверен resources/views/vendor/voyager/user_filter.blade.php: редактирование через ID и список заказов; отдельные create/delete действия в этом списке отсутствуют.
- В Clients добавлена явная pencil-square ссылка с доступным названием Редактировать клиента #ID; сохранена исходная ссылка ID, обе требуют edit_users. Последняя колонка закреплена и подписана Заказы / действия.
- Chrome: иконка клиента 43200 открывает /filament/bread/users/43200/edit; форма загрузила данные тестового клиента (email, имя, фамилию, телефон, адрес), включает остальные metadata-поля, Сохранить/Отмена. Сохранение не нажималось; реальные users/orders/images не изменены.
- ClientsTest 4 tests / 37 assertions PASS (SQLite :memory:); git diff --check PASS. Screenshot storage/app/testing/clients-edit-action.png. Дополнительная карточка Просмотр клиента в заказах уже существует в OrderLifecycleActions::viewClient; её действия здесь не запускались.
- Осталось: изолированная проверка сохранения полного редактора users и сравнение его полей с legacy; это не подтверждено проверкой открытия формы.

# IN PROGRESS — ADM-AUD-05B: язык клиента как в оригинале
- Источник users/edit-add.blade.php: отдельный select locale, верхних кнопок переводов нет. В новом users BREAD скрыты translation buttons; добавлено поле Язык пользователя, чтение settings.locale и сохранение с сохранением остальных settings. Изолированный тест preference/validation/неизменности пароля; рабочие записи не сохранять.

# DONE — ADM-AUD-05B: язык клиента как в оригинале (2026-10-08)
- Сверен локальный legacy users/edit-add.blade.php: select locale в форме пользователя, без верхнего переключателя переводов. Для users скрыты верхние language buttons в edit/read/list. Другие BREAD-разделы сохраняют переключение переводов.
- Добавлен Язык пользователя (__user_locale): читает виртуальный User.locale/settings.locale, fallback app.locale; допустимые языки проверяются Rule::in. В транзакции сохраняет settings.locale с сохранением остальных settings; отдельная колонка users.locale и записи translations не создаются. Миграции/изменения схемы не нужны.
- Изолированные проверки: 3 tests / 26 assertions PASS (новый preference test + существующие base/ru translation и shared draft tests); проверены сохранение языка, сохранность остальных settings и пароля, отклонение неизвестного языка. PHP lint и diff-check PASS.
- Chrome: верхние EN/RU/etc отсутствуют в редакторе 43200; select Язык пользователя имеет lv; роль и тип заполнены; Сохранить/Отмена видны. Рабочие данные не сохранялись. Screenshot storage/app/testing/client-language-preference.png.
- Осталось из предыдущих этапов: полная сверка остальных полей клиентского редактора и мобильная приёмка; текущий шаг подтверждает именно поведение языка.

# IN PROGRESS — ADM-AUD-05C: поля и сохранение редактора клиента (2026-10-09)
- Проверены legacy users/edit-add и vendor Voyager UserPolicy/UserController (C:/OSPanel/domains/asoft/viar), рабочие metadata users прочитаны без изменений.
- Исправить client_status на select user_types (отдельно от type_id), is_facebook_sale на 0/1, email validation и подписи. Сохранить date metadata и запрет редактирования news (edit=0). Восстановить запрет смены собственной роли.
- Обнаружены отдельные действия оригинала: запрос отзыва (включая клиента без заказов), PrintScreen ссылка, состояние приглашения, назначение заказов художнику. Не считать полную карточку перенесённой до покрытия этих действий; выделить ADM-AUD-05D.

# DONE — ADM-AUD-05C: поля клиентского редактора (2026-10-09)
- Источники: legacy users/edit-add, Voyager UserController/UserPolicy (read-only C:/OSPanel/domains/asoft/viar), users/data_rows/schema из локальной БД (read-only). В Chrome сверена открытая старая форма 43201 и обновлённая новая форма тестового пользователя 43200; записи не сохранялись.
- client_status теперь select из user_types с exists validation, отдельно от metadata связи type_id. is_facebook_sale select Да/Нет (1/0), email имеет формат и unique validation. Исправлены подписи имени/фамилии/телефона/адреса/бонусов/купонных полей. Существующие date1/date2 date controls, role/type relations и язык пользователя сохранены.
- Восстановлено правило Voyager self-edit: собственная роль disabled; на сервере role_id и roles pivots исключаются из изменений своего пользователя даже при подмене state. Edit других пользователей по существующему edit_users и allowlist metadata, связанная запись проверяется.
- Добавлены read-only подписка (news edit=0 сохраняется), Пригласил друга по inv_sale_code=alredy_used и PrintScreen ссылка (только корректный HTTP(S), rel noopener). Не менялись data_rows, схемы, пользователи/заказы/картинки в рабочей БД. Миграции не нужны.
- Изолированные Livewire/SQLite :memory: проверки: 5 tests / 48 assertions PASS. Сохранение client_status/type_id/bonuses/is_facebook_sale/active_coupon/inv_sale_code/date1/date2/torj1/torj2; неизменность news/password; invalid status/type/discount/email и duplicate email; собственная роль; язык и соседние translation workflows. Первоначальная ошибка теста null roles.display_name исправлена заполнением fixture, код dropdown ради fixture не изменялся. PHP lint и git diff --check PASS.
- Chrome: select Статус клиента показывает 8 реальных типов; Facebook=Нет; role/type/locale выбранные значения сохранены; русские подписи и summary видны. Browser save, mail и внешние действия не запускались. Screenshot storage/app/testing/client-fields-2026-10-09.png.

# TODO — ADM-AUD-05D: дополнительные действия карточки пользователя
- Перенести запрос отзыва из users/edit-add, включая пользователя без заказов, с существующим disabled mail guard и fake transport проверками; не использовать legacy GET, который сразу отправляет письмо.
- Для роли painter сравнить отдельное назначение заказов (painter_orders/update_painter_orders), перенести с проверкой прав и сохранением существующей логики назначений.
- Дальше: ADM-AUD-06 BREAD metadata editor. Мобильная визуальная приёмка списка и full editor остаётся открытой; полный визуальный дубль старой layout не заявляется.

# IN PROGRESS — ADM-AUD-05D: действия карточки пользователя (2026-10-09)
- Legacy users/edit-add: review link вызывает немедленную отправку для любого пользователя, painter role показывает активные orders и обновление назначений. update_painter_orders делает updateOrCreate пары order/user и удаляет невыбранные только для данного художника; пустой выбор использует ошибочный remove().
- Реализация: review через подтверждение и existing disabled mail guard; назначения отдельным действием, права browse_admin/edit_users/edit_orders, транзакция и только текущий painter. Сохранить метаданные оставленных назначений и чужие назначения. Проверки SQLite/fake, рабочие записи и письма не запускать.

# DONE — ADM-AUD-05D: действия карточки пользователя (2026-10-09)
- Добавлены Filament actions Запросить отзыв и Заказы художника в users edit. actionClient заново требует edit users и текущую users/editing/recordId; чужой BREAD или отсутствие прав не позволяют выполнить действие.
- Review: existing OrderReviewRequestService расширен sendToUser(User), send(Orders) сохранён как совместимый wrapper. Клиент без orders поддерживается; существующий review_request_enabled=false guard сохранён. Confirmation показывает email получателя из сохранённой записи; failed delivery не называется успехом. Legacy GET не вызывается. Новых endpoint/таблиц/миграций нет.
- Painter: только сохранённая роль painter и права browse_admin+edit_users+edit_orders; searchable multiple select активных заказов + уже назначенных завершённых, метки selected значений загружаются отдельно от лимита 50. Пустой выбор снимает только назначения текущего painter. Sync транзакционный с row locks, exists/distinct validation, повтор без дублей. Сохраняются in_work/прочие данные оставленных назначений и назначения других художников. Исторические completed назначения можно оставить, новые completed запрещены; это явное улучшение относительно legacy списка, где completed не показывались и могли сниматься при обычном обновлении.
- Проверки SQLite :memory:/Mail fake: 11 tests / 91 assertions PASS (действия+previous client editor/locale/translation). Проверены confirmation без отправки, suppression, fake отправка клиенту без заказов, invalid email, scope painter sync, repeat, clear, unknown/completed order, metadata preservation и отзыв прав. Рабочая БД, задания художников, mail и изображения не менялись.
- Chrome: Запросить отзыв открывает confirmation с email тестового клиента 43200; диалог отменён, Подтвердить не нажималась. Screenshot storage/app/testing/client-review-confirmation-2026-10-09.png. Painter modal проверен Livewire на fixtures; в рабочем Chrome назначения не открывались и не сохранялись. PHP lint новых action/service и diff-check PASS.
- Осталось: мобильная визуальная приёмка списка/карточки и модального окна painter на доступной локальной тестовой записи. Реальная доставка mail не проверялась и отключена.
- Следующий шаг: ADM-AUD-06 — редактор BREAD metadata, после фиксации текущего этапа.

# IN PROGRESS — ADM-AUD-06: редактор BREAD metadata (2026-10-09)
- Оригинальный VoyagerBreadController использует browse_bread; новая страница потребует browse_admin+browse_bread, права/роли не создаются автоматически.
- Добавить список существующих data_types, редактирование data_rows (подпись, тип, required, browse/read/add/edit/delete, порядок, details JSON), добавление metadata для существующей колонки или связи. Проверять schema/relationship contract и stale edits, сохранять только metadata транзакционно. Не изменять model/table/DB schema и business records.
- Проверки SQLite/UI read-only; рабочие metadata не сохранять. Прямое создание физической колонки остаётся миграцией.

# DONE — ADM-AUD-06: редактор BREAD metadata (2026-10-09)
- Добавлена /filament/bread-metadata: выбор data_types, список data_rows, изменение подписи/типа/порядка/required/visibility/details JSON; добавление описания существующей колонки или связи. Меню Дополнительно; права browse_admin+browse_bread.
- Сервис сохраняет только data_rows в транзакции; проверяет существование model/table/column, JSON object, duplicate field, belongsTo/belongsToMany и pivot, неизменность имени поля, stale fingerprint. Секретные и служебные поля защищены. Неизвестные JSON-ключи сохраняются.
- Проверки SQLite :memory:: 6 tests / 49 assertions PASS; сохранение/добавление/применение подписи и видимости/порядок/options/обе связи/invalid JSON и schema/duplicate/stale/foreign row/отзыв прав/секретные поля. Бизнес-записи и pivot не меняются.
- Chrome read-only: новая ссылка меню, переключение Gallery Holsts, реальные колонки и flags, открытие name, форма и Отмена. Сохранение не нажималось. Screenshot storage/app/testing/bread-metadata-editor-2026-10-09.png. PHP lint и git diff --check PASS.
- Инструкция и ограничения: docs/bread_metadata_editor.md. Физические колонки создаются миграцией; data_types/model/permissions/menu редактор не создаёт; специальные формы могут переопределять metadata; many-to-many применяется в edit, не create. Неподдерживаемые типы связей отклоняются. Рабочие metadata/данные/картинки не изменены.
- Осталось отдельно: мобильная приёмка клиентских страниц/модального окна painter и аудит разделов, которым нужны специальные overrides. Следующее действие: сверить оставшийся backlog ADM-AUD с оригиналом и выбрать следующий неперенесённый сценарий.

# DONE — ADM-AUD-06A: сравнение создания/редактирования BREAD с Voyager (2026-10-09)
- Chrome read-only: old /admin/bread, gallery_holsts/edit, abandoned_carts2/create; new bread-metadata/Add field/Cancel. Код legacy Controller/DataType/templates и новый service/page/registry сверены. Сохранение не выполнялось; данные/картинки не менялись.
- Полное сравнение: docs/bread_metadata_comparison.md. ADM-AUD-06 DONE означает редактор отдельных data_rows, НЕ полный перенос Voyager BREAD.
# TODO — ADM-AUD-06B: общие настройки раздела и применение sorting/search/scope в runtime
# TODO — ADM-AUD-06C: создание раздела для существующей таблицы, schema/default fields/model/menu/permission generation без auto grant
# TODO — ADM-AUD-06D: конструктор связей, hasOne/hasMany, ключи many-to-many/tagging и удаление metadata
# TODO — ADM-AUD-06E: пакетное сохранение, drag order, JSON editor, metadata translations и аудит недостающих типов полей
# TODO — ADM-AUD-06F: блокировка записи недоступного раздела и согласование required с SQL schema
- Следующий точный шаг: ADM-AUD-06B, сначала определить поддерживаемые настройки data_types и существующие источники query/order/scope. Риск: копирование controller/policy/model строк не обеспечивает совместимость с Filament; нужно подключение поведения в runtime, не только сохранение metadata.

# IN PROGRESS — ADM-AUD-06B: общие настройки BREAD (2026-10-09)
- Реализовать названия, slug, icon, description; order_column/order_direction/default_search_key и разрешённые локальные scopes модели применять в общем списке. Проверять таблицу/колонки, уникальность slug, параллельные изменения, сохранность остальных details.
- model/controller/policy только читать: перенос произвольных классов не обеспечивает совместимость. Создание разделов, выдача прав и управление reorder-order_display_column относятся к отдельным задачам.
- SQLite fixtures + Chrome без сохранения рабочей БД. При смене slug обновить ссылки существующего меню, привязанные к старому BREAD route/url; бизнес-данные не менять.

# DONE — ADM-AUD-06B: общие настройки существующего BREAD-раздела (2026-10-09)
- Добавлены Название одной записи/раздела, slug, icon, description, order_column/order_direction/default_search_key/scope. Классы model/controller/policy показаны read-only. Service проверяет существующую модель/таблицу, slug uniqueness, allowlist колонок/scopes, fingerprint; сохраняет data_types и остальные details без потери неизвестных ключей.
- Общий список применяет scope/sort/search; title/description читаются из metadata; singular используется в редакторе записи. Без настроек сохранены ID DESC и поиск по четырём visible text columns; сортировка имеет ID tie-breaker. Служебные translation scopes исключены; переводы значений работают существующим механизмом.
- Смена slug обновляет только связанные Voyager route и относительные /admin/<slug> в menu_items, сохраняя query strings. Старые прямые ссылки не редиректятся; абсолютные menu URL не переписываются. Menu title/icon имеют приоритет, datatype icon используется как fallback.
- SQLite: весь VoyagerBreadTest 73 tests / 711 assertions PASS; после финальной правки singular/scopes targeted settings 2 tests / 29 assertions PASS. PHP lint/diff-check PASS. Chrome read-only: Gallery Holsts settings загружают текущие данные/колонки, форма просмотрена и отменена; рабочие данные/картинки не сохранялись.
- Ограничения/инструкция: docs/bread_metadata_editor.md. Dedicated pages имеют свои запросы; order_display_column/reorder, server_side toggle, permission generation, model/controller/policy editing не реализованы и относятся к следующим этапам. Схема БД и API не менялись, миграции не нужны.
- Следующий точный шаг: ADM-AUD-06C — создание BREAD-раздела для существующей физической таблицы с выбором модели, schema defaults и подключением меню. ADM-AUD-06D/E/F остаются TODO.

# IN PROGRESS — ADM-AUD-06C: создание BREAD для существующей таблицы (2026-10-09)
- Сверить original /admin/bread index/create и BreadAdded listeners, добавить выбор таблицы без data_types, выбор существующей совместимой модели, schema defaults/preview, create data_type+rows+optional admin menu+permission definitions транзакционно. Ролям права не выдавать.
- Таблица должна иметь единственный primary key id; неподдерживаемая модель/схема — явная ошибка, без создания PHP классов и изменения бизнес-таблиц. Сохранение проверять в SQLite; Chrome только просмотр/отмена.

# DONE — ADM-AUD-06C: создание BREAD для существующей таблицы (2026-10-09)
- Добавлен выбор таблиц текущей схемы без data_types; новые BREAD создаются для существующей модели app/Models с единственным primary id. Автоматически подготовлены названия/slug и поля по SQL type/nullability/default; поля можно настроить до общего сохранения (подпись/type/order/flags/details). Имена/состав физических колонок фиксированы.
- Транзакционно сохраняются data_type/data_rows, опционально definitions browse/read/edit/add/delete и корневой пункт admin menu в конец. Permission_role не меняется; доступ к новому разделу остаётся по browse_<table>. Duplicate, invalid model/JSON/field, missing menu и stale schema отклоняются с откатом; ошибка JSON содержит имя поля. Секретные/служебные колонки защищены.
- Original Chrome: /admin/bread и abandoned_carts2/create. Сверены legacy Controller/DataType + AddBreadMenuItem/AddBreadPermission: старый listener по config может назначить права admin. Новый этого не делает. Model/controller/policy не генерируются; серверная пагинация фиксирована; sort/search после создания; relationship constructor/полный набор компонентов остаются следующие задачи.
- Chrome new: создание abandoned_carts2, семь колонок/SQL types/default fields/names/slug, отсутствие совместимой модели явно объяснено; форма отменена, Сохранить не нажималась. Screenshot storage/app/testing/bread-create-section-2026-10-09.png. Первоначальное перечисление затрагивало другие schemas MySQL; исправлено явным getCurrentSchemaName и повторно визуально проверено — только текущая БД.
- SQLite :memory: 11 tests / 114 assertions PASS (3 creation + прежние metadata/settings), включая бизнес-данные без изменений, definitions без auto grant, menu link, runtime list/edit после создания, invalid/duplicate/model/schema/menu/secret fields/transaction rollback/отзыв прав. На промежуточном шаге nested validation отфильтровала поля строк; исправлена передача полной строки в валидирующий BreadMetadataService, финальный прогон PASS. PHP lint и diff-check PASS.
- Документация обновлена docs/bread_metadata_editor.md и docs/bread_metadata_comparison.md. Схема и API не менялись; рабочие BREAD/permissions/menu/business records/images не записывались. Миграция не нужна.
- Следующий точный шаг: ADM-AUD-06D — конструктор связей с таблицами/колонками/key/label/pivot и аудитом поддержки hasOne/hasMany. ADM-AUD-06E/F остаются TODO.

# DONE — ADM-AUD-06C-LIST: отдельный список всех физических таблиц, как Voyager /admin/bread (2026-10-09)
- Добавлена /filament/bread-tables: таблицы только текущей схемы, состояние BREAD, поиск по таблице/названию раздела, ссылки Открыть / Редактировать BREAD / Добавить BREAD. Пункт Инструменты → BREAD ведёт на каталог; редактор имеет обратную ссылку.
- Query section выбирает существующий раздел, create открывает создание для выбранной таблицы. Browse учитывает разрешение и наличие модели. SQLite: 13 tests / 132 assertions PASS; Chrome: оригинал /admin/bread, поиск abandoned_carts, переходы Edit/Add и возврат, визуальная проверка. Рабочие данные не записывались.
- Ограничения: удаление BREAD и конструктор структуры БД ещё не перенесены; создание требует существующую совместимую модель. Следующий шаг ADM-AUD-06D — конструктор связей.

# DONE — ADM-AUD-06C-EDIT: единая форма редактирования BREAD по оригиналу (2026-10-09)
- Вместо обзора при входе из каталога сразу показать общие настройки и редактируемые строки: SQL type/key/nullability, visibility, input type, display name, JSON, order; единое транзакционное сохранение с проверкой конкурентных изменений. Сравнить одинаковую таблицу в Chrome без сохранения рабочих данных.

# DONE — ADM-AUD-06C-EDIT: единая форма редактирования BREAD (2026-10-09)
- Общие настройки + редактируемая таблица полей с единой транзакцией, stale guards и rollback. Chrome сравнение одинаковой a_collage_advantage_screen PASS; SQLite14/151 PASS. Evidence в MIGRATION_PROGRESS.md.
# TODO — ADM-AUD-06G: остальные возможности BREAD-редактора Voyager
- Изменение model/controller/policy, generate_permissions/server_side с корректным runtime, переводы data_types/data_rows, drag ordering, незарегистрированные колонки. Не считать полный редактор Voyager перенесённым до закрытия этих пунктов и ADM-AUD-06D/E/F.

# IN PROGRESS — ADM-AUD-06E: оставшийся функционал страницы BREAD (2026-10-09)
- Подтверждено по legacy Controller/DataType/DataRow: переводы metadata, drag ordering, физические колонки без metadata, constructor relationships, generate_permissions/server_side, смена классов и полный набор input types. Реализовать итеративно с SQLite и Chrome без записи рабочей БД; не объявлять полную parity до закрытия всех возможностей.

# DONE — ADM-AUD-06E-A: переводы metadata, порядок строк и колонки без BREAD (2026-10-09)
- Languages/drafts/base attributes/stale translations guards; registry labels по языку интерфейса; HTML5 drag+numeric order; schema columns without data_rows. Общий Save атомарен. SQLite full BREAD suite 83/839 PASS, Chrome read-only; MIGRATION_PROGRESS.md содержит evidence.
# IN PROGRESS — ADM-AUD-06D: конструктор связей
- Реализованы belongsTo/belongsToMany builder/edit/staged delete, validation и atomic Save. hasOne/hasMany readonly и taggable общего BREAD Add/Edit перенесены. Специальные controller store-сценарии остаются в ADM-AUD-06G; полная parity ещё не DONE.
# IN PROGRESS — ADM-AUD-06G: классы, режимы и компоненты
- Реализованы compatible model selection и generate_permissions definitions без grants. Остались controller/policy/class generation, server_side off/client pagination, остальные Voyager/VE field components, icon picker и специализированный JSON editor. Full parity не DONE.

# DONE — ADM-AUD-06D-CHILD: hasOne/hasMany по реальному поведению Voyager (2026-10-09)
- Source audit: vendor/tcg/voyager/resources/views/formfields/relationship.blade.php показывает hasOne/hasMany readonly даже при Edit/Add; attach/detach отсутствуют. Перенести builder mappings (child.column ↔ parent.key), валидацию модели/колонок, browse/read/edit вывод с scopes дочерней модели. Не добавлять запись дочерних сущностей. Проверить SQLite (включая попытку подмены payload) и Chrome без сохранений рабочей БД.

- Реализовано: builder четырёх типов, child.column/parent.key, readonly browse/read/edit/add, scopes/SoftDeletes, проверки модели/колонок. SQLite full BREAD suite 85 tests /863 assertions PASS; Chrome hasMany mapping PASS без Save. Следующий шаг: taggable belongsToMany. Полная parity BREAD остаётся IN PROGRESS.

# DONE — ADM-AUD-06D-TAG (общий BREAD Add/Edit): taggable belongsToMany (2026-10-09)
- Сверить legacy tagging store: add permission, addRows validation, immediate related record creation; перенос через createOption Filament с повторной серверной проверкой metadata и прав. SQLite без рабочей БД.

- ADM-AUD-06D-TAG evidence: SQLite 85 tests /901 assertions PASS (direct PHPUnit, memory_limit=512M); targeted Add/Edit+constructor 2/63 PASS. Chrome checkbox read-only PASS. Новых endpoint/миграций нет, рабочая БД не записывалась.
# TODO — ADM-AUD-06G-TAG-CUSTOM: taggable для специальных store-контроллеров
- Общий taggable не обходит custom controller/dedicated resources. Переносить отдельно после аудита их store/validation/hooks. Следующий точный шаг ADM-AUD-06G: аудит недостающих Voyager/VE типов полей и настройка их runtime; controller/policy/class generation и client pagination остаются TODO.
# DONE — ADM-AUD-06G-FIELDS-A: radio_btn/select_multiple/time/markdown_editor (2026-10-09)
- Read-only DB audit: 19 типов, adv_media_files 19 строк уже имеет отдельный runtime. Четыре стандартных типа отсутствуют в текущем snapshot, но доступны в оригинальном конструкторе. Перенести options radio/multiple, JSON list encoding, time и Markdown; relationship-backed select_multiple и coordinates/VE layouts выделить отдельно.

# TODO — ADM-AUD-06G-FIELDS-B: остальные поля и options parity
- coordinates: проверить spatial storage/map/API-key зависимости.
- select_multiple.relationship: Eloquent relation/list method и editablePivotFields; не заменять обычным JSON input.
- VE adv_json, adv_fields_group, adv_image, adv_select_dropdown_tree, adv_page_layout: отдельные контракты; adv_media_files уже перенесён, настройка его типа в metadata требует отдельной проверки.
- code_editor сейчас textarea без syntax highlighting; специализированные JSON/icon editors остаются TODO.
- Следующий точный шаг: adv_json — проверить legacy json_fields/fields/rows формат и реализовать редактирование строк без изменения существующих данных при открытии.
- FIELDS-A evidence: radio_btn/select_multiple/time/markdown_editor + hidden business runtime; SQLite full BREAD 87 tests /953 assertions PASS, targeted2/52 PASS; Chrome новые options read-only PASS. Схема и рабочие данные не менялись. Следующий шаг FIELDS-B adv_json.

# DONE — ADM-AUD-06G-ADVJSON: VE JSON rows editor (2026-10-09)
- Legacy adv_json.js: envelope fields + rows, add/delete/edit/drag sorting; сохранить формат и прежние ключи, malformed payload не обнулять. Repeater Add/Edit + metadata json_fields + SQLite проверки переводов/валидации. Рабочая БД read-only.

- ADVJSON evidence: SQLite full BREAD 89 tests /998 assertions PASS (66.991s); targeted2/45 PASS, Chrome metadata option read-only, lint/diff check PASS. Рабочая БД/картинки не менялись. Следующий шаг: adv_fields_group; общий FIELDS-B/ADM-AUD-06G остаётся IN PROGRESS.

# DONE — ADM-AUD-06G-GROUP: adv_fields_group (2026-10-09)
- Сверить VE view/content handler, поддержать text/number/textarea в fields envelope с сохранением definitions и неизвестных свойств, readonly malformed. SQLite Add/Edit/translations и metadata validation без рабочей БД.

- GROUP evidence: full SQLite BREAD90 tests /1027 assertions PASS (58.725s); targeted1/29 PASS, lint/diff check PASS. text/number/textarea, defaults, translations, preservation и malformed readonly реализованы. Следующий шаг adv_image; full parity ADM-AUD-06G не DONE.

# DONE — ADM-AUD-06G-ADVIMAGE (общий runtime): одиночная коллекция Spatie (2026-10-09)
- Legacy VE adv_image view/insertUpdateData: getFirstMedia(field), upload into collection, title/alt custom properties. Add/Edit upload deferred to parent Save, compact preview/delete, read/browse. SQLite fake storage, без рабочей БД.

# TODO — ADM-AUD-06G-MEDIA-RECOVERY: ошибки файловой системы
- SQL rollback не восстанавливает удалённые/созданные файлы Spatie. Перед production проверить disks/conversions/hooks и предусмотреть восстановление при частичном сбое замены; не заявлять атомарность DB+filesystem.
# DONE — ADM-AUD-06G-TREE (см. evidence ниже): adv_select_dropdown_tree
- Следующий точный шаг: legacy parent/label/options contract и runtime выбора дерева. Проверить на SQLite без добавления metadata в рабочую базу.
- ADVIMAGE evidence: final full SQLite BREAD91 tests /1057 assertions PASS (72.949s), targeted1/30 PASS; lint/diff check PASS. Add/Edit upload/replace/ALT/TITLE/delete/read/browse реализованы. Production storage/hooks и MEDIA-RECOVERY остаются TODO. Следующий шаг TREE; весь ADM-AUD-06G не DONE.

# DONE — ADM-AUD-06G-TREE (BelongsTo id/plain options): VE dropdown tree (2026-10-09)
- Source view/helper: relation method camel(field), optional List/where, id/parent_id traversal; plain options fallback. Scalar selection, custom options/zero sentinel, runtime validation/scopes/cycle protection, SQLite without working writes.

- TREE evidence: full SQLite BREAD92 tests /1086 assertions PASS (57.258s), lint/diff check PASS. Runtime plain options/BelongsTo id, where/List, scopes/tree/Read/Browse/validation. Other relation/owner-key configurations and callable defaults remain unsupported; full ADM-AUD-06G parity не DONE.
# DONE — ADM-AUD-06G-LAYOUT (общий runtime): adv_page_layout (2026-10-09)
- Следующий точный шаг: прочитать legacy view/content handler/layout JS, определить JSON/layout blocks контракт и реальные источники; затем перенести совместимый runtime и проверить SQLite без записи рабочей БД.
- Контракт оригинала подтверждён: JSON list секций {type,title,icon,key}; Field из layout_fields, Block из block_model(status=1, order), Form из form_model(status=1). Добавление, удаление и сортировка; контент самих блоков здесь не редактируется. Перенос через Repeater с серверной проверкой каталога и сохранением старых секций.

- LAYOUT evidence: full SQLite BREAD93 tests /1135 assertions PASS (55.249s); targeted1/49 PASS, lint/diff check PASS. JSON list, Field/Block/Form catalog, add/remove/reorder, preservation, translations и metadata validation реализованы. Read-only DB audit: 0 используемых adv_page_layout. Browser/production sources для этого типа не проверены. Full ADM-AUD-06G parity остаётся IN PROGRESS.
# IN PROGRESS — ADM-AUD-06G-COORDINATES: spatial field
- Следующий точный шаг: сверить Voyager coordinates form/content handler и пространственное хранение, зависимости карты/API key и реальное использование; определить совместимый runtime без изменения рабочей схемы/данных. Отдельно остаются select_multiple.relationship/pivot, code_editor highlighting, options parity и MEDIA-RECOVERY.
- Аудит 2026-10-09: legacy Coordinates::handle пишет ST_GeomFromText('POINT(lng lat)'), Spatial читает ST_AsText; форма Google Maps+Places/marker drag. Текущая DB mysql, coordinates rows=0, googlemaps.key не настроен; модели Spatial отсутствуют. Этап A: manual lat/lng editor, только POINT/GEOMETRY, server validation/read/browse/save; SQLite spatial-function fixture. Карта/Places/callbacks и реальный MySQL roundtrip остаются отдельной проверкой, рабочая DB не записывается.

# TODO — ADM-AUD-06G-COORDINATES-MAPS: Google Maps/Places parity
- Реальный mysql spatial roundtrip, SRID ограничения, карта/marker drag/address autocomplete, static maps/read maps и show-input/onChange options не проверены/не перенесены. API key в текущем config отсутствует. Записи в рабочую базу запрещены; пока ручной Point editor, без внешних вызовов и автозаписи центра.
# DONE — ADM-AUD-06G-CODE (native editor): code_editor (2026-10-09)
- Следующий точный шаг: сверить legacy language/theme/options и перенести syntax highlighting вместо textarea, с сохранением исходного текста. Реальные write-tests не использовать.
- Аудит: legacy Ace language/theme/default, синхронизация hidden textarea; Filament уже содержит CodeEditor/CodeMirror и локальный asset. Real rows: glob_config.analytics/analytics_body, blog_reklama_type.source, abandoned_carts.cart_data; details={} у всех. cart_data имеет array cast — нужен raw JSON fill и decode перед Eloquent assignment, unchanged raw оставить без переприсваивания. Syntax language из details с безопасными aliases, legacy theme не исполнять как путь/JS.

# DONE — ADM-AUD-06G-COORDINATES-A: manual POINT runtime (2026-10-09)
- Широта/долгота Add/Edit, диапазоны, nullable/required, POINT parsing/read/browse, legacy ST_GeomFromText(lng lat), metadata guards и readonly other geometry. Evidence: full SQLite BREAD94 tests /1179 assertions PASS (64.213s), targeted1/44 PASS; PHP lint/diff check PASS. Spatial functions в SQLite явно эмулированы WKT; реальный MySQL и Maps не проверены. Полный COORDINATES и ADM-AUD-06G IN PROGRESS. Следующий точный шаг CODE: аудит и перенос syntax highlighting.

- CODE evidence: full SQLite BREAD95 tests /1234 assertions PASS (67.714s, memory132MB); targeted1/55 PASS, PHP lint/diff check PASS. Chrome Source HTML highlighting/line numbers/folding read-only PASS. Metadata language/default validation, text/translations preservation and array/json cast handling implemented. Ace theme exact rendering not ported (uses global admin theme), other casts readonly. Working DB/images untouched.
# DONE — ADM-AUD-06G-SELECT-REL (BelongsToMany/HasMany source): select_multiple.relationship / List / editablePivotFields (2026-10-09)
- Следующий точный шаг: сверить оригинальные select_multiple view/content handler и реальные configs, определить relation/list method/pivot contract, затем перенести совместимый runtime с SQLite fixtures. Сохранить model scopes/permissions и не заменять relation JSON-колонкой.
- Уточнение по реальному коду (2026-10-09): select_multiple.blade.php вызывает camel(field) и optional *List для options; SelectMultiple::handle возвращает JSON list либо JSON map key=>editablePivotFields. Controller::insertUpdateData присваивает колонке для type=select_multiple; sync выполняется только для type=relationship/belongsToMany. Поэтому прежнее предположение выше про запрет JSON-колонки неверно: переносить физическую JSON/text колонку, без нового implicit pivot sync. В текущей DB select_multiple rows=0.
- Scope: relation-source BelongsToMany/HasMany, safe key/label/List, editablePivotFields только существующие бизнес-колонки pivot (BelongsToMany), JSON persistence с model hooks и проверками scopes/SoftDeletes; source selection при пустой колонке из relation, после сохранения из JSON для working roundtrip (отличие от legacy re-open только по relation).

- SELECT-REL evidence: full SQLite BREAD96 tests /1288 assertions PASS (71.056s, memory132MB); PHP lint/diff check PASS. Последняя same-connection guard дополнительно проверена targeted1/54 PASS. JSON-column persistence по реальному legacy контракту, searchable multiple/Repeater, scoped List/order/SoftDeletes, safe pivot attributes, Add/Edit/read/browse/shared language/array cast реализованы. Физические связи не синхронизируются этим типом. Browser/production hooks для него не проверены, текущая DB не содержит такого типа. Другие relations/casts остаются unsupported.
# IN PROGRESS — ADM-AUD-06G-OPTIONS: общие параметры полей
- Следующий точный шаг: аудит legacy text/number/text_area/select_dropdown details и текущих real metadata, определить не перенесённые display/default/validation/source options; переносить партиями на SQLite без рабочих DB writes. Maps, production media recovery и полный options parity остаются TODO.
- OPTIONS-A (2026-10-09): legacy text.placeholder; number min/max/step(any default); text_area.display.rows(default5); validateBread base rule + add/edit rules + messages. Real audit: text.validation8 (required/max255), number min/max4/step7, display.width26; select_dropdown только static options/default19, relationship отсутствует. Первая партия: scalar common fields placeholder/rows/bounds/step и safe validation subset/messages/actions. display.width, slug generator, dropdown relationship и сложные DB/regex/custom rules отдельно TODO.

# DONE — ADM-AUD-06G-OPTIONS-A: scalar validation / number constraints / placeholder / rows (2026-10-09)
- Runtime text/number/text_area применяет safe validation base+add/edit/messages, placeholder/rows, min/max/step/finite checks; taggable scalar create соблюдает merged validation. Metadata invalid guards и readonly unsupported settings. Evidence: full SQLite BREAD97 tests /1349 assertions PASS (65.827s, memory132MB), targeted1/61 PASS; PHP lint/diff check PASS. Working DB/images untouched, Chrome партия не проверялась.
# DONE — ADM-AUD-06G-OPTIONS-LAYOUT: display.width
- Следующий точный шаг: сверить legacy 12-column display.width и текущие form/tab schemas, применить responsive spans только в общем BREAD; проверить существующие width3/6/12 и отсутствие overflow/регрессий language/tabs. Затем dropdown relationship/List/where и slug generator. Unique/exists/regex/custom dependent validation, остальные type options остаются TODO. Весь OPTIONS/ADM-AUD-06G IN PROGRESS.
- Legacy base/VE edit-add применяет col-md-{display.width ?? 12} ко всем row wrappers. Перенос: root и tab grid12 на lg, ниже — 1 колонка; row spans1..12, default12. Дополнительные sections/full spans сохранить. Новые malformed widths отклонять в metadata, существующие malformed widths визуально fallback12 без изменения данных. Read-only audit выявил width3/6 metadata для отсутствующей canvas table; наличие таблиц проверять перед запросом записей.

## ADM-AUD-06G-OPTIONS-LAYOUT — DONE, 2026-10-09
- Legacy Voyager/VE edit-add uses col-md-{display.width ?? 12} for every field. Added BreadFormLayout: responsive grid default1/lg12; all root/tab fields use configured spans1..12, default12. Locale/account auxiliary controls remain full width. Small screens stack fields; Filament lg starts1024px (legacy Bootstrap md992px).
- New metadata rejects invalid display/width; existing invalid width falls back12 without rewriting values/metadata or disabling editing. Applies to scalar/relation/media components, not only text. No schema/API changes.
- Read-only DB audit: canvas metadata references missing physical table; guarded audit found canvas_req and req_photo_1..4 rich_text_box width3. Chrome canvas-req/1/edit desktop: four quarter-width editors per row, toolbars wrap; mobile390x844: editors stack, document clientWidth375 equals scrollWidth375. Viewport restored. No content entered or Save pressed.
- SQLite targeted1 test /51 assertions PASS: widths3/6/12, root/tab grid, Add/Edit, shared language state/translations, metadata rejection and legacy invalid-width preservation. Full BREAD98 tests /1400 assertions PASS (92.048s, memory132MB). Working DB/images untouched.
- OPTIONS and full ADM-AUD-06G remain IN PROGRESS. Next exact action: audit select_dropdown relationship/List/where/default sources against original, implement supported cases with SQLite fixtures; then slug generator and complex validation. Maps/media recovery remain TODO.
# DONE — ADM-AUD-06G-OPTIONS-SELECT (BelongsTo/scalar defaults, 2026-10-09)
- Audit legacy select_dropdown: camel(field) relation source, List overrides where, custom options/_empty_, scalar default; Class@method defaults require separate trusted resolver and remain unsupported. Implement scoped BelongsTo scalar-column sources and validation on SQLite; working DB read-only.

## ADM-AUD-06G-OPTIONS-SELECT — IN PROGRESS, 2026-10-09
- Source: legacy Voyager formfields/select_dropdown.blade.php. camel(field) relation, configured key/label, List collection takes precedence over relationship.where; custom options and _empty_ sentinel; scalar default when field null. Original callable Class@method default is not ported: separate trusted resolver remains TODO.
- Added BreadDropdownOptions: BelongsTo with matching FK/owner key, same connection, existing safe key/label columns; scoped related queries and SoftDeletes; optional List preserves order and overrides where, scalar custom options. Unsupported configs readonly/nondehydrated, original value preserved. Static options continue working. Generic Read/Browse displays option labels with current source record.
- Runtime Save verifies submitted selection against current options; Add default, Edit scalar-column persistence, custom sentinel, shared locale tested. No implicit relation/pivot writes. Metadata validates source even without static options; rejects malformed sources/defaults. Other relation types remain unsupported.
- Working DB read-only audit: 21 select_dropdown rows, relationship0, callable defaults0, malformed option labels0. No content values printed; no DB/image writes. Hence no real browser relation-source fixture is available; render/runtime verified in Livewire SQLite.
- Targeted SQLite PASS: 1 test /32 assertions (2.243s): source where/SoftDeletes, List precedence, Add/Edit/default/shared locale/read/browse, forged keys rejected, metadata guards and incompatible legacy config preserved. Full BREAD suite running. Next: complete evidence then slug generator/complex validation; whole OPTIONS/ADM-AUD-06G remains IN PROGRESS.

- Итог OPTIONS-SELECT: DONE для scoped BelongsTo/List/where/static options/scalar defaults. Full SQLite BREAD99 tests /1432 assertions PASS (70.031s, memory132MB). После уточнения guards (Class@method только relation default; safe where columns) targeted1/32 PASS (1.924s). PHP lint/diff check PASS. Рабочая DB/images не записывались; реальные relation-source browser формы отсутствуют. Полный ADM-AUD-06G/OPTIONS не DONE.
- TODO: OPTIONS-DEFAULT-CALLABLE — доверенный resolver legacy Class@method; другие relation types требуют отдельного подтверждения storage contract. Следующий точный шаг OPTIONS-SLUG: сверить original slugify rules, источники полей/forceUpdate/переводы, перенести и проверить SQLite.
# DONE — ADM-AUD-06G-OPTIONS-SLUG (text input contract, 2026-10-09)
- Audit legacy helperSlugify + slugify.js: origin, auto-track initially empty target, forceUpdate enabled by option presence (including false), no generation on hydration. Port original character map, scoped drafts and text input sources; SQLite only. Shared slug must not be overwritten by translated source in non-default locale.

## ADM-AUD-06G-OPTIONS-SLUG — IN PROGRESS, 2026-10-09
- Sources read-only: Voyager helperSlugify.php and resources/assets/js/slugify.js. Helper exposes origin/forceUpdate only; presence of forceUpdate (even false) emits true. Plugin binds input keyup/change, initially empty target tracks subsequent source changes; populated target remains until force or cleared. No generation on hydration or save; manual slug edits alone are retained.
- Added BreadSlug with original 462-character map (Voyager/Bruno Torrinha MIT attribution). Matches legacy lower-case/map/non-ASCII-to-hyphen pipeline, including щ->sh, ё->yo, currency/symbol maps, instead of Laravel generic transliteration. JSON asset local; no npm/external dependency.
- Runtime editable text target/source: source live debounce350, updatedData generates in form state; tracking initialized after fill and retained per locale, reset on new/open record. Manual value remains until source changes when auto-tracking or force is active, exactly like original flag behavior. Save does not generate or change existing URLs without a source edit. Invalid legacy slug config disables generation but permits manual entry; metadata rejects malformed/self/missing/secret source/unknown options/non-boolean force.
- Shared slug cannot be overwritten by translated source in non-default locale (intentional isolation safeguard). Translated slug generates for current language and saves its translation; drafts separate. Only accessible editable text sources trigger generation. Textarea/richtext sources, source chains/custom JS maps/separators are not implemented (legacy helper did not expose custom JS options).
- Working DB read-only metadata audit: configured slugify0; no real runtime fixture available for Chrome. No production metadata added and no real write tests. SQLite/Livewire covers generation/render/update/save and translations. DB/images unchanged, no schema/API changes.
- Targeted SQLite1 test /40 assertions PASS (2.600s): Add/Edit/filled preservation/cleared regeneration/tracking, force presence, source/manual edits, shared/translated locale isolation, metadata guards, malformed config manual fallback, legacy transliteration symbols. Initial test incorrectly assumed Page.title translatable; replaced with explicit test-only fixture. Another expected щ/ё mapping corrected against original, runtime unchanged. Full suite running; lint/diff check PASS.
- Next: finish suite evidence; complex validation (DB unique/exists, regex/dependent/custom) remains TODO. Full OPTIONS/ADM-AUD-06G IN PROGRESS, Maps/media recovery/callable dropdown defaults remain TODO.

- Итог OPTIONS-SLUG: DONE (editable text source/target, original map and force presence semantics). Full SQLite BREAD100 tests /1472 assertions PASS (75.949s, memory134MB); targeted1/40 PASS, PHP lint/diff check PASS. Livewire source state updates and translations verified; Chrome real field not tested because DB slugify configs0 and no working writes authorized. DB/images unchanged. Full OPTIONS/ADM-AUD-06G remains IN PROGRESS.
- Next exact action: OPTIONS-VALIDATION audit legacy validateBread and real metadata for unique/exists/regex/dependent rules; add supported safe contracts/record-ignore/translation handling with SQLite. Custom rules requiring application code and callable defaults remain separate TODO.
# DONE — ADM-AUD-06G-OPTIONS-VALIDATION (DB/regex/named dependencies subset, 2026-10-09)
- Audit validateBread: base+action rules, messages; legacy Edit replaces unique with current-table ignore(id), losing configured columns. Implement safe DB rules with configured table/column retained, model-derived ignore, regex arrays for pipe patterns and bounded named field dependencies. Translation unique must query translations in current locale. SQLite only; custom application rules remain TODO.

## ADM-AUD-06G-OPTIONS-VALIDATION — IN PROGRESS, 2026-10-09
- Original Controller::validateBread inspected: base+Add/Edit arrays merged, custom messages; Edit replaced any unique rule with unique(current table)->ignore(id), losing explicit column/table. New runtime retains configured column and uses server recordId/model key for ignore. Metadata-provided ignore IDs/connections/extra SQL parameters rejected; unique supports current model table only.
- Added BreadValidation compiler for text/number/text_area: unique:table[,column], exists:table[,column], regex/not_regex, same/different, required_if/unless, required_with/without/with_all/without_all. Dependencies map to actual data.<field> paths in forms and root fields for taggable create; existing schema required and secret columns prohibited. Extra custom/dependent/exclusion/date rules remain unsupported, not arbitrary application class execution.
- DB rules use model connection, real existing table/column. DB unique/exists preserve raw Laravel presence-query semantics (no model scopes/SoftDeletes implicit filtering); source-list rules remain separately scoped. Translated unique for non-default locale checks translations.value by table_name/column_name/locale and ignores same foreign_key, without modifying base values. Translation uniqueness applies to explicit translated rows, not display fallbacks. Metadata compile validates Add and Edit; incompatible old configs readonly/nondehydrated preserve values.
- Regex length<=1000, syntax checked before use; regex containing pipe must be supplied as array element, because legacy string rules split on pipe. Runtime validation uses PHP PCRE. Rules with custom validators/SQL conditions/application callbacks are separate TODO. Optional translations omit unconditional required, retain explicit conditional required rules.
- Taggable scalar Add uses same compiled DB/dependency rules with prefix empty; unsupported config surfaced as label error. Native user email unique rule remains appended. No endpoints/schema changes, no working DB/image writes.
- Working DB read-only audit: validation configured8, max8/required1, no DB/regex/dependent configs. Real browser fixture unavailable; tested runtime via Livewire SQLite without inserting metadata into working DB.
- Targeted SQLite1 test /73 assertions PASS (2.752s): current-record ignore, duplicate Add/Edit/custom message, regex alternation array, required_if+same, exists, shared language dependencies, translated duplicate/own ignore/base unchanged, malformed metadata guards and old readonly preservation. Additional rule syntax/path compilation covered. Initial final fixture Save mismatched shared flags with default description; fixed fixture state to test readonly preservation independently. Full suite running; lint/diff check PASS.
- Next exact action: finalize full suite, then audit remaining options by supported field type (checkbox/radio/dropdown/time/date/richtext/media). Overall OPTIONS/ADM-AUD-06G remains IN PROGRESS; custom rules/callable defaults/Maps/media recovery remain TODO.

- Итог OPTIONS-VALIDATION: DONE для DB unique/exists, regex/not_regex и перечисленных named-field dependencies в scalar runtime. Full SQLite BREAD101 tests /1545 assertions PASS (76.142s, memory134MB); targeted1/73 PASS; PHP lint/diff check PASS. Real DB/images untouched. Browser new contracts not tested (real configs absent); custom validators/exclusion/date rules/complex DB conditions remain TODO. Full OPTIONS/ADM-AUD-06G remains IN PROGRESS.
- Следующий точный шаг OPTIONS-TYPES: сверить legacy checkbox/radio/dropdown/time/date/richtext/media settings и реальную metadata, составить оставшийся список до заявления full parity; затем реализовать следующую подтверждённую разницу. Callable defaults, Maps и media filesystem recovery отдельно открыты.
# IN PROGRESS — ADM-AUD-06G-OPTIONS-TYPES (2026-10-09)
- Audit checkbox/radio/date/time/richtext/media against original. Next confirmed missing option: checkbox.checked Add/null fallback and on/off captions; preserve existing false values. Implement this subset with SQLite fixtures, no real writes. Remaining type-specific validation/media transforms/editor configuration tracked separately after source audit.

## ADM-AUD-06G-OPTIONS-TYPES / CHECKBOX — 2026-10-09
- Source audit: Voyager checkbox/radio/multiple_checkbox/date/timestamp/time/rich_text_box/markdown_editor views, Checkbox::handle and Image/MultipleImage content handlers. Checkbox uses checked (FILTER_VALIDATE_BOOLEAN) only when column absent/null, on/off captions and optional CSS class; persistence on=>1/off=>0. Radio scalar options/default already supported. Date native Y-m-d; timestamp display differs by admin widget; time supports placeholder/default. Richtext exposes tinymceOptions; Markdown uses EasyMDE defaults. Image handlers resize/aspect ratio/quality/default75/upsize/thumbnails/preserveFileUploadName; new generic uploader has not ported these transformations.
- Read-only working metadata audit: checkbox54 (checked31, on/off30), multiple_checkbox2 (checked2/options2), rich_text_box115 (display14/tab1, tinymceOptions0), image117 (format4/quality1/upsize1), multiple_images8, file7, date3, timestamp268 (step1/default1); radio/time/markdown0. No record content printed or DB/images changed. Presence of metadata option does not prove original consumes it (e.g. image.format/timestamp.step require handler audit before port).
- Implemented BreadCheckbox: checked default in Add and null-valued Edit, existing false preserved; boolean validation; on/off captions as escaped plain helper text in native Toggle and in Read/Browse. Legacy CSS class not injected into Filament; native Toggle used. Without captions retain current admin Да/Нет fallback. Unsupported old config uses manual Toggle/helper; new metadata validates checked boolean-compatible scalar and on/off text<=500. checked takes precedence over generic default like original checkbox view.
- Local targeted1 test /34 assertions PASS (1.833s): checked yes/off, Add/save, existing false, null fallback, shared locale true/false/save, view/list captions, invalid metadata and HTML escaping. Initial render caught missing details variable in column mapping; fixed per-row JSON decoding, repeat PASS. Full BREAD suite running; lint/diff check PASS. Chrome checkbox not tested this batch; live rendering exercised in Livewire SQLite. No real write tests.
- OPTIONS-TYPES remains IN PROGRESS. Next exact action: MULTICHECK checked fallback (real configs2) and option-label display; preserve existing selections and JSON map. Further TODO: TIME placeholder and type-specific validation options; RICHTEXT tinymceOptions mapping (Filament editor capabilities differ, cannot execute legacy config); IMAGE transforms resize/quality/upsize/thumbnails/preserve-name incl fake storage recovery checks. No existing DB image paths may be rewritten.

# DONE — ADM-AUD-06G-OPTIONS-MULTICHECK
- checked fallback для multiple_checkbox (2 real configs), существующие выбранные ключи/JSON map, подписи Read/Browse. Следующая задача.
# TODO — ADM-AUD-06G-OPTIONS-EDITORS-MEDIA
- Адаптация tinymceOptions в поддерживаемые настройки Filament RichEditor, TIME placeholder/type-specific validation; Image/MultipleImage resize/quality/upsize/thumbnails/preserveFileUploadName в fake storage, без изменения существующих image paths. Metadata format/step сначала сверить с реальным handler, не считать их автоматически работающими в Voyager.

## DONE — ADM-AUD-06G-OPTIONS-CHECKBOX (2026-10-09)
- checked defaults/null fallback/false preservation/on-off captions in form/Read/Browse implemented. Full SQLite BREAD102 tests /1579 assertions PASS (77.094s, memory134MB), targeted1/34 PASS, PHP lint/diff check PASS. Native Toggle appearance replaces legacy CSS class. Working DB/images unchanged. OPTIONS-TYPES and overall ADM-AUD-06G remain IN PROGRESS; next exact task MULTICHECK (checked and display labels), then editor/media options.
# DONE — ADM-AUD-06G-OPTIONS-MULTICHECK (2026-10-09)
- Legacy multiple_checkbox selects JSON values (not map keys); null/absent uses truthy checked for all options, persisted [] remains empty. Handler writes key=>key request map. Implement compatible decode/default/captions/validation and preserve malformed legacy data; SQLite only, working DB/images untouched.

## ADM-AUD-06G-OPTIONS-MULTICHECK — IN PROGRESS, 2026-10-09
- Source: Voyager multiple_checkbox view and MultipleCheckbox::handle, Read/Browse labels. View selects JSON values via in_array (not keys); checked truthy selects all options only for absent/null column, saved [] remains empty. Handler persists request key=>key map (numeric consecutive keys may serialize as JSON array). Original checked uses PHP truthiness: string false is true; retained, unlike single checkbox FILTER_VALIDATE_BOOLEAN.
- Added BreadMultiCheckbox: null initial fallback, decode JSON list/map values, safe selectable keys, captions for Read/Browse; <=500 options/selections and unique configured keys. Changed form hydration from array_keys to values to correct JSON-list selections. Encode preserves unchanged raw JSON/spacing/list/map; modified selections write legacy key=>key, [] on empty. Array/json casts decoded before Eloquent assignment to avoid double encoding.
- Generic Add/Edit/shared locale and translated fields supported. Translated state decodes JSON and persists string to translations; existing [] overrides checked, base remains unchanged. Required remains native checkbox-list validation. Invalid JSON/nested selections/retired keys/config or incompatible casts renders disabled/nondehydrated preservation field (no silent deletion). Malformed configs guarded in fill and display. Metadata validates scalar checked/options; legacy helper CSS not injected. Generic default ignored for this type like original form.
- Working metadata: 2 multiple_checkbox rows, both checked/options (read-only OPTIONS-TYPES audit); no working writes or new metadata fixtures added. Runtime render/Save tested exclusively on SQLite, no Chrome real Save tests. DB/images untouched; no schema/endpoints.
- Targeted1 test /40 assertions PASS (2.507s): all defaults Add, saved empty, values vs list indices, unchanged raw formatting, modified map, shared language, captions, rejected unknown/duplicate state, malformed/nested/retired preservation, metadata validation, translated state/save/reopen/base unchanged, array cast, PHP checked truthiness. Prior preservation test1/2 PASS. Full suite running, lint/diff check PASS.
- Next exact action: finalize evidence, then OPTIONS-EDITORS-MEDIA audit safe mapping of RichEditor settings and image handlers. Image resize/quality/upsize/thumbnails/preserve filename not ported yet. Callable defaults/custom rules/Maps/media recovery remain TODO; overall OPTIONS/ADM-AUD-06G IN PROGRESS.

- Итог OPTIONS-MULTICHECK: DONE. Full SQLite BREAD103 tests /1619 assertions PASS (81.365s, memory134MB); targeted1/40 PASS and prior shape1/2 PASS, PHP lint/diff check PASS. DB/images untouched. Chrome write tests not performed. OPTIONS/ADM-AUD-06G remains IN PROGRESS; next exact task OPTIONS-EDITORS-MEDIA: safe editor option mapping and upload transforms with fake storage, no rewriting current images.
# DONE — ADM-AUD-06G-OPTIONS-IMAGE (GD upload subset, 2026-10-09)
- Existing compact previews/library crop/Spatie single-image editor confirmed; generic image/multiple_images upload still lacks legacy resize/quality/upsize/thumbnails/preserve filename. Excluded legacy app Image.php has these transforms plus optional WebP companion, but no executable Intervention dependency. Implement GD upload-only pipeline, collision checks and batch failure cleanup with fake storage; never process stored paths. Editor options mapping remains next subtask.

## ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — IN PROGRESS, 2026-10-09
- Existing partial implementation confirmed: compact FileUpload previews, library crop via BreadImageCrop GD and Spatie adv_image editor; generic image/multiple_images lacked upload transforms. Both legacy vendor Image and excluded app Image.php inspected; app version also has optional WebP companion helper. Legacy Intervention class excluded from autoload and package absent, so reused available GD (GD/exif/AVIF functions verified).
- Added BreadImageUpload upload callback for new TemporaryUploadedFile only (stored string paths bypass callback unchanged). Aspect-ratio resize, JPEG/WebP/AVIF quality, legacy upsize=false prevents enlarging, scale/crop named thumbnails, preserve filename with collision check covering derivatives. MIME determines output extension. JPEG EXIF orientation implemented; exact production EXIF fixtures still TODO. Supported JPEG/PNG/WebP/GIF/BMP/AVIF; unsupported raster/codec yields validation error. Pixel/memory bounds, target limits and validated thumbnail names.
- GIF original bytes always retained and explicit -static derivative generated, to avoid GD flattening animations without control blocks; difference: static GIF also retains original. Render entire batch before writes; failed writes attempt cleanup of newly allocated batch paths and log error. Old files untouched; SQL failure after upload can still orphan files (MEDIA-RECOVERY TODO), concurrent preserve-name writes require production review. WebP companion from excluded app handler not ported yet, tracked separately.
- Added BreadRichEditor safe mapping of tinymceOptions toolbar commands/grouping/false, height->native wrapper min-height and placeholder. Native Filament defaults when incompatible old config, visible helper warning, HTML content remains editable. New metadata rejects unsupported plugin/script/unknown configs; toolbar functions supported by Filament only. Full TinyMCE plugin/dialog parity not claimed. Content/translation persistence unchanged.
- Real metadata read-only audit: image/multiple125, richtext115/tinymceOptions0. One existing quality=80% (upsize=true) discovered; legacy intval normalizes80, support added without metadata rewrite. Unknown image.format metadata not executed by original handlers and remains unused. No working DB/images changed; uploads only fake storage.
- Targeted two tests /57 assertions PASS before quality-percent adjustment: image Add/Edit/multiple/default upsize/no-upsize/aspect/thumbnail/collision/old bytes/paths preserved, GIF original+static, failed second storage write cleanup and validation; RichEditor toolbar/height/placeholder/disabled toolbar/HTML/translations/old plugin fallback/metadata guards. FileUpload replacement fixture clears existing state before adding new file (native UI removes/replaces); standalone toolbar assertion moved to mounted component.
- Full suite running. A targeted rerun overlapped full suite shared fake disk and encountered unavailable test GIF bytes; discard overlapping results, rerun sequentially for final evidence. Next: confirm quality percent and full suite; remaining WebP companion/EXIF/production failure recovery/custom editor plugins stay TODO. No schema/API changes.

## DONE — ADM-AUD-06G-OPTIONS-IMAGE / EDITOR-A (2026-10-09)
- Image/multiple upload GD transforms and safe RichEditor option subset completed. Full SQLite BREAD105 tests /1676 assertions PASS (76.018s, memory136MB). Final sequential targeted2 tests /57 assertions PASS (2.625s, memory88MB) after quality80% compatibility fix; overlapping targeted result discarded. PHP lint/diff check PASS. Real DB/images untouched, no Chrome write tests. Existing library crop/Spatie implementation retained.
- Overall EDITORS-MEDIA/OPTIONS/ADM-AUD-06G remains IN PROGRESS. Next exact task: OPTIONS-IMAGE-WEBP — compare excluded app handler companion generation with active image_webp_url helper, implement fake-storage-safe companions without processing old paths. Additional TODO: EXIF orientation fixtures, production codecs/storage/SQL orphan recovery, preserve-name concurrency, extended TinyMCE plugins/dialog behavior. Timestamp/time formatting and type-specific validation remain in options backlog.
# DONE — ADM-AUD-06G-OPTIONS-IMAGE-WEBP (upload companions, 2026-10-09)
- Original app Image handler generates main-file companion via image_webp_url(...,true), optional/nonfatal, quality85 and jpg/jpeg/png/gif/bmp only. Active helper finds existing companions but its generation still references absent Intervention; do not invoke it against real public paths. Add upload-only GD companion via configured Storage disk with collision reservation and nonfatal failure handling; fake storage tests. Lazy frontend helper generation remains separate backlog.

## ADM-AUD-06G-OPTIONS-IMAGE-WEBP — IN PROGRESS, 2026-10-09
- Compared legacy app Image::generateWebpForUpload and active helpers image_webp_supported_extensions/image_webp_path/image_webp_url. Contract: jpg/jpeg/png/gif/bmp main file only, sibling same stem.webp, quality85, optional failure does not block original upload; WebP/AVIF excluded. Existing companion URL lookup works without decoder; generate=true still references absent Intervention ImageManagerStatic, catches error and returns null.
- New upload-only GD companion generated from encoded stored-original bytes (matches legacy double encoding/dimensions), quality85, palette-to-truecolor and alpha preservation. GIF original retained, companion is first/static frame at original dimensions like helper decoding original; -static derivative remains separately resized. No thumbnail companions added. Existing strings never enter upload callback, no old file batch conversion or DB path rewrite.
- Filename candidate collision check now includes companion path before any writes, preserving existing sibling even if original path absent. Original/thumbnails write handling unchanged; companion failure attempts cleanup of only new candidate companion and logs, returns original path. Encoder failure and storage-false failure independently tested. Collision checking is not a distributed lock; concurrent preserve-name uploads remain MEDIA-RECOVERY/production audit TODO.
- Targeted two tests /54 assertions PASS (1.956s): JPEG companion dimensions/MIME, reserved sibling collision and old bytes preserved, transparent PNG alpha, native WebP no duplicate, GIF bytes preserved/main-size WebP, codec and write failure nonfatal, plus original upload-transform scenarios. Full BREAD running. PHP lint/diff check PASS.
- Files only Storage::fake('public')/mock disk; SQLite fixtures, no working images or database touched. Native image_webp_path reused; image_webp_url not invoked with generate=true against public files. Frontend lazy converter missing backend registered TODO, not silently enabled against existing images. Next: finish suite evidence, then EXIF fixtures and production error-recovery investigation. Full OPTIONS/ADM-AUD-06G remains IN PROGRESS.

# TODO — FRONT-IMAGE-WEBP-GENERATOR
- Active image_webp_url(generate=true) uses absent Intervention ImageManagerStatic; existing sibling lookup works, new admin uploads now produce GD siblings. Audit callers/public path safety and choose GD backend before enabling lazy generation; user constraint prohibits processing existing working pictures in current work. Do not rewrite image database paths.

- Итог OPTIONS-IMAGE-WEBP: DONE для новых image/multiple_images upload companions. Full SQLite BREAD106 tests /1694 assertions PASS (81.403s, memory136MB); targeted2 tests /54 assertions PASS, PHP lint/diff check PASS. Database paths retain original images; existing files untouched. Full OPTIONS/ADM-AUD-06G remains IN PROGRESS. Next exact task OPTIONS-IMAGE-EXIF: fixtures orientations2..8, dimensions/visible pixels, thumbnail/companion consistency on fake storage; then filesystem/SQL failure recovery.
# DONE — ADM-AUD-06G-OPTIONS-IMAGE-EXIF (2026-10-09)
- Build JPEG fixtures with explicit EXIF Orientation2..8; verify real EXIF reader, transformed corner colors/dimensions, thumbnail and WebP consistency in fake storage. Fix proven orientation mismatch; working DB/images untouched. Then review filesystem/SQL recovery.

## ADM-AUD-06G-OPTIONS-IMAGE-EXIF — IN PROGRESS, 2026-10-09
- Added real JPEG fixtures containing little-endian TIFF EXIF Orientation tag. PHP exif_read_data independently confirms each fixture tag. Explicit expected TL/TR/BL/BR colors for1..8; no-EXIF JPEG and unknown9 retain identity. Fixtures use4 large color quadrants; nearest-color checks tolerate JPEG/WebP compression while detecting wrong flip/rotation.
- Confirmed defect: orientation7 produced transpose corners A,C,B,D instead of transverse D,B,C,A. Corrected horizontal-flip rotation from+90 to-90; original/main, scale and cropped thumbnails, WebP now agree. No other orientation code changed.
- Targeted1 test /100 assertions PASS (0.719s, memory68MB): dimensions and visible corner layout for main/half/square/WebP, source fixture bytes unchanged, encoded JPEG drops Orientation tag to prevent double rotation. All fixtures/temp files isolated in fake public storage; real image/DB paths untouched. Initial fixture assertion PHP isset(expression) corrected; first real test exposed orientation7 failure, fixed runtime and repeat PASS.
- Full BREAD suite running; PHP lint/diff check PASS. EXIF extension present locally. Missing-extension hosts will retain current fallback without orientation correction and need deployment verification; full production codec/phone-file corpus not claimed.
- Next exact task MEDIA-RECOVERY: form getState saves generic uploads before remaining Save validation/DB transaction, so failures after upload can orphan new files. Track new batch paths per Save and clean only newly created files on rejected save/rollback; preserve old originals and derivatives. Spatie immediate operations and concurrent preserve-name uploads require separate recovery/locking work; no destructive real tests.

- Итог OPTIONS-IMAGE-EXIF: DONE (GD JPEG orientations1..8, unknown/no-tag fallback, derivative consistency). Full SQLite BREAD107 tests /1794 assertions PASS (80.072s, memory138MB); targeted1/100 PASS; PHP lint/diff check PASS. Orientation7 corrected. Working DB/images not changed. Full OPTIONS/ADM-AUD-06G remains IN PROGRESS. Next exact task generic-upload MEDIA-RECOVERY: failed validation/SQL cleanup journal, isolated fixtures, never deleting old referenced files. Laravel13 provides afterRollBack callback; evaluate pre-transaction and rollback paths separately.
# DONE — ADM-AUD-06G-MEDIA-RECOVERY-A (2026-10-09)
- Generic image/multiple_images uploads save during form getState before remaining business validation/transaction. Track server-created files in request-scoped journal, remove only new batch files on validation/SQL failure and restore upload form draft for retry; old paths/derivatives preserved. SQLite/fake storage only. Spatie immediate operations, other upload types, concurrent naming/process crashes remain separate.

## ADM-AUD-06G-MEDIA-RECOVERY-A — DONE, 2026-10-09
- Added optional server-only onCreated callback to BreadImageUpload::store (existing string return contract retained). Journal includes new original, thumbnails/static GIF and successfully created optional WebP; submitted existing paths never enter the journal.
- VoyagerBread Save wraps form getState, subsequent business validation and DB transaction. Failure removes only journaled files, restores prior recordId after rollback, keeps other drafts and existing file selections. Filament deletes successful temporary uploads, so new files must be selected again after rejection. Notification is outside recovery boundary, preventing deletion of committed files on notification failure.
- Targeted SQLite/fake-storage tests: 2 tests /31 assertions PASS (2.171s, memory82MB). Verified post-insert validation rollback, SQLite SQL trigger rejection, no orphan derivatives, old byte preservation, fresh upload retry, multiple images preserving old selection, second-file failure before DB transaction, text draft retention. Full BREAD suite running. PHP lint PASS for all three changed PHP files.
- afterRollBack considered: action owns its transaction and catch also covers pre-transaction failures; no global rollback callback needed for this scope. External enclosing transactions, process crashes, deletion/storage failures and concurrent preserve-name uploads are not claimed recovered. Cleanup exceptions/false results are reported; durable reconciliation remains TODO. No real DB/files modified.
- Next exact action: finish full BREAD regression suite and record evidence; then audit Spatie adv_image immediate upload/replace/conversion filesystem failure handling separately. Full ADM-AUD-06G remains IN PROGRESS.
- Проверка общего набора: первый запуск с системным memory_limit=128M оборвался на 64-м сценарии (PHP memory exhaustion), итоговых результатов не дал. Повторный запуск использует только CLI override -d memory_limit=512M; настройки приложения/сервера не изменены. Изолированные recovery-сценарии проходят и при исходном лимите.
- Итог MEDIA-RECOVERY-A: DONE для generic image/multiple_images в текущем BREAD Save. Full SQLite BREAD109 tests /1825 assertions PASS (85.951s, memory138MB; CLI memory_limit=512M). Targeted2 /31 PASS при исходном лимите, PHP lint и git diff --check PASS. Рабочая БД и реальные изображения не изменены. При отклонении сохранения новые файлы выбираются повторно; старые выбранные пути и текстовый draft остаются. Полный ADM-AUD-06G и MEDIA-RECOVERY для Spatie не закрыты.
- Следующая точная задача: ADM-AUD-06G-MEDIA-RECOVERY-B — аудит persistSingleImages/Spatie и устранение потери старого файла при частичном сбое upload/replace/conversion; сначала isolated SQLite/fake-storage сценарии. Durable recovery после аварии процесса и конкурентная резервация имён остаются TODO.
# DONE — ADM-AUD-06G-MEDIA-RECOVERY-B (2026-10-09)
- Audit: persistSingleImages currently deletes old Spatie media/files inside the Save transaction, loses previous originals on later rollback, and has no journal when conversions fail after copying new original. FileAdder can also enforce registered collectionSizeLimit before commit and consumes temporary source. Implement scoped safe FileAdder preserving source/old collection, rollback cleanup of new media files and post-commit old-file deletion. SQLite/fake storage only; immediate adv_media_files/delete actions and asynchronous conversion failures remain separate.
## ADM-AUD-06G-MEDIA-RECOVERY-B — DONE, 2026-10-09
- Found: persistSingleImages deleted previous media files inside DB transaction; later field failure restored old DB rows but not files. Spatie FileAdder can also auto-retire singleFile/limited collections before commit and removes Livewire temporary source on successful copy.
- Added scoped BreadPendingImageAdder, retaining normal collection acceptance/conversion settings while disabling automatic collection-size retirement for this action. Captures actual pending Media object before copying (ID populated by native save), preserves source, retains model relation for rollback/conversion cleanup. No global Spatie binding/vendor changes.
- persistSingleImages registers connection rollback/commit callbacks. Old rows deleted quietly within transaction; filesystem observer deferred: old files retired only after successful commit. Rollback removes new files including incomplete conversions in default per-media-ID directories. Custom generators use native selective removal; unknown partial custom paths remain TODO. SingleImageUploads clears after commit, retains uploaded temp source on failure for retry.
- Targeted SQLite/fake-storage3 tests /67 assertions PASS (4.815s, memory84MB): baseline ADVIMAGE workflows, singleFile registered collections, actual GD thumbnail output, later second-media SQLite trigger failure in Edit/Add, model/media rollback and create recordId restored, all previous original/conversion bytes preserved, failed conversion after writing actual derivative and untracked partial file, new files cleaned and same temporary upload retry succeeds. Real DB/image paths unchanged.
- Limits: asynchronous/deferred conversions after commit, custom path/remover edge cases, process crashes and filesystem retirement failures need separate durable reconciliation. Callback cleanup exceptions logged; successful DB commit is not undone by old-file cleanup failure. Direct deleteSingleImage/adv_media_files actions remain separate. No claim of distributed filesystem+DB atomicity. Next: full BREAD regression suite; then audit immediate media library operations.
- Итог MEDIA-RECOVERY-B: DONE для adv_image Add/Edit в BREAD Save при синхронных сбоях записи/конверсий. Full SQLite BREAD111 tests /1862 assertions PASS (76.531s, memory140MB, CLI memory_limit=512M); targeted3 /67 PASS; PHP lint/git diff --check PASS. Old media/files remain intact on rollback, new per-ID partial files removed, temporary source retained for retry. Successful commit retires old files and clears upload state. Рабочая БД и реальные файлы не изменены.
- Next exact task ADM-AUD-06G-MEDIA-RECOVERY-C: immediate adv_media_files uploadMedia/replaceMedia. Confirmed code gap: uploadMedia only tracks media after FileAdder returns, losing failing in-progress media; replaceMedia writes new row/file then order separately without transaction, and consumes source before order write. Add isolated failure fixtures first, reuse pending-media journal and defer retirement. Direct explicit deletions/durable cleanup/custom path generators/queued conversions remain separate TODO. Full ADM-AUD-06G remains IN PROGRESS.
# DONE — ADM-AUD-06G-MEDIA-RECOVERY-C (2026-10-09)
- Immediate adv_media_files uploadMedia and replaceMedia: use transaction-scoped pending-media journal, preserve temporary source, rollback partial original/conversions and DB rows, defer old-file retirement/UI state changes to commit. Preserve extra properties, selected IDs/order and configured collection size limits. Test isolated batch conversion failure and replacement order SQL failure first; no real DB/storage writes. Reuse/extract MEDIA-RECOVERY-B cleanup rather than duplicate it.
## ADM-AUD-06G-MEDIA-RECOVERY-C — DONE, 2026-10-09
- Failure fixtures confirmed both defects before runtime change: batch conversion failure produced IDs2,4 instead of old1,2; replacement order SQL rejection left old1 missing and new3 orphaned. Registered onlyKeepLatest(2) exposed native auto-retirement before successful operation. Initial fixture declaration order corrected before actual test runs.
- Extracted BreadMediaJournal from single-image recovery: transaction callbacks, pending Media capture through scoped BreadPendingImageAdder, source preservation, new original/partial conversion cleanup on rollback, quiet old-row deletion and post-commit filesystem retirement. Reused by adv_image Save and immediate adv_media_files; no vendor/global binding changes.
- uploadMedia now uses owning connection transaction and parent record lock; all batch additions, configured collection limit retirement and DB changes succeed together. Temporary inputs retained on operational failure, cleared after commit. Existing invalid-file validation behavior remains. replaceMedia rechecks owned collection/media under locks, includes new original/conversions/order/old-row retirement in transaction; UI properties/selected ID replacement and draft clearing happen after commit. Properties and order retained; old files never removed on failed transaction.
- Targeted SQLite/fake storage6 tests /150 assertions PASS (11.598s, memory90MB): new batch conversion failure + retry, replacement order SQL failure + retry, old originals/conversion bytes and DB IDs retained, actual GD derivatives and unfinished conversion cleanup, temporary sources retained, onlyKeepLatest(2) enforced after successful additions, extra multilingual property preserved, selection/order preserved; previous adv_image3 tests and full existing media workflow regression included. PHP lint/diff check PASS. Full BREAD suite next.
- Limits unchanged: custom path generators use native selective cleanup; untracked custom partial paths, asynchronous/deferred conversion failures, process crashes, deletion/storage failures require durable reconciliation. Immediate explicit single/bulk deletions still separate. Real DB/image files untouched; no real write tests. Next exact task after full suite: inspect explicit media deletions and define isolated failure-safe handling before changing them.
- Follow-up audit (read-only): deleteSelectedMedia currently loops immediate Media::delete with no transaction and updates UI per item. A SQL failure on a later item can leave a partially deleted selection and destroyed earlier files. deleteMedia/deleteSingleImage also use immediate filesystem observers. Next exact task ADM-AUD-06G-MEDIA-RECOVERY-D: wrap authorized single/bulk explicit deletion in connection transaction with owned-media locks and journal retirement; update UI only after commit. Use SQLite trigger rejecting second deletion to verify old rows/files remain intact, plus success/ownership guard regression. Durable storage deletion reconciliation remains TODO.
- Итог MEDIA-RECOVERY-C: DONE для immediate adv_media_files uploadMedia/replaceMedia при синхронных сбоях копирования/конверсий/SQL. Full SQLite BREAD113 tests /1901 assertions PASS (90.792s, memory140MB; CLI memory_limit=512M). Targeted6 /150 PASS, PHP lint/git diff --check PASS. Общий журнал также прошёл regression adv_image Save. Retry сохраняет загрузки; extra properties/order/selected ID replacement и collection limit проверены. Рабочая БД/реальные изображения не изменены, миграций/переписывания старых путей нет. Full ADM-AUD-06G остаётся IN PROGRESS. Следующая точная задача MEDIA-RECOVERY-D: безопасное одиночное/пакетное явное удаление медиа с проверкой отказа SQL на втором элементе.
# DONE — ADM-AUD-06G-MEDIA-RECOVERY-D (2026-10-09)
- Explicit deleteSingleImage/deleteMedia/deleteSelectedMedia: wrap owned media deletion in connection transaction and locks, retire via existing BreadMediaJournal, update UI only after commit. First reproduce second-delete SQL failure with isolated SQLite trigger; verify selected rows/files/properties remain intact. Check success and ownership guards. Real DB/files untouched; durable filesystem cleanup failure recovery remains separate.
## ADM-AUD-06G-MEDIA-RECOVERY-D — DONE, 2026-10-09
- SQLite fixture reproduced partial bulk deletion: second-delete trigger rejection left only ID2 instead of IDs1,2. Single-delete SQL rejection already preserved its own row; outer transaction rollback was an additional filesystem risk.
- deleteMedia/deleteSelectedMedia now use owned connection transaction, parent lock and ordered owned collection row locks; validate whole selection before retiring first item. Shared retireMediaRecords uses BreadMediaJournal quiet deletion and post-commit filesystem retirement. UI properties/replacement drafts/selection clear only after commit. deleteSingleImage rechecks current first media under locks and uses same journal semantics; title/alt clears after commit.
- Targeted4 tests /122 assertions PASS (6.960s, memory90MB): second bulk-delete SQL failure keeps all media rows/originals/real GD conversions and selection/properties/replacement draft; retry success removes selected originals/conversions and clears drafts. Single adv_media_files/adv_image SQL rejection preserves files; enclosing transaction rollback preserves original/conversions and UI until true commit. Successful deletion leaves other collection images intact. Existing media workflows and single-image foreign/hidden-field guards regression included.
- Real DB/image files untouched; no migration or path rewrite. Durable filesystem cleanup retry after commit/storage failure, custom partial paths and process-crash reconciliation remain separate TODO. Next: full BREAD suite and syntax/diff checks; record final evidence before closing this stage.
- Итог MEDIA-RECOVERY-D: DONE для явного одиночного/пакетного удаления при SQL failure и rollback внешней транзакции. Full SQLite BREAD115 tests /1949 assertions PASS (95.786s, memory140MB; CLI memory_limit=512M); targeted4 /122 PASS; PHP lint/git diff --check PASS. Второй отказ удаления больше не уничтожает первый файл/строку; выбор/ALT/TITLE/replacement draft остаются до commit. Успешный retry удаляет оригиналы/конверсии, не затрагивая остальные media. Рабочая БД/реальные картинки не изменены.
- Следующая точная задача MEDIA-RECOVERY-E (TODO): durable filesystem cleanup — сначала аудит Native FileRemover (он может проглотить исключения/false deletion), стратегия сохранения серверных retirement/cleanup данных для повторного выполнения после отказа хранилища или аварии процесса. Требуется отдельное решение для custom path generators и shared directories; не удалять реальные файлы при проверке. Асинхронные/deferred conversions и конкурентная резервация generic preserve-name uploads остаются TODO. Полный ADM-AUD-06G/MEDIA-RECOVERY не DONE; этапы A-D закрывают проверенные синхронные SQL/конверсионные сценарии.
# CANCELLED — ADM-AUD-06G-MEDIA-RECOVERY-E (2026-10-09)
- Confirmed native DefaultFileRemover suppresses exceptions and ignores false deletion. Add transactional retirement outbox, verified disk deletion and idempotent retry command/scheduler. Scope default per-ID path generator; custom/shared directory cleanup must fail closed until reviewed. New rollback orphan/process-crash cleanup is separate; do not claim whole media crash recovery. Migration prepared only, not applied to working DB. SQLite/fake/mock storage tests.
## ADM-AUD-06G-MEDIA-RECOVERY-E — CANCELLED, 2026-10-09
- Native DefaultFileRemover suppresses exceptions and ignores false deletion. Added bread_media_cleanup_tasks outbox via migration2026_10_09_150000 (prepared only, working DB NOT migrated): media_id, server-generated disk/directory targets, attempts, safe last_error, timestamps. Retirement intent inserted in same owning DB transaction before quiet Media row deletion; rollback rolls back task too. No old image path migration/backfill.
- BreadMediaCleanup only permits exact DefaultPathGenerator per-media-ID directory targets; custom/shared path generators reject retirement before deletion pending review. Retry validates relative path segments and terminal media ID, configured disk, absent live media row; live/re-imported IDs block cleanup. Snapshot targets survive prefix changes; no broad scans or client path input. Custom prefix names outside allowed safe segments fail closed pending review.
- BreadMediaJournal post-commit cleanup uses outbox; direct deleteDirectory failure/exception or remaining files keeps task and increments attempts, logs error without embedding credentials in task record. Idempotent absent/already-cleaned paths complete. Task deleted only after verified cleanup. Rollback new-file journal unchanged; whole process-crash recovery for uploads NOT claimed.
- Artisan bread:media-cleanup --connection=<optional> --limit=1..1000 retries bounded oldest tasks, locks each task, failure exit code when pending failures. Kernel schedules hourly withoutOverlapping, requires existing scheduler deployment. Command reports missing migration and does not create schema itself. Working DB command/scheduler not run. Apply migration before deploying journal runtime; production DB/files unchanged.
- Targeted5 SQLite/fake/mock storage tests /82 assertions PASS (5.245s, memory94MB): false deletion persists task/old bytes, retry command completes, partial delete+exception preserves remaining derivative/task and later completes, committed task without afterCommit execution later processes, repeated run idempotent, live ID/traversal blocked, transaction rollback removes task, command limit validation, previous single/bulk delete scenarios. Full BREAD suite next.
- Next exact action: full regression/lint/diff then document deployment requirement and remaining custom-generator/rollback-upload/process-crash/deferred-conversion recovery. Full ADM-AUD-06G remains IN PROGRESS.
- Итог MEDIA-RECOVERY-E: DONE для durable повторной очистки retired Spatie media со стандартными per-ID directories после commit/storage failure или пропуска afterCommit. Full SQLite BREAD118 tests /1983 assertions PASS (101.170s, memory140MB; CLI memory_limit=512M); targeted5 /82 PASS; PHP lint/git diff --check PASS. False/exception/partial deletion, retry, idempotency, safe-path/live-media guard and transactional task rollback verified. Новая migration только подготовлена, рабочая БД/изображения не изменены; runtime требует её применения.
- Deployment exact steps: apply database/migrations/2026_10_09_150000_create_bread_media_cleanup_tasks.php before enabling updated media retirement; ensure normal Laravel scheduler runs for hourly bread:media-cleanup. Manual bounded retry: php artisan bread:media-cleanup --limit=100 (optional --connection). Do NOT run broad image/path conversion/backfill. Table has no FK to media because retirement row must survive Media deletion. Missing table command returns failure with migration instruction; retirement cannot delete old rows if task insert fails.
- Next exact task MEDIA-RECOVERY-F (TODO): durable recovery for new uploads if process crashes or rollback cleanup itself fails; current rollback journal is in memory and generic filenames can collide/reuse on later retry, so design persistent UUID/ownership reservation before enabling cleanup for abandoned new paths. Asynchronous conversions/custom shared generators remain separate TODO. Full ADM-AUD-06G remains IN PROGRESS.
## DONE — ADM-AUD-06G-MEDIA-ORIGINAL (2026-10-09)
- User explicitly requested original-compatible functionality, without added durable cleanup infrastructure. MEDIA-RECOVERY-E outbox withdrawn: removed unapplied cleanup migration, service, retry command, hourly schedule and its3 infrastructure-only tests. No working DB migration had been run, so no schema/data rollback required or performed.
- BreadMediaJournal restores native Spatie Filesystem::removeAllFiles after commit without additional DB table. Prior A-D protections preserved: rollback of SQL writes, new pending-file cleanup on synchronous failure, source retry, old-file retirement only after commit, safe owned single/bulk deletion. No custom-generator retirement restriction added by outbox remains.
- MEDIA-RECOVERY-E and planned F durable infrastructure are CANCELLED / OUT OF SCOPE per user; historical evidence above describes withdrawn code, not deployment requirements. Do NOT apply removed migration or run removed bread:media-cleanup command. Follow next tasks from original functional parity, not added storage infrastructure.
- Next exact step full BREAD regression suite115 tests, lint/diff and runtime reference check. Real DB/images untouched.
- Итог MEDIA-ORIGINAL: DONE. Full SQLite BREAD115 tests /1949 assertions PASS (96.353s, memory140MB; CLI memory_limit=512M), PHP lint/git diff --check PASS. Runtime reference search app/tests/database/routes found no removed cleanup class/table/command/migration dependencies; Console Kernel diff is empty. Original native Spatie filesystem retirement restored; prior synchronous SQL/conversion/rollback protection A-D retained. Working DB/real files untouched; no migration needed for these cleanup changes. Outbox/retry scheduler and F infrastructure are cancelled per user. Next tasks must be original functional parity, not expansion of recovery infrastructure.
## Commit preparation — 2026-10-09
- User requested commit of accumulated admin migration work: BREAD table/metadata editor, field/options/relation parity, user actions and synchronous media protections A-D, original-compatible native retirement (added outbox withdrawn). Include code/views/tests and current documentation. No new media cleanup migration or scheduler included.
- Final evidence before commit: SQLite BREAD115 tests /1949 assertions PASS (96.353s); OrderReviewRequestService4 tests /10 assertions PASS (0.889s, memory70MB, local fixtures; no real mail). PHP lint/runtime reference check/git diff --check PASS. Working DB/images not changed. Overall admin parity remains IN PROGRESS as recorded in backlog.
# DONE — ADM-AUD-06G-CLASSES-A (2026-10-09)
- Read-only real metadata audit: configured controllers users VoyagerUserController, roles VoyagerRoleController, locales AdminLocaleController, orders OrdersController, ALT/SEO specialized controllers. Only configured policy is legacy Voyager UserPolicy. Legacy routes select controller per DataType; provider registers custom policy or BasePolicy; base controller authorizes model-instance read/edit/delete. Generic Filament permission-only checks miss self-profile read/edit exception in UserPolicy.
- First confirmed implementation step: match configured legacy UserPolicy self read/edit, retain section permissions for other users and add/delete; pass authoritative record ID to authorization on open/save/view/media paths. No dynamic execution of excluded legacy controllers. Controller/policy selectors remain disabled until compatible runtime adapters exist. No working DB changes. Test isolated SQLite self vs foreign profile and Save authorization; then full regression.
## ADM-AUD-06G-CLASSES-A — DONE, 2026-10-09
- Legacy source audit: voyager/routes/voyager.php chooses data_types.controller for resource endpoints; VoyagerServiceProvider registers policy_name if class exists else BasePolicy; VoyagerBaseController read/edit/delete authorize actual model instances. Only actual configured policy in read-only local snapshot: TCG\Voyager\Policies\UserPolicy on users. Controller overrides users/roles/locales/orders/ALT/SEO; generic controller dispatch does not map directly to Livewire and remains separate adapter work.
- UserPolicy original read/edit grants own record independent of section permission; editRoles forbids own role changes. Added exact legacy configured-policy compatibility to BreadRegistry::permitted optional recordId (leading slash normalized), no other actions/table permissions granted. openEdit/openView pass target ID before state changes; requireBread derives active edit/view ID for Save/view/locale/media operations. VoyagerBreadEdit initializes validated target ID before parent mount; own-profile direct Edit can mount/hydrate without browse_users while list remains denied. Existing self-role protection unchanged.
- Targeted SQLite2 tests /20 assertions PASS (1.327s, memory82MB): self read/edit/save, foreign direct edit/view denied, tampered recordId Save denied/no foreign writes, own add/delete and unknown record denied, removed policy disables exception, direct own edit without browse_users and foreign/list denied, self role stays intact. Permission grants modified within same test require reset request-scoped registry cache to emulate next request; fixed fixture cache, no production cache changes.
- Full BREAD next; all DB writes isolated SQLite, real snapshot read-only, no migration. Scope limitation: arbitrary custom policy methods/gates and controller selector/class generation are NOT implemented by this step. Fields remain disabled to avoid silently enabling unsupported callbacks; full CLASSES/ADM-AUD-06G remains IN PROGRESS.
- Next exact task CLASSES-B: inspect AdminLocaleController unique language-template/view behavior and current translations route coverage; identify compatible controller choices/adapters before allowing metadata controller writes. Custom controller/tag creation and arbitrary policy runtime require separate source tests.
- Итог CLASSES-A: DONE для подтверждённого legacy UserPolicy собственного профиля. Full SQLite BREAD116 tests /1967 assertions PASS (87.190s, memory140MB; CLI memory_limit=512M); targeted2 /20 PASS; PHP lint/git diff --check PASS. Real metadata audited read-only; real DB/files untouched. Full controller/policy selection/runtime/class generation remains IN PROGRESS, not declared complete. Next exact step CLASSES-B AdminLocaleController language-template behavior vs current translations runtime, before implementing compatible controller adapter/selection.
# DONE — ADM-AUD-06G-CLASSES-B (2026-10-09)
- AdminLocaleController source audit: standard BREAD list/read/edit/delete handlers plus store mkdir resources/lang/<prefix> for non-tagging creation. Language-template methods directly read/write PHP files; legacy view AJAX routes are not registered in inspected source routes. Current separate Translation Manager already offers group/locale editing/import/export per prior user choice; no duplicate embedded Filament editor.
- Confirmed gap: generic creation of locales with configured AdminLocaleController does not create language directory. Add scoped adapter for this controller only: validated prefix, create empty lang_path directory once, preserve existing folders/files, cleanup only empty newly created folder on SQL rollback. Updates must not rename/remove existing directories (matches source). Test isolated temporary language root and SQLite; no real lang files/DB edits.
## ADM-AUD-06G-CLASSES-B — DONE, 2026-10-09
- Audited original AdminLocaleController: standard CRUD plus non-tagging store mkdir('../resources/lang/'.$prefix), no copying defaults; update does not rename dirs, destroy does not remove language files. template/get/set methods reference legacy admin.locale_template with direct PHP-array edits up to3 levels; inspected source routes register none of those custom AJAX/template methods. Current VoyagerUiTranslations intentionally redirects to already-installed standalone Barryvdh Translation Manager (prior user request), supporting groups/locales/import/export; duplicate embedded editor not added.
- Added BreadLocaleController creation hook only for exact configured AdminLocaleController/locales metadata (leading slash normalized). Runs in BREAD creation transaction after model save; safe language prefix validation, lang_path root, no existing overwrite/file/symlink replacement. Creates0755 folder; rollback removes only newly created still-empty folder, preserves files populated later. Updating prefix never renames directories; generic unconfigured sections unchanged. New helper and Save call only, no routes/API/schema changes.
- Isolated temporary lang_path inside fake public storage plus SQLite1 test /24 assertions PASS (1.783s, memory78MB): new pt-BR directory, existing translation PHP byte preservation, update pt leaves old files/no new folder, traversal rejected/no DB record, file collision rejected/preserved, enclosing transaction rollback removes empty new folder/record, populated directory preserved on rollback, no-controller generic create does not touch files. Real lang files/working DB untouched.
- PHP lint/diff + full BREAD next. Runtime adapters remain scoped, controller/policy selectors/class generation still disabled until reviewed compatibility. No claim arbitrary legacy controller execution. Next exact task CLASSES-C: define recognized controller compatibility for generic vs dedicated sections and explicit unsupported-controller handling; review original BREAD controller/policy editing and class generation separately. Whole ADM-AUD-06G remains IN PROGRESS.
- Итог CLASSES-B: DONE для подтверждённого дополнительного store-поведения AdminLocaleController. Full SQLite BREAD117 tests /1991 assertions PASS (92.093s, memory140MB; CLI memory_limit=512M); targeted1 /24 PASS; PHP lint/git diff --check PASS. Создание каталогов языков совместимо с оригиналом; изменения/удаления не переносят и не удаляют переводы. Existing standalone Translation Manager retained per user, duplicate file editor not added. Рабочая БД/lang/image files не изменены, миграций нет. Next exact task CLASSES-C recognized controller compatibility/unsupported handling and metadata choices; full controller/policy/class generation parity remains IN PROGRESS.
# DONE — ADM-AUD-06G-CLASSES-C (2026-10-09)
- Enable controller choice in BREAD settings only for known adapted generic BREAD / users / roles / locales classes. Recognize legacy leading backslash. Unsupported current controllers remain visible/read-only and preserved; reject unsupported/cross-table controller submission server-side. Dedicated orders/ALT/SEO not routed through generic handlers. Controller policy generation/arbitrary dispatch remains separate. Isolated SQLite tests only.
## ADM-AUD-06G-CLASSES-C — DONE, 2026-10-09
- Added BreadControllerOptions compatibility list scoped by table: null/default or legacy VoyagerBaseController for generic BREAD, VoyagerUserController only users, VoyagerRoleController only roles, AdminLocaleController only locales. Names are metadata compatibility identifiers; no excluded legacy controller instantiation. Dedicated orders/ALT/SEO remain handled by their dedicated resources and disabled in generic settings.
- Controller settings now Select for supported current values, default clear option/search and readable labels. Leading slash current identifiers retained in option list and normalized on successful write. Unsupported current class remains visible/read-only/nondehydrated; saves of other settings preserve its exact value. Server validates optional nullable string max191 then checks both current class and submitted class including null; incompatible table/class/dedicated or clearing unsupported class rejected. Existing policy_name stays read-only; no arbitrary class execution/generation enabled.
- Targeted SQLite3 tests /59 assertions PASS (4.658s, memory96MB): default/base selection Save and clear, cross-table and missing class rejected, unsupported field disabled and untouched through label Save, forged unsupported null clear rejected, leading slash option normalization and actual Locale controller selection followed by directory creation, dedicated mapping denied, previous UserPolicy checks. Test fixture corrected required singular label and post-Save cleared form state before direct validation checks; final tests PASS.
- No migrations/real DB/lang/images writes. Generic users/roles continue the existing migrated form safeguards; selection is compatibility metadata, not dispatch of arbitrary old PHP handlers. Creation wizard controller choice, arbitrary policies, class generation, custom controllers/tagging remain open. Next exact task CLASSES-D: match original policy choice/default and supported authorization mapping before enabling policy metadata edits; full CLASSES/ADM-AUD-06G remains IN PROGRESS.
- Итог CLASSES-C: DONE для выбора известных адаптированных controller в настройках существующего BREAD. Full SQLite BREAD118 tests /2008 assertions PASS (103.767s, memory140MB; CLI memory_limit=512M); targeted3 /59 PASS; PHP lint/git diff --check PASS. No real DB/lang/image writes or migrations. Unsupported/dedicated class editing remains disabled and values preserved. Policy selection/arbitrary dispatch/generation and creation wizard choices remain open; full CLASSES/ADM-AUD-06G IN PROGRESS. Next exact task CLASSES-D supported policy selection/default mapping consistent with original, isolated authorization tests first.
# DONE — ADM-AUD-06G-CLASSES-D (2026-10-09)
- Policy choice in settings: standard null/BasePolicy and exact legacy UserPolicy only for users. Existing unknown policy retained/read-only; reject unsupported/cross-table/dedicated changes including null clears server-side. Verify chosen policy affects self profile exception through current runtime and does not grant foreign-record access. No dynamic legacy policy loading or global Gate mutation; no migrations/working DB writes. SQLite tests first.
## ADM-AUD-06G-CLASSES-D — DONE, 2026-10-09
- Source contract from earlier audit retained: Voyager registers BasePolicy for null/default; configured UserPolicy grants own-profile read/edit; other records/actions remain section permissions. Added BreadPolicyOptions exact supported metadata identifiers BasePolicy/null for generic types and UserPolicy only users. Dedicated resource policies and arbitrary legacy class invocation are not changed.
- Policy field now searchable Select with default placeholder, recognized options and exact existing leading-slash option retained. Settings normalize on save; unsupported current policy visible/disabled/nondehydrated, other settings preserve it. Service validates optional nullable string then rejects unsupported current/incoming/cross-table/dedicated changes including null clears. Existing registry own-profile compatibility uses same helper/constants; no Gate global registration or role grant edits.
- Targeted SQLite2 tests /24 assertions PASS (2.129s, memory92MB): choose UserPolicy => own edit/save allowed/foreign denied; choose BasePolicy or clear => own edit without edit_users denied; existing leading slash normalizes and restores own access; no delete grant; cross-table UserPolicy, unknown class and array payload rejected; unknown current policy unchanged on label Save; forged null/base overwrite rejected; dedicated scope denied. All metadata/data writes isolated SQLite, no working DB or files touched, no migrations.
- Full BREAD regression/lint/diff next. Unknown existing policy methods are not newly executed; compatible choices only. Existing self-role safeguards unchanged. Creation wizard model/controller/policy choices and arbitrary custom policies/class generation remain open. Next exact task CLASSES-E: apply same supported controller/policy choice/default rules to BREAD creation wizard with permission/rollback guards; no class generation until separately audited. Full ADM-AUD-06G remains IN PROGRESS.
- Итог CLASSES-D: DONE для выбора поддерживаемых policy в настройках существующего BREAD. Full SQLite BREAD120 tests /2032 assertions PASS (95.544s, memory140MB; CLI memory_limit=512M), targeted2 /24 PASS, PHP lint/git diff --check PASS. Chosen UserPolicy/BasePolicy/null influence own-profile runtime as original; foreign access and role grants unchanged. Unknown/dedicated policies not made executable/editable. Working DB/files untouched, migrations absent. Next exact step CLASSES-E supported controller/policy selection in new BREAD creation wizard with atomic metadata and authorization tests. Full CLASSES/ADM-AUD-06G remains IN PROGRESS.
## ADM-AUD-06G-CLASSES-E — DONE
Выбор совместимых controller/policy при создании BREAD. Проверки: SQLite, запрет неподдерживаемых классов, атомарный откат; реальную БД не изменять.

- Итог CLASSES-E: DONE для выбора совместимых controller/policy при создании BREAD. Full SQLite BREAD122 tests /2058 assertions PASS (101.864s,142MB; CLI memory_limit=512M); targeted5 /62 PASS; PHP lint/git diff --check PASS. New user BREAD stores VoyagerUserController/UserPolicy choices; generic BaseController/BasePolicy normalize; incompatible/array classes rejected; field errors roll back metadata/rows/menu/permission definitions; role grants unchanged. No real DB/lang/image writes, no migrations. Full ADM-AUD-06G remains IN PROGRESS. Next exact task CLASSES-F — audit original model/controller/policy class-generation flow and existing implementation before deciding compatible transfer; arbitrary class dispatch remains unsupported.

## ADM-AUD-06G-CLASSES-F — DONE, 2026-10-09
Source audit of BREAD class generation. Original VoyagerBreadController store/update call DataType::updateDataType; form takes existing class names; BreadAdded listeners only menu/permissions. Generation found in separate VoyagerDatabaseController and voyager:make:model command, not BREAD editor. Correct misleading model helper text and run isolated creation/settings checks; no generator or migrations to invent.

## ADM-AUD-06G-CLASSES-F — DONE (source audit), 2026-10-09
- Verified original vendor/tcg/voyager/resources/views/tools/bread/edit-add.blade.php: model_name/controller/policy_name are text inputs for existing class identifiers; no generation actions. VoyagerBreadController::store/update call DataType::updateDataType; that saves metadata/rows/permissions and uses app(model_name), not Artisan or filesystem code generation. VoyagerEventServiceProvider BreadAdded listeners only AddBreadMenuItem/AddBreadPermission; application EventServiceProvider adds no BREAD class generator.
- Actual model generation is a separate database-tool feature: VoyagerDatabaseController::store requires browse_database, optionally invokes voyager:make:model after table creation (optional migration), and cleanOldAndCreateNew invokes it on rename. MakeModelCommand extends Laravel ModelMakeCommand, uses a plain Eloquent model stub and optional SoftDeletes. No model/controller/policy generators exist in the inspected BREAD editor; do not represent their absence as an unported BREAD feature. Prior entries listing BREAD class generation as pending are superseded by this audit. No new generator/migration/runtime dispatch introduced.
- Corrected existing model selector helper: existing models; classes prepared separately, matching original BREAD. Current existing-model requirement and compatible controller/policy allowlist remain deliberate limits; arbitrary custom handlers/policies and model-less tables are not declared fully migrated.
- Evidence: targeted SQLite creation/controller/policy suite8 tests /101 assertions PASS (4.395s,98MB); PHP lint and git diff --check PASS. Only helper wording changed in runtime; previous full BREAD122 /2058 PASS remains last full regression. No real DB/lang/images/source-class writes and no external requests.
- Next exact task ADM-AUD-11-DB-A — audit original database tools (table/column create/edit/rename/drop, model/migration options) against current implementation, inventory existing work and actual missing functions before implementation. Separate from BREAD metadata/field configuration; database schema mutations need explicit operation review and isolated tests. Full admin migration remains IN PROGRESS.
## ADM-AUD-11-DB-A — TODO
Audit original database tool UI/routes/controllers/schema/model-generation features and current implementation before transfer. Do not mutate working schema during audit.

## ADM-AUD-06-FINAL — DONE (verification), 2026-10-09
Final verification of requested CHILD/TAG and FIELDS/OPTIONS/MEDIA/CLASSES subtasks: match source and implementation scope, rerun isolated regression, publish status matrix with unresolved items. MEDIA-RECOVERY-E remains cancelled per user original-compatible decision. No real DB/image writes or external integration requests.

### ADM-AUD-06-FINAL — промежуточный результат
Создана итоговая матрица всех 36 запрошенных идентификаторов: docs/bread_final_verification_2026-10-09.md. Найден и исправлен TAG × CLASSES: стандартный явный BaseController больше не запрещает createOption; null/leading slash совместимы, custom handler по-прежнему 403. Targeted SQLite1 /55 PASS; PHP lint40 files PASS. Первый полный прогон до исправления122 /2058 PASS; полный повтор после исправления в работе. Общие OPTIONS-TYPES, EDITORS-MEDIA, MEDIA-RECOVERY и CLASSES остаются PARTIAL; E recovery CANCELLED. Открыты type-specific options, расширенные editors, custom handlers/policies, maps/MySQL spatial, callable defaults/other relations и production media checks. Это ограничения, не ошибки закрытых подэтапов. Рабочие DB/изображения не изменены.

- ADM-AUD-06-FINAL — DONE (проверка): полный повтор после TAG исправления122 tests /2064 assertions PASS (112.002s,142MB; CLI memory_limit=512M), targeted1 /55 PASS, PHP lint40 files PASS. Матрица36 задач: docs/bread_final_verification_2026-10-09.md. OPTIONS-TYPES/EDITORS-MEDIA/MEDIA-RECOVERY/CLASSES PARTIAL; MEDIA-RECOVERY-E CANCELLED, не выполнять удалённую миграцию. Подэтапы DONE только в описанных границах. Новых endpoint/миграций/записей рабочей DB/images нет. Следующий точный шаг по данному аудиту: OPTIONS-TYPES — source/metadata audit time placeholder/date/timestamp options и изолированные tests before transfer; отдельный ADM-AUD-11-DB-A database tools остаётся TODO. Нового коммита в рамках проверки не создавали.

## ADM-AUD-06G-OPTIONS-TEMPORAL — DONE, 2026-10-09
Original time view consumes placeholder/default; date uses Y-m-d and label placeholder; timestamp displays m/d/Y g:i A and Carbon parses input. Add native temporal adapter, explicit storage/display/timezone and malformed-input guards, isolated Add/Edit/shared-language tests. Legacy timestamp step/format metadata are not consumed by original view and should not become invented options. Remaining requested gaps continue after this step.

## ADM-AUD-06G-OPTIONS-EDITOR-B — DONE (native subset), 2026-10-09
Read-only current metadata audit: no unsupported generic controllers/policies, no configured tinymceOptions/relationship/callable default/coordinates found; maps key absent (only presence reported). Legacy global TinyMCE still supplies code/image/textcolor/table commands even with no options. Add native HTML-source action and mapping for available image/text-color commands, preserving editor drafts until parent Save. Arbitrary JS plugins/callbacks remain unsupported; no need to invent adapters for absent classes. Two pre-existing missing-model metadata entries canvas/Canva and a_collag_faq/ACollagFaq recorded for separate physical-source audit, not rewritten.

## OPTIONS-TEMPORAL / EDITOR-B — implementation evidence, 2026-10-09
BreadTemporal adds legacy time placeholder/default metadata validation, date Y-m-d label placeholder, timestamp non-native display m/d/Y g:i A and Y-m-d H:i:s storage, app timezone; malformed input rejected, language shared state retained. Legacy timestamp step/format options not consumed by source, so not implemented as invented behavior. Native RichEditor now offers HTML source modal (Filament-supported HTML), text color/table in default toolbar and code/image/forecolor legacy mappings; public image attachments under table/month with MIME/10MiB limits. HTML action changes draft and sends setContent command; actual persistence only parent Save, translations isolated. Native TipTap schema still limits arbitrary legacy HTML/plugins; not claiming full TinyMCE engine parity. Targeted temporal+editor tests PASS; full regression next. Read-only current DB audit found no configured coordinates/callable defaults/TinyMCE options/unsupported generic controllers/policies; maps key absent. Two obsolete missing-model entries require separate schema source audit, no metadata rewritten. Working DB/images untouched.

- Итог OPTIONS-TEMPORAL/EDITOR-B: DONE в описанных границах. Full SQLite BREAD124 tests /2110 assertions PASS (90.510s,144MB; CLI memory_limit=512M). Последние UI-only уточнения (wrap source, Применить/Отмена, отсутствие второй кнопки table): targeted3 /67 PASS (2.750s,90MB); PHP lint/diff PASS. Chrome read-only gallery-page/1/edit: HTML toolbar/action/modal и подсветка проверены, Cancel без ввода/Save; дубль table устранён. Подсказка settings больше не утверждает, что controller/policy только readonly. Temporal validation/Add/Edit/storage/timezone/language isolation подтверждены SQLite; браузерный timestamp не проверялся при отсутствии безопасного editable fixture. Working DB/image paths неизменны, нет migration/real writes. EDITORS-MEDIA full TinyMCE HTML/plugins parity остаётся PARTIAL; callable/maps/custom classes без metadata пока не реализовывать как новые зависимости. MEDIA-RECOVERY-E отменён; A-D intact. Следующий точный шаг: review original global TinyMCE default styles/background-color/indent and legacy HTML documents against native TipTap preservation; при необходимости отдельный compatible raw HTML editor, до объявления полного EDITORS-MEDIA DONE.

## ADM-AUD-06G-OPTIONS-EDITOR-C — DONE, 2026-10-09
Required preservation follow-up: native TipTap schema can normalize/drop legacy HTML styles/classes/embedded form or iframe. HTML source Apply now switches the field to CodeEditor and writes raw draft state rather than feeding HTML through TipTap; mode isolated by locale and reset on opening another record/create. Existing legacy tags/attributes outside native supported subset detected during fill and open directly in HTML mode. Tests confirm exact old HTML survives unchanged Save and translated/source editing, invalid array rejected. Targeted4 /79 PASS (3.309s,90MB); full regression next. No writes to working DB/image files.

### ADM-AUD-06G-OPTIONS-EDITOR-C — проверка защиты вложений и общих полей
HTML source Apply сохраняет исходную разметку в CodeEditor без передачи в TipTap. Если в текущем документе есть ещё временная inline-картинка, Apply отклоняется с ошибкой поля HTML: сначала нужно сохранить основную форму. Модальное окно не переносит файлы на public disk и не записывает БД. Общие непереводимые rich-поля используют общий режим HTML во всех языках; переводимые поля сохраняют отдельный режим по locale. Режим сбрасывается при открытии другой записи/создания.
Evidence: targeted5 tests /104 assertions PASS (3.729s,92MB), включая native attachment action со Storage::fake, отсутствие permanent файлов до Save, сохранность legacy style/class/form/input/iframe и shared draft после смены языка. Full126 /2138 PASS до уточнения shared mode; полный повтор актуальной версии выполняется. PHP lint6 изменённых файлов и git diff --check PASS. Не менялись рабочая БД, изображения или исходный проект; миграций нет.
## ADM-AUD-06G-OPTIONS-EDITOR-C — DONE, 2026-10-09
Итог актуальной версии: полный VoyagerBreadTest — 126 tests /2147 assertions PASS (85.141s,144MB; PHP8.3 memory_limit=512M). Targeted temporal/source/translation/attachment/shared-mode — 5 tests /104 assertions PASS; PHP lint6 файлов и git diff --check PASS. Raw HTML mode сохраняет исходные styles/classes/embedded elements, общий режим непереводимых полей переживает смену языка, переводимые режимы изолированы; pending inline attachments блокируют переход до общего Save. Предыдущая запись EDITOR-B о setContent описывает промежуточную реализацию: актуальный Apply использует rawState + CodeEditor.
Temporal и native source/image/text-color/table gaps закрыты. Рабочая БД, реальные картинки, старый проект не изменены, миграций и внешних запросов с записью нет; коммит не создавался.
Оставшиеся границы: native toolbar не воспроизводит весь TinyMCE plugin/visual styles/background/indent UI; произвольные JS callbacks/application validators/controller dispatch не выполняются. Текущие metadata не требуют дополнительных custom/coordinates/callable adapters, но production snapshot нужен для повторного read-only audit (включая missing-model entries), затем конкретные адаптеры по обнаруженным контрактам. Maps/Places и реальный MySQL/SRID/media codecs остаются непроверенными. MEDIA-RECOVERY-E CANCELLED, аварийная outbox-инфраструктура не возвращалась; A-D сохранены. Общие PARTIAL этапы не объявлять полностью DONE. Следующее точное действие: после предоставления production snapshot выполнить read-only bread:audit и сверку data_types/data_rows/model/schema, затем закрывать обнаруженные реальные несовместимости на изолированных fixtures. ADM-AUD-11-DB-A database-tools — отдельная TODO, текущей доработкой BREAD не закрывается.
## ADM-AUD-11-DB-A — IN PROGRESS, 2026-10-12
Сравнение оригинального VoyagerDatabaseController/DatabaseUpdater/database views с viar13. Текущий BreadTables — metadata каталог; физический database manager отсутствует. Оригинал: browse_database, schema list/show, hidden tables/prefix, DDL create/edit/rename/drop через Doctrine, опциональный make:model/migration. update не синхронизирует BREAD автоматически (TODO в оригинале); reorder_column содержит незавершённый raw SQL и не является работающим контрактом.
Подзадачи: ADM-AUD-11-DB-B — каталог/просмотр физической структуры, indexes/FK, permission и связь с BREAD (IN PROGRESS); ADM-AUD-11-DB-C — создание таблиц/колонок и DDL preview/validation (TODO); ADM-AUD-11-DB-D — изменение/rename/index/drop и stale schema guards (TODO); ADM-AUD-11-DB-E — модель/migration generation (TODO). Тесты только SQLite :memory:, без реальных DDL/DB/image writes. Никаких автоматических data_types/model renames при изменении schema.
## ADM-AUD-11-DB-A — DONE (audit) / ADM-AUD-11-DB-B — DONE, 2026-10-12
Сравнены VoyagerDatabaseController, DatabaseUpdater и database/index/edit-add/vue editor в C:/OSPanel/domains/asoft/viar (read-only), текущие BreadTables/BreadMetadata. В оригинале physical DDL отдельно от BREAD. UI migration checkbox закомментирован как unfinished; endpoint принимает create_migration, но генерация не воспроизводит готовую полную schema migration. Не добавлять миграции только ради проверки. reorder_column содержит unfinished raw SQL, автоматический BREAD sync в update закомментирован. FK metadata доступны в schema, отдельного полноценного FK editor в просмотренном Vue UI не обнаружено.
Реализованы DatabaseInspectionService, DatabaseTables и management Blade: /filament/database-tables, поиск, visible tables/current schema, connection prefix normalization, columns/type/null/default/auto increment/generation/comment, indexes/PRIMARY/UNIQUE, foreign keys/actions, переход к add/edit BREAD только с browse_bread. Вход и каждый метод проверяют browse_admin + browse_database; selectedTable Locked; только known visible names допускаются к describe (hidden/missing/injection-like names 404). Legacy voyager.database.index и /admin/database отображаются в прежнем месте меню с active path. Не выполняются row count/scans/DDL/metadata writes.
Evidence: полный SQLite BREAD + menu regression130 tests /2192 assertions PASS (70.101s,144MB), затем финальные targeted catalog/menu5 /50 PASS (1.030s,82MB) после prefix/GET уточнений; lint новых PHP/diff PASS. Fixtures проверяют default/NULL/unique/FK, неизменность records/schema/BREAD, missing/hidden identifiers, prefix без double-prefix, HTTP GET structure и отзыв permissions между Livewire requests. Рабочая БД/картинки не изменялись, внешних write requests и миграций нет.
Chrome: оригинальный /admin/database доступен, каталог подтверждён. Новый каталог/menu/breadcrumb/search и GET structure gallery_holsts проверены DOM и screenshot на текущем MySQL runtime: id bigint unsigned/AUTO INCREMENT, name/density/hint/image varchar, price double, hit/default tinyint default0, timestamps NULL и PRIMARY id; FK отсутствуют. Native GET ссылки structure/close позволяют bookmark/back и не зависят от transient Livewire POST конфигурации. При первоначальном Livewire inspect запросе runtime вернул MissingAppKeyException; ключ не меняли, приложение не перезапускали. Search ранее ответил успешно; полный Livewire runtime после исправления окружения повторно проверить. До GET fallback исправлено quoting/loading target UI, затем wire:click заменён navigation link.
Осталось: ADM-AUD-11-DB-C — create table/column editor и native Schema validation/preview/изолированные DDL tests; ADM-AUD-11-DB-D — edit/rename/index/drop + stale schema guards (не запускать реальные destructive операции); ADM-AUD-11-DB-E — original optional model generation, collision/namespace review, без навязанной миграции. ENV-ADM-LIVEWIRE-KEY — TODO: проверить причину отсутствия APP_KEY именно при POST, не генерировать/менять существующий ключ. Следующий точный шаг: ADM-AUD-11-DB-C, first audit original supported types/column defaults/options and implement table creation fixture; общий database manager остаётся IN PROGRESS. Коммит не создавался.

## Исправление обозначений задач — 2026-10-12
ADM-AUD-07 уже относится к ALT bulk/revert. Ошибочно добавленные database-tools 07A–07E переименованы в ADM-AUD-11-DB-A–E во всех активных документах. Назначение и статусы не менялись: A сравнение, B каталог/структура, C создание таблиц/колонок, D изменение/rename/index/drop, E генерация модели. Старые названия в истории Git/чата — прежние обозначения этих же database задач.

## ADM-AUD-11-DB-C — IN PROGRESS, 2026-10-12
Создание таблицы отдельной страницей: native Laravel Schema, список колонок как в Voyager (имя/тип/длина/nullable/unsigned/auto increment/index/default), id integer unsigned PRIMARY по умолчанию. Только явно поддерживаемые SQL типы; ошибочные или неприменимые options отклонять. Предварительный SQL через connection pretend без исполнения; изменение формы требует нового preview, повторное имя отклонять до DDL. Генерация модели отдельно ADM-AUD-11-DB-E, schema edit/drop отдельно ADM-AUD-11-DB-D. Тестовые DDL только SQLite :memory:. Рабочий браузер — чтение формы без Submit.
## ADM-AUD-11-DB-C — PARTIAL (базовое создание реализовано), 2026-10-12
Реализованы DatabaseCreate (/filament/database-tables/create), DatabaseTableCreationService и форма колонок с начальным id integer unsigned PRIMARY AUTO INCREMENT. Доступ browse_admin + browse_database проверяется при открытии и каждом действии. Поддержаны 19 типов, длина string/char, decimal precision/scale, NULL, unsigned целых чисел, AUTO INCREMENT, одиночные PRIMARY/UNIQUE/INDEX и литеральные defaults. Имена/повторы/несовместимые параметры валидируются; существующая таблица не перезаписывается. Предварительный SQL через native Schema pretend не выполняет DDL; создание разрешено только для той же проверенной формы, определение повторно валидируется перед записью. Ошибка БД сбрасывает preview, сообщает об отказе без SQL-деталей; в лог попадают только имя таблицы и SQLSTATE. При частичном MySQL DDL созданная структура не удаляется автоматически и требует просмотра перед повтором.
Evidence: полный BREAD + menu regression 133 tests /2240 assertions PASS (82.898s,144MB) до последних уточнений defaults/index names/error handling; финальный targeted creation + catalog 6 tests /84 assertions PASS (2.200s,90MB) после уточнений. Проверены отсутствие DDL при preview, защита от изменённого определения и отзыва permissions, фактические колонки/index/default/insert, invalid options, частичный отказ и сохранность BREAD. PHP lint новых page/service PASS. Все записи только в SQLite :memory:, рабочая БД/картинки не изменялись; миграций и коммита нет.
Chrome: read-only GET формы подтверждён по AX/DOM — начальная колонка id и настройки видимы, кнопка создания появляется после preview. Данные не вводились, preview/create на рабочем runtime не запускались. Screenshot формы завершился timeout; визуальная проверка этой формы не завершена. ENV-ADM-LIVEWIRE-KEY остаётся TODO: прежний MissingAppKeyException на POST не исправлялся и не перепроверен этим read-only открытием.
Осталось для полной совместимости ADM-AUD-11-DB-C: сверить/перенести расширенные типы оригинала (enum/set/spatial/Doctrine-specific), integer length и float/double precision/scale, unsigned нецелых чисел и допустимые type-specific defaults/indexes. Сейчас составные индексы/PRIMARY и FK editor не поддерживаются; original FK UI ранее не обнаружен, не считать его обязательным без source evidence. SQL defaults functions не выполняются, пустой default означает отсутствие default. Следующий точный шаг: ADM-AUD-11-DB-C-TYPES — сверка оригинального Type/DatabaseUpdater и перенос подтверждённых опций на изолированных fixtures; затем ADM-AUD-11-DB-D edit/rename/index/drop/stale schema, ADM-AUD-11-DB-E optional model generation. Общий database manager IN PROGRESS, полной совместимости создания пока нет.
## ADM-AUD-11-DB-C-TYPES — IN PROGRESS, 2026-10-12
Сверка с оригинальным MySQL Type/Platform/Column/Doctrine: enum/set явно notSupported; integer display length и float/double length не участвуют в SQL; decimal default precision=10,scale=0 (Vue length не преобразует precision/scale). Binary/varbinary имеют длину по умолчанию 255; bit default1/max64. Добавить подтверждённые native и MySQL-specific типы, исправить binary/float/decimal defaults, trim default как в Vue, группировку списка и сброс default при смене типа. Проверки MySQL SQL выполняются без подключения к серверу; запись только SQLite :memory:. Полную live MySQL совместимость без реального DDL не объявлять проверенной.
### ADM-AUD-11-DB-C-HELPERS — IN PROGRESS, 2026-10-12
Chrome original /admin/database/create подтвердил полный MySQL список (35 selectable types, enum/set disabled), Add Timestamps/Soft Deletes; source database-table-helper-buttons создаёт created_at/updated_at/deleted_at timestamp nullable без default/index. Новая обычная колонка в оригинале integer nullable. Добавить helpers в рамках создания, проверяя повторы и предел100; модель остаётся ADM-AUD-11-DB-E.
## ADM-AUD-11-DB-C-TYPES / ADM-AUD-11-DB-C-HELPERS — DONE (описанные границы), 2026-10-12
Chrome read-only сравнение оригинальной /admin/database/create и новой /filament/database-tables/create подтвердило 35 разрешённых MySQL типов оригинала и disabled SET/ENUM. Новый адаптер охватывает эти типы плюс ранее добавленные aliases STRING/BOOLEAN (37 вариантов). Порядок исходных типов/групп воспроизведён; aliases добавлены в конец соответствующих групп. Добавлены mediumint/year/varchar, binary/varbinary, bit, tinytext/blob variants и 8 geometry types. Binary исправлен с прежнего BLOB на BINARY(255), varbinary=VARBINARY(255), bit default1. MySQL grammar с фиксированным whitelist добавляет отсутствующие Blueprint-типы и plain FLOAT/YEAR; переключается только на время создания/preview и восстанавливается finally, включая ошибку. Schema builder берётся после переключения grammar, cached Schema facade не используется для build. Геометрия без принудительного SRID, как в исходных Types. SQL через native compiler, не через пользовательские raw expressions.
Источник: оригинальные Mysql.php/Type.php, Types/Common/DoubleType.php и Types/Mysql/*, Doctrine MySqlPlatform/_getCommonIntegerTypeDeclarationSQL, Column precision10/scale0, Vue database-column/default/helper-buttons. Integer/float/double UI length в оригинальном SQL игнорируется — новая форма учитывает это; decimal default исправлен с8,2 на10,0. Явное precision,scale через новую форму сохраняется как дополнительная ранее реализованная возможность; исходная Vue сама length в precision/scale не преобразовывала. Unsigned поддержан для decimal/double согласно исходному Doctrine; custom FLOAT его не выводит. Default trim/пусто=>null, значение0 не теряется. TEXT/BLOB default/index и JSON default отключены, SET/ENUM не создаются. Прямой JSON index дополнительно запрещён из-за ограничения MySQL; bit длина>64 и несовместимые настройки отклоняются явно (оригинал bit clamp64). SQLite блокирует MySQL-specific geometry/bit/text/blob variants, не подменяет типы молча.
Форма: сгруппированный список, disabled unsupported options, сброс default при смене типа; недоступные defaults/indexes disabled, date/time/numeric input types. Новая обычная колонка integer nullable, как helper original. Добавить timestamps создаёт created_at/updated_at, soft deletes — deleted_at: timestamp nullable, без default/index/auto/unsigned. Повтор не создаёт дублей, существующие drafts сохраняются, предел100 проверяется до добавления, preview инвалидируется, permissions проверяются повторно.
Evidence: финальные isolated database tests9 /219 assertions PASS (4.655s,102MB). SQL compilation проверяет все37 вариантов MySQL, длины/defaults/unsigned/escaping, отказ enum/set/invalid options и finally restoration при сбое; MySQL PDO — mock только версии, ни server connection, ни DDL/queries не допускаются. SQLite :memory: подтверждает native create/insert/defaults, timestamps/soft deletes NULL, duplicate/max100/revoked permissions и базовые preview/create/catalog сценарии. PHP lint service/grammar/page/test PASS; git diff --check PASS. Chrome GET обеих форм/AX: полный список и исходные служебные helpers подтверждены; screenshot новой формы успешно просмотрен. В Chrome не вводились данные и не нажимались preview/create/helper actions, реальная БД/изображения не изменены, миграций/коммита нет.
Осталось: реальный MySQL DDL и отдельные server/version/storage ограничения не проверены (по ограничению пользователя); ENV-ADM-LIVEWIRE-KEY остаётся TODO, POST после ранее найденного MissingAppKeyException не перепроверен. Не объявлять весь database manager DONE: ADM-AUD-11-DB-D edit/rename/index/drop/stale schema и ADM-AUD-11-DB-E original optional model generation остаются TODO. Общий ADM-AUD-11-DB-C имеет готовый create/types/helpers adapter в этих границах, полной parity всех corner cases Doctrine/defaults/composite indexes не заявляется. Следующий точный шаг: ADM-AUD-11-DB-D — source audit исходной формы изменения таблицы и перенос preview/diff/recheck schema без реального DDL.
## Иерархия database-tools — 2026-10-12
По замечанию пользователя все активные database идентификаторы включены в главный ADM-AUD-11 (служебные инструменты). Источник: docs/filament_functionality_audit_2026-10-08.md, строка23 — явно перечисляет База данных вместе с cache/Compass/sitemap. Прежние ADM-DB-A–E теперь ADM-AUD-11-DB-A–E: A source audit, B каталог/структура, C создание (TYPES/HELPERS), D редактирование/rename/index/drop, E optional model. Названия и статусы существующей реализации сохранены, работы не переобъявлены выполненными. ADM-AUD-08 — другая задача: поиск field/operator, sorting, page size, bulk списков записей; в этом ходе лишь проверено её текущее состояние, реализация не начата и стадия остаётся TODO. Текущая задача: ADM-AUD-11-DB-D — IN PROGRESS (source audit начат, код изменения схемы ещё не реализован); следующий точный шаг — сверить VoyagerDatabaseController/DatabaseUpdater/update UI, затем реализовать preview/diff/stale-schema guards и изолированные DDL tests. Реальная БД/картинки не менялись; коммита нет.
## ADM-AUD-11-DB-D — IN PROGRESS, 2026-10-12
Оригинальный DatabaseUpdater сначала переименовывает колонки, затем применяет diff/indexes/table rename; update не синхронизирует BREAD (TODO исходника), column reorder unfinished. Перенести edit UI/native Blueprint diff и отдельный drop с именем подтверждения, snapshot+preview recheck. Не переписывать metadata/permissions/images. Не выполнять реальный DDL; SQLite fixtures и MySQL SQL compilation. Сохранять неизвестные/generated/composite свойства при отсутствии изменения; зависимые FK и неподдержанные изменения блокировать явно, без отключения FK на рабочем сервере. Первое действие: общий column builder/validation reusable, schema-to-form adapter, diff service и Page.
## ADM-AUD-11-DB-D — PARTIAL (основной адаптер реализован, визуальная проверка ожидается), 2026-10-12
Добавлены DatabaseTableEditingService, DatabaseEdit /filament/database-tables/edit?table=... и Blade, ссылка Изменить таблицу из каталога. Сверены VoyagerDatabaseController/update/destroy и DatabaseUpdater: column rename перед diff, table rename после изменения, BREAD sync TODO в исходнике, физический reorder unfinished. Новая форма редактирует имя таблицы, имена/типы/длину/NULL/unsigned/auto/default колонок, добавляет/удаляет колонки, меняет одиночные PRIMARY/UNIQUE/INDEX в доступных границах; timestamps/soft deletes переиспользованы. Общий column builder выделен из creation service без изменения его контракта по умолчанию. Native Blueprint->toSql компилируется вне pretend, поскольку SQLite alteration требует реальной read-only metadata; в preview statement не выполняется. SQL выводится до Save; draft и snapshot hash проверяются повторно при записи. Только known visible table, browse_admin+browse_database на mount/hydrate/каждом действии; initial UUID->origin Locked mapping захватывается после Filament fill и до Repeater dehydration, чтобы исходные колонки не воспринимались как новые. Изменившаяся schema/входящие FK после открытия формы блокируют сохранение.
Сохранность: неизменённые columns/default expressions/generated properties и существующие дополнительные/composite indexes не переписываются; при изменении обычных колонок сохраняются collation/comment. Новые значения валидируются общим whitelist. Rename collision/existing table/forged origin запрещены. Изменение generated column запрещено; column rename/drop внутри composite index и изменение колонок FK/incoming references блокируются явно. SQLite любая alteration таблицы со связями и PRIMARY drop блокируются; остальные DDL выполняются transaction с finally restore foreign_keys. Для MySQL DDL atomicity не обещается: после отказа preview сброшен, требуется просмотр фактической schema, автоматического destructive cleanup нет. Ошибки БД показываются без SQL details, лог только table/SQLSTATE. Поддержка произвольных expression defaults/type transitions/composite/FK editor/primary-auto transitions не объявляется полностью эквивалентной Doctrine.
Удаление: отдельный SQL preview, ввод точного original table name, UI confirm, повторный schema/permission/FK check. Referenced table не удаляется; удаление не чистит data_types/permissions/модели, как исходный destroy. BREAD sync и reorder не добавлены как выдуманные рабочие функции. События legacy TableAdded/Updated/Deleted в новом runtime не эмулируются; поиск app/routes не обнаружил текущих потребителей. Генерация модели остаётся отдельной ADM-AUD-11-DB-E.
Evidence: финальные isolated database tests14 /291 assertions PASS (6.774s,104MB): no-op preview/изменённый draft/stale schema, rename table+column/length/default/NULL/add/drop/index, retained rows и auto increment, exact table confirmation/drop, revoked permissions, hidden/missing targets, forged origins, incoming FK/composite/generated guards. SQLite fixtures подтверждают сохранность generated values и нескольких indexes, rollback при unique failure и восстановление FK flag. MySQL SQL compilation с mock PDO/version без server connection проверяет rename/modify/index/collation/comment/table rename и restoration schema grammar. Все записи только SQLite :memory:. PHP lint edit service/page/creation service/test и git diff --check PASS. Полный общий BREAD regression в этом ходе повторно не запускался; релевантный creation/catalog/edit regression включён в14 tests.
Chrome: попытка read-only открытия gallery_holsts edit завершилась navigation timeout; созданная вкладка осталась about:blank, привязка также timeout при focus emulation. Screenshot/визуальная проверка этой формы не выполнены; runtime POST после ENV-ADM-LIVEWIRE-KEY также не подтверждён. Реальных preview/save/drop/helper clicks, изменений рабочей БД/изображений, миграций/коммита нет.
Следующий точный шаг: восстановить доступ Chrome и визуально сравнить новую форму с /admin/database/gallery_holsts/edit без Save/Drop; затем закрыть обнаруженные UX проблемы. ADM-AUD-11-DB-D остаётся PARTIAL до этой проверки, ограничения связей/composite/Doctrine corner cases явно сохранены. После этого — ADM-AUD-11-DB-E (optional model generation); ADM-AUD-08 generic списки — отдельный TODO.
### ADM-AUD-11-DB-D — повторная визуальная проверка, 2026-10-12: PARTIAL
Chrome: оригинальная форма /admin/database/gallery_holsts/edit открылась в существующей вкладке; AX и screenshot подтверждают 10 колонок (id, name, density, hint, price, hit, created_at, updated_at, image, default), BIGINT unsigned PRIMARY auto для id, VARCHAR(255) для строк, DOUBLE price, TINYINT default=0 для hit/default, nullable timestamps/image. В оригинале checkbox означает Not Null (в новой форме NULL имеет обратный смысл). Подтверждены группы типов, disabled ENUM/SET, добавление колонки/timestamps/soft deletes и Update Table.
Новая форма: создание вкладки и переход в существующей вкладке к /filament/database-tables/edit?table=gallery_holsts снова завершаются Chrome CDP Page.navigate timeout; затем AX и DOM чтение этой вкладки также завершаются focus emulation timeout. Инвентаризация вкладок работает, старая админка доступна. Не считать это доказательством ошибки приложения или визуальной совместимости: новая форма не показана. Документированный browser troubleshooting прочитан, проверены новая и существующая вкладки, AX и DOM способы. Дополнительные повторы без изменения условий прекращены.
Код и тесты в этом шаге не изменены; прежние isolated 14 tests /291 assertions остаются последним evidence. Реальные preview/save/drop/helpers не запускались, рабочая БД и изображения не изменены. Следующий шаг: после восстановления управления новой вкладкой Chrome открыть оба редактора и завершить сравнение новой формы; до этого ADM-AUD-11-DB-D остаётся PARTIAL. ADM-AUD-11-DB-E и ADM-AUD-08 остаются следующими отдельными задачами.
### ADM-AUD-11-DB-D — визуальная сверка выполнена и исправления, 2026-10-12
Повторная проверка Chrome через меню: каталог /filament/database-tables открывается. При первоначальном переходе в editor снова timeout; в laravel.log обнаружен production Maximum execution time 300 seconds exceeded (11:31:49, Database Connection.php), поэтому прежний вывод о чисто браузерном блокере недостаточен. В snapshot устранён полный обход MySQL/MariaDB таблиц: один параметризованный SELECT information_schema.KEY_COLUMN_USAGE выбирает таблицы текущей schema, ссылающиеся на выбранную; дальше native getForeignKeys только этих таблиц. Скрытые таблицы остаются участниками защиты; префикс учитывается. SQLite сохраняет прежний проверенный путь. После исправления новая форма открылась в Chrome и повторные reload успешны (последний 5.538s — wall time browser tool, не серверный benchmark).
Сравнены screenshots original /admin/database/gallery_holsts/edit и new /filament/database-tables/edit?table=gallery_holsts. Все 10 имён/типов/длины строк255/PRIMARY/unsigned/auto/default0 и NULL flags совпадают по смыслу. Исправления UX: большие карточки Repeater заменены нативным table Repeater в create/edit; таблица прокручивается горизонтально внутри блока, единая подсказка под ней вместо повторения в каждой строке, кнопки с отступами, короткое «Нет» для отсутствия индекса. Для edit выключено перетаскивание: физический порядок не применяется (оригинал тоже не завершил reorder). DEFAULT NULL из MariaDB introspection нормализован в null до раскодирования строковых литералов; quoted 'NULL' остаётся строкой, '0' сохраняется. Это исправляет отображение timestamps/image, рабочие defaults в БД не изменялись.
Evidence: Chrome screenshots до/после; после reload видны все 10 строк и действия без выхода за правую границу на desktop, проверен нижний отдельный блок удаления без нажатия. Снимок storage/app/audit/adm-aud-11-db-d-desktop.jpg. Изолированный database regression16 tests /298 assertions PASS (6.344s,104MB); новый тест targeted dependency lookup без MySQL подключения/DDL, SQL NULL против literal NULL; PHP lint и git diff --check PASS. Последнее изменение placeholder «Нет» проверено финальным screenshot. Реальные preview/save/drop/helper actions не выполнялись, рабочая БД и изображения не менялись, миграций/коммита нет.
Статус: визуальная проверка desktop этой формы завершена. ADM-AUD-11-DB-D в целом PARTIAL по ранее перечисленным advanced/type/primary-auto/MySQL DDL ограничениям; live writes и mobile viewport не проверены. ENV-ADM-LIVEWIRE-KEY runtime POST остаётся отдельно TODO. Следующий точный шаг: ADM-AUD-11-DB-E optional model generation по оригиналу, либо отдельная согласованная проверка advanced DDL на fixtures; ADM-AUD-08 generic списки остаётся отдельной задачей.
### ADM-AUD-11-DB-LIST — IN PROGRESS, 2026-10-12
Пользователь показал original Database и новый каталог. Исправить плотность и размещение: имя таблицы как ссылка на структуру, отдельная группа BREAD (browse/edit/delete/add), отдельная группа физических действий view/edit/delete справа; компактные подписи без переноса; обычный алфавитный порядок. Browse URL переиспользовать из existing BREAD registry/routes, physical delete ведёт к уже защищённому разделу DatabaseEdit. Удаление BREAD сверить с original destroy (browse_bread, metadata/translations/permissions), реализовать отдельно от удаления физической таблицы с подтверждением и isolated fixtures. В рабочей БД не выполнять удаления/DDL.
### ADM-AUD-11-DB-LIST — DONE (desktop UI + isolated функционал), 2026-10-12
По screenshots пользователя сравнен original /admin/database и новый каталог. Компактные строки (8px vertical padding), имя таблицы — ссылка на структуру, отдельная центральная группа BREAD (Открыть/Редактировать/Удалить или Добавить), отдельная правая группа physical Структура/Изменить/Удалить. Подписи не переносятся, striped rows, горизонтальная прокрутка внутри таблицы. Алфавитный SORT_STRING вместо natural sorting восстанавливает a_collage_* в начале, как original; реальные дополнительные таблицы новой БД не скрывались ради визуального совпадения. Browse URL централизован в BreadRegistry::browseUrl, переиспользован в BreadTables и DatabaseTables с существующими model/permission checks и специальными orders/slider/SEO/ALT/menu routes. Удалить physical table — GET link в existing DatabaseEdit#database-delete, не immediate DROP.
Добавлено самостоятельное удаление BREAD: browse_admin+browse_bread в service, browse_database также в catalog action; разрешены только known visible physical names. Locked selected table/fingerprint; modal с точным именем и отдельный confirm, snapshot type/rows/translations/permissions/role assignments защищает от stale definitions. DB transaction удаляет metadata type/rows, только их translations, permissions table_name и pivot assignments. Records/physical table/model/files/content translations сохраняются. Original VoyagerBreadController::destroy авторизует browse_bread, удаляет datatype translations и Permission::removeFrom(table); dependent rows в исходной БД удалялись cascade. Legacy events не эмулируются. QueryException rollback + generic validation error, log table/SQLSTATE без SQL/секретов. Не добавлены migration или DB schema changes.
Evidence: final isolated database + existing BREAD catalog19 tests /334 assertions PASS (6.453s,110MB). Проверены exact confirmation, stale metadata rejection, revoked permission403, successful metadata/permission cleanup, сохранность physical rows и content translations; SQLite abort trigger проверяет rollback metadata/translations/pivots при ошибке последнего permission deletion. Все mutations только SQLite :memory:. PHP lint новых service/page/registry и git diff --check PASS. Chrome read-only reload /filament/database-tables screenshot подтверждает компактный catalog, a_collage order, 6 actions для настроенных доступных BREAD и отсутствие выхода кнопок за правый край на desktop. Screenshot storage/app/audit/adm-aud-11-db-list.jpg. Modal/action writes в working runtime не запускались; ENV-ADM-LIVEWIRE-KEY POST limitation остаётся отдельным TODO; mobile viewport не проверен.
Осталось по общей ADM-AUD-11-DB: E optional model generation, ранее зафиксированные advanced DDL ограничения D и отдельная runtime POST проверка после восстановления key. Working DB/images не изменялись, commit не создан.
## ADM-AUD-11-DB-D — дополнительная сверка редактора с Voyager, 2026-10-12
Статус этой правки: DONE (desktop UI + isolated regression); общая ADM-AUD-11-DB-D остаётся PARTIAL.
По screenshots a_collage_advantage_screen заменены переключатели на флажки Not Null / Unsigned / Auto increment. Not Null инвертирует только видимый checkbox: backend nullable=true по-прежнему означает разрешённый NULL. Для TEXT показана справочная длина 65535, для TIMESTAMP — 0; это readonly placeholders, не новые значения DDL. Добавить колонку / timestamps / soft deletes сгруппированы по центру. Кнопка Сохранить таблицу постоянно видна справа и через prepareSaveStructure готовит SQL с подтверждением в modal; прежние preview hash/schema/permission guards сохранены. Технические пояснения и удаление свёрнуты; ссылка удаления из каталога раскрывает соответствующий блок через action=delete.
Проверки: final isolated database regression 19 tests / 344 assertions PASS (6.937s,128MB), включая подготовку сохранения без DDL и сохранение nullable semantics после rename в SQLite :memory:. Chrome read-only reload и rendered DOM подтверждают все 7 строк и флажки как в оригинале; screenshot storage/app/audit/adm-aud-11-db-d-aligned.jpg. Live POST/save/drop/helper actions не запускались, рабочая БД и картинки не изменялись, миграций и коммита нет.
Осталось: ранее описанные advanced DDL/primary-auto/type/composite/FK ограничения общей D, mobile и runtime POST проверка (ENV-ADM-LIVEWIRE-KEY остаётся TODO). Следующий точный этап общей задачи — ADM-AUD-11-DB-E, optional model generation по оригиналу.
## ADM-AUD-11-DB-E — IN PROGRESS, 2026-10-12
Source audit: VoyagerDatabaseController::store optional create_model, namespace config voyager.models.namespace=App\Models\, Studly(singular(table)), plain Eloquent stub. SoftDeletes auto detection commented out; migration checkbox hidden in Vue; update does not invoke generator (cleanOldAndCreateNew call commented). Implement optional creation only, preview model name/source, collision checks before DDL, no overwrite; generation failure after MySQL DDL must clearly report table already created. Tests temporary filesystem + SQLite :memory:, no runtime DB writes/migrations.
## ADM-AUD-11-DB-E — DONE (перенос original optional model, isolated checks), 2026-10-12
Добавлен DatabaseModelGenerationService и флажок «Создать модель для таблицы» только в DatabaseCreate. Default=false, имя Studly(singular(table)), namespace config voyager.models.namespace (текущий App\Models\), простой Eloquent Model как original stub. Автоматические SoftDeletes/$table/$fillable/BREAD записи не добавляются: их нет в original store/stub. Migration UI в original commented/disabled, поэтому генерация миграций не перенесена; update/rename не пересоздаёт модель, как действующий original update (cleanOldAndCreateNew закомментирован).
Preview показывает class/source без файловой записи; create_model входит в hash формы. До DDL повторно проверяются модель/namespace, collision file/class, доступность каталога; PHP parse отклоняет reserved class names, namespace ограничен App подкаталогами, symlink paths отклоняются. После DDL файл создаётся exclusive fopen(x), без перезаписи. При ошибке генерации после создания таблицы preview сбрасывается, UI явно сообщает «Таблица уже создана»; DDL не откатывается/повторно не запускается ради файла. При изменении настроек namespace требуется повторный preview. Модели создаются только при будущем явном Submit пользователя; этот ход реальных моделей/таблиц не создавал.
Evidence: database regression 22 tests /385 assertions PASS (8.397s,128MB); новые тесты используют SQLite :memory: и случайный temporary app path с cleanup, проверяют pure preview, модель после create, неизменённые BREAD, singular collision/no overwrite до DDL, changed checkbox/stale preview, файл появившийся между preview и submit, traversal/reserved/external namespace rejection, post-DDL generation failure и запрет повторного создания. PHP lint service/page и git diff --check PASS. Chrome GET /filament/database-tables/create подтверждает unchecked model checkbox и компактную форму; ни флажок, ни Preview/Create не нажимались. Screenshot storage/app/audit/adm-aud-11-db-e-create-model.jpg. ENV-ADM-LIVEWIRE-KEY/runtime POST и mobile не проверены; working DB/images/migrations не менялись, commit не создан.
Следующий точный шаг: ADM-AUD-11-DB-D — ревизия оставшихся advanced/type/primary-auto ограничений по original на fixtures; общий database manager остаётся PARTIAL до завершения этих границ и отдельно разрешённой runtime проверки. Прежние TODO для optional model E закрыты этим шагом.
## ADM-AUD-11-DB-D — IN PROGRESS: advanced diff review, 2026-10-12
Source DatabaseUpdater applies column rename before remaining diff. Found: pure rename/index edits unnecessarily validate existing expression defaults as new literals; MySQL PRIMARY/AUTO transitions compile multiple ALTER statements with invalid intermediate state; adding a new PRIMARY beside preserved composite PRIMARY is not rejected before SQL. Fix rename preservation, compile MySQL diff as one ALTER after rename, explicitly add changed PRIMARY, guard existing composite PRIMARY and FK index alterations. Regression SQLite + MySQL compiler mock only, no real DDL.
## ADM-AUD-11-DB-D — advanced diff corrections DONE в указанных границах, 2026-10-12
DatabaseTableEditingService: pure rename/index edits сохраняют существующие type/default attributes без повторной проверки expression default как нового literal. Это позволяет rename CURRENT_TIMESTAMP без переписывания default; новые/изменённые attributes по-прежнему проходят creation whitelist, generated columns запрещены. Index enum/nullable PRIMARY/auto key invariants и TEXT/BLOB/JSON index guard сохранены отдельно. MySQL/MariaDB column rename идёт первым, затем index/drop/modify/add объединены в один native ALTER TABLE; переход PRIMARY+AUTO_INCREMENT не создаёт промежуточного unkeyed auto состояния. При изменении PRIMARY явный primary command добавляется также для auto column, исключая отсутствие или duplicate inline key. Table rename идёт последним; SQLite сохраняет прежнюю Blueprint reconstruction/transaction схему. Existing temporal precision timestamp/datetime/time сохраняется при изменении остальных attributes того же типа.
Guards: второй PRIMARY при сохранённом composite PRIMARY отклоняется до SQL; удаление/замена индекса колонок FK/incoming FK запрещены консервативно. Composite rename/drop, generated changes и SQLite primary/FK restrictions не сняты; физический reorder/BREAD sync не добавлены, в действующем original они unfinished/TODO. Для MySQL один ALTER уменьшает invalid intermediate states, но атомарность всего сценария с отдельным column/table rename не обещается.
Evidence: 24 database tests /401 assertions PASS (7.637s,126MB). SQLite :memory: rename timestamp с CURRENT_TIMESTAMP сохраняет старое значение и default для новой строки. Mock MySQL PDO/version без server connection подтверждает один ALTER drop primary+disable old auto+enable new auto+explicit primary, timestamp(6) precision, grammar restoration. Дополнительные проверки запрещают unkeyed auto, drop referenced index, second primary beside composite. Existing creation/model/catalog/deletion/stale/permissions regression включён. PHP lint и git diff --check PASS. UI в этом ходе не менялся; live SQL/POST не запускались, working DB/images/migrations не менялись, commit не создан.
Общий статус ADM-AUD-11-DB-D остаётся PARTIAL: advanced expression default attribute changes и дополнительная metadata (ON UPDATE/zerofill), server/version/DDL ограничения и runtime POST требуют отдельной сверки. Следующий точный шаг: сверить сохранение MySQL column EXTRA/ON UPDATE при обычном изменении колонки, добавить preservation либо явный отказ до DDL на isolated fixtures. ENV-ADM-LIVEWIRE-KEY, mobile и real MySQL DDL остаются непроверенными; текущие compiler tests не заменяют live server verification.
## ADM-AUD-11-DB-D — IN PROGRESS: сохранение EXTRA/default metadata, 2026-10-12
Native MySqlProcessor drops column EXTRA, while original Doctrine identifies auto_increment by substring and normalizes MariaDB quoted defaults separately from temporal expressions. Add parameterized read-only EXTRA lookup to snapshot, preserve trusted existing expressions only when unchanged, ON UPDATE current timestamp with original precision, ZEROFILL numeric declaration; incompatible/unknown extras rejected before DDL. No user-supplied raw SQL/default expression execution. SQLite :memory: + MySQL compiler/metadata mocks only.
## ADM-AUD-11-DB-D — EXTRA/default preservation DONE в указанных границах, 2026-10-12
DatabaseInspectionService теперь после проверки known visible table читает EXTRA parameterized information_schema.COLUMNS (database schema + prefixed table bindings). Snapshot включает EXTRA и признак quoted defaults MariaDB>=10.2.7; отсутствие metadata для колонки даёт409 вместо молчаливого удаления свойств. AUTO_INCREMENT обнаруживается substring, как original Doctrine, включая дополнительные флаги. EXTRA участвует в существующем schema fingerprint/stale checks.
DatabaseTableEditingService сохраняет неизменённый trusted default expression при редактировании других attributes: DEFAULT_GENERATED/MySQL, temporal CURRENT_TIMESTAMP и unquoted nonnumeric defaults современных MariaDB. Quoted literals остаются literals. Expression берётся только из исходного snapshot и только при точном совпадении default; новый ввод проходит literal validation/escaping. MySQL general expression оборачивается parentheses; CURRENT_TIMESTAMP сохраняется без quote. ON UPDATE CURRENT_TIMESTAMP(precision) передаётся через native Fluent onUpdate(Expression); temporal precision сохраняется также между совместимыми temporal types. ZEROFILL сохраняет исходную numeric declaration/display width/unsigned через grammar с regex whitelist; тип/длина/unsigned такой колонки не меняются без отдельной поддержки, вместо потери свойства выдаётся отказ. Неизвестный EXTRA при MODIFY и несовместимые ON UPDATE/default type changes запрещены до SQL; pure rename/index сохраняет metadata без MODIFY. Source audit: Doctrine MySqlSchemaManager читает auto_increment substring, MariaDB quote/expression различия; его options не включают самостоятельные ON UPDATE/zerofill controls — новые controls не выдуманы, сохранность исходных свойств обеспечена адаптером.
Evidence: final database regression27 tests /423 assertions PASS (8.911s,128MB); PHP lint всех трёх services/grammar и git diff --check PASS. Новые tests проверяют parameterized prefixed EXTRA lookup, auto_increment с другими flags, MySQL compile timestamp(6)+default+ON UPDATE, int(6) unsigned zerofill, general expression и quoted literal с одинаковым содержимым, MariaDB expression classification, unsafe new input escaping, incompatible type/unsigned/unknown EXTRA rejection. SQLite :memory: MODIFY nullable сохраняет CURRENT_TIMESTAMP, старую запись и default новой записи. Первый запуск дополнительного inspection test обнаружил неполный DB mock (connection expectation); test harness исправлен, финальный запуск чистый. Все DDL только SQLite memory, MySQL PDO/version/compiler mocks без server connection. UI не менялся; Chrome POST/live DDL не запускались, working DB/images/migrations не менялись, commit не создан.
Осталось по общей ADM-AUD-11-DB-D: намеренные ограничения composite/FK/generated/unsupported extras и old-server legacy rename; real MySQL DDL/server constraints/runtime POST (ENV-ADM-LIVEWIRE-KEY) и mobile не подтверждены. Совместимость произвольных expression/type transitions и снятие ZEROFILL/FK/composite свойств не заявляются. Следующий точный шаг: итоговая сверка возможностей database create/edit/catalog с original, перечисление unsupported UI сценариев и общего статуса ADM-AUD-11-DB; без реальных writes.
## ADM-AUD-11-DB — IN PROGRESS: final parity review, 2026-10-12
Chrome desktop old/new a_collage_advantage_screen confirms 7 columns/defaults/flags. At390px found shared table min-width1100 stretching Filament mobile cards; adjust only mobile styles. Code review found disabled default/index components may omit existing TEXT/BLOB state on dehydration, potentially change existing metadata in a no-op form; force dehydration and verify isolated existing TEXT default+index no-op/rename. Overall audit covers catalog/create/edit/model/bread deletion guards and explicitly separates live POST/DDL evidence from compiler fixtures. New separate finding: SA widget overlaps breadcrumb on mobile; record under ADM-AUD-03 follow-up, not database write logic.
## ADM-AUD-11-DB — итоговая сверка завершена, статус раздела PARTIAL, 2026-10-12
Отчёт: docs/database_tools_final_verification_2026-10-12.md (source/UI matrix, evidence, ограничения). Chrome original/new catalog/create/edit подтверждает initial order/actions, 35 original types +2aliases, optional model, 7 edit rows/flags/default0. Desktop overflow отсутствует. Найденные дефекты исправлены: mobile table min-width снимается <=767px (390px container/table scrollWidth=width295px); existing disabled TEXT/BLOB default/index принудительно dehydrated и no-op сохранён; physical drop требует сначала remove BREAD, как original index script remove-bread-warning. Tests recheck BREAD added after preview and actual drop only after removal; no-op/rename TEXT fixture сохраняет rows/index/default. Final targeted29 tests /437 assertions PASS (7.968s,126MB); lint и diff check PASS. Working DB/images/migrations не менялись, commit не создан.
Артефакты storage/app/audit/adm-aud-11-db-final-{editor,catalog,mobile}.jpg; viewport reset, обе вкладки возвращены к original/new editors. Live POST/DDL не запускались; ENV-ADM-LIVEWIRE-KEY остаётся TODO. Намеренные composite/FK/generated/unknown EXTRA/legacy version ограничения описаны. Original additional_attributes в show условно не перенесены (в текущих Models не обнаружены); добавлено как compatibility follow-up при появлении такого использования. Следующий точный шаг: ADM-AUD-03 — мобильное размещение плавающей SA кнопки, на390px перекрывает breadcrumb; отдельная runtime POST проверка после устранения ENV блокера и в разрешённых границах. Итоговая проверка DONE, весь database manager не объявлен DONE.
## ADM-AUD-11-DB — IN PROGRESS: authorized browser write cycle, 2026-10-12
Пользователь явно разрешил новый цикл через Chrome: создать новую таблицу/поля/BREAD, назначить editor types и создать запись. Это разрешение supersedes прежний запрет live writes только для отдельного тестового набора. Имя adm_browser_cycle_items, optional model, title/description/quantity/active + timestamps; существующие tables/images не менять. Результат оставить пользователю; удаление не выполнять. Все mutations через UI, evidence screenshots после результатов, при error исправить причину и продолжить. APP_KEY не менять/не печатать.
## ADM-AUD-11-DB — browser cycle: table/BREAD created, 2026-10-12
Через Chrome создана отдельная MariaDB таблица adm_browser_cycle_items (id, title varchar255 NOT NULL, description text nullable, quantity integer default0, active boolean default0, timestamps) и App\Models\AdmBrowserCycleItem. BREAD id205, slug adm-browser-cycle-items, название «Тест браузерного цикла»: text/text_area/number/checkbox назначены и сохранены. Каталог подтверждает BREAD настроен. Права создаются, но не назначаются автоматически; для продолжения через role editor назначаются admin только browse/read/add/edit нового test dataset. Existing permissions не переключались, delete не назначается. Livewire POST create/schema/BREAD прошли без APP_KEY error; общий ENV root cause этим не диагностирован. Следующий шаг — создать запись и подтвердить persistence после reload. Evidence: storage/app/audit/adm-browser-cycle-table.jpg.

## ADM-AUD-11-DB — browser create cycle DONE; admin creator rights DONE, 2026-10-12
Полный разрешённый Chrome цикл выполнен на отдельной локальной таблице adm_browser_cycle_items: schema+модель → BREAD205 → text/text_area/number/checkbox → меню → запись1. Название «Браузерная проверка BREAD», описание «Запись создана через Chrome: text, text_area, number, checkbox.», quantity7, active=true. Reload списка и повторный GET /filament/bread/adm-browser-cycle-items/1/edit подтверждают persistence всех четырёх значений. Тестовые объекты оставлены, existing business records/images не изменялись; local DB получила только новый test dataset, его metadata/menu/permissions и четыре назначения admin через role UI.
Во время настоящего POST найден TypeError updatedData(value, field): Livewire root-array update передаёт field=null. Исправлена nullable signature с guard bulk updates, nested-field slug tracking сохранён; после исправления UI сохранение успешно. Browser root-array regression добавлен в slug feature-test.
Уточнение пользователя: «если админ создал то админу и права будут». BreadCreationService теперь в той же транзакции назначает пять прав нового BREAD только роли admin, которой принадлежит создатель (primary/additional); остальные роли не получают доступ, generate_permissions=false ничего не создаёт/не назначает. UI helper обновлён. Старые назначения не расширяются ретроспективно. Для первой тестовой таблицы до исправления были вручную назначены browse/read/add/edit; delete не проверялся и не назначался. Automatic grant подтверждён isolated SQLite feature-tests, отдельный live BREAD после исправления не создавался.
Evidence: 7 tests /112 assertions PASS (3.821s,96MB): BREAD creation/validation/rollback/permissions boundary + bulk form/slug tracking. PHP lint четырёх changed/new PHP files и git diff --check PASS. Первый тестовый запуск выявил две устаревшие проверки отсутствия grants; ожидания обновлены по явно согласованному новому поведению. Screenshots storage/app/audit/adm-browser-cycle-{table,record,editor}.jpg. Chrome оставлен на списке нового раздела. APP_KEY не изменён, в данном live цикле ошибка не воспроизводится. Миграции не добавлялись, commit не создан.
Общий database manager остаётся PARTIAL в рамках отчёта docs/database_tools_final_verification_2026-10-12.md: этот live цикл подтверждает create/model/BREAD/record, но не real advanced ALTER/drop/FK/composite/generated scenarios. Следующий шаг: при необходимости отдельная сверка runtime редактирования структуры новой тестовой таблицы; данные рабочих таблиц не использовать.

## ADM-AUD-11-DB — delete action visible, 2026-10-12
После вопроса пользователя об отсутствующей кнопке назначено пятое право delete_adm_browser_cycle_items роли admin через Chrome role editor. Новый GET списка подтверждает действия Просмотр/Изменить/Удалить и прежнюю запись1. Screenshot adm-browser-cycle-record.jpg обновлён. Удаление не выполнялось; earlier note о четырёх правах superseded этим шагом. Новый BREAD, созданный admin после исправления, получает все пять прав автоматически.

## ADM-AUD-11-DB — IN PROGRESS: live edit cycle, 2026-10-12
Пользователь разрешил следующий шаг на adm_browser_cycle_items: добавить колонку, изменить её имя/тип/default, проверить сохранность записи1, подключить поле в BREAD и сохранить значение. Все runtime writes через Chrome UI только этой тестовой таблицы; destructive drop не выполнять. План: новая колонка browser_note VARCHAR80 default draft → browser_comment VARCHAR160 default ready; отдельное изменение типа в TEXT если UI/DDL допускает безопасное сохранение. После каждого DDL fresh GET и проверка сохранности записи. Затем BREAD205 new field и запись1, screenshot evidence. Existing business tables/images не менять.

## ADM-AUD-11-DB — live edit findings, 2026-10-12
Первый GET editor дал intermittent MissingAppKeyException; reload успешен без изменения key. Browser управление застопорилось на nested JS confirm внутри SQL modal; redundant wire:confirm снят только с кнопки modal footer (SQL confirmation dialog остаётся, прямой preview-save и drop confirmations сохранены). Ошибка агента: после asynchronous addColumn без подтверждения нового row была выбрана последняя существующая строка updated_at; rename/type/default ошибочно применены только к test table. updated_at восстановлен через Chrome rename+TIMESTAMP nullable без default, fresh structure подтверждает исходные7 колонок. Данные не удалялись. Эта коррекция выявила реальный compiler defect: original character collation переносилась в non-character target type (timestamp). Исправлено: collation сохраняется только для char/string/text targets; isolated compiler regression1test/10assertionsPASS. После восстановления новая восьмая строка подтверждена (9rows включаяheader), SQL исключительно ADD browser_note VARCHAR80 NULL DEFAULT draft проверен и отправлен. Далее fresh structure → rename/type/default → BREAD/record evidence.

## ADM-AUD-11-DB — live structure/BREAD edit cycle DONE, 2026-10-12
В Chrome на отдельной adm_browser_cycle_items завершено: ADD browser_note VARCHAR80 NULL DEFAULT draft; fresh GET подтверждает колонку; RENAME browser_note→browser_comment + MODIFY CHAR160 DEFAULT ready; fresh GET подтверждает name/type/default. Default draft, материализованный при ADD в существующую запись1, сохранён после rename/type/default (смена default не переписала старые значения). BREAD205 full editor обнаружил новую schema column как «Ещё не настроено», поле сохранено с display_name «Комментарий проверки», type text_area, browse/read/add/edit/delete flags. В списке и editor появились новая колонка/textarea. Запись1 сохранила title/description/quantity7/active=true/created_at; новое значение «Новое поле подключено к BREAD после изменения структуры.» сохранено через форму, fresh reload editor подтверждает persistence. updated_at после восстановления присутствует и успешный Eloquent save подтверждён.
Код: DatabaseTableEditingService переносит исходную collation только в character target types; VARCHAR→TIMESTAMP compiler regression добавлен. В SQL modal снято только повторное native confirm footer, само окно с SQL и предупреждением остаётся. Исправление не относится к bypass authorization/preview hash. Общая проверка 29tests/439assertionsPASS (8.935s126MB), PHP lint и git diff --checkPASS. Первый полный запуск выявил ошибочное размещение regression assertions в другом fixture; перенесены в корректный compiler test, итоговый suitePASS.
Уточнение по screenshot пользователя: на исходной вкладке был обычный native confirm, не доказательство зависания сайта. Инструмент failed to accept/observe, поэтому использован fresh tab и проверена фактическая schema до повтора. Пользователю сообщено cancel старое окно, так как draft устарел. Ошибка агента при выборе async last row и её восстановление задокументированы выше, не скрываются. Без DROP/удаления записей; рабочие таблицы/images/секреты не менялись. Test table сохраняется для просмотра. Evidence storage/app/audit/adm-browser-structure-record.jpg, live editor /filament/bread/adm-browser-cycle-items/1/edit оставлен открытым. Commit не создан.
Плановый TEXT переход заменён реальной проверкой VARCHAR→CHAR160 для совместимой смены типа с явным default. Live TEXT/FK/composite/drop и автоматическое назначение admin на новом live BREAD после фикса отдельно не проверялись. Следующее: отдельная тестовая связанная таблица + belongsTo/dropdown, сравнение с Voyager; новый цикл только после согласования следующего шага. Общий database manager PARTIAL, конкретный add/rename/type/default/BREAD/record cycle DONE.

## ADM-AUD-11-DB — IN PROGRESS: live belongsTo/dropdown cycle, 2026-10-12
Пользователь разрешил следующий цикл. Создать отдельный справочник adm_browser_cycle_categories (id,title,timestamps) с generated model/BREAD, две записи. В adm_browser_cycle_items добавить nullable integer category_id и настроить belongsTo через UI BREAD205, target label title; проверить варианты dropdown и сохранение/смену связи record1 с reload. Рабочие tables/images не менять, foreign-key DDL не создавать, удаления не выполнять. Сверить настройки relationship с original Voyager, факт autogrant admin для нового live BREAD проверить по появлению раздела и действию создания.

## ADM-AUD-11-DB — live belongsTo/dropdown cycle DONE, 2026-10-12
Chrome: создана отдельная adm_browser_cycle_categories (id unsigned auto PK,title VARCHAR120 NOT NULL,timestamps) + generated App\Models\AdmBrowserCycleCategory + BREAD «Тестовые категории BREAD», slug adm-browser-cycle-categories. Автоматические admin grants после create подтверждены live: каталог «Открыть», меню, создание двух записей (1 Первая тестовая категория; 2 Вторая тестовая категория), browse/read/edit/delete действия доступны без role editor. Основная тестовая adm_browser_cycle_items получила nullable unsigned int category_id через проверенный ALTER ADD UI.
BREAD205 relationship constructor: belongsTo / table adm_browser_cycle_categories / model App\Models\AdmBrowserCycleCategory / column category_id / key id / label title. Поле relationship «Тестовая категория» сохранено с browse/read/add/edit, requiredfalse. Raw category_id обнаружен full editor и сохранён вместе с новой связью. Runtime dropdown выводит обе title, поиск «Вторая» оставляет второй вариант. Record1: выбрать первую→save→reload; сменить вторую→save→reload; browse показывает category_id2 + title; read показывает title; clear selection→save→reload пусто; восстановить вторую→save→reload. Исходные title/description/quantity7/active/comment сохранены. Никаких FK constraints/pivot/drop/deletion; изменения только в разрешённых test tables/metadata/menu/permissions. Evidence storage/app/audit/adm-browser-belongsto.jpg, editor оставлен открыт.
Original сверка: Chrome GET /admin/bread/gallery_items/edit; read-only existing formfield resources/views/vendor/voyager/formfields/relationship.blade.php подтверждает belongsTo column/key/model/label mapping, AJAX searchable select, optional empty value и label на browse/read. Original Create Relationship DOM содержит четыре типа, скрытое окно просмотрено через DOM без сохранения; visual open не удалось подтвердить, полного visual parity не заявляем.
Найден/исправлен runtime UX defect: после create BREAD URL оставался ?create=, refresh abort409. BreadMetadata::save creation теперь redirect ?section=id после success/cancel; isolated test assertRedirect. Проверки8tests/100assertionsPASS (4.769s100MB): BREAD creation/admin grants/non-admin boundary/rollback + belongsTo invalid target/key validation + relationship constructor. Lint page/newmodel и git diff --checkPASS. Этот новый redirect проверен isolated, отдельный повторный live BREAD после фикса не создавался. Commit не создан.
Новый TODO: generic read показывает пустой Created At, хотя browse содержит дату; проверить temporal formatting в read view (не данные DB). Общий database manager PARTIAL, данный belongsTo/search/save/switch/clear cycleDONE. Осталось: live belongsToMany/pivot/taggable и hasOne/hasMany, read temporal issue, advanced schema constraints/version cases. Следующий точный шаг: исправить/проверить timestamp отображение в read, затем отдельный many-to-many browser cycle на test dataset.

## ADM-AUD-11-DB — IN PROGRESS: timestamp read + many-to-many live cycle, 2026-10-12
Read blank date cause: Eloquent Carbon object failed scalar-only renderer. viewedFields converts DateTimeInterface to date/time/timestamp string, stored DB value untouched. Regression1test/2assertionsPASS. Next Chrome verify read. Then create adm_browser_cycle_links pivot (id + adm_browser_cycle_item_id + adm_browser_cycle_category_id), no model/BREAD needed; stage belongsToMany via BREAD205 to existing test category table. Verify multi-select, save/reload, change subset, clear and restore; no record deletions/working tables/images touched.

## ADM-AUD-11-DB — many-to-many setup and persistence confirmed, 2026-10-12
Chrome создан adm_browser_cycle_links (id intunsignedautoPK, adm_browser_cycle_item_id intunsignedNOTNULL, adm_browser_cycle_category_id intunsignedNOTNULL), без модели/BREAD. BREAD205 constructor staged belongsToMany to adm_browser_cycle_categories via pivot_table adm_browser_cycle_links, keyid,labeltitle,taggable0, подпись «Дополнительные категории». Оригинальный formfield relationship.blade.php lines119+ подтверждает belongsToMany(model,pivot_table,...), multiselect and label display. Пользователь уточнил «как в оригинале» после объяснения pivot, продолжение разрешено. Две категории выбраны, сохранены; после завершённого POST и fresh reload editor обе chips присутствуют. Первый premature reload сразу после submit не показал chips; повторная отправка с закрытым dropdown и ожиданием завершения дала persistence, regression/error пока не утверждается. Далее subset/clear/restore и browse/read. Timestamp live read подтверждает 2026-10-12 12:58:25. Isolated targeted4tests85assertionsPASS (3.997s98MB), lint и diffcheckPASS.

## ADM-AUD-11-DB — timestamp read + many-to-many live cycle DONE, 2026-10-12
Read date fixed: viewedFields converts DateTimeInterface (Eloquent Carbon) to scalar YYYY-MM-DD HH:mm:ss (date/time use appropriate format); no writes to timestamp. Chrome read record1 shows Created At2026-10-12 12:58:25.
Many-to-many runtime completed on adm_browser_cycle_items record1 and test categories1/2 through adm_browser_cycle_links: both selected→save/reload both; removefirst→save/reload onlysecond; removesecond→save/reload empty; restoreboth→save/reload both. Browse and read display comma-separated category labels. Independent belongsTo category_id2 unchanged, old title/description/quantity7/active/comment/date retained; category records never deleted. All writes through Chrome test-only UI. Original custom relationship.blade.php lines119-169 confirms pivot_table + multiple select and optional taggable mode; user explicitly requested match original after explanation. No production schema/data/images changed, no migrations or commit.
Checks: targeted4tests85assertionsPASS (3.997s98MB) covering timestamp render/store preservation, many-to-many sync/validation and metadata constructor; PHP lint and git diff --checkPASS. Evidence storage/app/audit/adm-browser-many-to-many-read.jpg, Chrome left on final read. Named current subcycleDONE; full database manager remainsPARTIAL. Next exact step: live taggable creation from field, then hasOne/hasMany on test dataset; custom pivot key mapping/advanced FK constraints not included in this cycle.

## ADM-AUD-11-DB — контроль перед коммитом, 2026-10-12
Текущие доработки готовы к коммиту: объединённые проверки PASS (40 tests / 638 assertions). Общий этап PARTIAL; TODO: браузерный taggable, hasOne/hasMany и advanced schema cases. Локальные модели браузерного набора не входят в коммит.

## ADM-AUD-11-DB — IN PROGRESS: taggable, 2026-10-12
Проверить на локальном браузерном наборе создание категории из поля belongsToMany с taggable, сохранение и повторную загрузку, совпадение с исходным Voyager. Рабочие таблицы и изображения не изменять.

## ADM-AUD-11-DB — taggable DONE, 2026-10-12
Chrome: taggable включён через constructor BREAD205; новая категория3 создана из поля; до Save основной записи категория существует, связь отсутствует; после выбора/Save/reload связь присутствует. Пустое название блокируется required, лишних записей нет. Browse/read выводят новую категорию. Проверки 4 tests / 95 assertions PASS. Общий этап PARTIAL; далее hasOne/hasMany live, advanced schema cases. Повторно выявлена intermittent MissingAppKeyException на Livewire; после reload повтор успешен, root cause остаётся TODO.

## ADM-AUD-11-DB — IN PROGRESS: hasOne/hasMany live, 2026-10-12
Сверить read-only child relations с original Voyager, настроить две связи на локальном браузерном наборе и проверить browse/read/edit, сохранение без изменения дочерних ключей.

## ADM-AUD-11-DB — hasOne/hasMany live DONE, 2026-10-12
BREAD206 тестовых категорий: hasOne/hasMany на adm_browser_cycle_items по category_id=id. Создана вторая дочерняя запись2 в категории2. Chrome PASS: browse/read/edit, первая против всех записей, EN/RU, пустая категория1, новая форма без ID, сохранение родителя и сохранность ключей дочерних записей. Tests2/26PASS. Общий этап PARTIAL; следующий точный шаг — проверить поддержку нестандартных ключей pivot по оригиналу, затем оставшиеся ограничения структуры; intermittent APP_KEY остаётся TODO.

## ADM-AUD-11-DB — IN PROGRESS: custom pivot keys, 2026-10-12
Найдено отличие от оригинального belongsToMany: игнорируются foreign_pivot_key/related_pivot_key/parent_key/key; значения принудительно int. Исправить единое определение ключей, validation/select/load/save/browse/read/taggable; проверить стандартный и строковый mapping isolated tests. Рабочую БД не менять.

## ADM-AUD-11-DB — custom pivot keys DONE, 2026-10-12
Добавлен BreadPivotKeys: validation и единое разрешение foreign_pivot_key/related_pivot_key/parent_key/key. Runtime select/load/save/browse/read/taggable использует явные ключи и сохраняет строковые значения. Constructor получил три необязательных select, очистка возвращает defaults. Tests6/136PASS, lintPASS. Chrome BREAD205 explicit standard keys сохранены, categories1/2/3 остались выбраны. Нестандартный string mapping проверен isolated SQLite, отдельный live DDL не выполнялся. Общий этап PARTIAL; далее обзор advanced schema ограничений по Voyager и закрытие доказанных расхождений. ENV APP_KEY остаётся TODO.

## ADM-AUD-11-DB — IN PROGRESS: composite column rename, 2026-10-12
Source original DatabaseUpdater переименовывает columns отдельным diff, Vue предупреждает о composites без полноценного index editor. Добавить pure rename composite column для native RENAME COLUMN MySQL8.0.3+/MariaDB10.5.2+, сохраняя index. Drop/index/type/FK/generated restrictions не расширять без отдельной проверки. SQL compiler fixtures без live DDL.

## ADM-AUD-11-DB — composite rename DONE (compiler), 2026-10-12
Разрешён pure rename колонки составного индекса в MySQL8.0.3+/MariaDB10.5.2+ через native RENAME COLUMN. Индекс не удаляется/не перестраивается; совместная смена свойств/index/drop и старые серверы блокируются. Tests30/460PASS, lintPASS. Live MySQL DDL этого сценария не выполнялся. Общий этап PARTIAL: FK/generated/composite editor и произвольные expressions остаются за пределами текущего адаптера. Следующее — проверить и при необходимости обозначить эти ограничения в форме до отправки, сверив исходные предупреждения Voyager. ENV APP_KEY отдельный TODO.

## ADM-AUD-11-DB — IN PROGRESS: предупреждения по структуре, 2026-10-12
Показывать перед формой ограничения только при наличии composite indexes, FK/incoming references или generated columns в открытой таблице. Использовать snapshot mount без дополнительных schema scans. Проверить вывод и неизменность схемы isolated tests; Chrome read-only.

## ADM-AUD-11-DB — предупреждения по структуре DONE, 2026-10-12
Перед формой показаны notices по snapshot: composite index names/columns и native rename границы, FK/incoming constraints, generated column names. Обычные таблицы без блока. Tests3/40PASS, lint/diffPASS. Chrome read-only: adm_browser_cycle_items без notices, translations с composite notice; screenshot проверен. Общий этап PARTIAL; далее общий итоговый обзор database tools и открытых ENV/advanced cases.

## ADM-AUD-11-DB — ENV-ADM-LIVEWIRE-KEY IN PROGRESS, 2026-10-12
Ключ в .env присутствует, config cache отсутствует; изменения app.key в коде не найдены. OSPanel PHP8.3 ZTS, Apache php_module, Laravel Env использует PutenvAdapter. Это гипотеза о конкуренции окружения, не установленная причина. Добавить безопасный exception context только для MissingAppKeyException: состояние config/ENV/SERVER/getenv, выбранного env-файла и наличие ключа после отдельного parsing. Никаких значений ключей/секретов, request body или query, изменения .env, fallback/rotation. Проверить isolated fixtures и сохранить root cause TODO до следующего runtime evidence.

## ADM-AUD-11-DB — ENV diagnostics DONE / root cause TODO, 2026-10-12
MissingAppKeyException получает environment_key_diagnostics в существующем exception context: config/ENV/SERVER/process missing|empty|present, SAPI/ZTS, config cache и read/parse/key-presence выбранного env-файла. Значения секретов, содержимое файла, parser messages и request payload не включаются. Диагностика запускается только при данной ошибке и не загружает переменные повторно, не меняет key/config/.env. Tests3/17PASS, lintPASS, diffcheckPASS. Первый fixture с незакрытой кавычкой был принят установленным dotenv parser; заменён гарантированно невалидным unquoted whitespace fixture, код не менялся. Причина intermittent сбоя не установлена, исправление не заявляется; следующий шаг — при повторении ошибки прочитать новый context и определить источник missing/empty. Общий ADM-AUD-11-DB PARTIAL; browser cycle create/edit/BREAD/record/relations подтверждён, advanced composite rename только compiler. Отдельные mobile SA и ADM-AUD-08 остаются в backlog. Рабочая БД/images/APP_KEY не менялись, новый commit не создан.

## ADM-AUD-11-DB — APP_KEY diagnostics CANCELLED, 2026-10-12
По уточнению пользователя периодический сбой связан с OpenServer; отдельная диагностика приложения отменена. Удалены EnvironmentKeyDiagnostics и его unit tests, Handler возвращён к прежнему exception context. Предыдущие записи об установленной диагностике superseded этой записью. APP_KEY/.env/рабочая БД/images не менялись. Причина OpenServer принята как объяснение пользователя, отдельно не подтверждалась; ENV не блокирует дальнейшие задачи админки. Сохранены contextual structure notices и их tests. Следующее — ADM-AUD-03 mobile SA positioning.

## ADM-AUD-03 — IN PROGRESS: мобильная плавающая кнопка, 2026-10-12
Original сохраняет vertical top в saConversationsFloatingTop; новая кнопка использует тот же key и top70 на всех ширинах, на390px перекрывает breadcrumb. Сохранить drag/click/polling/count, добавить отдельную mobile position и default у нижнего края, чтобы desktop saved top не накладывался на мобильный header. Проверить смену breakpoint, clamp/persistence и браузерный переход в список без отправки сообщений. Chrome viewport override сейчас не меняет фактический innerWidth1711, это ограничение проверки, не подтверждение mobile PASS.

## ADM-AUD-03 — mobile badge implementation DONE / visual mobile TODO, 2026-10-12
Mobile<=640px: default bottom20, отдельный saConversationsFloatingTopMobile; desktop saConversationsFloatingTop и default70 сохранены. Init выбирает позицию после measurement, смена breakpoint восстанавливает соответствующий key, resize clamps. Drag/poll/sound/click handlers сохранены. Node fixture читает фактический Alpine x-data:15assertionsPASS (390x844 defaulttop781; drag381/reload; desktop70/mobile381; resize height300→237; negative/invalid storage; click/sound). Badge permission/polling isolated PHPUnit2tests16assertionsPASS (1.001s80MB), diffPASS.
Chrome desktop PASS: drag70→329, reload329, ordinary link открывает /filament/sa-conversations с heading, unread38; возврат в каталог, drag329→70. Screenshot storage/app/audit/sa-badge-position-desktop.jpg. Original source master.blade.php сверён: vertical drag, clamp, persisted top; old tab после reload не дала видимый widget, runtime original comparison не подтверждён. Chrome viewport capability set390x844 фактически оставляет innerWidth1711; отдельная новая вкладка и keyboard device-mode не дали мобильный viewport. Override reset выполнен. Не считать mobile visual/touch drag проверенными. Browser-only position storage менялось, записи/сообщения/изображения/ключи не менялись. Осталось визуальная сверка390px и touch drag; отдельно ADM-AUD-08 generic functions по плану. Коммит не создан.

## ADM-AUD-03 — mobile layout/browser drag DONE, 2026-10-12
Повторная Chrome проверка успешна: новый agent tab, viewport set ПОСЛЕ goto дал фактические390x844 (ранние попытки override на claimed tabs не меняли ширину). Initial badge top781/bottom824 (20px bottom gap), breadcrumb96..116.79, document overflowfalse. Screenshot визуально подтверждает свободный header. Browser drag top781→743.5 не открыл ссылку, reload восстанавливает743 (целочисленное original storage). Обычный click открывает /filament/sa-conversations, heading SA-диалоги, top743 сохраняется, unread38. Override reset: width1711, desktoptop70 — независимость позиции подтверждена. Evidence storage/app/audit/sa-badge-position-mobile.jpg (после drag/reload).
Source сверён именно в C:/OSPanel/domains/asoft/viar/resources/views/vendor/voyager/master.blade.php: vertical pointer drag/clamp/persist и legacy key сохранены в новом desktop варианте; mobile default намеренно перенесён вниз для устранения перекрытия. Физический touch на телефоне не проверен, browser drag здесь мышью на мобильной ширине. Предыдущий mobile visual TODO закрыт, device-touch остаётся дополнительной проверкой; не заявлять его PASS. Код в этом шаге не менялся, прошлые isolated tests15 JS assertions и PHPUnit2/16PASS сохраняют evidence. Сообщения/записи/БД/images не изменялись, commit не создан. Следующий этап — ADM-AUD-08 generic таблицы по активному backlog.
## ADM-AUD-08 — функции generic-таблиц, 2026-10-12
- DONE: поиск по выбранному доступному физическому полю (содержит/равно), сортировка физических колонок, размер страницы 10/25/50/100. Default search/order из BREAD сохранены; scope/type остаются в запросе. SQL поля из whitelist; password/remember_token исключены из списка/поиска/сортировки. Нет результатов — отдельная строка.
- TODO: отдельная сверка поиска/сортировки отношений, массовых действий и soft deletes; не считать весь ADM-AUD-08 DONE после первого шага.
- Проверки: isolated SQLite PHPUnit4tests43assertionsPASS; Chrome adm-browser-cycle-items: page size10, ID↑ меняет порядок2/1→1/2, quantity equals7 оставляет1 запись. Screenshot storage/app/audit/adm-aud-08-list-controls.jpg. Рабочие записи/images не менялись. Весь ADM-AUD-08 IN PROGRESS; следующий точный шаг — belongsTo поиск/сортировка по label как в оригинале, затем bulk/soft deletes.

### ADM-AUD-08 — дополнительная сверка backlog, 2026-10-12
- DONE: URL bindings поиска/key/filter/order/direction/perPage/lang/type/page + возврат в исходный список из edit. Locked return context строится из известных параметров, без внешнего return URL; list_lang сохраняет язык исходного списка даже после смены языка и reload формы. Regression17tests244assertionsPASS; Chrome type2/equals2/RU/IDdesc/perPage10/page2→edit964→EN/reload→Cancel возвращает исходный список/page2/RU. Screenshot storage/app/audit/adm-aud-08-return-state.jpg.
- DONE: bulk selection только текущей страницы, reset при смене страницы/фильтров/сортировки/языка, explicit confirm/count, delete permission и повторная проверка scope/type всей выборки в transaction. SQLite regression11tests105assertionsPASS; Chrome single/select-all/clear/page-size reset PASS без удаления. Screenshot storage/app/audit/adm-aud-08-bulk-selection.jpg. Следующее — model SoftDeletes browse switch/restore.
- DONE: отдельный belongsTo поиск по label через настроенные column/key, сортировка по label без размножения родительских строк. Только валидные browse relationships, сохранение scope/type и stable pagination. Isolated7tests94assertionsPASS; Chrome gallery type2: label equals Modular canvas →72, raw id_type equals2 →72; unknown label →empty. Screenshot storage/app/audit/adm-aud-08-relation-search.jpg.
- DONE: belongsTo поиск/сортировка по отображаемому названию (выделенный relationship control). Physical foreign-key поиск по ID сохранён. В оригинальной галерее AdminLocaleController ищет raw columns, а generic VoyagerBaseController поддерживает relationship label; не утверждать идентичность этих двух старых сценариев.
- DONE: checkbox selection/select all + bulk delete для текущей страницы. Восстановленный original gallery-items?type=2 и source destroy/bulk-delete сверены; permission/scoped atomic SQL и Spatie media cleanup проверены isolated.
- CONDITIONAL: SoftDeletes — оригинальный Voyager содержит переключатель и restore, но source search старых app/Models и остальных app classes вне controllers, а также новых app/Models не нашёл использования SoftDeletes. Не добавлять deleted_at/менять модели ради функции. Сначала inventory фактических BREAD model classes (включая vendor/custom inheritance); если активных SoftDeletes моделей нет, это не обязательный следующий этап. DB::table/global scopes audit остаётся отдельной проверкой.
- DONE: отдельная /filament/bread/{type}/order и кнопка «Порядок записей». Readonly inventory: 32 generic раздела с допустимой целочисленной колонкой порядка. Drag/стрелки сохраняют позиции1..N через Eloquent; edit permission mount/hydrate/move, scope/type, transaction, Locked snapshot против устаревшей выборки, rollback при veto модели. Первичный ключ/date/text/generated order columns не изменяются; dedicated orders без generic действия. Chrome FAQ: 55 записей/подписи совпадают; slider_photo: одна загруженная картинка96px vs100px original. Regression18tests215assertionsPASS; screenshot storage/app/audit/adm-aud-08-order-faq.jpg. Browser drag/write не выполнялись, сохранение проверено isolated.
- DONE: URL/state — поиск, условие, сортировка, page size, type, locale и page сохраняются в адресе; edit/cancel возвращает исходный контекст. Browser readonly reload/return PASS; isolated Save/return PASS. Browser Save и браузерный back/forward по истории фильтров отдельно не проверялись.
- DONE (основные поля): search whitelist отделён от sortable headers. Legacy default server-side: объявленные BREAD browse/read/edit/add поля + timestamps доступны, hidden/password/model-hidden и необъявленные колонки исключены; users и explicit VoyagerBaseController сохраняют browse-only. Client-style общий поиск учитывает visible ID/numeric/date/text/checkbox/static select labels/validated belongsTo и активный перевод с fallback. Все списки продолжают SQL pagination; копия DataTables/get-all не добавлена. Model global scopes теперь учитываются в browse. Regression21tests237assertionsPASS + null-translation targeted1test8assertionsPASS; Chrome gallery meta_title(canvas72/no-match0), holsts Off3 совпадают с оригиналом. Screenshot storage/app/audit/adm-aud-08-client-search.jpg.
- TODO: сложные display labels общего поиска (many-to-many/children/multiple_checkbox/select_multiple/динамические опции/JSON summary) и сортировка по переведённому/отформатированному значению. Это ещё не полная копия client-side DataTables; old RU «Глянцевый»=0 из-за search cache, new active translation=1 — намеренное улучшение. Scope/type/permission и URL-state сохранены.
- TODO (проверка): roles без add/edit/delete, крайние страницы после фильтра/удаления, совпадение показанных названий/сырых значений checkbox/select/relations и переводов при поиске/сортировке. Предыдущие4теста не покрывают всю эту матрицу.
- Отдельные незакрытые проверки: ADM-AUD-06G advanced custom handlers/maps/media engine и ADM-AUD-11-DB live composite/FK/generated cases; physical touch drag SA остаётся дополнительной device-проверкой. Это не дефекты уже закрытых изолированных сценариев. Отменённые recovery migrations/APP_KEY diagnostics не возвращать в работу.

### ADM-AUD-08 — порядок записей / модельный inventory, 2026-10-12
DONE: readonly inventory135 BREAD definitions,133 model/table pairs доступны. class_uses_recursive не обнаружил SoftDeletes (включая inheritance/vendor traits). Недоступны a_collag_faq/App Models ACollagFaq и canvas/App Models Canva; вывод не распространяется на эти неизвестные модели. Для текущих доступных моделей show/restore не требуется. Numeric ordering активна в32 generic разделах, все asc; custom label view отсутствуют. Desc сохраняет исходную Voyager нумерацию1..N, следовательно reload при desc отображает обратный номерной порядок; отдельный тест подтверждает эту исходную семантику.
Original order page не применяет metadata scope/type; новая сохраняет эти ограничения и обычные global scopes модели. При перемещении меняются order values/timestamps, image paths/files не затрагиваются. Рабочая БД read-only. Следующий точный шаг: сверить searchable fields/server_side режимы generic контроллеров оригинала и текущих списков, затем role/крайние страницы/переводы. ADM-AUD-08 IN PROGRESS, commit не создан.

## ADM-AUD-08 — видимость массовых действий DONE, 2026-10-12
По замечанию пользователя панель «Выбрано / Удалить выбранные / Снять выбор» отображается только при selectedRecords != []. Ошибки selection остаются доступны отдельно. Existing permissions/reset/confirm/delete logic сохранены. Обновлён existing selection regression: initial hidden, select-all visible, clear hidden; PHPUnit1test18assertionsPASS. Chrome gallery-holsts: initial0 buttons, select5 visible, clear0, reload0; screenshot storage/app/audit/adm-aud-08-bulk-hidden.jpg проверен. Первый screenshot был старым paint после async clear; повторный после reload подтверждает скрытую панель. Рабочие записи не удалялись, БД/images не менялись. ADM-AUD-08 в целом IN PROGRESS; следующий шаг остаётся поиск по сложным связям/составным полям и переведённая сортировка. Commit не создан.

### ADM-AUD-08 — поиск по связям IN PROGRESS, 2026-10-12
Предыдущие изменения зафиксированы commit4172835. Следующий шаг: общий client-style поиск по видимым belongsToMany/hasMany/hasOne labels через SQL подзапросы, без загрузки всех записей. hasOne ищет только первый отображаемый child, учитываются child model scopes и custom keys; server-side поведение сохраняется. Проверка на isolated SQLite; сложные JSON/options и translated sort остаются TODO.

### ADM-AUD-08 — поиск по связям DONE (этап), 2026-10-12
Client-style общий поиск теперь учитывает visible belongsToMany, hasMany, hasOne labels. EXISTS/коррелированные подзапросы не размножают родителей; custom parent key поддержан, hasOne использует первый child по PK, child model scopes применяются до поиска. Derived table устраняет конфликт имён self relations. Server-side поведение и выбор физического searchKey сохранены.
Проверки: regression11tests123assertionsPASS, после добавления self cases targeted1test14assertionsPASS; только isolated SQLite. Рабочая БД/images не менялись. Original browse.blade.php DataTables searches rendered cells (action column excluded); текущая реализация ищет отдельные labels, фразы через разделитель нескольких labels ещё не поддерживаются. Browser runtime нового шага не проверен. ADM-AUD-08 в целом IN PROGRESS.
Далее: multiple_checkbox/select_multiple/static options (JSON list/object), динамические опции и JSON summary, translated/formatted sort, role/крайние страницы. Новый этап ещё не закоммичен; предыдущий commit4172835 создан.

### ADM-AUD-08 — статические multiple options IN PROGRESS, 2026-10-12
Сверить original select_multiple/multiple_checkbox browse captions. Добавить общий client-style поиск по подписям выбранных статических options, JSON list/object, без LIKE по сырому JSON и без полного сканирования в PHP. Учитывать активный перевод/fallback, скрытые поля, malformed JSON и numeric/string keys. Тестировать isolated SQLite; MySQL SQL-ветку проверить отдельно локально без изменения данных. Dynamic relationship options/JSON summary остаются отдельными TODO.

### ADM-AUD-08 — статические multiple options DONE (этап), 2026-10-12
Общий client-style поиск multiple_checkbox/select_multiple по подписям выбранных объявленных статических options. JSON list/object читаются по root values, имена object keys и nested arrays не дают ложных совпадений. Числовые/string keys, quoted keys и active translation с fallback поддержаны. WHERE SQL сохраняет scope/type/pagination; raw field/server-side поиск сохранён. Unknown legacy values, фразы через разделители labels, dynamic relationship options и JSON summary остаются TODO.
Найденный дефект: select_multiple с scalar JSON ломал browse array_map. DONE: неверная структура теперь показывает пустую подпись; данные не перезаписываются.
Проверки: isolated SQLite15tests233assertionsPASS (10.948s98MB), local MariaDB10.6.9 SELECT-only fixture13casesPASS. Реальные таблицы/записи/images не менялись. MySQL/MariaDB ветка использует JSON_TABLE (MySQL8+/MariaDB10.6+); конкретно MySQL8 не запускался, подтверждён текущий MariaDB runtime. Fixture tests/Fixtures/bread-multiple-options-mysql.php воспроизводит SQL без DDL/DML. diff checkPASS. Browser UI этого этапа не проверялся.
Следующий точный шаг: dynamic select options и JSON summaries с сохранением SQL pagination; затем translated/formatted sort и крайние страницы/roles. ADM-AUD-08 IN PROGRESS; новые изменения не закоммичены.

### ADM-AUD-08 — динамические options IN PROGRESS, 2026-10-12
Сначала реализовать поиск по captions из validated relation catalog без per-record List: select_dropdown и select_multiple (JSON values либо editablePivotFields object keys). Использовать существующие adapters options(), scopes и metadata validation, не сканировать parent records. Произвольные record-dependent List нельзя заменять options пустой модели; отдельная задача для exact bounded evaluation. JSON summary остаётся следующей подзадачей, не считать её реализованной.

### ADM-AUD-08 — динамический каталог без List DONE (этап), 2026-10-12
Client-style общий поиск select_dropdown.relationship captions получает options через существующий BreadDropdownOptions: validated BelongsTo/key/label/where/custom options/related global scopes. select_multiple.relationship без List использует BreadSelectRelation options, scalar JSON values; editablePivotFields ищет root object keys (дополнительные pivot attribute values исключены). Parent records не сканируются в PHP; SQL pagination сохранена. Related option catalog загружается adapter options(), как при показе captions.
Original formfields/select_dropdown.blade.php и select_multiple.blade.php сверены: List предпочитается общему related catalog, dropdown where применяется без List. Original browse dynamic presentation отличается; не считать новую label-search ветку доказанной runtime копией старой. Это поддержка подписей текущего generic browse на основе legacy catalog contracts.
Проверки: isolated7tests139assertionsPASS (7.261s98MB), после record-dependent List guard targeted1test11assertionsPASS (1.170s76MB); local MariaDB10.6.9 SELECT-only13value+4pivotcasesPASS. Проверены where/soft delete, JSON values vs pivot keys, атрибуты не searchable, invalid label rejected; List требует persisted record и не вызывается на пустом model. Рабочая БД/images без изменений, browser не проверялся. diffPASS.
TODO: exact поиск record-dependent List и unknown raw fallback captions; validated relation methods с record-dependent related model требуют отдельной поддержки. JSON summary не реализован в этом шаге, далее отдельная подзадача: adv_json/adv_fields_group/adv_page_layout и translated/formatted sorting. ADM-AUD-08 IN PROGRESS, commit не создан.

### ADM-AUD-08 — original advanced browse parity IN PROGRESS, 2026-10-12
Сверка monstrex/voyager-extension browse-list: adv_fields_group показывает check/dot для заполненности (fallback metadata.fields), adv_json/adv_page_layout идут через OTHER TYPES как raw JSON. Новые browse summaries отличаются от original. Исправить только browse presentation/search: raw JSON для двух типов, indicators без подписи/value для group. Read/edit handlers сохранить. Новый local metadata inventory advanced types count0 — live browser original compare этих типов невозможен без добавления fixtures; не создавать рабочие записи. Проверить isolated rendered cells/search/translations/malformed fallback. Наличие details.view/browse_inline_editor требует отдельной проверки, не скрывать её отсутствием текущих fixtures.

### ADM-AUD-08 — original advanced browse presentation DONE (по исходникам/isolated), 2026-10-12
Сверены original monstrex/voyager-extension/resources/views/bread/browse-list.blade.php (GROUP/OTHER TYPES) и formfields adv_json/adv_fields_group; extension provider composer для voyager::bread.browse. Новый browse теперь оставляет adv_json/adv_page_layout raw JSON, escaping сохраняет текст HTML внутри JSON. Client-style global search использует raw видимое поле/active translation, malformed JSON также searchable как видимый текст. adv_fields_group показывает только filled/empty check/dot; zero/строка0 считаются empty как PHP empty в original; fallback metadata.fields, unknown fields fallback3dots. Group raw contents/labels убраны из td title и общего поиска. Read/view summaries и edit/storage handlers сохранены.
Это заменяет прежний TODO «поиск JSON summaries»: summary в browse не соответствует original, отдельный новый search summary не нужен. Browser parity не подтверждена: local metadata inventory всех трёх advanced types count0, данные/fixtures в рабочую базу не добавлялись. Нельзя утверждать конкретную active view branch original по этому inventory. Custom details.view и browse_inline_editor остаются TODO: original extension содержит group inline-edit button, текущая generic browse её не реализует. Зафиксировать/проверить эти функции отдельно при наличии metadata, не считать их отсутствующими во всём original.
Проверки: isolated5tests144assertionsPASS (5.130s94MB): raw JSON, malformed text, active translation, group values/labels не видны/searchable, metadata fallback и empty семантика, read/edit roundtrip сохранён. Расширенный regression выполнен, итог в MIGRATION_PROGRESS.md. Рабочие records/schema/images не менялись; commit не создан.
Далее ADM-AUD-08: translated/formatted sorting и acceptance (roles/крайние страницы); record-dependent List/related models и unknown fallback captions отдельно TODO. Весь ADM-AUD-08 IN PROGRESS.

### ADM-AUD-08 — сортировка после переключения языка IN PROGRESS, 2026-10-13
Проверка оригинала обязательна перед реализацией translated order. Chrome gallery-holsts RU после Name ascending даёт IDs4,1,5,2,3 (Cotton/Eco/Gloss/Interior/Synthetic), новая /filament/bread/gallery-holsts?lang=ru&order_by=name даёт тот же порядок. Original DataTables caches базовые values до multilingual init; source server-side orderBy тоже raw physical field. Поэтому translated SQL order не добавлять: это меняет подтверждённое поведение оригинала. Закрыть прежний TODO translated sorting как не требуемый для parity; добавить isolated regression locale/order/page/null/duplicate translation и детерминировать получение последнего перевода по id. Formatted option sorting остаётся отдельным TODO.

### ADM-AUD-08 — сортировка и язык DONE (проверенный сценарий), 2026-10-13
Chrome original gallery-holsts: EN→RU→Name↑ IDs4/1/5/2/3, Name↓3/2/5/1/4. New RU/name↑ и Name↓ дают тот же порядок. Original DataTables init precedes multilingual(), plugin не invalidates order cache; server-side source raw orderBy. Прежний TODO translated sorting заменён parity-проверкой: alphabetic translated ordering НЕ требуется для совпадения с подтверждённым original. Сортировка физических полей сохранена. Не распространять этот вывод на custom browse views и специально изменённые DataTables configs.
Детерминировано browse translation hydration orderBy(id): последний duplicate row побеждает, как уже в search subquery latest-id. Blank/null translation остаётся пустым, missing row сохраняет base. Test 12 records/perPage10 подтверждает asc/desc базовый порядок в EN/RU, page2 сохраняется при locale switch, sort click возвращает page1, translations меняют captions без изменения порядка. Первый тест ожидал reset page при switch; исправлено ожидание, существующее page-preservation не менялось.
Isolated9tests115assertionsPASS (4.781s94MB), diffPASS. Evidence storage/app/audit/adm-aud-08-sort-original-ru.png и adm-aud-08-sort-new-ru.png (RU Name↓, screenshot проверен). Chrome только readonly navigation/lang/sort, records/schema/images не менялись. Commit не создан.
Следующее: ADM-AUD-08 роли без add/edit/delete и крайние страницы после фильтра; formatted options sorting/record-dependent List/custom view/group inline editor остаются TODO. ADM-AUD-08 в целом IN PROGRESS.

### ADM-AUD-08 — права ролей/крайние страницы IN PROGRESS, 2026-10-13
Сверить original @can controls/destroy redirect. Проверить browse-only/read-only roles: отсутствуют mutation controls, прямые Livewire вызовы add/edit/delete/save/bulk запрещены. Edge tests: page2→filter, удаление последней single/bulk записи, veto, пустой список, устаревший page URL. Найдено: single delete не reset page/selection и игнорирует false от модели; original destroy показывает error при failure и redirect index. Исправить подтверждённые дефекты и stale-page recovery, только isolated SQLite writes. Рабочую БД/permissions не менять.

### ADM-AUD-08 — права ролей и крайние страницы DONE (isolated), 2026-10-13
Original source @can add/read/edit/delete в browse и destroy authorize/delete/error/redirect index сверены. Restricted roles проверены без изменения рабочих permissions: browse-only скрывает create/view/edit/delete/select controls; прямые openCreate/openEdit/openView/deleteRecord/togglePageSelection/deleteSelectedRecords/forged save дают403. read_roles добавляет только view, edit route403; role/permission данные неизменны.
Исправлено single delete: false от модели показывает danger, не success; successful delete clears selection/reset page1, как возврат в original index (сохраняются текущие search/type/lang параметры нового списка). Stale page URL/уменьшившаяся выборка clamp на последнюю существующую страницу, empty list на page1; selectedRecords очищается через pagination hook. Это recovery нового SQL-paginated списка, не заявление что original server-side исправлял invalid URL таким же способом. Single delete scope/type/id semantics вне этого изменения сохранены, аналогичны прежнему legacy прямому destroy.
Проверки: targeted4tests45assertionsPASS, regression19tests205assertionsPASS (8.789s98MB): browse/read roles, forbidden calls, page2 last single/bulk removal, filter reset/no match, page999 recovery, entire bulk empty list, veto retains selection/page/error notification, URL context/permissions existing tests. Изолированная SQLite; Chrome ограниченной роли и destructive browser actions не запускались. Рабочая DB/schema/images/permissions не менялись. diffPASS, commit не создан.
Следующее ADM-AUD-08: formatted select/checkbox сортировка по оригиналу, custom view/group inline editor, record-dependent List/related models и unknown captions. Весь ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — сортировка статических select/checkbox DONE (isolated/source), 2026-10-13
Original published Voyager и monstrex browse-list: static select_dropdown/radio показывают options captions (unknown -> empty); checkbox с on/off — captions, без on/off raw value; inline checkbox не имеет текстовой подписи. Client-style сортировка обычных static select/radio и checkbox с on/off теперь по captions через bound SQL CASE ranks. Числовой каталог сортируется численно, одинаковые numeric captions имеют одинаковый rank; текст без учёта регистра. Existing ID-desc tie breaker сохранён, exact legacy tie order не заявлен. Default/active locale сортирует base values до translation hydration, server-side physical order сохранён. Только browse static unknown key отображается пустым, read/dynamic fallback сохранены.
Проверки: SQLite regression20tests240assertionsPASS (10.163s98MB); после locale/custom/invalid metadata additions targeted2tests16assertionsPASS (1.279s78MB). Local MariaDB10.6.9 SELECT-only6sort casesPASS, существующие17search casesPASS; fixture tests/Fixtures/bread-multiple-options-mysql.php. Рабочая БД/schema/images/permissions не менялись. Chrome CDP Page.navigate и focus timeout: live option sorting этого шага не подтверждено. diffPASS. Новые изменения не закоммичены.
Границы: custom details.view, inline checkbox/editor, relationship catalogs пропускают новый sorter. DataTables numeric detection по фактическим cells при смешанном options catalog, formatted currency/date, captions через model casts/accessors и HTML-render parity требуют отдельной сверки. Текущий finite-catalog rank не заявляется полной копией arbitrary DataTables plugins. Unknown legacy search/raw-key совпадения остаются TODO.
Следующий точный шаг: record-dependent List/related model labels и custom browse view/group inline editor; затем браузерная сверка options при доступном Chrome. Весь ADM-AUD-08 остаётся IN PROGRESS.
### ADM-AUD-08 — record List / group inline dialog DONE (isolated/source), 2026-10-13
Сверены original formfields select_dropdown/select_multiple List на dataTypeContent, monstrex browse-list group-inline-edit JS, form-group-ajax и VoyagerExtensionBaseController.recordUpdate. Record-dependent form options уже используют persisted model; дополнительно подтверждены разные author_id List варианты для двух записей. Existing dropdown/select_multiple List/where/global scopes guards прошли. Общий поиск произвольного record-dependent List остаётся TODO; lookup пустой модели не вводился.
Добавлен adv_fields_group browse_inline_editor icon + отдельный Filament modal только text подполей, как original group form. Cancel без записи, Save меняет base column независимо от активного locale (original load_group_form/recordUpdate не читают translations). Existing codec сохраняет number/textarea, custom fields/version и посторонние колонки/images; original JS пересобирал fields из text inputs, потерю остальных данных не копировали. Inline availability учитывает metadata edit/browse, flag presence (даже false, как property_exists original), отсутствие custom view и edit permission.
Повторные server checks на mount/save: browse/edit permissions, metadata/type/physical field whitelist, не в full edit/view; native global scope + validated local scope + filterType; transaction/row lock и model save veto. Metadata edit выключена после открытия — save403. Excluded record вызывает ModelNotFoundException (HTTP404 domain), test catches exception, не рендерит public 404 template, требующий frontend fixtures.
Проверки: targeted5tests138assertionsPASS (5.044s94MB), regression18tests309assertionsPASS (13.063s100MB), после per-record List additions4tests67assertionsPASS (2.548s90MB). Проверены modal text schema, cancel/defaults/base-vs-translation, whitelist/roles, long text rejection, metadata recheck/scopes/type/veto, unrelated image path preservation. Initial tests исправлены: modal partial не входит в assertSee HTML, схема проверяется напрямую; action validation error path должен иметь mounted schema prefix. diffPASS. Read-only inventory advanced types count0; browser runtime/visual group parity в рабочей БД не подтверждены. Рабочие DB/schema/images/permissions не менялись, commit не создан.
Осталось: text/number inline edit, inline checkbox, custom browse views, общий поиск record-dependent List/related model и прочие явно открытые ADM-AUD-08 captions cases. Следующий точный шаг: перенести text/number inline editor по original edit/save/cancel/Enter и recordUpdate, с изолированными tests. Весь ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — text/number и checkbox inline DONE (implementation/isolated), 2026-10-13
Original monstrex browse-list text/number edit/save/cancel/Enter и checkbox tinyToggle immediate POST сверены с VoyagerExtensionBaseController.recordUpdate (authorize edit, base column save). Добавлены Alpine cell editor (pencil/check/cancel, Enter/Escape, busy guard) и compact role=switch immediate checkbox save. Checkbox requires on/off + flag presence, text/number isset(browse_inline_editor), false flag действует как в original. Custom view исключён. Input получает base value snapshot до translation hydration; Save не пишет translations, посторонние поля/images.
Общий inlineRecordContext используется группой и простыми полями: edit/browse metadata, permissions, physical field/type/flag whitelist, native global + local scope + filterType, запрет full-edit/view, повторный check в transaction/lock. Save применяет existing metadata validation/number min/max/step, boolean и required; unique/exists/cross-field compile с base locale и inline prefix. Cross-field context из persisted base attributes, не требует отправки всей формы. Malformed parameters/rules показывают error в ячейке. Model veto не возвращает success; SQL pagination/показ переводов сохранены.
Проверки: isolated6tests101assertionsPASS (3.039s90MB); regression19tests291assertionsPASS (11.822s98MB); после malformed-rule/scalar guard tests6tests111assertionsPASS (3.381s90MB). Проверены base-vs-translation, number min/max/step, different sibling, boolean rejection/on/off, browse-only forbidden, disabled flag/edit/browse/type/custom view, model veto и unaffected image path. Начальная flat-dot validation data пропускала nullable rules; исправлено вложенной data structure и подтверждено invalid inputs. Existing group context/scopes/type/metadata-recheck testsPASS.
Readonly current metadata inventory storage/app/audit/inline-field-inventory.php enabled=0. Browser visual/Enter/cancel/checkbox-click runtime не подтверждён, рабочие metadata fixtures не создавались. Есть проверка bindings/render + server writes, это не browser acceptance. diffPASS. Рабочие DB/schema/images/permissions не менялись, внешних вызовов нет. Изменения без commit.
Осталось: browser inline acceptance при подходящих fixture fields; custom browse views, arbitrary record-dependent List/related model caption search и ранее отмеченные mixed options formatting cases. Следующий точный шаг — custom details.view browse: сравнить original view contracts/реально объявленные metadata и поддержку новой страницы. Весь ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — custom details.view browse DONE (hook/source/isolated), 2026-10-13
Original published Voyager + monstrex browse-list custom include contract сверён: row/dataType/dataTypeContent/content/action=browse/view=browse/options + inherited data. Добавлен BreadBrowseView и customCells, приоритет над standard field rendering. Object row.details/options/dataType.details, actual current-page Eloquent models, ordered LengthAwarePaginator page context; sources загружаются только по IDs текущей страницы. `_browse` truthy accessor заменяет content/data field на clone, как original pre-include override, без изменения DB. Active translation snapshot берётся до стандартных option/coordinates/tree formatters; custom view не получает standard option caption вместо raw content. Repository Blade templates trusted, supported local view names и registered namespaces; excluded Voyager runtime не подключается.
Missing/invalid/unregistered/throwing views показывают «Шаблон колонки недоступен», без raw tooltip/content fallback. Render exception logs context/class без exception message/record contents. Реальный isolated failure обнаружил flush общего Blade component stack при caught nested exception; custom render получает cloned/reset View Factory (__env и facade/container temporarily swapped, restored finally), nested Filament components/throw inside component теперь не ломают surrounding page. Hidden browse rows не рендерятся.
Проверки: first2tests28assertionsPASS, expanded2tests31assertionsPASS, final regression21tests329assertionsPASS (17.925s100MB), diffPASS. Fixtures tests/Fixtures/views/bread-custom-browse.blade.php и bread-custom-broken.blade.php: variable contract, raw-vs-standard option, translated raw, accessor/data consistency, page2 IDs2/1 only, no-match empty, component render/error, missing/invalid/namespace names, hidden row/no raw leak/DB unchanged. Readonly inventory storage/app/audit/custom-browse-view-inventory.php custom_view_count=0; production browser visual custom view не проверен, working fixtures не создавались. Рабочие DB/schema/images/permissions не менялись, commit не создан.
Границы: row/dataType — object metadata adapters, не legacy Voyager Eloquent DataRow/DataType; arbitrary old model methods, inherited loop/controller locals и vendor-specific dependencies ещё не проверены и могут требовать port конкретного шаблона. dataTypeContent только текущая SQL page (client original мог содержать всю коллекцию). Arbitrary custom rendered caption search/sort остаётся TODO; raw/current generic search/sort не превращён в PHP scan/render-all. form/read details.view в этом этапе не переносились.
Следующий точный шаг: inventory реально используемых record-dependent List методов/related models и original caption search semantics; определить нужные missing cases по исходникам, не придумывать общий SQL для произвольного PHP List. Browser acceptance custom/inline остаётся отдельным TODO при доступных fixture fields. Весь ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — используемые List и поиск static captions DONE (inventory/source/isolated), 2026-10-13
SELECT-only inventory: 21 select_dropdown/select_multiple fields, relationship_fields=0, list_methods=0, unknown_models=0. Поиск объявлений List по app обеих версий не выявил методов каталогов; методы интеграций/платежей с суффиксом List не являются fieldList. Нет подтверждённой рабочей настройки per-record List, расширять SQL произвольных PHP методов не требуется. Сохранить существующую поддержку формы и isolated проверки persisted record, не объявлять произвольный List browse-search реализованным.
Подтверждённое отличие static dropdown/radio: original browse выводит caption из options, unknown key пустой; client DataTables ищет rendered text. Исправлен client-style global search static options без relationship/custom view: только matching caption keys, без дополнительного raw key OR. Для переведённых полей SQL IN использует latest active translation (missing fallback base, null/blank empty), совпадающую с текущим browse. Field-specific/server-side raw search сохранён.
Targeted4tests120assertionsPASS, regression20tests352assertionsPASS (13.510s102MB), diffPASS. Новая проверка dropdown/radio labels/HTML tags/raw unknown keys/equals/field-specific/latest+missing+null translation/server-side и unchanged base; существующие List forms/scopes/pivot/search, inline и custom browse проверки прошли. Browser runtime search этого шага не проверен; original source contract сверён. Рабочие DB/schema/images/permissions не менялись; no commit.
Следующий точный шаг ADM-AUD-08: caption search checkbox с неканоническими stored truthy values (original PHP truthiness), затем оставшиеся multi-label phrase/smart-search cases по оригиналу. Произвольный custom render/record-dependent List search и browser event acceptance остаются ограничениями, весь ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — checkbox caption search DONE (source/isolated/SELECT SQL), 2026-10-13
Original monstrex browse-list275-287: PHP truthiness on/off escaped text, inline flags property_exists (даже false), без on/off raw value. Исправлен global client-style labeled checkbox search через BreadCheckboxSearch: PHP scalar falsey null/empty/'0', остальные scalar strings truthy; raw-key OR убран. Подписи checkbox ищутся как escaped text, без strip_tags. Если обе подписи совпали, поле searchable для всех rows; если ни одна — false predicate. Custom/inline флаги исключены из caption branch; field-specific/server-side raw поиск сохранён. Inline raw search не объявляется полностью эквивалентным DataTables input cell — отдельный residual, особенно роль без edit и fallback caption.
Без on/off browse теперь показывает raw scalar как original (раньше generic Да/Нет). MariaDB comparisons используют BINARY CAST, иначе пробел при nonbinary comparison ошибочно равен пустой строке. Такой же byte-exact guard применён в caption sorting. Expression+translation bindings проверены отдельно; arbitrary model accessor/cast boolean transformations не покрыты.
Targeted7tests114assertionsPASS; regression21tests365assertionsPASS (17.287s100MB), diffPASS. Cases0/1/null/2/false/empty/00/space/0.0/-1, on/off/both/neither/escaped HTML label, inline false flag, raw no-label render, explicit/server-side raw search, base unchanged. SELECT-only MariaDB10.6.9:34 checkbox search cases+space sortPASS, existing17multiple+6sort casesPASS. Рабочие DB/schema/images/permissions не менялись. Browser acceptance этого шага не выполнялась; commit не создан.
Следующий точный шаг ADM-AUD-08: original multi-label phrase/smart-search и inline checkbox search branch, затем browser acceptance на существующих полях. Весь ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — smart global search DONE (words/within-caption phrases), 2026-10-13
Chrome original gallery-holsts search 'Off Gloss' находит Gloss (#5), новый до исправления s даёт0. Original monstrex/published client init и bundled public/admin_assets/js/app.js Se() token regex /"[^"]+"|[^ ]+/g + lookahead по каждому слову сверены. Добавлен BreadSmartSearch: words/quoted phrases, deduplicate equivalent tokens, whitespace-only match all. Для client-style global contains SQL AND token groups, внутри OR по существующим allowed physical/related/caption fields. Field-specific, equals и server-side literal contracts сохранены.
Chrome после reload новой страницы: Off Gloss и Off "Gloss Hint" обе админки дают Gloss (#5), 1result; input Livewire update подтверждён. Screenshots storage/app/audit/adm-aud-08-smart-search-new.png и adm-aud-08-smart-search-original.png сохранены и просмотрены. Records/schema/images/permissions не менялись; только navigation/filter. Тест first run ошибочно считал meta_description скрытым при fixture browse=true; fixture явно делает browse=false, production whitelist сохранён. Targeted6tests81assertionsPASS, regression42tests576assertionsPASS (28.849s108MB), diffPASS.
Граница: quoted phrase внутри одного caption/physical column поддержана; quoted phrase через несколько выбранных labels/между колонками пока не конкатенируется SQL и не объявляется полной эквивалентностью DataTables _sFilterRow (join двух пробелов). Wildcard '%'/'_' escaping, HTML entities/HTML stripping нормализация и arbitrary rendered custom captions требуют отдельной сверки. Inline input-only checkbox raw search/permission caption fallback остаётся TODO; рабочий metadata inline inventory ранее0, browser toggle сценарий не создавался.
Следующий точный шаг ADM-AUD-08: сопоставить quoted joined-label phrases и literal SQL wildcard chars с original browser/source, исправить подтверждённые случаи; input-only checkbox отдельно. Весь ADM-AUD-08 IN PROGRESS. Commit не создан.
### ADM-AUD-08 — literal wildcard search DONE (source/Chrome/isolated/SELECT), 2026-10-13
Chrome original gallery-holsts global '%' ->0, new before fix ->5: confirmed raw SQL LIKE wildcard false positive. BreadSmartSearch.containsPattern escapes !/%/_ and query uses explicit ESCAPE '!'; physical/latest translation and validated belongsTo/hasOne/hasMany/belongsToMany label branches covered. Caption catalogs use existing PHP literal matching. Explicit field/server-side raw LIKE semantics intentionally retained for legacy contract.
Targeted4tests62assertionsPASS; full generic/inline/custom browse regression44tests610assertionsPASS (28.665s108MB), diffPASS. Positive/negative %, _, !, backslash, mixed terms, latest/null/missing translation, all4relation branches, hidden fields/scopes existing regression, unchanged base checked. MariaDB10.6.9 SELECT-only10literal casesPASS, existing17multiple+6sort+34checkbox+space sortPASS.
Chrome after new reload '%' ->0; live '_' ->0 in both versions. Screenshot storage/app/audit/adm-aud-08-literal-search-new.png saved and checked after UI settled. Only navigation/search, no working records/schema/images/permissions changes; no commit.
Quoted cross-column probe original '"Gloss  (Shine)"' ->0; this does not justify adding normalized concatenation that changes whitespace from legacy rendering. General quoted multi-label phrase remains open: inspect real metadata/source filter normalization and render separators before implementation. HTML entity/strip normalization and inline input-only checkbox search also TODO. Next exact ADM-AUD-08: real multi-option metadata inventory and original quoted rendered captions/HTML normalization, then checkbox input-cell search. Full ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — real multi-caption search parity DONE (current fields/Chrome), 2026-10-13
По запросу пользователя «сверяй» выполнен SELECT-only inventory всех select_multiple/multiple_checkbox metadata: ровно2реальных поля, оба multiple_checkbox page, browse=true/server_side=false: creeping-line и page-faq. Примеры существующих записей с несколькими captions прочитаны без изменений. В текущей базе select_multiple fields не обнаружены, arbitrary dynamic catalogs не добавлялись.
Chrome old/new Creeping Lines: Главная Канвас -> record4, Канвас Главная -> record4; "Главная, Канвас" ->0 в обеих версиях после settled Livewire update. Old/new FAQ: Главная Канвас -> IDs2/3/1/6 (4results, same order), "Главная, Канвас" ->0 в обеих. Original monstrex multiple_checkbox branch241-250 emits each label with Blade whitespace; DataTables escaped quoted regex searches filter text, not normalized join of displayed caption labels. Нельзя из _sFilterRow join выводить новую функцию нормализованного caption concatenation.
Прежний TODO «добавить joined-label phrase search» пересмотрен: для проверенных действующих fields нормальная joined phrase в кавычках не работает и в original, missing functionality не подтверждена. Отдельное расширение SQL-конкатенации не делать. Произвольные exact-whitespace custom captions/other unconfigured handlers не считаются полностью проверенными; при появлении реального original scenario добавить точный regression.
Evidence storage/app/audit/multiple-caption-inventory.php (ignored SELECT-only), screenshot adm-aud-08-multiple-captions-new.png (FAQ4matches) сохранён/просмотрен. Production code не менялся, тесты повторно не запускались для read-only audit; previous relevant44tests610assertionsPASS сохраняются как предыдущие evidence, не новый запуск. Только navigation/search + docs; рабочие DB/schema/images/permissions не менялись, no commit.
Следующий точный ADM-AUD-08: checkbox input-only search/permission fallback и HTML entity/search normalization по original source, затем финальная сверка оставшихся требований. Full ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — поиск переключателей и текстовых multiple captions DONE (source/isolated/SELECT), 2026-10-13
Сверка original monstrex browse-list275-287 и223-250: inline checkbox с on/off выводится как input без текстового содержимого независимо от edit permissions; multiple_checkbox/select_multiple static captions выводятся escaped Blade, dropdown/radio — raw HTML. Исправлено: global client search не ищет raw values и captions input-only checkbox (presence flags false/null учитываются); без edit rights показан disabled switch и прямой save403. Explicit field/server-side raw contracts сохранены. Multiple captions сохраняют буквальные теги и entity text при отображении и поиске; неизвестные/falsey static captions скрыты как original, dynamic relation catalog не затронут.
Проверки: targeted9tests164assertionsPASS; generic/inline/custom regression46tests650assertionsPASS (35.813s108MB); после дополнительных unknown/falsey cases targeted3tests56assertionsPASS. MariaDB10.6.9 SELECT-only5literal multiple caption casesPASS и предыдущие13root/4pivot/6sort/34checkbox+space/10LIKE PASS. diff checkPASS. Первые проверки выявили fixture id-search collision (ID всегда выводится) и реальную потерю тегов в render; fixture разделён по distinct values, render исправлен. Browser acceptance unconfigured inline metadata не выполнялась (предыдущий inventory0); не объявлять браузерный toggle проверенным. Рабочая БД/schema/images/permissions не менялись; no commit.
Следующий точный ADM-AUD-08: сверить original empty multiple value/«None» и HTML entities raw dropdown/radio; произвольные custom views и record-dependent List search остаются ограничениями. Весь ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — пустые multiple values и HTML entities DONE (source/Chrome/isolated/SELECT), 2026-10-13
Original monstrex223-257 и Voyager generic en/ru: empty multiple values выводят None/Отсутствует; dropdown/radio captions raw HTML, entities декодируются браузером. BreadCaption нормализует static dropdown/radio visible text (strip tags перед entity decode); render и global caption search используют тот же текст, escaped output сохраняет encoded literal angle brackets. Multiple captions остаются буквальным текстом. None добавлен через en/ru admin_bread translations (other UI locales fallback en).
Static multiple browse/search: null/empty/invalid JSON/JSON null/empty arrays → None; empty object → None только multiple_checkbox, select_multiple empty object blank как original PHP object truthiness. Для select_multiple scalar false/0/0.0/empty string/'0' → None, unknown nonempty selections остаются blank. SQL applyEmpty SQLite/MariaDB учитывает repeated translation-style expression bindings; dynamic relationship catalogs не используют empty static caption. Рабочую БД не меняли.
Chrome original FAQ global None → ID10,1result; новая FAQ global Отсутствует → ID10,1result с видимой подписью. Screenshot storage/app/audit/adm-aud-08-empty-caption-new.png сохранён/просмотрен. Creeping Lines существующие4records имеют непустые multiple values. HTML entities source/isolated проверены; synthetic catalogs в рабочую DB не добавлялись.
Targeted2tests34assertionsPASS; full generic/inline/custom48tests688assertionsPASS (31.408s108MB); MariaDB10.6.9 SELECT-only28empty caption cases+repeat bindingsPASS, existing13root/5literal caption/4pivot/6sort/34checkbox+space/10LIKE PASS; diffPASS. Тест RU первоначально использовал EN Livewire snapshot, fixture пересоздаёт компонент при смене UI locale — production не менялся для этого. No commit.
Следующий точный ADM-AUD-08: финальная сверка оставшихся browse/search требований и custom view/record-dependent List ограничений по оригиналу; произвольные custom render, casts/accessors и DataTables exact-whitespace не объявлять полностью совместимыми без отдельного сценария. Весь ADM-AUD-08 IN PROGRESS.
### ADM-AUD-08 — финальная инвентаризация DONE (audit), этап IN PROGRESS, 2026-10-13
Эта запись фиксирует состояние на момент инвентаризации до последующих исправлений. Актуальные статусы — в разделе «ADM-AUD-08 — текущая сводка после rich text» ниже; закрытые TODO этой исторической таблицы не повторять.

| Пункт | Текущий статус / evidence |
| --- | --- |
| Pagination, headers, scope/type, права, single/bulk delete, edge pages | DONE по предыдущим source/isolated/Chrome проверкам; destructive runtime сейчас не повторялся |
| Static captions, checkbox truthiness/input-only, smart/literal search, empty lists, HTML entities | DONE в проверенной матрице; последний regression48tests688assertionsPASS — предыдущий запуск, не новый |
| Relations search и catalog без List | DONE isolated/SELECT; arbitrary record-dependent query/model не подтверждён |
| Language sorting и joined-caption quoted phrase | Сверены с Chrome original; прежние предложения расширить поведение сняты для проверенных сценариев |
| Custom details.view, inline editing, record-dependent List | Implemented/limited adapter; текущий metadata inventory custom_view_count0, enabled inline0,21dropdown/multiple fields:0relationship/0List. Browser acceptance не закрыта, считать conditional ограничениями, не активными missing fields |
| Text/text_area/number browse preview | TODO подтверждено source: original browse-list294-324 escaped literal text до200chars + ' ...'; new strips tags и обрезает до80. Следующий точный шаг: согласовать preview и проверить длинный/буквальный текст; отдельно сверить search cache по обрезанному тексту |
| Rich text browse preview | TODO source343-345: original сохраняет b/i/u при strip_tags и escaped literal output до200; new strip всех tags/80. Проверить реальные страницы перед исправлением |
| File browse links | TODO source326-339: JSON objects download_link/original_name либо single Download link; new file branch даёт image preview только при image extension, иначе raw value/JSON. Поле browse=file встречается6раз; проверить реальные local links без загрузки/изменения файлов |
| media_picker browse modes | TODO source362-395: show_as_images, список путей,3items,+count, empty label; new always image preview. Browse metadata5fields; сверить details/реальные страницы |
| External image URLs | TODO source357/373/389 принимает FILTER_VALIDATE_URL, new BreadImage.urls исключает '://'. Наличие external values в рабочей DB пока не проверялось; не объявлять их текущей поломкой без примера |
| Custom rendered search/sort, arbitrary accessors/casts, exact whitespace, formatted dates/currency | CONDITIONAL ограничения, current date format browse metadata не найден (единственный format у image newhome-services). Не вводить универсальный PHP scan/render-all без конкретного original сценария |

SELECT-only повторный inventory: browse text581/text_area21/rich_text_box35/number87/checkbox52/select_dropdown20/multiple_checkbox2/relationship23/timestamp75/image99/multiple_images7/media_picker5/file6/adv_media_files15/code_editor3/color4. Special browse options format/url/route/view/relationship/inline: только image.format newhome-services; обычные type=relationship23 не путать с details.relationship у dropdown. Evidence ignored storage/app/audit/generic-final-inventory.php и существующие relationship-list/custom-view/inline inventory scripts, все exit0. Source original только C:/OSPanel/domains/asoft/viar. Production code и рабочая DB/schema/images/permissions в этом audit не менялись; тесты не перезапускались, no commit.
### ADM-AUD-08 — текстовое preview DONE (source/Chrome/isolated), 2026-10-13
Original browse-list294-324/343-345 и multilingual plugin loadLang сверены. Важное уточнение к предыдущему audit: 200-char preview действует для непереводимых полей; multilingual.refresh/loadLang после init заменяет nextAll text полным literal значением выбранного языка, даже EN. Не сокращать translated FAQ до200 только по Blade source.
BreadCaption.preview: непереводимые text/number/text_area escaped literal до200 Unicode characters + ' ...' при превышении; непереводимый rich_text_box strip_tags с b/i/u, затем тот же предел. Для переводимых text/number/text_area/rich_text_box full literal text без strip/truncation как runtime original. Columns передают translated flag по configured multilingual и getTranslatableAttributes. Inline display использует тот же preview, persisted values/edit input state не сокращаются. Source route/url metadata сейчас отсутствуют, новых ссылок не добавляли.
Targeted4tests78assertionsPASS; full generic/inline/custom regression51tests726assertionsPASS (34.071s110MB), diffPASS. Cases199/200/201 Unicode chars, literal tags/entities, number/null, rich b/i/u vs removed tags, inline full source, EN/RU full literal translations и unchanged stored values. Первоначальный translated fixture использовал Page, у которого title не translatable; fixture исправлен на existing BreadListTranslatedFixture, production для этого не менялся.
Chrome original/new FAQ query quoted How is a canvas print made? → ID11/1result в обеих; полный answer >200 и literal <br> совпадают после settled input update. Original first page IDs11... отличается от new default ID-desc, сортировка не менялась в этом шаге. Screenshot storage/app/audit/adm-aud-08-text-preview-new.png сохранён/просмотрен. Рабочие DB/schema/images/permissions не менялись; no commit.
Осталось ADM-AUD-08: file download links и media_picker browse modes; search cache по сокращённому/strip rich text непереводимых полей, arbitrary custom renders/accessors — отдельная source/runtime сверка, raw SQL сейчас не менялся. Следующий точный шаг — реальные file fields и original links без скачивания/записи файлов. Весь этап IN PROGRESS.
### ADM-AUD-08 — file links и media_picker IN PROGRESS, 2026-10-13
SELECT-only inventory подтвердил6browse file fields и5media_picker fields. Все5media_picker без show_as_images: original выводит список путей, новая ошибочно previews. Реальные examples canvas-header our_works100items, collage-header45, oil-header4/2; file examples our-works/a_player и canvas-slider/avif JSON objects. Реализовать file download_link/original_name и single Download; media_picker list/images/3items/more/empty с safe encoded URLs. Проверить isolated fixtures и Chrome read-only существующие страницы, не скачивать и не менять файлы/DB.
### ADM-AUD-08 — длинные заголовки browse IN PROGRESS, 2026-10-13
Chrome oil-header показал перекрытие длинных заголовков соседних колонок: nowrap конфликтует с max-width240px. Добавить перенос заголовков внутри колонки и проверить screenshot; структура/данные не меняются.
### ADM-AUD-08 — file/media browse и длинные заголовки DONE, 2026-10-13
Подзадачи закрыты: named file download links/single Download; media_picker list/show_as_images/3items/more/empty. Все5реальных media fields без show_as_images; image mode проверен isolated. Chrome oil-header подтвердил список3+И еще1 и второй список2. Chrome reviews original/new:4named audio links, одинаковые пути и captions. Canvas-slider generic403 ожидаем: dedicated resource, прав не меняли. Длинные заголовки oil-header теперь переносятся внутри колонок, screenshot проверен.
Regression53tests757assertionsPASS; screenshot storage/app/audit/adm-aud-08-media-picker-new.png. Working DB/files/schema/permissions не менялись. Следующий точный шаг ADM-AUD-08: сверить поиск и сортировку по отображаемым именам file/media с Voyager; отдельно внешние image URLs и search cache обрезанных непереводимых текстов. Общий этап IN PROGRESS.
### ADM-AUD-08 — поиск file/media IN PROGRESS, 2026-10-13
Chrome reviews: original review_audio_6967db4eaa191 →1record3, new→0. Oil-header original oil-header/100.jpg→1, hidden fourth oil-header/500.jpg→0. Добавить client global search по file original_name (не download_link/JSON keys), media first3paths/more/empty; image mode не ищет невидимые paths. Explicit/server raw search сохранить. Сортировка file/media остаётся отдельным следующим шагом.
### ADM-AUD-08 — поиск file/media DONE в проверенных сценариях, 2026-10-13
Chrome old/new reviews review_audio_6967db4eaa191→record3/1result; oil-header visible oil-header/100.jpg→1 и hidden fourth oil-header/500.jpg→0. BreadBrowseFileSearch ищет original_name, scalar Download, первые3media paths, localized empty/more; image mode не ищет invisible paths. JSON keys/download_link не становятся caption search. Explicit/server raw behavior сохранён. Regression54tests777assertionsPASS, final targeted1test20assertionsPASS;14MariaDB SELECT-only fixture cases и real reviews ID3PASS. Сортировка file/media — следующий точный шаг, не закрыта текущим исправлением; общий ADM-AUD-08 IN PROGRESS. Arbitrary cross-caption quoted whitespace, malformed/object media, translated media/custom view и rejected unsafe file links не объявляются полной runtime parity.
### ADM-AUD-08 — сортировка file/media IN PROGRESS, 2026-10-13
Voyager client DataTables сортирует отображаемые captions, new fallback сортирует raw JSON/path. Chrome reviews A Player asc→IDs2,3,4,5. Реальные review download paths и captions совпадают, поэтому общий source gap проверить isolated reversed paths/names. Добавить SQL sorting named links/scalar Download и media first3/list/count/empty, image paths не участвуют. Server-side/custom views сохраняют fallback. Проверить SQLite, MariaDB SELECT-only и Chrome без DDL/DML.
### ADM-AUD-08 — сортировка file/media DONE в проверенной матрице, 2026-10-13
BreadBrowseFileSort: client sort по file original_name/scalar Download и media first3paths/more/empty; show_as_images исключает paths. SQL сортирует до pagination, без PHP scan всей рабочей таблицы. Unicode case-insensitive SQLite comparator connection-local; MariaDB LOWER/utf8mb4_bin. Server-side/custom views сохраняют прежний fallback, tie-break ID-desc сохранён.
Regression55tests787assertionsPASS; final targeted2tests30assertionsPASS. Tracked tests/Fixtures/bread-file-sort-mysql.php:6SELECT-onlyordersPASS, ignored script:2real review ordersPASS;16SELECT-only file/media searchcasesPASS, real search ID3PASS. Chrome reviews asc2/3/4/5 desc5/4/3/2 совпадает; canvas-header2existingrows с одинаковыми видимыми media captions сохраняют2/1. Неравные media captions/image mode проверены isolated/SELECT, реальные metadata image mode отсутствуют.
Ограничения: custom renderers, произвольные casts/translated media, malformed/object JSON, rejected unsafe URLs, exact multi-caption HTML whitespace, numeric-only DataTables type inference и очень длинные concatenated file captions (GROUP_CONCAT server limit) не объявляются универсальной parity. Общий ADM-AUD-08 IN PROGRESS. Следующий точный шаг — инвентаризация внешних URL обычных image/multiple_images и сверка runtime оригинала; затем search cache сокращённых непереводимых текстов.

ADM-AUD-08 — внешние image/multiple_images URL: IN PROGRESS. Voyager browse image124/multiple_images205 подтверждает прямой внешний URL. SELECT-only inventory:125 полей,215 HTTP(S) значений только alt-suggestions.image_path. Добавлена поддержка HTTP/HTTPS без изменения local paths; далее isolated render tests и read-only Chrome проверка доступной страницы.

ADM-AUD-08 — внешние image/multiple_images URL: DONE в проверенной матрице. Single/multiple browse/read HTTP/HTTPS включая endpoint/query/hash, mixed local/remote, unsafe schemes/traversal/malformed JSON и неизменность stored values:2tests12assertionsPASS. Chrome blog-posts page2: первые4превью loaded=true,52x52. Внешние реальные значения только dedicated ALT resource: generic external browser case не заявлен. Общий этап IN PROGRESS. Следующий шаг: search cache сокращённого непереводимого текста в сравнении с Voyager.

ADM-AUD-08 — поиск сокращённых непереводимых text/text_area/number: IN PROGRESS. Chrome canvas-header hidden95x140 original0/new2 подтверждён. Добавлен SQL caption200Unicode+ellipsis для global client search; explicit field/server/translated/custom view исключены. Далее tests, MariaDB SELECT fixtures, Chrome повтор. Непереводимый rich_text_box strip_tags SQL/search остаётся отдельной условной проверкой: real long browse examples не найдены.

ADM-AUD-08 — поиск сокращённых непереводимых text/text_area/number: DONE в проверенной матрице. Chrome canvas-header95x140 old/new0;visible80x120 old/new2. Targeted5tests68assertionsPASS; generic regression48tests660assertionsPASS; MariaDB18SELECT-only casesPASS. SQL до pagination, исходные значения не изменены. Общий ADM-AUD-08 IN PROGRESS. Следующий точный шаг: rich_text_box search — strip_tags с разрешёнными b/i/u и200символов, source/isolated cases; real long untranslated rich browse examples в snapshot не найдены, универсальная parity не заявлена.

ADM-AUD-08 — rich_text_box global search: IN PROGRESS. Source browse-list343-345 strip_tags(b/i/u)→escaped200Unicodepreview. SQL adapter: SQLite connection-local PHPstrip_tags; MariaDB/MySQLREGEXP_REPLACE standard markup with comments/quotedattributes before Unicode truncation, translated/customview/raw explicit excluded. Next isolated+MariaDB equality against PHP previews. Malformed markup regex equivalence remains conditional, no universal PHP scan/renderall.

### ADM-AUD-08 — rich_text_box поиск DONE в проверенной матрице, 2026-10-13
Source original browse-list343-345 подтверждён: strip_tags с b/i/u, escaped literal output, затем200Unicode+ellipsis. SQLite connection-local PHP function и MariaDB REGEXP_REPLACE для обычного HTML применены до pagination; значения/файлы не переписываются. Translated full text, explicit field raw и custom view исключены из adapter.
Targeted2tests33assertionsPASS; generic49tests677assertionsPASS (45.381s110MB); MariaDB18plain+10rich SELECT-only comparisons with PHPpreviewPASS. Rich cases removed tags/attributes/comments, preserved b/i/u attributes/case, quotes containing >, declaration/PHP block, Unicode/truncation/ellipsis, null/empty, literal entities/%/_, translations/full raw and unchanged source. Malformed HTML не объявляется regex/PHP-equivalent; runtime MySQL8 отдельно не проверен, фактическая MariaDB10.6.9 проверена.
Read-only inventory:35browse rich fields,34client,33translated,1untranslated reviews.text;4existing values18–102chars withoutmarkup. Chrome reviews Tundub original/new→ID3/1result, <p>→0both after settled AX; это smoke текущих short plain values, не browser acceptance HTML fixture. Screenshot storage/app/audit/adm-aud-08-rich-text-search-new.png saved/viewed. WorkingDB/images/schema/permissions unchanged, no commit.

### ADM-AUD-08 — текущая сводка после rich text
Эта таблица заменяет устаревшие статусы предыдущей инвентаризации. Заключительная сверка выполнена: ADM-AUD-08 DONE для исходных требований и проверенных стандартных сценариев. Условные ограничения ниже остаются незакрытыми дополнительными сценариями, а не подтверждёнными дефектами текущих настроек.

| Группа | Актуальный статус |
| --- | --- |
| Поиск полей/операторов, smart/literal search, scope/type, права, pagination/edge pages, single/bulk actions | DONE в зафиксированной ранее матрице |
| Static captions/checkbox/multiple/relations и сортировка/переводы | DONE в зафиксированной ранее матрице |
| Text/text_area/number/rich_text_box previews и global search | DONE в проверенной матрице, включая Unicode200/ellipsis и full translated text |
| File/media_picker browse/search/sort | DONE в проверенной матрице; SELECT и Chrome evidence выше |
| External image/multiple_images HTTP(S) | DONE source/isolated; реальные внешние values только dedicated ALT resource |
| Произвольные custom views/accessors/casts, record-dependent List, inline custom handlers | CONDITIONAL: hooks/isolated cases есть; inventory customview0, enabledinline0, dynamicdropdownList0. Реального browser acceptance нет |
| Нестандартные данные: malformed rich HTML/JSON, rejected unsafe file/image links, translated media, очень длинные group captions | CONDITIONAL: для отдельных случаев различия/границы документированы; универсальная совместимость не заявлена |
| DataTables exact multi-caption whitespace, numeric-only type inference, date/currency formatting | CONDITIONAL: требуют конкретного original сценария; current date-format metadata отсутствует |

Следующий точный шаг: заключительная сверка source/backlog ADM-AUD-08 и список применимых оставшихся сценариев. Не превращать три conditional группы в повторную проверку всех уже закрытых полей; новые задачи добавлять только по подтверждённому использованию в оригинале. Отдельные ADM-AUD-06G advanced/ADM-AUD-11-DB composite-FK/device touch проверки остаются в своих этапах, не включать их в число новых дефектов ADM-AUD-08.

ADM-AUD-08 — заключительная сверка: IN PROGRESS. Проверяется исходный scope из docs/filament_functionality_audit_2026-10-08.md: поиск field/operator, page size, sorting, permission-aware bulk. Повторный inventory обычных browse options: только image.format newhome-services; произвольные date/customview/inline настройки сейчас отсутствуют. Далее повтор inventories List/custom/inline и единый regression generic/inline/custom/image, после него статус и отдельные conditional limits.

### ADM-AUD-08 — заключительная сверка DONE, 2026-10-13
Исходный scope из docs/filament_functionality_audit_2026-10-08.md, пункт ADM-AUD-08: поиск по выбранному полю и contains/=, выбор количества записей, сортировка, разрешённые массовые действия. Реализован и проверен. Смежные browse captions/search/sort image/file/media/rich/static/relations проверены в описанной матрице; универсальной копией любого custom PHP renderer новая панель не объявляется.
Финальный единый regression:57tests813assertionsPASS,53.809s110MB — generic, inline group/text/number/checkbox, custom browse hook и image browse/read. Деструктивные проверки только в изолированной testDB. MariaDB SELECT-only:18plain+10rich caption casesPASS,6file/media ordersPASS. git diff --checkPASS (CRLF warnings only).
Повторный SELECT-only inventory:21dropdown/multiple fields,0details.relationship,0List,0unknown_models;customviews0;enabledinline0. Browse special options: только image.format newhome-services, date/currency/custom route metadata не найдено. Source original monstrex browse-list и текущие whitelist/page/selection/mutation checks перечитаны. Новых подтверждённых пробелов текущего ADM-AUD-08 не найдено. Рабочие DB/schema/permissions/images/files не изменены; no commit.
Границы: произвольные accessor/cast/custom view/List; malformedHTML/JSON/translatedmedia/rejected unsafeURLs/long group captions; exact DataTables whitespace/type inference/formatting — CONDITIONAL/не выполнена универсальная приёмка. Проверять их при появлении конкретного используемого original сценария, не возвращать закрытые TODO. MySQL8 не запускался: runtime MariaDB10.6.9. Исторические IN PROGRESS/TODO выше сохранены как хронология; итог этого раздела имеет приоритет.
Следующее точное действие за пределами закрытого ADM-AUD-08: сверить открытые пункты ADM-AUD-06G advanced custom handlers/maps/media и ADM-AUD-11-DB composite/FK/generated со статусами/evidence в плане; начать с инвентаризации используемых настроек, без изменений рабочей базы и внешних отправок. Production-copy UAT и физический touch остаются отдельной приёмкой.

ADM-AUD-06G/ADM-AUD-11-DB — применимость оставшихся проверок: IN PROGRESS. SELECT-only текущий snapshot: generated0; composite17индексов, BREAD tables gallery_items/image_alt_suggestions/orders/seo_meta_suggestions/site_images; FK10columns, BREAD users. Missing-model metadata canvas/Canva и a_collag_faq/ACollagFaq имеют physical_table=false. Conditional TinyMCE/coordinates/callable-default options не найдены;6customcontrollers — требуется сверка с уже имеющимися dedicated adapters, не считать автоматически unsupported. Следующее: source Original database composite/FK editor и current guard tests, уточнить актуальные PARTIAL без live DDL.

### ADM-AUD-06G / ADM-AUD-11-DB — актуальная применимость и проверка DONE (audit), 2026-10-13
Проверка оставшихся пунктов завершена; общие PARTIAL не переобъявлены DONE. Новых подтверждённых поломок используемых настроек не найдено. Код приложения в этом шаге не менялся.

| Проверка | Фактический результат |
| --- | --- |
| Custom controllers/policies | 6configured controllers: users/roles/locales supported adapter; orders/ALT/SEO dedicated. Unsupported generic controller не найден; policy UserPolicy covered existing isolated tests |
| TinyMCE options, coordinates, callable/object defaults | Metadata не содержит этих условных настроек. Global legacy editor engine отличается от native editor; произвольные plugins/HTML schema всё ещё не объявляются совместимыми |
| Missing model metadata | canvas→Canva и a_collag_faq→ACollagFaq: обе physical tables отсутствуют. Original/new code содержит ACollageFaq→a_collage_faq, не ACollagFaq; автопереименование/создание отсутствующих таблиц не выполнялось |
| Composite indexes | 17indexes in snapshot; BREAD gallery_items/image_alt_suggestions/orders/seo_meta_suggestions/site_images. Original Vue тоже предупреждает о composites и не даёт полноценного отдельного composite editor; original updater использует Doctrine diff. Current native rename/preservation есть, compound attribute/drop changes ограничены |
| Foreign keys | 10FK columns; из BREAD участвует users. Current affected-column/index guards есть, arbitrary FK editing не перенесено полностью |
| Generated columns | 0в текущем snapshot; current editing guard сохраняет неизменные definitions, изменять их запрещает. Новую поддержку не выдумывали |
| Current real schema read-only plan | На всех6BREAD tables composite/FK неизменная definition→hasChanges=false; DDL не исполнялся. Это не проверка live ALTER |

Изолированный regression39tests613assertionsPASS (25.470s138MB, PHPCLI memory_limit512M): database create/edit/drop/model/constraints, controller/policy, rich draft/source/pending attachments/legacyHTML, temporal. Первый запуск с128M остановился OOM на Repeater timestamp helper; повтор512McleanPASS, настройки PHP/app не меняли. git diffcheckPASS CRLF-only warnings. Inventory script storage/app/audit/remaining-admin-applicability.php ignored, information_schema и прочие запросы только SELECT.
Текущие ограничения ADM-AUD-06G: full TinyMCE/plugin schema, maps/spatial при появлении metadata, production codecs/disks/deferred/crash recovery. Recovery-E и APP_KEY diagnostics CANCELLED остаются отменёнными. ADM-AUD-11-DB остаётся PARTIAL: arbitrary FK/generated/composite property changes и реальный advanced ALTER на server не подтверждены; ранее выполненные browser create/edit/BREAD/relations cycles не возвращать в TODO.
Следующее точное применимое действие: ADM-AUD-11-DB — разобрать перенос переименования колонки с FK по original Doctrine contract на isolated/compile fixtures. До решения не расширять разрешённый real DDL; никаких действий с working users FK. Отдельную cleanup metadata canvas/a_collag_faq не запускать автоматически: physical source отсутствует.

### ADM-AUD-11-DB — переименование колонки с FK: IN PROGRESS, 2026-10-13
Original DatabaseUpdater применяет renamedColumnsDiff отдельно до основного diff. Добавить native RENAME COLUMN для MySQL 8.0.3+/MariaDB 10.5.2+, сохраняя входящие/исходящие связи и индексы. Изменение свойств, удаление связанных колонок и индексов остаются заблокированными. Проверить compile fixtures: inbound/outbound/self/composite FK, старые версии, SQLite, смешанные небезопасные изменения. Live DDL не выполнять.

### ADM-AUD-11-DB — переименование колонки с FK: DONE (код и изолированная проверка), 2026-10-13
DatabaseTableEditingService разрешает чистое переименование связанных колонок через native RENAME COLUMN в MySQL 8.0.3+/MariaDB 10.5.2+. Смена типа/nullable/default/unsigned/auto_increment и удаление связанных колонок, удаление необходимых индексов по-прежнему запрещены. Native rename сохраняет определения FK/индексов силами сервера; редактор не генерирует DROP/ADD FOREIGN KEY и не отключает FOREIGN_KEY_CHECKS. Общий version gate повторно использован для composite rename. SQLite и старые версии остаются заблокированными.
Original source: C:/OSPanel/domains/asoft/viar/vendor/tcg/voyager/src/Database/DatabaseUpdater.php — renamedColumnsDiff выполняется до основного diff; текущий compile уже обеспечивает такой порядок. Тест подтверждает и rename перед изменением несвязанной колонки.
Evidence: PHPCLI512M, VoyagerBreadTest --filter=test_database: 32tests684assertionsPASS, 14.057s128MB. Новый compile fixture: outgoing/incoming/self/composite FK; современные и граничные версии8.0.3/10.5.2; legacy8.0.2/10.5.1 reject; SQLite reject; no-op; запрет nullable/type/index/drop; неизменные snapshot/indexes/isolated records и восстановление grammar. PDO mock допускает только чтение version, запросов/DDL к MySQL нет. git diff --check PASS, предупреждения только CRLF.
Первый targeted run выявил ошибку тестового сценария: SQLite snapshot определяет BIGINT как INTEGER, поэтому проверка смены на INTEGER была no-op. Тест исправлен на SMALLINT; код защиты дополнительных изменений не ослаблялся.
Справочная проверка: https://dev.mysql.com/doc/refman/8.4/en/alter-table.html — native rename обновляет FK references; https://jira.mariadb.org/browse/MDEV-23950 — различие legacy CHANGE и RENAME COLUMN. Это source/compiler evidence, не live-server ALTER.
Ограничения: рабочая база, картинки и FK users не изменялись. Полный ADM-AUD-11-DB остаётся PARTIAL: произвольное изменение FK/generated/composite свойств и выполнение advanced ALTER на сервере ещё не подтверждены. BREAD metadata/модели при rename структуры автоматически не обновляются, как и ранее.
Следующее точное действие: подготовить сводный список действительно незакрытых пунктов ADM-AUD-06G/ADM-AUD-11-DB с отделением условных неиспользуемых настроек от необходимых проверок. Advanced server DDL/UAT — отдельный запуск на изолированной копии; при текущем запрете реальных тестов его не выполнять.

### ADM-AUD-11-DB — изолированный runtime ALTER: IN PROGRESS, 2026-10-13
Пользователь разрешил проверку выполнения ALTER. Запустить отдельный процесс MariaDB10.6.9 с новым datadir и localhost port, без подключения к рабочей базе. Synthetic schema/data: входящие/исходящие/self/composite FK, сохранение rows/indexes/actions и enforcement после rename, guards и stale fingerprint. После проверки остановить только созданный процесс.

### ADM-AUD-11-DB — реальный ALTER на изолированной MariaDB: DONE, 2026-10-13
Пользователь разрешил runtime-проверку. Отдельный mysqld10.6.9-MariaDB из C:/OSPanel/modules/database/MariaDB-10.6-Win10, новый datadir storage/app/audit/fk-runtime-20261013, bind127.0.0.1 port13317; рабочее подключение не использовано. Fixture перед CREATE проверяет @@datadir через realpath и отказывается работать с другим сервером или существующей fixture schema. Новая синтетическая база adm_fk_runtime_20261013, никаких копий пользовательских данных/изображений.
Через реальный DatabaseTableEditingService::preview/apply выполнены native ALTER: outgoing child parent_id, incoming parent id, self обе стороны FK, composite обе колонки и обе стороны FK. 56checksPASS: rows, column names, retained named indexes/FK/actions, invalid insert SQLSTATE23000, RESTRICT delete, UPDATE CASCADE, DELETE CASCADE, DELETE SET NULL, FK_CHECKS=1, no DROP/ADD FK/index, stale fingerprint rejection, guards type/nullable/index/drop. Итог: storage/app/audit/fk-runtime-20261013-result.txt (ignored); server log: storage/app/audit/fk-runtime-20261013/runtime-error.log.
Ручной сценарий: tests/Fixtures/database-fk-runtime.php. Повторный запуск после упрощения schema guard и переноса CREATE после bootstrap также56PASS. Рабочая конфигурация/.env не менялись. Fixture schema удалена в finally; созданный сервер штатно остановлен mysqladmin на port13317, PID/отсутствие LISTEN проверены. Автоматическая проверка безопасности отклонила recursive cleanup datadir с сообщением blocked by policy; каталог сохранён в ignored storage/app/audit, сервер остановлен. Код приложения в runtime-шаге не менялся, текущий unit baseline32tests684assertionsPASS сохраняется.
Закрыт runtime-gap для чистого FK rename именно MariaDB10.6.9. MySQL runtime, другие версии/engines и произвольная смена свойств FK/generated/composite не объявляются проверенными. Общий ADM-AUD-11-DB остаётся PARTIAL по этим ограничениям. Следующее: сводный список оставшихся применимых ограничений ADM-AUD-06G/ADM-AUD-11-DB; не повторять закрытые browse/BREAD/basic DDL и FK rename сценарии.

### ADM-AUD-03 — мобильная кнопка SA: IN PROGRESS, 2026-10-13
Chrome override фактически390x844 подтверждён. Проверены drag743→321, reload321 и переход к списку SA без изменения диалогов. Далее clamp/resize и original390px; touch ввод недоступен в browser capabilities, проверить actual Alpine handler на изолированных pointerType=touch событиях и не объявлять физический touch проверенным.

### ADM-AUD-03 — мобильная кнопка SA: DONE (Chrome responsive и handlers), physical touch TODO, 2026-10-13
Chrome390x844 реально подтверждён через innerWidth/innerHeight и screenshots (ранее недоступный override теперь сработал). New slider-mod: drag743→321, reload321, отсутствие navigation при drag, bounds64/781 приheight844/height43/bottom824, отдельная desktop/mobile позиция по actual handler tests. Original slider-photo390x844: drag60→321 и reload321, вертикальное перемещение и сохранение аналогичны; old source master.blade.php тоже сверён. Новый нижний default и независимый mobile storage сохраняются. Полноценный ввод пальцем в browser tools отсутствует: capabilities viewport/pageAssets, touch input не предоставлен. Физический touch НЕ объявлен проверенным.
Найдено и исправлено фактическое отличие перехода: original badge открывает /admin/sa-conversations?scope=unread, new раньше открывал все. Теперь SaConversationResource URL передаёт filters[scope][value]=unread (Filament5 Url alias filters). Chrome после клика показывает выбранное Непрочитанные/valueunread. Диалоги/сообщения не открывались для acknowledgement и не отправлялись, working data/images не менялись.
Tests: node tests/Fixtures/sa-inbox-position.cjs PASS — actual Alpine mouse/touch pointer handlers, wrong pointer ignored, mouse/touch reload positions, clamp, resize, pointercancel handler, suppression click после drag, ordinary next click, sound button and touch-action:none. PHPUnit SaInboxTest --filter=badge|revocation|list_filters:4tests38assertionsPASS,1.372s86MB. Новый URL→query→table test подтверждает unread selection и исключение прочитанной записи без изменения unread flags/внешних отправок. Первый тест выявил ошибочный tableFilters query param; заменён на подтверждённый vendor ListRecords Url(as:filters), повторPASS.
Screenshot: storage/app/audit/sa-badge-mobile-390.png (ignored), mobile bottom position на обычной странице каталога. В Chrome менялись только browser position preferences. Оригинальный saved top теперь321 после теста, новыйmobile top781. API viewport.reset вызывался; old viewport вернулся2560x1249, new после reset/reload продолжает390x844, последующие reset/set не изменили new viewport. Снятие override на этой user tab инструментом не подтверждено, дальнейшие повторы остановлены.
Осталось физическое touch gesture на телефоне/Chrome device input и production snapshot audit ADM-AUD-06G. Код drag в этом шаге не менялся: исправлен только переход к unread; новые проверки расширяют existing fixture.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — редактор блога: IN PROGRESS, 2026-10-14
По просьбе пользователя physical SA touch пропущен (DEFERRED), перейти к blog editor original. Source resources/views/vendor/voyager/blog-posts/edit-add.blade.php: custom toolbar formatselect/fontsizeselect/removeformat/bold/italic/strike/forecolor/backcolor/alignleft-center-right-justify/lists/outdent/indent/ltr-rtl/link/image/media/image-row/br/code; setup customimage-row иbr. Presence token media/ltr/rtl не доказывает работающий plugin: базовые imports link/image/code/table/textcolor/lists. Не объявлять generic RichEditor полностью blog-compatible. Первый подэтап: removeformat/justify/br через nativecommands; дальше font size/backcolor/indent/formats/image-row и image attributes, оригинальный browser acceptance. Рабочие records/images не менять.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — blog native tools: DONE (подэтап), общий blog editor IN PROGRESS, 2026-10-14
SA physical touch DEFERRED по просьбе пользователя. Новый ориентир — original blog-posts/edit-add.blade.php, а не общий TinyMCE. Source blog custom toolbar подтверждает fontsizeselect, removeformat, backcolor, alignjustify, outdent/indent, custom image-row и insertBrButton; base imports не доказывают работу media/ltr/rtl plugins. Требуется runtime-сверка этих кнопок перед заявлением полного контракта.
Добавлены blog_posts-only default buttons alignJustify/clearFormatting/breadBreak. Metadata toolbar allowlist принимает alignjustify/removeformat/insertBrButton с native mapping; toolbar:false и explicit custom toolbar по-прежнему управляют составом панели. breadBreak использует существующий TipTap setHardBreak(), новые assets/dependencies не нужны. Остальные BREAD default toolbars не расширены. Source HTML guard/сохранение styles/старых classes не ослаблены.
Tests: isolated VoyagerBreadTest --filter=test_blog_rich|test_rich_editor|test_rich_source|test_rich_legacy_html:5tests95assertionsPASS,3.527s94MB. Новый тест toolbar/config/disabled toolbar, source command, RU сохранение justify/br и неизменный EN. Первые standalone component fixtures не имели Filament schema/livewire container; исправлена только тестовая привязка к действующей isolated form.
Chrome new Blog-Статьи → Создать запись (это форма на browse URL, отдельного /create маршрута нет): draft body p→br→p same paragraph, native justify outputs text-align:justify; bold selected text then clearFormatting удаляет strong, сохраняет текст/br. Одна попытка locator.pressSequentially повторно сфокусировала textbox и вставила текст перед br; после обычного tab.typeText(null) подтверждён корректный <p style=text-align:justify>Первая строка<br>Вторая строка</p>. Сохранить в Chrome не нажимали; записи/real images не менялись. Screenshot storage/app/audit/blog-editor-basic-tools-20261014.png (ignored).
Осталось: style/format selector (не толькоh2/h3), fontsize, полноценная backcolor palette, indent/outdent, image-row с сохранением class и original partial-selection guard, image title/caption/options; media/ltr/rtl проверить в оригинальном runtime. ClearFormatting native UI не объявляется побайтово идентичным TinyMCE removeformat. Точная следующая подзадача в том же ADM-AUD-06G-OPTIONS-EDITORS-MEDIA: font size/backcolor поддержка с roundtrip HTML, затем custom image-row. Полный blog editor не DONE. git diffcheck PASS; commit не создан.

ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — font size/backcolor IN PROGRESS, 2026-10-14. Реализовать совместимый TipTap RichContentPlugin (PHP+JS), selection-aware Actions; font sizes8/10/12/14/18/24/36pt и HEX/RGB background. Визуальный blog roundtrip допускает только точно поддержанные стили, неизвестный HTML остаётся source-only. Tests и Chrome draft без записи в рабочую БД обязательны.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — font size/backcolor: DONE (подэтап), 2026-10-14
Добавлен BreadTextStylePlugin: selection-aware размер шрифта8/10/12/14/18/24/36pt, ColorPicker background HEX, снятие каждого атрибута отдельно. PHP BreadTextStyle и public/js/bread-text-style.js используют один mark с независимыми fontSize/backgroundColor, RGB нормализуется вHEX; серверный и JS serializer ограничивают значения. JS использует window.FilamentRichEditor.tiptap.core (встроенный runtime), отдельные npm/vendor изменения отсутствуют. Source original blog custom toolbar fontsizeselect/backcolor сохранён как ориентир; native интерфейс отличается от TinyMCE.
Default tools добавлены для blog_posts; metadata toolbar mapping fontsizeselect/backcolor работает и в generic полях. Plugin registered на native rich fields для explicit metadata. requiresSource принимает table name: только blog_posts может визуально открыть точно поддержанные spanfont/background и p/h2/h3 text-align. Неизвестные CSS/classes/image-row по-прежнему включают source-mode, чтобы не потерять разметку. Старые HTML/изображения/переводы в workingDB не переписывались.
Tests isolated: VoyagerBreadTest --filter=test_blog_|test_rich_editor|test_rich_source|test_rich_legacy_html:6tests113assertionsPASS,4.913s94MB. Покрыты toolbar mapping, PHP HTML→document→HTML→repeat стабильность, HEX/RGB, font/background/br/justify, invalid CSS/color/source guards, сохранение RU с неизменным EN. Новые PHPfiles lintPASS; git diffcheckPASS.
Chrome blog-posts → Создать запись, несохранённый draft: выбрать18pt через modal сохраняетselection; background#ffcc00 применяется к тому же тексту и сохраняетfont. Снять font→background остаётся; снятьbackground→18pt остаётся; повторное открытие font modal показывает18pt. Runtime HTML spanstylefont-size18pt/background-colorrgb(255,204,0), визуально жёлтый фон и24px. ColorPicker popup перекрыл pointer click Apply и изменил color вместо submit; semantic keyboard Apply Enter подтвердил действие. Пользователь может закрыть palette перед нажатием Apply; полноценная mouse-приёмка этого popup требует отдельной сверки. Первое captureScreenshot timeout; следующий сохранённый screenshot просмотрен: storage/app/audit/blog-editor-font-background-20261014.png. Кнопку общего Save не нажимали, workingDB не меняли.
Осталось в том же ADM-AUD-06G: formats selector, indent/outdent, image-row с original partial-selection guard и сохранением class, image title/caption/options; media/ltr/rtl требуют проверки работающих plugins оригинала. Полный editor всё ещё IN PROGRESS. Следующее точное действие — indent/outdent для paragraphs/lists по original40px и выбор формата, затем image-row. Commit не создан.

ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — formats/indent IN PROGRESS, 2026-10-14. Original blog formatselect + indent/outdent: paragraphs heading1..6/pre, indentation40px; list indentation uses nested list operations. Add native plugin with paragraph/heading margin-left roundtrip; preserve unsupported CSS via source fallback. Validate isolated HTML/translations and Chrome drafts, no working records/images writes.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — formats/indent: DONE (подэтап), 2026-10-14
Добавлен BreadBlockStylePlugin с выбором p/h1..h6/pre и кнопками уменьшить/увеличить отступ. JS заменяет только blog heading schema для H1..H6, сохраняя прежние h2/h3 commands; PHP renderer уже поддерживает H1..H6. BreadBlockStyle хранит padding-left на paragraph/heading/codeBlock; paragraph/pre шаг40px, нижняя граница0, допустимые целые кратные40px до999960. Нестандартные/отрицательные/calc значения сохраняются source-only, не преобразуются. В списках native sinkListItem/liftListItem меняет вложенность. Registered только blog_posts или explicit metadata formatselect/styleselect/indent/outdent. Исправлена duplicate BreadTextStylePlugin registration (plugins() добавляет, а не заменяет); allowlist mapping и source guard расширены только для поддержанных blogtags/styles.
Original runtime Chrome /admin/blog-posts/create: дваIncreaseindent→p padding-left80px,Decrease→40px; menu Paragraph/Heading1..6/Preformatted. Переключение Preformatted сохраняет40px и Increase меняет80px. Изначальный прототип использовал margin-left по предположению; после реальной сверки заменён наpadding-left во всех runtime/guards/tests, queryversionblockJSv2. Никакие реальные записи не сохранялись. Toolbar original не показываетmedia/ltr/rtl, хотя эти строки есть в old customconfig; отсутствие этих кнопок в new не является подтверждённой потерей работающих функций.
New Chrome /filament/bread/blog-posts →Создать запись: Increase0→40→80/Decrease80→40/zero-clampPASS; p→H1→p сохраняет отступ; H1 визуально30px; список2items sink делает вложенныйul, lift возвращает2siblings безCSSindent. Послеcorrectedreload ppadding40→prepadding40→80PASS, содержимое не теряется. Screenshot storage/app/audit/blog-editor-formats-indent-20261014.png сохранён и просмотрен. Формыoriginal/new остаются unsaved draft, рабочиеDB/images не менялись.
Isolated tests VoyagerBreadTest --filter=test_blog_|test_rich_editor|test_rich_source|test_rich_legacy_html:7tests185assertionsPASS,4.387s94MB. HTML roundtrip p/h1..6/pre +padding/font/background/br/justify; повторserialize; nestedul, escapedcode, unsupportedCSS guards, RU сохранение с неизменнымEN, metadata toolbar and3extensionURLs. lint/diffcheckPASS. Общий blog editor IN PROGRESS: image-row с сохранением class, partial-selection guard и свойстваimage TITLE/caption/размеры. Следующее точное действие — перенести image-row из original setup; не менять существующие imagepaths в рабочей базе. Commit не создан.

ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — image-row IN PROGRESS, 2026-10-14. Original setup: guards empty/no-image/partial same image-row; existing rows' images first, then standalone; leftover selection HTML preserved, append p.image-row. Implement native schema node + selection command + PHP roundtrip, keep image attrs/id and reject unsupported row contents in source guard. Synthetic Chrome draft only, no real record/image writes.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — image-row и базовые свойства: DONE (подэтап), 2026-10-14
BreadImageRow PHP node + public/js/bread-image-row.js + plugin добавляют p.image-row с image children; selected slice сериализуется через bundled TipTap DOMSerializer. Original setup source contract сохранён: guards empty/partial same row/no images, images existing rows first then standalone, leftover selected HTML затем объединённый row. Native schema исключает nested image-row. Visual CSS показывает картинки рядом. Source guard разрешает только непустой row с непосредственными img и whitespace, точнымclassimage-row; дополнительныеclasses/links/text/emptyrows остаются source-only для сохранения старогоHTML.
Добавлена action Свойства картинки для выбранногоimage: ALT/TITLE/width/height, размеры nullable numeric/CSSunits с validation; image src показанreadonly и не передаётся в updateAttributes, id тоже не изменяется. Изменения только draft через run-rich-editor-commands и исходное editorSelection. Blog sourceguard понимаетnativeimage width/heightstyles, data-id/loading, остальныеCSS не разрешены. Физическиеfiles и stored imagepaths не переписывались.
Tests isolated: VoyagerBreadTest --filter=test_blog_|test_rich_editor|test_rich_source|test_rich_legacy_html:9tests223assertionsPASS,4.856s94MB. Row HTML→PHPdocument→HTML→repeat, src/title/alt/dimensions, class, surroundingtext, RU translation/unchangedEN; lossyrow guards; action rejects malformedCSS dimension, dispatch содержиттолькоalt/title/width/height, forgedsrc не проходит, SQLrecord unchanged. Node tests/Fixtures/blog-image-row-guards.cjs PASS actual handler empty-selection and partial-row alert messages differ; availability check silent. PHP lint/diffcheckPASS.
Chrome new blog create draft: clipboardHTML с двумя ссылками на существующие local images, выделитьвсё→image-row сохраняетleftovertext, переносит2img в1row. SourceURLs неизменны. Свойстваfirstimage ALTПервая:тест/TITLEНазваниекартинки/width180/height100 применены; secondimage remainsaltВторая/width200,URL тотже. Кнопка Save не нажималась. Послеpaste image-containing contenteditable не считаетсяtextbox инструментом; DOM-grounded locator contenteditable=true aria-labelТекст(EN) применён. Screenshot storage/app/audit/blog-editor-image-row-20261014.png сохранён и просмотрен.
Исправление evidence по замечанию пользователя: первый alert после properties был Сначала выделите блок(и)/картинки, НЕ partial guard (selectioncollapsed). Затем explicitly clickedfirstimg, DOM ProseMirror-selectednode=true, вызван row; пользователь подтвердил точныйalert Выделен только фрагмент <p class=image-row>. Операция отменена. Неизменность1row/2images уже фиксировалась до второй проверки; после повторногоalert CDPreads задержались, последующий live DOM result не заявлен. getJsDialog/dismiss дал race No dialog is showing/undefined; эти предупреждения не считать зависанием приложения. Alert-text verification partial опирается на сообщение пользователя + Node actual-handler test.
Original /admin/blog-posts/create открыт, source setup/UIbuttonimage-row подтверждены. Попытка clipboardfixture→selectall→image-row вызвалаmodal и задержкуCDP, successfuloriginalDOMрезультат в этом подэтапе НЕ подтверждён; original semantics сверены по исходнику, не объявлять побайтовую browserparity. Рабочие DB/images/permissions не менялись, external sends отсутствуют.
Общий blog editor IN PROGRESS: image caption/figure, пропорции размеров, вставка/изменение URL изображения по original диалогу; полный media/plugin contract не заявлен. Следующий точный шаг — original image properties dialog и caption/ratio contract, затем перенос недостающих функций на isolatedfixtures. Коммит не создан.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — диалог изображения: IN PROGRESS, 2026-10-14
Original Chrome /admin/blog-posts/create: Insert/edit image показывает Source, Image description, Image Title, Width/Height, Constrain proportions (по умолчанию включён), Caption. Переносим URL insert/edit, caption figure/figcaption и пропорции на draft без записи рабочей БД. Добавлены PHP/JS schema nodes, action с URL-validation и protection pending attachments, HTML guard. Следующий шаг: isolated roundtrip/action/JS checks и Chrome unsaved draft; статус DONE пока не заявлен.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — URL, Caption, пропорции: DONE (подэтап), 2026-10-14
Original Chrome /admin/blog-posts/create image dialog подтверждён: Source, description/ALT, TITLE, Width/Height, Constrain proportions, Caption. На несохранённом original draft включение Caption создаёт figure.image + img + figcaption[contenteditable=true]; editor-only data-mce/helpers не являются saved HTML.
Новый диалог поддерживает вставку/изменение URL (HTTP(S) и локальный путь /), ALT/TITLE, размеры, включение/снятие подписи. PHP/JS nodes BreadImageFigure/BreadImageCaption сохраняют figure/figcaption; unsupported figure и block-caption остаются source-only. Caption может редактироваться непосредственно и через диалог; при неизменном тексте его inline marks сохраняются. Свойства открываются также из текста подписи, обновляется её картинка. При снятии Caption остаётся img в paragraph. Для картинки из image-row schema выделяет figure отдельно, соседние картинки сохраняются.
Пропорции: изменение одного numeric/px размера рассчитывает второй по прежним размерам; если размеры отсутствуют — по naturalWidth/naturalHeight загруженной картинки. Изменение обоих размеров либо выключенный переключатель оставляет введённые значения. Несопоставимые CSSunits не пересчитываются. URL-change очищает прежний attachment id в draft; смена URL pending upload блокируется сервером с ошибкой поля src по mounted-action state path. Физические файлы не перемещаются/не удаляются.
Найдена реальная несовместимость native Filament LocalFiles paste: все img[src] оборачиваются в p, разрушая figure/image-row и создавая placeholder images. BreadImageRow priority1200/PM paste handler перехватывает только structured URL-only HTML, напрямую parseSlice через текущую schema. File/data-image paste остаётся native upload path. JS asset v5. Объединение figure-картинок в image-row сохраняет текст/marks подписи в paragraph, предотвращая создание пустого img из опустевшего figure; порядок original existing-row-first сохранён. Визуальная подпись ограничена шириной изображения через display table/table-caption CSS.
Chrome new unsaved draft PASS: URL insertion, Caption; 200x100→180x90; Caption off→paragraph img; URL сменён только в draft на другое существующее local image; повторное Caption. После reload/latest assets structured clipboard сохраняет ровно figure с strong-caption и p.image-row с2img без placeholder. Caption для первой картинки ряда даёт отдельный figure, оставляя вторую в row. Из текста Жирная подпись properties распознаёт правильный src; width180→height90, strong сохраняется. После final v5 reload select-all+image-row на figure+existing row даёт ровно2images (existing-row image first), strong-caption paragraph и1row, никаких пустыхimg. Overall Save не нажимался ни в original, ни в new.
Проверки: PHPUnit VoyagerBreadTest filter test_blog_|test_rich_editor|test_rich_source|test_rich_legacy_html —9tests244assertionsPASS,7.232s94MB; isolated SQLite only. Figure roundtrip/repeat, unsupported guards, action invalidCSS/unsafeURL/pendingattachment validation, selection/commands payload, unchanged SQLrecord/translations. Node tests/Fixtures/blog-image-row-guards.cjs PASS: actual commands ratio/unlock/natural fallback, ID preservation/reset, caption insert/wrap/unwrap/marks, empty/partial guards. PHP lint/diffcheckPASS. Initial JS mixed-inline/block schema error corrected by inline caption; final Chrome editor initializes and all listed interactions pass.
Evidence screenshot storage/app/audit/blog-editor-caption-20261014.png сохранён/просмотрен: compact images + captions in unsaved form. Рабочая БД, картинки и permissions не менялись; uploads/external sends не выполнялись. Коммит не создан.
Осталось в общем ADM-AUD-06G редакторе (IN PROGRESS): итоговая совместная сверка link/table/HTML/source, undo/redo и image-row/caption на одном черновике, сравнение только реально доступных original toolbar actions. Следующий точный шаг — сверить original table/link dialogs и проверить их native Filament аналоги; не объявлять весь TinyMCE/plugin contract перенесённым по данному подэтапу.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — ссылки: IN PROGRESS, 2026-10-14
Chrome original blog create Insert link содержит Url/Text to display/Title/Target. Native Filament LinkAction не поддерживает текст и TITLE. Добавлены blog-only BreadLink PHP global title attribute/JS extension/plugin/action, вставка/редактирование/очистка URL, текст и новая вкладка; unsafe schemes validation. Общие generic поля пока используют прежний native LinkAction. Регрессия существующих editor tests9/244PASS. Следующее действие — отдельный roundtrip/action test и Chrome insert/edit/unlink/undo/redo. Таблицы ещё не проверены в этом подэтапе, DONE не заявлен. Рабочая БД и изображения не изменялись.

ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — ссылки: продолжается, 2026-10-14.
Добавлен isolated BlogPost/schema fixture: HTML TITLE и strong-text roundtrip/repeat; action unsafe scheme rejected; payload сохраняет original editorSelection и передаёт URL/text/TITLE/target. Все editor tests10/261PASS,5.002s94MB. Первые ошибки fixture model_name/slug устранены, рабочая БД не использовалась.
Chrome new: диалог содержит Адрес/Текст/TITLE/новая вкладка. Без предварительного текста inserted /blog#section, Проверка ссылки, TITLEПодсказка ссылки,target_blank; DOM это подтвердил. После ArrowLeft внутри ссылки повторное открытие заполнило все4значения правильно. Запущено изменение URL/text, но последующее DOM чтение timeout; текущая вкладка оказалась на /filament (инфопанель). Причина перехода не подтверждена, результат edit/undo/redo НЕ объявлен проверенным. Следующий точный шаг — повторить edit/unlink/undo/redo на новом черновике, сохранить screenshot, затем сверить table UI оригинала. Реальные записи/изображения не сохранялись; коммит отсутствует. Общий подэтап IN PROGRESS.

ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — ссылки: 2026-10-14 IN PROGRESS. Chrome draft показал реальную ошибку: attribute-only edit снял strong, undo вернул strong и исходныеhref/title, redo повторил потерю. Причина: команда после extendMarkRange проверяла исходный state.selection.empty и заменяла весь текст. Исправлено условие: неизменный непустой display text → setLink, replace только при изменении текста/новой вставке. Asset bread-link.js v2; actual JS command fixture blog-link-commands.cjs PASS. Следующий шаг: browser повтор на v2, edittext/unlink, original comparison и screenshot.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — текстовые ссылки/undo: DONE (подэтап), 2026-10-14
Chrome на финальном bread-link.js v2: /blog#old + TITLE + strong → изменение толькоhref/title сохраняетstrong; undo восстанавливаетoldhref/title,strong,redo возвращаетnewhref/title,strong. Cursor внутри существующей ссылки→диалог распознаётtext/title/href; изменениеtext заменяет display text и retainshref/title. Очистка URL убираетa, сохраняеттекст; undo возвращаетa иатрибуты. Original TinyMCE через штатный Source code draft: тотжеstrong-link, attributeedit сохраняетstrong; очисткаURL убираетa, сохраняетstrong. CUA paste в old iframe вставил literal HTML; fixture пересоздан через Sourcecode, не считать literalpaste результатом оригинальногоparser. Overall Save не нажимался.
PHPUnit editor10tests261assertionsPASS,5.237s94MB; Node actual command blog-link-commands.cjs PASS с отдельным guard неизменногоformattedtext. В исходном варианте обнаружена/исправлена потеряmarks приcollapsedselection послеextendMarkRange. Рабочая БД иfiles без изменений.
Общий подэтап редактора IN PROGRESS: ссылки на картинки/смешанное выделение (в original Text to display может скрываться), затем table dialogs и общий HTML/source regression. Следующий точный шаг — проверить original image-link vs new, не заменять выбранную картинку текстом. После этого table UI. Коммит не создан.

ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — image/mixed links IN PROGRESS, 2026-10-14. Original Chrome: выбратьimg→Insertlink показывает толькоURL/TITLE/Target безTexttodisplay; apply оборачиваетimg вa, сохраняяsrc/alt/width/height и соседниеtext/strong. New dialog canEditText=false дляnontextleafselection; JS дополнительно проверяет actualselection и делаетsetLink вместоinsertContent, даже при forgedtext. PHProundtrip image-link+strong, hiddenfield/action payload testPASS1/28; Node actualcommands image/mixed preservationPASS. Следующий шаг: Chrome new assign/edit/unlinkimage иmixedselection, затем original mixedselection правила.

ADM-AUD-06G image/mixed links: Chrome new assignimage href/title и unlink сохраняютimgsrc/alt/180x90, соседнийstrong; диалог повторно заполняется, textfield скрыт. Original selectallmixed→link сохраняетimg/strong/text и оборачиваетвa. Найден native LocalFiles paste split inline paragraph на3p; URL-only handler broadened to all HTMLimages (assetrowv6), data/fileupload остаётсяnative. Tests10/272PASS,Node2fixturesPASS. Следующий шаг final v6 paste/mixedlink screenshot. Общий статус IN PROGRESS.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — ссылки на картинки/смешанный фрагмент: DONE (подэтап), 2026-10-14
Final Chrome new rowJSv6/linkJSv3: clipboard HTML text+img+strong остаётся1p; selectall→link hidesdisplaytext; apply /blog#mixed gives1a containing all text/img/strong, src/alt/180x90 unchanged. Original Sourcecode fixture→selectall→link даёт такуюжеструктуру. Image-only assign/unlink также подтверждены безпотериimg. PHP hiddenfield/action/imageHTML repeatfixture10tests272assertionsPASS (5.909s94MB), JS actualcommands fixturesPASS. Защитаtextreplacement проверяетactualselection, ане толькоclientflag. Screenshot storage/app/audit/blog-editor-image-links-20261014.png saved/viewed послеtimeoutretry. Save не нажимался, DB/files unchanged.
Общий редактор IN PROGRESS: original Texttodisplay hiding дляформатированногоtext-onlyselection требуетдополнительнойсверки; затем table UI/source combined regression. Не объявлять полныйlinkcontract завершённым длявсехHTMLpatterns. Следующий шаг — сверить original pureformattedtext иimplement точноегоусловие, затемтаблицы. Коммит не создан.

ADM-AUD-06G formattedtextlink rule IN PROGRESS2026-10-14. Original bundled app.js module7384 O(content): plain/noanglemarkup or single simplea[href] withplaintext allowsTexttodisplay, otherHTML hides. Existinglink mutation usestext onlywhenprovided, otherwiseattribute-only. RelhelperD addsnoopener on_blank, removesitotherwise, keeps/sortsotherrel. New linkstorage analysesnonemptyselection marks/nontextleaf/parent, hidesformattedfield; collapsedcursor retainsdisplaytext likeoriginalemptygetContent. JS actualcommand also protectsformattedselection. Action relhandling matchedoriginal. Node classificationfixturePASS. NextChromeoriginalboldrange vs newv4 +targetrel; tableUI afterward. NoDBwrites.

ADM-AUD-06G formatted link finalJSv5: Chrome unsaved draft strongtextselectall→dialogdisplaytext hidden, /blog#formatted+newtab→a[target=_blank][rel=noopener] containingstrong, textunchanged. Initialv4 storageclosure editorundefined corrected: expliciteditorargument, finaldialogopensPASS. Screenshot storage/app/audit/blog-editor-formatted-link-20261014.png saved/viewed. PHP1test28PASS/lint; priorbroad10/272PASS, NodeclassificationsPASS. Originalruleverifiedbundledapp.jsmodule7384; realDB/files nochange. Formatted-text substageDONE; overalleditorINPROGRESS. Следующий шаг tableUI+sourceguard/roundtrip, sourcelegacy preservation and originalcontextactions, then combinedregression. Не заявлять complete arbitraryTinymce/plugincontract.

ADM-AUD-06G tables IN PROGRESS2026-10-14. Original blogcreate inserted2x2table viaSourcecode: selectcell exposesno tabletoolbar; rightclickAX noTinycontextmenu, so blogtablebuttons are notconfirmedoriginalUI. Pagescreate unexpectedlyhasnorichfield, corrected source via SELECTdata_rows: a-collage-advantage-screen.text actualrichfield; originalcreateopenedtoinspectstandardtoolbar. NativePHP supportsrowspan/colspan/data-colwidth but requiresSourceguard previouslyrejectedthem; validspans/colwidth allowlisted. Legacy table/cellwidth/height previouslyallowedgloballyyetnativePHPdropsdimensions: now keptsource-only. Isolatedroundtripmergedtable/header/colwidth+strongPASS1/17,repeatstable. Fulltableproperties notimplementedyet. Nextoriginalstandardtablemenuinventory,newChrome,row/columnmerge/split andpreservation. NoDBwrites.

Сверкаstandardoriginal: SELECT текущихdata_rows показал pagescreateнетrichtext; a-collage-advantage-screen.text реальноеrichfield. Chromeoriginal/create этогораздела послеинициализации имеетClearformat/Formats/bold/italic/underline/colors/align/lists/indent/link/image/CodeEditor; tablebuttonтакжеотсутствует. Поэтому advancedtableUI не считатьподтверждённымпробеломoriginaltoolbar. Правкиguard дляmergedcells/legacysize ужеPASS в11tests289assertions6.041s96MB. Следующийточныйшаг newdraft/nativecellspan/sourceHTMLcompatibility, затемобщаяфинальнаяпроверкаредактора.

ADM-AUD-06G tables: обнаруженоChrome datacolwidth180,220→nativeJSignoredwidth,minwidth50; PHPnative emitteddata-colwidth, JSparseusescolwidth. BreadTableCell/Header PHPoverride parsestandard+legacydata, rendersstandardcolwidth. BreadTablePlugin alwaysregistered; JStransformPastedHTML convertslegacydata-colwidth withouttouchingcontent. FinalChrome insertedlegacytable nowwidth400 withcols180/220, cellcolwidthstandard, colspan2/rowspan2/strong/surroundingtext preserved. Broadeditor11/289PASS5.872s96MB; expectedextensioncountupdatedto4 foraddedtableplugin. Следующийstepinspectsourcepayload,sourceguardnopreloss; advancedtableUIoriginalnotconfirmed. NoDBwrites.

ADM-AUD-06G tables sourcecheck: ChromeHTMLdialog выявил duplicateclosingtd/th в промежуточном customcellnodes approach (TiptapPHPregistry hadbothnative/customsame-namenodes). Customcellclasses удалены; BreadTableCompatibility uniqueExtension добавляетbreadColwidthglobalattribute, emitsstandardcolwidth alongside nativelegacydata whenpresent, avoidsnodeoverride. Tests addedmatchingopening/closingcounts andHTMLrepeat/guard, final11/291PASS5.767s96MB. Browser reopensHTMLdialogforfinalevidence. Промежуточныеbrokenoutputs не считатьготовымрезультатом. LegacyunsupportedCSS/dimensions remainHTMLmode. NoDBsave.

### ADM-AUD-06G tables — сохранение объединений/колонок: DONE (подэтап), 2026-10-14
FinalChromeHTMLdialog послеnewrequest: корректныеsingleclosingtd/th, standardcolwidth+legacydataколонки180/220, colspan2/rowspan2/strong/surroundingtext. Таблицаvisualwidth400px послеlegacydata-colwidthpaste preserved. Screenshot storage/app/audit/blog-editor-table-20261014.png saved/viewed. FinalPHP11tests291assertionsPASS; roundtrip/repeatbalancedtags, goodspans/data-colwidth/colwidthallowedsourceguard, unsupportedlegacytable/cellwidth/height/CSS staysHTMLmode preservingrawcontent. Original blog andactualgenericrichtext create toolbar do notexposetablebutton (sourceexpecteddefault differsruntimecustomtoolbar); noadvancedtablefeaturegap claimedfromdefaultconfigalone. CurrentFilamenttabletools were alreadyavailable beforethissubstage, notnewVoyagerfunctions.
Overall editor IN PROGRESS: combined existingHTML/source + image/caption/link roundtrip, fileuploadpendingguards and supportedmetadata toolbar finalgate. Advancedtableproperties/CSS notconverted tovisual; rawHTML preservation intentionaluntilactualoriginalUIconfirmed. Следующийшаг итоговаясверкаредакторанаодномчерновике и consolidated regression; затемсверитьследующуюоткрытуюзадачуплана. WorkingDB/files/images untouched; Save notclicked, commitnotcreated.

ADM-AUD-06G finalcombined IN PROGRESS2026-10-14. IsolatedactualBlogPost fixture combinedH1/indent/font/background/justify/linkTITLEtargetrel/imgcaption/image-row/tablewidthspans/strong/br→PHPHTMLrepeat→RU save→reopenRichEditor→HTMLsame, ENunchanged:1test17assertionsPASS. ReopenstateisTipTapJSON (initialassertstringwrong; correctedtoactualHTMLserialization). Combinedpastepath containedURLimages andtabledata-colwidth: customrowhandler bypassedtransformPastedHTML; fixed toapplyregisteredtransformersbeforeparseSlice, assetv7. NextChromecombineddraftwithlegacycolwidth andcaptionslinks→sourcepayload, finalregression. NoactualDB/filewrites.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — итоговая совместная проверка: IN PROGRESS, 2026-10-14
Реализована проверка полного документа BlogPost: H1/отступ/шрифт/фон, выравнивание, ссылка с TITLE/target/rel, inline картинка, figure с подписью, image-row и таблица с ширинами и объединениями. В изолированной SQLite документ сохраняется в RU, затем открывается повторно в RichEditor; результат повторной HTML-сериализации совпадает, EN не изменён.
Исправлено пересечение обработчиков вставки: URL-картинки обрабатываются специальным paste handler, который теперь вызывает зарегистрированные transformPastedHTML перед schema.parseSlice. Это сохраняет нормализацию data-colwidth таблицы в общем документе. JS image-row v8 включает также сохранение marks (в частности link) картинки при добавлении/снятии Caption. Source guard разрешает figure/image-row с простой ссылкой вокруг img; ссылки с посторонним текстом или другой неподдерживаемой разметкой остаются в HTML-режиме.
Проверки: PHPUnit filter test_blog_|test_rich_editor|test_rich_source|test_rich_legacy_html — 12 тестов, 312 assertions, PASS; 6.379s, 94MB. Node blog-image-row-guards.cjs и blog-link-commands.cjs PASS, включая сохранение ссылки картинки при Caption wrap/unwrap. Рабочая БД, права, файлы и картинки не менялись; общий Save в браузере не нажимался.
Браузерная итоговая проверка новой версии пока НЕ выполнена: создание Chrome tab для https://viar13.loc/filament/bread/blog-posts остановилось на ERR_CERT_AUTHORITY_INVALID. Существующие вкладки админок найдены, но новая страница требует разрешения сертификата. Пользователю отправлен запрос открыть эту страницу вручную. Правила computer-use требуют, чтобы предупреждение безопасности проходил пользователь; сертификаты и настройки браузера не менялись.
Следующее точное действие: после подтверждения «открыто» проверить общий документ в Chrome, его ширины колонок, подпись и link при Caption on/off, затем штатный HTML payload. До этого итоговый этап остаётся IN PROGRESS; весь редактор DONE не объявлять. Коммит не создан.

### ADM-AUD-06G-OPTIONS-EDITORS-MEDIA — итоговая совместная проверка: DONE (подэтап), 2026-10-14
После сообщения пользователя о восстановлении доступа Chrome открыл blog-posts без предупреждения сертификата. В несохранённом черновике проверен общий документ: H1, padding-left 40px, 18pt/фон, justify, ссылка TITLE/target/rel с inline img и strong, figure/link/caption, image-row и таблица colspan2/rowspan2 с колонками 180/220 (400px). Все элементы сохранились после вставки через штатный UI.
Свойства связанной картинки: 200x100 -> изменение только ширины на180 -> 180x90; link и форматированная em-подпись сохранились. Caption off оставил img внутри прежней ссылки в p; Caption on с новым текстом вернул figure с прежней ссылкой, ALT/TITLE/размерами. Штатный HTML-код подтвердил сохранение всей структуры и balanced td/th, colwidth/data-colwidth; вспомогательные tableWrapper/ProseMirror отсутствуют в сериализации.
Evidence: storage/app/audit/blog-editor-combined-20261014.png сохранён и просмотрен. Предшествующий финальный PHPUnit: 12 тестов/312 assertions PASS; два Node fixtures PASS. Новых изменений кода после этого прогона не было. Рабочие DB/права/картинки не менялись, общий Save не нажимался; сохранение RU и повторное открытие подтверждены изолированной SQLite, EN не изменён. Сертификатный блокер снят пользователем; настройки сертификатов агент не менял. Коммит не создан.
Закрыта итоговая проверка согласованных функций редактора. Неподдерживаемые legacy CSS/разметка сохраняются через HTML-режим; произвольные TinyMCE plugins и вся миграция не объявляются DONE. Следующее точное действие: сверить оставшиеся открытые задачи общего аудита в MIGRATION_PLAN.md и выбрать следующий подтверждённый оригиналом функциональный пробел.

### ADM-AUD-07 — массовая модерация ALT и откат: IN PROGRESS, 2026-10-14
Актуальная сверка: оригинальный AltSuggestionController::bulk поддерживает approve/reject/regenerate/apply; bulk approve берёт suggested значения, apply вызывает alt:apply только для approved, source=admin, applied-by и limit=count(ids). В оригинальном browse откат отдельный, виден при apply_logs_count>0; controller также умеет виртуальный applied без logs. Revert идёт по журналам в обратном порядке и восстанавливает поля/переводы/Spatie custom properties, затем approved/applied_at=null. Текущий Filament resource имеет только четыре индивидуальных действия, toolbar bulk и revert отсутствуют. Legacy controller в новом репозитории сохранён, но зависит от исключённого Voyager runtime; прямо подключать его не следует.
План: добавить массовые действия с повторной проверкой edit permission, счётчиком результатов и штатной командой применения; затем выделить существующую логику отката в runtime-независимый сервис и подключить индивидуальное действие. Проверки только SQLite/fake queue/storage/Artisan; рабочие картинки и БД не менять, реальные генерации не вызывать. Общий ADM-AUD-07 DONE до проверки bulk и всех применимых revert-веток не объявлять.

### ADM-AUD-07 — массовые действия: DONE (подэтап), 2026-10-14
В ImageAltSuggestionResource добавлены approve/reject/regenerate/apply BulkAction с подтверждением, повторной проверкой edit permission и снятием выбора. Кнопки появляются только после выделения: Chrome13138 selected -> все четыре видны; снятие выбора -> исчезают. Реальные действия не запускались. Approve берёт suggested_alt/title, reviewer/time; regenerate проверяет источник, сбрасывает ошибку и dispatch job; apply использует исходный контракт alt:apply approved/source admin/applied-by/limit. Недопустимый переход состояния пропускается и отражается в счётчике (original model запрещает applied->rejected); модельный контракт не ослаблен.
SQLite/fake Artisan/Bus проверили два предложения, overwrite approved suggested-значениями, точные параметры команды и неполное применение, reject, отсутствие источника, успешную постановку job и отзыв прав перед исполнением. Targeted4tests24assertionsPASS; весь FilamentAuthorizationTest9tests36assertionsPASS (2.090s90MB). Screenshot storage/app/audit/alt-bulk-actions-20261014.png saved/viewed. Рабочая DB/images не изменены, реальные генерации не выполнялись.
ADM-AUD-07 IN PROGRESS: отдельный revert ещё не реализован; следующие проверки — поля, переводы, Spatie custom properties, virtual applied и missing source. Визуально обнаружены broken preview в ALT очереди для site-images/December2025/mz1YaEBDVZldeHC7pmgt.png: TODO в ADM-AUD-07, определить наличие файла и original URL read-only перед исправлением; DB-пути не переписывать. Коммит не создан.

### ADM-AUD-07 — откат и восстановление превью: DONE (подэтап), 2026-10-14
AltSuggestionReverter переносит original revert без зависимости от Voyager controller: обратный порядок apply log, восстановление полей/HTML переводов/Spatie custom properties, null-property removal, статус approved и очистка applied_at/error. Журнал сохраняется, повторный откат допустим как в оригинале. Добавлена транзакция с блокировкой suggestion; отсутствующее поле/источник/media не дают частичного восстановления. Переводы используют существующую translations вместо отсутствующего translateOrNew; EN base не меняется при RU HTML revert. Кнопка видима по наличию apply logs и edit permission, как original browse. Virtual applied without logs поддержан сервисом, но не добавлен в UI оригинала без evidence.
Проверки SQLite: обратный порядок/repeat/null/log preservation; RU vs EN; media restore/remove; missing field/media rollback; virtual/missing source. Отдельный fixture использует настоящую Spatie Media модель и существующие миграции на SQLite: только custom_properties изменились, file_name/disk/size сохранились. Livewire action с mock service проверяет видимость и вызов; отзыв edit permission между mount/submit блокирует выполнение внутри Filament (HTTP200 no-op, service mock вызван лишь один раз), новая форма скрывает кнопку. Финальные два suites:17tests70assertionsPASS (2.934s90MB). Pint новых service/test, diffcheckPASS.
Превью: физический файл найден в обеих локальных версиях. DOM до исправления src пустой: относительный Storage URL ImageColumn проверял как disk path. Model preview_url теперь отдаёт абсолютный public disk URL, внешние/data пути сохраняются. Chrome после reload: src https://viar13.loc/storage/site-images/December2025/mz1YaEBDVZldeHC7pmgt.png, naturalWidth358; screenshot storage/app/audit/alt-preview-restored-20261014.png saved/viewed. DB-пути и физические картинки не менялись.
ADM-AUD-07 bulk/revert scope закрыт кодом и isolated evidence; вся ALT-страница ещё IN PROGRESS по выявленным original UI отличиям: подробности текущих/предложенных ALT и TITLE, фильтры модели/URL/ошибки/WebP, статистика WebP и переход к исходной BREAD сущности. Следующее точное действие — сверить original details drawer и filters, переносить только подтверждённые поля/действия. Реальный apply/revert/generate в рабочей БД не запускался. Коммит не создан.

### ADM-AUD-07 — подробности ALT/TITLE и основные фильтры: DONE (подэтап), 2026-10-14
Original browse details drawer сверён по коду: current/suggested/approved ALT/TITLE, источник type/id/field/locale, page URL, preview. В новой панели добавлена read-only modal «Детали» с этими значениями, ошибкой и компактным preview; Blade экранирует значения, page links только HTTP(S). Добавлены model/URL substring/error yes/no фильтры. Общая таблица получила существующий viar-admin-table marker. UI детализации не требует подтверждения и ничего не записывает.
SQLite Livewire проверяет model filter, URL filter и отсутствие ошибок, видимость record selection; Blade render подтверждает три группы и TITLE. Финальный targeted regression17tests84assertionsPASS. Chrome13138 details открываются с SiteImage#175/img/ee и всеми ALT/TITLE; ложный confirmation subtitle устранён через modal(). Screenshot storage/app/audit/alt-details-20261014.png saved/viewed. Рабочие данные и картинки не менялись.
ADM-AUD-07 остаётся IN PROGRESS по полному паритету страницы: WebP информация/фильтр/статистика, блок/позиция и HTML preview после применения, ссылка на исходный BREAD и сводные status counts ещё не перенесены. Следующий точный шаг — выделить read-only WebP helper по оригинальным normalize/path/filter/stats правилам и подключить его к details/filter. Коммит не создан.

### ADM-AUD-07 — WebP: DONE (подэтап), 2026-10-14
AltWebpInfo выделен из original controller: normalize local/storage/public-storage/HTTP path, убрать query/hash, заменить extension на webp (готовый webp сохраняется), проверять public disk existence. Details показывает Есть/Нет, ожидаемый путь и ссылку при наличии. Фильтр Есть/Нет WebP ограничивает query совпадающими исходными путями; stats считает уникальные image_path после фильтров, а не locale rows. Файлы не создаются/не меняются.
SQLite/fake public storage fixture: JPG/local/query/absolute URL и readyWebP; data/noextension; duplicate paths; exists/missing/empty filters; filtered stats. Regression18tests99assertionsPASS (3.589s92MB), Pint helper/diffcheckPASS. Chrome: исходно2237 unique paths,467WebP,1770missing,20.9%; фильтр Есть после Apply ->467unique/467exists/0missing/100%,3253 locale rows. Screenshot storage/app/audit/alt-webp-filter-20261014.png saved/viewed. Рабочая БД/images не менялись. Проверка public disk существования может требовать времени для большого inventory, как original; remote disks не проверены.
ADM-AUD-07 IN PROGRESS: HTML preview после применения, block/position label, исходный BREAD link и status counts остаются. Следующий точный шаг — сверить original формирование previewTag/blockLabel и mapping source entity, добавить read-only отображение и права перехода. Коммит не создан.

### ADM-AUD-07 — HTML-превью/блок и status chips: DONE (подэтап), 2026-10-14
Original browse lines267-276: context=context subarray иначе prompt_context; blockLabel=section_block/purpose/field/unknown; previewALT/TITLE=approved truthy иначе suggested иначе empty, htmlspecialcharsENT_QUOTESUTF8, tag только alt/title без src. Эти правила перенесены в details Blade с дополнительным escaped выводом code. WebP @php assignment исправлен на block syntax: смешение single-line @php и следующего @php block ранее приводило к неверной компиляции details. Final Livewire/render прошёл.
Статистика статусов как original global groupBy с нулями для всех statuses, независимо от filters. Chips выбирают status с сохранением прочих applied filters, обновляют deferred form и pagination через штатный updatedTableFilters. Всего снимает status. Labels locale/status русские. Original browse НЕ содержит отдельной BREAD edit link — прошлый TODO о такой ссылке отменён как неподтверждённый для этой страницы.
Проверки двух suites18tests105assertionsPASS (3.621s94MB), включая chips filter/reset и details strings. Chrome failed510 ->510results/statusfilterfailed, WebP239unique; Всего ->13131results. Details13138 показывает WebP link, block kampaaniapakkumine, escaped img alt/title и три группы. Screenshot storage/app/audit/alt-html-preview-20261014.png saved/viewed. Рабочие DB/images не менялись, apply/revert/generate не запускались.
ADM-AUD-07 остаётся IN PROGRESS до финальной сверки страницы: полная область поиска (current/title/error), applied approve visibility, даты generated и визуальная сверка с открытым original. Следующий точный шаг — проверить эту матрицу и закрыть только подтверждённый объём. Коммит не создан.

### ADM-AUD-07 — финальная сверка поиска/дат/visibility: DONE (подэтап), 2026-10-14
SearchUsing перенесён по original contains контракты: trim whole search, OR по page_url/image_path/current/suggested/approved alt/title/error; error_message только при наличии колонки. Без Filament word splitting. Original sort updated_at desc/id desc, generated_at Y-m-d H:i или Никогда; approve скрыт для applied. Model filter captions class_basename; locale options supportedLocales из original LocaleResolver, не только существующие строки. Page URL стал clickable HTTP(S).
SQLite regression18tests109assertionsPASS до последней caption/URL/options поправки; title-only original phrase search и applied approve-hidden покрыты. Original Chrome queue открыта и сверена:13131rows, statuses12621applied/510failed совпадают; original WebP759 vs new467 связано с разными локальными public storage и не является доказательством алгоритмической ошибки (проверять файлы отдельно, не копировать автоматически).
Визуальная сверка выявила остающийся реальный пробел: original browse имеет inline textareaALT/inputTITLE в строке и currentALT/TITLE/WebP/source непосредственно в колонках, новая панель пока редактирует через approve modal и часть данных только в Details. Поэтому общий parity страницы НЕ DONE. Следующий точный шаг — перенести editable proposal в строку с сохранением черновиков/валидацией и исходным approve workflow (по аналогии выполненного SEO Meta). Реальная генерация/apply/revert не запускались; рабочие DB/images не менялись. Коммит не создан.

### ADM-AUD-07 — inline ALT/TITLE: DONE (подэтап), 2026-10-14
Добавлены ViewColumn current ALT/TITLE, WebP exists/link/path, source type/id/field и proposal ALT textarea/TITLE input. Draft state altDrafts не записывается при вводе, фильтрации или выделении; initial values original truthy approved->suggested->empty. Подтвердить custom action view вызывает approveDraft с повторной browse/edit permission, актуальной записью, запретом applied и max125/70 validation; сохраняет approved/reviewer/time/status. Исходная modal action остаётся для старых программных вызовов, видимость applied-hidden. Массовое approve по original по-прежнему берёт suggested, не row draft.
SQLite18tests118assertionsPASS (4.170s94MB): draft до submit не меняетDB, success actual edited values, invalid length сохраняет DB, permission revoke blocks direct method. Chrome13138 applied row: inlinefields/current/WebP/source/generated20:05, approve отсутствует; ввод TITLE «Проверка черновика» сохранился при select/unselect без save. Reload вернул исходное значение. Screenshot storage/app/audit/alt-inline-20261014.png saved/viewed; styling correction explicit widths/borders/grid avoids unloaded utility classes. Дополнительные ID/file/updated скрыты по умолчанию, доступны через columns; нужные original колонки оставлены. DB/images unchanged, реальных action submit не было.
ADM-AUD-07 остаётся IN PROGRESS до завершающей матрицы: async regenerate должен обновлять untouched draft, dirty draft сохранять; final original/new визуальная сверка всей строки, browse-only права и проверка WebP различий хранения. Следующий точный шаг — добавить tracking draft source как в SEO и тест refresh без потери ввода, затем итог. Коммит не создан.

### ADM-AUD-07 — draft refresh и итоговая локальная приёмка: DONE, 2026-10-14
Добавлен Locked altDraftSources. При штатном render/refresh нетронутый draft обновляется из approved/suggested; dirty row остаётся целиком, включая TITLE. После approve очищаются draft/source. Original AJAX обновляет статус без polling — новый polling не добавлен. Изолированный Livewire fixture имитирует completion генерации изменением suggestion в SQLite, проверяет untouched refresh и dirty retention. Browse-only user не получает textarea и approveDraft возвращает403; existing hidden action и service permission gates проверены.
Фильтры размещены над таблицей как другие специальные панели, 25records default как original. Preview image link открывает public HTTP(S) URL; данных/файлов не меняет. Final regression18tests122assertionsPASS (4.848s94MB). Chrome подтверждает filters6, supportedLocales order,25rows, current/proposal/source/WebP/generated date и hidden approve applied. Screenshot storage/app/audit/alt-final-20261014.png saved/viewed. Code/source original финальная сверка выше; full page scope bulk/revert/filters/stats/details/inline/search/dates covered. Коммит не создан.
Закрытие ADM-AUD-07 относится к реализации и локальным isolated tests/read-only Chrome. Внешняя OpenAI/queue и реальное применение на production copy не запускались по текущему ограничению; это отдельная отложенная UAT, не объявлять выполненной. Different WebP inventory старого/нового storage (759 vs467) не синхронизирован, физические файлы не копировались. Общая миграция админки остаётся IN PROGRESS. Следующий основной пункт ADM-AUD-09: отдельная медиатека; сначала сверить текущий код, чтобы не повторять уже реализованные library операции.

### ADM-AUD-09 — самостоятельная медиатека: IN PROGRESS, 2026-10-14
Original VoyagerMediaController files/index/new_folder/delete/move read-only сверён: browse_media, root storage directory, не только изображения. Current built-in library requires media_picker editable BREAD field and record; standalone страницы не было. Добавлена MediaLibrary page /filament/media, original menu media mapping -> новая страница. Просмотр folders/files public storage, natural name sort, compact image previews, file MIME/size/open link,48files pagination, root/up navigation и folder URL state. Permission browse_media на mount/hydrate/list/browse/page; path traversal/control/backslash/colon blocked. SVG отображается как документ. Нет новых DB/DDL.
Изолированный fake storage Livewire1test8assertionsPASS: root->photos->root, txt visible/hidden ignored, traversal422, revokeaccess false, filecontent unchanged. Chrome menu Инструменты->Медиа виден, root49files, blog-posts показывает подпапки по месяцам и up/root. Screenshot storage/app/audit/media-standalone-20261014.png saved/viewed. Рабочие DB/files/images untouched, действий записи в браузере не было.
ADM-AUD-09 НЕ DONE: следующий шаг выделить общие library operations из VoyagerBread для standalone контекста с browse_media permission и root='', затем folder create/rename/empty-delete, file rename/move/reference preservation, upload/selection/delete original scenarios. Не считать standalone browse полной заменой original manager. Existing library code не дублировать и не переписывать DB-пути без явного пользовательского действия. Коммит не создан.

### ADM-AUD-09 — общий сервис создания папок: DONE (подэтап), 2026-10-14
BreadLibraryFolders::create извлечён из прежнего VoyagerBread::createLibraryFolder; обе страницы используют один сервис. BREAD сохраняет metadata/root permission gates; standalone проверяет browse_media/current folder и добавляет форму Имя новой папки/Создать папку. Повторная попытка сбрасывает прежнюю validation error. Имена unicode/space/hyphen/underscore max100, Windows reserved names/occupied paths отказ. Root standalone='' поддерживается; nearest existing resolved ancestor проверен внутри public disk перед записью, чтобы ссылка не уводила создание наружу. Отсутствующий base_path встроенного picker допускается как прежде.
Изолированный fake-storage regression обеих страниц2tests41assertionsPASS (3.233s104MB), diffcheckPASS. Проверены root/nested creation, duplicate, traversal и reserved names, retry, metadata prohibition, browse permissions, existing file unchanged. Рабочие storage/DB/images не менялись; реальное создание черезChrome не запускалось. UI новой create-form ещё требует read-only visual check.
ADM-AUD-09 IN PROGRESS: следующим вынести rename/empty-folder-delete в общий сервис с прежним reference preservation, подключить standalone actions и tests; затем file relocation/upload/delete. Прежний native Voyager допускает recursive delete, но согласованный ранее объём удаления папок только empty: его сохранить до отдельного запроса. Коммит не создан.

### ADM-AUD-09 — общий rename/delete folder: DONE (код и isolated проверки подэтапа), 2026-10-14
Переименование и empty-delete извлечены из VoyagerBread в BreadLibraryFolders без дублирования алгоритма. BREAD сохраняет metadata/root gates и обновление draft ссылок. Standalone MediaLibrary подключает rename form, folder action и deleteEmpty с wire:confirm; browse_media проверяется на каждом действии. Root нельзя переименовать/удалить, resolved path внутри public disk проверен. Rename root child корректно строит target без ./prefix. Existing transaction/reference permission и rollback copies сохраняются; исходная папка остаётся для ссылок в rich text/external content как ранее согласовано. Empty delete atomic nonrecursive rmdir; вложенные/скрытые файлы не удаляются.
Fake storage/SQLite5tests95assertionsPASS (6.427s100MB): standalone root rename и source copy retention, empty vs nonempty delete, BREAD nested refs/multipleJSON/unrelatedprefix, forbiddenroot/metadata, reference edit permission rollback. Pint service/page. diffcheck обнаружил только добавленную blankline EOF после extraction; исправлено. Working DB/files/images untouched; browser delete/rename не выполнялись.
ADM-AUD-09 IN PROGRESS: visual read-only check форм создания/rename/delete, затем shared file relocation и upload/delete original scenarios. Не объявлять весь менеджер завершённым. Коммит не создан.

### ADM-AUD-09 — shared file relocation и формы: DONE (подэтап), 2026-10-14
BreadLibraryFiles checkedFile/relocate извлечён из встроенной библиотеки. BREAD сохраняет metadata allow_move/rename, root/MIME guards и обновление draft ссылок; standalone работает с любыми существующими типами файлов, immutable extension и browse_media permission. Новые имя/папка, занятый target/unchangedpath отказ; source/destination resolved внутриpublic storage без symlinkescape. Copy + DBreference transaction/rollback прежние, исходник остаётся для legacy URLs. Root destination='' поддержан. В медиатеке file action/form/Отмена и shared service вызов.
Изолированный combined7tests131assertionsPASS (8.932s104MB): BREAD sharedrefs rollback forbiddensections, folderexistingtests, standalone txt rename/rootmove/sourcepreserved, wrongextension/collision отказ. Pint shared service/page, diffcheckPASS. Chromeblog-posts/April2026 shows4compactimages; file form initialname/path верны, Cancel, up->folderrename form initialApril2026; create/delete buttons visible. Ни Submit/rename/delete/upload не выполнялись. Screenshots storage/app/audit/media-file-form-20261014.png и media-folder-form-20261014.png saved; file-form viewed. WorkingDB/files/images unchanged.
ADM-AUD-09 IN PROGRESS: upload, selection/mass operations и file delete остаются; сверить оригинальный controller/UI (типы/размер/overwrite/thumbnails и confirmation) до реализации, соблюдать пользовательский запрет изменения рабочих картинок при тестировании. Коммит не создан.

### ADM-AUD-09 — загрузка файлов: IN PROGRESS (приём файлов реализован), 2026-10-14
Original VoyagerMediaController upload/config сверены: browse_media, currentfolder, allowed_mimetypes='*' в текущемconfig, collision suffix, imageorientation/reencode/defaultquality90, optional thumbnails/watermark/details filename templates. Добавлен Livewire WithFileUploads приём до20файлов/10MBeach в текущуюpublicfolder с name preservation/collision_suffix, allow_upload/config MIME checks, resolved destination guard и permission; валидируется всяпартиядо writes, failed storage run удаляет созданные этойпартией файлы. Исполняемые расширения запрещены в публичномstorage; это дополнительное ограничение по сравнению с legacy'*', зафиксировано явно. Existingfiles не overwrite.
Isolated fake storage/Livewire1test56assertionsPASS (2.145s80MB) включает standalonebrowse/folder/fileops ранее и новые uploads: collision preserves original+newbytes, rejectedPHP/configMIME/maxsize nofiles. Pint pagePASS. Никаких uploads в рабочей БД/storage/Chrome не было.
Полный upload parity НЕ DONE: JPEG/PNG orientation/reencode quality90 ещё не реализованы; thumbnails/watermark/details/templates применимость нуждается в сверке standalone originalUI. Текущий upload сохраняет исходные bytes. Следующий точный шаг — сверить original media component upload payload details, подключить используемые imageprocessing параметры через существующий engine, проверить orientation/quality и collision series без realfiles. Selection/bulk/filedelete тоже остаются ADM-AUD-09TODO. Коммит не создан.

### ADM-AUD-09 — image upload orientation/reencode: DONE для текущей конфигурации (подэтап), 2026-10-14
Original standalone media/index payload содержит только thumbnails/watermark изconfig, неfilename/quality/resize; в текущемconfig этиoptions закомментированы, empty. Original upload список raster JPEG/PNG/BMP reencode quality90, GIFcopy, WebP/video/docs не обрабатываются. BreadImageUpload::mediaBytes добавлен как processing перед записью standalone: JPEGEXIF orientation, raster render90, GIF/document bytes unchanged. Shared private orient извлечён из прежнего BreadImageUpload::store; повторения алгоритма нет. Filename/collision семантика standalone сохраняется, расширение JPEG имени не переименовывается вjpg. Никаких WebP/staticGIF derivatives standalone автоматически не добавляет, как original upload. InvalidSVG не raster decode -> validationerror, original Intervention/GD также не поддерживает такой decode.
Изолированные syntheticJPEG Orientation6 ->20x40, decodedmimeJPEG, unchangedGIF/document; existing EXIF corners1..8 +invalid9 проверяют shared orient дляBREAD. Combined3tests160assertionsPASS (2.347s86MB). WorkingDB/images untouched; real upload вChrome не выполнялся.
ADM-AUD-09 IN PROGRESS: selection/bulk/filedelete + final visual/runtime checks. Conditional nonemptyconfig thumbnails/watermark media syntax (fit/crop/resize) не реализован этой mediaBytes веткой; такиеconfig нельзя объявлять поддержанными. Следующий точный шаг — selection и originalbulk contract, затем удалить только выбранные файлы с явнымUIconfirmation и isolatedfake-storagetests. Коммит не создан.

### Сводка ADM-AUD-07 / ADM-AUD-09 и восстановление постоянного учёта — 2026-10-14

По уточнению пользователя выполненные работы снова фиксируются в основном плане и журнале. Ниже внесены этапы, ранее записанные отдельно в `docs/media_library_verification_2026-10-14.md`. Предыдущие записи TODO отражают состояние на момент их создания; актуальное состояние приведено здесь.

**ADM-AUD-07 — перенос очереди ALT/TITLE в Filament с функционалом Voyager: DONE в пределах локальной приёмки.** Это общее название уже выполненных подзадач, без изменения их истории. Итог: массовые действия, откат, фильтры, сведения/превью, редактирование предложений ALT/TITLE и обновление черновиков. Итоговая проверка: 18 тестов / 122 assertions PASS. Внешние OpenAI/queue и реальное применение/откат на копии production остаются отложенной UAT; различие количества WebP в двух storage не исправлялось копированием файлов.

**ADM-AUD-09 — перенос медиатеки с функционалом Voyager: IN PROGRESS.** Выполнены и проверены локально следующие подэтапы:

- DONE — выделение объектов и удаление выбранных файлов/пустых папок; массовые кнопки появляются после выделения. Непустая папка блокирует удаление всей выбранной партии до первой записи.
- DONE — массовое перемещение файлов, затем папок и смешанного набора через общие сервисы; проверка конфликтов до копирования, транзакция обновления ссылок и очистка новых копий при ошибке.
- DONE — настроенный корень `voyager.media.path`, `show_folders` и флаги разрешённых операций проверяются в интерфейсе и на сервере; переходы за корень запрещены.
- DONE — условные миниатюры fit/crop/resize и watermark при загрузке, проверка настроек, коллизий производных имён и отката; настройки в рабочем config не включались. Сравнение с исходным обработчиком: 23 сценария совпали по размерам и пикселям.
- DONE — обрезка через существующий обработчик BREAD: замена или новая копия, модальное окно, начальная область 80%, перемещение рамки и восемь ручек изменения размеров. Запись проверена только на fake storage.
- DONE — сведения о файле, дата/размер/MIME/URL, компактные превью и боковая панель; обновление списка сбрасывает устаревшие формы и выделение.
- DONE — компактная панель инструментов, иконки действий файлов/папок, формы с отменой и цепочка переходов к родительским папкам.
- DONE — визуальная проверка в Chrome с оригиналом и адаптивная проверка 390x844: горизонтального переполнения нет, массовые действия, сведения и окно обрезки доступны.

Evidence: подробные результаты и пути скриншотов — `docs/media_library_verification_2026-10-14.md`; обработка изображений 28 тестов / 575 assertions PASS, последний gate смешанных операций 5 тестов / 153 assertions PASS. Это разные наборы проверок, их результаты не суммируются. Рабочие БД, картинки и файлы при проверках не изменялись; в Chrome проверялись интерфейс, выделение, формы и отмена, действия записи не запускались.

Согласованные отличия от Voyager: удаляются только пустые папки; при переименовании/перемещении сохраняются источники для прежних ссылок, обновляются поддерживаемые ссылки. Новые миграции для этих этапов не добавлялись. Коммит по этим изменениям ещё не создан.

**Осталось / следующий точный шаг:** сверить дополнительные взаимодействия исходного cropper (zoom, колесо/касания и все ручки), затем выполнить общую приёмку медиатеки по оригиналу и зафиксировать оставшиеся различия. Реальные операции записи и внешняя UAT не считаются проверенными. Общий ADM-AUD-09 и миграция остаются IN PROGRESS.


### ADM-AUD-09 — Cropper оригинальной медиатеки: IN PROGRESS, 2026-10-14

Сверены Voyager media/manager.blade.php и package-lock.json: оригинал использует Cropper.js 1.5.11 с настройками по умолчанию и передаёт округлённые x/y/width/height в серверный crop. Ручная рамка новой медиатеки не поддерживала zoom колесом/двумя пальцами и переключение drag mode двойным щелчком. Подключены локальные неизменённые dist JS/CSS Cropper.js 1.5.11 с MIT LICENSE из официального репозитория; добавлен адаптер `public/js/filament/media-library-crop.js`. Внешний CDN во время работы страницы не используется. Существующий обработчик BREAD и режимы замены/копии сохранены.

Редактор создаётся после появления окна, уничтожается при удалении Alpine-компонента; область и числовые поля синхронизируются с Livewire libraryCrop. Проверено восемь ручек, стандартная начальная область, смена drag mode двойным щелчком, изменение ширины числовым полем после изменения/потери фокуса, отмена и открытие второго изображения без дублирования cropper-container. Найден порядок событий Alpine: setRegion перенесён в nextTick после обновления модели. Wheel/pinch поддерживаются библиотекой по умолчанию, но физическое колесо/мультитач в этом ходе не проверялись.

Явные ограничения: checkOrientation=false оставлен для согласования координат с существующим GD crop по исходной пиксельной матрице; сервер по-прежнему отклоняет выход области за исходное изображение. Полный паритет EXIF и выделения вне изображения с оригиналом пока не подтверждён. Это открытые пункты, а не завершённая приёмка.

Проверки: node --check адаптера PASS; прямой PHPUnit gate standalone_media_crop — 2 теста / 31 assertions PASS (2.139s, 98 MB), exit code 0. PHPUnit сообщает пять предупреждений о других пустых классах тестов; artisan test возвращает 1 при тех же двух успешных тестах. Chrome: первое изображение 1536x1024, второе 1024x1024, один cropper-container при повторном открытии; ошибок сайта в проверенных console errors нет (есть сторонняя ошибка расширения кошелька). Снимок `storage/app/audit/media-cropper-original-20261014.png` сохранён и просмотрен после повторной попытки captureScreenshot. Рабочие изображения/БД не изменялись, сохранение обрезки не нажималось. Коммит не создан.

Следующий точный шаг ADM-AUD-09: проверить колесо/касания и EXIF/границы исходного cropper, затем общая приёмка медиатеки. План и журнал обновлены этим шагом; общий этап остаётся IN PROGRESS.


### ADM-AUD-09 — границы области crop: IN PROGRESS, 2026-10-14
Прямая проверка исходного Intervention/GD на искусственном PNG обнаружила отличие: Voyager принимает область, частично или полностью выходящую за изображение, включая отрицательный x; свободное место заполняется чёрным. Четыре сценария сравнения с GD imagecrop совпали по каждому пикселю. Наш общий обработчик пока отклоняет эти координаты. Следующий шаг: разрешить такую область только для самостоятельной медиатеки, сохранить прежние ограничения встроенного BREAD и ограничение памяти; добавить проверку пикселей и режима записи на fake storage. Скрипт evidence: storage/app/audit/compare-media-crop.php. Рабочие файлы не изменялись.


### ADM-AUD-09 — границы области crop: DONE (подэтап), 2026-10-14

Исправлено подтверждённое отличие от Voyager: самостоятельная медиатека принимает отрицательные x/y и область, частично или полностью выходящую за источник. Общий BreadImageCrop получил необязательный allowOutside=false; только MediaLibrary передаёт true. Встроенный BREAD сохраняет прежнюю проверку границ. Чёрное заполнение свободной области соответствует прямой проверке исходного Intervention/GD. Ширина/высота должны быть положительными; выходной буфер ограничен 20 млн пикселей и расчётным лимитом памяти. Координаты медиатеки ограничены диапазоном GD int32, числовые поля x/y допускают отрицательные значения.

Новый feature-тест на fake storage проверяет каждый пиксель трёх обрезанных копий: частичный выход справа/снизу, полностью внешняя область и отрицательный x. Проверены размеры, неизменность оригинала, создание ровно одной копии при каждой операции и сохранение запрета внешней области для BREAD. Прежний тест слишком большой области теперь проверяет лимит выходного буфера вместо уже разрешённого выхода за край. Прямой targeted PHPUnit: 3 теста / 347 assertions PASS, 3.474s, 86 MB, exit code 0, без предупреждений при запуске конкретного файла. Сравнение четырёх сценариев с исходной библиотекой: все пиксели совпали. Рабочие storage/БД/изображения не менялись; коммит не создан.

EXIF: исходный сервер Voyager crop вызывает Image::make(...)->crop(...) без orientate; исходный клиент Cropper по умолчанию обрабатывает EXIF. Нельзя объявлять одинаковыми отображаемые и серверные координаты только по этим вызовам. Наш адаптер пока оставляет checkOrientation=false; поведение JPEG с EXIF в браузере требует отдельной проверки, полная совместимость не заявляется. Следующее ADM-AUD-09: проверить EXIF preview/координаты и физические wheel/pinch взаимодействия, затем общая приёмка медиатеки. Общий ADM-AUD-09 остаётся IN PROGRESS.


### ADM-AUD-09 — сверка EXIF crop: IN PROGRESS, 2026-10-14
Восемь искусственных JPEG с EXIF Orientation 1–8 обрезаны исходным Intervention/GD и нашим обработчиком: для одинаковых координат все пиксели совпали (storage/app/audit/compare-media-crop-exif.php, exit 0). Исходный клиент использует стандартный checkOrientation=true; наш override false отличается. Следующее действие: восстановить стандартную клиентскую настройку оригинала, убрать дополнительный CSS override ориентации и закрепить сохранение исходного серверного контракта тестом. Отдельное исправление старого расхождения preview/server для EXIF не входит в эту сверку.


### ADM-AUD-09 — EXIF crop: DONE (сверка кода и серверного контракта), 2026-10-14

Восстановлены стандартные настройки Cropper.js 1.5.11 оригинала: удалён checkOrientation=false из адаптера и дополнительный image-orientation:none из preview. Wheel/pinch/double-click и EXIF теперь используют параметры исходной библиотеки. Серверный crop, как Voyager, не выполняет orientate перед обрезкой; поворот при загрузке — отдельная существующая операция. Старое потенциальное расхождение EXIF preview/server сохраняется, скрытое исправление бизнес-контракта не добавлялось.

Прямая сверка восьми JPEG Orientation 1–8 с исходным Intervention/GD: размеры и каждый пиксель совпали, exit code 0. Скрипт: storage/app/audit/compare-media-crop-exif.php; изображения созданы в памяти, рабочие файлы/БД не изменялись. Добавлен tests/Unit/MediaCropExifTest.php: ориентация не изменяет исходные серверные координаты, результат JPEG и размеры 12x9 сохранены для всех восьми вариантов. Targeted PHPUnit по этому тесту и standalone_media_crop: 4 теста / 363 assertions PASS, 2.873s, 86 MB, exit code 0, без предупреждений. node --check адаптера PASS. README локального Cropper обновлён. Коммит не создан.

Общий ADM-AUD-09 IN PROGRESS. Этот DONE относится к сверке исходных настроек и серверного контракта, а не к проверке на физическом сенсорном устройстве. Осталось: фактические wheel/pinch/EXIF preview взаимодействия в браузере и общая приёмка медиатеки. Следующий точный шаг — проверить оставшиеся взаимодействия доступными средствами Chrome и сверить итоговый список операций с Voyager.


### ADM-AUD-09 — аудио/видео в сведениях: IN PROGRESS, 2026-10-14
Сверка original media/manager.blade.php (detail_img) выявила пропуск: видео и аудио имеют встроенные плееры с controls; новая панель показывает только значок документа. Следующий шаг — добавить компактные HTML video/audio по MIME в сведения выбранного файла и проверить на искусственных WAV/MP4 в fake storage. Автовоспроизведение не требуется оригиналом. Рабочие файлы не изменять.


### ADM-AUD-09 — аудио/видео в сведениях: DONE (код и изолированные проверки), 2026-10-14

По исходному media/manager.blade.php добавлены video/audio с controls в боковую панель выбранного файла. MIME определяет тип плеера; источник использует существующий кодированный public URL и фактический MIME вместо нескольких жёстко заданных MIME оригинала. Видео ограничено высотой 280px, аудио занимает ширину панели; autoplay отсутствует, preload=metadata. wire:key по пути заменяет плеер при смене файла. Для документов остаётся прежняя иконка.

Новый fake-storage feature-тест создаёт искусственные WAV/MP4 headers и документ, проверяет смену аудио/видео/документа, наличие controls, отсутствие autoplay и неизменность исходных bytes. Вместе с прежней проверкой сведений/refresh: 2 теста / 25 assertions PASS, 2.983s, 84 MB, exit 0. Это подтверждает определение MIME и HTML-плееры, не декодирование видео/аудио браузером. Локальный поиск существующих mp4/webm/ogg/mp3/wav в media storage образцов не нашёл; воспроизведение реального файла в Chrome не заявляется. Рабочие файлы/БД не изменялись, коммит не создан.

Дополнительные различия, найденные при сверке original selectFile/openFile: обычный клик выбирает один объект и показывает сведения, Ctrl/Meta расширяет выбор, Shift выбирает диапазон; двойной клик открывает папку или модальное превью изображения. Новая страница пока использует checkbox и отдельную кнопку сведений/переход к изображению в новой вкладке. Это незакрытые пункты паритета интерфейса, а не отсутствующие серверные операции.

Следующий точный шаг ADM-AUD-09 — перенести выбор карточек с Ctrl/Meta/Shift и открытие сведений, сохранив доступные checkbox. Затем сверить модальное превью, drag-and-drop upload/индикатор прогресса и оставшиеся cropper взаимодействия. Общий ADM-AUD-09 остаётся IN PROGRESS.


### ADM-AUD-09 — выбор карточек: IN PROGRESS, 2026-10-14
Original selectFile: обычный клик заменяет выбор, Ctrl/Meta добавляет без повторного удаления, Shift при одном выбранном объекте добавляет прямой диапазон; обратный Shift в исходном цикле добавляет только конечный объект. Следующий шаг: серверное selectCard с проверкой текущего списка/permission, выбор кнопкой карточки, сохранение checkbox, сведения только при одном файле и отдельное открытие папки для клавиатуры/мобильного ввода. Проверки — fake storage и Chrome без действий записи.


### ADM-AUD-09 — выбор карточек: DONE (подэтап), 2026-10-14

Добавлен MediaLibrary::selectCard: обычный клик заменяет выбор; Ctrl/Meta добавляет без переключения уже выбранного объекта; Shift при одном выбранном объекте добавляет прямой диапазон в текущем списке. Обратный Shift сохраняет исходную логику Voyager (добавляет конечный объект без промежуточных). Проверяются browse_media и принадлежность карточки текущему списку. Количество ограничено 500. Один выбранный файл показывает сведения, выбор папки/нескольких объектов скрывает их. Checkbox сохранены и тоже обновляют сведения; выбрать всё/снять выделение синхронизируют панель.

Карточки сделаны доступными кнопками с aria-pressed и голубым фоном выделения. Двойной клик папки открывает её; отдельная кнопка «Открыть папку» сохраняет однократное действие для клавиатуры и мобильного ввода. Публичная ссылка файла остаётся в панели сведений; модальное открытие изображения по двойному клику — следующий пункт.

Первый gate обнаружил две ошибки тестового Livewire snapshot: слишком ранний abort в updatedSelectedFiles разрывал прежний сценарий проверки массового удаления, а новый тест пытался вызвать действие после уже запрещённого mount. Исправлено: hook обновляет сведения только по текущему списку, серверные действия сохраняют проверки перед записью; revoke проверяется на ранее открытом компоненте. Итоговый gate: 5 тестов / 149 assertions PASS, 7.845s, 92 MB, exit 0. Покрыты обычный/добавочный/повторный/прямой и обратный диапазон, папка/checkbox, очистка, чужая папка, отзыв permission, прежние массовые операции и сведения.

Chrome blog-posts/April2026: обычный клик показал сведения первого файла; Shift до третьего дал [true,true,true,false] и убрал сведения; Ctrl до четвёртого дал четыре выбранных объекта. В blog-posts один клик April2026 дал aria-pressed=true/счётчик1, двойной клик открыл April2026 с четырьмя файлами. Screenshot storage/app/audit/media-card-selection-20261014.png сохранён. Действия записи не выполнялись, рабочие файлы/БД не менялись, коммит не создан.

Следующий точный шаг ADM-AUD-09 — модальное превью изображения по двойному клику, затем drag-and-drop/progress загрузки и оставшиеся физические cropper взаимодействия. Общий этап IN PROGRESS.


### ADM-AUD-09 — модальное превью: IN PROGRESS, 2026-10-15
Original openFile показывает imagemodal по двойному клику изображения. Следующий шаг — добавить отдельное модальное превью Filament с исходным public URL, выбором текущей карточки, закрытием/повторным открытием и проверкой текущей папки/permission. Отдельная кнопка просмотра обеспечит доступ с клавиатуры и мобильного ввода. SVG остаётся документом согласно уже принятому поведению медиатеки; рабочие файлы не менять.


### ADM-AUD-09 — модальное превью: DONE (подэтап), 2026-10-15

Добавлено openImage с проверками browse_media, текущей папки, локального файла и MIME изображения; выбирает одну карточку, показывает сведения и открывает Filament modal media-image-preview. Двойной клик изображения и отдельная кнопка-лупа открывают один диалог с исходным public URL. Название/alt экранируются Blade; изображение ограничено шириной окна и высотой 70vh. SVG остаётся документом. Закрытие/перезагрузка списка/переход папки или страницы сбрасывают previewFile. Просмотр не зависит от allow_crop и ничего не записывает.

Новый fake-storage feature-тест проверяет имя с пробелами/кодированный URL, dispatch окна, выбор одного файла, закрытие и открытие другого, refresh, отказ для документа/чужой папки и отзыв permission, неизменность исходных bytes и числа файлов. Начальный сбой fixture был связан с уничтожением временного UploadedFile до чтения; исправлено удержанием fixture в переменной. Итоговый gate с выбором карточек и сведениями: 4 теста / 53 assertions PASS, 4.717s, 88 MB, exit 0.

Chrome: двойной клик первого изображения открывает диалог, complete=true/naturalWidth=1536; ошибок сайта в проверенных console errors нет. Escape скрывает окно; кнопка-лупа второго изображения повторно открывает диалог с другим именем. Screenshot storage/app/audit/media-image-preview-20261015.png сохранён и просмотрен. Рабочие изображения/файлы/БД не менялись; действий сохранения не было. Коммит не создан.

Следующий точный шаг ADM-AUD-09 — сверить drag-and-drop загрузку и индикатор прогресса с Voyager, затем оставшиеся wheel/pinch/EXIF browser interactions и общая приёмка медиатеки. Общий этап остаётся IN PROGRESS.


### ADM-AUD-09 — drop upload/progress: IN PROGRESS, 2026-10-15
Original Dropzone автоматически отправляет выбранные/перетащенные файлы, показывает totaluploadprogress и обновляет список по queuecomplete. Новая страница пока требует отдельную отправку формы и не показывает процент. Следующий шаг — локальный Alpine-адаптер Livewire uploadMultiple (API проверен по установленному runtime), автоматическое сохранение через существующий uploadFiles, видимый прогресс/ошибки/отмена передачи и проверка папки назначения при завершении. Реальные browser uploads не выполнять; операции записи проверять только на fake storage.


### ADM-AUD-09 — drop upload/progress: DONE (код и изолированные проверки), 2026-10-15

Добавлен локальный Alpine-адаптер public/js/filament/media-library-upload.js. Выбор файлов или drop запускает Livewire uploadMultiple, после завершения передачи автоматически вызывается существующий uploadFiles — как сценарий Dropzone Voyager без дополнительной кнопки отправки. Показаны имена, процент передачи и отдельное состояние сохранения; добавлены сообщения ошибок и отмена передачи. После ошибки временная партия заменяется (append=false), повторная передача во время busy не запускается. Лимиты 20 файлов/10 МБ проверяются клиентом для обратной связи и повторно сервером. allow_upload по-прежнему контролируется сервером.

uploadFiles принимает необязательную папку начала передачи и возвращает true при успехе. Если текущая папка изменилась во время передачи, сервер отказывает 409 до записи, вместо сохранения в другую папку. Прежние вызовы без аргумента совместимы. Отмена доступна во время передачи; серверное сохранение после передачи отдельно обозначено и не прерывается кнопкой отмены.

Проверки: Node 3 tests PASS (77.5383ms) — progress, сохранение только после finish, фиксированная папка, отмена/ошибка без save, ограничения count/size и отсутствие ложного успеха при серверной валидации. PHP gate media_upload/standalone_media_library/media_configuration: 5 тестов / 124 assertions PASS, 6.930s, 92 MB, exit 0. Новый fake-storage тест проверяет 409 при изменении папки, отсутствие записей в обе папки и успешную загрузку с совпавшей папкой/return true.

Chrome: форма открывается, зона drop и лимиты видны, progress и отмена скрыты до передачи, ошибок сайта в проверенных console errors нет; закрытие скрывает форму. Screenshot storage/app/audit/media-upload-dropzone-20261015.png сохранён и просмотрен. Реальные выбор/drop/upload в Chrome не выполнялись по ограничению на рабочие файлы; браузерная передача/процент на реальном запросе не объявляются проверенными. Рабочие файлы/БД/изображения не менялись, коммит не создан.

Следующий точный шаг ADM-AUD-09 — общая сверка оставшихся операций с оригиналом и фиксация итоговых различий/отложенной браузерной UAT, включая wheel/pinch/EXIF preview и воспроизведение аудио/видео. Общий ADM-AUD-09 остаётся IN PROGRESS.


### ADM-AUD-09 — итоговая сверка / inline rename: IN PROGRESS, 2026-10-15
Original detail_info содержит редактируемое имя с сохранением по Enter при allowRename; в новой панели есть только имя и отдельная форма переименования. Следующее действие — добавить Enter/кнопку в сведения через общий BreadLibraryFiles::relocate, с проверкой текущей папки/allow_rename и прежним сохранением источников/ссылок. После этого общий регрессионный gate медиатеки и таблица оставшихся отличий/непроверенных сценариев.


### ADM-AUD-09 — hidden_files: IN PROGRESS, 2026-10-15
Итоговая сверка выявила игнорирование voyager.hidden_files в новой listing. Original исключает файл с пустым PATHINFO_FILENAME только при hidden_files=false; новая версия безусловно скрывает все dot-prefixed файлы. Следующее действие — перенести точное правило контроллера и проверить оба значения на fake storage, не меняя рабочий config.


### ADM-AUD-09 — общий gate, inline rename и hidden_files: 2026-10-15

DONE (подэтапы): в сведениях добавлено переименование файла по Enter/кнопке через общий relocation/reference service с сохранением источника; коллизии/ошибки пути/повторная попытка/allow_rename проверены. Chrome подтверждает исходное имя и кнопку; submit не запускался. Снимок storage/app/audit/media-inline-name-20261015.png сохранён. Учтён voyager.hidden_files по точному правилу PATHINFO_FILENAME исходного контроллера; рабочий config не менялся.

Общий gate после inline rename: 41 PHP-тест / 1082 assertions PASS, 33.030s, 118 MB, exit 0; Node upload 3 tests PASS, 78.202ms. После последнего hidden_files изменения targeted hidden_files + inline rename: 2 теста / 24 assertions PASS, 0.898s, 78 MB. Наборы пересекаются, не суммируются. Действия записи проверены только на SQLite/fake storage. Рабочие БД/файлы/изображения не менялись, коммит не создан.

Создана матрица original/new и evidence: docs/admin_media_parity_2026-10-15.md. Общий ADM-AUD-09 IN PROGRESS: остаются физические wheel/pinch/EXIF preview, воспроизведение аудио/видео, browser upload/drop/progress/cancel на отдельном тестовом storage и повторная мобильная проверка последних форм. Эти пункты не объявлены пройденными. Следующий точный шаг — мобильная проверка последних форм без записи; затем первичная сверка существующего ADM-AUD-10 кода с оригиналом, сохраняя открытый UAT медиатеки.


### ADM-AUD-09 — мобильная проверка последних форм: IN PROGRESS, 2026-10-15
Chrome viewport390x844: document clientWidth/scrollWidth375/375; форма загрузки видна, но её clientWidth295/scrollWidth366 — native file input переполняет собственную зону. Поле имени в сведениях занимает x57..276 и помещается. Следующий шаг — ограничить file input и grid child шириной контейнера, повторить измерение, проверить мобильное превью/закрытие. Реальные выбор/загрузка/переименование не запускались.


### ADM-AUD-09 — мобильная проверка последних форм: DONE (подэтап), 2026-10-15

В Chrome390x844 подтверждено внутреннее переполнение native file input: форма295/366px. Добавлены min-width:0 для grid/зоны и width/max-width100% для input. После перезагрузки/открытия формы clientWidth=scrollWidth=295; документ clientWidth=scrollWidth=375. Реальных выбора/загрузки файлов не было.

Модальное превью: изображение x47.75..342.24 (ширина294.49), укладывается в viewport390; кнопка закрытия работает. Поле имени x57..276, кнопка сохранения x282..318 укладываются в панель. Поле проверено фокусом/Tab без Enter/submit. Снимки storage/app/audit/media-mobile-upload-final-20261015.png, media-mobile-preview-final-20261015.png, media-mobile-inline-name-20261015.png сохранены. Временный viewport override сброшен. Изменение только CSS; отдельные тесты, повторяющие CSS, не добавлялись. Рабочие файлы/БД/картинки не менялись, коммит не создан.

Матрица docs/admin_media_parity_2026-10-15.md обновлена: последняя мобильная сверка закрыта. Общий ADM-AUD-09 остаётся IN PROGRESS из-за оставшейся UAT wheel/pinch/EXIF preview, реального воспроизведения media и browser upload цепочки на отдельном тестовом storage. Следующий основной шаг разработки — ADM-AUD-10: сверить уже реализованные операции меню/настроек и drag-and-drop с оригиналом; открытые проверки ADM-AUD-09 сохранить в плане.


### ADM-AUD-10 — операции меню/настроек: IN PROGRESS, 2026-10-15
Сверены VoyagerMenuController, model Menu, manager/builder и новые VoyagerMenuItems/VoyagerSettings. Уже реализованы item CRUD, title translations, parent/order/icon/status, cycle guards, редактирование отдельных значений настроек. Пропуски: управление самими меню, drag-and-drop дерева; у настроек отсутствуют создание/удаление/порядок/delete_value и часть типов полей. Не считать ADM-AUD-10 выполненным на основании прежних item/settings tests.

ADM-AUD-10A — создание и переименование меню: IN PROGRESS. Добавить действия add_menus/edit_menus, валидацию имени/уникальности, поддержку пустого списка и сброс кеша voyager_menu_<name> как в исходном model Menu. Реальные меню/БД не изменять: записи только в SQLite-тестах, Chrome — просмотр форм/отмена. Удаление меню и дерево требуют отдельной сверки каскада, остаются следующими подзадачами.


### ADM-AUD-10A — создание/переименование меню: DONE (подэтап), 2026-10-15
VoyagerMenuItems получил формы создания/переименования menus, add_menus/edit_menus на открытие и сохранение, trim/required/max255/unique с исключением собственного id. Пустой список больше не даёт404; можно создать первое меню, добавление пункта без меню запрещено. Сохранены id/пункты, очищаются старый/новый voyager_menu кеши согласно оригинальному model Menu. Формы/переключение/отмена проверены. Новых миграций нет.

PHPUnit: 3 tests / 40 assertions PASS, 2.212s, 86 MB, exit0. Пустой список, имя/коллизии, успешные записи в SQLite, сохранность пункта, cache invalidation, add/edit permission guards и прежний item/translation scenario. Chrome: create — пустое имя/отмена; rename footer — исходное footer/отмена. Original /admin/menus/create содержит единственное текстовое поле name, сверено. Скриншот storage/app/audit/menu-name-form-20261015.png сохранён. В браузере ничего не сохранялось; рабочие БД/меню/картинки не менялись. Коммит не создан.

Инвентаризация/следующие подзадачи: docs/admin_menu_settings_audit_2026-10-15.md. ADM-AUD-10 IN PROGRESS. Следующий точный шаг ADM-AUD-10B — сверить удаление menus/пунктов/переводов с оригинальными FK и hooks, затем реализация/SQLite-тест; далее ADM-AUD-10C drag-and-drop, 10D/10E операции/типы настроек. Открытый UAT ADM-AUD-09 сохраняется отдельно.

Визуальная доводка ADM-AUD-10A: действия меню сгруппированы справа с переносом; между формой имени и таблицей добавлен отступ24px.


### ADM-AUD-10B — удаление меню: IN PROGRESS, 2026-10-15
Источник: Voyager migrations/2016_05_19_173453_create_menu_table.php — FK menu_items.menu_id с onDelete cascade; Menu::deleted очищает voyager_menu кеш. Parent_id собственного FK в исходной схеме не имеет. Generic destroy проверяет delete permission. Следующий шаг: действие delete_menus с подтверждением, атомарное удаление выбранного menus и всех его menu_items, очистка переводов этих пунктов и кеша, выбор оставшегося меню/пустое состояние. Явное удаление пунктов поддержит восстановленные схемы без FK. Очистка translations — отличие от raw FK cascade оригинала, устраняющее orphan rows; чужие таблицы/меню не затрагивать. Проверки записи только SQLite; Chrome — открытие и отмена подтверждения.


### ADM-AUD-10B — удаление меню: DONE (подэтап), 2026-10-15
Добавлен deleteMenu с delete_menus, проверкой текущего id и транзакцией/lock выбранного menus. Удаляются все menu_items этого menu_id независимо от вложенности, их translations, затем меню. Cache voyager_menu_<name> сбрасывается после успешной транзакции. Другое меню/его пункты/переводы и переводы других таблиц сохраняются. После удаления выбирается оставшееся admin/первое по имени, после последнего — пустое состояние для создания меню. Незавершённые формы и validation очищаются. Новых миграций нет.

Подтверждение — модальное окно Filament с именем меню и отдельной отменой, как delete_modal исходного Voyager bread/browse. Первая проверка native wire:confirm открыла browser confirm; инструмент не смог его закрыть, тестовая вкладка закрыта без подтверждения. Итоговая реализация использует обычное модальное окно, сверенное с оригинальной разметкой; Chrome показывает имя/предупреждение, отмена закрывает окно, меню admin/footer/header остаются. Реальное удаление не запускалось. Снимок storage/app/audit/menu-delete-confirm-20261015.png сохранён.

PHPUnit menu creation/deletion/permissions/item translation: 5 тестов / 65 assertions PASS, 2.175s, 90 MB, exit0. SQLite fixture без FK подтверждает явный каскад, удаление последнего меню и очистку только своих переводов; synthetic trigger failure при финальном DELETE menus подтверждает откат пунктов/переводов/меню и сохранность кеша. Проверка запрета без delete_menus сохраняется. Отличие от raw FK cascade оригинала — явная очистка translations вместо оставления orphan rows.

Рабочие БД/меню/файлы/изображения не менялись, коммит не создан. ADM-AUD-10 IN PROGRESS. Следующий точный шаг ADM-AUD-10C — дерево пунктов и drag-and-drop порядка/вложенности, со сверкой оригинального orderMenu, проверкой чужих id/циклов/полного набора и тестами на SQLite; затем 10D/10E настройки.


### ADM-AUD-10C — дерево и drag-and-drop: IN PROGRESS, 2026-10-15
Original menus/builder использует Nestable2 и автоматически сохраняет serialize в orderMenu; sibling order=index+1, parent_id=null у корней. По установленному исходному bundle подтверждён default maxDepth5. Новая страница пока таблица. Следующий шаг — дерево через уже поставляемый window.Sortable Filament, одинаковые группы nested lists, автосохранение с maxDepth5, полный набор своих id и транзакция; при ошибке обновить дерево из сервера. Существующая форма parent/order остаётся альтернативой. Рабочее меню в Chrome не перетаскивать; запись проверять на SQLite.


### ADM-AUD-10C — дерево/drag-and-drop: реализация и локальные проверки выполнены, 2026-10-15
VoyagerMenuItems::menuTree строит вложенность с сохранением sibling order; legacy orphan/cyclic записи остаются видимыми без автоматической записи/исчезновения. Таблица заменена деревом с иконками/ссылками/статусами/редактированием/удалением. Для edit_menus подключён уже поставляемый Filament window.Sortable (touch support библиотеки), группы nested lists, ручки, автоматическое сохранение. Форма parent/order остаётся альтернативой. Новый public/js/filament/menu-tree-editor.js сериализует вложенность, блокирует drag во время сохранения, ограничивает глубину5 и восстанавливает дерево/переинициализирует Sortable после отказа сервера; destroy очищает обработчики.

reorderItems повторно проверяет edit_menus, выбранное меню, list shape/id/дубликаты/глубину и полный набор своих пунктов под lock/transaction. Защитный лимит2000 пунктов; sibling order=index+1, корни parent_id=null, как original orderMenu. Чужие id, неполное дерево и повторные/циклические id отвергаются до записей. Обновляются только parent_id/order/updated_at, title/URL/переводы сохраняются; кеш очищается после успеха. SQL failure откатывает предыдущие изменения/сохраняет кеш. Новых миграций/внешних пакетов нет.

Targeted PHP gate menu_tree/creation/deletion/permissions/item translation: 8 тестов / 107 assertions PASS, 3.506s, 94 MB, exit0. Проверены успешный reparent/reorder, пять уровней разрешены/шесть запрещены, неполный/чужой/повторный/malformed payload, readonly permission, rollback, видимость orphan/cycle без записи. Node: 4 tests PASS, 109.5075ms — nested serialization/menu id, восстановление/remount после отказа, readonly/self-drop и граница глубины5/6. node --check PASS.

Chrome read-only: /filament/menu-items?menu=2 и original /admin/menus/2/builder. В обоих header14пунктов,5корней, последовательностьid и parent_id полностью совпала. При первом измерении случайно захвачены data-menu-id боковой навигации; сравнение повторено строго внутри редактируемого дерева, итог14/14 подтверждён. Ошибок сайта в проверенных console errors нет. Снимок storage/app/audit/menu-tree-header-20261015.png сохранён. Реальный drag/save порядка/вложенности в Chrome не запускался; browser DnD/touch UAT остаётся открытым. Все записи только SQLite, рабочие БД/меню/файлы/изображения не менялись. Коммит не создан.

ADM-AUD-10C: код/локальная проверка DONE, полная браузерная UAT IN PROGRESS; общий ADM-AUD-10 IN PROGRESS. Следующее ADM-AUD-10D — создание/удаление/порядок/delete_value настроек; ADM-AUD-10E — типы настроек. Найден дополнительный пункт ADM-AUD-10F: сверить поля формы menu item (в исходном builder есть color, отсутствующий в новой форме; route/url/parameters также требуют итоговой сверки).


### ADM-AUD-10D — операции настроек: IN PROGRESS, 2026-10-15
Original VoyagerSettingsController store: slug(group)+'.'+key, unique key, value='', global max(order)+1 (первая0); move_up/down меняет местами ближайшие меньший/больший order только в своей группе; delete удаляет строку без файла, delete_value очищает value и удаляет физический файл только у type=image. update позволяет сменить группу/префикс ключа. Setting безtimestamps; original SettingUpdated чистит tagged cache, но новый Compatibility Voyager::setting читает DB напрямую (активного кеша значений нет).
Следующий шаг: формы создания/группы, up/down и модальное delete/clear с add/edit/delete_settings, транзакциями и восстановлением image bytes при DB failure. Никаких миграций/изменений рабочих значений или изображений. Проверки записи только SQLite и Storage::fake; Chrome — открытие форм/отмена.


### ADM-AUD-10D — операции настроек: DONE (подэтап), 2026-10-15
VoyagerSettings получил создание с полями original display_name/key/type/group/detailsJSON, prefix slug(group)+'.'+key, unique/max255 и global max(order)+1 (первая0), value=''. Перенесена смена группы при редактировании с изменением префикса и проверкой коллизии. Активная вкладка синхронизирована с сервером и сохраняет группу после действия. Новых миграций/таблиц нет.

Добавлены moveSetting up/down — обмен ближайшими order только в одной группе, граничный случай без записи, transaction/rollback. Delete/clear требуют delete_settings и открывают модальное подтверждение. Delete строки оставляет физический файл, как original; clear записываетvalue='', физически удаляет только type=image (file bytes остаются). Для image checkedFile ограничивает локальный public путь; при DB failure после удаления восстанавливаются исходные bytes. Новый runtime Compatibility Voyager::setting читает DB напрямую; tagged cache старого runtime не используется и не добавлялся.

PHPUnit settings gate: 5 тестов / 58 assertions PASS, 2.052s, 90MB, exit0. Покрыты создание/JSON/unique/key prefix/global order, смена группы/коллизия, order boundaries/group isolation и rollback второго UPDATE, delete/clear image/file, последняя строка, permission guards в том числе forged creating/pendingId, восстановление картинки после synthetic SQL failure. Все записи толькоSQLite/Storage::fake.

Chrome: новая форма creation открывается с пустым ключом, отмена возвращает группы Admin6/Site9/Модульные картины4/Соц.сети8. Подтверждение очистки «Название Сайта» показывает предупреждение и закрывается отменой; confirmRemove/move/save не нажимались. Ошибок сайта в проверенных console errors нет. Снимок storage/app/audit/settings-create-form-20261015.png сохранён. Рабочие настройки/БД/картинки/файлы не менялись, коммит не создан.

ADM-AUD-10 IN PROGRESS. Следующий точный шаг ADM-AUD-10E — проверить все девять типов settings и details/options, сохранение rich text/code/select/radio/file/image и режим сохранения нескольких значений по оригиналу. Новая форма предлагает типы оригинала, но прежний editor пока не покрывает все типы — это открытый этап10E, не считать его выполненным. ADM-AUD-10F — поля menu items (color и route/url/parameters); browser DnD/touch UAT10C остаётся открытым.


### ADM-AUD-10E — девять типов настроек: IN PROGRESS, 2026-10-15
Сверены settings/index, ContentTypes/File и Image: text/text_area/rich/code/checkbox/radio/select/file/image; details options/default/on/off/language/theme; File хранит JSON download_link/original_name, Image — строку пути. Если нового media upload нет, исходное значение должно сохраниться. Использовать BreadRichEditor/BreadCodeEditor/BreadImageUpload/BreadBrowseFiles, без дублирования движков. Следующий шаг — типизированная схема, сохранение raw HTML/кода и file shape, ручная обработка временных uploads после валидации с cleanup/повторной попыткой при SQL failure. Общий режим сохранения нескольких значений остаётся отдельным пунктом приёмки10E. Записи толькоSQLite/fake storage.


### ADM-AUD-10E — девять типов и details: реализация/локальные проверки выполнены, 2026-10-15
Добавлен BreadSettingFields: text без прежнего произвольного max5000; text_area textarea; rich_text_box использует BreadRichEditor с HTML source fallback для несовместимой legacy разметки; code_editor — BreadCodeEditor/language, прежний theme остаётся вdetails, внешний вид следует теме Filament (известное отличие от Ace). checkbox проверяет value==1 и подписиon/off; radio/select — options/default с выбором как исходная разметка. Rich source action поддерживается страницей. Для browse-only/forged settingId схема редактора пустая, чтобы не открывать upload/editor actions без edit_settings.

Image/file используют отдельное новое replacement поле и компактный просмотр старого значения через BreadImage/BreadBrowseFiles. Без нового upload исходная строка/JSON остаётся без изменений. BreadSettingUploads использует существующий BreadImageUpload (resize/quality/EXIF/thumbnail options), для file сохраняет JSON[{download_link,original_name}], settings/FY и preserveFileUploadName с цифровым суффиксом при коллизии. Новые данные обрабатываются после валидации, старые файлы при замене не удаляются. StoreFiles(false) сохраняет временный upload до успешного DB update; при SQL failure новые копии очищаются, можно повторить тот же upload. Исполняемые public расширения запрещены, как в новой медиатеке. Файл в таблице теперь показан ссылками, а не сырой JSON-строкой.

Targeted settings gate: 8 тестов / 116 assertions PASS, 3.188s, 94MB, exit0. Покрыты компоненты/сохранение семи текстовых/choice типов, длинный текст, многострочность, raw HTML/code, визуальный rich component, checkbox value2=false/on-off, defaults/invalid choice; оба media типа — unchanged values, FileJSON, preserve name/collision, rejectPHP без записи, image resize40x20->10x5, неизменность старых bytes, SQL failure cleanup и retry того же upload. Прежние операции settings10D также включены.

Read-only audit фактических типов текущей DB: text18, checkbox4, image4, code_editor1; другие типы в рабочих данных отсутствуют и проверялись искусственными fixtures. Скрипт storage/app/audit/settings-types-readonly.php выводит только counts, неvalues. Chrome: editor «Логотип Сайта» показывает текущее изображение complete=true/height160; replacement и group видны, ошибок сайта в проверенных console errors нет, отмена без save/upload. Снимок storage/app/audit/settings-image-editor-20261015.png сохранён. Рабочие DB/значения/файлы/картинки не менялись, новых миграций нет, коммит не создан.

ADM-AUD-10E IN PROGRESS: девять типов в индивидуальном editor реализованы и локально проверены; ещё нужен сценарий совместного сохранения нескольких значений, как общий settings update оригинала, и итоговая визуальная UAT типов, отсутствующих в рабочей DB. Перенос группы для media без reupload разрешён при сохранении прежнего файла (в оригинале такой update пропускался); это явное отличие, устраняющее необходимость повторной загрузки. Следующий точный шаг — общий editor/save нескольких настроек с сохранением тех же типов и отсутствующих media. ADM-AUD-10F menu-item поля и открытый browser DnD10C остаются в плане.

### ADM-AUD-10E — общее сохранение: IN PROGRESS, 2026-10-16
Сверен оригинальный settings update: одна форма отправляет все группы, media без замены пропускается, активная вкладка сохраняется. Реализовать общий editor с вкладками, теми же девятью компонентами и группой каждой настройки; фиксировать набор открытых id, проверять edit_settings при открытии/сохранении, валидировать всю форму до uploads. Сохранение нескольких строк — транзакция; при ошибке очистить только новые копии, оставить временные uploads для повторной попытки. Проверки записи — исключительно SQLite/fake storage; рабочие значения/изображения не менять.

### ADM-AUD-10E — общий редактор настроек: код/локальные проверки выполнены, 2026-10-16
Сверен исходный VoyagerSettingsController::update и settings/index: оригинал отправляет одной формой все группы, сохраняет активную вкладку, не заменяет image/file без нового upload. Добавлена кнопка «Редактировать все настройки»: общая форма с вкладками групп, девять типов через BreadSettingFields, отдельная группа каждой настройки и одна кнопка сохранения. Индивидуальный редактор сохранён. Поля имеют уникальные имена по settings.id; rich source переключается отдельно для каждого поля. Активная вкладка синхронизируется с activeGroup; после переноса последнего поля из группы выбирается существующая группа.

Сервер проверяет edit_settings, locked-набор id открытого редактора, наличие всех этих строк перед сохранением. Настройки, добавленные после открытия, не перезаписываются. Вся форма и будущие ключи проверяются до обработки replacements, в том числе коллизии двух новых ключей между собой. Строки сохраняются в одной DB transaction; при ошибке новые media-копии удаляются, прежние файлы и временный upload остаются для повторной попытки. Как и в индивидуальном редакторе, перенос media в другую группу без reupload разрешён с прежним путём (ранее зафиксированное отличие от original skip). Общая форма использует существующие rich/code/media движки. Badge checkbox приведён к исходному value==1 вместо bool(value).

Проверка: PHPunit settings targeted gate — 11 тестов / 152 assertions PASS, 4.971s, 102MB, exit0. Три новых сценария: смешанная форма всех девяти типов/две группы/перенос группы/unchanged media; SQL failure после первого file update с полным rollback/cleanup/retry; invalid options, существующий и новый дублирующий ключ, добавление/удаление строки после открытия, запрет browse-only. Восемь прежних settings тестов включены. php -l VoyagerSettings PASS, git diff --check по изменённым файлам PASS. Все записи только SQLite/fake public storage.

Chrome попытка открыть https://viar13.loc/filament/voyager-settings вернула net::ERR_CONNECTION_REFUSED. Визуальное подтверждение общей формы/вкладок и source mode нескольких rich полей НЕ выполнено, снимок нового UI не получен; не считать UAT завершённой. Рабочая DB/настройки/картинки не менялись, миграций и коммита нет.

ADM-AUD-10E IN PROGRESS до браузерной UAT общего editor и типов, отсутствующих в рабочей DB. Следующий точный шаг: при доступном локальном сервере открыть общий editor, проверить вкладки/компактные media/отмену без записи. Независимо остаётся ADM-AUD-10F — сверка menu-item color, route/url/parameters с оригиналом; общий ADM-AUD-10 и DnD/touch UAT10C открыты.

### ADM-AUD-10F — поля пунктов меню: IN PROGRESS, 2026-10-16
Сверены оригинальные menus/builder, VoyagerMenuController::prepareParameters и MenuItem mutators/hooks. Найдены отсутствующие color и переключатель URL/route: в оригинале route очищает url (mutator сохраняет пустую строку), URL очищает route/parameters. Параметры — textarea JSON; target _self/_blank; новые пункты получают следующий root order. Реализовать без новых миграций, сохранить переводы title, закрыть схемы без соответствующих add/edit permissions, сбрасывать cache меню после item save/delete. Проверить исключительно SQLite; визуальную сверку повторить при доступном локальном сервере.

### ADM-AUD-10F — поля пунктов меню: реализация/локальные проверки выполнены, 2026-10-16
Подтверждён исходный контракт menus/builder, VoyagerMenuController::prepareParameters, базового и app/Models/MenuItem: цвет; статический URL или именованный route; параметры textarea JSON; icon_class; target _self/_blank. Добавлены ColorPicker (пустой либо #RRGGBB), выбор типа ссылки, условные URL/route/parameters поля и серверная нормализация: route сохраняет url='' (как исходный setUrlAttribute), URL сохраняет route=NULL/parameters=''. Пустой цвет существующей записи сохраняется пустым; новые получают #000000 как native input color. Неизвестный route не запрещается, как в оригинале. Правило JSON сохранено; неиспользуемые параметры URL не валидируются и очищаются. Без add/edit соответствующая схема пуста.

Создание пункта получает следующий order среди корневых пунктов всех меню, как исходный highestOrderMenuItem() без menu_id filter. Существующие ручные parent/order/status поля оставлены. Заголовок базового языка теперь использует configured default locale вместо жёсткого EN. После item save, save перевода и delete сбрасывается voyager_menu_NAME, как исходные Eloquent hooks. Редактирование закрывает форму имени меню и очищает старые ошибки.

PHPunit targeted menu gate: 11 тестов / 145 assertions PASS, 4.103s, 98MB, exit0. Три новых теста: create/order/color/icon/target, переключение обоих link modes и cache invalidation для обычного save/перевода/delete; запрет плохого цвета/JSON/mode/target и foreign-menu item, игнорирование hidden parameters в URL режиме; browse-only forged editor/save. Прежний translation/cycle тест и tree/menu management gates включены. php -l VoyagerMenuItems и git diff --check PASS. Записи только SQLite; рабочие меню/БД/изображения не менялись; миграций и коммита нет.

Chrome https://viar13.loc/filament/menu-items: net::ERR_CONNECTION_REFUSED. Визуальная UAT новых полей остаётся открытой (нового снимка UI нет). ADM-AUD-10F: код/локальные проверки выполнены, браузерная UAT IN PROGRESS. Общий ADM-AUD-10, browser UAT10C/10E остаются открытыми.

### ADM-AUD-10G — переводы формы пунктов меню: TODO, 2026-10-16
Найденное при сверке отличие: оригинальная форма builder хранит title_i18n всех языков вместе, общие поля color/link/icon/target остаются на экране; новая форма пока редактирует перевод отдельно, при смене языка перечитывает запись и скрывает общие поля, создание переводов требует сначала сохранить пункт. Следующий точный шаг: черновик title для всех языков + постоянно видимые общие поля, одно транзакционное сохранение создания/редактирования/переводов, без потери введённого при смене языка, с теми же разрешениями и cache invalidation. Сначала сверить prepareMenuTranslations и клиентский language-selector; записи только SQLite. Это отдельная незакрытая часть parity, не считать все функции меню перенесёнными.

### ADM-AUD-10G — общий черновик переводов меню: IN PROGRESS, 2026-10-16
Оригинальный prepareMenuTranslations берёт базовый title из title_i18n[default] и сохраняет все переводы вместе с общими полями. Перенести черновики title между языками без повторной загрузки из DB; разрешить языки при создании; общие поля не скрывать. Проверять базовый заголовок и все черновики до транзакции, неизвестные locale не принимать. Создание id/общих полей/переводов — атомарно; при SQL failure не оставлять itemId несохранённой строки. Проверки локально на SQLite, working DB не менять.

### ADM-AUD-10G — переводы пунктов меню: код/локальные проверки выполнены, 2026-10-16
Сверены оригинальные prepareMenuTranslations, Translatable::setAttributeTranslations и language-selector. Исходник сохраняет базовый title из default locale, переводы вместе с общими полями, пустые значения (PHP empty, включая '0') пропускает, а не удаляет предыдущий перевод. Новая форма теперь хранит titleTranslations по разрешённым языкам, переключает только data.title без перечитывания DB и без form fill/reset. Общие URL/route/parameters/color/icon/target/parent/order/status остаются на форме во всех языках. Переводы доступны при создании до появления id; редактор существующей записи начинается с базового языка и загружает все настроенные переводы. Отмена очищает черновик без записи.

Перед save проверяются право add/edit, язык, вся карта переводов (без чужих locale), required default title и длины всех переводов, включая невидимые сейчас. Ошибки других языков выведены под формой с названием языка. Создание/изменение общих полей и всех непустых переводов выполняется одной транзакцией. itemId новой записи присваивается только после commit: после SQL failure можно повторить создание с тем же черновиком и add-only permission. Cache очищается после успешного commit. Пустой перевод теперь сохраняет прежнюю запись по оригиналу (прежняя реализация удаляла её); переводы вне настроенного списка остаются нетронутыми.

Проверка: targeted menu gate 15 тестов / 201 assertions PASS, 6.120s, 98MB, exit0; php -l VoyagerMenuItems и git diff --check PASS. Новые четыре теста покрывают смену EN/RU/LV с сохранением общего черновика, создание и последующее редактирование переводов одним save, обязательный базовый заголовок при другой активной локали, длину скрытого перевода, foreign locale, отмену без записи, SQL failure создания с rollback/cache preservation/id=NULL/retry, пустой перевод как skip, сохранность legacy locale, SQL failure изменения с откатом title/color/translations и успешным retry. Прежние 11 menu тестов включены. Все записи только SQLite; рабочие menus/translations/картинки не менялись, миграций и коммита нет.

Chrome https://viar13.loc/filament/menu-items снова вернул net::ERR_CONNECTION_REFUSED; визуальная проверка переключения языков не состоялась, снимок нового UI не получен. ADM-AUD-10G: код/локальные проверки выполнены, браузерная UAT IN PROGRESS. Общий ADM-AUD-10 остаётся IN PROGRESS. Следующая точная проверка при доступном сервере: открыть создание, ввести безопасный временный черновик EN/RU, проверить сохранность URL/цвета при переключении и отменить без save; затем сверить общий settings editor10E и DnD/touch10C. Ранее открытые визуальные проверки10F и отсутствующих типов10E также сохраняются.

### ADM-AUD-10H — удаление пункта с детьми: IN PROGRESS, 2026-10-16
Сверены original delete_menu, app/base MenuItem::boot и миграция2016_05_19: original удаляет только выбранную строку; parent_id не имеет FK/cascade, дочерние строки и их переводы сохраняются. Новая leaf-only проверка422 — подтверждённое отличие. Убрать запрет, сохранить исходную семантику «только выбранный пункт»; существующий menuTree fallback показывает оставшиеся orphan children на верхнем уровне, не переписывая parent_id. Обозначить это в подтверждении вместо native confirm. Удаление выбранного пункта/его переводов — транзакция и cache invalidation; дети/другие меню неизменны. Проверки только SQLite, реальное удаление в браузере запрещено текущим режимом.

### ADM-AUD-10H — удаление родительского пункта: код/локальные проверки выполнены, 2026-10-16
Подтверждено original delete_menu, app/base MenuItem::boot, migration2016_05_19: каскад есть только menus.id->menu_items.menu_id; parent_id не FK. Удаление пункта не удаляет детей и не переназначает parent_id. Снята новая leaf-only проверка422, выбранная строка проверяется/блокируется в транзакции до удаления её переводов. Дети и их переводы не меняются. Уже существующий menuTree fallback показывает детей с отсутствующим родителем на верхнем уровне, сохраняя вложенных в них потомков; это намеренное отличие представления от старого дерева, которое могло скрыть orphan rows, данные соответствуют оригинальной операции.

Native wire:confirm заменён Filament modal как исходный Voyager Bootstrap modal. Подтверждение содержит заголовок выбранной записи и сообщение о сохранении детей. Открытие/подтверждение проверяют delete_menus и принадлежность меню; pending id locked, смена меню/отмена/закрытие modal сбрасывают его. Cancel не пишет DB. Cache удаляется после успешной транзакции; при SQL failure выбранная строка/переводы/cache сохраняются. Удаление редактируемого пункта сбрасывает его черновик.

Targeted menu gate: 17 тестов / 227 assertions PASS, 6.665s, 100MB, exit0. Два новых теста покрывают запрос/отмену/подтверждение, детей/внуков/переводы/parent_id, видимость orphan branch, cache, SQL failure rollback после удаления переводов, foreign-menu запрет, сброс pending при смене меню, запрет confirm без pending и без разрешений. Прежние15 menu tests включены. php -l VoyagerMenuItems и git diff --check PASS. Записи только SQLite; рабочая база/меню/изображения не менялись, коммит не создан.

Итог ADM-AUD-10 на этом шаге: A/B/D локально проверены; C дерево/reorder, E типы/общий save, F поля, G переводы, H удаление реализованы и проверены на SQLite, но browser UAT не завершена. Верхний список актуальных статусов в docs/admin_menu_settings_audit_2026-10-15.md обновлён; старые хронологические записи сохранены. Последняя попытка Chrome в предыдущем шаге получила ERR_CONNECTION_REFUSED; текущий UI не объявляется визуально проверенным. Следующее: при доступном локальном сервере выполнить read-only/open/cancel проверку общего settings editor, переводов/полей и modal удаления; для DnD использовать только согласованную изолированную среду. Реальное удаление/сохранение рабочей базы не выполнять.

### ADM-AUD-10I — rollback вложений rich settings: IN PROGRESS, 2026-10-16
Новый isolated regression test подтвердил orphan inline image после SQL failure индивидуального settings save: rich editor сохраняет attachment в beforeStateDehydrated при form->getState, до DB update. Первый запуск FAIL (1 test/7 assertions): fake public содержит лишний settings/October2026/*.png при неизменном DB value. Исправить оба save режима: отслеживать только новые rich uploads, включить getState в cleanup boundary, восстановить raw документ/временные UUID при ошибке, после commit очистить временные uploads. Старые картинки/файлы не удалять. ТолькоSQLite/fake storage.

### ADM-AUD-10I — rollback вложений rich settings: код/локальные проверки выполнены, 2026-10-16
Regression воспроизведён до исправления: 1 test/7 assertions FAIL, после SQL failure в fake public оставался settings/October2026/*.png. Filament RichEditor сохраняет inline attachments в beforeStateDehydrated/form->getState и меняет UUID документа на постоянный путь. Это происходило вне прежнего списка image/file replacement cleanup; в bulk getState также находился перед try.

Добавлен settings-only callback сохранения rich attachments через штатный RichEditor API, без изменения shared BreadRichEditor/VoyagerBread. Каждый новый UUID-путь регистрируется до записи; прежние файлы не затрагиваются. Оба save режима сохраняют raw draft до getState, включают getState в try/cleanup, при ошибке удаляют новые inline копии и восстанавливают исходный документ с временными UUID. Временный upload сохраняется для retry; после успешного DB commit временные файлы/attachment-map очищаются. Обычные image/file replacements используют прежний cleanup. Визуальные компоненты/toolbar не менялись. Это исправление rollback адаптера Filament, не новая функция поверх Voyager; исходный тип rich_text_box по-прежнему сохраняет HTML с inline images.

Focused regression теперь PASS для single и bulk: 1 test/22 assertions. Совместный targeted gate settings + прежний pending-rich-source guard: 13 tests / 190 assertions PASS, 5.632s, 104MB, exit0. Проверены оба SQL failure/retry сценария, точное восстановление raw document, unchanged DB value, отсутствие новых orphan copies, успешное сохранение img и сохранность ранее существующих fake files. php -l VoyagerSettings и git diff --check PASS. Все записи SQLite/fake storage; рабочая DB/картинки не менялись; миграций/коммита нет.

ADM-AUD-10I: локальное исправление и regression gate выполнены. Общий ADM-AUD-10 IN PROGRESS: browser UAT10C/10E/10F/10G/10H остаётся открытой из-за последнего ERR_CONNECTION_REFUSED. Текущий шаг не изменяет визуальную форму и не объявляет её принятой. Следующий точный шаг — при доступном сервере завершить read-only/open/cancel визуальную сверку меню/настроек; записи/удаление в рабочем браузере не выполнять. После этого сверить актуальные открытые пункты общего ADM-AUD backlog, не возвращая уже закрытые исторические TODO.

### ADM-AUD-10E — HTML action нескольких rich полей: IN PROGRESS, 2026-10-16
Осталась конкретная проверка общей формы: вызов breadSource на одном из двух rich fields, изолированное переключение source mode, сохранность второго редактора/соседнего text field, exact HTML save. Проверить также запрет несуществующего/нередактируемого rich field. Chrome повторно подтвердил ERR_CONNECTION_REFUSED на /filament/voyager-settings; browser UAT этим шагом не заменяется и остаётся открыта. Автоматические записи только SQLite.

### ADM-AUD-10E — HTML mode общей/индивидуальной формы: исправление проверено, 2026-10-16
Проверен breadSource action на одном из двух rich settings с соседним text. Найдено реальное расхождение: richSources переключался, но cachedSchemas[form] в том же запросе оставлял старый RichEditor вместо CodeEditor. После установки source-флага теперь сбрасывается только cached form schema; raw HTML, соседние rich/text drafts сохраняются. Исправлены оба режима (single/bulk), shared BreadRichEditor и generic BREAD не менялись.

Regression проверяет реальный вызов Filament action, изолированные source flags, CodeEditor/RichEditor component classes после action, точный HTML, сохранность соседних полей, запрет перехода для text/несуществующего id и single action/save. Начальная проверка после корректировки сравнения внутреннего RichEditor document выявила неверный класс компонента (FAIL), после исправления PASS. Итоговый targeted gate: 14 tests / 217 assertions PASS, 5.840s, 104MB, exit0; git diff --check PASS. ТолькоSQLite, working DB/изображения не менялись; коммита нет.

Браузерный блокер повторно проверен именно этим шагом: Chrome https://viar13.loc/filament/voyager-settings вернул net::ERR_CONNECTION_REFUSED. Ранее то же подтверждалось на menu-items и voyager-settings в нескольких последовательных шагах. Нет живого процесса/операции, который ожидался бы в рамках этой проверки; повторное ожидание без запуска сервера не выполнит UAT. Текущая браузерная приёмка ADM-AUD-10 BLOCKED до запуска локального сайта пользователем. Кодовые результаты A-I сохраняются, общий этап не DONE. Для продолжения: запустить OpenServer/локальную новую админку, затем open/cancel сверка settings tabs/source/image previews и menu fields/translations/delete modal с оригиналом; реальных save/delete/upload в рабочие данные не выполнять. Остальные исторические UAT ADM-AUD-09 также не объявляются закрытыми.

ADM-AUD-10, повторная диагностика стенда 2026-10-16: viar13.loc разрешается в127.127.126.19; Get-NetTCPConnection не обнаружил слушателей на80/443. Это подтверждает отсутствие локального HTTP/HTTPS listener, а не ошибку формы Filament. Настройки сервера/hosts/DB не менялись. Для browser UAT требуется запустить локальный webserver; этап не завершён.

### ADM-AUD-10 — browser UAT возобновлена, 2026-10-16
Пользователь запустил локальный сервер; Chrome открыл обе settings страницы, прежний connection-refused blocker снят. Общая форма новой админки открывается, Site активна, текущее изображение complete=true/height160, есть Save/Cancel. Найдено browser/source отличие: original tabs Site/Admin/Соц.сети/Модульные картины, новые были alphabetic Admin/Site/Модульные/Соц.сети. Original controller orderBy(order) группирует по первому появлению настройки, не сортирует group по алфавиту. Исправить оба режима просмотра/общей формы без DB writes, проверить порядок повторно. Затем menu drafts и confirmation/cancel. Общий ADM-AUD-10 IN PROGRESS, не DONE.

ADM-AUD-10 browser UAT, 2026-10-16: EN/RU draft switching выявил статическую label/required схемы: выбран RU, но label EN*. Исправлено closures для текущей locale; повторный Chrome PASS: RU label без required, EN label required, оба черновика и общий URL сохранены; route показывает JSON/скрывает URL, отмена без save. Открыт modal удаления Catalogue of styles с пояснением о детях, отменён, DOM tree14 как до открытия. Срабатывание Livewire занимает дольше короткого tool wait; отмена завершилась, подтверждение удаления не нажималось.

В общем settings editor найден JS SyntaxError: x-effect attribute обрывался на неэкранированных двойных кавычках вокруг activeGroup. В выражении заменены кавычки на безопасные одинарные; regression DOMDocument проверяет, что HTML parsing сохраняет выражение целиком. Порядок групп изменён по original global order; pending повторная проверка браузером. Targeted3/42PASS; полный gate будет записан после UAT. Рабочие DB/settings/menu rows не сохранялись/не удалялись.

### ADM-AUD-10 — Chrome UAT меню/настроек после запуска сервера, 2026-10-16
Блокер connection-refused снят: открыты https://viar13.loc/filament/voyager-settings и original https://viarcanvas.loc/admin/settings, новые/оригинальные menus/2/builder. Этап снова IN PROGRESS. Рабочие формы проверялись только открытием, изменением несохранённых черновиков, переключением и отменой; save/delete/upload/reorder в рабочую DB не выполнялись.

Исправлены три подтверждённых Chrome-расхождения:
1. Порядок settings groups: original controller сортирует settings по order и группирует по первому появлению. Оба новых представления теперь используют тот же порядок (пустая группа первой), id используется как детерминированный tie-break. В Chrome теперь Site/Admin/Соц.сети/Модульные картины, как original.
2. Menu title label/required были статическими в закешированной schema при changeLocale. Label и required теперь closures по текущей locale. Chrome create draft: EN='Audit draft EN', RU='Черновик проверки RU', URL='/audit-draft-only'; EN->RU->EN->RU сохраняет оба заголовка и URL, RU label без *, EN с *. Общее поле URL остаётся доступно; route mode показывает маршрут/JSON и скрывает URL. Создание отменено, не сохранено.
3. Settings x-effect был усечён в HTML из-за двойных кавычек activeGroup; после устранения синтаксиса найдена вторая причина незапоминания — карта использовала id, а Filament tab использует relative key. Tab получил явный безопасный key=id с isInheritable=false; выражение использует одинарные кавычки. Chrome: после выбора «Модульные картины» и cancel список вернулся с этой группой active=true; повторное открытие и переключение Site работают. Новых site SyntaxError после исправленного открытия не наблюдалось; два старых SyntaxError остались в историческом console log, ошибка ethereum относится к Chrome extension.

Другие визуальные результаты: общий editor имеет все4вкладки и save/cancel; Site image complete=true/height160. New menu tree содержит14узлов, original14. Original New Menu Item modal визуально/DOM сверена: языки EN/RU/LV/EE/LT/DE/PL, title, link type, URL/route/parameters, icon, color, target; отменена без submit. Новый delete modal Catalogue of styles содержит предупреждение о сохранении детей, показан и отменён, tree count14 после отмены. Короткий tool wait сначала не дождался завершения Livewire cancel; последующая проверка подтвердила hidden modal и сохранность дерева, код закрытия менять не потребовалось.

Evidence screenshots: storage/app/audit/menu-drafts-20261016.png (подтверждён RU), settings-joint-20261016.png (исправленный порядок), menu-delete-modal-20261016.png, original-menu-create-20261016.png. Просмотрены сохранённые новые screenshots. Полные settings values/code не выводились в tool text; screenshots показывают публичные Site данные.

Тесты: общий targeted settings/menu gate 32 tests / 454 assertions PASS, 14.610s,108MB,exit0; git diff --check PASS. Добавлен regression порядка groups и полного x-effect после HTML parsing, соответствия tab key/id; дополнен тест актуальных RU label/required. Записи тестов SQLite/fake storage; рабочие menu/settings/image paths не менялись. Миграций и коммита нет.

Осталось: browser drag/drop/touch10C с записью только в согласованной изолированной среде; отсутствующие в рабочей DB rich/radio/select/file types10E проверены автоматически, но не визуально на реальных данных. HTML/SQL failure/media rollback10I подтверждены локальными тестами, не реальными browser uploads. Поэтому весь ADM-AUD-10 и общий аудит админки не DONE. Следующий шаг — сверить текущий общий ADM-AUD backlog и выбрать следующий незакрытый функциональный пункт; эти открытые UAT не терять и не возвращать исторические уже закрытые задачи.

### ADM-AUD-11-CACHE — служебные операции кеша: IN PROGRESS, 2026-10-16
Актуальный исходный аудит ADM-AUD-11 включает cache/Compass/sitemap; прежние DB/simulator работы не закрывают cache UI. Подтверждены original Admin/VoyagerAdminController::cache_clear=>cache:clear и cache_set=>config:cache,route:cache. В текущем runtime методы сохранены, но Filament page/menu target отсутствуют. Добавить страницу с этими фиксированными операциями, существующим browse_compass (по original служебному Compass) и проверкой права при каждом вызове; не создавать новые права/миграции. Произвольные команды/аргументы не принимать. В случае второй ошибки сообщить частичное выполнение. GET меню ведёт на страницу, не запускает очистку. Проверки через Artisan mock, рабочий cache/config/route не очищать и не собирать. Compass/logs и sitemap требуют отдельной сверки, не считать их автоматически перенесёнными.

### ADM-AUD-11-CACHE — страница служебных операций кеша: локально проверено, 2026-10-16
Добавлена CacheTools (/filament/cache-tools) и Blade; существующий legacy пункт «Очистка кеша» связан с ней через BreadMenuTree. Original cache_clear/cache_set перечитаны: ровно cache:clear либо config:cache, затем route:cache; никаких optimize:clear/произвольных команд. GET только открывает страницу, действие запускается кнопкой. Используется существующее browse_compass с проверкой в mount/hydrate/run. Ошибка показывает неуспешную команду и уже выполненную часть; секретные output/message не выводятся и не логируются. Новых permissions/schema/migrations нет.

Targeted PHPUnit 5tests/36assertionsPASS,1.216s88MBexit0: Artisan mock, GET read-only, запрет прав/чужих операций, порядок команд, partial failure/exception и отзыв разрешения. Первое тестовое сочетание двух HTTP users получило302 из-за AuthenticateSession старого password_hash; HTTP allowed GET вынесен в отдельный fixture test, защита не ослаблялась. php-l/diffcheckPASS. Chrome открыл страницу: обе кнопки, legacy пункт «Инструменты → Очистка кеша» и breadcrumbs корректны; screenshot storage/app/audit/cache-tools-20261016.png сохранён/просмотрен. Реальные команды не запускались, рабочие cache/config/routes/DB/images не менялись; коммита нет.

Подробный отчёт: docs/admin_service_tools_audit_2026-10-16.md. ADM-AUD-11-CACHE реализован/локально проверен, реальный рабочий execution не проверялся. Общий ADM-AUD-11 IN PROGRESS; следующий пункт ADM-AUD-11-COMPASS — source inventory вкладок и применимости, затем перенос необходимых оригинальных функций. Старый admin/sitemap return back — не выдавать за рабочую генерацию. Открытые UAT ADM-AUD-09/10 сохранены.

### ADM-AUD-11-COMPASS-A — ресурсы и каталог иконок: IN PROGRESS, 2026-10-16
Сверен original compass/index: три вкладки resources/commands/logs; отдельного phpinfo нет. Resources содержит два Voyager links и fonts catalogue412 классов. Общая защита original: browse_compass и (local OR voyager.compass_in_production). В текущем config flag=true уже существовал, не менять. Перенести Resources с теми же fonts/classes, локальными CSS/font assets и license, поиском и ссылкой из menu icon field. Не менять legacy icon values/DB и текущий Heroicon mapping. Commands/logs ещё не реализованы, не выдавать страницу ресурсов за полный Compass. Записи/Artisan/log deletion не выполнять.

### ADM-AUD-11-COMPASS-A — ресурсы и иконки: локально проверено, 2026-10-16
Завершена первая часть Compass: /filament/compass#fonts, original resources links, каталог классов и символьных значений с поиском, локальные original CSS/fonts/eot/woff/ttf/svg и MIT license. Прежняя оценка412 была числом input для двух представлений; уникальных классов206. Original class inputs и copied CSS selectors сравнены:206/206, различий нет. Никаких icon_class/menu DB значений не меняли; существующий Heroicon resolver сохранён.

Page соблюдает original browse_compass + (local OR compass_in_production); существующий config flag=true не менялся. Menu icon field получил доступную по тем же правам ссылку «Каталог иконок». В Chrome original Compass подтвердил tabs Resources/Commands/Logs, phpinfo отсутствует. New catalogue:206inputs, font loaded=true, pseudo-element voyager-home family=voyager; поискvoyager-home→1. Есть readonly класс и символ, для символа выставлен original font-family, чтобы избежать tofu. Ссылка menu helper→/filament/compass#fonts, открытая create menu форма отменена без save.

Targeted gate4tests34assertionsPASS,1.821s88MBexit0:206catalogue/filter/fonts/license, symbol values, permissions/local/production flag и menu hint visibility, прежний menu translation scenario. php-l/diffcheckPASS. Screenshot storage/app/audit/compass-icons-20261016.png (итоговое обновление снимка после font-family остаётся следующим шагом). Рабочая DB/images/logs/cache не менялись, команды не запускались, коммита нет. Общий Compass IN PROGRESS: команды и логи не перенесены первой частью. Отдельной phpinfo-задачи не создавать.

### ADM-AUD-11-COMPASS-B — чтение логов: IN PROGRESS, 2026-10-16
Сверены original LogViewer и logs.blade: *.log в storage/logs, reverse filename order, Laravel header/date/context/level/text + multiline stack, MAX_FILE_SIZE50MiB, выбор/поиск/просмотр, download/delete/delete-all. Начать с reader/файлов/парсинга и pagination на artificial fixtures, без чтения содержимого рабочих журналов. Использовать потоковое чтение с ограничением50MiB, filenames только внутри настроенного logs root, произвольные absolute/traversal/symlink escape не принимать. UI/download/delete останутся явными следующими пунктами до реализации и проверки; их не считать DONE.

### ADM-AUD-11-COMPASS-B — чтение/просмотр журналов: реализовано, общий этап IN PROGRESS, 2026-10-16
Добавлен CompassLogReader: список top-level *.log с reverse filename order, checked canonical path в logs root (абсолютные/traversal/не-listed файлы не принимаются), исходный50MiB limit, Laravel date/context/девять уровней/text/многострочный stack и исходная строка. Два потоковых прохода по одному открытому handle считают matches и берут только нужную страницу; порядок физических записей reverse, как исходный LogViewer. Поиск case-insensitive по message/stack/date/context/level, фильтр уровня, размеры10/25/50/100. Некорректные фильтры/большой файл получают понятную ошибку. Полный файл не загружается в array всех записей; прежние bytes не меняются.

Compass получил вкладки Ресурсы/Логи, выбор файла, поиск, уровень, pagination и details stack; сообщения/stack экранируются Blade. Возврат на ресурсы сохраняет введённый icon search. browse_compass/environment gate сохраняется и повторяется при hydrate; helper methods не запускают команды и не удаляют файлы. Рабочие log filenames/content не выводились в tool text, снимки логов не делались. Chrome проверил доступность файла/поиска/уровня и заголовки таблицы (Уровень/Контекст/Дата/Содержание), затем возврат на Ресурсы. Это проверка интерфейса; функциональный разбор/поиск проверен на synthetic logs.

Тесты reader:6cases/35assertions,5PASS+1SKIP,0.843s72MBexit0. SKIP — текущая Windows-сессия не разрешила symlink creation; реальный symlink escape этим не объявляется проверенным. Остальное: reverse order/page boundaries/stale page clamp, byte preservation, все9уровней/CRLF/Unicode/stacksearch, пути/не-log файлы, empty/invalidfilters, sparse file50MiB+1 rejection. Feature Compass resources/menuhint/log UI:5tests43assertionsPASS,1.395s88MBexit0. Synthetic UI проверяет escaping script-like text, stack, pages/filters, empty files, forbidden path, отсутствие writes и revoked permission. php-l reader/Compass и diffcheckPASS.

Обновлён безопасный screenshot storage/app/audit/compass-resources-logs-20261016.png: обе вкладки, поискvoyager-home→1, класс и символ с familyvoyager. Снимок просмотрен, символ домика отображается корректно; рабочие сообщения журналов на него не попали. Этим также закрыта фиксация финального визуального состояния11-COMPASS-A. Working DB/menu/images/cache/log files не изменялись, Artisan не вызывался, коммита нет.

Общий ADM-AUD-11-COMPASS IN PROGRESS. Следующий точный шаг11-COMPASS-B: скачивание выбранного журнала и modal deletion выбранного/всех файлов по original, с проверкой пути/прав/окружения, fixtures и без удаления рабочих журналов; затем11-COMPASS-C — вкладка команд. Отсутствующие download/delete/commands не считаются выполненными. Открытые UAT09/10 сохраняются.

### ADM-AUD-11-COMPASS-B — download/delete: IN PROGRESS, 2026-10-16
Source исходных logs.blade/Compass controller подтверждён ранее: selected download, selected delete, delete-all с подтверждением. Добавить streaming BinaryFileResponse через отдельный GET внутри middleware Filament (не Livewire-base64 для больших логов), с повторным browse_compass/environment/path gate. Delete: locked snapshot имён из root на открытии modal, prevalidate весь список, unlink только выбранных lexical filenames, частичный отказ сообщить без ложного общего успеха. Новые файлы, появившиеся после подтверждаемого snapshot, не удалять. Все delete/download тесты synthetic roots, рабочие логи не удалять/не скачивать.

### ADM-AUD-11-COMPASS-B — download/delete журналов: реализовано и локально проверено, 2026-10-16
Добавлен GET /filament/compass/download?file=<basename> внутри маршрутов Page/Filament: middleware панели, повторный browse_compass + local/compass_in_production gate, string/max255 query validation и checked logs-root path. BinaryFileResponse передаёт файл без Livewire base64, с private/no-store/nosniff; bytes не преобразуются. Ответы200 download,403 no permission/environment,302 guest login,422 invalid/missing/unlisted path. Preview limit50MiB не ограничивает download, как original. Download files в браузере не запускался; HTTP fixtures подтвердили exact bytes/name/type и файл50MiB+1 без загрузки в Base64.

Удаление выбранного/всех журналов: Filament modal с locked snapshot имён, повторная проверка права/окружения/пути на confirm, prevalidation всего списка до unlink. Unlink использует lexical выбранное имя в root (не resolved symlink target); новые журналы после открытия modal не включаются. Успех сбрасывает страницу и выбирает оставшийся файл; частичный файловый отказ оставляет failed names в modal для retry и показывает выполненную/невыполненную часть, не выдаёт ложный общий успех. При stale filename до операции файлы не удаляются, требуется переоткрыть выбор. Cancel/закрытие очищают snapshot без записи. Исходный original delete-all тоже не транзакционный; уже удалённые файлы при файловом отказе не объявляются восстановленными.

Feature gate Compass:12tests83assertionsPASS,2.110s94MBexit0. Покрыты protected HTTP download/guest/auth/path/query validation/exact bytes/large file, selected/all snapshots/cancel/late new file/stale file/revoked rights, partial failure с synthetic filesystem override, прежние resources/icons/menuhint/logfilters. Reader gate8cases42assertions:7PASS+1SKIP,0.907s74MBexit0. SKIP по-прежнему Windows symlink creation permission; symlink runtime не заявляется проверенным. php-l трёх классов и diffcheckPASS.

Chrome: видны download/selected-delete/delete-all, modal открытия выбранного файла и отмена проверены, после отмены кнопка cancel hidden. Confirm delete не нажимался, download link не запускался. Для снимка включён искусственный no-match search и подтверждено отсутствие message cells, поэтому реальные сообщения/stack не экспортировались. Screenshot storage/app/audit/compass-log-delete-confirm-20261016.png сохранён и просмотрен; API clip screenshot дважды получил CDP timeout, безопасный full viewport снимок после фильтра сработал. Затем modal отменён, вкладка переключена на ресурсы. Рабочие logs/DB/images/cache не изменялись; миграций/коммита нет.

Статус: ADM-AUD-11-COMPASS-B reading/download/delete локально проверены; реальное удаление/скачивание рабочих журналов не выполнялось. Общий Compass IN PROGRESS. Следующий точный шаг ADM-AUD-11-COMPASS-C — исходный Artisan commands catalogue/args/output и перенос вкладки с теми же rights/environment gate, тесты выполнения через mock; никаких реальных mutating commands во время проверки. Открытые UAT09/10 сохраняются.

### ADM-AUD-11-COMPASS-C — Artisan commands: IN PROGRESS, 2026-10-16
Original controller и commands.blade: каталог доступных команд текущего Artisan, описание, аргументы выбранной команды, call(command+' '+args), output/error message, скрытие output. Перенести на Compass с действующим browse_compass/environment gate при каждом вызове; сервер проверяет выбранное имя по текущему registry, не принимает произвольный shell. Аргументы передаются Symfony Console через Artisan::call, shell не запускается. Каталог текущего Laravel13, а не жёсткий список старых Voyager-команд. Ошибки/ненулевой exit не показывать как успех, output HTML-экранировать. Execution tests только Artisan mock; реальные mutating commands не выполнять в Chrome.

### ADM-AUD-11-COMPASS-C — команды Artisan: реализовано и локально проверено, 2026-10-16
Перенесена исходная вкладка Commands: каталог зарегистрированных команд текущего runtime с описаниями, поиск, выбор, аргументы, выполнение через Artisan::call, вывод/код завершения и скрытие результата. Скрытые команды исключены, алиасы не дублируются. Аргументы сохраняются отдельными черновиками команд; перед выполнением повторяются browse_compass/environment gate и проверка зарегистрированного имени. Вывод экранируется; ненулевой exit code и Throwable отмечаются ошибкой. Контракт сверялся с original VoyagerCompassController и compass/includes/commands.blade.php; shell не используется.
Chrome выявил буквальный @js($name) в wire:click компонента кнопки. Исправлено через Blade expression Js::from; добавлена регрессия именно rendered HTML/DOM attributes, поскольку прямые Livewire method calls обходили дефект кнопки. Повторный Chrome: selectCommand('about'), поле «Дополнительные аргументы» и кнопка «Выполнить команду» видимы. Поиск работает по имени и описанию. Команда в браузере НЕ выполнялась. Снимок storage/app/audit/compass-commands-20261016.png обновлён отдельным capture после завершения Livewire и просмотрен: форма/таблица/отступы отображаются корректно.
Feature gate --filter=compass: 16 tests / 131 assertions PASS, 2.991s, 96MB. Покрыты catalogue/filter/hidden/aliases/drafts, rendered click expressions, validation, quoted arguments, success/nonzero/exception/escaping, permission/environment; реальные исполнения заменены Artisan mocks. php-l Compass и git diff --check PASS (есть только предупреждения Git о LF/CRLF). Рабочие DB/images/logs/cache не изменялись, реальных Artisan команд/миграций и коммита нет.
Статус C: реализация и разрешённые проверки выполнены; сквозное исполнение реальных команд не заявляется проверенным. Общий ADM-AUD-11 остаётся IN PROGRESS. Следующий точный шаг — сверить оставшиеся служебные инструменты и актуальный backlog с оригиналом; открытые browser DnD/touch и отсутствующие типы настроек ADM-AUD-10, media UAT ADM-AUD-09 сохраняются.

### ADM-AUD-12 — перенос длинных строк в таблицах: IN PROGRESS, 2026-10-16
Подтверждён открытый пункт исходного аудита. Generic td использует truncate/nowrap, SEO page_url wrap не задаёт перенос непрерывного URL. Добавить ограниченный перенос внутри ячейки без изменения данных, href и текущего preview; проверить Chrome на существующих строках, без записи.

### ADM-AUD-12 — перенос длинных строк: исправлено, desktop проверен, 2026-10-16
Generic value td больше не использует truncate/nowrap, добавлен white-space:normal и overflow-wrap:anywhere с прежним max-width240px. Сохранены preview limits, tooltip, экранирование, href, media previews и sticky actions. SEO page_url получил отдельный класс с max-width320px и overflow-wrap:anywhere: прежний wrap не гарантировал перенос непрерывного URL. Данные/ссылки/действия не менялись.
Chrome /filament/seo-meta-suggestions: вычисленные white-space normal/overflow-wrap anywhere, 10 проверенных ячеек без horizontal overflow; на gallery-items?type=2 проверено625 value cells, все normal/anywhere, переполнений0. Снимки storage/app/audit/table-url-seo-20261016.png и table-url-generic-20261016.png просмотрены: текст и slug переносятся, действия остаются справа. Проверены текущие данные desktop; synthetic сверхдлинный URL и mobile отдельно не проверены. Автотесты для CSS не добавлялись. php-l SEO resource PASS. Исправлено замечание diffcheck об empty line EOF в плане. Рабочие DB/files/images не менялись, коммита нет.
Служебный sitemap повторно сверен: routes/admin.php original return back(), генератор закомментирован; перенос действующей генерации не требуется. Общий аудит IN PROGRESS, остаются ранее зафиксированные изолированные browser UAT и advanced DB ограничения; следующий шаг — сверка актуальной общей матрицы без возврата закрытых исторических TODO.

### ADM-AUD-08 / ADM-AUD-01 / ADM-AUD-11-DB — Blade обработчики кнопок: IN PROGRESS, 2026-10-16
После дефекта Compass проверены аналогичные @js внутри x-filament компонентов. Три regression tests rendered HTML воспроизвели literal @js: inline group action, catalog delete BREAD, role permission group selection (3 tests/9 assertions/3 failures до исправления). Дополнительно тот же шаблон у отмены inline field. Исправить сериализацию атрибутов через Blade expression Js::from и повторить существующие сценарии на SQLite; рабочие действия в Chrome не сохранять.

### ADM-AUD-08 / ADM-AUD-01 / ADM-AUD-11-DB — исправление атрибутов кнопок: DONE (код/SQLite regression), 2026-10-16
Обнаружены literal @js expressions в атрибутах x-filament компонентов: отмена inline text/number, editInlineGroup, prepareBreadDeletion, group selectRolePermissions true/false. Такие выражения не компилировались внутри компонентных атрибутов, поэтому прямые вызовы методов тестами проходили, а browser click не мог разобрать JS. Заменено на экранируемый Blade expression Illuminate Support Js::from. Обычные HTML-атрибуты с рабочим @js не менялись. Бизнес-правила и действия оригинала сохранены.
До исправления новые проверки HTML воспроизвели 3 failures/3tests (9assertions). После исправления targeted gate inline_group|inline_text_number|role_permission_group_selection|database_catalog_bread_deletion:7tests138assertionsPASS,4.065s94MBexit0. Проверены отсутствие literal @js в соответствующих формах, DOM attribute отмены value='Base', прежние save/cancel/permissions/metadata/model-veto и rollback удаления BREAD с сохранением физических записей. Записи только SQLite, действий в рабочем Chrome не выполняли. Browser click этих конкретных controls отдельно не заявляется проверенным. Рабочие права/DB/images не менялись, коммита нет.
Общий ADM-AUD аудит IN PROGRESS. Следующий точный шаг — разрешённая open/cancel browser проверка исправленных controls и mobile ADM-AUD-12; mutation UAT ADM-AUD-09/10 остаётся в изолированной среде.

### ADM-AUD-11-DB / ADM-AUD-01 — Chrome кнопки после исправления: PASS (open/draft/cancel), 2026-10-16
Каталог database-tables: поиск a_collage_advantage_screen, нажатие строки «Удалить BREAD» открыло modal с верным именем и input точного подтверждения. Никакого ввода подтверждения/финального удаления; отмена закрыла modal (input visible=false). Снимок storage/app/audit/bread-delete-button-20261016.png сохранён/просмотрен.
Роли: открыто создание без имени, group «Тестовые категории BREAD». До выбора счётчик0, после group select5 и checkedinputs5, после group clear0. Повторно открыта отфильтрованная группа; screenshot storage/app/audit/role-group-buttons-20261016.png просмотрен и показывает состояние после снятия (0, checkbox пустые; capture отстаёт от Livewire, не считать его снимком выбранных5). Роль отменена: Create button visible=true, role-toolbar count0. Сохранение/удаление не выполнялось, permissions в рабочей DB не менялись.
Подтверждены реальные клики исправленных component attributes DB deletion modal и role group selection; inline field/group runtime browser проверки остаются открытыми, SQLite регрессии7tests138assertions — последний gate. Нового кода/коммита в этом шаге нет. Следующее — mobile ADM-AUD-12 и проверка доступных inline controls без save; ранее записанные UAT09/10 сохраняются.

### ADM-AUD-12 — mobile 390px: IN PROGRESS, 2026-10-16
Chrome viewport390x844: document scrollWidth375<=390, generic scroll container293px/scrollWidth4732/overflowauto, value overflow0. Однако screenshot table-mobile-generic-20261016.png показывает слишком широкие sticky actions160px: в видимой области практически только selection/id/actions. Добавить mobile compact action grid2x2/84px с сохранением всех кнопок и sticky, затем повторить screenshot и SEO mobile. Рабочая DB не меняется.

### ADM-AUD-12 — mobile 390px: базовая проверка выполнена, 2026-10-16
На mobile generic sticky actions уменьшены160→84px, иконки grid2x2; desktop flex160px сохранён. Перенесён inline display flex в класс viar-bread-row-actions, без important. Chrome computed mobile cellWidth84/grid24px24px подтверждены. Region ArrowRight изменяет scrollLeft (8.6667), горизонтальная прокрутка доступна с клавиатуры. Таблица шире контейнера намеренно, данные доступны прокруткой; все колонки одновременно на390px не помещаются. Generic value overflow0. SEO mobile documentWidth375<=viewport390,10 URLcells overflowfalse/anywhere. Кнопки/фильтры формы в границах экрана. Реальный touch/swipe не выполнялся; очень длинный synthetic URL отдельно не проверен.
Снимки storage/app/audit/table-mobile-generic-20261016.png и table-mobile-seo-20261016.png просмотрены. SEO screenshot показывает верхний экран/фильтры, а не саму таблицу; URL geometry подтверждена DOM. Глобальный SA widget частично закрывает низ viewport как ранее draggable fixed control; его поведение в этом шаге не менялось. Viewport reset выполнен, рабочие DB/files/images не менялись. CSS правка не требует новых автотестов. Общий аудит IN PROGRESS; следующие открытые проверки — inline field/group browser open/cancel, synthetic longURL в изолированном browser fixture, физические media/touch UAT09/10. Коммита нет.

### ADM-AUD-08 — применимость inline UAT / общая матрица: audit выполнен, 2026-10-16
Повторный SELECT-only inventory текущей DB через BreadInlineEdit::enabled:1035 browse fields, inline_fields=[]; working metadata не менялась. Подтверждено отсутствие применимого реального раздела для inline button open/cancel. Текущие SQLite rendered/action tests остаются evidence кода; browser сценарий условный до original metadata/изолированного стенда. Не создавать искусственные настройки в рабочей DB ради проверки.
В docs/filament_functionality_audit_2026-10-08.md добавлен актуальный срез всех12 главных задач с состоянием реализации и отдельными границами UAT. Первоначальная матрица сохранена как история и явно подписана; её устаревшие «нет UI/routes» не использовать как текущие TODO. Наличие EmailSender/Clients/ALT bulk/revert и текущих служебных страниц сверено по исходникам, не выдаётся за новую сквозную приёмку. Общий аудит IN PROGRESS; реальные integrations/media writes и advanced DB условия остаются открытыми. Нового продуктового кода/коммита нет.

### ADM-AUD-11-DB — границы original advanced editor: source audit выполнен, 2026-10-16
Сверены original vendor/tcg/voyager/resources/views/tools/database/vue-components/database-table-editor.blade.php, database-column.blade.php и src/Database/Schema/{Table,Column}.php, DatabaseUpdater.php. UI имеет name/type/length/notnull/unsigned/autoincrement/index/default; FK rules и generated expression inputs отсутствуют. Table экспортирует foreignKeys/options в payload и передаёт в Doctrine — это сохранение metadata, не отдельный FK editor. Composite indexes вызывают no_composites_warning, getColumnsIndex содержит TODO advanced form; не объявлять несуществующий advanced editor обязательным переносом. Column::getExtra содержит TODO ON UPDATE.
Новая служба сохраняет неизменённые generated/multiple indexes и поддерживает native rename FK/composite в проверенных server/version границах. Но изменение/удаление FK columns, generated edits и часть composite operations блокируются до DDL. Эти guards остаются явным отличием/ограничением; отсутствие original input не доказывает эквивалентность backend ALTER. Общий DB advanced PARTIAL сохраняется, новые FK/generated UI не добавлялись без исходного сценария.
Targeted gate database_edit_guards_foreign|database_edit_preserves_generated|database_composite_column_rename|database_foreign_key_column_rename:4tests252assertionsPASS,0.894s82MBexit0. Это SQLite rollback/guards и compiled MySQL/MariaDB rename SQL, не реальный advanced ALTER. Рабочая DB/files не менялись, коммита нет. Общая матрица уточнена. Следующее — изолированная приёмка текущих форм/данных и открытые UAT09/10/12; не создавать отдельный произвольный schema designer вместо исходного Voyager.

### ADM-AUD-12 — synthetic long strings: generic browser fixture PASS, 2026-10-16
Сгенерирован статический HTML из настоящего VoyagerBread Blade через изолированный SQLite :memory: fixture (storage/app/audit/LongUrlVisualTest.php, вне tracked tests). Две проверки среды до вставок гарантируют sqlite/:memory:. Синтетические значения: continuous URL1014chars с существующим preview limit200 (+ellipsis), original filename964chars, href1000chars. Fixture test1/3assertionsPASS,0.632s78MB. Реальная DB не подключалась и не менялась.
Chrome открыл статический снимок на временном localhost server127.0.0.1:8765; никакого Livewire runtime/write endpoint в снимке нет. Desktop cell clientWidth=scrollWidth, URL preview204chars, filename964chars и полный href1000chars сохранены. При390x844: documentWidth375<=390, cells150/240/240 и scrollWidth каждой равен clientWidth; table789px внутри293px overflow container. Keyboard ArrowRight довёл scrollLeft до496, доступна длинная filename link без открытия/скачивания. Screenshot table-long-url-isolated-20261016.png просмотрен: длинное имя переносится в своей колонке. Это статическая fixture, не снимок рабочего UI: loading glyphs без Livewire не являются дефектом runtime. SEO synthetic rendering отдельно этим не проверяется; production SEO actual cells проверены предыдущим шагом.
Временный PHP server остановлен через session63710 Ctrl-C, terminal exit1 соответствует остановке. Chrome viewport reset выполнен. Product code в шаге не менялся, коммита нет. ADM-AUD-12 generic synthetic граница проверена; physical touch и отдельный SEO long-token остаются непроверенными. Общие UAT09/10 и advancedDB ограничения сохранены.

### ADM-AUD-08 / ADM-AUD-10 — isolated browser runtime: IN PROGRESS, 2026-10-16
Подготовить отдельный127.0.0.1 runtime с persistent SQLite fixture и fake public/session/storage в storage/app/audit. Инициализация только через testing SQLite :memory:→fixture export, synthetic admin и все9 settings types/inline metadata. Оригинальный код application используется без редактирования .env/config; runtime overrides до bootstrap запрещают рабочую DB/SMTP/queue/disk. Проверка browser open/draft/cancel и проверка fixture DB после отмены. Рабочие migrations/permissions/images не менять. Реальные provider actions исключены из маршрутов стенда.

### ADM-AUD-08 / ADM-AUD-10E — isolated Chrome open/draft/cancel: PASS, 2026-10-16
Создан persistent SQLite fixture из testing :memory: (seed1test3assertionsPASS,0.513s74MB) с synthetic admin, Pages inline text/group и всеми9 типами settings. Root fixture storage/app/audit/isolated-browser; отдельные public/tmp/session/views/log paths, SMTP array/cache array, restricted localhost router8766. Product .env/config/source не изменялись. Запуск стенда потребовал bind request до bootstrap, пустой glob_config fixture и разрешение фактического Livewire hashed resource prefix; это исправления вспомогательного стенда, не runtime дефекты продукта.
Chrome inline: edit field открыл textbox, введён черновик, cancel вернул исходный текст. editInlineGroup открыл dialog только с текстовой «Подпись», numeric count не редактируется по original; введён черновик и modal отменён. Screenshot inline-group-isolated-20261016.png просмотрен, показывает открытый dialog/исходную подпись (capture до прорисовки ввода), не сохранённое изменение.
Settings editAll: видны9 типов. Исходные text/textarea, rich strong content, CodeMirror JSON, checked checkbox, radio/select second, file link и loaded image height160 подтверждены DOM. Изменены только drafts text/textarea/rich/code/checkbox/radio/select; HTML action открыл актуальный rich draft, dialog отменён, затем общая форма отменена. Повторное открытие вернуло rich «Исходный текст», code audit:true и radio/select second. screenshot settings-html-isolated-20261016.png просмотрен. Инструмент fill в rich вставил draft перед старым текстом; полная замена выделенного rich текста этим не заявляется проверенной.
PDO query_only verification сравнил pages/settings с baseline.json и bytes synthetic PNG/PDF: pages_unchanged=true, settings_unchanged=true, image_unchanged=true, file_unchanged=true,exit0. Никаких save/upload/delete действий в этой browser UAT. PHP server session77528 остановлен Ctrl-C (exit1 остановки); asset paths/view changes остаются в ignored audit files, коммита нет.
Границы: закрыта ранее отсутствовавшая isolated browser open/draft/cancel приёмка inline и девяти types. Browser save/Enter/uploads, DnD/touch и provider operations остаются непроверенными; рабочие DB/images/permissions не менялись. Общая матрица обновлена, общий аудит IN PROGRESS. Следующее — конкретная изолированная приёмка save/Enter с synthetic SQLite либо remaining menu/media UAT; не возвращать открытие9types в TODO.

### ADM-AUD-08 / ADM-AUD-10E — isolated browser save: IN PROGRESS, 2026-10-16
Предыдущий runtime остановлен штатно; fixture DB baseline подтверждён. Возобновить только127.0.0.1:8766 с прежними SQLite/disk/session guards. Browser Enter inline, group save и joint settings save проверять на synthetic records; сравнить expected SQLite fields и неизменность synthetic media. Рабочую DB/.env/permissions/images не трогать. Исторический baseline сохранить для сравнения, после save изменение fixture ожидаемо и не должно ошибочно называться unchanged.

### ADM-AUD-08 / ADM-AUD-10E — isolated Chrome save/reopen: PASS, 2026-10-16
Runtime8766 повторно запущен после подтверждённого штатного завершения предыдущего, прежние fixture guards сохранены. Browser inline title Enter записал «Сохранённый inline» и вернулся к browse. Group action сохранил «Сохранённая подпись»; после reload/reopen textbox содержит это значение. Numeric count7 и остальные group metadata сохранены, подтверждено прямым SQLite сравнением с baseline с единственной заменой caption. Screenshot inline-group-saved-isolated-20261016.png сохранён.
Общий settings editor: text/textarea/code/checkbox/radio/select изменены, rich HTML через dialog→Применить, затем Сохранить настройки. Уведомление «Настройки сохранены» и возврат к browse. Reload/editAll подтвердили text «Сохранённый text», rich «Сохранённый HTML», code saved:true, checkboxfalse, selectfirst. Файлы/изображение не загружались заново.
Query-only verification storage/app/audit/isolated-browser-save-verify.php:22checks true,exit0; отчёт isolated-browser/save-verification.json. Проверены 7 изменённых типов, key/group normalization, file/image значения и bytes, inline Enter и group preservation. Fixture keys исходно audit.TYPE/groupSite без prefix; original VoyagerSettingsController update тоже добавляет slug(group) к suffix, поэтому итог site.audit.TYPE ожидаем (не изменение рабочего каталога). Проверка1seedtest3assertions предыдущего шага не является22 PHPUnit assertions: это отдельные22 прямые PDO условия после browser действий.
Screenshot settings-saved-isolated-20261016.png просмотрен: повторно открытая форма с сохранёнными text/textarea. Форма закрыта Отменой; PHPserver session58343 остановлен Ctrl-C,exit1 штатной остановки. Product .env/config/DB/permissions/images не изменялись, коммита нет.
Закрыты isolated browser save/Enter для inline text/group и joint save существующих9 типов без media replacement. Upload/progress/cancel/rollback и физический touch/DnD остаются отдельной UAT; текущий результат не означает их прохождение. План/матрица/журнал актуализированы, общий аудит IN PROGRESS.

### ADM-AUD-10E — isolated media replacement: IN PROGRESS, 2026-10-16
Source File/Image handlers сохраняют новое значение; старые файлы не удаляют при обычном replacement. Подготовить synthetic TXT/PNG и проверить chooser→upload→joint save→reload, сравнить DB paths, bytes и старые fake files. Public storage route стенда ограничен canonical fixture/public root. Все изменения только SQLite/fake files, никаких production image path rewrite.


### ADM-AUD-10E — isolated media replacement: browser chooser не получен, 2026-10-16
Original ContentTypes/File и Image повторно сверены: обычная загрузка сохраняет новый путь и не удаляет прежний файл; новый BreadSettingUploads следует этому контракту. Подготовлены только synthetic replacement.txt и PNG32x24 в isolated-browser/uploads; fake public route стенда теперь обслуживает только canonical descendants fixture/public, без выхода к рабочему storage.
Chrome waitForEvent(filechooser) перед click visible button истёк10s, unhandled rejection сбросил browser REPL. Сессия восстановлена через тот же Chrome3/текущий localhost URL, не новый браузер. Повтор actual input[type=file] (DOM видимый/не disabled) с перехваченным ожиданием тоже получил action timeout. setFiles НЕ выполнялся; upload/save replacement не состоялись. Это ограничение текущего browser control/chooser, не доказанный баг приложения и не проверенная ошибка backend upload. Таблица/редактор доступны; reload закрыл черновик, EditAll button visible=true.
Query-only save-verify после попыток:22checks true,exit0; public fixture содержит только исходные audit.pdf45bytes/audit.png68bytes. Ни новых хранимых файлов, ни изменений media values. Server session96008 остановлен Ctrl-C,exit1 штатной остановки. Рабочие DB/images/.env/config не менялись; product code/коммит не создавались. Replacement browser UAT остаётся открытой до доступного file chooser. Следующая независимая задача — browser CRUD меню на изолированной DB; upload через backend-only tests не выдавать за выполненный chooser/progress/cancel.

### ADM-AUD-10A/B/F/G — isolated menu browser cycle: IN PROGRESS, 2026-10-16
Создать synthetic menus/menu_items и права только в fixture SQLite. Проверить menu create/rename, title EN/RU save, common URL/icon/color, reopen, delete modal/cancel. Деструктивное удаление через browser не требуется для проверки open/cancel; backend cascade отдельно покрыт SQLite regression. Рабочие menu DB/settings не менять.


### ADM-AUD-10A/B/F/G — isolated menu cycle: create/rename/item/translations PASS, 2026-10-16
В fixture SQLite добавлены menus/menu_items и synthetic permissions; router разрешает menu-items. Browser с пустого списка: Create menu audit_browser_menu→Save; Rename audit_browser_renamed→Save; Add item titleEN Audit item EN, URL/audit-fixture, iconvoyager-home,color#336699; switchRU сохранил общийURL, titleRU Тестовый пункт RU; Save вRU вернул дерево с базовымEN title. Reload/Edit подтвердили EN/RU, icon/color/URL. Menu delete modal открылся с кнопкой «Удалить меню и пункты», выполнена Отмена удаления; фактический browser delete не выполнялся. Созданный fixture остаётся для дальнейшего parent/route/DnD теста.
Query-only isolated-browser-menu-verify.php:8checks true,exit0; menu_created_and_renamed/itemcount/EN/URL/icon/color/RU/deletecancel. Json evidence isolated-browser/menu-verification.json. Screenshot menu-item-saved-isolated-20261016.png просмотрен, показывает сохранённый RU иURL в повторно открытой форме. Полный CRUD delete через browser не объявляется пройденным; source/SQLite cascade tests — отдельное прежнее evidence.
PHPserver session78160 остановлен Ctrl-C,exit1 штатной остановки. Рабочие menus/permissions/DB/images не менялись; product code/коммита нет. Осталось отдельным browser сценарием route/JSON params/target, parent-child и DnD/touch. Upload chooser limitation прошлого шага не считается backend bug и остаётся открытым. Общий ADM-AUD-10 IN PROGRESS.

### ADM-AUD-10F/H — isolated route and parent cycle: IN PROGRESS, 2026-10-16
На существующем synthetic menu проверить URL→route/JSON/_blank с сохранениемEN/RU/icon/color, дочерний пункт через parent selector и reopen. Сравнить fixture DB. Рабочее меню не менять; операции удаления не требуются для этого этапа.


### ADM-AUD-10C — isolated browser drag: IN PROGRESS, 2026-10-16
Сохранены synthetic parent/child, screenshot menu-route-child-isolated-20261016.png подтверждает ручки и вложенность. Проверить документированный tab.drag по координатам текущего screenshot, затем DOM/SQLite. Не подменять физический drag вызовом reorderItems и не менять рабочие menu records.


### ADM-AUD-10F/H — isolated route/parent save: PASS, 2026-10-16
Browser существующего пункта: URL→route filament.admin.pages.voyager-settings, JSON audit:fixture,target_blank→Save; затем дочерний Audit child EN через parent selector родителя→Save. Reload сохраняет обе формы/дерево. Невалидный JSON «{» получил validation error, форма осталась открытой, DB прежние параметры не изменила; отмена после одного CDP dispatch timeout завершилась со второго разрешённого на свежем DOM клика. Это не runtime failure backend.
Query-only route-verification.json:9checks true — route/params/oldURLcleared/target/iconcolor/RU/childparent/menu/URL. Screenshot menu-route-child-isolated-20261016.png просмотрен. H deletion-parent сценарий этим не закрыт: проверена только иерархия, parent preservation при delete остаётся отдельным прежним SQLite evidence.

### ADM-AUD-10C — desktop native browser drag: PASS, 2026-10-16
Использован документированный tab.drag через реальную Chrome UI ручку, без JS dispatch/reorderItems. Child→root, root reorder Child перед Parent, обратный Child→parent — каждый успешный перенос дал «Порядок меню сохранён». SQL snapshots drag-root/reversed/nested.json: по2conditions true на этап,exit0. После reload корневой список1, дочерний1; итоговые parent_id и sibling order1/1 подтверждены. Повторная route verification9checks PASS: сохранены route/params/target/icon/color/RU/URL. Первая попытка root drop оказалась вне UL и не изменила дерево; bounds проверены, перенесено внутрь UL. Первый обратный drop поменял только root order; по свежему DOM выбрана фактическая empty child UL и вложенность восстановлена. Эти промежуточные drops не выдаются за пройденную вложенность.
Screenshots menu-drag-root-isolated-20261016.png и menu-drag-nested-isolated-20261016.png сохранены; final nested просмотрен. Реальные действия только в synthetic SQLite menu. Touch/пять уровней/большое дерево через physical UI отдельно не проверены; structural guards покрыты прежними tests. Desktop DnD больше не оставлять полностью непроверенным. Общий10C с touch остаётся IN PROGRESS.
Server session55901 остановлен Ctrl-C,exit1 штатной остановки; working DB/images/menu/settings не менялись, product code/коммита нет. План/журнал/матрица актуализированы. Следующие реальные границы — physical touch, browser chooser uploads, parent deletion UI и remaining media UAT; основной аудит IN PROGRESS.

### ADM-AUD-09 — isolated Cropper wheel/pan: IN PROGRESS, 2026-10-16
Original Cropper1.5.11 использует wheel/drag в crop modal. Standalone image preview отдельные zoom/reset кнопки не имеет; не добавлять придуманный UI. Подготовить fake PNG640x480 и browse_media только fixture, затем browser scroll/drag и cancel, сравнить SHA256. Working media не трогать.


### ADM-AUD-09 — isolated Cropper wheel/move/cancel: PASS, 2026-10-16
Сверка уточнила область: standalone preview только modal image, отдельных zoom/reset toolbar нет; wheel/pinch/move относятся к original Cropper1.5.11. Product UI не расширялся. Synthetic PNG640x480 с grid и browse_media добавлены только fixture SQLite/public; рабочие изображения не затрагивались.
Chrome native wheel через tab.scroll внутри canvas уменьшил480x360 до436.354x327.271 (inline style436.364x327.273); crop fields стали39,29,563,422. Doubleclick crop face переключил drag-box в move (DOM data-cropper-action=move); physical tab.drag от750,225 к790,245 изменил image canvas translate77.2424/16.2727→117.242/36.2727, cropx=-20/y=0. Отрицательная область допустима standalone по original. Только чтение DOM geometry, никакого вызова скрытых cropper методов через evaluate.
Cancel→modal hidden; reopen создал новый canvas480x360 и исходныеx64/y48; сноваCancel. Кнопки cropImage(false/true) не нажимались. SHA256 synthetic crop image до/после совпал,bytes2204. Screenshot media-wheel-pan-isolated-20261016.png сохранён/просмотрен и подтверждает moved canvas/поля. Server session55776 остановлен Ctrl-C,exit1 штатной остановки. Fixture helpers/routes не меняют product code; коммита нет.
Закрыта физическая desktop wheel/move UAT. Pinch/touch, EXIF preview, actual audio/video decode и browser upload/drop/progress/cancel по-прежнему открыты. Матрицы09/общего аудита обновлены, основной план и журнал дополнены; весь09 не DONE. Следующее — EXIF preview на synthetic Orientation1–8 либо декодирование local media samples; chooser block не превращать в ложный backend failure.

### ADM-AUD-09 — isolated EXIF preview1–8: IN PROGRESS, 2026-10-16
Создать synthetic JPEG320x240 с4цветными квадрантами и EXIF1–8. Browser открыть crop modal по одному, проверить Cropper DOM rotation/flip и отменить, bytes hashes до/после. Server raw-pixel контракт не менять: original не auto-orient перед crop.


### ADM-AUD-09 — EXIF fixtures/server contract PASS; browser preview open, 2026-10-16
Подготовлены8 synthetic JPEG320x240 с4цветными квадрантами и Orientation1–8 в isolated-browser/public. Прямой exif_read_data/getimagesize/SHA256 verifier подтвердил24 условия (8x3): Orientation, dimensions и неизменность bytes; отчёт isolated-browser/exif-fixture-verification.json. Это проверка fixtures, не24 PHPUnit assertions и не визуальное подтверждение поворота.
Повторный PHPUnit tests/Unit/MediaCropExifTest.php:1test16assertionsPASS,0.008s26MB. Все8 Orientation сохраняют исходный raw-pixel server crop contract12x9jpeg; это совместимость с original, а не auto-orientation correction. Product code не менялся.
Chrome createBrowserTab отклонён сообщением Local browser use is disabled in Work settings. Страница новыхEXIF не открывалась, rotate/flip preview не проверены. Не обходить отключённое управление сторонними браузерными средствами. Server session38763 остановлен Ctrl-C,exit1 штатной остановки. Рабочие DB/images не менялись, коммита нет. Этот browser blocker появился впервые после успешных предыдущих UI проверок; весь goal не blocked, независимая code/test работа доступна. Следующий visual шаг при доступном browser — crop modal каждогоEXIF, DOM transform/screenshot/cancel/hash. Пока09EXIF browser и pinch остаются открытыми.

### ADM-AUD — совместный regression BREAD: IN PROGRESS, 2026-10-16
После последних component attributes/menu/settings/media fixes запущен весь VoyagerBreadTest на testing SQLite/fake storage, JUnit storage/app/audit/bread-regression-20261016.xml. Процесс session20720 подтверждён запущенным; дождаться результата, ошибки исправлять по конкретному сценарию, не повторять широкие gates без причины.

### ADM-AUD — общий VoyagerBread regression PASS, 2026-10-16
Полный tests/Feature/Admin/VoyagerBreadTest.php после текущих menu/settings/media/component attribute fixes:276tests4490assertionsPASS,02:39.503,192MB,exit0. JUnit storage/app/audit/bread-regression-20261016.xml. Session20720 завершилась штатно, повторный широкий запуск не требуется. Gate подтверждает совместимость сценариев именно этого класса: metadata/forms/relations/roles/DB editor/library/settings/menu/Compass и regression fixtures; не является полным suite приложения и не доказывает provider/browser touch/upload UAT.
Рабочие DB/images/permissions не менялись; SQLite/fake storage задаются phpunit.xml. Код не менялся в этом шаге, найденная blank EOF плана устраняется этой записью. Открытые browserEXIF/pinch/media decode/upload chooser остаются в плане из-за отключённого browser control и непроведённых сценариев. Коммит не создан, общий аудит IN PROGRESS.

### ADM-AUD-13 — полная сверка раздела «Заказы» с оригиналом: TODO, 2026-10-16
Запрос пользователя: полностью сверить https://viar13.loc/filament/orders с исходным разделом заказов Voyager https://viarcanvas.loc/admin/orders. Ранее проверенные отдельные actions и SA-сценарии использовать как evidence, но не считать полной приёмкой раздела.
Область:
- Список: все колонки и их содержимое, изображения, статусы/цвета, порядок, поиск, фильтры, сортировки, пагинация, выделение/массовые действия, сохранение состояния и переходы.
- Создание/просмотр/редактирование заказа: все поля, вкладки, обязательность/валидация, переводы/язык PDF, клиент/адрес/фирма, товары, услуги, суммы/скидки/доставка; сохранение, отмена и повторное открытие.
- Все действия строк/карточки/модальных окон: жизненный цикл/статусы, художник, оплата/платёжные ссылки/номер/счёт, PDF, Venipak/накладные/печать, письма и отзывы, клиентский/внутренний/SA/художнический чаты и вложения — по фактическому original коду/UI, без предположения о наличии функции.
- Права и роли: доступность колонок/действий, серверный отказ при отсутствии/отзыве прав; ошибки, пустые состояния и повторные действия.
- Визуальная сверка desktop/mobile: шапка, ширины колонок, кнопки/иконки, отступы, диалоги, прокрутка, длинные значения и компактность изображений.
Порядок: сначала составить матрицу original/new со ссылками на исходники и конкретными расхождениями; затем исправлять подтверждённые различия и фиксировать проверки каждого сценария. Запись/изменение заказов и файлов — только изолированная тестовая БД/storage, реальные письма/платежи/доставка/внешние отправки не запускать. Рабочие изображения и пути не менять. Browser недоступность фиксировать отдельно; code/tests не заменяют визуальную сверку.
Definition of Done: каждый применимый original сценарий отмечен проверенным либо явно ограниченным с причиной; исправления проверены релевантными тестами и browser evidence где применимо; результаты и остаток записаны в плане/журнале. До этого ADM-AUD-13 остаётся TODO/IN PROGRESS. Следующее точное действие — инвентаризация original Orders browse/create/edit и соответствующих новых OrdersTable/Order*Actions/forms; сама сверка в текущем шаге не выполнялась.

### ADM-AUD-13 — первичная инвентаризация: IN PROGRESS, 2026-10-16
Создан docs/admin_orders_parity_2026-10-16.md с подзадачами13A список,13B filters/tabs,13C edit,13D create,13E payment/PDF/delivery actions,13F chats/artist/lifecycle,13G rights/design. Источники actual original orders/order/order_create/controller и new Resource/Pages/Schemas/Table/Services прочитаны. Прежние276tests BREAD не являются доказательством Orders parity.
Подтверждён пропуск13C: OrdersResource.form→OrdersForm содержит8fields; EditOrders использует UpdateOrderService с тем же whitelist. Original order.blade/update_admin_order содержит существенно больше: адрес/доставка/пункты выдачи/дата/скидки/фирма/позиции. Отдельные row actions не заменяют полный edit flow. Пока не утверждается точный перечень всех полей до детального controller audit. Фильтры и остальные actions существуют и требуют predicate-by-predicate сверки, не объявлены отсутствующими.
Следующее точное действие13C — исследовать original delivery/recipient update mapping и перенести блок с изолированными tests/preservation; затем item pricing/discounts/company и остальные применимые поля. Browser control был выключен, визуальная сверка этого шага не выполнялась. Изменена только документация, working DB/images/permissions не менялись, коммита нет.

### ADM-AUD-13C — доставка и получатель: IN PROGRESS, 2026-10-16
Подтверждён контракт original update_admin_order: delivery JSON, два телефона с fallback, справочники мастерских/городов, legacy pickup aliases, photo_short_code и manual express. Перенос: отдельный блок формы с вложенным delivery_edit state и сервис маппинга; остальные JSON ключи/items/пути изображений сохранять. Проверки — SQLite, без переименования файлов, PDF и внешних отправок. Остальные блоки13C (скидки/фирма/товары) остаются открытыми.

### ADM-AUD-13C — получатель и доставка: PARTIAL, 2026-10-16
Добавлен OrderDeliveryForm в OrdersForm; EditOrders заполняет delivery_edit через OrderDeliveryEditor, UpdateOrderService сохраняет разрешённые ключи JSON атомарно под row lock. Реальные справочники, RU labels/country filters, UI синхронизация адреса/города, оба телефона с original normalization/fallback, legacy pickup aliases/IDs, цена/дата/комментарий. Неизвестные JSON extensions/items/file paths сохраняются; старый caller без delivery_edit не меняет JSON. Общий комментарий допускает очистку. Нового общего express toggle нет — в original UI он относится к позиции, flag из items/delivery сохраняется.
Gate: OrderDeliveryFormTest + UpdateOrderServiceTest,14tests120assertionsPASS,SQLite :memory:,1.892s84MB; настоящий schema getState→service→refill, переключения/справочники/ошибки/rollback/конкурентный JSON. Roundtrip нашёл запрет форматированного телефона из tel regex Filament; заменено на type tel с original нормализацией, повторный gate PASS. Pint7files completed. Отчёт docs/admin_orders_parity_2026-10-16.md обновлён.
Осталось13C: скидки, фирма, канал/категория/даты, позиции и exact original save side effects/PDF. Настоящий browser EditOrders/права при submit ещё не проверены; раньше browser control отключён. Рабочие DB/images не изменены. Коммита нет. Весь13 IN PROGRESS.

### ADM-AUD-13C — скидки, реквизиты и параметры: IN PROGRESS, 2026-10-16
Original order.blade/update_admin_order/Orders::updateOrder/DynamicPDF подтверждены: EUR+% могут существовать одновременно, edit сохраняет их без пересчёта sale_price; PDF применяет EUR, затем%. Юр. реквизиты отдельными ur_* колонками, checkbox on/empty; approved_date свободный текст с fallback created_at d.m.Y H:i, пустое значение original не очищает. Категории IndexController::get_styles_for_quiz(ru), канал a_order_from виден только если заполнен, manager=0 означает Админ. Перенести в форму/сервис и проверить isolated roundtrip/preservation/invalid/old callers; новые миграции не нужны.

### ADM-AUD-13C — скидки, фирма и параметры: PARTIAL, 2026-10-16
OrderDetailsForm подключён к OrdersForm: EUR/% независимо, категория через общий styleOptions/get_styles_for_quiz(ru), канал a_order_from только когда уже указан, текстовая approved_date с fallback created_at, ur_name toggle+7requisites. Менеджеры role4 nick/email +0Админ; server exists больше не отвергает0. UpdateOrderService сохраняет новые поля, не пересчитывает price/sale_price (иначе PDF применит скидки дважды), сохраняет дату при empty и старые поля при omitted input. Флаг фирмы on/empty, explicit clear реквизитов supported. СхемаБД не менялась.
Проверено изолированно:20tests208assertionsPASS,2.407s86MB, OrderDetailsFormTest+OrderDeliveryFormTest+UpdateOrderServiceTest. JUnit storage/app/audit/orders-edit-20261016.xml. Новый harness использует production fields и настоящие fill/update hooks EditOrders, затем новую форму из сохранённой DB: менеджер0, sourcevisibility, виртуальныеcategories, bothdiscounts, всеrequisites/toggle/date, preservation items/delivery/imagepaths и invalid rollback. Не является проверкой browser или полного permission submit. Pint6files. Отчёт docs/admin_orders_parity_2026-10-16.md обновлён. Рабочие DB/images/письма/PDF/Venipak не затрагивались; коммита нет.
Далее13C: exact original update_order_item_price + редактор позиций, content_data и read-only all_sales; затем save side effects/PDF и реальный UI/права. Полная13 и13C IN PROGRESS.

### ADM-AUD-13C — редактор существующих позиций: IN PROGRESS, 2026-10-16
Подтверждён original update_order_item_price: отдельное сохранение позиции, name/price/sizeId/size_name/comment, manual canvas/gift/decoration/orientation/baget/express, presentation в locale доставки/клиента, total_terms_price и delivery express, пересчёт orders.price/sale_price по позициям. Встраивается отдельное действие «Обновить позицию» в inline карточки, без включения черновиков позиций в общий submit. Existing presentation service переиспользовать. Рабочие фото не переименовывать; side effects PDF/rename остаются отдельным gate. Добавление новой позиции/загрузка фото и read-only блоки проверяются отдельно после core edit; полную13C не закрывать.

### ADM-AUD-13C — существующие позиции и информация: PARTIAL, 2026-10-16
Перенесены OrderItemsForm/OrderItemEditor: отдельное сохранение inline карточки name/price/sizeId/size_name/comment и canvas/gift/decoration/orientation/baget/express. Actual numeric index/legacy fallback/служебные JSON keys, localization через существующий presentation service; aggregate terms/express/base order sums. Row lock+fingerprint, соседние позиции/extensions/paths сохраняются. Main submit исключает item drafts, item save обновляет parent price/sale_price; другие drafts остаются. Фото96px readonly preview. Исправлено неверное суммирование original number_format для цены>=1000, numeric round без comma separator; EUR/% не пересчитываются.
OrderInformationForm: original5all_sales, content для is_admin_order2 (read-only: original content_data не имеет save handler). Источники/контракт и точные отличия в docs/admin_orders_parity_2026-10-16.md. Existing EditOrders hooks дополнены items_edit.
Gate30tests320assertionsPASS,3.235s90MB после Pint6files; JUnit storage/app/audit/orders-edit-20261016.xml. SQLite :memory:, actual presentation/locale fixtures, schema action/main getState, price/paths/otherdrafts/invalid/stale/brokenJSON/свежий другойitem, reader forgedmount/revoked rights, safe content render. Старый item errorbag может оставаться видимым при успешном main getState; sentinel подтвердил отсутствие блокировки и отсутствие drafts в payload.
Осталось13C: добавление позиции/add_client_images/остальные media controls, original rename/PDF side effects (working paths не менять), настоящий EditOrders/browser desktop/mobile/full page permissions. Полная13 IN PROGRESS. РабочиеDB/images/permissions не менялись, real PDF/email/provider calls не выполнялись, коммита нет.

### ADM-AUD-13C — добавление позиции и исходников: IN PROGRESS, 2026-10-16
Original OrdersController::add_order_item(2537+) и add_client_images(1349+) прочитаны. Add — отдельная форма name/price/size/basket_terms/comment/manual codes/images, append numeric index и base totals. Attach — отдельное действие для существующего item_index, без сохранения черновика цены/названия. Перенос: add action + attach action, новые files только uploads/orders с existing business filename generator и collision suffix; старые файлы/links не переименовывать. Upload type/size validation, DB+new-file rollback, scoped index/fingerprint и edit permission. Запись тестируется только SQLite+fake uploads.

### ADM-AUD-13C — add position / attach files: PARTIAL, 2026-10-16
OrderItemEditor.add/attach, OrderItemUploads и OrderItemActions подключены к production OrderItemsForm. Add modal с original fields/defaults +images, разреженныеиндексы/общиеprices/root aggregates; отдельный attach к выбранному index, без сохранения/потери черновика, обновляетfingerprint. Общиеmanualfields reuse; B0/B1/B2 nowpersist (originaladd handler игнорировал свойselect). Новыеfiles толькоuploads/orders с production naming+collision suffix, старыеbytes/paths не трогаются. MIME+extension/30files/100MiB limits, исполняемые/подменаHTML rejected; update/add/attach/main Save=false теперь error, rollbackновыхfiles приDB/veto/partialwrite. Reader/revoked rights/stale modal protected.
Выявлен transport gap12MiB default Livewire vs100MiB adminfields. config/livewire.php задаёт temporary100MiB, остальныеvendor tempdefaults сохранены; perfield caps остаются. PHP/OpenServer size limits/real large transfer не проверены. SVG не входит в raster whitelist, PSD/FIG/HEIC chooser UAT не проведён.
Gate43tests447assertionsPASS,6.173s94MB послеPint8files; JUnit storage/app/audit/orders-edit-20261016.xml. SQLite :memory:+fakeuploads/local/tmp, Livewire add/attach→update, MIMEfinfo disguise, collision/oldbytes, partialsecondwrite/DB/veto cleanup, reader forgedmount/revocation/staleerror, temp20MiBaccept/>100MiBreject поfake metadata. Report docs/admin_orders_parity_2026-10-16.md обновлён.
Следующее13C: original edit photo/sketch/painter panels/actions→existingadapters matrix, затем PDF/general save side effects, настоящий page/permission/UI и chooser/PHP limits. РабочиеDB/images/permissions/externalservices не менялись, коммит не создан.13/13C IN PROGRESS.

### ADM-AUD-13E — Venipak, функционал и дизайн: IN PROGRESS, 2026-10-16
Пользователь отдельно подтвердил приоритет полной сверки создания этикетки Venipak: поля/defaults/условия видимости/количество и параметры мест/создание/повтор/ошибки/список/печать, а также внешний вид list/edit/modals desktop/mobile против original. Наличие сервиса не считать parity. Сначала original/new code matrix, исправления+Http fake tests; фактические provider запросы не выполнять. Chrome inventory повторно подтвердил Local browser use is disabled in Work settings, apps/browsers пусты. Visual gate остаётся открытым, без обхода настройки.

### ADM-AUD-13E — первичная сверка Venipak: IN PROGRESS, 2026-10-16
Коммит накопленных09–13 изменений создан:76ac5af,89files11588insertions232deletions; двеAdmBrowserCycle модели не включены. Далее Venipak аудит code+design с original. Подтверждён пропуск country/city/pickup catalogue selector: old form__label+bot_scripts подгружают/фильтруют пункты и автозаполняют identity, new OrdersTable просит ручные TextInput. Сначала перенос каталога с Http fake+schema gate; полный XML/create/print/repeat/ошибки и визуал ещё не приняты.

2026-10-16: после перезапуска Chrome доступ восстановлен. В обеих админках открыты заказы и форма Venipak заказа №18451; получены снимки viewport. Подтверждены ручные pickup поля вместо каталога и более длинный новый modal. Уточнение: дверной код/кабинет/склад уже реализованы в сервисе и схеме, но ошибочно скрыты при pickup; в original доступны при обоих направлениях. Сейчас исправляется их видимость. Страхование присутствует в original UI/JS расчёте цены, но в VinepakApiController передачи insurance в XML не найдено: сначала выяснить контракт, не добавлять вымышленную provider функцию. Рабочие labels не создавались, печать не запускалась. Следующее: каталог pickup, country options и остальные UI/XML различия; общий13E IN PROGRESS.
Небольшое исправление послекоммита: defaultData.g_name при отсутствии связанного user берёт delivery surname/name, как original; email/phone fallback уже существовали. Сервисный test использует orphan fixture и assertNothingSent. Gate2classes7tests34assertionsPASS,1.058s80MB,безproviderзапросов. В docs/admin_orders_parity_2026-10-16.md добавлена матрица13E. Chrome повторноотклонён настройкойLocalbrowserdisabled; screenshot/UIgateоткрыт. Следующий точныйшаг — original pickup JS/request contract и адаптер каталога/селекторы. РабочиеDB/images/labels не менялись.

ADM-AUD-13E — повтор visual по запросу пользователя,2026-10-16: cua.getState снова вернул apps/browsers=[] и Local browser use is disabled in Work settings. Сравнение скриншотов не выполнено. Для продолжения visual требуется включить Local browser use в Work settings; source/isolated работа остаётся доступной, весь этап не объявлен blocked/done.

ADM-AUD-13E, шаг видимости DONE (2026-10-16): door_code/office_no/warehous_no доступны при обоих направлениях как original; label Упаковки. PHP lint PASS;7tests34assertionsPASS; Chrome reload/DOM/viewport подтвердили. Evidence storage/app/audit/venipak-form-20261016.png. Каталог/страны/insurance contract/layout/mobile/XML/print остаются TODO; весь13E IN PROGRESS. Browser blocker снят.

ADM-AUD-13E — каталог Venipak (IN PROGRESS, 2026-10-16): original get_towns_for_admin POST к фиксированному get_pickup_points?country, все типы пунктов (вычисленный type=1 filter не используется), уникальные города в порядке ответа, отображение city/address/display_name и name/company_code fallback. Перенос: отдельный HTTP adapter с активными VenipakData credentials, ограниченными timeout/cache; зависимые country→city→point selectors и hidden автозаполнение, без нового endpoint/миграции. Проверки только Http fake/SQLite и schema Livewire; реальное создание/печать не выполнять.

ADM-AUD-13E — каталог/страны/автозаполнение IMPLEMENTED (2026-10-16): VenipakPickupCatalog + OrderVenipakPickupForm + общая OrderVenipakLabelForm подключены к OrdersTable action. Original POST/all-types/city-order/labels/name+code fallback, зависимости country/city/point, hidden identity/XML, match delivery address или workshop title; cache/timeout/без секретов. Нет нового endpoint/DB migration. Full production schema phones+selection и catalog→XML проверены:15tests106assertionsPASS,1.832s90MB,SQLite/Httpfake. Pint/diffcheck PASS. Visual новая Grid lg2/default1/modal7xl ещё TODO: изолированная форма HTTP200, Chrome navigation/state CDP timeout. Рабочая DB/images/provider create/print не затронуты. Следующее: desktop/mobile изолированный visual gate, insurance контракт и print/create/repeat/ошибки; весь13E IN PROGRESS.

ADM-AUD-13E — страхование/печать/ошибки IN PROGRESS (2026-10-16): original insur Select (10,100..10000) считает стоимость0.3% при сумме>10, иначе0; backend generate_label_xml/buildLabelPayload не читает insur, в app/routes других обработчиков не найдено. Перенести UI расчёт без оформления страхования/нового XML поля. Original print POST передаёт только user/pass/login_id/pack_no, без размера бумаги; new совпадает. Исправить обнаруженное принятие HTML вместо PDF, утечку exception object в лог и игнорирование save=false после create; проверить fake HTTP/SQLite, без реальных provider операций. Visual новой формы ещё TODO.

ADM-AUD-13E — страхование/печать/errors DONE по коду (2026-10-17): original insur quote0.3%, без передачи backend, перенесён в live Select+quote/dehydrated(false). Print multipart contract без paper/format совпадает; дополнительно отклоняется non-PDF. Create save=false/DB errors сообщают созданные номера и отсутствие rollback внешней операции, без retry; logs exception_type без secrets. XML libxml state восстановлен/NONET. Gate20tests149assertionsPASS,2.061s90MB,SQLite/Httpfake, Pint/diffcheckPASS. Полный13E IN PROGRESS: TODO isolated desktop/mobile visual + настоящий action permission/error/download UI; затем остальные Orders audit gates. Реальных provider операций и изменений workingDB/images не было.

ADM-AUD-13E — действия создания/печати IN PROGRESS, 2026-10-17: service errors venipak/label не связаны с видимым полем modal; проверить реальный action callback/error presentation/permission revocation/download. Вынести текущие actions в общий factory, подключённый к OrdersTable и Livewire action harness; добавить видимое сообщение об ошибке и проверку update перед provider вызовом. Только fake service/HTTP и SQLite, без реальных отправок.

ADM-AUD-13E — действия/видимые ошибки/упаковки IMPLEMENTED (2026-10-17): общий OrderVenipakActions подключён к OrdersTable. Создание показывает escaped persistent error и оставляет modal; field errors имеют Schema prefix. Print error отменяет action/no download. Перед service повторная update authorization. Add упаковки клонирует предыдущие weight/volume/pallet как original JS. Gate4classes26tests199assertionsPASS,3.059s92MB; production table integration OrderLifecycle6tests38assertionsPASS;Pint/diffcheckPASS. Проверки action callbacks через factory/mock service и production schema/SQLite, не browser. TODO13E: isolated Chrome desktop/mobile visual и настоящий table UI notification/download/scroll; далее остальные Orders gaps. Общий13E IN PROGRESS.

ADM-AUD-13E — desktop visual/main URL DONE частично (2026-10-17): actual viar13/filament/orders и viarcanvas/admin/orders, один18451. Recipient/sender layout, expanded pickup field, country options, read-only live city search/filter, quote500→1.5EUR и add package2.5→2.5+2.5 визуально проверены. Draft отменён/dialog0; no create/print/working record write. Добавлены original postcode search links и sender warning; неверный hintUrl вызвавший500 заменён на hintAction и runtime повторно проверен. Screenshot storage/app/audit/venipak-orders-packages-20261017.png. Gate26tests199assertionsPASS,2.875s92MB/Pint/diffcheckPASS. TODO mobile и isolated notifications/download browser, весь13E IN PROGRESS.

ADM-AUD-13E — responsive390 layout DONE (2026-10-17): Chrome iframe390x844 srcdoc с production schema/Livewire/CSS и fake catalog. Onecolumn fields, scroll до footer/buttons, pickup dropdown wrap+Liepāja selection, test submit→notification/closed modal подтверждены screenshot/AX. Evidence storage/app/audit/venipak-mobile-390-20261017.png. Не physicaltouch/mobilekeyboard и не oldmobile parity. Productionerrors/download UI всё ещёTODO; whole13E IN PROGRESS. Этот шаг без productioncode/db/providerchanges, fixtures ignored.

ADM-AUD-13E — поправка пользователя: стиль формы IN PROGRESS (2026-10-17). Desktop/mobile проверки полей не означают совпадение с оригиналом. Перекомпоновать форму по original: отдельный документ сверху; строки подпись/поле в recipient/sender, компактные блоки; comments слева/time справа, instructions слева/insurance справа; packages снизу. После изменения сверить viewport actual Orders original/new и повторить production schema/action gates. Browser errors/download остаются открытыми, не закрывать ими замечание по стилю.

ADM-AUD-13E — style correction DONE (2026-10-17): по замечанию пользователя исправлена компоновка и scoped CSS: topdoc, compact inline rows recipient/sender, comments/time and instructions/insurance paired panels, original radio delivery options, staticreturnaddress, packagebottom, original heading/submit/footeralignment. Real Chrome actual Orders original/new18451 + narrow390 productionCSS визуально проверены, screenshot storage/app/audit/venipak-original-layout-20261017.png. Gate26tests199assertionsPASS,3.304s94MB/Pint/diffcheckPASS. Реальный draft отменён/no write/create/print. Весь13E IN PROGRESS: browser actual error notification/download ещёTODO, затем Orders remaining matrix.

ADM-AUD-13E — browser actual actions/service/error gate DONE (2026-10-17): отдельная SQLite loopback8769, production factories/schema/service/policy, Httpfake/strayblocked. Chrome confirmed printHTMLerror notification; createerror persistent/modal+draft retained; create success→labelshown/modalclosed; print successrequest. Verify labelsFIXTURE-LABEL и4manualrequests/no retries. Screenshot storage/app/audit/venipak-create-error-20261017.png. Actual downloaded-file presence UNVERIFIED: chrome://downloads blocked URLpolicy, не обходили; Livewire downloadbytes/name gatePASS ранее. Физическийtouch такженепроверен. WorkingDB/providers unchanged. Далее ADM-AUD-13A/B list/filter matrix и13C remainingedit; whole13/13E IN PROGRESS, не сужать13EдоVenipak.

ADM-AUD-13B — phone/price/payment search IN PROGRESS, 2026-10-17. По original OrdersController::index: price фильтрует толькоprice; phone собирает users.id по userphone exact/trim/LIKE и orders.user_id по delivery.phone exact/whitespace-trim/substring2/3/4, затем ищет все заказы этих клиентов. New price ORsale_price и rawdeliveryLIKE отличаются. Перенести эти predicates в shared production filters с SQLite/Livewire fixture gate; OPR number uppercase какoriginal. Найденные отдельно TODO: customer missing address/post/country/translit/prices/user-wide semantics; tab default/current vs original order_filter bypass; size_name extension vs original sizeId; until date inclusive отличие отoriginal midnight bug. Не объявлять весь13B DONE.

ADM-AUD-13B — phone/price/OPR и табы IMPLEMENTED (2026-10-17): shared OrderSearchFilters подключён OrdersTable, phone sourceuser matching вместо JSONLIKE, priceonly, OPRuppercase. ListOrders recentallstatuses/datewindow и originalsort; activefilters bypass tab branch/rotation как original order_filter. Gate18tests105assertionsPASS,2.334s86MB (2newfilter/tabclasses+rotation+lifecycle),Pint/diffcheckPASS,SQLite/Livewirefactory/probe. TODOcustomer/translit/user-wide/details, size/manager/dateedge различия, actualpage/browser/MySQL. Не считать весь13B DONE. Nextcustomer sourcecontract/reusedtranslit. WorkingDB/images/providers unchanged.

ADM-AUD-13B — customer search IN PROGRESS, 2026-10-17: original OrdersController::index user fields email/id/first_name/last_name/phone/address/postal_index/country + exactdelivery email/names/translit/phone/address/post/comment and price/sale_price yield client IDs, then all orders of those clients; invoiceprefix finds one order. Reuse original rus2translit mapping via shared OrderCustomerSearch, without generic Str::ascii/slug substitution. Tests real production filter factory in isolated Livewire table, plus combined filters/invoice routing/no rawJSON falsepositives.

### ADM-AUD-13E — уточнение стиля формы Venipak, 2026-10-17
DONE (оформление): повторно сравнены открытые формы заказа №18451 в Chrome на viarcanvas.loc/admin/orders и viar13.loc/filament/orders. Уточнены серый фон, видимые рамки панелей, насыщенность подписей и заголовков; на desktop уменьшены высота обычных полей и промежутки между строками. На мобильной ширине высота полей не уменьшалась. CSS ограничен .viar-venipak-form; business logic не менялась.
Проверка: новая форма после reload повторно открыта в Chrome, оформление подтверждено снимком storage/app/audit/venipak-style-review-20261017.png. Реальные этикетки не создавались и не печатались, данные не сохранялись. Ограничение: original раскрывает форму в строке таблицы, новая использует modal Filament; абсолютная идентичность контейнера не заявляется. В этом шаге повторная mobile проверка не выполнялась. Весь ADM-AUD-13 остаётся IN PROGRESS; далее — оставшаяся матрица функционала и оформления Orders.

### ADM-AUD-13B — точный фильтр размера (IN PROGRESS), 2026-10-17
Сверен original OrdersController::index: sizeId — точный JSON match позиций 0–9, без size_name. Требуется убрать расширение поиска в новой таблице и проверить совпадения/границы/совместное применение с ценой. Дополнительно подтверждены незакрытые отличия manager NULL и конечной даты (original midnight, new весь день); в этом шаге они не объявляются исправленными.

### ADM-AUD-13B — поиск клиента и точный размер: реализовано, 2026-10-17
DONE (код и изолированные проверки): production OrderSearchFilters::size подключён в OrdersTable. Только точный sizeId позиций 0–9, без совпадений по size_name и без trim значения, как в original OrdersController::index. Поддержаны числовые ключи JSON-объекта и индексы JSON-массива: первоначальный тест массива выявил отсутствие совпадения у пути ->0; добавлен путь ->[0] наряду с исходным. Покрыты все десять позиций, исключение позиции 10, коллизии названия/частичного ID, пустой фильтр, ноль и AND с ценой.
Также зафиксирован ранее реализованный customer search: OrderCustomerSearch повторяет поиск user fields/address/post/country, точные delivery значения, исходную транслитерацию и поиск остальных заказов найденного клиента. rus2translit существующего контроллера делегирует общей таблице. Канонические номера четырёх фирм находят один связанный заказ. Намеренное отличие от original: сравнение цены выполняется лишь для числового запроса, чтобы MySQL не превращал произвольный текст в совпадение с нулевой ценой. Пробельный номер заявки оплаты не ограничивает выдачу.
Gate: OrderSearchFiltersTest + OrderListTabsTest + AdminOrderRotationSortTest + OrderLifecycleServiceTest — 28 tests / 347 assertions PASS, 5.766 s, 88 MB; git diff --check PASS (только LF/CRLF warnings). SQLite :memory:, production filter factories в Livewire harness; HTTP stray requests запрещены. Первые size tests завершились 2 failures из-за array JSON path; исправлено, итоговый gate PASS. Рабочие DB/images не менялись. MySQL и настоящий ListOrders browser gate этим тестом не подтверждены.
Осталось ADM-AUD-13B: manager NULL/ветка без менеджеров; решение и проверка конечной даты (original midnight против whole-day новой версии); итоговая сверка комбинированных фильтров/вкладок/сортировки в Chrome. ADM-AUD-13B и весь ADM-AUD-13 — IN PROGRESS. Следующий шаг — manager original predicates и изолированные проверки NULL/назначенного менеджера/Админ. Коммита этого шага нет.

### ADM-AUD-13B — менеджер (IN PROGRESS), 2026-10-17
Original index + orders.blade.php подтверждают: Админ исключает ID пользователей role_id=4 через NOT IN и не включает NULL. Новый OR NULL и подпись «без менеджера» расходятся. Перенести predicate в общий production filter и проверить конкретного менеджера, Админ, пустой выбор и отсутствие role_id=4. Даты и Chrome остаются следующим шагом.

ADM-AUD-13B — период дат IN PROGRESS: по требованию соответствия original восстановить сравнение timestamp с началом конечного дня вместо whereDate включающего весь день; покрыть midnight и раздельные границы изолированными тестами.

### ADM-AUD-13B — менеджер и период дат: код проверен, 2026-10-17
DONE (production predicates): OrderSearchFilters::manager используется реальным OrdersTable. Конкретный ID — equality; Админ при наличии role_id=4 — NOT IN, без прежнего OR NULL; подпись «Админ / без менеджера» заменена на «Админ», опция после менеджеров как original. Пустой выбор не ограничивает выдачу. При отсутствии менеджеров сохранена исходная equality ветка manager_id='all' (MySQL приводит строку к 0); это legacy поведение, не универсальная семантика отсутствующего менеджера. Isolated tests отдельно подтверждают branch/binding, NULL exclusion, ID/AND price/empty.
OrderSearchFilters::createdAt также подключён в OrdersTable: from >= startOfDay, until <= startOfDay, как original index, вместо прежнего whereDate до конца дня. Это намеренная совместимость: при равных датах проходит только полночь, не весь день. Тесты покрывают предыдущую секунду, саму полночь, следующую секунду, следующий день, отдельные границы и пустой фильтр. Новый endpoint/миграция не требуются.
Gate: Pint3files PASS; OrderSearchFiltersTest + OrderListTabsTest + AdminOrderRotationSortTest + OrderLifecycleServiceTest — 32 tests / 399 assertions PASS, 6.318 s, 88 MB; diffcheck PASS (CRLF warnings only). Тесты SQLite :memory:, HTTP stray blocked; production DB не изменялась.
Chrome read-only: new /filament/orders?filters[manager_id][value]=__admin__ и original /admin/orders?order_filter=1&filter_managers=all. Первые десять номеров и порядок совпали:18450,18449,18448,18447,18446,18445,18444,18443,18442,18441. New активный фильтр «Заказы менеджеров: Админ», счётчик12696; original12736. Read-only aggregate на локальной БД подтвердил: role4 managers=0, manager_id='all' orders=12696, после original LEFT JOIN vr_numbers строк12736, DISTINCT orders.id12696. Разница40 объясняется дублированием join оригинала; новая таблица показывает уникальные заказы, дубли не переносились. Это намеренное отличие счётчика; абсолютная идентичность не заявляется. Реальные provider действия/записи не выполнялись.
ADM-AUD-13B/13 IN PROGRESS. Осталось: Chrome дата/размер/customer/phone/комбинации и явная сортировка, визуальная ясность выбранной вкладки при фильтрах (сейчас selected «Текущие77», хотя фильтры по исходной логике идут по всем статусам), итоговый список различий. Следующий шаг — браузерная сверка периода/суммы/размера на существующих данных без записи, затем UI вкладки при фильтрах. Коммита этого шага нет.

### ADM-AUD-13B — видимость активных фильтров (IN PROGRESS), 2026-10-17
Chrome: дата12–13.08.2026 + price22 + sizeId40x40 даёт в обеих админках18440,18439. Найден дефект новой панели: custom filters применены, но badge0 и нет индикаторов условий. Добавить production indicators, независимое снятие границ даты и тест удаления условия; затем повторить Chrome. Исходную бизнес-выборку не менять.

### ADM-AUD-13B — браузерная сверка сочетаний и индикаторы: частично проверено, 2026-10-17
Chrome до отключения доступа: original order_filter=1 + filter_start_date=2026-08-12 + filter_end_date=2026-08-13 + filter_price=22 + sizeId=40x40 и соответствующие new filters дали ровно18440,18439 в одинаковом порядке. Найден дефект: custom фильтры применялись, но не показывали индикаторы и badge был0. Добавлены индикаторы customer/phone/price/payment_request/size/order_id/user_id и обеих границ даты, с независимым removeField. После первого reload badge4, размер и даты видимы, но сумма отсутствовала: строковые ключи value разных indicators перезаписывались при объединении Filament. Исправлено использованием числового списка Indicator объектов с removeField('value'); добавлен regression test одновременных пяти условий и удаления одного без потери остальных.
Проверки удаления даты/сохранения другой границы, сброса всех, суммы0, пустых строк и пяти независимых индикаторов выполнены Livewire production filter harness. Последняя правка после обнаружения коллизии не подтверждена повторно Chrome: cua.listTabs(browser3) вернул Local browser use is disabled in Work settings. Настройки не обходились. Новую браузерную проверку нельзя считать выполненной; следующий точный шаг — после восстановления доступа reload сочетания и проверить все4метки, снять сумму/границу, сверить выдачу и явную сортировку с original.
Итоговый общий gate: OrderSearchFiltersTest + OrderListTabsTest + AdminOrderRotationSortTest + OrderLifecycleServiceTest + OrderVenipakLabelServiceTest + OrderVenipakActionsTest + VenipakPickupCatalogTest + VenipakLabelDestinationTest —61tests651assertionsPASS,9.110s96MB. Только SQLite/fake HTTP; реальные заказы/изображения/этикетки не менялись. ADM-AUD-13B/13 остаются IN PROGRESS. По запросу пользователя готовится коммит накопленных ADM-AUD-13B/13E изменений: фильтры/вкладки/индикаторы, Venipak schema/catalog/actions/service/style и tests/docs; две временные AdmBrowserCycle модели исключены.

### ADM-AUD-13B — явная сортировка (IN PROGRESS), 2026-10-17
Source check: Filament append orderBy после tab created_at/FIELD; explicit номер не становится главным. Original orderIdSort заменяет порядок. Исправить applySortingToTableQuery только для существующей видимой sortable колонки, проверить ASC/DESC/default/неизвестную колонку и сохранение predicates; не менять express состав.

### ADM-AUD-13B — приоритет явной сортировки: исправлено, 2026-10-17
DONE (код/изолированные проверки): source audit original OrdersController orderIdSort и Filament CanSortRecords показал, что новая сортировка колонки добавлялась после созданного вкладкой created_at/FIELD. ListOrders::applySortingToTableQuery теперь очищает старые ORDER BY лишь при выборе существующей видимой sortable колонки, затем вызывает стандартную сортировку Filament. WHERE/выборка вкладки сохраняются. При очистке сортировки остаётся исходный порядок вкладки; неизвестная колонка не очищает порядок. Состав express и остальные predicates не менялись.
OrderListTabsTest использует реальный ListOrders override и стандартный Filament sorting с минимальным Table: production ASC/DESC/default/unknown, filter branch без статуса completed, дополнительный WHERE и замена raw FIELD (SQLite исполнение после reorder). Это не полный смонтированный OrdersTable/browser test. Gate: OrderSearchFiltersTest + OrderListTabsTest + AdminOrderRotationSortTest + OrderLifecycleServiceTest —37tests463assertionsPASS,6.534s88MB; Pint2filesPASS; diffcheckPASS (LF/CRLF warnings only). SQLite :memory:, никаких рабочих записей/провайдеров.
Повтор Chrome cua.listTabs(browser3) снова вернул Local browser use is disabled in Work settings; обход не выполнялся. Browser сортировка и окончательные индикаторы остаются TODO. Следующее: после включения Local browser use повторить сочетание даты/суммы/размера, проверить все метки и их снятие, ASC/DESC по Номер, customer/phone/OPR и поведение вкладок. При недоступном Chrome продолжить source audit оставшихся Orders действий. ADM-AUD-13B/13 IN PROGRESS. Предыдущий накопленный коммит2dc5b06 создан успешно; текущая правка сортировки после него пока не закоммичена.

### ADM-AUD-13B — размер страницы при фильтрации (IN PROGRESS), 2026-10-17
Original index: order_filter paginate10, обычные вкладки13. New всегда13. Восстановить automatic10/13 для стандартного размера; явные25/50/100 сохранить. Общий predicate активных фильтров использовать для scope и pagination. Сводка суммы в original видна над таблицей с role gates, в новой скрытая колонка; отдельный TODO13A, переносить только после сверки прав.

### ADM-AUD-13B — пагинация фильтра: реализовано, 2026-10-17
DONE (код/изолированные проверки): original OrdersController::index order_filter paginate10, обычные вкладки paginate13. ListOrders теперь использует общий hasActiveOrderFilters для обхода tab scope и автоматического размера10/13. Выбранные25/50/100 сохраняются в обоих режимах. В OrdersTable первая опция размера динамически10 при фильтрах либо13 на вкладках, чтобы не предлагать одновременно два автоматически заменяемых значения. Default13 сохранён. При сбросе последнего фильтра стандартный размер возвращается13; прежняя логика Filament resetPage при смене filters остаётся.
Gate: OrderListTabsTest + OrderSearchFiltersTest + AdminOrderRotationSortTest + OrderLifecycleServiceTest —38tests475assertionsPASS,6.932s90MB; Pint3filesPASS, diffcheckPASS (CRLF warnings only). Покрыты пустые/пробельные фильтры, zero price, граница даты, filter on/off и явные25/50/100. Это проверка production методов выбора per-page, не полный браузерный переход страниц; динамический dropdown требует Chrome gate. В этом шаге browser не запускался, прежний Local browser use disabled остаётся незакрытым ограничением. РабочаяDB/изображения не менялись, провайдеры не вызывались.
Найдено и добавлено TODO ADM-AUD-13A: сводка количества/суммы в original над таблицей видна кроме printing/lite_manager; в новой сумма спрятана в toggleable column. Следующий source шаг — проверить role gates и перенос сводки без раскрытия суммы ограниченным ролям; затем browser filters/indicators/sort/pagination при доступном Chrome. ADM-AUD-13B/13 IN PROGRESS. Сортировка и пагинация после2dc5b06 пока не закоммичены.

### ADM-AUD-13A — сводка списка (IN PROGRESS), 2026-10-17
Original index557+ суммирует price до pagination, orders.blade102–106 скрывает count/sum для printing/lite_manager. Добавить description над таблицей из filtered query, скрыть тем же ролям и без browse_orders; проверить дробную сумму, sale_price collision, пагинацию/пустую выдачу и отсутствие aggregate для restricted. Не переносить join-дубли.

### ADM-AUD-13A — сводка списка: реализована, 2026-10-17
DONE (код/изолированные проверки): OrderListSummary подключён как description настоящего OrdersTable над таблицей. Из getFilteredTableQuery (scope/filters/search до pagination) рассчитывается count и SUM orders.price; sale_price не используется, как original index557–561. Числа форматируются до двух знаков с удалением лишних нулей. Clone/reorder/select([]) не меняет запрос списка и исключает случайные selected columns/correlated counts из aggregate. Подсчёт уникальных заказов текущей query, без original LEFT JOIN vr_numbers duplicate inflation, как ранее документировано.
Original orders.blade102–106 скрывает сводку primary role printing/lite_manager: это правило сохранено. Дополнительно required browse_orders совпадает с OrdersPolicy viewAny; гость и denied user не получают summary query. Прочие role-based ограничения исходных карточек/табов требуют отдельной матрицы ADM-AUD-13G, не объявляются готовыми этим изменением. Существующий toggleable money column не менялся.
OrderListSummaryTest рендерит production callback в Livewire table harness с pageSize1: сумма всех3 заказов40.5 вместо sale_price6, фильтр2 строки20.5, переходpage2, пустая выдача0/0 и reset; query clone/select/order preservation; printing/lite_manager hidden и getFilteredTableQuery never called; guest/no-browse query never called. Только SQLite :memory:, HTTP stray blocked. Общее gate Summary+Tabs+SearchFilters+Rotation+Lifecycle —42tests502assertionsPASS,7.682s90MB; Pint3filesPASS, diffcheck PASS (CRLF warnings only).
Визуальная приёмка новой description и полного OrdersTable не выполнялась: Chrome ранее недоступен, новых доказательств доступа нет. Рабочие DB/images не изменялись, real providers не вызывались. ADM-AUD-13A/13B/13 IN PROGRESS. Следующий шаг — source audit primary role ограничения списка/вкладок (printing/lite_manager) против original; при доступном Chrome проверить layout сводки, фильтры/метки/сортировку/pagination. Изменения после2dc5b06 пока не закоммичены.

### ADM-AUD-13G — элементы printing (IN PROGRESS), 2026-10-17
Original orders.blade7+ и orders/payment.blade9+ скрывают создание заказа и редактирование оплаты для printing; new header/payment Blade этого не делают. Убрать эти UI расхождения, сохранив текущие policy gates и доступные в original Venipak controls. Остальные tabs/карточки проверить отдельно; lite_manager не приравнивать printing.

### ADM-AUD-13G — printing: создание и платёжные controls, 2026-10-17
DONE (UI/source + render tests): original orders.blade7+ скрывает Create order для printing; orders/payment.blade9–37 скрывает изменение payment_status и сумму/обновление предоплаты, при этом status badge/date и Venipak create/print остаются. В новой ListOrders header CreateAction добавлена visible primary role != printing; production order-payment Blade скрывает только платёжные edit controls/сумму предоплаты для printing. Статус и Venipak сохранены. Lite_manager не приравнивается printing: проверенный original ограничивает его сводку, но payment/header относятся к non-printing.
OrderListRolePresentationTest рендерит настоящий Blade на несохранённом synthetic Orders с loaded role: printing получает status/labels/print без updatePaymentStatus/updatePrepayment/amount; admin/lite_manager сохраняют controls; настоящий CreateAction visibility проверен через ListOrders probe. Это UI parity, не новый server authorization gate: существующие OrdersPolicy/edit/create и action guards не менялись. Прямая рольвая авторизация endpoints требует отдельной сверки original, UI скрытие не считается запретом endpoint.
Общий gate RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —44tests518assertionsPASS,8.431s90MB; Pint2filesPASS; diffcheckPASS (CRLF warnings only). Записей в рабочую DB/права/изображения не было. Browser render/desktop/mobile не проверены этим шагом.
Source matrix TODO13G: original printing не видит current/completed/unpaid links, видит production/printing/sent_today/sent/new_recently и «Все текущие заказы» (express=1); в новой все tabs и badges доступны. Однако original root /admin/orders дляprinting не перенаправляет (redirect закомментирован) и применяет current default: перед переносом tab default нужно отдельно согласовать navigation/query без случайной смены выборки. Остальные original role guards: id/company/payment request, user sales/channel, products prices, comments/actions. Не считать44tests полной role matrix.
Найдено TODO13B: цикл header sort original default→DESC→ASC→default, стандарт Filament default→ASC→DESC→default; приоритет query уже исправлен, цикл ещё не совпадает. Следующий точный шаг — согласовать этот цикл с original и проверить сброс page/сохранение filters; затем remaining printing controls/tabs. ADM-AUD-13G/13 IN PROGRESS, коммита текущих изменений после2dc5b06 нет.

### ADM-AUD-13B — цикл номера (IN PROGRESS), 2026-10-17
Original orders.blade265–280: default→desc→asc→default. Переопределить только toggle number_controls без явного direction; прямые asc/desc и остальные колонки оставить стандартному Filament. Проверить три состояния, reset page и неизменность filters/tab.

### ADM-AUD-13B — цикл сортировки номера: исправлено, 2026-10-17
DONE (код/изолированные проверки): ListOrders::sortTable для toggle number_controls повторяет original orders.blade265–280: default→DESC→ASC→default. Явно переданные asc/desc, очистка sort и другие колонки делегируются Filament. updatedTableSort вызывается во всех трёх состояниях, сохраняя штатный resetPage/session handling. Исходник Filament table Blade1790–1795 подтверждает вызов sortTable(column) без direction при click/Enter/Space.
Test на production ListOrders probe проверяет все3состояния, page3→1 каждый раз, неизменность filter state и activeTab, явные asc/desc и независимую created_at сортировку. Существующие query tests подтверждают результат ASC/DESC/default и WHERE preservation. Это проверка серверной стороны, не click в настоящем Chrome.
Gate: RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —45tests534assertionsPASS,6.987s90MB; Pint2filesPASS, diffcheckPASS (LF/CRLF warnings only). Рабочие DB/images/права не менялись; никаких внешних действий. Browser gate всё ещё TODO после отключения Local browser use. ADM-AUD-13B/13 IN PROGRESS. Следующий шаг — remaining printing tabs/role UI, затем source review остальных13E действий и browser acceptance при доступе. Изменения после2dc5b06 пока не закоммичены.

### ADM-AUD-13G — навигация printing (IN PROGRESS), 2026-10-17
Скрыть current/completed/unpaid/all tab controls для printing; express label «Все текущие заказы» и порядок как original. Оставить current в query map и default, поскольку original root redirect закомментирован. Это UI visibility, не запрет прямого запроса. Проверить видимые key/labels для printing/admin/lite_manager и неизменность default.

### ADM-AUD-13G — вкладки печатника: перенесены, 2026-10-17
DONE (UI schema/source + isolated checks): ListOrders getTabs сравнен с original orders.blade73–88. Для primary printing невидимы current/completed/unpaid и дополнительный new all; express переименован «Все текущие заказы» и размещён после new_recently. Видимые keys production,printing,sent_today,sent,new_recently,express повторяют original links. Для admin/lite_manager порядок current,completed,express,production,printing,sent_today,sent,new_recently,unpaid,all, исходные labels/queries сохранены. Дополнительный all остаётся последним только non-printing (ранее добавленная новая возможность).
Важная особенность сохранена: default activeTab=current и query map не удалены, даже если tab hidden. В original root /admin/orders дляprinting применяет current default, redirect закомментирован; прямой query не запрещён оригиналом. Поэтому новый server gate/redirect не вводился. На прямом root печатника текущий tab может не иметь видимого активного пункта, как исходная страница без соответствующей ссылки; это не заявляется новой UX доработкой.
OrderListRolePresentationTest проверяет реальные Tab::isVisible, labels и ordered keys для printing/admin/lite_manager, defaultcurrent/query key сохранены. Gate RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —47tests542assertionsPASS,7.840s92MB; Pint2filesPASS, diffcheckPASS (CRLF warnings only). Тесты не заменяют полный mounted ListOrders/browser и не подтверждают права прямых endpoints. РабочаяDB/права/images не менялись, providers не вызывались.
Осталось13G: company/invoice/payment-request/user-channel/comment/artist/lifecycle role controls и серверная policy matrix; UI rendered tabs в Chrome; badges являются дополнительным оформлением новой версии. Следующий точный шаг — original orders/id.blade и новый order-number/company actions дляprinting; сверить видимость сохранения/удаления номера и финансовых действий. Общие13G/13 IN PROGRESS. Текущие изменения после2dc5b06 без нового коммита.

### ADM-AUD-13G — финансовая колонка printing (IN PROGRESS), 2026-10-17
Original orders/id.blade23,102,113: скрыты update/delete VR, submit firm, invoice/payment_request; номер и firm details доступны, selector без VR доступен. Повторить visibility в production order-number, протестировать VR present/absent и admin/lite_manager. Не менять server policy без отдельного audit.

### ADM-AUD-13G — финансовая колонка printing: перенесена, 2026-10-17
DONE (source/Blade render): original orders/id.blade23,102,113 сверены с order-number. Дляprinting номер накладной и раскрываемые firm details сохранены; update/delete номера, Изменить данные фирмы, весь invoice block (включая PDF link) и payment_request block скрыты. Payment requests relation не читается в Blade дляprinting. Ветка выбора фирмы при отсутствии номера оставлена доступной, как в original (исходный select вне role guard). Admin/lite_manager сохраняют исходные финансовые controls и данные. Изменена только presentation, существующие action authorization/policy не изменялись; это не новый server запрет.
OrderListRolePresentationTest рендерит настоящий Blade с synthetic unsaved Orders/VrNumber/OrderPaymentRequest: VR present/absent, номер/firm видимы printing, финансовые controls/request amount/PDF URL отсутствуют; admin/lite_manager наоборот сохраняют их. Первоначальная fixture не содержала customer_country и publicUrl пытался lazy-load отсутствующую users таблицу SQLite; добавлено реальное поле fixture LV, production publicUrl не подменялся. Итоговый gate RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —49tests572assertionsPASS,7.335s92MB; Pint test PASS, diffcheckPASS (CRLF warnings only). РабочиеDB/images/права не менялись, отправок/платежей/provider calls не было.
Осталось13G: user sales/channel/status, comments/actions и artist/lifecycle UI; альтернативные toggleable колонки/rowActions и server policy matrix — скрытые Blade controls сами по себе не закрывают всю role matrix. Browser layout/role inspection не выполнены. Следующийточныйшаг — original orders/user.blade + orders.blade398–435 и новый order-user Blade, затем remaining role-dependent actions. ADM-AUD-13G/13 IN PROGRESS; текущие изменения после2dc5b06 без коммита.

### ADM-AUD-13G — клиент/продажи (IN PROGRESS), 2026-10-17
Original orders/user.blade и orders.blade398–435: contacts/pdf_locale/client_status доступны printing; sales channel/category/manager скрыты. Новый order-user показывает все. Ограничить sales segment, не убирать язык/статус. Original comments partial без printing guard — не скрывать чаты произвольно.

### ADM-AUD-13G — клиент и продажи: перенесено, 2026-10-17
DONE (source/Blade render): original orders/user.blade и orders.blade398–435 подтверждают printing contacts/ordercount/preferredLocale/pdf_locale/client_status видимы, а sales channel/category/manager скрыты. В production order-user добавлен guard только sales segment; channel/category lookups и manager relation не читаются дляprinting. Контакты/fallback отсутствующего пользователя/язык счёта/client-status controls сохранены. Admin/lite_manager сохраняют sales segment. Это presentation parity, не изменение server permissions.
OrderListRolePresentationTest дополнен настоящим Blade: synthetic loaded User+Orders, отдельные SQLite user_types/a_order_from справочники; printing видит contacts/ordercount/locale/status безsales, orphanfallbackemail/name работает без locale action; admin/lite_manager saleslabels+actions сохранены. Сверен original orders/comments.blade: отдельного printing guard длячатов не найдено, существующие комментарии/client/admin/SA/painter chat buttons не скрывались произвольно. Сквозные chat permissions не проверены этим чтением.
Gate RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —51tests599assertionsPASS,7.202s92MB; Pint test PASS;diffcheckPASS(CRLF warnings only). Все fixture записи SQLite :memory:, рабочиеDB/права/images не менялись, realprovider/mail/payment не вызывались. Визуальная Chrome role gate остаётся TODO.
Осталось13G: artist/sketch/final-image controls, lifecycle/orderbuttons и альтернативные toggleable колонки/rowActions/serverpolicies. Следующий точный шаг — original orders.blade468–714 и order-artist, отделить доступныеprinting final images от скрытых sketch/deadline/assignment/payment. Общие13G/13 IN PROGRESS, изменения после2dc5b06 пока без коммита.

### ADM-AUD-13G — printing artwork (IN PROGRESS), 2026-10-17
Original orders.blade475–715: sketches/deadline/painter_payed/show/assign controls hidden printing, final/client images visible; per-final-image status selector outside printing guard. Нужен узкий action статусов готовых изображений, не весь manageArtist. Проверить atomic ownership/type validation и role Blade; без рабочих файлов/провайдеров.

### ADM-AUD-13G — художник/изображения printing: реализовано, 2026-10-17
DONE (source/код/isolated): original orders.blade475–715 скрывает printing наброски, deadline, painter_payed, show-client и assignment controls; final pictures/client_images и селектор статуса каждой готовой картины доступны. Сверен original Account/AccountController::changeOrderPainterImageStatus: status+updated_at update по image/order, без mail. Новый order-artist скрывает sketches/deadline/payment/show/general manage дляprinting, сохраняет painter identity/final+client images/printing identity. Наличие painter остаётся условием галерей, как existing original block.
Добавлен узкий OrderArtistActions::paintingStatuses (managePaintingStatuses) и регистрация OrdersTable. Только список готовых изображений и status options из a_painter_images_status; нельзя добавлять/удалять/reorder строки. Кнопкаprinting отображается при edit policy и наличии final images. Server authorize+повторный can(update) в callback; OrderArtistService::updatePaintingStatuses валидирует distinct image IDs/status references, transaction lock order, ownership и is_img_painter=1, изменяет лишь image.status/updated_at. Assignment/order fields/письма не затрагиваются. Это сохраняет original функцию статусов через отдельный modal, визуальный контроль отличается от inline select оригинала и ещё требует Chrome.
Тесты: настоящий Livewire action→service→SQLite status update, no assignment/payment changes, read-only user action hidden, чужой final и sketch после первого valid update→ValidationException и полный rollback, Mail::assertNothingSent. Printing production artist Blade показывает final/client и узкий action без sketch/management/deadline/payment/show. Первая image fixture использовала несуществующие локальные пути и helper заменял их placeholder, assertion failed; заменены synthetic absolute example.test URLs, без HTTP загрузок, production helper не менялся.
Итог Artist+RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —60tests653assertionsPASS,8.030s96MB; Pint4filesPASS;diffcheckPASS(CRLF warnings only). Только отдельная SQLite, реальные изображения/DB/права не менялись, внешние отправки не выполнялись. Browser проверка этого modal и роли не выполнена.
Осталось13G: lifecycle/status/date/create/edit/delete/client buttons, альтернативные toggleable колонки/rowActions и полная policy matrix/revocation. Следующий точный шаг — original orders.blade797–885 и production order-state Blade; затем ограничения остальных UI путей. Общие13G/13 IN PROGRESS. Изменения после2dc5b06 пока без нового коммита.

### ADM-AUD-13G — lifecycle printing (IN PROGRESS), 2026-10-17
Original orders.blade719–885: status/create-copy/edit/client/history/photo доступны, delete hidden; existing when_send editor hidden printing, missing-date branch без role guard. Повторить обе ветки в Blade без расширения запретов server policy. Проверить role с edit/add/delete permissions и дату present/absent.

### ADM-AUD-13G — lifecycle printing: перенесено, 2026-10-17
DONE (source/Blade render): сверены original orders.blade719–885 и production order-lifecycle (предыдущее имя order-state в notes было ориентиром, реальный файл order-lifecycle). Printing сохраняет status selector (с edit policy), историю, create-copy/edit/client/history links и outgoing photo open/download. Delete button теперь скрытprinting даже при delete_orders, как original. Existing valid when_send: printing не видит редактор/дату в этом блоке, countdown/urgency сохранены. Missing/invalid when_send: editor остаётся доступен с edit permission, как original else branch без role guard. Server lifecycle actions/policy не менялись.
OrderLifecycleServiceTest дополнен настоящим Blade с printing role и реальными SQLite permissions edit/add/delete: проверены сохранённые actions/photo/countdown, скрытый delete/existing date, missing-date branch, и admin controls длятойже записи. Изменялись только synthetic role/order в SQLite :memory:, рабочие данные/права/images не затронуты. Mail::assertNothingSent.
Общий gate Artist+RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —61tests670assertionsPASS,8.862s96MB; Pint testPASS,diffcheckPASS(CRLF warnings only). Это code/Blade render, не настоящий browser interaction/полный role UAT. Осталось13G: toggleable альтернативные финансовые колонки/rowActions, server authorization/revocation и browser role/layout. Следующий точный шаг — инвентаризация альтернативных колонок (price/payment/invoice/artist) и их visibility vs original, затем action policy regression. Общие13G/13 IN PROGRESS, текущие изменения после2dc5b06 без коммита.

### ADM-AUD-13G — альтернативные столбцы (IN PROGRESS), 2026-10-17
Toggleable sale_price/manager/a_order_from/catid/painter_endtime и vr_number(request number) обходят ранее перенесённый printing presentation. Добавить role visibility на уровне production Column, чтобы сохранённый toggle не раскрывал данные. Остальные allowed columns оставить; тестировать настоящий OrdersTable schema.

### ADM-AUD-13G — альтернативные колонки printing: исправлено, 2026-10-17
DONE (production schema): обнаружено, что toggleable sale_price, manager.email, a_order_from, catid, painter_endtime и vr_number с payment-request number позволяли повторно показать скрытые данныеprinting через column manager. На эти6Column добавлен role visible guard printing=false. Основной номер остаётся в number_controls, контакты/оплата-status/готовые изображения/printing identity/created_at доступны. Admin/lite_manager сохраняют альтернативные столбцы. Ранее permanently visible(false) legacy columns не включались. Это защита presentation на уровне schema, не фильтрация серверной модели/новый API запрет.
OrderListRolePresentationTest строит настоящий OrdersTable::configure(Table), проверяет Column::isVisible для6закрытых и6разрешённых столбцов всех3ролей, 36проверок. Данные не загружаются, mock используется только как HasTable host; проверка не является mounted column-manager/browser session test. Сохранённые toggle preferences не отменяют role visible predicate; фактический browser toggle ещё TODO.
Gate Artist+RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —62tests706assertionsPASS,8.343s98MB; Pint2filesPASS,diffcheckPASS(CRLF warnings only). SQLite fixtures, рабочиеDB/права/images не затрагивались, providers не вызывались. Следующее: server action permission/revocation matrix и открытый modal нового managePaintingStatuses; затем browser role/filter/sort/pagination/summary UAT при доступе. Общие13G/13 IN PROGRESS, текущие изменения после2dc5b06 без коммита.

### ADM-AUD-13G — revoked modal gates (IN PROGRESS), 2026-10-17
Проверить actual Livewire callbacks после отзыва edit/delete_orders в открытом modal: manageArtist/managePaintingStatuses/updateOrderStatus/updateOrderDeliveryDate/deleteOrder. Сервисы должны не вызываться, SQLite запись/статусы неизменны. Фикс только если evidence выявит обход; роли рабочего приложения не менять.

### ADM-AUD-13G — отзыв прав в открытых формах: проверен, 2026-10-17
DONE (isolated permission gate для5actions): добавлены Livewire tests manageArtist/managePaintingStatuses/updateOrderStatus/updateOrderDeliveryDate/deleteOrder. В каждом сценарии fixture пользователь имеет edit/delete_orders, настоящий action открывается (assertActionMounted), данные формы заполнены (кроме delete безschema). Затем только в SQLite удаляется permission_role конкретной роли, auth получает свежийUser; can(update/delete)=false подтверждено перед callMountedAction. Service mocks shouldNotReceive(update/updatePaintingStatuses/updateStatus/updateDeliveryDate/delete) доказывают остановку до записи/провайдеров, реальная SQLite order/image запись неизменна, Mail::assertNothingSent. Production handlers в этом шаге не менялись: существующие Filament authorize и callback guard новогоpainting action срабатывают.
Первый lifecycle test ошибочно вызывал fillForm([]) у confirmation-only delete, получил mountedActionSchema0 missing; helper исправлен пропуском fill при пустомdata. Это test fixture ошибка, не runtime regression. Guard тесты выполнялись с fresh authenticated User, как отдельный запрос; долгоживущий auth объект с заранее загруженными relations/permissions этим тестом не проверен.
Итог Artist+RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —64tests746assertionsPASS,8.686s98MB; Pint2testsPASS,diffcheckPASS(CRLF warnings only). Рабочие роли/DB/images не затронуты, реальных писем/платежей/этикеток не было. Это не полная server action matrix: invoice/payment-request/firm/user controls и остальные CRM actions остаются отдельными gates, browser UAT всё ещё открыта.
Следующий точный шаг — source+tests финансовых actions (manageVrNumber/createPaymentRequest/editInvoiceFirm/generateInvoice/approveInvoice/updatePaymentStatus/updatePrepayment) на active permission/revocation; reuse существующих тестов и не повторять прошедшие provider gates без причины. Общие13G/13 IN PROGRESS, текущие изменения после2dc5b06 без нового коммита.

### ADM-AUD-13G — финансовые modal права (IN PROGRESS), 2026-10-17
Проверить7production record actions OrdersTable на revoked edit_orders: VR/payment request/firm/generate/approve/payment status/prepayment. Использовать getAction из настоящей configure без копирования handlers и запрещающие mocks сервисов. Реальные payment/mail/PDF не запускать.

### ADM-AUD-13G — финансовые формы после отзыва прав: проверены, 2026-10-17
DONE (isolated permissions для7productionactions): новый OrderFinancialActionPermissionsTest получает реальные record actions из OrdersTable::configure(Table)::getAction и переносит их в минимальный Livewire table host; обработчики/authorize/schema не копируются. Проверены manageVrNumber/createPaymentRequest/editInvoiceFirm/generateInvoice/approveInvoice/updatePaymentStatus/updatePrepayment. Длякаждого действия edit_orders разрешает mount (assertActionMounted), форма заполняется либо confirmation остаётся открытым, затем fixture permission_role удаляется и auth получает fresh User. can(update)=false, callMountedAction не вызывает сервис; повторный raw mount безправ также заблокирован. SQLite orderpayment/prepayment/pdf flags и vr_numbers неизменны.
Сервисы UpdateOrderVrNumberService::update, OrderPaymentRequestService::createForOrder, OrderInvoiceService::generate/approve, OrderPaymentService::updateStatus/updatePrepayment запрещены mocks; только defaultFirmData разрешён для открытия формы. HTTP preventStrayRequests+assertNothingSent, Mail fake+assertNothingSent. Initial fixture не содержала user_types, который нужен при построении production config; тест получил error, исправлено добавлением пустого synthetic справочника вSQLite, production не менялся. Никаких обходов прав не обнаружено, callback code не менялся.
Итоговый gate FinancialActionPermissions+InvoiceService+PaymentService+UpdateVrService+Artist+RolePresentation+Summary+Tabs+SearchFilters+Rotation+Lifecycle —75tests854assertionsPASS,10.579s104MB; Pint newtestPASS,diffcheckPASS(CRLF warnings only). Проверки записи толькоSQLite, invoice generation controller mocked в существующих tests; рабочиеDB/roles/images не затрагивались, платежи/внешние письма/provider calls не выполнялись.
Это подтверждение7финансовых permission branches, не сквозная browser приёмка оплаты/счёта/фирмы и не вся servermatrix. Осталось13G: user locale/client-status/recipient-email/review and chat actions revoke/forged checks; визуальный printing layout и browserfilters/sort/pagination/summary. Следующий точный шаг — existing OrderUserService/actions tests и production callbacks user/client controls, затем общий review новых изменений и commit по запросу пользователя. Общие13G/13 IN PROGRESS, текущие изменения после2dc5b06 пока без коммита.

### ADM-AUD-13G — клиентские modal права (IN PROGRESS), 2026-10-17
Production OrdersTable actions updateUserPdfLocale/updateClientStatus/composeRecipientEmail/requestReview используют edit_orders через update policy. Расширить общий permissions harness реальными actions, проверить mounted→revoke→no-service и forged mount; user fields неизменны, notification/mail/http fake.

### ADM-AUD-13G — клиентские actions после отзыва прав: проверены, 2026-10-17
DONE (isolated permission gate4actions): updateUserPdfLocale/updateClientStatus/composeRecipientEmail/requestReview используют update policy(edit_orders) в production OrdersTable. Общий тестовый файл переименован OrderFinancialActionPermissionsTest→OrderRecordActionPermissionsTest, host также OrderRecordPermissionsHarness, т.к. теперь включает11финансовых/клиентских actions. Это незакоммиченный новый файл, прошлые исторические записи о прежнем имени не переписывались.
Длякаждого из4customer actions реальный schema/authorize/callback получен через production configure::getAction, mount подтверждён, данные заполнены, затем толькоSQLite permission_role удалён и auth fresh User. can(update)=false, submit и повторный raw mount не вызывают OrderUserService::updatePdfLocale/updateClientStatus, OrderRecipientEmailService::send, OrderReviewRequestService::send. User2 pdf_locale=lv/client_status1 неизменны. HTTP/Mail/Notification fake/assertNothingSent; внешних отправок нет. Дополнен synthetic user_types и fields locale/pdf_locale/client_status/user_id, production не менялся. Отзыв проверен при fresh authenticated User между запросами, не на долгоживущем cached object.
Gate RecordActionPermissions+OrderUserServiceTest+OrderRecipientEmailServiceTest+OrderReviewRequestServiceTest+RolePresentation+Lifecycle+Artist —39tests395assertionsPASS,5.719s98MB; Pint newtestPASS,diffcheckPASS(CRLF warnings only). Это иной targeted набор, не уменьшение предыдущего75tests gate. SQLite fixtures, рабочие роли/DB/images не менялись. Сквозной браузерный email/review/send не выполнялся.
Следующее13G: existing client/admin/painter/SA chat actions и revoke/forged gates, не дублировать уже покрытые tests; затем полная source matrix Orders и browser при доступном Local browser use. Общие13G/13 IN PROGRESS; текущие изменения после2dc5b06 без нового коммита.

### ADM-AUD-13B — браузерная сверка фильтров: повтор доступа, 2026-10-17
Статус: IN PROGRESS; браузерная приёмка ожидает включения Local browser use в настройках Work. Повторный cua.getState вернул apps=[], browsers=[] и ошибку «Local browser use is disabled in Work settings». Страницы в этом запуске не открывались; новых визуальных результатов нет. Пользователю отправлен запрос включить доступ.
Следующая последовательность на viar13.loc/filament/orders и viarcanvas.loc/admin/orders: (1) одинаковые период12–13.08.2026 + цена22 + размер40x40 и номера результатов; (2) независимые метки/снятие одной границы/сброс; (3) клиент, телефон, OPR, менеджер по существующим данным; (4) номер default→DESC→ASC→default и сохранение фильтров; (5) размер10 при фильтрах,13 без фильтров,25/50/100 и переход страниц; (6) сводка количества/суммы и ясность активной вкладки. Все действия только чтение, без изменения заказов/прав/изображений и обращений к провайдерам. Автотесты не заменяют этот browser gate.

### ADM-AUD-13G — переписка: отзыв прав в actions, 2026-10-17
DONE (изолированная проверка 5 production actions): OrderRecordActionPermissionsTest дополнен sendClientChatMessage/sendPainterChatMessage/manageAdminChat и readClientChatMessage/readPainterChatMessage. Три реальные формы открываются с edit_orders и заполненным черновиком; затем permission_role удалён только в SQLite :memory:, auth получает свежий User, can(update)=false. Submit и повторный raw mount не вызывают send/add; поддельный raw mount read с message_id также не вызывает markMessageAsRead. Action/schema/authorize извлекаются из настоящего OrdersTable, не копируются. Добавлена пустая fixture order_admin_comments для рендера внутренней истории, её count остаётся0. Для client/painter отсутствие service call доказано mocks; новые fixture tables их сообщений не создавались.
Original сверка: VoyagerAdminController::update_admin_chat_ajax пишет comment/orders_id/user_id/timestamps в order_admin_comments; AccountController::admin_to_client_message_read/admin_message_read меняют admin_is_read в соответствующих потоках. Бизнес-логику/поля production в этом шаге не меняли. Для SA повторно использованы существующие tests inline stale/revoked permissions, history refresh revocation, reader denial, command validation/idempotency и fake transport, без дублирования.
Gate: RecordActionPermissions+ClientChat+PainterChat+AdminChatService+SaChat+SaCommand —107tests1117assertionsPASS,12.664s106MB. Pint исправил только import order/line endings нового теста. HTTP/Mail/Notification assertions в новом сценарии подтверждают отсутствие отправок; весь запуск изолирован от рабочей БД. Не сквозной Chrome UAT и не полная матрица ролей/переписки. ADM-AUD-13G/13 IN PROGRESS; 13B browser по-прежнему ожидает Local browser use. Следующий точный шаг: проверить отзыв прав у inline OrderChatComposer client/admin/painter (отдельный UI путь), переиспользуя существующие fixture; затем browser13B после восстановления доступа. Нового коммита нет.

### ADM-AUD-13G — встроенные формы: IN PROGRESS, 2026-10-17
Проверить прямые Livewire send и refresh уже открытого OrderChatComposer после отзыва только edit_orders, оставив browse_admin/read_orders. По текущему коду composer поддерживает client/painter/sa; admin использует проверенный manageAdminChat, отдельного admin composer нет. Добавлять новый путь без исходного требования не нужно. Повторно использовать SQLite fixtures ClientChat/PainterChat, mock send запрещён; отдельно подтвердить сохранение read policy при запрете update.

### ADM-AUD-13G — встроенная переписка: проверено, 2026-10-17
DONE (изолированный send/refresh gate): в OrderClientChatTest и OrderPainterChatTest добавлен сценарий с двумя открытыми production OrderChatComposer: sender с черновиком и viewer. Из SQLite fixture удаляется только edit_orders; свежий User сохраняет доступ к panel и view_orders (assert true), update false. send и $refresh возвращают403, service send запрещён mock, таблица сообщений остаётся пустой, Mail nothing sent. Это проверка текущего полномочия на двух входах компонента; восстановление draft после403 не заявляется.
ClientChat+PainterChat:56tests567assertionsPASS,5.895s92MB. Pint обоих файлов выполнен; diff не содержит посторонних функциональных правок. Admin stream в OrderChatComposer отсутствует по замыслу: внутренний чат использует manageAdminChat (предыдущий проверенный gate), новый composer не добавлялся. SA send/revoke/history уже покрыты предыдущим107tests gate. РабочиеDB/permissions/images не изменены, внешних отправок нет.
Дополнительно source review блока товаров: original vendor/voyager/orders/items.blade и новый order-product оба скрывают subtotal/discounts/deliverytotal/coupons/review link от printing; role передаётся в shared partial параметров товара. Это сверка расположения guards, не полная проверка значений/всех типов product и не визуальная приёмка. Найденные начальные неверные пути admin/orders и order-products уточнены на реальные файлы через rg --files.
Осталось13G: итоговая сводка role matrix по всем блокам списка/редактирования и проверка общих shared product partial на role guards; browser role/layout и13B по-прежнему ждут Local browser use. Следующий точный шаг — shared partial all_basket_order_items и исходные цены/параметры в printing, без расширения проверки на новые произвольные роли. Общий ADM13 IN PROGRESS; нового коммита нет.

### ADM-AUD-13G — параметры товара: IN PROGRESS, 2026-10-17
Общий partial all_basket_order_items байтово совпадает с original по git diff --no-index (различий нет). Подтвердить рендер production order-product для printing/admin/lite_manager: сохранены размеры/упаковка/количество, printing скрывает общую стоимость/итого/цену позиции/review, Express сокращён без цены. Номинал подарочной карты в original виден printing — не скрывать его как стоимость заказа. Проверка только synthetic models, без БД/медиа writes.

### ADM-AUD-13G — параметры товара: проверено, 2026-10-17
DONE (source и production Blade render выбранных веток): git diff --no-index между original/current partials/all_basket_order_items.blade.php не выявил различий. Новый OrderListRolePresentationTest рендерит настоящий order-product со shared partial для printing/admin/lite_manager на synthetic несохранённом заказе. Размер40x60/упаковка/название/подарочный код/номинал79€/delivery comment доступны всем3ролям; printing не видит Стоимость/Итого/цену позиции/review/доплату Express/coupon label, остальные видят. Printing сохраняет сокращённый Express. Номинал карты намеренно не скрыт, так как original partial показывает его вне role guard. Production не менялся.
Gate OrderListRolePresentationTest10tests156assertionsPASS,1.006s86MB; Pint выполнен. Это не проверка всех basket типов/lookup БД и не browser screenshot. РабочиеDB/images не менялись.
Найдено TODO ADM-AUD-13A (изображения товара): original vendor/voyager/orders/items.blade.php для construct/canvas/modular/oil/gallery activeImage ищет существующий uploads SVG и использует его как превью, иначе raster; новый order-product всегда использует raster activeImage. Для is_oil_portrait original также выводит OilHeader promo image, в новой ветке отсутствует. Поиск admin order views/services не нашёл альтернативной реализации. Следующий точный шаг — изучить текущие image helpers/storage roots, перенос SVG fallback и oil promo с сохранением ссылок без изменения файлов/БД; покрыть fake storage. Не считать ADM13A/13G закрытыми; browser13B ожидает Local browser use. Нового коммита нет.

### ADM-AUD-13A — SVG и oil promo: IN PROGRESS, 2026-10-17
Source confirmed: BasketController::addToBasketConstruct сохраняет companion SVG в public/uploads с тем же именем, заменяя raster suffix. Существующие order_image helpers используются другими страницами, менять их глобально не требуется. Перенести выбор существующего локального uploads SVG в отдельный admin helper; выводить SVG как изображение с raster fallback, сохраняя оригинальную raster ссылку и компактный размер. Oil promo брать из существующего oil_header.images через OilHeader, первый элемент JSON, URL через текущий Voyager storage adapter; пустой/повреждённый список не выводить. Проверки только fake uploads/SQLite, без записи существующих файлов и без сетевого скачивания.

### ADM-AUD-13A — SVG и oil promo: реализовано, 2026-10-17
DONE (код и изолированные проверки): новый Support/Admin/OrderProductPreview выбирает существующий SVG companion из uploads disk (кореньpublic) для construct/canvas/modular/oil/gallery флагов, как original primary preview branch. Поддержаныjpg/jpeg/png/heic/heif, /storage→/uploads, URL с host/query и public prefix; traversal/неuploads/неподдержанныйsuffix не используются. Проверка локальная, сетевых probes/download нет. В order-product src переключается на SVG при наличии; raster href сохранён, Alpine error.once возвращает raster src. Размер150x170contain сохранён. SVG выводится как img, без встраивания произвольной SVG разметки в DOM; отличие от raw-inline original требует визуального gate на реальном составном SVG (включая возможные external references), не объявляется визуально идентичным.
Для is_oil_portrait первая промокартинка берётся из существующего OilHeader::getOilPromoImage→oil_header.images. Один lookup на render колонки только при oil позиции, URL через существующий Voyager storage adapter; пустые/повреждённые/нестроковые/недопустимые URL пропускаются. Первый test выявил, что adapter превращает javascript-подобный input в storage path; добавлена проверка raw value до adapter. Никаких новых таблиц/полей/перезаписи картинок не требуется.
Gate ProductPreview+RolePresentation+OrderMediaUrl —36tests270assertionsPASS,2.572s86MB; Pint newhelper/newtestPASS;diffcheckPASS(CRLFwarnings). Новый test покрывает5форматов×5типов, fallback/missing/noneligible, сохранение rasterhref, отсутствие rawsvg, traversal, oilfirst/malformed и сохранность fakefiles. Только fake uploads/SQLite :memory:, HTTP nothing sent. Реальные файлы/DB/images не изменялись.
Осталось13A: визуальная SVG/oil приёмка вChrome и остальные desktop/mobile блоки. Browser13B всё ещё ожидает Local browser use. Следующее точное действие при доступеChrome — реальный order product соSVG/oil и фильтры13B; независимый source шаг — сводная role matrix остальных comments/recipient блоков и полей create/edit противoriginal. Общие13A/13G/13 IN PROGRESS, нового коммита нет.

### ADM-AUD-13G — получатель и комментарии: IN PROGRESS, 2026-10-17
Original user_data/comments и родительские includes не скрывают эти блоки от printing/lite_manager. Найдены отличия: payer_phone='-' должен переходить к portrait Phone; WhatsApp не должен терять начальные нули; pickup truck icon зависит от deliv_price>0. Порядок inline previews original: клиент created_at ASC, художник created_at DESC; новая eager loading используетidASC обоим. Исправляются эти ветки, проверки production Blade и query callback изолированы. Badge SA в original считает сообщения, в новой диалоги: отдельная сверка13F после проверки mark-read/service.

### ADM-AUD-13G — получатель и порядок комментариев: исправлено, 2026-10-17
DONE (код и изолированные проверки выбранных веток): сверены original orders/user_data, orders/comments, родительские includes и User::get_user_acc_comments/get_painter_all_comments. Ролевых guards printing/lite_manager вокруг этих двух блоков нет; новые ограничения не вводились.
В order-recipient восстановлен fallback payer_phone='-' к portrait Phone, как original; WhatsApp сохраняет начальные нули (цифровая нормализация форматирования сохранена); pickup icon truck выбирается только при deliv_price>0, а не при любом непустом значении. В OrdersTable eager loading клиентских inline комментариев теперь created_at ASC/id ASC, художника created_at DESC/id DESC, вместо обоих id ASC. История в отдельных modal actions не менялась. ID служит стабильным порядком при одинаковой дате; original не задаёт tie-break.
OrdersDisplayParityTest дополнен рендером placeholder/portrait/leading-zero телефона, pickup charges0/-5/4 и реальными eager-load callbacks production Table на SQLite: id намеренно не совпадает с датой, чужой order исключён. Gate DisplayParity+RolePresentation+ProductPreview+MediaUrl+RecordActionPermissions —55tests475assertionsPASS,6.334s98MB; PintPASS. РабочиеDB/права/images не менялись; тестовые fixtures изолированы. Это не browser visual и не полная рольвая матрица create/edit.
TODO ADM-AUD-13F: исходный badge WhatsApp SA считает непрочитанные inbound/bot messages, новая колонка показывает число unread conversations. Проверить действующие mark-read/snapshot правила и согласовать отображение с original без изменения данных; пока в этом шаге не исправлялось. Дополнительно original admin inline preview имеет дату в title автора, новая версия её не выводит — проверить вместе с оформлением13A. Следующий source шаг — эти SA badge/preview metadata; браузерные13A/13B по-прежнему ожидают Local browser use. Общие13G/13 IN PROGRESS, нового коммита нет.

### ADM-AUD-13F — SA badge: IN PROGRESS, 2026-10-17
Source review подтвердил два независимых состояния в original: badge в orders/comments считает inbound либо outbound bot со status!='read'; отдельные SA conversation screens используют unread_for_manager. Новый OrderSaChatService намеренно меняет только unread_for_manager и сохраняет delivery statuses; это правило уже покрыто tests и не подлежит замене ради badge. Восстановить original message predicate только в badge списка, title относится к сообщениям. Manager acknowledgement не обязан менять этот delivery-status счётчик; отдельно сохраняется глобальный счётчик диалогов. Также вернуть date title автора admin inline preview, как original. Проверить оба состояния и исключение manager/system/read сообщений без рабочих записей.

### ADM-AUD-13F — SA badge и metadata: исправлено, 2026-10-17
DONE (код/изолированный gate): в order-comments восстановлен original predicate по уже загруженным saMessages: direction=inbound либо direction=outbound/from.type=bot (case insensitive), status строго!='read'. Badge теперь показывает число сообщений, а не unread conversations. Title изменён на «Непрочитанные сообщения WhatsApp». Никаких дополнительных DB queries для счётчика не добавлено: production OrdersTable уже загружает saMessages. Fixtures inline comments дополнены loaded saMessages.
Важное подтверждённое различие состояний: original orders/comments использует message status, а отдельный original AdminSaIntegrationController::conversationShow/markConversationAsRead меняет unread_for_manager. Новый OrderSaChatService сохраняет эту независимость и защищает отметку snapshot token. Service/transport/statuses/read mutation не менялись. Manager acknowledgement само по себе не обнуляет message-status badge — таковы два отдельных состояния original, это отражено тестом. Глобальный счётчик SA-диалогов не менялся.
Дополнительно13A: автор admin inline preview снова имеет title с created_at в форматеd.m.Y H:i, как original; пустая дата не выводится. Это дата в подсказке, не новый текстовый блок.
Gate DisplayParity+SaChat+SaCommand —64tests478assertionsPASS,6.202s98MB; PintPASS,diffcheckPASS(CRLFwarnings). Production Blade test различает4eligible messages в1conversation, inbound/read/bot/manager/system/nullstatus/malformedJSON и независимость от manager ack; all-read убирает badge. Существующие SA tests подтверждают snapshot/stale/role guards/idempotency и неизменность delivery/client flags приread. Только SQLite/fake transport; рабочиеDB/images не затрагивались, внешних отправок нет. Визуальная проверка Chrome не выполнялась.
Осталось: browser13A/13B;13F сквозная визуальная переписка,13C/13D полная create/edit parity и рольвая матрица. Следующий независимый source шаг — инвентаризация полей/role guards форм создания и редактирования заказа противoriginal; не считать текущую сверку всего ADM13 завершённой. Нового коммита нет.

### ADM-AUD-13D — выбор доставки в создании: IN PROGRESS, 2026-10-17
Source original order_create::filterPickupOptionsByCountry/filterTownOptionsByCountry/syncDeliveryCreateFields: страна фильтрует реальные варианты, смена способа очищает несвязанные ID, выбирает первый допустимый, pickup_Riga/Daugavpils использует1/2; адрес/город синхронизируются. Новый AdminOrderCreateForm пока загружает все точки/города без фильтра и не синхронизирует адрес. Переиспользовать каталоги OrderDeliveryEditor и адаптировать имена state создания; учитывать original city=null при переключении pickup и отдельный selection callback. Проверки на существующих SQLite fixtures доставки; изменение рабочих заказов не требуется. Остальная13C/13D инвентаризация остаётся открытой.

### ADM-AUD-13D — страна и пункты доставки: исправлено, 2026-10-17
DONE (schema/изолированные проверки): AdminOrderCreateForm теперь использует существующие OrderDeliveryEditor::countries/pickups/towns, включая RU-переводы, is_show/sort, multi-country/ALL и country filtering. Country/method live callbacks синхронизируют ID и адрес через тот же editor с адаптацией плоских имён state создания. При переключении на pickup city очищается, как original create script; при ручном выборе пункта city задаётся названием, кроме мастерскойVIAR. При выборе города обновляется city. При уходе в другой способ очищаются несвязанные pickup/town IDs; city field скрыт наpickup. Стандартные Riga/Daugavpils1/2 сохранены. Серверный create delivery mapping не менялся: сохраняет базовые справочные названия/short code.
Добавлен harness в OrderDeliveryFormTest: извлекает только реальные production поля доставки из AdminOrderCreateForm, использует существующие SQLite каталоги/переводы, проверяетLV→EE→LV, первыйдопустимыйпункт, скрытый/чужойпункт, selection address/city, Riga default и resetIDs. Это не копия schema и не полный CreateRecord браузер. Существующие create defaults/items/discount/service tests повторно прошли.
Gate DeliveryForm+AdminCreateForm+AdminCreationService —19tests185assertionsPASS,2.277s90MB; PintPASS,diffcheckPASS(CRLFwarnings). РабочиеDB/права/images не менялись, реальных отправок нет.
Открыто13D: original email lookup показывает баланс бонусов и ставит max bonus по клиенту (order_create582–586), новый selector заполняет контакты без UI баланса/max; сервер уже проверяет balance под transaction lock. Следующий source шаг — исходный email lookup/bonus UI и новый client selector/существующие tests. Полные13C/13D field matrix, UI, media/PDF и browser gates не завершены. Общий ADM13 IN PROGRESS, нового коммита нет.

### ADM-AUD-13D — бонусы и email lookup: IN PROGRESS, 2026-10-17
Original show_bonuses использует введённыйemail, показывает положительный баланс/max, заполняет имя/фамилию/оба телефона существующего клиента. Backend нового create также выбирает клиента по LOWER(email), а не client_id. Добавить read-only отображение баланса/max по текущемуemail и lookup наblur, согласовать client_id с email; выбор клиента продолжает заполнять email/контакты. Не хранить trusted balance в hidden state; server transaction/lock проверки остаются. Тестировать смену клиента/email, отсутствие бонусов, max validation и неизменность actual fixture balances.

### ADM-AUD-13D — бонусы клиента: реализовано, 2026-10-17
DONE (schema/изолированные проверки): AdminOrderCreateForm показывает «Доступно бонусов» и max bonus по текущемуemail, используя тот же LOWER(email)/trim lookup, что server create. Баланс берётся из User при вычислении schema, не из hidden/client-supplied balance. При отсутствии положительного баланса поле скрыто и не входит в dehydrated state. Ввод существующегоemail наblur связывает client_id и заполняетfirst_name/last_name/phone/recipient_phone, как original show_bonuses. Выбор client_id по-прежнему заполняет email/контакты, helper/max рассчитываются поemail; сменаemail не оставляет прежнийclient_id. Текущая введённая сумма при смене на клиента с меньшим положительным балансом не списывается и не урезается молча — форма показывает max validation error.
Backend AdminOrderCreationService transaction/lock и проверка недостаточного баланса не менялись. Нормальная email:rfc валидация поля сохранена; lookup не добавляет отличающийся от неё FILTER_VALIDATE_EMAIL. Это не новый endpoint, реальных списаний не выполнялось.
AdminCreateForm production fields извлекаются в минимальный Livewire harness сSQLite users: выборклиента, emailcase, контакты, max12.5/3, изменениеDBbalanceдо1 после открытия, unknownemail→client_idnull/bonus исключён, отсутствие списания/HTTP/Mail. Gate AdminCreateForm+CreationService+DeliveryForm —20tests214assertionsPASS,2.505s92MB послеPint; diffcheck требуется перед сдачей. Это не полныйCreateRecord/browser и не проверка всех полей original формы.
Осталось13D: итоговые суммы/округление/копирование данных клиента и file/type UI;13C оставшиеся edit artist/media/PDF эффекты. Следующий точный source шаг — сверить original syncOrderTotalsFromItems/updateTotalWithSale и новый total/basketItem/service итог с действующими tests, устранять только подтверждённые различия. Browser13A/13B остаётся открытым из-за Local browser use. Общий ADM13 IN PROGRESS; нового коммита нет.

### ADM-AUD-13B — постоянная панель фильтров (IN PROGRESS), 2026-10-17
По прямому запросу пользователя перенести фильтры из dropdown в постоянную панель над таблицей, как original. OrdersTable использует FiltersLayout::AboveContent, responsive columns1/2/4/6. Predicate/состав/отложенное применение не менять. Повторный Chrome reload после правки вернул Local browser use is disabled in Work settings, визуальная приёмка новой панели пока не выполнена. Перед отключением Chrome confirmed new combined period12–13Aug2026/price22/size40x40: IDs18440,18439, summary2/44€,4отдельныеметки. Original datepicker при fill дописал текущую дату; его результат0 не является сравнимой выдачей. Продолжить ввод дат через original calendar после доступа; не объявлять browser parity завершённой.

### ADM-AUD-13D — округление итога формы: IN PROGRESS, 2026-10-17
Original create JS округляет сумму позиций, базу после бонусов, сумму после скидок и финал после доставки последовательно. Новый total пока округляет только при number_format конечного результата: на дробных центах сумма отличается. Восстановить этапы округления без изменения DB расчёта/контракта хранения; покрыть настоящий production Placeholder. Скидки и доставка в DB сохраняются отдельно от price/sale_price, как original.

### ADM-AUD-13D — округление итога: исправлено, 2026-10-17
DONE (production placeholder): AdminOrderCreateForm::total повторяет original order_create JS rounding stages: sum(items)→2decimals; bonus subtraction→2; EUR then percent→2/clamp0; delivery addition→2. Ранее только final number_format давал иной итог для price1.004/delivery0.004 (1.01 вместо1.00). Backend хранение/списание бонусов не менялось; price/sale_price содержат базу, discount/delivery отдельно, как original create_admin_order_action.
Production order_total Placeholder включён в существующий Livewire schema harness. Восемь сценариев: fractional cents, sum-before-round, bonus+EUR, bonus+percent, discount>subtotal, bonus>subtotal,100percent, thousands formatting. Gate AdminCreateForm+CreationService+DeliveryForm21tests224assertionsPASS,3.007s94MB; PintPASS. ТолькоSQLite/fakes, рабочиеDB/images не менялись, внешнихотправокнет. Это не realbrowserCreateRecord и не полной финансовой логикиPDF gate.
Следующее13D: file accepted types и создание для исходного клиента/country/defaults с проверкой original;13C remaining media/artist/PDF sideeffects. Browser13B/13A панелифильтров/SVG требуетвосстановленияChrome (последнийreload localbrowserdisabled). ОбщийADM13 IN PROGRESS, нового коммита нет.

### ADM-AUD-13D — create source files: IN PROGRESS, 2026-10-17
Original order_create input accept=image/*,image/heif,image/heic (initial and added positions); create backend сохраняет исходники без отдельного PDF workflow. Новый create UI/service дополнительно разрешает PDF/PostScript. Согласовать create сimage/HEIC/HEIF, не менять отдельный edit attach документов. Найден cleanup gap: storeSources регистрирует path только после успешного putFileAs; частичная запись сfalse/throw не отслеживается. Резервировать новый collision-checkedpath до записи и тестировать failure/rollback вfakeuploads; реальные файлы не трогать.

### ADM-AUD-13D — файлы создания: исправлено, 2026-10-17
DONE (код и изолированные проверки): accept в AdminOrderCreateForm и MIME rules AdminOrderCreationService согласованы с original order_create: image/*, HEIC, HEIF. Дополнительные PDF/PostScript убраны именно из создания; отдельный OrderItemEditor attach документов не менялся. Лимит 100 MiB сохранён. Это не подтверждение декодирования всех форматов: реальные HEIC/HEIF и chooser остаются browser gate.
storeSources теперь добавляет collision-checked новый orders/path в список компенсации до putFileAs. Частичная запись с возвратом false удаляется при rollback, как ранее для успешно записанных файлов; существующие файлы не входят в список. Тест с fake adapter физически пишет partial во временный disk, возвращает false и подтверждает удаление именно нового файла, неизменность existing.jpg и rollback users/orders. Резервирование до write покрывает также throw через существующий catch, отдельный throw adapter scenario здесь не запускался.
Gate CreationService + CreateForm + ItemEditor: 39 tests / 389 assertions PASS, 5.590 s / 96 MB после Pint. Документы PDF/PS отклоняются до DB/file writes; existing collision/source/bonus rollback tests и edit attach regression прошли. Все записи только SQLite/fake uploads; рабочая БД, изображения и провайдеры не затронуты. Diffcheck перед сдачей. Нового коммита нет.
Осталось: полный CreateRecord/browser permission flow, реальные uploads/PHP limits, 13C edit media/PDF side effects и 13A/13B визуальная приёмка. Следующий точный шаг — source review create/update model save failures и соответствие полей формы исходному обработчику; браузерную сверку фильтров продолжить после восстановления доступа. ADM13 IN PROGRESS.

### ADM-AUD-13D — model save veto: IN PROGRESS, 2026-10-17
Создание не проверяет boolean результат четырёх save: новый клиент, списание бонусов, первый order insert и финальный order update после uploads. Eloquent saving listener может вернуть false без exception; это допускает ложный успех/несогласованное списание. Привести к уже действующему UpdateOrderService правилу: false → ValidationException в transaction, cleanup новых файлов через существующий catch. Проверять четыре ветки только SQLite/fake uploads, бизнес-payload original не менять.

### ADM-AUD-13D — отмена model save: исправлено, 2026-10-17
DONE (код/изолированная проверка): четыре save результата в AdminOrderCreationService теперь проверяются. False при новомUser → email ValidationException, debit → bonus error, initialOrder/finalItems → items error. Все ошибки внутри transaction; существующий catch компенсирует зарегистрированные новые paths. Бизнес-поля original, successful flow и notifications flag не менялись. Это исправление отказа сохранения, согласованное с уже существующим UpdateOrderService, а не новая возможность original.
Один test последовательно проверяет4реальных Eloquent saving veto наSQLite/fakeuploads: initial user/order rollback, bonus balance15 сохранён, final veto после записи файла удаляет новыйфайл; orders0/users1existing во всех ветках, existing.jpg bytes=keep. HTTP/Mail nothing. Listener удаляется после каждого fixture scenario. Gate CreationService+CreateForm+ItemEditor40tests416assertionsPASS,5.194s96MB; Pint выполнен. РабочиеDB/права/images не затронуты, никаких реальных отправок.
Осталось: полная source field matrix 13C/13D, original edit artist/media/PDF sideeffects, реальные Create/EditRecord и browser13A/13B. Следующий точный шаг — сверить существующие media/artist панели в EditOrders с original order.blade (row actions списка не заменяют edit page). ОбщийADM13 IN PROGRESS, нового коммита нет.

### ADM-AUD-13C — галереи редактора: IN PROGRESS, 2026-10-17
Original order.blade содержит отдельные Фото клиента (items.orig_images), Наброски художника и Картины (DB rows с legacy CSV fallback), дополнительные client_images. В OrdersForm эти галереи отсутствуют; только orig_images у карточек позиции. Добавить отдельную media Section с компактными previews/full links/status/date и доступом к существующему manageArtist action. Общий Save не получает paths/media drafts. Original delete/legacy status controls требуют отдельных scoped services/tests и не объявляются завершёнными этим шагом. Рабочие файлы/DB не изменять.

### ADM-AUD-13C — галереи редактора: добавлены, 2026-10-17
DONE (отображение выбранных media веток): OrderMediaForm включён в OrdersForm перед позициями. Галереи Фото клиента изitems.orig_images, Наброски/Картины изorder_painter_images соstatusDefinition иthumbnail/full link, legacyCSV используется только при отсутствии DB rows соответствующего типа, какoriginalorder.blade. Дополнительныеclient_images отдельнойгруппой.150x150contain/lazy/fallback, безопасныеURL через существующий OrderMediaUrl (production paths безdownload/localexists). Изображениянепредлагаютсякобщемусохранению, новыеmediafieldsнеpersist. Footer подключает существующий OrderArtistActions::manage сегоpolicy/service; самостоятельный action не копировался.
Gate MediaForm+DetailsForm+ItemEditor+ArtistService36tests363assertionsPASS,5.245s98MB; PintPASS. Новый test — synthetic models/loadedrelations:4groups, DBprecedence,full/thumbnail links,status/date,legacyextra,unsafeURLomitted,attributesunchanged,emptygroups/noquery. Initialfixtureошибочно massassignedAPainterImagesStatus.title, исправленоforceFill толькоtest. ОстальныеsuitesреальныеSQLite/fakes. РабочиеDB/imagesнеизменены,HTTP/Mailнаружунет.
Честныйостаток: этиtestsнепроверяютполныйmountedEditOrders/footeractioncontext ибраузерныйрендер. Original deleteDB/legacyimages,legacyaggregate status,date,clientcommentsиотдельныеcontrols не объявляютсяперенесёнными. Следующийточныйшаг13C — провестиheader/footeraction mount черезproductionEditRecord/harness среальнымrecord ипроверитьrefill послеизменения; затемoriginalremove handlers сisolatedstorage,безудалениярабочихфайлов. Browser13A/13Bтакжеоткрыт. ОбщийADM13 IN PROGRESS;новогокоммитанет.

### ADM-AUD-13C — media footer action context: IN PROGRESS, 2026-10-17
Проверить именно OrderMediaForm Section footer manageArtist с record-bound Schema, безtablehost. Успех должен обновить status/date галереи, не заменять соседние drafts основнойформы. Reader/revoked edit должны остановиться доservice. ExistingArtistServiceTest fixtures переиспользовать; fullEditRecord/browser отдельно.

### ADM-AUD-13C — действие секции изображений: проверено, 2026-10-17
DONE (изолированный schema action context): ArtistMediaSchemaHarness использует настоящий OrderMediaForm::section с record-bound Filament Schema, без table action host. manageArtist открывается из footer и получает нужный Orders. Реальный сервис меняет статус картинки в SQLite; повторный рендер галереи показывает «Принят», unrelated_draft основной формы сохраняется. Проверка не подменяет action/service и не вызывает общий Save.
Второй сценарий: открыть форму с edit_orders, удалить только fixture permission, авторизовать свежий User; callMountedAction не вызывает mocked update, order остаётся неизменным, в новом рендере кнопки нет. Первоначальный assertActionHidden падал потому, что Filament удаляет недоступный footer action из schema (ActionNotResolvable); проверка исправлена на фактическое отсутствие кнопки плюс can(update)=false. Это тестовое ожидание, production не менялся.
Gate ArtistService + MediaForm + DetailsForm + ItemEditor: 38 tests / 381 assertions PASS, 6.477 s / 98 MB. Pint выполнен. Только SQLite и синтетические URL, почта fake; рабочие БД/права/изображения не менялись. Полный EditOrders page и Chrome всё ещё отдельно требуют проверки.
Следующее 13C — original remove handlers и legacy aggregate status: изучить DB row vs CSV удаления и file side effects, затем реализовать scoped actions с изолированными тестами без удаления рабочих файлов. Общие 13C/ADM13 IN PROGRESS; browser 13A/13B открыт, нового коммита нет.

### ADM-AUD-13C — удаление media: source confirmed, IN PROGRESS, 2026-10-17
Original OrdersController::remove_painter_image/remove_painter_sketch_image/remove_user_image НЕ удаляет физические файлы. DB row удаляется только с order_id+type; CSV дубликаты чистятся по пути/basename. Legacyветка удаляет индекс изCSV. Перенести этот контракт в отдельный scoped transaction service с Gateupdate и snapshotfingerprint, чтобы stale index не удалял другуюкартинку. Никаких Storage/delete/unlink, новых миграций илиперезаписипутей. Сначалаservice+SQLitetests, затемconfirmationactionsвгалерее; этапдоUIнеготов.

### ADM-AUD-13C — сервис удаления изображений: реализован, 2026-10-17
DONE только backend: OrderMediaRemovalService воспроизводит original OrdersController remove_painter_image/sketch/user: удаляет связь/DB row, НЕ физический файл. Для DB row ограничивает order_id и sketch/painting flag; чистит legacy CSV дубликаты по пути/basename, как original. Для старых списков sketch/painting/client удаляет выбранный нормализованный индекс. Gate update_orders, row lock/transaction, snapshot SHA256 пути либо всегоCSV предотвращает удаление по устаревшему состоянию. Save/delete false отменяет transaction. No arbitrary field/path from caller. Новых миграций нет.
Tests ArtistService+MediaForm: 16 tests / 122 assertions PASS, 2.586 s / 94 MB после Pint. Проверены DB sketch/painting, CSV client, basename cleanup, stale/foreign order/wrong type rejection, reader denial, bytes двух fake files сохранены. Нормализованный CSV индекс согласуется с будущим galleryUI; legacy whitespace/пустые элементы не становятся кнопками. Tests не проверяют все model veto branches нового сервиса и не UI action.
ОСТАЛОСЬ этой же задачи: подключить confirmation actions непосредственно у gallery images, server-generated идентификаторы/snapshot, refresh без потери drafts, reader/revocation и stale errors через UI. Сервис пока не доступен из панели; не считать удаление полностью перенесённым. Следующий точный шаг — OrderMediaForm gallery action registration и schema action tests, затем legacy aggregate statuses. РабочиеDB/images не менялись; общий13C/ADM13 IN PROGRESS, нового коммита нет.

### ADM-AUD-13C — gallery delete actions: IN PROGRESS, 2026-10-17
Подключить к каждому удаляемому preview зарегистрированный schema Action сconfirmation. Target формируется сервером и шифруется сorder_id/type/row-or-index/fingerprint; callback проверяетorder иактуальныеправа, вызываетscopedservice. Original itemorig_images не имеетdeletebutton — не добавлять. Validation/staleошибка остаётсявидимойnotificationбезложногоуспеха; galleryrefreshбезformfill. ПроверкиproductionPlaceholderaction черезexistingmedia schemaharness.

### ADM-AUD-13C — кнопки удаления media: подключены, 2026-10-17
DONE (schema/action): у DB sketch/painting и legacy sketch/painting/client previews добавлен iconButton removeOrderMedia, зарегистрированный на Placeholder mediaGallery. Original items.orig_images остаются без удаления. Action требует confirmation, поясняет сохранение физического файла; authorize update и повторный callback guard. Цель зашифрована Crypt: order_id/type/row-or-normalized-index/fingerprint, order mismatch403; forged/invalid token warning, stale/service validation warning+halt. Успех вызывает scoped removal service и refresh record, без formfill и потери drafts. Статические удаления файлов/URL не выполняются.
Production action вызывается через clone зарегистрированного action с server token; Filament сам формирует schema context. Livewire test использует реальный Placeholder action path mediaSection.mediaGallery: mount подтверждён, isConfirmationRequired=true, доsubmitданныенеизменны, послеудаления остаётсявтораякартинка и unrelateddraft, повтор старогоtokenне удаляетеё. Открытоеconfirmation послеотзываeditне вызываетservice. Первоначальнаяпроверка modalheading черезassertSee неувиделамодальнуюразметкувharness; заменена наisConfirmationRequired, этоНЕвизуальнаяприёмкамодальногоокна. Сложный UI/browser confirmation остаётся обязательнымgate.
Gate ArtistService+MediaForm+DetailsForm+ItemEditor:42tests420assertionsPASS,6.901s96MB послеPint. Только SQLite/fakeuploads, существующиеfakefilesсохраняются, рабочиеDB/imagesнеизменены, реальныхотправокнет. Не проверены отдельнымиactiontests forged/crossorderencryptedtoken (serviceforeign/stale/reader уже покрыты) и fullEditRecord. Следующийточныйшаг — этиaction-boundarytests и реальныйmodal послевосстановленияChrome; затемlegacyaggregate statusконтролы. Общий13C/ADM13 IN PROGRESS, нового коммита нет.

### ADM-AUD-13C — границы delete action и modal contract: проверено, 2026-10-17
DONE (изолированные action boundaries): добавлен production Placeholder action test с forged token (warning+halt) и настоящим encrypted token другого order (403). Mock removal::remove запрещён в обоих случаях; CSV обоих заказов неизменны. Это дополняет прошлые service order/type/snapshot и открытый action revocation tests.
Confirmation проверен шире: mountedActionShouldOpenModal=true, isConfirmationRequired=true, getModalHeading корректен, Livewire dispatched sync-action-modals. Vendor InteractsWithActions::syncActionModals и modals.blade используют отдельную синхронизацию/рендер. Прямой toModalHtmlable render из test method был ошибочным (Blade требует $this Livewire context), убран; это не runtime defect. Причина отсутствия modalheading в прежнем captured harness HTML не доказана полностью; actual visible modal не объявляется принятым.
ArtistService+MediaForm:19tests165assertionsPASS,3.618s94MB; Pint выполнен. РабочаяDB/права/файлы не менялись; SQLite/fakes only. Повтор cua.getState в этом шаге вернул Local browser use disabled, Chrome visual gate остаётся открытым.
Следующий шаг13C — original aggregate legacy sketch/painting status endpoints и fields/status_date; сохранить row statuses отдельными. Затем fullEditRecord/Chrome confirm и13B верхняяпанель. ОбщийADM13 IN PROGRESS, нового коммита нет.

### ADM-AUD-13C — legacy aggregate statuses: IN PROGRESS, 2026-10-17
Original OrdersController changePainterSketchImagesStatus/changePainterImagesStatus изменяет только соответствующие orders status/status_date через DB query, без изменения row statuses и без уведомлений. UI показывает общий selector только при CSV fallback (DB rows соответствующего типа отсутствуют). Добавить отдельный scoped update service и два live selectors вmediaSection, не dehydrated вобщийSave, сdate display. Не мигрировать CSV вDB и не смешивать уровнистатусов. Изолированныеproduction schema tests наSQLite.

### ADM-AUD-13C — legacy общие статусы: перенесены, 2026-10-17
DONE (код/schema/изолированные проверки): source OrdersController::changePainterSketchImagesStatus/changePainterImagesStatus обновляет только orders.*_status и *_status_date, без уведомлений и without updated_at. Новый OrderLegacyMediaStatusService сохраняет этот контракт: update Gate, allowed type, exists status, transaction/lock, CSV nonempty и отсутствие DB rows соответствующего типа. Статусы отдельных order_painter_images не меняются; неизвестный status/смена структуры отклоняется.
OrderMediaForm показывает два live Select и дату только для соответствующего CSV fallback, как original order.blade. Select disabled без edit; повторный callback guard. Fields dehydrated(false)/validatedWhenNotDehydrated(false), общийSave не пишет этиdrafts. Ошибка service отображается по полному пути поля, старое значение восстанавливается изDB; успешное изменение refresh record безformfill. Новых миграций/переносаCSV/физическихопераций нет.
Production schema test проверяет initial hydrate, два live updates, dates, сохранение order.updated_at/CSV исоседнегоdraft; servicetest — DB-backedgroup/unknownstatusrejection сunchangedrowstatuses. Gate Artist+Media+Details+Items45tests455assertionsPASS,7.881s96MB;PintPASS. SQLite/fakes only, рабочиеDB/imagesнеизменены, realmailнет. Отдельный legacyselector revoked/stale-live UI error сценарий ещё не покрыт; fullEditOrders/browser такжеоткрыт.
Следующийточныйшаг13C — добратьlegacyselector rejection/revocation иdehydration общегоsave, затемfullEditRecordотображение/actionmodal вChrome;13Bверхнийfilterlayout всёещёнепринятвизуально. ОбщийADM13 IN PROGRESS, коммита нет.

### ADM-AUD-13C — legacy status errors/permissions: IN PROGRESS, 2026-10-17
Добрать production schema сценарии: invalidstatus возвращает прежнеезначение иfullfieldvalidation; DBrowsпоявилисьпослеоткрытия — aggregateupdateотклонён; edit_ordersотозван — serviceневызывается; общийgetStateисключаетstatusfields. ИспользоватьArtistMediaSchemaHarness/SQLitefixtures, не менятьпродуктовуюлогикубезвыявленногоотказа.

### ADM-AUD-13B — фильтры над таблицей: код готов, визуальная сверка открыта, 2026-10-17
Статус: IN PROGRESS (браузерная приёмка).
Постоянный FiltersLayout::AboveContent сохранён; основные поля переставлены по original resources/views/vendor/voyager/orders.blade.php: номер, заявка на оплату, клиент, телефон, цена, оплата, канал, категория, страна, художник, менеджер, размер, период. Дополнительные существующие фильтры идут после них. Даты с/по расположены в двух колонках, подписи клиента/размера/заявки сокращены, примеры вынесены в placeholders. Apply/reset и условия запросов сохранены.
Проверки: SearchFilters, DisplayParity, ListTabs, ArtistService, MediaForm — 71 тест / 714 assertions PASS; Pint и git diff --check PASS. Только SQLite/fakes, рабочие данные и изображения не изменялись.
Chrome getState вернул Local browser use is disabled in Work settings. Следующее действие: визуально сверить верхнюю панель в обеих админках на desktop/mobile и проверить применение/сброс через UI. Полная задача ADM-AUD-13 не закрыта.

### ADM-AUD-13C — завершение проверки ошибок legacy статусов, 2026-10-17
Статус: DONE для изолированных сценариев; полная браузерная приёмка редактора остаётся открытой.
Исправлена найденная регрессия: успешный выбор статуса после невалидного значения теперь очищает ошибку только этого поля. Тесты подтверждают восстановление предыдущего значения при ошибке, отказ после появления DB-изображений, запрет вызова сервиса после отзыва edit_orders и исключение legacy status fields из общего сохранения. Эти сценарии входят в указанный gate 71/714. Следующее действие: полный EditRecord и видимая модальная форма в Chrome.

### ADM-AUD-13C — полный EditRecord: IN PROGRESS, 2026-10-17
Следующий gate: настоящий Livewire EditOrders с полной OrdersForm, а не отдельная Section. На SQLite fixture проверить загрузку разделов, сохранение соседних полей после live media status и общий Save без перезаписи медиа/позиций. HTTP/почту запретить или подменить; рабочую БД/файлы не использовать. Браузерное подтверждение модального окна остаётся отдельным открытым пунктом.

### ADM-AUD-13C — настоящий EditOrders: DONE для изолированного gate, 2026-10-17
Добавлен сценарий через Livewire::test(EditOrders::class) с полной OrdersForm и настоящим EditRecord save. SQLite fixtures: существующий товар, доставка, CSV наброска и два клиентских изображения. Проверены загрузка разделов, live aggregate status, сохранение черновика admin_comment, отложенное до подтверждения удаление и отсутствие восстановления удалённой ссылки при общем Save. Items остаются побайтно прежними, статус и оставшиеся ссылки сохраняются. После отзыва edit_orders открытая страница возвращает 403 на Save, комментарий в БД не меняется.
Уточнена прежняя неопределённость modal markup: Filament TestsActions::getMountedActionModalHtml читает effects.partials[action-modals] / action-modals.N. Заголовок настоящей модальной формы подтверждён в этом HTML. Обычный assertSee основного HTML не видит отдельный partial; это не основание считать окно отсутствующим. Первый mount в новом тесте использовал повторный префикс form в TestAction, исправлен относительный key теста; продуктовый код не менялся.
Gate ArtistService + MediaForm + DetailsForm + ItemEditor: 49 tests / 505 assertions PASS, 8.059 s / 98 MB; Pint выполнен. Все записи — SQLite :memory:, внешние HTTP запрещены, Mail fake. Рабочие БД/изображения не затронуты.
Осталось: реальная видимость/геометрия окна и редактора в Chrome; desktop/mobile фильтров 13B; остальные открытые пункты ADM-AUD-13. Следующий точный шаг — сверить оставшиеся действия PDF/финансов и редактора с оригиналом, без реальных внешних отправок. Общий этап IN PROGRESS, коммита нет.

### ADM-AUD-13E — PDF и подтверждение счёта: IN PROGRESS, 2026-10-17
Сверить original orders/invoice.blade.php и OrdersController::approve_user_checkout с OrderInvoiceService. Найден риск: результаты Eloquent save не проверяются в generate/approve/referral, из-за чего veto может дать ложный успех или отправку письма при несохранённом состоянии. Добавить проверки и изолированные rollback-сценарии с PDF stub, Mail fake и запретом HTTP. Успешные повторное подтверждение/дата/статус/bonus сохранить. Реальные PDF-файлы и рабочая БД не затрагиваются. Регенерация PDF после общего edit/item save остаётся отдельным открытым пунктом.

### ADM-AUD-13E — сохранение счёта и повторное подтверждение: DONE для текущих сценариев, 2026-10-17
Original sources: resources/views/vendor/voyager/orders/invoice.blade.php и OrdersController::approve_user_checkout. Подтверждены действия генерации/обновления, первого/повторного подтверждения, сохранение approved_date, переход watching→pegging и referral bonus. Успешный поток новой реализации сохранён.
Исправления OrderInvoiceService: проверяются все пять save в generate/approve/referral; false вызывает ValidationException с ключом invoice, DB transaction откатывается, до почты выполнение не доходит. wasApproved теперь читается из locked свежей записи, поэтому подтверждение ранее открытой модели корректно определяется как повторное. Тест со второй stale моделью сначала выявил ошибку reapproved=false; после исправления проходит.
Пять Eloquent veto сценариев проверяют неизменность заказа/даты/ссылки/бонусов/пригласительного кода, отсутствие BestEffortMail send/attempt и HTTP. PDF controller подменён, файлы не создаются. Первоначальный fixture forceCreate User требовал tracking columns; заменён DB insert fixture без изменения продуктового кода.
Gate InvoiceService + PaymentService + RecordActionPermissions:16 tests /237 assertions PASS,4.806s96MB; Pint повторно PASS после временного отказа записи formatter, diff check PASS. Рабочая БД/файлы/сервисы не затронуты.
Открыто: реальный PDF write не является частью DB transaction; если PDF уже записан, последующий отказ БД не восстанавливает прежний файл. Это не заявляется исправленным. Регенерация PDF после общего edit/item save, полный рендер документа и browser UI остаются отдельными пунктами. Следующий шаг13E — сверить генератор и после-сохранения PDF с original, только изолированные пути. Общий ADM13 IN PROGRESS, коммита нет.

### ADM-AUD-13C / 13E — PDF после edit/add/update: IN PROGRESS, 2026-10-17
По реальным routes/admin.php handlers: Admin/OrdersController::update_admin_order и update_order_item_price пересоздают PDF без изменения has_pdf/pdf_approved; root OrdersController::add_order_item делает то же. add_client_images не вызывает PDF. Перенести только эти три вызова через отдельный refreshDocument контракт, не через generate (он меняет флаги и требует номер). Проверить актуальные items/delivery/totals, ошибки PDF и отсутствие вызова на attach. Существующие тесты изолировать mock генератора; запрет на изменение рабочих путей картинок сохраняется.

### ADM-AUD-13C / 13E — автоматическое обновление PDF: DONE для переноса вызовов, 2026-10-17
Установлены точные original обработчики по routes/admin.php: Admin/OrdersController::update_admin_order (1278), update_order_item_price (1049), root OrdersController::add_order_item (2665). Все три пересоздают документ без установки has_pdf/pdf_approved. Original add_client_images PDF не вызывает. В UpdateOrderService и OrderItemEditor add/update подключён OrderInvoiceService::refreshDocument; attach не менялся. Этот метод берёт свежие raw DB данные, декодирует items/delivery, форматирует price и передаёт текущий VR + null firm в существующий DynamicPDFController. Предусловие наличия VR и изменение флагов из generate здесь не используются, как в original edit.
Вызовы происходят после сохранения новых значений внутри существующих транзакций; исключение генератора откатывает order. При add существующий catch также удаляет только новые uploads. Существующие фото не переименовываются по пользовательскому ограничению. Письма/подтверждение/бонусы не запускаются.
Проверки: свежие items/delivery/price/VR на PDF adapter, unchanged invoice flags, update/add вызовы с новыми total и позициями, attach без PDF, rollback общего edit и item update, rollback add с удалением нового файла и сохранением existing bytes. Existing schema/full EditRecord tests изолируют PDF side effect mock. 80 tests /796 assertions PASS (InvoiceService, ItemEditor, UpdateOrderService, ArtistService, DeliveryForm, DetailsForm),10.721s100MB; Pint/diff check PASS. Только SQLite/fake uploads/PDF stub, рабочих файлов/БД не меняли.
Открыто: full PDF rendering и atomic/compensated physical PDF replacement при последующем DB failure. Рабочая файл-запись не проверялась. Chrome 13A/13B/13C visual gate также остаётся. Следующий точный шаг13E — изолировать PDF write и проверить сбой записи/сохранность прежнего документа, затем сверить полный renderer с original. Общий ADM13 IN PROGRESS, коммита нет.

### ADM-AUD-13E — безопасная запись PDF: IN PROGRESS, 2026-10-17
DynamicPDFController сейчас вызывает dompdf.save прямо в public/storage/pdf/{id}.pdf. Сначала перенести запись готовых bytes во временный файл в том же каталоге, проверить полноту записи, заменить целевой файл rename и убрать temporary при ошибке. URL, A4/portrait и renderer не менять. Изолированные проверки: успешная замена, partial put, failed rename, новый документ, существующий файл. Отдельный вопрос — восстановление уже заменённого PDF после последующего DB rollback; не считать его решённым только атомарной записью.

### ADM-AUD-13E — временный файл при записи PDF: DONE для write gate, 2026-10-17
DynamicPDFController сохраняет прежний renderer, A4 portrait и /storage/pdf/{id}.pdf URL, но получает dompdf.output и передаёт bytes в InvoicePdfWriter. Writer создаёт временный файл в том же каталоге, проверяет полную длину put, сохраняет permissions существующего файла (новый0644), делает rename и удаляет temporary при ошибке. Создание отсутствующего каталога поддержано. Прежний документ не открывается на прямую перезапись.
Пять тестов: настоящий Dompdf на минимальном HTML в отдельном fake public path; renderer exception с прежним PDF; создание и замена; partial put; failed move. Проверены PDF header/EOF, URL, сохранность old bytes и отсутствие temporary. Это не тест полного invoice HTML/визуального содержимого.
Gate PdfWriter + SummaryRenderer + InvoiceTotalsCalculator + InvoiceService:33 tests /184 assertions PASS,2.919s86MB. Pint для новых writer/test PASS, diff check PASS. Formatter затронул весь legacy DynamicPDFController; массовое форматирование снято, оставлен только точечный diff3+/1-. Рабочие public PDF/images/БД не затрагивались.
Осталось: компенсация уже заменённого документа при последующем DB rollback и полный invoice renderer parity; Chrome visual gates также открыты. Laravel13 connection поддерживает afterRollBack; перед компенсацией необходимо проверить вложенные транзакции, повторные записи одного пути и невозможность восстановлением затереть более новый PDF. Следующий точный шаг — isolated transaction/file consistency scenarios. Общий ADM13 IN PROGRESS, коммита нет.

### ADM-AUD-13E — PDF rollback: IN PROGRESS, 2026-10-17
Добавить журнал замен PDF на время транзакции: исходные bytes, записи по вложенным transaction records, commit cleanup, rollback восстановления. Повторные записи и child commit/rollback должны восстанавливаться независимо от порядка callback. Перед восстановлением сверять hash последней своей версии, чтобы не затирать обнаруженный внешний документ. Без активной DB transaction writer остаётся обычной атомарной заменой. Тестировать SQLite :memory: и fake files, включая внешний overwrite. Аварийное завершение процесса/межпроцессная гонка не считаются покрытыми callbacks.

### ADM-AUD-13E — PDF при rollback: DONE для изолированных сценариев, 2026-10-17
InvoicePdfWriter зарегистрирован Singleton. В активной default DB transaction ведётся журнал исходного содержимого/версий по root и nested transaction records Laravel13. Commit очищает журнал; child rollback исключает только версии этого child; root rollback восстанавливает исходный PDF или удаляет новый. Повторные записи и committed sibling transactions не зависят от порядка вызова child callbacks. Без транзакции остаётся atomic replace. Перед восстановлением сравнивается hash текущего файла с последней своей версией; обнаруженная внешняя замена сохраняется с warning log.
Пять новых writer-сценариев: повторные записи existing/new PDF, nested rollback→parent write/commit→следующий rollback, два committed child→outer rollback, только child write→outer rollback, external replacement. Пять InvoiceService veto fixtures теперь реально вызывают writer на fake file и проверяют восстановление original bytes при отказе order/link/referral save, до mail.
Выявлен и исправлен Windows path bug: mixed slash нового пути и realpath уже созданного файла давали две journal entries, повторно созданный новый PDF оставался после rollback. Нормализация parent realpath+basename устранила ошибку; усиленный сценарий до исправления падал, после проходит.
Общий gate до усиления Windows-case:90 tests /841 assertions PASS,13.002s106MB. Финальный адресный PdfWriter+InvoiceService после исправления:20 tests /133 assertions PASS,3.047s86MB; Pint testPASS. Все SQLite :memory:/fake files; real Dompdf только synthetic HTML в изолированном каталоге из предыдущего gate. Рабочие БД/PDF/изображения не затронуты.
Ограничения: callbacks не восстанавливают файлы после аварийного завершения процесса; hash guard не является межпроцессной блокировкой между проверкой и rename; физический отказ самого восстановления остаётся ошибкой. Новых business UI/миграций нет. Следующий точный шаг — полный business invoice HTML/рендер и сравнение полей/итогов с original; Chrome visual gates остаются открытыми. ОбщийADM13 IN PROGRESS, коммита нет.

### ADM-AUD-13E — полный invoice renderer: IN PROGRESS, 2026-10-17
Source diff original/new DynamicPDFController подтверждает одинаковую business логику, отдельные различия: null VR cast, безопасный writer, ранее изменённые header/table styles. Нужен настоящий business HTML/PDF на SQLite fixtures: seller variants VAT/nonVAT/custom firm, позиции/количество/размер/express, доставка, EUR+percent discount, customer/legal data, PDF locale. Визуальный образец только synthetic данные; рабочую БД не читать/писать. Не объявлять дизайн совпадающим при наличии отличий шапки/границ.

### ADM-AUD-13E — полный invoice HTML/PDF: DONE для synthetic gate, 2026-10-17
Добавлен InvoiceDocumentTest с настоящим DynamicPDFController и SQLite fixtures users.settings.locale/pdf_locale, order_strings/translations. Шесть seller cases: default VR00, BAW, VRR nonVAT, DS020, custom firm VAT/nonVAT. Проверены реквизиты продавца/клиента, юр. адрес/банк/счёт, номер/дата, количество2/размер, express, доставка, последовательные10EUR+10%discount, итог118.05 и legacy VAT24.15. Эти суммы взяты из текущего original алгоритма; пересчёт налоговой логики не вводился.
Семь полных HTML проверяют приоритет pdf_locale над settings.locale (ru/en/lv/lt/pl/de/ee) и отсутствие untranslated keys. Первые language fixtures ошибочно использовали users.locale; compatibility accessor берёт settings.locale. Исправлен fixture, не production. Один сценарий создаёт настоящий полный PDF в fake public path, проверяет header/EOF/URL без внешних вызовов.
Отдельный временный QA test загрузил исходный original DynamicPDFController под другим именем класса в новом runtime: normalized full text на одинаковом synthetic order совпал (1test2assertions). Оба документа отрендерены Dompdf3.1.6→PNG Poppler и просмотрены. Это сравнение исходных templates в новом runtime, НЕ повторный запуск original dompdf0.8.6. Poppler предупредил о nameToUnicode paths с кириллицей, PNG созданы и читаемы.
Различия header/границ объяснены историей: b49dc43 «ADM-FIL-003: выровнять PDF счета с Voyager», MIGRATION_PROGRESS evidence2026-08-28 фиксирует совместимость нового dompdf с прежним PDF18451, включая0–1px lines. Его правки не отменялись: буквальный откат к старому невалидному HTML дал бы другой результат на новом движке. Текущий synthetic PNG без наложений/обрезки, одна визуально просмотренная страница.
Gate Document+Writer+SummaryRenderer+InvoiceService:43 tests381assertionsPASS,5.516s92MB; Pint нового testPASS. РабочаяБД/PDF/images не затронуты, Mailfake/HTTPprevent. Временные QA comparison artifacts: %TEMP%/viar13-invoice-qa-20261017; не часть repo/runtime.
Осталось: actual old/new runtime браузерная приёмка, длинные многостраничные документы/реальные шрифты и полный список открытых ADM13 actions. Следующий точный шаг — source matrix оставшихся финансовых/платёжных действий и их связь с полным Orders UI; 13B/13C Chrome gates не закрыты. ОбщийADM13 IN PROGRESS, коммита нет.

### ADM-AUD-13E — payment status/предоплата: IN PROGRESS, 2026-10-17
Original root OrdersController::changeOrderPayment при not_payed устанавливает use_bonus=1; новый сервис это пропускает. Сохранить original поведение повторного payed: customer bonus только при use_bonus=0, inviter+5 при каждом ручном payed. Добавить missing unpaid flag и проверки save в оплате/предоплате; отказ должен откатывать бонусы и блокировать mail/webhook. Тесты толькоSQLite/mocks.
TODO в том же13E: gift-card branch prepareGiftCards приводит casted User.settings array к string перед json_decode; сверить генерацию count/offline/locale/PDF/coupon save с original отдельно. Пока не объявлять подарочные сертификаты проверенными.

### ADM-AUD-13E — оплата/предоплата: DONE для текущего gate, 2026-10-17
Сверен root OrdersController::changeOrderPayment и calculate_bonus. Восстановлено пропущенное original not_payed→use_bonus=1. Сохранены прежние пороги calculate_bonus и повторный manual payed: customer bonus только use_bonus0, затем flag2→1; inviter получает5 при каждом explicit payed. Автоматическую идемпотентность, которой нет в original ручном действии, не добавляли. Prepayment status флаг не меняет, amount update меняет только сумму.
OrderPaymentService проверяет save inviter/customer/order и предоплаты. False вызывает ValidationException по payment_status/prepayment_price, rollbackDB до mail/gift/webhook. Пять Eloquent veto cases проверяют полное неизменное состояние заказа/клиентов и отсутствие BestEffortMail send/attempt/webhook при включённых notifications в config. Реальные внешние вызовы запрещены mocks/Httpprevent/Mailfake.
Добавлены explicit manual repeats и unpaid-from-zero tests. Gate PaymentService + RecordActionPermissions + InvoiceService + RolePresentation:34tests481assertionsPASS,6.693s98MB;Pint/diffcheckPASS. ТолькоSQLite :memory:, рабочие роли/данные/изображения/PDF не менялись.
Осталось в13E: gift-card branch settings array cast, count/offline/locale/coupon/PDF и его failure behavior; остальные browser gates остаются. Следующийточныйшаг — gift certificates по original changeOrderPayment, с repository/PDF stubs и безопасными fixtures. ОбщийADM13INPROGRESS, коммита нет.

### ADM-AUD-13E — gift cards: IN PROGRESS, 2026-10-17
Сверка original changeOrderPayment: pid5, count отдельных купонов, offline flag, locale из raw settings, PDF в coupon.pdf, затем одно GiftCard письмо. В новой модели settings уже array; строковый cast ломает branch. Ещё статус оплаты сейчас committed до генерации; при ошибке выдачи сертификата возможен paid без готовых документов. Перенести подготовку gift cards внутрь payment transaction, проверить coupon creation/save, письмо оставить после commit. Для gift PDF использовать существующий writer, чтобы rollback удалял только новые документы. Тестыcount/offline/locale/failure/missingcoupon сSQLite/fakefiles/PDFstub, никаких реальных отправок.

### ADM-AUD-13E — выдача подарочных сертификатов: DONE для isolated business gate, 2026-10-17
Сверен original root OrdersController::changeOrderPayment: одна карта на count единиц pid5, offline flag, settings.locale, coupon.pdf, одно письмо GiftCard после подготовки. Исправлен array-to-string cast User.settings. Подготовка сертификатов теперь в transaction смены оплаты; отсутствие купона и save=false отклоняют действие, письма/webhook остаются после успешного commit. При ошибке второго PDF или coupon save статус/bonus/coupons откатываются. GiftcardController PDF сохраняет через InvoicePdfWriter, поэтому transaction rollback удаляет новые PDF; URL/имя/675x525points/DPI96/HTML не менялись.
Проверен реальный UserRepository на SQLite:2offline по50+1online25 дают3купона и3ссылки, localeLV при countryDE,1giftmail+1paymentmail,1webhook через mocks. Prepayment/not_payed не создают купонов/PDF. Три failure cases(secondPDF,couponveto,missingcoupon) сохраняют исходные order/bonus/existingfile и удаляют новые. Отдельный actual GiftcardController contract test с fake Dompdf output подтверждает dimensions/options/name/URL и rollback файла.
Gate PaymentService + PdfWriter + RecordActionPermissions:31tests367assertionsPASS,7.849s102MB. Pint3filesPASS, controllerPHPsyntax/diffcheckPASS; legacy GiftcardController не форматировался целиком. ТолькоSQLite/fakestorage; настоящий gift PDF engine и отправка не запускались. Location mocked, mail fake, HTTP запрещён; SQLite RAND зарегистрирован только fixture для существующего GalleryTopMail helper.
Осталось: внешний вид настоящего gift PDF/email и long documents/browser UAT; gift repeated manual paid сохраняет original выдачу новых карт, дополнительной идемпотентности не добавляли. Общий payment service успешно прошёл basic/repeat/rollback/permissions gates, но весь13E не объявляется завершённым. Следующий точный шаг — сводная матрица оставшихся Orders actions (VR/payment requests/financial UI) по original и текущему коду, затем закрытие реальных пропусков. ОбщийADM13INPROGRESS, коммита нет.

### ADM-AUD-13E — накладные: IN PROGRESS, 2026-10-17
Подтверждены пропуски по original add_vr_num/update_vr_num/rem_vr_num и orders/id partial: новый select не передаёт prefix, отсутствует автоматический следующий номер, manual update не refresh PDF, remove не сбрасываетhas_pdf/не удаляетPDF, delete button не передаётdelete аргумент. Перенести create/update/delete contracts, используя current InvoicePdfWriter для rollback удаления. Счётчики по latest(id) nonnull нужной серии, какoriginal; правки существующих images не нужны. Новый action покрыть permission revoke и service/full schema сценариями. Реальная БД/файлы не используются.

### ADM-AUD-13E — накладные: DONE для текущего source/action/service gate, 2026-10-17
Original add_vr_num/update_vr_num/rem_vr_num подтверждают missing behaviors. Добавлен generate для4серий VR00/BAW/VRR445/DS020: latest(id) nonnull в выбранной колонке +1 (неMAX), defaultfirst1; orderlock и отказ повторногоcreateдлятого жеorder. Создание/смена номера пересоздаётPDF черезrefreshDocumentбезизмененияhas_pdf/pdf_approved. Savefalseотклоняетоперацию. Очистканомераудаляетvrrow,ставитhas_pdfNULL иудаляетpublic/storage/pdf/{id}.pdf,какoriginal; pdf_approved/pdf_linkнеочищаютсякакoriginal.
InvoicePdfWriter расширен remove с тем же transaction journal: позднийrollback восстанавливаетудалённыйPDF. Проверенотдельныйdeletefailure,PDFrefreshfailure,modelveto,newcounter4серии/latestnotmax/VRRpadding/повторcreate. РабочиеPDF не трогались; каждыйtest publicpathнаfake disk.
UI createVrNumber принимаетвыбранныйprefixвреальномaction,отдельныйselectfirmсoptionsизorder_strings(какoriginal; fallbackтолькобезданных). Numbercolumn передаётprefixиdelete:true;manageпослеоперацииrefreshrecordдляактуальногоhas_pdf. Actiontests выбраннойDS020/explicitdelete иновогоcreatepermissionrevocationпроходят. Browserclickещёнепроверен.
Общий запуск выявил ранее скрытый static cache clientStatusOptions межтестами: заменён once requestcache, минимальный rolefixture дополненuser_types. Первыйtest assertFormSetсмотрелdefaultformвместоmountedActionSchema0; исправлентестовыйtarget. Gate UpdateVR+RecordPermissions+PdfWriter+RolePresentation+DisplayParity54tests502assertionsPASS,8.402s102MB;Pint/diffcheckPASS.
Осталось: конкурентная выдача номера разным заказам не доказана(SQLiteнепроверяетMySQLgaplocks),originallatest+1контрактсохранён;actualChromecreate/update/removeиfirmlabels;заявкинаоплатуaction/source/servicegate. Следующийточныйшаг13E — originalpayment_requestpartial/AdminOrderPaymentRequestController иOrderPaymentRequestService::createForOrder:atomiccreation/publicnumber/validation. ОбщийADM13INPROGRESS,коммита нет.

### ADM-AUD-13E — создание заявки на оплату: IN PROGRESS, 2026-10-17
Original controller/partial сверены: amount0.01..999999.99,purpose255,EUR,pending,OPR+6digits,token40,customer snapshot,public link; новый Filament action использует тот же сервис. createForOrder делает два save без transaction/проверок: отказ второго оставляет неполную заявку. Добавить service validation сoriginalrules иatomicдвухsave, сохранитьповторныйmanualcreateкакновуюзаявку. Проверкиveto1/2/invalid/service+productionaction безgateway/provider вызовов. Отдельноbrowsercopy/link/showpending/paidостаётсяgate.

### ADM-AUD-13E — заявки на оплату: DONE для create gate, 2026-10-17
Сверены original Admin/OrderPaymentRequestController::store и orders/payment_request.blade.php: сумма0.01..999999.99, purpose<=255, pending/EUR,OPR-6digits,token40,customer snapshot иpubliclocaleURL. Filamentсоздаётчерезтотжесервис. createForOrder теперь валидируетoriginalrules и выполняетобаsaveвtransaction;falseнаinsertилиpublicnumberupdate→ValidationExceptionamount+rollback,безpartialrows. ДанныеorderберутсясвежимизБД,manualrepeatсоздаётнезависимуюзаявку(какoriginal),order/paymentнеизменяются. Gateway/publicpaymentcallbackлогиканеменялась.
Добавлены2saveveto,7invalidpayload,manualrepeat/freshsnapshot/token/publicnumber/rounding/publicURLtests. ProductionFilamentaction→realservice→SQLite:defaultamount,invalid0field error,noinsert,повторvalidsubmit12.50/trimmedpurpose/creator/OPR/noordermutation. Existingpermissionrevocationgateсохранён. ОдинoldVoyagerHTTPtestskippedиз-заотключённогомаршрута;новыйFilamentactiontestвыполнен,skipнесчитаетсяегоevidence.
PaymentRequest+RecordActions+RolePresentation+DisplayParity:47tests510assertions,1skip,остальныеPASS,7.831s98MB. Pintвыполненизменённомуcreatemethod/tests;массовыйформатнеизменённыхgatewayметодовснят,diffcheckPASS. SQLite :memory:/Mailfake/HTTPprevent,providersневызывались,рабочиеDB/imagesнеизменены.
Осталось: браузерныеcopylink/updatedlist/публичнаястраницабеззапускаплатежа,таблицаpaymentrequeststatus/methodlabels. Следующийточныйшаг13E — render/link/copyworkflowvsoriginal(включаяclipboardfailurefallback),проверитьactualUIкогдаChromeдоступен. ОбщийADM13INPROGRESS,коммита нет.

### ADM-AUD-13E — copy link и карточки заявок: IN PROGRESS, 2026-10-17
Original bot_scripts использует temp input + execCommandcopy; новыйBladeтолькоnavigator.clipboard.writeTextбезcatch. В средахсотказомclipboardкнопканедействует. Добавитьsuccessstate,legacyfallbackиreadonlyссылкудляручногокопированияпридвойномотказе;подключениепереживаетLivewireDOMupdates. Latest3pending/paid/method/amount/purposeсверитьrenderтестом,orderquerylimit3совпадаетoriginalgroup.take3. BrowserClipboardпоканеобъявлятьпроверенным.

### ADM-AUD-13E — карточки заявок и copy link: DONE для render/JS gate, 2026-10-17
Сверены original orders/payment_request.blade.php и partials/orders/bot_scripts1922: pending/paid,семьmethodlabelsсunknownfallback,amount/currency/purpose/number/copylink. OriginalControllergroupslatestid.take3 соответствуетnewrelationlatestid.limit3; лимитнеизменён.
Новыйadmin-payment-request-copy.js подключёнчерезexistingheadhook;Alpinecomponentнакаждойкарточке. Modernclipboard→legacytemporarytextareaexecCommand(какoriginal)→readonlymanualfieldприотказеобоих. Successstatusariarole,temporarycleanup/focusrestore/copyingguard. СсылкаэкранируетсявBlade/manualinput,Alpine@clickиспользует@js. ДоэтогобылwriteTextбезcatch,приотказекнопканичегонеделала.
JS5testsPASS:modernsuccess/noDOMfallback,missingAPI,permissionreject,legacyfalse/exception→manual,cleanup/focus. ProductionBlade14variants(7methods×2statuses)проверяютвсеlabels/amount/URL/escapedpurpose/manualmarkup,printinghidegateсохранён. PHPgateRolePresentation+RecordActions+PaymentRequest31tests586assertions,1oldVoyagerrouteSKIP,остальныеPASS,6.602s96MB;Pinttest/diffcheck/nodecheckPASS.
РабочаяБД/буферобмена/платежи/письманезатронуты,JSвVMstubDOM,PHPвSQLite. ActualChromeclipboardpermissions/focus/mobileиpublicpaymentpageпоканеобъявленыпринятыми. Следующийточныйшаг — read-onlypubliclinkpending/paid/notfoundиактуализацияспискапослеcreateвполномOrdersUI;браузерные13A/B/Cещёоткрыты. ОбщийADM13INPROGRESS,коммита нет.

### ADM-AUD-13E — public request read-only gate: IN PROGRESS, 2026-10-17
Проверить настоящий controller.show→productioncontentview дляpending/paid/unknownбезrenderобщегоfrontendlayout(егокаталогивнеэтоготеста),resolver eager order/customer,нулевыеwrites/внешниевызовы. Paidstartдолженredirectбезservice.start. НеобъявлятьfullHTTP/layout/browserприёмкойпроверкуконтента. ОтдельноfullOrdersрядпослеcreateдолженпоказатьновуюlatestзаявку,исчезновениестарейшейсверх3проверитьчерезrelation.

### ADM-AUD-13E — публичный контент и обновление latest3: DONE для указанного gate, 2026-10-17
Проверен настоящий PublicOrderPaymentRequestController::show→production content view,безобщегоfrontendlayout:pendingпоказываетформыметодов/PayPal/tokenaction,paid—successбезPOSTформ;номер/сумма/emailизsnapshot,relationsorder/customerзагружены,таблицазаявокнеизменна. Unknown show выбрасываетModelNotFoundдоgateway. НастоящийHTTPPOSTнаpaidstartredirectsсflashpaidбезservice.startиdatachanges. Платёжнеинициировался;использовалсяужeоплаченныйfixture.
Productioncreateaction+number_controlscolumnвLivewiretablehost:из3existingзаявокcreateчетвёртойпоказываетOPR4/Shipping,OPR1уходитизlatest3,actualserviceсоздаётSQLiterow;invalidsubmitмеждутемновыхrowsнедобавляет. Hostиспользуетproductioncolumn/actionиrelationlatestlimit3,нонеполнуюOrdersTableсовсемичатами. Это доказательствоDOMrefreshэтойкарточки,неполныйOrdersbrowsergate.
Начальныеошибкиtestexpectationисправлены:emailберётсяизdeliverysnapshotclient@example.test,анеUser.email;rawsnapshotсравниваетсяпослеfresh(сDBnullcolumns/порядком). Продуктовыйкодвэтомшагенеменялся.
PaymentRequest+RecordActions+RolePresentation35tests626assertions,1oldVoyagerrouteSKIP,остальныеPASS,6.740s96MB;Pint/diffcheckPASS. SQLite/Mailfake/HTTPprevent,реальныеданные/платёжныеprovidersневызывались. Общийpubliclayout/actualGETHTTPсfrontendкаталогами/Chromeclipboard/mobileещёнепроверены.
СледующийточныйшагADM13 — пересобратьсводныйостаток13A..13GпоактуальнымevidenceиотдельнопроверитьChromeдоступ,чтобынеподменятьвизуальнуюприёмкуещёоднимузкимтестом. ОбщийADM13INPROGRESS,коммита нет.

### ADM-AUD-13 — общая регрессия и браузерные фильтры, 2026-10-17
Статус: IN PROGRESS; общая задача не завершена.
Выполнен общий gate: php8.3 -d memory_limit=768M vendor/bin/phpunit tests/Feature/Admin tests/Feature/Invoice tests/Feature/Payment/OrderPaymentRequestTest.php --log-junit storage/logs/adm-aud-13-regression.xml. 798 tests /9792 assertions,1oldVoyagerrouteSKIP,0failures/errors,4:11.813/246MB. Процесс74226 завершёнexit0; тестоваяконфигурацияSQLite:memory:,MAILarray. Это общаяcode regression, не blanket UI acceptance.
Chrome восстановлен. Существующаяnewвкладкаreloadподтвердилаверхнююпостояннуюпанель(основныеполяoriginalпорядка);стараяoriginalвкладкаDebuggerunattached,поэтомуоткрытновыйtemporaryoriginaltab. ЧерезUIoriginalдатывведеныclick→CtrlA→typing(предыдущаяconcatпроблемаобойденаобычнымвводом). Обеадминкиprice22,size40x40,12–13Aug2026:IDs18440/18439,2orders/44€. Newreset→77orders/4101.56€. Никакихрабочихsave/почты/платежей/labelsнет.
Mobile390×844newпанельпросмотрена:однаколонка,полянеобрезаны;С/Поstackedнаmobile,рядомнаdesktop. Viewportcapприменялсякselectedtemporarytab,начальныйscreenshotnewбылdesktop2560;этоуточненоDOMwidth,затемnewURLоткрытвtargettemporarytabиmobileподтверждён. Overrideснятreset. SAfloatingbadgeвиденснизу;общуюmobileтаблицунаприёмкуневыдаём.
Снимки:storage/app/adm-aud-13-qa/orders-filters-desktop.jpg иorders-filters-mobile.jpg(локальныеQA,неpublicruntime). Своднаяверхняяматрицаdocs/admin_orders_parity_2026-10-16.mdактуализирована13A..G:историческиестатусыниженезаменяюттекущуюсводку.
Осталось:13Aвсетипы/SVG/tablemobile;13Bдругиеbrowsercombos/tabs/sort;13C/Dactualedit/create+uploadwebPHPlimits;13EUIcourier/labels/clipboard/giftPDFemail/longinvoice;13F/Gchat/lifecycle/rolesbrowser. Следующийточныйшаг — actualEditOrdersсравнитьсoriginalнаоднойкарточке,неpersistрабочихданных. Коммита нет.

### ADM-AUD-13C — actual Chrome editor gaps: IN PROGRESS, 2026-10-17
Actualold/newedit18451вChrome:originalимеетpaymentrequestslist+create/copy/open иклиентскиекомментариикфото(test/test2);вnewformэтихблоковнет. Managerвnew6009raw,originalAdmin(нетвmanageroptions). ПеренестипанельзаявоквEditOrdersсreuseexistingcreateaction/service,показатьoriginalклиентскийstreamиdate безновогоsend/readsideeffect. Hydrationunknownmanager→Adminпоoriginalselectfallback. ПерваяSectionуправлениязанимаетполшириныпривсехостальныхfullwidth—исправить. ТолькоSQLitewrites/browserread,realSaveнежать.

### ADM-AUD-13B — верхняя панель фильтров: DONE, 2026-10-17
По запросу пользователя фильтры постоянно открыты над таблицей (FiltersLayout::AboveContent), после вкладок, как в оригинале. Сверено в Chrome с viarcanvas.loc/admin/orders: основные поля в порядке номер / заявка / клиент / телефон / цена / оплата / канал / категория / страна / художник / менеджер / размер / период. Дополнительные существующие фильтры сохранены после основных. Адаптивная сетка 1/2/4/6 колонок, кнопки применения и сброса.
При открытии обнаружена ParseError OrdersTable: незавершённый вызов column action после извлечения createPaymentRequest. Восстановлена целостная конфигурация колонок/фильтров/actions, сохранены ограничения printing и действие создания VR. Общая createPaymentRequest используется также в редакторе. Исправлен foreign key fixture комментариев (order_id) для проверки полной формы.
Chrome: первоначально 77 заказов / 4101.56 EUR; фильтр ID18451 -> 1 заказ / 22 EUR; Сбросить -> 77 / 4101.56 EUR. Рабочие записи не сохранялись. Снимок: storage/app/adm-aud-13-qa/orders-filters-top-final.jpg.
Проверки: OrderListRolePresentation + OrderRecordActionPermissions + OrderArtistService —39 tests /731 assertions PASS; SearchFilters + ListTabs + DisplayParity + ListSummary —51 tests /539 assertions PASS; Pint и git diff --check PASS (только предупреждения LF/CRLF). SQLite :memory:, без реальных писем/платежей/этикеток.
Верхнее размещение принято; общий ADM-AUD-13 остаётся IN PROGRESS. Следующий шаг: браузерная сверка оставшихся действий редактора заказа (ADM-AUD-13C), включая новые блоки заявок на оплату/комментариев клиента. Их полный EditOrders сценарий проверен локально, визуальная приёмка после правок ещё открыта. Коммит не создавался.

ADM-AUD-13B — компактная верхняя панель (IN PROGRESS), 2026-10-17: уменьшить padding/gap, поля/подписи; 8/10 колонок на широком экране вместо 6. Область стилей только Orders. Проверить Chrome и сохранить результат.

ADM-AUD-13B — компактный верхний фильтр DONE (2026-10-17): отдельный класс viar-orders-table, padding12x16, gap10x12, уменьшены подписи/поля/кнопка; responsive 1/2/4/8/10 колонок. На широком Chrome поля теперь в двух строках вместо трёх; высота фильтра визуально уменьшена примерно с360 до220px. Выпадающие поля получили компактный padding на кнопке, без двойного padding контейнера. На мобильном min-height40px. Стиль ограничен фильтрами Orders, запросы и применение не менялись. Chrome desktop screenshot verified, список77/4101.56 EUR сохраняется. PHP lint/Pint/diff-check PASS; новых функциональных тестов для CSS не добавляли. Снимок storage/app/adm-aud-13-qa/orders-filters-compact.jpg. Мобильная визуальная сверка этой компактной версии ещё не выполнена. Общий ADM-AUD-13 IN PROGRESS; далее редактор13C. Коммита нет.

ADM-AUD-13B — одинаковая высота select/input IN PROGRESS, 2026-10-17: учесть min-height searchable select Filament и native select. Общая высота32px/40px mobile; проверить DOM размеры и screenshot Chrome.

ADM-AUD-13B — одинаковая высота полей DONE, 2026-10-17. Исправлена разница native select/searchable select/input: явные height и min-height32px, единые line-height/padding/box-sizing, снят min-height36px внутреннего select. Длинный выбранный текст ограничен одной строкой с ellipsis. Mobile использует общий размер40px. Chrome измерение input ID заказа / select Оплата / searchable select Канал продаж: каждый элемент32px, каждый внешний fi-input-wrp32px. Скриншот визуально проверен: storage/app/adm-aud-13-qa/orders-filters-equal-height.jpg. Только CSS; рабочая БД не менялась, тесты бизнес-логики повторно не запускались. Мобильный размер задан, новая мобильная визуальная приёмка остаётся открытой. Общий ADM-AUD-13 IN PROGRESS; следующий этап13C.

ADM-AUD-13C — реальные блоки редактора IN PROGRESS, 2026-10-18: Chrome18451 подтвердил payment requests/comments/Admin; create payment modal открывается с22EUR, отмена без создания. Найдены fallback английские labels Placeholder; локализовать и скрыть штатным hiddenLabel, проверить видимость и окно художника.

### ADM-AUD-13C — Chrome редактор / модальные окна, 2026-10-18
Предыдущий шаг (высоты фильтра) — progress: CSS изменён и DOM32px доказан. Продолжена реальная проверка EditOrders18451. Подтверждены: manager «Админ», заявка OPR-000043/22EUR/pending/назначение/даты/email/URL; комментарии клиента test/test2 от01.09.2026. Окно создания платежа открывается с22EUR и назначением; отмена закрывает без создания. Окно «Художник и печать» открывает исполнителей/время/оплачен/показывать клиенту; отмена закрывает. Save/Create не нажимались, рабочие заказы не менялись.
Исправлены fallback English labels в трёх read-only Placeholder: русские подписи+hiddenLabel, без видимых дубликатов. DOM fi-sr-only1x1 подтверждён для заявок/медиа/комментариев. Order media раньше уже имел hiddenLabel, его английский текст был accessibility label, не доказанная видимая надпись. Payment requests display/Client image comments имели label(''), приводивший к fallback; теперь hiddenLabel штатный. Не добавлены новые поля/бизнес-сценарии.
Gate: ArtistService+DetailsForm+MediaForm28tests285assertionsPASS (4.309s98MB), Pint3filesPASS. SQLite :memory:; внешний send/save не выполнялся. Screenshots storage/app/adm-aud-13-qa/order-editor-{artist,payment}-20261018.jpg.
Original source order.blade.php410/962 подтверждает блоки. Повторный живой original /admin/orders/18451/edit сегодня возвращает ErrorException Trying to get property avatar of non-object в voyager/master.blade.php87: повторное визуальное сравнение оригинала не состоялось. Старый runtime не изменялся. Общий ADM13/13C IN PROGRESS. Следующее: восстановить обычный вход в original и повторить desktop/mobile сравнение редактора; upload только на изолированных данных. Browser read-only новые блоки/отмена приняты; mobile/upload/все типы не приняты. Коммит не создавался.

### ADM-AUD-13B/13C — мобильная браузерная проверка, 2026-10-18
Предыдущий шаг — progress (локализация/hidden labels + реальные открытия окон). Продолжена проверка текущего runtime в Chrome с viewport390x844 (clientWidth375 из-за scrollbar); override сброшен после проверки.
13C: полная форма18451 width375/scrollWidth375 — без горизонтального overflow страницы. Модальное окно платежа помещается,22EUR/назначение/кнопки видны; отмена закрывает. Окно художника помещается с исполнителями/датой/переключателями/Save+Cancel; отмена закрывает. AddOrderItem открывается, длинная форма вертикально прокручивается до FileUpload и Добавить/Отменить; кнопки доступны, отмена закрывает. Загрузка/сохранение не запускались. Source original order.blade.php1267+ подтверждает name/price/size/files/terms/manual canvas/gift/decoration/orientation/baget/express/comment и варианты defaults; новый состав соответствует, расположение через modal вместо inline уже существовало.
13B: финальная компактная панель на мобильном в одну колонку. DOM input ID заказа/native select Оплата/searchable select Канал продаж имеют40px и внешние wrappers40px каждый. Скриншот просмотрен. Это закрывает прежний TODO мобильной визуальной сверки компактной панели и размеров; полная проверка всех фильтров/табов остаётся открытой.
Evidence: storage/app/adm-aud-13-qa/order-payment-mobile-20261018.jpg, order-artist-mobile-20261018.jpg, order-add-item-mobile-20261018.jpg, orders-filters-mobile-final-20261018.jpg. Код не менялся, повторных business tests для браузерного read-only шага нет. Рабочие orders/images/roles, внешние mail/payment/Venipak не менялись. Original live parity всё ещё ограничен подтверждённой прошлым шагом ошибкой avatar; source доступен. Общий ADM13/13C IN PROGRESS, без коммита. Следующее: сравнить original после входа и проверить оставшиеся UI attach/file chooser и delivery dependent fields на изолированных данных; фактические upload форматы/100MiB и все товарные типы пока не приняты.

ADM-AUD-13C — форматы исходников IN PROGRESS, 2026-10-18: original attach accept PNG/BMP/JPEG/PSD/FIG/PDF/HEIC/HEIF; add image-only. Проверить реальные MIME signatures HEIC/HEIF/PSD/FIG в production attach/service, сохранение байтов и отказ document formats в add без изменений SQLite/fake storage.

### ADM-AUD-13C — MIME исходников и FIG: исправлено, 2026-10-18
Original order.blade.php1232–1233: attach accept .png,.bmp,.jpg,.jpeg,.psd,.fig,.pdf,.heic,.heif; add1288–1289 image-only. Controller add_order_item2537+ копирует оригинальные bytes, без обязательного image decode. New source validation100MiB/30files и Livewire max102400 уже существовали; public/.user.ini300M лишь source config, effective веб-SAPI limits не доказаны.
Добавлена production service проверка HEIC/HEIF ISOBMFF signatures, PSD header, binary FIG и XFig text fixture через настоящий UploadedFile (fileinfo MIME, не fake MIME). Attach сохраняет точные bytes/расширения; HEIC/HEIF проходят также add; PSD/FIG add отклоняет без изменения order/files. Fixtures минимальные синтетические сигнатуры, НЕ полноценные документы и не доказательство декодирования/браузерного upload.
Найден реальный пропуск: public/js/filament/forms/components/file-upload.js bundled MIME library mapping application/x-xfig -> fig, но OrderItemUploads acceptedTypes(true) не содержал этот MIME. Добавлен application/x-xfig только в FIG allowlist. Расширения/содержимое по-прежнему проверяются сервером; image-only add не получает document MIME. Chrome подтверждает actual input accept содержит application/x-xfig после reload. Modal multiple-file/FilePond/Cancel просмотрен, без выбора/загрузки файлов и без сохранения рабочего заказа.
Gate: ItemEditor+MediaForm27tests300assertionsPASS,3.576s94MB; Pint2filesPASS; diffcheckPASS. SQLite :memory:/fake uploads+local, mail fake, Http preventStrayRequests; screenshot storage/app/adm-aud-13-qa/order-attach-files-20261018.jpg. Ранний минимальный fig header был распознан text/plain: изменена синтетическая fixture на binary header, text/plain в allowlist НЕ добавлялся.
13C/общий13 IN PROGRESS. Следующее: реальные browser chooser/drop/full HEIC/PSD/FIG и100MiB на изолированном runtime, effective PHP/proxylimits; зависимые поля доставки. Original live editor пока недоступен из-за avatar, исходники доступны. Коммита нет.

ADM-AUD-13C — синхронизация города при самовывозе IN PROGRESS, 2026-10-18. Source original syncDeliveryAdminFields очищает скрытый city при смене метода/страны для всех pickup; new synchronize сохранял label для Riga/Daugavpils. Привести transient form state к original, сохранив explicit pickup onchange и server normalization. Проверить delivery gates и Chrome без Save.

### ADM-AUD-13C — зависимые поля доставки / hidden city, 2026-10-18
Source original order.blade.php syncDeliveryAdminFields1440+ очищает скрытый city для всех pickup при method/country change; отдельный pickup onchange и controller update_admin_order1172+ используют city=title для legacy Riga/Daugavpils и null только workshop. New synchronize до этого сразу выставлял title для Riga/Daugavpils, в отличие от transient UI original. Исправлено очищение скрытого city именно при synchronize; explicit pickup callback и server attributes не изменены. Regression test проверяет Daugavpils→city null, явный выборpickup1→Рига, методRiga→null, возвратdoor. Create adapter уже очищал city, поведение сохранено.
Gate DeliveryForm+UpdateOrderService+AdminCreateForm25tests261assertionsPASS,2.882s90MB; Pint2filesPASS; diffcheckPASS. Только SQLite :memory:/mail fake/no HTTP.
Actual Chrome Edit18451 без Save: city_delivery→Город доставкиРига+ГородРига; pickup_Riga скрывает город/город доставки; to_the_door снова показывает пустойГород. Workshop selection Muitas3Daugavpils подставляет address. Смена countryEE фильтрует options до Aida7-26Pärnu/Savi20Pärnu/Aasa tee1Loo, address автоматомAida; латвийские options исключены. Screenshot storage/app/adm-aud-13-qa/order-delivery-country-20261018.jpg. Reload возвращает persisted countryLV/Lubanas65 и исходный workshop — рабочая запись не сохранена.
13C/общий13 IN PROGRESS. Source+новый runtime приняты для этих веток, повторного живого Voyager parity сегодня нет из-за ранее установленной ошибкиavatar. Остались полный browser upload в isolated runtime и фактические web PHP limits/большие файлы, все товарные типы/копирование/CreateRecord и прочие13A..G из матрицы. Следующее — ревизия ещё непроверенных действий списка/этикетки/courier поoriginal, без внешних запросов. Коммит не создавался.

ADM-AUD-13E — вызов курьера (IN PROGRESS), 2026-10-18. Найден missing header action vs original orders.blade.php7–24/form_courier/VinepakApiController::send_courier+generate_courier_xml. Перенести sender defaults/weight-volume-pallets/comment-doc/date/hour-quarter selections, XML description type3 и active Venipak credentials multipart. Права edit_orders/nonprinting и повторная проверка перед отправкой, validation/time window/logs/error notification, без автоretry. Изолированные success/validation/provider errors/revocation tests, Chrome только открыть/отменить. Рабочих вызовов нет.

### ADM-AUD-13E — вызов курьера Venipak: перенос реализован, 2026-10-18
Найден missing feature: original orders.blade.php7–24 показывает nonprinting «ВЫЗОВ КУРЬЕРА», включает form_courier.blade.php; Admin/Api/VinepakApiController::send_courier254+/generate_courier_xml description type3. В новой ListOrders этого действия до шага не было. Добавлен header action VenipakCourierAction рядом с Create order; existing этикетки/печать не заменялись.
Форма: активный sender VenipakData (name/code/country/address/city/post/contact/tel/email); weight/volume/pallet0..40/comment160/doc16/date; часыfrom08..22/default09, to10..23/default17, четверти00/15/30/45. Страны DK/EE/FI/LV/LT/PL/SE как original. Пустые дата/вес/кол-во паллет сохранены defaults. Номера ответов показываются success notification; service errors видимы persistent escaped notification и форма остаётся открытой.
VenipakCourierService: server validation senderlength/email/country/numericpositiveweight/nonnegativevolume/palletcount/date/timewindow; activecredentials из DB; escaped XML с original sender/weight/volume/pallets/date_y/m/d/hour/min/comment/doc sequence, multipart user/pass/login_id/xml_text POST на active import_url. connect5/total20сек. HTTP/XML/transport failures отдельно обрабатываются, логируются только тип/httpcode/кол-во подтверждений (без payload/credentials). No automatic retry: неизвестный исход сообщает проверить статус до повтора. Manual повтор — отдельная отправка как original, идемпотентность/dedupe отсутствуют; сохранения новой таблицы/миграции нет.
Права: nonprinting и edit_orders; повторный inner guard перед service. Нет нового входящего APIendpoint; действие доступно в существующей Filament странице. Новые success/validation/provider/revocation tests используют реальные action/schema/service на SQLite:memory+HTTP fake. Проверяется multipart payload/XML decodedvalues+escaping, senderdefaults/noDBwrites,6invalidinputs/missingcredentials, nested/direct providererror/malformed/HTML/HTTP503, action success/error/revoked form и printing denied, transportcalls1/no credential in errormessage.
Gate Courier+RolePresentation+VenipakActions+LabelService33tests472assertionsPASS (3.068s96MB), Pint4filesPASS; diffcheckPASS. Был исправлен тест multipart parser (Guzzle добавляет Content-Length); повторные Http fakes изолированы через Factory swap, разные errorresponses действительно проверяются. Numeric maxLength удалён из schema, поскольку Filament превращал его в max_digits и отвергал дробные12.5/0.3.
Chrome actualorders: кнопка видна, defaults из активного sender загрузились; форма desktop и390x844 просмотрена, длинная мобильная форма прокручивается до часов/минут/Отправить/Отменить; Cancel подтверждён, viewportreset. Нажатий Отправить не было, реальный HTTP/DB/order/images untouched. Desktop screenshot storage/app/adm-aud-13-qa/courier-form-20261018.jpg. Mobile screenshot показан tool; попытки сохранить mobileфайл завершилисьcapturetimeout, файл не заявляем.
ОбщийADM13/13E IN PROGRESS. Перенос курьера реализован и fakeHTTP gate пройден; реальный вызов/acceptedproviderXML live не проверялись. Живой original UI по-прежнему ограничен avatarerror; source contract проверен. Следующее: browser визуальная форма создания этикетки/параметры отправления без providercalls (через existing isolated harness), затем остальные13A..G матрицы; коммит не создавался.

ADM-AUD-13E — повторная браузерная проверка LabelForm IN PROGRESS, 2026-10-18. Запущен существующий loopback8769 fixture session91471, router проверяет отдельнуюSQLite; HTTP preventStrayRequests+fake import/print, добавлен synthetic pickupcatalog fake. До шага4fixture requests; workingprovider не используется. Проверить package clone/quote/destination/errors и сохранить evidence.

### ADM-AUD-13E — этикетка / текущая production schema в isolated Chrome, 2026-10-18
Предыдущий шаг — progress: missing courier перенесён и33tests472assertionsPASS. Продолжена browseracceptance LabelForm, а не повторная отправка в workingVenipak. Перечитаны ignored router+fixture: отдельнаяSQLite venipak-actions-isolated/database.sqlite, failclosed realpathguard, APP_ENVtesting, separate session/views/log/storage, Http preventStrayRequests, fake import/print. Добавлен synthetic fake каталога Rīga/Liepāja в ignoredfixture; productioncatalog/services/schema/callbacks не подменялись. Server8769session91471 запущен на loopback и остановлен после проверки(exit1 отCtrlC ожидаем), viewportoverrideсброшен.
Chrome: исходные weight2.5/volume0.2/Нетпаллет; Добавитьпосылку создаёт вторую с2.5/0.2/Нет. insur500→Цена1.5EUR, выбор какoriginal quoteonly и не оформляетстрахование. Направлениеpickup открывает city/pointselectors; syntheticLiepāja→автовыбор Liepāja,Fixturepoint2(FixtureLiepaja). Source form__label delivery_type radio/insurance/packages сопоставлен сproductionform; actualoriginalUI не открывался наэтомшаге.
Fake submit docFAIL→persistentОшибкаVenipak/Fixturelabelcreationrejected, modalостаётся, двеупаковки/quote/выбранныйпунктсохраняются. Наmobile390 реальнаяproductionCSSlayout1колонка,scrollWidth390/client390,ошибкавверхунедолжнаспрятать форму; screenshotпросмотрен. Closeerror+docFIXTURE-OK→fakeуспех«ЭтикеткаVenipakсоздана/НомераFIXTURE-LABEL»,modalзакрыт. Verifyfile:до4requests,после6;новыеcreatefailuretrue/false,никакихавторетраев. FixturelabelsFIXTURE-LABEL. No realprovider/workingDB/imagewrites.
Evidence storage/app/adm-aud-13-qa/label-mobile-error-20261018.jpg (синтетическиеконтакты/данные). Наэтомшагеproductionкоднеизменялся,толькоignoredfakecatalog+документация;повторныхbusinessgatesнет,последнийcourier/label/role/action33tests472assertionsPASS остаётсядействующимдляпроверенныхклассов. Полнаярегрессия798 от17Octпредшествуетпоследнимизмененияминеобъявляетсяактуальнойполнойприёмкой.
13E/общий13INPROGRESS:labelUIerror/success/currentCSS/fakecatalog/packageclone/quoteпроверены;реальныйprovider/realPDFdownload/originalmobile/longgiftinvoice/fullupload/allproducttypesостаютсяограничениями. СледующееADM-AUD-13A: сверкаотображенияразныхтоваровисоставовзаказа,особенноportrait/gift/collage/SVG;передфинальнымзакрытиемобщийregressiongateпослепоследнихправок. Коммитнесоздавался.

ADM-AUD-13A — форматированный content и legacy flags IN PROGRESS, 2026-10-18. Original orders/items.blade.php использует HTML items[1].content, isset couponflags/bonus; new показывал literal HTML и скрывал present0/false. Восстановить структуру описания через установленный Symfony HtmlSanitizer, presence semantics couponflags. Не менять публичный shared basket partial и БД. Проверить production Blade fixtures/typebranches.

### ADM-AUD-13A — описание/отметки купонов/товарные ветки, 2026-10-18
Source original vendor/voyager/orders/items.blade.php: HTML items[1].content выводится как разметка, legacycouponflags и bonus проверяются isset. New показывал literalHTML и использовал !empty/filled дляpresenceflags. Исправлено: OrderProductPreview::legacyContent через уже установленный Symfony HtmlSanitizer allowSafeElements/http/https/mailto/tel возвращает HtmlString; paragraphs/br/emphasis/lists/links сохраняются, unsafe scripts/handlers не исполняются. Копия публичного partials/all_basket_order_items.blade.php побайтово совпадает original (git diff --no-index безdiff), общий frontend partial не менялся. Coupons presence/bonus0 соответствуютoriginal, null скрыт.
ProductionBlade tests:formatteddescription HTML/links/br/script/handler cases;flags present0/false/bonus0 vsnull;8productbranches portrait/constructor/canvas/modular/collage/gallery/gallerydisabled/gift проверяют primary/secondary/giftpreview правила. Modularfixture имеет реальные обязательныеwall_size/pic_size (первоначальная минимальнаяfixture безwall_size падала вsharedoriginalpartial;productionpartial не менялся). Gate ProductPreview+DisplayParity+RolePresentation47tests480assertionsPASS,3.704s92MB;Pint2filesPASS/diffcheckPASS. SQLite:memory/fakestorage/HTTPpreventstray.
Readonlycorpus script ignoredstorage/app/audit/order-product-corpus.php просканировал5000latestorders толькоid/items:3787collageflag,316construct,2pid5;9orders сHTMLcontent;namedproductkeys0. Это НЕ полныйscanистории. Примерыconstruct18421/gift16112/html14839. Реальный Chrome filter14839:описание безliteralbr,DOM10br,цена95/доставка7/итого102; screenshot storage/app/adm-aud-13-qa/order-product-content-20261018.jpg. Gift16112 показываетDāvanu karte/type/electronic/nominal50€/total50 какsharedpartial. Реальныхизмененийorder/image/providerнет.
13A/общий13INPROGRESS: oil/modular/otherbranches покрытычастичноfixtures, неrealfullbrowser. SVGcompanionиспользуетimg вместоinlineoriginal, external-image reference rendering остаётся конкретнымTODO, не закрыто этими тестами. Следующее: изолированная SVGкомпозициясembedded/relativeimage refs и сравнение сoriginalinline; productalltypes/liveVoyager/reviewremainingmatrix такжеоткрыты. Коммита нет.

ADM-AUD-13A — SVG composition IN PROGRESS, 2026-10-18. Original inlines existing SVG; current img blocks SVG external image references by browser image-document rules. Reproduce local syntheticSVG+externalPNG, then render SVG in isolated sandbox srcdoc preserving composition, no scripts/parentaccess, fallback raster for invalid/missingSVG. No changes to DB/media. Test productionBlade and Chrome old img vsnew composition, relative/root refs caveats explicit.

### ADM-AUD-13A — SVG composition исправлено, 2026-10-18
Original orders/items.blade245+ inlines existing SVG, current new <img src=SVG> cannot load nested external images in browser image-document mode. Проблема воспроизведена через ignored isolatedfixture build-svg-preview.php: syntheticPNGred/blue,SVGimage external localURL/circleclip/text, oldimg vs originalinline equivalent vs realnewcolumn. Chrome старыйimg показываетbrokennestedimage, inline и newcomposition содержатred/bluecrop+caption.
OrderProductPreview::svgDocument читает только существующий validatedcompanion path черезuploadsdisk (общийprivatepath сsvgUrl), разбираетXML NONET безentityexpansion, отвергаетDOCTYPE/invalid/nonSVG и падающийread возвращаетnull→исходныйraster. ДляSVGсwidth/height безviewBox добавляетсяviewBoxвпамяти; файлы не перезаписываются. Документмасштабируется150x170ссохранениемviewBox/aspectratio. Blade выводит SVG черезiframe srcdoc спустымsandbox, noallow-scripts/noallow-same-origin,referrerpolicy no-referrer,pointereventsnone/tabindex-1; ссылкаоткрываетисходник. SVG не вставляется в parentadminDOM, IDs/clipPath не конфликтуют между товарами. Дляmissing/corruptfile rasterfallback сохранён. iframe используется дляdocumentcontext/externalimage rendering, не новыйpackage/dependency/API/migration.
Tests preserveimagehref/xlink/clipPath/text/viewBox,invalidXML/nonSVG/DOCTYPErasterfallback,empty sandbox andnoflags/rawSVGinadminHTML,sourcebytesunchanged/noHTTP,existingeligibletypes/role/previewgates. ProductPreview+DisplayParity+RolePresentation49tests505assertionsPASS,3.854s94MB;Pint2files/diffcheckPASS.
Chrome localhost8770 generatedactualBladecolumn; SVGtestscript tried data-script-ran, frameSVGattribute remainednull, iframe sandboxempty confirmed. Allassetslocalfixture, productionDB/mediaunmodified. Saved screenshot storage/app/adm-aud-13-qa/svg-composition-20261018.jpg; compactcapturetimeout,compactfile не заявляется. Server77167stoppedCtrlCexit1 expected. No viewportoverrideatthisstep.
Ограничения: это синтетическаяSVGcomposition; actualproductionSVGassets вlocalpublic/uploads не найденыбыстрымrg. Ссылки insideSVG сохраняются, relativeURLs наследуютbaseстраницы(какinline); переносrelativeURLмежду/admin и/filament, CSP/CORS/productionassetavailability и exoticSVGfonts/filters требуютrealassetcases. Не объявлятьallSVG/prodvisualDONE. Общий13/13A IN PROGRESS. Следующее — полныйAdmin/Invoice/PaymentRequestregressiongateпослепоследнихизменений, затемоставшиесяреальныеproductassets/clipboard/download/CreateRecord изматрицы. Коммита нет.

ADM-AUD-13 — общий regression gate IN PROGRESS, 2026-10-18. Проверен phpunit.xml: sqlite :memory:, array mail/cache/session. Запущен PHP768M Admin+Invoice+PaymentRequest с JUnit/log storage/logs/adm-aud-13-regression-20261018.*, execsession66617. Дождаться terminal result этого handle, не перезапускать по observationtimeout. Сверить failures/errors/skips и актуальную матрицу перед сдачей; полный browser scope не считать закрытым по тестам.

### ADM-AUD-13 — общий regression gate DONE, 2026-10-18
Предыдущий шаг SVG — progress: productionhelper/Blade исправлены, browsercomposition воспроизведена. Текущий шаг: один непрерывный CLIprocess66617 завершилсяexit0, не перезапускался при промежуточных observationtimeouts. Команда PHP8.3.30 -d memory_limit=768M vendor/bin/phpunit tests/Feature/Admin tests/Feature/Invoice tests/Feature/Payment/OrderPaymentRequestTest.php --log-junit storage/logs/adm-aud-13-regression-20261018.xml. stdoutlog storage/logs/adm-aud-13-regression-20261018.txt.
Результат:818tests9986assertions,817passed/1skipped,0errors/0failures,3:24.458/248MB. JUnit CLIArguments tests818/assertions9986/errors0/failures0/skipped1 перепроверен, error/failure nodes0. Skip ровно OrderPaymentRequestTest::test_admin_can_create_payment_request_for_order: source markTestSkipped при отсутствии legacy admin.order_payment_requests.store (oldadminroutes намеренноотключены). Новый Filament createPaymentRequest action/service/fullEditOrders проверяетсядругимивыполненнымиtests; skip не снят подменойконтракта.
Дополнительно node --test tests/js/payment-request-copy.test.cjs5testsPASS/0skip/0fail; git diff --check всегоworkingtreePASS (LF/CRLF warningsнеошибки). Проверен phpunit.xml:sqlite:memory/mailcache-sessionarray; workingorders/images/provider не использовалисьдлязаписи. Productionкод во время общего gate не менялся; документацияобновлялась. Последнийgreen17Oct798tests теперьисторический, текущийgate18Oct818tests.
Общий ADM13 остаётся IN PROGRESS: fullbrowseroriginal/liveassets/filechooser100MiB/clipboarddownload/longgiftinvoice/CreateRecord-copy/types/role matrix не доказываютсяPHPgate. Следующий приоритет ADM-AUD-13D — фактический сценарий создания/копирования заказа в изолированном runtime по original; документыматрицысохранены. Коммит не создавался, накопленныеизмененияостаютсявworkingtree; две AdmBrowserCycle модели ктекущейзадаченеотносятся.

ADM-AUD-13D — IN PROGRESS (2026-10-19): проверить полную CreateOrders Livewire-страницу: новый заказ, from_order_id, права add/read и сохранение новой позиции для существующего клиента. Сверка с original OrdersController::create и order_create.blade.php; только SQLite :memory:, Mail fake, HTTP blocked. Следующее: actual page gate вместо одного service/harness.

ADM-AUD-13D — IN PROGRESS (2026-10-19): actual CreateOrders/from_order_id/create/validation/add-read gate DONE: 4tests52assertions; связанный gate28tests324assertionsPASS. Source order/bonus/client count сохранены. Browser visual TODO: local browser use disabled in Work settings (cua inventory). Осталось browser isolated create/upload/types. Production код и рабочие данные в этом шаге не менялись.

ADM-AUD-13D — IN PROGRESS (2026-10-19): сверка переключения клиентов/email с original order_create JS и actual CreateOrders: новый клиент+исходник, неизвестный source, отзыв add_orders. Найден кандидат: после изменения email на новый сохраняются имя/телефоны прежнего клиента, хотя original очищает их при поиске. Подтвердить regression и исправить минимально. Browser всё ещё disabled in Work settings.

ADM-AUD-13D — IN PROGRESS (2026-10-19): DONE code+gate — очистка stale имени/телефонов при новом email по original, поддержка formatted телефонов без лишнего Filament regex; full CreateOrders newclient/upload, client switch, missing source, revoked permission. Gate32tests386assertionsPASS/JUnit storage/logs/adm-aud-13d-create-20261019.xml; PintPASS. Browser TODO: управление disabled in Work settings; остаются desktop/mobile original/chooser/drop100MiB/types. Рабочие данные не менялись.

ADM-AUD-13D — IN PROGRESS (2026-10-19): Chrome доступ восстановлен. New CreateOrders desktop открыт/осмотрен, tel fields и отсутствие общего overflow при1176px подтверждены; без save. Original create перенаправляет на вход Voyager — необходима авторизованная original session для парной сверки, вкладка оставлена. Browser-disabled больше не актуален.

ADM-AUD-13D — IN PROGRESS (2026-10-19): после входа live original create_admin_order подтверждены default a_order_from=первый реальный канал1 и country=первый sort справочникаNL; new пустой канал/LV. Исправить инициализацию CreateOrders по реальным справочникам, сохраняя country клиента при from_order_id. Не менять БД/статические mappings.

ADM-AUD-13D — IN PROGRESS (2026-10-19): live original штатный /admin/create_admin_order доступен. DONE: catalog defaults country/channel исправлены; source countrypriority; Chrome totals/exclusive discounts/pickupRiga сверены;33tests395assertionsPASS. TODO visual: вернуть расположение состава справа как original, цвет payment_status, отдельный subtotal товаров. New mobile screenshot/DOM просмотрен, original mobile DOM безoverflow, mobile screenshot timeout. Остались upload/types/sourcebrowser. Рабочие заказы не сохранялись.

ADM-AUD-13D — IN PROGRESS (2026-10-19): реализация visual TODO по живому original: двухколоночная форма (детали слева/товары справа), subtotal до бонусов/скидок/доставки, payment_status red#ff9d9d/prepayment#c19300/payed#2ba92b. Использовать layout components Filament, проверить actual CreateOrders и responsive Chrome; записи только SQLite fixtures.

ADM-AUD-13D — IN PROGRESS (2026-10-19): DONE layout данные слева/товары справа (FilamentGroup), subtotal до bonus/discount/delivery, original paymentstatus colors, раскрытая legal-section; Chrome desktop2560/mobile390 без overflow, 3 computed colors подтверждены. Gate34tests410assertionsPASS/Pint4filesPASS/JUnit обновлён. Осталось isolated browser create/upload/chooser/drop/largeformats/sourceclient/types и originalmobile. WorkingDB/media неизменны, коммита нет.

ADM-AUD-13D — IN PROGRESS (2026-10-19): подготовить isolated browser runtime отдельная SQLite/temporary uploads/final uploads/session/views, real CreateOrders component и browser FilePond chooser, HTTP blocked/Mail fake. Пройти sourceclient и новый клиент/upload/create, сверить DB JSON/bytes/именование. Рабочая DB/storage запрещены для записи.

ADM-AUD-13D IN PROGRESS: fieldorder/1col/extraexpress/counter исправлены поoriginal,34tests410checksPASS; browser PNG/JPEGupload завершён isolated8771. Create rollback из-за missing fixture termsstrings, fixtureдополнена, повторsubmit+verify впереди. UserfileURLaccess был включён, выбиратьчерезvisible FilePondlabel. Server1152live; не перезапускать безpoll.

ADM-AUD-13D IN PROGRESS: DONE порядокполей/1col/layoutapproxoriginal; browserreal PNG+JPEGchooser/upload/CreateOrders→901, SQLiteJSON/naming/SHA256 verified, source/bonusunchanged. Headerfrom_order_id stable afterLivewire. Gate34tests412assertionsPASS. Server1152 STOPPEDCtrlCexit1, fixtureсохранена. TODO newclientbrowser/100MiB/drop/HEIC-HEIF/finalnewshots/originalmobile/alltypes. WorkingDB/media/provider неизменны.

ADM-AUD-13D/13C IN PROGRESS: реальный Chrome boundary104857600bytePNG отклонёнFilePond «Максимальный102.4MB», хотя Laravelmax102400KiB допускает. Причина vendorFileUpload maxSize KB/FilePonddecimal1000. Согласовать FilePondmaxFileSize вbytes черезpublicpond.setOptions, неvendorpatch; применитьcreate+editupload. Проверить100MiBaccept и+1byte reject наisolatedruntime.

ADM-AUD-13D/13C IN PROGRESS: DONE FilePond decimalKB vs LaravelKiB mismatch исправлен общимOrderFileUploadLimits(create/edit), actualChrome100MiB accepted/create902/newclient/SHAbytes verified,+1byte rejected. Gate61tests712assertionsPASS/JUnit. Originalmobile порядок/closedsidebar375 проверен; newDOM375, чистыйnewmobile screenshot TODO (timeout/transitionoverlay). Нетlistener8771, fixtureсохранена, workingданныенеизменны. Next realHEIC/HEIF/drop/finalmobile затемremainingADM13матрица.

ADM-AUD-13D HEIC/HEIF IN PROGRESS: локальныхобразцовнет, sample https://github.com/strukturag/libheif/blob/master/examples/example.heic скачанвignoredfixtureinput718114bytes/MIMEimageheif/SHA7f8b363e4936c0666a25f64f3a92fda10bd8e5453be4592530b65a55dd98f3f2; .heif тажесущностьпереименованная (неотдельныйcodec). Isolatedserver81544/8771live, chooserfilesselected, дождатьсярезультатаиcreate+SHAverify. Новыебиблиотекинеустанавливались.

ADM-AUD-13D IN PROGRESS: DONE actualChrome publiclibheifHEIC+samebytes.heif chooser/upload/create903/byteSHAverified; noimageconversion, previewdecodeнепроверен. DONE чистыйdesktopfinalscreenshotcreate-final-order-desktop-20261019.png просмотрен. Remainingdrag/drop/cleanpairedmobile/remainingtypes затем13B/E/Fmatrix. Server81544STOPPEDCtrlC, workingданныенеизменны; productionкодне менялся, актуаленgate61tests712assertions.

ADM-AUD-13D mobile verified чистыйnewshot+originalclosedshot: порядок/нетoverflow, различиерасположенияsubmitсохранено. ADM-AUD-13B IN PROGRESS: notpaid+LV paircase/sourceSQLconfirmed; knownoriginalduplicateVRjoin2102/2133/10309 даёт+3/+130, newuniqueитогиобоснованы. DONE RUcountryfilter labels viaDBtranslations,12tests81assertionsPASS/Chrome/reset77+4101.56. TODOremainingfilters/tabs/sort+dragdrop/прочаяматрица.

ADM-AUD-13B IN PROGRESS: actualoriginal painter options nick-email (firstid577),newтолькоemail/orderemail. Восстановитьnick-email+catalogidorder безизмененияpredicate, testrole3only/fallbackblanknick. Затемпарнаяartistвыборка/reset/tabbrowser.

ADM-AUD-13B IN PROGRESS: DONE painter nick-email/catalogorder restored,13tests84checksPASS. Chromepainter577+managerAdmin uniqueorderselectionverified/read-onlySQL duplicatesexplainoldcounts. Reset→productiontab обе5/456EUR ивсеIDsсовпали,shotorders-production-parity. Remainingdate/tab/sort/pagination+13E/F. Workingданныенеизменны.

ADM-AUD-13B IN PROGRESS: DONE actualChrome dates01Aug–01Sep118/5176.84, first/page2IDsmatch; numberdesc/asc/defaultcyclematches+resetsPage; finalReset77/4101.56+13perpage. Screenshotorders-date-filter-parity20261019. Кодне менялся, рабочиеданныенеизменны. TODO paymentrequest/customer/remainingtabs+13E/F/13Ddrop.

### ADM-AUD-13D — повторная сверка внешнего вида и порядка полей, 2026-10-19
IN PROGRESS: Chrome подтвердил порядок полей обеих форм создания. Уменьшить вертикальные промежутки новой формы и высоту общего комментария по оригиналу; проверить итоговый desktop и нижние блоки. Изменения только в представлении, без сохранения заказов.

ADM-AUD-13D visual follow-up DONE (2026-10-19): порядок основных полей подтверждён в Chrome; компактные scoped gaps14px/label4px/comment1row реализованы. Full-page screenshot create-compact-fields-20261019.png просмотрен; gate34tests412assertionsPASS. Общий13INPROGRESS; после этой CSS-правки mobile отдельно не переснимался. Remaining:13B/E/Fmatrix; детали отличий и evidence в журнале.

### ADM-AUD-13B — сброс поиска при смене вкладки, 2026-10-19
IN PROGRESS: actual Chrome переход с OPR-000043 на Завершённые выявил сохранение фильтра в новой админке; оригинальные ссылки открывают вкладку без параметров поиска. Добавить сброс applied/deferred filters, поиска, сортировки и страницы при смене вкладки; проверить регрессией и повторить Chrome.

ADM-AUD-13B tab-reset DONE (2026-10-19): исправлено сохранение applied/deferred filters/search/sort после перехода между вкладками; Chrome exact/partial payment request, completed/unpaid/current проверены.14tests95assertionsPASS/PintPASS; completed duplicateVR итоги объяснены readonlySQL. Общий13INPROGRESS; следующее13Eclipboarddownload/13FUI.

### ADM-AUD-13E — копирование и скачивание, 2026-10-19
IN PROGRESS. DONE: UIcopiedfeedback вChrome; существующийinvoice18451скачанизобеихадминок,1page/textidentical/visualreview;35tests334assertionsPHP+5JS PASS. Systemclipboard точнаявставкане доказана: BrowserUsevirtualclipboardempty. СуществующийPDFне доказываетновуюгенерацию. Следующее13Fread-onlychatUI;рабочиеorders/providersне изменялись.

### ADM-AUD-13F — browser visual чатов, 2026-10-19
DONEдляпроверенныхread-onlyветок:client18451общаяистория,admin18451empty/18198nonempty,painter17696nonempty,client17696двекартины+набросок/4формы;newmobile390pxбезmodaloverflow.64tests788assertionsPASS. Ничегонеотправлялось/markreadненажимался;общий13INPROGRESS. Следующее:сведениеevidenceматрицы13Grolevisual/оставшиесяgates. Ограничения:реальныеmediaфайлычастичноотсутствуют,новыйpainterdesktopпервыйcaptureblankнеproof;надёжныйmobilePNGпросмотрен.

### ADM-AUD-13G — общая регрессия после сверок 19 октября
IN PROGRESS: выполнить актуальный Admin+Invoice+PaymentRequest gate после исправлений создания, фильтров и переключения вкладок; свести текущую матрицу evidence, не закрывать непроверенные browser/role/media ветки.

ADM-AUD-13G regression DONE (2026-10-19):831tests10143assertions,1legacyrouteskip,0errors/failures;JS5PASS. Матрица и очередь уточнены. ОбщийADM13INPROGRESS;следующееisolatedprinting/litemanagervisual,безизменениярабочихролей. Evidence:storage/logs/adm-aud-13-regression-20261019.xml/.txt.

### ADM-AUD-13G — изолированная визуальная матрица ролей, 2026-10-19
IN PROGRESS: подготовить read-only localhost-стенд настоящих Blade колонок и вкладок для admin/printing/lite_manager, фиктивные данные и SQLite:memory. Проверить различия и desktop/mobile; не считать статический preview проверкой интерактивных действий или полной страницы ListOrders.

ADM-AUD-13G role partial-preview PROGRESS (2026-10-19): actual4Bladecolumns+getTabs,admin/printing/lite_manager вChrome;desktop/mobilePNGпросмотрены,15tests323assertionsPASS. Это статическаяfixtureоболочка,НЕfullListOrders. ПолнаяrolevisualостаётсяTODO. Server8773остановлен,рабочиеrolesнеизменены. Следующее13Bclient/phone/remainingtabsreadonly.

### ADM-AUD-13B — client/phone и пустые вкладки, 2026-10-19
IN PROGRESS: новыйUI email/phone3orders66EUR;sent18323/65,printing/sent_today/recent0 подтвержденыreadonlySQLобеихбаз. OldUIfilterJSошибкиdatetimepicker/select2,послеreloadcertificatewarning—ручнойдоступзапрошен. Найденаошибочнаяempty-stateподпись;исправитьлокальноOrdersTable.

ADM-AUD-13B follow-up PROGRESS:email/phoneUInew3/66;sent18323/65и3emptytabsподтвержденыSQLобеихDB. Emptylabelисправлен/Chromeподтверждён;37tests492assertionsPASS. ПарнаяoldUIпроверкаpendingmanualcertallow(tab997247394),неDONE. Покаожиданиевозможны13EisolatedPDF/13Gfullroles.

### ADM-AUD-13E — многостраничный счёт, 2026-10-19
IN PROGRESS: создать60позицийнаSQLite:memory/fakeStorageчерезactualDynamicPDFController;проверитьвсепозиции/итоги/переносы,отрендеритьивизуальнопросмотретькаждуюстраницу. Никакихрабочихorders/PDF/providerwrites.

ADM-AUD-13E long-invoice DONEдля60позиций:2pages/всеID/итог732.05/footerпровереныextract+visual;36tests402assertionsPASS. ADM13Bpendingoldclientphone/sent/printing/sent_today/recentсняты:послепереоткрытияoriginalUIсовпал. Certificateнеобходился. Общий13INPROGRESS;следующееgiftPDF/emailfixture,fullroles/uploadremaining.

### ADM-AUD-13E — подарочные документы, 2026-10-19
IN PROGRESS: реальные GiftcardController PDF и GiftCard::render для7локалей,SQLite:memory/fakePublic/HTTPblocked/Mailfake. Проверитьфон/номинал/код/однустраницу,изоляциюссылокзаказа/экранированиеимени/переводы. ЗатемпросмотретьPDFиHTMLписьмовбраузере. ШаблонписьмаифонSHAсовпадаютсoriginal.

ADM-AUD-13E gift-render subset DONE (2026-10-19):7localeactualPDF+Mailable render,1/2certificateemailbranches;ru/dePDFPNGvisual+ruemaildesktop/mobilepreview.32tests352assertionsPASS. Rawtemplate/backgroundсовпалисoriginal. Outlook/Gmaildeliveryиrealpromoresourcesнепроверялись;provider/mailнеотправлялись. Общий13INPROGRESS;nextfullrolepage/mediaremaining.

### ADM-AUD-13C/D — фактические PHP upload limits OpenServer, 2026-10-19
IN PROGRESS: прочитатьтолькоupload_max_filesize/post_max_size/max_file_uploads/memory_limit черезвременныйлокальныйдиагностическийфайлсIPguard,удалитьсразупослеChromeпроверки. Сопоставитьс100MiBформы/Livewire,неphpinfo/не.env/нерабочиеданные.

ADM-AUD-13C/D OpenServer single-upload transport DONE (2026-10-19):actualPHP8.3.14Apache256M/256M/1GiB;Chrome104857600byteserror0/SHAexact. Tempdiagnosticsудалены,DB/mediaнеписались. ЭтоНЕfullFilamentOpenServersave. Remaining:batch30/drop/attachmentseditpreview/fullroles;goalactive.

### ADM-AUD-13C — PDF/PSD/FIG через форму вложений, 2026-10-19
IN PROGRESS. Проверить настоящий OrderItemsForm и действие «Добавить фото» в Chrome на отдельной SQLite и отдельных каталогах файлов. Сопоставить сохранённые расширения и SHA256 с исходниками, убедиться, что вложения добавлены к выбранной позиции. Рабочие заказы и изображения не менять. Стенд не считать полной страницей EditOrders.

### ADM-AUD-13C — обнаруженные недочёты отображения вложений, 2026-10-19
IN PROGRESS. Пакет PDF/PSD/FIG успешно сохранён через Chrome на изолированном стенде; четыре SHA256 совпали. После действия список в форме остался прежним до перезагрузки; документы выводятся через img и дают сломанные превью. Исправить обновление и компактные карточки документов; сохранить прямую ссылку на исходник и несохранённые поля позиции.

ADM-AUD-13C attachments subset DONE (2026-10-19): Chrome → FilePond/Livewire → настоящий attach action → отдельная SQLite/uploads; PDF/PSD/Figma FIG/Xfig сохранены с точными SHA256 и расширениями только в выбранной позиции. Исправлены компактные карточки документов и обновление списка без reload. Повторный PDF не перезаписал первый, черновик поля не потерян/не записан. 25 тестов, 284 проверки PASS; Pint PASS. Общий ADM13 IN PROGRESS: batch30/drag-drop, полная role-страница и оставшаяся матрица действий. Рабочие данные не менялись; стенд — схема OrderItemsForm, не полная EditOrders.

### ADM-AUD-13C — пакет 30 исходников, 2026-10-19
IN PROGRESS. Проверить в Chrome предел пакета 30 файлов через настоящий attach action на отдельном стенде. Сохранить контрольные суммы существующих вложений, добавить 30 разных PNG к выбранной позиции, проверить каждый файл и количество. PHP max_file_uploads=20 оставить как в OpenServer; выяснить фактическую разбивку запросов Livewire. Проверка не затрагивает рабочие данные.

### ADM-AUD-13G — подготовка полной страницы под ролями, 2026-10-19
IN PROGRESS. Пока идёт пакетная загрузка, подготовить отдельную SQLite с пустыми таблицами по read-only метаданным текущей БД и только фиктивными пользователями/заказами. Цель — настоящий ListOrders с восемью колонками, вкладками и модальными окнами, а не прежний статический preview четырёх колонок. Схема стенда не заменяет проверку MySQL-контрактов; рабочие данные не копировать/не менять.

ADM-AUD-13C batch30 DONE (2026-10-19): Chrome FileChooser→30 отдельных Livewire upload requests files=1/HTTP200→настоящий attach action; все30SHAсовпали, прежние5/другаяпозиция/остальныезаказы целы. PHPmax_file_uploads20 не обрезал очередь. 26tests323assertionsPASS/PintPASS. Это localhost pipeline, не Apache batch; паузы очереди требуют отдельного воспроизведения, drag/drop остаётсяTODO.

ADM-AUD-13G full-list role overview DONE subset (2026-10-19): настоящий ListOrders8columns/admin+printing+lite_manager,printingtabPOSTсохранилроль,desktopPNGпросмотрены;mobile390pageбезoverflow,managerchat358pxполе/submitвмещаются. ТолькофиктивныеданныеотдельнойSQLite,190пустыхтаблицпосхеме. 15tests323assertionsPASS. Предыдущийстатическийpreviewтеперьдополненнастоящейстраницей;всяматрицадействийнеDONE. Следующее13F:artist/lifecycle/SAокнанаэтомстенде+originalsourcecomparison.

### ADM-AUD-13F — художник и жизненный цикл на полной странице, 2026-10-19
IN PROGRESS. Сверить поля original Orders и проверить настоящее управление художником/печатником, дату доставки и статус через ListOrders на отдельной SQLite. Сохранение только фиктивных заказов; внешние уведомления отключить конфигурацией, Mail fake / HTTP blocked. Затем проверить переход из заказа в SA. Документировать различия UI и результаты roundtrip.

### ADM-AUD-13F — вернуть настройки художника в строку, 2026-10-19
IN PROGRESS, уточнение пользователя: открытое окно «Художник и печать» не соответствует оригиналу. В Voyager назначение/снятие исполнителя, срок, оплата/выполнение и показ картин меняются прямо в колонке. Вернуть эти controls в строку списка, убрать кнопку «Управление»; каждое действие сохраняет только своё поле и не перезаписывает соседние изменения. Проверить права печатника, назначение/снятие и немедленное обновление в Chrome на отдельной SQLite.

ADM-AUD-13F inline artist correction DONE (2026-10-19): по замечанию пользователя лишнее окно убрано из сценария списка. Настройки/назначение/снятие художника и печатника снова в колонке, как в original. Немодальный action сохраняет только одно разрешённое поле; staleсоседниезначения не перезаписываются, printingUIиserverdeny. Chrome roundtrip/date/paid/remove/reassign/dialogs0 подтверждены на отдельнойSQLite. 47tests643assertionsPASS/PintPASS. Evidence artist-inline-original-style-20261019.png,inline-artist-verified-20261019.json. ОбщийADM13INPROGRESS, не считать прежнийartistmodalэталоном.

ADM-AUD-13F SA/local lifecycle subset verified (2026-10-19): статус/доставка/отмена наfixture42, SAhistory+managerack тольколокально,delivery/bot/datesне менялись/sa_events0. SA47tests414assertionsPASS. Original rowbadge использует message.status!=read (как текущий код), поэтому acknowledgement менеджера не обнуляет этот отдельный счётчик; поведение не подменялось. Остались остальные сценарии полной матрицы, отправки провайдерам не выполнять.

### ADM-AUD-13 — поля в колонках по скриншоту оригинала, 2026-10-19
IN PROGRESS. Замечание пользователя относится ко всей строке из восьми колонок. Основные найденные отличия: readonly номер накладной/предоплата вместо редактируемых полей, отдельные окна при смене статуса/языка/статуса клиента/даты, растянутый язык счёта. Вернуть нативные поля и непосредственное сохранение в строке, использовать действующие сервисы/валидацию и права; сравнить визуальные размеры/порядок по оригиналу. Создание этикетки/чаты/редактирование заказа остаются отдельными сценариями оригинала, не смешивать с inline полями. Полную задачу не закрывать до сверки всех колонок.

### ADM-AUD-13 — нативные поля строки заказов, 2026-10-19
DONE для найденного подэтапа: номер накладной (серия/создание/изменение/снятие), статус оплаты, сумма предоплаты, язык счёта, статус клиента, статус заказа и дата доставки редактируются непосредственно в соответствующих колонках. Убраны лишние окна из этих сценариев, язык счёта компактный. Сохранение делегировано существующим сервисам, whitelist полей/валидация/проверка edit_orders на сервере; финансовые поля запрещены printing. Структура из восьми колонок и настройки художника в строке сохранены.
Проверки: Chrome на настоящем ListOrders/Livewire с отдельной SQLite; reload подтвердил сохранение; создание→изменение→снятие→повторное создание VR, dialogs=0. Остальные заказы и пользователи не изменились; delivery сохранил остальные поля. Штатные status_date/watching_date, use_bonus и user observer last_ip учитываются как ожидаемые изменения. 35 tests / 652 assertions PASS, JUnit storage/logs/adm-aud-13-inline-fields-20261019.xml. Evidence storage/app/audit/orders-inline-fields-20261019.png и full-role-isolated/inline-fields-verified-20261019.json.
Ограничения: VR refreshDocument на браузерном стенде замокан, создание настоящего PDF этим запуском не проверено; SMTP/HTTP заблокированы, рабочие заказы/файлы не изменялись. Общий ADM-AUD-13 IN PROGRESS. Следующий шаг: сверка оставшихся сценариев документов/этикетки и статусов картин по исходному Voyager, затем drag/drop. Полная идентичность всех действий пока не заявляется.

### ADM-AUD-13F — статусы под каждым превью в колонке художника, 2026-10-19
IN PROGRESS: дополнительный скриншот пользователя выявил отсутствие нативного списка статусов и полной даты под каждым наброском/картиной. Original orders.blade.php 460–590 содержит отдельные select для нормализованных изображений и общий статус старого списка. Вернуть их в колонку; printing видит/меняет только готовые картины. Сохранять один статус, не затрагивая назначение/срок/файлы/другие изображения, проверить чужие id и отсутствие edit_orders. Проверки только на отдельной SQLite.

### ADM-AUD-13F — статусы изображений в строке как в оригинале, 2026-10-19
DONE для подэтапа второго примера пользователя: у каждого нормализованного наброска/картины компактное превью 58x58 (contain, по центру), собственный select статуса и полная дата изменения под ним. В колонке больше не нужна отдельная кнопка окна «Статусы картин». Исходник Voyager orders.blade.php 472–590 проверен: общие статусы старых comma-separated списков закомментированы, поэтому дополнительные controls для них в списке не добавлены; старые превью сохранены. Это уточняет первоначальную формулировку IN PROGRESS выше.
Немодальный updateOrderMediaStatus вызывает OrderArtistService::updateMediaStatus: проверка edit_orders, whitelist типа, существующий статус, принадлежность id заказу и правильной галерее. Printing на сервере может изменять только картины; наброски ему не показываются. Сохраняются status/updated_at только одного изображения; заказ, назначение, файлы и другие изображения не перезаписываются.
Проверки: 62 tests / 944 assertions PASS (ArtistService + RolePresentation + DisplayParity + RecordActionPermissions), Pint выбранных PHP PASS. Общий pint --dirty --test ранее обнаружил форматирование в других незавершённых файлах, автоматически их не меняли. Chrome на настоящем ListOrders/Livewire отдельной SQLite: 901 sketch=2, соседний 902=1, painting903=2, затем printing сохранил painting903=1; reload подтвердил значения, dialogs=0, все три PNG loaded. Verifier подтвердил неизменность orders/users/assignments и SHA256 четырёх fixture PNG. Рабочая БД и картинки не менялись, внешние уведомления не отправлялись.
Evidence: storage/app/audit/orders-inline-media-20261019.png, storage/app/audit/full-role-isolated/inline-media-verified-20261019.json; JUnit storage/logs/adm-aud-13-inline-media-20261019.xml. Общий ADM-AUD-13 IN PROGRESS. Далее — оставшиеся сценарии документов/этикетки и drag/drop; полную идентичность всех действий не заявляем.

### ADM-AUD-13 — раскрывающаяся форма фирмы, 2026-10-22
IN PROGRESS. Продолжение сверки original resources/views/vendor/voyager/orders/id.blade.php: восемь реквизитов редактируются после кнопки «данные фирмы» непосредственно в колонке. Заменить справочную карточку/переход в окно на такую форму; использовать существующий OrderInvoiceService::generate, проверить валидацию и права, передаваемые реквизиты и отсутствие изменений соседних полей. После проверки сделать коммит накопленных изменений ADM-AUD-13. Чужие модели AdmBrowserCycle* и обнаруженные удаления четырёх public/orders/*.webp в коммит не включать.

### ADM-AUD-13 — форма реквизитов и подготовка коммита, 2026-10-22
Реализовано: «данные фирмы» раскрывает восемь редактируемых полей непосредственно в колонке номера, как original orders/id.blade.php; отправка без отдельного окна через updateOrderInvoiceFirm. Сервис генерации PDF прежний, реквизиты проходят whitelist/ограничения длины/обязательные имя и адрес; неизвестные аргументы не передаются. Printing и пользователь без edit_orders не могут сохранить. Значения экранируются. Для показа используются defaults действующего OrderInvoiceService и уже загруженный номер (без повторных запросов VR на каждой строке).
Проверки логики: отдельные Livewire feature-тесты успешного вызова, некорректных полей, прав и безопасного HTML восьми полей; существующие InvoiceDocumentTest покрывают пользовательские реквизиты в документе. Автотесты только SQLite :memory:/изолированные файлы, SMTP/HTTP не выполняются. Браузерное сохранение новой формы фирмы 22 октября ещё не проверялось; это остаётся в общей визуальной приёмке ADM-AUD-13. Удаления четырёх public/orders/*.webp подтверждены пользователем как его изменения, сохранены вне коммита ADM-AUD-13. Не относящиеся к этому этапу AdmBrowserCycle* также не включаются.
Осталось: браузерная сверка раскрытой формы фирмы и сохранения PDF, оставшиеся сценарии этикетки и drag/drop; общий ADM-AUD-13 остаётся IN PROGRESS. Новых миграций и изменений контрактов CRM/SA нет.

ADM-AUD-13 precommit gate, 2026-10-22: 348 tests / 3784 assertions, 0 failures/errors, 1 skip (legacy admin route intentionally disabled in test runtime); JUnit storage/logs/adm-aud-13-precommit-20261022.xml. JS payment-request-copy: 5/5 PASS. Pint пяти изменённых в этом шаге PHP-файлов PASS; git diff --cached --check PASS. Исправлены устаревшие ожидания тестов после переноса статусов/реквизитов в строку. Подготовлен общий коммит накопленных изменений ADM-AUD-13 (77 файлов); пользовательские удаления изображений и две модели AdmBrowserCycle вне коммита. Общая визуальная/провайдерная приёмка не закрыта.

DEV-AUTH-01, результат 2026-10-23: DONE локальные изменения Login (нативный type=password с сохранением Alpine toggle) и Composer post-autoload-dump публикация Livewire assets. FilamentPanelBootTest + FilamentAuthorizationTest: 30 tests / 689 assertions PASS. TODO: серверное применение docs/dev-login-deployment.md и успешный онлайн-вход; общий статус IN PROGRESS.

### ADM-BRAND-01 — логотип входа и шапки, 2026-10-23
DONE: локальная реализация и автопроверки. admin.title и admin.icon_image подключены к общему бренду Filament, fallback — ViarCanvas и URL пользователя. 32 tests / 698 assertions PASS; логотип HTTP 200 image/png. БД и CRM/SA не менялись. TODO после выкладки: визуально проверить dev /filament/login и шапку /filament/voyager-settings (desktop/mobile, тёмная тема). Онлайн-приёмка не заявляется.

### ADM-MENU-01 — персональная видимость меню, 2026-10-23
IN PROGRESS: внизу бокового меню добавить «Показать всё» и кнопку «Настроить меню». Окно на русском с группами, флажками, сбросом и сохранением. Выбор сохранять отдельно для пользователя в существующем users.settings, сохраняя locale и остальные ключи. Источник пунктов — текущее разрешённое BreadMenuTree; пустые группы скрывать, полный режим показывает только доступное по правам. Проверить изоляцию пользователей, права, сохранение/сброс, дерево и UI. Миграции и CRM/SA не требуются.

ADM-MENU-01, результат: DONE реализация и автоматические проверки; IN PROGRESS браузерная приёмка. Livewire MenuPreferences заменяет область nav в существующем sidebar override; нижняя панель закреплена вне прокрутки. Профиль users.settings.filament_menu хранит show_all и hidden (id разрешённых листьев). Сохранение включает личный режим, сброс выбирает все пункты до сохранения, закрытие отменяет несохранённый выбор. Пустые группы исчезают, права не расширяются. Миграции не нужны.
Проверки: 40 tests / 758 assertions PASS; Pint и git diff --check PASS. Окно на русском и нижняя панель визуально проверены desktop на отдельной SQLite. Найдена и исправлена кнопка сохранения (Filament трактовал form как wire:target); теперь wire:click=save, DOM и действие покрыты тестом. TODO: повторный browser save→reload→show-all, mobile/тёмная тема и реальное дерево viar13.loc. Управление браузером отключено в Work, в том числе Chrome, поэтому повторная проверка недоступна. Следующий точный шаг — проверить эти сценарии в открытом Chrome после включения управления браузером; рабочие профили пока не изменялись.

### ADM-MENU-02 — переключатель по образцу и выгрузка, 2026-10-23
DONE реализация: нижняя закруглённая панель «Основное — переключатель — Все — шестерёнка», серый выключенный track/белый бегунок, активный режим справа; русский текст окна уточнён. Сохранена логика персональных настроек. MenuPreferencesTest + BreadMenuNavigationTest: 8 tests / 60 assertions PASS; git diff --check PASS. Browser QA остаётся TODO из-за отключённого управления браузером.
IN PROGRESS упаковка всех коммитов за 2026-10-23 (Europe/Kiev) в C:\OSPanel\domains\28 Viar: файлы с путями относительно проекта, отдельные git patches и manifest. Это пользовательская папка выгрузок для сегодняшних коммитов; в будущих шагах этого чата использовать её. Секреты и runtime-артефакты в выгрузку не включать.

ADM-MENU-02 упаковка DONE: 2 коммита / 16 файлов выгружены в C:\OSPanel\domains\28 Viar; paths сохранены, SHA256 проверены, patches и JSON manifest приложены. Осталась browser-визуальная проверка, описанная выше.

### ADM-COLOR-01 — приглушённый основной синий, 2026-10-23
DONE реализация: точный цвет пользовательского образца #337AB7 используется как primary-600 Filament. Остальные оттенки через generateV3Palette сохраняют приглушённую насыщенность (generatePalette/Color::hex в установленном Filament сохраняет только hue и возвращает высокую chroma). FilamentPanelBootTest 7 tests / 22 assertions PASS; Pint/git diff --check PASS. Визуальная browser-проверка остаётся TODO (управление отключено). Коммит и сегодняшнюю выгрузку обновить в C:\OSPanel\domains\28 Viar.

### ADM-ACTIONS-01 — убрать тень колонки действий, 2026-10-23
DONE: удалён box-shadow у закреплённых правых ячеек .viar-admin-table; положение, фон и действия сохранены. Проверка diff: одна CSS-декларация удалена; git diff --check PASS. Браузерная проверка не выполнена (управление отключено). Выгрузить новый коммит в C:\OSPanel\domains\28 Viar.

### ADM-ACTIONS-02 — тень именно BREAD-таблицы, 2026-10-23
DONE: повторный скриншот gallery-items?type=5 выявил отдельный inline CSS в voyager-bread.blade.php: box-shadow:-4px 0 6px -4px #9ca3af для th/td:last-child. Декларация удалена и для заголовка, и для строк. ADM-ACTIONS-01 удалял только стиль другой таблицы, его результат не подтверждал исправление BREAD. Проверены оба источника; git diff --check PASS. Browser QA остаётся TODO (управление отключено). Коммит выгрузить в C:\OSPanel\domains\28 Viar.

### ADM-FILTERS-01 — подписи и сброс фильтров заказов, 2026-10-23
IN PROGRESS: длинные подписи одной строкой с ellipsis в ширине ячейки; «Сбросить» только при непустых применённых или ещё не применённых фильтрах. Сохранить штатный resetTableFiltersForm, нулевые значения считать заполненными. Проверить фильтрацию и сброс, выгрузить коммит в C:\OSPanel\domains\28 Viar.

ADM-FILTERS-01 DONE реализация/автопроверки: scoped CSS labels no-wrap/ellipsis; override штатного filters Blade ограничивает reset visibility только ListOrders через реактивные applied+deferred state. 23 search tests /397 assertions +21 role/tabs tests /364 assertions PASS; JS3/3PASS. Browser визуальная приёмка TODO из-за отключённого управления. Миграций/API нет. Коммит выгрузить в пользовательскую папку за сегодня.

### ADM-FILTERS-02 — сворачиваемая полоса фильтров, 2026-10-23
DONE реализация: ListOrders по умолчанию expanded:false на всех ширинах, кнопка-полоса «Фильтры» раскрывает/сворачивает поля и «Применить», chevron/aria-expanded/keyboard support. Значения и условный reset сохранены. 34PHPtests/693assertions и JS3/3PASS; git diff --check PASS. Browser QA TODO: управление отключено. Выгрузить коммит в C:\OSPanel\domains\28 Viar.

### ADM-FILTERS-03 — плавное раскрытие, 2026-10-23
DONE: x-collapse.duration.250ms на блоке фильтров ListOrders — анимация высоты в обе стороны через штатный Alpine collapse Filament. JS3/3PASS; git diff --check PASS. Визуальная browser QA недоступна (управление отключено). Коммит выгрузить в C:\OSPanel\domains\28 Viar.

### ADM-TABS-COLOR-01 — цветные вкладки заказов, 2026-10-23
DONE реализация по образцу: current/production оранжевые, completed голубая, express/unpaid красные, printing светло-зелёная, sent_today серая, sent светло-голубая, new_recently зелёная, all нейтральная. Цвета привязаны к ключам вкладок, не порядку/роли. Активная — контрастная рамка. 21tests/364assertionsPASS; Pint/diff checkPASS. Browser QA TODO (управление отключено). Выгрузить в C:\OSPanel\domains\28 Viar.

### ADM-TABS-COLOR-02 — убрать лишние счётчики и «Все», 2026-10-23
DONE: нулевые badge возвращают null, у completed badge убран целиком; all удалена из tabs/порядка и CSS. 22tests/372assertionsPASS; Pint/diff checkPASS. Browser QA TODO. Коммит выгрузить в C:\OSPanel\domains\28 Viar.

### ADM-TABS-COLOR-03 — спокойный стиль без обводки, 2026-10-23
DONE CSS: пастельные статусные заливки, приглушённый текст, компактные badge, убрать рамку active и внешнюю ring/shadow панели. Active выделен насыщенностью фона/тонкой нижней линией. Dark/focus/hover предусмотрены. Только CSS, git diff --checkPASS; browser QA TODO (управление отключено). Коммит в C:\OSPanel\domains\28 Viar.

### ADM-ORDERS-COMPACT-01 — поиск в строке фильтров, 2026-10-23
DONE CSS на ширине>=1024: native toolbar search/columns совмещён с полосой фильтров через grid, отдельная нижняя строка не занимает высоту. Поля раскрытия остаются полной ширины; на меньших экранах прежнее вертикальное размещение. Summary/RolePresentation15tests323assertionsPASS; diff checkPASS. Browser QA TODO (управление отключено), особенно проверить массовые действия/поиск и раскрытие. Выгрузить в C:\OSPanel\domains\28 Viar.

### ADM-HEADING-TOTALS-01 — итоги возле заголовка, 2026-10-23
DONE: количество/сумма перенесены к «Заказы», две компактные иконки с доступными подписями. table description убран. Сохранены query/filter/tab scope и role deny printing/lite_manager.16tests330assertionsPASS; Pint/diff checkPASS. Browser QA TODO из-за отключённого управления. Коммит выгрузить в C:\OSPanel\domains\28 Viar.

### ADM-CHAT-WIDTH-01 — одинаковая ширина кнопок чатов, 2026-10-26
DONE: все четыре кнопки в order-comments Blade width145px/max-width100%, одинаковое центрирование. Ранее синие122px, SA min-width145px. Только оформление; diff checkPASS. Browser QA не выполнена.

### ADM-RECIPIENT-WRAP-01 — перенос получателя, 2026-10-26
DONE: order-recipient wrapper white-space normal/overflow-wrap anywhere; адрес/email не наследуют nowrap таблицы. Только оформление; diff checkPASS, browser QA не выполнена. Выгрузка C:\OSPanel\domains\28 Viar.

### ADM-FIRM-CENTER-01 — центрирование «данные фирмы», 2026-10-26
DONE: summary block/fit-content/margin-inline auto; форма сохраняет левое выравнивание. Только оформление; diff checkPASS, browser QA не выполнена. Выгрузка C:\OSPanel\domains\28 Viar.

### ADM-ORDER-SPACING-01 — вертикальные отступы строк, 2026-10-26
DONE: .viar-orders-table .fi-ta-row>.fi-ta-cell padding-block10px. Все колонки заказов получают одинаковый верхний/нижний отступ, заголовки/другие таблицы прежние. diff checkPASS; browser QA не выполнена. Выгрузка C:\OSPanel\domains\28 Viar.

### ADM-CATALOG-LABEL-01 — убрать Voyager из названий разделов, 2026-10-26
DONE: пункт «Все разделы», заголовок «Разделы» в VoyagerCatalog; route/class прежние. PHP lint/diff checkPASS. Выгрузка C:\OSPanel\domains\28 Viar.

### ADM-AUD-13 — браузерный цикл фирмы и настоящий PDF, 2026-10-26
IN PROGRESS. Chrome доступен. Проверить настоящий ListOrders: раскрытие восьми реквизитов, сохранение и результат генерации PDF без mock OrderInvoiceService. Только отдельная копия SQLite/изолированный public_path/uploads; SMTP/HTTP blocked. Проверить изменение только has_pdf/pdf_link/updated_at выбранного заказа, наличие реквизитов в PDF, desktop/mobile раскрытую форму. Рабочие заказы/изображения не менять.

### ADM-AUD-13C — визуальное соответствие форм создания/редактирования, 2026-10-26
IN PROGRESS по замечанию пользователя. Скриншоты original/new edit18451 и create сохранены в storage/app/audit/*order*-20261026.png. Original edit: две половины, слева manager/comment/client/sales/payment/requests/delivery/discount/company, справа media/comments/items. Новый edit ошибочно full-width последовательность. Вернуть две колонки с полями в исходном порядке, компактными controls/заголовками; новые необходимые служебные поля сохранить отдельно. Проверить обе страницы desktop/mobile, без сохранения рабочих заказов. Создание уже двухколоночное, требует согласования размеров/подписей/плотности. Функциональные state paths/сервисы сохранить.

### ADM-AUD-13 — фирма/PDF и раскладка форм, 2026-10-26
DONE браузерный подэтап фирмы: настоящий ListOrders/Livewire на 127.0.0.1:8777, отдельная SQLite firm-pdf-isolated-20261026/public_path; все восемь введённых реквизитов сохранены в настоящем Dompdf PDF без mock InvoiceService. Файл 931211 bytes/1 страница, текст всех восьми реквизитов найден, PNG страницы просмотрен. Сравнение до/после БД: только has_pdf/pdf_link/updated_at заказа42; остальные orders/users/vr_numbers неизменны. HTTP blocked/Mail fake. Evidence storage/app/audit/firm-pdf-isolated-20261026/verified.json и invoice-page1.png. Браузерный mobile формы фирмы отдельно не проверен.
DONE основной визуальный подэтап create/edit по замечанию пользователя: открыты original /admin/orders/18451/edit и /admin/create_admin_order, новая та же запись/создание; сделаны и просмотрены screenshots до/после. Edit возвращён к двум половинам: слева manager/admin_comment/контакты/общий комментарий/канал/категория, оплата/requests/доставка/скидки/реквизиты; справа gallery/comments/items. Служебные status/price/sale_price сохранены в свёрнутом блоке «Дополнительные параметры». Поля позиции вертикальны. Галереи вертикально с центрированными заголовками. Общие scoped CSS create/edit: меньше радиусы/внутренние отступы, облегчённые подписи, компактные input/select. Заголовки/основные кнопки согласованы с original: «Заказ №…»/«Создание заказа», «Обновить»/«Добавить». Create секции подписаны «Данные пользователя», «Юридическая информация», «Товары в заказе». Цвет payment_status перенесён и на edit. Никакие бизнес-сервисы/state paths не менялись; selection/save права прежние.
Browser: desktop original/new screenshots открыты и сопоставлены, mobile390 обе формы в одну колонку, formwidth342.67/document375<=viewport390, горизонтального выхода нет. Рабочие формы только читались, submit/create/update позиций на них не вызывались. Evidence storage/app/audit/original-order-{edit,create}-20261026.png, new-order-{edit,create}-{before,after,mobile}-20261026.png. Автотесты 77tests/1164assertions PASS, Pint изменённых PHP PASS; JUnit storage/logs/adm-aud-13-form-layout-20261026.xml. Миграций/API/реальных отправок нет.
Ограничения: это исправление структуры/плотности и начальных экранов, не заявление полного pixel parity всех состояний. Новые полезные controls поиска клиента/дополнительных параметров сохранены. Остаётся визуально пройти раскрытие доставки, юрлица и действий добавления позиции/фото; Venipak и drag/drop остаются в общем backlog. Общий ADM-AUD-13 IN PROGRESS.

### ADM-AUD-13C — фоновое выделение групп оригинала, 2026-10-26
IN PROGRESS: по замечанию пользователя восстановить не только раскладку, но и серые группы. Original order.blade.php/order_create.blade.php используют background#fafafa/border#d9d9d9/radius8/padding14 для sales/payment/delivery/user blocks и позиций. Scoped классы добавлены к этим секциям, inputs белые; галереи остаются белыми. Проверить Chrome desktop/create/edit и отсутствие влияния на модальные формы/остальную админку.

ADM-AUD-13C фон групп DONE, 2026-10-26: восстановлены original #fafafa/#d9d9d9/radius8 для блоков данных пользователя в create, оплаты/доставки, payment requests и позиций; в edit канал/категория отдельно выделены серой группой. Inputs белые, galleries белые, dark overrides предусмотрены. Chrome выявил белую внутреннюю .fi-section, перекрывавшую фон wrapper; исправлено наследование, computed внутреннего контейнера rgb250/250/250 в обеих формах. Итоговые PNG просмотрены: storage/app/audit/order-{edit,create}-background-20261026.png. Feature subset13tests195assertionsPASS/PintPASS/diffcheckPASS; никаких сохранений рабочих форм/новых DB/API. План и журнал обновлены; общий ADM13INPROGRESS.

### ADM-AUD-13C — коммит и выгрузка форм, 2026-10-26
По указанию пользователя коммитить выполненные изменения и складывать файлы для выгрузки в C:\OSPanel\domains\31 Viar\ (новая актуальная папка вместо 28 Viar). В этот пакет входят двухколоночные create/edit, порядок полей, компактные controls, подписи кнопок, вертикальные галереи и фон групп как original. Проверки:77tests1164assertionsPASS + после фона13tests195assertionsPASS; Pint/diffcheckPASS; Chrome desktop/mobile и настоящий PDF на отдельном стенде подтверждены выше. В пакет включить только отслеживаемые изменения задачи, git patch и manifest с SHA256; секреты/runtime/screenshots/тестовые DB не выгружать. Общий ADM-AUD-13 остаётся IN PROGRESS: оставшиеся состояния форм, Venipak и drag/drop.

### ADM-FORMS-01 — единые современные поля админки, 2026-10-26
IN PROGRESS. По уточнению пользователя стиль вводов распространяется на все формы админки, BREAD и модальные окна. Отдельный общий CSS после styles workflows: radius8/тонкая граница/без объёмной тени, focus ring primary, видимые invalid/disabled, dark/reduced-motion. Нативные legacy inputs вне wrapper также стилизуются без изменения размера/status background. Checkbox/radio/file и вложенные controls исключены из native override. Проверить в Chrome orders, BREAD и модальное окно, затем commit+выгрузка C:\OSPanel\domains\31 Viar.

ADM-FORMS-01 DONE реализация общего стиля, 2026-10-26: public/css/filament-form-controls.css подключён глобально через AdminPanel head hook после workflow CSS, поэтому покрывает формы/BREAD/окна/авторизацию Filament и legacy native controls. Radius8/border#d0d5dd/без объёмной тени, primary focus ring3px, invalid red/disabled/dark/reduced-motion; checkbox/radio/file сохраняют штатный вид. Размеры и status backgrounds сохранены. Chrome orders wrapper radius8/shadownone, BREAD textbox/ALT wrapper radius8, focus borderprimary/3pxring подтверждены computed и PNG; меню настроек открыто без сохранения, checkbox штатные. Dark/invalid/disabled предусмотрены CSS, отдельный интерактивный прогон этих состояний не выполнялся. 7boot tests22assertionsPASS, diffcheckPASS. Evidence storage/app/audit/admin-inputs-bread-20261026.png. Код/журнал закоммитить и выгрузить в C:\OSPanel\domains\31 Viar. Данные форм не менялись.

### ADM-AUD-13C — убрать лишний сценарий «Просмотр», 2026-10-26
IN PROGRESS по замечанию пользователя. /filament/orders/{id} — добавленная read-only карточка Filament, не сценарий original create/edit. Original Admin/OrdersController::create_admin_order возвращает back с номером/успехом, order_create показывает success banner. Удалён header ViewAction в edit, create redirect возвращает к create с существующим уведомлением/номером. Сам read-route сохранён для совместимости ссылок; данные/права не меняются. Проверить create redirect feature и отсутствие кнопки в Chrome, затем commit+31 Viar.

ADM-AUD-13C лишний просмотр DONE, 2026-10-26: create возвращает к форме создания, уведомление содержит номер нового заказа; header ViewAction из edit удалён. Сам read-route оставлен для совместимости. 27tests304assertionsPASS (создание настоящей fixture записи/redirect + details), Pint/diffcheckPASS. Chrome edit18451 viewLinks0/viewButtons0, рабочие данные не сохранялись. Evidence storage/app/audit/order-edit-no-view-20261026.png. Commit/export в C:\OSPanel\domains\31 Viar; общийADM13INPROGRESS.

### ADM-AUD-13B — счётчик «Надо отправить сегодня», 2026-10-26
DONE: express tab ранее не имела badge вообще; добавлен count той же выборки status!=completed. Исходный express сценарий сортирует все текущие заказы по приоритету, а не фильтрует strictly date=today — выборка/логика original не менялись. При нуле badge скрыт, как остальные. Тест noncompleted5/completed2/zero, вместе Tabs+RolePresentation23tests376assertionsPASS/PintPASS/diffcheckPASS. Chrome локальная админка показала «Надо отправить сегодня 77»; PNG storage/app/audit/order-express-count-20261026.png. Рабочие данные не менялись. Commit/export C:\OSPanel\domains\31 Viar.

### ADM-MENU-03 — ширина свёрнутой панели, 2026-10-26
IN PROGRESS: Chrome показал collapsed105.33px при --collapsed-sidebar-width4.5rem/root16, навигация padding24px и нижняя bar81px. Свёрнутая sidebar растягивалась по содержимому. На desktop закреплены width/flexbasis штатного collapsed token72px; compact nav padding12px; компактная gear42x42 без большой рамки, minwidth0 у personal menu. Expanded/mobile ширина не переопределяется. Проверить Chrome collapse/expand и кнопку настроек, tests/menu, commit/export31Viar.

ADM-MENU-03 DONE,2026-10-26: Chrome collapsed72px вместо105.33px, gear42x42 вместонижнейbar81px; expanded320px сохранён. Collapse→expand→collapse и открытие/закрытие настроек шестерёнкой проверены, profile save не выполнялся.8tests60assertionsPASS/diffcheckPASS. Evidence storage/app/audit/menu-compact-72-20261026.png. Исправление commit/export31Viar; мобильная ширина штатная, правило ограничения действует толькоdesktop>=1024.

### ADM-MENU-04 — зазор после раскрытого меню, 2026-10-26
IN PROGRESS по скриншоту с красной полосой. Chrome: sidebar320px/mainctn x320, .fi-main padding32px, page x352; лишняя полоса — padding основного контента. Уменьшить padding-inline-start до12px только desktop>=1024 при mainctn-sidebar-open. Ширина меню/scrollbar/правая сторона/мобильные отступы прежние. Проверить actual gap, commit/export31Viar.

ADM-MENU-04 DONE,2026-10-26: Chrome expanded sidebar320px, gap12px вместо32px; collapsed72px сохранён. Изменена одна desktop CSS декларация для открытой панели, бизнес-логика/данные прежние. diffcheckPASS; отдельные автотесты на CSS-отступ не добавлялись. Evidence storage/app/audit/menu-expanded-gap12-20261026.png. Commit/export31Viar.


### ADM-MENU-05 — выравнивание нижней кнопки меню, 2026-10-26

IN PROGRESS: в свёрнутом меню иконки навигации учитывают ширину полосы прокрутки, нижняя кнопка центрируется по всей ширине. Для нижней панели добавлен такой же стабильный резерв полосы прокрутки и боковые отступы. Следующее действие: проверить центры иконок в Chrome.

ADM-MENU-05 DONE: Chrome — центры всех видимых иконок и нижней шестерёнки совпадают (x=28.333px); нижняя кнопка учитывает scrollbar gutter навигации. PNG storage/app/audit/menu-settings-aligned-20261026.png. Визуально проверены верхняя и нижняя части меню; данные/настройки профиля не сохранялись. CSS-only, отдельные автотесты не добавлялись. diffcheck PASS; commit/export C:\OSPanel\domains\31 Viar.


### ADM-MENU-06 — равномерные отступы свёрнутого меню, 2026-10-26
IN PROGRESS: ширина compact rail уменьшена с72 до56px; скрыта только полоса прокрутки compact navigation (прокрутка сохраняется), удалён резерв scrollbar нижней панели. Отступы12px с обеих сторон кнопок. Раскрытое меню не меняется. Следующее действие: Chrome и commit/export31Viar.

ADM-MENU-06 DONE: Chrome compact sidebar56px; отступы кнопок навигации слева12/справа12px, нижней кнопки12/12px. Визуальная проверка PASS, diffcheck PASS. На текущей высоте окна все пункты помещаются, вертикальная прокрутка остаётся overflow-y:auto. Отдельные автотесты на CSS не добавлялись. Commit/export31Viar.


### ADM-MENU-07 — проверка вложенности конструктора меню, 2026-10-26
IN PROGRESS. Пользователь сообщил о потере вложенности на dev.viarcanvas.com/filament/menu-items; эталон viarcanvas.loc/admin/menus. Original Voyager builder использует Nestable default maxDepth5; новый JS и PHP также допускают5 уровней. Original orderMenu рекурсивно сохраняет parent_id/order, новая реализация использует ту же схему + атомарную проверку полноты дерева. Начальные проверки: PHP menu_tree3tests41assertionsPASS, JS4testsPASS. Chrome недоступен: Local browser use is disabled in Work settings. Причина на dev не подтверждена; рабочие данные не изменять и не восстанавливать связи догадками. Следующее действие: тест перемещения целой ветки с последующим редактированием/переводом и браузерное сравнение после включения Chrome.

ADM-MENU-07 browser evidence: Chrome доступ восстановлен; original admin builder128 nodes, dev138 nodes. У128 общихID parent совпадает; extra10 IDs123,128,115,117,131,132,167,125,126,166 отображаются recovery roots. Локальный read-only DB подтверждает отсутствующие parent112/116/124. Риск подтверждён по коду: сериализация recovery roots как null переписывала исходный parent при постороннем drag. В работе: сохранять исходный отсутствующий parent при root→root сортировке; показывать предупреждение. Перенос под существующего родителя по-прежнему сохраняется. Dev/old данные не сохранялись.

ADM-MENU-07 DONE исправление и проверка локальной реализации: сортировка recovery roots сохраняет исходный отсутствующий parent; осознанный перенос под существующий узел и последующее вынесение в root работают. В форме добавлен текущий missing parent option; rename/перевод больше не требуют потери старой связи. Предупреждение в дереве сообщает missing parent. Добавлены regression tests: move5level branch→edit→RU→reopen с сохранением потомков; orphan root sort/repeat/edit/RU/reparent/root. PHP menu_21tests298assertionsPASS, JS4testsPASS, diffcheckPASS. Начальный широкий прогон выявил неполную users fixture (нет settings; SQLite value возвращала строку settings), fixture дополнена nullable settings; production MenuPreferences не менялся. Chrome dev/old сравнение read-only:128 общих связей совпадают; local UI выбран missing parent112 (PNG storage/app/audit/menu-missing-parent-20261026.png). Браузерное перетаскивание рабочих меню не выполнялось, запись проверена Livewire/SQLite fixtures. Original Nestable maxDepth5 сохранён. Dev не развёртывался, parent112/116/124 не восстановлены, рабочие menu_items не изменялись. Следующий шаг: выгрузка исправления на dev и согласование нужных родителей для10 legacy orphan nodes; автоматически восстанавливать удалённые разделы нельзя по имеющимся данным. Commit/export31Viar.

ADM-MENU-07 проверка после выгрузки пользователем: IN PROGRESS dev verification. Chrome reload dev:138 nodes, предупреждений missing parent0; UI edit123 Родитель=Выбрать вариант, missing option отсутствует. Это не позволяет отличить stale PHP/Blade от уже null parent_id на dev. Не считать исправление подтверждённым на сервере до read-only SQL parent_id для123,128,115,117,131,132,167,125,126,166 и проверки актуальностиPHP. Никаких сохранений/drag рабочих меню не выполнялось. Локальные21PHP/4JS проверки остаютсяPASS. Следующий шаг: серверный read-only DB lookup, затем очистка caches при необходимости.

ADM-MENU-07 DONE, подтверждение пользователя2026-10-26: «меню все норм». Пользовательский screenshot SQL подтвердил parent_id=NULL для10 ранее orphan nodes; предоставленный dump admin_dev не содержит родителей112/116/124. Предложен SQL для осознанного размещения10 пунктов внутри существующего разделаПОРТРЕТЫ135, без утверждения точного восстановления удалённых групп. Конкретное выполнение этого SQL отдельно не подтверждалось; итоговую работоспособность меню принял пользователь. Ранее локальные21PHP298assertions/4JS PASS. Открытых действий по меню нет. Следующий приоритетADM-AUD-13: сверка оставшихся состояний create/edit заказов с Voyager.


### BLOG-5FA03E77 — перенос коммита 5fa03e77, 2026-10-27
IN PROGRESS: источник C:\OSPanel\domains\asoft\viar, точный diff 5fa03e7776016ab49eb9a2ac2d071ff31afc88a1. Перенести 41 языковой файл, API ALT/story/revisions/idempotency/media, публичные языковые ссылки/дату/CSS. Адаптировать simulator к Filament; транзакции BREAD уже существуют. Проверить реальные отличия native EN/fallback, public disk, JSON ошибок; добавить regression feature tests. Не переносить Voyager runtime. Миграция unique key требует проверки legacy duplicates, не удалять данные. Следующий шаг: завершить адаптацию, SQLite regression tests и lint/Blade.


### BLOG-5FA03E77 — DONE (локальная реализация), 2026-10-27
Перенесён точный diff 5fa03e7776016ab49eb9a2ac2d071ff31afc88a1 из C:\OSPanel\domains\asoft\viar в viar13. Все 41 языковой файл адаптированы с сохранением прежних изменений viar13; сверка изменённых значений с исходным коммитом: 36 верхнеуровневых ключей, 0 расхождений. resources/lang/lv/canvas.php объединён вручную; hero_subtitle добавлен как совместимый ключ, действующий slider по-прежнему читает canvas_slider/translations (БД не заменена статикой).
Реализованы: ALT трёх изображений статьи и story_image через image_alt_suggestions, контракт automation_contract=3 (6 локалей/5 SHA256/только draft), native EN без фиктивных переводов, fingerprint создания/409 при переиспользовании ключа, revision/content_revision и блокировка PATCH, детерминированные media filenames. Публичные ссылки/hreflang/языковое меню исключают отсутствующие переводы и PL; unprefixed article не перенаправляется по locale сессии. Перенесены дата публикации/CSS узких экранов. Совместимость Translatable(false) учтена локально в BlogController: явные строки translations без ложного fallback.
Filament simulator использует Livewire executeSimulator для чтения ревизий, сохраняет введённый текст при конфликте/ошибке и блокирует stale save. Оригинальный legacy simulator также синхронизирован. Voyager middleware BlogEditTransaction не добавлялся: VoyagerBread уже выполняет транзакцию/lockForUpdate. Hardcoded Voyager UI для записи82 не переносился: Filament читает реальные translations без fallback; отдельный Voyager runtime не подключён.
БД: добавлен только unique index blog_posts_idempotency_unique. Миграция проверяет duplicates и останавливается без удаления/перезаписи записей; поддерживает повторный up. Локальная MySQL: duplicates=0, миграция применена успешно, индекс подтверждён. Данные orders/чатов/контента не редактировались.
Проверки: PHP8.4/PHPUnit12 — 62tests/299assertions (API/frontend boot/SEO/ALT/panel/simulator) + BREAD blog7tests/207assertions =69tests/506assertions PASS. JS revision workflow3tests PASS; simulator node --check PASS; PHP lint46 изменённых tracked PHP PASS, новые middleware/migration проверены Pint/исполнением; Blade view:cache PASS; git diff --check PASS. JUnit: storage/logs/blog-5fa03e77-tests.xml; переводы: storage/app/blog-translation-audit/verified.json. Старые19 API tests переведены с неисполняемого @test на PHPUnit12 attributes и реально выполнены.
Ограничения: browser визуальная проверка этого изменения не выполнялась; сервер не развёртывался. Следующий точный шаг на сервере: применить эту миграцию, php artisan view:cache, проверить статью/переключение языка/симулятор. Все PATCH-клиенты должны передавать expected_revision и expected_status из последнего GET; старые запросы без revision получают428. Внешние платные ALT-запросы не запускались. Общая миграция Laravel13 остаётся IN PROGRESS.


BLOG-5FA03E77 export, 2026-10-27: по запросу пользователя из точного коммита 7f990c3e45019f8e04037999c242b3c3304a8bfe выгружены 58 файлов в C:\OSPanel\domains\viar\viar13\ с сохранением структуры. Все SHA256 совпадают с git blobs; 4 Markdown-файла исключены, существующие посторонние файлы назначения не удалялись. Это пакет выгрузки, серверный deploy не выполнялся. Следующий шаг: загрузка пакета пользователем на сервер и применение миграции/Blade cache по инструкции выше.

## ADM-AUD-13G — Мобильный индикатор SA и кнопки формы, 2026-10-29

- DONE для дефекта перекрытия footer: на экранах до 640px добавлен нижний
  отступ 96px + safe-area только при наличии доступной ссылки SA.
- Проверено в actual EditOrders на изолированном SQLite fixture42:
  printing 390x844, lite_manager 320x700 и desktop. В конце страницы кнопки
  «Обновить»/«Отмена» выше индикатора (зазор >65px); на 320px scrollWidth=clientWidth=305.
  На desktop padding-bottom=0, badge top=70. Перетаскивание/звук/переход и бизнес-логика не менялись.
- Read-only browser, все девять business tables неизменны; внешние вызовы
  заблокированы fixture, сервер остановлен. Скриншоты sa-footer-*-fixed.png:
  storage/app/audit/roles-20261028. git diff --check PASS.
- Изменён только public/css/filament-admin-workflows.css; non-md экспорт
  в C:/OSPanel/domains/viar/viar13 с совпадением SHA256. Коммит без push.
- Общий ADM-AUD-13 остаётся IN PROGRESS: это закрытие мобильного дефекта,
  полная приёмка всех окон/типов товаров/комбинаций ролей не заявляется.
  Следующий шаг — продолжить сверку оставшихся окон и действий с оригиналом.
## ADM-AUD-13G — Окна email и запроса отзыва под ролями, 2026-10-29

- IN PROGRESS: source user_data/items подтверждает email для обеих ролей,
  requestReview только для non-printing. Проверить actual ListOrders fixture42:
  printing/lite_manager, открытие/отмена email desktop/mobile, manager review
  confirmation. Без submit/рабочих writes; fake Mail/HTTP, сверка snapshot.
- Результаты внести в журналы; изменения текущей задачи коммитить без push,
  non-md при наличии копировать с SHA256 в согласованную папку.
## ADM-AUD-13G — Окна email и запроса отзыва под ролями, 2026-10-29

- DONE для read-only browser поднабора: original user_data.blade.php показывает
  «Написать email» без printing guard; items.blade.php скрывает «Запрос отзыва»
  при printing. Actual ListOrders fixture42 повторяет эту видимость.
- Printing email desktop/390px и manager email390px: пользователь заказа
  fixture2@example.test, четыре поля, исходные заполненные тема/приветствие/прощание,
  пустой основной текст. Manager review confirmation desktop/390px показывает
  номер42 и email того же пользователя. Окна помещаются, Cancel закрывает:
  remainingDialogs=0/remainingConfirmations=0. Ни одна отправка не нажималась.
- 13tests307assertions PASS (role presentation + customer action permission
  revocation); все9business tables fixture unchanged. HTTP/Mail fakes, рабочие
  заказы и роли не менялись. Сервер остановлен, viewport reset.
- Evidence: storage/app/audit/roles-20261028/email-printing-{desktop,mobile}.png,
  email-manager-mobile.png, review-manager-{desktop,mobile}.png.
- Production code не менялся: для экспорта non-md файлов нет. Только документы,
  коммит текущей ветки без push. Общий ADM-AUD-13/13G остаётся IN PROGRESS.
- Следующее точное действие: ADM-AUD-13G — форма этикетки Venipak под printing
  и lite_manager: сверить доступ и поля с original, открыть/отменить desktop/mobile
  на isolated fixture без вызова перевозчика и без изменения labels.
## ADM-AUD-13G — Форма этикетки Venipak под ролями, 2026-10-29

- IN PROGRESS: original payment.blade.php располагает label create/print вне
  printing guard. Проверить actual ListOrders42 printing/lite_manager:
  opening/defaults/address-pickup/package controls/cancel, desktop/390px.
- Сверка полей с original form__label.blade.php. Никаких provider calls,
  создания/печати этикеток или сохранения рабочих данных. Только isolated SQLite.
- После проверки — evidence/журналы, non-md export с SHA256 при изменениях,
  commit текущей ветки без push.
## ADM-AUD-13G — Форма этикетки Venipak под ролями, 2026-10-29

- DONE для browser opening/cancel поднабора: original payment.blade.php
  create/print вне printing guard; actual ListOrders42 printing/lite_manager
  открывают форму. Сверены source form__label.blade.php и новые основные блоки:
  документ, получатель/отправитель, комментарии, сроки, указания, страховка, упаковки.
- Desktop paired sections и mobile390 single column просмотрены; footer
  «Отменить»/«Внести данные» доступен. Printing: address→pickup показывает
  город/пункт вместо address fields; обратное переключение восстанавливает улицу.
- Fixture без активных данных отправителя/справочника: поля отправителя пусты,
  pickup явно сообщает о недоступном/пустом списке. Выбор реального пункта
  и positive provider path этим browser прогоном не подтверждены.
- Временный draft printing: вес1.25 → добавленная посылка тоже1.25;
  страховая стоимость1000 → расчёт3 EUR. После Cancel и открытия manager:
  одна упаковка, вес пустой. Это form state, не создание отправления.
- 24tests186assertions PASS: OrderVenipakActionsTest, OrderVenipakLabelServiceTest,
  VenipakPickupCatalogTest. Все9business tables неизменны (включая orders.labels).
  Submit/create/print не запускались, HTTP/Mail заблокированы, рабочие данные
  не менялись. Dialog0, viewport reset, тестовый сервер остановлен.
- Evidence storage/app/audit/roles-20261028/venipak-*.png. Production code
  не менялся; non-md файлов для экспорта нет. Документы коммитить без push.
- Общий ADM-AUD-13/13G IN PROGRESS. Следующий точный шаг:
  ADM-AUD-13G — История статусов и просмотр клиента под обеими ролями:
  открыть/закрыть на fixture desktop/mobile, сверить original, без смены статусов.
## ADM-AUD-13G — История статусов и просмотр клиента под ролями, 2026-10-29

- IN PROGRESS: actual ListOrders42 printing/lite_manager, раскрытие истории,
  клиентская карточка desktop/mobile390 и закрытие. Без изменения статусов/клиента.
- Original orders.blade.php выводит status dates и ведёт на /admin/users/{id};
  новая кнопка открывает краткую карточку. Отдельно проверить доступность полного
  пользовательского раздела и зафиксировать функциональные различия.
- Только isolated SQLite/fake интеграции; snapshot, журналы, commit без push,
  export non-md с SHA256 при наличии.
## ADM-AUD-13G — История статусов и просмотр клиента под ролями, 2026-10-29

- DONE browser read-only поднабор: actual ListOrders42 printing/lite_manager
  desktop/390px. История раскрывает одинаковые watching/pegging/send dates:
  19.10.2026 18:19 /17:55 /17:48. Источник — те же legacy status date columns;
  порядок стадий сохраняется, даты форматируются, блок сворачивается details.
- Клиентская modal обеих ролей показывает user2, имя, email/телефон и3заказа;
  просмотрена desktop/mobile, закрыта без сохранения. Это только краткая карточка.
- FOUND/TODO ADM-AUD-13G — Полная карточка клиента из заказа:
  original orders.blade.php:865 ведёт /admin/users/{user_id}, новая кнопка
  viewOrderClient показывает5полей без перехода к полному BREAD read. Поэтому
  соответствие «Просмотр клиента» НЕ принято. Следующий точный шаг: восстановить
  переход к существующему users BREAD read с его permissions, сверить доступ
  read_users/browse_users и поведение отсутствующего клиента; не расширять права.
- 8tests77assertions PASS (OrderLifecycleServiceTest). Все9fixture business tables
  unchanged. Статусы/клиенты/письма не менялись. Viewport reset, сервер остановлен.
- Evidence: storage/app/audit/roles-20261028/history-{printing,manager}-{desktop,mobile}.png,
  client-{printing,manager}-{desktop,mobile}.png. Production code не менялся;
  non-md файлов для копирования нет. Документы коммитить без push.
- Общий ADM-AUD-13/13G IN PROGRESS; новая найденная разница остаётся в этом блоке,
  к другому модулю не переходить до решения.
## ADM-AUD-13G — Полная карточка клиента из заказа, 2026-10-29

- IN PROGRESS: добавить прямой BREAD read route с проверкой read конкретной
  записи, ссылку из краткого окна только при разрешённом users read.
  Существующие BREAD поля/политики использовать без копирования/расширения прав.
- Уточнение пользователя: коммит non-md файлов задачи также в целевом
  C:/OSPanel/domains/viar/viar13 (текущая main, чистая). Основная реализация G:,
  копирование с SHA256, оба commit без push. Правило добавлено в AGENTS.md.
- Первичный regression direct read PASS15assertions: обе роли, read без browse,
  edit запрещён, отсутствующая запись404, отзыв read блокирует ссылку/route.
## ADM-AUD-13G — Полная карточка клиента из заказа, 2026-10-29 — DONE

- Закрыто найденное расхождение навигации: краткое окно сохраняется и содержит
  «Полная карточка клиента» при разрешённом BREAD read. Новый прямой маршрут
  GET /filament/bread/{type}/{record}/view использует существующие BREAD read
  поля, модель и permissions, включая Voyager UserPolicy для собственного профиля.
- Read-доступ не требует browse: нельзя искусственно закрывать оригинальный
  read_users пользователю без browse_users. Read проверяется при mount/hydrate
  и чтении полей; без read ссылка отсутствует/route403; missing record404.
  Без edit_users openEdit запрещён; рабочие права/таблицы не менялись.
- Browser actual ListOrders42→краткая карточка→полная users2 под printing и
  lite_manager PASS; desktop/390px просмотрены. Fixture использует8настроенных
  read-полей; production состав определяется существующей data_rows, не этим fixture.
  Все9business tables после подготовки изолированного снимка unchanged.
- 13tests129assertions PASS: BREAD read/policy/read-only5tests52assertions,
  lifecycle8tests77assertions. Дополнены settings только в3HTTP test fixtures:
  прежний неполный SQLite users ломал sidebar, production schema не менялась.
  Pint2PHP/diff-check PASS. Browser viewport reset, fixture server stopped.
- Evidence: storage/app/audit/client-read-20261029/client-full-*.png и verified.json.
- Скопировано5non-md файлов в C:/OSPanel/domains/viar/viar13, все SHA256 совпали.
  Target commit48d1c00 в текущей main, без push. Основной коммит включает
  документацию и правило двойного commit в AGENTS.md; Markdown не копировался.
- Общий ADM-AUD-13 ещё IN PROGRESS. Следующий конкретный поднабор13G:
  четыре chat окна (клиент/внутренний/SA/художник) × printing/lite_manager,
  visible controls/read-only desktop/mobile на isolated fixture. После него
  обновить оставшуюся матрицу13A–13F; не объявлять все роли/типы принятыми автоматически.
## ADM-AUD-13G — Карточка клиента: переход и данные как в оригинале, 2026-10-29

- IN PROGRESS: предыдущий DONE относится только к доступности полного read,
  полную parity карточки тогда не проверяли. Уточнение по users/read.blade.php:
  отдельные блоки readRows, custom «Язык», header edit/delete/list по permissions.
- SELECT-only общая DB:35 readRows пользователей; источник данных остаётся
  data_rows, статический список полей не вводить. Восстановить прямой переход
  «Просмотр клиента» при read permission, исходный язык/структуру/действия.
- Проверить на isolated fixtures, без реального удаления/изменения клиентов.
  Commit в G: и C:/OSPanel/domains/viar/viar13, export non-md SHA256, без push.
## ADM-AUD-13G — Карточка клиента: переход и данные как в оригинале, 2026-10-29 — DONE для указанного поднабора

- Original users/read.blade.php и SELECT-only data_types/data_rows сверены:
 35configured readRows в исходном порядке + custom «Язык» из user.locale.
  Никаких пользовательских данных из общей БД в fixture не копировали.
- При разрешённом users read «Просмотр клиента» теперь непосредственно ведёт
  на полную страницу; промежуточная modal убрана из этого разрешённого сценария.
  При отсутствии полного доступа прежний краткий просмотр сохранён без расширения прав.
- Все35полей продолжают браться из data_rows; добавлен оригинальный нижний
  «Язык» (settings.locale), сохранены отношения/опции/изображения. Пользовательские
  read-блоки разделены отступами/линиями. Header edit/delete/list возвращены по
  существующим permissions; delete использует существующий BREAD обработчик.
- Browser fixture с35реальными определениями полей, но синтетическим user2:
  rendered36labels EXACT ordered match metadata35+Язык. Printing desktop/390,
  lite_manager direct link/390: dialogs0, нижняя кнопка и язык доступны.
  Это подтверждение структуры/данных средствами Filament, не pixel-копия Voyager.
- 16tests168assertions PASS: BREAD7/89 + lifecycle9/79. Read-only header hides,
  allowed header visible, stale delete after revoke does not delete client;
  direct-link HTML regression и locale regression PASS. Pint/diffcheck PASS.
- Все9fixture business tables unchanged после подготовки schema/metadata;
  реальные клиенты/статусы/файлы не менялись, внешних отправок нет.
  Viewport reset; fixture server stopped. Evidence client-original-*.png и
  client-read-metadata-20261029.json в storage/app/audit (только local ignored).
- 6non-md файлов скопированы в C:/OSPanel/domains/viar/viar13, SHA256 совпали.
  Target commit7ff88a0 в main без push. Основной commit включает эти журналы.
- Общий ADM-AUD-13 открыт. Далее13G: четыре chat окна × printing/lite_manager,
  затем оставшаяся матрица13A–13F по согласованному списку.
## ADM-AUD-13G — Чаты под печатником и менеджером, 2026-10-29

- IN PROGRESS: actual ListOrders42,4окна(client/admin/SA/painter)×2роли,
  desktop/390px. Проверить историю, доступные reply/read/bot controls и закрытие;
  без отправки/прочтения/смены bot mode. Сравнить original role guards.
- Только isolated SQLite fixture/fake HTTP/Mail. Snapshot должен включать также
  SA/admin tables, не ограничиваться прежними9таблицами. Evidence/журналы,
  commit обеих рабочих копий при code changes, export non-md SHA256, no push.
## ADM-AUD-13G — Чаты под печатником и менеджером, 2026-10-29 — DONE для матрицы 4окон×2роли

- Original admin/{client_chat,admin_chat,order_chat,sa_chat}.blade.php:
  дополнительных printing/lite_manager guards нет. Новые окна обеих ролей
  сохраняют общие read_orders/edit_orders gates для просмотра/reply/read/bot.
- Actual ListOrders42: client general+painting903+sketch901 (5messages/3forms,
  оба PNG loaded), painter3typed messages/general composer, internal empty
  и2synthetic author messages, SA PAUSED/history/bot controls/reply/handoff.
  Desktop/mobile390 просмотрены; клиентское окно343px без overflow, остальные
  поля/кнопки доступны. Все8окон открыты/закрыты без send/read/bot commands.
- Для positive internal history отдельно добавлены2synthetic fixture comments,
  затем новый baseline. После browser прохода все190SQLite tables unchanged,
  включая order_admin_comments/sa_messages/sa_conversations/sa_events.
- 88tests844assertions PASS: client/painter/SA/admin chat suites. Рабочая БД,
  реальные клиенты/исполнители/внешние провайдеры не менялись. Viewport reset,
  fixture server stopped. Evidence chats-*.png/chat-roles-verified.json в
  storage/app/audit/client-read-20261029; подготовка и снимки только ignored.
- Два быстрых переключения сразу после закрытия не открыли следующее окно;
  после завершения запроса закрытия оба открылись. Причину этим проходом
  не доказали; не заявлять приёмку быстрого переключения/не менять core вслепую.
- Production code не менялся, копировать/коммитить non-md в C: нечего;
  целевая main чистая. В G: только журналы, commit без push.
- Общий ADM-AUD-13 IN PROGRESS. Следующий блок согласованного списка:
  ADM-AUD-13A — браузерная сверка типов товаров и превью (портрет, конструктор,
  canvas/modular/collage/gallery/manual/oil/gift), с original source и isolated данными.
## ADM-AUD-13A — Типы товаров и превью, 2026-10-29

- IN PROGRESS: full ListOrders isolated fixture42 с11ветками: portrait,
  constructor/canvas/modular/collage, gallery0/1/2, manual, oil, gift.
- Сравнить source orders/items.blade.php и shared all_basket_order_items;
  browser primary/secondary/SVG, поля оформления/сертификата, desktop/mobile.
- Только локальные synthetic media/SQLite, исходные заказы не менять.
  Baseline всех fixture tables+media SHA до/после, tests, журналы, commits
  обеих копий/export non-md при исправлениях; no push.
## ADM-AUD-13A — Типы товаров и превью, 2026-10-29 — DONE для 11веток

- Source original voyager/orders/items.blade.php и shared all_basket_order_items
  сверены. Actual ListOrders42 fixture: portrait/constructor/canvas/modular/
  collage/gallery0/1/2/manual/oil/gift; исходные условия preview сохранены.
- Browser branch matrix:5SVG (constructor,canvas,modular,gallery1,oil),
  secondary portrait/gallery0/gallery2/gift; collage/manual secondary скрыт.
  SVG композиция из2локальных PNG видна в sandbox iframe. Oil promo отдельно
  загружается из oil_header; gift icon и все raster images loaded.
- Подтверждены portrait count/shape/type/originals/reference, modular wall/picture
  dimensions, finishing/canvas/package и gift sender/recipient/date/greeting/code/
  hide_nom/gift marker. Printing11веток/5SVG без «Цена:», подробности/код сохранены.
- Desktop и390px: column235=scrollWidth235, document375=scrollWidth375;
  картинки/данные/ссылки помещаются в горизонтально прокручиваемой таблице.
  Это source+synthetic actual-browser проверка; live парный pixel compare
  и доступность каждого реального production файла этим проходом не заявляются.
- 39tests502assertions PASS (OrderProductPreviewTest/OrderListRolePresentationTest).
  Все190fixture tables unchanged после подготовки11items+oil promo;3media SHA256
  unchanged. Рабочая DB/storage/реальные файлы не менялись. Viewport reset,
  server stopped. Evidence products-*.png,product-visual-evidence.json,
  product-types-expectations.json в storage/app/audit/client-read-20261029.
- Production code без изменений: копировать/коммитить non-md в C: нечего,
  target main чистая. Документы коммитить в G: без push.
- Общий ADM-AUD-13 открыт. Далее ADM-AUD-13B — финальные непустые комбинации
  фильтров/вкладок в браузере. Недоступные реальные media остаются в общей
  приёмке, не считать существование полного набора production файлов доказанным.
## ADM-AUD-13B — Непустые вкладки и комбинации фильтров, 2026-10-29

- IN PROGRESS: isolated full ListOrders fixture с nonempty production/printing/
  sent_today/recent/completed и записями LV/LT, manager/admin, painter/size.
- Source original app/Http/Controllers/OrdersController.php (не одноимённый
  Admin controller): active order_filter заменяет ветку вкладки, период until
  включительно только00:00, phone сначала выбирает клиентов. Сохранить контракт.
- Browser несколько AND фильтров + несовместимая комбинация/reset, вкладки,
  number sort и сброс при переключении; synthetic DB only. Snapshot190tables,
  tests/журналы, commit/export если будут исправления; no push.
## ADM-AUD-13B — Непустые вкладки и комбинации фильтров, 2026-10-29 — DONE для указанной матрицы

- Actual ListOrders synthetic101–106: sent_today=[103], production=[102,101],
  recent=[106,105,103,102,101] (включая completed105), completed=[105,44].
  Source original app/Http/Controllers/OrdersController.php подтверждает predicates/sort.
- СтранаLV+художник3+менеджер11+размер40x60+период29Oct→30Oct=[101].
  until29Oct→0: original upper boundary00:00 сохранена. Смена на completed
  сбрасывает size/from/until и остальные applied/deferred фильтры.
- С completed фильтрprice101→[101]: original order_filter заменяет tab branch.
  Printing tab безфильтров=[101,42]; number cycle desc[101,42]→asc[42,101]
  →default[101,42]. Admin+LT→0; Admin+LV→[106]; Reset→printing[101,42].
- Mobile390: форма и нижний apply доступны, просмотрены screenshots
  filters-{combined-desktop,combined-mobile,footer-mobile}.png в
  storage/app/audit/client-read-20261029. Browser write только фильтров/session.
- 20tests273assertions PASS (OrderListTabsTest/OrderListSummaryTest), включая
  nonempty tab/sort matrix3roles и сброс pending/applied. Все190fixture tables
  unchanged после подготовки6synthetic заказов. Рабочие заказы не менялись,
  provider calls не выполнялись. Viewport reset, fixture server stopped.
- Production code без изменений: target main чистая, non-md для выгрузки и
  commit в C: нет. Журналы в G: commit без push; diff-check PASS.
- Общий ADM-AUD-13 IN PROGRESS. Следующий пункт согласованного списка:
  ADM-AUD-13C/D — создание заказа с несколькими файлами и восстановление
  формы после отказа загрузки; различать full create/save и уже проверенный attach.
## ADM-AUD-13C/D — Полное создание с пакетной загрузкой после отказа, 2026-10-29

- IN PROGRESS: full CreateOrders,2позиции, первая2PNG/JPEG, вторая1PNG.
  Сначала unsupported.exe reject, затем recovery без reload и actual create/save.
- Только изолированные source42/client2, Mail/HTTP fake. Проверить ровно1новый
  заказ, принадлежность3файлов позициям/bytes SHA, прежние orders/users/другие
  таблицы и ранее существующие media. Не подменять full create одним attach.
- Добавлен regression actual CreateOrders: invalid validation no writes,
  same component recovery multi-position/multi-source. Результаты/журналы,
  копирование non-md SHA256 и оба commit без push.
## ADM-AUD-13C/D — Полное создание с пакетной загрузкой после отказа, 2026-10-29 — DONE для PNG/JPEG сценария

- Actual CreateOrders browser: unsupported.exe отклонён FilePond; заполненные
  данные сохранены, после удаления invalid файла без reload загрузились2PNG/JPEG
  первой позиции и1PNG второй. Первую временную вкладку потеряли между turns,
  повторили весь reject→recovery→create в новой форме, не выдавая старый draft за save.
- Создан только isolated order107 для fixture client2:2позиции25+35=60EUR,
  watching/not_payed. JSON orig_images:2файла первой/1второй, все3SHA256 совпали
  с input. Повторное открытие EditOrders107: все фото loaded.
- Verifier подтверждает190таблиц: все прежние записи неизменны, добавлен ровно
  один orders row; users прежние.7старых media SHA прежние, ровно3новых файла.
  Legacy имя N107-2 содержит count товара, не номер позиции; соответствие позиции
  проверялось JSON orig_images/размером/хешами, не ошибочной трактовкой filename.
- Добавлен actual CreateOrders regression: invalid upload no orders/users/media,
  тот же Livewire component recovery с2позициями/3файлами, byte SHA и isolation.
  29tests344assertions PASS (creation service/full page + create form).
- Рабочие заказы/клиенты не затронуты, Mail/HTTP fake; fixture server stopped.
  Evidence create-rejected-exe.png/create-recovery-saved-order.png и
  create-recovery-verified.json в storage/app/audit/client-read-20261029.
- Изменён только testPHP, скопирован без.md в C:/OSPanel/domains/viar/viar13,
  SHA256 совпал. Target main commit9d5f2b8 без push; production код прежний.
- Общий ADM-AUD-13 открыт. Этот gate не закрывает OS drag всех форматов,
  HEIC decoding или полный100MiB Filament save под Apache. Далее ADM-AUD-13E:
  финальный browser generate/download документов на isolated fixture, внешние
  email/payment/Venipak calls не выполнять.
## ADM-AUD-13E — Browser генерация и скачивание настоящего PDF, 2026-10-29

- IN PROGRESS: отдельная копия SQLite/disks из fixture, настоящий InvoiceService/
  DynamicPDFController, без partial mock; browser confirm generate и PDF link.
- Source original invoice logic/действия остаются эталоном. Проверить новый PDF,
  download SHA, текст/рендер, допустимые has_pdf/pdf_link/updated_at изменения
  только fixture42; остальные записи неизменны. Без approve/email/реальных интеграций.
- После evidence tests/журналы, commits/export при изменениях кода; no push.
## ADM-AUD-13E — Browser генерация и скачивание настоящего PDF, 2026-10-29 — DONE для одностраничного счёта

- Actual ListOrders fixture42 generateInvoice→confirm→настоящий InvoiceService/
  DynamicPDFController/Dompdf, без mock. ИсходногоPDFне было, появился42.pdf931192bytes.
  Ссылка «Счет» скачана browser downloadMedia; generated/download SHA256 совпали.
- PDF1page просмотрена через pdftoppm: номерVR001, synthetic seller/customer,
  товарUAT filter canvas, немецкая локаль из users.pdf_locale, итог94.00EUR.
  pypdf text/page assertions PASS. Текст сохранён invoice-text.txt.
- Snapshot190tables: только orders42.has_pdf/pdf_link/updated_at изменились;
  pdf_approved0, остальные order/user/chat/SA/role данные прежние. Approve/email,
  payment/Venipak не вызывались; Mail/HTTP fake. Fixture server stopped.
- Две ошибки подготовки fixture: отсутствовали CSS для filemtime и order_strings1.
  После первой generator failure состояние не сохранилось; добавлены assets и
  прежние synthetic invoice texts из firm-pdf fixture, повторный generate успешен.
  Production код не менялся; ошибки стенда не выдаются за дефекты приложения.
- 25tests357assertions PASS (InvoiceDocumentTest/OrderInvoiceServiceTest).
  Evidence storage/app/audit/invoice-browser-20261029/{verified.json,invoice-page1.png,
  invoice-text.txt,public/storage/pdf/42.pdf}. Download local Downloads/42.pdf.
- Только журналы: export non-md/commit C: не требуется, target main чистая;
  основной commit без push. Diff-check PASS.
- Общий ADM-AUD-13E/ADM13 IN PROGRESS: browser длинный/подарочный документ
  этим сценарием не закрыт (их controller/render gates записаны ранее).
  Следующий точный шаг — actual browser генерация длинного счёта с60позициями,
  скачать/проверить обе страницы без реальных отправок, затем gift documents.
## ADM-AUD-13E — Длинный счёт через Chrome, 2026-10-29

- IN PROGRESS: по просьбе пользователя Chrome extension вместо IAB. Actual
  ListOrders fixture42 generate→download60items, real PDF generator без mock.
- Только isolated SQLite/локальный public,60synthetic LONG-ITEM, subtotal726+
  delivery4=730EUR. Проверить все60позиций/страницы/итог и snapshot190tables.
  Без approve/email/providers. Журналы и commit без push; код без необходимости не менять.
## ADM-AUD-13E — Длинный счёт через Chrome, 2026-10-29 — DONE для60позиций

- По просьбе пользователя Chrome extension: actual ListOrders fixture42,
  Generate→confirm→real InvoiceService/Dompdf→download «Счет». Никаких
  invoice mocks/реальных заказов. PDF935967bytes, Chrome Downloads/42 (1).pdf
  SHA25611372aeeb81492d75a5358729b2251533b63a2f5a13af2b06b6d0679a0e85864
  совпал с generated файлом.
- pypdf:2pages, LONG-ITEM001..060 каждый ровно1раз, итог730.00EUR=726+4delivery.
  Обе страницы отрендерены/просмотрены: page1items1–34,page2items35–60,
  итог/НДС/сумма прописью вмещаются, строки не теряются на разрыве.
- 1targeted long invoice test68assertions PASS; предыдущий invoice suite25/357
  уже прошёл без production изменений. Snapshot190tables: только order42
  has_pdf/pdf_link/updated_at изменились, pdf_approved0/прочие данные прежние.
  Approve/email/payment/Venipak не запускались, HTTP/Mail fake; server stopped.
- Evidence storage/app/audit/invoice-browser-20261029/long-page-{1,2}.png,
  long-text.txt,chrome-long-generated.png,verified.json. Текущий42.pdf overwritten
  только в ignored fixture public; production storage не затронут.
- Код не менялся: только4журнала, non-md для копирования/commit в C: нет.
  Основной commit без push. Далее ADM-AUD-13E — подарочные документы через
  Chrome (реальный download/render, без отправки клиентам). ОбщийADM13 открыт.
## ADM-AUD-13C/D — Заказ60позиций с фотографиями, 2026-10-29

- IN PROGRESS: уточнение пользователя — сам заказ с фото, счёт без фото.
  Создать отдельный synthetic order штатным AdminOrderCreationService с60позициями,
  каждой отдельный локальный PNG UploadedFile. Не выдавать это за60browser uploads.
- Проверить60разных сохранённых путей/SHA и browser EditOrders Chrome previews,
  неизменность прежних190tables; Mail/HTTP fake. Только isolated fixture.
  Код не менять без дефекта, журналы/коммит без push.
## ADM-AUD-13C/D — Заказ с фотографиями: проверка 2026-10-29

- Проверенный сценарий DONE: отдельный synthetic order №108 создан штатным
  AdminOrderCreationService: 60 позиций PHOTO-ITEM-001..060, у каждой один PNG.
  Это service-level UploadedFile, а не 60 загрузок через browser file chooser.
- Одна синтетическая синяя картинка сохранена в 60 отдельных файлах; уникальность
  путей и SHA256 каждого файла проверены. В Chrome EditOrders показаны все
  60 позиций; загружены все 60 уникальных URL, ошибок изображений нет.
  120 img включают дубликаты галереи/позиций; 4 offscreen lazy-дубликата pending.
- После браузера повторно проверены 190 таблиц: все существовавшие строки прежние,
  добавлен только order108. Счёт без фотографий оставлен без изменений.
  SQLite/storage изолированы; Mail/HTTP fake, никаких отправок клиентам.
- Evidence (ignored): storage/app/audit/invoice-browser-20261029/
  sixty-photos-created.json, sixty-photos-before.json,
  chrome-sixty-photos-gallery.png, chrome-sixty-photos-last.png;
  storage/app/audit/sixty-photos-final-check.php: PASS. Сервер остановлен.
- Runtime-код не менялся; текущая сдача — документация в основном репозитории.
  Non-md файлов для копирования/коммита в C: нет; target working tree чистое.
  ADM-AUD-13 в целом остаётся открытым. Следующий шаг: ADM-AUD-13E —
  браузерная приёмка подарочных документов без отправок клиентам.

## ADM-AUD-13D — Ручное создание 10 товаров с фото, 2026-10-29

- IN PROGRESS: создать отдельный заказ исключительно через Chrome CreateOrders: 10 позиций, по одному фото через file chooser; проверить сохранение, сумму и привязку файлов. Только изолированная SQLite, уведомления отключены. Зафиксировать удобство и найденные проблемы.


## ADM-AUD-13D — Ручное создание 10 товаров с фото, 2026-10-29: результат

- DONE для сценария: через Chrome CreateOrders выбран fixture2@example.test,
  кнопкой «Добавить позицию» последовательно добавлены 10 товаров, названия
  «Ручной товар 01»..«Ручной товар 10», цены 10..19 EUR, размер 40x60.
  В каждой карточке фото отдельно выбрано через видимую ссылку «выберите» и
  штатный file chooser; сервис напрямую/Livewire evaluate не использовались.
- Перед нажатием «Добавить» все 10 загрузок завершились; итог формы 145 EUR.
  UI подтвердил «Заказ №109 создан. Внешние уведомления отключены для UAT».
  Повторно открыт EditOrders109: все 10 названий, 10 уникальных фото загружены,
  ошибок изображений нет (20 img = галерея + отдельные позиции).
- Фото — один синтетический PNG, выбранный отдельно десять раз. Проверены
  10 отдельных путей и SHA256 каждого файла, сумма orders.price=145.
  manual-ten-check.py PASS: существовавшие строки всех190tables не изменились.
  SQLite/storage изолированы, уведомления отключены, HTTP fake; реальных
  клиентов и production DB/storage не затрагивали. Счёт не создавали.
- UX: длинные развёрнутые карточки требуют прокрутки и повторного ввода размера.
  Локальный однопоточный PHP server отвечал с задержками и очередью Livewire:
  это наблюдение окружения, не доказательство задержек целевого сервера.
  Прямой клик инструмента по file input не сработал; видимая ссылка «выберите»
  успешно открыла все10 chooser, дефект приложения не подтверждён.
- TODO ADM-AUD-13D — Удобство повторного ввода: сравнить с оригиналом возможность
  дублирования позиции/общих параметров и компактного списка, согласовать
  улучшение без изменения расчётов и бизнес-логики. В этой проверке UI не менялся.
- Evidence ignored: storage/app/audit/invoice-browser-20261029/
  manual-ten-before.sqlite, manual-ten-verified.json,
  chrome-manual-ten-before-save.png, chrome-manual-ten-saved.png, requests.jsonl;
  verifier storage/app/audit/manual-ten-check.py. Сервер остановлен после проверки.
- Runtime изменений нет: только4журнала в source commit без push; non-md файлов
  для выгрузки/коммита в C: нет. ADM-AUD-13 целиком остаётся открытым.

## ADM-AUD-13E — Подарочные документы через Chrome, 2026-10-29
- IN PROGRESS: isolated order110 (2 offline50 +1 online25), browser payment status paid → real coupons/PDF + intercepted mail → download/render. Никаких внешних отправок. По просьбе пользователя форму товаров оставляем без изменений; UX улучшения отложены.


## ADM-AUD-13E — Подарочные документы через Chrome, 2026-10-29: результат

- DONE для browser-generation/download subset: isolated SQLite copy,
  synthetic order110:2offline50+1online25. Через реальный ListOrders inline select
  «Статус оплаты заказа №110» выбран payed; штатный OrderPaymentService создал
  3купона/3настоящих PDF. В браузере после reload статус payed.
- Coupon values50/50/25, offline1/1/0, три разных кода. pypdf: каждый1page,
  код/номинал совпадают с coupon. Render Poppler50/25 просмотрен: оформление,
  поворот, номинал и код читаются. Для второго50 отдельная visual не заявляется.
- Mail fake перехватил GiftCard и Payment_successful; gift render содержит3ссылки
  только order110. Chrome открыл локальный render реального письма; по всем3
  ссылкам скачаны Downloads/110_2_{1,2,3}.pdf, SHA256 каждого совпал с generated.
  download-event для target=_blank иногда timeout, но обычные клики скачали
  файлы; проверены реальные Downloads. ПервыйPDF также downloadMedia.
- Шаблон mail.giftcard и фонgiftcard.png SHA256 идентичны originalLaravel6;
  метод getGiftCardHtml от private до конца файла совпадает после normalizeLF.
  Это проверка business/render/download, не приёмка Outlook/Gmail.
- В локальном browser preview заменены только src origin на localhost; отсутствуют
  mail декоративные/промо картинки, поэтому полную визуальную приёмку письма
  не заявляем. Промо2синтетические строки+RUtranslations для original template.
- Первый export hook упал на пустых promo данных fixture после успешного action.
  Данные fixture дополнены, SQLite восстановлена из before и browser action
  повторён: в финальной fixture ровно3купона. Invoice-only assertSentCount
  в ignored router убран: gift workflow перехватывает2типа письма, это не
  production exception. Runtime-код не менялся.
- check-gift-browser.py PASS:190tables сверены с snapshot; разрешённые изменения
  order110 payment_status/use_bonus/updated_at, user2 bonuses/updated_at и3новых
  coupons. Прочие строки/поля прежние. Внешние уведомления выключены, Mail fake,
  HTTPpreventStrayRequests, Location fake. Производственные данные не затронуты.
- GiftCardDocumentTest+OrderPaymentServiceTest:32tests352assertions PASS,
  PHP8.4.1,77.97s. Код после тестов не менялся; diffcheck PASS.
- Evidence ignored: storage/app/audit/gift-browser-20261029/verified.json,
  before.sqlite, gift-mail-raw.html, gift-mail-preview.html, gift-{50,25}.png,
  chrome-paid.png, chrome-gift-mail.png, public/storage/pdf-gift-card/110_2_*.pdf.
  Сервер остановлен. Только4журнала source commit без push; non-md для C:нет,
  target working tree чистое. Общий ADM13/13E открыт.
- Уточнение пользователя: ADM-AUD-13D удобство формы отложено; текущую форму
  товаров оставляем, ручное создание10сфото работает.
- Следующая задача: ADM-AUD-13F — оставшиеся браузерные сценарии назначения
  исполнителя и смены статусов на тестовых данных, с original business logic.
