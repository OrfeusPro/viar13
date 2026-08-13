# S0-04 — Совместимый адаптер ролей и разрешений, построение навигации Filament и проверка доступа для всех восьми ролей

```text
TASK_ID: S0-04
STATUS: DONE
PRIORITY: Critical
OWNER: программист
BRANCH: codex/s0-04-rbac-navigation
CREATED_AT: 2026-07-21
UPDATED_AT: 2026-07-21
DEPENDS_ON: S0-03=DONE
NEXT_ACTION: перейти к S0-05 — translation adapter/versioned publisher; 133 deferred navigation items включать только вместе с готовыми модулями
```

## Цель

Перенести в Laravel 13 / Filament 5 существующую модель доступа Voyager без
повышения привилегий: восемь ролей, 675 permissions, 2 080 назначений и 138
элементов admin-меню должны давать явно проверяемые решения navigation/direct
URL/Policy/Action, а неизвестные или неназначенные права — отказ.

## Источники истины

- `appendix-filament-role-map.csv`;
- `appendix-filament-permission-map.csv`;
- `appendix-filament-navigation-map.csv`;
- `appendix-filament-custom-route-map.csv`;
- фактические read-only таблицы `roles`, `permissions`, `permission_role`, `user_roles`;
- legacy Voyager `hasPermission()` и admin middleware;
- завершённый target auth adapter из `S0-03`.

## Входит в задачу

- read-only reconciliation CSV ↔ local DB без PII;
- единый deny-by-default ability adapter поверх legacy permission keys;
- отображение CRUD prefixes `browse/read/edit/add/delete` в Filament abilities;
- versioned manifest custom Page/Action permissions;
- navigation visibility только как отражение Policy, не как защита;
- запрет неизвестных permissions, direct URL и Livewire action bypass;
- симуляция восьми ролей для 138 menu items и доступных target routes;
- базовый механизм немедленного применения permission change на следующем request;
- сохранение `FILAMENT_WRITE_ENABLED=false` и запрет bulk/inline/export actions.

## Не входит

- реализация всех 133 Resources/Pages и их бизнес-функций;
- запись в production/test или изменение legacy ACL schema/data;
- ownership/query scopes конкретных Orders/Chats/Files — они проверяются в S0-06/S0-07;
- перенос переводов меню — S0-05;
- серверное развёртывание;
- автоматический unrestricted bypass по имени роли `admin`.

## План выполнения

- [x] сверить counts/hashes/keys четырёх canonical CSV и local DB;
- [x] классифицировать 138 menu items: Resource, Page, external/legacy, separator/group;
- [x] зафиксировать permission-key → ability mapping и unknown-key policy;
- [x] расширить target adapter методами role/permission/ability без schema writes;
- [x] создать versioned navigation/ability manifest;
- [x] подключить безопасный первый navigation item/page — Dashboard через `browse_admin`;
- [x] добавить role × navigation × direct URL × Livewire tests;
- [x] доказать отсутствие name-based admin bypass и автоматического доступа user/painter;
- [x] проверить zero DB delta, полный suite, Pint, Composer/platform;
- [x] приложить evidence и обновить 64/65/running log.

## Definition of Done

- [x] все 8 ролей имеют детерминированный результат для каждой строки manifest;
- [x] 138 menu items покрыты решением без потерянных/неизвестных строк;
- [x] 675 permissions и 2 080 links reconciled без расхождений;
- [x] navigation visibility совпадает с Policy outcome;
- [x] скрытая Page закрыта по direct URL и Livewire request;
- [x] unknown/unassigned permission всегда DENY;
- [x] user/painter не получают Panel автоматически;
- [x] bulk/inline/export и write Actions отсутствуют или закрыты дополнительным gate;
- [x] permission change применяется на следующем request без устаревшего разрешения;
- [x] local DB business/ACL tables имеют zero unintended delta;
- [x] tests/evidence/docs PASS.

## Evidence

| Дата | Evidence | Результат |
|---|---|---|
| 2026-07-21 | S0-03: `/admin/login` ручной PASS; 22 tests / 55 assertions; `browse_admin` adapter | prerequisite PASS |
| 2026-07-21 | CSV ↔ local DB: roles 8/8, permissions 675/675, links 2 080/2 080; metadata/assignment mismatch=0 | PASS |
| 2026-07-21 | Admin menu: CSV 138, DB menu `admin` 138, missing IDs=0; 12 raw-title differences только whitespace, normalized mismatch=0 | PASS |
| 2026-07-21 | Classification: 113 datatype permissions + 17 groups + 8 explicit special-item decisions | 138/138 covered |
| 2026-07-21 | Generated `resources/filament/rbac/legacy-rbac-manifest.json`: 400 371 bytes, SHA-256 `ca1921d8e957f85e0f8f3e588ba583c755d83a84acd7550368140c92c6eb8cc1` | 1 104 role-navigation decisions |
| 2026-07-21 | Manifest states: active=1, deferred=133, retired=4; неизвестные permissions/actions default DENY | PASS |
| 2026-07-21 | Runtime simulation: admin=134, manager=93, Admin 2=114, printing=12, lite_manager=12, seo_manager=121, user=0, painter=0; 1 104 решений, mismatch=0 | PASS |
| 2026-07-21 | Permission-specific synthetic Page: direct HTTP и Livewire для wrong role → 403; разрешённая роль → доступ | PASS |
| 2026-07-21 | Повторная генерация manifest дала тот же SHA-256 `ca1921d8e957f85e0f8f3e588ba583c755d83a84acd7550368140c92c6eb8cc1` | deterministic PASS |
| 2026-07-21 | Full target suite: 36 tests / 1 470 assertions; Pint, Composer validate strict, non-dev platform requirements, `/admin/login` HTTP probe | PASS |
| 2026-07-21 | До/после: orders=14 423, order_user_comments=3 835, translations=64 779, users=23 801, user_roles=0 | zero unintended DB delta |

## Журнал работы

| Дата | Что сделано | Блокер | Следующее действие |
|---|---|---|---|
| 2026-07-21 | S0-04 открыта, ветка `codex/s0-04-rbac-navigation` создана | нет | reconciliation canonical CSV ↔ local DB |
| 2026-07-21 | Reconciliation без расхождений; реализованы `LegacyPermissionResolver`, Laravel Gates, write gate и live-permission test | нет | создать deterministic manifest и navigation registry |
| 2026-07-21 | Созданы generator/manifest/registry, покрыты 8×138 решения; Dashboard подключён через `browse_admin` | нет | добавить безопасную узкую Page и negative HTTP/Livewire tests |
| 2026-07-21 | Direct URL/Livewire probes, runtime role simulation, deterministic rebuild и полный suite завершены | нет | S0-04 закрыта; перейти к S0-05 |

## Stop conditions

- имя роли используется как unrestricted authorization bypass;
- menu visibility подменяет Policy/direct URL authorization;
- query/action возвращает или изменяет запрещённые records;
- требуется изменить legacy ACL schema/data вместо adapter;
- неизвестное permission получает allow;
- write/bulk/inline/export включается при false write gate;
- проверка обращается к server test/production или выводит PII/secrets.
