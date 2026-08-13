# 03. Маршрут Laravel 6 → 13

## Выбранная тактика

Production-код переносится в fresh Laravel 13 skeleton гибридно. Отдельная временная ветка может последовательно пройти majors, чтобы находить несовместимости, но не должна становиться единственным production path. На каждом шаге: lock backup → dependency resolution → static scan → unit/feature tests → smoke → tag; при неуспехе возврат к tag и lock.

## Применимые breaking changes

| Переход | Применимо к VIAR | Файлы/проверка | Gate и rollback |
|---|---|---|---|
| 6→7 | Symfony 5 требует `Throwable`; команды должны возвращать int; JSON date serialization меняется; `MAIL_DRIVER→MAIL_MAILER`; unique route names; Swift bindings меняются. | `app/Exceptions/Handler.php`, 13 commands, `BaseModel::serializeDate`, `config/mail.php`, routes, `AppServiceProvider.php`. | API snapshot dates, command exit codes, duplicate route scan. Rollback framework+lock. |
| 7→8 | factories/seeders namespaces; paginator default Tailwind; queue `retryAfter→backoff`, `timeoutAt→retryUntil`; route namespace behavior. | `database/factories`, `database/seeds`, pagination views, queue classes, `RouteServiceProvider`. | Tests/seed smoke; сохранить legacy factory package только временно. |
| 8→9 | PHP ≥8.0.2; Flysystem 3 semantics; SwiftMailer→Symfony Mailer; trusted proxy moves into framework; unvalidated array keys; filesystem ENV rename. | многочисленные `Storage::*`, `AppServiceProvider.php:29`, `TrustProxies.php`, request validation, `config/filesystems.php`. | upload/download/delete, 33 Mailables, SMTP failure tests. Это отдельный stop/go gate. |
| 9→10 | PHP ≥8.1; Composer ≥2.2; Monolog 3; raw DB expressions; `$dates` deprecation; `dispatchNow` removal; PHPUnit 10. | logging config/channels, 27 raw-query sites, models, tests. | log formatting, query result snapshots, test migration. |
| 10→11 | PHP ≥8.2; skeleton становится streamlined, но legacy structure поддерживается; exception/middleware config и model casts/Doctrine changes проверить по guide. | `bootstrap/app.php`, HTTP/Console Kernel, Handler, providers, casts/migrations. | Не переписывать структуру одновременно с доменной логикой; compare fresh skeleton. |
| 11→12 | Carbon 3; local disk default `storage/app/private`; image validation исключает SVG; route precedence; UUIDv7 behavior. | dates, `Storage::disk('local')`, upload rules, duplicate routes, UUID use in SA. | timezone/file/SVG/route tests; explicit local disk root. |
| 12→13 | PHP ≥8.3; CSRF middleware renamed to `PreventRequestForgery` и проверяет request origin; MySQL/MariaDB `upsert`; cache object allow-list; cache/session prefix defaults; route precedence; queue event payloads. | webhook exclusions, API/payment callbacks, raw/upsert search, cache/session config, route files. | callback tests with/without browser headers; explicit prefixes; DB contract tests. |

Официальные guides: [7](https://laravel.com/docs/7.x/upgrade), [8](https://laravel.com/docs/8.x/upgrade), [9](https://laravel.com/docs/9.x/upgrade), [10](https://laravel.com/docs/10.x/upgrade), [11](https://laravel.com/docs/11.x/upgrade), [12](https://laravel.com/docs/12.x/upgrade), [13](https://laravel.com/docs/13.x/upgrade).

## Практический порядок

### L0 — baseline

Команды (только на копии/CI):

```powershell
& 'C:\OSPanel\modules\php\PHP_7.4\php.exe' artisan list --format=json
& 'C:\OSPanel\modules\php\PHP_7.4\php.exe' artisan migrate:status
& 'C:\OSPanel\modules\php\PHP_7.4\php.exe' vendor\bin\phpunit
```

Сначала устранить/зафиксировать baseline route failure с `ImageController`, затем сохранить route snapshot, DB counts, locale URL map, mail/payment/API fixtures. Критерий: старое приложение воспроизводимо и тесты отличают старый дефект от нового.

### L7–L10 — compatibility lab

На изолированной ветке последовательно разрешать только один major. После каждого major запускать dependency audit, route/config cache, tests, scheduler list, queue serialization, upload/filesystem и SMTP sandbox. Не совмещать с Filament.

### L11–L13 — target skeleton

Создать новый Laravel 13 skeleton, перенести ENV names и explicit config, routes малыми группами, затем providers/middleware, domain services/models, controllers, Blade/API. Сохранить legacy kernels только как карту поведения; адаптировать к target skeleton осознанно.

### Проверки каждого major

```bash
composer validate
composer audit
php artisan about
php artisan route:list --json
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan schedule:list
php artisan test
```

Ни одна команда обновления не выполнялась в рамках аудита.

## Критерии перехода между major

- lock воспроизводим на CI;
- нет deprecation/fatal в boot, route/config/view cache;
- все characterization tests зелёные;
- договоры payment/webhook/OAuth/mail подтверждены;
- DB schema/data не изменились вне согласованной миграции;
- подготовлен tag и проверенный rollback;
- замечания и отложенные breaking changes не смешиваются со следующим major.

## Stop criteria

Остановить продвижение, если меняются `orders.id`, статусные строки, locale URLs, платежная идемпотентность, порядок чат-сообщений, доступы ролей, или если backup restore не проверен. Аналогично — при неподтверждённой совместимости критического SDK или отсутствии sandbox/UAT владельца.
