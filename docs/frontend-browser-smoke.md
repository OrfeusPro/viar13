# Browser smoke фронта Laravel 13

Обновлено: 2026-08-13.

## Проверенный контур

Проверка выполнена на локальном production-like домене `https://viar13.loc`
с текущей MySQL-базой `viar_laravel13`. Для карточки использован реальный
`gallery_items.id=31`, тип `module`.

| Сценарий | URL | Результат |
|---|---|---|
| Главная | `/` | HTTP 200, DOM и изображения загружены, project JS errors нет |
| Каталог | `/ru/new/gallery/module` | HTTP 200, DOM и изображения загружены, project JS errors нет |
| Карточка товара | `/ru/gallery/module/item/31` | HTTP 200, после исправления project JS errors нет |
| Canvas builder | `/ru/new/canvas` | HTTP 200, DOM загружен, project JS errors нет |
| Portrait | `/ru/new/graphic-portrait/portrait-caricature` | HTTP 200 |
| Legacy basket | `/ru/basket` | HTTP 200, пустое состояние рендерится |
| Current cart | `/ru/cart` | HTTP 200, пустое состояние рендерится |
| Login | `/ru/login` | HTTP 200 |
| Register | `/ru/register` | HTTP 200 |

Locale middleware канонизирует URL и переносит исходный путь в `_url`; это
существующий storefront contract, а не ошибка smoke-проверки.

## Исправлено по результатам smoke

1. В frontend Voyager adapter добавлен совместимый `MenuItem::link()`. Это
   устранило падение старого basket layout на закэшированных базовых menu items.
2. Вложенное меню больше не включает отключённый admin view
   `voyager::menu.bootstrap`, а рекурсивно использует frontend-шаблон.
3. Header widget получает theme resource prefix через `config('theme.resource')`,
   а не через нестабильный runtime `env()`.
4. `interiorGallery.js` не создаёт `InteriorGenerator`, если на конкретной
   gallery item card отсутствует `#canvas_interior`.

## Неблокирующие наблюдения

- CookieYes пишет ошибку о несовпадении зарегистрированного production-домена
  с `viar13.loc`; на production-домене это сообщение не ожидается.
- Canvas generator рендерит восемь `<img src="">` placeholders, которые
  позднее заполняются UI-логикой. Project JS errors и сломанных URL ресурсов
  из-за них не зафиксировано; cleanup отложен до interaction UAT.

## Следующий smoke-этап

1. Интерактивно пройти canvas: выбор файла, конфигурации и добавление в корзину.
2. Добавить реальный gallery item и проверить basket/cart state.
3. Пройти переходы корзина → данные → доставка → оплата без создания заказа.
4. Проверить mobile viewport основных страниц.
