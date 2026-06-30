<?php
use Ipolh\SDEK\Bitrix\Tools;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::includeModule('ipol.sdek');

if ($GLOBALS['APPLICATION']->GetGroupRight(IPOLH_SDEK) > 'D') // checking rights
{
    // Main menu block
    $aMenu = array(
        'parent_menu' => 'global_menu_store', // IM menu block
        'section' => 'sdek',
        'sort' => 110,
        'text' => Tools::getMessage('MENU_MAIN_TEXT'),
        'title' => Tools::getMessage('MENU_MAIN_TITLE'),
        'icon' => 'ipol_sdek_menu_icon', // CSS for icon
        'page_icon' => 'ipol_sdek_page_icon', // CSS for icon
        'module_id' => IPOLH_SDEK,
        'items_id' => IPOLH_SDEK_LBL.'menu',
        'items' => array(),
    );

    // Parent pages

    /*
    // Orders
    $aMenu['items'][] = array(
        'text' => Tools::getMessage('MENU_ORDERS_TEXT'),
        'title' => Tools::getMessage('MENU_ORDERS_TITLE'),
        'module_id' => IPOLH_SDEK,
        'url' => 'ipol_sdek_orders.php?lang='.LANGUAGE_ID,
        //"more_url" => array("ipol_sdek_orders_edit.php")  // Use it for admin pages like "Edit order with ID=..." and it will be marked in this menu as "opened"
    );
    */

    // Courier calls
    $aMenu['items'][] = array(
        'text' => Tools::getMessage('MENU_COURIER_CALLS_TEXT'),
        'title' => Tools::getMessage('MENU_COURIER_CALLS_TITLE'),
        'module_id' => IPOLH_SDEK,
        'url' => 'ipol_sdek_courier_calls.php?lang='.LANGUAGE_ID,
    );

    // Courier senders
    $aMenu['items'][] = array(
        'text' => Tools::getMessage('MENU_STORES_TEXT'),
        'title' => Tools::getMessage('MENU_STORES_TITLE'),
        'module_id' => IPOLH_SDEK,
        'url' => 'ipol_sdek_stores.php?lang='.LANGUAGE_ID,
    );

    /*
    // Sync
    $aMenu['items'][] = array(
        'text' => Tools::getMessage('MENU_SYNC_DATA_TEXT'),
        'title' => Tools::getMessage('MENU_SYNC_DATA_TITLE'),
        'module_id' => IPOLH_SDEK,
        'url' => 'ipol_sdek_sync_data.php?lang='.LANGUAGE_ID,
    );
    */

    // Options
    $aMenu['items'][] = array(
        'text' => Tools::getMessage('MENU_OPTIONS_TEXT'),
        'title' => Tools::getMessage('MENU_OPTIONS_TITLE'),
        'module_id' => IPOLH_SDEK,
        'url' => 'ipol_sdek_options.php?lang='.LANGUAGE_ID,
    );

    return $aMenu;
}
return false;