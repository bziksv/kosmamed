<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}
function getAgentInfo($agentName = '', $additionalOptions = [])
{ 
   $agentInfo = \Rbs\Moysklad\Agent::getInfo($agentName);
   if($agentInfo){
      return ["agent_info", GetMessage('IMPORT_HEAD_AGENT_INFO', $agentInfo + $additionalOptions), '', ['statichtml']];
   } else {
      return ["agent_info", GetMessage('IMPORT_HEAD_AGENT_OFF'), '', ['statichtml']];
   }
}

function human_filesize($bytes, $decimals = 2) {
   $sz = 'BKMGTP';
   $factor = floor((mb_strlen($bytes) - 1) / 3);
   return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
 }

function getDirSize($path) {

   $returnSize = 0;

   if (!$h = opendir($path)) return $returnSize;

   while (($element = readdir($h)) !== false) {
       if ($element != "." && $element != "..") {

           $all_path = $path . "/" . $element;

           if (filetype($all_path) == "file"){
               $returnSize += filesize($all_path);

           } elseif (filetype($all_path) == "dir"){
               $returnSize += getDirSize($all_path);
           }
       }
   }
   
   closedir($h);
   return $returnSize;
}

function delTree($dir) {
   $files = array_diff(scandir($dir), array('.','..'));
   foreach ($files as $file) {
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
   }
   return rmdir($dir);
}

function ShowParamsHTMLByArray($arParams)
{
   $mid = \Rbs\Moysklad\Config::getModuleId();
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