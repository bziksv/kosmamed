<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class ThresholdList
 * @package Ipolh\SDEK\Api
 * @subpackage Entity\UniversalPart
 * @method Threshold getFirst()
 * @method Threshold getNext()
 */
class ThresholdList extends AbstractCollection
{
    protected $Thresholds;

    public function __construct()
    {
        parent::__construct('Thresholds');
    }
}