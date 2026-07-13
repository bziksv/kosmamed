<?php

namespace Rbs\MoyskladStocks\Compitable;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Debug\Loger;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Debug\Counter;
use Rbs\MoyskladStocks\ApiNew;

use Rbs\MoyskladStocks\Import\Type\Prices;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

class ImportPricesClass
{
    public static function updateAll($limit = 1000, &$offset = 0, $entity = '', $items = null)
    {
        $loger = new Loger();
        
        do {

            $priceList = Config::getPrices();
            if (!Utils::is_count($priceList)) {
                $loger->addWarningMessage(LangMsg::get('WARNING_EMPTY_PRICES_FOR_IMPORT'));
                break;
            }

            $catalogIblockId = 0;
            $fModifIblock = '>';
            if (Config::isIblockRequired($entity)) {
                $fModifIblock = '=';
                $catalogIblockId = Config::getIblockId($entity);
                if ((int)$catalogIblockId <= 0) {
                    $loger->addWarningMessage(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'));
                    break;
                }
            }

            $currencies = Config::getCurrencyList();

            if ($items === null) {

                $customFilter = '';
                $lastDateUpdate = Config::getLastDateUpdate('prices_' . $entity);
                if (Config::checkFeature("{$entity}_prices_up_date")) {
                    $customFilter = 'updated>=' . $lastDateUpdate;
                }

                try {
                    $filterString = \CRbsMoyskladStocks::getFilterString($entity, $customFilter);
                } catch (\Throwable $e) {
                    $loger->addErrorMessage(Utils::build_exception_message($e));
                    break;
                }
                
                if (!empty($filterString)) {
                    $loger->addInfoMessage(LangMsg::get('INFO_API_FILTER', [
                        '#FILTER#' => $filterString
                    ]));
                }

                $filter = ['limit' => $limit, 'offset' => $offset, 'filter' => $filterString];
                $items = ApiNew::get("/entity/{$entity}", $filter);
                unset($filter);

                if (Utils::is_success($items)) {
                    if (!empty($items->meta->nextHref)) {
                        Config::setLastDateUpdate('prices_' . $entity, $lastDateUpdate);
                    } else {
                        if (Utils::array_exists($items)) {
                            Config::setLastDateUpdate('prices_' . $entity);
                        }
                    }
                }
            }

            $isClearPriceZero = Config::checkFeatureEntity('prices_clear_zero', $entity);
            $isPriceRangeFirst = Config::checkFeatureEntity('prices_range_first', $entity);
            $isLoadAllRanges = Config::checkFeatureEntity('prices_range_all', $entity);
            $doublePricesType = Config::getDoublePricesType($entity);

            if (Utils::is_success($items)) {

                if (!empty($items->meta->nextHref)) {
                    $offset += $limit;
                } else {
                    $offset = 0;
                }

                if (Utils::array_exists($items)) {

                    $pricesMsArrayByXmlId = [];
                    foreach ($items->rows as $row) {
                        $prices = Prices::loadPricesFromItem($row);
                        $xmlId = ProductIdentifier::getIdentifierValue($row);
                        if (Utils::is_count($prices) && !empty($xmlId)) {
                            $pricesMsArrayByXmlId[$xmlId] = $prices;
                        }
                    }

                    if (!Utils::is_count($pricesMsArrayByXmlId)) {
                        $loger->addWarningMessage(LangMsg::get('WARNING_EMPTY_PRICES_FOR_ROWS'));
                        break;
                    }

                    $xmlIds = array_keys($pricesMsArrayByXmlId);

                    $filter = ProductIdentifier::buildBatchFilter($xmlIds, $entity);
                    $filter[$fModifIblock . 'IBLOCK_ID'] = $catalogIblockId;

                    $rsIblockElements = \Bitrix\Iblock\ElementTable::getList([
                        'order' => ['ID' => $doublePricesType === 'DESC' ? 'DESC' : 'ASC'],
                        'filter' => $filter,
                        'select' => ['ID', 'XML_ID']
                    ])->fetchAll();

                    $sourceElementCount = count($rsIblockElements);

                    $doublesCheckList = [];
                    foreach ($rsIblockElements as $element) {
                        $xmlId = ProductIdentifier::extractXmlId($element['XML_ID'], $entity);
                        $doublesCheckList[$xmlId][$element['ID']] = '';
                    }
                    unset($rsIblockElements);

                    $productInfoArray = [];
                    $doubleElementCount = 0;
                    foreach ($doublesCheckList as $xmlId => $elements) {
                        if (isset($pricesMsArrayByXmlId[$xmlId])) {

                            $isDouble = count($elements) > 1;

                            switch ($doublePricesType) {
                                case 'ASC':
                                case 'DESC':
                                    $productInfoArray[array_key_first($elements)] = [
                                        'MS_PRICES' => $pricesMsArrayByXmlId[$xmlId]
                                    ];
                                    break;
                                case 'ALL':
                                    foreach ($elements as $elId => $element) {
                                        $productInfoArray[$elId] = [
                                            'MS_PRICES' => $pricesMsArrayByXmlId[$xmlId]
                                        ];
                                    }
                                    break;
                                case 'SKIP':
                                    if (!$isDouble) {
                                        $productInfoArray[array_key_first($elements)] = [
                                            'MS_PRICES' => $pricesMsArrayByXmlId[$xmlId]
                                        ];
                                    }
                                    break;
                            }

                            if ($isDouble) {
                                $doubleElementCount++;
                            }
                        }
                    }
                    unset($doublesCheckList);
                    unset($pricesMsArrayByXmlId);

                    if ($doubleElementCount > 0) {
                        $loger->addWarningMessage(LangMsg::get('WARNING_STOCKS_IMOPORT_DOUBLES', [
                            '#ELEMENT_COUNT#' => $sourceElementCount,
                            '#DOUBLE_ELEMENT_COUNT#' => $doubleElementCount,
                        ]));
                    }

                    $counterProductChange = new Counter(LangMsg::get('COUNTER_PRODUCT_UPDATE'));
                    $counterPriceChange = new Counter(LangMsg::get('COUNTER_PRICE_UPDATE'));

                    $productParamsFetchAll = \Bitrix\Catalog\ProductTable::getList([
                        'filter' => ['=ID' => array_keys($productInfoArray)]
                    ])->fetchAll();

                    $productParamsAll = [];
                    foreach ($productParamsFetchAll as $productParams) {
                        $productParamsAll[$productParams['ID']] = $productParams;
                    }
                    unset($productParamsFetchAll);

                    foreach ($productInfoArray as $productId => $info) {
                        $prices = $info['MS_PRICES'];
                        $productParams = isset($productParamsAll[$productId]) ? $productParamsAll[$productId] : [];
                        Prices::update($productId, $priceList, $prices, $currencies, $isClearPriceZero, $isPriceRangeFirst, $counterPriceChange, $counterProductChange, $productParams, 0, false, $isLoadAllRanges);
                    }

                    $loger->addInfoMessage(LangMsg::get('COUNTER_INFO_UPDATE', $counterProductChange->getReport()));
                    $loger->addInfoMessage(LangMsg::get('COUNTER_INFO', $counterPriceChange->getReport()));
                } else {

                    $loger->addWarningMessage(LangMsg::get('WARNING_EMPTY_ROWS'));
                }
            } else {

                if (Utils::has_errors($items)) {
                    $loger->addErrorMessageArray($items->{'errors'});
                } else {
                    $loger->addErrorMessage(LangMsg::get('EXCEPTION_API_ERROR'));
                }
            }
            
        } while(false);

        $loger->addInfoMessage(LangMsg::get('AGENT_FINISH', [
            '#API_COUNT#' => ApiNew::getCountRequests(),
            '#AGENT_TIME#' => $loger->getLogTime()
        ]));

        global $isHookScript;
        if(!$isHookScript){
            $loger->exportLog(LangMsg::get('AGENT_START_ENTITY', [
                '#AGENT_NAME#' => LangMsg::get('AGENT_IMPORT_PRICES'),
                '#ENTITY#' => $entity,
                '#LIMIT#' => $limit,
                '#OFFSET#' => $offset,
            ]));
        }

    }

    public static function updatePricesByItems($items = null, $entity = 'product', $limit = 100, $offset = 0)
    {
        if ($items === null) {
            return false;
        }

        $priceList = Config::getPrices();
        if (count($priceList) <= 0) {
            return false;
        }

        $catalogIblockId = 0;
        $fModifIblock = '>';
        if (Config::isIblockRequired($entity)) {
            $fModifIblock = '=';
            $catalogIblockId = Config::getIblockId($entity);
            if ((int)$catalogIblockId <= 0) {
                return false;
            }
        }

        $currencies = Config::getCurrencyList();

        $isClearPriceZero = Config::checkFeatureEntity('prices_clear_zero', $entity);
        $isPriceRangeFirst = Config::checkFeatureEntity('prices_range_first', $entity);

        if (Utils::is_success($items)) {

            if (Utils::array_exists($items)) {

                $counterProductChange = new Counter(LangMsg::get('COUNTER_PRODUCT_UPDATE'));
                $counterPriceChange = new Counter(LangMsg::get('COUNTER_PRICE_UPDATE'));

                foreach ($items->rows as $row) {

                    $prices = Prices::loadPricesFromItem($row);

                    $xmlId = ProductIdentifier::getIdentifierValue($row);
                    if (Utils::is_count($prices) && !empty($xmlId)) {
                        $filter = ProductIdentifier::buildSingleFilter($xmlId, $row->meta->type);
                        $filter[$fModifIblock . 'IBLOCK_ID'] = $catalogIblockId;
                        $element = \Bitrix\Iblock\ElementTable::getList(['filter' => $filter])->fetch();
                        if ((int)$element['ID'] > 0) {
                            Prices::update($element['ID'], $priceList, $prices, $currencies, $isClearPriceZero, $isPriceRangeFirst, $counterPriceChange, $counterProductChange);
                        }
                    }
                }
            }
        } else {
            return false;
        }
    }
}