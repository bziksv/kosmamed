<?php
namespace Rbs\Moysklad;

\Bitrix\Main\Loader::IncludeModule("sale");

class Config
{
    public static function getModuleId($isOrig = false): string
    {
        if (!$isOrig && self::getProfileId() > 0) {
            return 'rbs.moysklad_' . self::getProfileId();
        }
        return 'rbs.moysklad';
    }

    public static function isDemo(): bool
    {
        return \Bitrix\Main\Loader::includeSharewareModule(self::getModuleId(true)) === \Bitrix\Main\Loader::MODULE_DEMO;
    }

    public static function isDemoExpired(): bool
    {
        return \Bitrix\Main\Loader::includeSharewareModule(self::getModuleId(true)) === \Bitrix\Main\Loader::MODULE_DEMO_EXPIRED;
    }

    public static function getSitePath(): string
    {
        $siteConfig = self::getOption('site_path');
        $sitePath = $siteConfig ? $siteConfig : $_SERVER['HTTP_ORIGIN'];

        return $sitePath;
    }

    public static function getCachePath($path = ''): string
    {
        return '/' . self::getModuleId() . '/' . $path . '/';
    }

    public static function getProfileId()
    {
        return isset($GLOBALS['rbsMsOrdersProfile']) ? (int)$GLOBALS['rbsMsOrdersProfile'] : 0;
    }

    public static function setProfileId($profileId = 0)
    {
        $GLOBALS['rbsMsOrdersProfile'] = (int)$profileId;
    }

    public static function isIgnorePushToMs(): bool
    {
        return isset($GLOBALS['isHookScript']) ? (bool)$GLOBALS['isHookScript'] : false;
    }

    public static function setIgnorePushToMs(bool $flag = false)
    {
        $GLOBALS['isHookScript'] = $flag;
    }

    public static function setProfilesOn()
    {
        \Bitrix\Main\Config\Option::set('ordersprofiles', 'profiles_on', 'Y', false);
    }

    public static function setProfilesOff()
    {
        \Bitrix\Main\Config\Option::set('ordersprofiles', 'profiles_on', 'N', false);
    }

    public static function isProfilesOn()
    {
        return \Bitrix\Main\Config\Option::get('ordersprofiles', 'profiles_on') === 'Y';
    }

    public static function isLockOrder($orderId): bool
    {
        if(!empty($orderId)) {
            $orderId = (int)$orderId;
            return isset($GLOBALS[Config::getModuleId()]['updated_in_hit'][$orderId]);
        }
        return false;
    }

    public static function lockOrder($orderId)
    {
        if (!empty($orderId)) {
            $orderId = (int)$orderId;
            $GLOBALS[Config::getModuleId()]['updated_in_hit'][$orderId] = true;
        }
    }    

    public static function getUploadDir($subDir = '')
    {
        $profileDir = '';
        if (self::getProfileId() > 0) {
            $profileDir = 'profile_' . self::getProfileId() . '/';
        }

        $upload_dir = '/' . \COption::GetOptionString("main", "upload_dir", "upload") . '/';

        if (empty($subDir)) {
            $path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir . self::getModuleId(true) . '/' . $profileDir;
            if (!is_dir($path)) {
                mkdir($path, 0755);
            }
        } else {
            $path = $_SERVER['DOCUMENT_ROOT'] . $upload_dir . self::getModuleId(true) . '/' . $profileDir . $subDir . '/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        return $path;
    }

    public static function getProfileIdList($eventFrom = null, $customerOrder = null): array
    {
        $defaultProfieIdList = [0, 1];

        $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnGetProfileIdList", array(
            'eventFrom' => $eventFrom,
            'customerOrder' => $customerOrder
        ));
        $event->send();

        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $eventResultParams = $eventResult->getParameters();
                    if (!empty($eventResultParams['profile_id_list']) && is_array($eventResultParams['profile_id_list']) && count($eventResultParams['profile_id_list']) > 0) {
                        if(isset($eventResultParams['replace_profile_list']) && (bool)$eventResultParams['replace_profile_list']) {
                            $defaultProfieIdList = $eventResultParams['profile_id_list'];
                        } else {
                            $defaultProfieIdList = array_merge($defaultProfieIdList, $eventResultParams['profile_id_list']);
                        }
                    }
                }
            }
        }

        return $defaultProfieIdList;
    }

    public static function getBaseHrefLinkNew($entity = ''): string
    {
        $hrefBaseLinks = [
            'counterparty' =>   ApiNew::getApiEndPointUrl() . '/entity/counterparty/',
            'product' =>        ApiNew::getApiEndPointUrl() . '/entity/product/',
            'store' =>          ApiNew::getApiEndPointUrl() . '/entity/store/',
            'contract' =>       ApiNew::getApiEndPointUrl() . '/entity/contract/',
            'state' =>          ApiNew::getApiEndPointUrl() . '/entity/customerorder/metadata/states/',
            'paymentin_state'=> ApiNew::getApiEndPointUrl() . '/entity/paymentin/metadata/states/',
            'cashin_state' =>   ApiNew::getApiEndPointUrl() . '/entity/cashin/metadata/states/',
            'counterparty_state' => ApiNew::getApiEndPointUrl() . '/entity/counterparty/metadata/states/',
            'project' =>        ApiNew::getApiEndPointUrl() . '/entity/project/',
            'group' =>          ApiNew::getApiEndPointUrl() . '/entity/group/',
            'saleschannel' =>   ApiNew::getApiEndPointUrl() . '/entity/saleschannel/',
            'employee' =>       ApiNew::getApiEndPointUrl() . '/entity/employee/',
            'productfolder' =>  ApiNew::getApiEndPointUrl() . '/entity/productfolder/',
            'customEntity' =>   ApiNew::getApiEndPointUrl() . '/entity/customentity/'
        ];

        return isset($hrefBaseLinks[$entity]) ? $hrefBaseLinks[$entity] : '';
    }

    public static function getStandartEntityNamesForEnumProp()
    {
        return [
            'employee',
			'project',
			'counterparty',
			'product',
			'store',
			'contract'
        ];
    }

    public static function getAttributeMetaLink($propId = '', $entity = '')
    {
        return (object) [
            "href" => ApiNew::getApiEndPointUrl() . "/entity/{$entity}/metadata/attributes/" . $propId,
            "type" => "attributemetadata",
            "mediaType" => "application/json"
        ];
    }

    public static function getMetaDataStateNew($href = '', $entity = '')
    {
        return (object)[
            'meta' => (object)[
                'href' => $href,
                'metadataHref' => ApiNew::getApiEndPointUrl() . '/entity/' . $entity . '/metadata',
                'type' => 'state',
                'mediaType' => 'application/json'
            ]
        ];
    }

    public static function getMetaData($entity = '', $entityId = '')
    {
        return (object)[
            'meta' => (object)[
                'href' => Config::getBaseHrefLinkNew($entity) . $entityId,
                'type' => $entity,
                'metadataHref' => ApiNew::getApiEndPointUrl() . '/entity/' . $entity . '/metadata',
                'mediaType' => 'application/json'
            ]
        ];
    }

    public static function getCommentDelimiter()
    {
        return '[manager]';
    }

    public static function getApiCacheId()
    {
        $currentCache = self::getOption('cache_api_id');
        if (empty($currentCache)) {
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

    public static function getLogType()
    {
        return 'FILES';
    }

    public static function getAttemptsApiErrorCount()
    {
        $attempts = (int)self::getOption('attempt_api_error_count', 3);
        if($attempts > 10){
            $attempts = 10;
        }
        return $attempts > 0 ? $attempts : 3;
    }

    public static function getAttemptsApiDelay()
    {
        $delay = (int)self::getOption('attempt_api_error_delay', 500);
        if($delay > 2000){
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

    public static function checkSalt()
    {
        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
       
        $profileId = (int)$request->get('profile_id');
        if ($profileId > 0) {
            self::setProfileId($profileId);
        }

        if (!self::checkFeature('modulesync')) {
            return false;
        }
        
        return self::getOption('webhook_url_salt') === $request->get('checkUrl');
    }

    public static function getOrderPrefix()
    {
        return self::getOption('order_number_prefix');
    }


    public static function getPaymentStateHref($paymentType = 'paymentin')
    {
        $stateId = self::getOption('pays_sync_status_' . $paymentType);
        if (!empty($stateId)) {
            return self::getBaseHrefLinkNew($paymentType . '_state') . $stateId;
        }
        return false;
    }

    public static function getPaymentProjId($paymentType = 'paymentin')
    {
        $projId = self::getOption('pays_proj_' . $paymentType);
        if (!empty($projId) && $projId !== 'N') {
            return $projId;
        }
        return false;
    }

    public static function getPaymentSyncData($paySysId = 0)
    {
        return [
            'group' => self::getOption('paysys_group_default'),
            'owner' => self::getOption('paysys_employee_default'),
            'organization' => self::getOption('paysys_org_' . $paySysId),
            'organizationAccount' => self::getOption('paysys_acc_' . $paySysId),
        ];
    }

    public static function getPaymentType($paySysId)
    {
        return self::getOption('paysys_type_' . $paySysId, 'N');
    }

    public static function getDefaultPaysystemId($paymentType = 'paymentin')
    {
        return self::getOption('def_payid_' . $paymentType);
    }

    public static function getAgentInterval()
    {
        $value = self::getOption('agent_interval');
        if ((int)$value <= 0) {
            $value = 60;
        }
        return (int)$value;
    }

    public static function getAgentLiveTime()
    {
        return (int)self::getOption('agent_live_time') > 0 ? (int)self::getOption('agent_live_time') : 86400;
    }

    public static function getTrackNumberPropertyId()
    {
        return self::getOption('delivery_track', 'N') !== 'N' ? self::getOption('delivery_track', 'N') : '';
    }

    public static function getDeliveryNamePropertyId()
    {
        return self::getOption('delivery_name_export', 'N') !== 'N' ? self::getOption('delivery_name_export', 'N') : '';
    }

    public static function getPaysystemNamePropertyId()
    {
        return self::getOption('pays_name_export', 'N') !== 'N' ? self::getOption('pays_name_export', 'N') : '';
    }

    public static function getPaysystemInfoPropertyId()
    {
        return self::getOption('pays_info', 'N') !== 'N' ? self::getOption('pays_info', 'N') : '';
    }

    public static function getPaySyncType()
    {
        return self::getOption('pays_sync_type') === 'full' ? 'full' : 'default';
    }

    public static function getPropOrderId()
    {
        return !empty(self::getOption('order_sync_prop_id')) ? self::getOption('order_sync_prop_id') : 'N';
    }

    public static function getPropOrderAccountNum()
    {
        return !empty(self::getOption('order_sync_prop_account_num')) ? self::getOption('order_sync_prop_account_num') : 'N';
    }

    public static function getPayMethodPropHref($type = 'paymentin')
    {
        $val = self::getOption('pays_sync_method_prop_' . $type);
        if (!empty($val)) {
            return array_pop(explode(';', $val));
        }

        return false;
    }

    public static function getCustomEntityProp($propId = 0): array
    {
        $result = [];

        $val = self::getOption('prop_bx_enum_' . $propId, 'N');
        if (!empty($val)) {
            $result = explode(';', $val);
        }

        return $result;
    }

    public static function getPayMethodPropId($type = 'paymentin')
    {
        $val = self::getOption('pays_sync_method_prop_' . $type);
        if (!empty($val)) {
            return array_shift(explode(';', $val));
        }

        return false;
    }

    public static function getPayMethodIds($type = 'paymentin')
    {
        $paySystemResult = \Bitrix\Sale\PaySystem\Manager::getList(['filter' => ['ACTIVE' => 'Y']])->fetchAll();
        $result = [];

        foreach ($paySystemResult as $pay) {
            $result[$pay['ID']] = self::getOption('pays_sync_method_prop_' . $type . '_' . $pay['ID']);
        }

        return $result;
    }

    public static function getBasketBetaOrderIds()
    {
        if (!empty(trim(self::getOption('basket_beta_order_id')))) {
            return explode(',', self::getOption('basket_beta_order_id'));
        } else {
            return [];
        }
    }

    public static function getBasketDelay()
    {
        return (int)self::getOption('basket_delay');
    }

    public static function cacheTime($option = '')
    {
        if (empty($option)) {
            return 0;
        }

        $optionBx = '';
        switch ($option) {
            case 'basket_items_bx':
                $optionBx = 'cache_basket_bx_items';
            break;
            case 'basket_items_ms':
                $optionBx = 'cache_basket_ms_items';
            break;
        }

        if (!empty($optionBx)) {
            return (int)self::getOption($optionBx);
        } else {
            return 0;
        }
    }

    public static function getCacheHookTime()
    {
        $cacheTime =  (int)self::getOption('cache_webhook_time', 5);
        return $cacheTime > 0 && $cacheTime <= 10 ? $cacheTime : 5;
    }

    public static function getOrderNumField()
    {
        return self::getOption('order_name_bx_field') ? : 'ID';
    }

    public static function getUserId()
    {
        $uid = self::getOption('user_id');
        return (int)$uid > 0 ? (int)$uid : 0;
    }

    public static function getDefaultImportSiteId()
    {
        $defSite = \Bitrix\Main\SiteTable::getList([
            'filter' => [
                'DEF' => 'Y'
            ],
            'cache' => [
                'ttl' => 3600
            ]
        ])->fetch();
        $siteId = self::getOption('import_site', $defSite['LID']);
        return $siteId;
    }

    public static function getDefaultImportPaysys()
    {
        return self::getOption('import_order_pay');
    }

    public static function getDefaultImportDelivery()
    {
        return self::getOption('import_order_delivery');
    }

    public static function checkFeature($featureName = ''): bool
    {
        $optionFeature = '';
        switch ($featureName) {
            case 'modulesync':
                if (self::isDemoExpired()) {
                    return false;
                }
                $optionFeature = 'global_enabled';
            break;
            case 'importorder':
                $optionFeature = 'import_order';
            break;
            case 'orderextcode':
                $optionFeature = 'order_find_by_ext_code';
            break;
            case 'orderreserved':
                $optionFeature = 'is_order_reserved';
            break;
            case 'basketreservededit':
                $optionFeature = 'basket_reserved_edit';
            break;
            case 'cancelreserve':
                $optionFeature = 'order_cancel_reserve';
            break;
            case 'statuscancelreserve':
                $optionFeature = 'status_reserve_enable';
            break;
            case 'basketvatrate':
                $optionFeature = 'basket_vat';
            break;
            case 'responsesync':
                $optionFeature = 'order_r_sync';
            break;
            case 'employeegroupsync':
                $optionFeature = 'order_rg_sync';
            break;
            case 'ordernumberbx':
                $optionFeature = 'order_name_bx';
            break;
            case 'vatincluded':
                $optionFeature = 'is_order_vatincluded';
            break;
            case 'vatenabled':
                $optionFeature = 'is_order_vatenabled';
            break;
            case 'basketsync':
                $optionFeature = 'basket_enabled';
            break;
            case 'basketsyncbx':
                $optionFeature = 'basket_enabled_bx';
            break;
            case 'basketcreateitem':
                $optionFeature = 'basket_create_items';
            break;
            case 'baskethardadd':
                $optionFeature = 'basket_hard_add_item';
            break;
            case 'basketmodifsync': 
                $optionFeature = 'basket_modif_enabled';
            break;
            case 'basketarchived':
                $optionFeature = 'basket_archived';
            break;
            case 'basketdoublesync':
                $optionFeature = 'basket_doubles';
            break;
            case 'basketextcodessource':
                $optionFeature = 'basket_ext_code_source';
            break;
            case 'basketbundlesync':
                $optionFeature = 'basket_bundle_enabled';
            break;
            case 'basketbundlerecalc':
                $optionFeature = 'basket_bundle_recalc';
            break;
            case 'basketprovideroff':
                $optionFeature = 'basket_provider_off';
            break;
            case 'basketshipmentrefresh':
                $optionFeature = 'basket_shipment_refresh_enabled';
            break;
            case 'basketrecalc':
                $optionFeature = 'basket_recalc_enabled';
            break;
            case 'propssync':
                $optionFeature = 'props_enabled';
            break;
            case 'propsreversesync':
                $optionFeature = 'props_reverse_enabled';
            break;
            case 'orderidsync':
                $optionFeature = 'order_sync_id';
            break;
            case 'orderaccountsync':
                $optionFeature = 'order_sync_account_num';
            break;
            case 'paysync':
                $optionFeature = 'pays_enabled';
            break;
            case 'payinfosync':
                $optionFeature = 'pays_info_enabled';
            break;
            case 'paynamesync':
                $optionFeature = 'pays_name_export_enabled';
            break;
            case 'paysmethodsync':
                $optionFeature = 'pays_sync_method_enabled';
            break;
            case 'tracksync':
                $optionFeature = 'delivery_track_enabled';
            break;
            case 'storesync':
                $optionFeature = 'delivery_store_enabled';
            break;
            case 'storesyncreverse':
                $optionFeature = 'delivery_store_reverse';
            break;
            case 'deliverynamesync':
                $optionFeature = 'delivery_name_export_enabled';
            break;
            case 'deliverytypesync':
                $optionFeature = 'delivery_sync_enabled';
            break;
            case 'deliverypricesync':
                $optionFeature = 'dprice_sync_enabled';
            break;
            case 'commentsync':
                $optionFeature = 'order_comment_enabled';
            break;
            case 'commentusersync':
                $optionFeature = 'order_comment_user_enabled';
            break;
            case 'statussync':
                $optionFeature = 'order_state_func_enabled';
            break;
            case 'cancelsync':
                $optionFeature = 'order_cancel_enabled';
            break;
            case 'counterpersonaldivide':
                $optionFeature = 'counter_personal_divide';
            break;
            case 'counterlegal':
                $optionFeature = 'counter_search_ip';
            break;
            case 'counterfields':
                $optionFeature = 'counter_fields_enabled';
            break;
            case 'counterextwrite':
                $optionFeature = 'counter_ext_write';
            break;
            case 'counterprops':
                $optionFeature = 'counter_props_enabled';
            break;
            case 'countersaveforce':
                $optionFeature = 'save_force_enabled';
            break;
            case 'loggerapi':
                $optionFeature = 'logger_api_enabled';
            break;
            case 'loggerbx':
                $optionFeature = 'logger_bx_enabled';
            break;
            case 'loggerapirequests':
                $optionFeature = 'logger_api_requests';
            break;
            case 'loggerexchange':
                $optionFeature = 'logger_apiexchange';
            break;
            case 'loggerexchangenotify':
                $optionFeature = 'logger_apiexchange_notify';
            break;
            case 'loggerexchangeemail':
                $optionFeature = 'logger_apiexchange_byemail';
            break;
            case 'isapisearchuser':
                $optionFeature = 'use_api_params_counter';
            break;
            case 'responsesyncprop':
                $optionFeature = 'order_r_prop';
            break;
            case 'loghlenable':
                $optionFeature = 'loghlenable';
            break;
            case 'loghlnotify':
                $optionFeature = 'loghlnotify';
            break;
            case 'customextfield':
                $optionFeature = 'order_ext_ms';
            break;
            case 'payment_customextfield':
                $optionFeature = 'pays_ext_ms';
            break;
            case 'importcontact':
                $optionFeature = 'import_order_contact';
            break;
            case 'setcurrency':
                $optionFeature = 'basket_currency';
            break;
            case 'paysrecalc':
                $optionFeature = 'recalc_pays';
            break;
            case 'reversebxcreatedorder':
                $optionFeature = 'bx_order_put';
            break;
            case 'savemsname':
                $optionFeature = 'bx_order_ms_name_enabled';
            break;
            case 'statusexport':
                $optionFeature = 'status_export_enable';
            break;
            case 'paymenttypesync':
                $optionFeature = 'pays_prop_enabled';
            break;
            case 'pushmsorderid':
                $optionFeature = 'ms_push_bx_order_id';
            break;
            case 'pushmsordername':
                $optionFeature = 'ms_push_order_name';
            break;
            default:
                $optionFeature = $featureName;
        }

        if (!empty($optionFeature)) {
            return self::getOption($optionFeature) === 'Y';
        }

        return false;
    }

    public static function getVector($typeOfExchange = '', $defaultVector = 'FULL')
    {
        $option = self::getOption('vector_' . $typeOfExchange, $defaultVector);
        return in_array($option, ['BX_MS', 'MS_BX', 'FULL']) ? $option : 'FULL';
    }

        public static function checkVectorFromBxToMs($typeOfExchange = '', $defaultVector = 'FULL') : bool
        {
            return self::getVector($typeOfExchange, $defaultVector) === 'BX_MS' || self::getVector($typeOfExchange, $defaultVector) === 'FULL';
        }

        public static function checkVectorFromMsToBx($typeOfExchange = '', $defaultVector = 'FULL'): bool
        {
            return self::getVector($typeOfExchange, $defaultVector) === 'MS_BX' || self::getVector($typeOfExchange, $defaultVector) === 'FULL';
        }

    public static function getMsNamePropId($personalType = 1)
    {
        return (int)self::getOption('bx_order_ms_name_' . $personalType);
    }

    public static function getExtFieldId()
    {
        return !empty(self::getOption('order_ext_ms_field')) ? self::getOption('order_ext_ms_field') : 'ID';
    }

    public static function getPushOrderBxField()
    {
        $curerntField = self::getOption('ms_push_bx_order_id_field_bx', 'ID');
        return
        in_array($curerntField, ['ID', 'ACCOUNT_NUMBER']) ? $curerntField : 'ID';
    }

    public static function getPushOrderMsField()
    {
        return self::getOption('ms_push_bx_order_id_field_ms', '');
    }

    public static function getPushOrderNameField()
    {
        $curerntField = self::getOption('ms_push_order_name_field', 'ID');
        return
        in_array($curerntField, ['ID', 'ACCOUNT_NUMBER']) ? $curerntField : 'ID';
    }

    public static function getImportType()
    {
        $curerntType = self::getOption('import_type', 'CREATE');
        return in_array($curerntType, ['CREATE', 'UPDATE']) ? $curerntType : 'CREATE';
    }

    public static function getImportTypeUpdateFlag()
    {
        return self::getOption('import_type_update_flag', '');
    }

    public static function getWebHookLimitCount()
    {
        $currentLimitCount = self::getOption('webhook_limit_count', 5);
        return (int)$currentLimitCount <= 25 ? (int)$currentLimitCount : 5;
    }

    public static function isSearchLegal($counterType = '')
    {
        if ($counterType == 'legal' || $counterType == 'entrepreneur') {
            if (self::checkFeature('counterlegal')) {
                return true;
            }
        }
        return false;
    }

    public static function reverseLegalType($counterType = '')
    {
        if ($counterType == 'legal') {
            return 'entrepreneur';
        }
        
        return 'legal';
    }

    public static function getLogHlTypes()
    {
        $tmp = self::getOption('loghllevel');
        if (!empty($tmp)) {
            $tmp = explode(',', $tmp);
            if (Utils::is_count($tmp)) {
                return $tmp;
            }
        }

        return [];
    }

    public static function getNewItemsFolder()
    {
        return self::getOption('basket_create_group') ? : 'N';
    }
    
    public static function getResponsePropId()
    {
        return self::getOption('order_r_prop_id');
    }

    /** @deprecated */
    public static function getEmailNotification()
    {
        return self::getOption('logger_apiexchange_mail');
    }

    /** @deprecated */
    public static function getEmailNotificationFrom()
    {
        return \COption::GetOptionString("main", "email_from");
    }

    public static function getOrderIdStart()
    {
        return (int)self::getOption('order_start_exchange');
    }
    
    public static function isFilterOn()
    {
        return self::getOption('order_filter_bx') === 'Y';
    }

    public static function isDisableOrderIdSync($orderId = 0)
    {
        if ((int)$orderId <= 0 || self::getOrderIdStart() <= 0) {
            return false;
        }
        return $orderId < self::getOrderIdStart();
    }
   
    public static function getExternalStatusId($statusId = '')
    {
        return self::getOption("order_state_{$statusId}") ? : false;
    }

    public static function getStatusIdByExternalStatusId($externalStatusId = '')
    {
        $allStatusIds = self::getAllStatusIds();
        return isset($allStatusIds[$externalStatusId]) ? $allStatusIds[$externalStatusId] : false;
    }

    public static function getAllStatusIds()
    {
        $statusResult = \Bitrix\Sale\Internals\StatusTable::getList(['cache' => ['ttl' => 3600]])->fetchAll();
        $result = [];
        foreach ($statusResult as $statusArray) {
            $statusExtId = self::getExternalStatusId($statusArray['ID']);
            if (!empty($statusExtId)) {
                $result[$statusExtId] = $statusArray['ID'];
            }
        }
        return $result;
    }

    public static function getAllStatusInfo($type = 'O')
    {
        if(empty($type)) {
            $type = 'O';
        }

        $statusResult = \Bitrix\Sale\Internals\StatusTable::getList(['filter' => ['TYPE' => $type], 'cache' => ['ttl' => 3600]])->fetchAll();
        $result = [];
        foreach ($statusResult as $statusArray) {
            $result[$statusArray['ID']] = $statusArray;
        }
        return $result;
    }

    public static function getDeliveryExternalCode()
    {
        return 'ORDER_DELIVERY';
    }

    public static function getUserCommentProp()
    {
        return self::getOption('order_comment_user_prop');
    }

    public static function getDisableOrderProp()
    {
        $result = self::getOption('order_disable_prop');
        
        return (!empty($result) && $result !== 'N') ? $result : false;
    }

    public static function getDeliveryProp()
    {
        $result = self::getOption('delivery_sync_prop', 'N');
        return $result !== 'N' ? $result : '';
    }

    public static function getPaymentProp()
    {
        $result = self::getOption('pays_sync_prop', 'N');
        return $result !== 'N' ? $result : '';
    }

    public static function getDeliveryIds(): array
    {
        $optionPrefix = 'delivery_sync_prop_';
        $deliveryList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
        $result = [];
        foreach ($deliveryList as $delivery) {
            $propId = self::getOption($optionPrefix . $delivery['ID'], 'N');
            if (empty($propId) || $propId === 'N') {
                continue;
            }
            $result[$delivery['ID']] = $propId;
        }
        return $result;
    }

    public static function getPaymentIds(): array
    {
        $optionPrefix = 'pays_sync_prop_';
        $paySystemResult = \Bitrix\Sale\PaySystem\Manager::getList(['filter' => ['ENTITY_REGISTRY_TYPE' => 'ORDER']]);
        $result = [];
        foreach ($paySystemResult as $payment) {
            $propId = self::getOption($optionPrefix . $payment['ID'], 'N');
            if (empty($propId) || $propId === 'N') {
                continue;
            }
            $result[$payment['ID']] = $propId;
        }
        return $result;
    }

    public static function getEnumPropsIds($filter = []): array
    {
        $optionPrefix = 'prop_bx_enum_';
        $orderPropsBx = \Bitrix\Sale\Internals\OrderPropsTable::getList(['filter' => $filter]);
        $result = [];
        while ($obOrderPropBx = $orderPropsBx->fetch()) {
            $propId = self::getOption($optionPrefix . $obOrderPropBx['ID']);
            if (empty($propId) || $propId === 'N') {
                continue;
            }
            $result[] = $obOrderPropBx['ID'];
        }
        return $result;
    }

    public static function getLocationPropIds($entity = 'customerorder'): array
    {
        $orderPropsBx = \Bitrix\Sale\Internals\OrderPropsTable::getList([
            'filter' => [
                'TYPE' => 'LOCATION'
            ]
        ]);

        $propLocs = [];
        while ($obOrderPropBx = $orderPropsBx->fetch()) {
            $propLocs[] = $obOrderPropBx['ID'];
        }

        $result = [];
        if (Utils::is_count($propLocs)) {
            foreach ($propLocs as $propId) {
                foreach (['COUNTRY', 'REGION', 'CITY'] as $locationType) {
                    $optionPrefix = 'loc_' . $locationType . '_prop_bx_';
                    switch ($entity) {
                        case 'customerorder':
                            $optionPrefix = 'loc_' . $locationType . '_prop_bx_' . $propId;
                        break;
                    }
    
                    $val = (string)self::getOption($optionPrefix);
                    if (empty($val) || $val === 'N') {
                        continue;
                    }
                    $result[$propId][$locationType] = $val;
                }
            }
        }
        
        return $result;
    }

    public static function getPropsIds($entity = 'customerorder', $filter = []): array
    {
        $optionPrefix = 'prop_bx_';
        switch ($entity) {
            case 'customerorder':
                $optionPrefix = 'prop_bx_';
            break;
            case 'counterpartyfields':
                $optionPrefix = 'counter_fields_';
            break;
            case 'counterpartyprops':
                $optionPrefix = 'counter_prop_';
            break;
        }

        $orderPropsBx = \Bitrix\Sale\Internals\OrderPropsTable::getList(['filter' => $filter]);
        $result = [];
        while ($obOrderPropBx = $orderPropsBx->fetch()) {
            $propId = self::getOption($optionPrefix . $obOrderPropBx['ID']);
            if ($entity === 'customerorder' && $obOrderPropBx['IS_LOCATION'] === 'Y') {
                $propMsCityId = self::getOption('loc_CITY_prop_bx_' . $obOrderPropBx['ID'], '');
                if($propMsCityId === 'ADDR_city') {
                    $result[$obOrderPropBx['ID']] = $propMsCityId;
                }
                unset($propMsCityId);
            }
            if (empty($propId) || $propId === 'N') {
                continue;
            }
            $result[$obOrderPropBx['ID']] = $propId;
        }
        return $result;
    }

    public static function getOrderFieldIds(): array
    {
        $result = [];

        $optionPrefix = 'field_bx_';
       
        $fields = array_keys(self::geOrdertFieldsBx());
        foreach ($fields as $field) {
            $propId = self::getOption($optionPrefix . $field);
            if (empty($propId) || $propId === 'N') {
                continue;
            }
            $result[$field] = $propId;
        }
        return $result;
    }

    public static function geOrdertFieldsBx()
    {
        return [
            'PUBLICK_LINK' => LangMsg::get('CONFIG_PUBLICK_LINK'),
            'COUPON_LIST' => LangMsg::get('CONFIG_COUPON_LIST'),
            'LID' => LangMsg::get('CONFIG_ORDER_LID')
        ];
    }

    public static function getExternalCancelStatusId()
    {
        return self::getOption("order_cancel") ? : false;
    }

    public static function getSearchCounterpartyProps()
    {
        return self::getOption('counter_search_fields') ? explode(',', self::getOption('counter_search_fields')) : ['externalCode'];
    }

    public static function getSearchCounterpartyPropsDivide($ptype = 1)
    {
        return self::getOption("counter_search_fields_ptype_{$ptype}") ? explode(',', self::getOption("counter_search_fields_ptype_{$ptype}")) : ['externalCode'];
    }

    public static function getSearchCounterpartyPropsSorted()
    {
        $result = [];
        foreach (self::getSearchCounterpartyProps() as $propId) {
            $result[self::getOption('counter_search_field_' . $propId)] = $propId;
        }
        ksort($result);
        return $result;
    }

    public static function getSearchFieldId(string $typeOfDoc = ''): string
    {
        $result = 'XML_ID';
        switch($typeOfDoc) {
            case 'payment':
                if (Config::checkFeature('payment_customextfield')) {
                    $result = Config::getOption('pays_ext_ms_field');
                }
                break;
        }

        return $result;
    }

    public static function getSearchCounterpartyPropsSortedDivide($ptype = 1)
    {
        $result = [];
        foreach (self::getSearchCounterpartyPropsDivide($ptype) as $propId) {
            $result[self::getOption('counter_search_field_' . $propId . '_ptype_' . $ptype)] = $propId;
        }
        ksort($result);
        return $result;
    }

    public static function getSearchCounterpartyPropsId($searchField = '', $ptype = 1)
    {
        return self::getOption("counter_search_field_{$searchField}_ptype_{$ptype}_prop", 0);
    }

    public static function getOrderOrganization()
    {
        return self::getOption('order_organization');
    }

    public static function getOrderAcHelperCount()
    {
        return self::getOption('order_organization_acc');
    }

    public static function getOptionArray($optionName = '', $defaultValue = false, $siteId = '')
    {
        $fieldValue = self::getOption($optionName, $defaultValue, $siteId);
        if(is_array($fieldValue) && count($fieldValue) > 0) {
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

    public static function getOption($optionName = '', $defaultValue = false, $siteId = '')
    {
        return \Bitrix\Main\Config\Option::get(self::getModuleId(), $optionName, $defaultValue, $siteId);
    }
    
    public static function setOption($optionName = '', $value = '', $siteId = '')
    {
        return \Bitrix\Main\Config\Option::set(self::getModuleId(), $optionName, $value, $siteId);
    }

    public static function getCounterPartyFields()
    {
        return [
            'N' => LangMsg::get('CONFIG_COUNTERPTY_DONT_SYNC'),
            'name' => LangMsg::get('CONFIG_COUNTERPTY_NAME'),
            'code' => LangMsg::get('CONFIG_COUNTERPTY_CODE'),
            'email' => LangMsg::get('CONFIG_COUNTERPTY_EMAIL'),
            'phone' => LangMsg::get('CONFIG_COUNTERPTY_PHONE'),
            'fax' => LangMsg::get('CONFIG_COUNTERPTY_FAX'),
            //addres FULL
            'addr_postalCode' => LangMsg::get('CONFIG_COUNTERPTY_POSTALCODE'),
            'addr_city' => LangMsg::get('CONFIG_COUNTERPTY_CITY'),
            'addr_street' => LangMsg::get('CONFIG_COUNTERPTY_STREET'),
            'addr_house' => LangMsg::get('CONFIG_COUNTERPTY_HOUSE'),
            'addr_apartment' => LangMsg::get('CONFIG_COUNTERPTY_APART'),
            'addr_addInfo' => LangMsg::get('CONFIG_COUNTERPTY_ADDINFO'),
            'addr_comment' =>  LangMsg::get('CONFIG_COUNTERPTY_ADDRCOMMENT'),

            'legalTitle' => LangMsg::get('CONFIG_COUNTERPTY_LEGALTITLE'),
            'legalAddress' => LangMsg::get('CONFIG_COUNTERPTY_LEGALADDRESS'),
            'inn' => LangMsg::get('CONFIG_COUNTERPTY_INN'),
            
            'okpo' => LangMsg::get('CONFIG_COUNTERPTY_OKPO'),
            'ogrn' => LangMsg::get('CONFIG_COUNTERPTY_OGRN'),
            'ogrnip' => LangMsg::get('CONFIG_COUNTERPTY_OGRNIP'),
            'kpp' => LangMsg::get('CONFIG_COUNTERPTY_KPP'),
        ];
    }

    public static function getCounterType($pType = 1)
    {
        return self::getOption('counter_type_' . $pType) ? : 'individual';
    }

    public static function getCounterDefaultState($pType = 1)
    {
        $state = self::getOption('counter_state_' . $pType);
        return (!empty($state) && $state !== 'N') ? $state : '';
    }

    public static function getPersonalType($counterType = '')
    {
        $orderPersonalTypesBx = \Bitrix\Sale\Internals\PersonTypeTable::getList([
            'filter' => [
                'ENTITY_REGISTRY_TYPE' => 'ORDER',
                'LID' => Config::getDefaultImportSiteId()
            ]
        ]);
        
        $arPersonalTypesBx = [];
        while ($obOrderTypeBx = $orderPersonalTypesBx->fetch()) {
            $arPersonalTypesBx[] = $obOrderTypeBx['ID'];
        }

        foreach ($arPersonalTypesBx as $pType) {
            if (self::getCounterType($pType) == $counterType) {
                return $pType;
            }
        }

        return false;
    }

    public static function getStoreDefault()
    {
        return self::getOption('order_store') ? : '';
    }

    public static function getCounterTags($pType = 1)
    {
        $tagText = self::getOption('counter_tags_' . $pType);
        $result = [];
        if (!empty($tagText)) {
            $tagList = explode("\n", $tagText);
            if (Utils::is_count($tagList)) {
                foreach ($tagList as $tag) {
                    if (!empty(trim($tag))) {
                        $result[] = trim($tag);
                    }
                }
            }
        }

        return $result;
    }

    public static function getCounterPhoneSearchType()
    {
        return self::getOption('counter_search_phone')?:'filter';
    }

    public static function getCounterPartyTypes()
    {
        return [
            'legal' => LangMsg::get('CONFIG_COUNTERPTY_TYPE_LEGAL'),
            'entrepreneur' => LangMsg::get('CONFIG_COUNTERPTY_TYPE_ENTREPRENEUR'),
            'individual' => LangMsg::get('CONFIG_COUNTERPTY_TYPE_INDIVIDUAL')
        ];
    }

    public static function getCounterPartyFieldsSearch()
    {
        return [
            'externalCode' => LangMsg::get('CONFIG_COUNTERPTY_EXTERNAL_CODE'),
            'email' => LangMsg::get('CONFIG_COUNTERPTY_EMAIL'),
            'phone' => LangMsg::get('CONFIG_COUNTERPTY_PHONE'),
            'inn' => LangMsg::get('CONFIG_COUNTERPTY_INN'),
        ];
    }

    public static function getSearchTypes()
    {
        return [
            'filter' => LangMsg::get('CONFIG_COUNTERPTY_FILTER_SEARCH'),
            'search' => LangMsg::get('CONFIG_COUNTERPTY_SEARCH_SEARCH'),
        ];
    }

    public static function isOnlyPropsSearchPtype($ptype = 1): bool
    {
        return self::getOption("search_only_props_{$ptype}") === 'Y';
    }

    public static function isSkipExtCodePtype($ptype = 1): bool
    {
        return self::getOption("skip_ex_code_{$ptype}") === 'Y';
    }

    public static function isSkipCodePtype($ptype = 1): bool
    {
        return self::getOption("skip_code_{$ptype}") === 'Y';
    }

    public static function getAssociatedUserField($msField = '')
    {
        switch ($msField) {
            case 'externalCode':
                $r = 'XML_ID';
            break;
            case 'name':
                $r = 'NAME';
            break;
            case 'code':
                $r = 'LOGIN';
            break;
            case 'email':
                $r = 'EMAIL';
            break;
            case 'phone':
                $r = ['PHONE_NUMBER', 'PERSONAL_PHONE'];
            break;
            case 'inn':
                $r = 'PROPERTY_ORDER';
            break;
            default:
                $r = 'XML_ID';
        }

        return $r;
    }

    public static function getDateTime($dateString = ''): \DateTime
    {
        try {
            $timeZone = date_default_timezone_get();
            if (self::checkFeature('is_eu_msk_timezone')) {
                $timeZone = 'Europe/Moscow';
            }
            return new \DateTime($dateString, new \DateTimeZone($timeZone));
        } catch (\Exception $e) {
            return new \DateTime();
        }
    } 

    public static function getLastDateUpdate($key = '')
    {
        $lastDateUpdate = self::getOption('lastdate_' . $key, '');
        if (empty($lastDateUpdate)) {
            $refreshMinutes = (int)self::getOption('last_date_refresh_minutes', 30);
            $refreshMinutes = $refreshMinutes <= 0 ? 30 : $refreshMinutes;
            $lastDateUpdate = (self::getDateTime())->modify("-{$refreshMinutes} minutes")->format('Y-m-d H:i:s');
            self::setLastDateUpdate($key, $lastDateUpdate);
        }
        return $lastDateUpdate;
    }

    public static function setLastDateUpdate($key = '', $date = '')
    {
        if (empty($date)) {
            $date = (self::getDateTime())->format('Y-m-d H:i:s');
        }
        self::setOption('lastdate_' . $key, $date);
    }

    /** @deprecated */
    public static function getWebHookLimitCountInterval(): int
    {
        return (int)200;
    }

    /** @deprecated */
    public static function getStatusExport()
    {
        return self::getOption('status_export');
    }

    /** @deprecated */
    public static function getApiEndPointNew()
    {
        return ApiNew::getApiEndPointUrl();
    }

    /** @deprecated */
    public static function getAgentAttempts()
    {
        $value = self::getOption('agent_attempts');
        if ((int)$value <= 0 || (int)$value >= 5) {
            $value = 5;
        }
        return (int)$value;
    }
    
    /** @deprecated */
    public static function getPaymentProjHref($paymentType = 'paymentin')
    {
        $projId = self::getOption('pays_proj_' . $paymentType);
        if (!empty($projId) && $projId !== 'N') {
            return self::getBaseHrefLinkNew('project') . $projId;
        }
        return false;
    }

    /** @deprecated */
    public static function isApiExchange()
    {
        return true;
    }

    /** @deprecated */
    public static function getLogFileSize()
    {
        return 512;
    }

    /** @deprecated */
    public static function getApiEndPoint()
    {
        return 'https://online.moysklad.ru/api/remap/1.1';
    }

    /** @deprecated */
    public static function getBaseHrefLink($entity = '')
    {
        $hrefBaseLinks = [
            'state' => 'https://online.moysklad.ru/api/remap/1.1/entity/customerorder/metadata/states/',
            'paymentin_state' => 'https://online.moysklad.ru/api/remap/1.1/entity/paymentin/metadata/states/',
            'cashin_state' => 'https://online.moysklad.ru/api/remap/1.1/entity/cashin/metadata/states/',
            'project' => 'https://online.moysklad.ru/api/remap/1.1/entity/project/',
            'employee' => 'https://online.moysklad.ru/api/remap/1.1/entity/employee/',
            'productfolder' => 'https://online.moysklad.ru/api/remap/1.1/entity/productfolder/',
            'customEntity' => 'https://online.moysklad.ru/api/remap/1.1/entity/customentity/'
        ];

        return isset($hrefBaseLinks[$entity]) ? $hrefBaseLinks[$entity] : '';
    }

    /** @deprecated */
    public static function getMetaDataState($href = '', $entity = '')
    {
        return (object)[
            'meta' => (object)[
                'href' => $href,
                'metadataHref' => 'https://online.moysklad.ru/api/remap/1.1/entity/' . $entity . '/metadata',
                'type' => 'state',
                'mediaType' => 'application/json'
            ]
        ];
    }

}
