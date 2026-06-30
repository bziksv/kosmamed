<?php
namespace Ipolh\SDEK\SDEK;

use DateTime;
use Exception;
use Ipolh\SDEK\Api\Adapter\CurlAdapter;
use Ipolh\SDEK\Api\BadRequestException;
use Ipolh\SDEK\Api\Entity\EncoderInterface;
use Ipolh\SDEK\Api\Entity\Request\IntakesMake;
use Ipolh\SDEK\Api\Logger\Psr\Log\LoggerInterface;
use Ipolh\SDEK\Api\Sdk;
use Ipolh\SDEK\Core\Delivery\Shipment;
use Ipolh\SDEK\Core\Delivery\Location;
use Ipolh\SDEK\Core\Entity\CacheInterface;
use Ipolh\SDEK\Core\Order\Order;
use Ipolh\SDEK\SDEK\Controller\AutomatedCommonRequest;
use Ipolh\SDEK\SDEK\Controller\AutomatedCommonRequestByUuid;
use Ipolh\SDEK\SDEK\Controller\RequestCalculateList;
use Ipolh\SDEK\SDEK\Controller\RequestCalculateTariff;
use Ipolh\SDEK\SDEK\Controller\RequestCalculateMulti;
use Ipolh\SDEK\SDEK\Controller\RequestController;
use Ipolh\SDEK\SDEK\Controller\RequestDeliveryPoints;
use Ipolh\SDEK\SDEK\Controller\RequestIntakesMake;
use Ipolh\SDEK\SDEK\Controller\RequestLocationCities;
use Ipolh\SDEK\SDEK\Controller\RequestLocationRegions;
use Ipolh\SDEK\SDEK\Controller\RequestOrderMake;
use Ipolh\SDEK\SDEK\Controller\RequestOrderInfoByNumber;
use Ipolh\SDEK\SDEK\Controller\RequestOrderInfoByImNumber;
use Ipolh\SDEK\SDEK\Controller\RequestOrderInfoByNumberMulti;
use Ipolh\SDEK\SDEK\Controller\RequestPrintBarcodesMake;
use Ipolh\SDEK\SDEK\Controller\RequestPrintOrdersMake;
use Ipolh\SDEK\SDEK\Controller\RequestToken;


/**
 * Class SdekApplication
 * @package Ipolh\SDEK\SDEK
 */
class SdekApplication extends GeneralApplication
{
    /**
     * @var string
     */
    protected $account;
    /**
     * @var string
     */
    protected $securePassword;
    /**
     * @var string Auth bearer token
     */
    protected $token = "";
    /**
     * @var bool
     * Indicates if the method was already called (for recurrent calls for dead jwt)
     */
    protected $recursionFlag = false;

    /**
     * SdekApplication constructor.
     * @param string $clientAccount
     * @param string $clientSecurePassword
     * @param false $isTest
     * @param int $timeout
     * @param EncoderInterface|null $encoder
     * @param CacheInterface|null $cache
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        $clientAccount,
        $clientSecurePassword,
        $isTest = false,
        $timeout = 15,
        $encoder = null,
        $cache = null,
        $logger = null
    ) {
        $this->setAccount($clientAccount)
            ->setSecurePassword($clientSecurePassword)
            ->setTestMode($isTest)
            ->setTimeout($timeout)
            ->setEncoder($encoder)
            ->setCache($cache)
            ->setLogger($logger);

        $this->abyss = array();
        $this->errorCollection = new ExceptionCollection();

        try {
            $this->getToken();
        } catch (Exception $e) {
            $this->addError($e);
        }
    }

    /**
     * @param AutomatedCommonRequest|mixed $controller
     * @param bool $useCache
     * @param int $cacheTTL
     * @return Entity\AbstractResult|mixed
     */
    private function genericCall($controller, $useCache = false, $cacheTTL = 3600)
    {
        $resultObj = $controller->getResultObject();
        $this->setHash($controller->getSelfHash());
        if ($this->checkAbyss()) {
            $this->lastRequestType = 'abyss';
            return $this->abyss[$this->getHash()];
        } else {
            if ($useCache && $this->getCache() && $this->getCache()->setLife($cacheTTL)->checkCache($this->getHash())) {
                $this->lastRequestType = 'cache';
                return $this->getCache()->getCache($this->getHash());
            } else {
                $this->lastRequestType = 'direct';

                try {
                    $this->configureController($controller);
                } catch (Exception $e) {
                    $this->addError($e);
                    return $resultObj;
                }
                $controller->convert()
                    ->execute();

                if ($resultObj->getError()) {
                    if (($resultObj->getError()->getCode() == 401) && !$this->recursionFlag) {
                        $this->setToken("");
                        $this->recursionFlag = true; //blocking further recursive calls
                        try {
                            $this->getToken(true); //forcing token-request
                        } catch (AppLevelException $e) {
                            $this->addError($e);
                            return $resultObj;
                        }
                        return $this->genericCall($controller, $useCache, $cacheTTL);
                    } else {
                        $this->addError($resultObj->getError());
                    }
                } else {
                    $this->toAbyss($resultObj);
                    if ($useCache) {
                        $this->toCache($resultObj, $this->getHash());
                    }
                }
            }
        }
        return $resultObj;
    }

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @return Entity\TokenResult
     */
    protected function requestToken($clientId, $clientSecret)
    {
        $this->lastRequestType = 'token direct';

        try {
            $controller = new RequestToken($clientId, $clientSecret);
            $adapter = new CurlAdapter($this->getTimeout());
            if ($this->getLogger()) {
                $adapter->setLog($this->getLogger());
            }
            $mode = $this->testMode ? 'TEST' : 'API';
            $sdk = new Sdk($adapter, '', $this->getEncoder(), $mode, $this->customAllowed);
            return $controller->setSdk($sdk)->execute();
        } catch (BadRequestException $e) {
            $this->addError($e);
            $return = new Entity\TokenResult();
            $return->setSuccess(false);
            return $return;
        } catch (Exception $e) {
            $this->addError($e);
            $return = new Entity\TokenResult();
            $return->setSuccess(false);
            return $return;
        }
    }

    /*-----------------------------------------------------------------------*/

    /**
     * @param Shipment $shipment
     * @param DateTime|null $date - planned time of  departure
     * @param string|null $lang - lang for delivery info in response rus|eng|zho
     * @param int|null $currency
     * @param int|null $deliveryType - 1 = E-Shop 2 = regular shipping
     * @return Entity\CalculateListResult
     */
    public function calculateList(
        $shipment,
        $date = null,
        $lang = null,
        $currency = null,
        $deliveryType = null
    )
    {
        $controller = new RequestCalculateList(
            new Entity\CalculateListResult(),
            $shipment,
            $date,
            $lang,
            $currency,
            $deliveryType
        );
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller, true);
    }

    /**
     * @param Shipment $shipment
     * @param int $tariff_code - CDEK tariff number
     * @param DateTime|null $date - planned time of departure
     * @param int|null $type - 1 = E-Shop 2 = regular shipping
     * @param int|null $currency
     * @return Entity\CalculateTariffResult
     */
    public function calculateTariff(
        $shipment,
        $tariff_code,
        $date = null,
        $type = null,
        $currency = null
    )
    {
        $controller = new RequestCalculateTariff(
            new Entity\CalculateTariffResult(),
            $shipment,
            $tariff_code,
            $date,
            $type,
            $currency
        );
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller, true);
    }

    /**
     * @param Shipment $shipment
     * @param int[] $tariff_codes - CDEK tariff numbers
     * @param DateTime|null $date - planned time of departure
     * @param int|null $type - 1 = E-Shop 2 = regular shipping
     * @param int|null $currency
     * @return Entity\CalculateMultiResult
     */
    public function calculateMulti(
        $shipment,
        $tariff_codes,
        $date = null,
        $type = null,
        $currency = null
    )
    {
        $controller = new RequestCalculateMulti(
            new Entity\CalculateMultiResult(),
            $shipment,
            $tariff_codes,
            $date,
            $type,
            $currency
        );
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller, true);
    }

    /*-----------------------------------------------------------------------*/

    /**
     * @param Location|null $coreLocation // Some location-based filters
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
     * @return Entity\DeliveryPointsResult
     */
    public function deliveryPoints(
        $coreLocation = null,
        $pointType = 'ALL',
        $haveCashless = null,
        $haveCash = null,
        $allowedCod = null,
        $isDressingRoom = null,
        $weightMax = null,
        $weightMin = null,
        $lang = null,
        $takeOnly = null,
        $isHandout = null,
        $isReception = null
    )
    {
        $controller = new RequestDeliveryPoints(
            new Entity\DeliveryPointsResult(),
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
        );
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string|null $country_codes ISO_3166-1_alpha-2 codes like 'RU' or 'RU,BY,KZ'
     * @param int|null $size required if $page set. CDEK default is 500
     * @param int|null $page CDEK default is 0
     * @param string|null $lang CDEK default is 'rus'
     * @param int|null $code CDEK city code
     * @param int|null $region_code
     * @param string|null $kladr_region_code
     * @param string|null $fias_region_guid
     * @param string|null $kladr_code
     * @param string|null $fias_guid
     * @param string|null $postal_code
     * @param string|null $city
     * @param float|null $payment_limit
     * @return Entity\LocationCitiesResult
     */
    public function locationCities(
        $country_codes = 'RU',
        $size = null,
        $page = null,
        $lang = null,
        $code = null,
        $region_code = null,
        $kladr_region_code = null,
        $fias_region_guid = null,
        $kladr_code = null,
        $fias_guid = null,
        $postal_code = null,
        $city = null,
        $payment_limit = null
    )
    {
        $controller = new RequestLocationCities(
            new Entity\LocationCitiesResult(),
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
        );
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string|null $country_codes ISO_3166-1_alpha-2 codes like 'RU' or 'RU,BY,KZ'
     * @param int|null $region_code
     * @param string|null $kladr_region_code
     * @param string|null $fias_region_guid
     * @param int|null $size required if $page set. CDEK default is 1000
     * @param int|null $page CDEK default is 0
     * @param string|null $lang CDEK default is 'rus'
     * @return Entity\LocationRegionsResult
     */
    public function locationRegions(
        $country_codes = 'RU',
        $region_code = null,
        $kladr_region_code = null,
        $fias_region_guid = null,
        $size = null,
        $page = null,
        $lang = null
    )
    {
        $controller = new RequestLocationRegions(
            new Entity\LocationRegionsResult(),
            $country_codes,
            $region_code,
            $kladr_region_code,
            $fias_region_guid,
            $size,
            $page,
            $lang
        );
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param Order $order
     * @param int|null $type 1 - IM (default) | 2 - regular shipping
     * @param string|null $developerKey
     * @return Entity\OrderMakeResult
     */
    public function orderMake($order, $type = null, $developerKey = null)
    {
        $controller = new RequestOrderMake(new Entity\OrderMakeResult(), $order, $type, $developerKey);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string $sdekOrderUuid - uuid of order on SDEK server
     * @return Entity\OrderInfoResult
     */
    public function orderInfoByUuid($sdekOrderUuid)
    {
        $controller = new AutomatedCommonRequestByUuid(new Entity\OrderInfoResult(), $sdekOrderUuid);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string $sdekOrderNum - SDEK order number
     * @return Entity\OrderInfoResult
     */
    public function orderInfoByNumber($sdekOrderNum)
    {
        $controller = new RequestOrderInfoByNumber(new Entity\OrderInfoResult(), $sdekOrderNum);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string $imOrderNumber - CMS order number
     * @return Entity\OrderInfoResult
     */
    public function orderInfoByImNumber($imOrderNumber)
    {
        $controller = new RequestOrderInfoByImNumber(new Entity\OrderInfoResult(), $imOrderNumber);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string[] $cdek_numbers - CDEK order numbers
     * @return Entity\OrderInfoMultiResult
     */
    public function orderInfoByNumberMulti($cdek_numbers)
    {
        $controller = new RequestOrderInfoByNumberMulti(new Entity\OrderInfoMultiResult(), $cdek_numbers);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string $sdekOrderUuid - uuid of order on SDEK server
     * @return Entity\OrderDeleteResult
     */
    public function orderDelete($sdekOrderUuid)
    {
        $controller = new AutomatedCommonRequestByUuid(new Entity\OrderDeleteResult(), $sdekOrderUuid);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string[]|null $uuids orders UUIDs (required if no CDEK numbers given)
     * @param int[]|null $cdekNumbers orders CDEK numbers (required if no UUIDs given)
     * @param int|null $copyCount default is 2
     * @param string|null $type 'tpl_china' | 'tpl_armenia'
     *
     * @return Entity\PrintOrdersMakeResult
     */
    public function printOrdersMake($uuids = null, $cdekNumbers = null, $copyCount = null, $type = null)
    {
        $controller = new RequestPrintOrdersMake(new Entity\PrintOrdersMakeResult(), $uuids, $cdekNumbers, $copyCount, $type);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string $printOrderUuid UUID of print order request, use printOrdersMake() result to get it
     * @return Entity\PrintOrdersInfoResult
     */
    public function printOrdersInfo($printOrderUuid)
    {
        $controller = new AutomatedCommonRequestByUuid(new Entity\PrintOrdersInfoResult(), $printOrderUuid);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string[]|null $uuids orders UUIDs (required if no CDEK numbers given)
     * @param int[]|null $cdekNumbers orders CDEK numbers (required if no UUIDs given)
     * @param int|null $copyCount default is 1
     * @param string|null $format 'A4' | 'A5' | 'A6'
     * @param string|null $lang ISO 639-3 - 'RUS' | 'ENG'
     * @return Entity\PrintBarcodesMakeResult
     */
    public function printBarcodesMake($uuids = null, $cdekNumbers = null, $copyCount = null, $format = null, $lang = null)
    {
        $controller = new RequestPrintBarcodesMake(new Entity\PrintBarcodesMakeResult(), $uuids, $cdekNumbers, $copyCount, $format, $lang);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string $printBarcodeUuid UUID of print barcode request, use printBarcodesMake() result to get it
     * @return Entity\PrintBarcodesInfoResult
     */
    public function printBarcodesInfo($printBarcodeUuid)
    {
        $controller = new AutomatedCommonRequestByUuid(new Entity\PrintBarcodesInfoResult(), $printBarcodeUuid);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param IntakesMake $request
     * @return Entity\IntakesMakeResult
     */
    public function intakesMake($request)
    {
        $controller = new RequestIntakesMake(new Entity\IntakesMakeResult(), $request);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string $sdekOrderUuid - uuid of order on SDEK server
     * @return Entity\IntakesInfoResult
     */
    public function intakesInfo($sdekOrderUuid)
    {
        $controller = new AutomatedCommonRequestByUuid(new Entity\IntakesInfoResult(), $sdekOrderUuid);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /**
     * @param string $sdekOrderUuid - uuid of order on SDEK server
     * @return Entity\IntakesDeleteResult
     */
    public function intakesDelete($sdekOrderUuid)
    {
        $controller = new AutomatedCommonRequestByUuid(new Entity\IntakesDeleteResult(), $sdekOrderUuid);
        $controller->setSdkMethodName(__FUNCTION__);
        return $this->genericCall($controller);
    }

    /*-----------------------------------------------------------------------*/

    /**
     * @param RequestController $controller
     * sets sdk
     * @throws Exception
     */
    protected function configureController($controller)
    {
        $controller->setSdk($this->getSdk());
    }

    /**
     * @return Sdk
     * get the sdk-controller
     * ! timeout sets only here: later it wouldn't be changed !
     * @throws Exception
     */
    public function getSdk()
    {
        $mode = $this->testMode ? 'TEST' : 'API';
        $adapter = new CurlAdapter($this->getTimeout());
        if ($this->getLogger()) {
            $adapter->setLog($this->getLogger());
        }

        return new Sdk($adapter, $this->getToken(), $this->getEncoder(), $mode, $this->customAllowed);
    }

    /**
     * @return string
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param string $account
     * @return $this
     */
    private function setAccount($account)
    {
        $this->account = $account;
        return $this;
    }

    /**
     * @return string
     */
    public function getSecurePassword()
    {
        return $this->securePassword;
    }

    /**
     * @param string $securePassword
     * @return $this
     */
    private function setSecurePassword($securePassword)
    {
        $this->securePassword = $securePassword;
        return $this;
    }

    /**
     * @param bool $force
     * @return string
     * @throws AppLevelException
     */
    public function getToken($force = false)
    {
        if (!$force && $this->token) {
            return $this->token;
        }

        if (!$force && $this->getCache() && $this->getCache()->setLife(24*3600)->checkCache(md5('tokenTtl'))) {
            $tokenTtl = $this->getCache()->getCache(md5('tokenTtl'));
            if ($this->getCache()->setLife($tokenTtl)->checkCache(md5($this->getAccount() . 'token'))) {
                $this->setToken($this->getCache()->getCache(md5($this->getAccount() . 'token')));
                return $this->token;
            }
        }

        //if it's forced token request, or token yet not set in Application-object nor in Cache
        $newToken = $this->requestToken($this->getAccount(), $this->getSecurePassword());
        if (!$newToken->isSuccess()) {
            throw new AppLevelException("Fail to get token!");
        } else {
            $this->setToken($newToken->getAccessToken());
            if ($this->getCache()) {
                $tokenTtl = $newToken->getExpiresIn() - 1;
                $this->getCache()->setCache(md5('tokenTtl'), $tokenTtl);
                $this->getCache()->setLife($tokenTtl)->setCache(md5($this->getAccount() . 'token'), $this->token);
            }
            return $this->token;
        }
    }

    /**
     * @param string $token
     * @return SdekApplication
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }
}