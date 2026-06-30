<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

if ($moduleAccessLevel !== "W") {
   CAdminMessage::ShowMessage([
      'MESSAGE' => GetMessage('ACCESS_WRITE_ERROR')
   ]);
   include 'back_to_settings.php';
   return;
}

$directoryName = __DIR__ . '/../backup/';
$searchBackups = $directoryName . "*.json";

if(\Rbs\MoyskladStocks\Config::getProfileId() > 0){
   $searchBackups = $directoryName . "*_" . \Rbs\MoyskladStocks\Config::getProfileId() . ".json";
}

$countFiles = [];
foreach (glob($searchBackups) as $filename) {
   $fileNameDate = str_replace([$directoryName, '.json'], '', $filename);
   if(\Rbs\MoyskladStocks\Config::getProfileId() > 0){
      $fileNameDate = array_shift(explode('_', $fileNameDate));
   }
   $countFiles[$fileNameDate] = date('d.m.Y H:i:s', $fileNameDate);
}
if(!is_dir($directoryName) || count($countFiles) <= 0){
   CAdminMessage::ShowMessage([
      'MESSAGE' => GetMessage('BACKUP_EMPTY')
   ]);
   return;
}

if($request->get('do') !== 'Y'):
?>

<form action="/bitrix/admin/settings.php" method="GET">
   <h2><?=GetMessage('BACKUP_DATE')?></h2>
   <input type="hidden" name="mid" value="<?=$mid_orig?>">
   <input type="hidden" name="lang" value="ru">
   <?if(\Rbs\MoyskladStocks\Config::getProfileId() > 0):?>
      <input type="hidden" name="profile_id" value="<?=\Rbs\MoyskladStocks\Config::getProfileId()?>">
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
      if(\Rbs\MoyskladStocks\Config::getProfileId() > 0){
         $file = $directoryName . $request->get('backupStamp') . '_' . \Rbs\MoyskladStocks\Config::getProfileId() . '.json';
      }
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