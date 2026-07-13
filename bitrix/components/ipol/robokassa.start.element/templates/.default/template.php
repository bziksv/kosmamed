<?php

    use Bitrix\Main\Localization\Loc;

    /**
     * @var array $arParams
     * @var array $arResult
     * @var string $templateFolder
     */

    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
    {
        die();
    }

    CJSCore::Init(['jquery3', 'ajax']);
    \Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder . '/js/imask.js');

    /**
     * @todo fix jquery
     */


    Loc::loadMessages(__FILE__);
?>
    <noindex>
        <?php if(empty($arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_PLACEMENT'])):?>

            <div data-nosnippet class="robokassa-buttons <?php if(!empty($arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_POSITION'])):?>position-<?=$arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_POSITION'];?><?php endif;?>">
                <div
                    id="openRobokassaAdd2basket"
                    data-product-id="<?=$arResult['PRODUCT']['ID'];?>"
                    class="trigger-button
                    <?php if($arResult['PRODUCT']['QUANTITY'] > 0):?> add2basket-button<?php endif;?>
                    <?php if(!empty($arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_BUTTON_CLASS'])):?> <?=$arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_BUTTON_CLASS'];?><?php endif;?>
                    <?php if($arResult['PRODUCT']['QUANTITY'] === 0):?> disabled-quantity<?php endif;?>
                ">
                    <?php if($arResult['PRODUCT']['QUANTITY'] > 0):?>
                        <?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BUY_BUTTON');?>
                    <?php else:?>
                        <?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BUY_BUTTON_QUANTITY');?>
                    <?php endif;?>
                </div>
                <div
                    id="openRobokassaBasket"
                    class="trigger-button basket-button
                    <?php if(!empty($arParams['ROBOKASSA_OPTION']['OPTIONS_BUTTON_BASKET_CLASS'])):?> <?=$arParams['ROBOKASSA_OPTION']['OPTIONS_BUTTON_BASKET_CLASS'];?><?php endif;?>
                ">
                    <?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_BUTTON');?>
                </div>
            </div>

            <script>
                let robokassaBuyButtonBlock = {
                    session_id: '<?=bitrix_sessid();?>',
                    lang : {
                        empty: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.EMPTY_BASKET');?>',
                        item_remove: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.BASKET_ITEM.REMOVE');?>',
                        item_quantity: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.BASKET_ITEM.QUANTITY');?>',
                        create_order: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.CREATE_ORDER');?>'
                    }
                };
            </script>
        <?php else:?>
            <script>
                let robokassaBuyButtonBlock = {
                    session_id: '<?=bitrix_sessid();?>',
                    block: '<?=$arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_PLACEMENT'];?>',
                    productId: '<?=$arResult['PRODUCT']['ID'];?>',
                    price: '<?=$arResult['PRODUCT']['PRICE'];?>',
                    printPrice: '<?=$arResult['PRODUCT']['PRINT_PRICE'];?>',
                    quantity: '<?=$arResult['PRODUCT']['QUANTITY'];?>',
                    butButton: {
                        classes: [
                            "trigger-button",
                            "add2basket-button"
                            <?php if(!empty($arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_BUTTON_CLASS'])):?>, "<?=$arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_BUTTON_CLASS'];?>"<?php endif;?>
                            <?php if($arResult['PRODUCT']['QUANTITY'] === 0):?>, "disabled-quantity"<?php endif;?>
                        ],
                        text: '<?=($arResult['PRODUCT']['QUANTITY'] > 0) ? Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BUY_BUTTON') : Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BUY_BUTTON_QUANTITY');?>'
                    },
                    basketButton: {
                        classes: [
                            "trigger-button",
                            "basket-button"
                            <?php if(!empty($arParams['ROBOKASSA_OPTION']['OPTIONS_BUTTON_BASKET_CLASS'])):?>, "<?=$arParams['ROBOKASSA_OPTION']['OPTIONS_BUTTON_BASKET_CLASS'];?>"<?php endif;?>
                        ],
                        text: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_BUTTON');?>'
                    },
                    lang : {
                        empty: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.EMPTY_BASKET');?>',
                        item_remove: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.BASKET_ITEM.REMOVE');?>',
                        item_quantity: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.BASKET_ITEM.QUANTITY');?>',
                        create_order: '<?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.CREATE_ORDER');?>'
                    }
                };

                let robokassaBasketButton = $('<div />');
                let robokassaBuyButton = $('<div />');

                robokassaBuyButton
                    .attr('id', 'openRobokassaAdd2basket')
                    .attr('data-product-id', <?=$arResult['PRODUCT']['ID'];?>)
                    .addClass(robokassaBuyButtonBlock.butButton.classes)
                    .text(robokassaBuyButtonBlock.butButton.text)
                ;

                robokassaBasketButton
                    .attr('id', 'openRobokassaBasket')
                    .addClass(robokassaBuyButtonBlock.basketButton.classes)
                    .text(robokassaBuyButtonBlock.basketButton.text)
                ;

                $('#' + robokassaBuyButtonBlock.block)
                    .append(robokassaBuyButton)
                    .append(robokassaBasketButton)
                ;
            </script>
        <?php endif;?>
        <div data-nosnippet id="robokassaBasket" class="popup<?php if(!empty($arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_BASKET_POPUP_CLASS'])):?> <?=$arParams['ROBOKASSA_OPTION']['BUTTON_OPTIONS_BASKET_POPUP_CLASS'];?><?php endif;?>">
            <div class="popup-content">
                <div class="popup-header">
                    <div class="h2"><?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.TITLE');?></div>
                    <span class="close-button">
                        <span class="icon"></span>
                    </span>
                </div>
                <div class="popup-body">

                    <div class="basket-list"></div>

                    <div class="basket-price">
                        <div class="text">
                            <?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.BASKET_POPUP.SUM');?>
                        </div>
                        <div class="value"></div>
                    </div>

                    <div class="order-form">

                        <div class="response-errors"></div>
                        <div class="response-success"></div>

                        <div class="order-form-row">
                            <div class="order-form-field">
                                <label>
                                    <?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.ORDER_POPUP.NAME');?>
                                </label>
                                <input type="text" name="buyerName" id="robokassa-name" />
                            </div>
                        </div>
                        <div class="order-form-row">
                            <div class="order-form-field">
                                <label>
                                    <?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.ORDER_POPUP.PHONE_NUMBER');?>
                                </label>
                                <input type="text" name="phoneNumber" id="robokassa-phone-number" />
                            </div>
                        </div>
                        <div class="order-form-row">
                            <div class="order-form-field">
                                <label>
                                    <?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.ORDER_POPUP.EMAIL');?>
                                </label>
                                <input type="email" name="buyerEmail" id="robokassa-email" />
                            </div>
                        </div>

                        <div class="make-order">
                            <div class="order-button js-create-order">
                                <?=Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.ORDER_POPUP.CREATE_ORDER');?>
                            </div>

                            <div class="make-order-payments">
                                <img src="<?=$templateFolder;?>/images/payments.svg" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </noindex>