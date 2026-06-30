<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var string $templateName
 * @var string $componentPath
 */
?>
<div class="container qr_code">
    <?if($arParams['TYPE'] == 'Y') {?>
        <h4><?=Loc::getMessage('R52_QRCODE_DESC');?></h4>
    <?}?>
    <?=$arResult['QR_CODE']?>
</div>
