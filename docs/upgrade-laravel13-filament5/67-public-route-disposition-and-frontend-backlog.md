# 67. Public route disposition и исполняемый frontend backlog

Дата среза: 27.07.2026. Задача: `APP-005A-ROUTE-01`.

## Итог

Полная карта public frontend построена до продолжения пофункционального
переноса. Каждый legacy route вне `admin/*` и `api/*` получил handler/source,
controller-level view/code evidence, функциональный поток, side-effect class,
target match, disposition и task ID.

| Показатель | Значение |
|---|---:|
| Legacy Router, все registrations | 5 311 |
| Public rows, включая redirects и local Debugbar | 2 236 |
| Redirect dataset `routes/redirect.php` | 1 990 |
| Функциональные public rows без redirect dataset | 246 |
| GET | 149 |
| POST | 85 |
| `Route::any`-эквиваленты | 11 |
| DELETE | 1 |
| Target Router, все registrations | 51 |
| Target public registrations | 27 |
| Target public legacy-mapped registrations | 24 |
| Target RU compatibility redirects | 3 |
| Найденные public client callsites | 217 |

Standard legacy `artisan route:list --json` по-прежнему блокируется отсутствующим
`App\Http\Controllers\ImageController`. Поэтому exact legacy count проверен
безопасной загрузкой Router без разрешения controller middleware; он совпал с
зафиксированным полным snapshot `appendix-routes.csv` — 5 311 rows.

## Disposition

| Disposition | Все public rows | Без redirect dataset | Значение |
|---|---:|---:|---|
| `PARITY_DONE` | 12 | 12 | реализовано и подтверждено |
| `SHELL_ONLY` | 9 | 9 | существует только часть auth/UI contract |
| `MISSING_TARGET` | 189 | 189 | target route/flow ещё отсутствует |
| `SECURITY_REPLACE` | 2 026 | 36 | literal legacy behavior переносить нельзя |
| **Всего** | **2 236** | **246** | полный public cohort |

Двенадцать закрытых legacy contracts разворачиваются в target как 24
registrations: default RU без префикса и зеркальный `/{locale}` route. Ещё три
target routes — явные `/ru`, `/ru/condition`, `/ru/thanks` redirects на
канонический URL.

## Что уже закрыто

- `/`, `/condition`, `/robots.txt`, `/thanks`;
- popup login/register и password reset endpoints;
- FORM-01 `/all_styles_form`;
- FORM-02 `/send_photo_form`, `/user/send_photo_form`;
- locale mirrors для семи активных public locales;
- frozen theme/assets и standard `public/storage`.

`GET /password/reset` не объявлен завершённым: target поддерживает popup
reset request и token reset page, но legacy standard request page остаётся
отдельным `AUTH-001` contract.

## Функциональные потоки

| Поток | Routes | Основная задача |
|---|---:|---|
| redirects | 1 990 | `SEO-003`, `SEO-004` |
| basket/checkout/order | 59 | `APP-005B-CHECKOUT-01`, `SEC-009` |
| public content pages/widgets | 32 | `APP-005B-PAGES-01` |
| catalogue/gallery | 23 | `CAT-001` |
| generators/product families | 21 | `CAT-002` |
| account/chat/artwork | 24 | `AUTH-001`, `SEC-009`, `CHAT-001/002`, `APP-001` |
| standard/Socialite auth | 13 | `AUTH-001`, `AUTH-002` |
| Paysera/PayPal/account payment | 15 | `INT-002`, `INT-003`, `CUT-004` |
| mail previews/recovery | 12 | `SEC-010`, `MAIL-001` |
| auxiliary forms/content actions | 6 | `APP-005B-FORM-03` |
| reviews | 4 | `SEO-006` |
| public file delivery | 4 | `APP-001` |
| Venipak lookup | 3 | `INT-006` |
| sitemap/feed | 3 | `SEO-005`, `SEO-007` |
| public catalogue maintenance | 5 | `SEC-011`, `SEO-001`, `CUT-009` |
| gift card/PDF | 2 | `APP-003`, `APP-005B-CHECKOUT-01` |
| developer/image utility | 6 | `SEO-001`, `APP-001` |

Сумма таблицы использует функциональные группы и не заменяет route-level CSV;
связанные задачи могут пересекаться.

## Security replacement cohort

36 non-redirect routes нельзя переносить буквально. В него входят:

- четыре public file routes с traversal/ownership risk;
- семь static mail previews и пять controller previews/recovery routes;
- пять Debugbar endpoints;
- public `image_gen_all`;
- GET writers `set_sizes`, `set_genre`, `set_style`, `translate_item`,
  `set_meta`;
- `new/set_all_painter_images` и unsigned unsubscribe;
- семь checkout/order GET mutations, включая `save_order_and_pay`,
  `save_base64_image2`, bonus/coupon/order/image/status mutations.

Это не означает удаление бизнес-функции. Нужный outcome переносится через
POST/PATCH/DELETE, CSRF, policy/ownership, validation, idempotency, audit и
gated provider side effects.

## Client-side callers

Статический scan 789 legacy Blade/JS файлов после исключения admin/vendor/minified
кода дал 217 public callsites:

- 122 `$.ajax`;
- 87 forms;
- 5 `fetch`;
- 3 `$.get`/`$.post`.

78 выражений разрешены по route name, 52 — по literal URI, 85 остаются
динамическими expressions/data attributes. Две явные ссылки не имеют
зарегистрированного route name:

- `resources/views/auth/verify.blade.php` → `verification.resend`
  (`AUTH-001`);
- `resources/views/recovery.blade.php` → `cart.recover.process`
  (`APP-005B-CHECKOUT-01`).

Они не исправляются в route-аудите: caller/route/behavior contract проверяется
в соответствующей функциональной задаче.

## Порядок продолжения

1. `APP-005B-PAGES-01` — обычные public content pages и shared widget caller.
2. `APP-005B-FORM-03` — оставшиеся public формы этих страниц.
3. `CAT-001` — catalogue/gallery read contract.
4. `CAT-002` — generators до готового basket payload.
5. `APP-001` — private/public file access policy и uploads.
6. `APP-005B-CHECKOUT-01` — basket → checkout → order, без payment capture.
7. `INT-002/INT-003/CUT-004` — sandbox payments and receipt authority.
8. `AUTH-001/AUTH-002`, `CHAT-001/002` — полный account/Socialite/chat.
9. SEO/mail/delivery tasks согласно route-level map.

Такой порядок сначала расширяет реально отображаемый frontend, затем переносит
каталог и генераторы, и только после стабильного payload contract включает
критичный order writer.

## Артефакты и воспроизведение

- [legacy disposition](appendix-public-route-disposition.csv):
  2 236 rows, SHA-256
  `247907e8c2d09a64620a6ab6b7daf4fc1ec514c53db5d39e216703ad44666c09`;
- [target public map](appendix-target-public-route-disposition.csv):
  27 rows, SHA-256
  `b376b9d6aa7bfdf5a52420c4fdd755fd8b5e6d1cd6919ccadfe9385beb643e46`;
- [client callsites](appendix-public-client-endpoint-calls.csv):
  217 rows, SHA-256
  `1373d077bdf1258f7fcd726369dcd6616c1bfc5a25a0f1871521e9c911871461`;
- [builder](scripts/APP-005A-ROUTE-01-build.ps1).

Команда:

```powershell
& docs\upgrade-laravel13-filament5\scripts\APP-005A-ROUTE-01-build.ps1
```

Повторный запуск дал те же hashes. До/после запуска counts target DB:
`users=24025`, `orders=14724`, `translations=64779`,
`ltm_translations=21450`, `public_form_submissions=8`; delta=0.
Target full suite: 108 tests / 2 212 assertions PASS; Pint и
`git diff --check` PASS.
