<?php
namespace Ipolh\SDEK\Api\Methods;

use Ipolh\SDEK\Api\Adapter\CurlAdapter;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\EncoderInterface;
use Ipolh\SDEK\Api\Entity\Request\Oauth as ObjRequest;
use Ipolh\SDEK\Api\Entity\Response\Oauth as ObjResponse;

/**
 * Class Oauth
 * @package Ipolh\SDEK\Api
 * @subpackage Methods
 * @method ObjResponse getResponse
 */
class Oauth extends GeneralMethod
{
    /**
     * Oauth constructor.
     * @param ObjRequest $data
     * @param CurlAdapter $adapter
     * @param EncoderInterface|null $encoder
     * @throws BadResponseException
     */
    public function __construct(ObjRequest $data, CurlAdapter $adapter, $encoder = null)
    {
        $this->setDataGet($this->getEntityFields($data));
        parent::__construct(null, $adapter, ObjResponse::class, $encoder);
    }

}