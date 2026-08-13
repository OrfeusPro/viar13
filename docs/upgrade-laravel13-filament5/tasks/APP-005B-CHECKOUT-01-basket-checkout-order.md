# APP-005B-CHECKOUT-01 — Полный поток корзина → checkout → создание заказа

```text
TASK_ID: APP-005B-CHECKOUT-01
STATUS: BACKLOG
PRIORITY: Critical
OWNER: программист
BRANCH: TBD
CREATED_AT: 2026-07-27
UPDATED_AT: 2026-07-27
DEPENDS_ON: CAT-001, CAT-002, APP-001; payment capture remains INT-002/INT-003
PARENT: APP-005
NEXT_ACTION: после generator payload parity построить basket item-shape и money characterization corpus
```

## Scope

59 legacy routes: basket mutations, cart steps, coupon/bonus/session state,
order creation/change, gift-card/PDF linkage и семь unsafe GET mutations.
Payment provider capture не смешивается с этой задачей: checkout сначала
создаёт idempotent local order/payment attempt, adapters закрываются отдельно.

## Definition of Done

- все product-family item shapes и price rules покрыты differential fixtures;
- basket/session/coupon/bonus transitions валидируются server-side;
- order создаётся ровно один раз при retry/concurrency;
- DB/files/mail/Synvolve оформлены transaction/outbox/receipts;
- unsafe GET заменены безопасными methods без потери outcome;
- payment redirects/callbacks не считаются authority до `CUT-004`;
- full failure/replay/zero-duplicate suite PASS.
