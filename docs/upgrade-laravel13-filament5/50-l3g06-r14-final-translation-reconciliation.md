# 50. L3-G06: итог R14 и закрытие технической сверки переводов

Дата: 16.07.2026. Статус: **production/local technical reconciliation текущего translation scope завершён; применение изменений остаётся no-go до решения владельца контента и staging UAT**.

## 1. Что проверил R14

Production Batch R14 безопасно и без записи сравнил leaf-уровень группы `orders` в `resources/lang/ru/account_new.php`. Сравнение выполнено по opaque ID и canonical roots; исходные тексты в shareable output не выводились.

| Показатель | Production | Local | Итог |
|---|---:|---:|---|
| `orders` leaves | 44 | 45 | один local-only leaf |
| Общие opaque leaf IDs | 44 | 44 | все production identities присутствуют локально |
| Совпадение row count | 44/44 | 44/44 | PASS |
| Совпадение key root | 44/44 | 44/44 | PASS |
| Совпадение type root | 44/44 | 44/44 | PASS |
| Совпадение value root | 43/44 | 43/44 | один value mismatch |

Production runtime: elapsed=1 ms, peak memory=2 MiB, writes=0.

Exact opaque matrix: [appendix-language-ru-account-orders-leaf-delta-r14.csv](appendix-language-ru-account-orders-leaf-delta-r14.csv).

## 2. Детерминированное отображение opaque IDs

Локальный mapping тем же canonical algorithm однозначно сопоставил две позиции:

| Класс | Ключ | Доказательство | Безопасное решение до UAT |
|---|---|---|---|
| common value mismatch | `account_new.orders.user_status.print_text` | прямой consumer: `resources/views/theme/viar/account/order.blade.php:508` | production value сохраняется; Content owner выбирает production/local/manual RU copy после просмотра экрана заказа |
| local-only | `account_new.orders.not_specified` | прямой статический consumer не найден; dynamic lookup остаётся возможным | сохранить в versioned artifact, не публиковать и не удалять автоматически |

Отсутствие прямого статического consumer для `orders.not_specified` не является доказательством неиспользования: ключ может формироваться динамически или служить fallback.

## 3. Полный итог translation reconciliation R5–R14

- Production language tree: 332 PHP-файла; local: 334; production-only=0, local-only=2.
- 309 common files совпадают, 23 были проверены по runtime PHP-array semantics.
- Семь `mail.php` semantic-equal; content merge им не нужен.
- DB `ltm_translations`: все 21 450 identities и statuses совпадают; 21 442 values совпадают; различаются ровно 8 values `header_footer_new.header.top_sale` — по одному для `de|ee|en|et|lt|lv|pl|ru`.
- PHP `header_footer_new.php`: тот же exact value scope — `header.top_sale` в восьми locale; остальные 232 проверенных header values совпадают.
- PHP `account_new.php`: local-only `orders.not_specified` в восьми locale; дополнительный common value mismatch только `ru/orders.user_status.print_text`.
- Local-only `cn/google_reviews.php` и `jp/google_reviews.php` сохраняются: группа `google_reviews` реально используется публичными partial/page/controller consumers, хотя включение CN/JP routing отдельно не подтверждено.

Дополнительные production hash batch по текущему translation scope не требуются. Новая production сверка нужна только перед конкретной rehearsal/cutover wave для фиксации свежего checkpoint, поскольку production продолжает принимать изменения.

## 4. Матрица решений владельца

Полный список из 27 decision rows находится в [appendix-language-owner-disposition-matrix-r14.csv](appendix-language-owner-disposition-matrix-r14.csv):

- 8 DB values `header.top_sale`;
- 8 PHP-file values `header.top_sale`;
- 8 local-only `orders.not_specified`;
- 1 RU value `orders.user_status.print_text`;
- 2 local-only `cn/jp google_reviews.php`.

Для каждой строки допустим только подписанный disposition: `production wins`, `local wins`, `manual translation`, `retain dormant` или `retire after usage proof`. До решения безопасная позиция — сохранить production live behavior и обе версии источников.

## 5. Обязательные следующие действия

1. Content owner просматривает raw values только в защищённом сравнительном artifact/staging и подписывает 27 dispositions.
2. Создаётся versioned translation artifact без ручной перезаписи live `resources/lang`.
3. Выполняются PHP lint, manifest/hash и negative test на потерю locale/key/file.
4. Staging UAT покрывает header/footer для восьми locale, RU account order status, fallback/empty account fields и Google reviews section.
5. Перед cutover снимается новый production checkpoint и применяется только approved delta.
6. Publisher остаётся single-writer; publish/import/copy/regeneration в live checkout запрещены до rehearsal и rollback proof.

Детальный owner/UAT execution pack: [51-l3g06-translation-owner-decision-and-staging-uat.md](51-l3g06-translation-owner-decision-and-staging-uat.md).

## 6. Gate status

Техническая discovery/reconciliation translation scope закрыта. `REC-009` переводится в состояние **technical evidence complete / owner and cutover pending**. Это не означает готовность к миграции: `CUT-002/CUT-009`, versioned publish, UAT, owner sign-off и forward rollback rehearsal остаются открытыми.
