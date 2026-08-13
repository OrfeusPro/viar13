# 58. Filament Stage 0 — S02-D result и final pre-activation check S02-E

Дата: 17.07.2026. Статус: **listener/template/capacity PASS; public DNS BLOCKED; certificate/backend-template pending; mutation не выполнялась**.

Execution update: пользователь выбрал существующий `test.viarcanvas.com` как disposable staging и разрешил полностью перезалить его из локального target. Dev S02-E приостановлен; dev остаётся fallback.

## 1. Результат S02-D

Подтверждено:

- Nginx слушает `65.108.243.50:80` и `65.108.243.50:443`, но не `127.0.0.1`; прежний loopback failure не является дефектом Nginx;
- принудительный SNI/Host request через `--resolve dev.viarcanvas.com:443:65.108.243.50` возвращает HTTP/2 200 от Nginx;
- Hestia CLI содержит `v-unsuspend-web-domain`, `v-suspend-web-domain`, template/backend/docroot commands;
- web template `laravel` доступен;
- `/home/admin/web/dev.viarcanvas.com/public_html` пуст;
- dev config занимает 40 KiB, dev root 4 KiB;
- filesystem `/` имеет 18 GiB available, usage 75%.

Вывод: backup + unsuspend технически возможны, а rollback может использовать отдельную официальную Hestia suspend-команду. Но до mutation нужно закрыть два неизвестных: точное backend-template имя PHP 8.3 и состояние SSL certificate.

## 2. Дополнительный DNS result

Независимая внешняя проверка 17.07.2026:

- `dev.viarcanvas.com` A/AAAA: NXDOMAIN;
- authoritative nameservers `irena.ns.cloudflare.com` и `konnor.ns.cloudflare.com`;
- root/www обслуживаются Cloudflare addresses.

Следовательно, Hestia не является authoritative DNS owner для public record. Нельзя считать domain доступным клиенту только потому, что локальный `--resolve` работает.

Public DNS пока не добавляется: сначала dev domain должен быть активирован, закрыт Basic Auth и проверен по assigned IP. После этого владелец Cloudflare создаёт `A dev → 65.108.243.50`, сначала DNS-only для origin/certificate diagnostics.

## 3. Последний read-only block S02-E

```bash
STAGE_DOMAIN='dev.viarcanvas.com'
STAGE_IP='65.108.243.50'

echo '=== S02-E AUTHORITATIVE DNS ==='
if command -v dig >/dev/null 2>&1; then
    dig NS viarcanvas.com +short
    dig @1.1.1.1 "$STAGE_DOMAIN" A +noall +comments +answer
    dig @8.8.8.8 "$STAGE_DOMAIN" A +noall +comments +answer
else
    getent ahostsv4 "$STAGE_DOMAIN" || echo 'DEV_PUBLIC_DNS_NOT_RESOLVED'
fi

echo '=== S02-E ORIGIN CERTIFICATE ==='
timeout 15 openssl s_client \
  -connect "${STAGE_IP}:443" \
  -servername "$STAGE_DOMAIN" \
  </dev/null 2>/dev/null \
  | openssl x509 -noout -subject -issuer -serial -dates -ext subjectAltName \
  || echo 'ORIGIN_CERTIFICATE_READ_FAILED'

echo '=== S02-E DOMAIN BACKENDS ==='
/usr/local/hestia/bin/v-list-web-domain admin viarcanvas.com 2>/dev/null \
  | grep -E '^(DOMAIN|DOCUMENT_ROOT|TEMPLATE|BACKEND|SUSPENDED):' || true

/usr/local/hestia/bin/v-list-web-domain admin "$STAGE_DOMAIN" 2>/dev/null \
  | grep -E '^(DOMAIN|DOCUMENT_ROOT|TEMPLATE|BACKEND|SUSPENDED):' || true

echo '=== S02-E BACKEND TEMPLATE FILES ==='
FOUND=0
for dir in \
  /usr/local/hestia/data/templates/web/php-fpm \
  /usr/local/hestia/data/templates/web/nginx/php-fpm \
  /usr/local/hestia/data/templates/web/apache2/php-fpm
do
    if [ -d "$dir" ]; then
        FOUND=1
        echo "-- $dir --"
        find "$dir" -maxdepth 1 -type f -printf '%f\n' | sort
    fi
done
[ "$FOUND" -eq 1 ] || echo 'KNOWN_BACKEND_TEMPLATE_DIRECTORIES_NOT_FOUND'

echo '=== S02-E LARAVEL TEMPLATE DIRECTIVES ==='
find /usr/local/hestia/data/templates/web \
  -maxdepth 5 \
  -type f \
  \( -iname 'laravel.tpl' -o -iname 'laravel.stpl' -o -iname 'laravel.sh' \) \
  -print \
  -exec grep -nEi '^[[:space:]]*root |fastcgi_pass|proxy_pass|public_html/public|%backend_lsnr%' {} \; \
  2>/dev/null

echo '=== S02-E HESTIA DNS VIEW ==='
if [ -x /usr/local/hestia/bin/v-list-dns-domain ]; then
    /usr/local/hestia/bin/v-list-dns-domain admin viarcanvas.com 2>/dev/null \
      | grep -E '^(DOMAIN|IP|TPL|SUSPENDED):' || true
fi

if [ -x /usr/local/hestia/bin/v-list-dns-records ]; then
    /usr/local/hestia/bin/v-list-dns-records admin viarcanvas.com 2>/dev/null \
      | grep -Ei '(^ID|[[:space:]]dev[[:space:]]|dev\.viarcanvas\.com)' || echo 'HESTIA_DEV_DNS_RECORD_NOT_FOUND'
fi
```

S02-E не добавляет DNS record, не снимает suspend и не читает secrets.

## 4. Решения после S02-E

| Evidence | Решение |
|---|---|
| Certificate valid и содержит `dev.viarcanvas.com` | допускается origin HTTPS после unsuspend |
| Certificate expired/missing SAN | после DNS publication выполнить отдельный LE renewal; не отключать TLS verification в target |
| Backend template `PHP-8_3`/эквивалент существует | использовать exact найденное имя через Hestia CLI |
| `laravel` template root содержит `public_html/public` | применить template до/вместе с controlled unsuspend |
| Public DNS NXDOMAIN | не публиковать до Basic Auth; Cloudflare owner action required позже |

## 5. Mutation boundary

После S02-E будет разрешён только один controlled block:

1. root-only backup dev Hestia configs + PHP pool + domain state;
2. Basic Auth до public DNS;
3. `laravel` web template и exact PHP 8.3 backend template;
4. unsuspend только `dev.viarcanvas.com`;
5. `nginx -t`, socket/root/HTTP checks через `--resolve`;
6. автоматический re-suspend при любом FAIL.

В этот block не входят Laravel install, `.env`, database, Redis, worker, scheduler, provider secrets и Cloudflare mutation.
