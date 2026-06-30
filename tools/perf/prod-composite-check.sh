#!/bin/bash
# Проверка композитного кеша (с сервера или снаружи).
#   bash tools/perf/prod-composite-check.sh https://kosmamed.ru
set -euo pipefail

BASE="${1:-https://kosmamed.ru}"
UA='Mozilla/5.0 (compatible; kosmamed-warmup/1.0)'

URLS=(
  "/"
  "/catalog/"
  "/catalog/alkotestery/"
  "/product/operatsionnyy_stol_promerix_merivaara_finlyandiya/"
)

echo "== Composite check: $BASE =="
echo "Ожидание: X-Bitrix-Composite: Cache или warm TTFB < ~0.8s"
echo

home_headers=$(curl -skI --http1.1 -A "$UA" --max-time 120 "${BASE}/" 2>/dev/null || true)
home_size=$(curl -sk --http1.1 -A "$UA" --max-time 120 "${BASE}/" 2>/dev/null | wc -c | tr -d ' ')
home_powered=$(echo "$home_headers" | grep -i '^X-Powered-By:' | tr -d '\r' || true)
home_cookie=$(echo "$home_headers" | grep -ci '^Set-Cookie:' || true)
echo "Homepage: HTML=${home_size} bytes  cookies=${home_cookie}  ${home_powered:-no PHP header}"
if [[ "${home_size:-0}" -gt 500000 ]]; then
  echo "  WARN: HTML > 500 KB — проверь cssinliner/htmlcompressor"
fi
if [[ -n "$home_powered" ]]; then
  echo "  WARN: PHP на каждом хите — статический композит не отдаётся (прогрей / проверь .enabled)"
fi
echo

composite_hits=0
fast_hits=0

for path in "${URLS[@]}"; do
  url="${BASE}${path}"
  echo "--- $path ---"
  for i in 1 2 3; do
    headers=$(curl -skI --http1.1 -A "$UA" --max-time 120 "$url" 2>/dev/null || true)
    ttfb=$(curl -sk --http1.1 -A "$UA" -o /dev/null -w '%{time_starttransfer}' --max-time 120 "$url" 2>/dev/null || echo '?')
    comp=$(echo "$headers" | grep -i '^X-Bitrix-Composite:' | tr -d '\r' || true)
    powered=$(echo "$headers" | grep -i '^X-Powered-By:' | tr -d '\r' || true)
    fast_note=''
    if awk -v t="$ttfb" 'BEGIN { exit !(t+0 > 0 && t+0 < 0.85) }' 2>/dev/null; then
      fast_hits=$((fast_hits + 1)); fast_note='  [fast]'
    fi
    if [[ -n "$comp" ]]; then
      composite_hits=$((composite_hits + 1))
      echo "  hit $i: TTFB=${ttfb}s  $comp${fast_note}"
    else
      echo "  hit $i: TTFB=${ttfb}s  (no Cache header) ${powered:-}${fast_note}"
    fi
  done
  echo
done

echo "== Summary =="
if [[ "$composite_hits" -gt 0 ]]; then
  echo "  OK: X-Bitrix-Composite: Cache виден ($composite_hits ответов)"
elif [[ "$fast_hits" -ge 4 ]]; then
  echo "  OK: TTFB быстрый ($fast_hits хитов) — композит работает (режим Авто)"
else
  echo "  WARN: мало быстрых ответов"
  echo "  → bash tools/perf/prod-warmup.sh $BASE"
  echo "  → ls -la bitrix/html_pages/.enabled"
fi
