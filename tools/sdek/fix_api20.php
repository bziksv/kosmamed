<?php
/**
 * Настройка СДЭК API 2.0 (идемпотентно).
 *
 *   php -d short_open_tag=On tools/sdek/fix_api20.php
 */
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$docRoot = realpath(__DIR__ . '/../..');
if (!$docRoot) {
    fwrite(STDERR, "DOCUMENT_ROOT not found\n");
    exit(1);
}

$_SERVER['DOCUMENT_ROOT'] = $docRoot;
$_SERVER['HTTP_HOST'] = 'kosmamed.ru';
$_SERVER['SERVER_NAME'] = 'kosmamed.ru';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('ipol.sdek') || !CModule::IncludeModule('sale')) {
    fwrite(STDERR, "Modules ipol.sdek / sale not loaded\n");
    exit(1);
}

\Ipolh\SDEK\option::set('useOldApi', 'N');
\Ipolh\SDEK\option::set('logged', '3');
\Ipolh\SDEK\option::set('sdekDeadServer', false);
echo "options: useOldApi=N, logged=3, sdekDeadServer cleared\n";

$res = $GLOBALS['DB']->Query('SELECT CONFIG FROM b_sale_delivery_srv WHERE ID=133');
if ($row = $res->Fetch()) {
    $cfg = unserialize($row['CONFIG'], ['allowed_classes' => false]);
    $old = unserialize(unserialize($cfg['MAIN']['OLD_SETTINGS']), ['allowed_classes' => false]);
    if (!is_array($old)) {
        $old = [];
    }
    $old['ACCOUNT'] = '3';
    $cfg['MAIN']['OLD_SETTINGS'] = serialize(serialize($old));
    $ser = serialize($cfg);
    $GLOBALS['DB']->Query(
        'UPDATE b_sale_delivery_srv SET CONFIG="' . $GLOBALS['DB']->ForSql($ser) . '" WHERE ID=133'
    );
    echo "delivery 133: ACCOUNT=3\n";
}

$GLOBALS['DB']->Query('UPDATE ipol_sdeklogs SET ACTIVE="N" WHERE ID IN (1,2)');
echo "accounts 1,2: ACTIVE=N\n";

$tarifs = unserialize(COption::GetOptionString('ipol.sdek', 'tarifs', ''), ['allowed_classes' => false]);
if (!is_array($tarifs)) {
    $tarifs = [];
}
$tarifs['778'] = ['BLOCK' => 'Y'];
COption::SetOptionString('ipol.sdek', 'tarifs', serialize($tarifs));
echo "tariff 778: BLOCK=Y\n";

$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/IPOLSDEK';
if (is_dir($cacheDir)) {
    array_map('unlink', glob($cacheDir . '/*') ?: []);
}
echo "cache IPOLSDEK cleared\n";

echo "isLogged: " . (CDeliverySDEK::isLogged() ? 'Y' : 'N') . "\n";
echo "courier tarifs: " . implode(',', CDeliverySDEK::getListOfTarifs('courier', false)) . "\n";
echo "done\n";
