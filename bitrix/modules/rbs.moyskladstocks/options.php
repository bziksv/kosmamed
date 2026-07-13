<?php

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use Bitrix\Main\Context;
use Rbs\MoyskladStocks\Utils;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
\Bitrix\Main\Localization\Loc::setCurrentLang('ru');

if (\Bitrix\Main\Loader::includeSharewareModule('rbs.moyskladstocks') === \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED) {
    CAdminMessage::ShowMessage([
        'MESSAGE' => GetMessage('DEMO_EXPIRED'),
        'TYPE' => 'OK',
        "HTML" => true
    ]);
    return;
}

$hasInitErrors = false;

$request = Context::getCurrent()->getRequest();
$mid = $module_id = $mid_orig = 'rbs.moyskladstocks';
$moduleAccessLevel = $APPLICATION->GetGroupRight($mid_orig);

include_once 'options/utils/error_check.php';
if ($hasInitErrors) {
    return;
}

\Bitrix\Main\UI\Extension::load("ui.hint");
\Bitrix\Main\UI\Extension::load("ui.alerts");
\Bitrix\Main\UI\Extension::load('ui.textcrop');

if(CJSCore::IsExtRegistered('jquery3')) {
    CJSCore::Init(['jquery3']);
} else {
    CJSCore::Init(['jquery']);
}

$profileId = 0;
\Rbs\MoyskladStocks\Config::setProfileId($profileId);
if ((int)$request->get('profile_id') > 0) {
    \Rbs\MoyskladStocks\Config::setProfileId((int)$request->get('profile_id'));
    $profileId = (int)$request->get('profile_id');
    $mid = $module_id = \Rbs\MoyskladStocks\Config::getModuleId();
}

include_once 'options/utils/functions.php';

if (\Rbs\MoyskladStocks\Config::isDemo()) {
    CAdminMessage::ShowMessage([
        'MESSAGE' => GetMessage('DEMO_WORK'),
        'TYPE' => 'OK',
        "HTML" => true
    ]);
}

if ($request->get("backup") === 'Y' && ($request->get("backup_create") === 'Y' || $request->get("backup_get") === 'Y')) :

    if ($request->get("backup_create") === 'Y') {
        include_once 'options/utils/backup_create.php';
    }

    if ($request->get("backup_get") === 'Y') {
        include_once 'options/utils/backup_get.php';
    }

    return;

else :

    include_once 'options/utils/init_standart_select_list.php';

    $arOptionsHardSet = [];
    $showAlertForSaveOption = [
        'select' => [],
        'checkbox' => [],
    ];
    //tabs
    $aTabs = [];
    $paramsCheckBox = '';

    if ($request->get('custom_process') === 'Y' && !empty($request->get('custom_process_name'))) {
        
        $customProcessList = \Rbs\MoyskladStocks\Internals\OptionUtils::getCustomProcessList();
        $requestedCustomProcessName = $request->get('custom_process_name');
        
        if(!in_array($requestedCustomProcessName, $customProcessList)) {
            
            CAdminMessage::ShowMessage([
                'MESSAGE' => GetMessage('PROCESS_NOT_FOUND')
            ]);
            return;
        }

        $customProcessName = $customProcessList[array_search($requestedCustomProcessName, $customProcessList)];
        $customProcessFileInclude = __DIR__ . '/options/custom/process/' . $customProcessName . '.php';
        
        if (file_exists($customProcessFileInclude)) {
            include_once $customProcessFileInclude;
        } else {
            CAdminMessage::ShowMessage([
                'MESSAGE' => GetMessage('EMPTY_PROCESS')
            ]);
        }
        return;
    }

    $profileSelectFileInclude = __DIR__ . '/options/utils/profile_select.php';
    if (file_exists($profileSelectFileInclude)) {
        include_once $profileSelectFileInclude;
    }

    $customFileInclude = __DIR__ . '/options/custom/before_options.php';
    if (file_exists($customFileInclude)) {
        include_once $customFileInclude;
    }

    if ($moduleAccessLevel === 'W') {
        //options
        $arAllOptions = [
            "main" => [

                GetMessage("MAIN_SETTINGS_DOC", ['#LINK#' => '/rbs-moyskladstocks/settings/main']),

                GetMessage("MAIN_SETTINGS_HEAD", ['#LINK#' => '/rbs-moyskladstocks/settings/main/auth']),
                ["login", GetMessage("MAIN_SETTINGS_LOGIN"), "", ['text', 30]],
                ["pass", GetMessage("MAIN_SETTINGS_PASS"), "", ['text', 30]],
                ["hr", "<hr>", '', ['statichtml']],
                ["token", GetMessage("MAIN_SETTINGS_TOKEN"), "", ['text', 30]],
                ['note' => GetMessage("MAIN_SETTINGS_AUTH_NOTE", ['#LINK#' => '/rbs-moyskladstocks/settings/main/backup'])],

                GetMessage("MAIN_SETTINGS_ENABLAED", ['#LINK#' => '/rbs-moyskladstocks/settings/main/work']),
                ["global_enabled", GetMessage("MAIN_ENABLED"), '', ['checkbox', "N", $paramsCheckBox]],
                ["user_id", GetMessage('USER_ID'), $USER->GetId(), ['text', 30]],

            ]
        ];
    } else {
        $paramsCheckBox = 'disabled';
        $arAllOptions = [
            "main" => [
                GetMessage("MAIN_SETTINGS_ENABLAED", ['#LINK#' => '/rbs-moyskladstocks/settings/main/work']),
                ["global_enabled", GetMessage("MAIN_ENABLED"), '', ['checkbox', "N", $paramsCheckBox]],
                ["user_id", GetMessage('USER_ID'), $USER->GetId(), ['text', 30]]
            ]
        ];
    }

    $aTabs[] = ["DIV" => "main", "TAB" => GetMessage("MAIN_SETTINGS_MAIN"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_SETTINGS_MAIN_DESCR")];

    //save for auth settings before api available check
    $hideSaveBtn = false;
    $isApiAvailable = false;
    $isSaveHit = $request->isPost() && check_bitrix_sessid() && $Update;
    if ($isSaveHit) {

        if ($moduleAccessLevel === "W") {
            foreach ($arAllOptions['main'] as $option) {
                if($option[0] === 'global_enabled'){
                    $enabled = $request->get('global_enabled') === 'Y';
                    \Rbs\MoyskladStocks\Internals\ModuleWorkSwitcher::switchModuleWork($enabled, \Rbs\MoyskladStocks\Internals\ModuleWorkSwitcher::REASON_OPTION_CHANGE);
                } else {
                    __AdmSettingsSaveOption($mid, $option);
                }
            }
        } else {
            CAdminMessage::ShowMessage([
                'MESSAGE' => GetMessage('ACCESS_WRITE_ERROR')
            ]);
        }
        include_once 'options/utils/check_api.php';
    } else {
        include_once 'options/utils/check_api.php';
    }

    if ($isApiAvailable) {

        if ($request->get('process') === 'Y') {

            $processList = \Rbs\MoyskladStocks\Internals\OptionUtils::getImportOnceProcessList();
            $requestedProcessName = $request->get('process_name');

            if(!in_array($requestedProcessName, $processList)) {
                CAdminMessage::ShowMessage([
                    'MESSAGE' => GetMessage('PROCESS_NOT_FOUND')
                ]);
                return;
            }

            $processName = $processList[array_search($requestedProcessName, $processList)];
            $processFileInclude = __DIR__ . '/options/process/' . $processName . '.php';
            if (mb_strpos($processName, 'import_once_') === 0) {
                $processEntity = str_replace('import_once_', '', $processName);
                $processFileInclude = __DIR__ . '/options/process/import_once.php';
            }
            if (file_exists($processFileInclude)) {
                include_once $processFileInclude;
            } else {
                CAdminMessage::ShowMessage([
                    'MESSAGE' => GetMessage('EMPTY_PROCESS')
                ]);
            }
            return;
        }

        include_once 'options/utils/init_ms_fields.php';

        include_once 'options/tabs/hlcache.php';
        include_once 'options/tabs/webhook.php';
        include_once 'options/tabs/stocks.php';
        include_once 'options/tabs/current_stocks.php';
        include_once 'options/tabs/prices.php';
        include_once 'options/tabs/discount.php';
        include_once 'options/tabs/entity_tabs.php';
        include_once 'options/tabs/others.php';

        $event = new \Bitrix\Main\Event(\Rbs\MoyskladStocks\Config::getModuleId(true), "OnBeforeAllOptionsBuild", array(
            'arAllOptions' => $arAllOptions,
            'aTabs' => $aTabs,
            'isSaveHit' => $isSaveHit
        ));

        $event->send();

        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $eventParams = $eventResult->getParameters();
                    if (isset($eventParams['arAllOptions'])) {
                        $arAllOptions = $eventParams['arAllOptions'];
                    }
                    if (isset($eventParams['aTabs'])) {
                        $aTabs = $eventParams['aTabs'];
                    }
                    if (isset($eventParams['assetsFile']) && file_exists($eventParams['assetsFile'])) {
                        $fileInfo = pathinfo($eventParams['assetsFile']);
                        if ($fileInfo['extension'] === 'php') {
                            include_once $eventParams['assetsFile'];
                        }
                    }
                }
            }
        }

        //update settings
        if ($request->isPost() && $Update && check_bitrix_sessid() && $moduleAccessLevel === 'W') {
            
            COption::RemoveOption($mid);
            foreach ($arAllOptions as $key => $section) {
                foreach ($section as $option) {
                    __AdmSettingsSaveOption($mid, $option);
                }
            }

            foreach (\Rbs\MoyskladStocks\Services\StocksUtils::getStocksTypes() as $stocksType) {
                if (\Rbs\MoyskladStocks\Services\StocksUtils::isStocksWithChild($stocksType)) {
                    \Rbs\MoyskladStocks\Services\StocksUtils::updateParentStoresChildTree($stocksType);
                }
            }

        }
    }

    if (Utils::is_count($arOptionsHardSet)) {
        foreach ($arOptionsHardSet as $option => $value) {
            \Rbs\MoyskladStocks\Config::setOption($option, $value);
        }
    }

    if (\Rbs\MoyskladStocks\Config::getProfileId() <= 0) {
        $aTabs[] = [
            "DIV" => "rights",
            "TAB" => GetMessage("MAIN_TAB_RIGHTS"),
            "ICON" => "rights_settings",
            "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")
        ];
    }

    $tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

    function ShowParamsHTMLByArray($arParams)
    {
        $mid = \Rbs\MoyskladStocks\Config::getModuleId();
        foreach ($arParams as $Option) {
            if (
                (is_array($Option) && $Option[0] !== "" && $Option[2] !== "") &&
                (!isset($Option['note']))
            ) {
                $optStr = COption::GetOptionString($mid, $Option[0], $Option[2]);
                if ($optStr == null) {
                    COption::SetOptionString($mid, $Option[0], $Option[2]);
                }
            }
            __AdmSettingsDrawRow($mid, $Option);
        }
    }

    $actionStr = $APPLICATION->GetCurPage() . "?mid={$mid_orig}&lang=" . LANG;
    if (\Rbs\MoyskladStocks\Config::getProfileId() > 0) {
        $actionStr .= "&profile_id=" . \Rbs\MoyskladStocks\Config::getProfileId();
    }
?>
    <?php
        $assetsLoader = \Rbs\MoyskladStocks\Services\AssetsLoader::getInstance('license_checker');
        echo $assetsLoader->renderApp();
    ?>
    <? include_once 'options/js_libs/select2/css.php'; ?>
    <? include_once 'options/styles.php'; ?>
    <form name="main_options" method="POST" id="rbs_moyskladstocks_option_form" action="<? echo $actionStr ?>" novalidate>
        <?= bitrix_sessid_post() ?>
        <?php
        $tabControl->Begin();
        $tabControl->BeginNextTab();

        ShowParamsHTMLByArray($arAllOptions['main']);

        if ($isApiAvailable) {
            foreach ($arAllOptions as $blockId => $blockOptions) {
                if ($blockId === 'main') {
                    continue;
                }
                if (!empty($blockOptions)) {
                    $tabControl->BeginNextTab();
                    ShowParamsHTMLByArray($blockOptions);
                }
            }
        }

        if (\Rbs\MoyskladStocks\Config::getProfileId() <= 0) {
            $tabControl->BeginNextTab();
            require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");
        }

        $tabControl->Buttons(); ?>

        <? if (!$isApiAvailable) : ?>
            <input type="submit" name="UpdateAuth" value="<?= GetMessage("SAVE_AUTH") ?>" class="adm-btn-save">
        <? endif ?>

        <input type="submit" name="Update" value="<? echo GetMessage("MAIN_SAVE") ?>" title="<? echo GetMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save" <?= ($moduleAccessLevel !== 'W' || $hideSaveBtn || !$isApiAvailable) ? 'disabled' : ''; ?>>

        <div class="despi-save-alert-around-save-btn despi-alert-hide">
            &#8592; <?= GetMessage('SAVE_PARAMS_FOR_NEXT_ACTIONS'); ?>
        </div>

        <? $tabControl->End(); ?>

    </form>
    <? include_once 'options/js_libs/select2/js.php'; ?>
    <? include_once 'options/script.php'; ?>
    <? include_once 'options/js_agent_controller.php'; ?>
<? endif ?>