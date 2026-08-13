# 56. Filament Stage 0 — S0-02 provisioning preflight

Дата: 17.07.2026. Статус: **S02-A/B/C выполнены; PHP PASS, dev vhost/isolation BLOCKED; установка Laravel/Filament запрещена до PASS**.

Обновление после выполнения: S02-A/B/C получены 17.07.2026. PHP 8.3.30 CLI/FPM и extensions получили PASS; dev domain/root/storage/env/isolation остаются blocked/partial. Полный разбор и следующий S02-D: `57-filament-stage0-s02-preflight-result.md`.

## 1. Цель

Проверить, можно ли безопасно использовать `dev.viarcanvas.com` как isolated staging для Laravel 13 / Filament 5, не затрагивая работающий `viarcanvas.com`.

Уже известно:

- production vhost использует dedicated PHP 7.4 FPM socket;
- для `dev.viarcanvas.com` существует PHP 8.3 pool, но ранее Nginx показывал suspended root;
- наличие `ext-intl` в server PHP 8.3 CLI/FPM не подтверждено;
- production orders, translations и content продолжают изменяться;
- На момент S02-A/B/C `test.viarcanvas.com` был исключён. Новое решение 17.07.2026: test разрешено полностью заменить и использовать как staging; см. документ 59.

Все команды ниже read-only. Они не выполняют `apt install`, не перезапускают сервисы, не создают vhost/БД, не меняют `.env` и не запускают Composer/npm.

## 2. Команда S02-A — OS, PHP CLI/FPM и packages

Выполнять от `root` на сервере:

```bash
echo '=== S02-A SYSTEM ==='
date -u
hostnamectl 2>/dev/null | sed -n '1,12p'
sed -n '1,20p' /etc/os-release

echo '=== S02-A PHP BINARIES AND MODULES ==='
for ver in 8.3 8.4; do
    cli="/usr/bin/php${ver}"
    fpm="/usr/sbin/php-fpm${ver}"

    echo "-- PHP ${ver} --"

    if [ -x "$cli" ]; then
        "$cli" -r 'printf("cli_version=%s\ncli_sapi=%s\nini=%s\n", PHP_VERSION, PHP_SAPI, php_ini_loaded_file());'

        for ext in bcmath ctype curl dom fileinfo filter gd hash iconv intl json libxml mbstring openssl pdo_mysql session tokenizer xml xmlreader xmlwriter zip; do
            if "$cli" -m | grep -Fxiq "$ext"; then
                printf 'cli_ext_%s=YES\n' "$ext"
            else
                printf 'cli_ext_%s=NO\n' "$ext"
            fi
        done
    else
        echo 'cli=NOT_INSTALLED'
    fi

    if [ -x "$fpm" ]; then
        "$fpm" -v 2>&1 | head -n 2
        "$fpm" -i 2>/dev/null \
          | grep -Ei '^(Loaded Configuration File|Scan this dir for additional .ini files|memory_limit|post_max_size|upload_max_filesize|max_execution_time|date.timezone|opcache.enable|opcache.memory_consumption|Internationalization support)' \
          | head -n 40
    else
        echo 'fpm=NOT_INSTALLED'
    fi

    printf 'service_active='
    systemctl is-active "php${ver}-fpm.service" 2>/dev/null || true

    printf 'service_enabled='
    systemctl is-enabled "php${ver}-fpm.service" 2>/dev/null || true

    for sapi in cli fpm; do
        dir="/etc/php/${ver}/${sapi}/conf.d"
        if [ -d "$dir" ]; then
            printf '%s_conf_count=' "$sapi"
            find "$dir" -maxdepth 1 -type l | wc -l
            find "$dir" -maxdepth 1 -type l -printf '%f -> %l\n' | sort
        else
            echo "${sapi}_conf_dir=ABSENT"
        fi
    done
done

echo '=== S02-A PACKAGE STATE ==='
dpkg-query -W -f='${Package}|${Version}|${Status}\n' \
  'php8.3-*' 'php8.4-*' 2>/dev/null \
  | grep -E 'php8\.[34]-(cli|common|fpm|bcmath|curl|gd|intl|mbstring|mysql|opcache|xml|zip)' \
  | sort || true

for pkg in php8.3-intl php8.4-intl php8.3-fpm php8.4-fpm; do
    echo "-- apt-cache policy $pkg --"
    apt-cache policy "$pkg" 2>/dev/null | sed -n '1,12p'
done
```

## 3. Команда S02-B — vhost, FPM pool и filesystem root

Команда выводит только whitelisted directives, без SSL private keys и без `.env` values.

```bash
PROD_DOMAIN='viarcanvas.com'
STAGE_DOMAIN='dev.viarcanvas.com'
PROD_ROOT="/home/admin/web/${PROD_DOMAIN}/public_html"
STAGE_ROOT="/home/admin/web/${STAGE_DOMAIN}/public_html"

echo '=== S02-B HESTIA DOMAIN STATE ==='
if [ -x /usr/local/hestia/bin/v-list-web-domain ]; then
    /usr/local/hestia/bin/v-list-web-domain admin "$STAGE_DOMAIN" 2>&1 \
      | sed -E 's/([A-Z0-9_]*(PASSWORD|SECRET|TOKEN|KEY)[A-Z0-9_]*[=:])[[:graph:]]+/\1<redacted>/Ig'
else
    echo 'HESTIA_DOMAIN_COMMAND_NOT_FOUND'
fi

echo '=== S02-B NGINX DEV CONFIG ==='
for file in \
  "/home/admin/conf/web/${STAGE_DOMAIN}/nginx.conf" \
  "/home/admin/conf/web/${STAGE_DOMAIN}/nginx.ssl.conf" \
  "/home/admin/conf/web/${STAGE_DOMAIN}/nginx.forcessl.conf"
do
    if [ -f "$file" ]; then
        echo "-- $file --"
        grep -nEi 'server_name|^[[:space:]]*root |fastcgi_pass|proxy_pass|include |return 30[1278]|client_max_body_size|access_log|error_log' "$file" || true
    else
        echo "ABSENT | $file"
    fi
done

echo '=== S02-B PHP 8.3 DEV POOL ==='
POOL='/etc/php/8.3/fpm/pool.d/dev.viarcanvas.com.conf'
if [ -f "$POOL" ]; then
    grep -nE '^[[:space:]]*(listen|listen.owner|listen.group|listen.mode|user|group|pm|pm\.max_children|pm\.max_requests|pm\.process_idle_timeout|request_terminate_timeout|request_slowlog_timeout|slowlog|catch_workers_output|clear_env)[[:space:]]*=' "$POOL" || true
else
    echo 'DEV_PHP83_POOL=ABSENT'
fi

echo '=== S02-B ROOTS AND STORAGE LINKS ==='
for path in \
  "$PROD_ROOT" \
  "$PROD_ROOT/.env" \
  "$PROD_ROOT/storage" \
  "$STAGE_ROOT" \
  "$STAGE_ROOT/.env" \
  "$STAGE_ROOT/storage"
do
    if [ -e "$path" ]; then
        stat -c '%A | %U:%G | %s | %n' "$path"
    else
        echo "ABSENT | $path"
    fi
done

printf 'production_storage_link='
readlink -f "$PROD_ROOT/public/storage" 2>/dev/null || echo 'ABSENT'

printf 'staging_storage_link='
readlink -f "$STAGE_ROOT/public/storage" 2>/dev/null || echo 'ABSENT'

echo '=== S02-B LOCAL HTTPS RESPONSE ==='
STAGE_IP="$(getent ahostsv4 "$STAGE_DOMAIN" | awk 'NR==1 {print $1}')"
curl -ksSIL --max-redirs 3 \
  --resolve "${STAGE_DOMAIN}:443:${STAGE_IP}" \
  "https://${STAGE_DOMAIN}/" \
  | grep -Ei '^(HTTP/|location:|server:|content-type:|strict-transport-security:|set-cookie:)' \
  | sed -E 's/^(set-cookie:[[:space:]]*[^=]+=)[^;]*/\1<redacted>/I' \
  || echo 'DEV_HTTPS_CHECK_FAILED'
```

## 4. Команда S02-C — безопасное сравнение `.env`

Команда никогда не печатает значения secrets. Для non-secret isolation variables выводятся значения staging, чтобы можно было проверить фактические DB/Redis/session boundaries.

```bash
PROD_ENV='/home/admin/web/viarcanvas.com/public_html/.env'
STAGE_ENV='/home/admin/web/dev.viarcanvas.com/public_html/.env'

env_value() {
    file="$1"
    key="$2"
    [ -f "$file" ] || return 0
    sed -n "s/^${key}=//p" "$file" | tail -n 1 | sed -E 's/^"(.*)"$/\1/'
}

compare_secret() {
    key="$1"
    prod="$(env_value "$PROD_ENV" "$key")"
    stage="$(env_value "$STAGE_ENV" "$key")"

    if [ -z "$prod" ] && [ -z "$stage" ]; then
        result='BOTH_MISSING'
    elif [ -z "$prod" ]; then
        result='STAGING_ONLY'
    elif [ -z "$stage" ]; then
        result='PRODUCTION_ONLY'
    elif [ "$prod" = "$stage" ]; then
        result='SAME'
    else
        result='DIFFERENT'
    fi

    printf '%s=%s\n' "$key" "$result"
}

echo '=== S02-C ENV FILES ==='
for file in "$PROD_ENV" "$STAGE_ENV"; do
    if [ -f "$file" ]; then
        stat -c '%A | %U:%G | %s | %n' "$file"
    else
        echo "ABSENT | $file"
    fi
done

echo '=== S02-C NON-SECRET STAGING BOUNDARIES ==='
if [ -f "$STAGE_ENV" ]; then
    grep -E '^(APP_ENV|APP_DEBUG|APP_URL|LOG_CHANNEL|DB_CONNECTION|DB_HOST|DB_PORT|DB_DATABASE|CACHE_STORE|CACHE_DRIVER|QUEUE_CONNECTION|REDIS_HOST|REDIS_PORT|REDIS_DB|REDIS_CACHE_DB|REDIS_PREFIX|CACHE_PREFIX|SESSION_DRIVER|SESSION_CONNECTION|SESSION_STORE|SESSION_COOKIE|SESSION_DOMAIN|SESSION_SECURE_COOKIE|FILESYSTEM_DISK|FILESYSTEM_DRIVER)=' "$STAGE_ENV" || true
fi

echo '=== S02-C REQUIRED DIFFERENCES ==='
for key in \
  APP_KEY DB_DATABASE DB_USERNAME DB_PASSWORD \
  REDIS_DB REDIS_CACHE_DB REDIS_PREFIX CACHE_PREFIX \
  SESSION_COOKIE SESSION_DOMAIN
do
    compare_secret "$key"
done

echo '=== S02-C PROVIDER SECRET/ENDPOINT ISOLATION ==='
for key in \
  MAIL_HOST MAIL_USERNAME MAIL_PASSWORD \
  PAYPAL_ID PAYPAL_SECRET PAYPAL_SANDBOX_ID PAYPAL_SANDBOX_SECRET \
  FACEBOOK_CLIENT_ID FACEBOOK_CLIENT_SECRET GOOGLE_CLIENT_ID GOOGLE_CLIENT_SECRET \
  SYNVOLVE_ORDER_WEBHOOK_URL SYNVOLVE_MANAGER_MESSAGE_WEBHOOK_URL \
  SYNVOLVE_BOT_STATUS_WEBHOOK_URL SYNVOLVE_LEAD_UPDATE_WEBHOOK_URL
do
    compare_secret "$key"
done
```

## 5. PASS/FAIL interpretation

| Gate | PASS | FAIL / stop |
|---|---|---|
| Domain/root | `dev.viarcanvas.com` points to an active dedicated root | Hestia suspended template/root, production root or missing vhost |
| PHP | PHP 8.3 or 8.4 CLI and dedicated FPM pool active | only generic/shared pool, missing binary/service |
| Extensions | all required modules, especially `intl`, present in CLI and FPM | any required module absent; ignore flags needed |
| APP identity | staging `APP_KEY`, URL and cookie are isolated | same `APP_KEY` or cookie collision with production |
| Database | separate sanitized DB and least-privilege staging user | production DB/user or DML-capable production connection |
| Redis | separate DB numbers and prefixes; no production worker | DB/prefix/queue collision |
| Files | root/storage/link resolve under staging tree | link or write path resolves into production |
| Providers | sandbox/disabled/log-only; secrets/endpoints not production | production payment/mail/Socialite/Synvolve credentials active |
| HTTPS | local `--resolve` returns expected dev vhost | suspended page, wrong certificate/vhost, production redirect |

`APP_KEY=SAME`, production DB credentials, production Redis namespace, shared storage path или production provider secrets — безусловный `NO-GO`.

## 6. Рекомендуемый Stage 0 isolation contract

- `APP_ENV=staging`, `APP_DEBUG=false`, отдельный `APP_KEY`;
- host-only или отдельный `SESSION_DOMAIN`, уникальный `SESSION_COOKIE`;
- отдельная sanitized DB без персональных данных, отдельный DB user;
- на общем Redis допустимы только отдельные DB/prefix/queue names; лучше отдельный Redis instance;
- workers/scheduler не запускаются до отдельного разрешения;
- mail — log/sink, PayPal — sandbox, Synvolve/SA — disabled/test receiver, Socialite callbacks — dev-only;
- отдельный storage root; production uploads подключаются только read-only после отдельного решения;
- public assets production не копируются через npm/Vite и не пересобираются.

## 7. Следующий порядок

1. Выполнить S02-A, S02-B и S02-C и передать полный вывод без ручного редактирования.
2. По фактическому package/vhost/env state подготовить отдельный **mutation plan**: установка недостающих PHP packages, активация vhost/pool и создание isolated storage/DB boundaries.
3. Перед любой mutation сохранить server config backup и rollback commands.
4. После provisioning повторить preflight и получить PASS.
5. Только затем создавать fresh Laravel 13 skeleton и устанавливать Panel `/backoffice-next`.
