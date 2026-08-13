# 60. Filament Stage 0 — test preflight result и quarantine

Дата evidence: 17.07.2026. Статус: **TEST-S02-B PASS; текущий test успешно восстановлен и остаётся active до готовности нового target**.

## 0. Execution result TEST-S02-B

- Выполнено: `2026-07-17T12:08:10Z`.
- Backup: `/root/viar-stage0-backups/test-quarantine-20260717T120810Z`.
- Archive SHA-256: `ebb54bccce82c3856b6ef8058ae1f5c26f52b3517850f0a3498cdcea726f888c`.
- Backup directory `0700`, artifacts `0600`, owner `root:root`.
- Hestia state: `SUSPENDED: yes`; Nginx HTTP/HTTPS roots указывают на suspend template.
- `nginx -t`, Nginx, PHP 7.4 FPM и PHP 8.3 FPM: PASS/active.
- HTTP/2 200 после suspend — штатная Hestia suspend page, не legacy Laravel app.
- OCSP/`ssl_stapling` warnings не блокируют этап: config syntax/load PASS. Они остаются отдельным TLS maintenance item и не требуют изменения production в рамках staging preparation.

TEST-S02-C из документа 61 поставлен на hold и сейчас не выполняется. Текущий test возвращается в active state; replacement будет выполняться только после готовности и offline-проверки нового target artifact.

### Restore result

- Hestia: `SUSPENDED: no`.
- Backend: `PHP-7_4`.
- Template: `laravel`.
- Document root contract: `/home/admin/web/test.viarcanvas.com/public_html/` → Nginx Laravel public root.
- `nginx -t`: PASS.
- Старый application tree не перемещался; PHP 8.3 backend не назначался; production не изменялся.
- OCSP/`ssl_stapling` warnings повторились, но являются non-blocking: Nginx syntax/load PASS.

## 1. Подтверждённый test baseline

- Domain active, HTTPS, Hestia web template `laravel`.
- Nginx root: `/home/admin/web/test.viarcanvas.com/public_html/public`.
- Current backend: `PHP-7_4`, dedicated socket `/run/php/php7.4-fpm-test.viarcanvas.com.sock`.
- Target backend template `PHP-8_3.tpl` существует; PHP 8.3 test pool ещё не создан.
- Global Composer 2.3.5 запускается на PHP 8.3 с deprecation warnings. Его не обновлять глобально; для target нужен project-local Composer 2.9.5.
- Current test tree: 1.3 GiB; filesystem available 18 GiB.
- Storage link остаётся внутри test tree, production storage не используется.
- Worker/cron/systemd references на test root не найдены.
- Test публично получает traffic: Nginx access log 25 162 lines и обновлялся во время evidence; Laravel log также не пуст.

## 2. Isolation result

Положительное:

- DB name/user/password отличаются от production;
- current cache=file, queue=sync;
- session domain отличается;
- Synvolve endpoints отличаются;
- storage path отдельный.

Hard failures текущего `.env`:

- `APP_ENV=production`;
- `APP_KEY=SAME`;
- `.env` permissions `664`;
- `PAYPAL_SANDBOX=false`;
- production/test SMTP credentials SAME;
- PayPal live и sandbox credentials SAME;
- Google OAuth credentials SAME;
- Redis DB/prefix/cache vars отсутствуют и не образуют явную isolation boundary;
- session cookie на test не задан явно.

Вывод: старый `.env` нельзя копировать или использовать как основу target. Target получает новый файл с `APP_ENV=staging`, new APP_KEY/cookie, new sanitized DB user, explicit cache/Redis namespace и providers disabled/sandbox/log-only.

## 3. Решение по текущему test

Пользователь разрешил полную замену. Поэтому full 1.3 GiB archive не обязателен. До удаления/перемещения сохраняются:

- Hestia/Nginx domain configs;
- current PHP 7.4 pool config;
- Hestia domain state;
- filesystem metadata;
- root-only backup checksum.

Сам current tree пока не удаляется: на quarantine step он остаётся на месте, а domain переводится в suspended state. Это немедленно исключает HTTP-triggered PayPal/mail/OAuth/Synvolve calls и оставляет простой rollback через unsuspend.

## 4. TEST-S02-B — config backup и suspend only

Выполнить от `root`. Блок не меняет production, PHP versions, DB, Redis и содержимое test application.

```bash
set -euo pipefail

DOMAIN='test.viarcanvas.com'
CONF='/home/admin/conf/web/test.viarcanvas.com'
ROOT='/home/admin/web/test.viarcanvas.com/public_html'
POOL='/etc/php/7.4/fpm/pool.d/test.viarcanvas.com.conf'
HESTIA='/usr/local/hestia/bin'
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"
BACKUP_DIR="/root/viar-stage0-backups/test-quarantine-${STAMP}"

echo '=== TEST-S02-B PATH GUARDS ==='
[ "$(readlink -f "$CONF")" = "$CONF" ] || { echo 'CONF_PATH_GUARD_FAILED'; exit 1; }
[ "$(readlink -f "$ROOT")" = "$ROOT" ] || { echo 'ROOT_PATH_GUARD_FAILED'; exit 1; }
[ "$(readlink -f "$POOL")" = "$POOL" ] || { echo 'POOL_PATH_GUARD_FAILED'; exit 1; }
[ -x "$HESTIA/v-suspend-web-domain" ] || { echo 'SUSPEND_COMMAND_MISSING'; exit 1; }
[ -x "$HESTIA/v-unsuspend-web-domain" ] || { echo 'UNSUSPEND_COMMAND_MISSING'; exit 1; }

printf 'conf=%s\nroot=%s\npool=%s\nbackup=%s\n' "$CONF" "$ROOT" "$POOL" "$BACKUP_DIR"

echo '=== TEST-S02-B PRECHECK ==='
nginx -t || exit 1
systemctl is-active nginx
systemctl is-active php7.4-fpm
systemctl is-active php8.3-fpm

umask 077
install -d -m 700 "$BACKUP_DIR"

"$HESTIA/v-list-web-domain" admin "$DOMAIN" > "$BACKUP_DIR/domain-before.txt"
stat -c '%A | %U:%G | %s | %y | %n' "$ROOT" "$ROOT/.env" "$ROOT/storage" \
  > "$BACKUP_DIR/filesystem-before.txt" 2>&1 || true

tar -C / -czf "$BACKUP_DIR/test-config-and-pool.tgz" \
  "${CONF#/}" \
  "${POOL#/}"

sha256sum "$BACKUP_DIR/test-config-and-pool.tgz" \
  > "$BACKUP_DIR/SHA256SUMS"

chmod 600 "$BACKUP_DIR"/*

echo '=== TEST-S02-B BACKUP RESULT ==='
ls -la "$BACKUP_DIR"
cat "$BACKUP_DIR/SHA256SUMS"

echo '=== TEST-S02-B SUSPEND ==='
"$HESTIA/v-suspend-web-domain" admin "$DOMAIN" yes

if ! nginx -t; then
    echo 'POST_SUSPEND_NGINX_FAILED_ROLLING_BACK'
    "$HESTIA/v-unsuspend-web-domain" admin "$DOMAIN" yes || true
    nginx -t || true
    exit 1
fi

"$HESTIA/v-list-web-domain" admin "$DOMAIN" > "$BACKUP_DIR/domain-after.txt"
chmod 600 "$BACKUP_DIR/domain-after.txt"

echo '=== TEST-S02-B FINAL STATE ==='
grep -E '^(DOMAIN|DOCUMENT_ROOT|TEMPLATE|BACKEND|SUSPENDED):' "$BACKUP_DIR/domain-after.txt" || true
grep -nEi 'server_name|^[[:space:]]*root |fastcgi_pass' \
  "/home/admin/conf/web/${DOMAIN}/nginx.conf" \
  "/home/admin/conf/web/${DOMAIN}/nginx.ssl.conf" || true

curl -ksSIL --max-redirs 1 \
  --resolve "${DOMAIN}:443:65.108.243.50" \
  "https://${DOMAIN}/" \
  | grep -Ei '^(HTTP/|server:|content-type:|location:)' || true

echo "ROLLBACK_COMMAND=$HESTIA/v-unsuspend-web-domain admin $DOMAIN yes"
```

## 5. Acceptance TEST-S02-B

- backup directory root-only, archive checksum записан;
- `SUSPENDED: yes`;
- Nginx config root переключился на Hestia suspend template;
- `nginx -t` PASS;
- production domain/config/pools не менялись;
- current test root и `.env` физически остаются на месте.

После PASS будет подготовлен TEST-S02-C: same-filesystem move current root в rollback name, создание чистого root/release tree, Hestia backend switch `PHP-7_4 → PHP-8_3`, project-local Composer и fresh Laravel 13 skeleton. До TEST-S02-C ничего не удалять и не загружать.

## 6. Решение после TEST-S02-B: временно вернуть текущий test

Пользователь подтвердил, что текущий test должен работать до готовности Laravel 13 / Filament 5 target. Поэтому TEST-S02-C не выполнять. Для восстановления выполнить от `root`:

```bash
set -euo pipefail

DOMAIN='test.viarcanvas.com'
HESTIA='/usr/local/hestia/bin'

nginx -t
"$HESTIA/v-unsuspend-web-domain" admin "$DOMAIN" yes

if ! nginx -t; then
    echo 'UNSUSPEND_NGINX_FAILED_ROLLING_BACK'
    "$HESTIA/v-suspend-web-domain" admin "$DOMAIN" yes || true
    nginx -t || true
    exit 1
fi

STATE="$("$HESTIA/v-list-web-domain" admin "$DOMAIN")"
printf '%s\n' "$STATE" \
  | grep -E '^(DOMAIN|DOCUMENT_ROOT|TEMPLATE|BACKEND|SUSPENDED):'

printf '%s\n' "$STATE" | grep -Eq '^BACKEND:[[:space:]]+PHP-7_4$' \
  || { echo 'UNEXPECTED_BACKEND'; exit 1; }
printf '%s\n' "$STATE" | grep -Eq '^SUSPENDED:[[:space:]]+no$' \
  || { echo 'DOMAIN_STILL_SUSPENDED'; exit 1; }

grep -nEi 'server_name|^[[:space:]]*root |fastcgi_pass' \
  "/home/admin/conf/web/${DOMAIN}/nginx.conf" \
  "/home/admin/conf/web/${DOMAIN}/nginx.ssl.conf"

curl -ksSIL --max-redirs 2 \
  --resolve "${DOMAIN}:443:65.108.243.50" \
  "https://${DOMAIN}/" \
  | grep -Ei '^(HTTP/|server:|content-type:|location:)' || true

echo "EMERGENCY_RESUSPEND=$HESTIA/v-suspend-web-domain admin $DOMAIN yes"
```

Команда выполнена PASS: `BACKEND: PHP-7_4`, `SUSPENDED: no`, Hestia Laravel document root восстановлен. Сохранённый backup не удалять. Повторный unsuspend не требуется.
