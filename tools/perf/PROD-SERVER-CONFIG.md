# kosmamed.ru / medmarket.su — настройки сервера (prod)

Схема: **nginx frontend** (155.212.171.103:443) → **Apache :81** (mod_fcgid PHP).

Эталон по опыту **vilmed.ru**; пути и сертификаты — **medmarket**.

## Файлы в репозитории

| Приоритет | Что | Файл |
|-----------|-----|------|
| 1 | **Frontend vhost** (всё в одном файле, без `include …performance.conf`) | `nginx-kosmamed-frontend.conf` |
| 2 | Gzip + rate limit + `map $webp_suffix` в **http { }** | `nginx-http-globals.conf` |
| 3 | Apache backend + KeepAlive + Fcgid timeout | `kosmamed-apache-backend.conf` |
| 4 | PHP prod | `kosmamed-php.ini` |
| — | ~~performance include~~ — **не нужен**, встроен во frontend | `nginx-kosmamed-performance.include.conf` (legacy) |
| 6 | Проверка после выката | `prod-config-check.sh` |
| 7 | Установка performance include | `nginx-apply-prod.sh` |

---

## Установка на сервере (рекомендуемый порядок)

### 1. HTTP globals (один раз)

```bash
scp tools/perf/nginx-http-globals.conf kosmamed:/etc/nginx/conf.d/kosmamed-performance-http.conf
ssh kosmamed 'nginx -t && systemctl reload nginx'
```

Сверить с текущим `nginx.conf`: не дублировать `gzip on`, если уже есть.

### 2. Frontend vhost

Скопировать **целиком** `nginx-kosmamed-frontend.conf` — отдельный `medmarket.su.performance.conf` **не нужен**.

```bash
cp tools/perf/nginx-kosmamed-frontend.conf \
   /etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.conf

nginx -t && systemctl reload nginx
```

Если nginx ругается на `medmarket.su.performance.conf` — в vhost или в `medmarket.su.includes` удалить строку:
`include .../medmarket.su.performance.conf;`

### 3. HTTP globals (один раз, если ещё нет zone/map)

```bash
scp tools/perf/nginx-http-globals.conf kosmamed:/etc/nginx/conf.d/kosmamed-performance-http.conf
ssh kosmamed 'nginx -t && systemctl reload nginx'
```

Сверить с текущим `nginx.conf`: не дублировать `gzip on`, `limit_req_zone`, если уже есть.

### ~~2. Performance include~~ (устарело)

Не использовать. Содержимое уже внутри `nginx-kosmamed-frontend.conf`.

### 4. Apache backend

```bash
# Сверить diff, затем:
cp tools/perf/kosmamed-apache-backend.conf \
   /etc/apache2/fastpanel2-sites/medmarket_su_usr/medmarket.su.conf

apache2ctl configtest && systemctl reload apache2
```

Добавлено vs текущий конфиг:

- `KeepAlive On` (меньше overhead nginx↔Apache)
- `FcgidBusyTimeout 600` / `FcgidIOTimeout 600` (не держать PHP 160 мин)

### 5. PHP

```bash
cp tools/perf/kosmamed-php.ini \
   /var/www/medmarket_su_usr/data/php-bin/medmarket.su/php.ini

apache2ctl configtest && systemctl reload apache2
```

Проверка в Bitrix → Настройки PHP:

- `opcache.enable=1`
- `opcache.validate_timestamps=0`
- `realpath_cache_size=4096K`

**memory_limit:** в панели было 2048M — в репо 512M для витрины. Если падают выгрузки — поднять до 1024M.

### 6. Проверка

```bash
bash tools/perf/prod-config-check.sh https://kosmamed.ru
bash tools/perf/prod-warmup-sitemap.sh https://kosmamed.ru
```

Заголовки статики:

```bash
curl -sI 'https://kosmamed.ru/bitrix/js/main/core/core.js' | grep -iE 'cache-control|expires|content-encoding'
curl -sI 'https://kosmamed.ru/upload/iblock/'  # любой jpg
curl -sI -H 'Accept: image/webp' 'https://kosmamed.ru/upload/.../file.jpg'  # webp twin
```

---

## Что даёт vs текущий конфиг

| Было | Станет |
|------|--------|
| Вся статика через Apache (php-cgi соседи) | nginx отдаёт bitrix/upload с диска |
| `expires 3d` | 30d + immutable |
| Нет rate limit | 300 req/min + conn limit, блок .env-сканеров |
| `FcgidBusyTimeout 9600` (2.5 ч зависших PHP) | 600 с |
| Нет KeepAlive Apache | KeepAlive 500 req |
| gzip level 7, без vary | gzip 5 + vary (кэш CDN/браузер) |
| Нет open_file_cache | кеш fd для статики |

---

## Важно

- **HTML** (`/`, `/catalog/`, `/product/`) nginx **не кеширует** — композит Bitrix.
- После деплоя кода: `systemctl reload apache2` (opcache validate_timestamps=0).
- WebP twins: `bash tools/perf/webp-warmup.sh` перед включением `$webp_suffix`.
- Не чистить `bitrix/managed_cache` целиком на деплое — см. `prod-deploy.sh`.
