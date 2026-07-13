<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class ServiceList
 * @package Ipolh\SDEK\Api
 * @subpackage Entity\UniversalPart
 * @method Service getFirst()
 * @method Service getNext()
 */
class ServiceList extends AbstractCollection
{
    protected $Services;

    public function __construct()
    {
        parent::__construct('Services');
    }
}