<?
namespace Arturgolubev\Chatgpt\Suppliers;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Unitools as UTools;

class DeepSeek {
    static function getServerName(){
		$sName = UTools::getSetting('deepseek_custom_base');
		return ($sName) ? $sName : 'https://api.openai.com/v1';
	}
    
    static function getApiKey(){
        return trim(UTools::getSetting('deepseek_api_key'));
    }

    static function getCallData($message, $options){
		$data = [];

		$role = (isset($options['role']) && $options['role']) ? $options['role'] : UTools::getSetting('alg_role');
		
		$messages = [
			["role" => $role, "content" => $message]
		];
		
		$data = [
			"messages" => $messages,
			"model" => UTools::getSetting('deepseek_model'),
			"temperature" => floatval(UTools::getSetting('alg_temperature')),
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
                            'content' => 'Test debug content (deepseek)'
                        ]
                    ]
                ]
            ]
        ];
    }
}