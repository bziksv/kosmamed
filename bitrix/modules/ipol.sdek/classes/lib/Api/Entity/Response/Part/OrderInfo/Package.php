<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Package
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class Package extends \Ipolh\SDEK\Api\Entity\UniversalPart\Package
{
    use AbstractResponsePart;

    /**
     * @var string
     */
    protected $package_id;

    /**
     * Gram
     * @var int|null
     */
    protected $weight_volume;

    /**
     * Gram
     * @var int|null
     */
    protected $weight_calc;

    /**
     * @return string
     */
    public function getPackageId()
    {
        return $this->package_id;
    }

    /**
     * @param string $package_id
     * @return Package
     */
    public function setPackageId($package_id)
    {
        $this->package_id = $package_id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeightVolume()
    {
        return $this->weight_volume;
    }

    /**
     * @param int|null $weight_volume
     * @return Package
     */
    public function setWeightVolume($weight_volume)
    {
        $this->weight_volume = $weight_volume;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeightCalc()
    {
        return $this->weight_calc;
    }

    /**
     * @param int|null $weight_calc
     * @return Package
     */
    public function setWeightCalc($weight_calc)
    {
        $this->weight_calc = $weight_calc;
        return $this;
    }
}