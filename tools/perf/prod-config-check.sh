#!/bin/bash
# Проверка: применились ли nginx frontend + статика на prod kosmamed.
#   bash tools/perf/prod-config-check.sh
#   bash tools/perf/prod-config-check.sh https://kosmamed.ru
set -euo pipefail

BASE="${1:-https://kosmamed.ru}"
UA='Mozilla/5.0 (compatible; kosmamed-warmup/1.0)'
PASS=0
FAIL=0
WARN=0

ok()   { echo "  OK   $1"; PASS=$((PASS + 1)); }
fail() { echo "  FAIL $1"; FAIL=$((FAIL + 1)); }
warn() { echo "  WARN $1"; WARN=$((WARN + 1)); }

check_header() {
  local url="$1"
  local pattern="$2"
  local label="$3"
  local headers
  headers=$(curl -skI -A "$UA" --max-time 20 "$url" 2>/dev/null || true)
  if echo "$headers" | grep -qiE "$pattern"; then
    ok "$label"
    echo "$headers" | grep -iE "$pattern" | head -3 | sed 's/^/       /'
  else
    fail "$label (expected: $pattern)"
    echo "$headers" | head -8 | sed 's/^/       /'
  fi
}

echo "== prod-config-check: $BASE =="
echo

echo "0) SSL certificate"
host="${BASE#https://}"
host="${host#http://}"
host="${host%%/*}"
cert_pem=$(echo | openssl s_client -connect "${host}:443" -servername "$host" 2>/dev/null | openssl x509 2>/dev/null || true)
if [[ -z "$cert_pem" ]]; then
  fail "cannot read SSL cert for $host"
else
  echo "$cert_pem" | openssl x509 -noout -dates 2>/dev/null | sed 's/^/       /'
  if echo "$cert_pem" | openssl x509 -noout -checkend 0 >/dev/null 2>&1; then
    ok "SSL not expired"
  else
    fail "SSL EXPIRED"
  fi
fi

echo
echo "1) Bot filter (plain curl → 444 or empty)"
code=$(curl -sk --http1.1 -o /dev/null -w '%{http_code}' --max-time 10 "$BASE/" 2>/dev/null) || code="000"
if [[ "$code" == "000" || "$code" == "444" ]]; then
  ok "bot filter blocks plain curl (code=$code)"
else
  warn "plain curl got HTTP $code — bot filter may be off"
fi

echo
echo "2) Warmup UA → HTTP 200"
code=$(curl -sk -o /dev/null -w '%{http_code}' -A "$UA" --max-time 30 "$BASE/" 2>/dev/null) || code="000"
if [[ "$code" == "200" ]]; then
  ok "homepage 200 with kosmamed-warmup UA"
else
  fail "homepage code=$code with warmup UA"
fi

echo
echo "3) Bitrix JS — long cache + nginx (not Apache php)"
JS_URL="${BASE}/bitrix/js/main/core/core.js"
check_header "$JS_URL" "cache-control.*(immutable|max-age|public)" "bitrix JS cache headers"
srv=$(curl -skI -A "$UA" --max-time 15 "$JS_URL" 2>/dev/null | grep -i '^server:' | head -1 || true)
if echo "$srv" | grep -qi nginx; then
  ok "bitrix JS served by nginx ($srv)"
else
  warn "bitrix JS server header: ${srv:-unknown} (expected nginx)"
fi

echo
echo "4) Upload static — nginx direct"
# probe: any upload path from sitemap or fallback
UP="${BASE}/upload/"
code=$(curl -sk -o /dev/null -w '%{http_code}' -A "$UA" --max-time 15 "$UP" 2>/dev/null) || code="000"
if [[ "$code" == "403" || "$code" == "404" || "$code" == "200" ]]; then
  ok "upload/ reachable (code=$code, listing may be forbidden)"
else
  warn "upload/ code=$code"
fi

echo
echo "5) main.js size (deploy sanity)"
js_size=$(curl -sk -A "$UA" --max-time 20 "${BASE}/bitrix/templates/elektro_flat/js/main.js" 2>/dev/null | wc -c | tr -d ' ')
if [[ "$js_size" -ge 21000 ]]; then
  ok "main.js >= 21000 bytes ($js_size)"
else
  fail "main.js too small ($js_size) — stale deploy?"
fi

echo
echo "6) slick on homepage"
home=$(curl -sk -A "$UA" --max-time 30 "$BASE/" 2>/dev/null || true)
if echo "$home" | grep -q 'slick.min.js'; then
  ok "slick.min.js in homepage HTML"
else
  warn "slick.min.js missing on homepage — card sliders won't init"
fi

echo
echo "== summary: PASS=$PASS FAIL=$FAIL WARN=$WARN =="
[[ "$FAIL" -eq 0 ]]
