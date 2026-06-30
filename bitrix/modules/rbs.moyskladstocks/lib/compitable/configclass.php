<?php
namespace Rbs\MoyskladStocks\Compitable;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\ApiNew;

class ConfigClass
{
    public static function getCompitableFeatureList(): array
    {
        return [
            'modulesync' => 'global_enabled',

            'variantprops' => 'im_variant_is_props',

            'productstocks' => 'product_stocks_sync',
            'variantstocks' => 'variant_stocks_sync',
            'bundlestocks' => 'bundle_stocks_sync',

            'productprices' => 'product_prices_sync',
            'variantprices' => 'variant_prices_sync',
            'bundleprices' => 'bundle_prices_sync',
            'serviceprices' => 'service_prices_sync',

            //'loggerapi' => 'logger_api_enabled',
            'loggerbx' => 'logger_bx_enabled',
            'loggerapirequests' => 'logger_api_requests',
            'loggerexchange' => 'logger_apiexchange',
            'loggerexchangenotify' => 'logger_apiexchange_notify',

            'import_productfolder' => 'im_productfolder_enable',
            'import_product' => 'im_product_enable',
            'import_variant' => 'im_variant_enable',
            'import_bundle' => 'im_bundle_enable',
            'import_service' => 'im_service_enable',
        ];
    }

    public static function isEntityImportProcess($entity = 'product'): bool
    {
        $entityImport = Config::checkFeature("import_{$entity}");
        $entityAgent = Config::checkFeature("im_{$entity}_agent");

        return $entityImport && $entityAgent;
    }

    public static function getExportLogFileName()
    {
        $filename = 'main_log';
        if (Config::getProfileId() > 0) {
            $filename .= (string)Config::getProfileId();
        }
        $filename .= '.txt';
        return $filename;
    }

    public static function setFullUpdateAgent(string $agentFunctionName = '')
    {
        Config::setOption("full_agent_" . md5($agentFunctionName), 'Y');
    }

    public static function isFullUpdateAgent(string $agentFunctionName = ''): bool
    {
        $isFullUpdAgent = false;
        if (!empty($agentFunctionName)) {
            $isFullUpdAgent = Config::checkFeature("full_agent_" . md5($agentFunctionName));
            if (!$isFullUpdAgent) {
                $currentTime = new \DateTime('now');
                $fullUpdTimeHour = (int)Config::getOption("{$agentFunctionName}_full_upd_time", "4");
                $lastDateForEvent = Config::getDateForEvent($agentFunctionName);
                if (
                    $lastDateForEvent->format('d.m.Y') !== $currentTime->format('d.m.Y') &&
                    $fullUpdTimeHour === (int)$currentTime->format('G')
                ) {
                    Config::setFullUpdateAgent($agentFunctionName);
                    $isFullUpdAgent = true;
                }
            }
        }
        return $isFullUpdAgent;
    }

    public static function stopFullUpdateAgent(string $agentFunctionName = '')
    {
        if (!empty($agentFunctionName)) {
            Config::setOption("full_agent_" . md5($agentFunctionName), 'N');
            Config::setDateForEvent($agentFunctionName, Config::getDateTime());
        }
    }

    public static function getDateForEvent(string $event = ''): \DateTime
    {
        $result = Config::getDateTime();

        $currentSaveDate = Config::getOption('date_event_' . md5($event), '');
        if (empty($currentSaveDate)) {
            Config::setDateForEvent($event, $result->modify('-1 day'));
        } else {
            $result->setTimestamp($currentSaveDate);
        }

        return $result;
    }

    public static function setDateForEvent(string $event = '', \DateTime $date)
    {
        Config::setOption('date_event_' . md5($event), $date->format('U'));
    }

    public static function getLastDateUpdate($key = '')
    {
        $lastDateUpdate = Config::getOption('lastdate_' . $key, '');
        if (empty($lastDateUpdate)) {
            $refreshMinutes = 30;
            $refreshMinutes = $refreshMinutes <= 0 ? 30 : $refreshMinutes;
            $lastDateUpdate = (Config::getDateTime())->modify("-{$refreshMinutes} minutes")->format('Y-m-d H:i:s');
            Config::setLastDateUpdate($key, $lastDateUpdate);
        }
        return $lastDateUpdate;
    }

    public static function setLastDateUpdate($key = '', $date = '')
    {
        if (empty($date)) {
            $date = (Config::getDateTime())->format('Y-m-d H:i:s');
        }
        Config::setOption('lastdate_' . $key, $date);
    }

    public static function getStockAgentTime(): int
    {
        $currentLimit = (int)Config::getOption('stock_time');
        return $currentLimit > 0 ? $currentLimit : 120;
    }

    public static function getPriceAgentTime($entity = 'product'): int
    {
        $currentLimit = (int)Config::getOption("prices_{$entity}_agent_int");
        return $currentLimit > 0 ? $currentLimit : 120;
    }

    public static function getPriceAgentLimit($entity = 'product'): int
    {
        $currentLimit = (int)Config::getOption("prices_{$entity}_agent_limit");
        return $currentLimit > 0 ? $currentLimit : 500;
    }

    public static function getImportAgentLimit($entity = 'product'): int
    {
        $currentLimit = (int)Config::getOption("im_{$entity}_agent_limit");
        return $currentLimit > 0 ? $currentLimit : 500;
    }

    public static function getDiscountAgentLimit(): int
    {
        $currentLimit = (int)Config::getOption("ds_agent_limit");
        return ($currentLimit > 0 && $currentLimit <= 100) ? $currentLimit : 50;
    }

    public static function getAgentImportTime($entity = 'product'): int
    {
        $currentLimit = (int)Config::getOption("im_{$entity}_agent_time");
        return $currentLimit > 0 ? $currentLimit : 120;
    }

    public static function getApiEndPointNew(): string
    {
        return ApiNew::getApiEndPointUrl();
    }

    public static function getBaseUploadDirFiles()
    {
        $profileDir = '';
        if(Config::getProfileId() > 0){
            $profileDir = 'profile_' . Config::getProfileId() . '/';
        }
        return $_SERVER['DOCUMENT_ROOT'] . '/upload/' . Config::getModuleId(true) . '/' . $profileDir;
    }

    public static function getApiEndPoint()
    {
        return 'https://online.moysklad.ru/api/remap/1.1';
    }

    public static function getAgentInterval()
    {
        $value = Config::getOption('agent_interval');
        if((int)$value <= 0){
            $value = 300;
        }
        return (int)$value;
    }

    public static function getAgentLiveTime()
    {
        return (int)Config::getOption('agent_live_time') > 0 ? (int)Config::getOption('agent_live_time') : 600;
    }

    public static function getAgentAttempts()
    {
        $value = Config::getOption('agent_attempts');
        if((int)$value <= 0 || (int)$value >= 5){
            $value = 5;
        }
        return (int)$value;
    }

    public static function cacheTime($option = '')
    {
        if(empty($option)){
            return 0;
        }

        $optionBx = '';
        switch($option){
            case 'basket_items_bx':
                $optionBx = 'cache_basket_bx_items';
            break;
        }

        if(!empty($optionBx)){
            return (int)Config::getOption($optionBx);
        } else {
            return 0;
        }
    }

    public static function getStocksTypeApi()
    {
        return 'ALL';
    }
}
