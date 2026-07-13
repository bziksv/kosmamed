<?
namespace Rbs\MoyskladStocks\Entity;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\HlCache\PFolder;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

\Bitrix\Main\Loader::includeModule('iblock');

class Productfolder
{
    public $isLoaded = false;

    public $folderMs = null;
    public $folderBx = null;

    public $entity = '';
    public $fullXmlId = '';

    function __construct($folderHref = '', $isCreateItem = false) 
    {
        if(!empty($folderHref)){

            $folder = ApiNew::get($folderHref);
            if(Utils::is_success($folder)){ 

                $this->isLoaded = false;
                $this->folderMs = $folder;
                
                $this->entity = $folder->meta->type;
                $this->fullXmlId = ProductIdentifier::getSectionIdentifierValue($folder);

                $whParamList = ImportParamsConfig::getWhParams($this->entity);
                $importParamList = ImportParamsConfig::getImportParams($this->entity);
                $isOuterSec = $whParamList['outer_sec'];
                $isArchived = $whParamList['archived'];
                $isIgnoreSectionActive = !empty($importParamList['ignore_section_active']);

                $isCheckSection = $this->checkProductFolder();



                if(!$isOuterSec && !$isCheckSection){
                    return;
                }

                if(!$isArchived && $this->folderMs->archived){
                    return;
                }

                $cacheXmlId = !empty($this->getCacheXmlId()) ? $this->getCacheXmlId() : $this->fullXmlId;

                $filter = ['=XML_ID' => $cacheXmlId];
                $catalogIblockId = Config::getIblockId($this->entity);

                if($catalogIblockId > 0){

                    $filter['IBLOCK_ID'] = $catalogIblockId;
                    $rsSection = $this->getBxSection($filter);
                    
                    if((int)$rsSection['ID'] <= 0 && (!$isCreateItem || !Config::checkFeature('import_' . $this->entity))){
                        return;
                    }

                    if((int)$rsSection['ID'] <= 0 && $isCheckSection){
                        \CRbsMoyskladStocks::importOneSection($this->getItemMs());  
                        $rsSection = $this->getBxSection($filter);
                    }    

                    if ((int)$rsSection['ID'] > 0) {

                        $this->folderBx = $rsSection;

                        if(!$isIgnoreSectionActive){
                            if(
                                (
                                    ($isOuterSec && !$isCheckSection) ||
                                    ($isArchived && $this->folderMs->archived)
                                )
                                &&
                                $this->folderBx['ACTIVE'] === 'Y'
                            ){
                                $this->updateBxSection(['ACTIVE' => 'N']);
                            }

                            if(
                                (
                                    ($isOuterSec && $isCheckSection) ||
                                    ($isArchived && !$this->folderMs->archived)
                                )
                                &&
                                $this->folderBx['ACTIVE'] === 'N'
                            ){
                                $this->updateBxSection(['ACTIVE' => 'Y']);
                            }
                        }

                        if($cacheXmlId !== $this->fullXmlId){
                            $this->updateBxSection(['XML_ID' => $this->fullXmlId]);
                        }

                        $this->isLoaded = true;

                        if(PFolder::isExsist()){
                            PFolder::update([$this->getItemMs()]);
                        }
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

                if (!$importParamList['ms_section_root'] && $groupItem->{'id'} === $this->folderMs->{'id'}) {
                    return true;
                }

                if (mb_strpos($this->folderMs->{'pathName'}, $groupItem->{'name'}) !== 0) {
                    return false;
                }
                
            } else {
                return false;
            }
            
        }

        return true;
    }

    function updateBxSection($arUpdate = [])
    {
        $el = new \CIblockSection;

        $uid = Config::getUserId();
        if($uid > 0){
            $arUpdate['MODIFIED_BY'] = $uid;
        }

        $el->update($this->getItemBx()['ID'], $arUpdate);
    }

    function getBxSection($filter = [])
    {
        return \Bitrix\Iblock\SectionTable::getList([
            'order' => ['ID' => 'DESC'],
            'filter' => $filter,
            'select' => ['*'],
            'cache' => ['ttl' => 0]
        ])->fetch();
    }

    public static function delete($object = null)
    {
        if(PFolder::isExsist()){

            $currentItem = PFolder::get($object->meta->href);
            if(isset($currentItem['XML_ID']) && !empty($currentItem['XML_ID'])){

                PFolder::delete($object->meta->href);

                $filter = ['=XML_ID' => $currentItem['XML_ID']];
                $catalogIblockId = Config::getIblockId($object->meta->type);
                
                if($catalogIblockId > 0){

                    $filter['IBLOCK_ID'] = $catalogIblockId;

                    $section = \Bitrix\Iblock\SectionTable::getList(['filter' => $filter])->fetch();

                    if ((int)$section['ID'] > 0) {
                        $el = new \CIblockSection;
                        if (Config::isDeleteEntityByHook($object->meta->type)) {
                            $el->delete((int)$section['ID']);
                        } else {
                            $update = ['ACTIVE' => 'N'];
                            $uid = Config::getUserId();
                            if ((int)$uid > 0) {
                                $update['MODIFIED_BY'] = $uid;
                            }
                            $el->update((int)$section['ID'], $update);
                        }
                    }

                }
            }  
                      
        }
    }

    function getCacheXmlId()
    {
        if(PFolder::isExsist()){
            $currentItem = PFolder::get($this->getHref());
            if(isset($currentItem['XML_ID']) && !empty($currentItem['XML_ID'])){
                return $currentItem['XML_ID'];
            }
        }
        return '';
    }

    function getHref()
    {
        return $this->folderMs->meta->href;
    }

    function checkUpdateHook()
    {
        \CRbsMoyskladStocks::checkUpdateHookSection($this->getItemMs()); 
    }

    function getItemMs()
    {
        return $this->folderMs;
    }

    function getItemBx()
    {
        return $this->folderBx;
    }

    function isLoaded()
    {
        return $this->isLoaded;
    }
}