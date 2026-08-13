# 02. Совместимость зависимостей и платформы

## Целевая платформа

- Laravel 13: PHP ≥8.3; поддерживаемый диапазон PHP 8.3–8.5. Для проекта рекомендуется PHP 8.4 после проверки расширений и SDK, с PHP 8.3 как минимальной контрольной матрицей.
- Filament 5: PHP ≥8.2, Laravel ≥11.28, Livewire ≥4.0, Tailwind CSS ≥4.0. В target Laravel 13 фактический минимум проекта остаётся PHP ≥8.3.
- Composer ≥2.2 (требование Laravel 10; установлен 2.9.5).
- MariaDB 10.6.9 локально; production evidence подтвердил MariaDB 10.11.16 и Redis 7.0.15. Первый target сохраняет эти production service versions и не совмещает migration с major-upgrade БД/Redis.

Источники: [Laravel 13 releases](https://laravel.com/docs/13.x/releases), [Laravel 13 deployment](https://laravel.com/docs/13.x/deployment), [Filament 5 installation](https://filamentphp.com/docs/5.x/introduction/installation), [Filament 5 upgrade guide](https://filamentphp.com/docs/5.x/upgrade-guide), [Laravel 10 upgrade](https://laravel.com/docs/10.x/upgrade).

## S0-01 exact solver result — 17.07.2026

Изолированный Composer solver с platform PHP 8.3.0 разрешил `laravel/framework 13.20.0`, `filament/filament 5.6.8`, `livewire/livewire 4.3.3` и 108 packages. Target lock SHA-256: `02419848c492981859a3edf7e11658734abbdeab66d1e53feb730297915f5ae7`; audit: 0 advisories.

Первый нормальный solve остановился на обязательном `ext-intl` из `filament/support`. Текущий отдельный PHP 8.3.30 CLI не содержит `intl`; поэтому lock для анализа остальных зависимостей создан с игнорированием только этого platform requirement. Это не разрешение для staging: установка target запрещена, пока `composer check-platform-reqs --lock --no-dev` не проходит без ignore flags одновременно в CLI и dedicated FPM pool.

Server preflight 17.07.2026 уточнил среду: на Ubuntu host PHP 8.3.30 CLI/FPM уже содержит `intl` и остальные required modules, а dedicated dev pool active. Следовательно, local Windows gap не является server blocker. Остаются suspended dev vhost и отсутствие staging `.env`/DB/Redis/storage/provider isolation; см. `57-filament-stage0-s02-preflight-result.md`.

Target-only npm lock на официальных Laravel 13 skeleton constraints разрешил Vite 8.1.5, Laravel Vite Plugin 3.1.3, Tailwind CSS 4.3.3 и `@tailwindcss/vite` 4.3.3. Этот graph предназначен только для Filament theme; legacy public frontend в него не входит.

Полный evidence: `55-filament-stage0-s01-evidence-freeze.md` и `stage0-target-versions.json`.

## Composer: прямые зависимости из lock

Целевая версия означает намерение, а не разрешение установить сейчас. Для пакетов без официального подтверждения Laravel 13 указано «требует проверки».

| Пакет | Lock | Назначение | Цель / действие | Риск |
|---|---:|---|---|---|
| laravel/framework | 6.20.40 | Framework | `^13.0`, только в новом skeleton | Critical |
| tcg/voyager | 1.5.2 | Admin/BREAD | заменить Filament `^5.0`; временно оставить только legacy app | Critical |
| monstrex/voyager-extension | 0.95.2 | Voyager extensions | удалить после инвентаризации функций; прямого Filament-эквивалента нет | High |
| laravel/ui | 1.3.0 | auth/frontend scaffolding | обновить до совместимой версии или удалить после переноса auth views | High |
| laravel/socialite | 5.5.2 | OAuth | обновить до актуальной Laravel 13-compatible major; contract test | High |
| socialiteproviders/facebook | 4.1 | Facebook provider | актуальная совместимая версия, требует проверки release matrix | High |
| mcamara/laravel-localization | 1.6.2 | locale routes | обновить до версии с Laravel 13 либо заменить внутренним locale layer | Critical |
| barryvdh/laravel-translation-manager | 0.5.10 | переводы | не переносить вслепую; заменить Filament Resource для существующей `translations` | High |
| barryvdh/laravel-dompdf / dompdf | 0.8.7 / 0.8.6 | PDF | подобрать поддерживаемую target-версию; отключить remote/JS по умолчанию; pure renderer, locale/security/golden PDF tests | Critical |
| paypal/paypal-checkout-sdk | 1.0.2 | PayPal Orders API | SDK архивный/legacy-риск; оценить официальный PayPal server SDK/REST client | Critical |
| guzzlehttp/guzzle | 7.4.0 | HTTP | версия, разрешённая Laravel 13 skeleton; убрать прямой `env()` из сервисов | Medium |
| predis/predis | 1.1.9 | Redis | `^2`/актуальная версия либо ext-redis; проверить production driver | High |
| stevebauman/location | 6.2 | Geo/IP | актуальная совместимая версия или замена; privacy test | Medium |
| spatie/laravel-sluggable | 2.3 | slugs | актуальная Laravel 13-compatible major; сохранить URL | High |
| arrilot/laravel-widgets | 3.13.1 | widgets | заменить Blade components/View Composers/Filament Widgets | Medium |
| bensampo/laravel-enum | 1.38 | enum | перейти на native PHP enums только после mapping tests | High |
| davejamesmiller/laravel-breadcrumbs | 5.3.2 | breadcrumbs | пакет прекращён; заменить поддерживаемым решением/собственным builder | Medium |
| fideloper/proxy | 4.4.1 | trusted proxy | удалить; использовать `Illuminate\Http\Middleware\TrustProxies` | High |
| nesbot/carbon | 2.54 | dates | Carbon 3 обязателен для Laravel 12+; timezone/serialization tests | High |
| laravel/tinker | 2.6.2 | CLI | `^3.0` по Laravel 13 guide | Low |
| barryvdh/laravel-debugbar | 3.6.4 | dev debug | актуальная compatible major, dev-only | Low |
| facade/ignition | 1.18 | dev errors | заменить современным Laravel error tooling; не production | Medium |
| barryvdh/laravel-ide-helper | 2.8.2 | dev tooling | актуальная совместимая версия | Low |
| friendsofphp/php-cs-fixer | 3.3.1 | formatting | актуальная версия; отдельно от runtime migration | Low |
| squizlabs/php_codesniffer | 3.6.1 | lint | актуальная версия | Low |
| mockery/mockery | 1.4.4 | tests | версия из Laravel 13 skeleton | Medium |
| phpunit/phpunit | 8.5.21 | tests | `^12.0` по Laravel 13 guide; переписать deprecated assertions/config | High |
| nunomaduro/collision | 3.2 | CLI tests | версия из Laravel 13 skeleton | Low |
| fzaninotto/faker | 1.9.2 | fixtures | заменить `fakerphp/faker` | Medium |

Официальные источники пакетов должны фиксироваться в upgrade PR: [Laravel upgrade guides](https://laravel.com/docs/13.x/upgrade), [Voyager repository](https://github.com/the-control-group/voyager), [mcamara repository](https://github.com/mcamara/laravel-localization), [Socialite](https://github.com/laravel/socialite), [Spatie Sluggable](https://github.com/spatie/laravel-sluggable), [Dompdf wrapper](https://github.com/barryvdh/laravel-dompdf), [PayPal SDK repository](https://github.com/paypal/Checkout-PHP-SDK).

## npm lock

| Пакет | Lock | Цель | Действие / риск |
|---|---:|---|---|
| laravel-mix | 6.0.31 | сохранить как frozen legacy toolchain | Не обновлять и не удалять в рамках миграции; production использует проверенные готовые assets и manifest. High. |
| axios | 0.22.0 | актуальная stable | API/CSRF/error semantics regression. High. |
| lodash | 4.17.21 | сохранить или удалить | Проверить фактические imports; Medium. |
| cross-env | 7.0.3 | сохранить только для аварийного legacy rebuild | Не является частью штатной target-сборки. Low. |
| sass | 1.42.1 | актуальная | Sass deprecations и CSS diff. Medium. |
| sass-loader | 8.0.2 | не заменять для public frontend | Любое изменение может дать CSS diff; Filament theme собирается отдельно. Medium. |
| resolve-url-loader | 3.1.4 | сохранить в frozen legacy toolchain | URL/font/image diff при пересборке. Medium. |
| js-beautify | 1.11.0 | требует проверки использования | Low. |
| puppeteer | 24.37.5 | сохранить на совместимом Node LTS | Browser binary/CI resources. Medium. |

Filament требует Tailwind CSS 4.x; exact target lock сейчас содержит 4.3.3. Его theme собирается отдельным Vite entry только для новой админки. Публичный legacy CSS/JS не включается в Vite, не проходит Tailwind scan/purge и разворачивается как неизменяемый проверенный набор файлов.

## Расширения и сервер

Локально есть основные Laravel extensions, но в отдельном PHP 8.3.30 отсутствует `ext-intl`, которое реально требуется Filament 5. На staging PHP 8.3/8.4 CLI и FPM должны иметь одинаковые `bcmath`, `ctype`, `curl`, `dom/xml`, `fileinfo`, `filter`, `hash`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `session`, `tokenizer`, `zip`, а также используемые проектом `gd`/`imagick` и `redis` (если выбран ext-redis).

Проверить отдельно:

```bash
php -v
php --ini
php -m
composer check-platform-reqs
node --version && npm --version
mysql --version
```

Node 24 локально не следует считать baseline для Mix 6. Зафиксировать две независимые среды: frozen legacy build на подтверждённом Node LTS только для аварийного воспроизведения и target Vite build только для Filament. Штатный migration deploy не должен перегенерировать public assets.

## Dependency gates

1. создать fresh skeleton и получить зелёный `composer validate`/`composer audit` без подключения к production;
2. для каждого direct package зафиксировать официальный constraint в ADR/PR;
3. неподтверждённый пакет не допускается в target lock — только замена или isolated adapter;
4. сохранять исходный `composer.lock` как rollback artifact;
5. Software composition/security scan выполнить до UAT и перед cutover.
