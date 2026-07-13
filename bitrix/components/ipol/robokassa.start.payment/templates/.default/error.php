<?php

    /**
     * @var array $arResult
     * @var array $arParams
     */

    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
    {
        die();
    }

    \Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

    ShowError(
        \Bitrix\Main\Localization\Loc::getMessage(
            $arResult['ERROR_MESSAGE'] ?? '',
            [
                '#ORDER_ID#' => $arResult["ORDER_ID"],
            ]
        )
    );