<?php

use Bitrix\Main\Localization\Loc;


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
$result['STATE'] = 'OK';
$result['ERRORS'] = [];

?>

<form action="<?echo $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?echo LANG ?>">
<?php

if (!\Bitrix\Main\Loader::includeModule('sale')){

    $result['STATE'] = 'ERROR';
    $result['ERRORS'] = array_merge($result['ERRORS'], [Loc::getMessage("SALE_IS_NULL")]);
//    $result["ERRORS"] = ['У Вас не обнаружен модуль "Sale"'];

}


if(!extension_loaded('curl') || !function_exists('curl_init')){

    $result['STATE'] = 'ERROR';
   $result['ERRORS'] = array_merge($result['ERRORS'], [Loc::getMessage("CURL_IS_NULL")]);
    //$result['ERRORS'] = ['У вас не обнаружена зависимость curl в конфигурации интерпритатора PHP.'];
}


if (!version_compare(phpversion(), "7.4", ">=")) {

    $result['STATE'] = 'ERROR';
    $result['ERRORS'] = array_merge($result['ERRORS'], [Loc::getMessage("PHP_VERSION_IS_NOT_74_OR_LATER")]);
   // $result['ERRORS'] = ['Версия интепритатора PHP менее версии 7.4. Пожалуйста, обновите версию интерпритатора.'];
}


if($result['STATE'] == 'ERROR'){

    $html = Loc::getMessage("ERROR_MESSAGE");

    foreach ($result['ERRORS'] as $key => $error){
		$html.='<span style="color:red">'.$error.'</span><br/>';
    }

    echo $html;

} else {

    echo Loc::getMessage("SUCCESS_MESSAGE");
    if(isset($step['messages'])){
        foreach ($step['messages'] as $message){
            echo '<span>'.$message.'</span><br/>';
        }
    }

}


?>
</form>
