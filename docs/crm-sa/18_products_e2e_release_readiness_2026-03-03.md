# SA Integration Release Readiness (Products E2E)

Date: `2026-03-03`  
Environment: `https://viarcanvas.loc` (local test)

## Scope

- End-to-end verification for all services from `GET /api/sa/services-catalog`.
- Flow per service: create lead/order -> persist item/price -> inbound/outbound chat -> stage chain to `STG-50` -> final `completed`.

## Final Result

- Coverage: `12/12` services
- Status: `PASS`
- Critical blockers: none

## Verified Services and Orders

1. `HM-2` CANVAS PRINTING -> `126998`, `126999`, `127000`
2. `HM-3` COLLAGE ON CANVAS -> `127002`
3. `HM-43` MODULAR CANVAS -> `127005`
4. `HM-44` CANVAS CATALOG -> `127016`
5. `HM-27` CUSTOM CARICATURE -> `127015`
6. `HM-29` CUSTOM ART PORTRAIT -> `127017`
7. `HM-33` PORTRAIT CLASSIC -> `127018`
8. `HM-34` PORTRAIT POP ART -> `127019`
9. `HM-45` ACRYLIC PORTRAIT -> `127020`
10. `HM-47` SIMPSONS -> `127021`
11. `HM-48` CUSTOMS ROYAL PORTRAITS -> `127022`
12. `HM-49` PET PORTRAITS -> `127023`

## What Was Checked (per service)

1. `C1` Lead creation via `POST /api/sa/leads`
2. `C2` Real order created in `orders`
3. `C3` Service item persisted in `orders.items`
4. `C4` Price consistency (catalog/size logic -> persisted item/order price)
5. `C5` Chat sync (`sa_messages` + `order_user_comments`)
6. `C6` Stage transitions `STG-10 -> STG-20 -> STG-30 -> STG-40 -> STG-50`
7. `C7` Final order status `completed`

## Important Fixes Included in This Cycle

1. Non-canvas `terms_price` gap fixed for generic SA service items (enabled stable HM-3 flow).
2. Size-based pricing for non-canvas services with size maps fixed (`resolveServiceToBasket`), validated on HM-43/HM-44.
3. Canvas defaults and admin card display normalization previously aligned and preserved.

## Completeness Retest Result

1. `HM-44` and portrait group were retested after payload enrichment in `resolveServiceToBasket`.
2. New orders include richer `orders.items` structures required for admin display consistency:
   - portrait fields (`is_port_product`, `pack`, `terms`, `size_name`, `users_count`, etc.),
   - gallery fields (`size_name`, `pack`, `terms`, `boxIds`, `show`, `total_item_price`, etc.).

## Post-Deploy Smoke (5-7 min)

1. `GET /api/sa/services-catalog?lang=en&include_inactive=false&country_code=LV` returns `status=ok` and expected services.
2. Create one lead for a size-based service (`HM-43` or `HM-44`) with `service_request.options.size`; verify persisted item price equals `price-by-size`.
3. Send one inbound SA message with attachment; verify chat line appears in order.
4. Send one outbound CRM message; verify second chat line appears.
5. Move stage from `STG-10` to `STG-50`; verify final order status `completed`.

## References

1. Detailed per-product log: `docs/crm-sa/17_product_test_log.md`
2. Main implementation timeline: `docs/crm_sa_implementation_plan.md`

## Revalidation (Post-migration)

- Additional full rerun on real DB completed on `2026-03-03` after migration apply.
- New rerun order ids:
  - `HM-2` `16950`, `HM-3` `16961`, `HM-43` `16951`, `HM-44` `16952`,
  - `HM-27` `16953`, `HM-29` `16954`, `HM-33` `16955`, `HM-34` `16956`,
  - `HM-45` `16957`, `HM-47` `16958`, `HM-48` `16959`, `HM-49` `16960`.
- Result remains `PASS` (`12/12`).
