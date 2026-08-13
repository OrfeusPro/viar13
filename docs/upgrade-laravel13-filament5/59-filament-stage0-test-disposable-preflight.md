# 59. Filament Stage 0 — disposable `test.viarcanvas.com` preflight

Дата: 17.07.2026. Статус: **TEST-S02-A выполнен; test пригоден как disposable staging, но текущий app сначала требуется изолировать**.

Результат и следующий безопасный блок зафиксированы в [60-filament-stage0-test-preflight-result-and-quarantine.md](60-filament-stage0-test-preflight-result-and-quarantine.md). TEST-S02-A повторять не требуется; replacement ещё не выполнялся.

## 1. Решение

`test.viarcanvas.com` выбирается вместо `dev.viarcanvas.com` как основной staging candidate:

- public DNS уже указывает на `65.108.243.50`;
- HTTPS valid и возвращает 200/Nginx;
- старое test-приложение разрешено полностью заменить;
- восстанавливать его как рабочую среду не требуется.

Важно: «перезалить с локального» означает загрузить **отдельный Laravel 13 / Filament 5 target**, а не копировать текущий Laravel 6/Voyager поверх test. In-place `composer update` существующего test запрещён.

Перед replacement остаётся короткий read-only gate: убедиться, что test не владеет worker/cron, измерить объём, зафиксировать Nginx/PHP mapping и доказать отсутствие ссылок на production DB/Redis/providers.

## 2. TEST-S02-A — последний read-only block

```bash
PROD_ROOT='/home/admin/web/viarcanvas.com/public_html'
TEST_ROOT='/home/admin/web/test.viarcanvas.com/public_html'
PROD_ENV="$PROD_ROOT/.env"
TEST_ENV="$TEST_ROOT/.env"
TEST_DOMAIN='test.viarcanvas.com'

echo '=== TEST-S02-A DOMAIN AND NGINX ==='
/usr/local/hestia/bin/v-list-web-domain admin "$TEST_DOMAIN" 2>/dev/null \
  | grep -E '^(DOMAIN|IP|DOCUMENT_ROOT|SSL|LETSENCRYPT|SSL_FORCE|TEMPLATE|BACKEND|SUSPENDED):' || true

for file in \
  "/home/admin/conf/web/${TEST_DOMAIN}/nginx.conf" \
  "/home/admin/conf/web/${TEST_DOMAIN}/nginx.ssl.conf" \
  "/home/admin/conf/web/${TEST_DOMAIN}/nginx.forcessl.conf"
do
    if [ -f "$file" ]; then
        echo "-- $file --"
        grep -nEi 'server_name|^[[:space:]]*root |fastcgi_pass|proxy_pass|include |return 30[1278]|access_log|error_log' "$file" || true
    else
        echo "ABSENT | $file"
    fi
done

echo '=== TEST-S02-A PHP AND BACKEND TEMPLATES ==='
for pool in \
  /etc/php/7.4/fpm/pool.d/test.viarcanvas.com.conf \
  /etc/php/8.3/fpm/pool.d/test.viarcanvas.com.conf
do
    if [ -f "$pool" ]; then
        echo "-- $pool --"
        grep -nE '^[[:space:]]*(listen|user|group|pm|pm\.max_children|pm\.max_requests|pm\.process_idle_timeout)[[:space:]]*=' "$pool" || true
    else
        echo "ABSENT | $pool"
    fi
done

find /usr/local/hestia/data/templates/web \
  -maxdepth 5 -type f \
  \( -iname '*8_3*.tpl' -o -iname '*8_3*.stpl' -o -iname 'laravel.tpl' -o -iname 'laravel.stpl' \) \
  -printf '%p\n' \
  | sort

if command -v composer >/dev/null 2>&1; then
    /usr/bin/php8.3 "$(command -v composer)" --version
else
    echo 'COMPOSER_COMMAND_NOT_FOUND'
fi

echo '=== TEST-S02-A SIZE OWNERSHIP AND LINKS ==='
for path in "$TEST_ROOT" "$TEST_ENV" "$TEST_ROOT/storage" "$TEST_ROOT/public"; do
    if [ -e "$path" ]; then
        stat -c '%A | %U:%G | %s | %n' "$path"
    else
        echo "ABSENT | $path"
    fi
done

du -sh "$TEST_ROOT" "$TEST_ROOT/storage" 2>/dev/null || true
printf 'test_storage_link='
readlink -f "$TEST_ROOT/public/storage" 2>/dev/null || echo 'ABSENT'
df -hPT / /home 2>/dev/null

echo '=== TEST-S02-A WORKER CRON AND SERVICE REFERENCES ==='
MATCHES="$(grep -RFl "$TEST_ROOT" \
  /etc/systemd/system /lib/systemd/system /etc/cron.d /var/spool/cron/crontabs \
  2>/dev/null | sort)"
if [ -n "$MATCHES" ]; then printf '%s\n' "$MATCHES"; else echo 'TEST_RUNTIME_REFERENCE_NOT_FOUND'; fi

echo '=== TEST-S02-A LOG METADATA ==='
for log in \
  "/var/log/nginx/domains/${TEST_DOMAIN}.log" \
  "/var/log/nginx/domains/${TEST_DOMAIN}.error.log" \
  "$TEST_ROOT/storage/logs/laravel.log"
do
    if [ -f "$log" ]; then
        stat -c '%s bytes | %y | %U:%G | %a | %n' "$log"
        printf 'line_count | '
        wc -l < "$log"
    else
        echo "ABSENT | $log"
    fi
done

env_value() {
    file="$1"; key="$2"
    [ -f "$file" ] || return 0
    sed -n "s/^${key}=//p" "$file" | tail -n 1 | sed -E 's/^"(.*)"$/\1/'
}

compare_secret() {
    key="$1"
    prod="$(env_value "$PROD_ENV" "$key")"
    testv="$(env_value "$TEST_ENV" "$key")"
    if [ -z "$prod" ] && [ -z "$testv" ]; then result='BOTH_MISSING'
    elif [ -z "$prod" ]; then result='TEST_ONLY'
    elif [ -z "$testv" ]; then result='PRODUCTION_ONLY'
    elif [ "$prod" = "$testv" ]; then result='SAME'
    else result='DIFFERENT'; fi
    printf '%s=%s\n' "$key" "$result"
}

echo '=== TEST-S02-A NON-SECRET TEST SETTINGS ==='
if [ -f "$TEST_ENV" ]; then
    grep -E '^(APP_ENV|APP_DEBUG|APP_URL|DB_CONNECTION|DB_HOST|DB_PORT|DB_DATABASE|CACHE_STORE|CACHE_DRIVER|QUEUE_CONNECTION|REDIS_HOST|REDIS_PORT|REDIS_DB|REDIS_CACHE_DB|REDIS_PREFIX|CACHE_PREFIX|SESSION_DRIVER|SESSION_COOKIE|SESSION_DOMAIN|SESSION_SECURE_COOKIE|FILESYSTEM_DISK|FILESYSTEM_DRIVER|PAYPAL_SANDBOX|FACEBOOK_ENABLE|SYNVOLVE_TEST_RECEIVER_ENABLED|SYNVOLVE_WEBHOOK_VERIFY_SSL)=' "$TEST_ENV" || true
fi

echo '=== TEST-S02-A ISOLATION COMPARISON ==='
for key in \
  APP_KEY DB_DATABASE DB_USERNAME DB_PASSWORD \
  REDIS_DB REDIS_CACHE_DB REDIS_PREFIX CACHE_PREFIX \
  SESSION_COOKIE SESSION_DOMAIN \
  MAIL_HOST MAIL_USERNAME MAIL_PASSWORD \
  PAYPAL_ID PAYPAL_SECRET PAYPAL_SANDBOX_ID PAYPAL_SANDBOX_SECRET \
  FACEBOOK_CLIENT_ID FACEBOOK_CLIENT_SECRET GOOGLE_CLIENT_ID GOOGLE_CLIENT_SECRET \
  SYNVOLVE_ORDER_WEBHOOK_URL SYNVOLVE_MANAGER_MESSAGE_WEBHOOK_URL \
  SYNVOLVE_BOT_STATUS_WEBHOOK_URL SYNVOLVE_LEAD_UPDATE_WEBHOOK_URL
do
    compare_secret "$key"
done
```

## 3. После PASS

Будет подготовлен один controlled replacement block: suspend только test; root-only config backup; проверка абсолютных путей; новый Laravel 13 release tree; Laravel web template и dedicated PHP 8.3 backend; новый isolated `.env`; exact-lock install/upload; health checks; Basic Auth; rollback через re-suspend и возврат configs/docroot.

Production `viarcanvas.com`, его PHP 7.4 pool, DB, Redis, storage и providers не изменяются.
