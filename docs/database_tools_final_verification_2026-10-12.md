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
