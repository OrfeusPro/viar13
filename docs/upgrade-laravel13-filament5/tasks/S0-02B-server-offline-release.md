# S0-02B — Непубличная установка server release

```text
TASK_ID: S0-02B
STATUS: HOLD
PRIORITY: Critical
OWNER: программист
BRANCH: —
CREATED_AT: 2026-07-21
UPDATED_AT: 2026-07-21
DEPENDS_ON: S0-02A=DONE; S0-03…S0-08; OWNER_GO
NEXT_ACTION: не выполнять до готовности рабочей локальной админ-панели и отдельного owner GO
```

## Цель

Установить уже проверенный Laravel 13 / Filament 5 artifact в отдельный непубличный каталог сервера и доказать, что active `test.viarcanvas.com` не изменился.

Owner decision 21.07.2026: server upload/install отложен до готовности рабочей локальной админ-панели. Карточка сохранена как готовый runbook, но не является текущей задачей.

## Исходные артефакты

- canonical source Git working tree: `G:\OSPanel\home\viar_filament`;
- archive: `C:\OSPanel\domains\asoft\viar-next-artifacts\viar-next-stage0-20260717T123427Z.tar.gz`;
- SHA-256: `5f279d54e07d4d28e61340ddfce38997e67c92e456d4967a49ccf4c51cd6600b`;
- server release: `/home/admin/web/test.viarcanvas.com/releases/viar-next-stage0-20260717T123427Z`;
- точные команды и guards: [63-filament-stage0-offline-server-release-install.md](../63-filament-stage0-offline-server-release-install.md).

## Входит в задачу

- загрузка archive и checksum;
- проверка SHA-256 и archive traversal;
- распаковка в новый непубличный release-каталог;
- project-local Composer install под PHP 8.3 с предусмотренными guards;
- platform/audit/build-manifest проверки;
- повторная проверка неизменности active test.

## Не входит

- создание или копирование `.env`;
- подключение DB, Redis, SMTP, PayPal, Synvolve или других providers;
- Artisan migrations и production/test writes;
- изменение Hestia root/backend, PHP 7.4 pool или active `public_html`;
- HTTP-публикация новой панели;
- переключение, suspend или остановка test/production.

## Предварительные условия

- [x] S0-02A artifact и `.sha256` существуют локально;
- [x] clean extraction/install rehearsal PASS;
- [x] active test восстановлен на PHP 7.4 и не должен изменяться;
- [ ] перед запуском повторно проверить текущее состояние test по guards документа 63;
- [ ] убедиться, что server release path ещё не существует.

## План выполнения

- [ ] загрузить archive и `.sha256` командами из раздела 1 документа 63;
- [ ] выполнить TEST-S02-D без пропусков и ручного ослабления guards;
- [ ] сохранить полный stdout/stderr без секретов и персональных данных;
- [ ] проверить финальные строки PASS и неизменность active test;
- [ ] обновить эту карточку, управляющий реестр и running log.

## Definition of Done

- [ ] checksum совпал с зафиксированным SHA-256;
- [ ] release создан строго вне `public_html`;
- [ ] Composer/platform/audit/build manifest PASS;
- [ ] `.env`, DB, Redis и providers не подключались;
- [ ] Hestia root/backend и active test остались без изменений;
- [ ] output рассмотрен и не содержит необъяснимых warning/error;
- [ ] задача переведена в `DONE`, `S0-02C` — в `READY` либо зафиксирован `WAITING/HOLD` с причиной.

## Stop conditions

- checksum не совпадает;
- release path уже существует;
- active test не на ожидаемом PHP 7.4/root или suspended;
- archive содержит unsafe paths;
- отсутствует PHP 8.3/ext-intl либо normal platform check не проходит;
- команда требует `.env`, DB/provider access или изменения active vhost.

При любом stop задача не продолжает переключение: release остаётся непубличным либо не создаётся, test и production не изменяются.

## Evidence

| Дата | Evidence | Результат |
|---|---|---|
| 2026-07-17 | [offline target build](../62-filament-stage0-offline-target-build-result.md) | S0-02A PASS; prerequisite выполнен |
| — | Полный TEST-S02-D output | Ожидается |

## Журнал работы

| Дата | Что сделано | Блокер | Следующее действие |
|---|---|---|---|
| 2026-07-21 | Карточка создана из уже подготовленного command pack | Нет | Загрузить два artifact-файла и выполнить TEST-S02-D |
