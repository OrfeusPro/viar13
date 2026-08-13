# S0-07 — Изолированные адаптеры чатов и legacy-файлов с ownership, dedupe, MIME/path и rollback

```text
TASK_ID: S0-07
STATUS: BACKLOG
PRIORITY: Critical
OWNER: программист
BRANCH: codex/s0-07-chat-file-adapters
CREATED_AT: 2026-07-21
UPDATED_AT: 2026-07-21
DEPENDS_ON: S0-03=DONE; S0-04=DONE
NEXT_ACTION: возобновить после закрытия information/visual parity reopened S0-06
```

## Цель

Подготовить безопасные Filament adapters для просмотра legacy-чатов и файлов заказа,
не объединяя разные исторические streams и не расширяя доступ к данным. После
read-only parity доказать на synthetic fixtures dedupe, безопасную обработку файлов,
transaction/filesystem journal, failpoints и rollback без реальной почты или SA.

## Неприкосновенные инварианты

- `order_user_comments`, `orders_chats`, `order_admin_comments` и `order_painter_comments` остаются раздельными источниками до подписанного mapping;
- arbitrary `order_id`, `chatId` или image ID не открывает чужую запись;
- текущие legacy URL/path и derivatives сохраняются как compatibility contract;
- spike не отправляет mail, SA/webhook и не расширяет public file scope;
- message/file duplicate не создаёт вторую запись;
- path traversal, symlink escape, active content и MIME mismatch запрещены;
- PII, тексты сообщений, бинарные файлы и секреты не попадают в audit output/docs/tests.

## Исходный подтверждённый baseline

- `order_user_comments`: 3 835 rows / 1 519 orders; 120 orphan-order, 1 orphan-user;
- `orders_chats`: 2 840 rows / 1 409 orders; 40 null-order, 49 orphan-order; author user ID отсутствует;
- `order_admin_comments`: 106 rows / 92 orders; 5 orphan-order, 8 rows без user attribution;
- `order_painter_comments`: 25 rows / 21 orders; 9 orphan-order;
- ownership и actor semantics нельзя выводить только из одинаково названных полей;
- текущие legacy controller writers могут одновременно писать БД, отправлять mail и вызывать Synvolve/SA.

## План выполнения

- [ ] инвентаризировать schema/index/model/route/writer для каждого chat/file source;
- [ ] зафиксировать count/orphan/null/duplicate/path baseline без содержимого и PII;
- [ ] определить role × stream × order ownership matrix;
- [ ] реализовать scoped read-only chat projection с явным source label;
- [ ] реализовать read-only file metadata/path adapter без расширения public scope;
- [ ] покрыть direct URL/Livewire/IDOR и cross-stream negative cases;
- [ ] добавить synthetic message dedupe без mail/SA;
- [ ] добавить sanitized file fixture, real MIME/decode/size/count/dimension validation;
- [ ] доказать canonical-root, traversal/symlink protection, journal/failpoints/rollback;
- [ ] подтвердить zero unintended delta, полный suite и обновить evidence/docs.

## Definition of Done

- [ ] четыре stream не смешаны и их provenance виден;
- [ ] role/order ownership даёт deny-by-default для list, record и action;
- [ ] duplicate message/file детерминирован и не создаёт вторую запись;
- [ ] MIME/extension/decode/limits и safe download headers PASS;
- [ ] traversal, symlink escape и raw active content DENY;
- [ ] synthetic writers не вызывают mail/SA/outbound;
- [ ] failpoint/rollback не оставляют partial DB/file state;
- [ ] legacy business tables/files имеют zero unintended delta;
- [ ] tests/evidence/docs PASS.

## Evidence

| Дата | Evidence | Результат |
|---|---|---|
| 2026-07-21 | S0-03 auth/session и S0-04 RBAC готовы; S0-06 query/policy/receipt pattern в VERIFY | prerequisites/pattern available |
| 2026-07-21 | FN-09 audit baseline четырёх chat streams и orphan cohorts | initial contract frozen |
| 2026-07-21 | Fresh local exact counts без message bodies: streams 3 835 / 2 840 / 106 / 25 rows; order cohorts 1 519 / 1 409 / 92 / 21; null/orphan сохранены как ниже | baseline reconfirmed |
| 2026-07-21 | `order_painter_images`: 7 199 rows, 28 orphan-order, 0 empty image, 2 duplicate image values; `order_user_images`: 0 rows | file metadata baseline |
| 2026-07-21 | Read flags: user comments unread=542/admin-unread=2 518; orders chats unread=380/admin-unread=571; `orders_chats.sa_message_id` nonempty=0 | legacy semantics only, not trusted ownership |
| 2026-07-21 | У всех 6 проверенных tables нет FK; chat tables не имеют order indexes; legacy `uploads` disk root — `public_path()` | integrity/path risks confirmed |

## Schema baseline R1

| Source | Order field | Actor evidence | Indexes | Notes |
|---|---|---|---|---|
| `order_user_comments` | `order_id` | `user_id`, `is_admin` | primary only | image IDs/flags + two read flags; 1 missing author |
| `orders_chats` | `orders_id` | только `is_admin` | primary + SA field indexes | 40 null order; нет author ID; SA ID фактически пуст |
| `order_admin_comments` | `orders_id` | nullable `user_id` | primary only | internal stream; 8 rows without attribution per FN-09 |
| `order_painter_comments` | `order_id` | отсутствует | primary only | sender/read semantics absent |
| `order_painter_images` | `order_id` | отсутствует | primary only | `image/small_image`, type/status/show/error flags |
| `order_user_images` | `order_id` | отсутствует | primary only | current local table empty |

Exact orphan counts: `order_user_comments=120`, `orders_chats=49` плюс `40 null-order`,
`order_admin_comments=5`, `order_painter_comments=9`, `order_painter_images=28`,
`order_user_images=0`. Эти строки не удалять и не привязывать автоматически.

## Журнал работы

| Дата | Что сделано | Блокер | Следующее действие |
|---|---|---|---|
| 2026-07-21 | S0-07 открыта после local PASS S0-06; создана target branch | нет | schema/routes/writers/path/ownership inventory |
| 2026-07-21 | Сняты свежие schema/count/orphan/read-flag/file-metadata baselines без чтения текста и файлов; подтверждён public-root legacy uploads disk | route/writer/path classification не завершена | собрать ownership matrix и canonical roots |
| 2026-07-21 | Работа приостановлена после пользовательского обнаружения неполной parity OrderResource | reopened S0-06 | сохранить R1 и возобновить после S0-06 |

## Stop conditions

- query/action принимает ID без подтверждённого order/role ownership;
- streams автоматически объединяются или меняется actor attribution;
- путь выходит за canonical allowed root либо следует по опасной symlink;
- MIME определяется только по extension/client header;
- test вызывает реальные mail/SA/webhook или пишет в legacy business tables/files;
- audit выводит message body, PII, binary content, URL tokens или secrets.
