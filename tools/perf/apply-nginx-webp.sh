#!/bin/bash
# Включить WebP по Accept на prod (map + location для /upload/).
set -euo pipefail

SITE_DIR="${1:-/var/www/medmarket_su_usr/data/www/medmarket.su}"
HTTP_SNIPPET=/etc/nginx/conf.d/kosmamed-performance-http.conf
PERF_SNIPPET=/etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.performance.conf
INCLUDES=/etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.includes

cp -a "$SITE_DIR/tools/perf/nginx-http-globals.conf" "$HTTP_SNIPPET"
cp -a "$SITE_DIR/tools/perf/nginx-kosmamed-performance.include.conf" "$PERF_SNIPPET"

if ! grep -q 'medmarket.su.performance.conf' "$INCLUDES" 2>/dev/null; then
  {
    echo 'include /etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.performance.conf;'
  } >> "$INCLUDES"
fi

nginx -t
systemctl reload nginx
echo "OK: nginx webp map + /upload/ location applied"
