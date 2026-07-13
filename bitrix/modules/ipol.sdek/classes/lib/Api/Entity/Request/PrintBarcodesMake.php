<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\Entity\UniversalPart\OrderList;

/**
 * Class PrintBarcodesMake
 * @package Ipolh\SDEK\Api
 * @subpackge Request
 */
class PrintBarcodesMake extends AbstractRequest
{
    /**
     * @var OrderList
     */
    protected $orders;

    /**
     * @var int|null CDEK default 1
     */
    protected $copy_count;

    /**
     * @var string|null 'A4' | 'A5' | 'A6'
     */
    protected $format;

    /**
     * @var string|null ISO 639-3 - 'RUS' | 'ENG'
     */
    protected $lang;

    /**
     * @return OrderList
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param OrderList $orders
     * @return PrintBarcodesMake
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCopyCount()
    {
        return $this->copy_count;
    }

    /**
     * @param int|null $copy_count
     * @return PrintBarcodesMake
     */
    public function setCopyCount($copy_count)
    {
        $this->copy_count = $copy_count;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string|null $format
     * @return PrintBarcodesMake
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string|null $lang
     * @return PrintBarcodesMake
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }
}