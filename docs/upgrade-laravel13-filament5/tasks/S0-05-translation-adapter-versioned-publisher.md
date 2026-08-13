# S0-05 — Совместимый адаптер переводов, безопасная версионная публикация и повторная проверка языковых данных

```text
TASK_ID: S0-05
STATUS: VERIFY
PRIORITY: Critical
OWNER: программист
BRANCH: codex/s0-05-translation-adapter
CREATED_AT: 2026-07-21
UPDATED_AT: 2026-07-21
DEPENDS_ON: S0-02L=DONE; S0-04=DONE; DB-005=evidence available
NEXT_ACTION: при будущем staging выполнить 14-case UAT и fresh production checkpoint; local implementation завершена, live activation остаётся запрещена
```

## Цель

Подключить Laravel 13 / Filament 5 к существующим переводам без потери языков,
ключей и значений, определить один безопасный механизм публикации и обеспечить
проверяемый rollback. До отдельного разрешения любые операции выполняются только
в режиме чтения при `FILAMENT_WRITE_ENABLED=false`.

## Источники истины

- `resources/lang` legacy-проекта;
- таблицы `ltm_translations` и `translations` локальной копии БД;
- frozen evidence R5–R14 и language-owner UAT protocol;
- текущие legacy routes/controllers/services переводов;
- S0-04 deny-by-default RBAC adapter.

## Входит в задачу

- карта всех языков и фактических источников чтения;
- повторяемые canonical manifests/hashes R5–R14;
- read-only target adapter и Filament page/resource prototype;
- ACL для просмотра, изменения, публикации и rollback;
- один writer, version/receipt, locking и rollback contract;
- проверки null/HTML/placeholders/plurals/Unicode и alias `ee`/`et`;
- отрицательные role/direct URL/Livewire/write-gate tests;
- zero unintended DB/filesystem delta в read-only режиме.

## Не входит

- изменение production/test;
- массовый автоперевод или исправление текстов без решения владельца;
- удаление `cn`, `jp`, `ee`, `et` либо других существующих языков;
- публикация при выключенном write gate;
- изменение legacy frontend или перегенерация его стилей/assets.

## План выполнения

- [x] инвентаризировать legacy routes/controllers/views/services и команды переводов;
- [x] повторить и сверить R5–R14 на local files/DB;
- [x] классифицировать `resources/lang`, `ltm_translations`, `translations` по ownership;
- [x] реализовать read-only translation adapter;
- [x] подключить защищённый Filament prototype без write actions;
- [x] спроектировать versioned single-writer publish/rollback contract;
- [x] покрыть роли, direct URL, Livewire, write gate и content edge cases тестами;
- [x] подтвердить zero delta, полный suite и обновить evidence/docs.

## Definition of Done

- [x] все существующие языки и оба файловых/DB источника учтены;
- [x] R5–R14 воспроизводимы и расхождения классифицированы;
- [x] чтение переводов сохраняет раздельные legacy sources/locale identifiers и не смешивает precedence;
- [x] staging-artifact build/rollback имеет отдельный disabled-by-default gate, одного writer, lock, version и receipt;
- [x] неизвестная роль/permission и выключенный artifact/write gate дают DENY;
- [x] direct URL и Livewire не обходят ACL;
- [x] placeholders, HTML, plurals, null и Unicode сохраняются byte-identical в artifact;
- [x] read-only этап не изменяет DB/filesystem источники;
- [x] tests/evidence/docs PASS.

## Evidence

| Дата | Evidence | Результат |
|---|---|---|
| 2026-07-21 | R5–R14 production audit baselines уже сохранены в профильных приложениях | prerequisite available |
| 2026-07-21 | S0-04 deny-by-default Gates/manifest/direct URL/Livewire checks | prerequisite PASS |
| 2026-07-21 | Local read-only inventory: `ltm_translations`=21 450, groups=46, NULL=211, status 0/1=20 386/1 064; logical duplicates=0 | PASS |
| 2026-07-21 | Local `translations`=64 779, parent tables=111, logical duplicates=0; `locales` prefixes=`de|ee|en|lt|lv|pl|ru` | PASS |
| 2026-07-21 | Local PHP dictionary inventory: 334 files, 10 locale directories; value-free target manifest SHA-256 `e1b5977a6b9258e02312757a7183b8f2b0d81e7c8c7201496978b9b424b9f69d` | PASS; canonical R5 comparison pending |
| 2026-07-21 | `/admin/translations`: read-only aggregate/hash page; guest→login, wrong role direct URL/Livewire→403; no edit/publish actions | PASS |
| 2026-07-21 | Target full suite 40 tests / 1 491 assertions; Pint, Composer validate strict and non-dev platform requirements | PASS |
| 2026-07-21 | RBAC manifest после активации menu item 13: active=2, deferred=132, retired=4; SHA-256 `ffd22512a70821cd29067e5822fedfd03352143fedb64a871c655ef20352c840` | PASS |
| 2026-07-21 | Target canonical audit: R5 known delta 25/25, missing=0, hash mismatch=0; R6 all 3 roots equal frozen local baseline | PASS |
| 2026-07-21 | R7/R8/R9/R10/R11: 8/46/8/30/8 cohorts, comparison differences=0 для каждого набора | PASS |
| 2026-07-21 | R12/R13/R14: 23 files; 30 header cohorts/240 rows; 36 RU account groups/130 rows; 45 order leaves; comparison differences=0 | PASS |
| 2026-07-21 | После canonical implementation: full suite 42 tests / 1 512 assertions; Pint, Composer validate/platform | PASS |
| 2026-07-21 | Read projection на `/admin/translations`: отдельные LTM/model/PHP sources, locale/scope filters, escaped HTML, limit=100, без write controls | PASS |
| 2026-07-21 | Full artifact rehearsal: baseline/candidate по 334 файла; source tree SHA-256 `e1b5977a6b9258e02312757a7183b8f2b0d81e7c8c7201496978b9b424b9f69d`; lint 334/334 для каждой версии | PASS |
| 2026-07-21 | Sandbox state: baseline generation 1 → candidate generation 2 → rollback baseline generation 3; receipts сохранены, runtime activation=false | PASS |
| 2026-07-21 | Feature gate после rehearsal: artifact build=false, Filament writes=false, outbound=false; LTM=21 450, model translations=64 779, PHP files=334, source hash unchanged | zero delta PASS |
| 2026-07-21 | Final local suite: 49 tests / 1 548 assertions; Pint, Composer validate strict, non-dev platform requirements | PASS |

## Инвентаризация writers и precedence

| Источник | Фактическая роль сейчас | Риск/решение target |
|---|---|---|
| `resources/lang/{locale}/*.php` | runtime source для `@lang`/`__` после публикации | сохранять все 10 каталогов; читать без `include` в inventory; будущая активация только versioned artifact |
| `ltm_translations` | рабочие значения Translation Manager; `status` не доказывает совпадение с runtime-файлом | отдельный source; logical key `(locale,group,key)`; publish только одним target writer |
| `translations` | Voyager-переводы полей моделей | не смешивать с PHP/LTM; переключать вместе с родительским модулем |
| `locales` | семь публичных locale в custom BREAD | `ee` сохраняется; `et` не объединяется; `cn/jp` не активируются |

- `barryvdh/laravel-translation-manager` v0.5.10 регистрирует под `/admin/translations` операции edit/delete/import/find/add-remove locale/publish/auto-translate с middleware `web,auth`; отдельного permission middleware нет.
- `AdminLocaleController` назначен Voyager BREAD `locales` и содержит прямую файловую запись, но AJAX endpoints редактора из `locale_template.blade.php` в текущих source routes не зарегистрированы. Код считается потенциальным/stale writer, а не подтверждённым активным route.
- Legacy `route:list` нельзя использовать как полное доказательство маршрутов: команда падает на отсутствующем `App\\Http\\Controllers\\ImageController`. Route/source/package inventory выполнен статически и по DB metadata.

## Журнал работы

| Дата | Что сделано | Блокер | Следующее действие |
|---|---|---|---|
| 2026-07-21 | S0-05 открыта после закрытия S0-04 | нет | read-only inventory legacy translation flow и local R5–R14 reconciliation |
| 2026-07-21 | Writers/precedence/ACL инвентаризированы; добавлены value-free audit command, read-only service и защищённая Filament Page | нет | реализовать canonical comparison и candidate artifact contract |
| 2026-07-21 | Target-команда воспроизвела R5–R14 и дала 0 differences со всеми frozen local CSV baselines | versioned publisher ещё не реализован | создать candidate/previous artifact builder, lock, receipt и rollback test |
| 2026-07-21 | Реализованы read projection трёх sources, immutable builder, single-writer lock, lint, manifest/receipt, tamper rejection и rollback rehearsal | staging/server отложен решением пользователя | перевести S0-05 в VERIFY; продолжить local-first с S0-06 |

## Оставшиеся VERIFY-gates

- перед staging взять свежий production checkpoint, потому что production-переводы продолжают изменяться;
- собрать candidate только из approved `production wins`/`retain dormant` dispositions, а не активировать текущий local rehearsal artifact;
- выполнить I18N-UAT-001…014 и реальный staging rollback;
- только после этого отдельно решать включение web write actions; сейчас их нет.

## Stop conditions

- требуется запись в production/test;
- publish имеет более одного writer или не имеет lock/version/receipt;
- теряется язык, ключ, placeholder, HTML или Unicode;
- `ee` и `et` автоматически объединяются без подтверждённого mapping;
- permission отсутствует, но действие разрешается;
- write action доступен при `FILAMENT_WRITE_ENABLED=false`;
- проверка выводит PII, секреты или реальные тексты переводов вместо hashes/counts.
