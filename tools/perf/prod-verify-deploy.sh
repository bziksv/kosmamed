#!/bin/bash
# Проверка выката на прод: файлы на диске + маркеры в HTML/JS снаружи.
#
#   bash /var/www/medmarket_su_usr/data/www/medmarket.su/tools/perf/prod-verify-deploy.sh
#   bash .../prod-verify-deploy.sh https://kosmamed.ru aa7985ed7
set -euo pipefail

SITE_DIR="${SITE_DIR:-/var/www/medmarket_su_usr/data/www/medmarket.su}"
BASE_URL="${1:-https://kosmamed.ru}"
EXPECT_COMMIT="${2:-}"

cd "$SITE_DIR"

fail=0
ok() { echo "  OK  $*"; }
bad() { echo "  FAIL $*"; fail=1; }

echo "== Deploy verify: $BASE_URL =="
echo "SITE_DIR=$SITE_DIR"
echo

echo "--- Git ---"
HEAD=$(git rev-parse --short HEAD 2>/dev/null || echo '?')
echo "HEAD=$HEAD"
if [[ -n "$EXPECT_COMMIT" && "$HEAD" != "$EXPECT_COMMIT" && "$HEAD" != ${EXPECT_COMMIT:0:7}* ]]; then
  bad "commit: ожидали $EXPECT_COMMIT, на диске $HEAD"
else
  ok "commit ${EXPECT_COMMIT:-$HEAD}"
fi
if git status -sb | grep -qv '^##'; then
  echo "  WARN локальные изменения:"
  git status -sb | head -20
fi
echo

echo "--- Файлы на диске (ожидаемые размеры после aa7985ed7) ---"
MAIN_JS="$SITE_DIR/bitrix/templates/elektro_flat/js/main.js"
THEME_CSS="$SITE_DIR/bitrix/templates/elektro_flat/kosmamed-theme.css"
SECT_JS="$SITE_DIR/bitrix/templates/elektro_flat/components/bitrix/catalog.section/.default/script.js"
PERF_PHP="$SITE_DIR/bitrix/php_interface/include/kosmamed_perf.php"

for f in "$MAIN_JS" "$THEME_CSS" "$SECT_JS"; do
  [[ -f "$f" ]] || { bad "нет файла $f"; continue; }
  sz=$(wc -c < "$f" | tr -d ' ')
  echo "  $(basename "$f"): $sz b"
done

[[ $(wc -c < "$MAIN_JS" | tr -d ' ') -eq 20275 ]] && ok "main.js 20275" || bad "main.js $(wc -c < "$MAIN_JS") != 20275"
grep -q kmInitCatalogDeferredImages "$MAIN_JS" && ok "main.js kmInitCatalogDeferredImages" || bad "main.js без kmInitCatalogDeferredImages"
DOTS=$(grep -c 'slick-dots' "$THEME_CSS" 2>/dev/null || echo 0)
[[ "$DOTS" -ge 4 ]] && ok "kosmamed-theme slick-dots ($DOTS)" || bad "kosmamed-theme без slick-dots ($DOTS)"
[[ $(wc -c < "$SECT_JS" | tr -d ' ') -eq 6661 ]] && ok "catalog.section script.js 6661" || bad "catalog.section script.js $(wc -c < "$SECT_JS") != 6661"

if [[ -f "$PERF_PHP" ]]; then
  grep -q kmDeferCatalogOffscreenImages "$PERF_PHP" && ok "kosmamed_perf.php на месте" || bad "kosmamed_perf.php без defer-картинок"
else
  bad "нет $PERF_PHP (не в git — копировать вручную)"
fi
echo

echo "--- Снаружи (curl) ---"
REMOTE_MAIN=$(curl -sf "$BASE_URL/bitrix/templates/elektro_flat/js/main.js" | wc -c | tr -d ' ')
[[ "$REMOTE_MAIN" -eq 20275 ]] && ok "remote main.js $REMOTE_MAIN" || bad "remote main.js $REMOTE_MAIN (OPcache/старый файл?)"

check_page() {
  local path="$1" label="$2"
  shift 2
  local url="${BASE_URL}${path}"
  local html
  html=$(curl -sf --max-time 180 "$url?bxrand=$(date +%s)" -H 'Cache-Control: no-cache' || true)
  [[ -n "$html" ]] || { bad "$label: пустой ответ $url"; return; }
  local size=${#html}
  echo "  $label: HTML ${size} b"
  for needle in "$@"; do
    echo "$html" | grep -q "$needle" && ok "$label содержит $needle" || bad "$label НЕТ $needle"
  done
}

check_page "/" "главная" "kosmamed-theme.css" "anythingslider/slider.css" "anythingContainer" "catalog-item-card"
check_page "/catalog/anesteziologiya_i_reanimatsiya/" "каталог" "magic_slide_ss" "kosmamed-theme.css"
DATA_KM=$(curl -sf --max-time 180 "${BASE_URL}/catalog/anesteziologiya_i_reanimatsiya/?bxrand=$(date +%s)" | grep -c data-km-src || true)
if [[ "$DATA_KM" -gt 0 ]]; then
  ok "каталог data-km-src: $DATA_KM"
else
  bad "каталог data-km-src=0 (нет kosmamed_perf или composite-кеш)"
fi
echo

echo "--- Composite ---"
bash "$SITE_DIR/tools/perf/prod-composite-check.sh" "$BASE_URL" || true
echo

if [[ "$fail" -eq 0 ]]; then
  echo "== Итог: OK =="
else
  echo "== Итог: ЕСТЬ ОШИБКИ =="
  exit 1
fi
