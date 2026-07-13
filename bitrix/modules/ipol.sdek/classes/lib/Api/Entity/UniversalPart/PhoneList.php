<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class PhoneList
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 * @method Phone getFirst()
 * @method Phone getNext()
 */
class PhoneList extends AbstractCollection
{

    protected $Phones;

    public function __construct()
    {
        parent::__construct('Phones');
    }
}