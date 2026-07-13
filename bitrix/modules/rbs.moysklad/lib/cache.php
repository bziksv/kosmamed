<?php
namespace Rbs\Moysklad; 

class Cache
{
    public static function isExsistCache($cacheId = '', $cachePath = '', $cacheTime = 5)
    {
        $cache = \Bitrix\Main\Data\Cache::createInstance();
        if($cache->initCache($cacheTime, $cacheId, Config::getCachePath($cachePath))){
            return true;
        } else if ($cache->startDataCache()){
            $cache->endDataCache(time());
            return false;
        }
    }
}