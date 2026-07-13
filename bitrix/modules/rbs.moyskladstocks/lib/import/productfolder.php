<?php
namespace Rbs\MoyskladStocks\Import;

use \Rbs\MoyskladStocks\HlCache\PFolder;
use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\ApiNew;
use \Rbs\MoyskladStocks\Utils;
use \Rbs\MoyskladStocks\Debug;
use \Rbs\MoyskladStocks\AgentManager;
use \Rbs\MoyskladStocks\LangMsg;
use \Rbs\MoyskladStocks\Services\ImportParamsConfig;

use \Bitrix\Main\Loader;

class Productfolder
{
   private static $entity = 'productfolder';
   private static $allGroups = [];

   private static $paramList = [];
   private static $importParamList = [];
   private static $whParamList = [];
   private static $importArchivedItems = false;

   private static $counterSectionUpdate;

   public static function import()
   {
      Config::setOption('pf_can_import', 'N');
      Config::setOption('pf_last_size', 0);

      $logger = new Debug\Loger();
      $agentManager = new AgentManager(self::$entity);
      $agentManager->setOnlyFullUpdate();
      $agentManager->setConfigValue('limit', 1000);

      try {

         if (!PFolder::isExsist()) {
            PFolder::createTable();
         }

         if (!PFolder::isExsist()) {
            throw new \Bitrix\Main\SystemException(LangMsg::get('THROW_HL_TABLE_DOES_NOT_EXSISTS'));
         }

         $params = [
               'limit' => $agentManager->getLimit(),
               'offset' => $agentManager->getOffset(),
               'filter' => 'archived=true;archived=false',
         ];

         if (!empty($params['filter'])) {
            $logger->addInfoMessage(LangMsg::buildAgentFilterMessage($params['filter']));
         }

         ApiNew::refreshCountRequests();
         $msResult = ApiNew::get('/entity/productfolder', $params);
         if (Utils::is_success($msResult)) {
            
            if (!empty($msResult->meta->size)) {
               $agentManager->setSize($msResult->meta->size);
            }

            if (Utils::array_exists($msResult)) {

               $counterUpdateHlTable = PFolder::update($msResult->{'rows'});

               $logger->addMessage(LangMsg::get('COUNTER_INFO_ADD_UPDATE', $counterUpdateHlTable->getReport()),  Debug\Message::TYPE_INFO);

               if ($counterUpdateHlTable->hasErrors()) {
                  $logger->addErrorMessageArray($counterUpdateHlTable->getErrorMessageArray());
               }


               foreach ($msResult->{'rows'} as $row) {
                  ApiNew::createGetCache($row->{'meta'}->{'href'}, [], Config::getAgentImportTime('productfolder'), $row);
               }

            } else {
               Config::setOption('pf_can_import', 'N');
               $logger->addWarningMessage(LangMsg::get('WARNING_EMPTY_ROWS'));
            }

            if (!empty($msResult->meta->nextHref)) {
               $agentManager->setNextStepOffset();
            } else {
               $agentManager->setFinalStepParams();
               Config::setOption('pf_can_import', 'Y');
               Config::setOption('pf_last_size', (int)$msResult->{'meta'}->{'size'});
            }

            if (Config::checkFeature('pf_can_import')) {
               self::importAllItems($logger);
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

   private static function importAllItems(Debug\Loger &$logger)
   {
      if (!PFolder::isExsist()) {
         throw new \Bitrix\Main\SystemException(LangMsg::get('THROW_HL_TABLE_DOES_NOT_EXSISTS'));
      }

      if (!Loader::includeModule('iblock')) {
         throw new \Bitrix\Main\SystemException(LangMsg::get('MODULE_INSTALL_ERROR', ['#MODULE_ID#' => 'iblock']));
      }

      self::$paramList = \CRbsMoyskladStocks::getParamList(self::$entity);
      if (empty(self::$paramList)) {
         throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_UPDATE_PARAM_LIST'));
      }

      if (\Rbs\MoyskladStocks\Process\Helper::isNeedGroupItem(self::$entity)) {
         if (empty(self::$paramList['GROUP_ITEM']) || !is_object(self::$paramList['GROUP_ITEM'])) {
            throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_GET_FILTER_STRING_GROUP'));
         }
      }

      self::$importParamList = ImportParamsConfig::getImportParams(self::$entity);
      if (empty(self::$importParamList)) {
         throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_UPDATE_PARAM_LIST'));
      }

      $pFolderMsSize = (int)Config::getOption('pf_last_size', 0);
      if ($pFolderMsSize === 0) {
         throw new \Bitrix\Main\SystemException(LangMsg::get('THROW_PFOLDER_SIZE_ZERO'));
      }

      self::$importArchivedItems = Config::checkFeature("im_" . self::$entity . "_p_include_archived");

      self::$whParamList = ImportParamsConfig::getUpParams(self::$entity);

      self::$allGroups = PFolder::getList(['limit' => $pFolderMsSize, 'offset' => 0]);

      self::$counterSectionUpdate = new Debug\Counter(LangMsg::get('IMPORT_TYPE_PRODUCTFOLDER_COUNTER_PFOLDER_SECTION_TREE'));

      self::deleteSections();
     
      if (Utils::property_exists(self::$paramList['GROUP_ITEM'], ['meta', 'href'])) {
         $rootGroupHref = self::$paramList['GROUP_ITEM']->meta->href;
         if (!empty($rootGroupHref)) {
            self::$allGroups = PFolder::getListFromRootGroup($rootGroupHref, !self::$importParamList['ms_section_root']);
         }
      }

      $event = new \Bitrix\Main\Event(Config::getModuleId(true), "onBeforeCreateTreeOfGroups", array(
         'allGroups' => self::$allGroups
      ));

      $event->send();

      if ($event->getResults()) {
         foreach ($event->getResults() as $eventResult) {
            if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
               self::$allGroups = $eventResult->getParameters();
            }
         }
      }

      foreach (self::$allGroups as $pHref => $folder) {
         self::createTree($pHref);
      }

      $logger->addInfoMessage(LangMsg::get('COUNTER_INFO_IBLOCK_SECTION_PROCESS', self::$counterSectionUpdate->getReport()));
      if (self::$counterSectionUpdate->hasErrors()) {
         $logger->addErrorMessageArray(self::$counterSectionUpdate->getErrorMessageArray());
      }
      
   }

   private static function deleteSections()
   {
      $pFolderMsSize = (int)Config::getOption('pf_last_size', 0);
      if ($pFolderMsSize === 0) {
         throw new \Bitrix\Main\SystemException(LangMsg::get('THROW_PFOLDER_SIZE_ZERO'));
      }

      $allGroupList = PFolder::getList();

      if($pFolderMsSize < count($allGroupList) && $pFolderMsSize === count(self::$allGroups) && isset(self::$paramList['IBLOCK_ID']) && self::$paramList['IBLOCK_ID'] > 0){
         foreach ($allGroupList as $pFolderHref => $pFolderParams) {
            if (!isset(self::$allGroups[$pFolderHref])) {

               if(mb_strpos($pFolderHref, ApiNew::getApiEndPointUrl()) !== false) {

                  $section = \Bitrix\Iblock\SectionTable::getList([
                     'filter' => ['=XML_ID' => $pFolderParams['XML_ID'], 'IBLOCK_ID' => self::$paramList['IBLOCK_ID']],
                     'select' => ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'CODE', 'ACTIVE']
                  ])->fetch();

                  if(isset($section['ID']) && (int)$section['ID'] > 0) {

                     $rsElements = \CIBlockElement::GetList(['ID' => 'ASC'], [
                        'SECTION_ID' => $section['ID'],
                        'INCLUDE_SUBSECTIONS' => 'Y'
                     ], false, ['nTopCount' => 1], ['ID']);

                     $canDeleteSection = $rsElements->SelectedRowsCount() <= 0;

                     $isCanDeleteError = false;
                     $sectionClass = new \CIblockSection;
                     switch (Config::getDeleteAction('productfolder')) {
                        case 'DEACTIVATE':
                           $sectionClass->Update($section['ID'], ['ACTIVE' => 'N']);
                           break;
                        case 'DELETE':
                           if($canDeleteSection) {
                              $sectionClass->Delete($section['ID']);
                           } else {
                              $isCanDeleteError = true;
                              $sectionClass->Update($section['ID'], ['ACTIVE' => 'N']);
                           }                           
                           break;
                     }
                     if(!empty($sectionClass->LAST_ERROR)) {
                        self::$counterSectionUpdate->error(LangMsg::get('IMPORT_TYPE_PRODUCTFOLDER_ERROR_SECTION_DELETE', [
                           '#XML_ID#' => $pFolderParams['XML_ID'],
                           '#NAME#' => $pFolderParams['NAME'],
                           '#ERROR#' => $sectionClass->LAST_ERROR
                        ]));
                     } else {

                        if ($isCanDeleteError) {
                           self::$counterSectionUpdate->error(LangMsg::get('IMPORT_TYPE_PRODUCTFOLDER_ERROR_SECTION_DELETE', [
                              '#XML_ID#' => $pFolderParams['XML_ID'],
                              '#NAME#' => $pFolderParams['NAME'],
                              '#ERROR#' => LangMsg::get('IMPORT_TYPE_PRODUCTFOLDER_ERROR_SECTION_DELETE_WITH_ELEMENTS')
                           ]));
                        }
                        
                        self::$counterSectionUpdate->delete();
                        
                     }
                  }
               }

               PFolder::deleteById($pFolderParams['ID']);
            }            
         }
      }

   }

   private static function createTree($pHref = '')
   {
      if(!isset(self::$allGroups[$pHref])){
         return 0;
      }

      $folder = self::$allGroups[$pHref];

      if($folder['SECTION_ID'] > 0){
         return $folder['SECTION_ID'];
      }

      if(empty($folder['XML_ID'])){
         return 0;
      }

      $parentId = 0;
      if(!empty($folder['PARENT_HREF'])){
         $parentId = self::createTree($folder['PARENT_HREF']);
      }
      
      self::$allGroups[$pHref]['SECTION_ID'] = self::checkSection(self::$allGroups[$pHref], $parentId);

      return self::$allGroups[$pHref]['SECTION_ID'];
   }

   private static function checkSection($sectionParams = [], $parentId = 0)
   {
      if((int)$sectionParams['SECTION_ID'] > 0){
         return (int)$sectionParams['SECTION_ID'];
      }

      $sectionIdResult = 0;

      $sectionClass = new \CIBlockSection;

      $translitParams = [];
      if(self::$paramList['IBLOCK_ID'] > 0 && self::$importParamList['translit']){
            $translitParams = Config::getTranslitParamsIblockSectiton(self::$paramList['IBLOCK_ID']);
      }

      $rsSection = \Bitrix\Iblock\SectionTable::getList([
         'filter' => ['=XML_ID' => $sectionParams['XML_ID'], 'IBLOCK_ID' => self::$paramList['IBLOCK_ID']],
         'select' => ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'CODE', 'ACTIVE', 'DESCRIPTION', 'DESCRIPTION_TYPE']
      ])->fetchAll();

      if(count($rsSection) > 0){

         $sectionBx = array_pop($rsSection);

         $sectionIdResult = (int)$sectionBx['ID'];

         $arUpdateFields = [];

         if(self::$whParamList['archived'] && !self::$importParamList['ignore_section_active']){
            if($sectionParams['ARCHIVED'] === true && $sectionBx['ACTIVE'] === 'N'){
               $arUpdateFields['ACTIVE'] = 'Y';
            }
            if($sectionParams['ARCHIVED'] === false && $sectionBx['ACTIVE'] === 'Y'){
               $arUpdateFields['ACTIVE'] = 'N';
            }
         }

         if(self::$whParamList['structure']){
            if($parentId !== (int)$sectionBx['ID'] && $parentId !== (int)$sectionBx['IBLOCK_SECTION_ID']){
                  $arUpdateFields['IBLOCK_SECTION_ID'] = $sectionBx['IBLOCK_SECTION_ID'] = $parentId;
            }
         }

         if (self::$whParamList['name']) {
            if($sectionParams['NAME'] !== $sectionBx['NAME']){
                  $arUpdateFields['NAME'] = $sectionParams['NAME'];                                                    
            }
         }

         if(self::$whParamList['code']){
            $sectionCodeTranslit = \CRbsMoyskladStocks::getTranslitCode($sectionParams['NAME'], $translitParams, self::$importParamList['trim']);
            if(self::$importParamList['code_uniq']){
                  $parentSectionForCheckCode = self::$importParamList['code_uniq_parent'] ? (int)$sectionBx['IBLOCK_SECTION_ID'] : 0;
                  $sectionCodeTranslit = \CRbsMoyskladStocks::getSectionUniqCode(self::$paramList['IBLOCK_ID'], $sectionCodeTranslit, $sectionBx['ID'], $parentSectionForCheckCode);
            }
            if($sectionCodeTranslit !== $sectionBx['CODE']){
                  $arUpdateFields['CODE'] = $sectionCodeTranslit;     
            }
         }

         if (self::$whParamList['descr']) {
            $descrType = self::$importParamList['descr_type'] === 'html' ? 'html' : 'text';
            $description = $sectionParams['DESCRIPTION'] ?? '';
            if (!empty($description)) {
               if ($sectionBx['DESCRIPTION'] !== $description || $sectionBx['DESCRIPTION_TYPE'] !== $descrType) {
                  $arUpdateFields['DESCRIPTION'] = $description;
                  $arUpdateFields['DESCRIPTION_TYPE'] = $descrType;
               }
            } elseif (self::$importParamList['descr_delete'] && !empty($sectionBx['DESCRIPTION'])) {
               $arUpdateFields['DESCRIPTION'] = '';
            }
         }

         if(count($arUpdateFields) > 0){
            $sectionClass->Update($sectionBx['ID'], $arUpdateFields);
            if (!empty($sectionClass->LAST_ERROR)) {
               self::$counterSectionUpdate->error(LangMsg::get('IMPORT_TYPE_PRODUCTFOLDER_ERROR_SECTION_UPDATE', [
                  '#XML_ID#' => $sectionBx['XML_ID'],
                  '#NAME#' => $sectionBx['NAME'],
                  '#ERROR#' => $sectionClass->LAST_ERROR
               ]));
            } else {
               self::$counterSectionUpdate->update();
            }
         }
         
      } else {

         if ($sectionParams['ACTIVE'] === 'N' && !self::$importArchivedItems) {
            return 0;
         }

         $sectionAddFields = [
            'IBLOCK_ID' => self::$paramList['IBLOCK_ID'],
            'XML_ID' => $sectionParams['XML_ID'],
            'NAME' => $sectionParams['NAME'],
            'ACTIVE' => $sectionParams['ARCHIVED'] === true ? 'Y' : 'N'
         ];

         if (self::$importParamList['descr'] && !empty($sectionParams['DESCRIPTION'])) {
            $sectionAddFields['DESCRIPTION'] = $sectionParams['DESCRIPTION'];
            $sectionAddFields['DESCRIPTION_TYPE'] = self::$importParamList['descr_type'] === 'html' ? 'html' : 'text';
         }

         if (self::$importParamList['code'] && self::$importParamList['code_uniq']) {
            $sectionAddFields['CODE'] = $sectionParams['XML_ID'];
         }

         if(self::$importParamList['code'] && !self::$importParamList['code_uniq']){
            $sectionAddFields['CODE'] = \CRbsMoyskladStocks::getTranslitCode($sectionParams['NAME'], $translitParams, self::$importParamList['trim']);
         }

         if(isset(self::$paramList['SECTION_ID']) && $parentId <= 0){
            $sectionAddFields['IBLOCK_SECTION_ID'] = self::$paramList['SECTION_ID'];
         } else if($parentId > 0){
            $sectionAddFields['IBLOCK_SECTION_ID'] = $parentId;
         }

         if($sectionIdResult = $sectionClass->Add($sectionAddFields)){
            if(self::$importParamList['code'] && self::$importParamList['code_uniq']){
                  $translitCode = \CRbsMoyskladStocks::getTranslitCode($sectionParams['NAME'], $translitParams, self::$importParamList['trim']);
                  $parentSectionForCheckCode = self::$importParamList['code_uniq_parent'] ? (int)$sectionAddFields['IBLOCK_SECTION_ID'] : 0;
                  \CRbsMoyskladStocks::checkSectionUniqCode(self::$paramList['IBLOCK_ID'], $translitCode, $sectionIdResult, $parentSectionForCheckCode);
            }
            $sectionIdResult = (int)$sectionIdResult;
         }
         if (!empty($sectionClass->LAST_ERROR)) {
            self::$counterSectionUpdate->error(LangMsg::get('IMPORT_TYPE_PRODUCTFOLDER_ERROR_SECTION_ADD', [
               '#XML_ID#' => $sectionAddFields['XML_ID'],
               '#NAME#' => $sectionAddFields['NAME'],
               '#ERROR#' => $sectionClass->LAST_ERROR
            ]));
         } else if($sectionIdResult > 0) {
            self::$counterSectionUpdate->add();
         }                 
      }

      return $sectionIdResult;
   }

   /** @deprecated */
   public static function importItems($limit = 0, &$offset = 0)
   {
      Config::setOption('pf_can_import', 'N');
      Config::setOption('pf_last_size', 0);

      if (!PFolder::isExsist()) {
         PFolder::createTable();
      }

      if (PFolder::isExsist()) {

         $filter = [
            'filter' => 'archived=true;archived=false',
            'limit' => $limit,
            'offset' => $offset
         ];

         $pfResult = ApiNew::get('/entity/productfolder', $filter);
         if (Utils::is_success($pfResult)) {

            if ($pfResult->{'meta'}->{'nextHref'}) {
               $offset += $limit;
            } else {
               $offset = 0;
               Config::setOption('pf_can_import', 'Y');
               Config::setOption('pf_last_size', (int)$pfResult->{'meta'}->{'size'});
            }

            if (Utils::array_exists($pfResult)) {
               PFolder::update($pfResult->{'rows'});
               foreach ($pfResult->{'rows'} as $row) {
                  ApiNew::createGetCache($row->{'meta'}->{'href'}, [], Config::getAgentImportTime('productfolder'), $row);
               }
            } else {
               Config::setOption('pf_can_import', 'N');
            }
         }
      }
   }
}