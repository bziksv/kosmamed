#!/bin/bash
# OAuth-аккаунт СДЭК (id=3) с прода → локальная БД. Credentials не в git.
#
#   bash tools/sdek/sync_account_from_prod.sh
set -euo pipefail

SITE_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
REMOTE="${KM_SDEK_SSH:-kosmamed}"
REMOTE_SITE="${KM_SDEK_REMOTE_SITE:-/var/www/medmarket_su_usr/data/www/medmarket.su}"

ssh "$REMOTE" "cd '$REMOTE_SITE' && php -d short_open_tag=On -r '
\$_SERVER[\"DOCUMENT_ROOT\"] = getcwd();
\$_SERVER[\"HTTP_HOST\"] = \"kosmamed.ru\";
\$_SERVER[\"REQUEST_URI\"] = \"/\";
require \"bitrix/modules/main/include/prolog_before.php\";
\$r = \$GLOBALS[\"DB\"]->Query(\"SELECT * FROM ipol_sdeklogs WHERE ID=3\");
\$row = \$r->Fetch();
if (!\$row) { fwrite(STDERR, \"account 3 not found\\n\"); exit(1); }
foreach (\$row as \$k => \$v) {
  echo \$k.\"\\t\".str_replace([\"\\n\",\"\\r\",\"\\t\"], \" \", (string)\$v).\"\\n\";
}
'" > /tmp/sdek_acc3.tsv

php -d short_open_tag=On "$SITE_DIR/tools/sdek/import_account_row.php" /tmp/sdek_acc3.tsv
echo "Run: php -d short_open_tag=On tools/sdek/fix_api20.php"
