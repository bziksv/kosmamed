#!/bin/bash
# Установить performance include для kosmamed.ru / medmarket.su на prod.
#
# Использование (на сервере, от root):
#   cd /var/www/medmarket_su_usr/data/www/medmarket.su
#   bash tools/perf/nginx-apply-prod.sh
#
# Скрипт копирует include и добавляет строку в medmarket.su.includes, если её ещё нет.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
SNIPPET_SRC="${REPO_ROOT}/tools/perf/nginx-kosmamed-performance.include.conf"
SNIPPET_DST="/etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.performance.conf"
INCLUDES="/etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.includes"
MARKER="medmarket.su.performance.conf"

if [ ! -f "$SNIPPET_SRC" ]; then
  echo "ERROR: $SNIPPET_SRC not found"
  exit 1
fi

echo "== install performance include =="
cp -a "$SNIPPET_SRC" "$SNIPPET_DST"
echo "  $SNIPPET_DST"

if [ ! -f "$INCLUDES" ]; then
  echo "WARN: $INCLUDES not found — добавьте вручную в vhost:"
  echo "  include $SNIPPET_DST;"
else
  if grep -qF "$MARKER" "$INCLUDES"; then
    echo "== includes already references performance.conf =="
  else
    cp -a "$INCLUDES" "${INCLUDES}.bak.$(date +%Y%m%d%H%M%S)"
    echo "include $SNIPPET_DST; # $MARKER" >> "$INCLUDES"
    echo "  appended include to $INCLUDES"
  fi
fi

echo ""
echo "== http { } globals (если ещё нет) =="
echo "  cp tools/perf/nginx-http-globals.conf /etc/nginx/conf.d/kosmamed-performance-http.conf"
echo "  map \$http_accept \$webp_suffix — нужен для WebP в upload"

echo ""
echo "== test & reload =="
nginx -t
systemctl reload nginx
echo "Done. Run: bash tools/perf/prod-config-check.sh https://kosmamed.ru"
