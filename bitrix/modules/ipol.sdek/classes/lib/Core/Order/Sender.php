<?php


namespace Ipolh\SDEK\Core\Order;


/**
 * Class Sender
 * @package Ipolh\SDEK\Core
 * @subpackage Order
 */
class Sender extends AbstractPerson
{
    /**
     * @var mixed
     */
    protected $company;

    /**
     * Sender constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }
}