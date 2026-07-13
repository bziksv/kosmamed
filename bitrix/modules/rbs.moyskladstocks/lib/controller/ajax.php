<?php

namespace Rbs\MoyskladStocks\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;


use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Agent;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Utils;

use Rbs\MoyskladStocks\Internals\OptionUtils;
use Rbs\MoyskladStocks\Internals\Profiles;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;

class Ajax extends Controller
{

    private const ACTION_CONFIG = [
        'getOptionApiRequest' => [
            'prefilters' => []
        ],
        'getPropOption' => [
            'prefilters' => []
        ],
        'saveAuth' => [
            'prefilters' => []
        ],
        'setAgentParams' => [
            'prefilters' => []
        ],
        'getAgentInfo' => [
            'prefilters' => []
        ],
        'renameProfile' => [
            'prefilters' => []
        ]
    ];
    
    public function configureActions()
    {
        return array_map(function ($config) {
            return ['prefilters' => [
                new Authentication(),
                new Csrf()
            ]];
        }, self::ACTION_CONFIG);
    }

    public static function saveAuthAction($authType = 'token', $authData = '', $profileId = 0)
    {
        Config::setProfileId((int)$profileId);

        $result = ['status' => 'save'];
        
        if ($authType === 'token') {
            Config::setOption($authType, $authData, '');
        } else {
            $authData = explode('|', $authData);
            Config::setOption('login', $authData[0], '');
            Config::setOption('pass', $authData[1], '');
        }

        return Json::encode($result);
    }

    public static function getPropOptionAction($entity = '', $propId = '', $iblockId = 0, $profileId = 0)
    {
        Config::setProfileId((int)$profileId);

        $result = ['status' => 'empty'];

        $iblockId = (int)$iblockId;
        $propId = (int)$propId;
        
        if (\Bitrix\Main\Loader::includeModule('iblock') && (int)$iblockId > 0) {

            $propertyOption = new \Rbs\MoyskladStocks\Internals\PropertyOption($entity, $iblockId);

            if (!empty($propertyOption->getBxPropertyName($propId))) {

                $result['status'] = 'success';
                $result['option'] = [
                    'id' => "im_{$entity}_p_prop_{$propId}",
                    'name' => $propertyOption->getBxPropertyName($propId),
                    'variants' => $propertyOption->getPropertyVariantsForPropId($propId)
                ];

                $variantsAsArray = [];
                if (isset($result['option']['variants']) && Utils::is_count($result['option']['variants'])) {
                    foreach ($result['option']['variants'] as $vId => $vName) {
                        $variantsAsArray[] = [
                            'val' => $vId,
                            'text' => $vName
                        ];
                    }
                }
                if (count($variantsAsArray) > 0) {
                    $result['option']['variants'] = $variantsAsArray;
                }

            }
        }

        return Json::encode($result);
    }

    public static function getOptionApiRequestAction($type = '', $profileId = 0)
    {
        Config::setProfileId((int)$profileId);

        $result = [];
        switch ($type) {
            case 'stocks_count':
                //response
                $reportStock = ApiNew::get('/report/stock/bystore', ['limit' => 1, 'offset' => 0, 'filter' => Config::getFilterStocksString()]);
                if (Utils::is_success($reportStock)) {
                    $result = ['response' => $reportStock->meta->size, 'status' => 'success'];
                } else {
                    $result = ['status' => 'error'];
                }
            break;
            case 'product_count':
            case 'variant_count':
            case 'service_count':
            case 'bundle_count':

                try {

                    $entity = current(explode('_', $type));

                    $importParamsList = ImportParamsConfig::getImportParams($entity);
                    $customFilter = [];
                    if ($importParamsList['include_archived']) {
                        $customFilter = ['archived=true','archived=false'];
                    }

                    $isVariant = $entity === 'variant';
                    if($isVariant) {
                        $customFilter[] = 'type=variant';
                    }

                    $msApi = ApiNew::get($isVariant ? '/entity/assortment' : '/entity/' . $entity, ['limit' => 1, 'offset' => 0, 'filter' => \CRbsMoyskladStocks::getFilterString($entity, implode(';', $customFilter))]);

                    if (Utils::is_success($msApi)) {
                        $result = ['response' => $msApi->meta->size, 'status' => 'success'];
                    } else {
                        $result = ['status' => 'error', 'message' => current($msApi->errors)];
                    }

                } catch(\Bitrix\Main\SystemException $e) {
                    $result = ['status' => 'error', 'message' => $e->getMessage()];
                }

            break;
        }

        if (!empty($result)) {
            return Json::encode($result);
        }
        
        return Json::encode(['status' => 'empty']);
    }

    public static function setAgentParamsAction($agentId = '', $params = [], $profileId = 0)
    {
        Config::setProfileId((int)$profileId);
        $agentManager = new AgentManager($agentId);

        $currentParams = $agentManager->getAllParamsAsArray();
        foreach($currentParams as $paramId => $paramValue) {
            if(isset($params[$paramId]) && $params[$paramId] != $paramValue) {
                $agentManager->setConfigValue($paramId, $params[$paramId]);
            }
        }

        if (in_array($agentId, ['product', 'variant', 'service', 'bundle'])) {
            Agent::delete('deactivate_' . $agentId);
            Agent::delete('deactivate_by_filter_' . $agentId);
            Agent::delete('deactivate_by_folder_' . $agentId);
        }
        if (in_array($agentId, ['stocks', 'bundle'])) {
            Agent::delete('import_' . $agentId . '_component');
        }
        if (in_array($agentId, ['curr_stocks'])) {
            Agent::delete("import_bundle_current_stocks");
        }

        $agentFunction = isset(Agent::$agentFunctionList[$agentId]) ? Agent::$agentFunctionList[$agentId] : '';
        if($agentManager->isEnabled()) {
            if (!$agentManager->isEnableAgentForCron()) {

                Agent::set($agentFunction, $agentManager->getConfigValue('interval', 120));

                if (in_array($agentId, ['product', 'variant', 'service', 'bundle'])) {
                    if (!Config::checkFeature("im_{$agentId}_p_include_archived") && Config::checkFeature("im_{$agentId}_up_archived")) {
                        Agent::set("deactivate_{$agentId}", 360);
                    }
                    if (Config::checkFeature("im_{$agentId}_filter_prop") !== 'N' && Config::checkFeature("im_{$agentId}_up_active_by_filter")) {
                        Agent::set("deactivate_by_filter_{$agentId}", 360);
                    }
                    if (\Rbs\MoyskladStocks\Process\Helper::isNeedGroupItem($agentId) && Config::checkFeature("im_{$agentId}_up_outer_sec")) {
                        Agent::set("deactivate_by_folder_{$agentId}", 360);
                    }
                }

                if (in_array($agentId, ['curr_stocks'])) {
                    $currStocksParams = Config::getCurrentStocksParams();
                    if(in_array('bundle', $currStocksParams['entity_type'])) {
                        Agent::set("import_bundle_current_stocks", 60);
                    }
                }

            }
        }

        if(!$agentManager->isEnabled() || ($agentManager->isEnabled() && $agentManager->isEnableAgentForCron())) {
             Agent::delete($agentFunction);
        }

        $agentFunctionParams = ['ID' => 0];
        if(!empty($agentFunction)) {
            $agentFunctionParams = Agent::getInfo($agentFunction);
        }

        return Json::encode([
            'agent_function_params' => $agentFunctionParams,
            'agent_params' => $agentManager->getAllParamsAsArray() + OptionUtils::buildAgentIdInputParams($agentId)
        ]);
    }

    public static function getAgentParamsAction($agentId = '', $profileId = 0)
    {
        Config::setProfileId((int)$profileId);
        $agentManager = new AgentManager($agentId);

        $agentFunction = isset(Agent::$agentFunctionList[$agentId]) ? Agent::$agentFunctionList[$agentId] : '';
        $agentFunctionParams = ['ID' => 0];
        if (!empty($agentFunction)) {
            $agentFunctionParams = Agent::getInfo($agentFunction);
        }

        return Json::encode([
            'agent_function_params' => $agentFunctionParams,
            'agent_params' => $agentManager->getAllParamsAsArray() + OptionUtils::buildAgentIdInputParams($agentId)
        ]);
    }

    public static function getAgentInfoAction($agentId = '', $profileId = 0)
    {
        $agentInfo = [];

        Config::setProfileId((int)$profileId);
        $agentFunction = isset(Agent::$agentFunctionList[$agentId]) ? Agent::$agentFunctionList[$agentId] : '';
        if(!empty($agentFunction) && Agent::isEnabledAgent($agentFunction)) {
            $agentInfo = Agent::get($agentFunction);
        }
        return Json::encode([
            'agent_id' => isset($agentInfo['ID']) ? $agentInfo['ID'] : 0,
            'agent_info' => $agentInfo
        ]);
    }

    public static function renameProfileAction($profileId = 0, $name = '')
    {
        $profileId = (int)$profileId;
        $name = trim($name);

        Profiles::setProfileName($profileId, $name);

        return Json::encode(['status' => 'success']);
    }
}
