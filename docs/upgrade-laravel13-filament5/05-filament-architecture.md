# 05. Целевая архитектура Filament 5

## Panels и границы

- По подтверждённому решению клиента создаётся **одна общая `AdminPanel`**. Отдельный `CrmPanel` не создаётся.
- Внутри `AdminPanel` используются Clusters/navigation groups: CRM, Access, Catalog, Translations, Commerce, Content и SEO. Они организуют UI и код, но используют общий guard/session и единую permission model.
- На coexistence stage panel работает по `/backoffice-next`, Voyager остаётся `/admin`. Путь `/admin` передаётся Filament только после полного отключения Voyager и отдельной route/session проверки.
- Публичный сайт не переносится на Filament. Filament — только admin UI и связанные Livewire endpoints.

Filament 5 требует Livewire 4. Filament использует Laravel Policies для стандартных Resource operations, но custom Actions/Pages/Livewire, inline editable columns и query scopes требуют явной авторизации и тестов: [Filament security](https://filamentphp.com/docs/5.x/advanced/security).

Полный построчный blueprint и target-карты: `53-filament-implementation-blueprint.md`, `appendix-filament-resource-map.csv`, `appendix-filament-field-map.csv`, `appendix-filament-navigation-map.csv`, `appendix-filament-role-map.csv`, `appendix-filament-permission-map.csv`, `appendix-filament-custom-route-map.csv`, `appendix-filament-custom-view-map.csv`.

## Карта Voyager → Filament

| Группа Voyager | Filament-эквивалент | Сложность | Риск / зависимость |
|---|---|---:|---|
| Простые справочники (`country_tels`, statuses, colors/materials) | Resource | S–M | сохранить IDs/order |
| Homepage/header/footer/services | Resource + tabs/repeaters + translation relation | M | 64 779 translations, URL parity |
| Gallery catalogue/tags/items | Cluster + Resources + Relation Managers | L | uploads, filters, relationships, ordering |
| Blog/categories/authors/ads | Cluster + Resources + relation managers | L | integration API, slug/SEO, translations |
| Users | Resource + custom profile/actions | L | существующие passwords, role_id, Facebook ID |
| Roles/permissions | Resources + policy adapter | XL | 675 permissions, 2 080 links, deny-by-default |
| Menus/menu_items | nested Resource / custom Page | L | tree ordering, localized label/link |
| Settings | typed custom Page | M | сохранить keys и `setting()` adapter |
| Orders | CRM Cluster + Resource + custom Pages | XL | бизнес-логика не в form schema |
| Order chat | Relation Manager/Livewire Page | XL | attachments, ordering, dedupe, SA sync |
| Payment requests | Relation Manager + signed Actions | L | callback/idempotency/audit |
| Venipak | custom Actions/Page | L | external API, labels, credentials |
| SA conversations/bot/simulator | custom Pages/Actions/Widgets | XL | X-Api-Key, idempotency, retries |
| Email sender/marketing | custom Page + queued jobs | L | consent, batching, failures |
| Media manager/custom image field | dedicated Media Page + FileUpload adapters | XL | paths/derivatives/MIME/security |
| SEO/ALT suggestions | Resources + batch Actions + Jobs | L | existing controllers/jobs/logs |
| Canvas/collage/modular generators | Resources + custom schemas/pages | XL | many coupled fields and preview UI |
| Dashboard | Widgets | M | query cost and permissions |

## Данные и формы

- Модели и таблицы остаются прежними; Resources — presentation/application layer.
- Translation adapter читает/пишет существующую `translations`, включая base English policy. На форме — семь активных public locale `lv,lt,pl,ru,de,en,ee`; `et` сохраняется как отдельный dormant/audit contract, `uk` остаётся CRM fallback, `cn/jp` не активируются публично и сохраняются по принятому archive rule.
- Translation adapter не выполняет одноразовый импорт. Он работает с актуальным production storage либо получает доказанную инкрементальную синхронизацию. Write ownership переключается по Resource/Cluster, чтобы Voyager и Filament не перезаписывали один перевод конкурентно.
- Upload adapters обязаны сохранять текущие relative paths и derivatives. Перед write — MIME sniffing, size, image decode, randomized safe name; download — policy check.
- Order status Action вызывает доменный сервис и существующие side effects, а не прямой `$record->update()`.
- Bulk actions выключены по умолчанию для orders/users/payments/translations, пока нет транзакционного и authorization теста.

## RBAC

1. подключить adapter к существующим roles/permissions без переименования и без одновременной смены schema;
2. создать таблицу `legacy_permission → policy ability/resource/action` как версионируемый manifest;
3. по умолчанию deny; super-admin только через отдельный Gate;
4. сравнить доступ каждого из 8 roles к 138 admin menu items и каждому action;
5. логировать чувствительные actions: status/payment/refund/label/message/permission;
6. только после паритета решить, оставлять существующую schema или переходить на поддерживаемый permission package; plugin не выбирается автоматически.

## Параллельная работа

- Filament размещается на отдельном path (`/backoffice-next`), Voyager остаётся `/admin`.
- В каждой волне один модуль имеет единственный write-owner. Второй UI read-only, иначе возможны lost updates.
- Это правило распространяется на `translations`, базовые English columns, slugs, menu links, ordering и SEO metadata, а не только на основную строку модели.
- Общие sessions допускаются только после проверки guard/cookie/path; безопаснее отдельный route prefix и явный guard.
- Feature flag переключает navigation, не данные.
- Rollback UI: вернуть ссылку на Voyager; schema/data остаются обратно совместимыми.

## Definition of parity одного Resource

CRUD, search/filter/sort/order, relationships, translations, uploads, permissions, validation, audit, custom actions, server-side pagination и legacy URLs проверены. Выгрузка контрольной выборки до/после совпадает; бизнес-владелец подписал UAT; Voyager write для модуля выключен не менее одного стабильного релиза.

## FN-12 architecture constraints

133 BREAD классифицируются по `standard Resource|localized Resource|custom Page/Cluster|read-only archive|repair|retire`. Orders, chats, payments, Venipak, SA, SEO/alt bulk workflows, mail/coupons и maintenance не становятся generic CRUD. Dynamic DB-defined routes заменяются статическим Git-versioned registry; policies deny by default. Metadata translations архивируются и отображаются в новых labels осознанно, а content adapter продолжает работать со всеми locale rows. BREAD builder/Database/Compass не переносятся в production panel.
