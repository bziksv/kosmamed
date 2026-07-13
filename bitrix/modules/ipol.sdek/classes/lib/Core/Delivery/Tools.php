<?php


namespace Ipolh\SDEK\Core\Delivery;


/**
 * Class Tools
 * @package Ipolh\SDEK\Core
 * @subpackage Delivery
 */
class Tools
{
    /**
     * @param $termMin
     * @param $termMax
     * @param string $glue
     * @return mixed|string
     */
    public static function getTerm($termMin, $termMax, $glue = '-')
    {
        if($termMin == $termMax)
            return $termMin;
        else
            return $termMin.$glue.$termMax;
    }

}