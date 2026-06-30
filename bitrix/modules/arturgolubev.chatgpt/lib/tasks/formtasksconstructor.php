<?
namespace Arturgolubev\Chatgpt\Tasks;

use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Unitools as UTools,
	\Arturgolubev\Chatgpt\Tools,
	\Arturgolubev\Chatgpt\Tasks;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.chatgpt/admin/automatic_tasks.php");

class FormTasksConstructor extends \Arturgolubev\Chatgpt\FormConstructor {
	static function makeTasksHtml($postFields){
		$arFields = [];

		$curElement = ($postFields['id'] ? Tasks\Task::getTaskByID($postFields['id']) : false);
		if(is_array($curElement)){
			$postFields['iblock_id'] = $curElement['UF_IBLOCK'];
			$postFields['entity_type'] = $curElement['UF_ETYPE'];

			$status_text = Loc::getMessage('ARTURGOLUBEV_CHATGPT_TASKS_EDIT_FIELD_STATUS_'.$curElement['UF_STATUS']);

			if($curElement['UF_STATUS'] == 'stop_error'){
				$status_text .= '<br>'.$curElement['UF_PARAMS']['error_message'].' ['.$curElement['UF_PARAMS']['error_type'].']';
			}

			$arFields['status_text'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_TASKS_EDIT_FIELD_STATUS'),
				'TYPE' => 'simpletext',
				'CLASS' => '',
				'VALUE' => $status_text,
			];
			
			$arFields['enity_text'] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_TASKS_EDIT_FIELD_IBLOCK_ENTITY_TYPE'),
				'TYPE' => 'simpletext',
				'CLASS' => '',
				'VALUE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_TASKS_EDIT_FIELD_IBLOCK_'.$curElement['UF_ETYPE']),
			];
		}

		$arFields['name'] = [
			'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_TASKS_EDIT_FIELD_NAME'),
			'TYPE' => 'text',
			'CLASS' => 'js-required',
		];
		$arFields['provider'] = [
			'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER'),
			'TYPE' => 'select',
			'CLASS' => '',
			'VALUES_GROUPS' => 0,
			'VALUES' => []
		];

		// provider
		if(true){
			$aiList = Tools::getAiList();

			if(in_array('chatgpt', $aiList)){
				$arFields['provider']['VALUES']['chatgpt'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER_CHATGTP');
			}
			if(in_array('sber', $aiList)){
				$arFields['provider']['VALUES']['sber'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER_SBER');
			}
			if(in_array('deepseek', $aiList)){
				$arFields['provider']['VALUES']['deepseek'] = Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_PROVIDER_DEEPSEEK');
			}
		}

		// type fields
		if($postFields['entity_type'] == 'S'){
			$arFields = array_merge($arFields, self::makeTasksSectionHtml($postFields));
		}else{
			$arFields = array_merge($arFields, self::makeTasksElementHtml($postFields));
		}

		// save field
		$arFields['save_field'] = [
			'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_MASS_CREATE_SAVE_FIELD'),
			'TYPE' => 'select',
			'CLASS' => 'js-required',
			'VALUES_GROUPS' => 1,
			'VALUES' => []
		];

		$arFields['save_only_empty'] = [
			'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_MASS_CREATE_SAVE_ONLY_EMPTY'),
			'TYPE' => 'checkbox',
			'CLASS' => '',
			'DEFAULT' => 'Y',
			'VALUE' => '',
		];
		
		if($postFields['iblock_id']){
			if($postFields['entity_type'] == 'S'){
				$saveFields = self::getSectionFieldsToSave($postFields['iblock_id'], '', '');
			}else{
				$saveFields = self::getElementFieldsToSave($postFields['iblock_id'], '', '');
			}

			foreach($saveFields as $groupKey=>$items){
				$selectItems = [];
				foreach($items as $item){
					$selectItems[$item['CODE']] = $item['NAME'];
				}
				
				if(count($selectItems)){
					$arFields['save_field']['VALUES'][] = [
						'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_'.$groupKey),
						'ITEMS' => $selectItems
					];
				}
			}
		}

		if(is_array($curElement)){
			// echo '<pre>'; print_r($curElement); echo '</pre>';

			$arFields['name']['VALUE'] = $curElement['UF_NAME'];
			$arFields['prompt']['VALUE'] = $curElement['UF_PROMPT'];

			$arFields['provider']['VALUE'] = $curElement['UF_PARAMS']['provider'];
			$arFields['save_field']['VALUE'] = $curElement['UF_PARAMS']['save_field'];
			$arFields['save_only_empty']['VALUE'] = $curElement['UF_PARAMS']['save_only_empty'];
		}

		// echo '<pre>'; print_r($postFields); echo '</pre>';
		// echo '<pre>'; print_r($arFields); echo '</pre>';

		return self::_makeHtmlForJsForm($arFields);
	}

	static function makeTasksSectionHtml($postFields){
		$arFields = [];

		// prompt
		$arFields['prompt'] = [
			'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_TASKS_EDIT_FIELD_PROMPT'),
			'TYPE' => 'textarea',
			'CLASS' => 'js-required',
			'DEFAULT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DEFAULT'),
			'VALUE' => '',
			'HINT_BUTTON' => 1,
			'HINT_BUTTON_VARIANTS' => [],
			'TEMPL_BUTTON_VARIANTS' => [],
		];
			
		$arFields['prompt']['HINT_BUTTON_VARIANTS'][] = [
			'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_SECTION_GROUP_FIELDS'),
			'ITEMS' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_NAME'),
				'DESCRIPTION' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_DESCRIPTION'),
				// 'PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_FROM_SECTION_PICTURE'),
			]
		];
		
		$arProps = self::_getSectionFormProperties($postFields['iblock_id']);
		if(count($arProps)){
			$arFields['prompt']['HINT_BUTTON_VARIANTS'][] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_SECTION_GROUP_PROPERTIES'),
				'ITEMS' => $arProps,
			];
		}

		$rsEnum = \CUserFieldEnum::GetList(array(), array(
			'USER_FIELD_NAME' => 'UF_TYPE',
			'XML_ID' => 'sections'
		));
		if($arEnum = $rsEnum->Fetch()){
			$savedTemplates = Tools::getSavedTemplates([
				'UF_TYPE' => $arEnum['ID']
			]);
		}

		if(is_array($savedTemplates) && count($savedTemplates)){
			$arFields['prompt']['TEMPL_BUTTON_VARIANTS'][] = [
				'name' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DEFAULT_NAME'),
				'template' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DEFAULT'),
			];
			foreach($savedTemplates as $template){
				$arFields['prompt']['TEMPL_BUTTON_VARIANTS'][] = [
					'name' => $template['UF_NAME'],
					'template' => $template['UF_TEMPLATE'],
				];
			}
		}

		return $arFields;
	}
	
	static function makeTasksElementHtml($postFields){
		$arFields = [];

		// prompt
		$arFields['prompt'] = [
			'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_TASKS_EDIT_FIELD_PROMPT'),
			'TYPE' => 'textarea',
			'CLASS' => 'js-required',
			'DEFAULT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DEFAULT'),
			'VALUE' => '',
			'HINT_BUTTON' => 1,
			'HINT_BUTTON_VARIANTS' => [],
			'TEMPL_BUTTON_VARIANTS' => [],
		];
			
		$arFields['prompt']['HINT_BUTTON_VARIANTS'][] = [
			'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_FIELDS'),
			'ITEMS' => [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_NAME'),
				'PREVIEW_TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PREVIEW_TEXT'),
				'DETAIL_TEXT' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DETAIL_TEXT'),
				'PARENT_SECTION_NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PARENT_SECTION_NAME'),
				// 'PREVIEW_PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_PREVIEW_PICTURE'),
				// 'DETAIL_PICTURE' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DETAIL_PICTURE'),
			]
		];
		
		$arProps = self::_getElementFromProperties($postFields['iblock_id']);
		if(count($arProps)){
			$arFields['prompt']['HINT_BUTTON_VARIANTS'][] = [
				'NAME' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_GROUP_PROPERTIES'),
				'ITEMS' => array_merge(['ALL_PROPS' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_ALL_PROPERTIES')], $arProps),
			];
		}

		$rsEnum = \CUserFieldEnum::GetList(array(), array(
			'USER_FIELD_NAME' => 'UF_TYPE',
			'XML_ID' => 'elements'
		));
		if($arEnum = $rsEnum->Fetch()){
			$savedTemplates = Tools::getSavedTemplates([
				'UF_TYPE' => $arEnum['ID']
			]);
		}

		if(is_array($savedTemplates) && count($savedTemplates)){
			$arFields['prompt']['TEMPL_BUTTON_VARIANTS'][] = [
				'name' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DEFAULT_NAME'),
				'template' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_FORM_ELEMENT_CREATE_TEMPLATE_DEFAULT'),
			];
			foreach($savedTemplates as $template){
				$arFields['prompt']['TEMPL_BUTTON_VARIANTS'][] = [
					'name' => $template['UF_NAME'],
					'template' => $template['UF_TEMPLATE'],
				];
			}
		}

		return $arFields;
	}
}