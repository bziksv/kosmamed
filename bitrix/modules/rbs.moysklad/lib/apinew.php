<?php
namespace Rbs\Moysklad;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Data\Cache;

use Rbs\Moysklad\Utils;

class ApiNew
{
    private static $counterRequests = 0;
    private static $startTimeRequests = 0;

    private static $endPoint = 'https://api.moysklad.ru/api/remap/1.2';
    private static $endPointOld = 'https://online.moysklad.ru/api/remap/1.2';

    public static function getApiEndPointUrl(): string
    {
        return self::$endPoint;
    }

    public static function isAvailableUrl(string $url): bool
    {
        return mb_strpos($url, self::$endPoint) === 0;
    }

    public static function replaceOldDomain(string $url): string
    {
        $result = $url;
        if(mb_strpos($url, self::$endPointOld) !== false) {
            $result = str_replace(self::$endPointOld, self::$endPoint, $url);
        }
        return $result;
    }

    private static function getEntity($request = '')
    {
        return explode('/', str_replace(self::getApiEndPointUrl(), '', $request))[2];
    }

    public static function get($entity = '', $query = [], $cacheTTL = 0)
    {
        if ((int)$cacheTTL > 0) {

            $obCache = Cache::createInstance();
            $cacheId = md5(serialize([$entity, $query, Config::getModuleId(), Config::getApiCacheId(), 'v1']));

            $cacheEntityPath = self::getEntity($entity);
            if ($obCache->initCache((int)$cacheTTL, $cacheId, Config::getCachePath('get/' . $cacheEntityPath))) {

                $vars = $obCache->GetVars();
                $result = $vars['result'];
                
            } elseif ($obCache->startDataCache()) {

                $result = self::baseQuery('GET', $entity, $query);

                if (
                    Utils::has_errors($result) ||
                    (Utils::property_exists($result, ['rows']) &&
                        !Utils::is_count($result->rows)
                    ) || (is_array($result) && count($result) <= 0
                    )
                ) {
                    $obCache->abortDataCache();
                } else {
                    $obCache->endDataCache(array('result' => $result));
                }

            }

            return $result;

        } else {

            return self::baseQuery('GET', $entity, $query);

        }
    }

    public static function createGetCache($entity = '', $query = [], $cacheTTL = 0, $result = [])
    {
        $obCache = Cache::createInstance();
        $cacheId = md5(serialize([$entity, $query, Config::getModuleId(), Config::getApiCacheId()]));

        $cacheEntityPath = self::getEntity($entity);
        if(!$obCache->initCache((int)$cacheTTL, $cacheId, Config::getCachePath('get/' . $cacheEntityPath))) {
            if($obCache->startDataCache()){
                $obCache->endDataCache(array('result' => $result));
            }
        }
    }

    public static function post($entity = '', $query = [])
    {
        return self::baseQuery('POST', $entity, $query);
    }

    public static function put($entity = '', $query = [])
    {
        return self::baseQuery('PUT', $entity, $query);
    }

    public static function delete($entity = '', $query = [])
    {
        return self::baseQuery('DELETE', $entity, $query);
    }

    public static function refreshCountRequests()
    {
        self::$counterRequests = 0;
    }

    public static function getCountRequests()
    {
        $count = self::$counterRequests;
        self::$counterRequests = 0;
        return $count;
    }

    public static function getTimeRequests()
    {
        $time = self::$startTimeRequests;
        self::$startTimeRequests = 0;
        return time() - $time;
    }

    private static function baseQuery($type, $entity, $query, $attempt = 0)
    {
        if (self::isLockApi()) {
            return (object)[
                'isNull' => true,
                'hasErrors' => true,
                'errors' => [
                    '5' => 'Api limits error. Unblock after 5 sec.'
                ]
            ];
        }

        if ($httpClient = self::getHttpClient()) {

            if(self::$startTimeRequests <= 0){
                self::$startTimeRequests = time();
            }

            $entity = self::replaceOldDomain($entity);

            $endPointPath = self::getApiEndPointUrl();
            $endPoint = $endPointPath . str_replace($endPointPath, '', $entity);
            
            $response = false;

            $isUtfMode = \Bitrix\Main\Application::isUtfMode();

            switch ($type) {
                case 'GET':
                    if (!empty($query)) {
                        $queryStr = '?';
                        foreach ($query as $k => $v) {
                            $queryValue = $isUtfMode ? urlencode($v) : urlencode(mb_convert_encoding($v, 'UTF-8', 'Windows-1251'));
                            $queryStr .= $k . '=' . $queryValue . '&';
                        }
                        if(substr($queryStr, -1) === '&'){
                            $queryStr = substr($queryStr, 0, -1);
                        }
                    }
                    $endPoint .=  $queryStr;
                    $response = $httpClient->get($endPoint);
                break;
                case 'POST':
                    $response = $httpClient->post($endPoint, json_encode($query));
                break;
                case 'PUT':
                    $response = $httpClient->query('PUT', $endPoint, json_encode($query));
                break;
                case 'DELETE':
                    $response = $httpClient->query('DELETE', $endPoint, json_encode($query));
                break;
                default:
                    $response = $httpClient->post($endPoint, json_encode($query));
            }

            self::$counterRequests++;

            $returnResult = json_decode($response);
            if (!$isUtfMode && !is_null($returnResult)) {
                $isSourceResultArray = is_array($returnResult);
                $returnResult = \Bitrix\Main\Web\Json::decode($response);
                $returnResult = $isSourceResultArray ? (array)Utils::array_to_object($returnResult) : Utils::array_to_object($returnResult);
            }
            
            if(is_null($returnResult)){

                $returnResult = (object)[
                    'isNull' => true,
                    'hasErrors' => true,
                    'errors' => [
                        LangMsg::get('API_ERROR_NULL')
                    ]
                ];

            } else {

                if(is_object($returnResult)){

                    if (Utils::array_exists($returnResult, 'errors')) {

                        $returnResult->hasErrors = true;
                        $errorArray = [];

                        foreach ($returnResult->errors as $error) {

                            $delayMs = self::getApiLimitDelay($httpClient);
                            
                            if ($delayMs > 0) {
                                if($attempt < Config::getAttemptsApiErrorCount()) {
                                    usleep($delayMs * 1000);
                                    return self::baseQuery($type, $entity, $query, $attempt + 1);
                                }
                                Config::setOption('api_blocked_time', time());
                            } else if (Config::isRetryErrorCode($error->code) && ($attempt < Config::getAttemptsApiErrorCount())) {
                                usleep(Config::getAttemptsApiDelay() * 1000);
                                return self::baseQuery($type, $entity, $query, $attempt + 1);
                            }

                            $errorArray[$error->code] = LangMsg::get('API_ERROR', ['#CODE#' => $error->code, '#MSG#' => $error->error, '#LINK#' => $error->moreInfo, '#ENDPOINT#' => $endPoint]);
                        }

                        $returnResult->errors = $errorArray;

                    } else {
                        $returnResult->hasErrors = false;
                    }

                }

            }

            if (is_object($returnResult)) {
                $returnResult->attempts = $attempt;
            }

            return $returnResult;
        }

        return (object)[
            'isNull' => true,
            'hasErrors' => true,
            'errors' => [
                '0' => 'HttpClient error!'
            ]
        ];
    }
    
    public static function getHttpClient()
    {
        $token = Config::getToken();

        $httpClient = new HttpClient();

        $webHookUrl = Config::getOption('webhook_url', '');
        if(!empty($webHookUrl) && self::isValidWebhookUrl($webHookUrl)){
            $httpClient->setHeader('X-Lognex-WebHook-DisableByPrefix', $webHookUrl, true);
        }

        $httpClient->setHeader("Accept-Encoding", 'gzip', true);
        if(!empty($token)){
            $httpClient->setHeader("Authorization", 'Bearer ' . $token, true);
        } else {
            $login = Config::getLogin();
            $pass = Config::getPass();
            $httpClient->setHeader("Authorization", 'Basic ' . base64_encode($login.':'.$pass), true);
        }

        $httpClient->setHeader('Content-Type', 'application/json', true);

        return $httpClient;
    }

    public static function saveFile($serverFilePath = '', $fileObject = null): bool
    {
        if (
            (!empty($serverFilePath)) &&
            ($fileObject !== null && is_object($fileObject) && property_exists($fileObject, 'meta') && !empty($fileObject->meta->downloadHref)) &&
            ($httpClient = self::getHttpClient())
        ) {

            if (!self::isAvailableUrl($fileObject->meta->downloadHref)) {
                return false;
            }

            $httpClient->get($fileObject->meta->downloadHref);
            if ($filePath = $httpClient->getEffectiveUrl()) {
                $path = str_replace(':8080/', '/', $filePath);
                $httpClient = new HttpClient();
                $httpClient->setHeader("Accept-Encoding", 'gzip', true);
                if ($httpClient->download($path, $serverFilePath)) {
                    return file_exists($serverFilePath);
                }
            }
        }

        return false;
    }

    public static function downloadFile($url = '', $fileNameValue = '', $subDir = '')
    {
        if ($httpClient = self::getHttpClient()) {

            if (!self::isAvailableUrl($url)) {
                return false;
            }

            $httpClient->get($url);
            $imgUrl = $httpClient->getEffectiveUrl();

            if (empty($imgUrl)) {
                return false;
            }

            $file = array_pop(explode('filename=', $imgUrl));
            $fileInfo = pathinfo($file);
            $fileInfo['filename'] = $fileNameValue;

            $file = \CUtil::translit($fileInfo['filename'], 'ru') . '.' . $fileInfo['extension'];

            $filePath = Config::getUploadDir($subDir);
            $fileName = $filePath . $file;

            if (\file_exists($fileName)) {
                return $fileName;
            }

            $imgUrl = str_replace(':8080/', '/', $imgUrl);

            $httpClient = new HttpClient();
            $httpClient->setHeader("Accept-Encoding", 'gzip', true);
            $httpClient->download($imgUrl, $fileName);

            if (\file_exists($fileName)) {
                return $fileName;
            }

            return false;
        }
    }

    private static function isValidWebhookUrl(string $url): bool
    {
        $pattern = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        if (!preg_match($pattern, $url)) {
            return false;
        }
        $urlParts = parse_url($url);
        if (!isset($urlParts['scheme']) || !isset($urlParts['host'])) {
            return false;
        }
        if (!in_array($urlParts['scheme'], ['http', 'https'])) {
            return false;
        }
        if (mb_strpos($urlParts['host'], '.') === false) {
            return false;
        }
        return true;
    }

    private static function getApiLimitDelay(HttpClient &$httpClient): int
    {
        $lognexReset = $httpClient->getHeaders()->get('X-Lognex-Reset');
        $lognexRetryAfter = $httpClient->getHeaders()->get('X-Lognex-Retry-After');
        
        $resetMs = $lognexReset !== null ? (int)$lognexReset : 0;
        $retryAfterMs = $lognexRetryAfter !== null ? (int)$lognexRetryAfter : 0;
        
        $delayMs = max($resetMs, $retryAfterMs);
        
        return $delayMs;
    }

    private static function isLockApi(): bool
    {
        $blockedTime = (int)Config::getOption('api_blocked_time', 0);
        $currentTime = time();
        
        if ($blockedTime > 0 && ($currentTime - $blockedTime) < 5) {
            return true;
        }
        
        return false;
    }

    /**
     * @deprecated
     */    
    public static function download($url = '', $fileNameValue = '', $subDir = ''): string
    {
        $filePath = self::downloadFile($url, $fileNameValue, $subDir);
        return is_string($filePath) ? $filePath : '';
    }

}
