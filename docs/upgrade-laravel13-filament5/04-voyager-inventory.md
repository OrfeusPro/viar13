# 04. Полная инвентаризация Voyager

## Метаданные БД

| Объект | Количество | Значение |
|---|---:|---|
| `data_types` | 133 | BREAD-модули |
| `data_rows` | 1 997 | поля, validation/options/relationships/order |
| `menus` / `menu_items` | 3 / 166 | admin 138, footer 14, header 14 |
| `roles` | 8 | admin, user, painter, manager, Admin 2, printing, lite_manager, seo_manager |
| `permissions` | 675 | Voyager permissions |
| `permission_role` | 2 080 | фактическая матрица доступа |
| `settings` | 27 | runtime content/config через `setting()` |

Семь `data_types` используют server-side browsing. Шесть имеют custom controller: `users`, `roles`, `locales`, `orders`, `image_alt_suggestions`, `seo_meta_suggestions`. Две model/table-ссылки не разрешаются: `canvas → App\Models\Canva`, `a_collag_faq → App\Models\ACollagFaq`; рядом с последней существует корректный `a_collage_faq`. Полная L2-матрица 133 строк находится в `appendix-voyager-bread-modules.csv`.

## Все BREAD, сгруппированные по будущим модулям

### Core / доступы

`users`, `menus`, `roles`, `locales`, `pages`, `settings` (отдельная таблица Voyager), `user_types`, `user_messages`, `address`.

### Заказы / CRM / commerce

`orders`, `order_action`, `order_strings`, `order_payment_requests` (custom routes/UI, не BREAD в текущем списке), `stocks`, `coupons`, `gift_card`, `gift_card_noms`, `basket_strings`, `country_tels`, `a_order_from`, `a_painter_images_status`, `a_production_time`, `delivery_pickup_at_viar_workshop`, `payment_Info`, `a_delivery_towns`, `abandoned_carts`, `reviews`, `venipak_data`.

### Gallery / catalogue

`gallery_types`, `gallery_categories`, `gallery_sizes`, `gallery_holsts`, `gallery_boxes`, `gallery_tag_rooms`, `gallery_tag_colors`, `gallery_items`, `gallery_decorations`, `gallery_page`, `gallery_filters_page`, `gall_globs`, `our_works`, `a_gallery_genre`, `a_gallery_style`, `a_gallery_nationality`, `a_gallery_age`.

### Canvas / portraits / generators

`canvas`, `canvas_header`, `canvas_what`, `canvas_req`, `canvas_prices`, `canvas_work_serv`, `canvas_del_time`, `canvas_interiers`, `canvas_rams`, `canvas_global`, `canvas_comp_steps_arts_packs`, `canvas_new`, `canvas_slider`, `canvas_rams_colors`, `canvas_rams_materials`, `canvas_photo_improvements`, `portrait_slider`, `port_before_after`, `portraits_hard`, `port_prev_items`, `graph_por_styl_page`, `oil_header`, `oil_what_req`.

### Collage / modular / style pages

`collage_header`, `collage_constructor`, `collage_popular_items`, `modular_pics_head`, `modular_pics_what_size_price`, `mod_gall_page`, `mod_photo_page`, `mod_repr_page`, `slider_mod`, `slider_photo`, `slider_repr`, `family_constructor`, `styl_page`, `styl_page_port_obr`, `styl_page_port_goup_obr`, `styl_page_info`, `sharj_work_ex`, `sharj_work_group_ex`, `all_styles_page`, `all_styles`, `all_style_form`, `all_style_form_step_items`, `a_collage_slider`, `a_collage_advantage_screen`, `a_collage_popular_screen`, `a_collage_order_screen`, `a_collag_faq`, `a_collage_faq`, `a_collage_why_screen`, `a_collage_generator_colors`, `a_collage_fons`, `a_collage_sticker_group`, `a_collage_stiker`, `a_collage_head`.

### Homepage / navigation / content

`homepage_options`, `homepage_topslides`, `newhome_top_slider`, `newhome_services`, `newhome_work_ex`, `newhome_top_work_ex`, `header_menu`, `footer_menu`, `glob_config`, `creeping_line`, `page_faq`, `page_faq_desc`, `page_partnership`, `page_slugs`, `why_are_you_leaving_questions`, `our_working_process`, `our_team`, `a_mail_top_sale`, `send_rev`.

### Blog / SEO / tools

`blog_posts`, `blog`, `blog_categories`, `blog_categories_short_blocks`, `blog_reklama_type`, `blog_reklama`, `blog_authors`, `seo_city`, `site_images`, `image_alt_suggestions`, `seo_meta_suggestions`, `string_tranlations`, `string_tranlations2`, `string_tranlations3`.

## Custom слой

`resources/views/vendor/voyager` содержит 40 файлов: глобальные BREAD browse/edit/read/actions/order, dashboard/sidebar/master, multilingual selector/orders, custom relationship field, users, gallery-items, blog-posts, email sender, Facebook coupon, courier, orders/chat/payment/invoice/painter, SEO/ALT pages. Они должны рассматриваться как отдельные use cases, а не шаблоны, которые можно удалить.

`routes/admin.php` содержит 68 static route declarations. Из них 52 объявлены после `Voyager::routes()` и не входят во внутреннюю группу `admin.user`; их фактическая защита неоднородна и разобрана в FN-06/FN-12. Основные контроллеры: `OrdersController`, `Admin\OrdersController`, `Admin\AdminSaIntegrationController`, `Admin\Api\VinepakApiController`, `Voyager\AltSuggestionController`, `Voyager\SeoMetaSuggestionController`, `Voyager\SeoMetaGenerationController`, `AdminLocaleController`.

`orders` указывает на custom controller, который реализует `index/show/update`, но не полный resource/action contract Voyager (`create/store/edit/destroy/restore/order/relation/remove_media`). Поэтому target orders — специализированная Page/Actions, не generic Resource.

## Translations / uploads / settings

- Voyager Translatable хранит значения в общей `translations` по `(table_name,column_name,foreign_key,locale)`; уникальный composite index в аудите не подтверждён и должен быть проверен/добавлен только отдельной миграцией после поиска дублей.
- `config/voyager.php` включает multilingual для `ru,en,lv,ee,lt,pl,de`.
- Из 64 779 translation rows только 51 416 относятся к BREAD tables; 13 003 переводят `data_rows/data_types/menu_items`, ещё 360 принадлежат отсутствующим legacy tables `modular_pics/graph_port_pages/style` и требуют quarantine/owner decision.
- В `translations` есть 6 строк `et` для `canvas_photo_improvements`, хотя активный application locale — `ee`; их нельзя молча нормализовать или удалить.
- media manager допускает MIME `*`; custom `ContentTypes\Image` пишет на Voyager disk и создаёт варианты изображений.
- `setting()` вызывается в app/views; 27 значений нужно перенести в типизированный Filament Settings UI или оставить read adapter на той же таблице.

### Переводы в работающем production

Во время миграции редакторы продолжают менять переводы через Voyager. Поэтому snapshot 64 779 строк является только контрольной точкой, а не финальным набором для импорта.

- legacy production `translations` и базовые поля translatable tables являются system of record;
- для каждого переносимого BREAD назначается write-owner: Voyager или Filament;
- до переключения модуля Filament может читать актуальные данные, но не должен параллельно редактировать те же записи без conflict/version policy;
- delta-аудит включает insert/update/delete translation rows, изменения base English fields, slug/link/order/show flags и удаление parent record;
- сравниваются counts по `locale/table_name/column_name`, composite keys и content hashes; отдельно проверяются строки, изменённые после последнего snapshot;
- после переключения модуль остаётся доступен в Voyager только read-only до окончания rollback window.

## Что сохраняется после отключения Voyager

Немедленно не удалять `users`, `roles`, `permissions`, `permission_role`, `translations`, `settings`, `menus/menu_items`, `data_types/data_rows`. Первые семь могут оставаться runtime-источником. `data_types/data_rows` становятся архивным manifest для сверки и удаляются только после двух релизов без обращений и backup export. Media files и пути не перемещать одновременно с админкой.

## Волны переноса

1. read-only справочники и простые content Resources;
2. локализованные homepage/catalog Resources;
3. users/roles/permissions и settings;
4. blog/gallery/SEO/media;
5. orders + chat + payments + Venipak + SA как отдельный Cluster;
6. редкие generators/custom pages;
7. заморозка Voyager write, сверка, read-only окно, отключение.

Детальный аудит, risks, per-module tests и cutover gates: `29-voyager-bread-custom-admin-static-audit.md`.
