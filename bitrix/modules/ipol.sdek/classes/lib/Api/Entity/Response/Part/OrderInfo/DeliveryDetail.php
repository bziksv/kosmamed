<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use DateTime;
use Exception;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class DeliveryDetail
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class DeliveryDetail extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $recipient_name;

    /**
     * @var float|null
     */
    protected $payment_sum;

    /**
     * @var PaymentInfoList|null
     */
    protected $payment_info;

    /**
     * @var float
     */
    protected $delivery_sum;

    /**
     * @var float
     */
    protected $total_sum;

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return DeliveryDetail
     * @throws Exception
     */
    public function setDate($date)
    {
        $this->date = new DateTime($date);
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->recipient_name;
    }

    /**
     * @param string $recipient_name
     * @return DeliveryDetail
     */
    public function setRecipientName($recipient_name)
    {
        $this->recipient_name = $recipient_name;
        return $this;
    }

    /**
     * @return float
     */
    public function getPaymentSum()
    {
        return $this->payment_sum;
    }

    /**
     * @param float $payment_sum
     * @return DeliveryDetail
     */
    public function setPaymentSum($payment_sum)
    {
        $this->payment_sum = $payment_sum;
        return $this;
    }

    /**
     * @return PaymentInfoList|null
     */
    public function getPaymentInfo()
    {
        return $this->payment_info;
    }

    /**
     * @param array $array
     * @return DeliveryDetail
     * @throws BadResponseException
     */
    public function setPaymentInfo($array)
    {
        $collection = new PaymentInfoList();
        $this->payment_info = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return float
     */
    public function getDeliverySum()
    {
        return $this->delivery_sum;
    }

    /**
     * @param float $delivery_sum
     * @return DeliveryDetail
     */
    public function setDeliverySum($delivery_sum)
    {
        $this->delivery_sum = $delivery_sum;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalSum()
    {
        return $this->total_sum;
    }

    /**
     * @param float $total_sum
     * @return DeliveryDetail
     */
    public function setTotalSum($total_sum)
    {
        $this->total_sum = $total_sum;
        return $this;
    }
}