<?

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\IO\File;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Diag\Debug;
use Bitrix\Sale\Internals\BusinessValuePersonDomainTable;

Loc::loadMessages(__FILE__);
class CBoxberry
{
    private const DADATA_API_URL = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';
    private const DADATA_API_KEY = 'Token a105367bc6479ffb2a355fad7536e0fb504c1b97';
    private const BB_OLD_RECEPTION_POINT_NOTIFY_TAG = 'bb_old_select_reception_point';
    public const DEFAULT_API_URL = 'https://api.boxberry.ru/json.php';
    private const LOG_DIRECTORY = '/bitrix/cache/log/';
    private const CACHE_INIT_DIR = '/bb_api_old/';
    public const SOURCE_BOXBERRY = 'BOXBERRY';
    public const SOURCE_YANDEX = 'YANDEX';
    public const FIZ_PAYER_TYPE_ID = 'I';
    public const JUR_PAYER_TYPE_ID = 'E';
    private const LOG_FILE = 'boxberrydelivery.log';
    public static $moduleId = "up.boxberrydelivery";
    public static $apiToken;
    public static $apiUrl;
    private static $httpClient;
    private static $cache;
    private static $responseCode = 0;
    private static $yaDelivery = false;

    public static function initApi($apiToken = '')
    {
        $apiToken = $apiToken ?: self::getApiToken();
        if (!empty($apiToken)) {
            self::$apiToken = $apiToken;
        } else {
            return false;
        }

        self::$apiUrl = self::getApiUrl();
        return true;
    }

    private static function getHttpClient($socketTimeout = 60, $streamTimeout = 60)
    {
        if (!isset(self::$httpClient)) {
            self::$httpClient = new HttpClient(['socketTimeout' => $socketTimeout, 'streamTimeout' => $streamTimeout]);
        }

        return self::$httpClient;
    }

    public static function getApiToken()
    {
        return trim(Option::get(self::$moduleId, 'API_TOKEN', ''));
    }

    public static function getApiUrl()
    {
        return trim(Option::get(self::$moduleId, 'API_URL', self::DEFAULT_API_URL));
    }

    public static function getPayerTypeId($personTypeId)
    {
        if (!class_exists('\Bitrix\Sale\Internals\BusinessValuePersonDomainTable')) {
            return self::FIZ_PAYER_TYPE_ID;
        }

        try {
            $payerType = BusinessValuePersonDomainTable::query()
                ->addSelect('DOMAIN')
                ->where('PERSON_TYPE_ID', $personTypeId)
                ->setLimit(1)
                ->exec()
                ->fetch();

            if (isset($payerType['DOMAIN'])) {
                return $payerType['DOMAIN'];
            }

        } catch (Exception $e) {
        }

        return self::FIZ_PAYER_TYPE_ID;
    }

    /**
     * @return array
     */
    public static function getYaDelivery()
    {
        $companyApiSettings = self::companyApiSettings();
        $result = [
            'yaDelivery' => self::$yaDelivery,
            'authorization' => (self::$responseCode !== 401 && self::$responseCode !== 0),
        ];

        if (empty($companyApiSettings) || isset($companyApiSettings['err'])) {
            return $result;
        }

        if (isset($companyApiSettings['yaDelivery'])) {
            self::$yaDelivery = (bool)$companyApiSettings['yaDelivery'];
            $result['yaDelivery'] = self::$yaDelivery;
        }

        return $result;
    }

    /**
     * @param string $pointCode Код пвз или пункта приема
     * @return bool
     */
    public static function isPointCodeYaDelivery($pointCode)
    {
        return mb_strlen($pointCode) > 20;
    }

    public static function handleUserExport()
    {
        self::initSource();
        $receptionPointCode = self::getReceptionPointCode();

        if (self::isSourceBoxberry()) {
            if (empty($receptionPointCode)) {
                return;
            }

            if (self::isPointCodeYaDelivery($receptionPointCode)) {
                self::resetModuleApiData();
                return;
            }

        }

        if (self::isSourceYandex() && (empty($receptionPointCode) || !self::isPointCodeYaDelivery($receptionPointCode) && !self::isReceptionPointMessageAdded())) {
            self::resetModuleApiData();
            self::addReceptionPointSelectNeededMessage();
        }

    }

    public static function resetModuleApiData($apiToken = '')
    {
        self::cleanCache();
        CitiesTable::truncate();
        CitiesTable::getEntity()->cleanCache();

        if (self::isSourceYandex() && ReceptionPointsTable::getReceptionPointsSource() === self::SOURCE_BOXBERRY) {
            CBoxberryAgents::loadReceptionPoints($apiToken);
        }

        if (self::isSourceBoxberry() && ReceptionPointsTable::getReceptionPointsSource() === self::SOURCE_YANDEX) {
            CBoxberryAgents::loadReceptionPoints($apiToken);
        }

        if (ReceptionPointsTable::isEmpty()) {
            CBoxberryAgents::loadReceptionPoints($apiToken);
        }

        Option::set(self::$moduleId, 'RECEPTION_POINT_CODE', '');
        Option::set(self::$moduleId, 'RECEPTION_POINT_NAME', '');
    }

    public static function getSource()
    {
        return Option::get(self::$moduleId, 'SOURCE', self::SOURCE_BOXBERRY);
    }

    public static function isSourceBoxberry()
    {
        return self::getSource() === self::SOURCE_BOXBERRY;
    }

    public static function isSourceYandex()
    {
        return self::getSource() === self::SOURCE_YANDEX;
    }

    /**
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws ArgumentException
     */
    public static function setSource($source)
    {
        if (!in_array($source, [self::SOURCE_BOXBERRY,  self::SOURCE_YANDEX])) {
            throw new ArgumentException('Invalid source value');
        }

        Option::set(self::$moduleId, 'SOURCE', $source);
    }

    public static function cleanCache()
    {
        $cache = self::getCache();
        $cache->cleanDir(self::CACHE_INIT_DIR);
    }

    private static function getCache()
    {
        if (!isset(self::$cache)) {
            self::$cache = Cache::createInstance();
        }

        return self::$cache;
    }

    public static function initSource()
    {
        self::getYaDelivery();

        if (self::$yaDelivery === true) {
            self::setSource( self::SOURCE_YANDEX);
        } else {
            self::setSource(self::SOURCE_BOXBERRY);
        }
    }

    public static function getReceptionPointCode()
    {
        return Option::get(self::$moduleId, 'RECEPTION_POINT_CODE', '');
    }

    public static function getReceptionPointName()
    {
        return Option::get(self::$moduleId, 'RECEPTION_POINT_NAME', '');
    }

    private static function makeHttpRequest($url, $method = 'POST', $params = [], $headers = [], $cacheTime = 86400)
    {
        $cacheKey = md5($url . $method . serialize($params) . serialize($headers));

        $params = self::convertEncoding($params);

        $cache = self::getCache();

        if ($cache->startDataCache($cacheTime, $cacheKey, self::CACHE_INIT_DIR)) {

            $http = self::getHttpClient();

            if ($method === 'POST') {

                $http->clearHeaders();

                $http->setHeader('Content-Type', 'application/json');

                foreach ($headers as $headerName => $headerValue) {
                    $http->setHeader($headerName, $headerValue);
                }

                if (is_array($params)) {
                    $postData = Json::encode($params, JSON_UNESCAPED_UNICODE);
                } else {
                    $postData = $params;
                }

                $postData = self::convertEncoding($postData);
                $response = $http->post($url, $postData);
            } elseif ($method === 'GET') {
                $url .= '?' . http_build_query($params, '', '&');
                $response = $http->get($url);
            } else {
                return false;
            }

            if ($response) {
                $responseBody = $http->getResult();
                self::$responseCode = $http->getStatus();

                if (self::$responseCode === 200) {
                    $cache->endDataCache($responseBody);
                } else {
                    self::logRequest($url, $method, $params, $headers, $responseBody);

                    return false;
                }
            } else {
                return false;
            }
        } else {
            $responseBody = $cache->getVars();
            self::$responseCode = 200;
        }

        self::logRequest($url, $method, $params, $headers, $responseBody);

        try {
            return Json::decode($responseBody);
        } catch (Throwable $e) {
            return false;
        }
    }

    private static function convertEncoding($data, $targetCharset = 'UTF-8') {
        if (is_array($data)) {
            $convertedData = [];
            foreach ($data as $key => $value) {
                $convertedData[$key] = self::convertEncoding($value, $targetCharset);
            }
        } else {
            $sourceCharset = Encoding::detectUtf8($data) ? 'UTF-8' : 'CP1251';
            $convertedData = Encoding::convertEncoding($data, $sourceCharset, $targetCharset);
        }

        return $convertedData;
    }

    private static function logRequest($url, $method, $params, $headers, $responseBody)
    {
        $logFilePath = Application::getDocumentRoot() . self::LOG_DIRECTORY . self::LOG_FILE;

        if (Option::get(self::$moduleId, 'BB_LOG') === 'Y') {

            $logUrl = self::convertEncoding($url);
            $logMethod = self::convertEncoding($method);
            $logParams = self::convertEncoding($params);
            $logHeaders = self::convertEncoding($headers);
            $logResponseBody = self::convertEncoding($responseBody);

            $logMessage = "URL: $logUrl\n";
            $logMessage .= "Method: $logMethod\n";
            $logMessage .= "Headers: " . print_r($logHeaders, true) . "\n";
            $logMessage .= "Parameters: " . print_r($logParams, true) . "\n";
            $logMessage .= "Response Body: $logResponseBody\n";

            if (!File::isFileExists($logFilePath)) {
                Directory::createDirectory(Application::getDocumentRoot() . self::LOG_DIRECTORY);
            }

            Debug::writeToFile($logMessage, '', substr(self::LOG_DIRECTORY, 1) . self::LOG_FILE);
        } else {
            if (File::isFileExists($logFilePath)) {
                File::deleteFile($logFilePath);
            }
        }
    }

    public static function parselCheck($track)
    {
        $params = [
            'token' => self::$apiToken,
            'method' => 'ParselCheck',
            'ImId' => $track
        ];

        return self::makeHttpRequest(self::$apiUrl, 'GET', $params, [], 0);
    }

    public static function retryGetLabel($track)
    {
        $maxRetries = 5;
        $attempt = 0;
        $delay = 3;

        while ($attempt <= $maxRetries) {
            $result = self::parselCheck($track);
            if (empty($result) || isset($result['err']) || !isset($result['label'])) {
                sleep($delay);
                $attempt++;
            } else {
                return $result;
            }
        }

        return ['err' => 'Max retries reached'];
    }

    public static function retryGetSticker($tracks)
    {
        $maxRetries = 5;
        $attempt = 0;
        $delay = 3;

        while ($attempt <= $maxRetries) {
            $result = self::parselSend($tracks);
            if (empty($result) || isset($result['err']) || !isset($result['sticker'])) {
                sleep($delay);
                $attempt++;
            } else {
                return $result;
            }
        }

        return ['err' => 'Max retries reached'];
    }

    public static function getKeyIntegration()
    {
        $params = [
            'token' => self::$apiToken,
            'method' => 'GetKeyIntegration'
        ];

        return self::makeHttpRequest(self::$apiUrl, 'GET', $params);
    }

    public static function deliveryCalculation($params)
    {
        $disableCache = Option::get(self::$moduleId, 'BB_DISABLE_CALC_CACHE') === 'Y';

        return self::makeHttpRequest(self::$apiUrl, 'POST', $params, [], $disableCache ? 0 : 86400);
    }

    public static function listCitiesFull()
    {
        $params = [
            'token' => self::$apiToken,
            'method' => 'ListCitiesFull',
        ];

        return self::makeHttpRequest(self::$apiUrl, 'GET', $params);
    }

    public static function widgetSettings()
    {
        $params = [
            'token' => self::$apiToken,
            'method' => 'WidgetSettings',
        ];

        return self::makeHttpRequest(self::$apiUrl, 'GET', $params);
    }

    public static function companyApiSettings()
    {
        $params = [
            'token' => self::$apiToken,
            'method' => 'CompanyApiSettings',
        ];

        return self::makeHttpRequest(self::$apiUrl, 'GET', $params, [], 300);
    }

    public static function pointsForParcels()
    {
        $params = [
            'token' => self::$apiToken,
            'method' => 'PointsForParcels',
        ];

        return self::makeHttpRequest(self::$apiUrl, 'GET', $params, [], 3600);
    }

    public static function parselCreate($sdata)
    {
        $params = [
            'token' => self::$apiToken,
            'method' => 'ParselCreate',
            'sdata' => $sdata
        ];

        return self::makeHttpRequest(self::$apiUrl, 'POST', $params, [], 0);
    }

    public static function parselSend($ids)
    {
        $params = [
            'token' => self::$apiToken,
            'method' => 'ParselSend',
            'ImIds' => $ids,
        ];

        return self::makeHttpRequest(self::$apiUrl, 'GET', $params, [], 0);
    }

    public static function callApiDadata($address)
    {
        $params = [
            'query' => $address,
            'locations' => [
                [
                    'country' => '*',
                ]
            ]
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => self::DADATA_API_KEY
        ];

        return self::makeHttpRequest(self::DADATA_API_URL, 'POST', Json::encode($params, JSON_UNESCAPED_UNICODE), $headers);
    }

    public static function saveFilesFromApi($request, $orderId)
    {
        $pdfDir = '/bitrix/pdf/';
        $serverPdfDir = Application::getDocumentRoot() . $pdfDir;
        $pathToPdf = $serverPdfDir . $orderId . '-' . date('d_m_Y-h_i_s') . '.pdf';
        $linkToPdf = $pdfDir . $orderId . '-' . date('d_m_Y-h_i_s') . '.pdf';
        if (!Directory::isDirectoryExists($serverPdfDir)) {
            Directory::createDirectory($serverPdfDir);
        }

        if ((new HttpClient())->download($request, $pathToPdf)){
            return $linkToPdf;
        }

        return '';
    }

    public static function isReceptionPointMessageAdded()
    {
        return CAdminNotify::GetList([], ['TAG' => self::BB_OLD_RECEPTION_POINT_NOTIFY_TAG])->Fetch() !== false;
    }

    public static function addReceptionPointSelectNeededMessage()
    {
        $message = Loc::getMessage('BB_OLD_RECEPTION_POINT_NOTIFY_MESSAGE', [
            '#MODULE_ID#' => self::$moduleId,
            '#LANGUAGE_ID#' => LANGUAGE_ID
        ]);

        CAdminNotify::Add([
                'MESSAGE' => $message,
                'TAG' => self::BB_OLD_RECEPTION_POINT_NOTIFY_TAG,
                'MODULE_ID' => self::$moduleId,
                'ENABLE_CLOSE' => 'Y',
                'NOTIFY_TYPE' => CAdminNotify::TYPE_ERROR,
            ]
        );
    }

    public static function removeReceptionPointSelectNeededMessage()
    {
        CAdminNotify::DeleteByTag(self::BB_OLD_RECEPTION_POINT_NOTIFY_TAG);
    }

    public static function GetFullOrderData($orderId)
    {
        if ((int)$orderId <= 0) {
            return false;
        }

        $order = CSaleOrder::GetByID($orderId);
        $bxbOrderInfo = CBoxberryOrder::GetByOrderId($orderId);
        $order["PVZ_CODE"] = $bxbOrderInfo["PVZ_CODE"];

        if (!$order) {
            return false;
        }

        $dbProps = CSaleOrderPropsValue::GetOrderProps($orderId);
        while ($prop = $dbProps->Fetch()) {
            $order['PROPS'][$prop['ORDER_PROPS_ID']] = $prop;
        }

        $order['ITEMS'] = [];

        $dbBasket = CSaleBasket::GetList(['ID' => 'ASC'], ['ORDER_ID' => $orderId]);
        while ($arItem = $dbBasket->Fetch()) {
            $order['ITEMS'][] = $arItem;
        }

        return $order;
    }

    public static function getPaymentSum($paymentSum, $orderPayedStatus, $profileType)
    {
        if (Option::get(self::$moduleId, 'BB_PAYMENT_AS_PROFILE_TYPE') === 'Y') {
            if (strpos($profileType, '_COD') !== false) {
                return 0;
            } else {
                return $paymentSum;
            }
        }

        if ($orderPayedStatus === 'Y') {
            return 0;
        }

        return $paymentSum;

    }

    public static function makePropsArray($order)
    {
        $arReturn = [];

        $arReturn['VID'] = (($order['DELIVERY_ID'] === 'boxberry:PVZ' || $order['DELIVERY_ID'] === 'boxberry:PVZ_COD') ? 1 : 2);
        $arOptFields = Option::getForModule(self::$moduleId);

        foreach ($arOptFields as $key => $optName) {
            foreach ((array)$order['PROPS'] as $curProp) {
                if ($optName === $curProp['CODE']) {
                    $arReturn[$key] = $curProp["VALUE"];
                }
            }
        }

        return (count($arReturn) > 0 ? $arReturn : false);
    }

    public static function changeAddress ($ORDER_ID=NULL, $address=NULL)
    {
        if (!empty($address)){
            $dbProps = CSaleOrderPropsValue::GetOrderProps($ORDER_ID);
            $address_prop_bb = Option::get(self::$moduleId, 'BB_ADDRESS');
            $address = Encoding::convertEncodingToCurrent($address);


            while($prop = $dbProps->Fetch())
            {
                if ($prop['CODE'] == $address_prop_bb)
                {
                    CSaleOrderPropsValue::Update($prop['ID'], array("ORDER_ID"=>$ORDER_ID, "CODE"=>$prop['CODE'] ,"VALUE"=>$address));
                }
            }
        }

    }

    public static function updateOrder($pvzId, $orderId = null, $address = null)
    {
        $currentDate = date('d.m.Y H:i:s');
        $arFields = [
            'ORDER_ID' => $orderId,
            'PVZ_CODE' => Encoding::convertEncodingToCurrent($pvzId),
            'STATUS_DATE' => $currentDate
        ];

        CBoxberryOrder::Update($orderId, $arFields);
        self::changeAddress($orderId, $address);
        echo true;

    }

    public static function savePvz($pvzId)
    {
        $_SESSION['selPVZ'] = Encoding::convertEncodingToCurrent($pvzId);
    }

    public static function removePvz()
    {
        unset($_SESSION['selPVZ']);
    }

    public static function checkPvz()
    {
        $_SESSION['checkPVZ'] = true;
    }

    public static function disableCheckPvz()
    {
        unset($_SESSION['checkPVZ']);
    }

    public static function addWidgetJs()
    {
        $widgetUrl = trim(Option::get(self::$moduleId, 'WIDGET_URL'));

        if (!$widgetUrl) {
            $widgetUrl = '/bitrix/js/up.boxberrydelivery/boxberry.js';
        }

        Asset::getInstance()->addJs($widgetUrl);
    }

    /**
     * @return mixed
     */
    public static function getResponseCode()
    {
        return self::$responseCode;
    }

    /**
     * @param mixed $responseCode
     */
    public static function setResponseCode($responseCode)
    {
        self::$responseCode = $responseCode;
    }

    public static function isYaDelivery(): bool
    {
        return self::$yaDelivery;
    }

    public static function setYaDelivery(bool $yaDelivery): void
    {
        self::$yaDelivery = $yaDelivery;
    }

    /**
     * @param mixed $apiToken
     */
    public static function setApiToken($apiToken): void
    {
        self::$apiToken = $apiToken;
    }

}

?>