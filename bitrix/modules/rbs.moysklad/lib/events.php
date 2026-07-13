<?php
namespace Rbs\Moysklad; 

use \Bitrix\Main\Event;

class Events
{
    public static function onSalePaymentEntitySaved(Event $event)
    {
        if(!Config::isDemoExpired()){
            if(!Config::isProfilesOn()){
                return \CRbsMoysklad::onSalePaymentEntitySaved($event);
            } else {
                $profileIdList = Config::getProfileIdList($event);
                foreach($profileIdList as $profileId){
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::onSalePaymentEntitySaved($event);
                }
            }
        }
    } 

    public static function onBeforeSalePaymentDeleted(Event $event)
    {
        if(!Config::isDemoExpired()){
            if(!Config::isProfilesOn()){
                return \CRbsMoysklad::onBeforeSalePaymentDeleted($event);
            } else {
                $profileIdList = Config::getProfileIdList($event);
                foreach($profileIdList as $profileId){
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::onBeforeSalePaymentDeleted($event);
                }
            }
        }
    }

    public static function onSaleShipmentEntitySaved(Event $event)
    {
        if(!Config::isDemoExpired()){
            if(!Config::isProfilesOn()){
                return \CRbsMoysklad::onSaleShipmentEntitySaved($event);
            } else {
                $profileIdList = Config::getProfileIdList($event);
                foreach($profileIdList as $profileId){
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::onSaleShipmentEntitySaved($event);
                }
            }
        }
    }

    public static function onBeforeSaleShipmentDeleted(Event $event)
    {
        if (!Config::isDemoExpired()) {
            if (!Config::isProfilesOn()) {
                return \CRbsMoysklad::onBeforeSaleShipmentDeleted($event);
            } else {
                $profileIdList = Config::getProfileIdList($event);
                foreach ($profileIdList as $profileId) {
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::onBeforeSaleShipmentDeleted($event);
                }
            }
        }
    }

    public static function onSaleOrderEntitySaved(Event $event)
    {
        if(!Config::isDemoExpired()){
            if(!Config::isProfilesOn()){
                return \CRbsMoysklad::onSaleOrderEntitySaved($event); 
            } else {
                $profileIdList = Config::getProfileIdList($event);
                foreach($profileIdList as $profileId){
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::onSaleOrderEntitySaved($event);
                }
            }
        }
    } 

    public static function updateAgent(Customerorder $customerOrder)
    {
        if(!Config::isDemoExpired()){
            if(!Config::isProfilesOn()){
                return \CRbsMoysklad::updateAgent($customerOrder);
            } else {
                $profileIdList = Config::getProfileIdList(null, $customerOrder);
                foreach($profileIdList as $profileId){
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::updateAgent($customerOrder);
                }
            }
        }
    }

    public static function onSaleStatusOrderChange(Event $event)
    {
        if(!Config::isDemoExpired()){
            if(!Config::isProfilesOn()){
                return \CRbsMoysklad::onSaleStatusOrderChange($event);
            } else {
                $profileIdList = Config::getProfileIdList($event);
                foreach($profileIdList as $profileId){
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::onSaleStatusOrderChange($event);
                }
            }
        }
    }

    public static function onSaleOrderCanceled(Event $event)
    {
        if(!Config::isDemoExpired()){
            if(!Config::isProfilesOn()){
                return \CRbsMoysklad::onSaleOrderCanceled($event);
            } else {
                $profileIdList = Config::getProfileIdList($event);
                foreach($profileIdList as $profileId){
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::onSaleOrderCanceled($event);
                }
            }
        }
    }

    /** @deprecated */
    public static function updateOrder(Customerorder $customerOrder)
    {
        if (!Config::isDemoExpired()) {
            if (!Config::isProfilesOn()) {
                return \CRbsMoysklad::updateOrder($customerOrder);
            } else {
                $profileIdList = Config::getProfileIdList(null, $customerOrder);
                foreach ($profileIdList as $profileId) {
                    Config::setProfileId($profileId);
                    \CRbsMoysklad::updateOrder($customerOrder);
                }
            }
        }
    }

}