<?php
namespace Rbs\MoyskladStocks;

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Services\PropertiesImportUtils;
use Rbs\MoyskladStocks\Services\MoyskladImportUtils;
use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;
use Rbs\MoyskladStocks\Compitable\ConfigClass;

class Config
{
    public static function getModuleId($isOrig = false): string
    {   
        if(!$isOrig && self::getProfileId() > 0){
            return 'rbs.moyskladstocks_' . self::getProfileId();
        }
        return 'rbs.moyskladstocks';
    }

    public static function isDemo(): bool
    {
        return \Bitrix\Main\Loader::includeSharewareModule(self::getModuleId(true)) === \Bitrix\Main\Loader::MODULE_DEMO;
    }

    public static function getCachePath($path = ''): string
    {
        return '/' . self::getModuleId() . '/' . $path . '/';
    }

        public static function getProfileId()
        {
            return isset($GLOBALS['rbsMsStocksProfile']) ? (int)$GLOBALS['rbsMsStocksProfile'] : 0;
        }

        public static function setProfileId($profileId = 0)
        {
            $GLOBALS['rbsMsStocksProfile'] = (int)$profileId;
        }

        public static function isProfilesOn()
        {
            return \Bitrix\Main\Config\Option::get('stocksprofiles', 'profiles_on', 'N', '') === 'Y';
        }

            public static function setProfilesOn()
            {
                \Bitrix\Main\Config\Option::set('stocksprofiles', 'profiles_on', 'Y', '');
            }


    public static function getLogin()
    {
        return self::getOption('login');
    }

    public static function getPass()
    {
        return self::getOption('pass');
    }

    public static function getToken()
    {
        return self::getOption('token');
    }

    public static function checkSalt()
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
       
        $profileId = (int)$request->get('profile_id');
        if($profileId > 0){
            self::setProfileId($profileId);
        }

        if(!self::checkFeature('modulesync')) return false;
        
        return self::getOption('webhook_url_salt') === $request->get('checkUrl');
    }

    /**CRON */

    public static function setLastCronInitDate()
    {
        \Bitrix\Main\Config\Option::set(self::getModuleId(true) . '_global', 'last_cron_init', (new \DateTime())->format('U'), '');
    }

    public static function getLastCronInitDate()
    {
        return (float)\Bitrix\Main\Config\Option::get(self::getModuleId(true) . '_global', 'last_cron_init', 0, '');
    }

    public static function getLastCronInitSecondInterval()
    {
        $currentCronInitTimeStamp = self::getLastCronInitDate();
        return (float)(new \DateTime())->format('U') - $currentCronInitTimeStamp;
    }

    public static function isLastCronInitActually(): bool
    {        
        return self::getLastCronInitSecondInterval() < 600;
    }

    public static function getCronLockFileName()
    {
        $profileId = self::getProfileId();
        $lockFile = __DIR__ . '/../cron/lock_' . $profileId . '.txt';

        return $lockFile;
    }

    public static function isLockCron()
    {
        $lockFile = self::getCronLockFileName();
        if (!file_exists($lockFile)) {
            file_put_contents($lockFile, time());
            return false;
        } else {
            $timestamp = file_get_contents($lockFile);
            if (time() - (int)$timestamp > 600) {
                self::unLockCron();
            }
        }
        return true;
    }

    public static function unLockCron()
    {
        $lockFile = self::getCronLockFileName();
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }
    }

    /**CRON */

    public static function getApiCacheId()
    {
        $currentCache = self::getOption('cache_api_id');
        if(empty($currentCache)){
            $currentCache = self::refreshApiCacheId();
        }

        return $currentCache;
    }

    public static function refreshApiCacheId()
    {
        $currentCache = time();
        self::setOption('cache_api_id', $currentCache);

        return $currentCache;
    }

    public static function getCacheHookTime()
    {
        $cacheTime =  (int)self::getOption('cache_webhook_time');
        return $cacheTime > 0 ? $cacheTime : 5;
    }

    /**RETRY API */

    public static function getAttemptsApiErrorCount()
    {
        $attempts = (int)self::getOption('attempt_api_error_count', 3);
        if ($attempts > 10) {
            $attempts = 10;
        }
        return $attempts > 0 ? $attempts : 3;
    }

    public static function getAttemptsApiDelay()
    {
        $delay = (int)self::getOption('attempt_api_error_delay', 500);
        if ($delay > 2000) {
            $delay = 2000;
        }
        return $delay > 0 ? $delay : 500;
    }

    public static function isRetryErrorCode($code = '')
    {
        $code = (int)$code;
        return in_array($code, self::getRetryErrorCodes());
    }

    public static function getRetryErrorCodes()
    {
        return [429, 500, 502, 503, 504, 1073, 1049, 1074, 1999];
    }

    public static function getErrorsCodeForTimeNotify()
    {
        $code = array_merge([0], self::getRetryErrorCodes());
        return is_array($code) ? $code : [];
    }

    /**RETRY API */

    /**LIMIT WEBHOOKS */
    public static function getWebHookLimitCount()
    {
        $currentLimitCount = self::getOption('webhook_limit_count', 5);
        return (int)$currentLimitCount <= 25 ? (int)$currentLimitCount : 5;
    }

    public static function getWebHookLimitCountInterval()
    {
        $currentLimitCount = self::getOption('webhook_limit_count_interval', 200);
        return (int)$currentLimitCount < 2000 ? (int)$currentLimitCount : 200;
    }
    /**LIMIT WEBHOOKS */

    public static function checkFeature($featureName = ''): bool
    { 
        $compitableFeatures = ConfigClass::getCompitableFeatureList();
        $featureName = isset($compitableFeatures[$featureName]) ? $compitableFeatures[$featureName] : $featureName;
        return self::getOption($featureName) === 'Y';
    }

    /**FEATURES */

        public static function isClearXmlId(): bool
        {
            return self::getOption('variant_clear_xmlid') === 'Y';
        }

        public static function isIdentifyById(): bool
        {
            return self::getOption('identify_by_id') === 'Y';
        }

        public static function isIdentifySectionsById(): bool
        {
            return self::getOption('identify_sections_by_id') === 'Y';
        }

        public static function isDeleteEntityByHook($entity = 'variant'): bool
        {
            return self::getOption("im_{$entity}_wh_delete") === 'Y';
        }

        public static function isIblockRequired($entity = ''): bool
        {
            return self::getOption("{$entity}_iblock_req") === 'Y';
        }

        public static function isOnlyUpdate($entity = 'product'): bool
        {
            return self::getOption("{$entity}_uponly") === 'Y';
        }


    /**FEATURES */

    public static function checkFeatureEntity($feature = '', $entity = 'product'): bool
    {
        return self::getOption("{$entity}_{$feature}") === 'Y';
    }

    public static function getEntityParam($entity = 'product', $param = '')
    {
        return self::getOption("{$entity}_{$param}");
    }

    public static function getIblockListForClearCacheTag(): array
    {
        return self::getOptionArray('tag_cache_iblocks_stores');
    }
    
    public static function setParentStores($array = [], $type = 'default')
    {
        $optionName = $type === 'default' ? 'parent_stores_arr' : 'curr_parent_stores_arr';
        self::setOption($optionName, serialize($array));
    }

    public static function getParentStores($type = 'default')
    {
        $optionName = $type === 'default' ? 'parent_stores_arr' : 'curr_parent_stores_arr';
        return unserialize(htmlspecialchars_decode(self::getOption($optionName)));
    }

    public static function getImportBundleType()
    {
        return self::getOption('im_bundle_type_imp');
    }

    public static function getBundlePartIblockId(): array
    {
        $result = [];
        $iblockArray = self::getOptionArray('im_bundle_type_imp_iblock');
        if(Utils::is_count($iblockArray)){
            foreach($iblockArray as $iblockId){
                if(intVal($iblockId) > 0){
                    $result[] = intVal($iblockId);
                }
            }
        }
        return $result;
    }

    public static function getDeleteAction($entity = 'productfolder')
    {
        $defAction = 'DEACTIVATE';
        if(mb_strlen(self::getOption("{$entity}_delete_action")) > 0){
            $defAction = self::getOption("{$entity}_delete_action");
        }
        return $defAction;
    }

    /** @deprecated */
    public static function getMeasureList(): array
    {
        return ConfigurationUtils::getMeasureList();
    }

    /** @deprecated */
    public static function getPropTypesBx($iblockId = 0): array
    {
        return PropertiesImportUtils::getPropertiesTypesBx((int)$iblockId);
    }

    public static function isEnableFilterProp($entity = 'product')
    {
        return self::getFilterPropId($entity) !== 'N' && !empty(self::getFilterPropId($entity));
    }

    public static function isEnableActiveProp($entity = 'product')
    {
        return self::getOption("im_{$entity}_p_active_prop", 'N') !== 'N';
    }

    public static function getActivePropId($entity = 'product')
    {
        return self::getOption("im_{$entity}_p_active_prop", 'N');
    }

    public static function getFilterPropId($entity = 'product')
    {
        return empty(self::getOption("im_{$entity}_filter_prop")) ? 'N' : self::getOption("im_{$entity}_filter_prop"); 
    }

    public static function getFilterPropString($propId = '', $value = '', $entity = 'product')
    {
        $entity = $entity === 'variant' ? 'product' : $entity;
        return ApiNew::getApiEndPointUrl() . "/entity/{$entity}/metadata/attributes/{$propId}={$value}";
    }

    public static function getFilterPropValue($entity = 'product')
    {
        return self::getOption("im_{$entity}_filter_prop_value") === 'N' ? 'false' : 'true';
    }

    public static function getFilterPropValueReverse($entity = 'product')
    {
        return self::getOption("im_{$entity}_filter_prop_value") === 'N' ? 'true' : 'false';
    }

    public static function getFilterStocksString()
    {
        $filterStr = 'stockMode=all';
        if(Config::checkFeature('stock_filter_enable')){
            $groupId = Config::getOption('stock_filter_group');
            if(!empty($groupId) && $groupId !== 'N'){
                //$group = ApiNew::get('/entity/productfolder/' . $groupId, [], 86400 * 365);
                //if(Utils::is_success($group)){
                    $filterStr .= ';productFolder=' . ApiNew::getApiEndPointUrl() . '/entity/productfolder/' . $groupId;
                //}
            }
            $propId = Config::getOption('stock_filter_flag');
            if(!empty($propId) && $propId !== 'N'){
                $filterStr .= ';' . Config::getFilterPropString($propId, self::getFilterPropValue('stocks'));
            }
        }
        return $filterStr;
    }

    /** @deprecated */
    public static function getCurrencyList(): array
    {
        return ConfigurationUtils::getCurrencyList();
    }

    public static function getLogFileSize()
    {
        return (int)self::getOption('logger_filesize');
    }

    public static function getUserId()
    {
        $uid = self::getOption('user_id');
        return (int)$uid > 0 ? (int)$uid : 0;
    }

    public static function getVariantProps($propMsId = '')
    {
       return self::getOption('im_variant_pp_' . $propMsId);
    }

    public static function setVariantProps($propMsId = '', $propBxId = 0)
    {
        if(!empty($propMsId) && $propBxId > 0){
            return self::setOption('im_variant_pp_' . $propMsId, $propBxId); 
        }
    }

    /** @deprecated */
    public static function getSymCodeParams($iblockId = 0, $type = 'CODE'): array
    {
        return ConfigurationUtils::getIblockSymbolicCodeParams((int)$iblockId, (string)$type);
    }

    /** @deprecated */
    public static function getTranslitParamsIblock($iblockId = 0)
    {
        return ConfigurationUtils::getIblockElementTranslitParams((int)$iblockId);
    }

    /** @deprecated */
    public static function getTranslitParamsIblockSectiton($iblockId = 0)
    {
        return ConfigurationUtils::getIblockSectionTranslitParams((int)$iblockId);
    }

    /** @deprecated */
    public static function getPrices()
    {
        return ConfigurationUtils::getPriceTypeList();
    }

    /** @deprecated */
    public static function getVatList()
    {
        return ConfigurationUtils::getVatList();
    }

    /*STOCKS_PARAMS*/
        public static function getCurrentStocksParams(): array
        {
            $result = [
                'stock_type' => 'quantity',
                'double_type' => 'ASC',
                'qty_type' => 'ALL',
                'entity_type' => [],
                'limit' => 5000,
                'full_limit' => 10000
            ];

            foreach($result as $paramKey => $paramValue){
                $option = is_array($paramValue) ? self::getOptionArray('curr_stocks_p_' . $paramKey, []) : self::getOption('curr_stocks_p_' . $paramKey, '');
                if(!empty($option)) {
                    $result[$paramKey] = $option;
                }
            }

            return $result;
        }

        /** @deprecated */
        public static function getStores($type = 'default'): array
        {
            return ConfigurationUtils::getStoreList($type);
        }

        public static function getTypeStockSync($entity = 'product')
        {
            return self::getOption("{$entity}_stocks_type", 'A');
        }

            public static function getTypeStockSyncFormated($entity = 'product')
            {
                switch(self::getOption("{$entity}_stocks_type", 'A')){
                    case 'A':
                        return 'q';
                    case 'S':
                        return 's';
                    case 'T':
                        return 't';
                }
                return 'q';
            }

        public static function getQtyStockSync($entity = 'product')
        {
            return self::getOption("{$entity}_stocks_qty", 'ALL');
        }

        public static function getDoubleStockType($entity = 'product')
        {
            return self::getOption("{$entity}_stocks_double_type", 'ASC');
        }

        public static function getDoublePricesType($entity = 'product')
        {
            return self::getOption("{$entity}_prices_double_type", 'ASC');
        }

        public static function getStockLimit(): int
        {
            $currentLimit = (int)self::getOption('stock_limit');
            return $currentLimit > 0 && $currentLimit <= 1000 ? $currentLimit : 100;
        }

        public static function getCurrStockAgentTime(): int
        {
            $currentLimit = (int)self::getOption('curr_stocks_time');
            return $currentLimit > 0 ? $currentLimit : 120;
        }
    /*STOCKS_PARAMS*/

    public static function getIblockId($entity = 'product'): int
    {
        return (int)self::getOption("im_{$entity}_iblock", 0);
    }

    public static function getSectionId($entity = 'product'): int
    {
        return (int)self::getOption("im_{$entity}_section");
    }

    public static function getGroupId($entity = 'product')
    {
        return self::getOption("im_{$entity}_group");
    }

    public static function getUpParamValue($entity = 'product', $param = '')
    {
        return self::getOption("im_{$entity}_up_{$param}");
    }

    /** @deprecated */
    public static function getImportFeature($entity = 'product', $param = '')
    {
        return ImportParamsConfig::getImportFeature($entity, $param);
    }

    /** @deprecated */
    public static function getImportParams($entity = 'product')
    {
        return ImportParamsConfig::getImportParams($entity);
    }

    /** @deprecated */
    public static function getImportPropList($entity = 'product')
    {
        return ImportParamsConfig::getImportPropList($entity);
    }

    public static function getPropSort($entity = 'product')
    {
        $result = self::getOption("im_{$entity}_p_sort_prop");
        if(empty($result)){
            $result = 'N';
        }
        return $result;
    }

    public static function getPropRatio($entity = 'product')
    {
        $result = self::getOption("im_{$entity}_p_ratio_prop");
        if (empty($result)) {
            $result = 'N';
        }
        return $result;
    }

    public static function getWeightM($entity = 'product')
    {
        $w = (float)self::getOption("im_{$entity}_p_weight_m");
        if($w <= 0){
            $w = 1000;
        }

        return $w;
    }

    /** @deprecated */
    public static function getWhFeature($entity = 'product', $param = '')
    {
        return ImportParamsConfig::getWhFeature($entity, $param);
    }

    /** @deprecated */
    public static function getWhParams($entity = 'product')
    {
        return ImportParamsConfig::getWhParams($entity);
    }

    /** @deprecated */
    public static function getUpFeature($entity = 'product', $param = '')
    {
        return ImportParamsConfig::getUpFeature($entity, $param);
    }

    /** @deprecated */
    public static function getUpParams($entity = 'product')
    {
        return ImportParamsConfig::getUpParams($entity);
    }

    public static function getUploadDir($subDir = '')
    {
        $profileDir = '';
        if(self::getProfileId() > 0){
            $profileDir = 'profile_' . self::getProfileId() . '/';
        }

        $upload_dir = '/' . \COption::GetOptionString("main", "upload_dir", "upload") . '/';

        if(empty($subDir)){
            $path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir . self::getModuleId(true) . '/' . $profileDir;
            if(!is_dir($path)){
                mkdir($path, 0755);
            }   
        } else {
            $path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir . self::getModuleId(true) . '/' . $profileDir . $subDir . '/';
            if(!is_dir($path)){
                mkdir($path, 0755, true);
            }   
        }
             
        return $path;
    }

    public static function getDiscountModuleId()
    {
        $moduleId = self::getOption('ds_module');
        if($moduleId !== 'catalog' || $moduleId !== 'sale'){
            return 'sale';
        }
        return $moduleId;
    }

    public static function getOptionGroupsArray($filter = [], $additionalOptios = [])
    {
        $msGroups = \Rbs\MoyskladStocks\ApiNew::get('/entity/productfolder', $filter);
        $selectGroup = $additionalOptios;
        if(Utils::is_success($msGroups) && Utils::array_exists($msGroups)){
            foreach($msGroups->rows as $row){
                $selectGroup[$row->id] = $row->name;
            }
        }
        return $selectGroup;
    }

    /** @deprecated */
    public static function getOptionPropsArray(array $filter = [], int $cache = 0): array
    {
        return MoyskladImportUtils::getOptionAttributesArray($filter, (int)$cache);
    }

    public static function getDateTime($dateString = ''): \DateTime
    {
        try {
            $timeZone = date_default_timezone_get();
            if (self::checkFeature('is_eu_msk_timezone')) {
                $timeZone = 'Europe/Moscow';
            }
            return new \DateTime($dateString, new \DateTimeZone($timeZone));
        } catch (\Throwable $e) {
            return new \DateTime();
        }
    }

    public static function getOption($optionName = '', $defaultValue = false, $siteId = '', $ignoreProfile = false)
    {
        $moduleId = $ignoreProfile ? self::getModuleId(true) : self::getModuleId();
        return \Bitrix\Main\Config\Option::get($moduleId, $optionName, $defaultValue, $siteId);
    }

    public static function getOptionArray($optionName = '', $defaultValue = false, $siteId = '')
    {
        $fieldValue = self::getOption($optionName, $defaultValue, $siteId);
        if (is_array($fieldValue) && count($fieldValue) > 0) {
            return $fieldValue;
        } else if (!empty($fieldValue) && !is_array($fieldValue)) {
            $tmpExplode = explode(',', $fieldValue);
            if (count($tmpExplode) > 0) {
                return $tmpExplode;
            } elseif (!empty($fieldValue)) {
                return [$fieldValue];
            }
        }
        return [];
    }

    public static function setOption($option = '', $val = '', $siteId = '', $ignoreProfile = false)
    {
        $moduleId = $ignoreProfile ? self::getModuleId(true) : self::getModuleId();
        return \Bitrix\Main\Config\Option::set($moduleId, $option, $val, $siteId);
    }

    /** @deprecated */
    public static function isEntityImportProcess($entity = 'product'): bool { return ConfigClass::isEntityImportProcess($entity); }

    /** @deprecated */
    public static function getExportLogFileName() { return ConfigClass::getExportLogFileName(); }

    /** @deprecated */
    public static function setFullUpdateAgent(string $agentFunctionName = '') { ConfigClass::setFullUpdateAgent($agentFunctionName); }

    /** @deprecated */
    public static function isFullUpdateAgent(string $agentFunctionName = ''): bool { return ConfigClass::isFullUpdateAgent($agentFunctionName); }

    /** @deprecated */
    public static function stopFullUpdateAgent(string $agentFunctionName = '') { ConfigClass::stopFullUpdateAgent($agentFunctionName); }

    /** @deprecated */
    public static function getDateForEvent(string $event = ''): \DateTime { return ConfigClass::getDateForEvent($event); }

    /** @deprecated */
    public static function setDateForEvent(string $event = '', \DateTime $date) { ConfigClass::setDateForEvent($event, $date); }

    /** @deprecated */
    public static function getLastDateUpdate($key = '') { return ConfigClass::getLastDateUpdate($key); }

    /** @deprecated */
    public static function setLastDateUpdate($key = '', $date = '') { ConfigClass::setLastDateUpdate($key, $date); }

    /** @deprecated */
    public static function getStockAgentTime(): int { return ConfigClass::getStockAgentTime(); }

    /** @deprecated */
    public static function getPriceAgentTime($entity = 'product'): int { return ConfigClass::getPriceAgentTime($entity); }

    /** @deprecated */
    public static function getPriceAgentLimit($entity = 'product'): int { return ConfigClass::getPriceAgentLimit($entity); }

    /** @deprecated */
    public static function getImportAgentLimit($entity = 'product'): int { return ConfigClass::getImportAgentLimit($entity); }

    /** @deprecated */
    public static function getDiscountAgentLimit(): int { return ConfigClass::getDiscountAgentLimit(); }

    /** @deprecated */
    public static function getAgentImportTime($entity = 'product'): int { return ConfigClass::getAgentImportTime($entity); }

    /** @deprecated */
    public static function getApiEndPointNew(): string { return ConfigClass::getApiEndPointNew(); }

    /** @deprecated */
    public static function getBaseUploadDirFiles() { return ConfigClass::getBaseUploadDirFiles(); }

    /** @deprecated */
    public static function getApiEndPoint() { return ConfigClass::getApiEndPoint(); }

    /** @deprecated */
    public static function getAgentInterval() { return ConfigClass::getAgentInterval(); }

    /** @deprecated */
    public static function getAgentLiveTime() { return ConfigClass::getAgentLiveTime(); }

    /** @deprecated */
    public static function getAgentAttempts() { return ConfigClass::getAgentAttempts(); }

    /** @deprecated */
    public static function cacheTime($option = '') { return ConfigClass::cacheTime($option); }

    /** @deprecated */
    public static function getStocksTypeApi() { return ConfigClass::getStocksTypeApi(); }
   
}
