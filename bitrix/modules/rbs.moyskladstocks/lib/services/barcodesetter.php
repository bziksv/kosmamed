<?php

namespace Rbs\MoyskladStocks\Services;

class BarcodeSetter
{
    public static function setBarCodes($elId, array $sourceBarcodeList, array $bxBarcodeList, $isMultiple = false, $uid = 1)
    {
        $msBarcodeList = [];
        if(count($sourceBarcodeList) > 0) {
            foreach($sourceBarcodeList as $barcode) {
                $barcodeValue = current((array)$barcode);
                if(!empty($barcodeValue)) {
                    $msBarcodeList[] = $barcodeValue;
                    if(!$isMultiple) {
                        break;
                    }
                }
            }
        }

        $currentBarcodes = array_flip($bxBarcodeList);
        
        if(empty($msBarcodeList)) {
            foreach($bxBarcodeList as $id => $barcode) {
                \Bitrix\Catalog\StoreBarcodeTable::delete($id);
            }
            return;
        }

        $barcodesToAdd = array_diff($msBarcodeList, array_values($bxBarcodeList));        
        $barcodesToDelete = array_diff(array_values($bxBarcodeList), $msBarcodeList);

        foreach($barcodesToDelete as $barcodeToDelete) {
            if(isset($currentBarcodes[$barcodeToDelete])) {
                \Bitrix\Catalog\StoreBarcodeTable::delete($currentBarcodes[$barcodeToDelete]);
            }
        }

        foreach($barcodesToAdd as $barcodeToAdd) {
            \Bitrix\Catalog\StoreBarcodeTable::add([
                'PRODUCT_ID' => $elId,
                'BARCODE' => $barcodeToAdd,
                'STORE_ID' => 0,
                'CREATED_BY' => $uid,
                'MODIFIED_BY' => $uid,
                'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime(),
            ]);
        }
    }

    public static function add($elId, array $barcodeList, $isMultiple = false, $uid = 1)
    {
        if(count($barcodeList) > 0) {
            foreach($barcodeList as $barcode) {
                $barcodeValue = current((array)$barcode);

                if(!empty($barcodeValue)) {
                    \Bitrix\Catalog\StoreBarcodeTable::add([
                        'PRODUCT_ID' => $elId,
                        'BARCODE' => $barcodeValue,
                        'STORE_ID' => 0,
                        'CREATED_BY' => $uid,
                        'MODIFIED_BY' => $uid,
                        'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                        'DATE_MODIFY' => new \Bitrix\Main\Type\DateTime(),
                    ]);
                    if(!$isMultiple) {
                        break;
                    }
                }

            }
        }
    }
}