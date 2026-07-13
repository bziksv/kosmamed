<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Package
 * @package Ipolh\SDEK\Api\Entity\Request\Part\CalculateList
 */
class Dimensions extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var float
     * sm
     */
    protected $width;
    /**
     * @var float
     * sm
     */
    protected $height;
    /**
     * @var float
     * sm
     */
    protected $depth;

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param float $width
     * @return Dimensions
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param float $height
     * @return Dimensions
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return float
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @param float $depth
     * @return Dimensions
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
        return $this;
    }

}