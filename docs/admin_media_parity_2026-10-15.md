# ADM-AUD-09 — итоговая сверка медиатеки, 2026-10-15

Статус: **IN PROGRESS**. Реализация и изолированные проверки выполнены для перечисленных операций; оставшаяся браузерная UAT не считается пройденной.

## Источники

- Оригинал: `C:/OSPanel/domains/asoft/viar/vendor/tcg/voyager/resources/views/media/manager.blade.php`, `src/Http/Controllers/VoyagerMediaController.php`, package-lock.json этого пакета.
- Новая версия: `app/Filament/Pages/MediaLibrary.php`, `resources/views/filament/pages/media-library.blade.php`, общие `BreadLibraryFiles`, `BreadLibraryFolders`, `BreadImageUpload`, `BreadImageCrop`.
- Подробная история проверок: `docs/media_library_verification_2026-10-14.md` (включает продолжение 15 октября).

## Сопоставление операций

| Сценарий оригинала | Новая версия / подтверждение |
|---|---|
| Отдельная страница Медиа, browse_media | `/filament/media`, меню Инструменты → Медиа; mount/hydrate/action guards; Chrome и fake-storage тесты |
| Папки, переходы, цепочка пути | Корень/up/предки, двойной клик папки и отдельная кнопка открытия; Chrome |
| Настроенный корень и флаги операций | media.path/show_folders/allow_*; серверные проверки и тесты |
| hidden_files | Правило PATHINFO_FILENAME из исходного контроллера; оба значения проверены на fake storage |
| Один объект, Ctrl/Meta, Shift | selectCard и checkbox, визуальное выделение, прямой/обратный Shift как в исходном коде; тесты и Chrome |
| Сведения при одном файле | MIME, размер, дата, public URL, превью; тесты и Chrome |
| Редактирование имени по Enter | Поле в сведениях, кнопка сохранения, общий relocate/reference service; fake-storage тест и визуальная проверка без submit |
| Просмотр изображения | Двойной клик/лупа, модальное окно, Escape/повторное открытие; тесты и Chrome |
| Аудио/видео | HTML controls по MIME; HTML и смена типов проверены, декодирование реального файла в Chrome не проверено |
| Создание папки | Общий сервис, проверки пути/имени/конфликтов; fake storage |
| Переименование папки | Общий сервис и обновление ссылок; fake storage, форма/отмена Chrome |
| Перемещение файлов/папок/смешанного выбора | Предварительная проверка, общая транзакция ссылок, cleanup копий; fake storage, формы/выбор Chrome |
| Массовое удаление | Выбранные файлы и только пустые папки; fake storage, показ кнопок Chrome |
| Выбор/drop файлов и progress | uploadMultiple → автоматический uploadFiles, процент, сохранение, ошибки/отмена; JS callback tests, fake-storage PHP, вид формы Chrome |
| Смена папки во время upload | Ожидаемая папка фиксируется; при несовпадении 409 до записи; fake storage |
| Коллизии имён загрузки | Существующие файлы не заменяются, учитываются пути миниатюр; fake storage |
| JPEG orientation/reencode при upload | Отдельная обработка как исходный upload, GIF/docs bytes сохраняются; synthetic fixtures |
| Условные thumbnails/watermark | fit/crop/resize/позиции, rollback; 23 прямых сравнения исходного движка по пикселям |
| Обрезка/создание копии | Общий GD обработчик, GIF первый кадр, ограничения памяти; fake storage |
| Область за границей изображения | В standalone допускается, чёрное заполнение как Intervention/GD; прямое сравнение и feature-тест каждого пикселя |
| EXIF при crop | Стандартные настройки Cropper 1.5.11, серверный raw-pixel контракт оригинала; 8 прямых сравнений Orientation 1–8 |
| Рамка crop/масштабирование | Локальный Cropper.js той же версии; окно/числа/double-click проверены. 16 октября isolated Chrome подтвердил физическое wheel и drag move; pinch остаётся открытым |
| Обновление списка | Сброс устаревших форм/выделения/preview и актуальный список; fake storage и Chrome |
| Узкий экран | Chrome 390x844: исправлен native file input, форма295/295px и документ375/375px; поле/кнопка имени и модальное превью помещаются, закрытие работает |

## Согласованные и явно сохранённые различия

- Удаление папок — только пустых, согласно прежнему решению пользователя; рекурсивное удаление Voyager не перенесено.
- При переименовании/перемещении сохраняются источники для прежних ссылок, поддерживаемые ссылки обновляются. Это ранее согласованное поведение.
- Публичные исполняемые расширения запрещены; лимиты партии/размера и памяти действуют независимо от legacy wildcard MIME.
- Встроенный BREAD сохраняет прежние ограничения обрезки; выход за границы разрешён только standalone.
- Legacy crop не поворачивает серверную матрицу по EXIF перед обрезкой. Его контракт сохранён; отдельное исправление расхождения preview/server не заявляется.
- Внешний вид соответствует общей панели Filament. Папочные операции доступны через иконки/формы, вместо поля имени папки в правой панели Voyager.

## Последний общий gate

- PHPUnit по FilamentAuthorizationTest, MediaImageProcessingTest, VoyagerBreadTest, MediaCropExifTest с фильтром медиатеки/общих image/library операций: **41 тест / 1082 assertions PASS**, 33.030s, 118 MB. Выполнен после inline rename, до последнего исправления hidden_files.
- После изменения hidden_files: targeted hidden_files + inline rename — **2 теста / 24 assertions PASS**, 0.898s, 78 MB.
- Node upload adapter: **3 теста PASS**, 78.202ms.
- Наборы пересекаются и не суммируются. Это не полный тестовый набор приложения.
- Рабочие БД/файлы/изображения не изменялись. Изменения хранения проверены на SQLite/fake storage. Коммит не создан.

## Открытая приёмка

1. Двухпальцевый жест и EXIF-превью в Chrome; колесо и перемещение проверены 16 октября на изолированном PNG.
2. Реальное декодирование аудио/видео в Chrome при наличии подходящих образцов.
3. Полная browser upload/drop/progress/cancel цепочка на отдельном тестовом storage, без рабочих изображений.
Мобильная сверка inline-name/upload/preview завершена 15 октября; временный viewport сброшен. Снимки: media-mobile-upload-final-20261015.png, media-mobile-preview-final-20261015.png, media-mobile-inline-name-20261015.png в storage/app/audit.

До подтверждения этих пунктов ADM-AUD-09 не объявлять полностью DONE. Следующий этап разработки из основного плана — ADM-AUD-10 (операции меню/настроек и drag-and-drop): сначала проверить уже существующий код и сопоставить с оригиналом.

### ADM-AUD-09 — isolated Cropper wheel/move/cancel: PASS, 2026-10-16
Сверка уточнила область: standalone preview только modal image, отдельных zoom/reset toolbar нет; wheel/pinch/move относятся к original Cropper1.5.11. Product UI не расширялся. Synthetic PNG640x480 с grid и browse_media добавлены только fixture SQLite/public; рабочие изображения не затрагивались.
Chrome native wheel через tab.scroll внутри canvas уменьшил480x360 до436.354x327.271 (inline style436.364x327.273); crop fields стали39,29,563,422. Doubleclick crop face переключил drag-box в move (DOM data-cropper-action=move); physical tab.drag от750,225 к790,245 изменил image canvas translate77.2424/16.2727→117.242/36.2727, cropx=-20/y=0. Отрицательная область допустима standalone по original. Только чтение DOM geometry, никакого вызова скрытых cropper методов через evaluate.
Cancel→modal hidden; reopen создал новый canvas480x360 и исходныеx64/y48; сноваCancel. Кнопки cropImage(false/true) не нажимались. SHA256 synthetic crop image до/после совпал,bytes2204. Screenshot media-wheel-pan-isolated-20261016.png сохранён/просмотрен и подтверждает moved canvas/поля. Server session55776 остановлен Ctrl-C,exit1 штатной остановки. Fixture helpers/routes не меняют product code; коммита нет.
Закрыта физическая desktop wheel/move UAT. Pinch/touch, EXIF preview, actual audio/video decode и browser upload/drop/progress/cancel по-прежнему открыты. Матрицы09/общего аудита обновлены, основной план и журнал дополнены; весь09 не DONE. Следующее — EXIF preview на synthetic Orientation1–8 либо декодирование local media samples; chooser block не превращать в ложный backend failure.

### ADM-AUD-09 — EXIF fixtures/server contract PASS; browser preview open, 2026-10-16
Подготовлены8 synthetic JPEG320x240 с4цветными квадрантами и Orientation1–8 в isolated-browser/public. Прямой exif_read_data/getimagesize/SHA256 verifier подтвердил24 условия (8x3): Orientation, dimensions и неизменность bytes; отчёт isolated-browser/exif-fixture-verification.json. Это проверка fixtures, не24 PHPUnit assertions и не визуальное подтверждение поворота.
Повторный PHPUnit tests/Unit/MediaCropExifTest.php:1test16assertionsPASS,0.008s26MB. Все8 Orientation сохраняют исходный raw-pixel server crop contract12x9jpeg; это совместимость с original, а не auto-orientation correction. Product code не менялся.
Chrome createBrowserTab отклонён сообщением Local browser use is disabled in Work settings. Страница новыхEXIF не открывалась, rotate/flip preview не проверены. Не обходить отключённое управление сторонними браузерными средствами. Server session38763 остановлен Ctrl-C,exit1 штатной остановки. Рабочие DB/images не менялись, коммита нет. Этот browser blocker появился впервые после успешных предыдущих UI проверок; весь goal не blocked, независимая code/test работа доступна. Следующий visual шаг при доступном browser — crop modal каждогоEXIF, DOM transform/screenshot/cancel/hash. Пока09EXIF browser и pinch остаются открытыми.
