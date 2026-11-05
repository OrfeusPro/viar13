# Production runbook: Laravel 13 frontend

## Назначение

Инструкция для развёртывания публичного frontend `viar` на Laravel 13.
Админ-панель в этот релиз не входит: Voyager routes отключены, Filament 5 —
отдельный будущий этап.

Источник релиза — только этот репозиторий. Проект `viar_filament` не
использовать как источник кода, конфигурации или статуса.

## Подтверждённая среда

- Laravel `13.25.0`;
- PHP `8.3.30`, требование Composer — PHP `^8.3`;
- MySQL;
- web root должен указывать на каталог `public`;
- frontend использует готовые статические CSS/JS: Node/Mix build не требуется;
- `composer validate --no-check-publish` — PASS;
- `composer check-platform-reqs` — PASS.

## 1. Подготовка релиза

Перед обновлением:

1. Зафиксировать текущую git revision и время начала работ.
2. Сделать резервную копию БД, production `.env` и пользовательских файлов из
   `storage/app/public`.
3. Проверить свободное место и возможность записи в `storage` и
   `bootstrap/cache`.
4. Не переносить `.env` из репозитория и не публиковать секреты в логах.
5. Не выполнять `php artisan key:generate` для существующего production:
   необходимо сохранить текущий `APP_KEY`, иначе станут недоступны ранее
   зашифрованные данные и сессии.

Проверки окружения:

```bash
php -v
composer --version
composer validate --no-check-publish
composer check-platform-reqs
```

## 2. Обязательная production-конфигурация

Минимальные правила для `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://production-host.example
SESSION_SECURE_COOKIE=true
SYNVOLVE_TEST_RECEIVER_ENABLED=false
MAIL_VERIFY_PEER=true
```

Дополнительно должны быть заполнены и проверены без вывода значений в консоль:

- `APP_KEY` — сохранить действующий ключ;
- `DB_*` — production БД;
- `SESSION_DRIVER` и соответствующее хранилище; текущий проверенный runtime
  использует `database`;
- `THEME` / `THEME_RESOURCES` — сохранить действующие production-значения;
- `ADMIN_MAIL`, `MAIL_*` — SMTP и адрес отправителя;
- `PAYSERA_PROJECT_ID`, `PAYSERA_SIGN_PASSWORD`, `PAYSERA_TEST=false`;
- `PAYPAL_SANDBOX=false`, `PAYPAL_ID`, `PAYPAL_SECRET`;
- Google/Facebook credentials — если соответствующий вход включён;
- `SA_API_KEY` — не использовать значение `test-key` в production;
- `SYNVOLVE_*` — актуальные URL и SSL verification.

Секреты нельзя хранить в Git, документации или диагностических логах.

## 3. Установка кода и зависимостей

После выкладки согласованной revision:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan optimize:clear
```

Composer autoload должен собраться без PSR-4 warnings.

## 4. База данных

Сначала выполнить только read-only проверку:

```bash
php artisan migrate:status
```

На проверенной БД все миграции имеют статус `Ran`. Если на целевом сервере есть
pending migrations:

1. сверить список с revision релиза;
2. подтвердить наличие свежего backup;
3. только после этого выполнить:

```bash
php artisan migrate --force
```

Не выполнять автоматический `migrate:rollback` при откате приложения: старый
код и новые данные могут требовать отдельного плана восстановления БД.

## 5. Cache policy

Разрешено:

```bash
php artisan optimize:clear
php artisan view:cache
```

Не использовать в текущем релизе:

```bash
php artisan route:cache
php artisan config:cache
php artisan optimize
```

Причины:

- route cache сохраняет только неперефиксованные routes, поэтому
  локализованные `/lv/*`, `/de/*` и другие storefront URL отвечают `404`;
- PayPal legacy service пока читает credentials через runtime `env()`;
- полный optimize в локальной проверке также проявил ранее согласованный
  server-dependent сбой `View [pages.index.footer] not found`.

Рабочее состояние после релиза: config/events/routes — `NOT CACHED`, views —
`CACHED`.

## 6. Storage и права

Каталоги должны существовать и быть доступны PHP на запись:

- `storage/framework/cache`;
- `storage/framework/sessions`;
- `storage/framework/views`;
- `storage/logs`;
- `storage/app/public`;
- `bootstrap/cache`.

`public/storage` должен быть системной ссылкой на `storage/app/public`. В
проверенной Windows-среде это Junction, созданная штатной командой:

```bash
php artisan storage:link
```

Не удалять существующую директорию или ссылку вслепую. Если команда сообщает,
что `public/storage` уже существует, сначала проверить её тип, target и
содержимое обоих деревьев. На проверенной среде junction указывает точно на
`storage/app/public`; новый файл Laravel Storage сразу появился через ссылку и
был получен Apache по HTTPS без запуска PHP.

Compatibility route `GET /storage/{path}` остаётся fallback приложения, но при
исправной системной ссылке файлы должен напрямую обслуживать web server.

## 7. Запуск и smoke-check

После обновления перезапустить PHP worker/PHP-FPM или сбросить OPcache способом,
принятым на сервере. Затем проверить:

```bash
php artisan about
php artisan migrate:status
```

В `artisan about` обязательно:

- `Environment: production`;
- `Debug Mode: OFF`;
- maintenance mode — OFF после завершения;
- routes/config — NOT CACHED;
- views — CACHED.

HTTP smoke через HTTPS:

- `/`;
- `/lv`;
- `/lv/new/gallery/reproduction`;
- `/lv/cart`;
- `/login` и `/register`;
- `/lv/sitemap`;
- по одной странице `ru|en|lv|lt|de|ee`;
- `/pl/*` должен сохранить штатный внешний redirect.

Функциональный smoke:

1. Добавить gallery item в корзину и обновить страницу.
2. Проверить Canvas/portrait upload, включая большой печатный исходник: в
   приложении нет ограничения 20/100 MiB, но лимиты web server/PHP должны
   соответствовать требованиям бизнеса.
3. Пройти data → delivery → payment без создания заказа.
4. Проверить login, logout, registration и password reset.
5. Один реальный заказ создавать только после отдельного подтверждения;
   проверить запись в БД, страницу «Спасибо», очистку корзины и оба письма.
6. Paysera/PayPal production payment не запускать как smoke без согласованной
   реальной транзакции.

После smoke проверить новые записи:

- `storage/logs/laravel.log`;
- `storage/logs/mail.log`;
- PayPal channel log при проверке PayPal.

Старые ошибки лучше архивировать/очищать до начала smoke, чтобы проверять только
текущий релиз.

## 8. Известные ограничения

- Synvolve `POST NewOrder` при последнем реальном заказе вернул
  `503 Database is not ready`; checkout продолжил работу благодаря best-effort
  обработке. Исправление требуется на стороне Synvolve.
- `paypal/paypal-checkout-sdk` помечен abandoned; замена SDK — отдельная задача.
- Универсальный серверный пересчёт цены не внедрён: разные JS-конструкторы
  используют разные контракты. Бизнес-логику цены не менять без отдельного
  аудита каждой формы.
- Admin/Voyager не входит в runtime. Один admin payment-request test ожидаемо
  skipped до этапа Filament 5.
- Полный PHPUnit suite содержит пять классифицированных legacy failures только
  в `SynvolveWebhookServiceTest`; публичный frontend regression suite зелёный.

## 9. Rollback

Если после релиза обнаружена критическая ошибка:

1. Перевести приложение в maintenance mode, если это не блокирует доступ к
   необходимому health-check:

   ```bash
   php artisan down --retry=60
   ```

2. Вернуть предыдущую согласованную git revision и соответствующий
   `composer.lock`.
3. Выполнить:

   ```bash
   composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
   php artisan optimize:clear
   php artisan view:cache
   ```

4. Не откатывать БД автоматически. При несовместимой схеме восстановить backup
   только по отдельному подтверждённому плану.
5. Проверить ключевые URL и логи.
6. Вернуть приложение из maintenance mode:

   ```bash
   php artisan up
   ```

## 10. Критерии закрытия релиза

- [ ] Production backup подтверждён.
- [ ] `APP_DEBUG=false`.
- [ ] Composer/platform checks проходят без warnings/errors.
- [ ] Pending migrations отсутствуют либо согласованно применены.
- [ ] `public/storage` указывает на `storage/app/public`, тестовый asset доступен.
- [ ] Config/events/routes не закэшированы; views закэшированы.
- [ ] Все ключевые HTTPS URL отвечают ожидаемыми кодами.
- [ ] Корзина, auth/account и checkout smoke пройдены.
- [ ] SMTP-письма проверены.
- [ ] Свежие Laravel logs не содержат новых необъяснённых ошибок.
- [ ] Synvolve `503` принят как внешний блокер либо устранён внешней стороной.
