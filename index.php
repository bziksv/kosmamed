<?  
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetPageProperty("title", "Интернет-магазин медицинского оборудования КОСМАМЕД");
$APPLICATION->SetPageProperty("description", "Интернет-магазин медицинского оборудования. Доставка по всей России. Звоните ☎ +7 (499) 112-08-45");
    $APPLICATION->SetTitle("Медицинское оборудование ");
    global $arSetting;
if(in_array("CONTENT", $arSetting["HOME_PAGE"]["VALUE"])):?><h1>КосмаМед</h1>
 Мы поставляем&nbsp;бренды: Kawe, Riester, Unicos,&nbsp;Shin Nippon, Зенит, Зомз.<br><?endif;
    //CANONICAL
    $pageUrl = $APPLICATION->GetCurPageParam();
    $query_str = parse_url($pageUrl);
    
    if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
    $protocol = 'https://';
    else
    $protocol = 'http://';
    
    parse_str($query_str['query'], $query_params);
    if(!empty($query_params)){
        $APPLICATION->AddHeadString("<link rel='canonical' href='".$protocol.$_SERVER['HTTP_HOST'].$query_str["path"]."'>");
    }
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>