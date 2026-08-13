# 53. Filament 5 Implementation Blueprint

Дата фиксации: 17.07.2026.

Статус: технический blueprint до начала application implementation. Клиентские решения 1–68 закрыты; перечисленные ниже gates требуют инженерного evidence и UAT, а не нового общего опроса клиента.

## 1. Результат аудита Filament

Миграция Voyager → Filament декомпозирована до пяти связанных реестров:

| Поверхность | Покрытие | Source of truth |
|---|---:|---|
| Voyager BREAD → Filament Resource/Page | 133 из 133 | `appendix-filament-resource-map.csv` |
| BREAD fields → Filament form/table components | 1 997 из 1 997 | `appendix-filament-field-map.csv` |
| Admin navigation | 138 из 138 активных admin menu items | `appendix-filament-navigation-map.csv` |
| Roles | 8 из 8, включая две роли без `permission_role` assignments | `appendix-filament-role-map.csv` |
| Voyager permissions | 675 permissions / 2 080 role assignments | `appendix-filament-permission-map.csv` |
| Нестандартные admin routes | 58 из 58 ACL-аудита | `appendix-filament-custom-route-map.csv` |
| Переопределённые Voyager views | 40 из 40 | `appendix-filament-custom-view-map.csv` |

Эти карты являются blueprint, а не сгенерированным кодом. Target-классы считаются предложенными именами до создания Laravel 13 skeleton. Ни один Resource нельзя объявить готовым только потому, что для него создан PHP-класс.

## 2. Зафиксированные решения

1. Создаётся **одна общая Filament Panel**. CRM, Access, Catalog, Translations, Commerce, Content и SEO оформляются как navigation groups/Clusters внутри одной панели, а не как отдельные panels с независимой авторизацией.
2. Во время совместной работы target открывается на `/backoffice-next`, Voyager остаётся на `/admin`. После полного отключения Voyager и отдельной проверки URL target может занять `/admin`.
3. Используется текущий `web` guard и существующие пользователи. Совместимость cookie/session/`APP_KEY` доказывается тестом; массовый logout не выполняется по умолчанию.
4. В panel допускаются только пользователи, прошедшие `canAccessPanel()` и существующую role/permission policy. Видимость navigation не является авторизацией.
5. Сохраняются восемь ролей, 675 permission keys и 2 080 назначений. Неизвестное действие закрыто по умолчанию.
6. Публичный frontend, текущие CSS/JS и `mix-manifest.json` не пересобираются. Vite/Tailwind используются только в изолированном Filament theme entry.
7. Модели, первичные ключи и production-таблицы сохраняются на первой волне. `orders.id` остаётся order ID и CRM `lead_id`.
8. Для каждого модуля одновременно существует только один write-owner: Voyager либо Filament. Shadow target читает и сравнивает, но не пишет до cutover gate.
9. Все подтверждённые языки и три storage-контура переводов сохраняются: base columns + `translations`, `ltm_translations`, PHP dictionaries.
10. Новый стандартный дизайн Filament допустим; функциональный результат, права, данные, side effects и внешние контракты обязаны пройти parity.

## 3. Целевой технический baseline

| Компонент | Требование |
|---|---|
| PHP | 8.3/8.4 для Laravel 13 target |
| Laravel | 13.x с отдельным upgrade/skeleton gate |
| Filament | 5.x |
| Livewire | 4.x — обязательная зависимость Filament 5 |
| Tailwind | 4.x только для Filament theme |
| Node/Vite | отдельный admin entry; legacy public assets исключены из scan/build/purge |
| DB | существующая schema сначала остаётся backward-compatible с Voyager |
| Queue/locks | управляемые workers, failed-job policy, distributed locks и release-safe serialization |

Перед установкой фиксируются точные Composer/Node versions и совместимость каждого plugin. Официальный upgrade guide предупреждает, что часть Filament plugins может не иметь версии для v5: <https://filamentphp.com/docs/5.x/upgrade-guide>.

## 4. Одна Panel и внутренние Clusters

Предлагаемый provider:

`App\Providers\Filament\AdminPanelProvider`

Контракт provider:

- panel ID: `admin-next` на coexistence stage;
- path: `/backoffice-next` до финального route switch;
- guard: `web`;
- отдельные `authMiddleware` и production `canAccessPanel()`;
- Resources/Pages регистрируются явно или через ограниченные namespace discovery roots;
- navigation groups: `Заказы и CRM`, `Пользователи и роли`, `Чаты`, `Каталог и цены`, `Переводы`, `Платежи и доставка`, `Контент`, `SEO`, `Операции`;
- theme/CSP/assets не подключают legacy public CSS/JS;
- destructive/bulk/inline editing выключены до отдельного Policy и теста.

Предлагаемая структура:

```text
app/Filament/
  Clusters/
    Crm/
    Access/
    Catalog/
    Translations/
    Commerce/
    Content/
    Seo/
  Pages/
    Dashboard.php
    Operations.php
    MediaManager.php
    Settings.php
  Support/
    Authorization/
    Translations/
    Files/
    Receipts/
  Widgets/
app/Policies/
app/Domain/
  Orders/
  Payments/
  Delivery/
  Chat/
  Translations/
```

Clusters организуют код и navigation, но не создают вторую сессию или независимую permission model.

## 5. Карта 133 BREAD

`appendix-filament-resource-map.csv` содержит для каждой строки:

- legacy table/model/controller и исходный migration class;
- предложенный Resource/Page и PHP namespace;
- navigation group и приоритетную волну;
- field/table/translation/file contracts;
- actions и authorization contract;
- минимальные тесты;
- write-owner, cutover и rollback;
- implementation status.

Распределение:

| Волна | BREAD-модулей |
|---|---:|
| 1. Заказы и CRM | 8 |
| 2. Пользователи и роли | 3 |
| 4. Каталог и цены | 36 |
| 5. Переводы | 4 |
| 6. Платежи и доставка | 9 |
| 7. Контент и SEO | 73 |
| **Итого** | **133** |

Chats не представлены отдельным BREAD-контуром: их target описан custom route/view картами и входит в волну 3.

Два некорректных BREAD (`canvas`, `a-collag-faq`) не превращаются автоматически в работающие Resources. Они получают read-only evidence page до production usage proof. Пустой `payment-info` также не получает write UI без подтверждённого production-сценария.

## 6. Карта 1 997 полей

`appendix-filament-field-map.csv` построен из локальных `data_types/data_rows` через PHP 7.4 и содержит все 1 997 rows без пропусков.

| Voyager type | Количество | Предлагаемый Filament-подход |
|---|---:|---|
| `text` | 1 205 | `TextInput` / `TextColumn` с явными casts/limits |
| `timestamp` | 268 | `DateTimePicker` либо read-only lifecycle field |
| `image` | 117 | `FileUpload(image)` через legacy-path adapter |
| `rich_text_box` | 115 | `RichEditor` с HTML allowlist и XSS/CSP review |
| `number` | 95 | numeric `TextInput` / `TextColumn` |
| `checkbox` | 54 | `Toggle/Checkbox` / `IconColumn` |
| `text_area` | 30 | `Textarea` |
| `relationship` | 28 | scoped `Select/CheckboxList/RelationManager` |
| `select_dropdown` | 21 | `Select` с versioned options |
| `adv_media_files` | 19 | custom media adapter; generic plugin не подставляется автоматически |
| остальные типы | 45 | явные FileUpload/ColorPicker/DatePicker/CheckboxList/CodeEditor/Hidden contracts |

Каждая строка хранит BREAD-флаги `browse/read/edit/add/delete`, `required`, order, relationship details, validation rule, options и SHA-256 исходного `details`. DB-driven runtime form builder в target не используется: schema переносится в Git-versioned PHP и проверяется с этим CSV.

## 7. Навигация

`appendix-filament-navigation-map.csv` фиксирует все 138 активных admin menu items, полную parent path, route/URL, порядок, proposed target и проверку восьми ролей.

Правила:

1. Menu group переносится как Filament navigation group/Cluster.
2. Resource link виден только при `viewAny`/page ability, но direct URL проверяется отдельно.
3. Custom URLs связываются с target Page/Action из route map.
4. `BREAD`, `Database`, `Compass` не переносятся как production schema/console editors. Их capability заменяется reviewed migrations и local/staging diagnostics. Эти три позиции помечены `retire_security_exception` и требуют явного sign-off этапа 0.
5. Voyager Media заменяется ограниченной Page с allowlisted roots/types/actions и журналом; unrestricted root/MIME `*` не сохраняется.
6. Settings становятся typed Page поверх существующих keys; generic arbitrary type/key editing запрещён до manifest.

## 8. RBAC: 675 permissions и 2 080 assignments

`appendix-filament-permission-map.csv` содержит все permissions и все role assignments. Базовое соответствие:

| Voyager key | Filament/Laravel ability |
|---|---|
| `browse_*` | `viewAny` |
| `read_*` | `view` |
| `add_*` | `create` |
| `edit_*` | `update` |
| `delete_*` | `delete` |
| остальные keys | отдельная custom Action/Page ability без автоматического доступа |

Обязательные слои:

- `canAccessPanel()` — только perimeter;
- `Policy` — Resource и record scope;
- `Action::authorize()`/явная проверка — custom/toolbar/bulk actions;
- query scope — пользователь не получает чужие rows через table/search/export;
- `disabled()` + authorization для inline editable columns;
- direct Page/Livewire/API routes имеют ту же ability и ownership;
- изменение permission matrix логируется и не разрешает удалить последний защищённый admin access.

`appendix-filament-role-map.csv` отдельно сохраняет все восемь ролей. У `user` и `painter` в текущем snapshot нет строк `permission_role`; это не ошибка экспорта и не разрешение наследовать доступ. Для них Panel закрыта по умолчанию, пока конкретный custom workflow не получит доказанную Page/ability mapping. Остальные шесть ролей имеют `browse_admin` и проходят полную role×navigation×resource×action matrix.

Filament автоматически проверяет Policies для стандартных Resource operations, но custom Actions, Pages, Livewire и API требуют явной авторизации: <https://filamentphp.com/docs/5.x/advanced/security>.

## 9. Нестандартные маршруты и views

`appendix-filament-custom-route-map.csv` покрывает 58 маршрутов:

- Critical: 37;
- High: 17;
- Medium: 1;
- Low: 3.

Для каждой строки заданы target surface/class, module/wave, HTTP method contract, authorization, idempotency, audit receipt, tests, cutover и disposition.

Распределение route target по волнам:

| Волна | Routes |
|---|---:|
| 0. Foundation/operations/auth | 6 |
| 1. Заказы и CRM | 23 |
| 2. Пользователи и роли | 7 |
| 3. Чаты | 11 |
| 4. Каталог и цены | 1 |
| 6. Платежи и доставка | 4 |
| 7. Контент и SEO | 6 |

`appendix-filament-custom-view-map.csv` покрывает все 40 Voyager overrides. Blade markup не переносится механически. Сначала фиксируются fields, conditional visibility, buttons, routes, JS events, translations, files, side effects и role guards; затем поведение реализуется средствами Filament и проверяется UAT.

## 10. Package/plugin policy

| Область | Решение первой волны | Причина |
|---|---|---|
| Roles/permissions | Adapter к существующим tables + Laravel Policies | сначала доказать parity 675/2 080; не менять одновременно schema и UI |
| Filament Shield/Spatie Permission | не устанавливать автоматически | отдельный compatibility/schema/mapping spike после parity |
| Translations | собственный adapter к существующим storages | нельзя потерять base fields, `translations`, LTM и PHP files |
| Media | собственный safe adapter/Page | текущие paths/derivatives и принятая public delivery не совпадают с default plugin assumptions |
| Settings | typed custom Page над текущей таблицей | сохранить keys/`setting()` и запретить произвольный unsafe type change |
| Audit | domain receipts + admin action ledger | заказ/оплата/доставка/чат требуют idempotent business evidence, а не только generic model diff |
| Import/export | выключены по умолчанию | нужны per-record query scope, formula-injection handling, PII и permission tests |
| Bulk/inline editing | выключены по умолчанию | включать только после authorization/concurrency/duplicate tests |

Любой сторонний plugin добавляется только после проверки Filament 5/Livewire 4/Laravel 13 compatibility, license, maintenance, migrations, rollback и security review.

## 11. Обязательные технические spikes

| ID | Результат | Stop condition |
|---|---|---|
| FIL-SPK-01 | Laravel 13 + Filament 5 skeleton, одна Panel, `web` guard, isolated theme | public asset hashes изменились или session/auth несовместимы |
| FIL-SPK-02 | Role adapter и Policies для 8 ролей на representative Resource/Action | любое permission escalation или direct URL bypass |
| FIL-SPK-03 | Translation adapter: base + `translations` + LTM/PHP publisher, optimistic lock | потеря locale/key/value/status или два writer |
| FIL-SPK-04 | Order Resource read-only detail с scoped data, tabs/sections и audit-safe queries | N+1/latency, ID/amount/status mismatch, PII leak |
| FIL-SPK-05 | Один безопасный order Action через domain service/receipt/idempotency | duplicate/lost side effect или частичная запись |
| FIL-SPK-06 | Chat Page/RelationManager с ownership, ordering, attachment policy и dedupe | wrong-order access, duplicate message, потеря read semantics |
| FIL-SPK-07 | Legacy-path FileUpload/download adapter | URL/path/derivative regression, traversal, MIME bypass |
| FIL-SPK-08 | Menu + permission simulation всех 138×8 combinations | visibility/direct URL/action mismatch |

Spikes выполняются на isolated staging. Они не включают production write и не являются разрешением переключить модуль.

## 12. Per-Resource Definition of Done

Resource/Page готов только если выполнено всё:

1. поля и флаги сверены с field map;
2. list/search/filter/sort/pagination и query count подтверждены;
3. create/view/edit/delete/restore/reorder соответствуют legacy contract или явно disabled;
4. relationships проверены на null/orphan/pivot/ownership;
5. translations сверены по locale/key/value/base field и fallback;
6. files сохраняют согласованные URL/paths/derivatives и проходят validation;
7. Policy, Action ability, query scope, navigation и direct URL проверены для восьми ролей;
8. custom side effects проходят success/duplicate/failure/unknown-outcome tests;
9. optimistic locking/concurrency не создаёт lost update;
10. shadow counts/hashes/deltas совпадают;
11. письменный UAT PASS получен;
12. rollback write-owner проверен без восстановления старого общего DB dump.

## 13. Волны реализации

### Волна 0 — Foundation

- target runtime и Panel;
- theme isolation;
- auth/session/RBAC adapter;
- feature flags и read-only shadow mode;
- audit receipts, observability и deployment/runbook;
- отключение GET/ANY mutations в новых routes;
- explicit Resource registry и базовые test helpers.

### Волна 1 — Заказы и CRM

Сначала read-only OrderResource, затем малые Actions. Orders не становятся generic CRUD. Все status/payment/mail/bonus/coupon/file/SA effects проходят отдельный domain contract.

### Волна 2 — Пользователи и роли

UserResource, RoleResource, permission matrix, Socialite/account/session compatibility. Role editing включается только после 675/2 080 simulation.

### Волна 3 — Чаты

Четыре потока остаются раздельными domain contracts; ownership/read flags/attachments/SA sync проверяются отдельно.

### Волна 4 — Каталог и цены

36 Resources, relationships, ordering, pricing, gallery/media and public read parity. Public frontend остаётся frozen.

### Волна 5 — Переводы

Один versioned publisher, all locale manifests, production wins, dormant keys, CN/JP archive rules и 14-case UAT.

### Волна 6 — Платежи и доставка

Provider adapters остаются legacy-owned до contract tests и controlled smoke. Venipak/PayPal/Paysera/mail callbacks требуют receipts и reconciliation.

### Волна 7 — Контент, блог, галерея и SEO

Оставшиеся 73 BREAD, custom SEO/ALT batches, media, navigation/settings и legacy custom screens. URL/canonical/sitemap/feed contracts не меняются.

## 14. Cutover и rollback

Для каждой волны:

1. Voyager production — source of truth;
2. Filament read-only shadow и differential checks;
3. final delta rows/translations/files/permissions;
4. short component action gate;
5. только один writer;
6. суббота 20:00 Europe/Riga;
7. smoke/reconciliation;
8. 24 часа observation + минимум 48 часов monitoring;
9. при stop condition target writes/navigation выключаются, Voyager снова становится writer;
10. новые production orders/payments/messages/translations не уничтожаются rollback-операцией.

## 15. Оставшиеся execution gates — не вопросы клиенту

1. Зафиксировать точные Composer/Node lock versions target skeleton.
2. Выполнить FIL-SPK-01…08.
3. Привязать 1 997 fields к фактическим Eloquent casts/accessors/mutators и DB constraints.
4. Снять production access frequency для двух stale BREAD и одного unresolved custom navigation item.
5. Получить sign-off на замену BREAD/Database/Compass reviewed migrations/dev tooling вместо production editors.
6. Подписывать UAT и конкретный go/no-go перед каждой волной.

Новые общие вопросы по цели, панели, ролям, языкам, frontend, сроку, отчётности или рискам не требуются. Если spike обнаружит новый бизнес-вариант, он оформляется отдельным change request с влиянием на часы и прогноз.

## 16. Следующее действие

Исполняемый пакет этапа 0: `54-filament-stage0-execution-pack.md`. Начать с S0-01/FIL-SPK-01: сохранить legacy/public hash baseline, затем поднять Laravel 13 / Filament 5 skeleton в isolated staging, зарегистрировать одну Panel на `/backoffice-next`, подключить read-only user/session adapter и доказать, что публичные asset hashes и production writes не затронуты.
