<?php

/**DEPRECATED */

namespace Rbs\Moysklad;

use \Bitrix\Main\Web\HttpClient;
use \Bitrix\Main\Data\Cache;
use \Rbs\Moysklad\Utils;

class Api
{
    static function getEntity($request = '')
    {
        return explode('/', str_replace(Config::getApiEndPoint(), '', $request))[2];
    }

    public static function get($entity = '', $query = [], $cacheTTL = 0)
    {
        if ((int)$cacheTTL > 0) {

            $obCache = Cache::createInstance();
            $cacheId = md5(serialize([$entity, $query, Config::getModuleId(), Config::getApiCacheId()]));

            $cacheEntityPath = self::getEntity($entity);
            if ($obCache->initCache((int)$cacheTTL, $cacheId, Config::getCachePath('get/' . $cacheEntityPath))) {
                $vars = $obCache->GetVars();
                $result = $vars['result'];
            } elseif ($obCache->startDataCache()) {
                
                $result = self::baseQuery('GET', $entity, $query);
                
                if($result->hasErrors || (is_object($result) && property_exists($result, 'rows') &&  is_array($result->rows) && count($result->rows) <= 0) || (is_array($result) && count($result) <= 0)){
                    $obCache->abortDataCache();
                } else {
                    $result->isCached = true;
                    $obCache->endDataCache(array('result' => $result));
                }

            }
            return $result;
        } else {
            return self::baseQuery('GET', $entity, $query);
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

    private static function baseQuery($type, $entity, $query)
    {
        if ($httpClient = self::getHttpClient()) {
            
            $endPointPath = Config::getApiEndPoint();
            $endPoint = $endPointPath . str_replace($endPointPath, '', $entity);
            
            $response = false;

            $isUtfMode = \Bitrix\Main\Application::isUtfMode();

            switch ($type) {
                case 'GET':
                    if (!empty($query)) {
                        $queryStr = '?';
                        foreach ($query as $k => $v) {
                            $queryStr .= $k . '=' . urlencode($v) . '&';
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

            if(!$isUtfMode){
                $returnResult = \Bitrix\Main\Web\Json::decode($response);
                $returnResult = Utils::array_to_object($returnResult);
            } else {
                $returnResult = json_decode($response);
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

                if(is_object($returnResult) && Utils::count($returnResult->errors) > 0){

                    $returnResult->hasErrors = true;
                    $errorArray = [];
                    foreach($returnResult->errors as $error){
                        $errorArray[] = LangMsg::get('API_ERROR', ['#CODE#' => $error->code, '#MSG#' => $error->error, '#LINK#' => $error->moreInfo]);
                    }          
                    $returnResult->errors = $errorArray;
    
                } else if(is_object($returnResult)) {
    
                    $returnResult->hasErrors = false;
    
                }

            }

            

            return $returnResult;
        }

        return false;
    }

    public static function getHttpClient()
    {
        $login = Config::getLogin();
        $pass = Config::getPass();

        $httpClient = new HttpClient();
        $httpClient->setAuthorization($login, $pass);
        $httpClient->setHeader('Content-Type', 'application/json', true);

        return $httpClient;
    }
}
