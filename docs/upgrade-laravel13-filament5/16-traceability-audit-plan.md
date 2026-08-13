# 16. План углублённого traceability-аудита

Статус на 13.07.2026: статические L0/L1-реестры и ручные L2-карты критических потоков подготовлены. L3 runtime/production work packages выполняются на этапе 0 после получения инфраструктурных данных и владельцев UAT.

## Цель

Связать каждую внешнюю и административную точку входа с фактическим кодом и данными:

`route/command/job → middleware/validation → controller method → service/repository/model → tables/files/integration → side effects → tests → migration action`.

Этот проход не изменяет приложение. Он создаёт карту, по которой разработчик сможет переносить функциональность небольшими модулями и проверять отсутствие потерь при работающем production.

## Объём

1. Все зарегистрированные Laravel routes, включая package/Voyager и legacy redirects.
2. Все классы и функции в `app/` с file/line/visibility/размером и признаками побочных эффектов.
3. Console commands, scheduler, jobs, observers, notifications, Mailables и provider boot logic.
4. Ручная трассировка критических потоков: order lifecycle, checkout, payment, chat, CRM/SA, translations/content, authentication, email, uploads, Venipak, SEO redirects.
5. Покрытие существующими тестами и список недостающих characterization tests.

## Уровни детализации

| Уровень | Что документируется | Формат |
|---|---|---|
| L0 | Полный машинный реестр без интерпретации | CSV/JSON appendix |
| L1 | Классификация по домену, доступу и риску | Markdown summary |
| L2 | Подробная цепочка вызовов и данных для критического потока | Flow cards в Markdown |
| L3 | Контракт: вход, выход, ошибки, idempotency, side effects, rollback | Migration work package |

## Правила

- Closure и динамическая dispatch помечаются как требующие ручного просмотра.
- Наличие вызова в методе не доказывает его выполнение по всем ветвям; условие фиксируется отдельно в L2.
- Методы package/vendor не копируются в реестр приложения, но route handler и package dependency фиксируются.
- 550 legacy redirects рассматриваются как SEO dataset; они получают полный machine inventory и агрегированную ручную проверку, а не 550 одинаковых описаний.
- Для каждого write-потока учитывается продолжающийся production: system of record, write-owner, concurrency, idempotency и delta reconciliation.
- Для переводов учитываются `translations`, базовые English fields, slugs, links, ordering, SEO и parent deletes.

## Результаты

- `17-route-inventory.md` — сводка и risk classification маршрутов;
- `appendix-routes.csv` — полный зарегистрированный route registry;
- `18-function-inventory.md` — сводка классов/методов и hotspots;
- `appendix-functions.csv` — полный статический method inventory;
- `19-critical-flow-traces.md` — ручные карты критических бизнес-потоков;
- `20-pre-migration-function-audit.md` — очередь незавершённых L3-проверок функций, тесты и Definition of Ready;
- `21-admin-acl-static-audit.md` и `appendix-admin-acl-routes.csv` — L2-разбор guards/side effects и построчная матрица 58 admin routes;
- `22-basket-order-static-audit.md` и `appendix-basket-order-contracts.csv` — L2-разбор money rules, cart state и public/Admin/SA order paths;
- `23-payment-static-audit.md` и `appendix-payment-contracts.csv` — L2-разбор Paysera/PayPal/payment requests, callbacks, receipts и financial side effects;
- `24-files-pdf-static-audit.md` и `appendix-file-pdf-contracts.csv` — L2-разбор public/private storage, uploads, rename/delete, PDF/locale и SA attachments;
- `25-venipak-delivery-static-audit.md` и `appendix-venipak-delivery-contracts.csv` — L2-разбор checkout delivery authority, provider directory, XML, courier/label/print, credentials и replay lifecycle;
- `26-auth-account-chat-static-audit.md` и `appendix-auth-account-chat-contracts.csv` — L2-разбор standard/custom auth, Socialite, session/account ownership и четырёх legacy chat streams;
- `27-mail-queue-scheduler-static-audit.md` и `appendix-mail-queue-scheduler-contracts.csv` — L2-разбор SMTP, 33 Mailables, queues/retries, public previews, пяти scheduled commands и operational delivery semantics;
- `28-public-catalog-generators-static-audit.md` и `appendix-public-catalog-generator-contracts.csv` — L2-разбор public gallery/routes, filters, prices, sessions, generators, translations, SEO и frozen assets;
- `29-voyager-bread-custom-admin-static-audit.md` и `appendix-voyager-bread-modules.csv` — L2-разбор всех 133 BREAD, dynamic routing, custom controllers/views, ACL, translations, media и write-owner cutover;
- `30-seo-redirect-preview-auxiliary-static-audit.md`, `appendix-seo-redirect-map.csv` и `appendix-fn13-route-contracts.csv` — L2-разбор активных redirects, sitemap/feeds/robots, previews/reviews/test APIs и route-cache gate;
- обновления roadmap, risk register, task checklist и клиентского DOCX.

## Критерий готовности

- каждый зарегистрированный route присутствует в appendix и классифицирован;
- каждый метод `app/` присутствует в appendix либо явно исключён как generated/vendor;
- каждый Critical flow имеет owner, входы, данные, side effects, тесты, target design и rollback;
- неизвестные связи не скрыты, а вынесены в open questions;
- traceability data не содержит секретов и payload/PII.

## Итог прохода

- 5 311 зарегистрированных routes в полном CSV;
- 335 application routes/closures в рабочем CSV без redirect dataset;
- 1 770 методов/функций `app/` в статическом CSV;
- выявлены 11 missing handlers, 5 duplicate route-name groups и Critical ACL-аудит для 58 `admin/*` routes с route-level `web`;
- по 58 routes завершён L2 static pass: 38 без обнаруженного auth enforcement, 14 с controller `auth`, 3 ожидаемо публичных, 1 с явным method auth+role, 1 с неуправляемым Auth dereference и 1 unresolved handler; runtime L3 остаётся открытым;
- по FN-01/FN-02 завершён L2 static pass: зафиксированы неодинаковые multiplier/count/rounding rules, дублированный SA coupon calculator, ручной Admin price authority и неатомарный набор order side effects; runtime golden fixtures остаются открытыми;
- по FN-03 завершён L2 static pass: signature без amount validation, несколько write-authority paths, непроверенный PayPal capture response, отсутствие receipt ledger и незащищённый manual status endpoint; sandbox L3 остаётся открытым;
- по FN-04 завершён L2 static pass: 40 contracts, 123 file sinks, ACL/ownership/public-PDF gaps, неатомарный rename, raw SVG/base64 и SA attachment SSRF; production/failpoint L3 остаётся открытым;
- по FN-08 завершён L2 static pass: 40 contracts, client-authoritative delivery price/point, public/admin ACL gaps, duplicated provider readers без timeout/cache, XML без escaping, plaintext/tracked credentials, отсутствие attempt ledger/void/reconciliation и unsafe print; sandbox/runtime L3 остаётся открытым;
- по FN-09 завершён L2 static pass: 56 contracts, parallel auth routes, public raw user lookup, auth-only IDOR/admin impersonation, unsafe Socialite linking/state, четыре chat streams и отсутствие message idempotency/outbox; staging/runtime L3 остаётся открытым;
- по FN-10 завершён L2 static/data pass: 78 contracts, queue=sync/cache=file, SMTP TLS verification off, public state-changing mail previews, consent/unsubscribe gaps, abandoned/overdue first-run risks и ineffective job retry semantics; production/provider/runtime L3 остаётся открытым;
- по FN-11 завершён L2 static/data pass: 72 contracts, 1 038 items/64 779 translations, public catalogue write GET, item-route 404, orphan/unindexed pivots, locale/price/SEO/session contracts и SHA-256 12 frozen outputs; production/browser/runtime L3 остаётся открытым;
- по FN-12 завершён L2 static/data pass: 133 module records, 2 stale definitions, 6 custom controllers, 40 views, 52 custom routes вне `admin.user`, 64 779 translations по всем источникам и per-module migration gates; production/browser/UAT L3 остаётся открытым;
- по FN-13 завершён L2 static/data + limited read-only runtime pass: 256 redirect rules/2 048 routes, 47 auxiliary contracts, 2 003 closures, XML/feed counts, public mutating/debug paths, robots/PL/route-cache defects; production crawler/merchant/security/UAT L3 остаётся открытым;
- описаны 10 критических сквозных потоков, включая непрерывные production-заказы и два storage-контура переводов.
- основные L2 function-audit packages FN-01…FN-13 завершены; P0 containment и все production L3 work packages остаются открытыми.
