# 55. Filament Stage 0 — S0-01 evidence freeze

Дата фиксации: 17.07.2026. Статус: **CONDITIONAL GO только на подготовку isolated staging; NO-GO на установку в legacy и на S0-02 до закрытия PHP extension gate**.

## 1. Результат

S0-01 выполнен как read-only/documentation step. Application code, legacy dependency declarations/locks, БД и production не изменялись. До начала сбора evidence рабочее дерево было чистым.

Зафиксированы:

- legacy snapshot: branch `main`, commit `bc8f95636214fa633b77bbbd75c0f6bdc180d1d6`;
- SHA-256 пяти legacy dependency/build artifacts и `public/mix-manifest.json`;
- SHA-256 всех 12 фактически перечисленных public CSS/JS bundles;
- canonical public asset root `e6b47e10bc8da6080b377dee23c4b78e1c5f25146e3a8f07903e0d2f084ee80d`, 3 045 730 bytes;
- локальные legacy/default runtimes и ранее полученный production runtime baseline;
- изолированные exact Composer и npm locks для target core/theme;
- политика optional Filament plugins: baseline без сторонних plugins.

Полные артефакты: `stage0-target-versions.json`, `appendix-stage0-legacy-lock-hashes.csv`, `appendix-stage0-public-asset-hashes.csv`, `appendix-stage0-runtime-baseline.csv`, `appendix-stage0-plugin-decisions.csv` и `.audit-tmp/stage0-solver/`.

## 2. Исправленный runtime baseline

Текущий Laravel 6 проект локально запускается явным `C:\OSPanel\modules\php\PHP_7.4\php.exe`, версия PHP 7.4.30. Default `php`/Composer используют отдельный PHP 8.3.30 из `C:\dev\php\php8.3\php.exe`; это не runtime legacy приложения.

Read-only runtime check подтвердил локально MariaDB 10.6.9 и Redis 5.0.14.1. Подключение приложения к Redis сейчас пытается выполнить `AUTH`, хотя локальный Redis не имеет пароля; прямой read-only `PING` вернул `PONG`. Это локальная конфигурационная разница, не основание менять production.

Production evidence от 16.07.2026 остаётся: PHP 7.4.33 FPM, MariaDB 10.11.16 и Redis 7.0.15. Первый target не совмещает миграцию админки с major-upgrade БД/Redis.

## 3. Composer solver result

Solver выполнялся только в `.audit-tmp/stage0-solver`, с отключёнными scripts/plugins и без install в legacy tree. При platform PHP 8.3.0 разрешены:

| Пакет | Exact version |
|---|---:|
| `laravel/framework` | 13.20.0 |
| `filament/filament` | 5.6.8 |
| `livewire/livewire` | 4.3.3 |

Target `composer.lock`: 108 packages, SHA-256 `02419848c492981859a3edf7e11658734abbdeab66d1e53feb730297915f5ae7`; `composer audit --locked` — 0 advisories.

Первый solver run корректно остановился: `filament/support` требует `ext-intl`, а в локальном PHP 8.3.30 оно отсутствует. Для завершения анализа остальных зависимостей lock был создан с игнорированием **только** `ext-intl`. Это не waiver для staging. На staging обязательны зелёные `composer check-platform-reqs --lock --no-dev` без `--ignore-*` и одинаковый module set CLI/FPM.

## 4. Target frontend lock без изменения public frontend

Target-only package constraints взяты из официального Laravel 13 skeleton. `npm install --package-lock-only --ignore-scripts` разрешил:

| Пакет | Exact version |
|---|---:|
| `vite` | 8.1.5 |
| `laravel-vite-plugin` | 3.1.3 |
| `tailwindcss` | 4.3.3 |
| `@tailwindcss/vite` | 4.3.3 |
| `concurrently` | 10.0.3 |

Target `package-lock.json`: 101 package entries, SHA-256 `0b3d51ed86bcc6cd833de9492cd8fff012a0096d811f373eca70995ea7904400`; `npm audit --package-lock-only`: 0 vulnerabilities. `node_modules` не устанавливался, build не запускался.

Этот lock относится только к `/backoffice-next`. В Vite inputs/Tailwind sources запрещено добавлять legacy `public/css`, `public/js`, Blade public frontend и старый Mix graph. Текущие 12 bundles переносятся byte-identical.

## 5. Plugin policy

На Stage 0 нет ни одного утверждённого third-party Filament plugin. Значит, для baseline нет plugin migrations, лицензий или rollback scripts, которые можно было бы безопасно утвердить.

ACL/Shield, Spatie Permission, translation/media/settings/audit plugins помечены `DEFERRED` или `REJECTED_FOR_BASELINE`. Сначала используются project adapters над существующими таблицами, policies и storage. Любой кандидат получает exact version/license/support/migration/rollback review только в своём parity spike; до этого он не попадает в target lock.

## 6. Acceptance и stop conditions

| Проверка | Результат |
|---|---|
| Legacy Git snapshot определён | PASS |
| Legacy PHP/npm/build hashes сохранены | PASS |
| Все 12 manifest assets существуют и захешированы | PASS |
| Legacy locks/build/public files не изменены | PASS |
| Core Laravel 13/Filament 5/Livewire 4 graph разрешён изолированно | CONDITIONAL PASS |
| Target frontend graph разрешён без install/build | PASS |
| Security advisories в target Composer lock | PASS: 0 |
| PHP 8.3/8.4 CLI/FPM с `ext-intl` | PASS ON SERVER: PHP 8.3.30; local Windows PHP 8.3 remains unsuitable |
| Production writes/provider calls/public rebuild | NOT PERFORMED |

Первоначальный local stop condition S0-01 сработал из-за отсутствующего `ext-intl` в Windows PHP 8.3. Solver не требует и не разрешает менять legacy dependencies.

Обновление по server evidence 17.07.2026: production host уже имеет PHP 8.3.30 CLI/FPM с `ext-intl` и одинаковыми 41 conf.d links. Server PHP gate закрыт; оставшийся blocker перенесён в S0-02 — suspended dev vhost и отсутствие staging isolation. См. `57-filament-stage0-s02-preflight-result.md`.

## 7. Следующее действие

1. Создать отдельный staging vhost и dedicated PHP 8.3/8.4 FPM pool.
2. Включить одинаковые required extensions в CLI/FPM, обязательно `intl`.
3. Повторить Composer platform check без ignore flags.
4. Изолировать DB/schema, Redis DB/prefix, queues, cookies, storage write paths и provider credentials.
5. Только после PASS выполнить S0-02: fresh Laravel 13 skeleton и Panel `/backoffice-next`.

## 8. Официальные источники

- [Laravel 13 release notes](https://laravel.com/docs/13.x/releases) — PHP 8.3 minimum и support window.
- [Laravel 13 application skeleton](https://github.com/laravel/laravel/tree/13.x) — Composer/npm constraints target skeleton.
- [Filament 5 upgrade guide](https://filamentphp.com/docs/5.x/upgrade-guide) — PHP/Laravel/Livewire/Tailwind requirements и plugin compatibility warning.
- [Filament 5 styling](https://filamentphp.com/docs/5.x/styling/overview) — isolated custom theme/Vite registration.
- [Livewire 4 installation](https://livewire.laravel.com/docs/4.x/installation) — platform prerequisites.
