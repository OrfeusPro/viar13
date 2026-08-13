# Аудит frontend payloads добавления в корзину

Обновлено: 2026-08-13.

## Вывод

Проверка всех вариантов **не завершена**. В проекте опубликовано восемь
add-endpoints, а payload формируется несколькими поколениями Blade и JS.
Готовыми считаются только контракты, для которых указан automated evidence.

## Матрица endpoints

| Endpoint | Основные источники payload | Статус |
|---|---|---|
| `POST basket/add` | global `new_bot_scripts.js`, legacy canvas partial, canvas-new partial, legacy `graph_portrait.blade.php` | Частично: current canvas и modular validation протестированы |
| `POST basket/add/portrait` | `module.js`, `simpsons.js`, `sharj.js`, `royal.js`, `oil.js`, legacy `public/js/sharj.js` | Готово: simple generated, active oil и current wizard families протестированы |
| `POST basket/add/inter` | legacy canvas form/action | Не проверен, Form Request отсутствует |
| `POST basket/add/module` | legacy modular/interior flow | Не проверен, Form Request отсутствует |
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

Статус: базовая validation и файл 100 MiB подтверждены тестом.

### Legacy canvas Blade

Источник: `resources/views/partials/canvas/top_scripts.blade.php`.

Payload близок current canvas, но отличается способом выбора `sizeId`, цены,
preview/base64 и response parsing. Нужен отдельный contract test.

### Canvas-new Blade

Источник: `resources/views/partials/canvas_new/new_bot_scripts.blade.php`.

Имеет собственную копию сборщика FormData и отличается от global JS. Нужна
проверка, какой именно скрипт выполняется на странице и нет ли двойного submit.

### Legacy `graph_portrait.blade.php` через общий endpoint

Источник: `resources/views/graph_portrait.blade.php`.

Найдено расхождение: FormData передаёт canvas-поля, но не передаёт `price`.
Текущий `BasketStoreRequest` вернёт JSON 422. Прямой `return view()`/include для
этого файла пока не найден, поэтому он классифицирован как вероятный orphan до
полного reachability-аудита.

Активные `/graphic-portrait*` рендерят `graphical-portrait-buy`, подключают
`partials/grap_styl_paint_send.blade.php` и отправляют цену на
`basket/add/portrait`; это отдельный current wizard payload family.

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

## Общие риски

1. `VerifyCsrfToken::$except` исключает `basket/add`, `*/basket/*`, `/cart/*`
   и фактически отключает CSRF для basket/cart mutations.
2. Цена для нескольких endpoints принимается из frontend request; необходим
   серверный пересчёт по каталогу/конфигурации.
3. Есть исходники, min-копии и inline Blade-копии одного сценария; они уже
   различаются по полям и обработке JSON response.
4. Реальный web-server/PHP должен быть настроен выше ожидаемого размера всего
   multipart-запроса; Laravel намеренно не задаёт собственный size limit.
5. Base64-ветки пишут файлы через `file_put_contents()` без единого строгого
   decode/MIME/storage contract.

## Следующие действия

1. Подтвердить reachability legacy `graph_portrait.blade.php` и удалить либо
   исправить его только после подтверждения использования.
2. Завершить reachability-классификацию `inter` и `module`.
3. Спроектировать общий server-side pricing для конфигурируемых товаров.
5. После contract coverage сузить CSRF exceptions и подтвердить все callers.
