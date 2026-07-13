<?php

/**
 * $entity === 'productfolder'
 */

use Rbs\MoyskladStocks\Internals\OptionUtils;
use Rbs\MoyskladStocks\Config;

$arAllOptions['import' . $entity][] = GetMessage('ALL_PARAMS', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/main']);

$iblockId = $_REQUEST["im_{$entity}_iblock"] ?: Config::getIblockId($entity);

$arAllOptions['import' . $entity][] = ["im_{$entity}_iblock", GetMessage('IBLOCK_ID'), '', ['selectbox', $selectCatalog]];
$showAlertForSaveOption['select'][] = "im_{$entity}_iblock";

if ($iblockId > 0 && !empty($arIblockTypeList[$iblockId])) {
    $arAllOptions['import' . $entity][] = ['note' => GetMessage('IBLOCK_NAVIGATION', [
        '#IBLOCK_ID#' => $iblockId,
        '#IBLOCK_TYPE#' => $arIblockTypeList[$iblockId]
    ])];
}

if ($iblockId > 0) {
    $allSections = \Bitrix\Iblock\SectionTable::getList(['filter' => ['IBLOCK_ID' => $iblockId, 'DEPTH_LEVEL' => 1]])->fetchAll();
    if (count($allSections) > 0) {
        $selectSection = ['N' => GetMessage('ROOT_SECTION')];
        foreach ($allSections as $section) {
            $selectSection[$section['ID']] = "[{$section['ID']}] {$section['NAME']}";
        }
        $arAllOptions['import' . $entity][] = ["im_{$entity}_section", GetMessage('SECTION_ID'), '', ['selectbox', $selectSection]];
    }
}

$arAllOptions['import' . $entity][] = GetMessage('IMPORT_NEW', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/base']);

$arAllOptions['import' . $entity][] = ["im_{$entity}_enable", GetMessage('ENABLE_IMPORT'), '', ['checkbox', 'N', $paramsCheckBox]];

$arAllOptions['import' . $entity][] = ["im_{$entity}_p_ms_section_root", GetMessage('IMPORT_PARAMS_MS_SECTION_ROOT'), '', ['checkbox', "N", $paramsCheckBox]];

$arAllOptions['import' . $entity][] = ["im_{$entity}_p_include_archived", GetMessage('IMPORT_PARAMS_INCLUDE_ARCHIVED_PFOLDERS'), '', ['checkbox', "N", $paramsCheckBox]];

$arAllOptions['import' . $entity][] = ["im_{$entity}_p_ignore_section_active", GetMessage('IMPORT_PARAMS_IGNORE_SECTION_ACTIVE'), '', ['checkbox', "N", $paramsCheckBox]];

if (count($selectGroup) > 0) {
    $arAllOptions['import' . $entity][] = GetMessage('FILTER_SECTION');
    $arAllOptions['import' . $entity][] = ["im_{$entity}_group", GetMessage('GROUP_ID'), '', ['selectbox', $selectGroup]];
}

$arAllOptions['import' . $entity][] = GetMessage('IMPORT_FIELDS_PARAMS', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/fields']);

//DESCRIPTION
$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_DESCR'), '', ['statichtml']];
$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr_type", GetMessage('IMPORT_PARAMS_DESCR_TYPE_TEXT'), '', ['selectbox', $descrTextTypes]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr", GetMessage('HOOK_PARAMS_DESCR'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr_delete", GetMessage('DELETE_DESCR_IF_EMPTY_IN_MS'), '', ['checkbox', "N", $paramsCheckBox]];

//SYM_CODE
$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_SYM_CODE'), '', ['statichtml']];

if ($iblockId > 0) {
    $requiredSecCodeParams = \Rbs\MoyskladStocks\Services\ConfigurationUtils::getIblockSymbolicCodeParams((int)$iblockId, 'SECTION_CODE');
    if ($requiredSecCodeParams['required']) {
        $arOptionsHardSet["im_{$entity}_p_code"] = 'Y';
        $arAllOptions['import' . $entity][] = ["im_{$entity}_p_code", GetMessage('HOOK_PARAMS_CODE_GEN'), 'Y', ['checkbox', "Y", 'disabled']];
    } else {
        $arAllOptions['import' . $entity][] = ["im_{$entity}_p_code", GetMessage('HOOK_PARAMS_CODE_GEN'), 'Y', ['checkbox', "N", $paramsCheckBox]];
    }
    if ($requiredSecCodeParams['uniq']) {
        $arOptionsHardSet["im_{$entity}_p_code_uniq"] = 'Y';
        $arAllOptions['import' . $entity][] = ["im_{$entity}_p_code_uniq", GetMessage('HOOK_PARAMS_CODE_UNIQ'), 'Y', ['checkbox', "Y", 'disabled']];
    } else {
        $arAllOptions['import' . $entity][] = ["im_{$entity}_p_code_uniq", GetMessage('HOOK_PARAMS_CODE_UNIQ'), 'Y', ['checkbox', "N", $paramsCheckBox]];
    }
} else {
    $arAllOptions['import' . $entity][] = ["im_{$entity}_p_code", GetMessage('HOOK_PARAMS_CODE_GEN'), 'Y', ['checkbox', "N", $paramsCheckBox]];
    $arAllOptions['import' . $entity][] = ["im_{$entity}_p_code_uniq", GetMessage('HOOK_PARAMS_CODE_UNIQ'), 'Y', ['checkbox', "N", $paramsCheckBox]];
}

$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code_uniq_parent", GetMessage('HOOK_PARAMS_CODE_UNIQ_PARENT'), '', ['checkbox', "N", $paramsCheckBox]];

$arAllOptions['import' . $entity][] = ["im_{$entity}_p_translit", GetMessage('HOOK_PARAMS_CODE_IBLOCK'), '', ['checkbox', "N", $paramsCheckBox]];


$arAllOptions['import' . $entity][] = GetMessage('IMPORT_UPDATE_SECTION', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/import-update']);

$arAllOptions['import' . $entity][] = ["im_{$entity}_up_name", GetMessage('HOOK_PARAMS_NAME'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_up_code", GetMessage('HOOK_PARAMS_CODE'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_up_structure", GetMessage('HOOK_PARAMS_STRUCTURE'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_up_archived", GetMessage('HOOK_PARAMS_ARCHIVED_PF'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_up_descr", GetMessage('HOOK_PARAMS_DESCR'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["{$entity}_delete_action", GetMessage('UP_PARAMS_DELETE_GROUP'), 'CREATE', ['selectbox', [
    'DEACTIVATE' => GetMessage('DEACTIVATE'),
    'DELETE' => GetMessage('DELETE'),
]]];

//HOOK
$arAllOptions['import' . $entity][] = GetMessage('IMPORT_HEAD_HOOK_SECTION', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/import-hook']);

$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_name", GetMessage('HOOK_PARAMS_NAME'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_code", GetMessage('HOOK_PARAMS_CODE'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_structure", GetMessage('HOOK_PARAMS_STRUCTURE'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_outer_sec", GetMessage('IMPORT_PARAMS_SECTION_OUTER_PF'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_archived", GetMessage('HOOK_PARAMS_ARCHIVED_PF'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_descr", GetMessage('HOOK_PARAMS_DESCR'), '', ['checkbox', "N", $paramsCheckBox]];

$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('HOOK_PARAMS_EVENTS'), '', ['statichtml']];
$arAllOptions['import' . $entity][] = ["{$entity}_create_hook", GetMessage('HOOK_PARAMS_CREATE_ALWAYS'), 'CREATE', ['selectbox', [
    'CREATE' => GetMessage('HOOK_CREATE'),
    'ALL' => GetMessage('HOOK_ALL'),
]]];
$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_delete", GetMessage('HOOK_PARAMS_DELETE_GROUP'), '', ['checkbox', "N", $paramsCheckBox]];

//agent
OptionUtils::buildAgentOptionArray($arAllOptions['import' . $entity], $entity, $paramsCheckBox, true, ['limit', 'full_once', 'full_time', 'updated', 'last_update']);
if ($isSaveHit) {
    OptionUtils::saveAgentAction($entity, "import_{$entity}");
}

OptionUtils::buildImportOnceButton($arAllOptions['import' . $entity], 'import_once_productfolder');

if (!empty($arAllOptions['import' . $entity])) {
    $aTabs[] = [
        "DIV" => "import_" . $entity,
        "TAB" => GetMessage('IMPORT_HEAD_' . $entity),
        "ICON" => "order_settings",
        "TITLE" => GetMessage('IMPORT_HEAD_' . $entity)
    ];
}