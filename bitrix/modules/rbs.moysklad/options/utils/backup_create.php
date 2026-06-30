<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

use \Rbs\Moysklad\Config;

if ($moduleAccessLevel === "W") {

   $directoryName = __DIR__ . '/../backup/';
   if(Config::getProfileId() > 0){
      $directoryName = __DIR__ . '/../backup/profile_' . Config::getProfileId() . '/';
   }
   if(!is_dir($directoryName)){
      mkdir($directoryName, 0755, true);
   }

   $fileName = time() . '.json';
   $optionsArray = \Bitrix\Main\Config\Option::getForModule($mid);
   foreach(['login', 'pass', 'token'] as $fieldDelete){
      if(isset($optionsArray[$fieldDelete])){
         unset($optionsArray[$fieldDelete]);
      }
   }

   $jsonArray = \Bitrix\Main\Web\Json::encode($optionsArray);
   $file = $directoryName . $fileName;
   file_put_contents($file, $jsonArray);

   if(file_exists($file)){
      CAdminMessage::ShowMessage([
         'MESSAGE' => GetMessage('BACKUP_CREATED', ['#NAME#' => $fileName]),
         'TYPE' => 'OK'
      ]);
   } else {
      CAdminMessage::ShowMessage([
         'MESSAGE' => GetMessage('BACKUP_CREATED_FAIL')
      ]);
   }

} else {

   CAdminMessage::ShowMessage([
      'MESSAGE' => GetMessage('ACCESS_WRITE_ERROR')
   ]);

}

include 'back_to_settings.php';