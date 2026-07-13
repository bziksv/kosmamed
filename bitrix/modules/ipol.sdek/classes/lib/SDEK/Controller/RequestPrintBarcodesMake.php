<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\BadRequestException;
use Ipolh\SDEK\Api\Entity\Request\PrintBarcodesMake as RequestObj;
use Ipolh\SDEK\SDEK\Entity\PrintBarcodesMakeResult as ResultObj;

/**
 * Class RequestPrintBarcodesMake
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestPrintBarcodesMake extends AutomatedCommonRequest
{
    use RequestPrintFormsMake;

    /**
     * RequestPrintBarcodesMake constructor.
     * @param ResultObj $resultObj
     * @param string[]|null $uuids
     * @param int[]|null $cdekNumbers
     * @param int|null $copyCount
     * @param string|null $format
     * @param string|null $lang
     */
    public function __construct(
        $resultObj,
        $uuids,
        $cdekNumbers,
        $copyCount,
        $format,
        $lang
    )
    {
        parent::__construct($resultObj);
        $this->uuids = $uuids;
        $this->cdekNumbers = $cdekNumbers;

        $this->setRequestObj(new RequestObj());

        $this->getRequestObj()
            ->setCopyCount($copyCount)
            ->setFormat($format)
            ->setLang($lang);
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