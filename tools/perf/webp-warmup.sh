#!/usr/bin/env bash
# webp-warmup.sh — генерация WebP-двойников для картинок (append-конвенция: photo.jpg -> photo.jpg.webp)
#
# Зачем: модуль delight.webpconverter подменяет <img src> на .webp только для resize_cache.
# Прямые картинки (полноразмерные iblock, баннеры, CSS-фоны) остаются jpg/png.
# Этот скрипт создаёт рядом с каждым jpg/png файл <name>.webp, который nginx отдаёт
# при Accept: image/webp (см. tools/perf/nginx-webp.conf). Так "в webp" уходит ВСЁ.
#
# Оригиналы НЕ удаляются и НЕ изменяются — нужны для ресайза и сверки с МойСклад.
#
# Использование:
#   bash webp-warmup.sh <КОРЕНЬ> [КАЧЕСТВО] [--force]
# Примеры:
#   bash webp-warmup.sh /var/www/.../upload/iblock           80
#   bash webp-warmup.sh /var/www/.../upload                  80
#   bash webp-warmup.sh ~/Documents/projects/kosmamed/medmarket.su/upload/iblock 80
#
# Требует cwebp (libwebp). macOS: brew install webp. Debian: apt install webp.

set -euo pipefail

ROOT="${1:-}"
QUALITY="${2:-80}"
FORCE="${3:-}"

if [[ -z "$ROOT" || ! -d "$ROOT" ]]; then
  echo "Usage: bash webp-warmup.sh <dir> [quality=80] [--force]" >&2
  exit 1
fi
if ! command -v cwebp >/dev/null 2>&1; then
  echo "cwebp не найден. Установите libwebp (brew install webp / apt install webp)." >&2
  exit 1
fi

echo "WebP warmup: root=$ROOT quality=$QUALITY force=${FORCE:-no}"
created=0; skipped=0; failed=0; total=0

# По умолчанию resize_cache пропускаем (долго). Для каталога без delight.webpconverter:
#   INCLUDE_RESIZE_CACHE=1 bash webp-warmup.sh /path/upload 80
if [[ "${INCLUDE_RESIZE_CACHE:-}" == "1" ]]; then
  PRUNE=''
else
  PRUNE='-name resize_cache -prune -o'
fi

while IFS= read -r -d '' img; do
  total=$((total+1))
  out="${img}.webp"
  if [[ -f "$out" && "$FORCE" != "--force" ]]; then
    skipped=$((skipped+1)); continue
  fi
  if cwebp -quiet -q "$QUALITY" "$img" -o "$out" 2>/dev/null; then
    created=$((created+1))
  else
    failed=$((failed+1)); rm -f "$out" 2>/dev/null || true
  fi
  if (( total % 2000 == 0 )); then
    echo "  ...обработано $total (создано $created, пропущено $skipped, ошибок $failed)"
  fi
done < <(find "$ROOT" $PRUNE -type f \
            \( -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.png' \) -print0)

echo "Готово: всего $total, создано $created, пропущено $skipped, ошибок $failed"
