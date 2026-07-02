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

# kosmamed: на проде бывают локальные правки footer/counter — иначе pull падает
git fetch origin
git checkout origin/main -- \
  "$SITE_DIR/bitrix/templates/elektro_flat/footer.php" \
  "$SITE_DIR/include/counter_2.php" \
  2>/dev/null || true

git pull origin main

cp -a /root/.settings.php.bak "$SITE_DIR/bitrix/.settings.php"
chown "$OWNER" "$SITE_DIR/bitrix/.settings.php"

# kosmamed: perf.php не в git — положи копии в /root/deploy/ один раз
for f in kosmamed_perf.php km_section_preview.php; do
  if [[ -f "/root/deploy/$f" ]]; then
    cp -a "/root/deploy/$f" "$SITE_DIR/bitrix/php_interface/include/$f"
    chown "$OWNER" "$SITE_DIR/bitrix/php_interface/include/$f"
  fi
done

done

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

chown -R "$OWNER" "$SITE_DIR"

# Быстрая проверка до долгого warmup (SSH не обрывать)
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
