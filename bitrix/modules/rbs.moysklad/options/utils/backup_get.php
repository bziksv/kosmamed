<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\Config;

if ($moduleAccessLevel !== "W") {
   CAdminMessage::ShowMessage([
      'MESSAGE' => GetMessage('ACCESS_WRITE_ERROR')
   ]);
   include 'back_to_settings.php';
   return;
}

$directoryName = __DIR__ . '/../backup/';
if(Config::getProfileId() > 0){
   $directoryName = __DIR__ . '/../backup/profile_' . Config::getProfileId() . '/';
}

$searchBackups = $directoryName . "*.json";

$countFiles = [];
foreach (glob($searchBackups) as $filename) {
   $fileNameDate = str_replace([$directoryName, '.json'], '', $filename);
   $countFiles[$fileNameDate] = date('d.m.Y H:i:s', $fileNameDate);
}
if(!is_dir($directoryName) || Utils::count($countFiles) <= 0){
   CAdminMessage::ShowMessage([
      'MESSAGE' => GetMessage('BACKUP_EMPTY')
   ]);
   include 'back_to_settings.php';
   return;
}

if($request->get('do') !== 'Y'):
?>

<form action="/bitrix/admin/settings.php" method="GET">
   <h2><?=GetMessage('BACKUP_DATE')?></h2>
   <input type="hidden" name="mid" value="<?=$mid_orig?>">
   <input type="hidden" name="lang" value="<?=$request->get('lang')?>">
   <?if(Config::getProfileId() > 0):?>
      <input type="hidden" name="profile_id" value="<?=Config::getProfileId()?>">
   <?endif?>
   <input type="hidden" name="backup" value="Y">
   <input type="hidden" name="backup_get" value="Y">
   <input type="hidden" name="do" value="Y">
   <div>
      <select name="backupStamp">
            <?foreach($countFiles as $filePath => $fileName):?>
               <option value="<?=$filePath?>"><?=$fileName?></option>
            <?endforeach?>
         </select>
   </div>
   <div>
      <br>
      <input type="submit" value="<?=GetMessage('BACKUP_GET')?>">
   </div>
</form>

<?elseif(!empty($request->get('backupStamp'))):?>

   <?
      $file = $directoryName . $request->get('backupStamp') . '.json';

      if(!file_exists($file)){
         CAdminMessage::ShowMessage([
            'MESSAGE' => GetMessage('BACKUP_EMPTY')
         ]);
         return;
      }

      $file = file_get_contents($file);
      $optionArray = \Bitrix\Main\Web\Json::decode($file);

      foreach(['login', 'pass', 'token'] as $fieldGet){
         $optionArray[$fieldGet] = COption::GetOptionString($mid, $fieldGet);
      }

      COption::RemoveOption($mid);
      foreach($optionArray as $opt => $val){
         if(empty($val)) continue;
         COption::SetOptionString($mid, $opt, $val);
      }

      CAdminMessage::ShowMessage([
         'MESSAGE' => GetMessage('BACKUP_OK'),
         'TYPE' => 'OK'
      ]);

      include 'back_to_settings.php';

      return;
   ?>
<?endif?>