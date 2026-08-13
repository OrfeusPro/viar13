# 61. Filament Stage 0 — clean test root и PHP 8.3 backend

Дата: 17.07.2026. Статус: **HOLD — текущий test подтверждён active на PHP 7.4; не выполнять до готовности нового Laravel 13 / Filament 5 artifact и отдельного подтверждения пользователя**.

> Решение пользователя после TEST-S02-B: текущий test должен продолжать работать. Сначала готовится и проверяется новый target вне active test root; только затем согласуется короткое переключение. Командный блок ниже сохранён как будущий cutover-preparation draft, а не как текущее действие.

## 1. Назначение и границы

TEST-S02-C выполняет только подготовку target runtime:

- подтверждает root-only backup из TEST-S02-B по SHA-256;
- требует, чтобы `test.viarcanvas.com` оставался suspended;
- same-filesystem `mv` сохраняет весь старый `public_html` как rollback tree;
- закрывает permissions старого `.env` до `0600`;
- создаёт пустой новый `public_html/public` без `.env`, vendor и application code;
- переключает backend только test `PHP-7_4 → PHP-8_3`;
- проверяет dedicated pool/socket, Hestia state и Nginx;
- не unsuspend домен, не запускает Composer, не подключает DB/Redis/providers и не меняет production.

Если команда завершается ошибкой, ERR trap пытается вернуть PHP 7.4 и исходный root. Если новый root неожиданно перестал быть пустым, destructive cleanup не выполняется: домен остаётся suspended для ручного разбора.

## 2. TEST-S02-C — выполнить от root одним блоком

```bash
set -euo pipefail

DOMAIN='test.viarcanvas.com'
BASE='/home/admin/web/test.viarcanvas.com'
CURRENT_ROOT="$BASE/public_html"
CONF='/home/admin/conf/web/test.viarcanvas.com'
HESTIA='/usr/local/hestia/bin'
BACKUP_DIR='/root/viar-stage0-backups/test-quarantine-20260717T120810Z'
EXPECTED_SHA='ebb54bccce82c3856b6ef8058ae1f5c26f52b3517850f0a3498cdcea726f888c'
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
LEGACY_ROOT="$BASE/public_html.legacy-$STAMP"
PHP83_POOL='/etc/php/8.3/fpm/pool.d/test.viarcanvas.com.conf'
PHP83_SOCKET='/run/php/php8.3-fpm-test.viarcanvas.com.sock'

MOVED=0
BACKEND_CHANGED=0

rollback() {
    rc=$?
    trap - ERR
    set +e
    echo "=== TEST-S02-C AUTOMATIC ROLLBACK rc=$rc ==="

    if [ "$BACKEND_CHANGED" -eq 1 ]; then
        "$HESTIA/v-change-web-domain-backend-tpl" admin "$DOMAIN" PHP-7_4 yes
    fi

    if [ "$MOVED" -eq 1 ]; then
        rm -f -- "$CURRENT_ROOT/.stage0-target-placeholder"
        rmdir -- "$CURRENT_ROOT/public" 2>/dev/null
        if rmdir -- "$CURRENT_ROOT" 2>/dev/null; then
            mv -- "$LEGACY_ROOT" "$CURRENT_ROOT"
            echo 'ROOT_ROLLBACK=COMPLETED'
        else
            echo 'ROOT_ROLLBACK=NOT_AUTOMATIC_NEW_ROOT_NOT_EMPTY'
            echo "LEGACY_ROOT_RETAINED=$LEGACY_ROOT"
        fi
    fi

    nginx -t || true
    echo 'DOMAIN_MUST_REMAIN_SUSPENDED'
    exit "$rc"
}

trap rollback ERR

echo '=== TEST-S02-C PATH AND STATE GUARDS ==='
[ "$(id -u)" -eq 0 ] || { echo 'ROOT_REQUIRED'; exit 1; }
[ "$(readlink -f "$BASE")" = "$BASE" ] || { echo 'BASE_PATH_GUARD_FAILED'; exit 1; }
[ "$(readlink -f "$CURRENT_ROOT")" = "$CURRENT_ROOT" ] || { echo 'ROOT_PATH_GUARD_FAILED'; exit 1; }
[ "$(readlink -f "$CONF")" = "$CONF" ] || { echo 'CONF_PATH_GUARD_FAILED'; exit 1; }
[ -d "$CURRENT_ROOT" ] || { echo 'CURRENT_ROOT_ABSENT'; exit 1; }
[ ! -e "$LEGACY_ROOT" ] || { echo 'LEGACY_TARGET_ALREADY_EXISTS'; exit 1; }
[ -x "$HESTIA/v-change-web-domain-backend-tpl" ] || { echo 'BACKEND_COMMAND_MISSING'; exit 1; }

DOMAIN_STATE="$("$HESTIA/v-list-web-domain" admin "$DOMAIN")"
printf '%s\n' "$DOMAIN_STATE" | grep -Eq '^SUSPENDED:[[:space:]]+yes$' \
  || { echo 'DOMAIN_NOT_SUSPENDED'; exit 1; }

grep -Eq '^[[:space:]]*root[[:space:]]+/usr/local/hestia/data/templates/web/suspend;' \
  "$CONF/nginx.conf" "$CONF/nginx.ssl.conf" \
  || { echo 'SUSPEND_ROOT_NOT_CONFIRMED'; exit 1; }

RUNTIME_REFS="$(grep -RFl "$CURRENT_ROOT" \
  /etc/systemd/system /lib/systemd/system /etc/cron.d /var/spool/cron/crontabs \
  2>/dev/null || true)"
[ -z "$RUNTIME_REFS" ] || { printf '%s\n' "$RUNTIME_REFS"; echo 'RUNTIME_REFERENCE_FOUND'; exit 1; }

echo '=== TEST-S02-C BACKUP VERIFICATION ==='
[ -f "$BACKUP_DIR/test-config-and-pool.tgz" ] || { echo 'BACKUP_ARCHIVE_ABSENT'; exit 1; }
ACTUAL_SHA="$(sha256sum "$BACKUP_DIR/test-config-and-pool.tgz" | awk '{print $1}')"
printf 'expected=%s\nactual=%s\n' "$EXPECTED_SHA" "$ACTUAL_SHA"
[ "$ACTUAL_SHA" = "$EXPECTED_SHA" ] || { echo 'BACKUP_SHA_MISMATCH'; exit 1; }

nginx -t
systemctl is-active nginx
systemctl is-active php7.4-fpm
systemctl is-active php8.3-fpm

echo '=== TEST-S02-C MOVE LEGACY ROOT ==='
printf 'current=%s\nlegacy=%s\n' "$CURRENT_ROOT" "$LEGACY_ROOT"
mv -- "$CURRENT_ROOT" "$LEGACY_ROOT"
MOVED=1

if [ -f "$LEGACY_ROOT/.env" ]; then
    chown admin:admin "$LEGACY_ROOT/.env"
    chmod 600 "$LEGACY_ROOT/.env"
fi

install -d -m 0751 -o admin -g www-data "$CURRENT_ROOT"
install -d -m 0755 -o admin -g admin "$CURRENT_ROOT/public"
printf 'stage0 target placeholder; domain must remain suspended\n' \
  > "$CURRENT_ROOT/.stage0-target-placeholder"
chown admin:admin "$CURRENT_ROOT/.stage0-target-placeholder"
chmod 600 "$CURRENT_ROOT/.stage0-target-placeholder"

[ ! -e "$CURRENT_ROOT/.env" ] || { echo 'NEW_ENV_MUST_BE_ABSENT'; exit 1; }
[ ! -e "$CURRENT_ROOT/vendor" ] || { echo 'NEW_VENDOR_MUST_BE_ABSENT'; exit 1; }

echo '=== TEST-S02-C PHP 8.3 BACKEND ==='
BACKEND_CHANGED=1
"$HESTIA/v-change-web-domain-backend-tpl" admin "$DOMAIN" PHP-8_3 yes

[ -f "$PHP83_POOL" ] || { echo 'PHP83_TEST_POOL_ABSENT'; exit 1; }
grep -Eq "^[[:space:]]*listen[[:space:]]*=[[:space:]]*$PHP83_SOCKET[[:space:]]*$" "$PHP83_POOL" \
  || { echo 'PHP83_SOCKET_MAPPING_MISMATCH'; exit 1; }
systemctl is-active php8.3-fpm
[ -S "$PHP83_SOCKET" ] || { echo 'PHP83_SOCKET_ABSENT'; exit 1; }
nginx -t

FINAL_STATE="$("$HESTIA/v-list-web-domain" admin "$DOMAIN")"
printf '%s\n' "$FINAL_STATE" | grep -Eq '^SUSPENDED:[[:space:]]+yes$' \
  || { echo 'DOMAIN_UNEXPECTEDLY_ACTIVE'; exit 1; }
printf '%s\n' "$FINAL_STATE" | grep -Eq '^BACKEND:[[:space:]]+PHP-8_3$' \
  || { echo 'BACKEND_STATE_MISMATCH'; exit 1; }

grep -Eq '^[[:space:]]*root[[:space:]]+/usr/local/hestia/data/templates/web/suspend;' \
  "$CONF/nginx.conf" "$CONF/nginx.ssl.conf" \
  || { echo 'FINAL_SUSPEND_ROOT_MISMATCH'; exit 1; }

trap - ERR

echo '=== TEST-S02-C FINAL EVIDENCE ==='
printf '%s\n' "$FINAL_STATE" \
  | grep -E '^(DOMAIN|DOCUMENT_ROOT|TEMPLATE|BACKEND|SUSPENDED):'
stat -c '%A | %U:%G | %s | %n' \
  "$CURRENT_ROOT" "$CURRENT_ROOT/public" \
  "$CURRENT_ROOT/.stage0-target-placeholder" "$LEGACY_ROOT"
du -sh "$LEGACY_ROOT"
grep -nE '^[[:space:]]*(listen|user|group|pm|pm\.max_children|pm\.max_requests)[[:space:]]*=' "$PHP83_POOL"
ss -lx | grep -F "$PHP83_SOCKET"
echo "LEGACY_ROLLBACK_ROOT=$LEGACY_ROOT"
echo 'NEXT=DO_NOT_UNSUSPEND_OR_UPLOAD_ENV; send full output for TEST-S02-D preparation'
```

## 3. Acceptance TEST-S02-C

- backup SHA совпадает с TEST-S02-B;
- test остаётся `SUSPENDED: yes`;
- Hestia backend test равен `PHP-8_3`;
- dedicated PHP 8.3 pool и socket существуют;
- старый 1.3 GiB tree сохранён целиком под printed `LEGACY_ROLLBACK_ROOT`;
- новый `public_html` содержит только пустой `public/` и placeholder;
- новый `.env`, vendor, DB/Redis/provider connections отсутствуют;
- `nginx -t` PASS;
- production root, pool, DB, Redis и providers не изменялись.

После PASS будет подготовлен TEST-S02-D: способ загрузки immutable Laravel 13 target artifact, project-local Composer 2.9.5, новый APP_KEY, отдельные cookie/cache/Redis namespaces, disabled/sandbox providers и health check. До этого домен не unsuspend и старый `.env` не копировать.
