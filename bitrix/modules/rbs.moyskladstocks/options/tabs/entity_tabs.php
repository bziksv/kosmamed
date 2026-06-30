<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\MoyskladStocks\ApiNew;
use \Rbs\MoyskladStocks\Utils;
use \Rbs\MoyskladStocks\Internals\OptionUtils;
use \Rbs\MoyskladStocks\Services\BitrixFeatures;

$arEntities = ['product', 'variant', 'bundle', 'service', 'productfolder'];
if (count($selectCatalog) > 0) {

	foreach ($arEntities as $entity) {

		if ($isSaveHit) {
			\Rbs\MoyskladStocks\Agent::delete('import_' . $entity);
		}

		$arAllOptions['import' . $entity][] = GetMessage("MAIN_SETTINGS_DOC", ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity]);

		if ($entity === 'productfolder') {
			$entityTabFile = __DIR__ . '/entity_tabs/productfolder.php';
			$fileInfo = pathinfo($entityTabFile);
			if ($fileInfo['extension'] === 'php') {
				include_once $entityTabFile;
			}
			continue;
		}

		$arAllOptions['import' . $entity][] = GetMessage('ALL_PARAMS', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/main']);

		$iblockId = $_REQUEST["im_{$entity}_iblock"] ?: \Rbs\MoyskladStocks\Config::getIblockId($entity);

		$arAllOptions['import' . $entity][] = ["im_{$entity}_iblock", GetMessage('IBLOCK_ID'), '', ['selectbox', $selectCatalog]];
		$showAlertForSaveOption['select'][] = "im_{$entity}_iblock";

		if ($iblockId > 0 && !empty($arIblockTypeList[$iblockId])) {
			$arAllOptions['import' . $entity][] = ['note' => GetMessage('IBLOCK_NAVIGATION', [
				'#IBLOCK_ID#' => $iblockId,
				'#IBLOCK_TYPE#' => $arIblockTypeList[$iblockId]
			])];
		}

		if ($entity !== 'variant' && $iblockId > 0) {
			$allSections = \Bitrix\Iblock\SectionTable::getList(['filter' => ['IBLOCK_ID' => $iblockId, 'DEPTH_LEVEL' => 1]])->fetchAll();
			if (count($allSections) > 0) {
				$selectSection = ['N' => GetMessage('ROOT_SECTION')];
				foreach ($allSections as $section) {
					$selectSection[$section['ID']] = "[{$section['ID']}] {$section['NAME']}";
				}
				$arAllOptions['import' . $entity][] = ["im_{$entity}_section", GetMessage('SECTION_ID'), '', ['selectbox', $selectSection]];
			}
		}

		$arAllOptions['import' . $entity][] = ["{$entity}_iblock_req", GetMessage('IBLOCK_REQ'), '', ['checkbox', "N", $paramsCheckBox]];

		if ($entity !== 'variant') {
			$arAllOptions['import' . $entity][] = ["{$entity}_uponly", GetMessage('UP_ONLY'), '', ['checkbox', "N", $paramsCheckBox]];
		}


		$arAllOptions['import' . $entity][] = GetMessage('IMPORT_NEW', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/base']);

		$arAllOptions['import' . $entity][] = ["im_{$entity}_enable", GetMessage('ENABLE_IMPORT'), '', ['checkbox', 'N', $paramsCheckBox]];

		if (class_exists('CCatalogProductSet') && $entity === 'bundle') {
			$arAllOptions['import' . $entity][] = ["im_{$entity}_type_imp", GetMessage('BUNDLE_TYPE_IMP'), '', ['selectbox', ['DEFAULT' => GetMessage('BUNDLE_TYPE_IMP_DEFAULT'), 'BUNDLE' => GetMessage('BUNDLE_TYPE_IMP_BUNDLE')]]];
			$currentTypeBundle = $_REQUEST["im_{$entity}_type_imp"] ?: \Rbs\MoyskladStocks\Config::getImportBundleType();
			if ($currentTypeBundle === 'BUNDLE' && count($multiSelectCatalog) > 0) {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_type_imp_iblock", GetMessage('BUNDLE_PARTS_IBLOCK_ID'), '', ['multiselectbox', $multiSelectCatalog]];
			}
		}

		if ($entity == 'variant') {
			$arAllOptions['import' . $entity][] = ["{$entity}_clear_xmlid", GetMessage('IMPORT_PARAMS_CLEAR_XMLID'), '', ['checkbox', "N", $paramsCheckBox]];
		}

		if (count($selectGroup) > 0) {
			$arAllOptions['import' . $entity][] = ["im_{$entity}_group", GetMessage('GROUP_ID'), '', ['selectbox', $selectGroup]];
		}

		if ($entity !== 'variant') {

			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_ms_section_root", GetMessage('IMPORT_PARAMS_MS_SECTION_ROOT'), '', ['checkbox', "N", $paramsCheckBox]];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_section_off", GetMessage('IMPORT_PARAMS_SECTION_OFF'), '', ['checkbox', "N", $paramsCheckBox]];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_section_keep", GetMessage('IMPORT_PARAMS_SECTION_KEEP'), '', ['checkbox', "N", $paramsCheckBox]];
		}

		$arAllOptions['import' . $entity][] = ["im_{$entity}_p_include_archived", GetMessage('IMPORT_PARAMS_INCLUDE_ARCHIVED'), '', ['checkbox', "N", $paramsCheckBox]];
	
		$arAllOptions['import' . $entity][] = GetMessage('FILTER_SECTION', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/filter']);
		$arAllOptions['import' . $entity][] = ["im_{$entity}_filter_prop", GetMessage('FILTER_PROP'), 'N', ['selectbox', ['N' => GetMessage('NON_USE')] + $selectBoolProps]];
		$arAllOptions['import' . $entity][] = ["im_{$entity}_filter_prop_value", GetMessage('FILTER_PROP_VALUE'), 'Y', ['selectbox', ['Y' => GetMessage('FILTER_PROP_Y'), 'N' => GetMessage('FILTER_PROP_N')]]];

		$arAllOptions['import' . $entity][] = ['note' => GetMessage("ENTITY_SYNC_COUNT", [
			'#entity#' => $entity
		])];

		//---------

		$propsFiles = \Bitrix\Iblock\PropertyTable::getList(['filter' => ['IBLOCK_ID' => $iblockId, 'PROPERTY_TYPE' => 'F', 'MULTIPLE' => 'Y']])->fetchAll();

		$selectFilesProp = ['N' => GetMessage('NON_SYNC')];
		if (count($propsFiles) > 0 && $iblockId > 0) {
			foreach ($propsFiles as $prop) {
				$selectFilesProp[$prop['ID']] = "[{$prop['CODE']}] {$prop['NAME']}";
			}
		}

		$arAllOptions['import' . $entity][] = GetMessage('IMPORT_FIELDS_PARAMS', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/fields']);

		$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('CIBLOCK_ADD_PARAMS'), '', ['statichtml']];

		$arAllOptions['import' . $entity][] = ["im_{$entity}_p_workflow", GetMessage('CIBLOCK_ADD_WORKFLOW'), '', ['checkbox', "N", $paramsCheckBox]];
		$arAllOptions['import' . $entity][] = ["im_{$entity}_p_updsearch", GetMessage('CIBLOCK_ADD_UPDSEARCH'), '', ['checkbox', "N", $paramsCheckBox]];
		$arAllOptions['import' . $entity][] = ["im_{$entity}_p_resizepic", GetMessage('CIBLOCK_ADD_RESIZEPIC'), '', ['checkbox', "N", $paramsCheckBox]];

		//SYM_CODE
		$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_SYM_CODE'), '', ['statichtml']];

		if ($iblockId > 0) {
			$requiredSecCodeParams = \Rbs\MoyskladStocks\Services\ConfigurationUtils::getIblockSymbolicCodeParams((int)$iblockId, 'CODE');
			if ($requiredSecCodeParams['required']) {
				$arOptionsHardSet["im_{$entity}_p_code"] = 'Y';
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code", GetMessage('HOOK_PARAMS_CODE_GEN'), 'Y', ['checkbox', "Y", 'disabled']];
			} else {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code", GetMessage('HOOK_PARAMS_CODE_GEN'), 'Y', ['checkbox', "N", $paramsCheckBox]];
			}
			if ($requiredSecCodeParams['uniq']) {
				$arOptionsHardSet["im_{$entity}_p_code_uniq"] = 'Y';
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code_uniq", GetMessage('HOOK_PARAMS_CODE_UNIQ'), 'Y', ['checkbox', "Y", 'disabled']];
			} else {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code_uniq", GetMessage('HOOK_PARAMS_CODE_UNIQ'), 'Y', ['checkbox', "N", $paramsCheckBox]];
			}
		} else {
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code", GetMessage('HOOK_PARAMS_CODE_GEN'), 'Y', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code_uniq", GetMessage('HOOK_PARAMS_CODE_UNIQ'), 'Y', ['checkbox', "N", $paramsCheckBox]];
		}

		if ($iblockId > 0) {
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_translit", GetMessage('HOOK_PARAMS_CODE_IBLOCK'), '', ['checkbox', "N", $paramsCheckBox]];
		}

		if ($entity !== 'variant') {
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code_from_pf", GetMessage('GET_CODE_PF_PARAMS'), '', ['checkbox', "N", $paramsCheckBox]];
		}


		//DESCRIPTION
		$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_DESCR'), '', ['statichtml']];

		$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr_type", GetMessage('IMPORT_PARAMS_DESCR_TYPE_TEXT'), '', ['selectbox',  $descrTextTypes]];

		if ($entity !== 'variant' && count($selectStrPropsMs) > 0) {
			$selectStrPropsMs = array_merge(['DEFAULT' => GetMessage('DESCR_FIELD')], $selectStrPropsMs);
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr_source", GetMessage('IMPORT_PARAMS_DESCR_SOURCE'), '', ['selectbox', $selectStrPropsMs]];
		}

		$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr_full_type", GetMessage('IMPORT_PARAMS_DESCR_FULL_TYPE_TEXT'), '', ['selectbox',  $descrTextTypes]];

		if ($entity !== 'variant' && count($selectStrPropsMs) > 0) {
			$selectStrPropsMs = array_merge(['DEFAULT' => GetMessage('DESCR_FIELD')], $selectStrPropsMs);
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr_full_source", GetMessage('IMPORT_PARAMS_DESCR_FULL_SOURCE'), '', ['selectbox', $selectStrPropsMs]];
		}

		$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr_delete", GetMessage('DELETE_DESCR_IF_EMPTY_IN_MS'), '', ['checkbox', "N", $paramsCheckBox]];

		//IMAGES
		if (count($selectFilesProp) > 1) {
			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_PIC'), '', ['statichtml']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_img_del", GetMessage('IMPORT_PARAMS_IMG_DEL'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_img_more_prop", GetMessage('IMPORT_PARAMS_IMG_PROP'), '', ['selectbox', $selectFilesProp]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_img_more_all", GetMessage('IMPORT_PARAMS_IMG_MORE_ALL'), '', ['checkbox', "N", $paramsCheckBox]];
		}

		//SIZES
		if ($entity !== 'service' && $entity !== 'variant') {
			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_PRODUCT'), '', ['statichtml']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_weight_m", GetMessage('IMPORT_PARAMS_WEIGHT_M'), '1000', ['text', '']];
			if (count($selectGabbPropsMs) > 0) {
				$selectGabbPropsMs = array_merge(['N' => GetMessage('NON_SYNC')], $selectGabbPropsMs);
				foreach (['width', 'height', 'length'] as $propSize) {
					$arAllOptions['import' . $entity][] = ["im_{$entity}_p_{$propSize}", GetMessage('IMPORT_PARAMS_SIZES_' . $propSize), '', ['selectbox', $selectGabbPropsMs]];
				}
			}
		}

		if ($entity !== 'variant') {

			//PRODUCT_TYPE
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_ignore_prodtype", GetMessage('IS_IGNORE_PRODUCT_TYPE'), '', ['checkbox', "N", $paramsCheckBox]];

			//ACTIVE PROPERTY
			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_ACTIVE_PROPERTY'), '', ['statichtml']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_active_prop", GetMessage('IMPORT_PARAMS_ACTIVE_PROPERTY_PROP'), '', [
				'selectbox',
				['N' => GetMessage('NON_SYNC')] + $selectBoolProps
			]];

			//SORT
			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_SORT'), '', ['statichtml']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_sort_prop", GetMessage('IMPORT_PARAMS_SORT_PROP'), '', [
				'selectbox',
				['N' => GetMessage('NON_SYNC')] + $selectPropsForProps['N']
			]];

			//Ratio
			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_RATIO'), '', ['statichtml']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_ratio_prop", GetMessage('IMPORT_PARAMS_RATIO_PROP'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectPropsForProps['N']]];
		}


		//wenhook logic

		$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('HOOK_PARAMS_EVENTS'), '', ['statichtml']];

		$arAllOptions['import' . $entity][] = ["{$entity}_create_hook", GetMessage('HOOK_PARAMS_CREATE_ALWAYS'), 'CREATE', ['selectbox', [
			'CREATE' => GetMessage('HOOK_CREATE'),
			'ALL' => GetMessage('HOOK_ALL'),
		]]];

		if ($entity == 'variant') {
			$arAllOptions['import' . $entity][] = ["{$entity}_load_agent", GetMessage('VARIANT_LOAD_AGENT'), '', ['checkbox', "N", $paramsCheckBox]];
		}
		$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_delete", GetMessage('HOOK_PARAMS_DELETE'), '', ['checkbox', "N", $paramsCheckBox]];

		//---------

		//VARIANT_PROPS
		if ($entity === 'variant' && $iblockId > 0) {
			$arAllOptions['import' . $entity][] = GetMessage('IMPORT_FIELDS_VARIANTS', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/variant-props']);

			$arAllOptions['import' . $entity][] = ["im_{$entity}_is_props", GetMessage('IS_IMPORT_FIELDS_VARIANTS'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ['note' => GetMessage('IS_IMPORT_FIELDS_VARIANTS_NOTE')];

			$rsProperty = \Bitrix\Iblock\PropertyTable::getList(array(
				'filter' => [
					'IBLOCK_ID' => $iblockId,
					'PROPERTY_TYPE' => ['S', 'N', 'L', 'E'],
					'MULTIPLE' => 'N'
				]
			))->fetchAll();

			$propsMs = ApiNew::get('/entity/variant/metadata');

			if (Utils::is_count($rsProperty) > 0 && Utils::is_success($propsMs)) {

				$bxProps = ['N' => GetMessage('NON_SYNC')];

				foreach ($rsProperty as $propBx) {
					$propIdForName = $propBx['CODE'] ?: $propBx['ID'];
					if ($propBx['PROPERTY_TYPE'] === 'S' && !empty($propBx['USER_TYPE'])) {
						if (!in_array($propBx['USER_TYPE'], ['directory'])) {
							continue;
						}
					}
					$bxProps[$propBx['ID']] = "[{$propIdForName}] {$propBx['NAME']}";
				}

				if (count($bxProps) > 1 && Utils::array_exists($propsMs, 'characteristics')) {
					foreach ($propsMs->characteristics as $prop) {
						$arAllOptions['import' . $entity][] = ["im_{$entity}_pp_" . $prop->id, $prop->name, '', ['selectbox', $bxProps]];
					}
				}
			}
		}

		//PROPS
		
		$arAllOptions['import' . $entity][] = GetMessage('IMPORT_PROPS', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/props']);

		if ($iblockId > 0) {

			$propertyOption = new \Rbs\MoyskladStocks\Internals\PropertyOption($entity, (int)$iblockId);

			if ($propertyOption->hasBxProps()) {

				$arAllOptions['import' . $entity][] = [
					'note' => GetMessage('IMPORT_PROPS_NOTE')
				];

				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_emptyim", GetMessage('PROP_EMPTY_IMPORT'), '', ['checkbox', "N", $paramsCheckBox]];

				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_proplist", GetMessage('PROP_LIST'), '', ['multiselectbox', $propertyOption->getBxPropertyNames()]];

				$propList = $_REQUEST["im_{$entity}_p_proplist"] ?: \Rbs\MoyskladStocks\Config::getOptionArray("im_{$entity}_p_proplist");
				if (count($propList) > 0) {
					foreach ($propList as $propId) {
						$propId = (int)$propId;
						$arAllOptions['import' . $entity][] = ["im_{$entity}_p_prop_{$propId}", $propertyOption->getBxPropertyName($propId), '', ['selectbox',  $propertyOption->getPropertyVariantsForPropId($propId)]];
					}
				}
			}

		} else {
			$arAllOptions['import' . $entity][] = [
				'note' => GetMessage('CHOOSE_IBLOCK_FOR_ASSOC_PROPS')
			];
		}

		//RELATIONS FOR HARD OPTIONS

		//disabled import folder if section_off
		$sectionOff = OptionUtils::getOptionValueForOptionPage("im_{$entity}_p_section_off", "N", $isSaveHit) === 'Y';
		if($sectionOff) {
			$arOptionsHardSet["im_{$entity}_p_folder"] = 'N';
			$arOptionsHardSet["im_{$entity}_up_folder"] = 'N';
			$arOptionsHardSet["im_{$entity}_wh_folder"] = 'N';
			$arOptionsHardSet["im_{$entity}_p_section_keep"] = 'N';
		} else {
			$arOptionsHardSet["im_{$entity}_p_folder"] = 'Y';
		}

		//disabled active by archived if include_archived in import
		$includeArchivedItems = OptionUtils::getOptionValueForOptionPage("im_{$entity}_p_include_archived", "N", $isSaveHit) === 'Y';
		if($includeArchivedItems) {
			$arOptionsHardSet["im_{$entity}_p_archived"] = 'Y';
			$arOptionsHardSet["im_{$entity}_up_archived"] = 'Y';
			$arOptionsHardSet["im_{$entity}_wh_archived"] = 'Y';
		} else {
			$arOptionsHardSet["im_{$entity}_p_archived"] = 'N';
		}

		//disabled active by outer sec if no chooosen ms root group
		$isNeedImportFromGroup = OptionUtils::getOptionValueForOptionPage("im_{$entity}_group", "N", $isSaveHit) !== 'N';
		if(!$isNeedImportFromGroup) {
			$arOptionsHardSet["im_{$entity}_up_outer_sec"] = 'N';
			$arOptionsHardSet["im_{$entity}_wh_outer_sec"] = 'N';
		}

		//disabled active by filter if no choosen filter prop
		$isNeedFilterByProp = OptionUtils::getOptionValueForOptionPage("im_{$entity}_filter_prop", "N", $isSaveHit) !== 'N';
		if(!$isNeedFilterByProp) {
			$arOptionsHardSet["im_{$entity}_up_active_by_filter"] = 'N';
			$arOptionsHardSet["im_{$entity}_wh_active_by_filter"] = 'N';
		}

		//disable all active scenario if active field by attr
		$activeFieldByAttr = OptionUtils::getOptionValueForOptionPage("im_{$entity}_p_active_prop", "N", $isSaveHit) !== 'N';
		if($activeFieldByAttr) {
			$arOptionsHardSet["im_{$entity}_p_archived"] = 'N';
			$arOptionsHardSet["im_{$entity}_up_archived"] = 'N';
			$arOptionsHardSet["im_{$entity}_wh_archived"] = 'N';

			$arOptionsHardSet["im_{$entity}_up_outer_sec"] = 'N';
			$arOptionsHardSet["im_{$entity}_wh_outer_sec"] = 'N';

			$arOptionsHardSet["im_{$entity}_up_active_by_filter"] = 'N';
			$arOptionsHardSet["im_{$entity}_wh_active_by_filter"] = 'N';
		}
		
		
		//IMPORT_NEW
		if(true) {
			$arAllOptions['import' . $entity][] = GetMessage('IMPORT_FIELDS_TABLE', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/import-new']);
			$arAllOptions['import' . $entity][] = [
				'note' => GetMessage('IMPORT_FIELDS_TABLE_NOTE')
			];

			$arAllOptions['import' . $entity][] = "start_new_items_{$entity}";

			$arAllOptions['import' . $entity][] = GetMessage('IMPORT_FIELDS', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/import-new']);

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_BASE'), '', ['statichtml']];
			$arOptionsHardSet["im_{$entity}_p_name"] = 'Y';
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_name", GetMessage('HOOK_PARAMS_NAME'), 'Y', ['checkbox', "Y", 'disabled']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_code", GetMessage('HOOK_PARAMS_CODE'), 'N', ['checkbox', "N", 'disabled']];
			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_folder", GetMessage('HOOK_PARAMS_FOLDER'), '', ['checkbox', "N", 'disabled']];
			}
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_props", GetMessage('HOOK_PARAMS_PROPS'), '', ['checkbox', "N", $paramsCheckBox]];

			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_sort", GetMessage('IMPORT_PARAMS_SORT'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_ACTIVE'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_archived", GetMessage('HOOK_PARAMS_ARCHIVED'), '', ['checkbox', "N", 'disabled']];
			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_outer_sec", GetMessage('IMPORT_PARAMS_SECTION_OUTER'), '', ['checkbox', "N", 'disabled']];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_active_by_filter", GetMessage('HOOK_PARAMS_ACTIVE_BY_FILTER'), '', ['checkbox', "N", 'disabled']];
			}


			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_DESCR'), '', ['statichtml']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr", GetMessage('HOOK_PARAMS_DESCR'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_descr_full", GetMessage('HOOK_PARAMS_DESCR_FULL'), '', ['checkbox', "N", $paramsCheckBox]];

			if ($entity !== 'service') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_PIC'), '', ['statichtml']];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_img", GetMessage('HOOK_PARAMS_IMG'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_img_full", GetMessage('HOOK_PARAMS_IMG_FULL'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_img_prop", GetMessage('HOOK_PARAMS_IMG_PROP'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_PRODUCT'), '', ['statichtml']];

			if(BitrixFeatures::isBarCodeTableExist() && $entity !== 'service') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_barcode", GetMessage('HOOK_PARAMS_BARCODE'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			if(OptionUtils::canTrackingTypeImport() && in_array($entity, ['product', 'bundle'])) {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_tracking_type", GetMessage('IMPORT_HEAD_PARAMS_TRACKING_TYPE'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			if ($entity !== 'variant') {

				if ($entity !== 'service') {
					$arAllOptions['import' . $entity][] = ["im_{$entity}_p_sizes", GetMessage('HOOK_PARAMS_SIZES'), '', ['checkbox', "N", $paramsCheckBox]];
				}
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_uom", GetMessage('HOOK_PARAMS_UOM'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_ratio", GetMessage('IMPORT_PARAMS_RATIO'), '', ['checkbox', "N", $paramsCheckBox]];
			
			} else {

				if(OptionUtils::canTrackingTypeImport() && BitrixFeatures::isUseOfferMarkingCodeGroup()) {
					$arAllOptions['import' . $entity][] = ["im_{$entity}_p_parent_tracking", GetMessage('HOOK_PARENT_TRACKING_TYPE'), '', ['checkbox', "N", $paramsCheckBox]];
				}

				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_parent_weight", GetMessage('HOOK_PARENT_WEIGHT_UP'), '', ['checkbox', "N", $paramsCheckBox]];

				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_parent_sizes", GetMessage('HOOK_PARENT_SIZES_UP'), '', ['checkbox', "N", $paramsCheckBox]];

				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_parent_measure", GetMessage('HOOK_PARENT_MEASURE_UP'), '', ['checkbox', "N", $paramsCheckBox]];

				$arAllOptions['import' . $entity][] = ["im_{$entity}_p_parent_ratio", GetMessage('HOOK_PARENT_RATIO'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_VAT'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_vat", GetMessage('HOOK_PARAMS_VAT'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_vat_inc", GetMessage('HOOK_PARAMS_VAT_INC'), '', ['checkbox', "N", $paramsCheckBox]];

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_OTHER'), '', ['statichtml']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_update_facet", GetMessage('HOOK_PARAMS_FACET'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_p_seocache", GetMessage('HOOK_PARAMS_SEOCACHE'), '', ['checkbox', "N", 'disabled']];

			$arAllOptions['import' . $entity][] = "end_new_items_{$entity}";
		}

		//IMPORT_UPDATE
		if(true) {
			$arAllOptions['import' . $entity][] = "start_update_items_{$entity}";

			$arAllOptions['import' . $entity][] = GetMessage('IMPORT_UPDATE', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/import-update']);

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_BASE'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_name", GetMessage('HOOK_PARAMS_NAME'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_code", GetMessage('HOOK_PARAMS_CODE'), '', ['checkbox', "N", $paramsCheckBox]];
			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_folder", GetMessage('HOOK_PARAMS_FOLDER'), '', ['checkbox', "N", $sectionOff ? 'disabled' : $paramsCheckBox]];
			}
			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_props", GetMessage('HOOK_PARAMS_PROPS'), '', ['checkbox', "N", $paramsCheckBox]];

			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_sort", GetMessage('IMPORT_PARAMS_SORT'), '', ['checkbox', "N", $paramsCheckBox]];
			}


			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_ACTIVE'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_archived", GetMessage('HOOK_PARAMS_ARCHIVED'), '', ['checkbox', "N", $includeArchivedItems | $activeFieldByAttr ? 'disabled' : $paramsCheckBox]];
			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_outer_sec", GetMessage('IMPORT_PARAMS_SECTION_OUTER'), '', ['checkbox', "N", $isNeedImportFromGroup && !$activeFieldByAttr ? $paramsCheckBox : 'disabled']];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_active_by_filter", GetMessage('HOOK_PARAMS_ACTIVE_BY_FILTER'), '', ['checkbox', "N", $isNeedFilterByProp && !$activeFieldByAttr ? $paramsCheckBox : 'disabled']];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_DESCR'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_descr", GetMessage('HOOK_PARAMS_DESCR'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_descr_full", GetMessage('HOOK_PARAMS_DESCR_FULL'), '', ['checkbox', "N", $paramsCheckBox]];

			if ($entity !== 'service') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_PIC'), '', ['statichtml']];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_img", GetMessage('HOOK_PARAMS_IMG'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_img_full", GetMessage('HOOK_PARAMS_IMG_FULL'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_img_prop", GetMessage('HOOK_PARAMS_IMG_PROP'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_PRODUCT'), '', ['statichtml']];

			if(BitrixFeatures::isBarCodeTableExist() && $entity !== 'service') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_barcode", GetMessage('HOOK_PARAMS_BARCODE'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			if(OptionUtils::canTrackingTypeImport() && in_array($entity, ['product', 'bundle'])) {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_tracking_type", GetMessage('IMPORT_HEAD_PARAMS_TRACKING_TYPE'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			if ($entity !== 'variant' && $entity !== 'service') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_sizes", GetMessage('HOOK_PARAMS_SIZES'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_uom", GetMessage('HOOK_PARAMS_UOM'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_ratio", GetMessage('IMPORT_PARAMS_RATIO'), '', ['checkbox', "N", $paramsCheckBox]];
			} else {
				if(OptionUtils::canTrackingTypeImport() && BitrixFeatures::isUseOfferMarkingCodeGroup()) {
					$arAllOptions['import' . $entity][] = ["im_{$entity}_up_parent_tracking", GetMessage('HOOK_PARENT_TRACKING_TYPE'), '', ['checkbox', "N", $paramsCheckBox]];
				}
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_parent_weight", GetMessage('HOOK_PARENT_WEIGHT_UP'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_parent_sizes", GetMessage('HOOK_PARENT_SIZES_UP'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_parent_measure", GetMessage('HOOK_PARENT_MEASURE_UP'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_up_parent_ratio", GetMessage('HOOK_PARENT_RATIO'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_VAT'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_vat", GetMessage('HOOK_PARAMS_VAT'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_vat_inc", GetMessage('HOOK_PARAMS_VAT_INC'), '', ['checkbox', "N", $paramsCheckBox]];

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_OTHER'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_update_facet", GetMessage('HOOK_PARAMS_FACET'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_up_seocache", GetMessage('HOOK_PARAMS_SEOCACHE'), '', ['checkbox', "N", $paramsCheckBox]];

			//$arAllOptions['import' . $entity][] = ["im_{$entity}_up_date", GetMessage('HOOK_PARAMS_DATE'), '', ['checkbox', "N", $paramsCheckBox]];

			$arAllOptions['import' . $entity][] = "end_update_items_{$entity}";
		}

		//WEBHOOK
		if(true) {
			$arAllOptions['import' . $entity][] = "start_webhook_items_{$entity}";

			$arAllOptions['import' . $entity][] = GetMessage('IMPORT_HEAD_HOOK', ['#LINK#' => '/rbs-moyskladstocks/settings/' . $entity . '/import-hook']);

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_BASE'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_name", GetMessage('HOOK_PARAMS_NAME'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_code", GetMessage('HOOK_PARAMS_CODE'), '', ['checkbox', "N", $paramsCheckBox]];

			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_folder", GetMessage('HOOK_PARAMS_FOLDER'), '', ['checkbox', "N", $sectionOff ? 'disabled' : $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_props", GetMessage('HOOK_PARAMS_PROPS'), '', ['checkbox', "N", $paramsCheckBox]];

			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_sort", GetMessage('IMPORT_PARAMS_SORT'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_ACTIVE'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_archived", GetMessage('HOOK_PARAMS_ARCHIVED'), '', ['checkbox', "N", $includeArchivedItems | $activeFieldByAttr ? 'disabled' : $paramsCheckBox]];
			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_outer_sec", GetMessage('IMPORT_PARAMS_SECTION_OUTER'), '', ['checkbox', "N", $isNeedImportFromGroup && !$activeFieldByAttr ? $paramsCheckBox : 'disabled']];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_active_by_filter", GetMessage('HOOK_PARAMS_ACTIVE_BY_FILTER'), '', ['checkbox', "N", $isNeedFilterByProp && !$activeFieldByAttr ? $paramsCheckBox : 'disabled']];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_DESCR'), '', ['statichtml']];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_descr", GetMessage('HOOK_PARAMS_DESCR'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_descr_full", GetMessage('HOOK_PARAMS_DESCR_FULL'), '', ['checkbox', "N", $paramsCheckBox]];

			if ($entity !== 'service') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_PIC'), '', ['statichtml']];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_img", GetMessage('HOOK_PARAMS_IMG'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_img_full", GetMessage('HOOK_PARAMS_IMG_FULL'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_img_prop", GetMessage('HOOK_PARAMS_IMG_PROP'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_PRODUCT'), '', ['statichtml']];

			if(BitrixFeatures::isBarCodeTableExist() && $entity !== 'service') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_barcode", GetMessage('HOOK_PARAMS_BARCODE'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			if(OptionUtils::canTrackingTypeImport() && in_array($entity, ['product', 'bundle'])) {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_tracking_type", GetMessage('IMPORT_HEAD_PARAMS_TRACKING_TYPE'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			if ($entity !== 'variant' && $entity !== 'service') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_sizes", GetMessage('HOOK_PARAMS_SIZES'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			if ($entity !== 'variant') {
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_uom", GetMessage('HOOK_PARAMS_UOM'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_ratio", GetMessage('IMPORT_PARAMS_RATIO'), '', ['checkbox', "N", $paramsCheckBox]];
			} else {

				if(OptionUtils::canTrackingTypeImport() && BitrixFeatures::isUseOfferMarkingCodeGroup()) {
					$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_parent_tracking", GetMessage('HOOK_PARENT_TRACKING_TYPE'), '', ['checkbox', "N", $paramsCheckBox]];
				}

				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_parent_weight", GetMessage('HOOK_PARENT_WEIGHT_UP'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_parent_sizes", GetMessage('HOOK_PARENT_SIZES_UP'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_parent_measure", GetMessage('HOOK_PARENT_MEASURE_UP'), '', ['checkbox', "N", $paramsCheckBox]];
				$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_parent_ratio", GetMessage('HOOK_PARENT_RATIO'), '', ['checkbox', "N", $paramsCheckBox]];
			}

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_VAT'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_vat", GetMessage('HOOK_PARAMS_VAT'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_vat_inc", GetMessage('HOOK_PARAMS_VAT_INC'), '', ['checkbox', "N", $paramsCheckBox]];

			$arAllOptions['import' . $entity][] = ["im_{$entity}", GetMessage('IMPORT_HEAD_PARAMS_OTHER'), '', ['statichtml']];

			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_update_facet", GetMessage('HOOK_PARAMS_FACET'), '', ['checkbox', "N", $paramsCheckBox]];
			$arAllOptions['import' . $entity][] = ["im_{$entity}_wh_seocache", GetMessage('HOOK_PARAMS_SEOCACHE'), '', ['checkbox', "N", $paramsCheckBox]];

			$arAllOptions['import' . $entity][] = "end_webhook_items_{$entity}";
		}
		
		//AGENT
		OptionUtils::buildAgentOptionArray($arAllOptions['import' . $entity], $entity, $paramsCheckBox, true, []);
		if ($isSaveHit) {
			OptionUtils::saveAgentAction($entity, "import_{$entity}");
		}
		//---------

		OptionUtils::buildImportOnceButton($arAllOptions['import' . $entity], "import_once_{$entity}");

		//---------
		if (!empty($arAllOptions['import' . $entity])) {
			$aTabs[] = [
				"DIV" => "import_" . $entity,
				"TAB" => GetMessage('IMPORT_HEAD_' . $entity),
				"ICON" => "order_settings",
				"TITLE" => GetMessage('IMPORT_HEAD_' . $entity)
			];
		}
	}
}