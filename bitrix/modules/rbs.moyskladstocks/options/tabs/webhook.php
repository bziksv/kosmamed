<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Rbs\MoyskladStocks\Utils;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$needWebHooks = [
   'product' => ['CREATE', 'UPDATE', 'DELETE'],
   'variant' => ['CREATE', 'UPDATE', 'DELETE'],
   'bundle' =>  ['CREATE', 'UPDATE', 'DELETE'],
   'service' => ['CREATE', 'UPDATE', 'DELETE'],
   'productfolder' => ['CREATE', 'UPDATE', 'DELETE'],
   'specialpricediscount' => ['CREATE', 'UPDATE']
];

$event = new \Bitrix\Main\Event(\Rbs\MoyskladStocks\Config::getModuleId(true), "OnBeforeWebHookOptionsBuild", []);

$event->send();

if ($event->getResults()) {
    foreach ($event->getResults() as $eventResult) {
        if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
            $eventParams = $eventResult->getParameters();
            if (isset($eventParams['result']) && is_array($eventParams['result'])) {
                $needWebHooks = array_merge($needWebHooks, $eventParams['result']);
            }
        }
    }
}

$entitiesWebHook = array_keys($needWebHooks);
if (Utils::is_count($_REQUEST['whd_delete'])) {
    foreach ($_REQUEST['whd_delete'] as $whId => $val) {
        \Rbs\MoyskladStocks\ApiNew::delete('/entity/webhook/' . $whId);
    }
    unset($_REQUEST['whd_delete']);
}

$webhook = \Rbs\MoyskladStocks\ApiNew::get('/entity/webhook', ['limit' => 1000]);
$hooks = [];
if (Utils::is_success($webhook) && Utils::array_exists($webhook)) {
    foreach ($webhook->rows as $row) {
        if (in_array($row->entityType, $entitiesWebHook)) {
            if (in_array($row->action, $needWebHooks[$row->entityType])) {
                $hooks[$row->entityType][$row->action][$row->id] = [
                   'URL' => $row->url,
                   'ACTIVE' => $row->enabled ? 'Y' : 'N',
                   'ID' => $row->id
               ];
            }
        }
    }
}

$arAllOptions['webhook'][] = GetMessage("MAIN_SETTINGS_DOC", ['#LINK#' => '/rbs-moyskladstocks/settings/web-hooks']);
$arAllOptions['webhook'][] = GetMessage('MAIN_SETTINGS_WEBHOOK_URL', ['#LINK#' => '/rbs-moyskladstocks/settings/web-hooks/url']);

$protocol = $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$httpHost = str_replace([':443', ':80'], '', $_SERVER['HTTP_HOST']);

$arAllOptions['webhook'][] = ["webhook_url", GetMessage("MAIN_SETTINGS_WEBHOOK_OPTION_URL"), $protocol . $httpHost . '/mshooks/hookstocks.php', ['text', 30]];
$arAllOptions['webhook'][] = ["webhook_url_salt", GetMessage("MAIN_SETTINGS_WEBHOOK_OPTION_URL_SALT"), md5(serialize([$httpHost, time()])), ['text', 30]];

$arAllOptions['webhook'][] = GetMessage('MAIN_SETTINGS_WEBHOOK_LIMITS');
$arAllOptions['webhook'][] = ["webhook_limit_count", GetMessage('MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT'), '', ['selectbox', [
    5 => 5,
    10 => 10,
    15 => 15,
    20 => 20,
    25 => 25,
]]];

$arAllOptions['webhook'][] = ["webhook_limit_count_interval", GetMessage('MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT_INTERVAL'), '200', ['text', 30]];
$arAllOptions['webhook'][] = ['note' => GetMessage("MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT_NOTE")];

foreach ($needWebHooks as $entity => $events) {
    foreach ($events as $eventName) {
        $arAllOptions['webhook'][] = GetMessage('HOOK_HEAD_EVENT', ['#EVENT#' => $eventName, '#ENTITY#' => $entity, '#LINK#' => '/rbs-moyskladstocks/settings/web-hooks/structure']);

        $webHookOptions = [
            'url' => $_REQUEST['webhook_url'] . '?checkUrl=' . $_REQUEST['webhook_url_salt'],
            'action' => $eventName,
            'entityType' => $entity
      ];

        if (\Rbs\MoyskladStocks\Config::getProfileId() > 0) {
            $webHookOptions['url'] .= '&profile_id=' . \Rbs\MoyskladStocks\Config::getProfileId();
        }

        //UPDATE
        if (Utils::is_count($_REQUEST['whd_update'])) {
            if (isset($hooks[$entity][$eventName])) {
                foreach ($hooks[$entity][$eventName] as $hookId => $hookInfo) {
                    if (in_array($hookId, array_keys($_REQUEST['whd_update']))) {
                        \Rbs\MoyskladStocks\ApiNew::put('/entity/webhook/' . $hookId, $webHookOptions);
                        $hooks[$entity][$eventName][$hookId]['URL'] = $webHookOptions['url'];
                        unset($_REQUEST['whd_update'][$hookId]);
                    }
                }
            }
        }

        //CREATE
        if ($_REQUEST["webhook_{$entity}_{$eventName}"] === 'Y') {
            $webhookResult = \Rbs\MoyskladStocks\ApiNew::post('/entity/webhook', $webHookOptions);

            if (empty($webhookResult->errors)) {
                $hooks[$entity][$eventName][$webhookResult->id] = [
                  'ID' => $webhookResult->id,
                  'URL' => $webhookResult->url
               ];
            } else {
                foreach($webhookResult->errors as $errorHook){
                    CAdminMessage::ShowMessage([
                        'MESSAGE' => $errorHook,
                        "HTML" => true
                    ]);
                }
            }

            unset($_REQUEST["webhook_{$entity}_{$eventName}"]);
        }

        if (isset($hooks[$entity][$eventName])) {
            foreach ($hooks[$entity][$eventName] as $hookInfo) {
                $arAllOptions['webhook'][] = ["note" => $hookInfo['URL']];
                $arAllOptions['webhook'][] = ["whd_update[{$hookInfo['ID']}]", GetMessage('UPDATE'), 'N', ['checkbox', 'N', $paramsCheckBox]];
                $arAllOptions['webhook'][] = ["whd_delete[{$hookInfo['ID']}]", GetMessage('DELETE'), 'N', ['checkbox', 'N', $paramsCheckBox]];
            }
        } else {
            $arAllOptions['webhook'][] = ['note' => GetMessage("MAIN_SETTINGS_WEBHOOK_OPTION_NEW", ['#ENTITY#' => $entity, '#EVENT#' => $eventName])];
        }
        $arAllOptions['webhook'][] = ["hooks", "<hr>", '', ['statichtml']];
        $arAllOptions['webhook'][] = ["webhook_{$entity}_{$eventName}", GetMessage("CREATE"), 'N', ['checkbox', 'N', $paramsCheckBox]];
    }
}

$aTabs[] = ["DIV" => "webhook", "TAB" => GetMessage("WEBHOOK"), "ICON" => "main_settings", "TITLE" => GetMessage("MAIN_SETTINGS_WEBHOOK")];
