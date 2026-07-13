<?php

namespace Rbs\MoyskladStocks\Process;

use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Services\EntityMetaBuilder;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

class DeactivateEntity extends Base
{

	private static $deactivatteTypeList = [
		'archive', 'filter', 'folder'
	];
	private $deactivateType;
	private $entity = 'product';
	private $additionalParams = [];

	public function __construct(string $entity, string $deactivateType)
	{
		$this->entity = $entity;
		$this->deactivateType = in_array($deactivateType, self::$deactivatteTypeList) ? $deactivateType : 'archive';
		parent::__construct();
	}

	public function setFullOnce()
	{
		$this->agentManager->setFullOnce();
	}

	protected function initializeAgentManager()
	{
		$this->agentManager->setConfigValue('limit', 500);
		$this->agentManager->setOnlyUpdated();
	}

	protected function getAgentManagerId(): string
	{
		$agentId = 'd_' . $this->entity;
		switch ($this->deactivateType) {
			case 'filter':
				$agentId = 'df_' . $this->entity;
				break;
			case 'folder':
				$agentId = 'dfol_' . $this->entity;
				break;
		}
		return $agentId;
	}

	protected function validateProcess()
	{
		if (empty($this->entity)) {
			throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_ENTITY'));
		}

		$catalogIblockId = Config::getIblockId($this->entity);
		if ($catalogIblockId <= 0) {
			throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'));
		} else {
			$this->additionalParams['catalogIblockId'] = (int)$catalogIblockId;
		}

		if ($this->deactivateType === 'filter') {
			if(!Config::isEnableFilterProp($this->entity)) {
				throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_FILTER_PROP'));
			} else {
				$this->additionalParams['filterPropString'] = Config::getFilterPropString(Config::getFilterPropId($this->entity), Config::getFilterPropValueReverse($this->entity), $this->entity);
			}			
		}

		if ($this->deactivateType === 'folder') {
			if(!Helper::isNeedGroupItem($this->entity)) {
				throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_FILTER_GROUP_STRING_NOT_NEEDED'));
			}
			$metaBuilder = new EntityMetaBuilder('productfolder', Config::getGroupId($this->entity));
			$this->additionalParams['currentProductFolderHref'] = $metaBuilder->getMetaHref();
			if(empty($this->additionalParams['currentProductFolderHref'])) {
				throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_GET_FILTER_STRING_GROUP'));
			}
		}
	}	

	protected function buildParams(): array
	{
		$params = [
			'limit' => $this->agentManager->getLimit(),
			'offset' => $this->agentManager->getOffset(),
		];

		$customFilter = [];
		$skipStandartFilterString = false;
		switch($this->deactivateType) {
			case 'archive':
				$customFilter = ['archived=true'];
				break;
			case 'filter':
				$customFilter = [$this->additionalParams['filterPropString'], 'archived=true', 'archived=false'];
				$skipStandartFilterString = true;
				break;
			case 'folder':
				$customFilter = ["type={$this->entity}", "productFolder!=" . $this->additionalParams['currentProductFolderHref']];
				$skipStandartFilterString = true;
				break;
		}

		if($this->entity === 'variant') {
			$customFilter[] = "type={$this->entity}";
		}

		$params['filter'] = \CRbsMoyskladStocks::buildAgentFilterStringForEntity($this->entity, $this->agentManager, $customFilter, $skipStandartFilterString);		

		return $params;
	}

	protected function fetchData()
	{
		ApiNew::refreshCountRequests();
		if ($this->deactivateType === 'folder' || $this->entity === 'variant') {
			return ApiNew::get("/entity/assortment", $this->params);
		}
		return ApiNew::get("/entity/{$this->entity}", $this->params);
	}

	protected function processRows(array $rows)
	{
		if(!\Bitrix\Main\Loader::includeModule('iblock')) {
			throw new \Exception(LangMsg::get('THROW_MODULE_NOT_INCLUDED', [
				'#MODULE_ID#' => 'iblock'
			]));
		}

		$arItems = [];
		foreach ($rows as $row) {
			$row->{'meta'}->{'href'} = explode('?', $row->{'meta'}->{'href'})[0];
			$arItems[ProductIdentifier::getIdentifierValue($row)] = $row;
		}

		$filter = ProductIdentifier::buildBatchFilter(array_keys($arItems), $this->entity);
		$filter['IBLOCK_ID'] = $this->additionalParams['catalogIblockId'];
		$filter['ACTIVE'] = 'Y';
		$select = ['ID', 'IBLOCK_ID', 'ACTIVE'];

		$rsIblock = \CIblockElement::GetList(['ID' => 'DESC'], $filter, false, false, $select);
		$element = new \CIBlockElement;
		while ($ob = $rsIblock->GetNext()) {
			if (!$element->Update($ob['ID'], ['ACTIVE' => 'N'])) {
				$this->logger->addMessage(LangMsg::get('WARNING_UPDATE_BX_ELEMENT', [
					'#ID#' => (string)$ob['ID'],
					'#ERROR#' => $element->{'LAST_ERROR'}
				]), Debug\Message::TYPE_ERROR);
			}
		}

		$this->logger->addMessage(LangMsg::get("AGENT_IMPORT_DEACTIVATE_{$this->deactivateType}", [
			'#COUNT#' => (string)$rsIblock->SelectedRowsCount()
		]), Debug\Message::TYPE_SUCCESS);
	}

	protected function processLastStepActions($response){}
	protected function prepareResponse($response) {}

}