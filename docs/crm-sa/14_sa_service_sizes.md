# GET /api/sa/services/{service_id}/sizes

## Purpose

Return available sizes and base price list for a specific service from real DB sources.

## Auth

- Header: `X-Api-Key: <key>`

## Path Params

| Param | Required | Type | Example | Notes |
|---|---|---|---|---|
| `service_id` | yes | string | `HM-2` | Format: `HM-{header_menu.id}` |

## Query Params

| Param | Required | Type | Example | Notes |
|---|---|---|---|---|
| `country_code` | no | string | `LV` | Pricing multiplier source: `country_tels.price_country_mltpr` |
| `country` | no | string | `FI` | Alias to `country_code` |

## Request Example

```http
GET /api/sa/services/HM-2/sizes?country_code=LV
X-Api-Key: test-key
```

## Response Example

```json
{
  "status": "ok",
  "meta": {
    "currency": "EUR",
    "country_code": "LV",
    "country_multiplier": 1
  },
  "data": {
    "service_id": "HM-2",
    "service_path": "/new/canvas",
    "sizes": [
      {
        "size": "30x40",
        "width": 30,
        "height": 40,
        "format": "portrait",
        "price": {
          "type": "fixed",
          "amount": 15,
          "original_amount": 45,
          "is_discounted": true
        }
      }
    ]
  }
}
```

## Source Matrix

- `/new/canvas` -> `canvas_header.sizes_*`
- `/collage` -> `a_collage_head.sizes_*`
- `/new/caricature` -> `gallery_items.slug=caricature`, fallback `is_sharj=1`
- `/simpsons` -> `gallery_items.slug=simpsons-portrait`, fallback `slug=simpsons`
- `/new/graphic-portrait/{slug}` -> `gallery_items.slug={slug}`
- `/modular-generator` -> `gallery_items.id_type=2`
- `/new/gallery` -> `gallery_items.id_type in (2,3,4)`

## Errors

- `404 SERVICE_NOT_FOUND` when `service_id` cannot be resolved to `header_menu`.
- `401/403` unauthorized on invalid or missing `X-Api-Key`.
- `400 VALIDATION_ERROR` invalid params.

---

# GET /api/sa/services/{service_id}/price-by-size

## Purpose

Return exact price for one requested size.

## Path Params

Same as `/sizes`.

## Query Params

| Param | Required | Type | Example | Notes |
|---|---|---|---|---|
| `size` | yes | string | `40x60` | Format: `WxH` |
| `country_code` | no | string | `LV` | Optional multiplier |
| `country` | no | string | `FI` | Alias |

## Request Example

```http
GET /api/sa/services/HM-2/price-by-size?size=40x60&country_code=FI
X-Api-Key: test-key
```

## Response Example

```json
{
  "status": "ok",
  "meta": {
    "currency": "EUR",
    "country_code": "FI",
    "country_multiplier": 1.3
  },
  "data": {
    "service_id": "HM-2",
    "service_path": "/new/canvas",
    "size": "40x60",
    "width": 40,
    "height": 60,
    "format": "portrait",
    "price": {
      "type": "fixed",
      "amount": 28.6,
      "original_amount": 84.5,
      "is_discounted": true
    }
  }
}
```

## Discount Price Notes

1. Для обычных товаров сайт хранит текущую и старую цену:
   - в constructor-like товарах обе цены могут быть закодированы прямо в size-строке, например `40x60[65-22]`;
   - в gallery-item flow скидочная цена может приходить из отдельного sale-source.
2. Поэтому API теперь возвращает:
   - `price.amount` -> текущая цена;
   - `price.original_amount` -> старая цена, если есть скидка;
   - `price.is_discounted` -> признак скидки.
3. Если скидки нет:
   - `original_amount = null`
   - `is_discounted = false`

## Errors

- `404 SERVICE_NOT_FOUND`
- `404 SIZE_NOT_FOUND`
- `400 VALIDATION_ERROR`
- `401/403` unauthorized
