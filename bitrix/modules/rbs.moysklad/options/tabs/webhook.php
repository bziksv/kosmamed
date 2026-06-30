<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

use \Rbs\Moysklad\Utils;

$needWebHooks = [
   'customerorder' => ['CREATE', 'UPDATE'/* , 'DELETE' */],
   'paymentin' => ['CREATE', 'UPDATE', 'DELETE'],
   'cashin' =>  ['CREATE', 'UPDATE', 'DELETE'],
   'demand' =>  ['CREATE', 'UPDATE'/* , 'DELETE' */],
];

$event = new \Bitrix\Main\Event(\Rbs\Moysklad\Config::getModuleId(true), "OnBeforeWebHookOptionsBuild", []);

$event->send();

if ($event->getResults()){
   foreach($event->getResults() as $eventResult){
      if($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS){
         $eventParams = $eventResult->getParameters();
         if(isset($eventParams['result']) && is_array($eventParams['result'])){
            $needWebHooks = array_merge($needWebHooks, $eventParams['result']);
         }             
      }
   }
}

$entitiesWebHook = array_keys($needWebHooks);
if(isset($_REQUEST['whd_delete']) && Utils::is_count($_REQUEST['whd_delete'])){
   foreach($_REQUEST['whd_delete'] as $whId => $val){
       \Rbs\Moysklad\ApiNew::delete('/entity/webhook/' . $whId);
   }
   unset($_REQUEST['whd_delete']);
}

$webhook = \Rbs\Moysklad\ApiNew::get('/entity/webhook');
$hooks = [];
if(Utils::is_success($webhook) && Utils::array_exists($webhook)){
   foreach($webhook->rows as $row){
       if(in_array($row->entityType, $entitiesWebHook)){
           if(in_array($row->action, $needWebHooks[$row->entityType])){
               $hooks[$row->entityType][$row->action][$row->id] = [
                   'URL' => $row->url,
                   'ID' => $row->id,
                   'ENABLED_STATUS' => $row->enabled ? 'Y' : 'N',
               ];
           }
       }
   }
}

$arAllOptions['webhook'][] = GetMessage('MAIN_SETTINGS_WEBHOOK_URL');
   
$protocol = $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$httpHost = str_replace([':443', ':80'], '', $_SERVER['HTTP_HOST']);

$arAllOptions['webhook'][] = ["webhook_url", GetMessage("MAIN_SETTINGS_WEBHOOK_OPTION_URL"), $protocol . $httpHost . '/mshooks/hook.php', ['text', 30]];
$arAllOptions['webhook'][] = ["webhook_url_salt", GetMessage("MAIN_SETTINGS_WEBHOOK_OPTION_URL_SALT"), md5(serialize([$httpHost, time()])), ['text', 30]];

$arAllOptions['webhook'][] = GetMessage('MAIN_SETTINGS_WEBHOOK_LIMITS');
$arAllOptions['webhook'][] = ["webhook_limit_count", GetMessage('MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT'), '', ['selectbox', Utils::build_number_array(5, 25, 5)]];
$arAllOptions['webhook'][] = ['note' => GetMessage("MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT_NOTE")];
$arAllOptions['webhook'][] = ["webhook_limit_tasker", GetMessage('MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT_TASKER'), 20, ['selectbox', Utils::build_number_array(10, 50, 5)]];
$arAllOptions['webhook'][] = ['note' => GetMessage("MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT_TASKER_NOTE")];

$webhookExecutionTypes = [
    'instant' => GetMessage('WEBHOOK_EXECUTION_TYPE_INSTANT'),
    'queue' => GetMessage('WEBHOOK_EXECUTION_TYPE_QUEUE'),
    'mixed' => GetMessage('WEBHOOK_EXECUTION_TYPE_MIXED')
];

foreach($needWebHooks as $entity => $events){

   foreach($events as $eventName){

      $arAllOptions['webhook'][] = GetMessage('HOOK_HEAD_EVENT', ['#EVENT#' => $eventName, '#ENTITY#' => $entity]);

      $arAllOptions['webhook'][] = ["webh_execution_{$entity}_{$eventName}", GetMessage('WEBHOOK_EXECUTION_TYPE'), 'instant', ['selectbox', $webhookExecutionTypes]];

      $webHookOptions = [
            'url' => $_REQUEST['webhook_url'] . '?checkUrl=' . $_REQUEST['webhook_url_salt'],
            'action' => $eventName,
            'entityType' => $entity
      ];

      if((int)\Rbs\Moysklad\Config::getProfileId() > 0){
         $webHookOptions['url'] .= '&profile_id=' . (int)\Rbs\Moysklad\Config::getProfileId();
      }

      //UPDATE
      if(isset($_REQUEST['whd_update']) && Utils::is_count($_REQUEST['whd_update'])){
            if(isset($hooks[$entity][$eventName])){
               foreach($hooks[$entity][$eventName] as $hookId => $hookInfo){
                  if(in_array($hookId, array_keys($_REQUEST['whd_update']))){
                        \Rbs\Moysklad\ApiNew::put('/entity/webhook/' . $hookId, $webHookOptions);
                        $hooks[$entity][$eventName][$hookId]['URL'] = $webHookOptions['url'];
                        unset($_REQUEST['whd_update'][$hookId]);
                  }
               }
            }
      }
      
      //CREATE
      if($_REQUEST["webhook_{$entity}_{$eventName}"] === 'Y'){

            $webhookResult = \Rbs\Moysklad\ApiNew::post('/entity/webhook', $webHookOptions);
            
            if(empty($webhookResult->errors)){
               $hooks[$entity][$eventName][$webhookResult->id] = [
                  'ID' => $webhookResult->id,
                  'URL' => $webhookResult->url,
                  'ENABLED_STATUS' => $webhookResult->enabled ? 'Y' : 'N'
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

      if(isset($hooks[$entity][$eventName])){
            foreach($hooks[$entity][$eventName] as $hookInfo){
               
               $statusText = $hookInfo['ENABLED_STATUS'] == 'Y' ? GetMessage('WEBHOOK_STATUS_ACTIVE') : GetMessage('WEBHOOK_STATUS_INACTIVE');
               $statusColor = $hookInfo['ENABLED_STATUS'] == 'Y' ? 'green' : 'red';
               
               $arAllOptions['webhook'][] = ["note" => $hookInfo['URL'] . " <span style='color: {$statusColor}; font-weight: bold;'>({$statusText})</span>"];
               
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