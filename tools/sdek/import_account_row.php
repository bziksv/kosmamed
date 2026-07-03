<?php
/**
 * Импорт одной строки ipol_sdeklogs из TSV (KEY\\tVALUE).
 *   php tools/sdek/import_account_row.php /tmp/sdek_acc3.tsv
 */
if (PHP_SAPI !== 'cli' || $argc < 2) {
    fwrite(STDERR, "Usage: php import_account_row.php file.tsv\n");
    exit(1);
}

$docRoot = realpath(__DIR__ . '/../..');
$_SERVER['DOCUMENT_ROOT'] = $docRoot;
$_SERVER['HTTP_HOST'] = 'kosmamed.ru';
$_SERVER['SERVER_NAME'] = 'kosmamed.ru';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$row = [];
foreach (file($argv[1]) as $line) {
    $line = rtrim($line, "\r\n");
    if ($line === '') {
        continue;
    }
    $pos = strpos($line, "\t");
    if ($pos === false) {
        continue;
    }
    $row[substr($line, 0, $pos)] = substr($line, $pos + 1);
}

if (empty($row['ID'])) {
    fwrite(STDERR, "No ID in TSV\n");
    exit(1);
}

$id = (int)$row['ID'];
$exists = $GLOBALS['DB']->Query('SELECT ID FROM ipol_sdeklogs WHERE ID=' . $id)->Fetch();

$sets = [];
foreach ($row as $k => $v) {
    if ($k === 'ID') {
        continue;
    }
    $sets[] = $k . '="' . $GLOBALS['DB']->ForSql($v) . '"';
}

if ($exists) {
    $GLOBALS['DB']->Query('UPDATE ipol_sdeklogs SET ' . implode(',', $sets) . ' WHERE ID=' . $id);
    echo "Updated ipol_sdeklogs ID=$id\n";
} else {
    $cols = array_keys($row);
    $vals = array_map(fn($v) => '"' . $GLOBALS['DB']->ForSql($v) . '"', array_values($row));
    $GLOBALS['DB']->Query(
        'INSERT INTO ipol_sdeklogs (' . implode(',', $cols) . ') VALUES (' . implode(',', $vals) . ')'
    );
    echo "Inserted ipol_sdeklogs ID=$id\n";
}
