# CRM-SA Real Data Source Audit (2026-03-02)

## Goal

Original Laravel 6 code location confirmed by user on 2026-09-05:
`C:\OSPanel\domains\asoft\viar` (read-only source for business-logic comparisons).
Migration changes belong in `G:\OSPanel\home\viar13`; do not use `viar_filament`.
Determine from existing code and DB which real tables/statuses should be used instead of static hardcoded data in SA integration endpoints.

## Confirmed Existing Tables (DB: `viar`)
1. `orders` - canonical lead/order entity (`lead_id` = `orders.id`).
2. `order_user_comments` - real "chat with client" stream used in admin/client UI.
3. `sa_events`, `sa_conversations`, `sa_messages`, `sa_escalations`, `sa_bot_controls` - SA integration event/message/audit storage.
4. `newhome_services` + `translations` (`table_name = 'newhome_services'`) - real multilingual service catalog source currently used across site pages.

## Confirmed Existing Order Statuses (`orders.status`)
Observed in DB (2026-03-02):
1. `completed`
2. `pegging`
3. `watching`
4. `new`
5. `sended`
6. `in_production`
7. `send_lubanas`

Observed in code (admin/UI/controllers):
1. `watching`
2. `pegging`
3. `in_production`
4. `sended`
5. `send_lubanas`
6. `completed`
7. `new` (temporary/integration order creation path)

## What Is Static Right Now (Needs Refactor)
File: `app/Http/Controllers/Api/SaIntegrationController.php`

1. `GET /api/sa/services-catalog` currently reads from `buildCatalog()` hardcoded dictionary and hardcoded categories/services/bundles.
2. Stage mapping and transition order are hardcoded in:
   - `mapStageIdToOrderStatus()`
   - `mapOrderStatusToStageId()`
   - `isAllowedStageTransition()`
3. Some integration response IDs/URLs are synthetic placeholders (`SA-MSG-...`, `crm.example.com`).

## Conclusion for Implementation
1. Do not treat `sa_service_catalog*` and `sa_stage_mappings` as already existing - they are not present in current DB.
2. For immediate real-data implementation without introducing new catalog tables:
   - use `newhome_services` + `translations` as source for `/api/sa/services-catalog`;
   - keep pricing policy explicit (no native price in `newhome_services` table).
3. For stage mapping, source of truth is existing `orders.status` set and current business flow in orders/admin UI.
4. If external CRM requires custom pipeline/stage IDs and strict transition matrix, then add dedicated mapping tables in a separate migration; otherwise keep mapping in config and document it.

## Decision Snapshot
1. Real source for services in current project: `newhome_services` (+ `translations`).
2. Real source for lead stage status domain: `orders.status` values above.
3. Dedicated SA catalog/mapping tables are optional extension, not a pre-existing dependency.

## Update 2026-03-02 (Menu-driven Services)
For "services as shown in site header dropdown", the real source is `header_menu` (via `App\\Models\\HeaderMenu`), not `newhome_services`.

Used fields:
1. `title` (localized through `translations`)
2. `link` (localized through `translations`)
3. `png` (menu image)
4. `menu_pos` (group/category in header columns)
5. `is_show` (active visibility)

Note:
`header_menu` does not contain price/duration/bundle economics, so pricing must be mapped from another source or returned as nullable/default values in SA catalog contract.

## Source Matrix (Menu -> Data/Price)

### A. Menu item source (display)
1. Table: `header_menu`
2. Model: `App\\Models\\HeaderMenu`
3. Translation source: `translations` (`table_name='header_menu'`, columns `title`, `link`)
4. Grouping: `menu_pos` (header columns), visibility: `is_show`, image: `png`.

### B. Price source by route pattern
1. `/new/graphic-portrait/{slug}` and `/simpsons`, `/new/caricature`
   - base source: `gallery_items` by `slug`
   - price fields: `price_from`, `custom_size_prices`, `custom_users_prices`
   - examples in DB: `graphic-portrait`, `portrait-dream-art`, `pop-art-portrait`, `kartiny`, `portrait-historical`, `pet-portrait`, `simpsons-portrait`, `caricature`.
2. `/new/canvas`
   - page pricing source: `canvas_header` (`sizes_30x40`, `sizes_38x38`, `sizes_40x30`, ...)
   - extra descriptive pricing block: `canvas_prices`.
3. `/collage`
   - page pricing source: `a_collage_head` (`sizes_30x40`, `sizes_38x38`, `sizes_40x30`).
4. `/modular-generator`
   - calculator/catalog pricing is tied to basket/product configuration tables (not `header_menu`), primarily through gallery/canvas option data and basket calculation flow.
5. `/new/gallery`
   - catalog listing with item-level prices comes from `gallery_items.price_from` and per-item option fields.

### C. Practical implication for SA `services-catalog`
1. Service names/links/images should be read from `header_menu`.
2. Price extraction must be route-aware (different table families per route type).
3. No single universal "service price" table currently exists for all menu services.
