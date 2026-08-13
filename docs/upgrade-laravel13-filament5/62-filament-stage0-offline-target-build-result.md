# 62. Filament Stage 0 — offline target build result

Дата: 17.07.2026. Статус: **offline skeleton/artifact PASS; test остаётся active на PHP 7.4; server upload/switch не выполнялись**.

## 1. Изоляция

Новый target создан отдельно:

- legacy source: `C:\OSPanel\domains\asoft\viar` — не изменялся application code;
- target source: `C:\OSPanel\domains\asoft\viar-next`;
- artifact directory: `C:\OSPanel\domains\asoft\viar-next-artifacts`;
- active `test.viarcanvas.com`: без изменений, `PHP-7_4`, `SUSPENDED: no`.

Это не in-place Composer update Laravel 6 и не копия текущего test. Ни production, ни test DB/Redis/providers не подключались.

Relocation update 21.07.2026: канонический Git working tree теперь находится в `G:\OSPanel\home\viar_filament`. Исходный путь `C:\OSPanel\domains\asoft\viar-next` выше сохранён как историческое место сборки и после проверенного переноса удалён. Сверено 16 361 файл / 97 385 517 bytes; для последних 6 977 файлов выполнено сравнение size + SHA-256, `missing=0`, `mismatch=0`. Проверенный deployment artifact в `C:\OSPanel\domains\asoft\viar-next-artifacts` не перемещался и не изменялся.

Post-relocation verification из `G:\OSPanel\home\viar_filament`: Laravel Framework 13.20.0 boot PASS; Composer validate PASS; Composer platform requirements PASS под PHP 8.3.30 с `ext-intl`; test suite 6/6, 8 assertions PASS.

## 2. Frozen application baseline

| Компонент | Версия / результат |
|---|---|
| Laravel application skeleton | `13.8.0` |
| Laravel framework | `13.20.0` |
| Filament | `5.6.8` |
| Livewire | `4.3.3` |
| PHP solver platform | `8.3.0` |
| Composer | project-local `2.9.5` |
| Composer packages | 111 runtime + 33 dev |
| npm lock packages | 101 |
| Security | Composer 0 advisories; npm 0 vulnerabilities |

Panel создана одна: ID `backoffice-next`, path `/backoffice-next`. Зарегистрировано ровно три начальных route: dashboard, login, logout.

## 3. Safety baseline

- `FILAMENT_PANEL_ENABLED=false`: panel возвращает 404 по умолчанию;
- `User::canAccessPanel()` deny-by-default до реализации legacy ACL adapter;
- `FILAMENT_WRITE_ENABLED=false`;
- `STAGE0_OUTBOUND_ENABLED=false`;
- mail=`log`, queue=`sync`, PayPal sandbox=true, Facebook disabled;
- отдельные session cookie, cache prefix, Redis prefix/DB placeholders;
- `.env` и provider credentials в artifact отсутствуют;
- `.env.example` и `.env.stage0.example` безопасны и не содержат production/test credentials;
- старый `.env` запрещено копировать;
- npm/Vite build не запускался; legacy public CSS/JS не менялись;
- опубликованы только prebuilt Filament assets нового target: 37 files / 4 324 395 bytes.

## 4. Verification

Локальный PHP 8.3.30 имеет `php_intl.dll`, но extension не включён в loaded `php.ini`; проверки target выполнялись с временным `-d extension=php_intl.dll`. Installed php.ini не менялся. Server PHP 8.3 ранее прошёл normal CLI/FPM `ext-intl` gate и на server не должен использовать ignore flags.

PASS:

- Composer `validate --strict`;
- `check-platform-reqs --lock`, включая `ext-intl`;
- Composer audit: 0;
- npm audit: 0;
- PHP lint: 29 files / 0 failures;
- Pint;
- 6 tests / 8 assertions;
- panel hidden by default;
- enabled panel guest redirect → `/backoffice-next/login`;
- all users denied before ACL adapter;
- write/outbound flags disabled.

Первый packaging rehearsal выявил, что overly broad exclude удалял required empty `storage/framework/views`; archive был отклонён и заменён. Финальный archive сохраняет directory placeholders. Clean extraction rehearsal затем прошёл: bundled Composer install 144 packages, APP_KEY generation во временном isolated `.env`, package discovery, platform check, audit и 6/6 tests. Временная verification copy удалена.

## 5. Artifact

- File: `C:\OSPanel\domains\asoft\viar-next-artifacts\viar-next-stage0-20260717T123427Z.tar.gz`.
- Size: 2 267 407 bytes.
- Entries: 164.
- Forbidden entries: 0.
- SHA-256: `5f279d54e07d4d28e61340ddfce38997e67c92e456d4967a49ccf4c51cd6600b`.
- Checksum file: same path + `.sha256`.

Artifact содержит source, exact locks, 37 Filament assets, tests, safe env templates, build manifest, server verification script и Composer 2.9.5. Не содержит `.env`, `vendor`, node_modules, SQLite, runtime caches/logs или legacy files.

Ключевые hashes:

- `composer.lock`: `054fc367b324891b1c60863743af02b82754ceb8e1e25cf618bd072b84fe1b71`;
- `package-lock.json`: `aad66713652b730c45f5ce0f46b58d0f3c918e0d03db34cd99f388837928a52b`;
- Composer PHAR: `c86ce603fe836bf0861a38c93ac566c8f1e69ac44b2445d9b7a6a17ea2e9972a`;
- canonical source tree: `ebd5d44b44e149f3c8a7e1252c8f50a662f5ab4488ae43cdaeb4218fafe8cbc8` (102 files / 8 219 907 bytes; excludes manifest/runtime/vendor).

## 6. Что ещё не сделано

Этот artifact не означает готовую миграцию админки. Ещё отсутствуют:

- legacy users/auth/session adapter;
- 8-role/675-permission adapter;
- Orders/CRM Resources и Actions;
- chats/files adapters;
- translations publisher и все существующие языки;
- catalogue/prices/content/SEO modules;
- sanitized staging DB и import/reconciliation;
- provider sandbox contracts;
- server-side offline release install и PHP 8.3 FPM mapping;
- UAT и atomic switch rehearsal.

Следующий безопасный шаг: загрузить artifact в отдельный non-public release directory рядом с test, проверить SHA-256 и выполнить PHP 8.3 Composer install без изменения active `public_html`, Hestia backend или `.env` текущего test. Переключение по-прежнему запрещено.
