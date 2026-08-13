# 54. Filament Stage 0 Execution Pack

Дата фиксации: 17.07.2026.

Статус: готово к согласованию и последующему выполнению на isolated staging. Этот пакет описывает подготовительный этап и технические spikes; он не разрешает изменять production или включать Filament как writer.

Execution decision 21.07.2026: выбран режим **local-first**. `S0-03…S0-08` выполняются в `G:\OSPanel\home\viar_filament` на OpenServer PHP 8.3 с локальной DB и выключенными write/outbound flags. `S0-02B…S0-02D` (server upload, isolated server config и preview) отложены до готовности рабочей локальной админ-панели и отдельного owner GO. Это меняет порядок, но не отменяет server/staging/rollback gates перед приёмкой Stage 0 или production.

## 1. Цель Stage 0

Создать доказуемо безопасную основу Laravel 13 / Filament 5, на которой можно поэтапно переносить Voyager без изменения публичного frontend, потери текущих сессий, расширения прав и конкурирующей записи в production.

Stage 0 закрывается не фактом установки Filament, а комплектом evidence:

1. воспроизводимый target skeleton;
2. одна Panel на `/backoffice-next`;
3. изолированная admin-сборка;
4. проверенный user/session adapter;
5. симуляция восьми ролей, 138 menu items и 675 permissions;
6. translation adapter spike;
7. read-only OrderResource и одна безопасная Action;
8. chat/file adapter spikes;
9. feature flags, observability, CI и rollback runbook.

## 2. Входит и не входит в этап

### Входит

- Laravel 13 / Filament 5 / Livewire 4 target skeleton;
- одна Panel и внутренние Clusters;
- подключение к sanitized staging dataset;
- read-only adapters к legacy users/roles/permissions/translations/orders/chats/files;
- ограниченные spike writes только в synthetic staging fixtures;
- автоматические tests/evidence для FIL-SPK-01…08;
- deployment/cutover scaffolding без production switch.

### Не входит

- включение production-записи через Filament;
- отключение Voyager;
- обновление или пересборка публичных CSS/JS;
- массовая миграция/нормализация данных;
- смена permission schema на Spatie/Shield;
- перемещение media/files;
- реальная оплата, Venipak label, production SMTP или Synvolve write;
- обещание полного Orders/CRM cutover.

## 3. Предварительные условия

Перед server preview/cutover должны существовать:

- отдельный staging hostname и document root;
- PHP 8.3/8.4 CLI/FPM с необходимыми extensions;
- sanitized DB/files без production PII;
- отдельные cache/session/queue prefixes или logical databases;
- outbound deny по умолчанию;
- sinks/fakes для mail, payments, delivery и webhooks;
- отдельные `.env`, APP URL, cookie name/domain и storage paths;
- immutable snapshot текущих Composer/Node locks и public asset hashes;
- назначенный программист и дата первого weekly report.

Если staging разделяет cookie, Redis keys, queues, storage write paths или external credentials с production, Stage 0 не начинается.

Для local-first реализации до server phase достаточно закрытого `S0-02L`: отдельный local APP_KEY/cookie/cache/Redis namespace, mail log, queue sync, write/outbound false, read-only DB baseline и тесты. Локальная DB не передаётся на сервер и не считается sanitized staging artifact.

## 4. Оценка Stage 0

| ID | Пакет | Часы | Связанные spikes |
|---|---|---:|---|
| S0-01 | Evidence freeze и exact target dependency locks | 12–20 | prerequisite |
| S0-02 | Laravel 13 skeleton, одна Panel и isolated theme | 20–32 | FIL-SPK-01 |
| S0-02L | OpenServer/local env/DB runtime gate | 4–8 | local-first prerequisite |
| S0-03 | User/auth/session compatibility | 16–28 | FIL-SPK-01 |
| S0-04 | RBAC adapter, navigation и role simulation | 28–50 | FIL-SPK-02, FIL-SPK-08 |
| S0-05 | Translation adapter и versioned publisher spike | 20–36 | FIL-SPK-03 |
| S0-06 | Read-only OrderResource и одна безопасная Action | 24–42 | FIL-SPK-04, FIL-SPK-05 |
| S0-07 | Chat и legacy-path file adapters | 20–40 | FIL-SPK-06, FIL-SPK-07 |
| S0-08 | Feature flags, receipts, observability и deployment scaffold | 12–20 | cross-cutting |
| S0-09 | CI evidence, runbook, отчёт и Stage 0 sign-off | 8–12 | closure |
| **Итого** |  | **164–288** | **4–7,2 инженерных недели** |

Диапазон рассчитан для одного программиста. Ожидание инфраструктуры, доступов и sign-off не является инженерными часами, но влияет на календарный прогноз.

## 5. S0-01 — Evidence freeze и dependency locks

### Действия

1. Зафиксировать commit/branch исходного legacy snapshot.
2. Снять SHA-256 `composer.lock`, `package-lock.json`, `mix-manifest.json` и всех public CSS/JS bundles.
3. Зафиксировать PHP/extensions, Composer, Node, npm, MariaDB, Redis, Nginx/FPM target.
4. Получить Composer solver result для Laravel 13 / Filament 5 / Livewire 4 без изменения legacy lock.
5. Для каждого планируемого plugin записать version, license, Laravel/Filament/Livewire support, migrations и rollback.
6. Выбрать точные target versions и создать version manifest.

### Evidence

- `stage0-target-versions.json`;
- legacy/public hash manifest;
- dependency compatibility report;
- список rejected/deferred plugins.

### Stop

- solver требует изменить legacy production dependencies;
- plugin не поддерживает Filament 5/Livewire 4;
- target требует включить public frontend в Tailwind/Vite pipeline.

### Фактический результат S0-01 — 17.07.2026

Evidence freeze выполнен без изменения application code и legacy locks. Зафиксированы commit `bc8f95636214fa633b77bbbd75c0f6bdc180d1d6`, пять legacy dependency/build hashes, `mix-manifest.json`, все 12 public bundles и canonical asset root.

Изолированный solver разрешил Laravel 13.20.0 / Filament 5.6.8 / Livewire 4.3.3 и target-only Vite 8.1.5 / Tailwind 4.3.3. Optional third-party Filament plugins в baseline отсутствуют. Статус `CONDITIONAL GO`: разрешён только provisioning isolated staging. S0-02 installation блокируется, пока PHP CLI/FPM не имеют одинаковый required module set и обязательный `ext-intl`.

Evidence: `55-filament-stage0-s01-evidence-freeze.md`, `stage0-target-versions.json` и четыре `appendix-stage0-*.csv`.

## 6. S0-02 — Skeleton, Panel и theme isolation

Provisioning preflight до любых установок: `56-filament-stage0-s02-provisioning-preflight.md`. Первоначально проверялся dev; 17.07.2026 пользователь выбрал существующий test как disposable staging. TEST-S02-A выполнен и разобран в документе 60; replacement и skeleton запрещены до quarantine PASS.

Факт 17.07.2026: server PHP 8.3.30 CLI/FPM, required extensions и dedicated dev pool получили PASS. Domain остаётся suspended, Nginx указывает на Hestia suspend template, staging `.env`/storage/isolation отсутствуют. Следующий шаг — read-only S02-D из `57-filament-stage0-s02-preflight-result.md`; S0-02 application installation не начата.

S02-D result: origin listener отвечает HTTP/2 через assigned IP, Laravel web template доступен, dev root пуст; public DNS `dev.viarcanvas.com` — NXDOMAIN у authoritative Cloudflare. Затем пользователь выбрал `test.viarcanvas.com` как future staging и разрешил replacement после готовности target. TEST-S02-A подтвердил отдельные DB/storage и отсутствие worker/cron, но также общий с production APP_KEY, SMTP/PayPal/Google credentials и live PayPal mode. TEST-S02-B PASS: root-only config backup + SHA-256 сохранены, test временно suspended, Nginx PASS. После этого пользователь потребовал вернуть текущий test в работу; TEST-S02-C поставлен на hold до готового offline target artifact.

### Действия

1. Создать отдельный Laravel 13 target skeleton, не обновлять framework внутри работающего Laravel 6 tree.
2. Установить Filament 5 и Livewire 4 в target.
3. Создать один `AdminPanelProvider` с path `/backoffice-next`.
4. Зарегистрировать Clusters: CRM, Access, Catalog, Translations, Commerce, Content, SEO.
5. Настроить отдельный Filament Vite/Tailwind entry.
6. Запретить scan/import legacy public assets.
7. Добавить health endpoint и build/release ID.

### Acceptance

- target panel login page открывается на isolated staging;
- legacy public asset manifest и hashes до/после идентичны;
- target theme удаляется одним artifact/feature-flag rollback;
- panel не доступна по production domain/path;
- target не пишет в production DB/files/queues/providers.

### Stop

- изменился хотя бы один legacy public asset;
- route/cookie collision с `/admin`;
- target загружает production provider credentials.

## 7. S0-03 — Auth и session compatibility

### Действия

1. Подключить read-only `User` adapter/casts к sanitized schema.
2. Реализовать `FilamentUser::canAccessPanel()` без проверки только по имени роли.
3. Сверить guard, provider, password hashes, remember tokens, session driver, cookie name/domain/path/Secure/SameSite и APP_KEY strategy.
4. Проверить login/logout/password reset и переход между legacy/target.
5. Проверить cart/order/account continuity без массового удаления sessions.
6. Проверить privilege change: следующая Livewire/HTTP операция должна увидеть новые права.

### Матрица

- anonymous;
- invalid credentials;
- admin;
- manager/Admin 2/printing/lite_manager/seo_manager;
- `user` и `painter` без permission assignments;
- blocked/deleted user;
- active session до/после target deploy;
- remember token;
- session fixation/regeneration.

### Stop

- target допускает `user`/`painter` только по факту наличия роли;
- требуется массовый logout без отдельного auth-wave решения;
- cart/session теряются;
- target и legacy создают конфликтующие cookies.

## 8. S0-04 — RBAC, navigation и simulation

### Sources

- `appendix-filament-role-map.csv`;
- `appendix-filament-permission-map.csv`;
- `appendix-filament-navigation-map.csv`;
- `appendix-filament-custom-route-map.csv`.

### Действия

1. Реализовать adapter existing roles/permissions без смены schema.
2. Сопоставить CRUD prefixes с Policies.
3. Создать versioned manifest custom Page/Action abilities.
4. Применить `canAccessPanel`, `Policy`, query scope и explicit Action authorization.
5. Запретить bulk/inline editing/export до собственных checks.
6. Выполнить simulation 8 roles × 138 menu items × direct target URLs.
7. Для sensitive actions проверить record ownership и audit receipt.

### Acceptance

- ноль permission escalations;
- navigation visibility совпадает с intended legacy access;
- hidden page закрыта по direct URL и Livewire request;
- `user`/`painter` остаются без автоматического Panel access;
- unassigned/unknown permission не выдаёт доступ;
- изменение permission действует на следующем request.

### Stop

- menu visibility используется вместо Policy;
- query возвращает запрещённые rows;
- inline/bulk/export обходят record scope;
- role name `admin` используется как единственный unrestricted bypass без protected Gate/test.

## 9. S0-05 — Translation adapter spike

### Действия

1. Реализовать read projection base fields + `translations`.
2. Добавить чтение LTM groups/status и PHP dictionaries как отдельных sources.
3. Показать семь активных locale без изменения значений.
4. Сохранить `et`, dormant keys и CN/JP archive rules.
5. Реализовать optimistic lock/version check.
6. Создать versioned publisher только для staging artifact.
7. Выполнить hash/count/key/value/status comparison и rollback artifact.

### Acceptance

- production wins для согласованных конфликтов;
- все R5–R14 roots/deltas воспроизводятся;
- нет потерянных locale/file/key/value/status;
- два editor/writer одновременно невозможны;
- syntax-invalid PHP artifact не публикуется;
- 14-case staging UAT может быть выполнен повторяемо.

### Stop

- adapter выполняет одноразовый импорт без дальнейшей delta strategy;
- base fields и translation rows расходятся без conflict UI;
- target автоматически переименовывает `ee↔et` или активирует CN/JP.

## 10. S0-06 — OrderResource и безопасная Action

### Read-only Resource

- order ID/lead ID;
- user/customer and sanitized contact section;
- status/payment/delivery snapshots;
- items/prices/discounts;
- assignments;
- files/PDF references;
- chats and SA receipts;
- query scopes, eager loading и pagination.

### Action spike

Выбирается одна обратимая synthetic staging Action с явной ability, validation, DB transaction/outbox or receipt, idempotency key и failure injection. Она не должна вызывать real mail/payment/delivery/webhook.

### Acceptance

- row/amount/status/item/file counts совпадают с legacy fixture;
- `orders.id` неизменен;
- wrong-role/foreign-order access закрыт;
- query count и p95 baseline не хуже утверждённого threshold;
- duplicate Action даёт один effect;
- failpoint не оставляет partial local/file state;
- rollback возвращает legacy owner без удаления новых rows.

## 11. S0-07 — Chat и file adapters

### Chat spike

- сохранить раздельные legacy streams;
- scoped order/conversation lookup;
- ordering, read flags и actor attribution;
- duplicate message key;
- sanitized attachment fixture;
- no real SA/mail delivery.

### File spike

- сохранить current URL/path/derivative compatibility;
- canonical allowed root;
- random safe name для новых test uploads;
- extension + real MIME + decode + size/count/dimension checks;
- safe download headers;
- policy/ownership для target actions;
- filesystem journal/failpoints.

### Stop

- произвольный `order_id/chatId/image_id` открывает чужие данные;
- повтор создаёт второе сообщение/файл;
- path traversal/symlink escape;
- raw active content или MIME mismatch проходит validation;
- spike расширяет текущий public file scope.

## 12. S0-08 — Feature flags, receipts и observability

Минимальные flags:

- `FILAMENT_PANEL_ENABLED`;
- `FILAMENT_<MODULE>_VISIBLE`;
- `FILAMENT_<MODULE>_WRITE_ENABLED`;
- provider-specific write flags;
- translation publisher flag.

Минимальные telemetry/evidence:

- release/build ID;
- panel request/error/latency;
- auth denied/allowed без PII;
- query/queue failures;
- action receipt/idempotency outcome;
- data/hash drift;
- external call blocked/sent/unknown;
- disk/memory/worker health.

Flag не является authorization. Write flag проверяется после Policy и до domain mutation. Rollback procedure обязана отключать target write независимо от availability UI.

## 13. S0-09 — CI, runbook и closure

CI gates:

1. Composer audit/validate и locked install;
2. PHP lint/static analysis;
3. Laravel/Filament/Livewire tests;
4. 8-role authorization matrix;
5. resource/field/menu/permission manifest consistency;
6. public asset hash invariant;
7. translation manifest/hash check;
8. migration backward-compatibility check;
9. no production outbound/credentials in staging;
10. deployment + rollback rehearsal.

Stage 0 report содержит:

- план/факт часов по S0-01…09;
- exact versions и build ID;
- PASS/FAIL каждого FIL-SPK;
- defects и residual risks;
- изменившийся прогноз волн 1–7;
- решение `GO`, `CONDITIONAL GO` или `NO-GO` для Orders/CRM implementation.

## 14. Stage 0 Definition of Done

Stage 0 закрыт только когда:

- FIL-SPK-01…08 имеют письменный PASS либо явно принятый deferred item без production write;
- target воспроизводимо разворачивается из lock/artifact;
- одна Panel и isolated theme подтверждены;
- session/auth compatibility доказана;
- все восемь ролей, 138 menu items, 675 permissions и 2 080 links проходят simulation;
- translation, order, chat и file spikes не теряют данные/права;
- public asset hashes неизменны;
- outbound deny доказан;
- deployment/rollback rehearsal успешен;
- клиент принимает отчёт этапа и отдельно разрешает начало волны 1.

## 15. Еженедельный отчёт

Каждую неделю фиксируются:

1. завершённые S0-пакеты и evidence links;
2. фактические часы против диапазона;
3. тесты PASS/FAIL;
4. новые дефекты/риски;
5. блокеры и требуемый owner;
6. изменение прогноза Stage 0 и milestone до 01.10.2026;
7. план следующей недели;
8. change requests отдельно от согласованного scope.

## 16. Следующее действие

Staging hostname для будущего переключения выбран: `test.viarcanvas.com`, owner — программист. TEST-S02-B backup PASS; test успешно восстановлен: `SUSPENDED: no`, `PHP-7_4`, Nginx PASS. TEST-S02-C не выполнять. Offline target build PASS: отдельный Laravel 13.20.0 / Filament 5.6.8 artifact создан и прошёл clean extraction/install/platform/audit/6-test rehearsal; детали в документе 62. Следующий шаг — offline server release install вне active `public_html`, без Hestia/backend switch; legacy public assets не пересобирать.
