# S0-03 — Локальная совместимость users, auth и sessions

```text
TASK_ID: S0-03
STATUS: DONE
PRIORITY: Critical
OWNER: программист
BRANCH: codex/s0-03-local-auth-session
CREATED_AT: 2026-07-21
UPDATED_AT: 2026-07-21
DEPENDS_ON: S0-02L=DONE
NEXT_ACTION: задача закрыта; продолжить S0-04 по отдельной карточке
```

## Цель

Подключить Laravel 13 / Filament 5 к существующей локальной схеме пользователей без изменения legacy-данных и доказать совместимость password hashes, guard/provider, panel access, session/cookie и cart continuity.

## Входит в задачу

- read-only inventory legacy `User` model, таблиц users/roles/permissions/permission_role и auth routes/config;
- target User adapter/casts/interfaces, необходимые для Laravel 13 и Filament 5;
- `FilamentUser::canAccessPanel()` deny-by-default;
- совместимость существующих password hashes и remember tokens;
- отдельные target cookie/session names и защита от session fixation;
- login/logout и минимальные password-reset contracts без production mail;
- тесты anonymous/invalid/admin/всех восьми ролей/blocked or deleted user;
- проверка, что `user` и `painter` без подтверждённых permission assignments не получают panel access;
- read-only проверка локальной DB; все target writes/outbound остаются выключены.

## Не входит

- перенос на сервер, изменение `test.viarcanvas.com` или production;
- включение Filament CRUD/actions;
- полная реализация 675 permissions и 138 menu items — это `S0-04`;
- Socialite Facebook/Google — отдельная `AUTH-002`/`INT-005`;
- массовый logout, сброс паролей или изменение legacy password hashes;
- изменение существующих orders, chats, translations или files;
- отправка реальной почты и внешние provider calls.

## Предварительные условия

- [x] `S0-02L` закрыта: OpenServer PHP 8.3, Laravel boot, local DB `SELECT 1` и tests PASS;
- [x] target расположен в `G:\OSPanel\home\viar_filament`;
- [x] новый APP_KEY сохранён; отдельный session cookie задан;
- [x] `FILAMENT_WRITE_ENABLED=false` и `STAGE0_OUTBOUND_ENABLED=false`;
- [x] mail использует `log`, queue — `sync`;
- [x] задача явно переведена в `IN_PROGRESS`, указана ветка;
- [x] до первой реализации снят read-only auth/schema inventory без PII output.

## План выполнения

- [x] зафиксировать legacy и target auth/guard/provider/password/session contracts;
- [x] проверить schema/index/nullability для users/roles/permissions и связей без вывода PII;
- [x] создать characterization tests до изменения adapter;
- [x] реализовать минимальный target User adapter и panel access policy;
- [x] реализовать/настроить локальный login/logout/session regeneration;
- [x] прогнать role/access matrix и negative direct-URL tests;
- [x] проверить remember/session/cookie/cart compatibility в рамках локального контура;
- [x] выполнить полный target test suite, Composer platform check и secret-safe log review;
- [x] приложить evidence и обновить документы 64, 65 и running log.

## Definition of Done

- [x] существующий разрешённый admin входит в локальную Filament panel без изменения password hash — ручная проверка пользователя PASS;
- [x] anonymous, invalid, deleted/missing и неподтверждённые роли получают ожидаемый отказ; `users.status` не используется legacy auth-кодом и поэтому не объявлен новым блокирующим флагом без бизнес-правила;
- [x] восемь legacy roles имеют явно зафиксированный результат доступа без privilege escalation;
- [x] login регенерирует session ID, logout завершает target session;
- [x] target cookie не конфликтует с legacy cookie;
- [x] нет массового удаления/изменения legacy sessions или remember tokens;
- [x] orders/chats/translations/files DB diff равен нулю для read-only сценариев;
- [x] реальные mail/webhook/payment/provider side effects равны нулю;
- [x] релевантные tests и platform check PASS;
- [x] evidence приложена без секретов и персональных данных;
- [x] обновлены центр управления задачами и хронологический журнал.

Локальная непрерывность корзины здесь означает сохранение `basket` при
регенерации target session во время входа. Перенос уже существующей legacy
cookie/session через финальное production-переключение этим тестом не доказан:
staging намеренно использует другой `APP_KEY` и cookie. Этот отдельный cutover-
контракт остаётся в `OPS-003` и должен исключить незапланированный массовый
logout/потерю корзины.

## Evidence

| Дата | Evidence | Результат |
|---|---|---|
| 2026-07-21 | `S0-02L`: OpenServer PHP 8.3.14, DB `SELECT 1`, Laravel 13.20.0, tests 6/6 | prerequisite PASS |
| 2026-07-21 | Read-only schema: users=23 801; roles=8; permissions=675; permission_role=2 080; user_roles=0; password_resets=172; sessions=1 | PASS, PII не выводились |
| 2026-07-21 | Password algorithm aggregate: 23 801 × bcrypt `$2y$` | PASS |
| 2026-07-21 | Voyager middleware + role map: `browse_admin`; роли 1/4/5/6/7/8 allow, 2/3 deny | PASS |
| 2026-07-21 | Failing characterization baseline до adapter: 5 tests, 2 failures + 1 error | ожидаемое доказательство red→green |
| 2026-07-21 | Target full suite после реализации: 22 tests / 55 assertions | PASS |
| 2026-07-21 | Pint; Composer validate strict; Composer non-dev platform requirements | PASS |
| 2026-07-21 | Local HTTPS: login=200, guest panel=302→login, password reset=200 | PASS |
| 2026-07-21 | Реальная local DB role probe: admin/printing/seo_manager allow; user/painter deny; роли без назначенных пользователей подтверждены synthetic matrix | PASS |
| 2026-07-21 | Before/after counts: orders=14 423; order_user_comments=3 835; translations=64 779; ltm_translations=21 450; users=23 801; sessions=1; password_resets=172 | zero count delta |
| 2026-07-21 | Ручной вход существующей разрешённой admin-учётной записью через `/admin/login` | PASS, панель работает |

## Журнал работы

| Дата | Что сделано | Блокер | Следующее действие |
|---|---|---|---|
| 2026-07-21 | Задача переведена в `IN_PROGRESS`, ветка `codex/s0-03-local-auth-session` создана | нет | выполнить read-only inventory |
| 2026-07-21 | Inventory завершён; characterization red baseline зафиксирован; реализованы legacy User/Role/Permission adapter, login/logout/reset/session/cart contracts и non-destructive migration boundary | семантика `users.status` не подтверждена и в legacy auth не применяется | прогнать реальную read-only матрицу и полный suite |
| 2026-07-21 | Реальная local DB matrix и zero-count-delta PASS; панель локально включена, write/outbound остаются false; задача переведена в `VERIFY` | нужен ручной login разрешённым admin | выполнить один ручной вход и закрыть S0-03 |
| 2026-07-21 | По решению пользователя публичный path изменён с `/backoffice-next` на `/admin`; внутренний panel ID сохранён | Voyager и Filament нельзя одновременно публиковать на одном домене под `/admin` | повторить suite/HTTP probes, затем ручной login `/admin/login` |
| 2026-07-21 | Пользователь подтвердил успешный вход и работу локальной панели | нет | S0-03 `DONE`; открыть S0-04 |

## Stop conditions

- любая команда обращается к production/test server вместо локального target;
- требуется изменить существующие password hashes или массово завершить sessions;
- `user`/`painter` получают доступ только по имени роли без подтверждённой policy;
- target и legacy используют конфликтующие cookie names/domains;
- тест вызывает реальную почту, payment, webhook или другой outbound;
- read-only auth flow изменяет orders/chats/translations/files;
- для продолжения нужны PII/secrets в документации или tool output.
