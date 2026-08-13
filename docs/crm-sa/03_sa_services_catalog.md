# GET /api/sa/services-catalog

## Purpose

Получение каталога услуг SA из сайта/CRM.

## Query Params

| Param | Required | Type | Example | Notes |
|---|---|---|---|---|
| `lang` | no | enum | `ru` | Dynamic from project locales (`laravellocalization.supportedLocales`) + required `ru|en` |
| `updated_since` | no | datetime | `2026-02-10T00:00:00Z` | Delta load |
| `include_inactive` | no | bool | `false` | Include disabled services |
| `client_phone` | no | string | `+37129999999` | If `country_code` is not passed, pricing country can be inferred from phone prefix |
| `phone` | no | string | `+37129999999` | Alias to `client_phone` |

## Request Example

```http
GET /api/sa/services-catalog?lang=ru&include_inactive=false
```

## Response Shape

| Field | Required | Type | Example |
|---|---|---|---|
| `status` | yes | string | `ok` |
| `meta.currency` | yes | string | `UAH` |
| `meta.generated_at` | yes | datetime | `2026-02-12T16:00:00Z` |
| `meta.version` | yes | string | `2026-02-12_1` |
| `data.categories[]` | yes | array | `[{id,name,order}]` |
| `data.services[]` | yes | array | `[{id,category_id,name,...}]` |
| `data.bundles[]` | no | array | `[{id,name,items,...}]` |

## Test cases

1. Запрос без параметров -> `200`, полный активный каталог.
2. `lang=ru` -> локализованные поля возвращены на русском.
3. `lang=ee` и `lang=en` -> корректная локализация.
4. `updated_since=<valid>` -> только измененные записи.
5. `include_inactive=false` -> неактивные услуги скрыты.
6. `include_inactive=true` -> неактивные услуги присутствуют.
7. Если `country_code` не передан, но передан `client_phone`, pricing country определяется по префиксу номера.
8. Некорректный `lang` -> `400 VALIDATION_ERROR`.
9. Неавторизованный запрос (`X-Api-Key`) -> `401/403`.
