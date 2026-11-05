# ADM-AUD-11-DB — итоговая проверка, 2026-10-12

Общий статус: **PARTIAL**. Основные сценарии перенесены и проверены изолированно; рабочие POST/DDL и полная совместимость всех server/type вариантов не подтверждены.

## Источники и границы

- Original: `C:/OSPanel/domains/asoft/viar/vendor/tcg/voyager/src/Http/Controllers/VoyagerDatabaseController.php`, `Database/DatabaseUpdater.php`, `Commands/MakeModelCommand.php`, `resources/views/tools/database/index.blade.php` и Vue editor/components.
- Chrome: original `/admin/database`, `/admin/database/create`, `/admin/database/a_collage_advantage_screen/edit`; new `/filament/database-tables`, `/create`, `/edit?table=a_collage_advantage_screen`.
- Запись проверяется только SQLite `:memory:` и временным filesystem модели. MySQL compiler/metadata tests используют mocks; рабочую БД, изображения и миграции этот аудит не меняет.

## Матрица

| Возможность | Результат |
| --- | --- |
| Каталог, алфавитный порядок, ссылки структуры | Совпадает по назначению; первые a_collage_* и группы действий подтверждены Chrome |
| Открыть/добавить/редактировать/удалить BREAD | Реализовано; permission, stale metadata, transactional cleanup и сохранность записей проверены fixtures |
| Просмотр колонок, индексов, FK | Реализовано; вместо original modal используется отдельная секция |
| Создание таблицы, id, колонки, timestamps, soft deletes | Реализовано; duplicate/limits/validation/permissions проверены |
| Типы | 35 разрешённых original MySQL типов + STRING/BOOLEAN aliases; ENUM/SET отключены как original |
| Необязательная модель | Реализовано только при create, как original; collision/no overwrite/post-DDL failure проверены |
| Миграция | В original UI скрыта/disabled; новая генерация не добавлена |
| Rename table/column, NULL/default/type/length/unsigned, одиночные indexes | Реализовано в указанных service границах; records/defaults/index preservation проверены |
| PRIMARY/AUTO_INCREMENT | MySQL compiler проверяет единый ALTER для перехода; SQLite primary changes ограничены |
| EXTRA, ON UPDATE, ZEROFILL, существующий default expression | Сохраняются для совместимых изменений; несовместимые/неизвестные свойства отклоняются до DDL |
| Физическое удаление | Требует сначала удаления BREAD, как original UI; exact name, permission/schema/FK recheck проверены |
| Порядок существующих колонок, BREAD sync при schema update | Не реализованы в действующем original (unfinished/TODO); не считаются забытым рабочим функционалом |
| Composite/FK/generated editor | Полного редактора нет; существующие свойства сохраняются, опасные изменения ограничены. Original предупреждает о composite, отдельный FK UI не найден |

## Найдено и исправлено в итоговом ходе

1. На мобильном экране shared table min-width1100 растягивал responsive карточки. При ширине390px после исправления container/table width=295px, scrollWidth=295px, page width375px — внутреннего горизонтального переполнения формы нет. Desktop 7 rows/no page overflow подтверждены.
2. Disabled default/index inputs могли не dehydrating existing TEXT/BLOB state. Теперь значения передаются; isolated no-op existing TEXT default+index даёт пустой SQL, rename сохраняет записи/index/default новых записей.
3. Original catalog script блокирует удаление таблицы с BREAD через remove-bread-warning. Добавлен service guard в dropPreview, повторно вызываемый при drop; fixture подтверждает блокировку BREAD, появившегося после preview. Прежняя запись, что controller destroy не очищает BREAD, остаётся верна, но UI prerequisite теперь воспроизведён.

## Проверки

- Final targeted regression: **29 tests /437 assertions PASS**, 7.968s,126MB.
- PHP lint DatabaseCreate/DatabaseTableEditingService и `git diff --check` PASS; ранее в этом цикле lint остальных database services PASS.
- Chrome read-only GET обеих админок; model checkbox/create helpers/type options/edit seven rows/Not Null/unsigned/auto/default0/catalog actions проверены. Working Preview/Save/Delete/helper actions не нажимались.
- Screenshots: `storage/app/audit/adm-aud-11-db-final-editor.jpg`, `adm-aud-11-db-final-catalog.jpg`, `adm-aud-11-db-final-mobile.jpg`.
- Temporary viewport390x844 сброшен; вкладки возвращены на исходные editors.
- При первом запуске нового drop fixture не был задан обязательный model_name; fixture исправлен. Existing permissions test обновлён: previewDrop pages с BREAD теперь ожидает отказ, revoked permission всё равно403. Финальный запуск чистый.

## Что остаётся

1. Рабочие browser POST/DDL не проверены; прежний ENV-ADM-LIVEWIRE-KEY остаётся TODO. Изолированные тесты не доказывают исправление APP_KEY в рабочем runtime.
2. Real MySQL/MariaDB server/version/constraint cases, legacy column rename старых версий и вся последовательность DDL не подтверждены. Атомарность MySQL сценария с отдельным rename не обещается.
3. Composite/FK/generated changes, неизвестный EXTRA, снятие/изменение типа ZEROFILL и произвольные expression/type transitions остаются явно ограниченными.
4. Original show умеет добавлять `additional_attributes` модели к описанию. Новый physical inspector этого не делает; свойства не найдены в текущих App\Models. Это условная совместимость, если такие модели появятся, а не подтверждённый пропуск активной таблицы.
5. Отдельный ADM-AUD-03 follow-up: плавающая SA кнопка перекрывает breadcrumb при390px. Форму БД этот audit исправил; глобальный SA layout не менялся.

Следующий шаг: исправить мобильное размещение SA кнопки в рамках ADM-AUD-03; runtime POST проверять отдельно с учётом запрета реальных writes. Общий ADM-AUD-11-DB не отмечать полностью DONE.

### Taggable: PASS в Chrome
BREAD205 belongsToMany taggable=on. Создана категория3 из поля, подтверждена запись до Save родителя, отсутствие pivot после reload без Save, наличие связи после Save/reload, отображение browse/read. Пустое название блокируется required, лишних категорий нет. Screenshot: storage/app/audit/adm-browser-taggable-read.jpg. 4 targeted tests / 95 assertions PASS. Первый запрос встретил intermittent MissingAppKeyException; после reload повтор успешен, ENV issue остаётся открытым. Live hasOne/hasMany ещё TODO.

### HasOne/hasMany: PASS в Chrome
BREAD206 category→items category_id=id. Две дочерние записи: hasOne первая, hasMany обе. Browse/read/edit, EN/RU, сохранение родителя, отсутствие изменения дочерних ключей, пустая категория и create без ID проверены. Tests2/26PASS. Скриншот storage/app/audit/adm-browser-child-relations.jpg. Read-only поведение соответствует original relationship.blade.php; точный визуальный дубль таблицы не заявляется. Следующее: custom pivot keys и advanced schema cases.

### Custom pivot key mapping: implementation + isolated PASS
foreign_pivot_key/related_pivot_key/parent_key/key поддерживаются в validation, constructor, select/load/sync/browse/read/taggable. String keys001/blue/03 не преобразуются в int. Parent скрытый browse column подбирается для join; NULL parent не связывается с unassigned pivot. 6 targeted tests136assertionsPASS, lintPASS. Chrome явные стандартные ключи BREAD205 сохранены, chips1/2/3 сохранены (adm-browser-explicit-pivot-keys.jpg). Нестандартные колонки/string keys проверены SQLite isolated, live DDL не выполнялся. Defaults conventional table singular_id/id сохранены; schema migrations не добавлены.

### Composite column pure rename: compiler PASS
Современные MySQL8.0.3+/MariaDB10.5.2+: native rename сохраняет составной индекс без drop/add index. Изменение свойств/index/drop одновременно с rename отклоняется, legacy версии также. Source DatabaseUpdater original rename diff сверён. Новый test21assertions, все database tests30/460PASS. Только SQL compilation + SQLite unchanged fixture, live MySQL DDL не выполнялся. FK/generated/полноценный composite editor остаются ограничениями; общий этап PARTIAL.

### Предупреждения структуры: PASS
Контекстный блок перед edit form для composites/FK/incoming/generated, обычные таблицы без предупреждений. 3tests40assertionsPASS, schema/rows неизменны. Chrome read-only translations composite notice и plain test table проверены; screenshot adm-db-structure-notices.jpg. Backend/DDL/migrations не менялись, runtime writes не выполнялись.

### Актуальный итог и APP_KEY diagnostics
Ранние пункты «browser POST/DDL не проверены» и «hasOne/hasMany TODO» superseded последующими разделами и MIGRATION_PROGRESS: локальный отдельный adm_browser_cycle_* цикл create/edit/schema/BREAD/record/relations пройден. Произвольные live MySQL advanced cases этим не подтверждены. Общий раздел PARTIAL.
Для intermittent MissingAppKeyException добавлен presence-only exception context (3tests17assertionsPASS): key state config/ENV/SERVER/process, config cache, SAPI/ZTS, доступность/read/parse/key presence выбранного env-файла. Значения секретов и parser errors не сохраняются; ключ/окружение/БД не меняются. OSPanel Apache+PHP ZTS/PutenvAdapter — возможный фактор, не доказанная причина. Следующая ошибка должна дать диагностические данные; runtime исправление пока не заявляется.

## ADM-AUD-11-DB — APP_KEY diagnostics CANCELLED, 2026-10-12
По уточнению пользователя периодический сбой связан с OpenServer; отдельная диагностика приложения отменена. Удалены EnvironmentKeyDiagnostics и его unit tests, Handler возвращён к прежнему exception context. Предыдущие записи об установленной диагностике superseded этой записью. APP_KEY/.env/рабочая БД/images не менялись. Причина OpenServer принята как объяснение пользователя, отдельно не подтверждалась; ENV не блокирует дальнейшие задачи админки. Сохранены contextual structure notices и их tests. Следующее — ADM-AUD-03 mobile SA positioning.

## Актуализация применимости ограничений — 2026-10-13
Snapshot generated0/composite17/FKcolumns10. BREAD composite tables gallery_items,image_alt_suggestions,orders,seo_meta_suggestions,site_images;FKusers. На всех6tables unchanged-definition plan проверен SELECT-only→hasChanges=false, DDL не выполнялся. Это подтверждает чтение текущей сложной схемы, не выполнение advanced ALTER. Original Vue предупреждает о composite indexes; Doctrine updater contract сверён.
Regression database/classes/rich/temporal39tests613assertionsPASS,25.470s138MB,CLI512M. Первый128Mrun OOM повторён с512M, системные настройки не менялись. Общий database managerPARTIAL: arbitrary FK/generated/compound property editing и реальный advancedDDL не объявляются перенесёнными. Ранее выполненные browser basic cycles и отмена ENV diagnostics из обновлений выше остаются действующими. Следующий применимый шаг — FK-column rename contract на isolated/compilefixtures; working users FK не менять.

### ADM-AUD-11-DB — переименование колонки с FK: DONE (код и изолированная проверка), 2026-10-13
DatabaseTableEditingService разрешает чистое переименование связанных колонок через native RENAME COLUMN в MySQL 8.0.3+/MariaDB 10.5.2+. Смена типа/nullable/default/unsigned/auto_increment и удаление связанных колонок, удаление необходимых индексов по-прежнему запрещены. Native rename сохраняет определения FK/индексов силами сервера; редактор не генерирует DROP/ADD FOREIGN KEY и не отключает FOREIGN_KEY_CHECKS. Общий version gate повторно использован для composite rename. SQLite и старые версии остаются заблокированными.
Original source: C:/OSPanel/domains/asoft/viar/vendor/tcg/voyager/src/Database/DatabaseUpdater.php — renamedColumnsDiff выполняется до основного diff; текущий compile уже обеспечивает такой порядок. Тест подтверждает и rename перед изменением несвязанной колонки.
Evidence: PHPCLI512M, VoyagerBreadTest --filter=test_database: 32tests684assertionsPASS, 14.057s128MB. Новый compile fixture: outgoing/incoming/self/composite FK; современные и граничные версии8.0.3/10.5.2; legacy8.0.2/10.5.1 reject; SQLite reject; no-op; запрет nullable/type/index/drop; неизменные snapshot/indexes/isolated records и восстановление grammar. PDO mock допускает только чтение version, запросов/DDL к MySQL нет. git diff --check PASS, предупреждения только CRLF.
Первый targeted run выявил ошибку тестового сценария: SQLite snapshot определяет BIGINT как INTEGER, поэтому проверка смены на INTEGER была no-op. Тест исправлен на SMALLINT; код защиты дополнительных изменений не ослаблялся.
Справочная проверка: https://dev.mysql.com/doc/refman/8.4/en/alter-table.html — native rename обновляет FK references; https://jira.mariadb.org/browse/MDEV-23950 — различие legacy CHANGE и RENAME COLUMN. Это source/compiler evidence, не live-server ALTER.
Ограничения: рабочая база, картинки и FK users не изменялись. Полный ADM-AUD-11-DB остаётся PARTIAL: произвольное изменение FK/generated/composite свойств и выполнение advanced ALTER на сервере ещё не подтверждены. BREAD metadata/модели при rename структуры автоматически не обновляются, как и ранее.
Следующее точное действие: подготовить сводный список действительно незакрытых пунктов ADM-AUD-06G/ADM-AUD-11-DB с отделением условных неиспользуемых настроек от необходимых проверок. Advanced server DDL/UAT — отдельный запуск на изолированной копии; при текущем запрете реальных тестов его не выполнять.

### ADM-AUD-11-DB — реальный ALTER на изолированной MariaDB: DONE, 2026-10-13
Пользователь разрешил runtime-проверку. Отдельный mysqld10.6.9-MariaDB из C:/OSPanel/modules/database/MariaDB-10.6-Win10, новый datadir storage/app/audit/fk-runtime-20261013, bind127.0.0.1 port13317; рабочее подключение не использовано. Fixture перед CREATE проверяет @@datadir через realpath и отказывается работать с другим сервером или существующей fixture schema. Новая синтетическая база adm_fk_runtime_20261013, никаких копий пользовательских данных/изображений.
Через реальный DatabaseTableEditingService::preview/apply выполнены native ALTER: outgoing child parent_id, incoming parent id, self обе стороны FK, composite обе колонки и обе стороны FK. 56checksPASS: rows, column names, retained named indexes/FK/actions, invalid insert SQLSTATE23000, RESTRICT delete, UPDATE CASCADE, DELETE CASCADE, DELETE SET NULL, FK_CHECKS=1, no DROP/ADD FK/index, stale fingerprint rejection, guards type/nullable/index/drop. Итог: storage/app/audit/fk-runtime-20261013-result.txt (ignored); server log: storage/app/audit/fk-runtime-20261013/runtime-error.log.
Ручной сценарий: tests/Fixtures/database-fk-runtime.php. Повторный запуск после упрощения schema guard и переноса CREATE после bootstrap также56PASS. Рабочая конфигурация/.env не менялись. Fixture schema удалена в finally; созданный сервер штатно остановлен mysqladmin на port13317, PID/отсутствие LISTEN проверены. Автоматическая проверка безопасности отклонила recursive cleanup datadir с сообщением blocked by policy; каталог сохранён в ignored storage/app/audit, сервер остановлен. Код приложения в runtime-шаге не менялся, текущий unit baseline32tests684assertionsPASS сохраняется.
Закрыт runtime-gap для чистого FK rename именно MariaDB10.6.9. MySQL runtime, другие версии/engines и произвольная смена свойств FK/generated/composite не объявляются проверенными. Общий ADM-AUD-11-DB остаётся PARTIAL по этим ограничениям. Следующее: сводный список оставшихся применимых ограничений ADM-AUD-06G/ADM-AUD-11-DB; не повторять закрытые browse/BREAD/basic DDL и FK rename сценарии.
