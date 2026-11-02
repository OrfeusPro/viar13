# Production deployment — 2026-11-02

## Текущая схема после уточнения пользователя

Основной Laravel 13 checkout, Git и runtime находятся в `/home/admin/web/viarcanvas.com/public_html`. Первоначальная схема с активным кодом в releases заменена по запросу пользователя: обновляться нужно из привычного `~/web/viarcanvas.com/public_html` под пользователем admin.

- Branch: `main`, tracking `origin/main`.
- Текущий server commit: `eea6ce5e7b4830aebbc273de6f808ad8a5f9132a`; установленный исходный release был `150e8593f4fff144236a8c082fd6963ea7c1bf09`.
- Origin: `https://gitlab.com/OrfeusPro/viar13.git`.
- После локального production-коммита HEAD опережает полученную origin/main на один коммит: ahead/behind — 1/0. Push не выполнялся.
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

Общий PHP backend домена переключён штатной командой Hestia `v-change-web-domain-backend-tpl admin viarcanvas.com PHP-8_3 no`, затем выполнены syntax tests и graceful reload. В Hestia сохранено BACKEND=PHP-8_3. Основной template laravel направляет все PHP-запросы в `/run/php/php8.3-fpm-viarcanvas.com.sock`; pool `/etc/php/8.3/fpm/pool.d/viarcanvas.com.conf` работает как admin. Это исправило прежнюю неполную схему, где только index.php использовал8.3, а phpinfo.php оставался7.4.

Custom exact index includes nginx.conf_viar13/nginx.ssl.conf_viar13 и ненужный pool viarcanvas13 выведены из активной конфигурации перемещением в private/domain-php83-20261102. Активный nginx не ссылается на custom socket. Штатная конфигурация теперь поддерживается Hestia; прежнего статического X-Viar-Release header больше нет.

Public `.user.ini` используется из исходного расположения: upload/post300M, max_input_vars10000, memory512M, input/execution180s. Исходные public/storage файлы при исправлении PHP backend не менялись.

Worker drop-in `/etc/systemd/system/viar-alt-worker.service.d/50-viar13.conf` использует:

- WorkingDirectory: `/home/admin/web/viarcanvas.com/public_html`.
- PHP8.3 и `/home/admin/web/viarcanvas.com/public_html/artisan`.
- Redis queues alt-gen/default, tries2, backoff60, timeout180, sleep3, memory256, TimeoutStopSec240.
- Process-only disable_functions override разрешает pcntl_signal и pcntl_alarm, отключённые в глобальном PHP8.3 CLI. Остальные исходные запреты сохранены, включая shell_exec. Глобальные PHP/FPM ограничения не менялись.

Private launcher первой выкладки не используется действующим web runtime/worker; старый release удалён по разрешению пользователя. Прямые `php artisan --version` и `php artisan route:list --path=filament/login --json` от admin прошли в public_html. Base path приложения подтверждён как public_html. Laravel Framework — 13.29.0.

Новые scheduler entries не добавлялись. В проверенных /etc/cron.d и root/admin crontab совпадений artisan/viarcanvas не найдено. Другие способы запуска scheduler отдельно не проверялись.

## Git status и production-файлы

До запрошенного пользователем production-коммита репозиторий содержал сохранённые отличия public/storage. Проверка до коммита: 74 modified entries, четыре отсутствовавших ранее tracked файла public/orders и один untracked `public/theme/viar/js/registration-captcha.js`. Единственное tracked отличие вне public — `storage/framework/sessions/.gitignore`. Это не удаления, выполненные при переносе: четыре orders-файла отсутствовали до операции и не копировались с dev.

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
## Уточнение Git status и PHP backend после замечания пользователя

Из74modified entries73 отличаются от Git blob только окончаниямиCRLF/LF; единственное содержательное отличие — public/.htaccess. Это уточнено прямым побайтовым сравнением с HEAD, а не предположением по расширениям. Git diff и status показывают эти случаи по-разному из-за нормализации text=auto. Index refresh не очистил status. Restore/renormalize/add/skip-worktree не выполнялись; в том числе WebP не переписывались ради чистого статуса. Четыре orders-файла отсутствовали до переноса, untracked registration-captcha.js сохранён.

После штатного переключения Hestia внешний HTTPS phpinfo.php показывает8.3.30. Главная, /filament/login, /en/cart и существующее изображение storage ответили200. Nginx/FPM syntax tests прошли, worker active/running/NRestarts0. Backup исходных Hestia/nginx/pool configs и verified.json находятся в private/domain-php83-20261102; user web.conf backup имеет режим0600. PHPInfo output с переменными окружения не сохранялся в Git или чат, выводилась только версияPHP.

TODO: отдельно согласовать нормализацию исторических окончаний строк и источник registration-captcha.js; не менять эти production-файлы автоматически для очистки Git status.

## Production-коммит по запросу пользователя

От admin в текущей main создан eea6ce5e7b4830aebbc273de6f808ad8a5f9132a (автор коммита Codex, command-only identity codex@local.invalid). Git identity глобально не менялась. Зафиксированы шесть путей: .htaccess, registration-captcha.js и четыре уже отсутствовавших tracked orders-файла. Физические файлы не удалялись. Для всех 79 исходных status paths проверены SHA256 либо факт отсутствия до и после: рабочие bytes не менялись. При git add нормализация/обновление index устранили73пометкиCRLF/LF без отдельных content changes в коммите.

Git status чистый, staged changes отсутствуют, main опережает origin/main на один локальный коммит. Push не выполнялся. Когда remote main продвинется независимо, перед обновлением нужно согласовать локальный production-коммит с upstream; не использовать reset/force push или бездумный restore данных. Evidence: private/production-commit-20261102/{before.json,commit.txt}; .env, backup secrets и прочие runtime data в коммит не включены. Runtime-тесты не повторялись для операции Git: содержимое сайта побайтово сохранилось.

## Дополнение production .env после сравнения с dev

Сравнение показало четыре dev-only keys: MAIL_MAILER и три PAYSERA_* ключа. На prod добавлено 15 переменных, сохранив все предыдущие bytes файла и владельца admin. Dev .env не копировался. Secret backup находится только в private/env-update-20261102, режим0600.

- `MAIL_MAILER=smtp`. Existing production MAIL_DRIVER=SMTP остаётся, но новый mail manager использует явный lowercase smtp. SMTP credentials/port25/encryptionnull сохранены. Transport создаётся без отправки письма.
- `PAYSERA_PROJECT_ID` и `PAYSERA_SIGN_PASSWORD` взяты из backup старого production controller, без вывода значений. `PAYSERA_TEST=false` сохраняет прежний live mode. Настройки загружены, пробный платёж не выполнялся.
- По явному подтверждению пользователя добавлены следующие флаги:

```dotenv
ADMIN_INVOICE_EMAIL_ENABLED=true
ADMIN_PAYMENT_NOTIFICATIONS_ENABLED=true
ADMIN_RECIPIENT_EMAIL_ENABLED=true
ADMIN_REVIEW_REQUEST_ENABLED=true
ADMIN_CLIENT_CHAT_NOTIFICATIONS_ENABLED=true
ADMIN_PAINTER_CHAT_NOTIFICATIONS_ENABLED=true
ADMIN_ARTIST_NOTIFICATIONS_ENABLED=true
ADMIN_ORDER_STATUS_NOTIFICATIONS_ENABLED=true
ADMIN_ORDER_CREATION_NOTIFICATIONS_ENABLED=true
ADMIN_BULK_EMAIL_ENABLED=false
ADMIN_SA_COMMANDS_ENABLED=false
```

Флаги включают обычные email/CRM side effects будущих действий пользователей, но тестовые сообщения при изменении env не отправлялись. Worker перезапущен, active/running/NRestarts0. Config cache отсутствовал, общая очистка cache/storage не запускалась. Production Git status остаётся чистым, `.env` не отслеживается. Реальную доставку письма и оплату нужно проверить отдельным согласованным сценарием.

TODO отдельно: SA_API_KEY отсутствует в обеих .env и имеет тестовый default в config/services.php. Его замену нужно согласовать с внешней стороной SA; односторонняя ротация в этом шаге не выполнялась.