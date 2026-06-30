<?
use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc,
	\Bitrix\Iblock\InheritedProperty, 
	\Bitrix\Main\Web\HttpClient,
	\Bitrix\Main\Web\Json;

use \Arturgolubev\Chatgpt\Encoding,
	\Arturgolubev\Chatgpt\Tools,
	\Arturgolubev\Chatgpt\Hl,
	\Arturgolubev\Chatgpt\Unitools as UTools,
    \Arturgolubev\Chatgpt\Tasks;

use Arturgolubev\Chatgpt\Suppliers\DeepSeek, 
	Arturgolubev\Chatgpt\Suppliers\ChatGpt,
	Arturgolubev\Chatgpt\Suppliers\GigaChat;

include 'autoload.php';
include 'jscore.php';

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.chatgpt/include.php");

Class CArturgolubevChatgpt 
{
	const MODULE_ID = 'arturgolubev.chatgpt';
	
	const TOKEN = 300;

	const DEBUG = 0;
	const LOG_DEBUG = 0;
	
	// demo
	static function isDemo(){
		return (CModule::IncludeModuleEx(self::MODULE_ID) == 2);
	}

	static function checkDemoAccess(){
		if(self::isDemo()){
			$actualToken = intval(UTools::getSetting('alg_max_token'));
			if($actualToken >= self::TOKEN){
				return 1;
			}
		}

		return 0;
	}
	static function checkDemoCounts(){
		if(self::isDemo()){
			$actualToken = intval(UTools::getSetting('alg_max_token'));
			UTools::setSetting('alg_max_token', $actualToken + 1);
		}

		return 0;
	}

	
	// chatgpt
	static function checkLimitError($keynum, $api_keys, $result){
		$keynum++;

		$arErrorVariants = [
			'Rate limit reached',
			'You exceeded your current quota',
			// 'Incorrect API key provided',
		];

		if(is_array($result['error']) && $result['error']['message']){
			foreach($arErrorVariants as $errorType){
				if(strpos($result['error']['message'], $errorType) !== false){
					if(isset($api_keys['keys'][$keynum])){
						return 1;
					}
				}
			}
		}

		return 0;
	}

	/* all system */
	static function callChatProvider($question, $options){
		if($options['provider'] == 'sber'){
			return self::callSberGPT($question, $options);
		}elseif($options['provider'] == 'deepseek'){
			return self::callDeepSeekGPT($question, $options);
		}else{
			if($options['content_type'] == 'image'){
				return self::gptGenImage($question, $options);
			}else{
				return self::callChatGPT($question, $options);
			}
		}
	}
	
	/* chatgpt chat api */
	static function gptGenImage($message, $options){
		$result = [];

		$api_keys = ChatGpt::getApiKey($options['keynum']);

		if($api_keys['error']){
			$error = 1;
			if($api_keys['error'] == 'no_next'){
				$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_END_KEY_LIST');
			}else{
				$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.$api_keys['error'];
			}
		}
		
		if(!$api_keys['key']){
			$error = 1;
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_NO_API_KEY_ERROR');
		}

		if(!$error){
			$data = ChatGpt::getImageCallData($message, $options);

			self::writeModuleDebug(false, 'gpt image get', $data);

			// echo '<pre>'; print_r($options); echo '</pre>';
			// echo '<pre>'; print_r($data); echo '</pre>';
			// die();

			$curl = curl_init();

			if(isset($data['image'])){
				$headers = [
					"Authorization: Bearer " . $api_keys['key']
				];
				
				curl_setopt($curl, CURLOPT_URL, ChatGpt::getServerName()."/images/edits");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);		
			}else{
				$headers = [
					"Accept: application/json" ,
					"Content-Type: application/json" ,
					"Authorization: Bearer " . $api_keys['key']
				];
				
				curl_setopt($curl, CURLOPT_URL, ChatGpt::getServerName()."/images/generations");
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);	
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, Json::Encode($data));
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);	
			}

			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, Tools::getTimeout()); 
			curl_setopt($curl, CURLOPT_TIMEOUT, Tools::getTimeout());

			$proxy = ChatGpt::getProxy();
			if(is_array($proxy)){
				curl_setopt($curl, CURLOPT_PROXY, $proxy['ip']);
				if($proxy['login']){
					curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxy['login']);
				}
			}
			
			$baseResult = curl_exec($curl);
			
			$result["error"] = curl_error($curl);
			$result["error_no"] = curl_errno($curl);
			$result["header"] = curl_getinfo($curl);

			curl_close($curl);

			if($baseResult){
				if(UTools::isHtmlPage($baseResult)){
					$result['result']['error']['message'] = $baseResult;
				}else{
					$result["result"] = Json::Decode($baseResult);
				}
			}else{
				$result['result']['error']['message'] = '['.$result['header']['http_code'].'] '.(($result["error"]) ? $result["error"] : 'Empty answer.');
			}

			self::writeModuleDebug(false, 'gpt image result', $result);

			if(self::checkLimitError($options['keynum'], $api_keys, $result["result"])){
				$result['next_key'] = 1;
			}
		}
		
		$result = Tools::prepareResult($options, $result);

		return $result;
	}

	static function callChatGPT($message, $options){
		$result = [];

		$api_keys = ChatGpt::getApiKey($options['keynum']);

		if($api_keys['error']){
			$error = 1;

			if($api_keys['error'] == 'no_next'){
				$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_END_KEY_LIST');
			}else{
				$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.$api_keys['error'];
			}
		}
		
		if(!$api_keys['key']){
			$error = 1;
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_NO_API_KEY_ERROR');
		}

		if(!$error){
			if(self::DEBUG){
				$result = ChatGpt::getDebug();
			}else{
				$proxy = ChatGpt::getProxy();
				$data = ChatGpt::getCallData($message, $options);
				
				self::writeModuleDebug(false, 'gpt chat get', $data);
				
				// echo '<pre>'; print_r($data); echo '</pre>';
				// echo '<pre>'; print_r($proxy); echo '</pre>';
				// die();
				
				$headers = [
					"Accept: application/json" ,
					"Content-Type: application/json" ,
					"Authorization: Bearer " . $api_keys['key']
				];

				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, ChatGpt::getServerName()."/chat/completions");
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, Tools::getTimeout()); 
				curl_setopt($curl, CURLOPT_TIMEOUT, Tools::getTimeout());
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

				if(is_array($proxy)){
					curl_setopt($curl, CURLOPT_PROXY, $proxy['ip']);
					if($proxy['login']){
						curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxy['login']);
					}
				}

				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, Json::Encode($data));
				
				$baseResult = curl_exec($curl);
				
				$result["error"] = curl_error($curl);
				$result["error_no"] = curl_errno($curl);
				$result["header"] = curl_getinfo($curl);

				curl_close($curl);

				if($baseResult){
					if(UTools::isHtmlPage($baseResult)){
						$result['result']['error']['message'] = $baseResult;
					}else{
						$result["result"] = Json::Decode($baseResult);
					}
				}else{
					$result['result']['error']['message'] = '['.$result['header']['http_code'].'] '.(($result["error"]) ? $result["error"] : 'Empty answer.');
				}

				$result = ChatGpt::prepareResult($result);

				self::writeModuleDebug(false, 'gpt chat result', $result);
			}

			if(self::checkLimitError($options['keynum'], $api_keys, $result["result"])){
				$result['next_key'] = 1;
			}
		}
		
		$result = Tools::prepareResult($options, $result);

		return $result;
	}

	// deepseek
	static function callDeepSeekGPT($message, $options){
		$result = [];

		$api_key = DeepSeek::getApiKey();

		if(!$api_key){
			$error = 1;
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_NO_API_KEY_ERROR');
		}

		if(!$error){
			if(self::DEBUG){
				$result = DeepSeek::getDebug();
			}else{
				$data = DeepSeek::getCallData($message, $options);

				self::writeModuleDebug(false, 'deepseek chat get', $data);
				
				$headers = [
					"Accept: application/json" ,
					"Content-Type: application/json" ,
					"Authorization: Bearer " . $api_key
				];

				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, DeepSeek::getServerName()."/chat/completions");
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, Tools::getTimeout()); 
				curl_setopt($curl, CURLOPT_TIMEOUT, Tools::getTimeout());
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, Json::Encode($data));
				
				$baseResult = curl_exec($curl);
				
				$result["error"] = curl_error($curl);
				$result["error_no"] = curl_errno($curl);
				$result["header"] = curl_getinfo($curl);

				curl_close($curl);

				if($baseResult){
					if(UTools::isHtmlPage($baseResult)){
						$result['result']['error']['message'] = $baseResult;
					}else{
						$result["result"] = Json::Decode($baseResult);
					}
				}else{
					$result['result']['error']['message'] = '['.$result['header']['http_code'].'] '.(($result["error"]) ? $result["error"] : 'Empty answer.');
				}

				self::writeModuleDebug(false, 'deepseek chat result', $result);
			}
		}
		
		$result = Tools::prepareResult($options, $result);

		return $result;
	}

	/* sber */
	static function checkSberToken(){
		$exp = intval(intval(UTools::getSetting('sber_access_expires'))/1000);
		$now = intval(microtime(true));

		$real = $exp-$now;

		if($real <= 60){
			return self::getSberToken();
		}

		return [];
	}

	static function getSberToken(){
		$result = [];

		// UTools::setSetting('sber_access_token', '');
		// UTools::setSetting('sber_access_expires', '');

		$headers = [
			"Authorization: Bearer ".UTools::getSetting('sber_authorization'),
			"Content-Type: application/x-www-form-urlencoded",
			"RqUID: ".Tools::getGuid4(),
		];
		
		$data = [
			'scope' => UTools::getSetting('sber_scope')
		];

		// echo '<pre>'; print_r($headers); echo '</pre>';
		// echo '<pre>'; print_r($data); echo '</pre>';
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://ngw.devices.sberbank.ru:9443/api/v2/oauth");
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, Tools::getTimeout()); 
		curl_setopt($curl, CURLOPT_TIMEOUT, Tools::getTimeout());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);		
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		
		$baseResult = curl_exec($curl);
		
		$result["error"] = curl_error($curl);
		$result["error_no"] = curl_errno($curl);
		$result["header"] = curl_getinfo($curl);
		
		curl_close($curl);

		if($baseResult){
			$result["result"] = Json::Decode($baseResult);

			if($result["result"]['access_token']){
				UTools::setSetting('sber_access_token', $result["result"]['access_token']);
				UTools::setSetting('sber_access_expires', $result["result"]['expires_at']);
			}elseif($result["result"]['message']){
				$result["error_message"] = $result['result']['message'];
				if($result["result"]['code']){
					$result["error_message"] .= ' [error code = '.$result["result"]['code'].']';
				}
			}
		}elseif($result["error"]){
			$result["error_message"] = $result["error"];
		}

		return $result;
	}

	static function callSberGPT($message, $options){
		$result = [];

		$checkResult = self::checkSberToken();

		if(is_array($checkResult) && $checkResult['error_message']){
			$error = 1;
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.$checkResult['error_message'];
		}

		$access_token = UTools::getSetting('sber_access_token');
		
		if(!$access_token){
			$error = 1;
			$result['result']['error']['message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ERROR').' '.Loc::getMessage('ARTURGOLUBEV_CHATGPT_SBER_NO_ACCESS_TOKEN_ERROR');
		}
		
		if(!$error){
			if(self::DEBUG){
				$result = GigaChat::getDebug();
				// $result['result']['error']['message'] = 'LIMIT';
			}else{
				$data = GigaChat::getCallData($message, $options);
				
				self::writeModuleDebug(false, 'gigachat chat get', $data);
				
				$headers = [
					"Content-Type: application/json",
					"Authorization: Bearer " . $access_token
				];

				// echo '<pre>'; print_r($data); echo '</pre>';

				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, "https://gigachat.devices.sberbank.ru/api/v1/chat/completions");
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, Tools::getTimeout()); 
				curl_setopt($curl, CURLOPT_TIMEOUT, Tools::getTimeout());
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);		
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_POSTFIELDS, Json::Encode($data));
				
				$baseResult = curl_exec($curl);
				
				$result["error"] = curl_error($curl);
				$result["error_no"] = curl_errno($curl);
				$result["header"] = curl_getinfo($curl);
				
				curl_close($curl);

				if($baseResult){
					if(UTools::isJsonPage($baseResult)){
						$result["result"] = Json::Decode($baseResult);

						if($result["result"]['status']){
							$result['result']['error']['message'] = $result['result']['message'];
						}
					}else{
						$result['result']['error']['message'] = $baseResult;
					}
				}else{
					$result['result']['error']['message'] = 'Empty answer';
				}

				self::writeModuleDebug(false, 'gigachat chat result', $result);
			}
		}
		
		$result = Tools::prepareResult($options, $result);
		
		return $result;
	}

	static function applyDefaultVals($postFields, $get = 1){
		$arCheckFields = ['provider', 'operation', 'type', 'for', 'from', 'length', 'html', 'lang', 'template_element', 'template_section', 'template_image', 'mass_save_field', 'save_only_empty', 'savefield', 'size', 'output_format', 'quality', 'files'];
		
		if(!isset($_SESSION['AGCG_DEFAULT']) || !is_array($_SESSION['AGCG_DEFAULT'])){
			$_SESSION['AGCG_DEFAULT'] = [];
		}
		
		foreach($arCheckFields as $field){
			if($postFields[$field]){
				if($field == 'template_element' || $field == 'template_section'){
					$postFields[$field] = Encoding::convertFromUtf($postFields[$field]);
				}
				
				$_SESSION['AGCG_DEFAULT'][$field] = $postFields[$field];
			}
		}
		
		if($get){
			foreach($arCheckFields as $field){
				if(!$postFields[$field]){
					if(isset($_SESSION['AGCG_DEFAULT'][$field])){
						$postFields[$field] = $_SESSION['AGCG_DEFAULT'][$field];
					}
				}
			}
		}
		
		// echo '<pre>'; print_r($_SESSION['AGCG_DEFAULT']); echo '</pre>';
		
		return $postFields;
	}
	
	static function createImage($input){
		$data = self::gptGenImage($input['question'], $input);

		if(is_array($data['result']['error'])){
			$result['next_key'] = ($data['next_key']) ? 1 : 0;
			$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CHATGPT_ERROR').' '.($data['result']['error']['message'] ? $data['result']['error']['message'] : $data['result']['error']['code']);
		}else{
			$image_url = Tools::prepareImageOutput($data['result']['data'][0], $input['output_format']);

			if($image_url){
				$result['created_image'] = $image_url;
			}else{
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
			}
		}

		return $result;
	}

	static function askQuestion($input){
		$result = [];

		$options = [
			'provider' => $input['provider'],
			'keynum' => intval($input['keynum']),
		];

		$data = self::callChatProvider($input['question'], $options);

		$result['full_result'] = $data['result'];
		
		if($data['prepared']['error']){
			$result['next_key'] = ($data['next_key']) ? 1 : 0;
			$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CHATGPT_ERROR').' '.$data['prepared']['error_message'];
		}else{
			if($data['prepared']['answer']){
				$result['created_text'] = $data['prepared']['answer'];
			}else{
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
			}
		}
		
		return $result;
	}
	
	static function makeQuestionByTemplate($template, $main_info){
		$question = $template;
		
		preg_match_all('/#([a-zA-Z_0-9]+)#/is', $template, $match);
		
		if(is_array($match[1]) && count($match[1])){
			$arFind = [];
			$arReplace = [];
			foreach($match[1] as $macros){
				$arFind[] = '#'.$macros.'#';
				$arReplace[] = $main_info[$macros];
			}
			
			$question = str_replace($arFind, $arReplace, $question);
		}
		
		
		return $question;
	}
	
	static function makeQuestion($input, $main_info){
		// echo '<pre>'; print_r($input); echo '</pre>';
		
		$main_info = strip_tags(htmlspecialchars_decode($main_info));
		
		if($input['operation'] == 'REWRITE'){
			$question = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE");
		}elseif($input['operation'] == 'TRANSLATE'){
			$question = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TRANSLATE");
		}elseif($input['operation'] == 'CREATE' && $input['type'] == 'REVIEW'){
			$question = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_REVIEW");
		}else{
			$question = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE");
		}
		
		if($input['operation'] == 'REWRITE'){
			if($input['for'] != 'ARTICLE'){
				switch($input['type']){
					case 'TEXT': 
						$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_TYPE_DESCRIPTION");
					break;
				}
			}
			
			switch($input['for']){
				case 'PRODUCT': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_FOR_PRODUCT");
				break;
				case 'ARTICLE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_FOR_ARTICLE");
				break;
				case 'SERVICE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_FOR_SERVICE");
				break;
			}
			
			// $question .= '. ';
			
			if($input['html']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_HTML");
			}

			$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_REWRITE_OBJECT"). $main_info;
		}else{
			switch($input['type']){
				case 'H1': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_H1");
				break;
				case 'TITLE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_TITLE");
				break;
				case 'DESCRIPTION': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_DESCRIPTION");
				break;
				case 'KEYWORDS': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_KEYWORDS");
				break;
				case 'TEXT': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_TEXT");
				break;
				case 'REVIEW': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_TYPE_REVIEW");
				break;
			}
			
			switch($input['for']){
				case 'PRODUCT': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_FOR_PRODUCT");
				break;
				case 'ARTICLE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_FOR_ARTICLE");
				break;
				case 'SERVICE': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_FOR_SERVICE");
				break;
				case 'PRODUCT_SECTION': 
					$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_FOR_PRODUCT_SECTION");
				break;
			}
			
			$question .= '"' . $main_info . '" ';
		
			if($input['lang']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_TRANSLATE_LANG", ['#lang#' => Encoding::convertFromUtf($input['lang'])]);
			}
			
			if($input['length']){
				$question .= Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_LENGTH", ['#length#' => $input['length']]);
			}

			$arDopInstruction = [];
			
			if($input['html']){
				$arDopInstruction[] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_WRITE_HTML");
			}

			if(count($arDopInstruction)){
				$question = trim($question) . Loc::getMessage("ARTURGOLUBEV_CHATGPT_MAIN_ELEMENT_FOR_RESULT") . implode(', ', $arDopInstruction);
			}

		}

		if($input['additionals']){
			$question = trim($question).'. '.$input['additionals'];
		}
		
		return $question;
	}
	
	// element
	static function getElementBaseData($elementId){
		$result = [];
		
		if(Loader::IncludeModule("iblock")){
			$domain = (CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"];

			$res = CIBlockElement::GetList([], ['ID' => $elementId], false, ["nPageSize"=>1], ['NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', '*']);
			while($ob = $res->GetNextElement(true, false)){
				$result = $ob->GetFields();
				$props = $ob->GetProperties();
				
				$result['DETAIL_PAGE_URL'] = $domain.$result['DETAIL_PAGE_URL'];

				$result['DETAIL_TEXT_HTML'] = $result['DETAIL_TEXT'];
				$result['PREVIEW_TEXT_HTML'] = $result['PREVIEW_TEXT'];
				
				if($result['PREVIEW_PICTURE']){
					$result['PREVIEW_PICTURE'] = $domain.CFile::GetPath($result['PREVIEW_PICTURE']);
				}

				if($result['DETAIL_PICTURE']){
					$result['DETAIL_PICTURE'] = $domain.CFile::GetPath($result['DETAIL_PICTURE']);
				}

				$result['ALL_PROPS'] = '';

				foreach($props as $property){
					if($property['USER_TYPE'] == 'HTML' && is_array($property['VALUE'])){
						$property['VALUE'] = $property['VALUE']['TEXT'];
					}
					
					if($property['USER_TYPE'] == 'directory'){
						if(is_array($property['VALUE'])){
							foreach($property['VALUE'] as $k=>$v){
								$property['VALUE'][$k] = Hl::getPropValueField($property, $v);
							}
						}else{
							$property['VALUE'] = Hl::getPropValueField($property, $property['VALUE']);
						}
					}
					
					if($property['PROPERTY_TYPE'] == 'E'){
						if(!is_array($property['VALUE'])){
							$property['VALUE'] = [$property['VALUE']];
						}

						foreach($property['VALUE'] as $k=>$v){
							if($v){
								$resOne = CIBlockElement::GetList([], ['ID' => $v], false, ["nPageSize"=>1], ['ID', 'NAME']);
								while($arOneFields = $resOne->Fetch()){
									$property['VALUE'][$k] = $arOneFields['NAME'];
								}
							}
						}
					}

					if($property['PROPERTY_TYPE'] == 'F'){
						if(!is_array($property['VALUE'])){
							if($property['VALUE']){
								$property['VALUE'] = [$property['VALUE']];
							}else{
								$property['VALUE'] = [];
							}
						}

						foreach($property['VALUE'] as $k=>$v){
							if($v){
								$property['VALUE'][$k] = $domain . CFile::GetPath($v);
							}
						}
					}

					$valueString = (is_array($property['VALUE']) ? implode(', ', $property['VALUE']) : $property['VALUE']);

					if($valueString && !in_array($property['PROPERTY_TYPE'], ['F'])){
						$result['ALL_PROPS'] .= $property['NAME'].': '.$valueString.'; ';
					}

					$result['PROPERTY_'.$property['CODE']] = $valueString;
				}
				
				$ipropElementValues = new InheritedProperty\ElementValues($result['IBLOCK_ID'], $result['ID']);
				$values = $ipropElementValues->getValues();
				foreach($values as $key=>$val){
					$result['SEO_'.$key] = $val;
				}

				if($result['IBLOCK_SECTION_ID']){
					$rdbSections = \Bitrix\Iblock\SectionTable::getList(array(
						'select' => array('NAME'),
						'filter' => array('ID' => $result['IBLOCK_SECTION_ID'])
					));
					while ($dctSection = $rdbSections->fetch()) {
						$result['PARENT_SECTION_NAME'] = $dctSection['NAME'];
					}
				}
				
			}
		}
		
		return $result;
	}

	static function checkElementEmptySaveFiled($ibid, $eid, $field){
		$elementInfo = self::getElementBaseData($eid);

		return ['result' => ($elementInfo[$field] == ''), 'element_name' => $elementInfo['NAME']];
	}
	
	static function createElementText($input){
		$result = [];
		$options = [
			'content_type' => 'text',
			'provider' => $input['provider'],
			'keynum' => intval($input['keynum'])
		];

		if($input['type'] == 'KEYWORDS'){
			$options['role'] = 'system';
		}
		
		$elementInfo = self::getElementBaseData($input['ID']);
		$result['element_name'] = $elementInfo['NAME'];
		
		if(!$elementInfo[$input['from']] && !in_array($input['operation'], ['TEMPLATE', 'IMAGE'])){
			$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_EMPTY_FIELD_FROM_ERROR');
		}

		if($input['mass_generation'] && self::isDemo()){
			$actualToken = intval(UTools::getSetting('alg_max_token'));
			if($actualToken >= self::TOKEN){
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_DEMO_MASS_GENERATE_LIMIT');
			}
		}
		
		if(!$result['error_message']){
			$result['question_show'] = (UTools::getSetting('show_query') == 'Y');
			$result['show_tokens'] = (UTools::getSetting('show_tokens') == 'Y');
			
			if($input['operation'] == 'TEMPLATE'){
				$input['template_element'] = Encoding::convertFromUtf($input['template_element']);
				$result['question'] = self::makeQuestionByTemplate($input['template_element'], $elementInfo);
			}elseif($input['operation'] == 'IMAGE'){
				$options['content_type'] = 'image';
				$options['input'] = $input;

				$input['template_image'] = Encoding::convertFromUtf($input['template_image']);
				$result['question'] = self::makeQuestionByTemplate($input['template_image'], $elementInfo);
			}else{
				$result['question'] = self::makeQuestion($input, $elementInfo[$input['from']]);
			}

			if($input['files'] && $elementInfo[$input['files']]){
				$result['files_vals'] = $elementInfo[$input['files']];
				$options['files'] = explode(', ', $elementInfo[$input['files']]);
			}

			$result['content_type'] = $options['content_type'];

			foreach(GetModuleEvents(self::MODULE_ID, "modifyElementQuestionBeforeSend", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, [&$result['question'], $input, $elementInfo]);
			
			if($input['preview']){
				return $result;
			}

			self::writeModuleDebug(false, 'createElementText', $input);

			$data = self::callChatProvider($result['question'], $options);

			$input['question'] = $result['question'];

			foreach(GetModuleEvents(self::MODULE_ID, "modifyElementAnswer", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, [&$data, $input, $elementInfo]);

			if($data['prepared']['error']){
				$result['next_key'] = ($data['next_key']) ? 1 : 0;
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CHATGPT_ERROR').' '.$data['prepared']['error_message'];
			}else{
				if($input['mass_generation'] && self::isDemo()){
					UTools::setSetting('alg_max_token', $actualToken + 1);
				}
				
				if($options['content_type'] == 'text'){
					if($data['prepared']['answer']){
						$result['answer'] = $data['prepared']['answer'];
						$result['used_tokens_cnt'] = intval($data['result']['usage']['total_tokens']);
						$result['used_tokens'] = Tools::calculateTokens($result['used_tokens_cnt'], $options['provider']);
					}else{
						$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
						if(isset($data['result']['detail']) && $data['result']['detail']){
							$result['error_message'] .= ': ' . $data['result']['detail'];
						}
					}
				}else{
					$image_url = Tools::prepareImageOutput($data['result']['data'][0], $input['output_format']);

					if($image_url){
						$result['answer'] = $image_url;
						$result['used_tokens_cnt'] = intval($data['result']['usage']['total_tokens']);
						$result['used_tokens'] = Tools::calculateTokens($result['used_tokens_cnt'], $options['provider']);
					}else{
						$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
					}
				}
			}
		}
		
		// echo '<pre>input '; print_r($input); echo '</pre>';
		// echo '<pre>'; print_r($result); echo '</pre>';
		
		return $result;
	}
	
	static function saveToElement($params){
		Loader::includeModule('iblock');
		
		if($params['re_encoding']){
			$params['genresult'] = Encoding::convertFromUtf($params['genresult']);
		}
		
		$result = [
			'genresult' => $params['genresult'],
			'savefield_type' => 'field',
			'savefield' => $params['savefield'],
		];
		
		if(strpos($result['savefield'], 'PROPERTY_') !== false){
			$result['savefield_type'] = 'property';
			$result['savefield'] = str_replace('PROPERTY_', '', $result['savefield']);
		}elseif(strpos($result['savefield'], 'SEO_') !== false){
			$result['savefield_type'] = 'seo';
			$result['savefield'] = str_replace('SEO_', '', $result['savefield']);
		}

		if($result['savefield_type'] == 'field'){
			$el = new CIBlockElement;

			$addTypeHtml = 0;

			if(in_array($result['savefield'], ['PREVIEW_TEXT_HTML', 'DETAIL_TEXT_HTML'])){
				$result['savefield'] = str_replace('_HTML', '', $result['savefield']);
				$addTypeHtml = 1;
			}
			
			if(in_array($result['savefield'], ['PREVIEW_TEXT', 'DETAIL_TEXT']) && $params['html']){
				$addTypeHtml = 1;
			}

			$updateData = [
				$result['savefield'] => $result['genresult']
			];
			
			if(in_array($result['savefield'], ['PREVIEW_PICTURE', 'DETAIL_PICTURE'])){
				$updateData[$result['savefield']] = CFile::MakeFileArray($result['genresult']);
			}

			if($addTypeHtml){
				$updateData[$result['savefield'].'_TYPE'] = 'html';
			}

			$res = $el->Update($params['ID'], $updateData);
			if(!$res){
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_SAVE_ERROR') . $res->LAST_ERROR;
			}
		}
		
		if($result['savefield_type'] == 'property'){
			$propertyInfo = [];
			$properties = \CIBlockProperty::GetList([], ["CODE"=>$result['savefield'], "IBLOCK_ID"=>$params['IBLOCK_ID']]);
			if($prop_fields = $properties->GetNext()){
				$propertyInfo = $prop_fields;
			}
			
			$result['savefield_id'] = $propertyInfo['ID'];

			if($propertyInfo['PROPERTY_TYPE'] == 'L' && !$propertyInfo['USER_TYPE']){
				$save_xml_id = \Cutil::translit($result['genresult'], "ru", ["replace_space" => "_", "replace_other"=> "-"]);

				$db_enum_list = CIBlockProperty::GetPropertyEnum($propertyInfo['ID'], [], Array("IBLOCK_ID"=>$params['IBLOCK_ID'], "VALUE"=>$result['genresult']));
				if($ar_enum_list = $db_enum_list->GetNext()){
					$saveID = $ar_enum_list['ID'];
				}

				if(!$saveID){
					$db_enum_list = CIBlockProperty::GetPropertyEnum($propertyInfo['ID'], [], Array("IBLOCK_ID"=>$params['IBLOCK_ID'], "XML_ID"=>$save_xml_id));
					if($ar_enum_list = $db_enum_list->GetNext()){
						$saveID = $ar_enum_list['ID'];
					}
				}

				if(!$saveID){
					$ibpenum = new CIBlockPropertyEnum;
					$saveID = $ibpenum->Add([
						'PROPERTY_ID'=>$propertyInfo['ID'],
						'VALUE'=> $result['genresult'],
						'XML_ID'=> $save_xml_id,
					]);
				}

				$saveData = [$result['savefield'] => $saveID];
			}elseif($propertyInfo['USER_TYPE'] == 'HTML'){
				$result['savefield_type'] = 'property_html';
				
				$saveData = [
					$result['savefield'] => [
						'VALUE' => ['TYPE'=>'HTML', 'TEXT'=>$result['genresult']]
					]
				];
			}else{
				$saveData = [$result['savefield'] => $result['genresult']];
			}

			\CIBlockElement::SetPropertyValuesEx($params['ID'], $params['IBLOCK_ID'], $saveData);
		}
		
		if($result['savefield_type'] == 'seo'){
			$ipropElementTemplates = new InheritedProperty\ElementTemplates($params['IBLOCK_ID'], $params['ID']);			
			$ipropElementTemplates->set([$result['savefield'] => $result['genresult']]);
			
			$ipropElementValues = new InheritedProperty\ElementValues($params['IBLOCK_ID'], $params['ID']);
			$ipropElementValues->clearValues();
		}
		
		return $result;
	}
	
	// section
	static function getSectionBaseData($iblockId, $sectionId){
		$result = [];
		
		if(Loader::IncludeModule("iblock")){
			$domain = (CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"];

			$db_list = CIBlockSection::GetList([$by=>$order], ['IBLOCK_ID' => $iblockId, 'ID'=>$sectionId], false, ['ID', 'NAME', 'DESCRIPTION', 'IBLOCK_ID', 'SECTION_PAGE_URL', 'PICTURE', 'UF_*']);
			while($ar_result = $db_list->GetNext(true, false)){
				$result = $ar_result;
				
				// echo '<pre>'; print_r($result); echo '</pre>';

				$result['DESCRIPTION_HTML'] = $result['DESCRIPTION'];

				$result['SECTION_PAGE_URL'] = (CMain::IsHTTPS() ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . $result['SECTION_PAGE_URL'];
				
				if($result['PICTURE']){
					$result['PICTURE'] = $domain.CFile::GetPath($result['PICTURE']);
				}

				$ipropElementValues = new InheritedProperty\SectionValues($ar_result['IBLOCK_ID'], $ar_result['ID']);
				$values = $ipropElementValues->getValues();
				foreach($values as $key=>$val){
					$result['SEO_'.$key] = $val;
				}
			}
		}
		
		return $result;
	}
	
	static function checkSectionEmptySaveFiled($ibid, $sid, $field){
		$elementInfo = self::getSectionBaseData($ibid, $sid);
		return ['result' => ($elementInfo[$field] == ''), 'element_name' => $elementInfo['NAME']];
	}

	static function createSectionText($input){
		$result = [];
		
		$options = [
			'content_type' => 'text',
			'provider' => $input['provider'],
			'keynum' => intval($input['keynum'])
		];
		
		if($input['type'] == 'KEYWORDS'){
			$options['role'] = 'system';
		}

		$elementInfo = self::getSectionBaseData($input['IBLOCK_ID'], $input['ID']);
		$result['element_name'] = $elementInfo['NAME'];
		
		// echo '<pre>'; print_r($input); echo '</pre>';
		// echo '<pre>elementInfo '; print_r($elementInfo); echo '</pre>';
		
		if(!$elementInfo[$input['from']] && !in_array($input['operation'], ['TEMPLATE', 'IMAGE'])){
			$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_EMPTY_FIELD_FROM_ERROR');
		}
		
		if($input['mass_generation'] && self::isDemo()){
			$actualToken = intval(UTools::getSetting('alg_max_token'));
			if($actualToken >= self::TOKEN){
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_DEMO_MASS_GENERATE_LIMIT');
			}
		}
		
		if(!$result['error_message']){
			$result['question_show'] = (UTools::getSetting('show_query') == 'Y');
			$result['show_tokens'] = (UTools::getSetting('show_tokens') == 'Y');
			
			if($input['operation'] == 'TEMPLATE'){
				$input['template_section'] = Encoding::convertFromUtf($input['template_section']);
				$result['question'] = self::makeQuestionByTemplate($input['template_section'], $elementInfo);
			}elseif($input['operation'] == 'IMAGE'){
				$options['content_type'] = 'image';
				$options['input'] = $input;

				$input['template_image'] = Encoding::convertFromUtf($input['template_image']);
				$result['question'] = self::makeQuestionByTemplate($input['template_image'], $elementInfo);
			}else{
				$result['question'] = self::makeQuestion($input, $elementInfo[$input['from']]);
			}

			$result['content_type'] = $options['content_type'];
			
			foreach(GetModuleEvents(self::MODULE_ID, "modifySectionQuestionBeforeSend", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, [&$result['question'], $input, $elementInfo]);
			
			if($input['preview']){
				return $result;
			}

			$data = self::callChatProvider($result['question'], $options);

			$input['question'] = $result['question'];
			
			foreach(GetModuleEvents(self::MODULE_ID, "modifySectionAnswer", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, [&$data, $input, $elementInfo]);

			if($data['prepared']['error']){
				$result['next_key'] = ($data['next_key']) ? 1 : 0;
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CHATGPT_ERROR').' '.$data['prepared']['error_message'];
			}else{
				if($input['mass_generation'] && self::isDemo()){
					UTools::setSetting('alg_max_token', $actualToken + 1);
				}

				if($options['content_type'] == 'text'){
					if($data['prepared']['answer']){
						$result['answer'] = $data['prepared']['answer'];
						$result['used_tokens_cnt'] = intval($data['result']['usage']['total_tokens']);
						$result['used_tokens'] = Tools::calculateTokens($result['used_tokens_cnt'], $options['provider']);
					}else{
						$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
						if(isset($data['result']['detail']) && $data['result']['detail']){
							$result['error_message'] .= ': ' . $data['result']['detail'];
						}
					}
				}else{
					$image_url = Tools::prepareImageOutput($data['result']['data'][0], $input['output_format']);
					
					if($image_url){
						$result['answer'] = $image_url;
						$result['used_tokens_cnt'] = intval($data['result']['usage']['total_tokens']);
						$result['used_tokens'] = Tools::calculateTokens($result['used_tokens_cnt'], $options['provider']);
					}else{
						$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_CREATE_ERROR');
					}
				}
			}
		}
		
		// echo '<pre>input '; print_r($input); echo '</pre>';
		// echo '<pre>'; print_r($result); echo '</pre>';
		
		return $result;
	}
	
	static function saveToSection($params){
		Loader::includeModule('iblock');
		
		if($params['re_encoding']){
			$params['genresult'] = Encoding::convertFromUtf($params['genresult']);
		}
		
		$result = [
			'genresult' => $params['genresult'],
			'savefield_type' => 'field',
			'savefield' => $params['savefield'],
		];
		
		if(strpos($result['savefield'], 'UF_') !== false){
			$result['savefield_type'] = 'section_uf';
		}elseif(strpos($result['savefield'], 'SEO_') !== false){
			$result['savefield_type'] = 'seo';
			$result['savefield'] = str_replace('SEO_', '', $result['savefield']);
		}
		
		if($result['savefield_type'] == 'field' || $result['savefield_type'] == 'section_uf'){
			$bs = new CIBlockSection;
			
			$addTypeHtml = 0;

			if(in_array($result['savefield'], ['DESCRIPTION_HTML'])){
				$result['savefield'] = str_replace('_HTML', '', $result['savefield']);
				$addTypeHtml = 1;
			}
			
			if(in_array($result['savefield'], ['DESCRIPTION']) && $params['html']){
				$addTypeHtml = 1;
			}

			$updateData = [
				$result['savefield'] => $result['genresult']
			];

			if(in_array($result['savefield'], ['PICTURE'])){
				$updateData[$result['savefield']] = CFile::MakeFileArray($result['genresult']);
			}
			
			if($addTypeHtml){
				$updateData[$result['savefield'].'_TYPE'] = 'html';
			}

			$res = $bs->Update($params['ID'], $updateData);
			if(!$res){
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_SAVE_ERROR') . $res->LAST_ERROR;
			}
		}
		
		if($result['savefield_type'] == 'seo'){
			$ipropElementTemplates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($params['IBLOCK_ID'], $params['ID']);			
			$ipropElementTemplates->set([$result['savefield'] => $result['genresult']]);
			
			$ipropElementValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($params['IBLOCK_ID'], $params['ID']);
			$ipropElementValues->clearValues();
		}
		
		return $result;
	}
	
	static function writeModuleDebug($path, $module, $data){
		if(self::LOG_DEBUG){
			// UTools::simpleDataLog($path, $module, $data);
			AddMessage2Log($data, 'ag.chatgpt '.$module, 0);
		}
	}

	// tasks
	static function taskWorker($task_id){
		$log_path = '/logs_chatgpt/tasks/task_'.$task_id.'.txt';

		self::writeModuleDebug($log_path, 'TaskWorker #'.$task_id, '= Start Iteration');

		$elements = Tasks\Element::getTaskWorkElements($task_id, 1); // todo
		if(count($elements)){
			$start = microtime(true);

			$task = Tasks\Task::getTaskByID($task_id);
			
			foreach($elements as $element){
				$workTime = round(microtime(true) - $start, 2);
				if($workTime > 5) break;

				self::writeModuleDebug($log_path, 'TaskWorker #'.$task_id.' Work $element', $element);

				if(is_array($task)){
					if($task['UF_ETYPE'] == 'E'){
						$result = self::taskElementWork($task, $element);
					}elseif($task['UF_ETYPE'] == 'S'){
						$result = self::taskSectionWork($task, $element);
					}
				}

				if($result['error_message'] || $result['error_type']){
					self::writeModuleDebug($log_path, 'TaskWorker #'.$task_id.' error $result', $result);

					$finishTaskError = 0;

					$updateElement = [
						'UF_GENERATION_DATE' => date('d.m.Y H:i:s'),
						'UF_PARAMS' => (is_array($element['UF_PARAMS']) ? $element['UF_PARAMS'] : []),
					];
						
					$updateElement['UF_PARAMS']['error_type'] = $result['error_type'];
					$updateElement['UF_PARAMS']['error_message'] = $result['error_message'];

					if(in_array($result['error_type'], ['skip_value', 'not_found'])){
						$updateElement['UF_STATUS'] = 'skip';
					}elseif(in_array($result['error_type'], ['demo_limit', 'save_error'])){
						$updateElement['UF_STATUS'] = 'error';
						$finishTaskError = 1;
					}else{
						$errorCount = intval($element['UF_PARAMS']['error_count']) + 1;

						if($errorCount >= 5){
							$updateElement['UF_STATUS'] = 'error';
							$finishTaskError = 1;
						}
						
						$updateElement['UF_PARAMS']['error_count'] = $errorCount;
					}

					$r = Tasks\Element::update($element['ID'], $updateElement);

					if($finishTaskError){
						$updateTask = [
							'UF_STATUS' => 'stop_error',
							'UF_PARAMS' => $task['UF_PARAMS'],
						];

						$updateTask['UF_PARAMS']['error_type'] = $result['error_type'];
						$updateTask['UF_PARAMS']['error_message'] = $result['error_message'];
						
						$r = Tasks\Task::finishTask($task_id, $updateTask);
					}
				}else{
					self::writeModuleDebug($log_path, 'TaskWorker #'.$task_id.' success $result', $result);

					$updateElement = [
						'UF_STATUS' => 'success',
						'UF_GENERATION_DATE' => date('d.m.Y H:i:s'),
						'UF_GENERATION_RESULT' => $result['generated_data'],
						'UF_VALUE_BACKUP' => $result['base_field_value'],
						'UF_PARAMS' => '',
					];
					$r = Tasks\Element::update($element['ID'], $updateElement);
				}
			}
		}else{
			self::writeModuleDebug($log_path, 'TaskWorker #'.$task_id, 'finish task, no elements');

			Tasks\Task::finishTask($task_id, [
				'UF_STATUS' => 'finish',
			]);
		}
		
		self::writeModuleDebug($log_path, 'TaskWorker #'.$task_id, '= End Iteration');

		return 'CArturgolubevChatgpt::taskWorker('.$task_id.');';
	}
	
		static function taskElementWork($task, $element){
			$result = [];

			$options = [
				'content_type' => 'text',
				'provider' => $task['UF_PARAMS']['provider'],
				'keynum' => 0,
			];

			$input = [
				'is_automatic_task' => 1,
			];
			
			$elementInfo = self::getElementBaseData($element['UF_ELEMENT']);
			if(!is_array($elementInfo) || !count($elementInfo)){
				$result['error_type'] = 'not_found';
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ELEMENT_NOT_FOUND');
			}

			$result['base_field_value'] = $elementInfo[$task['UF_PARAMS']['save_field']];

			if(self::checkDemoAccess()){
				$result['error_type'] = 'demo_limit';
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_DEMO_MASS_GENERATE_LIMIT');
			}

			self::writeModuleDebug(false, 'taskElementWork empty check base_field_value', $result['base_field_value']);

			if($task['UF_PARAMS']['save_only_empty'] == 'Y'){
				if($result['base_field_value']){
					$result['error_type'] = 'skip_value';
					$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_SKIP_BY_VALUE');
				}
			}
			
			if(!$result['error_message']){
				$input['template_element'] = Encoding::convertFromUtf($task['UF_PROMPT']);
				$input['question'] = self::makeQuestionByTemplate($input['template_element'], $elementInfo);
				
				foreach(GetModuleEvents(self::MODULE_ID, "modifyElementQuestionBeforeSend", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, [&$input['question'], $input, $elementInfo]);
				
				self::writeModuleDebug(false, 'taskElementWork before request input', $input);

				$data = self::callChatProvider($input['question'], $options);

				self::writeModuleDebug(false, 'taskElementWork after request data', $data);
				
				foreach(GetModuleEvents(self::MODULE_ID, "modifyElementAnswer", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, [&$data, $input, $elementInfo]);

				if($data['prepared']['error']){
					$result['error_type'] = $data['prepared']['error_type'];
					$result['error_message'] = $data['prepared']['error_message'];
				}else{
					self::checkDemoCounts();
					$result['generated_data'] = $data['prepared']['answer'];
				}
			}

			if(!$result['error_message']){
				$saveParams = [
					'genresult' => $result['generated_data'],
					'savefield' => $task['UF_PARAMS']['save_field'],
					'ID' => $element['UF_ELEMENT'],
					'IBLOCK_ID' => $task['UF_IBLOCK'],
				];

				self::writeModuleDebug(false, 'taskElementWork before save saveParams', $saveParams);

				$saveResult = self::saveToElement($saveParams);
				if($saveResult['error_message']){
					$result['error_type'] = 'save_error';
					$result['error_message'] = $saveResult['error_message'];
				}
			}
			
			self::writeModuleDebug(false, 'taskElementWork result', $result);

			return $result;
		}


		static function taskSectionWork($task, $element){
			// echo '<pre>task section '; print_r($task); echo '</pre>';
			// echo '<pre>element '; print_r($element); echo '</pre>';

			$result = [];

			$options = [
				'content_type' => 'text',
				'provider' => $task['UF_PARAMS']['provider'],
				'keynum' => 0,
			];

			$input = [
				'is_automatic_task' => 1,
			];

			$elementInfo = self::getSectionBaseData($task['UF_IBLOCK'], $element['UF_ELEMENT']);
			if(!is_array($elementInfo) || !count($elementInfo)){
				$result['error_type'] = 'not_found';
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_ELEMENT_NOT_FOUND');
			}

			$result['base_field_value'] = $elementInfo[$task['UF_PARAMS']['save_field']];

			if(self::checkDemoAccess()){
				$result['error_type'] = 'demo_limit';
				$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_DEMO_MASS_GENERATE_LIMIT');
			}

			// echo '<pre>'; print_r($result); echo '</pre>';
			// echo '<pre>'; print_r($task); echo '</pre>';
			// die();

			if($task['UF_PARAMS']['save_only_empty'] == 'Y'){
				if($result['base_field_value']){
					$result['error_type'] = 'skip_value';
					$result['error_message'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_GENERAL_SKIP_BY_VALUE');
				}
			}

			if(!$result['error_message']){
				$input['template_section'] = Encoding::convertFromUtf($task['UF_PROMPT']);
				$input['question'] = self::makeQuestionByTemplate($input['template_section'], $elementInfo);

				foreach(GetModuleEvents(self::MODULE_ID, "modifySectionQuestionBeforeSend", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, [&$result['question'], $input, $elementInfo]);
				
				self::writeModuleDebug(false, 'createSectionText AutoTask', $input);
				
				$data = self::callChatProvider($input['question'], $options);

				foreach(GetModuleEvents(self::MODULE_ID, "modifySectionAnswer", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, [&$data, $input, $elementInfo]);

				if($data['prepared']['error']){
					$result['error_type'] = $data['prepared']['error_type'];
					$result['error_message'] = $data['prepared']['error_message'];
				}else{
					self::checkDemoCounts();
					$result['generated_data'] = $data['prepared']['answer'];
				}
			}

			if(!$result['error_message']){
				$saveParams = [
					'genresult' => $result['generated_data'],
					'savefield' => $task['UF_PARAMS']['save_field'],
					'ID' => $element['UF_ELEMENT'],
					'IBLOCK_ID' => $task['UF_IBLOCK'],
				];

				$saveResult = self::saveToSection($saveParams);
				if($saveResult['error_message']){
					$result['error_type'] = 'save_error';
					$result['error_message'] = $saveResult['error_message'];
				}
			}

			return $result;
		}

	// events
	static function addActionMenu(&$list){
		$list = \Arturgolubev\Chatgpt\Ehandlers::addActionMenu($list);
	}
	
	static function onEpilog(){
		if(!Loader::IncludeModule(self::MODULE_ID) || !defined("ADMIN_SECTION")) return 0;
		
		$cur = UTools::GetCurPage();
		if(Tools::checkRights('question')){
			$element = ($cur == '/bitrix/admin/iblock_element_edit.php' ? 1 : 0);
			$section = ($cur == '/bitrix/admin/iblock_section_edit.php' ? 1 : 0);
			
			if($element || $section){
				\CJSCore::Init(["ag_chatgpt_base"]);
				
				$genOption = [
					'ENTITY_TYPE' => ($element ? 'element' : 'section'),
					'ID' => $_GET['ID'],
					'IBLOCK_ID' => $_GET['IBLOCK_ID'],
				];
				
				echo '<script>agcg.initElementButton('.CUtil::PhpToJSObject($genOption).');</script>';
			}
			
			$isSectionPage = ($cur == '/bitrix/admin/iblock_section_admin.php');
			$isCatalogPage = ($cur == '/bitrix/admin/iblock_list_admin.php' || $cur == '/bitrix/admin/cat_product_list.php' || $isSectionPage);
			$isCatalogElementPage = ($cur == '/bitrix/admin/iblock_element_admin.php' || $cur == '/bitrix/admin/cat_product_admin.php');
			
			if($isCatalogPage || $isCatalogElementPage){
				\CJSCore::Init(["ag_chatgpt_base", 'ag_chatgpt_tasks']);
				
				$workElements = Tools::getWorkElements([
					'isSectionPage' => $isSectionPage
				]);
				
				if(is_array($_POST["action"])){
					foreach($_POST["action"] as $action){
						if($action == "agcg_generate")
							$showGenerationWindow = 1;
						
						if($action == "agcg_queue")
							$showAddTaskWindow = 1;
					}
				}else{
					$action = $_POST["action"];

					if($action == 'agcg_generate')
						$showGenerationWindow = 1;
					
					if($action == 'agcg_queue')
						$showAddTaskWindow = 1;
				}
				
				if($showGenerationWindow){
					$demo = self::isDemo();
					$dcount = self::TOKEN - intval(UTools::getSetting('alg_max_token'));
					if($dcount < 0){
						$dcount = 0;
					}
					?>
						<script>
							var agcgInitParams = {
								demo: <?=intval($demo)?>,
								dcount: <?=$dcount?>,
								action_all_rows: <?=intval($workElements['action_all_rows'])?>,
								ibid: <?=IntVal($_GET["IBLOCK_ID"])?>,
								eids: <?=\CUtil::PhpToJSObject($workElements['elements'])?>,
								sids: <?=\CUtil::PhpToJSObject($workElements['sections'])?>,
								limits: "<?=(defined('AG_CHATGPT_UNLOCK_LIMITS') && AG_CHATGPT_UNLOCK_LIMITS == 'Y') ? 'N' : 'Y'?>",
							};
							top.agcg.initMassWork(agcgInitParams);
						</script>
					<?
				}

				if($showAddTaskWindow){
					?>
						<script>
							var agcgInitParams = {
								action_all_rows: <?=intval($workElements['action_all_rows'])?>,
								ibid: <?=IntVal($_GET["IBLOCK_ID"])?>,
								eids: <?=\CUtil::PhpToJSObject($workElements['elements'])?>,
								sids: <?=\CUtil::PhpToJSObject($workElements['sections'])?>,
								limits: "<?=(defined('AG_CHATGPT_UNLOCK_LIMITS') && AG_CHATGPT_UNLOCK_LIMITS == 'Y') ? 'N' : 'Y'?>",
							};
							top.agcg.initAddWindow(agcgInitParams);
						</script>
					<?
				}
			}
		}
	}
}
?>