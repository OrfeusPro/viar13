# ADM-FIL-022/029 — актуальная инвентаризация, 2026-11-02

Источник: текущая подключённая MySQL, SET TRANSACTION READ ONLY + rollback.

Проверено 135 определений BREAD и 2014 полей. Хеш metadata до/после совпал.

## Фактически настроенные типы полей

| Тип | Количество |
| --- | ---: |
| `adv_media_files` | 19 |
| `checkbox` | 55 |
| `code_editor` | 4 |
| `color` | 6 |
| `date` | 3 |
| `file` | 7 |
| `hidden` | 8 |
| `image` | 117 |
| `media_picker` | 6 |
| `multiple_checkbox` | 2 |
| `multiple_images` | 8 |
| `number` | 99 |
| `password` | 1 |
| `relationship` | 32 |
| `rich_text_box` | 115 |
| `select_dropdown` | 21 |
| `text` | 1207 |
| `text_area` | 32 |
| `timestamp` | 272 |

## Результат классификации

- Две модели browser-cycle восстановлены штатным шаблоном; targeted list/read/add/edit audit:2types,0issues.
- Два старых определения canvas иa-collag-faq не имеют таблиц/моделей; metadata не исправлялись наугад и не удалялись.
- Все текущие controller/policy значения поддержаны существующими адаптерами.
- Custom views, включённые inline editors/List, coordinates, tinymceOptions и callable defaults в этом snapshot не обнаружены. Их универсальная поддержка не объявляется выполненной.
- Некорректных JSON details:0.

## Исправленный дефект аудита

bread:audit теперь учитывает валидированные hasOne/hasMany черезchildrenRows. Ранее они ошибочно отмечались недоступными. Регрессия воспроизведена доисправления; после5tests/51assertionsPASS, invalid relationships продолжают отклоняться.

## Границы

Это inventory/model/schema и targeted read/form проверка, не сохранение всех135types и не визуальная проверка каждой формы. Общий первоначальный read-only audit завершён:135types,6issues на версии command доисправлений. Targetedpostfixзакрылкатегориитестовогоцикла/галерею/коллажи. Остались3историческихограничения:canvas/a-collag-faq безтаблиц иpayment-info безполей/записей. Это комбинированные evidence, не единыйpostfix135PASS. Медиа/ссылки остаются следующим подэтапом.

Evidence: ignored storage/app/audit/content-inventory-20261102/{inventory.json,restored-report.md,restored-verified.json,export-verified.json}; JUnit storage/logs/content-audit-20261102.xml.

## Исправлены реальные pivot-связи галереи

Четыре существующие таблицы имеют ASCII-пробел в имени. Прежний BreadPivotKeys отвергал их до чтения схемы. Теперь допускается безопасный формат такого имени с обязательным существованием ключевых колонок; SQL-пунктуация по-прежнему запрещена. Имена таблиц, записи и определения BREAD не менялись.

После исправления:8tests/145assertionsPASS; actual read-only gallery-items/collage-header audit2types/0issues. SHA десяти таблиц до/после совпали. Обе формы галереи и edit коллажа рендерятся; это не сохранение всех рабочих записей.

Коммиты копии C::327a994 (модели/аудит),150e859 (pivot). Изменённые PHP проверены SHA256, Markdown не выгружался, push не выполнялся. Следующий пункт — остаточные media/link URL.
