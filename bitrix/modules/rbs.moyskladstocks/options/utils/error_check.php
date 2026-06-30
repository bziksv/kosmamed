<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Bitrix\Main\Loader;

$errorList = [];
//access check
if ($moduleAccessLevel === 'D') {
    $errorList[] = GetMessage("ACCESS_DENIED");
}
//check modules
if (!Loader::IncludeModule('rbs.moyskladstocks')) {
    $errorList[] = GetMessage('MODULE_SELF_ERROR');
}
if (!Loader::IncludeModule('iblock')) {
    $errorList[] = GetMessage('MODULE_IBLOCK_ERROR');
}
if (!Loader::IncludeModule('catalog')) {
    $errorList[] = GetMessage('MODULE_CATALOG_ERROR');
}
if (!Loader::IncludeModule('sale')) {
    $errorList[] = GetMessage('MODULE_SALE_ERROR');
}
if (!Loader::IncludeModule('highloadblock')) {
    $errorList[] = GetMessage('MODULE_HL_ERROR');
}
if (!Loader::IncludeModule('currency')) {
    $errorList[] = GetMessage('MODULE_CURRENCY_ERROR');
}

if (is_array($errorList) && count($errorList) > 0) {
    foreach ($errorList as $error) {
        CAdminMessage::ShowMessage($error);
    }
    $hasInitErrors = true;
}