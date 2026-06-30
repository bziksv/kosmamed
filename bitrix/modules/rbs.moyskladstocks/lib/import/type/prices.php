<?php

namespace Rbs\MoyskladStocks\Import\Type;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Debug\Loger;
use Rbs\MoyskladStocks\Debug\Counter;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\Internals\IblockElementUpdaterForEvents;

use Rbs\MoyskladStocks\Compitable\ImportPricesClass;
use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;
use Rbs\MoyskladStocks\Internals\ProductFinder\StocksProductFinder;


use \Bitrix\Currency\CurrencyManager;

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

class Prices
{

    public static function update_entity_prices_from_rows($rows = [], $entity = 'product', Loger &$logger)
    {
        self::update_entity_price($rows, $entity, $logger);
    }

    public static function import_from_ms_object($itemRows = [])
    {
        $logger = new Loger();
        try {
            $seperateItemsByEntity = [];
            if(Utils::is_count($itemRows)) {
                foreach($itemRows as $row) {
                    if(!empty($row->meta->type)) {
                        $seperateItemsByEntity[$row->meta->type][] = $row;
                    }                    
                }
            }
            if(Utils::is_count($seperateItemsByEntity)) {
                foreach($seperateItemsByEntity as $entity => $rows) {
                     
                    $logger->addInfoMessage(LangMsg::get('IMPORT_FROM_MS_OBJECT_ENTITY_COUNT', [
                        '#ENTITY#' => $entity,
                        '#COUNT#' => count($rows)
                     ]));

                    self::update_entity_price($rows, $entity, $logger);
                   
                }
                unset($seperateItemsByEntity);
            } else {
                $logger->addWarningMessage(LangMsg::get('IMPORT_FROM_MS_OBJECT_EMPTY_OBJECT'));
            }
        } catch (\Throwable $e) {
            $logger->addErrorMessage(Utils::build_exception_message($e));
        }

        $logger->addFinishMessage(LangMsg::get('IMPORT_FROM_MS_OBJECT_FINISH'));

        $logger->exportLog(LangMsg::get('IMPORT_FROM_MS_OBJECT', [
            '#IMPORT_TYPE#' => LangMsg::get('AGENT_IMPORT_PRICES')
        ]));
    }

    public static function import($entity = 'product')
    {
        $agentManager = new AgentManager("{$entity}_price");
        $logger = new Loger();

        try {

            if (!Utils::is_count(ConfigurationUtils::getPriceTypeList())) {
                throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_PRICES_FOR_IMPORT'));
            }

            if (Config::isIblockRequired($entity) && Config::getIblockId($entity) <= 0) {
                throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'));
            }

            $params = [
                'limit' => $agentManager->getLimit(500),
                'offset' => $agentManager->getOffset(),
            ];

            $customFilter = [];
            if (!$agentManager->isFullUpdate()) {
                $customFilter = ['updated>=' . $agentManager->getLastDateUpdate()];
            }

            $isVariant = $entity === 'variant';
            if($isVariant) {
                $customFilter[] = 'type=variant';
            }

            $filterString = \CRbsMoyskladStocks::getFilterString($entity, implode(';', $customFilter));
            if (!empty($filterString)) {
                $params['filter'] = $filterString;
                $logger->addInfoMessage(LangMsg::buildAgentFilterMessage($filterString));
            }

            ApiNew::refreshCountRequests();
            $msResult = ApiNew::get($isVariant ? '/entity/assortment' : "/entity/{$entity}", $params);

            if (Utils::is_success($msResult)) {

                if (!empty($msResult->meta->size)) {
                    $agentManager->setSize($msResult->meta->size);
                }

                if (Utils::array_exists($msResult)) {
                    self::update_entity_price($msResult->rows, $entity, $logger);
                } else {
                    if ($agentManager->isFullUpdate()) {
                        $logger->addWarningMessage(LangMsg::get('WARNING_EMPTY_ROWS'));
                    } else {
                        $logger->addInfoMessage(LangMsg::get('INFO_EMPTY_ROWS'));
                    }
                }

                if (!empty($msResult->meta->nextHref)) {
                    $agentManager->setNextStepOffset();
                } else {
                    $agentManager->setFinalStepParams();
                }

            } else {
                if (Utils::has_errors($msResult)) {
                    $logger->addErrorMessageArray($msResult->{'errors'});
                } else {
                    throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_API_ERROR'));
                }
            }

        } catch (\Throwable $e) {
            $logger->addErrorMessage(Utils::build_exception_message($e));
        }

        $logger->addFinishMessage(LangMsg::buildAgentFinishMessage($logger->getLogTime()));
        $logger->exportLog(LangMsg::buildAgentHeadMessage($agentManager));

        return (object)[
            'logger' => $logger,
            'agentManager' => $agentManager
        ];
    }

    private static function update_entity_price($msItemsRows = [], $entity = 'product', Loger &$logger)
    {
        $priceList = ConfigurationUtils::getPriceTypeList();
        if (!Utils::is_count($priceList)) {
            throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_PRICES_FOR_IMPORT'));
        }

        $isClearPriceZero = Config::checkFeatureEntity('prices_clear_zero', $entity);
        $isPriceRangeFirst = Config::checkFeatureEntity('prices_range_first', $entity);
        $isLoadAllRanges = Config::checkFeatureEntity('prices_range_all', $entity);

        $currencies = ConfigurationUtils::getCurrencyList();
        
        $counterProductChange = new Counter(LangMsg::get('COUNTER_PRODUCT_UPDATE'));
        $counterPriceChange = new Counter(LangMsg::get('COUNTER_PRICE_UPDATE'));

        $productInfoArray = self::buildProductInfoArray($entity, $msItemsRows, $logger);

        $productParamsFetchAll = \Bitrix\Catalog\ProductTable::getList([
            'filter' => ['=ID' => array_keys($productInfoArray)]
        ])->fetchAll();
        $productParamsAll = [];
        foreach ($productParamsFetchAll as $productParams) {
            $productParamsAll[$productParams['ID']] = $productParams;
        }
        unset($productParamsFetchAll);

        $currentPriceBxList = [];
        if(Utils::is_count($productParamsAll)) {
            $tmpPriceList = $priceList;
            if (isset($tmpPriceList['price_purchase'])) {
                unset($tmpPriceList['price_purchase']);
            }
            $currentPricesBx = \Bitrix\Catalog\PriceTable::getList([
                'filter' => [
                    'PRODUCT_ID' => array_keys($productParamsAll),
                    'CATALOG_GROUP_ID' => array_keys($tmpPriceList)
                ]
            ])->fetchAll();
            unset($tmpPriceList);
            if(Utils::is_count($currentPricesBx)) {
                foreach($currentPricesBx as $currentPriceOne) {
                    $currentPriceBxList[$currentPriceOne['PRODUCT_ID']][] = $currentPriceOne;
                }
            }
            unset($currentPricesBx);
        }

        $changedProductsIds = [];

        foreach ($productInfoArray as $productId => $info) {
            $prices = $info['MS_PRICES'];
            $productParams = isset($productParamsAll[$productId]) ? $productParamsAll[$productId] : [];
            $currentPrices = isset($currentPriceBxList[$productId]) ? $currentPriceBxList[$productId] : [];
            $result = self::update($productId, $priceList, $prices, $currencies, $isClearPriceZero, $isPriceRangeFirst, $counterPriceChange, $counterProductChange, $productParams, 0, false, $isLoadAllRanges, $currentPrices);
            if($result['isChangedPrice']) {
                $changedProductsIds[] = $productId;
                IblockElementUpdaterForEvents::updateFromPrices($entity, $productId);
            }
        }

        $event = new \Bitrix\Main\Event(Config::getModuleId(true), 'OnPriceStepImported', [
            'entity' => $entity,
            'changedProductsIds' => $changedProductsIds,
            'productInfoArray' => $productInfoArray,
        ]);
        $event->send();


        $logger->addInfoMessage(LangMsg::get('COUNTER_INFO_UPDATE', $counterProductChange->getReport()));
        $logger->addInfoMessage(LangMsg::get('COUNTER_INFO', $counterPriceChange->getReport()));
    }

    private static function buildProductInfoArray($entity = 'product', $msItemsRows = [], Loger &$logger): array
    {
        $catalogIblockId = 0;
        $fModifIblock = '>';
        if (Config::isIblockRequired($entity)) {
            $fModifIblock = '=';
            $catalogIblockId = Config::getIblockId($entity);
            if ((int)$catalogIblockId <= 0) {
                throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'));
            }
        }

        $pricesMsArrayByXmlId = [];
        foreach ($msItemsRows as $row) {
            $prices = self::loadPricesFromItem($row);
            $rowXmlId = ProductIdentifier::getIdentifierValue($row);
            if (Utils::is_count($prices) && !empty($rowXmlId)) {
                $pricesMsArrayByXmlId[$rowXmlId] = $prices;
            }
        }

        if (!Utils::is_count($pricesMsArrayByXmlId)) {
            throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_PRICES_FOR_ROWS'));
        }

        $xmlIds = array_keys($pricesMsArrayByXmlId);

        $doublePricesType = Config::getDoublePricesType($entity);
        $doublesCheckList = StocksProductFinder::findElementsWithDoubles(
            $xmlIds, $entity, [$fModifIblock . 'IBLOCK_ID' => $catalogIblockId], $doublePricesType
        );

        $sourceElementCount = array_sum(array_map('count', $doublesCheckList));

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
            $logger->addWarningMessage(LangMsg::get('WARNING_STOCKS_IMOPORT_DOUBLES', [
                '#ELEMENT_COUNT#' => $sourceElementCount,
                '#DOUBLE_ELEMENT_COUNT#' => $doubleElementCount,
            ]));
        }

        return $productInfoArray;
    }
   
    public static function update($productBxId, $priceList, $prices, $currencies, $isClearPriceZero = false, $isPriceRangeFirst = false, Counter &$counterPrices, Counter &$counterProducts, $productParams = [], $uid = 0, $needUpdateElement = false, $isLoadAllRanges = false, $currentPrices = null)
    {
        if($isLoadAllRanges) {
            $isPriceRangeFirst = false;
        }

        $priceFlipList = $priceList;

        if (isset($priceList['price_purchase'])) {
            unset($priceList['price_purchase']);
        }
        $arPricesIds = array_keys($priceList);
        
        if($currentPrices === null) { //compitable variant
            $filter = [
                'PRODUCT_ID' => $productBxId,
                'CATALOG_GROUP_ID' => $arPricesIds
            ];
            $currentPrices = \Bitrix\Catalog\PriceTable::getList([
                'filter' => $filter
            ])->fetchAll();
        }        
        
        $currencies = array_flip($currencies);

        $currentPriceAdded = [];
        $isChangedPrice = false;

        foreach ($currentPrices as $priceInfo) {

            $counterPrices->count();

            if ($isPriceRangeFirst) {
                if ($isClearPriceZero && (int)$priceInfo['QUANTITY_FROM'] <= 0) {
                    \Bitrix\Catalog\Model\Price::delete($priceInfo['ID']);
                    $counterPrices->delete();
                    $isChangedPrice = true;
                    continue;
                }
                if ((int)$priceInfo['QUANTITY_FROM'] > 1) {
                    continue;
                }
            } else if(!$isLoadAllRanges) {
                if ($isClearPriceZero && (int)$priceInfo['QUANTITY_FROM'] === 1) {
                    \Bitrix\Catalog\Model\Price::delete($priceInfo['ID']);
                    $counterPrices->delete();
                    $isChangedPrice = true;
                    continue;
                }
                if ((int)$priceInfo['QUANTITY_FROM'] > 0) {
                    continue;
                }
            }

            $currentPriceAdded[] = $priceInfo['CATALOG_GROUP_ID'];

            if (!isset($priceFlipList[$priceInfo['CATALOG_GROUP_ID']])) {
                continue;
            }
            if (!isset($prices[$priceFlipList[$priceInfo['CATALOG_GROUP_ID']]])) {
                continue;
            }

            $currentPriceMs = $prices[$priceFlipList[$priceInfo['CATALOG_GROUP_ID']]]['PRICE'];

            $currencyMsId = $prices[$priceFlipList[$priceInfo['CATALOG_GROUP_ID']]]['CURRENCY'];
            $currentCurrencyMs = isset($currencies[$currencyMsId]) && $currencies[$currencyMsId] !== 'N' ? $currencies[$currencyMsId] : CurrencyManager::getBaseCurrency();

            $changes = [];

            if (
                ((float)$currentPriceMs !== (float)$priceInfo['PRICE']) ||
                ($currentCurrencyMs !== $priceInfo['CURRENCY'])
            ) {
                $changes['PRICE'] = $currentPriceMs;
                $changes['CURRENCY'] = $currentCurrencyMs;
            }
            
            if (count($changes) > 0) {
                if ($isClearPriceZero && isset($changes['PRICE']) && $changes['PRICE'] <= 0) {
                    \Bitrix\Catalog\Model\Price::delete($priceInfo['ID']);
                    $counterPrices->delete();
                } else {
                    \Bitrix\Catalog\Model\Price::update($priceInfo['ID'], $changes);
                    $counterPrices->update();
                }
                $isChangedPrice = true;
            } else {
                if ($isClearPriceZero && (float)$priceInfo['PRICE'] <= 0) {
                    \Bitrix\Catalog\Model\Price::delete($priceInfo['ID']);
                    $counterPrices->delete();
                    $isChangedPrice = true;
                }
            }
        }
        
        if (count($arPricesIds) !== count($currentPriceAdded)) {
            foreach ($arPricesIds as $priceId) {
                if (!in_array($priceId, $currentPriceAdded) && isset($prices[$priceFlipList[$priceId]])) {

                    $counterPrices->count();

                    $currentPriceMs = $prices[$priceFlipList[$priceId]]['PRICE'];
                    $currentCurrencyMs = $prices[$priceFlipList[$priceId]]['CURRENCY'];

                    if ($currentPriceMs <= 0 && $isClearPriceZero) {
                        continue;
                    }

                    $changes = [
                        'PRODUCT_ID' => $productBxId,
                        'CATALOG_GROUP_ID' => $priceId,
                        'PRICE' => $currentPriceMs,
                        'CURRENCY' => isset($currencies[$currentCurrencyMs]) && $currencies[$currentCurrencyMs] !== 'N' ? $currencies[$currentCurrencyMs] : CurrencyManager::getBaseCurrency()
                    ];

                    if ($isPriceRangeFirst) {
                        $changes += ['QUANTITY_FROM' => 1];
                    }

                    \Bitrix\Catalog\Model\Price::add($changes);
                    $counterPrices->add();

                    $isChangedPrice = true;
                }
            }
        }

        $counterProducts->count();
        
        if (isset($priceFlipList['price_purchase']) && $priceFlipList['price_purchase'] !== 'N') {

            if(empty($productParams['ID'])) {
                $productParams = \Bitrix\Catalog\ProductTable::getList([
                    'filter' => ['=ID' => $productBxId]
                ])->fetch();
            }

            if ($productParams['ID'] > 0) {

                $changedVals = [];

                if ((float)$productParams['PURCHASING_PRICE'] !== (float)$prices[$priceFlipList['price_purchase']]['PRICE']) {
                    $changedVals['PURCHASING_PRICE'] = (float)$prices[$priceFlipList['price_purchase']]['PRICE'];
                }

                if (!empty($prices[$priceFlipList['price_purchase']]['CURRENCY'])) {
                    $currencyMsId = $prices[$priceFlipList['price_purchase']]['CURRENCY'];
                    $currentCurrencyMs = isset($currencies[$currencyMsId]) && $currencies[$currencyMsId] !== 'N' ? $currencies[$currencyMsId] : CurrencyManager::getBaseCurrency();
                    if ($productParams['PURCHASING_CURRENCY'] !== $currentCurrencyMs) {
                        $changedVals['PURCHASING_CURRENCY'] = $currentCurrencyMs;
                    }
                }

                if (count($changedVals) > 0) {
                    \Bitrix\Catalog\Model\Product::update($productBxId, $changedVals);
                    $isChangedPrice = true;
                    $counterProducts->update();
                }

            }

        }

        return [
            'isChangedPrice' => $isChangedPrice,
        ];
    }

    public static function loadPricesFromItem($row = null): array
    {
        $result = [];
        if (is_object($row)) {
            $salesPrices = Utils::array_exists($row, 'salePrices') ? $row->salePrices : [];
            $result = self::loadPrices($salesPrices, [
                'minPrice' => $row->minPrice,
                'buyPrice' => $row->buyPrice
            ]);
        }
        return $result;
    }

    public static function loadPrices($salePrices = [], $arDefPrices = []): array
    {
        $prices = [];
        if (Utils::is_count($salePrices)) {
            foreach ($salePrices as $price) {
                if (!empty($price->priceType->id) && !empty($price->currency->meta->href)) {
                    $prices[$price->priceType->id] = [
                        'PRICE' => !empty($price->value) ? (int)$price->value / 100 : 0,
                        'CURRENCY' => array_pop(explode('/', $price->currency->meta->href))
                    ];
                }
            }
        }
        if (Utils::is_count($arDefPrices)) {
            foreach ($arDefPrices as $priceId => $priceParams) {
                if (!empty($priceParams->currency->meta->href)) {
                    $prices[$priceId] = [
                        'PRICE' => !empty($priceParams->value) ? (int)$priceParams->value / 100 : 0,
                        'CURRENCY' => array_pop(explode('/', $priceParams->currency->meta->href))
                    ];
                }
            }
        }
        return $prices;
    }

    /** @deprecated */
    public static function updateAll($limit = 1000, &$offset = 0, $entity = '', $items = null)
    {
        return ImportPricesClass::updateAll($limit, $offset, $entity, $items);
    }

    /** @deprecated */
    public static function updatePricesByItems($items = null, $entity = 'product', $limit = 100, $offset = 0)
    {
        return ImportPricesClass::updatePricesByItems($items, $entity, $limit, $offset);
    }
}
