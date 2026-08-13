# Product E2E Plan (Canvas as Reference)

## Scope

- Goal: run SA -> CRM E2E checks for each product from `/api/sa/services-catalog`.
- Reference product: `HM-2` (Canvas Printing).
- Environment: `https://viarcanvas.loc`, local DB.

## Product List (current catalog, LV)

| Service ID | Name | Path | Type |
|---|---|---|---|
| `HM-27` | CUSTOM CARICATURE | `/new/caricature` | portrait |
| `HM-29` | CUSTOM ART PORTRAIT | `/new/graphic-portrait/portrait-dream-art` | portrait |
| `HM-33` | PORTRAIT CLASSIC | `/new/graphic-portrait/graphic-portrait` | portrait |
| `HM-34` | PORTRAIT POP ART | `/new/graphic-portrait/pop-art-portrait` | portrait |
| `HM-45` | ACRYLIC PORTRAIT | `/new/graphic-portrait/kartiny` | portrait |
| `HM-47` | SIMPSONS | `/simpsons` | portrait |
| `HM-48` | CUSTOMS ROYAL PORTRAITS | `/new/graphic-portrait/portrait-historical` | portrait |
| `HM-49` | PET PORTRAITS | `/new/graphic-portrait/pet-portrait` | portrait |
| `HM-2` | CANVAS PRINTING | `/new/canvas` | canvas |
| `HM-3` | COLLAGE ON CANVAS | `/collage` | collage |
| `HM-43` | MODULAR CANVAS | `/modular-generator` | modular |
| `HM-44` | CANVAS CATALOG | `/new/gallery` | gallery |

## Unified E2E Flow (per product)

1. Create lead/order via `POST /api/sa/leads` with `service_request.service_id`.
2. Verify real order created (`orders.id`) and item exists in `orders.items`.
3. Verify item structure:
   - common: `name`, `price`, `sumPrice`, `count`
   - size-related: `sizeId` or equivalent product-specific field
   - display block values visible in admin card.
4. Verify pricing:
   - item price
   - `orders.price/sale_price`
   - delivery and total.
5. Upload client media/message via `POST /api/sa/webhooks/messages` (`inbound` + attachment).
6. Send outbound admin reply via `POST /api/crm/webhooks/send-message`.
7. Move pipeline stages: `STG-10 -> STG-20 -> STG-30 -> STG-40 -> STG-50`.
8. Verify final order status chain: `watching -> in_work -> in_production -> sended -> completed`.
9. Verify chat persistence:
   - `sa_messages`
   - `order_user_comments`.
10. Record result in log (`pass/fail`, order id, notes).

## Product-Specific Checks

- `canvas` (`HM-2`):
  - defaults: canvas/packaging/decoration/improvement/execution/terms
  - localized display values by country locale.
- `collage` (`HM-3`):
  - collage-specific structure and terms.
- `modular` (`HM-43`):
  - modular sizes/format and rendering fields.
- `gallery` (`HM-44`):
  - fallback/minimum price logic and display integrity.
- portraits (`HM-27/29/33/34/45/47/48/49`):
  - single-item service mapping correctness and stage/chat lifecycle.

## Execution Order

1. `HM-2` (baseline, already validated).
2. `HM-3`, `HM-43`, `HM-44` (complex non-portrait products).
3. Portrait services (`HM-27`, `HM-29`, `HM-33`, `HM-34`, `HM-45`, `HM-47`, `HM-48`, `HM-49`).

## Exit Criteria

- All 12 services have at least one E2E run in log.
- No critical failures in:
  - order creation,
  - pricing persistence,
  - status transitions,
  - chat sync.
