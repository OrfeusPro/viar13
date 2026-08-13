# Аудит публичных изменяющих маршрутов

Обновлено: 2026-08-13.

## Назначение

Рабочая инвентаризация POST/PUT/PATCH/DELETE/ANY routes для frontend-миграции
на Laravel 13. Общий план и статусы находятся в `MIGRATION_PLAN.md`.

## Снимок route inventory

- всего mutating route records: **112**;
- web routes без `api/`: **99**;
- API routes: **13**;
- web records без явно назначенного `auth` middleware: **81**;
- `orders/*` POST routes без явно назначенного `auth` middleware: **20**;
- mutating web routes, указывающие на Admin controllers: **3**.

Отсутствие route-level `auth` ещё не доказывает уязвимость: проверка может
находиться внутри controller/service, а payment callbacks и гостевые формы
должны быть публичными по назначению. Каждый высокорисковый маршрут требуется
проверить отдельно до изменения middleware или контракта.

## Группы

### Публичные формы

- auth: login, register, reset/confirm password;
- review;
- photo/portrait/all-styles requests;
- friend email, stock/coupon и review helpers;
- guest AJAX login/register.

### Корзина и checkout

- `basket/*`: 14 records;
- `cart/*`: 10 records;
- `payment-request/*`: 6 records;
- PayPal/Paysera callbacks и accept/cancel routes.

### Кабинет

- `new/*`: account settings, comments, message state, image status и payment;
- legacy account update/comment/image routes.

### Повышенный риск — требует отдельной проверки

- 20 POST routes `orders/*` без явного route-level `auth`;
- `POST /admin/check-user` вне отключённой admin route group;
- `ANY /image_gen_all` ведёт в `Admin\ImageGenController`;
- `POST /get_towns_for_admin` ведёт в admin API controller;
- служебные GET/ANY routes и callbacks, перечисленные в `MIGRATION_PLAN.md`.

## Выполнено

- auth contract покрыт feature-тестами;
- review form переведена на `CreateReviewRequest`;
- review files ограничены двумя файлами и 10 MiB на файл;
- review audio принимает только ограниченный data URL и имеет лимит размера;
- текстовый отзыв без файлов/аудио сохраняется без undefined variables.
- review image/audio сохраняются явно на public disk; success-path подтверждён
  fake storage тестом.
- portrait/all-styles endpoints используют общий `QuickOrderRequest`;
- быстрые заявки проверяют email, телефон, 1--10 файлов, MIME и лимит 15 MiB;
- нормализуются оба найденных upload-контракта: `file[]` и legacy
  `file`, `file2` ... `file10`;
- validation contracts photo/portrait/all-styles подтверждены feature-тестами.
- guest AJAX login/register используют отдельные validation contracts;
- подтверждены JSON 422 errors, success login/register и нормализация контактов;
- endpoints защищены CSRF web middleware и rate limits `10/min`, `5/min`;
- удалена неработавшая обязательная CAPTCHA-связка; полноценное подключение
  CAPTCHA/Turnstile остаётся отдельной задачей плана.
- `basket/remove` и `basket/update/count` получили JSON validation contracts;
- подтверждено безопасное отклонение неверных индексов/количеств и сохранение
  корректного session state;
- отдельно выявлены изменяющие состояние GET/ANY basket routes и необходимость
  типизированной валидации разных `basket/add/*` payloads.

## Следующие проверки

1. Cart/basket validation и session state.
2. Route-level и controller-level authorization для `orders/*`.
3. Назначение и защита admin-controller routes в публичной группе.
