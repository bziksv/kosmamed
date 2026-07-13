<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class RelatedEntity
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class RelatedEntity extends \Ipolh\SDEK\Api\Entity\Response\Part\Common\RelatedEntity
{
    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $cdek_number;

    /**
     * TODO: check real data type
     * @var string|null
     */
    protected $date;

    /**
     * TODO: check real data type
     * @var string|null
     */
    protected $time_from;

    /**
     * TODO: check real data type
     * @var string|null
     */
    protected $time_to;

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return RelatedEntity
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCdekNumber()
    {
        return $this->cdek_number;
    }

    /**
     * @param string|null $cdek_number
     * @return RelatedEntity
     */
    public function setCdekNumber($cdek_number)
    {
        $this->cdek_number = $cdek_number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string|null $date
     * @return RelatedEntity
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimeFrom()
    {
        return $this->time_from;
    }

    /**
     * @param string|null $time_from
     * @return RelatedEntity
     */
    public function setTimeFrom($time_from)
    {
        $this->time_from = $time_from;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimeTo()
    {
        return $this->time_to;
    }

    /**
     * @param string|null $time_to
     * @return RelatedEntity
     */
    public function setTimeTo($time_to)
    {
        $this->time_to = $time_to;
        return $this;
    }
}