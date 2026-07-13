<?php
namespace Ipolh\SDEK\Api\Methods;

use Ipolh\SDEK\Api\Adapter\CurlAdapter;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\EncoderInterface;

/**
 * Class GeneralUrlImplementedMethod
 * @package Ipolh\SDEK\Api
 * @subpackage Methods
 */
class GeneralUrlImplementedMethod extends GeneralMethod
{
    /**
     * GeneralUrlImplementedMethod constructor.
     * @param string $urlImplement
     * @param CurlAdapter $adapter
     * @param string $responseClass
     * @param EncoderInterface|null $encoder
     * @throws BadResponseException
     */
    public function __construct(
        $urlImplement,
        CurlAdapter $adapter,
        $responseClass,
        $encoder = null
    ) {
        $this->setUrlImplement($urlImplement);
        parent::__construct(null, $adapter, $responseClass, $encoder);
        
    }

}