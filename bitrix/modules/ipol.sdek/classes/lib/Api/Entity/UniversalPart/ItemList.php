<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class ItemList
 * @package Ipolh\SDEK\Api
 * @subpackage Entity\UniversalPart
 * @method Item getFirst()
 * @method Item getNext()
 */
class ItemList extends AbstractCollection
{
    protected $Items;

    public function __construct()
    {
        parent::__construct('Items');
    }
}