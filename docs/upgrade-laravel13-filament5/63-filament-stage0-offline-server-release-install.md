# 63. Filament Stage 0 — offline server release install

Дата: 17.07.2026. Статус: **command pack подготовлен; upload/install ещё не выполнялись; active test не переключается**.

## 1. Локальный upload

Artifact:

`C:\OSPanel\domains\asoft\viar-next-artifacts\viar-next-stage0-20260717T123427Z.tar.gz`

SHA-256:

`5f279d54e07d4d28e61340ddfce38997e67c92e456d4967a49ccf4c51cd6600b`

Если используется PowerShell + OpenSSH, выполнить локально:

```powershell
ssh root@65.108.243.50 "install -d -m 700 /root/viar-stage0-uploads"

scp "C:\OSPanel\domains\asoft\viar-next-artifacts\viar-next-stage0-20260717T123427Z.tar.gz" `
  root@65.108.243.50:/root/viar-stage0-uploads/

scp "C:\OSPanel\domains\asoft\viar-next-artifacts\viar-next-stage0-20260717T123427Z.tar.gz.sha256" `
  root@65.108.243.50:/root/viar-stage0-uploads/
```

Можно загрузить эти два файла другим SFTP-клиентом в тот же каталог. Не распаковывать в active `public_html`.

## 2. TEST-S02-D — offline extract и Composer install

После upload выполнить весь блок от `root` на server:

```bash
set -euo pipefail

DOMAIN='test.viarcanvas.com'
HESTIA='/usr/local/hestia/bin'
ARTIFACT='/root/viar-stage0-uploads/viar-next-stage0-20260717T123427Z.tar.gz'
EXPECTED_SHA='5f279d54e07d4d28e61340ddfce38997e67c92e456d4967a49ccf4c51cd6600b'
RELEASE_BASE='/home/admin/web/test.viarcanvas.com/releases'
RELEASE='/home/admin/web/test.viarcanvas.com/releases/viar-next-stage0-20260717T123427Z'
EXPECTED_COMPOSER_LOCK='054fc367b324891b1c60863743af02b82754ceb8e1e25cf618bd072b84fe1b71'
EXPECTED_COMPOSER_PHAR='c86ce603fe836bf0861a38c93ac566c8f1e69ac44b2445d9b7a6a17ea2e9972a'

echo '=== TEST-S02-D ACTIVE TEST GUARD ==='
STATE="$("$HESTIA/v-list-web-domain" admin "$DOMAIN")"
printf '%s\n' "$STATE" | grep -E '^(DOMAIN|DOCUMENT_ROOT|TEMPLATE|BACKEND|SUSPENDED):'
printf '%s\n' "$STATE" | grep -Eq '^BACKEND:[[:space:]]+PHP-7_4$' \
  || { echo 'ACTIVE_TEST_BACKEND_CHANGED'; exit 1; }
printf '%s\n' "$STATE" | grep -Eq '^SUSPENDED:[[:space:]]+no$' \
  || { echo 'ACTIVE_TEST_NOT_ACTIVE'; exit 1; }

for file in \
  "/home/admin/conf/web/${DOMAIN}/nginx.conf" \
  "/home/admin/conf/web/${DOMAIN}/nginx.ssl.conf"
do
  grep -Eq '^[[:space:]]*root[[:space:]]+/home/admin/web/test\.viarcanvas\.com/public_html/public;' "$file" \
    || { echo "ACTIVE_TEST_ROOT_CHANGED=$file"; exit 1; }
done

nginx -t

echo '=== TEST-S02-D ARTIFACT GUARDS ==='
[ -f "$ARTIFACT" ] || { echo 'ARTIFACT_ABSENT'; exit 1; }
ACTUAL_SHA="$(sha256sum "$ARTIFACT" | awk '{print $1}')"
printf 'expected=%s\nactual=%s\n' "$EXPECTED_SHA" "$ACTUAL_SHA"
[ "$ACTUAL_SHA" = "$EXPECTED_SHA" ] || { echo 'ARTIFACT_SHA_MISMATCH'; exit 1; }

UNSAFE_ENTRIES="$(tar -tzf "$ARTIFACT" \
  | grep -E '(^/|(^|/)\.\.(/|$))' || true)"
[ -z "$UNSAFE_ENTRIES" ] \
  || { printf '%s\n' "$UNSAFE_ENTRIES"; echo 'UNSAFE_ARCHIVE_PATH'; exit 1; }

[ "$(realpath -m "$RELEASE_BASE")" = "$RELEASE_BASE" ] \
  || { echo 'RELEASE_BASE_PATH_GUARD_FAILED'; exit 1; }
[ "$(realpath -m "$RELEASE")" = "$RELEASE" ] \
  || { echo 'RELEASE_PATH_GUARD_FAILED'; exit 1; }
case "$RELEASE" in
  "$RELEASE_BASE"/*) ;;
  *) echo 'RELEASE_ESCAPES_BASE'; exit 1 ;;
esac
[ ! -e "$RELEASE" ] || { echo 'RELEASE_ALREADY_EXISTS'; exit 1; }

echo '=== TEST-S02-D PHP 8.3 CLI ==='
PHP83="$(command -v php8.3)"
[ -n "$PHP83" ] || { echo 'PHP83_CLI_ABSENT'; exit 1; }
"$PHP83" -r 'if (PHP_VERSION_ID < 80300) { exit(1); } echo PHP_VERSION, PHP_EOL;'
"$PHP83" -m | grep -Fx intl >/dev/null || { echo 'PHP83_EXT_INTL_ABSENT'; exit 1; }

echo '=== TEST-S02-D EXTRACT OFFLINE RELEASE ==='
install -d -m 0750 -o admin -g admin "$RELEASE_BASE"
install -d -m 0750 -o admin -g admin "$RELEASE"
tar -xzf "$ARTIFACT" -C "$RELEASE"
chown -R admin:admin "$RELEASE"

[ ! -e "$RELEASE/.env" ] || { echo 'ARTIFACT_ENV_PRESENT'; exit 1; }
[ ! -e "$RELEASE/vendor" ] || { echo 'ARTIFACT_VENDOR_PRESENT'; exit 1; }
[ -f "$RELEASE/BUILD-MANIFEST.json" ] || { echo 'BUILD_MANIFEST_ABSENT'; exit 1; }
[ -f "$RELEASE/.env.stage0.example" ] || { echo 'SAFE_ENV_TEMPLATE_ABSENT'; exit 1; }

"$PHP83" -r '
    $path = $argv[1];
    $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (($data["build_id"] ?? null) !== "viar-next-stage0-20260717T123427Z") {
        fwrite(STDERR, "BUILD_ID_MISMATCH\n");
        exit(1);
    }
    echo "BUILD_MANIFEST=PASS\n";
' "$RELEASE/BUILD-MANIFEST.json"

LOCK_SHA="$(sha256sum "$RELEASE/composer.lock" | awk '{print $1}')"
PHAR_SHA="$(sha256sum "$RELEASE/tools/composer-2.9.5.phar" | awk '{print $1}')"
[ "$LOCK_SHA" = "$EXPECTED_COMPOSER_LOCK" ] || { echo 'COMPOSER_LOCK_SHA_MISMATCH'; exit 1; }
[ "$PHAR_SHA" = "$EXPECTED_COMPOSER_PHAR" ] || { echo 'COMPOSER_PHAR_SHA_MISMATCH'; exit 1; }

echo '=== TEST-S02-D OFFLINE COMPOSER INSTALL ==='
sudo -u admin "$PHP83" "$RELEASE/tools/composer-2.9.5.phar" \
  --working-dir="$RELEASE" validate --strict --no-interaction

sudo -u admin "$PHP83" "$RELEASE/tools/composer-2.9.5.phar" \
  --working-dir="$RELEASE" install \
  --prefer-dist --no-interaction --no-scripts

sudo -u admin "$PHP83" "$RELEASE/tools/composer-2.9.5.phar" \
  --working-dir="$RELEASE" check-platform-reqs --lock --no-interaction

sudo -u admin "$PHP83" "$RELEASE/tools/composer-2.9.5.phar" \
  --working-dir="$RELEASE" audit --locked --no-interaction

[ ! -e "$RELEASE/.env" ] || { echo 'ENV_CREATED_UNEXPECTEDLY'; exit 1; }

find "$RELEASE/storage" "$RELEASE/bootstrap/cache" -type d -exec chmod 0775 {} +
find "$RELEASE/storage" "$RELEASE/bootstrap/cache" -type f -exec chmod 0664 {} +
chown -R admin:admin "$RELEASE/storage" "$RELEASE/bootstrap/cache"

echo '=== TEST-S02-D RELEASE RESULT ==='
du -sh "$RELEASE"
stat -c '%A | %U:%G | %s | %n' \
  "$RELEASE" "$RELEASE/composer.lock" \
  "$RELEASE/vendor/autoload.php" "$RELEASE/.env.stage0.example"

echo '=== TEST-S02-D ACTIVE TEST STILL UNCHANGED ==='
FINAL_STATE="$("$HESTIA/v-list-web-domain" admin "$DOMAIN")"
printf '%s\n' "$FINAL_STATE" | grep -E '^(DOMAIN|DOCUMENT_ROOT|TEMPLATE|BACKEND|SUSPENDED):'
printf '%s\n' "$FINAL_STATE" | grep -Eq '^BACKEND:[[:space:]]+PHP-7_4$' \
  || { echo 'FINAL_ACTIVE_TEST_BACKEND_CHANGED'; exit 1; }
printf '%s\n' "$FINAL_STATE" | grep -Eq '^SUSPENDED:[[:space:]]+no$' \
  || { echo 'FINAL_ACTIVE_TEST_NOT_ACTIVE'; exit 1; }
nginx -t

curl -ksSIL --max-redirs 2 \
  --resolve "${DOMAIN}:443:65.108.243.50" \
  "https://${DOMAIN}/" \
  | grep -Ei '^(HTTP/|server:|content-type:|location:)' || true

echo "OFFLINE_RELEASE=$RELEASE"
echo 'TEST-S02-D=PASS_IF_ALL_GUARDS_ABOVE_PASSED'
echo 'NEXT=DO_NOT_CREATE_ENV_OR_SWITCH_BACKEND_ROOT; send full output'
```

## 3. Результат этого шага

После PASS новый release существует рядом с test, но полностью non-public:

- active test продолжает работать из старого `public_html` на PHP 7.4;
- PHP 8.3 использован только как CLI для dependency/platform checks;
- release не имеет `.env` и не может подключиться к DB/providers;
- Hestia template/backend/root не меняются;
- никакие миграции и Artisan package scripts не выполняются;
- при ошибке release остаётся offline для анализа, active test продолжает работать.

Следующий gate после review: создать отдельную sanitized staging DB/user, сформировать новый `.env`, выполнить package discovery/tests через PHP 8.3 без HTTP exposure. Только затем обсуждается isolated FPM preview и позже atomic switch.
