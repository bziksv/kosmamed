<?php
global $APPLICATION;

$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/qr_header.php"), false, array("HIDE_ICONS" => "Y"));

if (file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/r52.qrcode/handlers/r52qrcode/template/template.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/r52.qrcode/handlers/r52qrcode/template/template.php');
}

$APPLICATION->IncludeComponent("bitrix:main.include", "", array("AREA_FILE_SHOW" => "file", "PATH" => SITE_DIR."include/qr_bottom.php"), false, array("HIDE_ICONS" => "Y"));
