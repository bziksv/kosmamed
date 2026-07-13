<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class PackageList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method Package getFirst
 * @method Package getNext
 * @method Package getLast
 */
class PackageList extends \Ipolh\SDEK\Api\Entity\UniversalPart\PackageList
{
    public function __construct()
    {
        parent::__construct();
        $this->setChildClass(Package::class);
    }
}