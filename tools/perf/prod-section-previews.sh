#!/bin/bash
# Заполнить PICTURE у подкатегорий каталога из фото товаров (см. tools/section-previews/README.md).
#
#   bash tools/perf/prod-section-previews.sh apply
#   bash tools/perf/prod-section-previews.sh scan
set -euo pipefail

SITE_DIR=/var/www/medmarket_su_usr/data/www/medmarket.su
MODE="${1:-apply}"
OWNER=medmarket_su_usr:medmarket_su_usr

cd "$SITE_DIR"

if [[ ! -f "$SITE_DIR/tools/section-previews/generate.php" ]]; then
  echo "FAIL: $SITE_DIR/tools/section-previews/generate.php not found (deploy via git pull first)"
  exit 1
fi

echo "=== section-previews: $MODE ==="
php -d short_open_tag=On "$SITE_DIR/tools/section-previews/generate.php" "$MODE" "${@:2}"

echo "=== clear menu + section preview cache ==="
find "$SITE_DIR/bitrix/cache/s1/bitrix/menu" -mindepth 1 -delete 2>/dev/null || true
find "$SITE_DIR/bitrix/cache" -type d -name 'km_section_preview' -exec rm -rf {} + 2>/dev/null || true
if command -v php >/dev/null 2>&1; then
  php -d short_open_tag=On -r '
    $_SERVER["DOCUMENT_ROOT"] = "'"$SITE_DIR"'";
    define("NO_KEEP_STATISTIC", true);
    define("NOT_CHECK_PERMISSIONS", true);
    require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
    if (isset($GLOBALS["CACHE_MANAGER"]) && is_object($GLOBALS["CACHE_MANAGER"])) {
      $GLOBALS["CACHE_MANAGER"]->ClearByTag("iblock_id_24");
    }
  ' || true
fi

chown -R "$OWNER" "$SITE_DIR/tools/section-previews" 2>/dev/null || true
echo "Done."
