<?php
namespace Ipolh\SDEK\Api;

use \Exception;
use Ipolh\SDEK\Api\Adapter\AdapterInterface;
use Ipolh\SDEK\Api\Adapter\CurlAdapter;
use Ipolh\SDEK\Api\Entity\EncoderInterface;
use Ipolh\SDEK\Api\Methods\GeneralMethod as GeneralMethodAlias;
use Ipolh\SDEK\Api\Methods\GeneralUrlImplementedMethod;
use Ipolh\SDEK\Api\Methods\Oauth;

/**
 * Class Sdk
 * @package Ipolh\SDEK\Api
 */
class Sdk
{
    /**
     * @var CurlAdapter
     */
    private $adapter;
    /**
     * @var EncoderInterface|null
     */
    private $encoder;
    /**
     * @var array
     */
    protected $map;

    /**
     * Sdk constructor.
     * @param CurlAdapter $adapter
     * @param string $token
     * @param EncoderInterface|null $encoder
     * @param string $mode
     * @param bool $custom
     */
    public function __construct(
        CurlAdapter $adapter,
        $token = '',
        $encoder = null,
        $mode = 'API',
        $custom = false)
    {
        $this->adapter = $adapter;
        $this->encoder = $encoder;
        $this->map = $this->getMap($mode, $custom);

        if ($token) {
            $this->adapter->appendHeaders(['Authorization: Bearer ' . $token]);
        }
    }

    /**
     * @param string $mode
     * @param bool $custom
     * @return array
     */
    protected function getMap($mode, $custom)
    {
        $api = 'https://api.cdek.ru/v2';
        $test = 'https://api.edu.cdek.ru/v2';

        $arMap = [
//----------Auth-----------------------------------------------------------------------------
            'oauth' => [
                'API' => $api.'/oauth/token',
                'TEST' => $test.'/oauth/token',
                'REQUEST_TYPE' => 'POST_GET'
            ],
//----------Calculator-----------------------------------------------------------------------------
            'calculateTariff' => [
                'API' => $api.'/calculator/tariff',
                'TEST' => $test.'/calculator/tariff',
                'REQUEST_TYPE' => 'POST'
            ],
            'calculateList' => [
                'API' => $api.'/calculator/tarifflist',
                'TEST' => $test.'/calculator/tarifflist',
                'REQUEST_TYPE' => 'POST'
            ],
            'calculateMulti' => [
                'API' => $api.'/calculator/tariff',
                'TEST' => $test.'/calculator/tariff',
                'REQUEST_TYPE' => 'POST_MULTI'
            ],
//----------Order-----------------------------------------------------------------------------
            'orderMake' => [
                'API' => $api.'/orders',
                'TEST' => $test.'/orders',
                'REQUEST_TYPE' => 'POST'
            ],
            'orderInfoByUuid' => [
                'API' => $api.'/orders/', // https://api.cdek.ru/v2/orders/72753033-1cf5-447c-a420-c29f4b488ac6
                'TEST' => $test.'/orders/',
                'REQUEST_TYPE' => 'GET'
            ],
            'orderInfoByNumber' => [
                'API' => $api.'/orders', // https://api.cdek.ru/v2/orders?cdek_number=1106207812
                'TEST' => $test.'/orders',
                'REQUEST_TYPE' => 'GET'
            ],
            'orderInfoByImNumber' => [
                'API' => $api.'/orders', // https://api.cdek.ru/v2/orders?im_number=00004792842619
                'TEST' => $test.'/orders',
                'REQUEST_TYPE' => 'GET'
            ],
            'orderInfoByNumberMulti' => [
                'API' => $api.'/orders', // https://api.cdek.ru/v2/orders?cdek_number=1106207812
                'TEST' => $test.'/orders',
                'REQUEST_TYPE' => 'GET_MULTI'
            ],
            'orderDelete' => [
                'API' => $api.'/orders/', // https://api.cdek.ru/v2/orders/72753031-826d-4ef7-b127-1074f405b269
                'TEST' => $test.'/orders/',
                'REQUEST_TYPE' => 'DELETE'
            ],
//----------Documents-----------------------------------------------------------------------------
            'printOrdersMake' => [
                'API' => $api.'/print/orders',
                'TEST' => $test.'/print/orders',
                'REQUEST_TYPE' => 'POST'
            ],
            'printOrdersInfo' => [
                'API' => $api.'/print/orders/', // https://api.cdek.ru/v2/print/orders/72753034-4b28-40af-a89e-fc2e18935307
                'TEST' => $test.'/print/orders/',
                'REQUEST_TYPE' => 'GET'
            ],
            'printBarcodesMake' => [
                'API' => $api.'/print/barcodes',
                'TEST' => $test.'/print/barcodes',
                'REQUEST_TYPE' => 'POST'
            ],
            'printBarcodesInfo' => [
                'API' => $api.'/print/barcodes/', // https://api.cdek.ru/v2/print/barcodes/72753034-c617-46ef-b70a-8f5f520b6be4
                'TEST' => $test.'/print/barcodes/',
                'REQUEST_TYPE' => 'GET'
            ],
//----------Courier call (Intakes)-----------------------------------------------------------------
            'intakesMake' => [
                'API' => $api.'/intakes',
                'TEST' => $test.'/intakes',
                'REQUEST_TYPE' => 'POST'
            ],
            'intakesInfo' => [
                'API' => $api.'/intakes/', // https://api.cdek.ru/v2/intakes/72753031-0525-4aa4-9629-d6ae52e825f5
                'TEST' => $test.'/intakes/',
                'REQUEST_TYPE' => 'GET'
            ],
            'intakesDelete' => [
                'API' => $api.'/intakes/', // https://api.cdek.ru/v2/intakes/72753031-0525-4aa4-9629-d6ae52e825f5
                'TEST' => $test.'/intakes/',
                'REQUEST_TYPE' => 'DELETE'
            ],
//----------References-----------------------------------------------------------------------------
            'deliveryPoints' => [
                'API' => $api . '/deliverypoints',
                'TEST' => $test . '/deliverypoints',
                'REQUEST_TYPE' => 'GET'
            ],
            'locationRegions' => [
                'API' => $api . '/location/regions',
                'TEST' => $test . '/location/regions',
                'REQUEST_TYPE' => 'GET'
            ],
            'locationCities' => [
                'API' => $api . '/location/cities',
                'TEST' => $test . '/location/cities',
                'REQUEST_TYPE' => 'GET'
            ],
//-------------------------------------------------------------------------------------------------
        ];
        if (defined('IPOL_SDEK_CUSTOM_MAP') && is_array(IPOL_SDEK_CUSTOM_MAP)) {
            foreach (IPOL_SDEK_CUSTOM_MAP as $method => $url) {
                $arMap[$method]['CUSTOM'] = $url;
            }
        }

        if ($mode != 'TEST' && $mode != 'API') {
            throw new Exception('Unknown Api-map configuring mode');
        }

        $arReturn = array();
        foreach ($arMap as $method => $arData) {
            if ($custom && isset($arData['CUSTOM'])) {
                $url = $arData['CUSTOM'];
            } else {
                $url = $arData[$mode];
            }

            $arReturn[$method] = array(
                'URL' => $url,
                'REQUEST_TYPE' => $arData['REQUEST_TYPE']
            );
        }
        return $arReturn;
    }

    /**
     * @param string $method name of method in api-map
     */
    protected function configureRequest($method)
    {
        if (array_key_exists($method, $this->map)) {
            $url = $this->map[$method]['URL'];
            $type = $this->map[$method]['REQUEST_TYPE'];
        } else {
            throw new Exception('Requested method "'.$method.'" not found in module map!');
        }

        $this->adapter->setMethod($method);
        $this->adapter->setUrl($url);
        $this->adapter->setRequestType($type);
    }

    /**
     * @param Entity\Request\Oauth $data
     * @return Oauth
     * @throws BadResponseException
     */
    public function oauth(Entity\Request\Oauth $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new Oauth($data, $this->adapter, $this->encoder);
    }

    /**
     * @param Entity\Request\CalculateTariff $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function calculateTariff(Entity\Request\CalculateTariff $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\CalculateTariff::class, $this->encoder);
    }

    /**
     * @param Entity\Request\CalculateList $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function calculateList(Entity\Request\CalculateList $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\CalculateList::class, $this->encoder);
    }

    /**
     * @param Entity\Request\CalculateMulti $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function calculateMulti(Entity\Request\CalculateMulti $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\CalculateMulti::class, $this->encoder);
    }

    /**
     * @param Entity\Request\OrderMake $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function orderMake(Entity\Request\OrderMake $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\OrderMake::class, $this->encoder);
    }

    /**
     * @param string $uuid
     * @return GeneralUrlImplementedMethod
     * @throws BadResponseException
     */
    public function orderInfoByUuid($uuid)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralUrlImplementedMethod($uuid, $this->adapter, Entity\Response\OrderInfo::class, $this->encoder);
    }

    /**
     * @param Entity\Request\OrderInfoNumber $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function orderInfoByNumber(Entity\Request\OrderInfoNumber $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\OrderInfo::class, $this->encoder);
    }

    /**
     * @param Entity\Request\OrderInfoByImNumber $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function orderInfoByImNumber(Entity\Request\OrderInfoByImNumber $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\OrderInfo::class, $this->encoder);
    }

    /**
     * @param Entity\Request\OrderInfoNumberMulti $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function orderInfoByNumberMulti(Entity\Request\OrderInfoNumberMulti $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\OrderInfoMulti::class, $this->encoder);
    }

    /**
     * @param string $uuid
     * @return GeneralUrlImplementedMethod
     * @throws BadResponseException
     */
    public function orderDelete($uuid)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralUrlImplementedMethod($uuid, $this->adapter, Entity\Response\OrderDelete::class, $this->encoder);
    }

    /**
     * @param Entity\Request\PrintOrdersMake $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function printOrdersMake(Entity\Request\PrintOrdersMake $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\PrintOrdersMake::class, $this->encoder);
    }

    /**
     * @param string $uuid
     * @return GeneralUrlImplementedMethod
     * @throws BadResponseException
     */
    public function printOrdersInfo($uuid)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralUrlImplementedMethod($uuid, $this->adapter, Entity\Response\PrintOrdersInfo::class, $this->encoder);
    }

    /**
     * @param Entity\Request\PrintBarcodesMake $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function printBarcodesMake(Entity\Request\PrintBarcodesMake $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\PrintBarcodesMake::class, $this->encoder);
    }

    /**
     * @param string $uuid
     * @return GeneralUrlImplementedMethod
     * @throws BadResponseException
     */
    public function printBarcodesInfo($uuid)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralUrlImplementedMethod($uuid, $this->adapter, Entity\Response\PrintBarcodesInfo::class, $this->encoder);
    }

    /**
     * @param Entity\Request\IntakesMake $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function intakesMake(Entity\Request\IntakesMake $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\IntakesMake::class, $this->encoder);
    }

    /**
     * @param string $uuid
     * @return GeneralUrlImplementedMethod
     * @throws BadResponseException
     */
    public function intakesInfo($uuid)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralUrlImplementedMethod($uuid, $this->adapter,Entity\Response\IntakesInfo::class, $this->encoder);
    }

    /**
     * @param string $uuid
     * @return GeneralUrlImplementedMethod
     * @throws BadResponseException
     */
    public function intakesDelete($uuid)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralUrlImplementedMethod($uuid, $this->adapter, Entity\Response\IntakesDelete::class, $this->encoder);
    }

    /**
     * @param Entity\Request\DeliveryPoints $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function deliveryPoints(Entity\Request\DeliveryPoints $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\DeliveryPoints::class, $this->encoder);
    }

    /**
     * @param Entity\Request\LocationRegions $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function locationRegions(Entity\Request\LocationRegions $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\LocationRegions::class, $this->encoder);
    }

    /**
     * @param Entity\Request\LocationCities $data
     * @return GeneralMethodAlias
     * @throws BadResponseException
     */
    public function locationCities(Entity\Request\LocationCities $data)
    {
        $this->configureRequest(__FUNCTION__);
        return new GeneralMethodAlias($data, $this->adapter, Entity\Response\LocationCities::class, $this->encoder);
    }
}