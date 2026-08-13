# Header Menu Links Audit (2026-03-02)

## Scope
Per-link study of active `header_menu` entries (`is_show=1`) with route/controller binding and actual data/price source.

## Link-by-Link Results
1. `ШАРЖ / КАРИКАТУРА`
- URL: `/new/caricature`
- Route/Controller: `routes/web.php` -> `SharjController@index`
- Entity source: `gallery_items` (`slug=caricature`, `id=1058`, `id_type=8`)
- Price source: `gallery_items.custom_size_prices`, `gallery_items.custom_users_prices`, fallback `price_from=45`

2. `АРТ ПОРТРЕТ`
- URL: `/new/graphic-portrait/portrait-dream-art`
- Route/Controller: `routes/web.php` -> `PortraitPageController@hbindex`
- Entity source: `gallery_items` (`slug=portrait-dream-art`, `id=16`, `id_type=5`)
- Price source: `gallery_items.custom_size_prices`, `gallery_items.custom_users_prices`, fallback `price_from=45`

3. `ПОРТРЕТ КЛАССИЧЕСКИЙ`
- URL: `/new/graphic-portrait/graphic-portrait`
- Route/Controller: `routes/web.php` -> `PortraitPageController@hbindex`
- Entity source: `gallery_items` (`slug=graphic-portrait`, `id=15`, `id_type=5`)
- Price source: `gallery_items.custom_size_prices`, `gallery_items.custom_users_prices`, fallback `price_from=55`

4. `ПОРТРЕТ ПОП АРТ`
- URL: `/new/graphic-portrait/pop-art-portrait`
- Route/Controller: `routes/web.php` -> `PortraitPageController@hbindex`
- Entity source: `gallery_items` (`slug=pop-art-portrait`, `id=17`, `id_type=5`)
- Price source: `gallery_items.custom_size_prices`, `gallery_items.custom_users_prices`, fallback `price_from=45`

5. `ПОРТРЕТ АКРИЛОМ`
- URL: `/new/graphic-portrait/kartiny`
- Route/Controller: `routes/web.php` -> `PortraitPageController@hbindex`
- Entity source: `gallery_items` (`slug=kartiny`, `id=1053`, `id_type=5`)
- Price source: `gallery_items.custom_size_prices`, `gallery_items.custom_users_prices`, fallback `price_from=55`

6. `СИМПСОНЫ`
- URL: `/simpsons`
- Route/Controller: `routes/web.php` -> `SimpsonsController@index`
- Entity source: `gallery_items` (`slug=simpsons-portrait`, `id=1055`, `id_type=5`)
- Price source: `gallery_items.custom_size_prices`, `gallery_items.custom_users_prices`, fallback `price_from=50`

7. `ПОРТРЕТ В ОБРАЗЕ-КОРОЛЕВСКИЙ`
- URL: `/new/graphic-portrait/portrait-historical`
- Route/Controller: `routes/web.php` -> `PagePortraitRoyalController@index`
- Entity source: `gallery_items` (`slug=portrait-historical`, `id=1056`, `id_type=5`)
- Price source: `gallery_items.custom_size_prices`, `gallery_items.custom_users_prices`, fallback `price_from=50`

8. `ПОРТРЕТ ЖИВОТНОГО`
- URL: `/new/graphic-portrait/pet-portrait`
- Route/Controller: `routes/web.php` -> `PortraitPageController@hbindex`
- Entity source: `gallery_items` (`slug=pet-portrait`, `id=1054`, `id_type=5`)
- Price source: `gallery_items.custom_size_prices`, `gallery_items.custom_users_prices`, fallback `price_from=20`

9. `ФОТО НА ХОЛСТЕ / КАНВАС`
- URL: `/new/canvas`
- Route/Controller: `routes/web.php` -> `PortraitPageController@hbcanvas`
- Entity source: `canvas_header` + canvas domain tables
- Price source: `canvas_header.sizes_30x40`, `sizes_38x38`, `sizes_40x30` (country multiplier applied in controller)

10. `КОЛЛАЖ НА ХОЛСТЕ`
- URL: `/collage`
- Route/Controller: `routes/web.php` -> `PortraitPageController@hbcollage`
- Entity source: `a_collage_head` + collage domain tables
- Price source: `a_collage_head.sizes_30x40`, `sizes_38x38`, `sizes_40x30` (country multiplier applied in controller)

11. `МОДУЛЬНЫЕ КАРТИНЫ`
- URL: `/modular-generator`
- Route/Controller: `routes/web.php` -> `ModulegeneratorController@index`
- Entity source: `GalleryItem::getItems(type=2)` + modular tables
- Price source: `gallery_items.price_from` (plus basket/options calculation)

12. `КАТАЛОГ КАРТИН`
- URL: `/new/gallery`
- Route/Controller: `routes/web.php` -> `GalleryController@hb_render`
- Entity source: `gallery_types` + `gallery_items`
- Price source: `gallery_items.price_from` (sorting/filtering; detailed option price in basket flow)

## Conclusion
1. Menu display source is fully `header_menu` (+ `translations`).
2. Price source is route-specific and heterogeneous; no single universal table covers all menu links.
3. For SA catalog adapter, service list should come from `header_menu`, while price must be resolved by route strategy.
