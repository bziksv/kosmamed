<?php
namespace Ipolh\SDEK\Api\Methods;

use Ipolh\SDEK\Api\Adapter\CurlAdapter;
use Ipolh\SDEK\Api\ApiLevelException;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\EncoderInterface;
use Ipolh\SDEK\Api\Entity\Request\AbstractRequest as ObjRequest;
use Ipolh\SDEK\Api\Entity\Response\AbstractResponse;
use Ipolh\SDEK\Api\Entity\Response\ErrorResponse;

/**
 * Class CalculateList
 * @package Ipolh\SDEK\Api
 * @subpackage Methods
* @method AbstractResponse|mixed getResponse
 */
class GeneralMethod extends AbstractMethod
{
    /**
     * CalculateList constructor.
     * @param ObjRequest|mixed|null $data
     * @param CurlAdapter $adapter
     * @param string $responseClass
     * @param EncoderInterface|null $encoder
     * @throws BadResponseException
     */
    public function __construct($data, CurlAdapter $adapter, $responseClass, $encoder = null)
    {
        parent::__construct($adapter, $encoder);

        if (!is_null($data)) {
            $this->setData($this->getEntityFields($data));
        }

        try {
            $response = new $responseClass($this->request());
            $response->setSuccess(true);
        } catch (ApiLevelException $e) {
            $response = new ErrorResponse($e->getAnswer());
            $response->setSuccess(false); //TODO if there are valid error-codes
        }
        $this->setResponse($this->reEncodeResponse($response));
        $this->setFields();
    }

}