<?php

namespace Rbs\MoyskladStocks\Import;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\AgentManager;

use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;
use Rbs\MoyskladStocks\Internals\ProductFinder\DiscountProductFinder;
use Rbs\MoyskladStocks\Import\Type\Prices;

use Bitrix\Main\Loader;

class Discount
{

    public static function importOneDiscountFromHref($discountHref = '')
    {
        $discount = ApiNew::get($discountHref, ['expand' => 'productFolders,assortment']);
        if (Utils::is_success($discount)) {
            $loger = new Debug\Loger();
            self::importRows([$discount], $loger);
        }
    }

    public static function import()
    {
        $logger = new Debug\Loger();
        $agentManager = new AgentManager('discount');
        $agentManager->setOnlyFullUpdate();

        try {

            $params = [
                'limit' => $agentManager->getLimit(100),
                'offset' => $agentManager->getOffset(0),
                'expand' => 'productFolders,assortment'
            ];

            ApiNew::refreshCountRequests();
            $msResult =  ApiNew::get('/entity/specialpricediscount', $params);

            if (Utils::is_success($msResult)) {
                
                if (!empty($msResult->meta->size)) {
                    $agentManager->setSize($msResult->meta->size);
                }

                if (Utils::array_exists($msResult)) {
                    self::importRows($msResult->rows, $logger);
                } else {
                    $logger->addWarningMessage(LangMsg::get('WARNING_EMPTY_ROWS'));
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

    private static function importRows(array $rows = [], Debug\Loger &$logger)
    {
        $discountModuleId = Config::getDiscountModuleId();
        if(Loader::includeModule($discountModuleId)) {
            if (Utils::is_count($rows)) {

                $counterDiscountPriceType = new Debug\Counter(LangMsg::get('COUNTER_DISCOUNT_PRICE_UPDATE'));
                $counterDiscountPercentType = new Debug\Counter(LangMsg::get('COUNTER_DISCOUNT_PERCENT_UPDATE'));

                foreach ($rows as $discount) {
                    if (!$discount->allAgents || $discount->allProducts) {
                        $logger->addWarningMessage(LangMsg::get('WARNING_DISCOUNT_CONDITIONS_FAIL', [
                            '#DISCOUNT_NAME#' => $discount->name
                        ]));
                        continue;
                    }
                    switch ($discountModuleId) {
                        case 'sale':
                            if ($discount->usePriceType) {
                                self::delelteDiscountById(self::buildDiscountXmlId($discount->id));
                                if (!empty($discount->specialPrice->priceType->id)) {
                                    self::addDiscountByPriceType($discount, $discount->specialPrice->priceType->id, $counterDiscountPriceType, $logger);
                                }
                            } elseif ((int)$discount->discount > 0) {
                                self::addDiscountByPercent($discount, $counterDiscountPercentType);
                            }
                            break;
                    }
                }

                $logger->addInfoMessage(LangMsg::get('COUNTER_INFO_ADD_UPDATE', $counterDiscountPriceType->getReport()));
                $logger->addInfoMessage(LangMsg::get('COUNTER_INFO_ADD_UPDATE', $counterDiscountPercentType->getReport()));

            }
        } else {
            throw new \Bitrix\Main\SystemException(LangMsg::get('MODULE_INSTALL_ERROR', ['#MODULE_ID#' => $discountModuleId]));
        }
        
    }

    private static function addDiscountByPriceType($discount, $msPriceTypeId = '', Debug\Counter &$counter, Debug\Loger &$logger = null)
    {
        $assorment = self::findAssortmentWithPrice($discount, $msPriceTypeId, $logger);
        if (Utils::is_count($assorment)) {
            foreach ($assorment as $productId => $priceValue) {
                self::addDiscountByPrice($discount, $productId, $priceValue, $counter);
            }
        }

        $currentDiscounts = self::getAllProductDiscounts(self::buildDiscountXmlId($discount->id));

        if (Utils::is_count($currentDiscounts) > 0) {
            if (Utils::is_count($assorment)) {
                foreach ($assorment as $productId => $priceValue) {
                    if (isset($currentDiscounts[self::buildDiscountXmlId($discount->id) . '_' . $productId])) {
                        unset($currentDiscounts[self::buildDiscountXmlId($discount->id) . '_' . $productId]);
                    }
                }
            }
            if(Utils::is_count($currentDiscounts)) {
                $discountObject = new \CSaleDiscount();
                foreach ($currentDiscounts as $xmlId => $discountId) {
                    $discountObject->delete($discountId);
                    $counter->delete();
                }
            }
            
        }
    }

    private static function addDiscountByPrice($discount, $productId = 0, $priceValue = 0, Debug\Counter &$counter)
    {
        $fieldsDiscount = [
            'LID' => Config::getOption('ds_site_id'),
            'NAME' => '[' . Config::getModuleId() . '] ' . $discount->name . ' (' . $productId . ')' ,
            'LAST_LEVEL_DISCOUNT' => Config::getOption('ds_last_level') === 'Y' ? 'Y' : 'N',
            'LAST_DISCOUNT' => Config::getOption('ds_last') === 'Y' ? 'Y' : 'N',
            'SORT' => (int)Config::getOption('ds_sort'),
            'PRIORITY' => (int)Config::getOption('ds_priority'),
            'CURRENCY' => Config::getOption('ds_currency'),
            'ACTIVE' => $discount->active ? 'Y' : 'N',
            'COUPON_ADD' => 'N',
            "CONDITIONS" =>  [
                'CLASS_ID' => 'CondGroup',
                'DATA' => [
                    'All' => 'AND',
                    'True' => 'True',
                ],
                'CHILDREN' => []
            ],
            'ACTIONS' => [
                "CLASS_ID" => "CondGroup",
                "DATA" => [
                "All" => "AND"
                ]
            ]
        ];

        $userGroups = Config::getOptionArray('ds_user_groups');
        if (count($userGroups) > 0) {
            $fieldsDiscount['USER_GROUPS'] = Config::getOptionArray('ds_user_groups');
        } else {
            $fieldsDiscount['USER_GROUPS'] = [1];
        }

        $priceTypes = Config::getOptionArray('ds_price_type');
        $fieldsDiscount['ACTIONS']['CHILDREN'][0] = [
            'CLASS_ID' => 'ActSaleBsktGrp',
            'DATA' => [
                'Type' => 'Closeout',
                'Value' => $priceValue,
                'Unit' => 'CurEach',
                'Max' => 0,
                'All' => 'AND',
                'True' => 'True'
            ]
        ];

        if (count($priceTypes) > 0) {
            $fieldsDiscount['ACTIONS']['CHILDREN'][0]['CHILDREN'][] = [
                'CLASS_ID' => 'ActSaleSubGrp',
                'DATA' => [
                    'All' => 'AND',
                    'True' => 'True'
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'CondCatalogPriceType',
                        'DATA' => [
                            'logic' => 'Equal',
                            'value' => $priceTypes
                        ]
                    ]
                ]
            ];
        }

        $fieldsDiscount['ACTIONS']['CHILDREN'][0]['CHILDREN'][] = [
            'CLASS_ID' => 'ActSaleSubGrp',
            'DATA' => [
                'All' => 'OR',
                'True' => 'True'
            ],
            'CHILDREN' => [
                [
                    'CLASS_ID' => 'CondIBElement',
                    'DATA' => [
                        'logic' => 'Equal',
                        'value' => [
                            $productId
                        ]
                    ]
                ]
            ]
        ];

        $counter->count();
        $currentDiscount = self::findDiscountById(self::buildDiscountXmlId($discount->id) . '_' . $productId);
        if ($currentDiscount === null) {
            $fieldsDiscount['XML_ID'] = self::buildDiscountXmlId($discount->id) . '_' . $productId;
            $discount = \CSaleDiscount::Add($fieldsDiscount);
            $counter->add();
        } else {
            \CSaleDiscount::Update($currentDiscount['ID'], $fieldsDiscount);
            $counter->update();
        }
    }

    private static function addDiscountByPercent($discount, Debug\Counter &$counter)
    {
        self::deleteDiscountsByPrice(self::buildDiscountXmlId($discount->id));

        $discountValue = (int)$discount->discount;
        $fieldsDiscount = [
            'LID' => Config::getOption('ds_site_id'),
            'NAME' => '[' . Config::getModuleId() . '] ' . $discount->name,
            'LAST_LEVEL_DISCOUNT' => Config::getOption('ds_last_level') === 'Y' ? 'Y' : 'N',
            'LAST_DISCOUNT' => Config::getOption('ds_last') === 'Y' ? 'Y' : 'N',
            'SORT' => (int)Config::getOption('ds_sort'),
            'PRIORITY' => (int)Config::getOption('ds_priority'),
            'CURRENCY' => Config::getOption('ds_currency'),
            'ACTIVE' => $discount->active ? 'Y' : 'N',
            'COUPON_ADD' => 'N',
            "CONDITIONS" =>  [
                'CLASS_ID' => 'CondGroup',
                'DATA' => [
                    'All' => 'AND',
                    'True' => 'True',
                ],
                'CHILDREN' => []
            ],
            'ACTIONS' => [
                "CLASS_ID" => "CondGroup",
                    "DATA" => [
                    "All" => "AND"
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'ActSaleBsktGrp',
                        'DATA' => [
                            'Type' => 'Discount',
                            'Value' => $discountValue,
                            'Unit' => 'Perc',
                            'Max' => 0,
                            'All' => 'AND',
                            'True' => 'True'
                        ]
                    ]
                ]
            ]
        ];

        $userGroups = Config::getOptionArray('ds_user_groups');
        if (count($userGroups) > 0) {
            $fieldsDiscount['USER_GROUPS'] = Config::getOptionArray('ds_user_groups');
        } else {
            $fieldsDiscount['USER_GROUPS'] = [1];
        }

        $priceTypes = Config::getOptionArray('ds_price_type');
        if (count($priceTypes) > 0) {
            $fieldsDiscount['ACTIONS']['CHILDREN'][0]['CHILDREN'][] = [
                'CLASS_ID' => 'ActSaleSubGrp',
                'DATA' => [
                    'All' => 'AND',
                    'True' => 'True'
                ],
                'CHILDREN' => [
                    [
                        'CLASS_ID' => 'CondCatalogPriceType',
                        'DATA' => [
                            'logic' => 'Equal',
                            'value' => $priceTypes
                        ]
                    ]
                ]
            ];
        }

        $childrens = self::getChildrenCondsForDiscount($discount);
        if (count($childrens) > 0) {
            $fieldsDiscount['ACTIONS']['CHILDREN'][0]['CHILDREN'][] = [
            'CLASS_ID' => 'ActSaleSubGrp',
            'DATA' => [
               'All' => 'OR',
               'True' => 'True'
            ],
            'CHILDREN' => $childrens
         ];
        } else {
            $fieldsDiscount['ACTIVE'] = 'N';
        }

        $counter->count();
        $currentDiscount = self::findDiscountById(self::buildDiscountXmlId($discount->id));
        if ($currentDiscount === null) {
            $fieldsDiscount['XML_ID'] = self::buildDiscountXmlId($discount->id);
            $discount = \CSaleDiscount::Add($fieldsDiscount);
            $counter->add();
        } else {
            \CSaleDiscount::Update($currentDiscount['ID'], $fieldsDiscount);
            $counter->update();
        }
    }

        private static function getChildrenCondsForDiscount($discount = null)
        {
            $result = [];

            if (Utils::array_exists($discount, 'assortment')) {
                foreach ($discount->assortment as $assortment) {
                    $assortmentXmlId = ProductIdentifier::getIdentifierValue($assortment);
                    if (!empty($assortmentXmlId)) {
                        $result[] = [
                            'CLASS_ID' => 'CondIBXmlID',
                            'DATA' => [
                                'logic' => 'Equal',
                                'value' => $assortmentXmlId
                            ]
                        ];
                    }
                }
            }

            $sectionIds = self::getSectionIds($discount);
            if(count($sectionIds) > 0){
                foreach ($sectionIds as $sectionId) {
                    $result[] = [
                        'CLASS_ID' => 'CondIBSection',
                        'DATA' => [
                            'logic' => 'Equal',
                            'value' => $sectionId
                        ]
                    ];
                }
            }

            return $result;
        }

    private static function findAssortmentWithPrice($discount = null, $msPriceTypeId = '', Debug\Loger &$logger = null)
    {
        $result = [];

        // Assortment: read prices from MS API
        $msPricesByXmlId = [];
        if (Utils::array_exists($discount, 'assortment')) {
            foreach ($discount->assortment as $item) {
                $xmlId = ProductIdentifier::getIdentifierValue($item);
                if (empty($xmlId)) {
                    continue;
                }
                $prices = Prices::loadPricesFromItem($item);
                if (isset($prices[$msPriceTypeId]) && $prices[$msPriceTypeId]['PRICE'] > 0) {
                    $msPricesByXmlId[$xmlId] = (float)$prices[$msPriceTypeId]['PRICE'];
                }
            }
        }

        if (Utils::is_count($msPricesByXmlId)) {
            $assormentExtIds = DiscountProductFinder::buildAssortmentXmlIds($discount->assortment);
            if (count($assormentExtIds) > 0 && Loader::includeModule('iblock')) {
                $filter = ['XML_ID' => $assormentExtIds];
                $iblockIds = Config::getOptionArray('ds_iblock_id');
                if (Utils::is_count($iblockIds)) {
                    $filter['=IBLOCK_ID'] = $iblockIds;
                }
                $rs = \CIblockElement::GetList([], $filter, false, false, ['ID', 'XML_ID']);
                while ($ob = $rs->GetNext()) {
                    $cleanXmlId = $ob['XML_ID'];
                    if (strpos($cleanXmlId, '#') !== false) {
                        $parts = explode('#', $cleanXmlId);
                        $cleanXmlId = end($parts);
                    }
                    if (isset($msPricesByXmlId[$cleanXmlId])) {
                        $result[(int)$ob['ID']] = $msPricesByXmlId[$cleanXmlId];
                    }
                }
            }
        }

        // ProductFolders: fallback to prices from Bitrix
        $sectionIds = self::getSectionIds($discount);
        if (Utils::is_count($sectionIds)) {
            $priceAssoc = array_flip(ConfigurationUtils::getPriceTypeList());
            if (isset($priceAssoc[$msPriceTypeId]) && (int)$priceAssoc[$msPriceTypeId] > 0) {
                $folderProducts = self::getElementListWithPrice(
                    ['=IBLOCK_SECTION_ID' => $sectionIds],
                    (int)$priceAssoc[$msPriceTypeId]
                );
                foreach ($folderProducts as $productId => $price) {
                    if (!isset($result[$productId])) {
                        $result[$productId] = $price;
                    }
                }
            } elseif ($logger !== null && Utils::array_exists($discount, 'productFolders')) {
                $priceName = !empty($discount->specialPrice->priceType->name) ? $discount->specialPrice->priceType->name : $msPriceTypeId;
                foreach ($discount->productFolders as $productFolder) {
                    $logger->addWarningMessage(LangMsg::get('WARNING_DISCOUNT_FOLDER_PRICE_NOT_MAPPED', [
                        '#DISCOUNT_NAME#' => $discount->name,
                        '#FOLDER_NAME#' => !empty($productFolder->name) ? $productFolder->name : '',
                        '#PRICE_NAME#' => $priceName
                    ]));
                }
            }
        }

        return $result;
    }

    private static function getSectionIds($discount = null)
    {
        $result = [];

        $productFoldersExtIds = [];
        if (Utils::array_exists($discount, 'productFolders')) {
            foreach ($discount->productFolders as $productFolder) {
                $xmlId = ProductIdentifier::getSectionIdentifierValue($productFolder);
                if (!empty($xmlId)) {
                    $productFoldersExtIds[] = $xmlId;
                }
            }
        }
        if(count($productFoldersExtIds) > 0){
            $result = self::getSectionList(['=XML_ID' => $productFoldersExtIds]);
        }

        return $result;
    }

        private static function getElementListWithPrice($filterBase = [], $priceTypeId = 0)
        {
            $result = [];
            if (Loader::includeModule('iblock') && !empty($filterBase) && (int)$priceTypeId > 0) {
                if (isset($filterBase['LOGIC'])) {
                    $filter = [$filterBase];
                } else {
                    $filter = $filterBase;
                }
                $iblockIds = Config::getOptionArray('ds_iblock_id');
                if (Utils::is_count($iblockIds)) {
                    $filter['=IBLOCK_ID'] = $iblockIds;
                }
                $rs = \CIblockElement::GetList([], $filter, false, false, ['ID', 'CATALOG_PRICE_' . $priceTypeId]);
                while ($ob = $rs->GetNext()) {
                    if ((float)$ob['CATALOG_PRICE_' . $priceTypeId] <= 0) {
                        continue;
                    }
                    $result[$ob['ID']] = (float)$ob['CATALOG_PRICE_' . $priceTypeId];
                }
            }
            return $result;
        }

        private static function getSectionList($filterBase = [])
        {
            $result = [];
            if (Loader::includeModule('iblock') && !empty($filterBase)) {
                
                if (isset($filterBase['LOGIC'])) {
                    $filter = [$filterBase];
                } else {
                    $filter = $filterBase;
                }

                $iblockIds = Config::getOptionArray('ds_iblock_id');
                if (Utils::is_count($iblockIds)) {
                    $filter['=IBLOCK_ID'] = $iblockIds;
                }
                $rs = \CIblockSection::GetList([], $filter, false, ['ID'], false);
                while ($ob = $rs->GetNext()) {
                    $result[] = (int)$ob['ID'];
                }
            }
            return $result;
        }

    private static function getAllProductDiscounts($discountId = '')
    {
        $result = [];
        if (!empty($discountId)) {
            switch (Config::getDiscountModuleId()) {
                case 'sale':
                    $res = \CSaleDiscount::GetList([], ['%XML_ID' => $discountId . '_%'], false, false, ['ID', 'XML_ID']);
                    while ($discount = $res->getNext()) {
                        if ($discount['XML_ID'] === $discountId) {
                            continue;
                        }
                        $result[$discount['XML_ID']] = $discount['ID'];
                    }
                    break;
            }
        }
        return $result;
    }

    private static function findDiscountById($discountId = '')
    {
        $result = null;
        if (!empty($discountId)) {
            switch (Config::getDiscountModuleId()) {
                case 'sale':
                    $res = \CSaleDiscount::GetList([], ['XML_ID' => $discountId]);
                    if ($discount = $res->getNext()) {
                        $result = $discount;
                    }
                break;
            }
        }
        return $result;
    }

    private static function delelteDiscountById($discountId = '')
    {
        if (!empty($discountId)) {
            switch (Config::getDiscountModuleId()) {
                case 'sale':
                    $discount = self::findDiscountById($discountId);
                    if ($discount !== null && is_array($discount) && isset($discount['ID']) && (int)$discount['ID'] > 0) {
                        $d = new \CSaleDiscount();
                        $d->delete((int)$discount['ID']);
                    }
                    break;
            }
        }
    }

    private static function deleteDiscountsByPrice($discountId = '')
    {
        if (!empty($discountId)) {
            switch (Config::getDiscountModuleId()) {
                case 'sale':
                    $discountObject = new \CSaleDiscount();
                    $res = \CSaleDiscount::GetList([], ['%XML_ID' => $discountId . '_%'], false, false, ['ID', 'XML_ID']);
                    while ($discount = $res->getNext()) {
                        if ($discount['XML_ID'] === $discountId) {
                            continue;
                        }
                        $discountObject->delete($discount['ID']);
                    }
                    break;
            }
        }
    }

    private static function buildDiscountXmlId(string $discountId = ''): string
    {
        $result = $discountId;
        if(Config::getProfileId() > 0) {
            $result .= '_profile_' . Config::getProfileId();
        }
        return $result;
    }

    /** @deprecated */
    public static function importItems($limit = 50, &$offset = 0)
    {
        $writer = new Debug\Writer(LangMsg::get('AGENT_START_ENTITY', [
            '#AGENT_NAME#' => LangMsg::get('AGENT_IMPORT_DISCOUNT'),
            '#ENTITY#' => 'specialpricediscount',
            '#LIMIT#' => $limit,
            '#OFFSET#' => $offset,
        ]));

        $loger = new Debug\Loger();

        $discounts =  ApiNew::get('/entity/specialpricediscount', ['limit' => $limit, 'offset' => $offset, 'expand' => 'productFolders,assortment']);

        if (Utils::is_success($discounts)) {

            if (!empty($discounts->{'meta'}->{'nextHref'})) {
                $offset += $limit;
            } else {
                $offset = 0;
            }

            if (Utils::array_exists($discounts)) {
                self::importRows($discounts->rows, $loger);
            } else {
                $loger->addMessage(LangMsg::get('WARNING_EMPTY_ROWS'), Debug\Message::TYPE_WARNING);
            }
        } else {

            if (Utils::has_errors($discounts)) {
                $loger->addMessageArray($discounts->{'errors'}, Debug\Message::TYPE_ERROR);
            } else {
                $loger->addMessage(LangMsg::get('EXCEPTION_API_ERROR'), Debug\Message::TYPE_ERROR);
            }
        }

        $loger->addMessage(LangMsg::get('AGENT_FINISH', [
            '#API_COUNT#' => ApiNew::getCountRequests(),
            '#AGENT_TIME#' => $loger->getLogTime()
        ]), Debug\Message::TYPE_INFO);

        $writer->setLogerMessages($loger->getMessageArray());
        $writer->exportLog();
    }
}
