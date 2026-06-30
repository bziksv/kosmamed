<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"panel",
	array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "24",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_GROUPS" => "Y",
		"TOP_DEPTH" => "1",
		"SECTION_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"SECTION_USER_FIELDS" => array(
			0 => "",
			1 => "UF_ICON",
			2 => "",
		),
		"SECTION_URL" => "",
		"ADD_SECTIONS_CHAIN" => "N",
		"DISPLAY_IMG_WIDTH" => "50",
		"DISPLAY_IMG_HEIGHT" => "50",
		"COMPONENT_TEMPLATE" => "panel",
		"SECTION_ID" => $_REQUEST["SECTION_ID"],
		"SECTION_CODE" => "",
		"COUNT_ELEMENTS" => "Y",
		"FILTER_NAME" => "sectionsFilter",
		"CACHE_FILTER" => "N"
	),
	false
);?>
