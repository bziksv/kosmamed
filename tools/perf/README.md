# tools/perf — деплой и проверка kosmamed.ru

## Порядок выката

**Только через git:** commit + push → `git pull` на сервере → `prod-deploy.sh`. Без `scp` и `/root/deploy/`.

| Шаг | Где | Действие |
|-----|-----|----------|
| 1 | Mac | `git commit` + `git push origin main` |
| 2 | Сервер | `bash tools/perf/prod-deploy.sh` |
| 3 | Проверка | `prod-verify-deploy.sh --disk-only`, remote `main.js` = 20275 |

**Сервер:** `ssh kosmamed` (155.212.171.103) → `/var/www/medmarket_su_usr/data/www/medmarket.su`, PHP **8.3-fpm**.

## Скрипты

| Файл | Назначение |
|------|------------|
| `prod-deploy.sh` | pull, проверка файлов, чистка кеша, reload FPM, verify, warmup в фоне |
| `prod-verify-deploy.sh` | файлы на диске + curl-маркеры снаружи (`--disk-only` — только диск) |
| `prod-warmup.sh` | прогрев страниц |
| `prod-composite-check.sh` | TTFB / X-Bitrix-Composite |

## Кеш при деплое

По умолчанию: `bitrix/html_pages` (кроме `.enabled`) и `bitrix/cache/css|js`.

**Не чистить** `managed_cache` — иначе `km_section_preview.php` долбит БД (~60 с TTFB).

Полная чистка только осознанно: `FULL_CACHE_CLEAR=1 bash tools/perf/prod-deploy.sh`.

## Проверка «файлы = git»

```bash
git rev-parse --short HEAD    # = origin/main
bash tools/perf/prod-verify-deploy.sh https://kosmamed.ru --disk-only
wc -c bitrix/templates/elektro_flat/js/main.js   # 20275
```

Допустимые расхождения с git на проде: логи МойСклад, SDEK `list.json`, `catalog_export/yandex_*.php` — на JS/CSS не влияют.

## В git (деплоятся pull'ом)

`kosmamed_perf.php`, `km_section_preview.php`, шаблон `elektro_flat`, эти скрипты.

Правило для агента Cursor: `.cursor/rules/prod-deploy-git-first.mdc`.
