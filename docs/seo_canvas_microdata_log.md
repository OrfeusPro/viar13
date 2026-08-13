# Журнал микроразметки SEO - canvas

Дата: 2026-04-17

## Область

Страница:
- `/lv/new/canvas`

Цель:
- сделать структурированные данные чище для Google
- убрать дублирующиеся или конфликтующие блоки schema
- оставить страницу ориентированной на продукт

## Что изменили

1. Добавили page-level JSON-LD в общий layout для canvas-страниц:
- `WebPage`
- `BreadcrumbList`

2. Оставили в body canvas-разметку товара как `Product` microdata.

3. Убрали дублирующую breadcrumb schema из body-разметки.

4. Заменили общий тип бизнес-схемы с `LocalBusiness` на `Organization` в partial для города/бренда.

5. Связали брендовые метаданные с уже существующими источниками проекта:
- `og:site_name` из переводов
- социальные ссылки из Voyager settings
- телефоны из существующих translation files

6. Вынесли повторяющиеся брендовые данные в общий helper:
- `site_brand_schema()`
- `site_brand_same_as()`
- `site_brand_contact_points()`
- `site_brand_area_served_cities()`

7. Для `/lv/new/caricature` отдельно усилили страницу продукта:
- `og:type` переключен на `product`
- `og:image` берётся из реального изображения страницы и нормализуется в абсолютный URL
- breadcrumb-разметка вынесена в JSON-LD в `<head>`
- body breadcrumb microdata убрана из активного шаблона

8. Общую бренд-схему начали использовать и на странице `about`:
- ручной `Organization` JSON-LD заменён на `site_brand_schema()`
- контактные данные и `areaServed` собираются через общий helper
- список стран для `about` оставлен отдельным, так как это не города
- активный `about`-шаблон использует безопасные fallback'и для `meta_title`, `meta_description` и `content`

9. Довели ещё две продуктовые страницы до единого SEO-профиля:
- `/lv/simpsons`
- `/lv/new/graphic-portrait/portrait-historical`
- для обеих страниц добавлены `og:type=product`, релевантный `og:image` и JSON-LD `WebPage` + `BreadcrumbList`

10. Расширили тот же SEO-профиль на ещё три продуктовые страницы:
- `/lv/collage`
- `/lv/new/graphic-portrait/portrait-oil`
- `/lv/modular-generator`
  - для них добавлены `og:type=product`, релевантный `og:image`, JSON-LD `WebPage` + `BreadcrumbList`, а breadcrumb microdata в body убрана там, где была

20. Для `graphic_portrait.new_page` закреплён источник `og:image`:
- `PortraitPageController@hbindex` теперь сначала берёт картинку из `GalleryItem->sliderImage.size_img`
- если `size_img` пустой, используется `sliderImage.png`
- это выравнивает `og:image` страницы с той же картинкой, что показана в карточке на `/sizesprices`

11. Привели gallery item pages к тому же чистому профилю:
- `/lv/gallery/photo/item/59`
- `/lv/gallery/reproduction/item/35`
- для gallery item pages добавлены `og:type=product`, `og:image` из самих изображений товара, JSON-LD `WebPage` + `BreadcrumbList`
- breadcrumb microdata в body заменена на визуальные крошки без schema.org-атрибутов

12. Почистили gallery list pages:
- `/lv/gallery`
- `/lv/gallery/{type}`
- `/lv/gallery/{type}/{category}`
- `/lv/gallery/painters`
- `/lv/gallery/style/{alias}`
- убрали `BreadcrumbList` microdata из body и оставили только визуальные крошки
- исправили сломанный вложенный `<a>` в `gallery_category.blade.php`

13. Вернули доступность breadcrumb для gallery list pages через JSON-LD в head:
- `hb_type_render()` теперь прокидывает `WebPage` + `BreadcrumbList` для `/lv/new/gallery/reproduction` и соседних list pages
- body breadcrumbs остаются визуальными без schema.org-атрибутов
- для `/lv/new/gallery/reproduction` breadcrumb path теперь снова доступен поисковику: `Home -> Gallery -> Reproduction`

14. Довели корневую gallery list page до того же стандарта:
- `/lv/new/gallery`
- добавлен `BreadcrumbList` JSON-LD в head: `Home -> Gallery`
- `og_url` и `WebPage` остаются в head через общий layout

15. Дочистили `photo` category page breadcrumbs:
- `/lv/new/gallery/photo/canvas-landscape`
- в body breadcrumbs добавлен 4-й визуальный пункт категории
- path теперь совпадает с head breadcrumb JSON-LD: `Home -> Gallery -> Foto gleznas -> Landscape`

16. Breadcrumb DOM для photo-страниц приведён к виду `span.breadcrumbs__item > a.breadcrumbs__link`:
- третий пункт теперь использует локализованное `page->name`
- только последний breadcrumb на подстранице остаётся без `breadcrumbs__link_main`

17. Локализация названий категорий в gallery приведена к текущей локали:
- `gallery_category`, `photo` и `module` теперь выводят `getTranslatedAttribute('name')` для категории
- табы `genre`, `age`, `nationality`, `style`, `painter` тоже используют переводы вместо сырого `name`
- `photo` дополнительно использует переведённое имя категории в `alt` у preview-изображения

18. `photo` gallery-страницы локализовали `WebPage` schema:
- `BreadcrumbList` и `WebPage` теперь берут переводимые `name`/`meta_title`/`meta_description` у категории
- сырой `Landscape` больше не должен попадать в JSON-LD на локализованных страницах

19. Исправлен runtime-fail на gallery pages:
- добавлен общий helper `translated_value()` для совместимости с моделями и `TCG\Voyager\Translator`
- `photo`, `module`, `reproduction` и gallery tabs теперь не зависят от прямого вызова `getTranslatedAttribute()` на translated-объектах
- локальный `canvas-landscape` снова отдается с переведённым `Пейзаж` в body breadcrumb и JSON-LD

20. Для `graphic_portrait.new_page` закреплён источник `og:image`:
- `PortraitPageController@hbindex` теперь выбирает активный `portrait_slider` с `is_show=1`
- если активного preview нет, используется relation `GalleryItem->sliderImage`
- это выравнивает `og:image` страницы с картинкой из карточки на `/sizesprices`

21. Убрали расхождение между sizes/prices карточкой и portrait pages:
- `simpsons`, `caricature`, `portrait-historical` и `portrait-oil` тоже теперь выбирают активный `portrait_slider` с `is_show=1`
- fallback на `GalleryItem->sliderImage` остался запасным вариантом
- теперь `og:image` у portrait pages должен совпадать с visible preview на `/sizesprices`

22. Обновили SEO-профиль `/new/canvas`:
- добавили общий helper `single_image_url()` для канонических SEO-картинок
- закрепили основной SEO image source на стабильном маршруте `/images_single/canvas.png`
- этот маршрут динамически отдаёт ту же картинку, которая раньше использовалась как canvas image source: `CanvasSlider.size_img`, затем `CanvasSlider.image`
- добавили `link rel="image_src"` с тем же URL, что и `og:image`
- расширили JSON-LD canvas на `ImageObject`, `Product` и `AggregateRating`
- `ImageObject`, `og:image:width` и `og:image:height` получают фактические размеры выбранной картинки
- `AggregateRating` теперь подтягивается из Google widget fallback values с безопасным fallback на 4.9 / 202

23. Довели page-specific schema до `condition` и `delivery`:
- `condition` теперь отдаёт JSON-LD `WebPage` + `BreadcrumbList` через `structured_data`
- `delivery` теперь отдаёт JSON-LD `WebPage` + `BreadcrumbList` через `structured_data`
- в `ru/pages.php` восстановлен нормальный текст `delivery_text` вместо заглушки `Тест`

24. Расширили stable image alias подход на другие страницы с `og:image`:
- добавлен общий маршрут `/images_single/{slug}.png`, который отдаёт старый реальный источник картинки по стабильному публичному alias
- дополнительный маршрут `/images_single/{slug}` оставлен только как диагностический/резервный вариант для проверки production rewrite
- `/sizesprices` использует `/images_single/sizesprices.png?v=...`, источник остаётся `CanvasSlider.size_img`, затем `CanvasSlider.image`
- `/collage` использует `/images_single/collage.png?v=...`, источник остаётся `ACollageSlider.size_img`, затем локализованная картинка слайда
- `/modular-generator` использует `/images_single/module-generator.png?v=...`, источник остаётся `theme/viar/images/module-generator/main.png`
- portrait/product pages используют `/images_single/portrait-{id}.png`, `/images_single/product-{id}.png`, `/images_single/product-oil-{id}.png`
- gallery item pages используют `/images_single/gallery-item-{id}.png`
- во всех активных controller-based страницах, где задаётся `og_image`, добавлен `image_src` с тем же URL
- старый `/images_single/canvas-banner-img.png` и `/img/canvas-banner-img.png` оставлены как redirect на `/images_single/canvas.png`

## Текущее состояние structured data

- `Product` microdata: объект canvas в body
- `BreadcrumbList` JSON-LD: в `<head>`
- `WebPage` JSON-LD: в `<head>`
- `ImageObject` JSON-LD: в `<head>` для основной SEO-картинки canvas
- `Product` JSON-LD: в `<head>` для canvas
- `WebPage` + `BreadcrumbList` JSON-LD: на `condition` и `delivery`
- `Organization` JSON-LD: общая брендовая схема
- общие поля бренда и контактов собираются через helper в `app/Helpers/functions.php`
- для `caricature` используется `WebPage` + `BreadcrumbList` JSON-LD в head, а тело страницы остаётся с `Product` microdata
- `about` использует `site_brand_schema()` с `areaServed` по странам
- `/lv/simpsons` и `/lv/new/graphic-portrait/portrait-historical` тоже используют `og:type=product` и page-level JSON-LD в head
- `/lv/collage`, `/lv/new/graphic-portrait/portrait-oil` и `/lv/modular-generator` тоже переведены на `og:type=product` и page-level JSON-LD в head
- `graphic_portrait.new_page` теперь тоже получает `og:type=product` и `og:image` из активного `portrait_slider`
- gallery item pages используют тот же подход: product microdata в body, page-level JSON-LD и один чистый breadcrumb signal в head

## Карта og:image

- `/sizesprices` отдаёт `og:image` через `/images_single/sizesprices.png?v=...`; реальный источник: `CanvasSlider.size_img`, затем `CanvasSlider.image`
- `/new/canvas` использует стабильный маршрут `/images_single/canvas.png`, который динамически отдаёт прежний canvas image source: `CanvasSlider.size_img`, затем `CanvasSlider.image`
- `/collage` отдаёт `og:image` через `/images_single/collage.png?v=...`; реальный источник: `ACollageSlider.size_img`, затем локализованная картинка слайда
- для товарных страниц портретного блока `og:image` отдаётся через `/images_single/portrait-{id}.png?v=...`; реальный источник: активный `portrait_slider.size_img/png`, затем `GalleryItem->sliderImage`
- `/new/caricature`, `/new/caricature/{pageslug}`, `/simpsons` и `/new/graphic-portrait/portrait-historical` отдают `og:image` через `/images_single/product-{id}.png?v=...`; реальный источник: `portrait_slider.size_img/png`, затем media товара `our_works_new` -> `background` -> `new_main_image`
- `/new/graphic-portrait/portrait-oil` отдаёт `og:image` через `/images_single/product-oil-{id}.png?v=...`; реальный источник: первый активный `portrait_slider.png`, затем общий product source, затем `works_examples_new`
- `/modular-generator` отдаёт `og:image` через `/images_single/module-generator.png?v=...`; реальный источник: `theme/viar/images/module-generator/main.png`
- gallery item pages отдают `og:image` через `/images_single/gallery-item-{id}.png?v=...`; реальный источник: первая картинка товара из `images`, затем `new_main_image`
- если для страницы нет отдельного `og_image`, layout остаётся на `logo.png`
- локальная проверка показала, что часть старых storage-файлов отсутствует в текущей локальной копии (`portrait-slider/...`, `a-collage-slider/...`), поэтому соответствующие alias-URL локально могут отдавать 404 до синхронизации этих файлов; код при этом не подменяет источник на новую картинку

## SEO-заметка

Схема сделана консервативно:
- одна понятная product-сущность для страницы
- одна brand-сущность для сайта
- без дублирования breadcrumb schema
- без конфликта page-specific `LocalBusiness`
- для `caricature` отдельно убран шум из breadcrumb microdata и оставлен только один семантический путь в head

## Затронутые файлы

- `app/Http/Controllers/PortraitPageController.php`
- `resources/views/theme/viar/layouts/app.blade.php`
- `resources/views/theme/viar/partials/seo_city.blade.php`
- `resources/views/theme/viar/pages/canvas/breads.blade.php`
- `resources/views/partials/canvas_new/breads.blade.php`
- `app/Http/Controllers/Pages/SharjController.php`
- `app/Http/Controllers/Pages/SizespricesController.php`
- `resources/views/theme/viar/pages/portrait/breads.blade.php`
- `resources/views/about.blade.php`
