# APP-005A-ASSET-02 — Полный перенос runtime-корпуса `public/theme/viar` без перезаписи production-frozen и target-specific файлов

```text
TASK_ID: APP-005A-ASSET-02
STATUS: DONE
PRIORITY: Critical
OWNER: программист
BRANCH: codex/app-005a-frontend-foundation
CREATED_AT: 2026-07-23
UPDATED_AT: 2026-07-23
DEPENDS_ON: APP-005A frontend foundation
PARENT: APP-005A
NEXT_ACTION: сохранять full-corpus baseline; вернуться к ручному VERIFY APP-005A-FORM-01
```

## Причина

Browser-проверка обнаружила `404` для
`/theme/viar/img/flags.webp`. Полная сверка показала, что legacy-каталог
содержит 1 942 файла / 169 985 040 bytes, а target — только 283 файла /
43 059 863 bytes. Отсутствуют 1 662 файла / 126 924 244 bytes.

## Границы

- источник: `C:\OSPanel\domains\asoft\viar\public\theme\viar`;
- target: `G:\OSPanel\home\viar_filament\public\theme\viar`;
- копируются только отсутствующие runtime-файлы;
- 14 существующих target CSS/JS не перезаписываются: это ранее проверенные
  актуальные production-версии;
- сохраняются target-only `public-auth.js` и `homepage-photo-lead.js`;
- `.DS_Store` и `active-projects.lnk` исключаются как служебные файлы;
- Mix/Webpack/Vite не запускаются, файлы не преобразуются.

## Definition of Done

- [x] все допустимые source-файлы существуют в target;
- [x] каждый скопированный файл совпадает с source по SHA-256;
- [x] существующие 14 production-frozen/target-файлов не изменены;
- [x] target-only adapters сохранены;
- [x] `flags.webp` и выборочные CSS/JS/font/image URL дают HTTP 200;
- [x] frontend regression и полный test suite PASS;
- [x] evidence, commit и следующее действие записаны.

## Evidence

- target code/assets commit: `97b4431`;
- target evidence commit: `f487995`;
- imported: 1 661 files / 126 923 399 bytes;
- исходный импорт завершён baseline `1 943` файла; после удаления ошибочного
  target-only `homepage-photo-lead.css` и возврата legacy file UI актуальный
  target baseline: 1 942 файла / 169 965 432 bytes /
  SHA-256 `34a90ff1e3c0a79ade488c0c2de274fc410124d77ba5973c1c9d34c051794e2e`;
- source reconciliation: missing=0, byte-identical=1 926,
  expected existing differences=14;
- HTTP: `flags.webp`, large JS, SVG and font = 200;
- Chrome after reload: `flags.webp` errors=0,
  `Failed to load resource` entries=0;
- targeted: 10 tests / 334 assertions; full: 97 / 2 127 PASS.
