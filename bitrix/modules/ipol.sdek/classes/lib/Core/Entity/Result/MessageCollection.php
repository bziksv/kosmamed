<?php
namespace Ipolh\SDEK\Core\Entity\Result;

/**
 * Class MessageCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 * @method Message getFirst
 * @method Message getNext
 * @method Message getLast
 */
class MessageCollection extends InfoCollection
{
    protected $Messages;

    public function __construct()
    {
        parent::__construct('Messages');
    }
}