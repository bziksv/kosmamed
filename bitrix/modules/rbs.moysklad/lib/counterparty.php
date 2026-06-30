<?php
namespace Rbs\Moysklad;

use \Bitrix\Main\UserTable;
use \Rbs\Moysklad\LangMsg; 
use \Rbs\Moysklad\Controller\ExceptionRuler;
use \Rbs\Moysklad\Debug\Message as LogMsg;

\Bitrix\Main\Loader::includeModule('sale');

class Counterparty
{
    private $log = [];

    private $agent = null;
    private $order = null;

    private $strAttrs = [];
    private $userFields = null;
    private $profileProps = [];

    private $isLoadedAgent = false;

    private $hasErrors = false;

    private $hasSearchErrors = false;

    public static function createForOrder(\Bitrix\Sale\Order &$order = null): self
    {
        if($order instanceof \Bitrix\Sale\Order) {
            return new self($order->getUserId(), $order->getPersonTypeId(), $order->getPropertyCollection(), $order->getId());
        } else {
            return new self(0, 0, null, 0);
        }
    }

    public function __construct($userId = null, $pType = 1, $propertyCollection = null, $orderId = 0)
    {
        /**custom for despi.mscounterparty users | now deprecated variant */
        if (\Bitrix\Main\Loader::includeModule('despi.mscounterparty') && $orderId > 0 && Config::getProfileId() == 0) {
            if (\Despi\Mscounterparty\Config::checkFeature('global_enabled')) {
                $counterDespi = \CDespiMscounterparty::exportCounterPartyFromOrderBx($orderId);
                if ($counterDespi->isLoadedCounterParty()) {
                    $this->isLoadedAgent = true;
                    $this->agent = $counterDespi->getCounterParty();
                    return;
                }
            }
        }

        if (\Bitrix\Main\Loader::includeModule('despi.moyskladusers') && Config::checkFeature('counter_dmu_enable')) {
            $this->addInfoMessage(LangMsg::get('COUNTER_SEARCH_CP_WITH_DESPI_MSUSERS'));
            if (\Despi\MoyskladUsers\Internals\Feature::check('global_enable') && \Despi\MoyskladUsers\Internals\Feature::check('ue_enable')) {
                try {
                    if (Config::checkFeature('counter_dmu_search')) {
                        $userBx = new \Despi\MoyskladUsers\Controller\Export\UserBx($userId);
                        $userBx->searchCounterParty();
                        if ($userBx->hasCounterParty()) {
                            $this->isLoadedAgent = true;
                            $this->agent = $userBx->getCounterParty();
                            $this->addInfoMessage(LangMsg::get('COUNTER_SEARCH_CP_WITH_DESPI_MSUSERS_SUCCESS'));
                            return;
                        } elseif (Config::checkFeature('counter_dmu_add') && \Despi\MoyskladUsers\Internals\Feature::check('ue_add')) {
                            $userBx->create();
                            if ($userBx->hasCounterParty()) {
                                $this->isLoadedAgent = true;
                                $this->agent = $userBx->getCounterParty();
                                $this->addInfoMessage(LangMsg::get('COUNTER_SEARCH_CP_WITH_DESPI_MSUSERS_CREATE_SUCCESS'));
                                return;
                            } else {
                                $this->addWarningMessage(LangMsg::get('COUNTER_DESPI_MSUSERS_CRAETE_FAIL'));
                                foreach ($userBx->getLogger()->getMessageArray() as $message) {
                                    $this->addErrorMessage($message->getText());
                                }
                            }
                        } else {
                            $this->addWarningMessage(LangMsg::get('COUNTER_DESPI_MSUSERS_CRAETE_OFF'));
                        }

                        foreach ($userBx->getLogger()->getErrorMessageArray() as $message) {
                            $this->addErrorMessage($message->getText());
                        }

                    } else {
                        $this->addInfoMessage(LangMsg::get('COUNTER_SEARCH_CP_WITH_DESPI_MSUSERS_OFF'));
                    }
                } catch (\Throwable $e) {
                    $this->addWarningMessage(LangMsg::get('COUNTER_ERROR_DESPI_MSUSERS_SEARCH', ['#ERROR#' => $e->getMessage()]));
                }
            } else {
                $this->addWarningMessage(LangMsg::get('COUNTER_DESPI_MSUSERS_OFF'));
            }
        }

        if ($userId <= 0 || $orderId <= 0) {
            $this->addErrorMessage(LangMsg::get('EMPTY_USER_ID'));
            return false;
        }

        if($order = \Bitrix\Sale\Order::load($orderId)) {
            $this->setOrder($order);
        }

        $rsUser = \Bitrix\Main\UserTable::getList([
            'select' => ['*'],
            'filter' => ['=ID' => $userId]
        ]);

        $counterType = Config::getCounterType($pType);
        $phone = '';

        if ($obUser = $rsUser->fetch()) {

            $phoneAuth = \Bitrix\Main\UserPhoneAuthTable::getList([
                'select' => ['PHONE_NUMBER'],
                'filter' => ['=USER_ID' => $userId]
            ])->fetch();

            if (!empty($phoneAuth['PHONE_NUMBER'])) {
                $obUser['PHONE_NUMBER'] = $phoneAuth['PHONE_NUMBER'];
                $phone = $phoneAuth['PHONE_NUMBER'];
            }

            if (empty($obUser['PHONE_NUMBER']) && empty($obUser['PERSONAL_PHONE'])) {
                if ($phonePropValue = $propertyCollection->getPhone()) {
                    if ($phonePropValue->getValue()) {
                        $obUser['PHONE_NUMBER'] = $phone = $phonePropValue->getValue();
                    }
                }
            }

            $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnCustomCounterPartySearch", array(
                'orderId' => $orderId
            ));

            $event->send();

            if ($event->getResults()) {
                foreach ($event->getResults() as $eventResult) {
                    if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                        $counterParty = $eventResult->getParameters();
                        if(is_object($counterParty)){
                            $this->isLoadedAgent = true;
                            $this->agent = $counterParty;
                            return;
                        }
                    }
                }
            }

            $this->userFields = $obUser;

            if (Config::checkFeature('counterpersonaldivide')) {

                $propSearchFields = Config::getSearchCounterpartyPropsSortedDivide($pType);
                $isOnlySearchProps = Config::isOnlyPropsSearchPtype($pType);
                $isCompanyTypeIgnore = Config::getOption("search_without_ctype_{$pType}", 'N') === 'Y';

                $profilePropsValues = [];
                if($isOnlySearchProps) {
                    
                    $filterProfile['USER_ID'] = $this->userFields['ID'];
                    foreach(['personal_type', 'all'] as $searchType) {
                        if($searchType === 'personal_type') {
                            $filterProfile['PERSON_TYPE_ID'] = $pType;
                        } else if (isset($filterProfile['PERSON_TYPE_ID'])) {
                            unset($filterProfile['PERSON_TYPE_ID']);
                        }
                        $dbProfile = \CSaleOrderUserProps::GetList(['ID' => 'DESC'], $filterProfile);
                        if ($profile = $dbProfile->fetch()) {
                            $rsProfileProps = \CSaleOrderUserPropsValue::GetList([], ['USER_PROPS_ID' => $profile['ID']]);
                            while ($obProp = $rsProfileProps->GetNext()) {
                                $profilePropsValues[$obProp['PROP_ID']] = $obProp;
                            }
                            break;
                        }
                    }

                    $this->profileProps = $profilePropsValues;
                }
                
                unset($profilePropsValues);
                
                foreach ($propSearchFields as $fieldPropMs) {

                    $fieldPropBx = Config::getAssociatedUserField($fieldPropMs);
                    if ($isOnlySearchProps || $fieldPropBx === 'PROPERTY_ORDER') {

                        $propId = Config::getSearchCounterpartyPropsId($fieldPropMs, $pType);
                        $fieldPropBx = 'PROPERTY_ORDER_' . $propId;
                        if ((int)$propId > 0) {
                            $needCheckProfileProp = true;
                            if ($somePropValue = $propertyCollection->getItemByOrderPropertyId($propId)) {
                                if ($propValue = $somePropValue->getValue()) {
                                    if (!empty($propValue)) {
                                        $this->userFields[$fieldPropBx] = $propValue;
                                        $needCheckProfileProp = false;
                                    }
                                }
                            }
                            if($needCheckProfileProp) {
                                if (isset($this->profileProps[$propId]) && !empty($this->profileProps[$propId]['VALUE'])) {
                                    $this->userFields[$fieldPropBx] = $this->profileProps[$propId]['VALUE'];
                                }
                            }
                        }

                    } elseif (is_array($fieldPropBx)) {

                        $propActive = '';
                        if(Utils::is_count($fieldPropBx)){
                            foreach ($fieldPropBx as $propBx) {
                                if (isset($this->userFields[$propBx]) && !empty($this->userFields[$propBx])) {
                                    $propActive = $propBx;
                                    break;
                                }
                            }
                        }
                        if (!empty($propActive)) {
                            $fieldPropBx = $propActive;
                        }
                        unset($propActive);

                    }

                    if(gettype($fieldPropBx) !== 'string') {
                        continue;
                    }
    
                    if (!isset($this->userFields[$fieldPropBx]) || empty($this->userFields[$fieldPropBx])) {
                        continue;
                    }

                    if ($fieldPropMs === 'phone' && Config::getCounterPhoneSearchType() === 'search') {
                        $queryForSearch = [
                            'search' => $this->userFields[$fieldPropBx]
                        ];
                        if(!$isCompanyTypeIgnore) {
                            $queryForSearch['filter'] = 'companyType=' . $counterType;
                        }
                    } else {
                        $queryForSearch = [
                            'filter' => 'companyType=' . $counterType . ';' . $fieldPropMs . '=' . $this->userFields[$fieldPropBx]
                        ];
                        if ($isCompanyTypeIgnore) {
                            $queryForSearch = [
                                'filter' => $fieldPropMs . '=' . $this->userFields[$fieldPropBx]
                            ];
                        }
                    }

                    $counterParty = ApiNew::get('/entity/counterparty', $queryForSearch);

                    //search other legal type if empty
                    if (
                        !$isCompanyTypeIgnore &&
                        Config::isSearchLegal($counterType) &&
                        Utils::is_success($counterParty) && 
                        !Utils::array_exists($counterParty)
                    ) {
                        $counterTypeReverse = Config::reverseLegalType($counterType);
                        if ($fieldPropMs === 'phone' && Config::getCounterPhoneSearchType() === 'search') {
                            $counterParty = ApiNew::get('/entity/counterparty', ['search' => $this->userFields[$fieldPropBx], 'filter' => 'companyType=' . $counterTypeReverse]);
                        } else {
                            $counterParty = ApiNew::get('/entity/counterparty', ['filter' => 'companyType=' . $counterTypeReverse . ';' . $fieldPropMs . '=' . $this->userFields[$fieldPropBx]]);
                        }
                    }
                    
                    if (Utils::is_success($counterParty)) {
                        if (Utils::array_exists($counterParty)) {
                            foreach ($counterParty->rows as $counterRow) {
                                if (!$counterRow->archived) {
                                    $this->isLoadedAgent = true;
                                    $this->agent = $counterRow;
                                    break;
                                }
                            }
                        } else {
                            continue;
                        }
                    } else if (Utils::has_errors($counterParty)) {
                       
                        $this->hasSearchErrors = true;
                        $this->addErrorMessageArray($counterParty->errors);
                        
                        ExceptionRuler::checkApiResponseErrors($counterParty, [
                            'id' => 'counterparty',
                            'action' => 'search'
                        ]);

                    }
    
                    if ($this->isLoadedAgent) {
                        break;
                    }

                }

            } else {

                $propSearchFields = Config::getSearchCounterpartyPropsSorted();
                foreach ($propSearchFields as $fieldPropMs) {

                    $fieldPropBx = Config::getAssociatedUserField($fieldPropMs);
                    if (Utils::is_count($fieldPropBx)) {
                        $propActive = '';
                        foreach ($fieldPropBx as $propBx) {
                            if (isset($this->userFields[$propBx]) && !empty($this->userFields[$propBx])) {
                                $propActive = $propBx;
                                break;
                            }
                        }
                        if (!empty($propActive)) {
                            $fieldPropBx = $propActive;
                        } else {
                            $fieldPropBx = '';
                        }
                        unset($propActive);
                    }

                    if (empty($fieldPropBx) || !isset($this->userFields[$fieldPropBx]) || empty($this->userFields[$fieldPropBx])) {
                        continue;
                    }
    
                    if ($fieldPropMs === 'phone' && Config::getCounterPhoneSearchType() === 'search') {
                        $counterParty = ApiNew::get('/entity/counterparty', ['search' => $this->userFields[$fieldPropBx]]);
                    } else {
                        $counterParty = ApiNew::get('/entity/counterparty', ['filter' => $fieldPropMs . '=' . $this->userFields[$fieldPropBx]]);
                    }
                    
                    if (Utils::is_success($counterParty)) {

                        if (Utils::array_exists($counterParty)) {
                            foreach ($counterParty->rows as $counterRow) {
                                if (!$counterRow->archived) {
                                    $this->isLoadedAgent = true;
                                    $this->agent = $counterRow;
                                    break;
                                }
                            }
                        } else {
                            continue;
                        }

                    } else if (Utils::has_errors($counterParty)) {

                        $this->hasSearchErrors = true;
                        $this->addErrorMessageArray($counterParty->errors);

                        ExceptionRuler::checkApiResponseErrors($counterParty, [
                            'id' => 'counterparty',
                            'action' => 'search'
                        ]);

                    }
    
                    if ($this->isLoadedAgent) {
                        break;
                    }

                }
            }

            if($this->hasSearchErrors) {
                return;
            }

            if (!$this->isLoadedAgent) {

                $name = [];
                if (!empty($this->userFields['LAST_NAME'])) {
                    $name[] = $this->userFields['LAST_NAME'];
                }
                if (!empty($this->userFields['NAME'])) {
                    $name[] = $this->userFields['NAME'];
                }
                if (!empty($this->userFields['SECOND_NAME'])) {
                    $name[] = $this->userFields['SECOND_NAME'];
                }
                $name = implode(' ', $name);

                if (empty($name)) {
                    if ($namePropValue  = $propertyCollection->getPayerName()) {
                        if ($namePropValue->getValue()) {
                            $name = $namePropValue->getValue();
                        }
                    }
                }

                $postFields = [];
                if (!empty($this->userFields['XML_ID']) && !Config::isSkipExtCodePtype($pType)) {
                    $postFields['externalCode'] = (string)$this->userFields['XML_ID'];
                }
                
                if (empty($phone)) {
                    $postFields['phone'] = $this->userFields['PERSONAL_PHONE'] ?? '';
                } else {
                    $postFields['phone'] = $phone ?? '';
                }

                if (!empty($this->userFields['EMAIL'])) {
                    $postFields['email'] = $this->userFields['EMAIL'];
                }
                
                if(!Config::isSkipCodePtype($pType)) {
                    $postFields['code'] = uniqid('code_');
                }
                
                
                if (!empty($counterType)) {
                    $postFields['companyType'] = $counterType;
                }
                if (!empty($name)) {
                    $postFields['name'] = $name;
                } else {
                    $postFields['name'] = $this->userFields['ID'];
                }

                $tags = Config::getCounterTags($pType);
                if (Utils::is_count($tags)) {
                    $postFields['tags'] = $tags;
                }

                $defaultState = Config::getCounterDefaultState($pType);
                if (!empty($defaultState)) {
                    $stateHref = Config::getBaseHrefLinkNew('counterparty_state') . $defaultState;
                    $postFields['state'] = (object)['meta' => ['href' => $stateHref, 'type' => 'state', 'mediaType' => 'application/json']];
                }

                if ($propertyCollection) {
                    $changedProps = $this->getChangedPropsFromProps($propertyCollection);
                    foreach ($changedProps as $key => $val) {
                        $postFields[$key] = $val;
                    }
                }

                if (!empty($postFields['phone']) && Config::checkFeature('counter_format_phone')) {
                    $formatPhoneType = Config::getOption('counter_format_phone_type', 'WITH_PLUS');
                    $postFields['phone'] = Helper::parsePhone($postFields['phone'], $formatPhoneType === 'WITH_PLUS');
                }

                if($pType > 0) {
                    $nameFieldDefaultPropId = Config::getOption('counter_name_field_' . $pType, 'N');
                    if (!empty($nameFieldDefaultPropId) && $nameFieldDefaultPropId !== 'N' && (int)$nameFieldDefaultPropId > 0) {
                        $defaultName = \CRbsMoyskladHelper::getBxPropValue($this->order, $nameFieldDefaultPropId);
                        if (!empty($defaultName)) {
                            $postFields['name'] = $defaultName;
                        }
                    }
                }

                Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeCreateCounterParty', [
                    'orderId' => $orderId,
                    'counterPartyCreateArray' => $postFields
                ], $postFields);
                
                $postResponse = ApiNew::post('/entity/counterparty', $postFields);
                if (Utils::is_success($postResponse) && Utils::property_exists($postResponse, ['id'])) {

                    $this->isLoadedAgent = true;
                    $this->agent = $postResponse;

                    if (empty($this->userFields['XML_ID'])) {
                        $this->userFields['XML_ID'] = $this->agent->externalCode;
                        $this->updateXmlId();
                    }

                } else if(Utils::has_errors($postResponse)) {

                    $this->addErrorMessageArray($postResponse->errors);

                    ExceptionRuler::checkApiResponseErrors($postResponse, [
                        'id' => 'counterparty',
                        'action' => 'create'
                    ]);

                }

            } else {

                if (Config::checkFeature('counterextwrite') && !Config::isSkipExtCodePtype($pType)) {
                    if (empty($this->userFields['XML_ID'])) {
                        $this->userFields['XML_ID'] = md5(serialize($this->userFields));
                        $this->updateXmlId();
                    }

                    if ($this->userFields['XML_ID'] !== $this->agent->externalCode) {
                        ApiNew::put($this->agent->meta->href, ['externalCode' => (string)$this->userFields['XML_ID']]);
                    }
                }

            }

        } else {
            $this->addErrorMessage(LangMsg::get('ERROR_FETCH_USER', ['#USER#' => $userId]));
        }
    }

    public static function getDefaultCounterParty($pType = 0): self
    {
        $obj = new self;
        $obj->clearLog();
        
        if($pType > 0) {
            $extCode = Config::getOption('counter_ext_code_' . $pType, 'DEFAULT_RBS_MOYSKLAD');
        } else {
            $extCode = Config::getOption('counter_ext_code_default', 'DEFAULT_RBS_MOYSKLAD');
        }

        if(empty($extCode)) {
            $extCode = 'DEFAULT_RBS_MOYSKLAD';
        }

        $counterPartyMs = ApiNew::get('/entity/counterparty', ['filter' => 'externalCode=' . $extCode, 'liimt' => 1], 86400);
        if (Utils::is_success($counterPartyMs)) {
            if (Utils::array_exists($counterPartyMs)) {
                $obj->setLoadedAgent(true);
                $obj->setCounterParty($counterPartyMs->rows[0]);
            } else {
                $obj->addWarningMessage(LangMsg::get('CANT_FIND_DEFAULT_USER'));
                $counterPartyMs = ApiNew::post('/entity/counterparty', [
                    'name' => LangMsg::get('CREATE_DEFAULT_CP_NAME'),
                    'externalCode' => 'DEFAULT_RBS_MOYSKLAD',
                    'code' => 'DEFAULT_RBS_MOYSKLAD',
                ]);
                if (Utils::is_success($counterPartyMs) && Utils::property_exists($counterPartyMs, ['id'])) {
                    $obj->setLoadedAgent(true);
                    $obj->setCounterParty($counterPartyMs);
                } else if (Utils::has_errors($counterPartyMs)) {
                    $obj->addErrorMessageArray($counterPartyMs->errors);
                }
            }
        } else if (Utils::has_errors($counterPartyMs)) {
            $obj->addErrorMessageArray($counterPartyMs->errors);
            ExceptionRuler::checkApiResponseErrors($counterPartyMs, [
                'id' => 'counterparty',
                'action' => 'search'
            ]);
        }
        
        return $obj;
    }

    public function getAgentName()
    {
        return $this->agent->name;
    }

    public function updateXmlId()
    {
        if ($this->userFields['ID'] > 0 && !empty($this->userFields['XML_ID'])) {
            $user = new \CUser;
            $user->Update($this->userFields['ID'], ['XML_ID' => $this->userFields['XML_ID']]);
        }
    }

    public static function getUserBxFromAgentHref($agent = null): array
    {
        $result = [
            'USER_ID' => false,
            'PROFILE_ID' => false,
            'IS_NEW_PROFILE' => false,
            'PERSON_TYPE' => 1,
            'AGENT' => $agent
        ];

        if(Utils::property_exists($agent, ['id'])) {

            $pType = Config::getPersonalType($agent->companyType);
            if ($agent->companyType === 'entrepreneur' && Config::isSearchLegal($agent->companyType)) {
                $pType = Config::getPersonalType(Config::reverseLegalType($agent->companyType));
            }

            if (
                Config::getProfileId() === 0 && 
                (
                    \Bitrix\Main\Loader::includeModule('despi.moyskladusers') ||
                    \Bitrix\Main\Loader::includeModule('despi.mscounterparty')
                )
            ) {
                if (\Bitrix\Main\Loader::includeModule('despi.moyskladusers')) {
                    if(Config::checkFeature('counter_dmu_import')) {
                        $integration = new \Rbs\Moysklad\Integrations\DespiMoyskladUsers($result);
                        $result = $integration->buildImportResult($agent);
                    }                    
                } else if (\Bitrix\Main\Loader::includeModule('despi.mscounterparty')) {
                    $integration = new \Rbs\Moysklad\Integrations\DespiMsCounterParty($result);
                    $result = $integration->buildImportResult($agent);                    
                }
                if ((int)$result['USER_ID'] > 0 && (int)$result['PROFILE_ID'] <= 0) {
                    $result['PROFILE_ID'] = self::getProfileId($result['USER_ID'], $pType, $agent->name);
                }
                if ((int)$result['USER_ID'] > 0 && (int)$result['PROFILE_ID'] <= 0) {
                    $result['PROFILE_ID'] = self::createProfileId($agent->name, $result['USER_ID'], $pType);
                    $result['IS_NEW_PROFILE'] = true;
                }
                if ((int)$result['USER_ID'] > 0 && (int)$result['PROFILE_ID'] > 0) {
                    $result['CONTACT_ID'] = self::createContact($agent);
                    return $result;
                }               
            }

            $rsUser['ID'] = 0;

            $agent->phone = Helper::parsePhone($agent->phone, false);
            $phoneParser = \Bitrix\Main\PhoneNumber\Parser::getInstance()->parse($agent->phone);
            if (!$phoneParser->isValid()) {
                $agent->phone = '';
            }

            if(\COption::GetOptionString("main", "new_user_phone_required", "N") === 'Y' && !empty($agent->phone)) {
                $phoneAuth = \Bitrix\Main\UserPhoneAuthTable::getList([
                    'select' => ['*'],
                    'filter' => ['%PHONE_NUMBER' => Helper::parsePhone($agent->phone, false)]
                ])->fetch();
                if (!empty($phoneAuth['USER_ID'])) {
                    $rsUser = self::getUserBx(['=ID' => $phoneAuth['USER_ID']]);
                }
            }

            if((int)$rsUser['ID'] <= 0 && !empty($agent->externalCode)){
                $rsUser = self::getUserBx(['=XML_ID' => $agent->externalCode]);
            }

            if ((int)$rsUser['ID'] <= 0 && !empty($agent->email)) {
                $rsUser = self::getUserBx(['=EMAIL' => $agent->email]);
            }

            if ((int)$rsUser['ID'] <= 0 && !empty($agent->email)) {
                $rsUser = self::getUserBx(['=LOGIN' => $agent->email]);
            }

            $result['USER_ID'] = (int)$rsUser['ID'] > 0 ? $rsUser['ID'] : false;

            if (Config::checkFeature('counterpersonaldivide')) {
                if ((int)$pType > 0) {
                    $result['PERSON_TYPE'] = $pType;

                    $propSearchFields = Config::getSearchCounterpartyPropsSortedDivide($pType);
                    $isOnlySearchProps = Config::isOnlyPropsSearchPtype($pType);

                    foreach ($propSearchFields as $fieldPropMs) {
                        
                        $valuePropMs = $agent->{$fieldPropMs};
                        if (empty($valuePropMs)) {
                            continue;
                        }

                        $fieldPropBx = Config::getAssociatedUserField($fieldPropMs);
                        if ($isOnlySearchProps || $fieldPropBx === 'PROPERTY_ORDER') {
                            
                            $propId = Config::getSearchCounterpartyPropsId($fieldPropMs, $pType);
                            if ((int)$propId > 0) {
                                $rsProfileProps = \CSaleOrderUserPropsValue::GetList([], ['ORDER_PROPS_ID' => $propId, 'VALUE' => $valuePropMs]);
                                if ($obProfile = $rsProfileProps->GetNext()) {
                                    $userProfile = \CSaleOrderUserProps::GetByID($obProfile['USER_PROPS_ID']);
                                    $result['USER_ID'] = $userProfile['USER_ID'];
                                    $result['PROFILE_ID'] = $obProfile['USER_PROPS_ID'];
                                }
                            }

                        } elseif (Utils::is_count($fieldPropBx)) {

                            foreach ($fieldPropBx as $propBx) {
                                if ($propBx === 'PHONE_NUMBER') {

                                    $phoneAuth = \Bitrix\Main\UserPhoneAuthTable::getList([
                                        'select' => ['*'],
                                        'filter' => ['%' . $propBx => Helper::parsePhone($valuePropMs, false)]
                                    ])->fetch();

                                    if ((int)$phoneAuth['USER_ID'] > 0) {
                                        $result['USER_ID'] = $phoneAuth['USER_ID'];
                                    }

                                    break;
                                }

                                $rsUser = self::getUserBx(["=" . $propBx => $valuePropMs]);

                                if ($rsUser['ID'] > 0) {
                                    $result['USER_ID'] = $rsUser['ID'];
                                }
                            }

                        }

                        if ($result['USER_ID'] > 0) {
                            break;
                        }
                    }
                }
            } else {

                $propSearchFields = Config::getSearchCounterpartyPropsSorted();

                foreach ($propSearchFields as $fieldPropMs) {

                    $valuePropMs = $agent->{$fieldPropMs};
                    if (empty($valuePropMs)) {
                        continue;
                    }

                    $fieldPropBx = Config::getAssociatedUserField($fieldPropMs);

                    if (Utils::is_count($fieldPropBx)) {
                        foreach ($fieldPropBx as $propBx) {
                            if ($propBx === 'PHONE_NUMBER') {

                                $phoneAuth = \Bitrix\Main\UserPhoneAuthTable::getList([
                                    'select' => ['*'],
                                    'filter' => ['%' . $propBx => Helper::parsePhone($valuePropMs, false)]
                                ])->fetch();

                                if ((int)$phoneAuth['USER_ID'] > 0) {
                                    $result['USER_ID'] = $phoneAuth['USER_ID'];
                                    break;
                                }

                                continue;
                            }

                            $rsUser = self::getUserBx(["=" . $propBx => $valuePropMs]);

                            if ($rsUser['ID'] > 0) {
                                $result['USER_ID'] = $rsUser['ID'];
                                break;
                            }
                        }
                    } else {
                        $rsUser = self::getUserBx(["=" . $fieldPropBx => $valuePropMs]);

                        if ($rsUser['ID'] > 0) {
                            $result['USER_ID'] = $rsUser['ID'];
                        }
                    }

                    if ($result['USER_ID'] > 0) {
                        break;
                    }
                }
            }

            if ((int)$result['USER_ID'] > 0 && (int)$result['PROFILE_ID'] <= 0) {
                $result['PROFILE_ID'] = self::getProfileId($result['USER_ID'], $pType, $agent->name);
            }

            if ((int)$result['USER_ID'] <= 0) {

                $user = new \CUser;
                $pass = \Bitrix\Main\Security\Random::getString(10, true);

                $login = $agent->email ? :  $agent->id . "@moyskladtempluser.ru";

                $addUserFields = [
                    'NAME' => $agent->name,
                    'LOGIN' => $login,
                    'EMAIL' => $login,
                    'XML_ID' => $agent->externalCode,
                    'PASSWORD' => $pass,
                    'CONFIRM_PASSWORD' => $pass
                ];

                if(!empty($agent->phone)) {
                    $addUserFields['PHONE_NUMBER'] = Helper::parsePhone($agent->phone);
                    $addUserFields['PERSONAL_PHONE'] = Helper::parsePhone($agent->phone, false);
                }

                $uid = $user->add($addUserFields);
                $defaultUid = Config::getOption('import_user_id', 0);

                if((int)$uid <= 0 && $defaultUid > 0) {
                    $uid = $defaultUid;
                    $result['PROFILE_ID'] = self::getProfileId($uid, $pType, $agent->name);
                    $result['LAST_ERROR'] = trim($user->LAST_ERROR);
                }

                $result['USER_ID'] = (int)$uid;
                if ((int)$result['USER_ID'] > 0 && (int)$result['PROFILE_ID'] <= 0) {
                    $result['PROFILE_ID'] = self::createProfileId($agent->name, $uid, $pType);
                    $result['IS_NEW_PROFILE'] = true;
                }
                
            }

            $result['CONTACT_ID'] = self::createContact($agent);
        }
        
        return $result;
    }

    public static function createContact($agent = null)
    {
        $result['CONTACT_ID'] = 0;

        if (
            \Bitrix\Main\Loader::includeModule('crm') && 
            Config::checkFeature('importcontact') && 
            Utils::is_success($agent) &&
            $agent !== null
        ) {
            if (!empty($agent->phone)) {

                $r = \Bitrix\Crm\FieldMultiTable::getList([
                    'filter' => [
                        'ENTITY_ID' => 'CONTACT',
                        'TYPE_ID' => 'PHONE',
                        'VALUE_TYPE' => 'MOBILE',
                        'VALUE' => $agent->phone
                    ]
                ])->fetch();
                if ($r['ID'] > 0) {
                    $result['CONTACT_ID'] = $r['ELEMENT_ID'];
                }

            }
            if (empty($result['CONTACT_ID']) && !empty($agent->email)) {

                $r = \Bitrix\Crm\FieldMultiTable::getList([
                    'filter' => [
                        'ENTITY_ID' => 'CONTACT',
                        'TYPE_ID' => 'EMAIL',
                        'VALUE_TYPE' => 'WORK',
                        'VALUE' => $agent->email
                    ]
                ])->fetch();
                if ($r['ID'] > 0) {
                    $result['CONTACT_ID'] = $r['ELEMENT_ID'];
                }

            }
            if (empty($result['CONTACT_ID'])) {

                $r = \Bitrix\Crm\ContactTable::add([
                    'NAME' => $agent->name
                ]);
                if ($r->isSuccess()) {
                    $result['CONTACT_ID'] = $r->getId();
                    if (!empty($agent->phone)) {
                        \Bitrix\Crm\FieldMultiTable::add([
                            'ELEMENT_ID' => $result['CONTACT_ID'],
                            'ENTITY_ID' => 'CONTACT',
                            'TYPE_ID' => 'PHONE',
                            'VALUE_TYPE' => 'MOBILE',
                            'VALUE' => $agent->phone
                        ]);
                    }
                    if (!empty($agent->email)) {
                        \Bitrix\Crm\FieldMultiTable::add([
                            'ELEMENT_ID' => $result['CONTACT_ID'],
                            'ENTITY_ID' => 'CONTACT',
                            'TYPE_ID' => 'EMAIL',
                            'VALUE_TYPE' => 'WORK',
                            'VALUE' => $agent->email
                        ]);
                    }
                }

            }
        }

        return $result['CONTACT_ID'];
    }

    private static function getProfileId($uid, $pType, $agentName): int
    {
        $result = 0;

        $db_sales = \CSaleOrderUserProps::GetList([], ['USER_ID' => $uid, 'PERSON_TYPE_ID' => $pType]);
        while ($ar_sales = $db_sales->Fetch()) {
            $result = $ar_sales['ID'];
            if (mb_strtolower($ar_sales['NAME']) === mb_strtolower($agentName)) {
                break;
            }
        }

        return $result;
    }

    private static function createProfileId($agentName, $uid, $pType)
    {
        return \CSaleOrderUserProps::Add([
            "NAME" => $agentName,
            "USER_ID" => $uid,
            "PERSON_TYPE_ID" => $pType
        ]);
    }

    public static function getUserBx($filter = [])
    {
        return UserTable::getList([
            'order' => ['ID' => 'DESC'],
            'select' => ['*'],
            'filter' => $filter
        ])->fetch();
    }

    public static function createFromObjectAndOrder(object $counterParty, \Bitrix\Sale\Order $order): self
    {
        $obj = new self;
        $obj->clearLog();

        if(Utils::property_exists($counterParty, ['id']) && $order->getUserId() > 0) {

            $obj->setCounterParty($counterParty);
            
            $rsUser = UserTable::getList([
                'select' => ['*'],
                'filter' => ['=ID' => $order->getUserId()]
            ]);

            if ($user = $rsUser->fetch()) {

                $obj->setOrder($order);
                $obj->setUserFields($user);
                $obj->setAttributes();

                $obj->setLoadedAgent(true);

            } else {

                $obj->addErrorMessage(LangMsg::get('CANT_FIND_USERBX', ['#XML_ID#' => $counterParty->externalCode]));
            }

        } else {
            $obj->addErrorMessage(LangMsg::get('ERROR_XML_ID_EMPTY', ['#XML_ID#' => $counterParty->id]));
        }

        return $obj;
    }

    public function checkChanges()
    {
        $changedProps = $this->getChangedProps();
        if (Utils::is_count($changedProps)) {
            ApiNew::put($this->agent->meta->href, $changedProps);
        }
    }

    private function setAttributes()
    {
        if ($this->agent->attributes) {
            foreach ($this->agent->attributes as $attr) {
                switch ($attr->type) {
                    case 'string':
                    case 'text':
                        $this->strAttrs[$attr->id] = $attr->value;
                    break;
                }
            }
        }
    }


    private function getChangedPropsFromProps($propertyCollection = null): array
    {
        $changedProps = [];

        if ($propertyCollection) {
            $arCheckTypes = ['fields', 'props'];
            foreach ($arCheckTypes as $type) {

                $propIds = Config::getPropsIds('counterparty' . $type);
                if (!Utils::is_count($propIds)) {
                    continue;
                }

                foreach ($propIds as $propBx => $propMs) {
                    if ($property = $propertyCollection->getItemByOrderPropertyId($propBx)) {
                        $propValueBx = $property->getValue();

                        if ($property->getType() === 'LOCATION' && !empty($propValueBx)) {
                            $locationFullName = [];
                            $locRes = \Bitrix\Sale\Location\LocationTable::getList(array(
                                'filter' => array(
                                    '=CODE' => $propValueBx,
                                    '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                    '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                ),
                                'select' => array(
                                    'NAME_LOCATION' => 'PARENTS.NAME.NAME',
                                ),
                                'order' => array(
                                    'PARENTS.DEPTH_LEVEL' => 'asc'
                                )
                            ));
                            while ($itemLoc = $locRes->fetch()) {
                                $locationFullName[] = $itemLoc['NAME_LOCATION'];
                            }
                            if (!empty($locationFullName)) {
                                $propValueBx = implode(', ', $locationFullName);
                            }
                        }

                        if (empty($propValueBx)) {
                            continue;
                        }

                        if ($type === 'fields') {
                            if (mb_strpos($propMs, 'addr_') !== false) {
                                $addrProp = str_replace('addr_', '', $propMs);
                                $changedProps['actualAddressFull'][$addrProp] = $propValueBx;
                            } else {
                                $changedProps[$propMs] = $propValueBx;
                            }
                        } else {
                            $changedProps['attributes'][$propMs] = [
                                'id' => $propMs,
                                'value' => $propValueBx
                            ];
                        }
                    }
                }

            }
        }

        if (Utils::is_count($changedProps['attributes'])) {
            $changedProps['attributes'] = \CRbsMoyskladHelper::convertAttributesToNewApi($changedProps['attributes'], 'counterparty');
        }
            
        return $changedProps;
    }

    private function getChangedProps(): array
    {
        $changedProps = [];

        if ($propertyCollection = $this->order->getPropertyCollection()) {

            $arCheckTypes = ['fields', 'props'];
            foreach ($arCheckTypes as $type) {

                $propIds = Config::getPropsIds('counterparty' . $type);

                if (!Utils::is_count($propIds)) {
                    continue;
                }

                foreach ($propIds as $propBx => $propMs) {
                    if ($property = $propertyCollection->getItemByOrderPropertyId($propBx)) {
                        $propValueBx = $property->getValue();

                        if ($property->getType() === 'LOCATION' && !empty($propValueBx)) {
                            $locationFullName = [];
                            $locRes = \Bitrix\Sale\Location\LocationTable::getList(array(
                                'filter' => array(
                                    '=CODE' => $propValueBx,
                                    '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                    '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                ),
                                'select' => array(
                                    'NAME_LOCATION' => 'PARENTS.NAME.NAME',
                                ),
                                'order' => array(
                                    'PARENTS.DEPTH_LEVEL' => 'asc'
                                )
                            ));
                            while ($itemLoc = $locRes->fetch()) {
                                $locationFullName[] = $itemLoc['NAME_LOCATION'];
                            }
                            if (!empty($locationFullName)) {
                                $propValueBx = implode(', ', $locationFullName);
                            }
                        }

                        if (empty($propValueBx)) {
                            continue;
                        }

                        if ($type === 'fields') {
                            if (mb_strpos($propMs, 'addr_') !== false) {
                                $addrProp = str_replace('addr_', '', $propMs);
                                $propValueMs = $this->agent->actualAddressFull->{$addrProp};
                                if (
                                    empty($propValueMs) ||
                                    Helper::isDifferentStringFields($propValueBx, $propValueMs)
                                ) {
                                    $changedProps['actualAddressFull'][$addrProp] = $propValueBx;
                                }
                                unset($propValueMs);
                            } else {
                                if (
                                    empty($this->agent->{$propMs}) ||
                                    Helper::isDifferentStringFields($propValueBx, $this->agent->{$propMs})
                                ) {
                                    $changedProps[$propMs] = $propValueBx;
                                }
                            }
                        } else {
                            if (
                                empty($this->strAttrs[$propMs]) ||
                                Helper::isDifferentStringFields($propValueBx, $this->strAttrs[$propMs])
                            ) {
                                $changedProps['attributes'][$propMs] = [
                                    'id' => $propMs,
                                    'value' => $propValueBx
                                ];
                            }
                        }
                    }
                }

            }

            $pType = $this->order->getPersonTypeId();
            if ($pType > 0) {
                $nameFieldDefaultPropId = Config::getOption('counter_name_field_' . $pType, 'N');
                if (!empty($nameFieldDefaultPropId) && $nameFieldDefaultPropId !== 'N' && (int)$nameFieldDefaultPropId > 0) {
                    if(isset($changedProps['name'])) {
                        unset($changedProps['name']);
                    }
                    $defaultName = \CRbsMoyskladHelper::getBxPropValue($this->order, $nameFieldDefaultPropId);
                    if (!empty($defaultName) && $defaultName !== $this->agent->name) {
                        $changedProps['name'] = $defaultName;
                    }
                }
            }
        }

        if (Utils::is_count($changedProps['attributes'])) {
            $changedProps['attributes'] = \CRbsMoyskladHelper::convertAttributesToNewApi($changedProps['attributes'], 'counterparty');
        }

        return $changedProps;
    }

    public function getMeta()
    {
        if ($this->isLoaded() && !empty($this->agent->meta)) {
            return (object)[
                'meta' => $this->agent->meta
            ];
        }

        return false;
    }

    public function isLoaded()
    {
        return $this->isLoadedAgent;
    }

    public function clearLog()
    {
        $this->log = [];
    }

    public function addMainMessage($msg = '')
    {
        $this->log[] = LogMsg::addMain($msg);
    }

    public function addErrorMessage($msg = '', $code = '')
    {
        if (!$this->hasErrors) {
            $this->hasErrors = true;
        }
        $this->log[] = LogMsg::addError($msg, $code);
    }

    public function addErrorMessageArray($errorArrayMsg = [])
    {
        foreach ($errorArrayMsg as $code => $msg) {
            $this->addErrorMessage($msg, $code);
        }
    }

    public function setCounterParty($counterParty = null)
    {
        $this->agent = $counterParty;
    }

    public function setUserFields($userFields = [])
    {
        $this->userFields = $userFields;
    }

    public function setLoadedAgent($isLoaded = false)
    {
        $this->isLoadedAgent = $isLoaded;
    }

    public function setOrder(\Bitrix\Sale\Order $order)
    {
        $this->order = $order;
    }

    public function addWarningMessage($msg = '')
    {
        $this->log[] = LogMsg::addWarning($msg);
    }

    public function addInfoMessage($msg = '')
    {
        $this->log[] = LogMsg::addInfo($msg);
    }

    public function addSuccessMessage($msg = '')
    {
        $this->log[] = LogMsg::addSuccess($msg);
    }

    public function hasErrors()
    {
        return $this->hasErrors;
    }

    /**
     * @return array of \Rbs\Moysklad\Debug\Message
     */
    public function getLogList(): array
    {
        $result = [];

        foreach ($this->log as $log) {
            if ($log instanceof LogMsg) {
                $result[] = $log;
            }
        }

        return $result;
    }

    /** @deprecated */
    public static function createFromHref(string $href = '')
    {

    }

    /** @deprecated */
    public function setOrderInfo(Customerorder $order)
    {
       //deprecated
    }

    /** @deprecated */
    public static function addUserBx($agent)
    {
        $user = new \CUser;
        $pass = randString(10);
        $uid = $user->add([
            'NAME' => $agent->name,
            'LOGIN' => $agent->email ?? $agent->id,
            'EMAIL' => $agent->email ?? $agent->id . "@moyskladtempluser.ru",
            'XML_ID' => $agent->externalCode,
            'PASSWORD' => $pass,
            'CONFIRM_PASSWORD' => $pass
        ]);

        return (int)$uid;
    }
}
