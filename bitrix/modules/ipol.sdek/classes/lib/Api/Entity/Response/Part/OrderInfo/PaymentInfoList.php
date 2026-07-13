<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class PaymentInfoList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method PaymentInfo getFirst()
 * @method PaymentInfo getNext()
 */
class PaymentInfoList extends AbstractCollection
{
    protected $PaymentInfos;

    public function __construct()
    {
        parent::__construct('PaymentInfos');
        $this->setChildClass(PaymentInfo::class);
    }
}