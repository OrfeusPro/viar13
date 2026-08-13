# Product Test Log (SA -> CRM)

## Legend

- `planned`: not started
- `in_progress`: running now
- `done`: passed
- `failed`: failed (needs fix/retest)

## Run Log

| Service ID | Name | Status | Date | Order ID | Checks | Notes |
|---|---|---|---|---|---|---|
| `HM-2` | CANVAS PRINTING | done | 2026-03-03 | `126998`, `126999`, `127000` | create/order item/price/chat/stage/defaults/localization | baseline reference; delivery override also verified |
| `HM-3` | COLLAGE ON CANVAS | done | 2026-03-03 | `127002` | C1,C2,C3,C4,C5,C6,C7 | first run `127001` failed (empty items, `terms_price` missing in generic service item), fixed and retested |
| `HM-43` | MODULAR CANVAS | done | 2026-03-03 | `127005` | C1,C2,C3,C4,C5,C6,C7 | first run `127004` exposed C4 gap (size price ignored); fixed in `resolveServiceToBasket` and retested |
| `HM-44` | CANVAS CATALOG | done | 2026-03-03 | `127016` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix: rich gallery-like item persisted (`size_name`,`pack`,`terms`,`boxIds`,`show`,`orig_images`,`total_item_price`) |
| `HM-27` | CUSTOM CARICATURE | done | 2026-03-03 | `127015` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix: portrait fields persisted (`is_port_product`,`forma_id`,`users_count`,`holst_id`,`hud_of`,`compl_id`,`pack`,`terms`,`orig_images`) |
| `HM-29` | CUSTOM ART PORTRAIT | done | 2026-03-03 | `127017` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix with portrait payload |
| `HM-33` | PORTRAIT CLASSIC | done | 2026-03-03 | `127018` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix with portrait payload |
| `HM-34` | PORTRAIT POP ART | done | 2026-03-03 | `127019` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix with portrait payload |
| `HM-45` | ACRYLIC PORTRAIT | done | 2026-03-03 | `127020` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix with portrait payload |
| `HM-47` | SIMPSONS | done | 2026-03-03 | `127021` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix with portrait payload |
| `HM-48` | CUSTOMS ROYAL PORTRAITS | done | 2026-03-03 | `127022` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix with portrait payload |
| `HM-49` | PET PORTRAITS | done | 2026-03-03 | `127023` | C1,C2,C3,C4,C5,C6,C7 | retest after completeness fix with portrait payload |

## Common Checklist (mark per row in Notes)

- `C1` lead created (API 200)
- `C2` real order created in `orders`
- `C3` item persisted in `orders.items`
- `C4` item + order pricing consistent
- `C5` inbound + outbound chat persisted
- `C6` stage transitions valid up to `STG-50`
- `C7` final status `completed`

## Revalidation Run (Post-migration, real DB)

Date: `2026-03-03`

- Full 12/12 E2E rerun executed after migration restore/apply.
- New verification order ids:
  - `HM-2` -> `16950`
  - `HM-3` -> `16961`
  - `HM-43` -> `16951`
  - `HM-44` -> `16952`
  - `HM-27` -> `16953`
  - `HM-29` -> `16954`
  - `HM-33` -> `16955`
  - `HM-34` -> `16956`
  - `HM-45` -> `16957`
  - `HM-47` -> `16958`
  - `HM-48` -> `16959`
  - `HM-49` -> `16960`

- Outcome:
  - all services passed `C1..C7`.
  - chat counters validated (`sa_messages=2`, `order_user_comments=2`) for each rerun script.
  - final status for each rerun order: `completed`.
