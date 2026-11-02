# Production deployment — 2026-11-02

Техническое переключение выполнено. Админка остаётся по адресу https://viarcanvas.com/filament. Отдельный обработчик старого `/admin` не подключался по уточнению пользователя.

## Активный код и Git

- Release: `/home/admin/web/viarcanvas.com/releases/20261102-150e859`.
- Commit: `150e8593f4fff144236a8c082fd6963ea7c1bf09`, branch `main`.
- Origin: `https://gitlab.com/OrfeusPro/viar13.git`.
- Release создан как shallow clone из проверенной Git-копии dev на сервере. Доступ к GitLab для fetch не настроен ни у admin, ни у root. Совпадение с remote HEAD не проверено; подтверждено совпадение с установленным dev commit.

Старый `/home/admin/web/viarcanvas.com/public_html` сохранён вместе со старым Git checkout для отката. Не выполнять там обновление Laravel 13 или Artisan под PHP 8.3: новый код находится в releases.

## Production runtime

Production `.env` используется из прежнего public_html. Изменена только строка `DB_DATABASE=admin_main`: остальные bytes совпали с private backup. Username уже был `admin_main`. Dev `.env` не копировался, APP_KEY не менялся.

Существующие public и storage остаются в public_html. Исходный `public/index.php` не менялся, его SHA256 до и после совпал. Ссылка `public/storage` продолжает указывать на прежний `storage/app/public`.

Private runtime находится в `/home/admin/web/viarcanvas.com/private/deploy-20261102`:

- `production-app.php` подключает autoload/bootstrap нового release, задаёт environment/public/storage paths на прежние production-каталоги и подтверждённую DB_DATABASE=admin_main. Compiled views и file cache изолированы в storage нового release. Секретов в этом PHP-файле нет.
- `http.php` — новый front controller.
- `artisan.php` — CLI launcher для того же приложения.
- Прежний public `.user.ini` скопирован без изменения содержимого рядом с private front controller. Исходный файл сохранён. Лимиты upload/post — 300M, max_input_vars — 10000, memory_limit — 512M, input/execution — 180s. После копирования PHP-FPM перезагружен.

PHP 8.3 pool: `/etc/php/8.3/fpm/pool.d/viarcanvas13.conf`; socket `/run/php/php8.3-fpm-viarcanvas13.sock`; пользователь и группа admin. Open_basedir включает каталог production-домена для доступа к releases/private/shared data. Старый pool PHP 7.4 сохранён для отката.

Hestia custom includes `nginx.conf_viar13` и `nginx.ssl.conf_viar13` в `/home/admin/conf/web/viarcanvas.com` добавляют exact location `/index.php` с private front controller и PHP 8.3 socket. Основные generated nginx.conf/nginx.ssl.conf не переписывались. Wildcard custom includes уже были подключены в обоих generated configs.

Рабочие CLI-команды:

```bash
sudo -u admin php8.3 /home/admin/web/viarcanvas.com/private/deploy-20261102/artisan.php COMMAND
```

Простой `php artisan` в старом public_html запускает legacy приложение.

## Assets и зависимости

Скопированы 93 assets: 74 новых, 19 обновлённых; всего 13,108,084 bytes. SHA256 совпал для 93 из 93 файлов. Оригиналы 19 обновлённых файлов сохранены в private `assets-before`.

Обновлялись только существующие файлы кода и статических ресурсов; 2792 существующих non-code entries и symlinks сохранены. Каталоги storage/uploads/orders/audio/img-max/letters исключены, как и PHP/index/hidden config/robots/web.config. Команды удаления public/storage не выполнялись.

Vendor установлен из composer.lock: 148 пакетов, `composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader`. Platform requirements прошли на PHP 8.3.30. Package discovery, Filament assets и Livewire assets затем подготовлены отдельно в release/public с READ ONLY DB и изолированными session/cache. Принудительный publish в production public не выполнялся; подготовленные файлы перенесены по проверенному manifest.

## База данных

Применены только две миграции, указанные через explicit `--path`:

1. `2026_09_25_120000_add_generated_conversions_to_media_table`: nullable JSON `media.generated_conversions`.
2. `2026_10_08_230000_create_admin_email_campaigns_table`: новая таблица `admin_email_campaigns`.

Ledger и наличие schema проверены SELECT в read-only transaction. Эти миграции не меняли orders/chat таблицы.

Общий migrate не запускался. Legacy filename `2025_05_24_102221_add_registration_fields_to_users.php.php` не совпадает с ledger без дополнительного `.php`, хотя все пять полей users уже существуют. Повторно выполнять эту миграцию нельзя. Исправление имени/ledger остаётся отдельной задачей.

## Queue и scheduler

Service `viar-alt-worker.service` переведён на новый release через `/etc/systemd/system/viar-alt-worker.service.d/50-viar13.conf`: PHP 8.3 и private Artisan; Redis queues alt-gen/default; tries=2, backoff=60, timeout=180, sleep=3, memory=256, TimeoutStopSec=240.

Первый запуск падал из-за отключённых в PHP 8.3 `pcntl_signal` и `pcntl_alarm`. В ExecStart добавлен process-only override disable_functions, разрешающий ровно эти две функции. Остальные исходные запреты сохранены, включая shell_exec. Глобальные ограничения PHP/FPM не менялись.

После исправления несколько раз проверено: ActiveState=active, SubState=running, ExecMainStatus=0, NRestarts=0. Выполнение конкретного business job не имитировалось.

Новые задания scheduler не добавлялись. В проверенных /etc/cron.d и root/admin crontab совпадений artisan/viarcanvas не найдено. Другие способы запуска scheduler отдельно не проверялись.

## Проверки и границы

- `nginx -t` и `php-fpm8.3 -t` прошли; graceful reload выполнен.
- CLI smoke и candidate на 127.0.0.1:18083 использовали MySQL session READ ONLY, session/cache=array, Http preventStrayRequests и Mail/Queue fake. Ошибки UrlGenerator/contry_mult были вызваны неполной имитацией request/REQUEST_URI в тестовом скрипте и исправлены там; runtime-код не менялся.
- Candidate pages и все 16 актуальных asset URLs страницы входа Filament прошли проверку. Candidate listener после проверки отключён перемещением конфигурации в private. Публичного debug endpoint нет.
- Production origin HTTPS с `--resolve`: `/`, `/en`, `/lv`, `/login`, `/en/cart`, `/filament/login` — 200 и X-Viar-Release=150e859. `/filament` — 302 на страницу входа. Проверенные CSS/JS и существующее изображение storage — 200.
- Внешняя проверка из Windows по обычному DNS: главная, `/filament/login`, `/en/cart` — 200 и тот же release header.
- На сервере hostname viarcanvas.com резолвится в 127.0.1.1, где nginx не слушает 443. Поэтому server-origin проверки используют `--resolve`; внешний DNS из Windows работает. /etc/hosts не менялся. При наличии self-HTTP интеграций их нужно проверить отдельно.
- Полный baseline из 900 тестов не повторялся и не выдаётся за production acceptance. Реальная авторизация, создание/оплата заказа, загрузка крупных файлов и отправка чата требуют пользовательского smoke. Production заказы и оплаты для теста не создавались.

## Откат

Подготовлен, но не выполнялся как rehearsal:

```bash
python3 /home/admin/web/viarcanvas.com/private/deploy-20261102/rollback.py
```

Скрипт перемещает только собственные custom includes и worker drop-in в private, проверяет nginx и возвращает старый обработчик и worker PHP 7.4. Восстанавливает 19 assets только при совпадении их SHA с выгруженной версией: конкурентные изменения не перезаписывает. Новые добавленные файлы, исправленный DB_DATABASE и additive DB schema сохраняет. Down/drop и удаление public/storage не выполняет.

## Evidence и оставшиеся задачи

Evidence находится в private/deploy-20261102 на сервере: assets-plan.json/assets-before, migration-result.txt, activation-state.json, production.env.before, origin-results.json, login-assets-results.json, final-verification.txt, deployment-manifest.json и runtime/deployment scripts. Manifest фиксирует SHA файлов конфигурации. Backup `.env` имеет режим root0600; его содержимое не переносить в Git или чат.

Осталось настроить GitLab read-access для будущих fetch, согласовать legacy migration filename/ledger, выполнить authenticated UAT админки/чатов/checkout и при фактическом использовании проверить scheduler/self-HTTP. Общая миграция не объявляется DONE на основании технического переключения.