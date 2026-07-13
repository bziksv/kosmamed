<?php
namespace Rbs\MoyskladStocks\Services;

use Rbs\MoyskladStocks\Utils;

\Bitrix\Main\Loader::includeModule('catalog');

class CatalogUtils
{
    public static function getMeasureRatioList(array $itemBxIds = []): array
    {
        $measureRatioList = [];
        $measureRatioListResult = \Bitrix\Catalog\MeasureRatioTable::getList([
            'select' => array('ID', 'RATIO', 'PRODUCT_ID'),
            'filter' => array('=PRODUCT_ID' => array_values($itemBxIds))
        ])->fetchAll();
        if (Utils::is_count($measureRatioListResult)) {
            foreach ($measureRatioListResult as $measrueRatioItemForProductId) {
                $measureRatioList[$measrueRatioItemForProductId['PRODUCT_ID']] = $measrueRatioItemForProductId;
            }
        }
        return $measureRatioList;
    }

    public static function getProductArray(array $itemBxIds = [], bool $needUfFields = false): array
    {
        $result = [];
        if(count($itemBxIds) > 0){

            $select = ['*'];
            if($needUfFields) {
                $select[] = 'UF_*';
            }

            $rsItem = \Bitrix\Catalog\ProductTable::getList([
                'filter' => ['=ID' => array_values($itemBxIds)],
                'select' => $select,
                'cache' => ['ttl' => 0]
            ])->fetchAll();
            
            foreach($rsItem as $item){
                $result[$item['ID']] = $item;
            }
        }

        return $result;
    }

    public static function getProductIdsByXmlIds(array $itemXmlIds = [], int $iblockId = 0): array
    {
        $result = [];
        if(count($itemXmlIds) > 0 && (int)$iblockId > 0){

            $rsItem = \Bitrix\Iblock\ElementTable::getList([
                'filter' => ['=IBLOCK_ID' => $iblockId, '=XML_ID' => array_values($itemXmlIds)],
                'select' => ['ID', 'XML_ID'],
                'cache' => ['ttl' => 0]
            ])->fetchAll();
            
            foreach($rsItem as $item){
                $result[$item['XML_ID']] = $item['ID'];
            }
        }
        
        return $result;
    }

    public static function getBarcodeList(array $itemBxIds = []): array
    {
        $result = [];
        
        if(count($itemBxIds) > 0 && class_exists('\Bitrix\Catalog\StoreBarcodeTable')) {
            $rsItem = \Bitrix\Catalog\StoreBarcodeTable::getList([
                'filter' => ['=PRODUCT_ID' => array_values($itemBxIds), '=STORE_ID' => 0, '=ORDER_ID' => null],
                'select' => ['PRODUCT_ID', 'BARCODE', 'ID'],
                'cache' => ['ttl' => 0]
            ])->fetchAll();
            foreach($rsItem as $item){
                $result[$item['PRODUCT_ID']][$item['ID']] = $item['BARCODE'];
            }
        }

        return $result;
    }
}