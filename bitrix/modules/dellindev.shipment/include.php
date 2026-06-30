<?php

use Bitrix\Main\Localization\Loc;

CModule::AddAutoloadClasses('dellindev.shipment', array(

    //'Sale\Handlers\Delivery\DellinHandler'      => '/classes/general/dellindeliveryhandler.php',
    //���� ������������ �������
    'BiaTech\Base\Composite\CompositeInterface' => '/lib/src/BiaTech/Base/Composite/CompositeInterface.php',
    'BiaTech\Base\Composite\Container'          => '/lib/src/BiaTech/Base/Composite/Container.php',
    'BiaTech\Base\Composite\Field'              => '/lib/src/BiaTech/Base/Composite/Field.php',
    //���� ��� ������������
    'BiaTech\Base\Log\LoggerInterface'          => '/lib/src/BiaTech/Base/Log/LoggerInterface.php',
    'BiaTech\Base\Log\Logger'                   => '/lib/src/BiaTech/Base/Log/Logger.php',
    //���� ��������� ��� ������ ������� � ��
    'DellinShipping\Entity\Order\Order'         => 'lib/src/DellinShipping/Entity/Order/Order.php',
    'DellinShipping\Entity\Config'              => '/lib/src/DellinShipping/Entity/Config.php',
    'DellinShipping\Entity\Cargo'               => '/lib/src/DellinShipping/Entity/Cargo.php',
    'DellinShipping\Entity\Requirements'        => '/lib/src/DellinShipping/Entity/Requirements.php',
    'DellinShipping\Entity\Order\Person'        => '/lib/src/DellinShipping/Entity/Order/Person.php',
    'DellinShipping\Entity\Order\Product'       => '/lib/src/DellinShipping/Entity/Order/Product.php',
    //���� ����������� ������� �������� � api
    'DellinShipping\Requests\RequestHandler'    => '/lib/src/DellinShipping/Requests/RequestHandler.php',
    //������� ���� SDK
    'DellinShipping\NetworkService'             => '/lib/src/DellinShipping/NetworkService.php',
    //���� ��� �������������� ���� SDK � CMS Bitrix
    'DellinShipping\Kernel'                     => '/lib/src/DellinShipping/Kernel.php',
    //�������������� �������� ��� ����������� ����� ������������
    'Sale\Handlers\Delivery\Dellin\AjaxService' => '/classes/general/ajaxservice.php',
    'DellinShipping\ExclusionList' => '/lib/src/DellinShipping/ExclusionList.php',
    'Sale\Handlers\Delivery\DellinBlockAdmin' =>
    'classes/general/dellinblockadmin.php',

));


if(CModule::IncludeModule('crm')){

    Bitrix\Main\EventManager::getInstance()->addEventHandler(
        'crm',
        'onEntityDetailsTabsInitialized',
        static function(\Bitrix\Main\Event $event) {
            $guid = $event->getParameter('guid');
            if(substr( $guid, 0, 14 ) === "order_shipment"){

                $tabs = $event->getParameter('tabs');
                $shipmentID = $event->getParameter('entityID');

                $dbRes = \Bitrix\Sale\ShipmentCollection::getList([
                    'select' => ['ORDER_ID', 'DELIVERY_ID'],
                    'filter' => [
                        '=ID' => $shipmentID,
                    ]
                ]);

                $orderID = 0;
                $shipmentMethodID = 0;
                while ($item = $dbRes->fetch())
                {
                    $orderID = $item['ORDER_ID'];
                    $shipmentMethodID = $item['DELIVERY_ID'];
                }

                $sessid = bitrix_sessid_get();

                global $APPLICATION;
                $siteID = $APPLICATION->GetSiteByDir()['SITE_ID'];


                if(\Sale\Handlers\Delivery\DellinBlockAdmin::isDellinMethod($shipmentMethodID)){

                    $tabs[] = [
                        'id' => 'dellin_custom',
                        'name' => Loc::getMessage("DELLINDEV_CREATE_REQUEST"),
                        'loader'=> [
                            'serviceUrl' => '/local/components/dellindev/crm.dellin.request/lazyload.ajax.php?'.$sessid.
                                '&shipmentId='.$shipmentID.'&site='.$siteID,
                            'componentData' => [
                                'template' => '.default',
                                'shipment' => $shipmentID,
                                'orderID' => $orderID
                            ]
                        ]
                    ];
                }

//                echo '<pre>';
//                var_dump($tabs);
//                echo '</pre>';
//                die();

                return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, [
                    'tabs' => $tabs,
                ]);
            }
        }
    );

}


