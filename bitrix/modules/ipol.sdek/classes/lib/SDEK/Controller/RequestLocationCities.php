<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\Entity\Request\LocationCities as RequestObj;
use Ipolh\SDEK\SDEK\Entity\LocationCitiesResult as ResultObj;

/**
 * Class RequestLocationCities
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestLocationCities extends AutomatedCommonRequest
{
    /**
     * RequestLocationCities constructor.
     * @param ResultObj $resultObj
     * @param string|null $country_codes ISO_3166-1_alpha-2 codes like 'RU' or 'RU,BY,KZ'
     * @param int|null $region_code
     * @param string|null $kladr_region_code
     * @param string|null $fias_region_guid
     * @param string|null $kladr_code
     * @param string|null $fias_guid
     * @param string|null $postal_code
     * @param int|null $code
     * @param string|null $city
     * @param int|null $size required if $page set
     * @param int|null $page
     * @param string|null $lang
     * @param float|null $payment_limit
     */
    public function __construct(
        $resultObj,
        $country_codes,
        $region_code,
        $kladr_region_code,
        $fias_region_guid,
        $kladr_code,
        $fias_guid,
        $postal_code,
        $code,
        $city,
        $size,
        $page,
        $lang,
        $payment_limit
    )
    {
        parent::__construct($resultObj);
        $this->setRequestObj(new RequestObj());

        $this->getRequestObj()
            ->setCountryCodes($country_codes)
            ->setRegionCode($region_code)
            ->setKladrRegionCode($kladr_region_code)
            ->setFiasRegionGuid($fias_region_guid)
            ->setKladrCode($kladr_code)
            ->setFiasGuid($fias_guid)
            ->setPostalCode($postal_code)
            ->setCode($code)
            ->setCity($city)
            ->setSize($size)
            ->setPage($page)
            ->setLang($lang)
            ->setPaymentLimit($payment_limit);
    }
}