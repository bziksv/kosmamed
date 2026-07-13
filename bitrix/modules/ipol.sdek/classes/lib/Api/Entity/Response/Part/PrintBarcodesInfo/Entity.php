<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\PrintBarcodesInfo;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\UniversalPart\OrderList;

/**
 * Class Entity
 * @package Ipolh\SDEK\Api\Entity\Response\Part\PrintBarcodesInfo
 */
class Entity extends \Ipolh\SDEK\Api\Entity\Response\Part\Common\Entity
{
    /**
     * @var OrderList
     */
    protected $orders;

    /**
     * @var int|null
     */
    protected $copy_count;

    /**
     * @var string|null
     */
    protected $format;

    /**
     * @var string|null
     */
    protected $lang;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var StatusList
     */
    protected $statuses;

    /**
     * @return OrderList
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param array $array
     * @return Entity
     * @throws BadResponseException
     */
    public function setOrders($array)
    {
        $collection = new OrderList();
        $this->orders = $collection->fillFromArray($array);
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
     * @return Entity
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
     * @return Entity
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
     * @return Entity
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return Entity
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return StatusList
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @param array $array
     * @return Entity
     * @throws BadResponseException
     */
    public function setStatuses($array)
    {
        $collection = new StatusList();
        $this->statuses = $collection->fillFromArray($array);
        return $this;
    }
}