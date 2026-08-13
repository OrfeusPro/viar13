# APP-005B-FORM-03 — Перенос оставшихся публичных форм frontend

```text
TASK_ID: APP-005B-FORM-03
STATUS: BACKLOG
PRIORITY: Critical
OWNER: программист
BRANCH: TBD
CREATED_AT: 2026-07-27
UPDATED_AT: 2026-07-27
DEPENDS_ON: APP-005B-PAGES-01, применимые page-family routes
PARENT: APP-005
NEXT_ACTION: после page inventory снять отдельный call graph шести legacy form routes
```

## Scope

Шесть route: portrait photo form, `user/send_screen`, `user/send_dates`,
`user/send_free_image`, stock coupon и friend email. Для каждого нужны exact
request/file/order/mail/session/provider effects, idempotency, locale,
failure contract и собственные gates.

## Definition of Done

- UI/file/validation/success behavior совпадает с legacy;
- DB/file/mail/provider side effects наблюдаемы и idempotent;
- outbound и production credentials default-off;
- client/admin письма локализованы по назначению;
- automated и ручная browser/mail acceptance PASS.
