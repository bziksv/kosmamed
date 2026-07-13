#!/bin/bash
# Проверка: local / origin / prod на одном commit, без грязных tracked-файлов.
set -euo pipefail

REPO="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$REPO"

LOCAL="$(git rev-parse --short HEAD)"
ORIGIN="$(git rev-parse --short origin/main 2>/dev/null || echo '?')"
DIRTY_LOCAL="$(git status --porcelain --untracked-files=no | wc -l | tr -d ' ')"

echo "== Git sync check =="
echo "local HEAD:  $LOCAL"
echo "origin/main: $ORIGIN"
echo "local dirty tracked files: $DIRTY_LOCAL"

if [[ "$LOCAL" != "$ORIGIN" ]]; then
  echo "FAIL: local != origin/main (git pull / push?)"
  exit 1
fi

if [[ "$DIRTY_LOCAL" != "0" ]]; then
  echo "FAIL: локальные изменения tracked-файлов:"
  git status --short --untracked-files=no | head -20
  exit 1
fi

if [[ "${SKIP_PROD:-}" == "1" ]]; then
  echo "SKIP_PROD=1 — проверка прода пропущена"
  exit 0
fi

PROD_OUT="$(ssh kosmamed "cd /var/www/medmarket_su_usr/data/www/medmarket.su && git rev-parse --short HEAD && git status --porcelain --untracked-files=no | wc -l" 2>/dev/null || true)"
PROD_HEAD="$(echo "$PROD_OUT" | sed -n '1p')"
PROD_DIRTY="$(echo "$PROD_OUT" | sed -n '2p' | tr -d ' ')"

echo "prod HEAD:   $PROD_HEAD"
echo "prod dirty tracked files: $PROD_DIRTY"

if [[ "$PROD_HEAD" != "$LOCAL" ]]; then
  echo "FAIL: prod commit != local/origin"
  exit 1
fi

if [[ "$PROD_DIRTY" != "0" ]]; then
  echo "FAIL: на проде грязные tracked-файлы — bash tools/perf/prod-deploy.sh"
  ssh kosmamed "cd /var/www/medmarket_su_usr/data/www/medmarket.su && git status --short --untracked-files=no | head -20"
  exit 1
fi

echo "OK: local = origin = prod ($LOCAL), tracked-файлы чистые"
