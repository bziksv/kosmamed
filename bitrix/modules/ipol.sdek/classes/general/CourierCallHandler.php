<?php
namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Adapter\CourierCall;
use Ipolh\SDEK\Bitrix\Controller\CourierCall as CourierCallController;
use Ipolh\SDEK\Bitrix\Entity\cache;
use Ipolh\SDEK\Bitrix\Entity\encoder;
use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;
use Ipolh\SDEK\CourierCallsTable;
use Ipolh\SDEK\SDEK\SdekApplication;

use Bitrix\Main\Type\DateTime;

IncludeModuleLangFile(__FILE__);

class CourierCallHandler extends abstractGeneral
{
    /**
     * @var CourierCall
     */
    protected static $courierCall;

    /**
     * @return CourierCall
     */
    public static function getCourierCall()
    {
        return self::$courierCall;
    }

    /**
     * Init da CourierCall adapter
     * @return void
     */
    protected static function initCourierCall()
    {
        $options = new Options();
        self::$courierCall = new CourierCall($options);
    }

    /**
     * Makes new CourierCall using default preset
     * @param int $type
     * @return CourierCall
     */
    public static function loadNewCourierCall($type)
    {
        self::initCourierCall();
        return self::getCourierCall()->newCourierCall($type);
    }

    /**
     * Load existing CourierCall from DB
     * @param int $id
     * @return CourierCall
     */
    public static function loadUploadedCourierCall($id)
    {
        self::initCourierCall();
        return self::getCourierCall()->uploadedCourierCall($id);
    }

    /**
     * Returns CourierCall data if given id exists or new object if not
     * @param int $id
     * @return CourierCall
     */
    public static function getCourierCallData($id)
    {
        if (!empty($id) && CourierCallsTable::getByPrimaryId($id)) {
            return self::loadUploadedCourierCall($id);
        }

        return self::loadNewCourierCall(CourierCall::TYPE_ORDER);
    }

    /**
     * Upload CourierCall using data from AJAX call
     * @return Result
     */
    public static function uploadCourierCall()
    {
        $result = new Result();
        $resultData = ['STATUS' => 'NEW', 'UUID' => false, 'INTAKE_NUMBER' => false];

        if (\sdekHelper::isAdmin('W')) {
            self::initCourierCall();
            $courierCall = self::getCourierCall()->requestCourierCall();

            $saveResult = self::saveCourierCall($courierCall);
            if ($saveResult->isSuccess()) {
                $callId = $saveResult->getData()['ID'];

                $account = \sqlSdekLogs::getById($courierCall->getAccount());
                if (!empty($account)) {
                    $controller = new CourierCallController(self::makeApplication($account['ACCOUNT'], $account['SECURE']));
                    $sendResult = $controller->sendCourierCall($courierCall->toRequestObject(), 1);
                    $data = $sendResult->getData();

                    // \Bitrix\Main\Diag\Debug::WriteToFile(['call'=> $courierCall, 'send' => $sendResult], __FUNCTION__, '___cdek.log');

                    $uuid = (!empty($data['UUID'])) ? $data['UUID'] : false;
                    $intakeNumber = (!empty($data['INTAKE_NUMBER'])) ? $data['INTAKE_NUMBER'] : false;

                    $preparedData = ['STATUS' => 'OK'];
                    switch (true) {
                        case (!$uuid || !$sendResult->isSuccess()):
                            $preparedData['STATUS'] = 'ERROR';
                            break;
                        case ($uuid && !$intakeNumber):
                            $preparedData['STATUS'] = 'WAIT';
                            break;
                    }

                    $preparedData['INTAKE_UUID'] = ($uuid) ? $uuid : null;
                    $preparedData['INTAKE_NUMBER'] = ($intakeNumber) ? $intakeNumber : null;

                    if (!empty($data['STATUSES']) && is_array($data['STATUSES'])) {
                        $preparedData['STATUS_CODE'] = $data['STATUSES'][0]['STATUS'];
                        $preparedData['STATUS_DATE'] = DateTime::createFromTimestamp($data['STATUSES'][0]['DATETIME']);

                        $status = self::getStatusLink($preparedData['STATUS_CODE']);
                        if ($intakeNumber && $status) {
                            $preparedData['STATUS'] = $status;
                        }
                    }

                    if (!empty($data['INFO_LAST_STATE_CODE'])) {
                        $preparedData['STATE_CODE'] = $data['INFO_LAST_STATE_CODE'];
                        $preparedData['STATE_DATE'] = DateTime::createFromTimestamp($data['INFO_LAST_STATE_DATE']);
                    } else if (!empty($data['MAKE_LAST_STATE_CODE'])) {
                        $preparedData['STATE_CODE'] = $data['MAKE_LAST_STATE_CODE'];
                        $preparedData['STATE_DATE'] = DateTime::createFromTimestamp($data['MAKE_LAST_STATE_DATE']);
                    }

                    $preparedData['MESSAGE'] = $sendResult->getErrors()->isEmpty() ? null : serialize($sendResult->getErrors()->getMessages());
                    $preparedData['OK']      = ($intakeNumber) ? 'Y' : 'N';
                    $preparedData['UPTIME']  = DateTime::createFromTimestamp(time());

                    $dbResult = CourierCallsTable::update($callId, $preparedData);
                    if (!$dbResult->isSuccess()) {
                        foreach ($dbResult->getErrors() as $error) {
                            $result->addError(new Error($error->getMessage(), $error->getCode()));
                        }
                    }

                    $resultData = ['STATUS' => $preparedData['STATUS'], 'UUID' => $uuid, 'INTAKE_NUMBER' => $intakeNumber];
                    $result->addWarnings($sendResult->getWarnings());
                    $result->addErrors($sendResult->getErrors());
                } else {
                    $result->addError(new Error(Tools::getMessage('MESS_COURIER_CALL_UNKNOWN_ACCOUNT')));
                }
            } else {
                $result->addErrors($saveResult->getErrors());
            }
        } else {
            $result->addError(new Error(Tools::getMessage('MESS_COURIER_CALL_NO_RIGHTS_UPLOAD')));
        }
        $result->setData($resultData);

        if (Tools::isModuleAjaxRequest()) {
            echo Tools::jsonEncode([
                'success'       => $result->isSuccess(),
                'errors'        => $result->getErrors()->isEmpty() ? '' : $result->getErrorsString(Result::SEPARATOR_NEW_LINE),
                'warnings'      => $result->getWarnings()->isEmpty() ? '' : $result->getWarningsString(Result::SEPARATOR_NEW_LINE),
                'status'        => $result->getData()['STATUS'],
                'intake_uuid'   => $result->getData()['UUID'],
                'intake_number' => $result->getData()['INTAKE_NUMBER'],
            ]);
        }

        return $result;
    }

    /**
     * Save CourierCall data to DB
     * @param CourierCall $courierCall
     * @return Result
     */
    public static function saveCourierCall($courierCall)
    {
        $result = new Result();

        $store = $courierCall->getStore();

        $data = array(
            'TYPE'                    => $courierCall->getType(),
            'CDEK_ORDER_ID'           => $courierCall->getOrderId(),
            // 'CDEK_ORDER_UUID'         => $courierCall->getOrderUuid(),

            'ACCOUNT'                 => $courierCall->getAccount(),

            'STORE_ID'                => $courierCall->getStore()->getId(),

            'INTAKE_DATE'             => DateTime::createFromTimestamp($courierCall->getIntakeDate()),
            'INTAKE_TIME_FROM'        => $store->getCoreAddress()->getField('intakeTimeFrom'),
            'INTAKE_TIME_TO'          => $store->getCoreAddress()->getField('intakeTimeTo'),
            'LUNCH_TIME_FROM'         => $store->getCoreAddress()->getField('lunchTimeFrom'),
            'LUNCH_TIME_TO'           => $store->getCoreAddress()->getField('lunchTimeTo'),

            'PACK_NAME'               => $courierCall->getPack()->getDetails(),
            'PACK_WEIGHT'             => $courierCall->getPack()->getWeight(),
            'PACK_LENGTH'             => $courierCall->getPack()->getLength(),
            'PACK_WIDTH'              => $courierCall->getPack()->getWidth(),
            'PACK_HEIGHT'             => $courierCall->getPack()->getHeight(),

            'SENDER_COMPANY'          => $store->getCoreSender()->getCompany(),
            'SENDER_NAME'             => $store->getCoreSender()->getFullName(),
            'SENDER_PHONE_NUMBER'     => $store->getCoreSender()->getPhone(),
            'SENDER_PHONE_ADDITIONAL' => $store->getCoreSender()->getField('phoneAdditional'),
            'NEED_CALL'               => ($store->getCoreSender()->getField('needCall') ? 'Y' : 'N'),
            'POWER_OF_ATTORNEY'       => ($store->getCoreSender()->getField('powerOfAttorney') ? 'Y' : 'N'),
            'IDENTITY_CARD'           => ($store->getCoreSender()->getField('identityCard') ? 'Y' : 'N'),

            'FROM_LOCATION_CODE'      => $store->getCoreAddress()->getCode(),
            'FROM_LOCATION_ADDRESS'   => $store->getCoreAddress()->getLine(),
            'COMMENT'                 => $store->getCoreAddress()->getComment(),

            'STATUS'                  => 'NEW',
            'UPTIME'                  => DateTime::createFromTimestamp(time()),
        );

        $dbData = CourierCallsTable::getByPrimaryId($courierCall->getId());
        if (!empty($dbData)) {
            $dbResult = CourierCallsTable::update($courierCall->getId(), $data);
        } else {
            $dbResult = CourierCallsTable::add($data);
        }

        if ($dbResult->isSuccess()) {
            // ID of updated or added record
            $result->setData(['ID' => (!empty($dbData) ? $courierCall->getId() : $dbResult->getId())]);
        } else {
            foreach ($dbResult->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), $error->getCode()));
            }
        }

        return $result;
    }

    /**
     * Checks states of CourierCalls
     * @return Result
     */
    public static function getCourierCallStates()
    {
        $result = new Result();
        $resultData = ['CALLS' => []];

        if (CourierCallsTable::getDataCount(false) > 0) {
            $callsLimit = 50;
            $callsUptime = 30;

            $calls = [];
            $dbData = CourierCallsTable::getList([
                'select' => ['ID', 'INTAKE_UUID', 'ACCOUNT' /*, 'OK', 'UPTIME'*/],
                'filter' => [
                    '=OK' => 'Y',
                    '!=INTAKE_UUID' => false,
                    '!=STATUS' => ['NEW', 'REMOVED', 'DONE'],
                    '>UPTIME' => DateTime::createFromTimestamp(strtotime('-'.$callsUptime.' days'))
                ],
                'order'  => ['UPTIME' => 'ASC'],
                'limit'  => $callsLimit,
            ]);
            while ($tmp = $dbData->fetch()) {
                $calls[$tmp['ACCOUNT']][$tmp['ID']] = $tmp;
            }

            if (!empty($calls)) {
                foreach ($calls as $accountId => $callsData) {
                    $account = \sqlSdekLogs::getById($accountId);
                    if (!empty($account)) {
                        $application = self::makeApplication($account['ACCOUNT'], $account['SECURE']);
                        foreach ($callsData as $courierCall) {
                            $getInfoResult = self::getCourierCallInfo($courierCall['ID'], $courierCall['INTAKE_UUID'], $application);

                            $resultData['CALLS'][$courierCall['ID']] = ['IS_SUCCESS' => $getInfoResult->isSuccess()] + $getInfoResult->getData();
                            if (!$getInfoResult->isSuccess()) {
                                $result->addErrors($getInfoResult->getErrors());
                            }
                        }
                    } else {
                        $result->addError(new Error(Tools::getMessage('MESS_COURIER_CALL_UNKNOWN_ACCOUNT')));
                    }
                }
            }
        } else {
            $result->addWarning(new Warning('CourierCall table empty.'));
        }
        $result->setData($resultData);

        return $result;
    }

    /**
     * Ajax wrapper for getCourierCallStates
     * @return void
     */
    public static function getCourierCallStatesRequest()
    {
        $result = ['success' => false, 'errors' => 'Unknown error.', 'warnings' => '', 'calls_data' => []];

        if (Tools::isModuleAjaxRequest()) {
            $statesResult = self::getCourierCallStates();

            $result['success']    = $statesResult->isSuccess();
            $result['errors']     = $statesResult->getErrors()->isEmpty() ? '' : $statesResult->getErrorsString(Result::SEPARATOR_NEW_LINE);
            $result['warnings']   = $statesResult->getWarnings()->isEmpty() ? '' : $statesResult->getWarningsString(Result::SEPARATOR_NEW_LINE);
            $result['calls_data'] = $statesResult->getData()['CALLS'] ?: [];
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Checks state of given CourierCall: accepted or any errors raised etc
     * @param $id
     * @return Result
     */
    public static function getCourierCallState($id)
    {
        $result = new Result();

        if (!empty($id)) {
            $courierCall = CourierCallsTable::getByPrimaryId($id);
            if (!empty($courierCall['ID'])) {
                if (!empty($courierCall['INTAKE_UUID'])) {
                    $account = \sqlSdekLogs::getById($courierCall['ACCOUNT']);
                    if (!empty($account)) {
                        $getInfoResult = self::getCourierCallInfo($courierCall['ID'], $courierCall['INTAKE_UUID'], self::makeApplication($account['ACCOUNT'], $account['SECURE']));
                        if ($getInfoResult->isSuccess()) {
                            $result->setData($getInfoResult->getData());
                        } else {
                            $result->addErrors($getInfoResult->getErrors());
                        }
                    } else {
                        $result->addError(new Error(Tools::getMessage('MESS_COURIER_CALL_UNKNOWN_ACCOUNT')));
                    }
                } else {
                    $result->addError(new Error('Get state failed cause CourierCall UUID empty.'));
                }
            } else {
                $result->addError(new Error('Get state failed cause no CourierCall found by given ID.'));
            }
        } else {
            $result->addError(new Error('Get state failed cause no CourierCall ID given.'));
        }

        return $result;
    }

    /**
     * Ajax wrapper for getCourierCallState
     * @param array $request
     * @return void
     */
    public static function getCourierCallStateRequest($request)
    {
        $result = ['success' => false, 'errors' => 'Unknown error.', 'warnings' => '', 'intake_number' => false];

        if (Tools::isModuleAjaxRequest()) {
            $stateResult = self::getCourierCallState($request['callId']);

            $result['success']       = $stateResult->isSuccess();
            $result['errors']        = $stateResult->getErrors()->isEmpty() ? '' : $stateResult->getErrorsString(Result::SEPARATOR_NEW_LINE);
            $result['warnings']      = $stateResult->getWarnings()->isEmpty() ? '' : $stateResult->getWarningsString(Result::SEPARATOR_NEW_LINE);
            $result['intake_number'] = $stateResult->getData()['INTAKE_NUMBER'] ?: false;
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Get info for given CourierCall, update data in DB  
     * @param int $callId
     * @param string $uuid
     * @param SdekApplication $application
     * @return Result
     */
    protected static function getCourierCallInfo($callId, $uuid, $application)
    {
        $result = new Result();
        $resultData = ['INTAKE_NUMBER' => false];
        $preparedData = [];

        $controller = new CourierCallController($application);
        $getInfoResult = $controller->getCourierCallInfo($uuid);
        
        $getInfoData = $getInfoResult->getData();
        
        // This one for DB
        if (!empty($getInfoData['STATUSES']) && is_array($getInfoData['STATUSES'])) {
            $preparedData['STATUS_CODE'] = $getInfoData['STATUSES'][0]['STATUS'];
            $preparedData['STATUS_DATE'] = DateTime::createFromTimestamp($getInfoData['STATUSES'][0]['DATETIME']);
        }
        if (!empty($getInfoData['INFO_LAST_STATE_CODE'])) {
            $preparedData['STATE_CODE'] = $getInfoData['INFO_LAST_STATE_CODE'];
            $preparedData['STATE_DATE'] = DateTime::createFromTimestamp($getInfoData['INFO_LAST_STATE_DATE']);
        }
        $preparedData['UPTIME'] = DateTime::createFromTimestamp(time());
        
        if ($getInfoResult->isSuccess()) {
            // Because of Async API 2.0 'success' not equal to 'we got da intake number'
            $intakeNumber = (!empty($getInfoData['INTAKE_NUMBER'])) ? $getInfoData['INTAKE_NUMBER'] : false;

            if ($intakeNumber) {
                $preparedData['INTAKE_NUMBER'] = $intakeNumber;
                $preparedData['OK']            = 'Y';

                if (!empty($preparedData['STATUS_CODE'])) {
                    $status = self::getStatusLink($preparedData['STATUS_CODE']);
                    if ($status) {
                        $preparedData['STATUS'] = $status;
                    }
                } else {
                    $preparedData['STATUS'] = 'OK';
                }

                $resultData['INTAKE_NUMBER']   = $intakeNumber;
            } else {
                $preparedData['OK']            = 'N';
            }

            $preparedData['MESSAGE'] = null;            
        } else {
            $result->addErrors($getInfoResult->getErrors());

            if (!empty($preparedData['STATE_CODE']) && $preparedData['STATE_CODE'] === 'INVALID') {
                // $preparedData['INTAKE_NUMBER'] = null;
                // $preparedData['INTAKE_UUID']   = null;
                $preparedData['STATUS']        = 'ERROR';                
                $preparedData['OK']            = 'N';
            }

            $preparedData['MESSAGE'] = $getInfoResult->getErrors()->isEmpty() ? null : serialize($getInfoResult->getErrors()->getMessages());
        }

        $result->setData($resultData);
        $result->addWarnings($getInfoResult->getWarnings());

        $dbResult = CourierCallsTable::update($callId, $preparedData);
        if (!$dbResult->isSuccess()) {
            foreach ($dbResult->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), $error->getCode()));
            }
        }

        return $result;
    }

    /**
     * Get corresponding module status for given Cdek status code
     * @param string $cdekStatusCode
     * @return string
     */
    protected static function getStatusLink($cdekStatusCode)
    {
        $status = '';

        switch($cdekStatusCode) {
            case 'ACCEPTED':
            case 'CREATED':
            case 'REMOVED':
            case 'READY_FOR_APPOINTMENT':
            case 'APPOINTED_COURIER':
            case 'DONE':
            case 'PROBLEM_DETECTED':
            case 'PROCESSING_REQUIRED':
                $status = $cdekStatusCode;
                break;
            case 'INVALID':
                $status = 'ERROR';
                break;
        }

        return $status;
    }

    /**
     * Ajax wrapper for loading existed CourierCall data
     * @param array $request
     * @return void
     */
    public static function loadCourierCallRequest($request)
    {
        $result = ['success' => false, 'errors' => 'Unknown error.', 'data' => false];

        if (Tools::isModuleAjaxRequest()) {
            $id = (int)$request['callId'];
            if (!empty($id)) {
                if (CourierCallsTable::getByPrimaryId($id)) {
                    $result['success'] = true;
                    $result['errors']  = '';
                    $result['data']    = self::loadUploadedCourierCall($id); // Da JsonSerializable magic hides in adapter
                } else {
                    $result['errors'] = 'Loading CourierCall data failed cause no CourierCall found by given ID.';
                }
            } else {
                $result['errors'] = 'Loading CourierCall data failed cause no CourierCall ID given.';
            }
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Ajax wrapper for loading new CourierCall data
     * @param array $request
     * @return void
     */
    public static function newCourierCallRequest($request)
    {
        $result = ['success' => false, 'errors' => 'Unknown error.', 'data' => false];

        if (Tools::isModuleAjaxRequest()) {
            $callType = (int)$request['callType'] ?: CourierCall::TYPE_ORDER;
            switch ($callType) {
                case CourierCall::TYPE_ORDER:
                default:
                    $result['data'] = self::loadNewCourierCall(CourierCall::TYPE_ORDER);
                    break;
                case CourierCall::TYPE_CONSOLIDATION:
                    $result['data'] = self::loadNewCourierCall(CourierCall::TYPE_CONSOLIDATION);
                    break;
            }

            $result['success'] = true;
            $result['errors']  = '';
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Erase CourierCall from DB
     * @param int $id
     * @return Result
     */
    public static function eraseCourierCall($id)
    {
        $result = new Result();

        if (\sdekHelper::isAdmin('W')) {
            if (!empty($id)) {
                $courierCall = CourierCallsTable::getByPrimaryId($id);
                if (!empty($courierCall['ID'])) {
                    $eraseResult = CourierCallsTable::delete($courierCall['ID']);
                    if ($eraseResult->isSuccess()) {
                        $result->setData(['ERASED_ID' => $courierCall['ID']]);
                    } else {
                        foreach ($eraseResult->getErrors() as $error) {
                            $result->addError(new Error($error->getMessage(), $error->getCode()));
                        }
                    }
                } else {
                    $result->addError(new Error('Erase failed cause no CourierCall found by given ID.'));
                }
            } else {
                $result->addError(new Error('Erase failed cause no CourierCall ID given.'));
            }
        } else {
            $result->addError(new Error(Tools::getMessage('MESS_COURIER_CALL_NO_RIGHTS_ERASE')));
        }

        return $result;
    }

    /**
     * Ajax wrapper for eraseCourierCall
     * @param array $request
     * @return void
     */
    public static function eraseCourierCallRequest($request)
    {
        $result = ['success' => false, 'errors' => 'Unknown error.'];

        if (Tools::isModuleAjaxRequest()) {
            $eraseResult = self::eraseCourierCall($request['callId']);

            $result['success'] = $eraseResult->isSuccess();
            $result['errors']  = $eraseResult->getErrors()->isEmpty() ? '' : $eraseResult->getErrorsString(Result::SEPARATOR_NEW_LINE);
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Unmake CourierCall from API
     * @param int $id
     * @return Result
     */
    public static function deleteCourierCall($id)
    {
        $result = new Result();

        if (\sdekHelper::isAdmin('W')) {
            if (!empty($id)) {
                $courierCall = CourierCallsTable::getByPrimaryId($id);
                if (!empty($courierCall['ID'])) {
                    if (!empty($courierCall['INTAKE_UUID'])) {
                        $account = \sqlSdekLogs::getById($courierCall['ACCOUNT']);
                        if (!empty($account)) {
                            $controller = new CourierCallController(self::makeApplication($account['ACCOUNT'], $account['SECURE']));
                            $deleteResult = $controller->deleteCourierCall($courierCall['INTAKE_UUID']);
                            if ($deleteResult->isSuccess()) {
                                $result->setData(['DELETED_ID' => $courierCall['ID']]);
                            } else {
                                $result->addErrors($deleteResult->getErrors());
                            }
                        } else {
                            $result->addError(new Error(Tools::getMessage('MESS_COURIER_CALL_UNKNOWN_ACCOUNT')));
                        }
                    } else {
                        $result->addError(new Error('Delete failed cause CourierCall UUID empty.'));
                    }
                } else {
                    $result->addError(new Error('Delete failed cause no CourierCall found by given ID.'));
                }
            } else {
                $result->addError(new Error('Delete failed cause no CourierCall ID given.'));
            }
        } else {
            $result->addError(new Error(Tools::getMessage('MESS_COURIER_CALL_NO_RIGHTS_DELETE')));
        }

        return $result;
    }

    /**
     * Ajax wrapper for deleteCourierCall
     * @param array $request
     * @return void
     */
    public static function deleteCourierCallRequest($request)
    {
        $result = ['success' => false, 'errors' => 'Unknown error.'];

        if (Tools::isModuleAjaxRequest()) {
            $deleteResult = self::deleteCourierCall($request['callId']);

            $result['success'] = $deleteResult->isSuccess();
            $result['errors']  = $deleteResult->getErrors()->isEmpty() ? '' : $deleteResult->getErrorsString(Result::SEPARATOR_NEW_LINE);
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Returns list of active CDEK accounts
     * @return array
     */
    public static function getActiveAccounts()
    {
        $result = [];

        $accounts = \sqlSdekLogs::getAccountsList(true);
        $basicAccount = \sdekHelper::getBasicAuth(true);

        foreach ($accounts as $key => $val) {
            $tmp = $val['ACCOUNT'];

            if (!empty($val['LABEL']))
                $tmp .= ' ('.$val['LABEL'].')';

            if ($key == $basicAccount)
                $tmp .= ' ['.Tools::getMessage('LBL_BASIC_ACCOUNT').']';

            $result[$key] = $tmp;
        }

        return $result;
    }

    /**
     * Da 2.0 only
     * @param string $account
     * @param string $secure
     * @return SdekApplication
     */
    public static function makeApplication($account, $secure)
    {
        return new SdekApplication(
            $account,
            $secure,
            false,
            10,
            new encoder(),
            new cache()
            //, new \Ipolh\SDEK\Admin\IvanInlineLoggerController()
        );
    }
}