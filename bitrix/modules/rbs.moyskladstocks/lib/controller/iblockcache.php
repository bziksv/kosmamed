<?php

namespace Rbs\MoyskladStocks\Controller;

use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Config;

class IblockCache
{
    private static function getTag($tag = 'stocks')
    {
        return "iblock_cache_cleared_{$tag}";
    }

    public static function clearIblockTagCache(Debug\Loger $logger = null, $tag = 'stocks')
    {
        if (Config::checkFeature('cleartagcachestores')) {
            $iblocks = Config::getIblockListForClearCacheTag();
            if (Utils::is_count($iblocks)) {
                global $CACHE_MANAGER;
                $clearIblockCache = [];
                foreach ($iblocks as $iblockId) {
                    if ((int)$iblockId > 0) {
                        $CACHE_MANAGER->ClearByTag("iblock_id_{$iblockId}");
                        $clearIblockCache[] = (int)$iblockId;
                    }
                }
                if (Utils::is_count($clearIblockCache) && $logger instanceof Debug\Loger) {
                    $logger->addMessage(LangMsg::get('INFO_CLEAR_IBLOCK_TAG_CACHE', [
                        '#IBLOCK_ID#' => implode('; ', $clearIblockCache),
                        '#TAG#' => $tag
                    ]), Debug\Message::TYPE_INFO);
                }
            }
        }
    }

    public static function handleCacheClear(Debug\Loger $logger = null, $tag = 'stocks')
    {
        $cacheCleared = Config::getOption(self::getTag($tag), false);
        if (!$cacheCleared) {
            self::clearIblockTagCache($logger, $tag);
            Config::setOption(self::getTag($tag), true);
        }
    }

    public static function resetState($tag = 'stocks')
    {
        Config::setOption(self::getTag($tag), false);
    }
}