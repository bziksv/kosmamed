<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */


$arComponentParameters = array(
    "PARAMETERS" => array(
        "LEVEL" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("R52_LEVEL"),
            "TYPE" => "LIST",
            "VALUES" => array(
                'L' => 'L',
                'M' => 'M',
                'Q' => 'Q',
                'H' => 'H'
            ),
            'DEFAULT' => 'M'
        ),
        "SIZE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("R52_SIZE"),
            "TYPE" => "STRING",
            'DEFAULT' => '3'
        ),
        "MARGIN" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("R52_MARGIN"),
            "TYPE" => "STRING",
            'DEFAULT' => '3'
        ),
        "ENCODING" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("R52_ENCODING"),
            "TYPE" => "STRING",
            'DEFAULT' => '3'
        ),

        "TYPE" => array(
            "PARENT" => "BASE",
            "NAME" => GetMessage("R52_TYPE"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
            "REFRESH" => "Y",
        ),
    ),
);
if($arCurrentValues['TYPE'] == 'N' || !$arCurrentValues['TYPE']) {
    $arComponentParameters['PARAMETERS']["CUSTOM_URL"] = array(
        "PARENT" => "ADDITIONAL_SETTINGS",
        "NAME" => GetMessage("R52_CUSTOM_URL"),
        "TYPE" => "STRING");
}
else {
    $arComponentParameters['PARAMETERS']["ORDER_ID"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_ORDER_ID"),
        "TYPE" => "STRING",
        'DEFAULT' => '={$_REQUEST["ID"]}'
    );
    $arComponentParameters['PARAMETERS']["PURPOSE"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_PURPOSE"),
        "TYPE" => "STRING",
    );
    $arComponentParameters['PARAMETERS']["NAME"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_NAME"),
        "TYPE" => "STRING",
    );

    $arComponentParameters['PARAMETERS']["PAYEE_INN"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_PAYEE_INN"),
        "TYPE" => "STRING",
    );
    $arComponentParameters['PARAMETERS']["KPP"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_KPP"),
        "TYPE" => "STRING",
    );
    $arComponentParameters['PARAMETERS']["PERSONAL_ACC"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_PERSONAL_ACC"),
        "TYPE" => "STRING",
    );
    $arComponentParameters['PARAMETERS']["BANK_MAME"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_BANK_MAME"),
        "TYPE" => "STRING",
    );
    $arComponentParameters['PARAMETERS']["BIC"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_BIC"),
        "TYPE" => "STRING",
    );
    $arComponentParameters['PARAMETERS']["CORRESP_ACC"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_CORRESP_ACC"),
        "TYPE" => "STRING",
    );
    $arComponentParameters['PARAMETERS']["SUM"] = array(
        "PARENT" => "DATA_SOURCE",
        "NAME" => GetMessage("R52_ORDER_SUM"),
        "TYPE" => "STRING",
    );
}

?>