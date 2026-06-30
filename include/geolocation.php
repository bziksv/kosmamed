<?
$APPLICATION->IncludeComponent(
	"altop:geolocation", 
	"city", 
	[
		"IBLOCK_TYPE" => "content",
		"IBLOCK_ID" => "7",
		"SHOW_CONFIRM" => "Y",
		"SHOW_DEFAULT_LOCATIONS" => "N",
		"SHOW_TEXT_BLOCK" => "Y",
		"SHOW_TEXT_BLOCK_TITLE" => "Y",
		"TEXT_BLOCK_TITLE" => "",
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "36000000",
		"COOKIE_TIME" => "36000000",
		"COMPONENT_TEMPLATE" => "city",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "DYNAMIC_WITH_STUB",
		"MODE_OPERATION" => "BITRIX"
	],
	false
);?>
