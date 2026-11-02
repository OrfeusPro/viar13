# Production deployment — 2026-11-02

## Текущая схема после уточнения пользователя

Основной Laravel 13 checkout, Git и runtime находятся в `/home/admin/web/viarcanvas.com/public_html`. Первоначальная схема с активным кодом в releases заменена по запросу пользователя: обновляться нужно из привычного `~/web/viarcanvas.com/public_html` под пользователем admin.

- Branch: `main`, tracking `origin/main`.
- Commit: `150e8593f4fff144236a8c082fd6963ea7c1bf09`.
- Origin: `https://gitlab.com/OrfeusPro/viar13.git`.
- HEAD и origin/main совпадают, ahead/behind — 0/0.
- Пользователь выполнил успешный HTTPS fetch от admin до переноса; полученные refs/FETCH_HEAD перенесены с новым Git. Постоянное хранение credentials отдельно не настраивалось и не проверялось.
- Админка остаётся https://viarcanvas.com/filament. Отдельный legacy `/admin` handler не подключён.

Рабочий каталог:

```bash
sudo -iu admin
cd ~/web/viarcanvas.com/public_html
whoami
git status
```

Git и Composer выполнять от admin. Токены не помещать в URL, исходники или чат. Для будущего обновления сначала получить и проверить diff; `git pull --ff-only` выполнять после проверки конфликтов с сохранёнными production-файлами. Git сам по себе не устанавливает новые vendor-зависимости и не применяет миграции.

## Перенос кода и сохранение данных

Новый checkout/vendor подготовлен через rsync от admin без public, storage и production `.env`. Заменяемые каталоги кода, root files и старый `.git` перемещены в `private/publichtml-cutover-20261102/code-before`, а новый код установлен в public_html. Посторонние root files сохранены.

- `.env` не менялся при этом переносе: SHA256 до и после совпал. Ранее при первой выкладке исправлена только `DB_DATABASE=admin_main`; DB_USERNAME уже был admin_main. APP_KEY сохранён, dev `.env` не копировался.
- Каталоги public и storage остались на прежнем месте, их inode до и после совпал. Ничего из них не удалялось.
- Обновлён только `public/index.php` на версию нового Git; предыдущая версия сохранена в private `public-index.before.php`.
- В storage добавлен только отсутствовавший tracked `framework/cache/.gitignore`; существующие файлы не заменялись.
- Все установленные записи кода/vendor/Git принадлежат admin:admin. Проверка владельцев установленных top-level entries и их содержимого не нашла non-admin записей. System configs и административные rollback/evidence файлы остаются под root.

## Runtime и worker

Nginx custom includes `/home/admin/conf/web/viarcanvas.com/nginx.conf_viar13` и `nginx.ssl.conf_viar13` теперь направляют exact `/index.php` непосредственно в `/home/admin/web/viarcanvas.com/public_html/public/index.php` через `/run/php/php8.3-fpm-viarcanvas13.sock`.

Основные Hestia generated configs не переписывались. Wildcard custom includes уже были подключены в обоих server blocks. PHP-FPM pool `/etc/php/8.3/fpm/pool.d/viarcanvas13.conf` работает как admin; open_basedir включает каталог production-домена. Прежний public `.user.ini` снова используется из исходного расположения: upload/post300M, max_input_vars10000, memory512M, input/execution180s.

Worker drop-in `/etc/systemd/system/viar-alt-worker.service.d/50-viar13.conf` использует:

- WorkingDirectory: `/home/admin/web/viarcanvas.com/public_html`.
- PHP8.3 и `/home/admin/web/viarcanvas.com/public_html/artisan`.
- Redis queues alt-gen/default, tries2, backoff60, timeout180, sleep3, memory256, TimeoutStopSec240.
- Process-only disable_functions override разрешает pcntl_signal и pcntl_alarm, отключённые в глобальном PHP8.3 CLI. Остальные исходные запреты сохранены, включая shell_exec. Глобальные PHP/FPM ограничения не менялись.

Private launcher первой выкладки не используется действующим web runtime/worker; старый release удалён по разрешению пользователя. Прямые `php artisan --version` и `php artisan route:list --path=filament/login --json` от admin прошли в public_html. Base path приложения подтверждён как public_html. Laravel Framework — 13.29.0.

Новые scheduler entries не добавлялись. В проверенных /etc/cron.d и root/admin crontab совпадений artisan/viarcanvas не найдено. Другие способы запуска scheduler отдельно не проверялись.

## Git status и production-файлы

Новый репозиторий не полностью чистый, поскольку production public/storage сохранены без принудительного приведения к Git. Итоговая проверка: 74 modified entries, четыре отсутствовавших ранее tracked файла public/orders и один untracked `public/theme/viar/js/registration-captcha.js`. Единственное tracked отличие вне public — `storage/framework/sessions/.gitignore`. Это не удаления, выполненные при переносе: четыре orders-файла отсутствовали до операции и не копировались с dev.

Не применять git clean, reset --hard или массовый restore к production public/storage. Не скрывать эти tracked отличия через skip-worktree/assume-unchanged. При входящих изменениях тех же путей сначала согласовать их с production-копией.

В `.git/info/exclude` от admin добавлены только локальные исключения существующих production-данных: public/orders, generated public/vendor/livewire, runtime cache/data и cache2, `.env.save` и сохранённые старые служебные root files. Это сохраняет файлы и не меняет upstream `.gitignore`. Core.filemode=false установлен локально. Отслеживаемые различия остаются видны.

## Первая выкладка: зависимости, assets и schema

До переноса в public_html новый vendor установлен из composer.lock:148 пакетов, `composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader`. Platform requirements прошли на PHP8.3.30. Затем package discovery, Filament и Livewire assets были подготовлены отдельно в release/public с READ ONLY DB и изолированными session/cache.

Первоначально в production public скопированы 93 assets:74 новых и19 обновлённых,13,108,084 bytes. SHA256 совпал для93/93. Оригиналы19 файлов сохранены в `private/deploy-20261102/assets-before`. Существующие media/config entries сохранялись; команды удаления public/storage не выполнялись. Созданные тогда от root26 каталогов assets впоследствии исправлены на admin:admin. При текущем переносе эти assets не переписывались.

Применены только две additive migrations через explicit path:

1. `2026_09_25_120000_add_generated_conversions_to_media_table`: nullable JSON `media.generated_conversions`.
2. `2026_10_08_230000_create_admin_email_campaigns_table`: новая таблица `admin_email_campaigns`.

Ledger и наличие schema проверены SELECT в read-only transaction. Orders/chat таблицы эти миграции не меняли. При переносе в public_html миграции и Composer повторно не запускались.

Общий migrate не запускать до reconciliation: filename `2025_05_24_102221_add_registration_fields_to_users.php.php` не совпадает с ledger без дополнительного `.php`, хотя все пять полей users уже существуют. Повторно выполнять эту миграцию нельзя.

## Проверки и границы

- До первой выкладки CLI/candidate smoke использовал MySQL session READ ONLY, session/cache=array, Http preventStrayRequests и Mail/Queue fake. Candidate listener127.0.0.1:18083 после проверки отключён.
- После текущего переноса origin HTTPS проверил `/`, `/en`, `/lv`, `/login`, `/en/cart`, `/filament/login`, Filament CSS, Livewire JS и существующее изображение storage:200. `/filament` вернул302 на login.
- Внешняя проверка из Windows по обычному DNS: главная, `/filament/login`, `/en/cart` —200.
- Worker повторно проверен: ActiveState=active, SubState=running, ExecMainStatus=0, NRestarts=0, WorkingDirectory=public_html.
- Пользователь до текущего переноса подтвердил тестовый заказ и вход в админку. Новые production заказы, оплаты и сообщения для автоматической проверки переноса не создавались.
- На сервере hostname viarcanvas.com резолвится в127.0.1.1, где nginx не слушает443. Server-origin проверки используют --resolve; внешний DNS из Windows работает. /etc/hosts не менялся. Self-HTTP интеграции при наличии требуют отдельной проверки.
- Полный baseline900 tests не повторялся: исходники runtime не менялись, проверялся перенос уже установленного кода. Проверка реальной оплаты, отправки чата и крупных загрузок остаётся за пользовательским smoke.

## Резерв releases удалён по разрешению пользователя

После переноса основного checkout/runtime в public_html пользователь разрешил удалить `/home/admin/web/viarcanvas.com/releases`. Каталог с единственным release 20261102-150e859 удалён; перед удалением занимал около843MiB. Основной vendor в public_html сохранён.

Перед удалением подтверждено: nginx и worker используют public_html, симлинков из public_html/private в releases нет. Production .env и public/index.php сохранили SHA256, каталоги public/storage — inode. После удаления главная и /filament/login ответили200 по внешнему HTTPS; worker active/running/NRestarts0.

Автоматический откат на предшествующую release-схему больше недоступен. В private/publichtml-cutover-20261102/rollback.py добавлен и проверен guard: при отсутствии release он завершается до изменения конфигурации или сервисов. Старый delegate rollback тоже защищён этим guard. Остальные private backups/evidence не удалялись. Предварительный полный backup пользователь подтвердил ранее; его восстановление не проверялось.

## Evidence и остаток

Текущий перенос: private/publichtml-cutover-20261102/{before.json,installed.json,code-before,config-before,public-index.before.php,http-results.json,final-state.json,git-status-summary.json,manifest.json} и install/restore/rollback scripts. Первый deployment evidence сохранён в private/deploy-20261102; backup.env не переносить в Git или чат.

Осталось согласовать legacy migration filename/ledger, будущую стратегию tracked production media и runtime ignore rules в основном репозитории; выполнить отдельные проверки оплаты/чатов/загрузок/scheduler/self-HTTP по фактическому использованию. Автоматическая GitLab авторизация без интерактивного ввода отдельно не настроена. Общая миграция не объявляется DONE на основании этого переноса.