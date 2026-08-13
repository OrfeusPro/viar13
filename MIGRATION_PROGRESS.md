# Миграция на Laravel 13 — текущий статус

Обновлено: 2026-08-13.

## Цель

Перевести этот репозиторий `G:\OSPanel\home\viar13` с Laravel 6 на Laravel 13.
Первый приоритет — полностью поднять публичный frontend. Админка временно вне
scope; Filament 5 будет рассматриваться отдельным этапом.

## Принятые решения

- миграция выполняется на основе текущего проекта, без использования
  `G:\OSPanel\home\viar_filament`;
- существующую публичную разметку и assets сохраняем, без редизайна;
- Voyager и admin routes не должны блокировать запуск публичной части;
- обновление выполняется итеративно, с сохранением поведения и тестами;
- production/test не переключаются до локального frontend UAT.

## Исходное состояние

- Laravel: `^6.2`;
- PHP constraint: `^7.3`;
- frontend build: Laravel Mix 6 / Webpack;
- админка: Voyager 1.4 и voyager-extension;
- публичные routes, controllers и Blade находятся в этом репозитории;
- текущий legacy runtime не загружается под активным PHP 8.3 из-за
  несовместимости Laravel 6 с современными сигнатурами PHP.

## Что сделано

- направление миграции и границы frontend-first зафиксированы;
- обновлён `AGENTS.md`;
- создан этот краткий журнал работ.
- создана рабочая ветка `codex/laravel13-frontend`;
- Composer runtime обновлён до PHP `^8.3` и Laravel `13.25.0`;
- Voyager, voyager-extension и translation-manager исключены из обязательного
  runtime; admin routes выключены константой конфигурации;
- совместимые публичные пакеты обновлены: localization, breadcrumbs, DomPDF,
  Socialite, enum, sluggable, location и media library;
- добавлен локальный compatibility layer для используемых frontend-контрактов
  Voyager: translations, `Voyager::image`, settings, menu models и resize path;
- исправлены Laravel 13/PHP 8.3 несовместимости exception handler,
  trusted proxies, breadcrumbs и slug return types;
- public auth controllers переписаны без удалённых `laravel/ui` traits:
  login/logout, register, password reset/confirm и email verification;
- исправлена компиляция JSON-LD: literal `@context` экранирован для нового
  Blade compiler;
- подтверждено: `php artisan --version` выводит Laravel 13.25.0;
- подтверждено: локальный HTTP `GET /` возвращает `200`, 498 864 bytes HTML,
  время smoke-запроса 2.82 s.
- `route:list` снова работает после замены public auth traits и добавления
  отсутствовавшего legacy `ImageController`;
- зарегистрировано 2 260 application routes; Voyager admin route group не
  подключается (legacy public helper `/admin/check-user` пока сохранён);
- HTTP smoke: `/` и `/en` возвращают 200 с RU/EN HTML;
- targeted regression: 5 tests / 12 assertions PASS;
- `composer validate`, package discovery, полный Blade `view:cache` и
  `git diff --check` PASS.

## В работе

- стабилизация первого frontend slice и автоматических тестов под PHPUnit 12;
- проверка остальных публичных страниц и locale aliases.

## Следующие действия

1. Добавить Laravel 13-native public auth controllers без `laravel/ui` traits.
2. Прогнать public route/view compile smoke и исправить следующий набор
   несовместимостей.
3. Добавить автоматический smoke-тест Laravel 13 boot/route contract.
4. Последовательно проверить locale homepage, FAQ, About, Contacts и catalog.
5. Затем переходить к формам, account, корзине и checkout.

## Риски и открытые вопросы

- часть legacy-пакетов не поддерживает Laravel 13 и потребует замены;
- старые controllers/models могут содержать несовместимые PHP-сигнатуры;
- admin/Voyager providers и routes нужно изолировать, не удаляя бизнес-данные;
- перед изменением схемы БД требуется аудит существующих таблиц и миграций.
- `paypal/paypal-checkout-sdk` Composer помечает abandoned; до payment-этапа
  требуется перенос на PayPal Server SDK с sandbox evidence;
- текущий compatibility translator покрывает frontend read-path; translation
  write-path должен быть реализован и протестирован отдельно до Filament.

## Журнал

### 2026-08-13 — старт нового направления

- подтверждено: основа — `G:\OSPanel\home\viar13`;
- старый отдельный target исключён из работы;
- текущий scope: публичный frontend; админка отложена.

### 2026-08-13 — первый Laravel 13 frontend boot

- обновлены Composer dependencies и lock;
- Laravel 13.25.0 успешно загружается через Artisan;
- главная страница текущего проекта отвечает HTTP 200 на локальном PHP 8.3;
- БД-схема и production/test не изменялись;
- следующий точный шаг: public auth compatibility и автоматический smoke.
