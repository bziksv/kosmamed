<?php

namespace Ipolh\SDEK\Bitrix\Controller;


use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Legacy\transitApplication;
use Ipolh\SDEK\SDEK\SdekApplication;

class abstractController
{
    protected static $MODULE_LBL = IPOLH_SDEK_LBL;
    protected static $MODULE_ID  = IPOLH_SDEK;

    protected $application = null;
    protected $options     = null;

    /**
     * abstractController constructor.
     * @param SdekApplication|transitApplication|null $application
     */
    public function __construct($application = false)
    {
        $this->options = new Options();

        if($application){
            $this->application = $application;
        }
    }

    /**
     * @return transitApplication|SdekApplication|null
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param transitApplication|SdekApplication|null $application
     * @return $this
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }
}