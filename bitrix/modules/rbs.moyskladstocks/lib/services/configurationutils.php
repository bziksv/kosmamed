<?php
namespace Rbs\MoyskladStocks\Services;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;

class ConfigurationUtils
{
    public static function getCurrencyList(): array
    {
        $result = [];

        if(!\Bitrix\Main\Loader::includeModule('currency')){
            return $result;
        }

        $currencyBx = \Bitrix\Currency\CurrencyLangTable::getList([
            'filter' => [
                '=LID' => 'ru'
            ],
            'cache' => [
                'ttl' => 86400
            ]
        ])->fetchAll();

        if(Utils::is_count($currencyBx)) {
            foreach ($currencyBx as $row) {
                $result[$row['CURRENCY']] = Config::getOption("currency_{$row['CURRENCY']}", 'N');
            }
        }
        
        return $result;
    }

    public static function getIblockSymbolicCodeParams(int $iblockId = 0, string $type = 'CODE'): array
    {
        $result = [
            'required' => false,
            'uniq' => false
        ];

        if(!\Bitrix\Main\Loader::includeModule('iblock')){
            return $result;
        }

        if($iblockId > 0){
            $iblockFields = \CIblock::GetFields($iblockId);
            $result = [
                'required' => $iblockFields[$type]['IS_REQUIRED'] === 'Y',
                'uniq' => $iblockFields[$type]['DEFAULT_VALUE']['UNIQUE'] === 'Y'
            ];
        }

        return $result;
    }

    public static function getIblockElementTranslitParams(int $iblockId = 0)
    {
        $result = [];

        if(!\Bitrix\Main\Loader::includeModule('iblock')){
            return $result;
        }

        if($iblockId > 0){
            $iblockFields = \CIblock::GetFields($iblockId);
            if(!empty($iblockFields['CODE']) && !empty($iblockFields['CODE']['DEFAULT_VALUE'])){
                if((int)$iblockFields['CODE']['DEFAULT_VALUE']['TRANS_LEN'] > 0){
                    $result['max_len'] = (int)$iblockFields['CODE']['DEFAULT_VALUE']['TRANS_LEN'];
                }
                if($iblockFields['CODE']['DEFAULT_VALUE']['TRANS_CASE']){
                    $result['change_case'] =$iblockFields['CODE']['DEFAULT_VALUE']['TRANS_CASE'];
                }
                $result['replace_space'] = false;
                if($iblockFields['CODE']['DEFAULT_VALUE']['TRANS_SPACE']){
                    $result['replace_space'] =$iblockFields['CODE']['DEFAULT_VALUE']['TRANS_SPACE'];
                }
                if($iblockFields['CODE']['DEFAULT_VALUE']['TRANS_OTHER']){
                    $result['replace_other'] =$iblockFields['CODE']['DEFAULT_VALUE']['TRANS_OTHER'];
                }
            }
        }

        return $result;
    }

    public static function getIblockSectionTranslitParams(int $iblockId = 0): array
    {
        $result = [];

        if(!\Bitrix\Main\Loader::includeModule('iblock')){
            return $result;
        }

        if($iblockId > 0){
            $iblockFields = \CIblock::GetFields($iblockId);
            if(!empty($iblockFields['SECTION_CODE']) && !empty($iblockFields['SECTION_CODE']['DEFAULT_VALUE'])){
                if((int)$iblockFields['SECTION_CODE']['DEFAULT_VALUE']['TRANS_LEN'] > 0){
                    $result['max_len'] = (int)$iblockFields['SECTION_CODE']['DEFAULT_VALUE']['TRANS_LEN'];
                }
                if($iblockFields['SECTION_CODE']['DEFAULT_VALUE']['TRANS_CASE']){
                    $result['change_case'] =$iblockFields['SECTION_CODE']['DEFAULT_VALUE']['TRANS_CASE'];
                }
                $result['replace_space'] = false;
                if($iblockFields['SECTION_CODE']['DEFAULT_VALUE']['TRANS_SPACE']){
                    $result['replace_space'] =$iblockFields['SECTION_CODE']['DEFAULT_VALUE']['TRANS_SPACE'];
                }
                if($iblockFields['SECTION_CODE']['DEFAULT_VALUE']['TRANS_OTHER']){
                    $result['replace_other'] =$iblockFields['SECTION_CODE']['DEFAULT_VALUE']['TRANS_OTHER'];
                }
            }
        }

        return $result;
    }

    public static function getPriceTypeList(): array
    {
        $result = [];

        if(!\Bitrix\Main\Loader::includeModule('catalog')){
            return $result;
        }

        $priceOpt = 'price_purchase';
        $priceMs = Config::getOption($priceOpt);
        if(!empty($priceMs) && $priceMs !== 'N'){
            $result[$priceOpt] = $priceMs;
        }

        $pricesBx = \Bitrix\Catalog\GroupTable::getList(['cache' => ['ttl' => 86400]])->fetchAll();
        if(Utils::is_count($pricesBx)){
            foreach($pricesBx as $price){
                $priceOpt = 'price_' . $price['ID'];
                $priceMs = Config::getOption($priceOpt);
                if(!empty($priceMs) && $priceMs !== 'N'){
                    $result[$price['ID']] = $priceMs;
                }
            }
        }

        return $result;
    }

    /**
     * @param string $type default | current
     * @return array
     */
    public static function getStoreList($type = 'default'): array
    {
        $result = [];
        
        if(!\Bitrix\Main\Loader::includeModule('catalog')){
            return $result;
        }

        $storesBx = \Bitrix\Catalog\StoreTable::getList([
            'filter' => [
                'ACTIVE' => 'Y'
            ],
            'cache' => [
                'ttl' => 86400
            ]
        ])->fetchAll();

        if(Utils::is_count($storesBx)){
            foreach($storesBx as $store){

                $storeOpt = $type === 'default' ? 'store_' . $store['ID'] : 'curr_stocks_store_' . $store['ID'];

                $storeMs = Config::getOption($storeOpt);
                if(!empty($storeMs) && $storeMs !== 'N'){
                    $result[$store['ID']] = $storeMs;
                }

            }
        }
        
        return $result;
    }

    public static function getVatList(): array
    {
        $result = [];

        $vatListRs = \CCatalogVat::GetListEx([], ['ACTIVE' => 'Y']);
        while($ob = $vatListRs->GetNext()) {
            if($ob['EXCLUDE_VAT'] === 'Y') {
                $result['N'] = $ob['ID'];
            } else {
                $result[(float)round((float)$ob['RATE'], 2)] = $ob['ID'];
            }
        }

        return $result;
    }

    public static function getMeasureList(): array
    {
        $result = [];

        $arMeasure = \Bitrix\Catalog\MeasureTable::getList()->fetchAll();
        if (Utils::is_count($arMeasure)) {
            foreach ($arMeasure as $measure) {
                $result[$measure['CODE']] = $measure['ID'];
            }
        }

        return $result;
    }
}