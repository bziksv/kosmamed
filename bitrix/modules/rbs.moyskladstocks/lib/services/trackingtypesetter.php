<?php
namespace Rbs\MoyskladStocks\Services;

use Rbs\MoyskladStocks\Internals\Enums\TrackingType;
use Rbs\MoyskladStocks\Internals\HlBlockValues;

class TrackingTypeSetter
{
    private static $ufEntity = 'PRODUCT';
    private static $instance = null;
    private $trackingHlBlockId = 0;
    
    private function __construct()
    {
        $this->initTrackingHlBlock();
    }
    private function __clone() {}
    private function __wakeup() {}
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initTrackingHlBlock()
    {
        $entityId = \Bitrix\Catalog\ProductTable::getUfId();
        $fieldName = 'UF_PRODUCT_GROUP';

        $r = \CUserTypeEntity::GetList([], ['ENTITY_ID' => $entityId, 'FIELD_NAME' => $fieldName]);
        
        if($userField = $r->GetNext()) {
            if((int)$userField['SETTINGS']['HLBLOCK_ID'] > 0) {
                $this->trackingHlBlockId = (int)$userField['SETTINGS']['HLBLOCK_ID'];
            }
        }
    }
    
    public function getTrackingHlBlockId()
    {
        return $this->trackingHlBlockId;
    }
    
    public function canMarking()
    {
        return $this->trackingHlBlockId > 0;
    }

    public function setTrackingTypeFromId(int $elId, int $trackingTypeId, int $currentHlValueId = 0)
    {
        global $USER_FIELD_MANAGER;
        if($trackingTypeId > 0 && $trackingTypeId !== $currentHlValueId) {
            $USER_FIELD_MANAGER->Update(self::$ufEntity, $elId, ['UF_PRODUCT_GROUP' => $trackingTypeId]);
        } else if($trackingTypeId <= 0 && $currentHlValueId > 0) {
            $USER_FIELD_MANAGER->Update(self::$ufEntity, $elId, ['UF_PRODUCT_GROUP' => null]);
        }
    }
    
    public function setTrackingType(int $elId, string $trackingType, int $currentHlValueId = 0)
    {
        if(!$this->canMarking()) {
            return;
        }

        global $USER_FIELD_MANAGER;

        if($trackingType !== 'NOT_TRACKED') {
            $trackingTypeValue = TrackingType::getTrackingTypeValue($trackingType);
            $hlValues = HlBlockValues::getInstance($this->trackingHlBlockId, 'UF_NAME', true);
            $hlValueId = $hlValues->getValueId($trackingTypeValue);
            if($hlValueId > 0 && $hlValueId !== $currentHlValueId) {
                $USER_FIELD_MANAGER->Update(self::$ufEntity, $elId, ['UF_PRODUCT_GROUP' => $hlValueId]);
            }
        } else if($currentHlValueId > 0) {
            $USER_FIELD_MANAGER->Update(self::$ufEntity, $elId, ['UF_PRODUCT_GROUP' => null]);
        }
    }
}