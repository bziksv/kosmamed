<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?
$frameId = "askaron_clientid_yandex_".$this->randString();


$frame = new \Bitrix\Main\Page\FrameBuffered($frameId);
$frame->begin("");
echo "#ASKARON_CLIENTID_CODE#";
$frame->end();