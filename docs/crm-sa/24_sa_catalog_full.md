# GET /api/sa/catalog-full

## Purpose

Получение полного каталога SA одним запросом:

- категории,
- сервисы,
- размеры,
- цены,
- главное фото товара,
- список фото товара.

Endpoint предназначен для полной синхронизации каталога на стороне SA без дополнительных запросов по каждому `service_id`.

## Query Params

| Param | Required | Type | Example | Notes |
|---|---|---|---|---|
| `lang` | no | enum | `ru` | Dynamic from project locales (`laravellocalization.supportedLocales`) + required `ru|en` |
| `updated_since` | no | datetime | `2026-02-10T00:00:00Z` | Delta load |
| `include_inactive` | no | bool | `false` | Include disabled services |
| `country` | no | string | `LV` | Alias to `country_code` |
| `country_code` | no | string | `LV` | Country context for prices |
| `client_phone` | no | string | `+37129999999` | If `country_code` is not passed, pricing country can be inferred from phone prefix |
| `phone` | no | string | `+37129999999` | Alias to `client_phone` |

## Request Example

```http
GET /api/sa/catalog-full?lang=ru&include_inactive=false&country_code=LV
```

## Response Shape

| Field | Required | Type | Example |
|---|---|---|---|
| `status` | yes | string | `ok` |
| `meta.currency` | yes | string | `EUR` |
| `meta.generated_at` | yes | datetime | `2026-04-01T12:00:00Z` |
| `meta.version` | yes | string | `2026-04-01_1` |
| `meta.country_code` | yes | string | `LV` |
| `meta.country_multiplier` | yes | number | `1` |
| `data.categories[]` | yes | array | `[{id,name,order}]` |
| `data.services[]` | yes | array | `[{id,category_id,name,...,price,sizes,photo,photos}]` |
| `data.bundles[]` | no | array | `[]` |

## Service Enrichment

Каждый элемент `data.services[]` дополнительно содержит:

| Field | Required | Type | Example | Notes |
|---|---|---|---|---|
| `sizes[]` | yes | array | `[{code,label,width,height,price}]` | Тот же прайсинговый контекст, что и в `GET /api/sa/services/{service_id}/sizes` |
| `price` | yes | object | `{type,amount,original_amount,is_discounted}` | Минимальная витринная цена услуги |
| `photo` | no | object/null | `{url,source,is_primary}` | Главное фото товара |
| `photos[]` | yes | array | `[{url,source,is_primary}]` | Доступные фото товара |

## Response Example

```json
{
  "status": "ok",
  "meta": {
    "currency": "EUR",
    "generated_at": "2026-04-01T12:00:00Z",
    "version": "2026-04-01_1",
    "country_code": "LV",
    "country_multiplier": 1
  },
  "data": {
    "categories": [
      {
        "id": "CAT-CANVAS",
        "name": "Canvas and photo products",
        "order": 20
      }
    ],
    "services": [
      {
        "id": "HM-2",
        "category_id": "CAT-CANVAS",
        "name": "CANVAS PRINTING",
        "price": {
          "type": "fixed",
          "amount": 15,
          "original_amount": 30,
          "is_discounted": true
        },
        "sizes": [
          {
            "size": "60x80",
            "width": 60,
            "height": 80,
            "format": "portrait",
            "price": {
              "type": "fixed",
              "amount": 38,
              "original_amount": 60,
              "is_discounted": true
            }
          }
        ],
        "photo": {
          "url": "https://example.com/storage/site-images/canvas/example.jpg",
          "source": "site_images",
          "is_primary": true
        },
        "photos": [
          {
            "url": "https://example.com/storage/site-images/canvas/example.jpg",
            "source": "site_images",
            "is_primary": true
          }
        ]
      }
    ],
    "bundles": []
  }
}
```

## Errors

| HTTP | `code` | Meaning |
|---|---|---|
| `400` | `VALIDATION_ERROR` | Invalid `lang`, `updated_since`, `include_inactive`, `country` or `country_code` |
| `401/403` | `UNAUTHORIZED` | Missing or invalid `X-Api-Key` |

## Notes

1. Endpoint uses the same real data sources as `GET /api/sa/services-catalog`.
2. Sizes and prices are calculated with the same country-aware logic as `GET /api/sa/services/{service_id}/sizes`.
3. `photo` is the preferred primary image; `photos[]` is the full media set available for the service.
4. If a service has no resolved image source, `photo` may be `null` and `photos[]` may be empty.
5. `price.amount` and `sizes[*].price.amount` are current витринные цены; при наличии скидки API также отдает `original_amount` и `is_discounted`.
6. Приоритет выбора pricing country:
   - `country_code`
   - `country`
   - `client_phone` / `phone`
   - fallback country (`LV`)

## Test cases

1. Request without params -> `200`, full active catalog with enriched services.
2. `lang=ru` -> localized categories and service names.
3. `country_code=LV` -> prices/sizes built with LV multiplier.
4. `include_inactive=false` -> inactive services hidden.
5. `updated_since=<valid>` -> only changed services returned.
6. Invalid `lang` -> `400 VALIDATION_ERROR`.
7. Unauthorized request -> `401/403`.
