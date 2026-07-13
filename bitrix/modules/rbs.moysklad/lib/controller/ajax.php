<?php
namespace Rbs\Moysklad\Controller;
 
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Rbs\Moysklad\Agent;
use Rbs\Moysklad\Config;
use Rbs\Moysklad\Services\OrderFilter;

class Ajax extends Controller
{
	/**
	 * @return array
	 */
	public function configureActions()
	{
		return [
			'createorder' => [
				'prefilters' => []
            ],
            'getLogRequest' => [
                'prefilters' => []
            ],
            'saveAuth' => [
                'prefilters' => []
            ],
            'getLicenseCheck' => [
                'prefilters' => [],
                'params' => []
            ]
		];
	}

    public function getLicenseCheckAction()
    {
        return \Bitrix\Main\Web\Json::encode(\CRbsMoyskladHelper::getLicenseData());
    }

    /** @deprecated */
    public static function getLogRequestAction($type = '', $profileId = 0)
    {
        return \Bitrix\Main\Web\Json::encode(['status' => 'empty']);
    }

    public static function saveAuthAction($authType = 'token', $authData = '', $profile_id = 0)
    {
        $result = ['status' => 'save'];

        if($profile_id > 0) {
            Config::setProfileId($profile_id);
        }
        
        if($authType === 'token'){
            Config::setOption($authType, $authData, '');
        } else {
            $authData = explode('|', $authData);
            Config::setOption('login', $authData[0], '');
            Config::setOption('pass', $authData[1], '');
        }

        return \Bitrix\Main\Web\Json::encode($result);
    }
 
	public static function createorderAction($orderAccountId = '')
	{
        if(Config::isProfilesOn()){
            $profileIdList = Config::getProfileIdList();
            foreach($profileIdList as $profileId){
                Config::setProfileId($profileId);
                self::createOrderAjax((string)$orderAccountId);
            }
        } else {
            self::createOrderAjax((string)$orderAccountId);
        }
	}

        private static function createOrderAjax($orderAccountId = '')
        {
            if(Config::checkFeature('modulesync') && Loader::includeModule('sale') && !Config::isDemoExpired() && !empty($orderAccountId)){
                if($order = \Bitrix\Sale\Order::loadByAccountNumber((string)$orderAccountId)){

                    $orderId = $order->getField('ID');
                    
                    if (Config::isDisableOrderIdSync($orderId)) {
                        return;
                    }

                    if (OrderFilter::isFiltred($orderId)) {
                        return;
                    }

                    if (OrderFilter::isFiltredByStatus($order->getField('STATUS_ID'))) {
                        return;
                    } 
                                        
                    $agentResult = Agent::createOrderApi($orderId, Config::getProfileId());
                    if($agentResult === false){
                        Agent::delete("createOrderApi({$orderId});");
                    }
                    
                }
            }
        }

    public static function getScript()
    {
        $scriptArr = [
            "<script>",
                "let params = (new URL(document.location)).searchParams;",
                "request = BX.ajax.runAction('rbs:moysklad.api.ajax.createorder', {",
                    "data: {orderAccountId: params.get('ORDER_ID')}",
                "});",
            "</script>"
        ];

        echo implode("\n", $scriptArr);
    }
}