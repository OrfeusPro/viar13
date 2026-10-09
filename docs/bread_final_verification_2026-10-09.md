# Финальная проверка BREAD — 2026-10-09

## Область и результат

Проверены перечисленные пользователем этапы: актуальный runtime, автоматические сценарии и ранее зафиксированные контракты оригинального Voyager/VE. Это проверка кода и локального выполнения на SQLite, а не подтверждение всех сценариев на production MySQL или визуального совпадения каждого экрана.

`DONE` ниже означает выполнение описанного подэтапа в его подтверждённых границах. `PARTIAL` означает, что обобщающую задачу нельзя закрыть как полный перенос оригинала. Исторические TODO в пошаговом журнале следует читать вместе с этой итоговой таблицей.

## Найденная и исправленная ошибка

TAG × CLASSES: BreadTagCreationService запрещал любой непустой controller. После появления выбора стандартного VoyagerBaseController это отключало создание связанных тегов даже для стандартного BREAD. Теперь null и точный стандартный класс (в том числе с ведущим обратным слешем) разрешены; нестандартные контроллеры и специальные разделы по-прежнему не подменяются общим store.

Расширен существующий интеграционный тест: создание тега с двумя вариантами имени BaseController, отсутствие pivot до сохранения родителя, запрет stale modal после смены controller на custom, создание через модальное окно родителя с явным стандартным controller. Проверки прав и stale taggable сохранены.

## Матрица задач

| Задача | Статус | Подтверждённый результат / граница |
| --- | --- | --- |
| ADM-AUD-06D-CHILD | DONE | hasOne/hasMany показывают дочерние записи с учётом scopes; назначение детей отсутствует и в оригинальном компоненте. |
| ADM-AUD-06D-TAG | DONE | Немедленное создание target, pivot при Save родителя, проверки прав/required/defaults/stale metadata. Только стандартный target BREAD; custom store требует отдельного адаптера. Найденный конфликт BaseController исправлен. |
| ADM-AUD-06G-FIELDS-A | DONE | radio, multiple select, time, Markdown, hidden и сохранение/переводы в общем runtime; полный набор options выделен отдельно. |
| ADM-AUD-06G-ADVJSON | DONE | Rows/fields envelope, добавление/удаление/порядок, старые свойства, переводы, malformed readonly; поддерживаемые текстовые ячейки/ключи и лимиты. |
| ADM-AUD-06G-GROUP | DONE | text/number/textarea groups, defaults, старые definitions/root properties, переводы; изменение definitions не мигрирует старые записи автоматически. |
| ADM-AUD-06G-ADVIMAGE | DONE | Spatie single collection, компактный preview, TITLE/ALT, Save/replace/delete/guards; синхронные отказы проверяются также этапами recovery. |
| ADM-AUD-06G-TREE | DONE | Дерево BelongsTo с owner key id, order/parent_id/scopes/List/where; другие relation/owner-key contracts не поддержаны. |
| ADM-AUD-06G-LAYOUT | DONE | Каталог Field/Block/Form, порядок/повторы/переводы и сохранение старого JSON; содержимое блоков/форм редактируется отдельно. |
| ADM-AUD-06G-COORDINATES-A | DONE | Ручные lat/lng, Point parsing/validation и SQL expressions. SQLite WKT fixtures; карты/Places/marker и реальный MySQL/SRID не подтверждены. |
| ADM-AUD-06G-CODE | DONE | Native CodeEditor, поддерживаемые языки, raw text/JSON cast/переводы; точные Ace themes не воспроизводятся. |
| ADM-AUD-06G-SELECT-REL | DONE | BelongsToMany/HasMany как источник options/List; JSON list/map, editablePivotFields. По оригинальному contract это запись JSON-колонки, не отдельный pivot sync. |
| ADM-AUD-06G-OPTIONS-A | DONE | Scalar placeholder/default/rows/min/max/step, базовые правила и сообщения; остальные типы проверяются отдельными этапами. |
| ADM-AUD-06G-OPTIONS-LAYOUT | DONE | display.width, desktop grid и mobile stacking; Filament breakpoint отличается от Bootstrap. |
| ADM-AUD-06G-OPTIONS-SELECT | DONE | Scoped BelongsTo/List/where/static options/scalar default; callable Class@method defaults не перенесены. |
| ADM-AUD-06G-OPTIONS-SLUG | DONE | Оригинальная карта символов, tracking/force/manual value и language isolation для editable text; другие источники/цепочки не поддержаны. |
| ADM-AUD-06G-OPTIONS-VALIDATION | DONE | Scalar rules, ограниченные unique/exists, regex и перечисленные dependent rules; произвольные application/custom rules не выполняются. |
| ADM-AUD-06G-OPTIONS-TYPES | PARTIAL | Checkbox/multicheck/editor/image подпункты готовы. Time placeholder/default и date/timestamp контракт закрыты продолжением OPTIONS-TEMPORAL. Произвольные остальные type-specific options не объявляются поддержанными. |
| ADM-AUD-06G-OPTIONS-CHECKBOX | DONE | checked при null, сохранение false, on/off captions и escaping; native Toggle вместо legacy CSS. |
| ADM-AUD-06G-OPTIONS-MULTICHECK | DONE | checked fallback, JSON list/map values, captions, пустой выбор, старый raw JSON, переводы. |
| ADM-AUD-06G-OPTIONS-EDITORS-MEDIA | PARTIAL | Native RichEditor safe toolbar/height/placeholder и image transforms готовы; полный TinyMCE plugin/dialog contract отсутствует. |
| ADM-AUD-06G-OPTIONS-IMAGE | DONE | Resize/quality/upsize/thumbnails/preserve-name, GIF original/static, старые path strings не преобразуются. |
| ADM-AUD-06G-OPTIONS-IMAGE-WEBP | DONE | Companion для новых uploads, прозрачность/коллизии/нефатальные отказы; старые изображения не конвертируются. |
| ADM-AUD-06G-OPTIONS-IMAGE-EXIF | DONE | Реальные JPEG EXIF fixtures 1..8, pixels/dimensions/thumbnails/WebP; orientation 7 исправлен ранее. Production codec corpus не проверялся. |
| ADM-AUD-06G-MEDIA-RECOVERY | PARTIAL | A–D закрывают синхронные сбои; crash recovery, deferred conversions и concurrent name reservation не гарантируются. Durable инфраструктура отменена пользователем. |
| ADM-AUD-06G-MEDIA-RECOVERY-A | DONE | Новые generic upload paths очищаются после validation/SQL failure; старые файлы/пути сохраняются. |
| ADM-AUD-06G-MEDIA-RECOVERY-B | DONE | adv_image Save: rollback новых media/конверсий, старые файлы до commit, source для retry. |
| ADM-AUD-06G-MEDIA-RECOVERY-C | DONE | Immediate batch upload/replace транзакционны; order/properties/selection/collection limits, rollback и retry. |
| ADM-AUD-06G-MEDIA-RECOVERY-D | DONE | Single/bulk delete: ownership, locks, откат SQL и отложенное удаление файлов после commit. |
| ADM-AUD-06G-MEDIA-RECOVERY-E | CANCELLED | Outbox/migration/command/scheduler удалены по указанию пользователя. Не требуется применять миграцию; актуальный код использует native Spatie cleanup. |
| ADM-AUD-06G-CLASSES | PARTIAL | Известные адаптеры и selection готовы; произвольные controllers/policies/model-less BREAD не перенесены полностью. |
| ADM-AUD-06G-CLASSES-A | DONE | UserPolicy: свой профиль read/edit, остальные действия по section permissions; self-role guard. |
| ADM-AUD-06G-CLASSES-B | DONE | AdminLocaleController: пустой каталог языка при create, сохранение existing files и rollback нового пустого каталога; Translation Manager отдельно. |
| ADM-AUD-06G-CLASSES-C | DONE | Controller selection существующего BREAD, нормализация и table-specific allowlist; неизвестные значения сохранены readonly. |
| ADM-AUD-06G-CLASSES-D | DONE | BasePolicy/null и UserPolicy для users; запрет cross-table/unknown policy изменений, сохранение чужих прав. |
| ADM-AUD-06G-CLASSES-E | DONE | Те же controller/policy choices при создании BREAD, validation/normalization/атомарный rollback, без автоматического grant ролям. |
| ADM-AUD-06G-CLASSES-F | DONE (audit) | В оригинальном BREAD нет генерации классов. ModelMake относится к отдельному database tool; отсутствие генератора здесь не является потерей BREAD-функции. |

## Проверки

- До исправления TAG: полный VoyagerBreadTest — 122 tests /2058 assertions PASS, 98.965s, 142MB.
- После исправления TAG: targeted validated_many_to_many_relation_syncs_pivot — 1 test /55 assertions PASS, 2.065s, 82MB.
- PHP lint: 40 файлов общего BREAD runtime, сервисов, страниц и теста — PASS.
- Поиск app/tests/database/routes: удалённых BreadMediaCleanup/table/command/migration dependencies нет.
- Полный повтор после исправления: 122 tests /2064 assertions PASS, 112.002s, 142MB; PHP CLI memory_limit=512M. Все 36 идентификаторов сведены в матрицу; финальная проверка завершена, полная миграция общих PARTIAL задач не заявляется.
- phpunit.xml: SQLite :memory:, mail array, queue sync; uploads Storage::fake(public), language-directory tests используют временный lang root. Рабочая БД, реальные картинки и исходный проект не изменялись.

## Оставшиеся задачи по этому списку

1. OPTIONS-TYPES: time placeholder/default и date/timestamp contract закрыты; новые неподдерживаемые options сверять с фактическими production metadata перед расширением.
2. EDITORS-MEDIA: решить, какие TinyMCE plugins/dialogs нужны сверх native RichEditor; полного соответствия сейчас нет.
3. CLASSES: текущий read-only snapshot не содержит неподдерживаемых generic controller/policy. После production import повторить инвентаризацию и переносить обнаруженные custom store/access сценарии конкретными адаптерами. Два исторических missing-model metadata entries canvas/Canva и a_collag_faq/ACollagFaq требуют отдельной сверки физического schema/source; metadata не переписывались.
4. COORDINATES: Maps/Places/marker/options и MySQL/SRID engine evidence — отдельные задачи, не часть завершённого ручного подэтапа A.
5. SELECT/TREE: callable defaults и иные relationship contracts требуют отдельного подтверждения. Проверить, используются ли они в production metadata, до расширения.
6. Production codecs/disks/model hooks и deferred conversions — проверка после подключения production snapshot; реальных записей в рамках этого аудита нет.

MEDIA-RECOVERY-E не возвращать в backlog без нового указания пользователя. Существующие ограничения аварий процесса/native cleanup зафиксированы, а не объявлены реализованными.

## Продолжение реализации после аудита

2026-10-09: добавлен BreadTemporal — time placeholder/default validation, date Y-m-d/label placeholder, timestamp display m/d/Y g:i A/storage Y-m-d H:i:s, app timezone, guards и shared-language state. Native RichEditor дополнен HTML-source modal с подсветкой и переносом длинных строк, public image attachments table/month с MIME/10MiB limits, text color, code/image/forecolor toolbar mappings. Source changes — draft until main Save; translations isolated. Устранён конфликт TAG с явным BaseController (описан выше), нет второй кнопки table и устаревшего readonly-текста в settings.

Проверки: full SQLite124 tests /2110 assertions PASS (90.510s,144MB); final targeted3 /67 PASS после UI уточнений; lint/diff PASS. Chrome gallery-page/1/edit: source modal открывается, HTML highlighting и toolbar подтверждены; только Cancel, без ввода/Save. Read-only metadata audit не обнаружил координат, callable defaults, tinymceOptions или неподдерживаемых generic controllers/policies; maps key отсутствует. Исходные записи/файлы не изменены.

Эта партия закрывает temporal gap и полезные default editor commands. Не объявляет полный TinyMCE/TipTap HTML contract, карты или production spatial/media engine проверки выполненными. Следующий необходимый source review: сохранность legacy HTML attributes/styles/embedded elements и дополнительные default TinyMCE commands (background/indent/styles). Исходная матрица выше — результат финального аудита до этой партии; уточнения этой секции имеют приоритет по новым temporal/editor возможностям.

### ADM-AUD-06G-OPTIONS-EDITOR-C — проверка защиты вложений и общих полей
HTML source Apply сохраняет исходную разметку в CodeEditor без передачи в TipTap. Если в текущем документе есть ещё временная inline-картинка, Apply отклоняется с ошибкой поля HTML: сначала нужно сохранить основную форму. Модальное окно не переносит файлы на public disk и не записывает БД. Общие непереводимые rich-поля используют общий режим HTML во всех языках; переводимые поля сохраняют отдельный режим по locale. Режим сбрасывается при открытии другой записи/создания.
Evidence: targeted5 tests /104 assertions PASS (3.729s,92MB), включая native attachment action со Storage::fake, отсутствие permanent файлов до Save, сохранность legacy style/class/form/input/iframe и shared draft после смены языка. Full126 /2138 PASS до уточнения shared mode; полный повтор актуальной версии выполняется. PHP lint6 изменённых файлов и git diff --check PASS. Не менялись рабочая БД, изображения или исходный проект; миграций нет.
## ADM-AUD-06G-OPTIONS-EDITOR-C — DONE, 2026-10-09
Итог актуальной версии: полный VoyagerBreadTest — 126 tests /2147 assertions PASS (85.141s,144MB; PHP8.3 memory_limit=512M). Targeted temporal/source/translation/attachment/shared-mode — 5 tests /104 assertions PASS; PHP lint6 файлов и git diff --check PASS. Raw HTML mode сохраняет исходные styles/classes/embedded elements, общий режим непереводимых полей переживает смену языка, переводимые режимы изолированы; pending inline attachments блокируют переход до общего Save. Предыдущая запись EDITOR-B о setContent описывает промежуточную реализацию: актуальный Apply использует rawState + CodeEditor.
Temporal и native source/image/text-color/table gaps закрыты. Рабочая БД, реальные картинки, старый проект не изменены, миграций и внешних запросов с записью нет; коммит не создавался.
Оставшиеся границы: native toolbar не воспроизводит весь TinyMCE plugin/visual styles/background/indent UI; произвольные JS callbacks/application validators/controller dispatch не выполняются. Текущие metadata не требуют дополнительных custom/coordinates/callable adapters, но production snapshot нужен для повторного read-only audit (включая missing-model entries), затем конкретные адаптеры по обнаруженным контрактам. Maps/Places и реальный MySQL/SRID/media codecs остаются непроверенными. MEDIA-RECOVERY-E CANCELLED, аварийная outbox-инфраструктура не возвращалась; A-D сохранены. Общие PARTIAL этапы не объявлять полностью DONE. Следующее точное действие: после предоставления production snapshot выполнить read-only bread:audit и сверку data_types/data_rows/model/schema, затем закрывать обнаруженные реальные несовместимости на изолированных fixtures. ADM-AUD-07A database-tools — отдельная TODO, текущей доработкой BREAD не закрывается.