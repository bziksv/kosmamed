<?
namespace Arturgolubev\Chatgpt\Suppliers;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Unitools as UTools;

class GigaChat {
    static function getCallData($message, $options){
        $data = [];

        $role = (isset($options['role']) && $options['role']) ? $options['role'] : UTools::getSetting('sber_role');
        
        $max_tokens = intval(UTools::getSetting('sber_max_tokens'));
        if(!$max_tokens) $max_tokens = 2048;
        
        $data = [
            "messages" => [
                ["role" => $role, "content" => $message]
            ],
            "model" => UTools::getSetting('sber_model', 'GigaChat:latest'),
            "temperature" => floatval(UTools::getSetting('sber_temperature')),
            "max_tokens" => $max_tokens,
        ];
        
        return $data;
    }

    static function getDebug(){
        return [
            'result' => [
                'usage' => [
                    'total_tokens' => 940,
                ],
                'choices' => [
                    0 => [
                        'message' => [
                            'content' => 'Test debug content (gigachat)'
                        ]
                    ]
                ]
            ]
        ];
    }
}