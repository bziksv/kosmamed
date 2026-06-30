<?php

namespace Rbs\MoyskladStocks;

class Webhook
{
    public static function isCacheEntity($eventHook)
    {
        $cacheId = md5($eventHook->meta->type . $eventHook->action . $eventHook->meta->href);
        return Utils::is_exsists_cache($cacheId, 'hookque', Config::getCacheHookTime());
    }

    public static function cacheUpdateEntity($href = '', $type = 'product', $action = 'UPDATE')
    {
        $href = explode('?', $href)[0];
        $href = ApiNew::replaceOldDomain($href);
        return self::isCacheEntity((object)[
            'meta' => (object)[
                'type' => $type,
                'href' => $href
            ],
            'action' => $action
        ]);
    }

    public static function processHook($inputData = null)
    {
        Utils::say_ok();

        if (!Config::checkFeature('modulesync')|| !is_object($inputData) || Utils::count($inputData->events) <= 0) {
            return;
        }

        $eventsArray = array_chunk($inputData->events, Config::getWebHookLimitCount())[0];

        foreach ($eventsArray as $eventHook) {

            $eventHook->meta->href = ApiNew::replaceOldDomain($eventHook->meta->href);
            
            if (self::isCacheEntity($eventHook)) {
                continue;
            }

            $type = (string)$eventHook->meta->type;
            $action = $eventHook->action;

            if ($type === 'productfolder' && !Config::checkFeature('import_' . $type)) {
                continue;
            }

            if ($type === 'variant' && Config::checkFeature('variant_load_agent')) {
                continue;
            }

            $item = null;

            if ($action === 'DELETE') {
                if ($type !== 'productfolder') {
                    Entity\Base::delete($eventHook);
                } else {
                    Entity\Productfolder::delete($eventHook);
                }
                continue;
            }

            $isCreateItems = $action === 'CREATE';
            if (Config::getEntityParam($type, 'create_hook') === 'ALL') {
                $isCreateItems = true;
            }

            switch ($type) {
                case 'productfolder':
                    $item = new Entity\Productfolder($eventHook->meta->href, $isCreateItems);
                    break;
                case 'product':
                    $item = new Entity\Product($eventHook->meta->href, $isCreateItems);
                    break;
                case 'variant':
                    $item = new Entity\Variant($eventHook->meta->href, $isCreateItems);
                    break;
                case 'bundle':
                    $item = new Entity\Bundle($eventHook->meta->href, $isCreateItems);
                    break;
                case 'service':
                    $item = new Entity\Service($eventHook->meta->href, $isCreateItems);
                    break;
                case 'specialpricediscount':
                    if(Config::checkFeature('ds_sync')) {
                        \Rbs\MoyskladStocks\Import\Discount::importOneDiscountFromHref($eventHook->meta->href);
                    }
                    break;
            }

            if ($item !== null) {
                if ($item->isLoaded()) {
                    $item->checkUpdateHook();
                    if ($type === 'product' && Config::checkFeature('variant_load_agent')) {
                        $itemMs = $item->getItemMs();
                        if ((int)$itemMs->variantsCount > 0) {
                            $profileId = (int)Config::getProfileId();
                            $productId = $item->getProductId();
                            Agent::set_webhook_agent("import_variants_by_product('{$productId}', {$profileId}, 0);", "import_variants_by_product('{$productId}', {$profileId}%");
                        }
                    }
                    $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnWebhookUpdate", array('bx_element' => $item->getItemBx(), 'ms_element' => $item->getItemMs()));
                    $event->send();
                }
            }

            usleep(Config::getWebHookLimitCountInterval() * 1000);
        }
    }

    /** @deprecated */
    public static function sayOkForMs()
    {
        return false;
    }
}
