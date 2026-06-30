<?php

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */

use \Bitrix\Main\Loader;
use \Bitrix\Main\Context;

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\Core\Tasker\Core\TaskerTable;
use \Rbs\Moysklad\Core\Tasker\Core\TaskerInstall;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");


if (Loader::includeSharewareModule('rbs.moysklad') === Loader::MODULE_DEMO_EXPIRED) {
    CAdminMessage::ShowMessage([
        'MESSAGE' => GetMessage('DEMO_EXPIRED'),
        'TYPE' => 'OK',
        "HTML" => true
    ]);
    return;
}

$showAlertForSaveCheckbox = [];
$showAlertForSaveSelect = [];
$hasInitErrors = false;

$request = Context::getCurrent()->getRequest();
$mid = $module_id = $mid_orig = 'rbs.moysklad';
$moduleAccessLevel = $APPLICATION->GetGroupRight($mid);

include 'options/utils/error_check.php';
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


$GLOBALS['rbsMsOrdersProfile'] = 0;
if ((int)$_REQUEST['profile_id'] > 0) {
    \Rbs\Moysklad\Config::setProfileId((int)$_REQUEST['profile_id']);
    $mid = $module_id = \Rbs\Moysklad\Config::getModuleId();
}

include 'options/utils/functions.php';

if (\Rbs\Moysklad\Config::isDemo()) {
    CAdminMessage::ShowMessage([
        'MESSAGE' => GetMessage('DEMO_WORK'),
        'TYPE' => 'OK',
        "HTML" => true
    ]);
}

if ($request->get("backup") === 'Y' && ($request->get("backup_create") === 'Y' || $request->get("backup_get") === 'Y')) :

    if ($request->get("backup_create") === 'Y') {
        include 'options/utils/backup_create.php';
    }

    if ($request->get("backup_get") === 'Y') {
        include 'options/utils/backup_get.php';
    }

    return;

else :

    $arOptionsHardSet = [];

    //tabs
    $aTabs = [];
    $paramsCheckBox = '';

    $customFileInclude = __DIR__ . '/options/custom/before_options.php';
    if (file_exists($customFileInclude)) {
        include_once $customFileInclude;
    }

    if ($moduleAccessLevel === 'W') {
        $arAllOptions = [
            "main" => [
                GetMessage("MAIN_SETTINGS_HEAD"),
                ["login", GetMessage("MAIN_SETTINGS_LOGIN"), "", ['text', 30]],
                ["pass", GetMessage("MAIN_SETTINGS_PASS"), "", ['text', 30]],
                ["hr", "<hr>", '', ['statichtml']],
                ["token", GetMessage("MAIN_SETTINGS_TOKEN"), "", ['text', 30]],
                ['note' => GetMessage("MAIN_SETTINGS_AUTH_NOTE", ['#NAME#' => $table['NAME'], '#ID#' => $table['ID']])],
                GetMessage("MAIN_SETTINGS_ENABLAED"),
                ["global_enabled", GetMessage("MAIN_ENABLED"), '', ['checkbox', "N"]],
                ["ex_type", GetMessage("EX_TYPE"), 'API', ['selectbox', [/* 'STANDART' => GetMessage('STANDART_TYPE'), */'API' => GetMessage('API_TYPE')]]],
                ["user_id", GetMessage('USER_ID'), $USER->GetId(), ['text', 30]]
            ]
        ];
    } else {
        $paramsCheckBox = 'disabled';
        $arAllOptions = [
            "main" => [
                GetMessage("MAIN_SETTINGS_ENABLAED"),
                ["global_enabled", GetMessage("MAIN_ENABLED"), '', ['checkbox', "N", $paramsCheckBox]]
            ]
        ];
    }

    //tabs
    $aTabs = [
        [
            "DIV" => "main",
            "TAB" => GetMessage("MAIN_SETTINGS_MAIN"),
            "ICON" => "main_settings",
            "TITLE" => GetMessage("MAIN_SETTINGS_MAIN_DESCR")
        ]
    ];

    //save for auth settings before api available check
    $isApiAvailable = false;
    $hideSaveBtn = false;
    $isSaveHit = $request->isPost() && check_bitrix_sessid() && $Update;
    if ($isSaveHit) {

        if(\Rbs\Moysklad\Config::getProfileId() === 0) {
            \Rbs\Moysklad\Agent::set("check_module_agents();");
        }

        \Rbs\Moysklad\Agent::set("clear_logs();", 100, 86400);
        
        \Rbs\Moysklad\Agent::delete('check_orders_from_ms();');

        try {
            if(!TaskerInstall::isInstalled()) {
                TaskerInstall::install();
            }
            if(TaskerInstall::isInstalled()) {
                \Rbs\Moysklad\Agent::set("webhook_tasker_worker();");
            } else {
                throw new \Exception('Error installing TaskerTable');
            }
        } catch (\Throwable $e) {
            CAdminMessage::ShowMessage([
                'MESSAGE' => $e->getMessage(),
                'TYPE' => 'ERROR',
                "HTML" => true
            ]);
        }
        

        if ($moduleAccessLevel === "W") {
            foreach ($arAllOptions['main'] as $option) {
                __AdmSettingsSaveOption($mid, $option);
            }
        } else {
            CAdminMessage::ShowMessage([
                'MESSAGE' => GetMessage('ACCESS_WRITE_ERROR')
            ]);
        }
        include 'options/utils/check_api.php';
    } else {
        include 'options/utils/check_api.php';
    }



    if ($isApiAvailable) {

        if ($request->get('process') === 'Y' && !empty($request->get('process_name'))) {
            $processFileInclude = __DIR__ . '/options/process/' . $request->get('process_name') . '.php';
            if (mb_strpos($request->get('process_name'), 'import_once_') === 0) {
                $processEntity = str_replace('import_once_', '', $request->get('process_name'));
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

        include 'options/utils/init_standart_bx_fields.php';
        include 'options/utils/init_standart_ms_fields.php';

        //WEBHOOK_TAB
        include 'options/tabs/webhook.php';
        include 'options/tabs/export.php';
        include 'options/tabs/import.php';
        include 'options/tabs/status.php';
        include 'options/tabs/basket.php';
        include 'options/tabs/props.php';
        include 'options/tabs/counterparty.php';
        include 'options/tabs/payments.php';
        include 'options/tabs/delivery.php';

        include 'options/tabs/service_settnigs.php';

        $event = new \Bitrix\Main\Event(\Rbs\Moysklad\Config::getModuleId(true), "OnBeforeAllOptionsBuild", array(
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

        if ($request->isPost() && $Update && check_bitrix_sessid() && $moduleAccessLevel === 'W') {
            COption::RemoveOption($mid);
            foreach ($_REQUEST as $key => $value) {
                if (
                    mb_strpos($key, 'delivery_sync_prop_')  === 0 ||
                    mb_strpos($key, 'pays_sync_prop_')      === 0 ||
                    mb_strpos($key, 'counter_fields_')      === 0 ||
                    mb_strpos($key, 'prop_bx_')             === 0
                ) {
                    if ($value === 'N') {
                        unset($_REQUEST[$key]);
                        $deleted[$key] = $value;
                    }
                }
            }
            foreach ($arAllOptions as $key => $section) {
                foreach ($section as $option) {
                    __AdmSettingsSaveOption($mid, $option);
                }
            }
        }
    }   

    if (Utils::is_count($arOptionsHardSet)) {
        foreach ($arOptionsHardSet as $option => $value) {
            \Rbs\Moysklad\Config::setOption($option, $value);
        }
    }

    $aTabs[] = [
        "DIV" => "log_window",
        "TAB" => GetMessage("LOG"),
        "ICON" => "order_settings",
        "TITLE" => GetMessage("LOG")
    ];

    if (\Rbs\Moysklad\Config::getProfileId() === 0) {
        $aTabs[] = [
            "DIV" => "rights",
            "TAB" => GetMessage("MAIN_TAB_RIGHTS"),
            "ICON" => "rights_settings",
            "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")
        ];
    }

    $tabControl = new CAdminTabControl("tabControl", $aTabs);
?>

    <?php
        $assetsLoader = \Rbs\Moysklad\Services\AssetsLoader::getInstance('license_checker');
        echo $assetsLoader->renderApp();
    ?>

    <? include_once 'options/style.php'; ?>

    <form name="<?= \Rbs\Moysklad\Internals\OptionPageParams::getStaticHtmlNames()['option_form_name'] ?>" id="<?= \Rbs\Moysklad\Internals\OptionPageParams::getStaticHtmlNames()['option_form_id'] ?>" method="POST" action="<?= \Rbs\Moysklad\Internals\OptionPageParams::getActionStr() ?>">
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

        $tabControl->BeginNextTab();
        include 'custom_opt_tabs/logs.php';

        if (\Rbs\Moysklad\Config::getProfileId() === 0) {
            $tabControl->BeginNextTab();
            require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");
        }

        $tabControl->Buttons(); ?>

        <? if (!$isApiAvailable) : ?>
            <input type="submit" name="<?= \Rbs\Moysklad\Internals\OptionPageParams::getStaticHtmlNames()['option_auth_btn_name'] ?>" value="<?= GetMessage("SAVE_AUTH") ?>" class="adm-btn-save">
        <? endif ?>

        <input <? if ($moduleAccessLevel !== 'W' || $hideSaveBtn || !$isApiAvailable) echo "disabled" ?> type="submit" name="<?= \Rbs\Moysklad\Internals\OptionPageParams::getStaticHtmlNames()['option_save_btn_name'] ?>" value="<?= GetMessage("MAIN_SAVE") ?>" title="<?= GetMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save">

        <div class="save-alert-around-save-btn alert-hide">
            &#8592; <?= GetMessage('SAVE_PARAMS_FOR_NEXT_ACTIONS'); ?>
        </div>

        <? $tabControl->End(); ?>

    </form>

    <? include_once 'options/script.php'; ?>

<? endif ?>