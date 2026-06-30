#!/bin/bash
# Прогрев композитного кеша Bitrix после деплоя / очистки html_pages.
#
# На сервере:
#   cd /var/www/medmarket_su_usr/data/www/medmarket.su
#   bash tools/perf/prod-warmup.sh https://kosmamed.ru
set -euo pipefail

BASE="${1:-https://kosmamed.ru}"
UA='Mozilla/5.0 (compatible; kosmamed-warmup/1.0)'
HITS="${WARMUP_HITS:-3}"

# Базовый список (главная, каталог, несколько реальных разделов, товар, корзина)
URLS=(
  "/"
  "/catalog/"
  "/catalog/alkotestery/"
  "/catalog/akusherskie_stetoskopy/"
  "/catalog/analizatory_laboratornye/"
  "/catalog/anesteziologiya_i_reanimatsiya/"
  "/catalog/akvadistillyatory/"
  "/product/operatsionnyy_stol_promerix_merivaara_finlyandiya/"
  "/personal/cart/"
)

# Доп. разделы из sitemap (если есть) — топ-20
if [[ -f sitemap-iblock-24.xml ]]; then
  while read -r p; do
    [[ -n "$p" ]] && URLS+=("$p")
  done < <(grep -oE 'https://[a-z.]+/catalog/[^/<]+/' sitemap-iblock-24.xml 2>/dev/null \
            | sort -u | head -20 | sed -E 's|https://[a-z.]+||')
fi

echo "== Warmup: $BASE (hits per URL: $HITS) =="

for path in "${URLS[@]}"; do
  url="${BASE}${path}"
  last_ttfb='?'
  comp=''
  for ((i=1; i<=HITS; i++)); do
    headers=$(curl -skI --http1.1 -A "$UA" --max-time 120 "$url" 2>/dev/null || true)
    last_ttfb=$(curl -sk --http1.1 -A "$UA" -o /dev/null -w '%{time_starttransfer}' --max-time 120 "$url" 2>/dev/null || echo '?')
    comp=$(echo "$headers" | grep -i '^X-Bitrix-Composite:' | tr -d '\r' || true)
  done
  if [[ -n "$comp" ]]; then
    echo "  $path  TTFB:${last_ttfb}s  $comp"
  else
    echo "  $path  TTFB:${last_ttfb}s"
  fi
done

echo "Done. Проверка: bash tools/perf/prod-composite-check.sh $BASE"
