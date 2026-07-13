<?php
namespace Rbs\MoyskladStocks\Diagnostic\Dashboard;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Page
{
    private static function getGroupedItems(): array
    {
        return [
            'module' => [
                'title' => Loc::getMessage('MODUL_'),
                'items' => ['version', 'globalenable', 'cronagents'],
            ],
            'bitrix' => [
                'title' => Loc::getMessage('BITRIKS'),
                'items' => ['version', 'cronagents'],
            ],
            'system' => [
                'title' => Loc::getMessage('SISTEMA'),
                'items' => ['phpversion'],
            ],
            'msconnect' => [    
                'title' => Loc::getMessage('MOJSKLAD'),
                'items' => ['auth'],
            ],
        ];
    }
    public static function getData(): array
    {
        $data = [];
        
        foreach (self::getGroupedItems() as $group => $groupParams) {
            
            $data[$group] = [
                'title' => $groupParams['title'],
                'items' => [],
            ];

            foreach ($groupParams['items'] as $key) {
                if($dashboardItem = Core\ItemsFactory::createItem($group, $key)) {
                    $data[$group]['items'][] = [
                        'id' => $dashboardItem->getKey(),
                        'group' => $dashboardItem->getGroup(),
                        'title' => $dashboardItem->getTitle(),
                        'description' => $dashboardItem->getDescription(),
                        'value' => $dashboardItem->getValue(),
                        'status' => $dashboardItem->getStatus(), 
                        'value_description' => $dashboardItem->getValueDescription(),
                        'card_type' => $dashboardItem->getCardType(),
                        'recommendations' => $dashboardItem->getRecommendations(),
                    ];
                }
            }
        }
        
        return $data;
    }
}


