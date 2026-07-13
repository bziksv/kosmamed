<?
namespace Rbs\MoyskladStocks\Entity;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\HlCache\Stocks;
use Rbs\MoyskladStocks\HlCache\ExtCodes;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;
use Rbs\MoyskladStocks\Process\Helper;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

class Base 
{
    protected $isLoaded = false;

    protected $productMs = null;
    protected $productBx = null;

    protected $stocks = [];
    protected $prices = [];

    protected $entity = '';
    protected $fullXmlId = '';

    function __construct($productHref = '', $isCreateItem = false) 
    {
        if(!empty($productHref)){

            $parsedHref = Utils::parse_href($productHref);

            $expand = !empty($parsedHref['entity']) ? Helper::buildExpandParams($parsedHref['entity']) : '';

            $product = ApiNew::get($productHref, ['expand' => $expand]);
            if(Utils::is_success($product)){

                $product->meta->href = explode('?', $product->meta->href)[0];
                $this->productMs = $product;

                $this->entity = $product->meta->type;
                $this->fullXmlId = ProductIdentifier::getIdentifierValue($product);

                $whParamList = ImportParamsConfig::getWhParams($this->entity);
                $isOuterSec = $whParamList['outer_sec'];
                $isArchived = $whParamList['archived'] || ImportParamsConfig::getImportFeature($this->entity, 'include_archived');

                $isCheckSection = $this->checkProductFolder();

                if(!$isOuterSec && !$isCheckSection){
                    return;
                }

                if(!$isArchived && $this->productMs->archived){
                    return;
                }

                $cacheXmlId = $this->getCacheXmlId();
                if (empty($cacheXmlId)) {
                    $this->updateXmlId();
                    $cacheXmlId = $this->getXmlId();
                }

                $filter = ProductIdentifier::buildSingleFilter($cacheXmlId, $this->entity);

                $rsProduct = $this->getBxElement($filter);

                if ((int)$rsProduct['ID'] > 0) {
                    $this->productBx = $rsProduct;
                }

                $filterPropId = Config::getFilterPropId($this->entity);
                if($filterPropId !== 'N'){
                    $canImport = false;
                    if(Utils::array_exists($this->productMs, 'attributes')){
                        foreach($this->productMs->attributes as $attr){
                            $needValue = Config::getFilterPropValue($this->entity) === 'true' ? true : false;
                            if($attr->id === $filterPropId && (bool)$attr->value === $needValue){
                                $canImport = true;
                            }
                        }
                    }
                    if(!$canImport){
                        if($whParamList['active_by_filter'] && $this->productBx['ACTIVE'] === 'Y') {
                            $this->updateBxElement(['ACTIVE' => 'N']);
                        }
                        return;
                    }
                }

                /** @deprecated Backward compatibility: update Bundles HL cache for legacy installations that still have the table */
                if($this->isBundle() && class_exists('\\Rbs\\MoyskladStocks\\HlCache\\Bundles')){
                    if(\Rbs\MoyskladStocks\HlCache\Bundles::isExsist()){
                        \Rbs\MoyskladStocks\HlCache\Bundles::update([$this->productMs]);
                    }
                }

                if((int)$rsProduct['ID'] <= 0 && (!$isCreateItem || !Config::checkFeature('import_' . $this->entity))){
                    return;
                }
               
                $catalogIblockId = (int)Config::getIblockId($this->getEntity());
                if((int)$catalogIblockId > 0 && (int)$rsProduct['ID'] <= 0 && $isCheckSection && !Config::isOnlyUpdate($this->entity)){ 
                    \CRbsMoyskladStocks::importOne($this->getItemMs()); 
                    $rsProduct = $this->getBxElement($filter);
                }

                if ((int)$rsProduct['ID'] > 0) {

                    $this->productBx = $rsProduct;

                    if(
                        (
                            ($isOuterSec && !$isCheckSection) || 
                            ($isArchived && $this->productMs->archived)
                        ) 
                        && 
                        $this->productBx['ACTIVE'] === 'Y'
                    ){
                        $this->updateBxElement(['ACTIVE' => 'N']);
                        return;
                    }

                    if(
                        (
                            ($isOuterSec && $isCheckSection) || 
                            ($isArchived && !$this->productMs->archived) ||
                            $whParamList['active_by_filter']
                        ) 
                        && 
                        $this->productBx['ACTIVE'] === 'N'
                    ){
                        $this->updateBxElement(['ACTIVE' => 'Y']);
                    }

                    $this->isLoaded = true;

                    if($cacheXmlId !== $this->fullXmlId){
                        $this->updateXmlId();
                    }
                }
            }
        }
    }

    function checkProductFolder()
    {
        if(\Rbs\MoyskladStocks\Process\Helper::isNeedGroupItem($this->entity)) {

            $groupItem = \CRbsMoyskladStocks::getGroupItem($this->entity);
            $importParamList = ImportParamsConfig::getImportParams($this->entity);

            if (Utils::property_exists($groupItem, ['meta', 'href'])) {

                if (!$importParamList['ms_section_root'] && $groupItem->meta->href === $this->productMs->productFolder->meta->href) {
                    return true;
                }
                
                if (mb_strpos($this->productMs->{'pathName'}, $groupItem->{'name'}) !== 0) {
                    return false;
                }

            } else {
                return false;
            }
            
        }

        return true;
    }

    function updateBxElement($arUpdate = [])
    {
        $el = new \CIblockElement;

        $uid = Config::getUserId();
        if($uid > 0){
            $arUpdate['MODIFIED_BY'] = $uid;
        }

        $el->update($this->getBxProductId(), $arUpdate);
    }

    public static function delete($object = null)
    {
        $cacheXmlId = '';

        if (ProductIdentifier::isExtCodesRequired()) {
            if(ExtCodes::isExsist()){
                $cacheXmlId = ExtCodes::get($object->meta->href);
                if(!empty($cacheXmlId)){
                    ExtCodes::delete($object->meta->href);
                }
            }
        } else {
            $cacheXmlId = ProductIdentifier::extractIdFromHref($object->meta->href);
        }

        if(!empty($cacheXmlId)){
            $filter = ProductIdentifier::buildSingleFilter($cacheXmlId, $object->meta->type);

            $catalogIblockId = Config::getIblockId($object->meta->type);
            if($catalogIblockId > 0){
                $filter['IBLOCK_ID'] = $catalogIblockId;
            } else {
                return [];
            }

            $element = \Bitrix\Iblock\ElementTable::getList(['filter' => $filter])->fetch();

            if ((int)$element['ID'] > 0) {
                $el = new \CIblockElement;
                if(Config::isDeleteEntityByHook($object->meta->type)){
                    $el->delete((int)$element['ID']);
                } else {
                    $update = ['ACTIVE' => 'N'];
                    $uid = Config::getUserId();
                    if((int)$uid > 0){
                        $update['MODIFIED_BY'] = $uid;
                    }
                    $el->update((int)$element['ID'], $update);
                }
            }
        }
    }

    function getBxElement($filter)
    {
        $catalogIblockId = (int)Config::getIblockId($this->getEntity());
        if($catalogIblockId > 0){
            $filter['IBLOCK_ID'] = $catalogIblockId;
        }

        $element = \Bitrix\Iblock\ElementTable::getList(['filter' => $filter])->fetch(); 

        if(!empty($element['ID']) && (int)$element['ID'] > 0){

            $product = \Bitrix\Catalog\ProductTable::getList([
                'order' => ['ID' => 'DESC'],
                'filter' => ['ID' => $element['ID']],
                'select' => [
                    '*',
                    'XML_ID' => 'IBLOCK_ELEMENT.XML_ID',
                    'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID',
                    'NAME' => 'IBLOCK_ELEMENT.NAME'
                ],
                'cache' => [
                    'ttl' => 0
                ]
            ])->fetch();

            $product['ACTIVE'] = $element['ACTIVE'];

            return $product;

        } else {
            return ['ID' => 0];
        }
    }
    
    function updateXmlId()
    {
        if(ProductIdentifier::isExtCodesRequired() && ExtCodes::isExsist()){
            ExtCodes::update([$this->getItemMs()]);
        }

        if($this->isLoaded()){
            $el = new \CIblockElement;
            $el->update($this->getBxProductId(), ['XML_ID' => $this->getXmlId()]);
        }
    }

    function getCacheXmlId()
    {
        if (ProductIdentifier::isExtCodesRequired()) {
            if(ExtCodes::isExsist()){
                return ExtCodes::get($this->getHref());
            }
            return '';
        }
        return ProductIdentifier::extractIdFromHref($this->getHref());
    }

    function checkPrices()
    {        
        if(!$this->isLoaded()) return false;
        \Rbs\MoyskladStocks\Import\Type\Prices::import_from_ms_object([$this->getItemMs()]);
    }

    function loadPrices()
    {
        if(!$this->isLoaded()) return false;

        $this->prices = \Rbs\MoyskladStocks\Import\Type\Prices::loadPricesFromItem($this->productMs);

        return count($this->prices) > 0; 
    }

    function loadStocks(): bool
    {
        if(!$this->isLoaded()) return false;

        $reprotStocks = ApiNew::get('/report/stock/bystore', ['filter' => "stockMode=all;{$this->getEntity()}={$this->getHref()}"]);
        if(Utils::is_success($reprotStocks) && Utils::array_exists($reprotStocks)){
            Stocks::update($reprotStocks->rows);
            return true;
        }

        return false;
    }

    function checkStocks()
    {
        if(!$this->isLoaded()) return false;
        \Rbs\MoyskladStocks\Import\Type\Stocks::update($this->getEntity(), $this->getHref(), $this->getXmlId());
    }

    function isLoaded()
    {
        return $this->isLoaded;
    }

    function getXmlId()
    {
        return $this->fullXmlId;
    }

    function getHref()
    {
        return $this->productMs->meta->href;
    }

    function getEntity()
    {
        return $this->productMs->meta->type;
    }

    function getProductId()
    {
        return $this->productMs->id;
    }

    function getBxProductId()
    {
        return $this->productBx['ID'];
    }

    function isVariant()
    {
        return $this->entity === 'variant';
    }

    function isBundle()
    {
        return $this->entity === 'bundle';
    }

    function isProduct()
    {
        return $this->entity === 'product';
    }

    function isService()
    {
        return $this->entity === 'service';
    }

    function getItemMs()
    {
        return $this->productMs;
    }

    function getItemBx()
    {
        return $this->productBx;
    }
}