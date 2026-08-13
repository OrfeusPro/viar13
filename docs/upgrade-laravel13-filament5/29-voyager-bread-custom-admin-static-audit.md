# FN-12: Voyager BREAD, custom admin CRUD, каталог и переводы

Дата аудита: 14.07.2026. Уровень: static/data L2. Runtime production L3 и визуальная проверка административных экранов не выполнялись.

## 1. Граница и безопасность аудита

Проверены `routes/admin.php`, способ подключения маршрутов, Voyager route factory и middleware, `config/voyager.php`, `data_types`, `data_rows`, `permissions`, `permission_role`, `roles`, `menus`, `menu_items`, `settings`, `translations`, кастомные Voyager/Admin controllers, переопределённые Blade views и существующие тесты.

Локальная БД использовалась только в read-only режиме для schema metadata, exact counts и агрегатов. Значения настроек, тексты переводов, содержимое заказов, сообщения, email, телефоны и файлы не читались. Административные HTTP endpoints не вызывались: многие из них изменяют БД, файлы, cache, отправляют почту или обращаются к внешним системам.

Рабочий runtime baseline: `C:\OSPanel\modules\php\PHP_7.4\php.exe`. Public assets не собирались и не изменялись.

Полный построчный снимок 133 модулей: `appendix-voyager-bread-modules.csv`. Маршрутные и функциональные детали дополнительно находятся в `appendix-admin-acl-routes.csv`, `appendix-routes.csv` и `appendix-functions.csv`.

## 2. Главный вывод

Voyager здесь — не заменяемая одним пакетом оболочка CRUD. Он одновременно является:

1. DB-driven registry 133 административных модулей;
2. фабрикой маршрутов, которая создаёт resource/action/relation/media routes на старте приложения;
3. системой прав для 8 ролей и 675 permissions;
4. редактором 105 переводимых BREAD-модулей и 64 779 translation rows;
5. media/file manager с собственными путями и операциями;
6. контейнером для кастомного управления заказами, пользователями, ролями, языками, SEO/alt, рассылкой, доставкой, чатами и CRM↔SA;
7. источником 40 переопределённых Blade views и Voyager route names, от которых зависит существующий JS/UI.

Поэтому установка Filament и механическая генерация 133 Resources не обеспечивает паритет. Нужны подписанный module manifest, отдельные specialized Pages/Actions для бизнес-процессов, точное отображение ACL и переводов, а также покомпонентное переключение write-owner.

Production продолжает принимать заказы и изменения каталога/переводов. Legacy Voyager остаётся единственным write-owner конкретного модуля до final delta reconciliation и явного переключения этого модуля.

## 3. Подтверждённый inventory локального snapshot

| Объект | Подтверждено |
|---|---:|
| `data_types` | 133 |
| `data_rows` | 1 997 |
| BREAD с существующими table и model class | 131 |
| Некорректные/устаревшие BREAD definitions | 2 |
| BREAD с custom controller | 6 |
| `server_side=1` | 7 |
| BREAD с translation rows | 105 |
| Relationship fields | 28 в 15 модулях |
| Custom Voyager Blade files | 40 |
| `permissions` | 675 |
| `permission_role` | 2 080 |
| Roles | 8 |
| Roles с `browse_admin` | 6 |
| Menus / menu items | 3 / 166 |
| Settings | 27 |
| Translation rows | 64 779 |
| Активные locale records | 7 |
| Static route declarations в `routes/admin.php` | 68 |
| Custom declarations после `Voyager::routes()` | 52 |
| Специализированные Voyager/admin CRUD tests | 0 найдено |

### 3.1. Роли и объём назначенных permissions

| Role | Permission assignments |
|---|---:|
| `admin` | 638 |
| `manager` | 456 |
| `Admin 2` | 353 |
| `seo_manager` | 616 |
| `printing` | 6 |
| `lite_manager` | 11 |
| `user` | 0 |
| `painter` | 0 |

`browse_admin` назначен `admin`, `manager`, `Admin 2`, `printing`, `lite_manager`, `seo_manager`. Имена ролей нельзя использовать как единственный target ACL: переносится фактическая матрица abilities по каждому ресурсу и custom action.

### 3.2. Типы полей BREAD

| Тип | Количество |
|---|---:|
| text | 1 205 |
| timestamp | 268 |
| image | 117 |
| rich_text_box | 115 |
| number | 95 |
| checkbox | 54 |
| text_area | 30 |
| relationship | 28 |
| adv_media_files | 19 |
| multiple_images | 8 |
| hidden | 8 |
| file | 7 |
| color | 6 |
| media_picker | 6 |
| code_editor | 4 |
| date | 3 |
| multiple_checkbox | 2 |
| password | 1 |

Это 1 997 отдельных contracts видимости, валидации, defaults/options, хранения, uploads и переводимости. В Filament их нельзя восстанавливать только из SQL column type.

## 4. DB-driven routing и BREAD metadata

`Voyager::routes()` загружает `vendor/tcg/voyager/routes/voyager.php`. На старте приложения выполняется `DataType::all()`, после чего для каждого BREAD регистрируются resource routes и дополнительные `order`, `action`, `update_order`, `restore`, `relation`, `remove_media` routes. Controller берётся из `data_types.controller`, иначе используется `VoyagerBaseController`.

Следствия:

- route registry зависит от текущего содержимого БД;
- изменение BREAD metadata может изменить доступные routes без изменения Git;
- cached/uncached route parity нельзя считать доказанным;
- target Laravel 13 должен иметь статически версионируемый registry Resources/Pages/Policies;
- перед отключением Voyager сохраняется неизменяемый snapshot `data_types`, `data_rows`, relationships, validation/options JSON и route names.

JSON в проверенных `data_rows.details` и DataType metadata синтаксически корректен. Для каждого из 133 модулей подтверждены table/model/controller existence, row count, relation count, permissions, translation counts и migration class в приложении CSV.

## 5. Подтверждённые дефекты и неоднозначности BREAD registry

### 5.1. Два устаревших BREAD definitions

| ID | Slug/table | Model | Состояние |
|---:|---|---|---|
| 27 | `canvas` / `canvas` | `App\Models\Canva` | table и model отсутствуют; при этом в `translations` остаётся 40 строк для `canvas` |
| 129 | `a-collag-faq` / `a_collag_faq` | `App\Models\ACollagFaq` | table и model отсутствуют; рядом существует корректный BREAD ID 131 `a-collage-faq` / `a_collage_faq` с 5 rows и 48 translations |

Их нельзя автоматически удалять: нужны production access logs, owner и архив metadata/translations. Но их нельзя переносить как рабочие Filament Resources без явного решения `repair|redirect|read-only archive|retire`.

### 5.2. Custom orders BREAD не реализует полный resource contract

`orders` указывает на `App\Http\Controllers\OrdersController`, который не наследует `VoyagerBaseController`. В нём присутствуют `index`, `show`, `update`, но отсутствуют стандартные resource methods `create`, `store`, `edit`, `destroy`, а также Voyager `restore`, `order`, `update_order`, `action`, `relation`, `remove_media`.

Voyager route factory всё равно регистрирует эти routes. Часть может быть скрыта UI/permissions, но сам registry содержит потенциальные method-not-found paths. Target не должен воспроизводить слепой resource set: для заказов создаётся отдельная Filament Page/Actions по фактически используемым сценариям, а не generic CRUD.

### 5.3. Permission shape отличается у alt suggestions

132 BREAD имеют стандартные ключи `browse/read/edit/add/delete`. `image_alt_suggestions` намеренно имеет только `browse` и `edit`; custom controller сам разделяет approve/reject/regenerate/revert/bulk. Это подтверждает, что пять CRUD verbs не описывают custom action ACL.

### 5.4. Пустые/редко используемые модули

- `payment_Info` существует, но имеет 0 rows и 0 DataRows;
- `seo_meta_suggestions` имеет 28 DataRows, но локально 0 business rows;
- локальный ноль не является доказательством отсутствия production данных или использования;
- перед retire нужны production counts, access logs и владелец.

## 6. Middleware и custom admin routes

Core Voyager routes находятся внутри `admin.user`. Middleware проверяет authenticated guard и только `browse_admin`; внутри `VoyagerBaseController` выполняются resource-level `browse/read/edit/add/delete` policy checks.

Однако внешний `Route::group(['prefix' => 'admin', 'namespace' => 'Admin'])` не имеет `admin.user`. После закрытия защищённой SEO/alt группы вызывается `Voyager::routes()`, а затем объявляются ещё 52 custom routes. Они получают только общий middleware `web`, если controller не защищает себя самостоятельно.

Подтверждены разные модели защиты:

- `VoyagerAdminController` добавляет только `auth`, без resource permission;
- `Admin\OrdersController`, `EmailSenderController`, `ImageGenController`, `AdminSaIntegrationController` и `Admin\Api\VinepakApiController` не имеют общего constructor auth middleware;
- отдельные методы иногда проверяют `Auth`, role или permission, но единого deny-by-default perimeter нет;
- часть endpoints выполняет side effects: orders/chat/status/price/files, painter/printing assignments, Venipak labels, email campaign, coupons/sales, cache/route cache, SA messages/binding/order creation;
- несколько изменяющих операций доступны через GET-capable routes;
- `image_gen_all` дополнительно зарегистрирован в public localized web group.

Это согласуется с FN-06: текущую неоднородность нельзя переносить как норму. Перед framework migration нужен отдельный security hotfix: весь admin perimeter под auth + explicit ability/ownership + CSRF/method contract; high-impact actions получают audit actor, idempotency/receipt и безопасный retry.

## 7. Custom admin layers, которые generic Resource не заменяет

### 7.1. Заказы

Существуют два больших контроллера:

- `App\Http\Controllers\OrdersController` — одновременно public/account/admin/payment/order/chat logic и custom BREAD browse/show/update;
- `App\Http\Controllers\Admin\OrdersController` — ручное создание и изменение заказа, ручные цены, pickup/delivery, users, coupons и mail side effects.

Добавляются custom order Blade views, filters, painter/printing actions, PDF locale, payment requests, files, chats, Venipak и SA. Заказы переносятся только как специализированный модуль с инвариантами `orders.id`, status domain, totals, payment/delivery/chat contracts и audit trail.

### 7.2. Языки

`AdminLocaleController` — копия/вариант большого Voyager base controller. Кроме CRUD он содержит методы чтения и перезаписи `resources/lang/{locale}/{template}.php` через `scandir`, dynamic `include`, `fopen(..., 'w')` и `fwrite`.

Эти методы не найдены среди явных routes, поэтому их reachability нужно подтвердить production route/access snapshot. При переносе их нельзя случайно сделать доступными. Если редактор PHP language files нужен бизнесу, он становится отдельным строго разрешённым workflow: allowlist locale/template/key, versioning, syntax validation, atomic file replace, backup/diff, audit actor и deploy-safe storage. Редактирование tracked PHP-файлов прямо на production не должно быть неявной функцией Filament.

### 7.3. SEO/alt

SEO meta и alt suggestions используют custom controllers, свои permissions, bulk actions, entity/media lookup и запись translated attributes. Они требуют state-machine, idempotent apply/revert, media/file receipt, conflict/version checks и audit log; обычный CRUD не сохраняет эти semantics.

### 7.4. Рассылка, coupons и maintenance

Email sender загружает пользователей и запускает массовую отправку; coupon actions меняют users/coupons и отправляют письма; cache endpoints вызывают Artisan commands. Эти функции должны быть вынесены в отдельные разрешённые Pages/Jobs с dry-run, batch limit, consent, queue, attempt ledger и operational permissions. Их нельзя размещать как свободные toolbar actions generic Resources.

### 7.5. Media и custom views

Подтверждено 40 файлов в `resources/views/vendor/voyager`, включая orders, users, roles/BREAD, gallery item, blog post, SEO/alt, multilingual selectors, email sender, coupons и dashboard/sidebar/master overrides. Они зависят от Voyager markup, route names, JS и permission helpers.

Для каждого экрана требуется screenshot/DOM/action inventory до замены. Публичные assets остаются frozen; административный frontend меняется отдельно и не даёт разрешения пересобирать public Mix outputs.

## 8. Переводы и языки: сохраняются все источники

### 8.1. Locale baseline

Таблица `locales` содержит 7 записей: `en`, `ru`, `lv`, `ee`, `lt`, `de`, `pl`. Они совпадают с `config/voyager.php`. В `translations` присутствует восьмой код `et` — 6 строк для `canvas_photo_improvements.name|hint`. Код `ee` используется приложением как Estonian locale и не может быть массово переименован в `et` без URL/fallback/content решения.

| Locale | Translation rows |
|---|---:|
| `ru` | 12 661 |
| `lt` | 10 433 |
| `ee` | 10 408 |
| `lv` | 10 408 |
| `pl` | 9 409 |
| `de` | 9 392 |
| `en` | 2 062 |
| `et` | 6 |

`uk` требуется CRM↔SA API, но в локальном Voyager snapshot отсутствует и сейчас обеспечивается fallback/проекцией. Это отдельный API-locale contract, а не разрешение удалить другие языки.

### 8.1.1 Translation Manager — третий storage-контур

Помимо base fields/`translations` и PHP-файлов установлен `barryvdh/laravel-translation-manager` v0.5.10. Локально `ltm_translations` содержит 21 450 строк в 46 groups: `de=2960`, `ee=3088`, `en=2960`, `et=2519`, `lt=2431`, `lv=2473`, `pl=2431`, `ru=2588`; status=0 у 20 386, status=1 у 1 064, `NULL value` у 211. Дубликатов `(locale,group,key)` локально нет, но unique constraint отсутствует.

Маршруты под `admin/translations` статически защищены только `web,auth`; package поддерживает edit/delete/import/find/publish/add/remove locale/auto-translate. Publish экспортирует БД в `resources/lang/{locale}/{group}.php`. Это второй writer тех же файлов наряду с `AdminLocaleController`. До Filament cutover нужны role ACL, audit receipt, production duplicate audit и один versioned publisher; прямой live publish двумя editors является no-go.

### 8.2. Не только BREAD content

Из 64 779 translation rows:

- 51 416 относятся к таблицам, зарегистрированным как BREAD;
- 10 952 переводят `data_rows` — подписи/описания полей Voyager;
- 1 284 переводят `data_types` — названия модулей;
- 767 переводят `menu_items`;
- 360 относятся к отсутствующим legacy tables: `modular_pics` 288, `graph_port_pages` 48, `style` 24.

Последние 360 являются orphan/legacy evidence, а не автоматически удаляемым мусором. Их нужно архивировать, проверить production/code history и получить решение владельца.

### 8.3. Правило переноса

Сохраняются одновременно:

1. base columns моделей;
2. все 64 779 rows `translations`, включая `ee/et` anomaly и orphan quarantine;
3. PHP files `resources/lang` со всеми существующими каталогами;
4. translated slugs, SEO, HTML, menu labels, ordering и fallbacks;
5. mapping Voyager metadata labels → Filament resource/navigation/form labels либо read-only archive исходных metadata.

Нельзя оставлять Voyager и Filament двумя независимыми writer для одного table/entity/locale. До cutover legacy пишет, target читает/shadow compares. Затем выполняются короткий component write gate, final delta, hashes/counts и переключение единственного writer.

## 9. Voyager operational tools и media policy

Core Voyager предоставляет Menu, Settings, Media, BREAD builder, Database и Compass routes под общим `admin.user`. При этом:

- `admin.user` проверяет только `browse_admin` до входа в panel;
- dedicated least-privilege abilities для BREAD builder/Database/Compass требуют отдельного подтверждения;
- `compass_in_production=true`;
- media allowed MIME настроен как `*`;
- media root `/`, upload/move/delete/create-folder/rename включены;
- storage disk берётся из `FILESYSTEM_DRIVER`, default `public`.

В target BREAD builder/Database/Compass не переносятся как production feature. Schema changes идут только через reviewed migrations. Media manager получает allowlist, size/decode policy, private/public classification, path normalization, malware/XSS controls и audit trail.

## 10. Производительность и объёмы

Шесть BREAD tables превышают 1 000 rows:

| Module | Rows | Server-side |
|---|---:|---:|
| users | 23 801 | да |
| orders | 14 423 | да |
| order_action | 6 696 | да |
| coupons | 1 586 | нет |
| image_alt_suggestions | 1 158 | нет |
| gallery_items | 1 038 | да |

Также server-side включён для `family_constructor`, `header_menu`, `footer_menu`. Сам флаг не является достаточным performance specification. Для каждого target table нужны production filters/sorts/search columns, indexes, pagination, eager loading, query count и p95/p99. Нельзя переносить unrestricted `User::all()`/bulk lists в Filament только потому, что локально экран открывается.

## 11. Write ownership при работающем production

| Компонент | До cutover | Shadow phase | Cutover | Rollback |
|---|---|---|---|---|
| orders/status/chat | Legacy | target read-only compare | short action gate + final delta | вернуть route/UI owner legacy, reconcile new receipts |
| catalogue/content | Voyager | Filament read-only | module write gate + row/translation/media delta | disable target writes, restore legacy owner |
| translations | Voyager/PHP files | locale/table/column hash compare | один writer на module+locale | replay journal, не откатывать всю БД |
| users/roles/permissions | Voyager | policy matrix simulation | signed role wave | panel off, Voyager ACL retained |
| SEO/alt/media | Voyager custom actions | dry-run/shadow result | action-specific receipt gate | revert by immutable apply log/artifact |
| menu/settings | Voyager | read-only target projection | owner-approved snapshot/delta | legacy read/write owner |

Обязательный delta ledger: table, primary key, operation, source writer, version/updated_at, actor, locale, before/after hash и artifact receipt. Для таблиц без надёжного `updated_at` нужен binlog/trigger CDC либо controlled component freeze.

## 12. План переноса FN-12

### Шаг 1. Production evidence

- снять production-safe `data_types/data_rows/permissions/permission_role/roles/menu/settings/translations` snapshot;
- собрать 30–90 дней access logs по BREAD/custom admin routes;
- определить owners и frequency для каждого модуля/action;
- получить role×resource×action matrix и список реально используемых custom screens.

### Шаг 2. Подписанный module manifest

Для каждой из 133 строк зафиксировать:

- `migrate|specialized page|read-only archive|repair|retire`;
- model/table/PK/soft delete/order/filter/search;
- fields, validation, defaults, relationships, translations, media;
- roles/abilities/custom actions/side effects;
- source writer, cutover wave, UAT owner, rollback owner.

### Шаг 3. Security stabilization до framework migration

- общий auth+ability perimeter для всех `/admin/*`;
- убрать writes из GET/ANY;
- закрыть public image/mail/test/maintenance endpoints;
- добавить actor/audit/idempotency для необратимых actions;
- запретить BREAD builder/Database/Compass и unrestricted media для неподтверждённых ролей.

### Шаг 4. Translation/content adapter

- сохранить текущую таблицу `translations` на первой волне;
- реализовать Filament adapter, который понимает base values, all locales и fallback без регенерации текста;
- отдельно перенести/архивировать metadata translations и PHP lang files;
- добавить optimistic locking и conflict UI.

### Шаг 5. Filament waves

1. read-only references и простые непереводимые справочники;
2. локализованные content/catalog modules;
3. menu/settings при подтверждённом owner;
4. users/roles/permissions;
5. blog/gallery/SEO/alt/media;
6. orders/chats/payments/Venipak/SA как specialized Pages/Actions;
7. редкие generators/custom/legacy modules после access-log решения.

### Шаг 6. Per-module parity

Для каждого модуля: list/filter/sort/search/pagination, create/read/edit/delete/restore/reorder, validation, relation, translation, upload/remove media, custom action, ACL negative cases, audit log, concurrency/lost update и rollback.

### Шаг 7. Controlled cutover

- initial copy/shadow reads;
- repeatable deltas;
- module-level short write gate;
- final count/max-ID/hash/locale/media/permission reconciliation;
- switch one writer;
- observation window;
- disable Voyager module только после UAT и rollback rehearsal.

### Шаг 8. Voyager retirement

После всех волн: экспорт immutable metadata, удаление dynamic routes, отключение panel write access, retention window, backup/restore validation. Runtime tables и translations не удаляются в день cutover.

## 13. Обязательная тестовая матрица

Минимум для каждого BREAD/Resource:

1. guest/no-`browse_admin`/wrong-role/allowed-role;
2. browse filters, ordering, pagination, server-side search;
3. field visibility и validation по create/edit/read;
4. base locale + `ru/en/lv/ee/lt/pl/de` и сохранение `et` anomaly;
5. translated slug/HTML/SEO/menu values и fallback;
6. relationship null/orphan/multi-select cases;
7. upload MIME/size/name/path/replace/delete/rollback;
8. soft delete/restore/bulk/reorder concurrency;
9. custom actions с actor, duplicate/retry/failure receipt;
10. optimistic lock: одновременное изменение Voyager/Filament;
11. orders/custom pages — отдельные domain fixtures, не generic CRUD tests;
12. cached/uncached route registry и отсутствие unresolved handlers.

Специализированных тестов Voyager BREAD/admin permissions/locales/custom routes в текущих 23 test files не найдено. Существующие API tests translations не покрывают административный writer, role matrix, media и custom action semantics.

## 14. Новые риски FN-12

- R-92 — custom admin routes вне `admin.user` сохранят ACL bypass;
- R-93 — DB-driven route/BREAD metadata потеряется или изменит route cache;
- R-94 — permissions и custom action abilities неверно отобразятся в Filament;
- R-95 — 1 997 field contracts/relationships/validation/options будут упрощены;
- R-96 — два writer создадут lost update переводов/контента;
- R-97 — media/file path, derivatives и delete semantics будут потеряны;
- R-98 — menu/settings и translated navigation изменят сайт/админку;
- R-99 — BREAD builder/Database/Compass/media останутся опасными production tools;
- R-100 — stale/missing model/table/controller methods сломают registry;
- R-101 — 40 custom views и side-effect actions исчезнут при generic CRUD migration;
- R-102 — bulk/delete/reorder/custom actions повторятся при retry/concurrency;
- R-103 — большие tables и non-server-side lists вызовут latency/memory regression;
- R-104 — production edits после snapshot не попадут в target;
- R-105 — rollback без единственного writer и mutation ledger перезапишет новые данные;
- R-106 — PHP language editor/path input может повредить или раскрыть tracked locale files;
- R-107 — `ee/et/uk`, metadata translations и orphan translation history будут ошибочно нормализованы/удалены.

## 15. Gate закрытия FN-12

FN-12 остаётся открытым до выполнения всех условий:

- production snapshot и access logs получены;
- все 133 module decisions подписаны business/security owners;
- 8-role ACL и custom action matrix утверждены;
- stale BREAD IDs 27/129 и incomplete orders resource routes имеют решение;
- все 64 779 translations и PHP lang files входят в migration/reconciliation manifest;
- admin security containment завершён;
- browser/runtime parity выполнен на staging PHP 7.4 и target;
- по каждой wave есть one-writer cutover и rollback rehearsal;
- Voyager отключается только после завершения observation/retention window.

## 16. Что не выполнялось

- production не читался;
- административные HTTP actions не запускались;
- визуальные страницы Voyager/Filament не сравнивались;
- письма, Venipak, SA, cache commands и file writes не вызывались;
- public/admin assets не пересобирались;
- route runtime registry остаётся частично заблокирован ранее подтверждённым отсутствующим `App\Http\Controllers\ImageController`.

FN-13 завершён отдельным L2-пакетом `30-seo-redirect-preview-auxiliary-static-audit.md`. Следующий этап — P0 containment и production L3 evidence, а не новый static package.
