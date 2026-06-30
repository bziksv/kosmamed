<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
global $USER;
if ($USER->IsAdmin()):

    $back = true;

    if($_REQUEST["page"]>1){
        $iNumPage = $_REQUEST["page"];
    }else{
        $iNumPage = 1;
    }
    

    CModule::IncludeModule("iblock");

    $iblockId = 24;

    $arSelect = Array("ID", "NAME", "PROPERTY_CML2_ARTICLE");
    $arFilter = Array("IBLOCK_ID"=>IntVal($iblockId), "ACTIVE_DATE"=>"Y", "!PROPERTY_CML2_ARTICLE"=>false, "ACTIVE"=>"Y");
    $res = CIBlockElement::GetList(Array("xml_id"=>"asc"), $arFilter, false, Array("nPageSize"=>500, 'iNumPage' => $iNumPage), $arSelect);
    $count = $res->SelectedRowsCount(); // количество элементов

    while($ob = $res->GetNextElement())
    {
        $arFields = $ob->GetFields();

        CIBlockElement::SetPropertyValues($arFields["ID"], $iblockId, false, "WORKED_OUT");
        if($arFields["PROPERTY_CML2_ARTICLE_VALUE"]){
            $arArticle[] = $arFields["PROPERTY_CML2_ARTICLE_VALUE"];
        }
    }

    if( ($iNumPage*500)<$count ) $back = false;

    $iNumPage++;

    foreach ($arArticle as $key => $article) {

        $arTovar = [];

        $arSelect = Array("ID", "NAME", "PROPERTY_CML2_ARTICLE", "PROPERTY_KOD_TOVARA_DLYA_MEDMARKETA");
        $arFilter = Array("IBLOCK_ID"=>IntVal($iblockId), "PROPERTY_CML2_ARTICLE"=>$article, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
        $res = CIBlockElement::GetList(Array("xml_id"=>"asc"), $arFilter, false, Array("nPageSize"=>50), $arSelect);
        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();

            $arTovar[] = ['ID' => $arFields["ID"],'TOVAR' => $arFields["PROPERTY_KOD_TOVARA_DLYA_MEDMARKETA_VALUE"] ];

        }

        $cntTover = count($arTovar);
        if($cntTover>1){

            if($cntTover==2){
                foreach ($arTovar as $keyT => $tovar) {

                   $arrTovar = explode('+', $tovar['TOVAR']);

                   if($arrTovar[count($arrTovar)-1] == 'Товар'){

                        $el = new CIBlockElement;
                        $arFieldsUP = [ "ACTIVE" => "N" ];
                        if (!$el->Update($tovar['ID'], $arFieldsUP)) {
                            echo "Ошибка: " . $el->LAST_ERROR;
                        }

                   }
                   

                }
            }else{
                $arrComplect = [];
                $arrTovar = [];
                $arComplect = [];
                $maxArComplect = 0;
                foreach ($arTovar as $keyT => $tovar) {

                   $arrTovar = explode('+', $tovar['TOVAR']);

                   if($arrTovar[count($arrTovar)-1] == 'Товар'){

                        $el = new CIBlockElement;
                        $arFieldsUP = [ "ACTIVE" => "N" ];
                        if (!$el->Update($tovar['ID'], $arFieldsUP)) {
                            echo "Ошибка: " . $el->LAST_ERROR;
                        }

                   }
    
                   if($arrTovar[count($arrTovar)-1] == 'Комплект'){

                        $arrComplect = explode('-', $arrTovar[count($arrTovar)-2]);

                        $arComplect[$tovar['ID']] = $arrComplect[count($arrComplect)-1];

                   }

                }
                if($arComplect){
                    $maxArComplect = max($arComplect);


                    foreach ($arComplect as $keyC => $complect) {

                        if($complect!=$maxArComplect){
                             CIBlockElement::SetPropertyValues($keyC, $iblockId, 2776, "NOINDEX");
                        }
        
                    }
                }

            }
        }
    }


     ?>


    <?
    if($back):
        ?>
        <script>
            $( document ).ready(function() {
              $(window.location).attr('href', '/');
            });
        </script>
        <?
    else:
        ?>
        <script>
            $( document ).ready(function() {
              $(window.location).attr('href', '/xml_id/?page=<?=$iNumPage?>');
            });
        </script>
        <?
    endif;
endif;
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>