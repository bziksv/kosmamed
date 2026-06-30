<?	global $APPLICATION;
	global $arOptions;
	\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
	\Bitrix\Main\Loader::includeModule('sale');
	require_once(__DIR__ .'/classes/general/boxberry.php'); 
	require_once(__DIR__ .'/classes/general/boxberry_parsel.php'); 
	require_once(__DIR__ .'/classes/general/delivery_boxberry.php'); 
	require_once(__DIR__ .'/classes/mysql/boxberry_order.php');
    require_once(__DIR__ .'/classes/mysql/CitiesTable.php');
    require_once(__DIR__ .'/classes/mysql/ReceptionPointsTable.php');
    require_once(__DIR__ .'/classes/general/boxberry_psend.php');
    require_once(__DIR__ .'/classes/general/boxberry_agents.php');
?>