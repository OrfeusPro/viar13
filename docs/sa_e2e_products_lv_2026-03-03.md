# SA E2E по товарам (LV)

Дата прогона: 2026-03-03
Среда: `https://viarcanvas.loc`
Сценарий для каждого `HM-*`:
1. `POST /api/sa/leads` (создание заказа)
2. `POST /api/sa/webhooks/messages` inbound + attachment
3. `POST /api/crm/webhooks/send-message` outbound
4. `POST /api/sa/webhooks/messages` `message.status=delivered`
5. `PATCH /api/sa/leads/{id}` STG-20 -> STG-30 -> STG-40 -> STG-50

## Результат

| Service | Path | Order ID | Размер (chosen/item) | Цена товара | Цена заказа | Статус | Сообщения | Итог |
|---|---|---:|---|---:|---:|---|---:|---|
| HM-3 | /collage | 17054 | 30x40 / 30x40 | 15 | 15 | completed | 2 | OK |
| HM-43 | /modular-generator | 17055 | 60x135 / 60x135 | 75 | 75 | completed | 2 | OK |
| HM-44 | /new/gallery | 17056 | 30x20 / 30x20 | 15 | 15 | completed | 2 | OK |
| HM-27 | /new/caricature | 17057 | 30x40 / 30x40 | 60 | 60 | completed | 2 | OK |
| HM-29 | /new/graphic-portrait/portrait-dream-art | 17058 | 30x40 / 30x40 | 60 | 60 | completed | 2 | OK |
| HM-33 | /new/graphic-portrait/graphic-portrait | 17059 | 30x40 / 30x40 | 65 | 65 | completed | 2 | OK |
| HM-34 | /new/graphic-portrait/pop-art-portrait | 17060 | 30x40 / 30x40 | 55 | 55 | completed | 2 | OK |
| HM-45 | /new/graphic-portrait/kartiny | 17061 | 30x40 / 30x40 | 80 | 80 | completed | 2 | OK |
| HM-47 | /simpsons | 17062 | 30x40 / 30x40 | 50 | 50 | completed | 2 | OK |
| HM-48 | /new/graphic-portrait/portrait-historical | 17063 | 30x40 / 30x40 | 60 | 60 | completed | 2 | OK |
| HM-49 | /new/graphic-portrait/pet-portrait | 17064 | 30x40 / 30x40 | 50 | 50 | completed | 2 | OK |

Итого: 11/11 OK.

## Примечания

- Для `HM-2` (Canvas) отдельный полный E2E прогон был выполнен ранее: order `17022` (OK).
- В ходе работ исправлен дефект заполнения `size`/`size_name` для Canvas (`HM-2`) в `buildCanvasBasketFromServiceRequest`.
- Для `HM-34` в `header_menu.link` есть хвостовые пробелы; в E2E не помешало, но лучше нормализовать `trim(link)` при чтении.
