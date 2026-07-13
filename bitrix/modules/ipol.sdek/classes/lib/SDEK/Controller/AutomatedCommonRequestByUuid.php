<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Exception;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\ErrorResponse;
use Ipolh\SDEK\SDEK\AppLevelException;
use Ipolh\SDEK\SDEK\Entity\AbstractResult;
use ReflectionClass;

class AutomatedCommonRequestByUuid extends RequestController
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * BasicController constructor.
     * @param AbstractResult|mixed $resultObj
     */
    public function __construct($resultObj, $uuid)
    {
        $this->resultObject = $resultObj;
        $this->uuid = $uuid;
    }

    /**
     * @return $this|mixed
     */
    public function convert()
    {
        return $this;
    }

    /**
     * @return AbstractResult|mixed
     */
    public function execute()
    {
        $result = $this->getResultObject();

        try {
            $requestProcess = $this->getSdk()->{$this->getSdkMethodName()}($this->uuid);

            $result->setSuccess($requestProcess->getResponse()->getSuccess())
                ->setResponse($requestProcess->getResponse());
            if ($result->isSuccess()) {
                $result->parseFields();
            } elseif (is_a($requestProcess->getResponse(), ErrorResponse::class)) {
                    $result->setError(new Exception('Execute fail')); //TODO errorResponse parsing
                }
        } catch (BadResponseException $e) {
            $result->setSuccess(false)
                ->setError($e);
        } catch (AppLevelException $e) {
            $result->setSuccess(false)
                ->setError($e);
        } catch (Exception $e) {
            // Handling errors such as argument types fuckup and another cool situations
            $result->setSuccess(false)->setError(new Exception($e->getMessage()));
        } finally {
            return $result;
        }
    }
    public function getSelfHash()
    {
        $extended = new ReflectionClass(get_class($this)); //real running classname - extension-class

        if ($extended->getMethod('convert')->getDeclaringClass()->name === get_class($this) &&
            get_class($this) !== __CLASS__) {
            throw new Exception('Default getSelfHash() method is not suitable for converted requests. Declare custom method in extended class.');
        }
        return md5($this->getSelfHashByRequestObj());
    }

    protected function getSelfHashByRequestObj()
    {
        if (!is_null($this->getRequestObj())) {
            $resString = get_class($this->getRequestObj());
            $resString .= serialize($this->getRequestObj()->getFields());
        } else {
            $resString = get_class($this->getResultObject());
        }

        return $resString;
    }
}