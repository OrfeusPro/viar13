# 31. План P0 containment публичных auxiliary-маршрутов

Дата аудита и актуализации: **14.07.2026**.

> Статус: **план, не реализация**. Application code не изменён. Временный прототип удалён после уточнения, что текущая задача — аудит и клиентский план.

## Цель и границы

До framework migration требуется deny-by-default закрыть публичные maintenance, preview, debug и test endpoints. Изменения не должны затрагивать `orders`, статусы, CRM↔SA, `order_user_comments`, каталог/переводы, внутренний вызов gift-card PDF и готовые CSS/JS. Production остаётся единственным write-owner до отдельного cutover.

## Подтверждённая поверхность риска

| Группа | Маршруты / поведение | Основной риск |
|---|---|---|
| content maintenance | `/translate_item`, `/set_meta` | anonymous bulk DB writes, TLS/query defects |
| mail preview | `/mail/test`, `/mail/gift_cart`, `/mail/abandoned_cart*`, `/mail/order`, `/mail/1..7` | PII/template exposure, coupon/token side effects |
| image debug | `/images_single_debug/*`, `?debug=1` | filesystem/debug disclosure |
| gift card | `/giftcard/preview`, `ANY /giftcard/pdf/{price}/{locale}` | public artifact generation, signature mismatch |
| Synvolve test | test receiver при включённом flag | payload disclosure без integration auth |
| admin sitemap | `/admin/sitemap` | неполный admin perimeter |

Внутренних `route()`/URL callers для maintenance/mail/image-debug/public gift-card routes при reference scan не найдено. Gift-card PDF вызывается напрямую из order/payment flow, поэтому блокировать нужно HTTP-вход, а не внутренний service path.

## Рекомендуемый механизм

1. Добавить единый `legacy.aux:<feature>` middleware с пустым `404` до handler и до localization redirect.
2. Все feature flags оставить `false` по умолчанию.
3. В production требовать конкретный flag и отдельный общий break-glass flag.
4. Поставить gate первым в middleware priority, чтобы locale redirect/catch-all не обходил запрет.
5. Не использовать текущий legacy 404 view до исправления undefined `$trackers_body`, иначе ожидаемый 404 может стать 500.
6. Для `admin/sitemap` добавить `admin.user`; отдельно устранить Closure/cache blocker.

Рекомендуемые flags: общий production break-glass, content maintenance, mail preview, image debug, gift-card preview и gift-card HTTP generation. Flags — аварийный операционный механизм, а не постоянный production interface.

## Обязательные тесты реализации

- default deny и отсутствие DB/file/provider side effects;
- explicit enable только в testing/local;
- production double opt-in;
- gate до localization/catch-all для всех семи public locales;
- отсутствие filesystem debug disclosure;
- сохранение внутреннего paid gift-card flow;
- Synvolve production guard без изменения CRM↔SA webhook;
- fresh boot, route registration, `route:list` и `route:cache` после устранения missing `ImageController`/Closures.

Предыдущие числа тестов относились к удалённому прототипу и не считаются доказательством текущего codebase. После реальной реализации suite запускается заново на `C:\OSPanel\modules\php\PHP_7.4\php.exe`.

## Deployment acceptance

Пакет закрывается только после отдельного release: проверен ENV/config-cache fingerprint; все production flags фактически `false`; anonymous probes дают blank 404; DB/files/providers не изменились; orders, payment callbacks, CRM↔SA и seven-locale URLs проходят smoke; подготовлен rollback artifact и owner окна break-glass.

## Остаточные работы

- review security — отдельный пакет 32;
- исправить общий 404/error contract;
- устранить missing `ImageController`, Closures и `require_once` route lifecycle;
- исправить robots self-loop, feed price state leak и redirect/PL conflicts после SEO sign-off;
- заменить опасные HTTP maintenance writers на авторизованные typed command/service с dry-run, журналом и rollback.
