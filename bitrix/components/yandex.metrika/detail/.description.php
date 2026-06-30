<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => GetMessage("YM_COM_DETAIL_NAME"),
	"DESCRIPTION" => GetMessage("YM_COM_DETAIL_DESCR"),
	"PATH" => array(
		"ID" => "yandex_metrika",
		"CHILD" => array(
			"ID" => "detail",
			"NAME" => GetMessage("YM_COM_DETAIL_NAME"),
		)
	),
	"ICON" => "/images/icon.gif",
);
