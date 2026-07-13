<?php
namespace Ipolh\SDEK\API\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class OfficeImage
 * @package Ipolh\SDEK\API\Entity\Response\Part\DeliveryPoints
 */
class OfficeImage extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string(255)
     */
    protected $url;
    /**
     * @var int
     */
    protected $number;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return OfficeImage
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     * @return OfficeImage
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }
}