<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("ipol.sdek");

if (!$USER->IsAdmin()) die("Admin only");

$fileContent = \Ipolh\SDEK\Bitrix\Admin\Logger::getLog();

if($fileContent){
    echo "<pre>";
    echo htmlspecialchars($fileContent);
    echo "</pre>";
} else {
    echo 'No logs';
}
?>