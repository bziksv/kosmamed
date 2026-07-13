<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class PhoneList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method Phone getFirst
 * @method Phone getNext
 * @method Phone getLast
 */
class PhoneList extends \Ipolh\SDEK\Api\Entity\UniversalPart\PhoneList
{
    public function __construct()
    {
        parent::__construct();
        $this->setChildClass(Phone::class);
    }
}