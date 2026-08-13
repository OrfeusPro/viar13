# 66. Полный аудит трассируемости документов и задач

## 1. Результат

Проверен зафиксированный до начала этого прохода набор из **124 технических файлов** в `docs/upgrade-laravel13-filament5`: 68 Markdown, 54 CSV и 2 JSON. Четыре DOCX исключены по прямому указанию пользователя и относятся к отдельному финальному документному этапу `DOC-001`.

```text
AUDIT_DATE: 2026-07-21
SOURCE_FILES: 124
MARKDOWN: 68
CSV: 54
JSON: 2
DOCX_EXCLUDED: 4
FILES_REVIEWED: 124
FILES_WITHOUT_CLASSIFICATION: 0
TRACEABILITY_RESULT: PASS
CATALOG_VERSION_AFTER_AUDIT: 2
CATALOG_TASKS_AFTER_AUDIT: 130
NEW_EXECUTABLE_TASKS: 10
```

Построчная матрица находится в [appendix-document-task-traceability.csv](appendix-document-task-traceability.csv). Для каждого исходного файла указаны его роль, связанные нормализованные задачи и результат проверки.

## 2. Что считалось задачей

Отдельная строка каталога создавалась только для работы, у которой есть самостоятельный результат, зависимости и проверяемый Definition of Done. Не создавались отдельные задачи для:

- строк evidence, hash manifests, route/function inventories и baseline snapshots;
- каждого из 163 рисков — риск связывается с одной или несколькими задачами устранения;
- каждого из 103 пунктов ручной приёмки — это acceptance criteria для `DEP-002`, `S0-09` и профильных `TST-*`;
- 68 клиентских решений — они задают scope, ограничения и DoD профильных задач;
- завершённых промежуточных R1–R14 read-only проверок переводов;
- шаблонных placeholders в `tasks/TEMPLATE.md`.

## 3. Что было реально пропущено старым каталогом

После сопоставления roadmap, production readiness, рисков, решений и приложений добавлены десять самостоятельных задач:

| Новая задача | Почему нужна отдельно | Основной источник |
|---|---|---|
| `SEC-001` | ротация обнаруженного Paysera signing credential и перенос секретов | 23, 12 |
| `SEC-002` | `.env` permissions, закрытие public DB admin surface и test receiver | 33, 31 |
| `OPS-001` | ротация, права, retention, redaction и alerts для фактических log paths | 33 |
| `OPS-002` | reconciliation ALT failures и безопасный worker lifecycle | 27, 33 |
| `OPS-003` | session TTL/capacity/locking/cleanup/continuity | 33 |
| `OPS-004` | storage link, permissions, capacity и fresh-release smoke | 33 |
| `APP-004` | перенос общих моделей, casts, dates, serialization и domain services | 06, 11 |
| `APP-005` | перенос public routes/controllers/Blade без изменения public assets | 06, 11 |
| `GOV-001` | еженедельный отчёт, change log и поэтапный PASS/FAIL | 52 |
| `DOC-001` | финальная клиентская редакция и отдельный визуальный QA DOCX | 52 |

Эти строки не означают, что remediation уже выполнена. Их статус остаётся `BACKLOG` либо `WAITING`; единственная текущая `READY` задача по-прежнему `S0-02B`.

## 4. Crosswalk старого roadmap

В `11-step-by-step-roadmap.md` использованы 43 коротких ID. Они не являются дополнительными задачами после следующей нормализации:

| Старый ID | Нормализованная задача |
|---|---|
| `AUD-01` | `AUD-001` |
| `AUD-01A` | `AUD-004` |
| `AUD-01B` | `AUD-005` |
| `AUD-01C` | `AUD-006` |
| `AUD-01D` | `AUD-008` |
| `AUD-01E` | `AUD-009` |
| `AUD-02` | `AUD-002` |
| `TST-01` | `TST-001` |
| `TST-01A` | `TST-005` |
| `TST-02` | `TST-002` |
| `TST-02A` | `TST-006` |
| `TST-02B` | `TST-007` |
| `DEP-01` | `DEP-001`, `L3-002`, `S0-02C` |
| `PHP-01` | `PHP-001` |
| `L7-01` | `L7-001` (`CANCELLED`) |
| `L8-01` | `L8-001` (`CANCELLED`) |
| `L9-01` | `L9-001` (`CANCELLED`) |
| `L10-01` | `L10-001` (`CANCELLED`) |
| `L11-13` | `L13-001`, `S0-02A` |
| `APP-01` | `APP-004` |
| `APP-02` | `APP-005` |
| `APP-03` | `APP-001`, `APP-002` |
| `APP-04` | `APP-003` |
| `DB-01` | `DB-003` |
| `DB-02` | `DB-004` |
| `DB-03` | `DB-005` |
| `INT-01` | `INT-001` |
| `INT-01A` | `INT-007` |
| `INT-02` | `INT-002` |
| `INT-03` | `INT-003` |
| `INT-04` | `INT-004` |
| `INT-05` | `INT-005` |
| `INT-06` | `INT-006` |
| `FIL-01` | `FIL-001`, `FIL-002`, `S0-03`, `S0-04` |
| `FIL-02` | `FIL-003`, `VOY-007` |
| `FIL-03` | `FIL-003`, `VOY-005` |
| `FIL-04` | `FIL-004` |
| `FIL-05` | `FIL-005` |
| `FE-01` | `FE-001` |
| `FE-02` | `FE-002` |
| `DEP-02` | `DEP-002`, `DEP-003` |
| `DEP-02A` | `DEP-004` |
| `VOY-01` | `VOY-008`, `RBK-003` |

## 5. Crosswalk Filament spikes

| Blueprint spike | Исполняемые задачи |
|---|---|
| `FIL-SPK-01` | `S0-02A`, `S0-03`, `FE-001` |
| `FIL-SPK-02` | `S0-04`, `FIL-002` |
| `FIL-SPK-03` | `S0-05`, `ACL-001`, `CUT-008` |
| `FIL-SPK-04` | `S0-06` |
| `FIL-SPK-05` | `S0-06` |
| `FIL-SPK-06` | `S0-07`, `CHAT-001`, `CHAT-002` |
| `FIL-SPK-07` | `S0-07`, `APP-001`, `APP-002` |
| `FIL-SPK-08` | `S0-04`, `VOY-004` |

## 6. Остальные типы идентификаторов

- `DEC-*` — утверждённые решения клиента; это не backlog IDs.
- `R-*` — риски; mitigation и recovery входят в профильные `SEC`, `OPS`, `TST`, `INT`, `APP`, `DB`, `CUT`, `DEP` и `RBK` задачи.
- `Q-*` — входные решения или технические gates; они остаются зависимостями соответствующих задач и не разрешают production cutover сами по себе.
- `REC-*`, `L3-E*`, `L3-G*`, `R1…R14`, `S02-*`, `TEST-S02-*` — проверки, gates и evidence identifiers; они не создают второй исполняемый backlog поверх задач.
- Row IDs приложений описывают маршруты, поля, роли, datasets и тест-кейсы; они являются scope/acceptance evidence.

## 7. Контроль полноты

Проверка выполнена по следующим сигналам:

1. все Markdown titles, таблицы с ID, action/gate markers и unchecked checklists;
2. headers и назначение всех 54 CSV, включая поля `required_action`, `status`, `disposition`, `required_test`, `acceptance` и `stop_condition`;
3. оба JSON manifest как source of exact Stage 0 versions/build evidence;
4. сопоставление старых task IDs, Filament spikes, client decisions, risk/gate/evidence identifiers;
5. отдельная проверка того, что evidence и критерии не превращены в дублирующие задачи.

Итог: у всех 124 исходных технических файлов есть классификация и связь с каталогом либо обоснованное `NO_NEW_TASK`. Необъяснённых исполняемых обязательств после добавления десяти строк не осталось.

Post-audit update 21.07.2026: после решения `DEC-STG-69` добавлена отдельная выполненная задача `S0-02L`, каталог вырос до 131 строки, а server tasks `S0-02B…D` отложены. Это новое execution-решение, а не пропуск исходного аудита 124 файлов.

## 8. Текущая точка продолжения

Аудит полноты не меняет порядок выполнения:

```text
ACTIVE_TASK: NONE
NEXT_TASK: S0-02B
READY_TASK: S0-02B
PRODUCTION_WRITE_OWNER: LEGACY
```

Следующая практическая работа — [S0-02B: непубличная установка server artifact](tasks/S0-02B-server-offline-release.md). Ни один новый P0/OPS пункт не выполняется на production без отдельной карточки, согласованных команд, backup/rollback и явного разрешения.
