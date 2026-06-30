<?php

namespace Rbs\Moysklad\Entity;

use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\ApiNew;
use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\Customerorder;
use \Rbs\Moysklad\Config;
use \Rbs\Moysklad\Webhook;

use \Bitrix\Sale\Shipment;

class Demand
{
	private $customerorder;
	private $shipmentKeys = [];
	private $demandPushData = [];
	private $isChangedDeliveryPrice = false;

	public function __construct(Customerorder &$customerorder)
	{
		$this->customerorder =& $customerorder;
	}

	public function isChangedDeliveryPrice()
	{
		return $this->isChangedDeliveryPrice;
	}

	public function importAllDemandsToShipments()
	{
		$orderMs = $this->getOrderMs();
		if(Utils::array_exists($orderMs, 'demands')) {
			$isChanged = false;
			foreach($orderMs->demands as $demand) {
				if(!($shipment = $this->getShipmentByDemand($demand))) {
					$shipment = $this->createShipmentByDemand($demand);
				}
				if ($shipment instanceof Shipment) {
					if ($this->isDeletedDemand($demand)) {
						$shipment->setField('DEDUCTED', 'N');
						$shipment->delete();
					} else {
						$this->updateShipmenFieldsByDemand($shipment, $demand);
					}
					$isChanged = true;
				}
			}
			if($isChanged) {
				$result = $this->getOrderBx()->save();
				if (!$result->isSuccess()) {
					$this->customerorder->addErrorMessageArray($result->getErrorMessages());
				} else if (count($result->getWarningMessages()) > 0) {
					$this->customerorder->addWarningMessageArray($result->getWarningMessages());
				} else {
					$this->customerorder->addSuccessMessage(LangMsg::get('SUCCESS_UPDATE_ALL_SHIPMENT', ['#ORDER_ID#' => $this->getOrderBx()->getId()]));
				}
			}
		}
	}

	public function updateShipment($demand = null)
	{
		if ($this->isValidDemand($demand)) {
			if (!($shipment = $this->getShipmentByDemand($demand)) && !$this->isDeletedDemand($demand)) {
				$shipment = $this->createShipmentByDemand($demand);
			}
			if($shipment instanceof Shipment) {

				if($this->isDeletedDemand($demand)) {
					$shipment->setField('DEDUCTED', 'N');
					$shipment->delete();
				} else {
					$this->updateShipmenFieldsByDemand($shipment, $demand);
				}

				$result = $this->getOrderBx()->save();
				if (!$result->isSuccess()) {
					$this->customerorder->addErrorMessageArray($result->getErrorMessages());
				} else if (count($result->getWarningMessages()) > 0) {
					$this->customerorder->addWarningMessageArray($result->getWarningMessages());
				} else {
					$this->customerorder->addSuccessMessage(LangMsg::get('SUCCESS_UPDATE_SHIPMENT', ['#ID#' => $shipment->getId(), '#ORDER_ID#' => $this->getOrderBx()->getId()]));
				}
			}
		}
	}

		private function isDeletedDemand($demand): bool
		{
			$deleteAttr = Config::getOption('demand_delete_attr', 'N');

			if(!empty($deleteAttr) && $deleteAttr !== 'N' && Utils::array_exists($demand, 'attributes')) {
				foreach($demand->attributes as $attr) {
					if($attr->id === $deleteAttr) {
						return (bool)$attr->value;
					}
				}
			}

			return false;
		}

		private function updateShipmenFieldsByDemand(Shipment &$shipment, Object $demand)
		{
			$isApplicable = Utils::property_exists($demand, ['applicable']) ? $demand->applicable : false;

			$shipment->setField('DEDUCTED', 'N'); //cancel deducted before change basket items
			
			$this->buildShipmentBasketItems($shipment, $demand); //create shipment item collection before deducted!!!

			$demandAttrs = [];
			if(Utils::array_exists($demand, 'attributes')) {
				foreach($demand->attributes as $attr) {
					$demandAttrs[$attr->id] = $attr;
				}
			}

			if(Utils::is_count($demandAttrs)) {

				$allowAttr = Config::getOption('demand_allow_attr', 'N');
				if (!empty($allowAttr) && $allowAttr !== 'N' && isset($demandAttrs[$allowAttr])) {
					$currentAllowDelivery = (bool)$demandAttrs[$allowAttr]->value ? 'Y' : 'N';
					if ((string)$shipment->getField('ALLOW_DELIVERY') !== $currentAllowDelivery) {
						$shipment->setField('ALLOW_DELIVERY', $currentAllowDelivery);
					}
				}

				$trackAttr = Config::getOption('demand_track_attr', 'N');
				if (!empty($trackAttr) && $trackAttr !== 'N' && isset($demandAttrs[$trackAttr])) {
					if((string)$shipment->getField('TRACKING_NUMBER') !== (string)$demandAttrs[$trackAttr]->value) {
						$shipment->setField('TRACKING_NUMBER', (string)$demandAttrs[$trackAttr]->value);
					}
				}

				if (Config::getOption('demand_price_type', 'N') === 'ATTR') {
					$demandPriceAttr = Config::getOption('demand_price_attr', 'N');
					if (!empty($demandPriceAttr) && $demandPriceAttr !== 'N' && isset($demandAttrs[$demandPriceAttr])) {
						if((float)$shipment->getField('PRICE_DELIVERY') !== (float)$demandAttrs[$demandPriceAttr]->value) {
							$shipment->setField('PRICE_DELIVERY', (float)$demandAttrs[$demandPriceAttr]->value);
							$shipment->setField('CUSTOM_PRICE_DELIVERY', 'Y');
							$this->isChangedDeliveryPrice = true;
						}
					}
				}

				$deliveryTypeAttr = Config::getOption('demand_delivery_type_attr');
				$deliveryTypeAttrEntityId = Config::getOption('demand_delivery_type_attr_entity_id', '');
				if (!empty($deliveryTypeAttr) && $deliveryTypeAttr !== 'N' && !empty($deliveryTypeAttrEntityId) && isset($demandAttrs[$deliveryTypeAttr]) && Utils::property_exists($demandAttrs[$deliveryTypeAttr], ['value', 'meta', 'href'])) {
					$demandTypeAttrValue = $demandAttrs[$deliveryTypeAttr]->value->meta->href;
					$valueEntity = array_pop(explode('/', $demandTypeAttrValue));
					if(!empty($valueEntity)) {
						$deliveryList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
						foreach ($deliveryList as $delivery) {
							if(Config::getOption("demand_delivery_type_var_" . $delivery['ID'], 'N') === $valueEntity) {
								$shipment->setField('DELIVERY_ID', $delivery['ID']);
								$shipment->setField('DELIVERY_NAME', $delivery['NAME']);
								break;
							}
						}
					}
				}

			}

			if (Config::getOption('demand_price_type', 'N') === 'SERVICE') {
				$currentXmlId = Config::getDeliveryExternalCode();
				if (Utils::property_exists($demand, ['positions']) && Utils::array_exists($demand->positions)) {
					foreach ($demand->positions->rows as $position) {
						if (
							$position->assortment->meta->type === 'service' && $position->assortment->externalCode ===
							$currentXmlId
						) {
							$finalDiscountPrice = \CRbsMoyskladHelper::getPositionFinalPrice($position);
							if((float)$shipment->getField('PRICE_DELIVERY') !== (float)($finalDiscountPrice / 100)) {
								$shipment->setField('PRICE_DELIVERY', (float)$finalDiscountPrice / 100);
								$shipment->setField('CUSTOM_PRICE_DELIVERY', 'Y');
								$this->isChangedDeliveryPrice = true;
							}
						}
					}
				}
			}

			if(Utils::property_exists($demand, ['state', 'id'])) {
				$statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList(array(
					'order' => array('STATUS.SORT' => 'ASC'),
					'filter' => array('STATUS.TYPE' => 'D', 'LID' => LANGUAGE_ID),
					'select' => array('STATUS_ID', 'NAME', 'DESCRIPTION'),
				));
				while ($obStatus = $statusResult->fetch()) {
					$statusId = $obStatus['STATUS_ID'];
					$currentStateId = Config::getOption("demand_state_{$statusId}", 'N');
					if($currentStateId === $demand->state->id) {
						$shipment->setField('STATUS_ID', $statusId);
						break;
					}
				}
			}

			if (Config::checkFeature('demand_store_ext_code') && Utils::property_exists($demand, ['store', 'externalCode'])) {
				$store = \Bitrix\Catalog\StoreTable::getList([
					'filter' => ['XML_ID' => (string)$demand->store->externalCode],
					'cache' => ['ttl' => 86400]
				])->fetch();
				if (!empty($store['ID']) && (int)$shipment->getId() > 0) {
					\Bitrix\Sale\Delivery\ExtraServices\Manager::saveStoreIdForShipment($shipment->getId(), $shipment->getDeliveryId(), $store['ID']);
				}
			}	
			
			$shipment->setField('COMMENTS', $demand->description ?? '');
			$shipment->setField('DEDUCTED', $isApplicable ? 'Y' : 'N'); //last field
		}

			private function buildShipmentBasketItems(Shipment &$shipment, Object $demand)
			{
				if ($shipmentItemCollection = $shipment->getShipmentItemCollection()) {
					foreach ($shipmentItemCollection as $shipmentItem) {
						$shipmentItem->delete();
					}
					$orderBx = $this->getOrderBx();
					if(Utils::property_exists($demand, ['positions']) && Utils::array_exists($demand->positions) && ($basketItems = $orderBx->getBasket())) {
						foreach($demand->positions->rows as $position) {
							if($basketItem = $this->findBasketItemFromPosition($basketItems, $position)) {
								if($shipmentItem = $shipmentItemCollection->createItem($basketItem)) {
									$qty = (float)$basketItem->getField('QUANTITY') >= (float)$position->quantity ? (float)$position->quantity : (float)$basketItem->getField('QUANTITY');
									$shipmentItem->setField('QUANTITY', $qty);
								}
							}
						}
					}
				}
			}

				private function findBasketItemFromPosition(\Bitrix\Sale\BasketItemCollection $basketItems, object $position)
				{

					$event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeFindBasketItemFromPosition", array(
						'order' => $this->getOrderBx(),
						'orderMs' => $this->getOrderMs(),
						'basketItems' => $basketItems,
						'position' => $position
					));

					$event->send();

					if ($event->getResults()) {
						foreach ($event->getResults() as $eventResult) {
							if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
								$result = $eventResult->getParameters();
								if ($result['basket_item'] instanceof \Bitrix\Sale\BasketItem) {
									return $result['basket_item'];
								}
							}
						}
					}

					$assortment = $position->assortment;
					if (Utils::property_exists($assortment, ['externalCode']) && !empty($assortment->externalCode)) {

						foreach ($basketItems as $basketItem) {
							$xmlId = (string)$basketItem->getField('PRODUCT_XML_ID');
							if (mb_strpos($xmlId, '#') !== false && Config::checkFeature('basketmodifsync')) {
								$xmlId = array_pop(explode('#', $xmlId));
							}
							if (!empty($xmlId) && $xmlId === (string)$assortment->externalCode) {
								return $basketItem;
							}
						}

						$filterProduct = ['=XML_ID' => (string)$assortment->externalCode];
						if ($assortment->type === 'variant' && Config::checkFeature('basketmodifsync')) {
							$filterProduct = ['XML_ID' => '%#' . (string)$assortment->externalCode];
						}

						$product = \Bitrix\Catalog\ProductTable::getList([
							'filter' => $filterProduct,
							'select' => [
								'*',
								'XML_ID' => 'IBLOCK_ELEMENT.XML_ID',
								'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID',
								'NAME' => 'IBLOCK_ELEMENT.NAME'
							]
						])->fetch();

						if (!is_array($product) || empty($product['ID'])) {
							foreach ($basketItems as $basketItem) {
								if ($basketItem->getField('PRODUCT_ID') == $product['ID']) {
									return $basketItem;
								}
							}
						}
					}

					return null;
				}

	public function exportAllShipmentsToDemands()
	{
		if ($shipmentCollection = $this->getOrderBx()->getShipmentCollection()) {
			foreach ($shipmentCollection as $shipment) {
				if (!$shipment->isSystem()) {
					$pushData = $this->getPushDataForDemand($shipment);
					if(is_array($pushData) && count($pushData) > 0) {
						$this->demandPushData[] = $pushData;
						$this->shipmentKeys[count($this->demandPushData) - 1] = $shipment;
					}
				}
			}
			$this->pushDataToMs();
		}
	}

	public function exportShipmentToDemand(Shipment $shipment)
	{
		if (!$shipment->isSystem()) {
			$pushData = $this->getPushDataForDemand($shipment);
			if (is_array($pushData) && count($pushData) > 0) {
				$this->demandPushData[] = $pushData;
				$this->shipmentKeys[count($this->demandPushData) - 1] = $shipment;
				$this->pushDataToMs();
			}
		}
	}

	public function deleteDemandFromShipmentExternalCode(string $shipmentXmlId = '')
	{
		if ($msDemand = $this->findDemandByShipmentXmlId($shipmentXmlId)) {
			if(Config::getOption('demand_delete_bx_type', 'N') === 'DELETE') {
				\Rbs\Moysklad\ApiNew::delete($msDemand->meta->href);
			}
			if (Config::getOption('demand_delete_bx_type', 'N') === 'ATTR') {
				$deleteAttr = Config::getOption('demand_delete_attr', 'N');
				if(!empty($deleteAttr) && $deleteAttr !== 'N') {
					\Rbs\Moysklad\ApiNew::put($msDemand->meta->href, [
						'applicable' => false,
						'attributes' => [
							[
								'meta' => Config::getAttributeMetaLink($deleteAttr, 'demand'),
								'value' => true
							]
						]
					]);
				}
			}
		}
	}

		private function getPushDataForDemand(Shipment $shipment): array
		{
			$result = [];

			if (!$shipment->isSystem()) {
				if ($msDemand = $this->findDemandByShipment($shipment)) {
					$result = $this->buildDemandForUpdate($shipment, $msDemand);
				} else {
					$result = $this->buildDemandForCreate($shipment);
				}

				$result = is_null($result) ? [] : $result;

				Utils::send_bx_event(Config::getModuleId(true), 'OnAfterBuildDemandPushData', [
					'order' => $this->getOrderBx(),
					'orderMs' => $this->getOrderMs(),
					'shipment' => $shipment,
					'result' => $result
				], $result);

			}
			return $result;
		}

			private function findDemandByShipmentXmlId(string $shipmentXmlId = '')
			{
				$orderMs = $this->getOrderMs();
				if (Utils::array_exists($orderMs, 'demands') && !empty($shipmentXmlId)) {
					foreach ($orderMs->demands as $demand) {
						if ((string)$demand->externalCode === (string)$shipmentXmlId) {
							return $demand;
						}
					}
				}
				return null;
			}

			private function findDemandByShipment(Shipment $shipment)
			{
				$orderMs = $this->getOrderMs();
				if (Utils::array_exists($orderMs, 'demands') && !empty($shipment->getField('XML_ID'))) {
					foreach ($orderMs->demands as $demand) {
						if ((string)$demand->externalCode === (string)$shipment->getField('XML_ID')) {
							return $demand;
						}
					}
				}
				return null;
			}

			private function buildDemandForUpdate(Shipment $shipment, object $demand): array
			{
				$deamndFields = $this->buildDemandRawFields($shipment);

				$deamndFields['meta'] = $demand->meta;

				return $deamndFields;
			}

			private function buildDemandForCreate(Shipment $shipment): array
			{
				$orderMs = $this->getOrderMs();

				$vatdd = Config::getOption('demand_sync_vat', 'from_order');
				$vatddInc = Config::getOption('demand_sync_vat_inc', 'from_order');

				$deamndFields = [
					'customerOrder' => (object)[
						'meta' => $orderMs->meta
					],
					'agent' => (object)[
						'meta' => $orderMs->agent->meta
					],
					'organization' => (object)[
						'meta' => $orderMs->organization->meta
					],

					'vatEnabled' => ($vatdd === 'from_order') ? (bool)$orderMs->vatEnabled : ($vatdd === 'hard_set' ? true : false),
					'vatIncluded' => ($vatddInc === 'from_order') ? (bool)$orderMs->vatIncluded : ($vatddInc === 'hard_set' ? true : false),

					'externalCode' => (string)$shipment->getField('XML_ID'),
				];

				if(!empty($orderMs->owner)) {
					$deamndFields['owner'] = (object)['meta' => $orderMs->owner->meta];
				}

				if (!empty($orderMs->group)) {
					$deamndFields['group'] = (object)['meta' => $orderMs->group->meta];
				}

				$syncIdField = Config::getOption('demand_sync_id', 'N');
				if (!empty($syncIdField) && $syncIdField !== 'N') {
					$deamndFields['name'] = $shipment->getField($syncIdField);
				}

				foreach (['store', 'project', 'salesChannel'] as $field) {
					if (Utils::property_exists($orderMs, [$field, 'meta'])) {
						$deamndFields[$field]['meta'] = $orderMs->{$field}->meta;
					}
				}

				if(empty($deamndFields['store'])) {
					$storeDefault = Config::getOption('demand_sync_store_default', '');
					if(empty($storeDefault)) {
						$storeMs = \Rbs\Moysklad\ApiNew::get('/entity/store', ['limit' => 1], 86400);
						if(Utils::is_success($storeMs) && Utils::array_exists($storeMs)) {
							$storeDefault = $storeMs->rows[0]->id;
						}
					}
					if (!empty($storeDefault)) {
						$deamndFields['store'] = Config::getMetaData('store', $storeDefault);
					}
				}

				if (Utils::property_exists($orderMs, ['shipmentAddressFull'])) {
					$deamndFields['shipmentAddressFull'] = $orderMs->shipmentAddressFull;
				}

				$deamndFields = array_merge($deamndFields, $this->buildDemandRawFields($shipment));

				$syncIdField = Config::getOption('demand_sync_id_comment', 'N');
				if (!empty($syncIdField) && $syncIdField !== 'N') {
						$deamndFields['description'] .= (empty($deamndFields['description']) ? '' : "\n") . LangMsg::get(
						'COMMENT_WITH_DOC_ID',
						[
							'#TYPE#' => LangMsg::get('COMMENT_WITH_DOC_ID_TYPE_DEMAND'),
							'#ID#' => $shipment->getField($syncIdField)
						]
					);
				}

				return $deamndFields;
			}

	private function buildDemandRawFields(Shipment $shipment)
	{
		$result = [
			'applicable' => $shipment->getField('DEDUCTED') === 'Y'
		];

		if(Config::checkFeature('demand_date_deducted_bx')) {
			$dateDeducted = $shipment->getField('DATE_DEDUCTED');
			if($dateDeducted instanceof \Bitrix\Main\Type\DateTime) {
				$result['moment'] = Config::getDateTime($dateDeducted->format('Y-m-d H:i:s'))->format('Y-m-d H:i:s');
			}
		}

		$result['description'] = (string)$shipment->getField('COMMENTS');

		$positions = $this->buildDemandPositions($shipment);
		if (count($positions) > 0) {
			$result['positions'] = $positions;
		}

		$state = Config::getOption('demand_state_' . $shipment->getField('STATUS_ID'), 'N');
		if(!empty($state) && $state !== 'N') {
			$result['state'] = Config::getMetaDataStateNew(
				ApiNew::getApiEndPointUrl() . '/entity/demand/metadata/states/' . $state,
				'demand'
			);
		}

		$allowAttr = Config::getOption('demand_allow_attr', 'N');
		if (!empty($allowAttr) && $allowAttr !== 'N') {
			$result['attributes'][$allowAttr] = [
				'meta' => Config::getAttributeMetaLink($allowAttr, 'demand'),
				'value' => $shipment->getField('ALLOW_DELIVERY') === 'Y'
			];
		}

		$trackAttr = Config::getOption('demand_track_attr', 'N');
		if (!empty($trackAttr) && $trackAttr !== 'N') {
			$result['attributes'][$trackAttr] = [
				'meta' => Config::getAttributeMetaLink($trackAttr, 'demand'),
				'value' => (string)$shipment->getField('TRACKING_NUMBER')
			];
		}

		if(Config::getOption('demand_price_type', 'N') === 'ATTR') {
			$demandPriceAttr = Config::getOption('demand_price_attr', 'N');
			if(!empty($demandPriceAttr) && $demandPriceAttr !== 'N') {
				$result['attributes'][$demandPriceAttr] = [
					'meta' => Config::getAttributeMetaLink($demandPriceAttr, 'demand'),
					'value' => (float)$shipment->getField('PRICE_DELIVERY')
				];
			}
		}

		if(Config::checkFeature('demand_store_ext_code') && (int)$shipment->getStoreId() > 0) {
			$store = \Bitrix\Catalog\StoreTable::getList([
				'filter' => ['ID' => (int)$shipment->getStoreId()],
				'cache' => ['ttl' => 86400]
			])->fetch();
			if (!empty($store['XML_ID'])) {
				$storeMs = ApiNew::get('/entity/store', ['filter' => 'externalCode=' . $store['XML_ID']], 86400);
				if (Utils::is_success($storeMs) && Utils::array_exists($storeMs)) {
					$storeMsObj = array_pop($storeMs->rows);
					$result['store'] = (object)['meta' => ['href' => $storeMsObj->meta->href, 'type' => 'store']];
				}
			}
		}	

		$deliveryTypeAttr = Config::getOption('demand_delivery_type_attr');
		$deliveryTypeAttrEntityId = Config::getOption('demand_delivery_type_attr_entity_id', '');
		if (!empty($deliveryTypeAttr) && $deliveryTypeAttr !== 'N' && !empty($deliveryTypeAttrEntityId)) {
			$currentDeliveryTypeAttrValue = Config::getOption("demand_delivery_type_var_" . $shipment->getField('DELIVERY_ID'));
			if(!empty($currentDeliveryTypeAttrValue) && $currentDeliveryTypeAttrValue !== 'N') {
				$result['attributes'][$deliveryTypeAttr] = [
					'meta' => Config::getAttributeMetaLink($deliveryTypeAttr, 'demand'),
					'value' => (object)[
						'meta' => (object)[
							'href' => ApiNew::getApiEndPointUrl() . "/entity/customentity/{$deliveryTypeAttrEntityId}/{$currentDeliveryTypeAttrValue}",
							'metadataHref' => ApiNew::getApiEndPointUrl() . "/context/companysettings/metadata/customEntities/{$deliveryTypeAttrEntityId}",
							'type' => 'customentity',
							'mediaType' => 'application/json'
						]
					]
				];
			}
		}

		if(!empty($result['attributes']) && Utils::is_count($result['attributes'])) {
			$result['attributes'] = array_values($result['attributes']);
		}

		return $result;
	}

		private function buildDemandPositions(Shipment $shipment): array
		{
			$positions = [];

			$vatRate = Config::checkFeature('basketvatrate');
			$vatDelivery = (int)Config::getOption('dprice_vat');
			
			if ($shipmentItemCollection = $shipment->getShipmentItemCollection()) {

				$orderMs = $this->getOrderMs();

				$orderPositionsByExternalCode = [];
				if (Utils::property_exists($orderMs, ['positions', 'rows']) && Utils::array_exists($orderMs->positions)) {
					foreach ($orderMs->positions->rows as $orderPosition) {
						$orderPositionsByExternalCode[$orderPosition->externalCode] = $orderPosition;
					}
				}

				foreach ($shipmentItemCollection as $shipmentItem) {
					$basketItem = $shipmentItem->getBasketItem();
					$currentXmlId = \CRbsMoyskladHelper::getXmlIdFromBasketItem($basketItem);
					if (!empty($currentXmlId)) {
						$currentAsortmentItem = false;
						if (isset($orderPositionsByExternalCode[$currentXmlId])) {
							$currentAsortmentItem = $orderPositionsByExternalCode[$currentXmlId]->assortment;
						} else {
							$currentAsortmentItem = \CRbsMoyskladHelper::getProductMsByExternalCode($currentXmlId);
						}
						if ($currentAsortmentItem) {

							$discount = 0;
							if (
								$basketItem->getField('PRICE') <> $basketItem->getField('BASE_PRICE') &&
								$basketItem->getField('BASE_PRICE') > intval(0)
							) {
								$discount = (float)(1 - ($basketItem->getField('PRICE') / $basketItem->getField('BASE_PRICE'))) * 100;
							}

							$positions[] = [
								'quantity' => (float)$shipmentItem->getField('QUANTITY'),
								'price' => (float)$basketItem->getField('BASE_PRICE') * 100,
								'discount' => $discount,
								'assortment' => (object)[
									'meta' => $currentAsortmentItem->meta
								],
								'vat' => $vatRate ? round($basketItem->getField('VAT_RATE') * 100, 0) : 0
							];
						}
					}
				}

				if (Config::getOption('demand_price_type', 'N') === 'SERVICE') {
					$currentXmlId = Config::getDeliveryExternalCode();
					$currentAsortmentItem = false;
					if (isset($orderPositionsByExternalCode[$currentXmlId])) {
						$currentAsortmentItem = $orderPositionsByExternalCode[$currentXmlId]->assortment;
					} else {
						$currentAsortmentItem = \CRbsMoyskladHelper::getProductMsByExternalCode($currentXmlId);
					}
					if($currentAsortmentItem) {
						$positions[] = [
							'quantity' => (float)1,
							'price' => (float)$shipment->getField('PRICE_DELIVERY') * 100,
							'assortment' => (object)[
								'meta' => $currentAsortmentItem->meta
							],
							'vat' => $vatRate ? $vatDelivery : 0
						];
					}
				}

			}

			return $positions;
		}

	private function pushDataToMs()
	{
		if (count($this->demandPushData) > 0) {

			foreach($this->demandPushData as $demandPushDataRow) {
				if(!empty($demandPushDataRow['meta'])) {
					Webhook::cacheUpdateEntity($demandPushDataRow['meta']->href, $demandPushDataRow['meta']->type, 'UPDATE');
				}
			}

			$msResult = ApiNew::post('/entity/demand', $this->demandPushData);

			if (Utils::is_count($msResult)) {
				foreach ($msResult as $demandKey => $resultItem) {
					$currentShipment = isset($this->shipmentKeys[$demandKey]) ? $this->shipmentKeys[$demandKey] : null;
					if($currentShipment !== null) {
						if (Utils::has_errors($resultItem)) {
							foreach ($resultItem->errors as $error) {
								$logMessageForShipment = LangMsg::get('EXPORT_DEMAND_FOR_SHIPMENT_MESSAGE', [
									'#SHIPMENT_ID#' => $currentShipment->getId(),
									'#MESSAGE#' => $error->error
								]);
								$this->customerorder->addErrorMessage($logMessageForShipment, 0);
							}
						} else {
							$logMessageForShipment = LangMsg::get('EXPORT_DEMAND_FOR_SHIPMENT_MESSAGE', [
								'#SHIPMENT_ID#' => $currentShipment->getId(),
								'#MESSAGE#' => LangMsg::get('DEMAND_SUCCESS_LOGGING')
							]);
							$this->customerorder->addSuccessMessage($logMessageForShipment);
						}
					} else {
						$this->customerorder->addErrorMessage(LangMsg::get('DEMAND_EXPORT_ERROR_ALL'), 0);
					}
				}
			} else {
				if (Utils::has_errors($msResult)) {
					$this->customerorder->addErrorMessageArray($msResult->errors);
				} else {
					$this->customerorder->addErrorMessage(LangMsg::get('DEMAND_EXPORT_ERROR_ALL'), 0);
				}
			}
		}
	}

	private function createShipmentByDemand($demand)
	{
		if($this->isValidDemand($demand)) {
			if ($shipmentCollection = $this->getOrderBx()->getShipmentCollection()) {
				$shipment = $shipmentCollection->createItem();
				if($shipment instanceof Shipment) {
					$shipment->setField('XML_ID', $demand->externalCode);
				}
				return $shipment;
			}
		}
		return null;
	}

	private function getShipmentByDemand($demand, bool $strictSearch = false)
	{
		if($this->isValidDemand($demand)) {
			
			$countOfShipments = 0;
			if ($shipmentCollection = $this->getOrderBx()->getShipmentCollection()) {
				foreach ($shipmentCollection as $shipment) {
					$countOfShipments++;
					if (!$shipment->isSystem()) {
						if ($shipment->getField('XML_ID') == $demand->externalCode) {
							return $shipment;
						}
					}
				}
			}

			/* if(!$strictSearch) {
				$orderMs = $this->getOrderMs();
				$isFirstMsDemand = Utils::array_exists($orderMs, 'demands') && count($orderMs->demands) == 1;
				if ($countOfShipments == 1 && $isFirstMsDemand && Config::getOption('bind_first_demand_from_ms_to_bx')) {
					$shipment->setField('XML_ID', $demand->externalCode);
					return $shipment;
				}
			} */
			
		}
		return null;
	}

	private function isValidDemand($demand)
	{
		return Utils::property_exists($demand, ['externalCode']) && !empty($demand->externalCode);
	}

	private function getOrderMs()
	{
		return $this->customerorder->getOrder();
	}

	private function getOrderBx()
	{
		return $this->customerorder->getOrderEntity();
	}
	
}
