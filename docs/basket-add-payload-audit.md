# Аудит frontend payloads добавления в корзину

Обновлено: 2026-08-13.

## Вывод

Payload и reachability всех восьми опубликованных add-endpoints
классифицированы. Активные контракты имеют automated evidence; недостижимые
legacy endpoints и шаблоны явно отмечены и оставлены до frontend UAT.

## Матрица endpoints

| Endpoint | Основные источники payload | Статус |
|---|---|---|
| `POST basket/add` | активный global `new_bot_scripts.js`; три недостижимых legacy Blade builder | Готово: current canvas validation и optional payload протестированы; legacy builders классифицированы orphan |
| `POST basket/add/portrait` | `module.js`, `simpsons.js`, `sharj.js`, `royal.js`, `oil.js`, legacy `public/js/sharj.js` | Готово: simple generated, active oil и current wizard families протестированы |
| `POST basket/add/inter` | закомментированный legacy canvas tab | Orphan: route name сохранён, endpoint возвращает JSON 410 |
| `POST basket/add/module` | старый modular/interior flow | Orphan: active modular использует construct, endpoint возвращает JSON 410 |
| `POST basket/add/construct` | collage old/new, family и modular generator | Готово: raw/data base64 и original-file режимы разделены условным contract |
| `POST basket/add/future_art` | graphic portrait и footer form | Validation contract готов; 100 MiB разрешены, максимум 15 файлов, frontend-лимит удалён |
| `POST basket/add/recommended` | `module.js` | Готово: session offer + серверная базовая gallery-цена с 30% |
| `POST basket/add-canvas-recommendation` | cart recommendations Blade | Готово: catalog size price + 30%, нужен базовый товар, 100 MiB разрешены |

## Варианты общего `basket/add`

### Current canvas

Источник: `public/theme/viar/js/new_bot_scripts.js` и его min-копия.

Основные поля: `basketType`, `price`, `userImage`, `Image3d`, `formId`,
`sizeId`, `canvasId`, `executionId`, `decorationId`, `boxIds`, `photo_ex`,
`ram_id`, `terms`, `terms_price`, `pid`, `improve_photo`.

Статус: обязательные идентификаторы конфигурации, optional-поля, JSON `boxIds`,
base64 preview и соотношение `terms_price <= price` проверяются сервером.
Основной файл, оригинал и пример по 100 MiB подтверждены тестом без
application-level size limit. При JSON 422 активный JS показывает первое
сообщение Laravel и не очищает выбранные большие файлы.

### Legacy canvas Blade

Источник: `resources/views/partials/canvas/top_scripts.blade.php`.

Payload близок current canvas, но отличается способом выбора `sizeId`, цены,
preview/base64 и response parsing. Top-level `resources/views/canvas.blade.php`
не рендерится контроллерами и не включается другими Blade; builder признан
orphan и не определяет активный контракт.

### Canvas-new Blade

Источник: `resources/views/partials/canvas_new/new_bot_scripts.blade.php`.

Имеет собственную копию сборщика FormData и отличается от global JS. Однако
top-level `resources/views/canvas_new.blade.php` также не имеет активного
render/include; прежний `return view('canvas_new')` в контроллере закомментирован.
На `/new/canvas` двойного submit от этой inline-копии нет.

### Legacy `graph_portrait.blade.php` через общий endpoint

Источник: `resources/views/graph_portrait.blade.php`.

Найдено расхождение: FormData передаёт canvas-поля, но не передаёт `price`, и
текущий `BasketStoreRequest` вернул бы JSON 422. Полный source search не нашёл
прямого или косвенного render/include этого файла, поэтому он окончательно
классифицирован как orphan.

Активные `/graphic-portrait*` рендерят `graphical-portrait-buy`, подключают
`partials/grap_styl_paint_send.blade.php` и отправляют цену на
`basket/add/portrait`; это отдельный current wizard payload family.

Активный `/new/canvas` рендерит `theme.viar.pages.canvas`, layout подключает
ровно global `public/theme/viar/js/new_bot_scripts.js`, а submit выполняется
через `#t3_submit_btn`. Его поля совпадают с текущим `BasketStoreRequest`.

## Portrait payload families

- simple legacy family: `pid`, `price`, `name`, base64 `image`, size/decor/frame
  поля;
- current wizard family: дополнительно передаёт `orig_images[]`, `photo_ex`,
  `full_size`, `terms_price`, `fon`, `obraz` и другие style-specific поля;
- отдельные oil/royal/sharj варианты добавляют `obraz_title`, `obraz_img`;
- часть скриптов передаёт строку `undefined` вместо отсутствующего значения.

Automated evidence покрывает simple generated payload, active oil payload без
base64 preview и current wizard payload с существующим `pid`, оригиналами и
style-specific полями. Невалидный base64 и отсутствие исходника отклоняются.

## Future-art payload

Оба найденных сборщика отправляют `name`, `tel`, `email`, `images[]` и
`TotalImages`; footer-вариант дополнительно передаёт `is_canvas_collage`.
`FutureArtRequest` нормализует контакты, требует от 1 до 15 изображений и
проверяет MIME без application-level ограничения размера. Legacy-проверка
`> 20 MiB` удалена из `graph_portrait.blade.php`; в активном footer-сборщике
такой проверки не было. Файл 100 MiB и JSON 422 для неполного payload
подтверждены тестами. Отправка двух email остаётся legacy side effect и не
входит в validation-тест.

## Construct payload families

Подтверждены два взаимоисключающих режима:

- collage/family передают `image_offset` как raw base64 либо data URL;
- modular generator передаёт `is_orig_file=1`, файл `image`, MD5-compatible
  `collageSvgImage_hash` и необязательный SVG preview.

`ConstructBasketRequest` требует общие `name`, `price`, `size`, затем реальный
источник соответствующего режима. `photo_ex`, `fon[]` и `orig_images[]`
проверяются по MIME без size limit. Удалена недостижимая ветка, которая при
отсутствующем `image_offset` повторно пыталась сохранить это же отсутствующее
поле. Base64 теперь декодируется строго и пишется через uploads disk. Success
session/storage path и оба invalid source режима подтверждены feature-тестами;
правила original-файла принимают 100 MiB.

## Recommendation endpoints

Помимо двух `basket/add*` найден активный `POST /cart/add-recommended` из
checkout modal. Все три endpoint игнорируют совместимое поле `price`:

- gallery modal и item-card получают базовую цену из `gallery_items.price_from`,
  применяют country multiplier и 30%; требуется одноразовый session offer;
- canvas получает цену выбранного `full_size` из
  `canvas_header.sizes_30x40`, затем применяет 30%; в корзине уже должен быть
  хотя бы один базовый товар;
- canvas `userImage` проверяется по MIME без size limit; 100 MiB подтверждены.

Подмена browser-поля `price` значением `0.01` покрыта тестами для gallery,
checkout modal и canvas. Найдено отдельное функциональное ограничение:
item-card recommendation endpoint сохраняет только базовый gallery item и не
переносит выбранные пользователем size/frame/options. Эти поля нельзя добавить
в доверенный контракт до серверного пересчёта полной конфигурации.

## Retired inter/module endpoints

Reachability-проверка не нашла активных submit/callers:

- единственная форма с action `add_item_to_basket_inter` находится в
  `partials/canvas/tab2.blade.php`, include которой закомментирован;
- её legacy click-handler в любом случае перенаправлял действие на общий
  `.js_sbm__calc`, а не выполнял form submit;
- route/URL caller для `add_item_to_basket_module` отсутствует;
- классы `.js_add_basket_module` в активных скриптах только вызывают
  `#t1_submit_btn`; текущая modular page отправляет данные в `add/construct`.

Имена маршрутов сохранены, но оба POST URL направлены в compatibility handler:
HTTP 410, `code=legacy_endpoint_retired` и URL актуальной замены. Тест
подтверждает отсутствие session/file mutation. Старые недостижимые методы пока
оставлены в контроллере для сравнения на UAT и запланированы к удалению после
подтверждения.

## Общие риски

1. `VerifyCsrfToken::$except` исключает `basket/add`, `*/basket/*`, `/cart/*`
   и фактически отключает CSRF для basket/cart mutations.
2. Цена для нескольких endpoints принимается из frontend request; необходим
   серверный пересчёт по каталогу/конфигурации.
3. Есть исходник и min-копия активного JS; одинаковая обработка validation
   error синхронизирована. Отличающиеся inline Blade-копии недостижимы.
4. Реальный web-server/PHP должен быть настроен выше ожидаемого размера всего
   multipart-запроса; Laravel намеренно не задаёт собственный size limit.
5. Base64-ветки пишут файлы через `file_put_contents()` без единого строгого
   decode/MIME/storage contract.

## Следующие действия

1. Проверить точный охват basket/cart CSRF exceptions и безопасно сузить их для
   подтверждённых callers.
2. Спроектировать общий server-side pricing для конфигурируемых товаров.
3. После frontend UAT удалить orphan Blade/builders и старые controller methods.
