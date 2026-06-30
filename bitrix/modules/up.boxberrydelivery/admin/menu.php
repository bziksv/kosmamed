<?

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$module_id = "up.boxberrydelivery";
if($APPLICATION->GetGroupRight($module_id) != "D")
{
    $aMenu = array(
        "parent_menu" => "global_menu_store",
        "sort" => 100,
        "text" => Loc::getMessage("BB_MENU_TEXT"),
        "title" => Loc::getMessage("BB_MENU_TITLE"),
        "module_id" => $module_id,
        "icon" => "boxberry_menu_icon",
        "page_icon" => "boxberry_page_icon",
        "url" => "boxberry.php?lang=".LANGUAGE_ID,
    );
	return $aMenu;
}
return false;



?>



