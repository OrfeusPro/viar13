# Матрица видимых menu products -> SA API

Дата аудита: 2026-03-08

Источник истины:
- `header_menu`
- фильтр видимых позиций: `menu_pos IN (1,2)` и `is_show=1`

## Итого по menu

- Видимых product entries: `11`
- Скрытых product entries, которые есть в БД, но не видны в текущем menu: есть
- Ключевой скрытый продукт: `HM-44 /new/gallery`

## Видимые позиции

| Service ID | Menu title | Path | API builder | Тесты | Статус |
|---|---|---|---|---|---|
| HM-27 | CUSTOM CARICATURE | `/new/caricature` | `buildPortraitBasketFromServiceRequest()` | `t05_007` | OK |
| HM-29 | CUSTOM ART PORTRAIT | `/new/graphic-portrait/portrait-dream-art` | `buildPortraitBasketFromServiceRequest()` | shared portrait coverage | OK |
| HM-33 | PORTRAIT CLASSIC | `/new/graphic-portrait/graphic-portrait` | `buildPortraitBasketFromServiceRequest()` | shared portrait coverage | OK |
| HM-34 | PORTRAIT POP ART | `/new/graphic-portrait/pop-art-portrait` | `buildPortraitBasketFromServiceRequest()` | shared portrait coverage | OK |
| HM-45 | ACRYLIC PORTRAIT | `/new/graphic-portrait/kartiny` | `buildPortraitBasketFromServiceRequest()` | shared portrait coverage | OK |
| HM-47 | SIMPSONS | `/simpsons` | `buildPortraitBasketFromServiceRequest()` | shared portrait coverage | OK |
| HM-48 | CUSTOMS ROYAL PORTRAITS | `/new/graphic-portrait/portrait-historical` | `buildPortraitBasketFromServiceRequest()` | shared portrait coverage | OK |
| HM-49 | PET PORTRAITS | `/new/graphic-portrait/pet-portrait` | `buildPortraitBasketFromServiceRequest()` | shared portrait coverage | OK |
| HM-2 | CANVAS PRINTING | `/new/canvas` | `buildCanvasBasketFromServiceRequest()` | `t05_009` | OK |
| HM-3 | COLLAGE ON CANVAS | `/collage` | `buildCollageBasketFromServiceRequest()` | `t05_012` | OK |
| HM-43 | MODULAR CANVAS | `/modular-generator` | `buildModularBasketFromServiceRequest()` | `t05_013`, `t05_014`, `t05_015` | OK |

## Скрытые, но все еще существующие в базе

| Service ID | Menu title | Path | API builder | Статус |
|---|---|---|---|---|
| HM-44 | CANVAS CATALOG | `/new/gallery` | `buildGalleryCatalogBasketFromServiceRequest()` | Hidden in menu, supported by API |

## Замечания по parity с frontend

### HM-2 /new/canvas

- Реальный dedicated builder есть.
- Размер, упаковка, decoration, canvas type, photo improvement, production mode поддержаны.
- Покрытие хорошее.

### HM-3 /collage

- Реальный dedicated builder есть.
- Поддержаны `size`, `form_id/formId`, `holst_id`, `decor_id`, `compl_id`, `ram_id`, `production_mode`.
- Структура item приведена к реальному `construct`-формату сайта.

### HM-43 /modular-generator

- Dedicated builder есть.
- Create-lead теперь не generic, а `construct-style`.
- Поддержаны базовые option fields:
  - `size`
  - `form_id/formId`
  - `holst_id` (legacy-compatible finish key)
  - `packaging_id/compl_id`
  - `execution_id/executionId`
  - `production_mode`
  - `wall_size_mod`
- Exact pricing contract теперь есть:
  - `options.layout_svg` (preferred)
  - alias: `options.collageSvgImage`
  - JSON fallback: `options.layout_blocks[]`
- Без layout данных остается fallback mode.

### Portrait group

- Восемь видимых portrait-позиций идут через общий portrait builder.
- Для них parity считается достаточной по текущему scope.

## Что уже закрыто дополнительно

- Separate recipient:
  - `name_rec`
  - `last_name_rec`
  - `phone_rec`
  - `address_rec`
  - `postal_index_rec`
- B2B / invoice fields:
  - `ur_*`
- Modular custom layout payload:
  - `options.layout_svg`
  - alias `options.collageSvgImage`
  - `options.layout_blocks[]`
- Pricing contract:
  - `lead.pricing.coupon_code`
  - `lead.pricing.use_bonus`

## Что уже закрыто вне menu, но как отдельные site flows

- `gift-card` как отдельный orderable product:
  - добавлен synthetic service:
    - `GC-5`
  - в `services-catalog` теперь есть отдельная ветка подарочных карт;
  - реализованы:
    - nominal-based `sizes`
    - nominal-based `price-by-size`
    - `create-lead` с `basketType = 5`
    - online/email delivery rule
  - parity достаточный по текущему scope.

- `family-constructor` как отдельный orderable product:
  - добавлен synthetic service:
    - `FC-1`
  - в `services-catalog` теперь есть отдельный family-constructor flow;
  - реализованы:
    - size matrix из `family_constructor.sizes`
    - `price-by-size`
  - `create-lead` с construct-compatible basket item
  - canvas/packaging/terms mapping
  - parity достаточный по текущему scope.

- `HM-44 /new/gallery` как exact catalog item flow:
  - service-level entry сохранен;
  - добавлен exact contract:
    - `gallery_item_id`
    - alias `item_id`
    - alias `product_id`
  - реализованы:
    - exact `sizes`
    - exact `price-by-size`
    - `create-lead` с `pid = gallery_items.id`
    - exact `custom_size_prices` mapping
    - execution/canvas/packaging/ram/terms pricing
  - parity достаточный по текущему scope.

## Public API hardening status

- `lead.delivery.payment` формализован как внешний контракт.
- Current values:
  - `on_delivery`
  - `online_paysera`
  - `creditcart`
  - `google_pay`
  - `apple_pay`
  - `paypalOnetimePayment`
  - `transfer`
  - `prepayment`
  - `cash_in_office`
- Legacy aliases:
  - `online` -> `online_paysera`
  - `online_banking` -> `online_paysera`
  - `bank` -> `transfer`
