<?
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/discount.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/discount.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/quantity_section.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/quantity_section.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/hide_product.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/hide_product.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/id_to_id.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/id_to_id.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/catalog_section_list_json.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/catalog_section_list_json.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/yandex_conversion.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/yandex_conversion.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/hit.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/hit.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/new.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/new.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/price_false.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/price_false.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/xml_id.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/xml_id.php');
}
if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/hide_item_sect.php')){
   require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/hide_item_sect.php');
}
function isWrapAttr($current, $target)
{
    if($current < $target)
        return true;
        
    return false;
}