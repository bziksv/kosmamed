<?php
namespace Rbs\MoyskladStocks\Internals;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\AgentManager;

\Bitrix\Main\Loader::includeModule('iblock');

class IblockElementUpdaterForEvents
{
    private static $userId = null;

    public static function updateFromStocks(string $entity = '', int $elementId = 0)
    {
        self::updateFromSource($entity, $elementId, 'stocks');
    }

    public static function updateFromPrices(string $entity = '', int $elementId = 0)
    {
        self::updateFromSource($entity, $elementId, 'prices');
    }

    public static function updateFromCurrentStocks(int $elementId = 0)
    {
        self::updateFromSource('', $elementId, 'current_stocks');
    }

    private static function updateFromSource(string $entity = '', int $elementId = 0, string $source = 'stocks')
    {
        if ($elementId <= 0) { return; }

        switch ($source) {
            case 'prices':
                $settingKey = "{$entity}_prices_update_element";
                break;
            case 'current_stocks':
                $settingKey = "curr_stocks_update_element";
                break;
            default: // 'stocks'
                $settingKey = "{$entity}_stocks_update_element";
                break;
        }

        $defaultValue = ($source === 'current_stocks') ? 'N' : 'AUTO';

        $setting = self::getUpdateElementSetting($settingKey, $defaultValue);
        
        if ($setting === 'N') { return; }
        
        if ($source !== 'current_stocks' && $setting === 'AUTO' && !self::isAutoUpdateForEntity($entity)) {
            return;
        }
          
        self::updateElement($elementId);
    }

    private static $settingIds = [];

    private static function getUpdateElementSetting(string $settingId = '', string $defaultValue = 'AUTO'): string
    {
        if(!isset(self::$settingIds[$settingId])){
            self::$settingIds[$settingId] = Config::getOption($settingId, $defaultValue);
        }
        return self::$settingIds[$settingId];
    }

    private static $isAutoUpdateForEntity = [];

    private static function isAutoUpdateForEntity(string $entity = ''): bool
    {
        if(!isset(self::$isAutoUpdateForEntity[$entity])){
            $agentManager = new AgentManager($entity);
            self::$isAutoUpdateForEntity[$entity] = Config::checkFeature("im_{$entity}_enable") && $agentManager->isEnabled();
        }
        return !self::$isAutoUpdateForEntity[$entity];
    }

    private static function getUserId(): int
    {
        if (self::$userId === null) {
            self::$userId = Config::getUserId();
        }
        return (int)self::$userId;
    }

    private static function updateElement(int $elementId = 0)
    {
        if ($elementId <= 0) {
            return;
        }

        $uid = self::getUserId();
        if ($uid > 0) {
            $el = new \CIblockElement;
            $el->Update($elementId, ['MODIFIED_BY' => $uid], false, false, false, false);
        }
    }
}
