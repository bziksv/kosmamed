<?php


namespace Ipolh\SDEK\SDEK\Controller;


use Ipolh\SDEK\Api\Entity\Request\DeliveryPoints as RequestObj;
use Ipolh\SDEK\SDEK\Entity\DeliveryPointsResult as ResultObj;
use Ipolh\SDEK\Core\Delivery\Location;

/**
 * Class RequestDeliveryPoints
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestDeliveryPoints extends AutomatedCommonRequest
{
    /**
     * @var Location
     */
    protected $coreLocation;

    /**
     * RequestDeliveryPoints constructor.
     * @param ResultObj $resultObj
     * @param Location|null $coreLocation
     * @param string|null $pointType // 'PVZ' | 'POSTAMAT' | 'ALL'
     * @param bool|null $haveCashless
     * @param bool|null $haveCash
     * @param bool|null $allowedCod
     * @param bool|null $isDressingRoom
     * @param int|null $weightMax
     * @param int|null $weightMin
     * @param string|null $lang
     * @param bool|null $takeOnly
     * @param bool|null $isHandout
     * @param bool|null $isReception
     */
    public function __construct(
        $resultObj,
        $coreLocation,
        $pointType,
        $haveCashless,
        $haveCash,
        $allowedCod,
        $isDressingRoom,
        $weightMax,
        $weightMin,
        $lang,
        $takeOnly,
        $isHandout,
        $isReception
    )
    {
        parent::__construct($resultObj);
        $this->coreLocation = $coreLocation;

        $this->setRequestObj(new RequestObj());

        $this->getRequestObj()
            ->setType($pointType)
            ->setHaveCashless($haveCashless)
            ->setHaveCash($haveCash)
            ->setAllowedCod($allowedCod)
            ->setIsDressingRoom($isDressingRoom)
            ->setWeightMax($weightMax)
            ->setWeightMin($weightMin)
            ->setLang($lang)
            ->setTakeOnly($takeOnly)
            ->setIsHandout($isHandout)
            ->setIsReception($isReception);
    }

    /**
     * @return string
     */
    public function getSelfHash()
    {
        return $this->getSelfHashByRequestObj() . md5(serialize($this->coreLocation));
    }
    
    public function convert()
    {
        if (is_object($this->coreLocation)) {
            $coreLocation = $this->coreLocation;
            $requestObj = $this->getRequestObj();

            $requestObj
                ->setPostalCode($coreLocation->getZip())
                ->setCityCode($coreLocation->getId()) // CDEK city ID
                ->setCountryCode($coreLocation->getField('countryCode')) // ISO_3166-1_alpha-2
                ->setRegionCode($coreLocation->getField('regionCode')) // CDEK region code
                ->setFiasGuid($coreLocation->getField('cityFiasGuid'));
        }

        return $this;
    }
}