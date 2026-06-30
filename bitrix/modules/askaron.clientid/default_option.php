<?
$rsSites = \CSite::GetList($by = "sort", $order = "desc", []);

$arOptions = [
	"consider_user_authorize" => [
		"CODE" => 'consider_user_authorize',
		"COMMON" => 'Y',
		'VALUE' => 'N',
	],
	"ym_token" => [
		"CODE" => 'ym_token',
		"COMMON" => 'N',
		'VALUE' => '',
	],
	"ym_counter" => [
		"CODE" => 'ym_counter',
		"COMMON" => 'N',
		'VALUE' => '',
	],
	"ym_new_order" => [
		"CODE" => 'ym_new_order',
		"COMMON" => 'N',
		'VALUE' => '',
	],
];
while($arSite=$rsSites->Fetch()){
	foreach ($arOptions as $arOption){
		$arOption['COMMON']=='Y'?$siteSuffix = '':$siteSuffix=$arSite['LID'];
		$askaron_clientid_default_option[$arOption['CODE'].$siteSuffix] = $arOption['VALUE'];

	}
}
