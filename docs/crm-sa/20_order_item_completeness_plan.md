# Order Item Completeness Plan (SA -> CRM)

Date: `2026-03-03`

## 1. Проблема

Для заказов:
1. `HM-44` -> `127006`
2. `HM-27` -> `127007`
3. `HM-29` -> `127008`
4. `HM-33` -> `127009`
5. `HM-34` -> `127010`
6. `HM-45` -> `127011`
7. `HM-47` -> `127012`
8. `HM-48` -> `127013`
9. `HM-49` -> `127014`

выполнен базовый flow (create/chat/stages), но `orders.items` заполнен слишком минимально для “реального” вида заказа в админке.

## 2. Что сейчас сохраняется (факт)

### 2.1 Portrait HM-27/29/33/34/45/47/48/49
- Ключи item:
  - `count,id,price,sumPrice,terms_price,name,description,type,service_path`

### 2.2 HM-44
- Ключи item:
  - `count,id,price,sumPrice,terms_price,name,description,type,service_path,size,size_name`

## 3. Что ожидается по структуре (по реальным заказам)

На основе реальных заказов (не SA), для portrait-подобных товаров часто есть:
1. `is_port_product`
2. `orig_images` / `activeImage`
3. `pack`
4. `terms`
5. `forma_id`
6. `size_name`
7. `users_count`
8. `type`
9. `holst_id`
10. `hud_of`
11. `compl_id`
12. `userComment`
13. `basket_type`/`basketType`
14. `is_def_product`
15. `total_item_price`

Для HM-44 (gallery-like) ожидается хотя бы:
1. `size_name`/`size`
2. `terms`
3. `pack` или `show.box`
4. `orig_images`/`activeImage` при наличии файла
5. `total_item_price`

## 4. Причина

`SaIntegrationController::resolveServiceToBasket()` для non-canvas сейчас формирует generic `sa_service` item с минимальным набором полей и не строит типизированный payload как в обычной корзине.

## 5. План доработки

## Шаг 1. Ввести критерий “completeness”
1. Зафиксировать минимальный обязательный набор полей для:
   - `portrait` services (`/new/caricature`, `/new/graphic-portrait/*`, `/simpsons`)
   - `gallery` service (`/new/gallery`)
2. Добавить этот критерий в `test log` как gating для PASS.

## Шаг 2. Реализовать builder для portrait services
1. Добавить `buildPortraitBasketFromServiceRequest()` в `SaIntegrationController`.
2. На вход принимать:
   - `service_id`, `country_code`
   - `options.size` (если передан)
   - `options.users_count`
   - `options.type`/`execution`
   - `options.packaging_id`
   - `options.terms_mode`
3. Формировать item с совместимыми ключами для админ-карточки:
   - минимум: `is_port_product=1`, `is_def_product=1`, `basket_type=1`,
   - `size_name`, `users_count`, `type`, `pack`, `terms`,
   - `sumPrice`, `total_item_price`.
4. Подключить в `resolveServiceToBasket` по `service_path` портретных маршрутов.

## Шаг 3. Реализовать builder для HM-44
1. Добавить `buildGalleryCatalogBasketFromServiceRequest()`.
2. Поддержать:
   - `options.size` + price-by-size,
   - `terms/pack` defaults,
   - `size_name`, `total_item_price`.
3. При наличии attachments в lead/message связывать изображение с item (`orig_images`/`activeImage`) по правилам проекта.

## Шаг 4. Валидация входных options
1. Расширить `POST /api/sa/leads` валидацию для new options:
   - `users_count`, `type`, `packaging_id`, `terms_mode`, `size`.
2. Некорректные значения -> `VALIDATION_ERROR`.

## Шаг 5. Автотесты (обязательно)
1. Добавить feature-тесты:
   - portrait createLead сохраняет обязательные portrait fields.
   - HM-44 createLead сохраняет size + completeness fields.
2. Для каждого: success + validation + duplicate.

## Шаг 6. Ретест в sa-simulator
1. Повторно прогнать 9 сервисов.
2. Проверить:
   - API ответ,
   - итог `orders.items` ключи,
   - визуальный блок товара в админке.
3. После ретеста перевести статусы в `done`.

## 6. Риски

1. Слишком “жесткая” имитация полей может конфликтовать с legacy-рендерами.
2. Для части полей нет прямого соответствия в SA payload, потребуется дефолтная политика.
3. Нужно согласовать, какие поля обязательны, а какие optional-but-recommended.

## 7. Next Action

1. Утвердить “минимально обязательный набор полей” для portrait и HM-44.
2. После утверждения реализовать Step 2 + Step 3.
