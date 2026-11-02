# ADM-AUD-09 — итоговая сверка медиатеки, 2026-10-15

Статус локальной приёмки: **DONE 2026-11-02 в согласованном объёме**. Physical OSdrop SKIPPED пользователем; pinch/touch DEFERRED до целевого устройства. Эти исключения не означают PASS физических жестов. Остальные перечисленные browser checks завершены.

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

1. Двухпальцевый жест DEFERRED до целевого устройства по явному решению пользователя2ноября. EXIF-превью1–8 проверены2ноября; wheel/pan —16октября.
2. DONE2ноября: actualbrowser playback3секундныхPCM WAV/VP8 WebM во встроенномбраузере. Другиеcodecs/physicalaudiooutputнепроверялись.
3. Физический OSdrop остаётся открытым. Browser chooser upload/size reject/backend reject/recovery/progress/response-wait cancel/retry проверены2ноября наизолированнойfixture; partial-wire cancelне заявляется.
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


## ADM-AUD-09 — EXIF-превью JPEG1–8, 2026-11-02
- IN PROGRESS: восстановлена отдельная fixture media-exif-20261102 (копия synthetic SQLite и11public files, включая8JPEG Orientation1–8). Свои sessions/views/logs/tmp, localhost8800, DB isolation guard, Http::preventStrayRequests иMail::fake. Production DB/storage не подключаются.
- Browser openCrop→preview DOM/transform/screenshot→cancel для8ориентаций, безcrop/save; затем сравнить11SHA исходников иsnapshotDB. Ранее unit raw-pixel contract проверен; это отдельная оставшаяся visual UAT, не новая автокоррекция EXIF. Pinch/touch/upload/media decode этим сценарием не закрываются.


## ADM-AUD-09 / ADM-AUD-13G — Продолжение проверки, 2026-11-02
- EXIF browser gate не завершён: два перехода кlocalhost8800 черезChrome не подтвердились; вкладка осталась наdashboard. Native screenshot fallback остановлен Computer Use URL-confidence safety guard. После этого UI input прекращён, защиту не обходили. Статус09 IN PROGRESS; это не доказанный дефект медиатеки.
- Отдельный fixture server остановлен Ctrl-C.11/11 synthetic public SHA256 неизменны, report media-exif-20261102/after-hashes.json. Production images/DB не затрагивались; crop/save не нажимались. Следующее действие09: после восстановления управления открыть тестовую медиатеку ипроверить8EXIF preview/cancel.
- ADM-AUD-13G: JS15testsPASS,0fail/skip,212.4886ms; общий900PHP gate всё ещё выполняется, session6279. Final JUnit не подменяется промежуточными dots. Следующее действие13G: опросить этот же живой процесс до terminal результата, не запускать дублирующий suite.
- ТолькоMarkdown результаты иignored fixture helpers. Non-md runtime/tests дляC:/OSPanel/domains/viar/viar13 нет; mirror clean. Commit документации в source безpush; актуальная верхняя матрица очищена от уже закрытых historical TODO.


## ADM-AUD-09 — EXIF-превью: результат 2026-11-02

- **DONE для EXIF desktop preview/cancel.** Во встроенном браузере открыта настоящая `/filament/media` на отдельной SQLite/public fixture. Chrome/native ввод после прежнего safety stop не использовался. Проверены все восемь JPEG320×240 с Orientation1–8; просмотрены восемь снимков окон обрезки.
- Отображение Cropper: 1 — исходное; 2 — scaleX(-1); 3 — rotate(-180deg); 4 — scaleY(-1); 5 — rotate(90deg)+scaleY(-1); 6 — rotate(90deg); 7 — rotate(90deg)+scaleX(-1); 8 — rotate(-90deg). Цветные квадранты/надпись TOP LEFT на снимках соответствуют этим преобразованиям.
- Поля исходной области: orientations1–4 — x32/y24/w256/h192, orientations5–8 — x24/y32/w192/h256. Полотно/выделение и четыре числовых поля видны; для portrait-вариантов область меняет ориентацию. Методы Cropper через evaluate не вызывались: только штатное openCrop, DOM чтение и отмена.
- На каждом файле нажата «Отмена обрезки». В итоговом состоянии cancelVisible=false, Файлов11. Ни `cropImage(false)`, ни `cropImage(true)`, ни замена/создание файла не выполнялись. Не выдаём preview за проверку нового серверного auto-orientation: сохранён прежний raw-pixel crop contract оригинала.
- Original manager.blade.php использует new Cropper(croppingImage, {crop:...}) с default orientation handling; новая библиотека также1.5.11, прежний source audit сохранён. Парный оригинальный browser crop на этих fixture не запускался; вывод относится к восьми актуальным preview новой панели и сверке исходного контракта.
- Snapshot15tables после первого preview и до последних семи сравнён с финалом: DB unchanged.11/11 SHA public-файлов совпали с baseline до первого браузерного открытия. Собственная auth fixture/users.settings добавлена до проверки; рабочая DB/storage не затронуты. Http::preventStrayRequests/Mail::fake остаются guard стенда.
- Evidence: ignored storage/app/audit/media-exif-20261102/{preview-results.json,verified.json,db-before.json,before-hashes.json,exif-1.jpg...exif-8.jpg,closed-final.jpg}. Несколько locator waits получили stale-element на смене Livewire DOM; повторная read-only observation подтвердила нужный файл, submit не повторялся. Это не трактуется как доказанный дефект приложения.
- Вкладка закрыта, server session49840 штатно остановлен Ctrl-C. Изменений runtime/tests нет, нового regression ради read-only визуальной проверки не добавляли. В C:/OSPanel/domains/viar/viar13 non-md для копирования нет; target commit не требуется. Source документы коммитятся без push. CRM/SA API/auth/DB/mappings без изменений, lead_id=orders.id.
- **ADM-AUD-09 остаётся IN PROGRESS:** физический pinch/touch, audio/video decoding и browser upload/drop/progress/cancel ещё открыты. Следующее точное действие09 — полный upload/recovery на этой изолированной медиатеке; проверенные EXIF preview повторно в TODO не возвращать. ADM-AUD-13G общий900test gate продолжает выполняться, по последнему наблюдению819/900 безfail markers; final report ещё ожидается.


## ADM-AUD-09 — Медиатека: browser upload/reject/recovery, 2026-11-02
- IN PROGRESS. Отдельная media-upload-20261102 fixture: собственные SQLite/public/tmp/views/sessions, baseline11files/15tables, Http::preventStrayRequests иMail::fake. Проверить chooser oversized отказ→server forbidden-extension отказ→valid PNG/PDF recovery/save/SHA; затем отмену передачи с fixture-only8s response delay, безподмены JSкомпонента.
- Production DB/media не использовать.8EXIF preview/cancel ужеDONE, ихнеповторять. Настоящий OS drag/drop иphysicalpinch остаются отдельными пунктами; chooser заdragневыдавать. Runtime пока без изменений; после результата обновить планы/коммитбезpush, non-md exportтолькоприproduct/testизменениях.


## ADM-AUD-09 — Browser upload/reject/cancel/recovery: результат 2026-11-02

- **DONE для chooser upload/recovery/cancel desktop.** Actual mediaLibraryUpload/WithFileUploads/uploadFiles на изолированном localhost8800, SQLite/public/tmp. Runtime JS/PHP не подменялись; рабочие БД/файлы/SMTP/HTTP integrations не затрагивались.
- File chooser открывался штатным input; multiple=true. Oversize PNG11536540bytes (>10MiB) немедленно показал «До20файлов,до10МБкаждый», public остался11files. Synthetic blocked.php содержит только безвредный текст: реальный backend запретил расширение сообщением «Недопустимое имя или тип файла», UI не сообщил успех/public11.
- Первый transport failure оказался дефектом fixture: APP_ENVtesting переключает Livewire disk наtmp-for-tests, он не был настроен. Исправлен только ignored router — local disk сroot fixture/tmp. После повторного выбора actual forbidden extension достиг server validation. Этот500 не объявляется исправлением production upload; продуктовый код не менялся.
- После отказа в той же форме выбраны valid uat-upload.png + uat-upload.pdf. Actual transfer→save→«Загрузка завершена», alerts пусты, count13. PDF45bytes byte-for-byte совпал сinput. PNG640×480 штатно перекодирован: rawSHA отличается, RGBA pixels/dimensions совпадают полностью. Это не потеря фото; исходное image processing поведение сохраняется.
- Cancel: только fixture router задержал ответ upload-file на8секунд. Видимые progress100 и «Отменить передачу» подтверждены; штатный click дал «Передача отменена». При этом public13 иcancel-upload.png отсутствовал: uploadFiles не создал постоянный файл. Это response-wait cancellation после передачи bytes, не physical slow-wire partial-byte сценарий.
- Задержка снята. Тот же cancel-upload.png выбран повторно черезchooser: «Загрузка завершена», alerts[], public14. Размер/пиксели второго PNG совпали сinput; SHA обоих сохранённыхPNG одинаковыйd4f097229bd00e64b259f6858191f15d926b2fd4f81ee269b3bd758848fba93e. Отмена не повредила следующий upload.
- Проверка15tables unchanged;11/11 исходных publicSHA неизменны. Только3ожидаемых новых постоянных файла:uat-upload.png/uat-upload.pdf/cancel-upload.png. Oversize/blocked вpublicотсутствуют; проверкаassert прошла. Temporary upload files остаются вisolated tmp, не врабочемstorage; этот шагнепроверяетTTLcleanup.
- Evidence ignored media-upload-20261102/{verified.json,requests.jsonl,before-hashes.json,db-before.json,oversize-rejected.jpg,forbidden-rejected.jpg,recovery-success.jpg,cancel-progress.jpg,cancelled.jpg,retry-after-cancel.jpg}. Снимки отказов/успеха/отмены просмотрены. Форма штатно закрыта, вкладка закрыта, server49062 остановлен Ctrl-C.
- Код/tests продукта не менялись; новых implementation-mirroring tests не добавляли. Общий900/11272/1legacy skip gate иJS15PASS завершены вэтотдень, runtimeпослених прежний. Source docs commit безpush; non-md дляC:/OSPanel/domains/viar/viar13 нет, mirrorclean, targetcommitне требуется. CRM/SA API/auth/DB/static mappings безизменений,lead_id=orders.id.
- **ADM-AUD-09 всё ещё IN PROGRESS:** физическийOSdrop, pinch/touch иactualaudio/video decode остаются открыты. Chooser иcancel-response не выдаются зафизическийdrop/partial-wire cancellation. Следующее09 — browser audio/video preview накоротких synthetic files, затемоставшиеся device gestures; остальные задачи админки/DEVauth/cutoverне закрыты этим шагом.


## ADM-AUD-09 — Аудио/видео: browser decode, 2026-11-02
- IN PROGRESS: отдельная media-playback-20261102 SQLite/public fixture. Созданы3секундные silent PCM WAV иVP8 WebM160×96/10fps/30frames, безproduction media. Собственныеsessions/views/logs/tmp, Http::preventStrayRequests иMail::fake; browser открытие сведений→штатныйplay→currentTime/ended/error/dimensions→сменафайла. Затем2SHA/15tables snapshot.
- Original manager имеетnative controls/load дляaudio/video; новаяпанель сохраняетnative controls сподстановкойactual MIME. Вызовыplay/loadчерезevaluateнеиспользовать, толькоUIkeyboard/button. MP4/AAC, другие codecs/deviceaudiooutput иphysicalgestures этимнеобъявлятьпройденными.


## ADM-AUD-09 — Аудио/видео: результат 2026-11-02

- **DONE для actual browser decode WAV/VP8 WebM.** На настоящей медиатеке отдельной fixture открыты сведения двух synthetic файлов. Native audio play черездоступнуюкнопку: pausedfalse/currentTime0.471715/duration3/readyState4/errornull, затем endedtrue/currentTime3. WAV silent PCM16000Hzmono,96044bytes; это decode/timeline, анепроверка физических колонок/слышимости.
- При выбореWebM previousaudio исчез:audioCount0. Video metadata160×96/duration3/readyState4/errornull; штатныйSpaceнаcontrols запустилplay (pausedfalse,currentTime0.1973), затемendedtrue/currentTime3/errornull. Screenshot показывает последнийкадр анимации/3из3секунд; decoded-frame counter вэтойсреде недоступен(null), нечистое число30неприписываетсябраузеру.
- WebM созданOpenCVVP8:30frames/10fps,2950bytes; creationwarningFOURCC контейнера не сталотказомencoder —writeropened иVideoCapture30frames проверены отдельно. ПоддержкаMP4/H264/AAC/прочихcodecs невыводитсяизодногоWebM.
- Штатное закрытиесведенийудаляетобаmediaelements:audioCount0/videoCount0. Доступкplay/load черезevaluateнеиспользовался, толькокнопка/Space/выбор/закрытие. Original manager.blade.php154–168/519–522такжеnativecontrols сloadпривыборе; новыйsourceиспользуетactualMIMEвместоисходногоспискавозможныхтипов. Парныйoriginalbrowserplayэтихfixtureневыполнялся.
-2/2SHApublicфайлов и15tablesunchanged. Evidence ignored media-playback-20261102/{verified.json,playback-results.json,audio-ended.jpg,video-ended.jpg}; снимкипросмотрены. Вкладказакрыта, server46542остановленCtrl-C. Runtime/tests/DBсхема/CRM-SAконтрактыбезизменений,lead_id=orders.id; текущий900/11272/1legacy skip иJS15PASSостаютсяbaselineбезповторногопрогона.
- ТолькоMarkdown; non-md дляC:/OSPanel/domains/viar/viar13нет, mirrorcommitнет, sourcedocscommitбезpush. **ADM-AUD-09остаетсяINPROGRESS:** физическийOSdrop иpinch/touchещёоткрыты. Подготовленновыйisolatedgestureстенд, дляреальногожестаизПроводниканужнодействиепользователя; chooserнеподменяетdrop.


## ADM-AUD-09 — Физические жесты: решение пользователя 2026-11-02
- Pinch/touch **DEFERRED до целевого устройства** по явному ответу пользователя: «Нет, отложить сенсорную проверку до целевого устройства». Это неPASS и неотказотфункции; desktop wheel/pan уже проверены ранее. Не блокировать текущуюлокальнуюприёмкунаотсутствующемсенсорномоборудовании.
- Physical OSdrop **IN PROGRESS**: actual isolated медиатека открыта справа http://127.0.0.1:8800/filament/media, областьuploadраскрыта. Дано конкретное ручное действие: изПроводника перетащить media-gestures-20261102/input/drag-test.png. Auto-save только вsynthetic public; browser tab4 markHandoff, server95746 жив.
- Baselinepublic gesture-crop.png и15tables записаны. После ответапользователя проверить actual newfile/DOMsuccess/PNGpixels/oldSHA/DB; доэтогоdropнеобъявлятьвыполненным. Другиеmedia gates EXIF/chooser/reject/cancel/recovery/WAV/WebM ужеDONE.


## ADM-AUD-09 / ADM-AUD-10E — Решение и следующий шаг, 2026-11-02
- Пользователь явно указал: «по перетаскивания пропусти». Physical OSdrop **SKIPPED по решению пользователя**, неPASS. Pinch/touch ранееDEFERRED доцелевогоустройства. Все остальные записанные media checks завершены; локальный ADM-AUD-09 принят воставшемсясогласованномобъёме. Физическиежестынеобъявляютсяпроверенными.
- Gesture server95746 остановлен, тестовыхdrop/uploadsпередskipнебыло, publicтолькоgesture-crop.png. Вкладказакрывается. Исходникиproductionнеизменны.
- **ADM-AUD-10E IN PROGRESS**: отдельный settings-media-20261102 localhost8801, synthetic SQLite settings9types/public, tmp-for-testsнастроенвнутриfixture, Http::preventStrayRequests/Mail::fake. Проверитьsingleimageиfile replacement→save→reopen, oldfileskept/paths/contracts; далееjointsaveеслинеобходимодляоставшейсяUAT. Сначалаbaseline15tables/11files; workingDB/storageнеиспользовать.
