<?php

namespace Ipolh\SDEK\Bitrix\Controller;

use Ipolh\SDEK\Core\Entity\BasicEntity;
use Ipolh\SDEK\Core\Entity\BasicResponse;
use Ipolh\SDEK\Core\Entity\Collection;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\ErrorCollection;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;
use Ipolh\SDEK\Legacy\transitApplication;
use Ipolh\SDEK\SDEK\SdekApplication;

class Order extends abstractController
{
    use ControllerHelpers;

    protected $order;
    protected $result;

    /**
     * orderController constructor.
     * @param SdekApplication|transitApplication|null $application
     * @param \Ipolh\SDEK\Core\Order\Order $order
     */
    public function __construct($application,$order = false)
    {
        parent::__construct($application);

        if ($order) {
            $this->order = $order;
        }
    }

    /**
     * @return BasicResponse - uid - UDID, state - state of request (checkOrderSendState), cdekNumber - sdek_id
     */
    public function sendOrder()
    {
        $obReturn     = new BasicResponse();

        $resultOfMake = $this->application->orderMake($this->order,1,'4b1d17d262bdf16e36b9070934c74d47');
        $obResult = new BasicEntity();

        // preCheck
        $arErrors = array();
        $appErrors = $this->getApplicationErrors();
        if(!empty($appErrors)){
            $arErrors['sending'] = $appErrors;
        }

        $uid     = false;
        if($resultOfMake->isSuccess()){
            $obReturn->setSuccess(true);

            $response = $resultOfMake->getResponse();

            if($response->getEntity()){
                if($response->getEntity()->getUuid()){
                    $uid = $response->getEntity()->getUuid();
                }
            }

            if($response->getRequests()){
                $response->getRequests()->reset();
                while($obRequest = $response->getRequests()->getNext()){
                    if($obRequest->getState() == 'INVALID'){
                        $obRequest->getErrors()->reset();
                        while($error = $obRequest->getErrors()->getNext()){
                            $arErrors[$error->getCode()]= $error->getMessage();
                        }
                        $uid = false;
                    }
                }
            }

            $obResult->setField('uid',$uid);
            $obResult->setField('state',false);
            $obResult->setField('cdekNumber',false);

            if($uid){
                $invoiceCheck = $this->checkOrderSendState($uid);

                $errs = $invoiceCheck->getError();
                if(!empty($errs)){
                    $arErrors['checking'] = array();
                    $errs->reset();
                    while($obErr = $errs->getNext()){
                        $arErrors['checking'][]= $obErr;
                    }
                }

                if($invoiceCheck->isSuccess()){
                    $response = $invoiceCheck->getResponse();
                    if($response->getField('state')){
                        $obResult->setField('state',$response->getField('state'));
                        if($response->getField('state') === 'SUCCESSFUL'){
                            $obResult->setField('cdekNumber',$response->getField('cdekNumber'));
                        }
                    }
                }
            }
        } else {
            $obReturn->setSuccess(false);
        }

        // dealing errors
        if(!empty($arErrors)){
            $obError = new Collection('error');
            foreach ($arErrors as $code => $errs){
                switch($code){
                    case 'sending'  :
                        foreach ($errs as $errText){
                            $obError->add($errText.' ('.$code.')');
                        }
                    break;
                    case 'checking' :
                        foreach ($errs as $errText){
                            $obError->add($errText);
                        }
                    break;
                    default :
                        $obError->add($errs . ' (' . $code . ')');
                    break;
                }
            }
            $obReturn->setError($obError);
        }
        $obReturn->setResponse($obResult);

        return $obReturn;
    }

    /**
     * @param $uid
     * @return BasicResponse state - SUCCESSFUL|ACCEPTED|WAITING|INVALID, cdekNumber - sdek_id
     */
    public function checkOrderSendState($uid){
        $obReturn     = new BasicResponse();
        $obResponse   = $this->application->orderInfoByUuid($uid);

        $arErrors = array();
        $appErrors = $this->getApplicationErrors();
        if(!empty($appErrors)){
            $arErrors['checking'] = $appErrors;
        }
        $cNumber = false;
        $state   = false;

        if($obResponse->isSuccess()){
            $obReturn->setSuccess(true);

            $response = $obResponse->getResponse();

            if($response->getEntity()){
                if($response->getEntity()->getCdekNumber()){
                    $cNumber = $response->getEntity()->getCdekNumber();
                }
            }

            if($response->getRequests()){
                $response->getRequests()->reset();
                while($obRequest = $response->getRequests()->getNext()){
                    if($obRequest->getType() === 'CREATE') {
                        $state = $obRequest->getState();
                        if ($state == 'INVALID') {
                            if ($obRequest->getErrors()) {
                                $obRequest->getErrors()->reset();
                                while (
                                $error = $obRequest->getErrors()->getNext()) {
                                    $arErrors[$error->getCode()] = $error->getMessage();
                                }
                            } else {
                                $arErrors['invalid_order'] = 'Order state is INVALID but no error messages were received from CDEK server.';
                            }
                            $cNumber = false;
                        } elseif ($state !== 'SUCCESSFUL') {
                            $cNumber = false;
                        }
                    }
                }
            }
        } else {
            $obReturn->setSuccess(false);
        }

        $obResult = new BasicEntity();
        $obResult->setField('cdekNumber',$cNumber);
        $obResult->setField('state',$state);
        $obReturn->setResponse($obResult);

        // dealing errors
        if(!empty($arErrors)){
            $obError = new Collection('error');
            foreach ($arErrors as $code => $errs){
                switch($code){
                    case 'checking' :
                        foreach ($errs as $errText){
                            $obError->add($errText.' ('.$code.')');
                        }
                    break;
                    default :
                        $obError->add($errs . ' (' . $code . ')');
                    break;
                }
            }
            $obReturn->setError($obError);
        }

        return $obReturn;
    }

    /**
     * Get Order info by given CDEK number
     * @param string $cdekNumber
     * @return Result
     */
    public function getOrderInfoByNumber($cdekNumber)
    {
        $result = new Result();

        if (empty($cdekNumber)) {
            $result->addError(new Error('No CDEK number of Order creation request given.'));
        }

        if (!($this->application instanceof SdekApplication)) {
            $result->addError(new Error('Only API 2.0 SdekApplication class allowed.'));
        }

        if ($result->isSuccess()) {
            $answer = $this->application->orderInfoByNumber($cdekNumber);
            if ($answer->isSuccess()) {
                $response = $answer->getResponse();
                $data = ['UUID' => null, 'NUMBER' => null, 'STATUSES' => [], 'RESPONSE' => $response];

                if ($entity = $response->getEntity()) {
                    $data['UUID'] = $entity->getUuid();
                    $data['NUMBER'] = $entity->getNumber();

                    $entity->getStatuses()->reset();
                    while ($status = $entity->getStatuses()->getNext()) {
                        $data['STATUSES'][] = [
                            'DATETIME' => $status->getDateTime()->getTimestamp(),
                            'STATUS' => $status->getCode(),
                            'REASON_CODE' => $status->getReasonCode()
                        ];
                    }
                }

                if ($requestsList = $response->getRequests()) {
                    $processRequestResult = $this->processRequestList($requestsList);
                    $data['INFO_LAST_STATE_CODE'] = $processRequestResult->getData()['LAST_STATE_CODE'];
                    $data['INFO_LAST_STATE_DATE'] = $processRequestResult->getData()['LAST_STATE_DATE'];

                    if (!$processRequestResult->getErrors()->isEmpty())
                        $result->addErrors($processRequestResult->getErrors());

                    if (!$processRequestResult->getWarnings()->isEmpty())
                        $result->addWarnings($processRequestResult->getWarnings());
                } else {
                    $result->addError(new Error('Unknown data object has invaded from server.'));
                }

                $result->setData($data);
            } else {
                $this->collectApplicationErrors($result,  __FUNCTION__);
            }
        }

        return $result;
    }

    /**
     * Get Orders info by given CDEK numbers. Beware: CURL multi inside
     * @param string[] $cdekNumbers
     * @return Result
     */
    public function getOrderInfoByNumberMulti($cdekNumbers)
    {
        $result = new Result();

        if (empty($cdekNumbers)) {
            $result->addError(new Error('No CDEK numbers of Order creation request given.'));
        }

        if (!($this->application instanceof SdekApplication)) {
            $result->addError(new Error('Only API 2.0 SdekApplication class allowed.'));
        }

        if ($result->isSuccess()) {
            $answer = $this->application->orderInfoByNumberMulti($cdekNumbers);
            if ($answer->isSuccess()) {
                $response = $answer->getResponse();
                $orders = $response->getOrderInfoList();

                $ordersInfo = [];

                $orders->reset();
                while ($response = $orders->getNext()) {
                    $data = ['UUID' => null, 'NUMBER' => null, 'STATUSES' => [], 'ERRORS' => new ErrorCollection(), 'RESPONSE' => $response];

                    if ($entity = $response->getEntity()) {
                        $data['UUID'] = $entity->getUuid();
                        $data['NUMBER'] = $entity->getNumber();

                        $entity->getStatuses()->reset();
                        while ($status = $entity->getStatuses()->getNext()) {
                            $data['STATUSES'][] = [
                                'DATETIME' => $status->getDateTime()->getTimestamp(),
                                'STATUS' => $status->getCode(),
                                'REASON_CODE' => $status->getReasonCode()
                            ];
                        }
                    }

                    if ($requestsList = $response->getRequests()) {
                        $processRequestResult = $this->processRequestList($requestsList);
                        $data['INFO_LAST_STATE_CODE'] = $processRequestResult->getData()['LAST_STATE_CODE'];
                        $data['INFO_LAST_STATE_DATE'] = $processRequestResult->getData()['LAST_STATE_DATE'];

                        if (!$processRequestResult->getErrors()->isEmpty())
                            $data['ERRORS']->append($processRequestResult->getErrors());
                    }

                    $ordersInfo[$response->getCdekNumber()] = $data;
                }

                $result->setData(['ORDERS' => $ordersInfo]);
            } else {
                $this->collectApplicationErrors($result,  __FUNCTION__);
            }
        }

        return $result;
    }

    /**
     * Delete Order by given UUID
     * @param string $uuid
     * @return Result
     */
    public function deleteOrder($uuid)
    {
        $result = new Result();

        if (empty($uuid)) {
            $result->addError(new Error('No UUID of Order creation request given.'));
        }

        if (!($this->application instanceof SdekApplication)) {
            $result->addError(new Error('Only API 2.0 SdekApplication class allowed.'));
        }

        if ($result->isSuccess()) {
            $answer = $this->application->orderDelete($uuid);
            if ($answer->isSuccess()) {
                $response = $answer->getResponse();
                $data = [];

                if ($requestsList = $response->getRequests()) {
                    $processRequestResult = $this->processRequestList($requestsList);
                    $data['DELETE_LAST_STATE_CODE'] = $processRequestResult->getData()['LAST_STATE_CODE'];
                    $data['DELETE_LAST_STATE_DATE'] = $processRequestResult->getData()['LAST_STATE_DATE'];

                    if (!$processRequestResult->getErrors()->isEmpty())
                        $result->addErrors($processRequestResult->getErrors());

                    if (!$processRequestResult->getWarnings()->isEmpty())
                        $result->addWarnings($processRequestResult->getWarnings());
                } else {
                    $result->addError(new Error('Unknown data object has invaded from server.'));
                }

                $result->setData($data);
            } else {
                $this->collectApplicationErrors($result,  __FUNCTION__);
            }
        }

        return $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return \Ipolh\SDEK\Core\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Ipolh\SDEK\Core\Order\Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return array
     * Returns array of application errors
     */
    protected function getApplicationErrors()
    {
        $arErrors = array();
        if($this->application) {
            $errors = $this->application->getErrorCollection();
            $errors->reset();
            while (
            $error = $errors->getNext()) {
                $arErrors [] = $error->getMessage();
            }
        }
        return $arErrors;
    }
}