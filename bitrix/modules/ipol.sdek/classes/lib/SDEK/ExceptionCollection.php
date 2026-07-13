<?php


namespace Ipolh\SDEK\SDEK;


use Exception;
use Ipolh\SDEK\Core\Entity\Collection;

class ExceptionCollection extends Collection
{
    public function __construct()
    {
        parent::__construct('errors');
    }

    public function getAllMessages()
    {
        $this->reset();
        if ($current = $this->getNext()) {
            /**@var $current Exception*/
            $strReturn = $current->getMessage();
        } else {
            return '';
        }

        while ($current = $this->getNext()) {
            /**@var $current Exception*/
            $strReturn .= ', ' . $current->getMessage();
        }

        return $strReturn;
    }
}