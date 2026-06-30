<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
 

    if (CModule::IncludeModule("iblock")) {

        $iblockId = 24; // здесь указываем ID инфоблока, в котором нужно очистить описание
        $descriptionFieldCode = "PREVIEW_TEXT"; // здесь указываем символьный код свойства описания товара
        $emptyDescriptionValue = ""; // здесь указываем значение, которое нужно присвоить свойству описания товара

        $dbItems = CIBlockElement::GetList(
            [],
            ["IBLOCK_ID" => $iblockId, "!PREVIEW_TEXT" => false],
            false,
            ["nPageSize"=>500, "iNumPage"=>1],
            ["ID", $descriptionFieldCode]
        );

        while ($item = $dbItems->Fetch()) {
            $arEtamId[] = $item["ID"];
            $element = new CIBlockElement();
            $element->Update($item["ID"], [$descriptionFieldCode => $emptyDescriptionValue]);
        }

        if(count($arEtamId)>0){
            LocalRedirect("/text/del_PREVIEW_TEXT.php");
        }
       

        echo "Описание товаров в инфоблоке с ID $iblockId было очищено";
    } else {
        echo "Модуль инфоблоков не установлен";
    }