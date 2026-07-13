<?
namespace Arturgolubev\Chatgpt\Suppliers;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Unitools as UTools,
    \Arturgolubev\Chatgpt\Tools;

class ChatGpt {
    static function getServerName(){
		$sName = UTools::getSetting('chatgpt_custom_base');
		return ($sName) ? $sName : 'https://api.openai.com/v1';
	}

    static function getApiKey($api_key_num = 0){
		$result = [
			'error' => '',
			'keys' => [],
			'key' => '',
		];
		
		$result['keys'] = UTools::explodeByEOL(UTools::getSetting('api_key'));
		
		if(count($result['keys']) > 1){
			if(($api_key_num + 1) > count($result['keys'])){
				$result['error'] = 'no_next';
			}
			
			$result['key'] = $result['keys'][$api_key_num];
		}else{
			$result['key'] = $result['keys'][0];
		}
		
		return $result;
	}

    static function getImageCallData($message, $options){
        $data = [
            'model' => UTools::getSetting('alg_image_model', 'dall-e-2'),
            'prompt' => $message,
            'n' => 1,
        ];

        $paramsList = ['size', 'output_format', 'quality'];
        foreach($paramsList as $param_name){
            if($options['input'][$param_name]){
                $data[$param_name] = $options['input'][$param_name];
            }
        }

        if(is_array($options['files']) && count($options['files'])){
            $domain = (\CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"];

            // $data['image'] = [];
            foreach($options['files'] as $fileLink){
                $fileLinkBase = $_SERVER['DOCUMENT_ROOT'].str_replace($domain, '', $fileLink);
                $info = pathinfo($fileLinkBase);

                $data['image'] = new \CURLFile($fileLinkBase, "image/".($info['extension'] == 'jpg' ? 'jpeg' : $info['extension']), $info['filename'].'.'.$info['extension']);
                // $data['image'][] = new \CURLFile($fileLinkBase, "image/".($info['extension'] == 'jpg' ? 'jpeg' : $info['extension']), $info['filename'].'.'.$info['extension']);
                // $data['image[]'] = new \CURLFile($fileLinkBase, "image/".$info['extension'], $info['filename'].$info['extension']);
            }
        }

        return $data;
    }

    static function getCallData($message, $options){
		$data = [];

        $model = UTools::getSetting('alg_model');
        if($model == 'other'){
            $model = UTools::getSetting('alg_model_other');
        }

		$role = (isset($options['role']) && $options['role']) ? $options['role'] : UTools::getSetting('alg_role');
		
        if(is_array($options['files']) && count($options['files'])){
            $content = [
                ['type' => 'text', 'text' => $message],
            ];

            foreach($options['files'] as $v){
                $content[] = ['type' => 'image_url', 'image_url' => ['url' => $v]];
            }
        }else{
            $content = $message;
        }

		$messages = [
			["role" => $role, "content" => $content]
		];
		
		$max_tokens = intval(UTools::getSetting('alg_max_tokens'));
		if($max_tokens <= 0){
			$max_tokens = 4096;
		}

        if(strpos($model, 'gpt-5') !== false){
            $data = [
                "messages" => $messages,
                "model" => $model,
                'max_completion_tokens' => $max_tokens,
            ];
        }else{
            if($max_tokens > 4096){
                $max_tokens = 4096;
            }

            $data = [
                "messages" => $messages,
                "model" => $model,
                'max_tokens' => $max_tokens,
            ];
        }

        // echo '<pre>'; print_r($data); echo '</pre>';
        // die();
		
		return $data;
	}

    static function getProxy(){
        $result = false;

        Tools::remakeProxy();
        
        $data = [
            'ip' => UTools::getSetting('proxy_ip'),
            'port' => UTools::getSetting('proxy_port'),
            'login' => UTools::getSetting('proxy_login'),
            'pass' => UTools::getSetting('proxy_password'),
        ];

        if($data['ip']){
            $result = [];

            $result['ip'] = $data['ip'];
            if($data['port']){
                $result['ip'] .= ':'.$data['port'];
            }

            if($data['login']){
                $result['login'] = $data['login'];
                if($data['pass']){
                    $result['login'] .= ':'.$data['pass'];
                }
            }

            $result['data'] = $data;
        }
        
        return $result;
    }

    static function prepareResult($result){
        if(is_array($result['result']) && is_array($result['result']['error'])){
            if($result['result']['error']['message']){
                $result['result']['error']['message'] = str_replace([
                    'Country, region, or territory not supported'
                ], [
                    'Country, region, or territory not supported '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_ERROR_COUNTRY_NOT_SUPPORTED')
                ], $result['result']['error']['message']);
            }
        }

        return $result;
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
                            'content' => 'Test debug content (chatgpt)'
                        ]
                    ]
                ]
            ]
        ];
    }
}