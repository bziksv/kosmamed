<?php

    /**
     * @var array $arResult
     * @var array $arParams
     * @global \CMain $APPLICATION
     * @var CatalogSectionComponent $component
     * @var CBitrixComponentTemplate $this
     * @var string $templateName
     * @var string $componentPath
     * @var string $templateFolder
     *
     */

    use Bitrix\Main\Localization\Loc;
    use Ipol\Robokassa;

    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
    {
        die();
    }

    if($arParams['WIDGET_PARAMS']['USE_TYPE'] == Robokassa\RobokassaWidget::USE_WIDGET_TYPE_NONE)
    {
        return;
    }

    Loc::loadMessages(__FILE__);

    $this->addExternalJs('//auth.robokassa.ru/merchant/bundle/robokassa-iframe-badge.js');
?>
<div id="robokassa-widget-box">
    <?php if($arParams['WIDGET_PARAMS']['USE_TYPE'] == Robokassa\RobokassaWidget::USE_WIDGET_TYPE_BADGE): ?>
        <div class="js-robokassa-badge-box">
            <robokassa-badge
                merchantLogin="<?=$arParams['WIDGET_PARAMS']['MERCHANT'];?>"
                outSum="<?=$arResult['PRICE'];?>"
                theme="<?=$arParams['WIDGET_PARAMS']['BADGE_THEME'];?>"
                size="<?=$arParams['WIDGET_PARAMS']['BADGE_SIZE'];?>"
                <?php if(!empty($arParams['WIDGET_PARAMS']['BADGE_COLOR'])):?>colorScheme="<?=$arParams['WIDGET_PARAMS']['BADGE_COLOR'];?>"<?php endif;?>
                showLogo="<?=($arParams['WIDGET_PARAMS']['BADGE_SHOW_LOGO'] === 'Y' ? 'true' : 'false');?>"
                onclick="handleRobokassaBadgeCheckout"
            >
            </robokassa-badge>
        </div>
    <?php elseif ($arParams['WIDGET_PARAMS']['USE_TYPE'] == Robokassa\RobokassaWidget::USE_WIDGET_TYPE_WIDGET):?>
        <div class="js-robokassa-widget-box">
            <robokassa-widget
                merchantLogin="<?=$arParams['WIDGET_PARAMS']['MERCHANT'];?>"
                outSum="<?=$arResult['PRICE'];?>"
                theme="<?=$arParams['WIDGET_PARAMS']['WIDGET_THEME'];?>"
                size="<?=$arParams['WIDGET_PARAMS']['WIDGET_SIZE'];?>"
                showLogo="<?=($arParams['WIDGET_PARAMS']['WIDGET_SHOW_LOGO'] === 'Y' ? 'true' : 'false');?>"
                borderRadius="<?=$arParams['WIDGET_PARAMS']['WIDGET_BORDER_RADIUS'];?>"
                hasSecondLine="<?=$arParams['WIDGET_PARAMS']['WIDGET_HAS_SECOND_LINE'];?>"
                descriptionPosition="<?=$arParams['WIDGET_PARAMS']['WIDGET_DESCRIPTION_POSITION'];?>"
                type="<?=$arParams['WIDGET_PARAMS']['WIDGET_WIDGET_TYPE'];?>"
                mode="checkout"
                checkoutUrl="<?= (\Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest()->isHttps() ? 'https://' : 'http://') . \Bitrix\Main\HttpApplication::getInstance()->getContext()->getServer()->getHttpHost();?>"
                oncheckout="handleRobokassaWidgetCheckout"
            ></robokassa-widget>
        </div>
    <?php endif;?>

    <script>
        let robokassaWidgetOptions = {
            useType: <?=$arParams['WIDGET_PARAMS']['USE_TYPE'];?>,
            skuVariable: <?php if(!empty($arParams['SKU_BLOCK_CODE'])):?>'<?=$arParams['SKU_BLOCK_CODE'];?>'<?php else:?>null<?php endif;?>,
            lastPrice: 0,
            lastSukId: 0,
            params : {
                "merchantLogin": "<?=$arParams['WIDGET_PARAMS']['MERCHANT'];?>",
                "outSum": 0,
                <?php if($arParams['WIDGET_PARAMS']['USE_TYPE'] == Robokassa\RobokassaWidget::USE_WIDGET_TYPE_BADGE): ?>
                "theme": "<?=$arParams['WIDGET_PARAMS']['BADGE_THEME'];?>",
                "size": "<?=$arParams['WIDGET_PARAMS']['BADGE_SIZE'];?>",
                "colorScheme": "<?=$arParams['WIDGET_PARAMS']['BADGE_COLOR'];?>",
                "showLogo": "<?=($arParams['WIDGET_PARAMS']['BADGE_SHOW_LOGO'] === 'Y' ? 'true' : 'false');?>",
                "onclick": "handleRobokassaBadgeCheckout",
                <?php elseif ($arParams['WIDGET_PARAMS']['USE_TYPE'] == Robokassa\RobokassaWidget::USE_WIDGET_TYPE_WIDGET):?>
                "theme": "<?=$arParams['WIDGET_PARAMS']['WIDGET_THEME'];?>",
                "size": "<?=$arParams['WIDGET_PARAMS']['WIDGET_SIZE'];?>",
                "showLogo": "<?=($arParams['WIDGET_PARAMS']['WIDGET_SHOW_LOGO'] === 'Y' ? 'true' : 'false');?>",
                "borderRadius": "<?=$arParams['WIDGET_PARAMS']['WIDGET_BORDER_RADIUS'];?>",
                "hasSecondLine": "<?=$arParams['WIDGET_PARAMS']['WIDGET_HAS_SECOND_LINE'];?>",
                "descriptionPosition": "<?=$arParams['WIDGET_PARAMS']['WIDGET_DESCRIPTION_POSITION'];?>",
                "type": "<?=$arParams['WIDGET_PARAMS']['WIDGET_WIDGET_TYPE'];?>",
                "mode": "checkout",
                "checkoutUrl": "<?= (\Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest()->isHttps() ? 'https://' : 'http://') . \Bitrix\Main\HttpApplication::getInstance()->getContext()->getServer()->getHttpHost();?>",
                "oncheckout": "handleRobokassaWidgetCheckout",
                <?php endif;?>
            }
        }
    </script>
</div>