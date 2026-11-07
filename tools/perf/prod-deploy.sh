#!/bin/bash
# Деплой kosmamed.ru — как vilmed.ru + 3 отличия (см. комментарии).
#
#   bash /var/www/medmarket_su_usr/data/www/medmarket.su/tools/perf/prod-deploy.sh
set -euo pipefail

SITE_DIR=/var/www/medmarket_su_usr/data/www/medmarket.su
OWNER=medmarket_su_usr:medmarket_su_usr
BASE_URL=https://kosmamed.ru

cd "$SITE_DIR"

cp -a "$SITE_DIR/bitrix/.settings.php" /root/.settings.php.bak

# Единственный источник правды — origin/main. Локальные правки tracked-файлов на проде сбрасываем.
# Файлы, которые перепишет git, запоминаем: им (и новым папкам) потом вернём владельца.
OLD_HEAD=$(git rev-parse HEAD)
DIRTY_LIST=$(mktemp)
git diff --name-only -z HEAD > "$DIRTY_LIST"
git fetch origin
git reset --hard origin/main

cp -a /root/.settings.php.bak "$SITE_DIR/bitrix/.settings.php"
chown "$OWNER" "$SITE_DIR/bitrix/.settings.php"

# perf-файлы в git с d392b8230 — /root/deploy/ больше не трогаем (там могут лежать старые копии)

echo "--- Проверка файлов после pull (до чистки кеша) ---"
bash "$SITE_DIR/tools/perf/prod-verify-deploy.sh" "$BASE_URL" --disk-only || {
  echo "FAIL: на диске не те файлы / не тот commit — warmup и чистка кеша не запускаются"
  exit 1
}

# Composite + CSS/JS. managed_cache НЕ чистим — иначе меню+preview долбят БД ~60с на каждый хит.
find "$SITE_DIR/bitrix/html_pages" \
  -mindepth 1 ! -name ".enabled" ! -name ".config.php" -delete 2>/dev/null || true
find "$SITE_DIR/bitrix/cache/css" "$SITE_DIR/bitrix/cache/js" \
  -mindepth 1 -delete 2>/dev/null || true

if [[ "${FULL_CACHE_CLEAR:-}" == "1" ]]; then
  echo "FULL_CACHE_CLEAR=1 — чистим bitrix/cache, managed_cache, stack_cache"
  find "$SITE_DIR/bitrix/cache" "$SITE_DIR/bitrix/managed_cache" \
       "$SITE_DIR/bitrix/stack_cache" \
    -mindepth 1 -delete 2>/dev/null || true
fi

touch "$SITE_DIR/bitrix/html_pages/.enabled"
chown "$OWNER" "$SITE_DIR/bitrix/html_pages/.enabled"

apache2ctl configtest
# kosmamed: PHP 8.3-fpm (не apache) — иначе OPcache держит старый код
systemctl reload php8.3-fpm
systemctl reload apache2

# chown -R по всему сайту идёт минутами и падает на файлах кеша, которые Bitrix удаляет на ходу.
if [[ "${FULL_CHOWN:-}" == "1" ]]; then
  chown -R "$OWNER" "$SITE_DIR" 2>/dev/null || echo "WARN: chown -R частично не прошёл (удалённые на ходу файлы кеша)"
else
  CHANGED=$(
    { cat "$DIRTY_LIST"; git diff --name-only -z "$OLD_HEAD" HEAD; } | tr '\0' '\n' | sort -u | grep -v '^$' || true
  )
  if [[ -n "$CHANGED" ]]; then
    # файлы + все их родительские папки внутри сайта (git создаёт новые папки от root)
    printf '%s\n' "$CHANGED" \
      | awk -F/ '{ p=$1; print p; for (i=2; i<=NF; i++) { p=p"/"$i; print p } }' \
      | sort -u \
      | while IFS= read -r path; do
          [[ -e "$path" ]] && chown -h "$OWNER" "$path"
        done || true
    echo "chown: $(printf '%s\n' "$CHANGED" | wc -l | tr -d ' ') изменённых файлов"
  fi
fi
rm -f "$DIRTY_LIST"

# Быстрая проверка снаружи после reload (warmup — отдельно, в фоне)
bash "$SITE_DIR/tools/perf/prod-verify-deploy.sh" "$BASE_URL" || true

# Warmup 10+ мин и рвёт SSH — в фон, лог в /tmp
if [[ "${SKIP_WARMUP:-}" == "1" ]]; then
  echo "SKIP_WARMUP=1 — прогрев пропущен"
else
  LOG="/tmp/kosmamed-warmup-$(date +%Y%m%d-%H%M%S).log"
  echo "Warmup в фоне → $LOG (SSH можно закрывать)"
  nohup bash "$SITE_DIR/tools/perf/prod-warmup.sh" "$BASE_URL" >>"$LOG" 2>&1 &
  echo "  tail -f $LOG"
fi

bash "$SITE_DIR/tools/perf/prod-composite-check.sh" "$BASE_URL" || true
