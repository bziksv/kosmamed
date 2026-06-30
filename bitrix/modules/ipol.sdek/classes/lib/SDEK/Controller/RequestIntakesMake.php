<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\Entity\Request\IntakesMake as RequestObj;
use Ipolh\SDEK\SDEK\Entity\IntakesMakeResult as ResultObj;

/**
 * Class RequestIntakesMake
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 */
class RequestIntakesMake extends AutomatedCommonRequest
{
    /**
     * RequestIntakesMake constructor.
     * @param ResultObj $resultObj
     * @param RequestObj $data
     */
    public function __construct($resultObj, $data)
    {
        parent::__construct($resultObj);
        $this->setRequestObj($data);
    }
}