<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("ASKARON_CLIENTID_LIST_NAME"),
	"DESCRIPTION" => GetMessage("ASKARON_CLIENTID_LIST_DESCRIPTION"),
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "askaron_components",
		"NAME" => GetMessage("ASKARON_COMPONENTS_GROUP_NAME"),
		"CHILD" => array(
			"ID" => "askaron_clientid",
			"NAME" => GetMessage("ASKARON_CLIENTID_GROUP_NAME"),
		)
	),
);
?>