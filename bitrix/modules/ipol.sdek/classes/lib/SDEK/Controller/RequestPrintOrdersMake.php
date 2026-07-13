<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\BadRequestException;
use Ipolh\SDEK\Api\Entity\Request\PrintOrdersMake as RequestObj;
use Ipolh\SDEK\SDEK\Entity\PrintOrdersMakeResult as ResultObj;

/**
 * Class RequestPrintOrdersMake
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestPrintOrdersMake extends AutomatedCommonRequest
{
    use RequestPrintFormsMake;

    /**
     * RequestPrintOrdersMake constructor.
     * @param ResultObj $resultObj
     * @param string[]|null $uuids
     * @param int[]|null $cdekNumbers
     * @param int|null $copyCount
     * @param string|null $type
     */
    public function __construct(
        $resultObj,
        $uuids,
        $cdekNumbers,
        $copyCount,
        $type
    )
    {
        parent::__construct($resultObj);
        $this->uuids = $uuids;
        $this->cdekNumbers = $cdekNumbers;

        $this->setRequestObj(new RequestObj());

        $this->getRequestObj()
            ->setCopyCount($copyCount)
            ->setType($type);
    }

    public function getSelfHash()
    {
        return $this->getSelfHashByRequestObj().md5(serialize([$this->uuids, $this->cdekNumbers]));
    }

    /**
     * @return $this
     * @throws BadRequestException
     */
    public function convert()
    {
        $this->getRequestObj()
            ->setOrders($this->generateOrders());

        return $this;
    }
}
