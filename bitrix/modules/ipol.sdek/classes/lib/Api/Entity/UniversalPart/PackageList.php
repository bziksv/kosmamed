<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class PackageList
 * @package Ipolh\SDEK\Api
 * @subpackage Entity\UniversalPart
 * @method Package getFirst()
 * @method Package getNext()
 */
class PackageList extends AbstractCollection
{
    protected $Packages;

    public function __construct()
    {
        parent::__construct('Packages');
    }
}