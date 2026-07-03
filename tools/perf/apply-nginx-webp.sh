#!/bin/bash
# WebP по Accept: map в http { } + nested location в ^~ /upload/
set -euo pipefail

SITE_DIR="${1:-/var/www/medmarket_su_usr/data/www/medmarket.su}"
NGINX_SITE=/etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.conf
NGINX_INCLUDES=/etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.includes
HTTP_SNIPPET=/etc/nginx/conf.d/kosmamed-webp-map.conf
PERF_SNIPPET=/etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.performance.conf

# FastPanel подключает fastpanel2-sites/*/*.conf в http { } — .performance.conf с location ломает nginx.
if [[ -f "$PERF_SNIPPET" ]]; then
  mv -f "$PERF_SNIPPET" "${PERF_SNIPPET}.disabled" 2>/dev/null || rm -f "$PERF_SNIPPET"
fi
rm -f /etc/nginx/conf.d/kosmamed-performance-http.conf

cat > "$HTTP_SNIPPET" <<'EOF'
# kosmamed: append twins photo.jpg.webp (tools/perf/webp-warmup.sh)
map $http_accept $webp_suffix {
    default        "";
    "~*image/webp" ".webp";
}
EOF

# Убрать ошибочный include performance.conf (location в конце server ломает nginx)
if [[ -f "$NGINX_INCLUDES" ]]; then
  sed -i '/medmarket\.su\.performance\.conf/d' "$NGINX_INCLUDES"
fi

if grep -q 'km-webp-upload' "$NGINX_SITE" 2>/dev/null; then
  echo "nginx upload webp block already present"
else
  python3 <<'PY'
from pathlib import Path
path = Path("/etc/nginx/fastpanel2-sites/medmarket_su_usr/medmarket.su.conf")
text = path.read_text()
needle = "    location ^~ /upload/ {"
block = """    # km-webp-upload: Accept: image/webp -> file.jpg.webp
    location ^~ /upload/ {
        location ~ \\.php$ {
            return 403;
        }
        location ~* \\.(?:png|jpe?g)$ {
            access_log off;
            add_header Vary Accept;
            expires 30d;
            add_header Cache-Control "public, immutable";
            try_files $uri$webp_suffix $uri =404;
        }
        access_log off;
        expires 30d;
        add_header Cache-Control "public";
        try_files $uri =404;
    }"""
if needle not in text:
    raise SystemExit("upload location block not found in nginx site conf")
# Replace default upload block (no nested webp yet)
old = """    location ^~ /upload/ {
        location ~ \\.php$ {
            return 403;
        }
        access_log off;
        expires 30d;
        add_header Cache-Control "public";
        try_files $uri =404;
    }"""
if old in text:
    text = text.replace(old, block, 1)
else:
    raise SystemExit("expected upload block shape changed — patch manually")
path.write_text(text)
print("patched", path)
PY
fi

nginx -t
systemctl reload nginx
echo "OK: webp map + upload nested location applied"
