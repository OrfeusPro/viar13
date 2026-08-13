# 57. Filament Stage 0 — S0-02 server preflight result

Дата evidence: 17.07.2026 11:45 UTC. Статус: **PHP platform PASS; dev vhost/isolation BLOCKED; S0-02 application installation NOT STARTED**.

## 1. Итог

Server runtime уже пригоден для Laravel 13 / Filament 5 на PHP 8.3. Устанавливать PHP 8.4 или выполнять `apt upgrade` сейчас не требуется.

Подтверждено:

- Ubuntu 24.04.4 LTS, x86-64, Hetzner KVM;
- PHP 8.3.30 CLI и FPM из одного package build;
- `php8.3-fpm` active/enabled;
- обязательные extensions присутствуют, включая `intl`, `pdo_mysql`, `gd`, `imagick`, `mbstring`, XML и ZIP;
- CLI/FPM имеют по 41 conf.d links; FPM: `intl enabled`, OPcache enabled, timezone UTC;
- существует dedicated pool `/etc/php/8.3/fpm/pool.d/dev.viarcanvas.com.conf` и socket `/run/php/php8.3-fpm-dev.viarcanvas.com.sock`;
- pool работает от `admin:admin`, `ondemand`, `max_children=8`, `max_requests=4000`.

Не готово:

- Hestia domain `dev.viarcanvas.com` имеет `SUSPENDED=yes`;
- Nginx root обоих dev server blocks — `/usr/local/hestia/data/templates/web/suspend`;
- suspended Nginx config не содержит mapping на dedicated PHP 8.3 socket;
- staging `.env`, `storage` и storage symlink отсутствуют;
- DB/Redis/session/provider isolation ещё невозможно проверить;
- HTTPS check через `127.0.0.1` не доказателен: Nginx может слушать только assigned public IP `65.108.243.50`.

## 2. Решение по PHP

Использовать для Stage 0 уже установленный PHP 8.3.30. Он удовлетворяет minimum Laravel 13 и exact S0-01 solver platform. PHP 8.4.23 доступен в repository, но его установка сейчас добавит ненужное изменение production-host.

Candidate PHP 8.3.32 также не устанавливается автоматически: upgrade всех связанных CLI/FPM/module packages требует отдельного maintenance window и regression check. Stage 0 provisioning сначала выполняется на согласованном 8.3.30.

Локальный Windows gap `ext-intl` не переносится на server: server CLI/FPM `intl` gate закрыт.

## 3. Evidence status S02-E01…E17

| Group | Result |
|---|---|
| E01 OS/host | PASS |
| E02 PHP CLI | PASS |
| E03 PHP FPM/pool | PASS |
| E04 extensions | PASS |
| E05 packages | PASS |
| E06 Hestia domain | BLOCKED: suspended |
| E07 Nginx root | BLOCKED: suspend template |
| E08 socket mapping | PARTIAL: pool/socket exist, vhost mapping absent while suspended |
| E09 ownership | PARTIAL: root ownership known, runtime directories absent |
| E10 storage | BLOCKED: absent |
| E11 HTTPS | INCONCLUSIVE: retry against assigned IP required |
| E12 APP isolation | BLOCKED: staging `.env` absent |
| E13 DB isolation | BLOCKED: staging `.env`/DB absent |
| E14 Redis isolation | BLOCKED: staging config absent |
| E15 providers | SAFE BY ABSENCE, final configuration pending |
| E16 no-write/no-secret preflight | PASS |
| E17 public DNS | BLOCKED: NXDOMAIN; authoritative owner Cloudflare |

Exact row status находится в `appendix-stage0-s02-preflight-evidence-request.csv`.

## 4. Следующий read-only block S02-D

До `v-unsuspend-web-domain` нужно подтвердить DNS/listeners, повторить HTTPS через assigned IP, посмотреть доступные Hestia templates и убедиться, что public root не содержит нужного live application.

```bash
STAGE_DOMAIN='dev.viarcanvas.com'
STAGE_IP='65.108.243.50'
STAGE_ROOT="/home/admin/web/${STAGE_DOMAIN}/public_html"

echo '=== S02-D DNS AND LISTENERS ==='
getent ahostsv4 "$STAGE_DOMAIN" | sort -u
ip -brief address
ss -ltnp | grep -E '(:80|:443)[[:space:]]' || echo 'NO_HTTP_HTTPS_LISTENER_VISIBLE'

echo '=== S02-D HTTPS AGAINST ASSIGNED IP ==='
curl -ksSIL --max-redirs 3 \
  --resolve "${STAGE_DOMAIN}:443:${STAGE_IP}" \
  "https://${STAGE_DOMAIN}/" \
  | grep -Ei '^(HTTP/|location:|server:|content-type:|strict-transport-security:|set-cookie:)' \
  | sed -E 's/^(set-cookie:[[:space:]]*[^=]+=)[^;]*/\1<redacted>/I' \
  || echo 'DEV_ASSIGNED_IP_HTTPS_CHECK_FAILED'

echo '=== S02-D HESTIA COMMANDS AND TEMPLATES ==='
for command in \
  v-unsuspend-web-domain \
  v-suspend-web-domain \
  v-change-web-domain-tpl \
  v-change-web-domain-backend-tpl \
  v-change-web-domain-docroot \
  v-list-web-templates
do
    path="/usr/local/hestia/bin/${command}"
    if [ -x "$path" ]; then
        echo "$command=AVAILABLE"
    else
        echo "$command=ABSENT"
    fi
done

/usr/local/hestia/bin/v-list-web-templates json 2>/dev/null \
  | sed -n '1,240p' \
  || /usr/local/hestia/bin/v-list-web-templates 2>/dev/null \
  || echo 'WEB_TEMPLATE_LIST_FAILED'

echo '=== S02-D DEV ROOT CONTENTS ==='
if [ -d "$STAGE_ROOT" ]; then
    find "$STAGE_ROOT" -maxdepth 2 -mindepth 1 \
      -printf '%y | %m | %u:%g | %s | %P\n' \
      | sort \
      | head -n 240
else
    echo 'DEV_ROOT_ABSENT'
fi

echo '=== S02-D BACKUP CAPACITY ==='
df -hPT / /home /root 2>/dev/null
du -sh "/home/admin/conf/web/${STAGE_DOMAIN}" "$STAGE_ROOT" 2>/dev/null
```

S02-D read-only: он не снимает suspend и не перезапускает Nginx/PHP.

Фактический результат S02-D получен: assigned-IP HTTPS HTTP/2 200, Nginx слушает только public IP, `laravel` template доступен, dev root пуст, available disk 18 GiB. Public DNS оказался NXDOMAIN. Финальная certificate/backend-template проверка перенесена в S02-E: `58-filament-stage0-s02-d-result-and-e.md`.

## 5. Условия перед активацией dev domain

После анализа S02-D mutation block должен:

1. создать root-only archive текущих Hestia dev configs и PHP pool;
2. сохранить Hestia domain state до изменения;
3. выполнить только `v-unsuspend-web-domain admin dev.viarcanvas.com yes`;
4. проверить `nginx -t` и фактические `root`/`fastcgi_pass`;
5. при ошибке немедленно вернуть `v-suspend-web-domain admin dev.viarcanvas.com yes`;
6. не создавать `.env`, DB, Redis namespace, workers или provider credentials в том же шаге;
7. не менять `viarcanvas.com` и не выполнять общий PHP upgrade.

Официальная Hestia CLI reference подтверждает отдельные команды `v-unsuspend-web-domain`, `v-suspend-web-domain`, `v-change-web-domain-tpl`, `v-change-web-domain-backend-tpl` и `v-change-web-domain-docroot`: <https://hestiacp.com/docs/reference/cli>.

## 6. Текущий GO/NO-GO

- GO: выполнить S02-D.
- CONDITIONAL GO после review S02-D: backup + unsuspend только dev web domain.
- NO-GO: Laravel/Filament install, `.env`, database import, Redis/queue workers, provider connections и public asset build.
