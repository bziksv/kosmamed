<?php
namespace Rbs\Moysklad;

use \Bitrix\Main\Loader;

Loader::includeModule('sale');

class OrderFilter
{
   public static function isFiltred($orderId = 0, $order = null)
   {
      $isFiltred = false;

      $isValidOrder = (int)$orderId > 0 || $order instanceof \Bitrix\Sale\Order;

      if (Config::isFilterOn() && $isValidOrder) {

         if (!($order instanceof \Bitrix\Sale\Order) && (int)$orderId > 0) {
            $order  = \Bitrix\Sale\Order::load($orderId);
         }

         if ($order instanceof \Bitrix\Sale\Order && $order->getId() > 0) {
            
            $siteFilter = Config::getOption('f_site_id');
            if($siteFilter !== 'NON_FILTER'){
               if($siteFilter !== $order->getSiteId()){
                  $isFiltred = true;
               }
            }

            $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnFilterOrder", array(
               'orderId' => $orderId,
               'isFiltred' => $isFiltred
            ));
            $event->send();
           
            if ($event->getResults()) {
               foreach ($event->getResults() as $eventResult) {
                  if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                     $paramsFromsEvent = $eventResult->getParameters();
                     if(isset($paramsFromsEvent['isFiltred'])){
                        $isFiltred = (bool)$paramsFromsEvent['isFiltred'];
                     }
                  }
               }
            }
            
         }
      }

      return $isFiltred;
   }

   /**
    * true - order should be stop export
    * false - order can export
    */

   public static function isFiltredByStatus($statusId = ''): bool
   {
      $statusExportType = Config::getOption('status_export_type', 'NON_SYNC');
      $targetStatus = Config::getOption('status_export', '');

      $isFiltred = false; //can export is default option

      if(!empty($statusId) && !empty($targetStatus) && $statusExportType !== 'NON_SYNC') {
         
         switch ($statusExportType) {
            case 'UPPER':
               if($statusId !== $targetStatus) {
                  $statusSort = Config::getAllStatusInfo();
                  if (isset($statusSort[$statusId]) && isset($statusSort[$targetStatus])) {
                     $isEqualSort = (int)$statusSort[$statusId]['SORT'] === (int)$statusSort[$targetStatus]['SORT'];
                     $currentStatusSort = $isEqualSort ? $statusSort[$statusId]['ID'] : (int)$statusSort[$statusId]['SORT'];
                     $targetStatusSort = $isEqualSort ? $statusSort[$targetStatus]['ID'] : (int)$statusSort[$targetStatus]['SORT'];
                     $isFiltred = $currentStatusSort < $targetStatusSort;
                  }
               }             
               break;
            case 'EQUAL':
               $isFiltred = $targetStatus !== $statusId; //stop export if state isn't equal
               break;
         }
      }

      return $isFiltred;
   }

}