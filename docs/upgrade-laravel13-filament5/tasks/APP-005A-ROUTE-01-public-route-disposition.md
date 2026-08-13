# APP-005A-ROUTE-01 — Полная классификация публичных маршрутов, обработчиков и транзакционных сценариев frontend

```text
TASK_ID: APP-005A-ROUTE-01
STATUS: DONE
PRIORITY: Critical
OWNER: программист
BRANCH: codex/app-005a-frontend-foundation
CREATED_AT: 2026-07-27
UPDATED_AT: 2026-07-27
DEPENDS_ON: APP-005A-FORM-02=DONE
PARENT: APP-005A
NEXT_ACTION: задача закрыта; продолжение — APP-005B-PAGES-01
```

## Цель

Получить единую поштучную карту public frontend до продолжения переноса:
маршрут, HTTP-метод, имя, middleware/locale, handler, Blade/response, assets,
операции чтения и записи, внешние side effects, состояние target и отдельная
исполняемая задача для каждого незавершённого потока.

## Правила классификации

- `PARITY_DONE` — route и применимые UI/read/write/failure/locale contracts
  подтверждены тестами и evidence;
- `SHELL_ONLY` — URL/шаблон существует, но данные, JS или side effects
  перенесены не полностью;
- `MISSING_TARGET` — legacy route отсутствует в target;
- `SECURITY_REPLACE` — небезопасный legacy endpoint не переносится буквально,
  указан безопасный replacement и отдельный test contract;
- `DECOMMISSION_CANDIDATE` — route можно удалить только после owner decision и
  evidence об отсутствии использования;
- `NOT_PUBLIC` — admin/API/internal endpoint исключён из public cohort с
  зафиксированной причиной.

## План выполнения

- [x] получить runtime route inventories обоих приложений;
- [x] дополнить runtime inventory source scan маршрутов, которые не bootятся;
- [x] нормализовать locale aliases без потери отдельных контрактов;
- [x] связать routes с controllers/closures/views и client-side submit/click;
- [x] отметить DB/file/session/mail/webhook/payment/queue side effects;
- [x] сопоставить legacy route с target route и назначить disposition;
- [x] назначить child-task ID каждому `SHELL_ONLY`/`MISSING_TARGET`/replacement;
- [x] выделить state-changing GET и скрытые JS writers;
- [x] выполнить count/hash/duplicate/name/security проверки;
- [x] обновить control center, catalog, running log и выбрать следующий flow.

## Обязательные артефакты

- нормализованный machine-readable manifest;
- человекочитаемая сводка по функциональным потокам;
- legacy→target route map с disposition;
- список state-changing routes и integration gates;
- child-task backlog с зависимостями и Definition of Done;
- команды воспроизведения и проверочные counts/hashes.

## Definition of Done

- каждый legacy public route имеет disposition и target/task reference;
- каждый target public route имеет legacy/new justification;
- ни один writer не классифицирован только по HTTP-методу;
- locale aliases, closures, redirects и JS-triggered endpoints учтены;
- totals, duplicates и unresolved handlers вынесены в evidence;
- следующий функциональный flow выбран из проверенной карты;
- production/server/рабочие данные не изменены.

## Stop conditions

- runtime inventory требует production write или внешнего provider call;
- маршрут невозможно классифицировать без бизнес-решения — фиксируется
  `WAITING`, но остальная карта продолжается;
- обнаружен секрет в route/source output — артефакт не сохраняется до
  редактирования.

## Evidence

- [результат и порядок продолжения](../67-public-route-disposition-and-frontend-backlog.md);
- `appendix-public-route-disposition.csv`: 2 236 rows, SHA-256
  `247907e8c2d09a64620a6ab6b7daf4fc1ec514c53db5d39e216703ad44666c09`;
- `appendix-target-public-route-disposition.csv`: 27 rows, SHA-256
  `b376b9d6aa7bfdf5a52420c4fdd755fd8b5e6d1cd6919ccadfe9385beb643e46`;
- `appendix-public-client-endpoint-calls.csv`: 217 rows, SHA-256
  `1373d077bdf1258f7fcd726369dcd6616c1bfc5a25a0f1871521e9c911871461`;
- repeat build hashes identical; DB count delta=0;
- target full suite 108 tests / 2 212 assertions PASS; Pint и
  `git diff --check` PASS.
