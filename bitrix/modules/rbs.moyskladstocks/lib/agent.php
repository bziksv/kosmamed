<?php
namespace Rbs\MoyskladStocks;

use CRbsMoyskladStocks;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;

class Agent
{
    public static $agentFunctionList = [
        
        'product' => 'import_product',
        'variant' => 'import_variant',
        'service' => 'import_service',
        'bundle' => 'import_bundle',
        'productfolder' => 'import_productfolder',
        
        'product_price' => 'import_prices_product',
        'variant_price' => 'import_prices_variant',
        'service_price' => 'import_prices_service',
        'bundle_price' => 'import_prices_bundle',

        'stocks' => 'import_stocks',
        'curr_stocks' => 'import_current_stocks',
        'bundle_stocks' => 'import_bundle_stocks',

        'discount' => 'import_discount',
        'update_ext_codes' => 'update_ext_codes',
    ];

    public static function convertAgentNames()
    {
        $res = \CAgent::GetList(["ID" => "DESC"], ['NAME' => '\Rbs\MoyskladStocks\Agent%']);
        while ($obAgent = $res->GetNext()) {
            $newName = explode('(', $obAgent['NAME'])[0] . '(0);';
            \CAgent::Update($obAgent['ID'], ['NAME' => $newName]);
        }
    }

    public static function getAgentProfileName($agentName = '')
    {
        $agentName = explode('(', $agentName)[0];
        $profileId = \Rbs\MoyskladStocks\Config::getProfileId();

        return $agentName . "({$profileId});";
    }

    public static function set($agentName = '', $interval = 120, $offsetTime = 0, $agentStrSearch = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);

        if ($offsetTime === 0) {
            $offsetTime = $interval;
        }

        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        
        $culture = \Bitrix\Main\Context::getCurrent()->getCulture();
        $phpDateTime = new \DateTime();
        $dateTime = \Bitrix\Main\Type\DateTime::createFromPhp($phpDateTime->modify('+'.((int)$offsetTime).' second'));

        if ($obAgent = $res->GetNext()) {
            \CAgent::Update($obAgent['ID'], [
                'NAME' => $agentNameFull,
                'AGENT_INTERVAL' => $interval
            ]);
        } else {
            \CAgent::AddAgent($agentNameFull, Config::getModuleId(true), "N", $interval, $dateTime->toString($culture), "Y", $dateTime->toString($culture), 30);
        }
    }

    public static function get($agentName = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        return $res->GetNext();
    }

    public static function getInfo($agentName = ''): array
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        $result = ['ID' => 0];
        if ($obAgent = $res->GetNext()) {
            foreach ($obAgent as $field => $val) {
                if ($field === 'LAST_EXEC' && empty($val)) {
                    $val = LangMsg::get('AGENT_NON_EXEC');
                }
                if ($field === 'ACTIVE') {
                    $val = $val === 'Y' ? LangMsg::get('UNIVERSAL_LANG_ACTIVE_Y') : LangMsg::get('UNIVERSAL_LANG_ACTIVE_N');
                }
                $result[$field] = $val;
            }
        }
        return $result;
    }

    public static function isEnabledAgent($agentName = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        if ($res->GetNext()) {
            return true;
        }
        return false;
    }

    public static function delete($agentName = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        if ($obAgent = $res->GetNext()) {
            \CAgent::Delete($obAgent['ID']);
        }
    }

    public static function deleteArray($agentName = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        while ($obAgent = $res->GetNext()) {
            \CAgent::Delete($obAgent['ID']);
        }
    }

    public static function set_module_auto_on($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        \Rbs\MoyskladStocks\Internals\ModuleWorkSwitcher::checkModuleSwitchOn();

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function check_module_agents($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync')) {
            $res = \CAgent::GetList(["ID" => "DESC"], ["MODULE_ID" => Config::getModuleId(true), 'ACTIVE' => 'N']);
            while ($obAgent = $res->GetNext()) {
                \CAgent::Update($obAgent['ID'], [
                    'ACTIVE' => 'Y'
                ]);
            }
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function clear_logs($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync')) {
            Debug\FileController::getInstance()->clearFileProcess();
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function update_ext_codes($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if(Config::checkFeature('modulesync')) {
            \Rbs\MoyskladStocks\Import\Type\Assortment::import_ext_codes();
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_current_stocks($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync')) {
            \Rbs\MoyskladStocks\Import\Type\CurrentStocks::import();
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }
 
    public static function import_stocks($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync')) {
            \Rbs\MoyskladStocks\Import\Type\Stocks::import();
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_prices_product($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('productprices')) {
            \Rbs\MoyskladStocks\Import\Type\Prices::import('product');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_prices_variant($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('variantprices')) {
            \Rbs\MoyskladStocks\Import\Type\Prices::import('variant');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_prices_bundle($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('bundleprices')) {
            \Rbs\MoyskladStocks\Import\Type\Prices::import('bundle');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_prices_service($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('serviceprices')) {
            \Rbs\MoyskladStocks\Import\Type\Prices::import('service');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_product($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('import_product')) {
            \CRbsMoyskladStocks::import_entity('product');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_variant($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('import_variant')) {
            \CRbsMoyskladStocks::import_entity('variant');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_bundle($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('import_bundle')) {
            \CRbsMoyskladStocks::import_entity('bundle');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_service($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('import_service')) {
            \CRbsMoyskladStocks::import_entity('service');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_productfolder($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }
        
        if (Config::checkFeature('modulesync') && Config::checkFeature('import_productfolder')) {
            Import\Productfolder::import();
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_discount($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('ds_sync')) {
            Import\Discount::import();
        }
        
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_bundle_stocks($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature('bundlestocks')) {
            CRbsMoyskladStocks::import_bundle_stocks('default');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function import_bundle_current_stocks($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync')) {
            CRbsMoyskladStocks::import_bundle_stocks('current');
        }

        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function run_all_agents()
    {
        $arExecutedAgents = [];

        if(!Config::checkFeature('modulesync')) {
            return $arExecutedAgents;
        }

        $profileId = Config::getProfileId();

        //update_ext_codes
        $agentManager = new AgentManager('update_ext_codes');
        if ($agentManager->isEnableAgentForCron()) {
            $arExecutedAgents[] = "update_ext_codes({$profileId})";
            self::update_ext_codes($profileId);
        }
        
        $baseEntityList = ['productfolder', 'product', 'variant', 'service', 'bundle'];
        foreach($baseEntityList as $entity) {

            $updateList = ImportParamsConfig::getUpParams($entity);
            $importParamsList = ImportParamsConfig::getImportParams($entity);

            $isImportAgentExec = (new AgentManager($entity))->isEnableAgentForCron();
            $isPricesAgentExec = (new AgentManager("{$entity}_price"))->isEnableAgentForCron();

            $isDeactivateAgentExec = !$importParamsList['include_archived'] && $updateList['archived'];
            $isDeactivateByFilterAgentExec = $updateList['active_by_filter'] && Config::isEnableFilterProp($entity);
            $isDeactivateByFolderAgentExec = $updateList['outer_sec'] && \Rbs\MoyskladStocks\Process\Helper::isNeedGroupItem($entity);

            $agentList = [
                'import_'. $entity => $isImportAgentExec,
                'import_prices_' . $entity => $isPricesAgentExec,
                'deactivate_' . $entity => $isImportAgentExec && $isDeactivateAgentExec,
                'deactivate_by_filter_' . $entity => $isImportAgentExec && $isDeactivateByFilterAgentExec,
                'deactivate_by_folder_' . $entity => $isImportAgentExec && $isDeactivateByFolderAgentExec,
            ];

            foreach($agentList as $agentName => $agentExec) {
                if($agentExec && method_exists(__CLASS__, $agentName)) {
                    $arExecutedAgents[] = "{$agentName}({$profileId})";
                    self::$agentName($profileId);
                }
            }

        }

        $paramsCurrentStocks = Config::getCurrentStocksParams();
        $hasCurrBundleStocksImport = in_array('bundle', $paramsCurrentStocks['entity_type']);

        $oneAgentEntity = ['stocks' => 'import_stocks', 'curr_stocks' => 'import_current_stocks', 'discount' => 'import_discount'];
        foreach($oneAgentEntity as $entity => $agentName) {
            if ((new AgentManager($entity))->isEnableAgentForCron()) {
                $arExecutedAgents[] = "{$agentName}({$profileId})";
                self::$agentName($profileId);
                if($agentName === 'import_current_stocks' && $hasCurrBundleStocksImport) {
                    self::import_bundle_current_stocks($profileId);
                    $arExecutedAgents[] = "import_bundle_current_stocks({$profileId})";
                }
            }
        }

        //bundle component agent
        $agentManager = new AgentManager('bundle_stocks');
        if($agentManager->isEnableAgentForCron() && Config::checkFeature("bundlestocks")){
            $arExecutedAgents[] = "import_bundle_stocks({$profileId})";
            self::import_bundle_stocks($profileId);
        }

        return $arExecutedAgents;

    }

    public static function set_webhook_agent($agentName = '', $searchAgentStr = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . $agentName;
        $agentNameSearch = '\\' . __CLASS__ . '::' . $searchAgentStr;

        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameSearch]);

        $offsetTime = $interval = 60;
        
        $culture = \Bitrix\Main\Context::getCurrent()->getCulture();
        $phpDateTime = new \DateTime();
        $dateTime = \Bitrix\Main\Type\DateTime::createFromPhp($phpDateTime->modify('+'.((int)$offsetTime).' second'));

        if ($obAgent = $res->GetNext()) {
            \CAgent::Update($obAgent['ID'], [
                'NAME' => $agentNameFull,
                'AGENT_INTERVAL' => $interval
            ]);
        } else {
            \CAgent::AddAgent($agentNameFull, Config::getModuleId(true), "N", $interval, $dateTime->toString($culture), "Y", $dateTime->toString($culture), 30);
        }
    }

    public static function import_variants_by_product($productId = '', $profileId = 0, $offset = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && !empty($productId)) {

            $process = new \Rbs\MoyskladStocks\Process\ImportVariantByWebhook($productId, (int)$offset);
            $process->execute();
            $offset = $process->getOffset();

            if ($offset  > 0) {
                return '\\' . __METHOD__ . "('{$productId}', {$profileId}, {$offset});";
            }
            
        }

        return false;
    }

    public static function deactivate_entity($profileId = 0, $entity = 'product')
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature("import_{$entity}")) {
            $deactivateProcess = new \Rbs\MoyskladStocks\Process\DeactivateEntity($entity, 'archive');
            $deactivateProcess->execute();
        }
    }

    public static function deactivate_product($profileId = 0)
    {
        self::deactivate_entity($profileId, 'product');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_variant($profileId = 0)
    {
        self::deactivate_entity($profileId, 'variant');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_service($profileId = 0)
    {
        self::deactivate_entity($profileId, 'service');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_bundle($profileId = 0)
    {
        self::deactivate_entity($profileId, 'bundle');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_entity_by_filter($profileId = 0, $entity = 'product', $type = 'filter')
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync') && Config::checkFeature("import_{$entity}")) {
            $deactivateProcess = new \Rbs\MoyskladStocks\Process\DeactivateEntity($entity, $type);
            $deactivateProcess->execute();
        }
    }

    public static function deactivate_by_filter_product($profileId = 0)
    {
        self::deactivate_entity_by_filter($profileId, 'product');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_by_filter_variant($profileId = 0)
    {
        return false;
    }

    public static function deactivate_by_filter_service($profileId = 0)
    {
        self::deactivate_entity_by_filter($profileId, 'service');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_by_filter_bundle($profileId = 0)
    {
        self::deactivate_entity_by_filter($profileId, 'bundle');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_by_folder_product($profileId = 0)
    {
        self::deactivate_entity_by_filter($profileId, 'product', 'folder');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_by_folder_variant($profileId = 0)
    {
        return false;
    }

    public static function deactivate_by_folder_service($profileId = 0)
    {
        self::deactivate_entity_by_filter($profileId, 'service', 'folder');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    public static function deactivate_by_folder_bundle($profileId = 0)
    {
        self::deactivate_entity_by_filter($profileId, 'bundle', 'folder');
        return '\\' . __METHOD__ . "({$profileId});";
    }

    

    /** @deprecated */
    public static function import_entity($entity = '', $profileId = 0, $limit = 0, $offset = 0)
    {
        return $offset;
    }

    /** @deprecated */
    public static function import_bundle_component($profileId = 0)
    {
        $method = str_replace('_component', '_stocks', __METHOD__);
        return '\\' . $method . "({$profileId});";
    }

    /** @deprecated */
    public static function stockCheck($profileId = 0)
    {
        return false;
    }

    /** @deprecated */
    public static function cache_groups($profileId = 0)
    {
        return false;
    }

    /** @deprecated */
    public static function moduleExecuteAgent($profileId = 0)
    {
        return false;
    }

    /** @deprecated */
    public static function updateExtCodes($profileId = 0)
    {
        return false;
    }
}
