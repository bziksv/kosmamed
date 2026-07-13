<?php

    global $APPLICATION, $USER;

    use Bitrix\Main\Config\Option;
    use Bitrix\Main\Localization\Loc;

    $moduleId = 'ipol.robokassa';

    Loc::loadMessages(__FILE__);

    if (!$USER->IsAdmin())
    {
        return false;
    }

    if(Option::get($moduleId, 'ENABLE_START_FUNCTION', 'N') !== 'Y')
    {
        return false;
    }

    return [
        "parent_menu" => "global_menu_content",
        "section" => "ipol_robokassa",
        "sort" => 1000,
        "text" => GetMessage("IPOL_ROBOKASSA_ADMIN_MENU_GLOBAL"),
        "title" => GetMessage("IPOL_ROBOKASSA_ADMIN_MENU_GLOBAL"),
        "icon" => "ipol_robokassa_menu_icon",
        "page_icon" => "ipol_robokassa_page_icon",
        "items_id" => "menu_ipol_robokassa",
        "items" => [
            [
                "text" => GetMessage("IPOL_ROBOKASSA_ADMIN_MENU_ORDER_TITLE"),
                "title" => GetMessage("IPOL_ROBOKASSA_ADMIN_MENU_ORDER_TITLE"),
                "more_url" => [
                    "ipol_robokassa_order_list.php",
                ],
                "url" => "ipol_robokassa_order_list.php"
            ]
        ]
    ];