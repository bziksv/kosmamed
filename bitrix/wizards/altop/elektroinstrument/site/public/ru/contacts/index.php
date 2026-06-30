<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Контакты");?>

<h2>ООО "Интернет-магазин электроинструмента ЭЛЕКТРОСИЛА"</h2>
<p>г. Новгород, ул. Славная 51<br />Тел. 7 (495) 000-00-00, 7 (495) 000-00-00</p>
<h2>Схема проезда</h2>
<?$APPLICATION->IncludeComponent("bitrix:map.yandex.view", "",
	Array(
		"INIT_MAP_TYPE" => "MAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:58.520915387234396;s:10:\"yandex_lon\";d:31.293660457672132;s:12:\"yandex_scale\";i:15;s:10:\"PLACEMARKS\";a:1:{i:0;a:3:{s:3:\"LON\";d:31.293660457672132;s:3:\"LAT\";d:58.520915387234396;s:4:\"TEXT\";s:27:\"г. Новгород, ул. Славная 51\";}}}",
		"MAP_WIDTH" => "100%",
		"MAP_HEIGHT" => "305",
		"CONTROLS" => array("ZOOM", "TYPECONTROL", "SCALELINE"),
		"OPTIONS" => array("ENABLE_DBLCLICK_ZOOM", "ENABLE_DRAGGING"),
		"MAP_ID" => "1"
	),
	false
);?>
<br />
<? $APPLICATION->IncludeComponent("bitrix:main.include", "",
    array(
        "AREA_FILE_SHOW" => "file",
        "PATH" => SITE_DIR . "include/form_contact.php",
        "AREA_FILE_RECURSIVE" => "N",
        "EDIT_MODE" => "html",
    ),
    false,
    array("HIDE_ICONS" => "Y")
); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>