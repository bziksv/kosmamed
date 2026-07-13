<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\Entity\Request\LocationRegions as RequestObj;
use Ipolh\SDEK\SDEK\Entity\LocationRegionsResult as ResultObj;

/**
 * Class RequestLocationRegions
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestLocationRegions extends AutomatedCommonRequest
{
    /**
     * RequestLocationRegions constructor.
     * @param ResultObj $resultObj
     * @param string|null $country_codes ISO_3166-1_alpha-2 codes like 'RU' or 'RU,BY,KZ'
     * @param int|null $region_code
     * @param string|null $kladr_region_code
     * @param string|null $fias_region_guid
     * @param int|null $size required if $page set
     * @param int|null $page
     * @param string|null $lang
     */
    public function __construct(
        $resultObj,
        $country_codes,
        $region_code,
        $kladr_region_code,
        $fias_region_guid,
        $size,
        $page,
        $lang
    )
    {
        parent::__construct($resultObj);
        $this->setRequestObj(new RequestObj());

        $this->getRequestObj()
            ->setCountryCodes($country_codes)
            ->setRegionCode($region_code)
            ->setKladrRegionCode($kladr_region_code)
            ->setFiasRegionGuid($fias_region_guid)
            ->setSize($size)
            ->setPage($page)
            ->setLang($lang);
    }
}