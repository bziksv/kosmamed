<?php
namespace Rbs\MoyskladStocks\Services;

use \Bitrix\Main\Config\Option;

class BitrixFeatures
{
    public static function isUseOfferMarkingCodeGroup(): bool
    {
        return Option::get('catalog', 'use_offer_marking_code_group', 'N', '') === 'Y';
    }

    public static function isBarCodeTableExist(): bool
    {
        if(\Bitrix\Main\Loader::includeModule('catalog')) {
            return class_exists('\Bitrix\Catalog\StoreBarcodeTable');
        }
        return false;
    }
}