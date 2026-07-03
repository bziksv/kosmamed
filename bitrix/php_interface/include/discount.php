<?
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

function upDiscount($iNumPage=1){

   
    // меняем под себя эти значения
    $iblock_id = 24; // основной инфоблок
    $idPropSale = 1988; // ID значения свойства отвечающего за Акцию
     
    $oldPropsSale = $arNewSale = array();
     
    // перебираем все активные элементы нашего инфоблока
    $resItem = CIBlockElement::GetList(
        array("ID"=>"asc"),
        array( "ACTIVE"=>"Y", "IBLOCK_ID"=>$iblock_id),
        false,
        Array("nPageSize"=>500, "iNumPage"=>$iNumPage),
        array("ID", "IBLOCK_ID", "PROPERTY_DISCOUNT")
    );
    while($arItem = $resItem->Fetch())
    {
        // запишем текущее значение свойства скидки
        $oldPropsSale[$arItem["ID"]] = $arItem["PROPERTY_DISCOUNT_VALUE"];
         
        // смотрим есть ли у нашего товара SKU
        $offersExist = CCatalogSKU::getExistOffers($arItem["ID"], $arItem["IBLOCK_ID"]);
         
        // для товаров с SKU
        if($offersExist[$arItem["ID"]] == 1)
        {
            // получим все SKU
            $resSku = CCatalogSKU::getOffersList(
                $arItem["ID"],
                $arItem["IBLOCK_ID"],
                array("ACTIVE"=>"Y") // дополнительный фильтр предложений. по умолчанию пуст.
            );
             
            // Если нашли SKU
            if(!empty($resSku[$arItem["ID"]]))
            {
                // Перебираем их
                foreach($resSku[$arItem["ID"]] as $sku)
                {
                    // смотрим есть ли для текущего предложения скидка
                    $arDiscounts = CCatalogDiscount::GetDiscountByProduct($sku["ID"], array(2), "N", 1, SITE_ID);// ID, группа пользователей, пр.подписки, Группа цены, сайт
                     
                    // если нашлась скидка
                    if(!empty($arDiscounts))
                    {
                        // ставим для родителя флаг наличия скидки
                        $arNewSale[$sku["PARENT_ID"]] = "Y";
                        // если нашли останавливаем цикл
                        break;
                    }
                }
            }
        }
        else // для простых товаров
        {
            // смотрим есть ли для текущего предложения скидка
            $arDiscounts = CCatalogDiscount::GetDiscountByProduct($arItem["ID"], array(2), "N", 1, SITE_ID);// ID, группа пользователей, пр.подписки, Группа цены, сайт
            // если нашлась скидка
            if(!empty($arDiscounts))
            {
                // ставим для родителя флаг наличия скидки
                $arNewSale[$arItem["ID"]] = "Y";
            }
        }
    }

    // теперь проставляем свойство где нужно
    foreach($arNewSale as $prodId => $propVal)
    {
        // ставим значение только если оно уже не установлено
        if($oldPropsSale[$prodId] != "да" && $propVal == "Y")
        {
            //echo "Значение для товара $prodId добавлено \r\n";
            CIBlockElement::SetPropertyValuesEx($prodId, $iblock_id, array("DISCOUNT" => $idPropSale));
        }
    }
     
    // теперь очистим устаревшие значения свойства Акция
    foreach($oldPropsSale as $prodId => $propVal)
    {
        // ставим значение только если оно уже не установлено
        if($oldPropsSale[$prodId] == "да" && $arNewSale[$prodId] != "Y")
        {
            //echo "Значение у товара $prodId удалено \r\n";
            CIBlockElement::SetPropertyValuesEx($prodId, $iblock_id, array("DISCOUNT" => false));
        }
    }

    if(count($oldPropsSale)==500){
        $iNumPage++;
    }else{
        $iNumPage = 1;
    }
    return "upDiscount(".$iNumPage.");";
}