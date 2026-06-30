<?php

namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Tools;

class option extends abstractGeneral
{
    // optionsControll
    public static $ABYSS = array();

    public static function get($option,$noRemake = true)
    {
        $self = \COption::GetOptionString(self::$MODULE_ID,$option,self::getDefault($option));

        if($self && $noRemake) {
            $handlingType = self::getHandling($option);
            switch ($handlingType) {
                case 'serialize' :
                    $self = unserialize($self, ['allowed_classes' => false]);
                    break;
                case 'json'      :
                    $self = json_decode($self,true);
                    break;
            }
        }

        return $self;
    }

    public static function set($option,$val,$doSerialise = false)
    {
        if($doSerialise){
            $val = serialize($val);
        }
        return \COption::SetOptionString(self::$MODULE_ID,$option,$val);
    }

    public static function getDefault($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt))
            return $opt[$option]['default'];
        return false;
    }

    public static function checkMultiple($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt) && array_key_exists('multiple',$opt[$option]))
            return $opt[$option]['multiple'];
        return false;
    }

    public static function getHandling($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt) && array_key_exists('handling',$opt[$option]))
            return $opt[$option]['handling'];
        return false;
    }

    public static function toOptions($helpMakros = false)
    {
        if(!$helpMakros)
            $helpMakros = "<a href='#' class='PropHint' onclick='return ".self::$MODULE_LBL."setups.popup(\"pop-#CODE#\", this);'></a>";

        $arOptions = array();
        foreach(self::collection() as $optCode => $optVal){
            if(!array_key_exists('group',$optVal) || !$optVal['group'])
                continue;

            if (!array_key_exists($optVal['group'], $arOptions))
                $arOptions[$optVal['group']] = array();

            $name = ($optVal['hasHint'] == 'Y') ? " ".str_replace('#CODE#',$optCode,$helpMakros) : '';

            $arDescription = array($optCode,Tools::getMessage("OPT_{$optCode}").$name,$optVal['default'],is_array($optVal['type']) ? $optVal['type'] : array($optVal['type']));

            if($optVal['type'] === 'selectbox'){
                $arDescription []= self::getSelectVals($optCode);
            }

            $arOptions[$optVal['group']][] = $arDescription;
        }

        return $arOptions;
    }

    public static function collection()
    {
        // name - always IPOLSDEK_OPT_<code>
        $arOptions = array(
            // logData
            'logged' => array(
                'group'   => 'logData',
                'hasHint' => 'N',
                'default' => false,
                'type'    => 'text'
            ),
            // common
            'departure' => array(
                'group'   => 'common',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => 'special'
            ),
            'termInc' => array(
                'group'   => 'common',
                'hasHint' => 'N',
                'default' => false,
                'type'    => array("text",1)
            ),
            'showInOrders' => array(
                'group'   => 'common',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => "selectbox"
            ),
            'addDeparture' => array(
                'group'   => 'common',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => "special",
                'handling' => 'serialize'
            ),
            // print
            'prntActOrdr' => array(
                'group'   => 'print',
                'hasHint' => 'Y',
                'default' => 'O',
                'type'    => 'selectbox'
            ),
            'numberOfPrints' => array(
                'group'   => 'print',
                'hasHint' => 'Y',
                'default' => '2',
                'type'    => array("text",1)
            ),
            // printShtr
            'numberOfStrihs' => array(
                'group'   => 'printShtr',
                'hasHint' => 'Y',
                'default' => '1',
                'type'    => array("text",1)
            ),
            'formatOfStrihs' => array(
                'group'   => 'printShtr',
                'hasHint' => 'Y',
                'default' => 'A4',
                'type'    => array("text",1)
            ),
            // dimensionsDef
            'lengthD' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => '400',
                'type'    => array("text",6)
            ),
            'widthD' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => '300',
                'type'    => array("text",6)
            ),
            'heightD' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => '200',
                'type'    => array("text",6)
            ),
            'weightD' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => '1000',
                'type'    => array("text",6)
            ),
            'defMode' => array(
                'group'   => 'dimensionsDef',
                'hasHint' => 'N',
                'default' => 'O',
                'type'    => 'selectbox'
            ),
            // commonRequest
            'deliveryAsPosition' => array(
                'group'   => 'commonRequest',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            'normalizePhone' => array(
                'group'   => 'commonRequest',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'addData' => array(
                'group'   => 'commonRequest',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            // orderProps
            'location' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'LOCATION',
                'type'    => "special"
            ),
            'name' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'FIO',
                'type'    => "text"
            ),
            'fName' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'FIRSTNAME',
                'type'    => "text"
            ),
            'sName' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'SECONDNAME',
                'type'    => "text"
            ),
            'mName' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'MIDDLENAME',
                'type'    => "text"
            ),
            'email' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'EMAIL',
                'type'    => "text"
            ),
            'phone' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'PHONE',
                'type'    => "text"
            ),
            'address' => array(
                'group'   => 'orderProps',
                'hasHint' => 'Y',
                'default' => 'ADDRESS',
                'type'    => "text"
            ),
            'street' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'STREET',
                'type'    => "text"
            ),
            'house' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'HOUSE',
                'type'    => "text"
            ),
            'flat' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'FLAT',
                'type'    => "text"
            ),
            'extendName' => array(
                'group'   => 'orderProps',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            // usualOrderProps
            'comment' => array(
                'group'   => 'usualOrderProps',
                'hasHint' => 'N',
                'default' => 'B',
                'type'    => "selectbox"
            ),
            // itemProps
            'articul' => array(
                'group'   => 'itemProps',
                'hasHint' => 'N',
                'default' => 'ARTNUMBER',
                'type'    => "text"
            ),
            'feacn' => array(
                'group'   => 'itemProps',
                'hasHint' => 'Y',
                'default' => 'FEACN',
                'type'    => 'text'
            ),
            'getParentArticul' => array(
                'group'   => 'itemProps',
                'hasHint' => 'N',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'addMeasureName' => array(
                'group'   => 'itemProps',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'noVats' => array(
                'group'   => 'itemProps',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            // NDS
            'NDSUseCatalog' => array(
                'group'   => 'NDS',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            'NDSGoods' => array(
                'group'   => 'NDS',
                'hasHint' => 'N',
                'default' => 'VATX',
                'type'    => "selectbox"
            ),
            'NDSDelivery' => array(
                'group'   => 'NDS',
                'hasHint' => 'N',
                'default' => 'VATX',
                'type'    => "selectbox"
            ),
            // status
            'setDeliveryId' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'markPayed' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'statusOK' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => false,
                'type'    => "selectbox"
            ),
            'statusSTORE' => array(
                'group'   => 'status',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => "selectbox"
            ),
            'statusTRANZT' => array(
                'group'   => 'status',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => "selectbox"
            ),
            'statusCORIER' => array(
                'group'   => 'status',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => "selectbox"
            ),
            'statusPVZ' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => false,
                'type'    => "selectbox"
            ),
            'statusDELIVD' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => false,
                'type'    => "selectbox"
            ),
            'statusOTKAZ' => array(
                'group'   => 'status',
                'hasHint' => 'N',
                'default' => false,
                'type'    => "selectbox"
            ),
            'setTrackingOrderProp' => array(
                'group'   => 'status',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => 'text'
            ),
            // vidjet
            'pvzID' => array(
                'group'   => 'vidjet',
                'hasHint' => 'N',
                'default' => '',
                'type'    => "text"
            ),
            'pickupID' => array(
                'group'   => 'vidjet',
                'hasHint' => 'N',
                'default' => '',
                'type'    => "text"
            ),
            'pvzPicker' => array(
                'group'   => 'vidjet',
                'hasHint' => 'Y',
                'default' => 'ADDRESS',
                'type'    => "text"
            ),
            'buttonName' => array(
                'group'   => 'vidjet',
                'hasHint' => 'N',
                'default' => '',
                'type'    => "text"
            ),
            'buttonNamePST' => array(
                'group'   => 'vidjet',
                'hasHint' => 'N',
                'default' => '',
                'type'    => "text"
            ),
            'ymapsAPIKey' => array(
                'group'   => 'vidjet',
                'hasHint' => 'Y',
                'default' => \COption::GetOptionString('fileman', 'yandex_map_api_key', ''),
                'type'    => "text"
            ),
            'noYmaps' => array(
                'group'   => 'vidjet',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'vidjetSearch' => array(
                'group'   => 'vidjet',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            'autoSelOne' => array(
                'group'   => 'vidjet',
                'hasHint' => 'Y',
                'default' => '',
                'type'    => "checkbox"
            ),
            'mindVWeight' => array(
                'group'   => 'vidjet',
                'hasHint' => 'N',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'widjetVersion' => array(
                'group'   => 'vidjet',
                'hasHint' => 'Y',
                'default' => 'ipol.sdekPickup',
                'type'    => "selectbox"
            ),
            // basket
            'noPVZnoOrder' => array(
                'group'   => 'basket',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'hideNal' => array(
                'group'   => 'basket',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'hideNOC' => array(
                'group'   => 'basket',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'cntExpress' => array(
                'group'   => 'basket',
                'hasHint' => 'Y',
                'default' => '500',
                'type'    => "text"
            ),
            // delivery
            'mindEnsure' => array(
                'group'   => 'delivery',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'ensureProc' => array(
                'group'   => 'delivery',
                'hasHint' => 'N',
                'default' => '0.75',
                'type'    => "text"
            ),
            'mindNDSEnsure' => array(
                'group'   => 'delivery',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            'forceRoundDelivery' => array(
                'group'   => 'delivery',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => "checkbox"
            ),
            // paySystems
            'paySystems' => array(
                'group'   => 'paySystems',
                'hasHint' => '',
                'default' => 'a:0:{}', // Empty array
                'type'    => "special",
                'handling' => 'serialize'
            ),
            // addingService
            'addingService' => array(
                'group'   => 'addingService',
                'hasHint' => '',
                'default' => 'a:0:{}', // Empty array
                'type'    => "special",
                'handling' => 'serialize'
            ),
            'tarifs' => array(
                'group'   => 'addingService',
                'hasHint' => '',
                'default' => 'a:0:{}', // Empty array
                'type'    => "special",
                'handling' => 'serialize'
            ),
            // warhouses
            'warhouses' => array(
                'group'   => 'warhouses',
                'hasHint' => 'Y',
                'default' => false,
                'type'    => "checkbox"
            ),
            // autoloads
            'autoloadsMode' => array(
                'group'   => 'autoloads',
                'hasHint' => 'N',
                'default' => 'O',
                'type'    => "selectbox"
            ),
            'autoloadsStatus' => array(
                'group'   => 'autoloads',
                'hasHint' => 'N',
                'default' => false,
                'type'    => "selectbox"
            ),
            // service
            'schet' => array(
                'group'   => 'service',
                'hasHint' => 'N',
                'default' => 0,
                'type'    => "text"
            ),
            'statCync' => array(
                'group'   => 'service',
                'hasHint' => 'N',
                'default' => 0,
                'type'    => "text"
            ),
            'useOldApi' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            'dostTimeout' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 6,
                'type'    => array("text",1)
            ),
            'timeoutRollback' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 15,
                'type'    => array("text",1)
            ),
            'autoAddCities' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'noSertifCheckNative' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debugMode' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            // other
            'senders' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => false,
                'type'    => "text"
            ),
            'countries' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => '{"rus":{"act":"Y"}}',
                'type'    => "text",
                'handling' => 'json'
            ),
            'noteOrderDateCC' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            'importMode' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            'autoloads' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => "checkbox"
            ),
            'sdekDeadServer' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => false,
                'type'    => "text"
            ),
            'lastSuncId' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => 0,
                'type'    => "text"
            ),
            'orderStatusesLimit' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 100,
                'type'    => "text"
            ),
            'orderStatusesUptime' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 60,
                'type'    => "text"
            ),
            'orderStatusesAgentRollback' => array(
                'group'   => 'service',
                'hasHint' => 'Y',
                'default' => 30,
                'type'    => "text"
            ),
            // debug
            'debug_widget' => array(
                'group'   => 'debug',
                'hasHint' => 'Y',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_startLogging' => array(
                'group'   => 'debug',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_fileMode' => array(
                'group'   => 'debug',
                'hasHint' => 'Y',
                'default' => 'w',
                'type'    => 'selectbox'
            ),
            // debug_events
            'debug_calculation' => array(
                'group'   => 'debug_events',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'debug_turnOffWidget' => array(
                'group'   => 'debug_events',
                'hasHint' => 'Y',
                'default' => 'Y',
                'type'    => 'checkbox'
            ),
            'debug_compability' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_calculate' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_shipments' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_orderSend' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            'debug_statusCheck' => array(
                'group'   => 'debug_events',
                'hasHint' => 'N',
                'default' => 'N',
                'type'    => 'checkbox'
            ),
            /* Check API 1.5 is enabled */
            'lastOldApiCheck' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => null,
                'type'    => "text"
            ),
            'accountsWithDisabledOldApi' => array(
                'group'   => 'other',
                'hasHint' => 'N',
                'default' => 'a:0:{}',
                'type'    => "json"
            ),
        );

        if(\sdekdriver::isConverted()){
            $arOptions = array_merge_recursive($arOptions,
                array(
                    'shipments' => array(
                        'group'   => 'common',
                        'hasHint' => 'Y',
                        'default' => 'N',
                        'type'    => "checkbox"
                    ),
                    'stShipmentOK' => array(
                        'group'   => 'status',
                        'hasHint' => 'N',
                        'default' => false,
                        'type'    => "selectbox"
                    ),
                    'stShipmentSTORE' => array(
                        'group'   => 'status',
                        'hasHint' => 'Y',
                        'default' => false,
                        'type'    => "selectbox"
                    ),
                    'stShipmentTRANZT' => array(
                        'group'   => 'status',
                        'hasHint' => 'Y',
                        'default' => false,
                        'type'    => "selectbox"
                    ),
                    'stShipmentCORIER' => array(
                        'group'   => 'status',
                        'hasHint' => 'Y',
                        'default' => false,
                        'type'    => "selectbox"
                    ),
                    'stShipmentPVZ' => array(
                        'group'   => 'status',
                        'hasHint' => 'N',
                        'default' => false,
                        'type'    => "selectbox"
                    ),
                    'stShipmentDELIVD' => array(
                        'group'   => 'status',
                        'hasHint' => 'N',
                        'default' => false,
                        'type'    => "selectbox"
                    ),
                    'stShipmentOTKAZ' => array(
                        'group'   => 'status',
                        'hasHint' => 'N',
                        'default' => false,
                        'type'    => "selectbox"
                    ),
                )
            );
        }

        return $arOptions;
    }

    public static function getSelectVals($code)
    {
        $arVals = false;

        switch($code){
            case 'debug_fileMode' :
                $arVals = array('w'=>Tools::getMessage('OPT_debug_fileMode_w'),'a'=>Tools::getMessage('OPT_debug_fileMode_a'));
                break;
            case 'showInOrders'   :
                $arVals = array("Y" => Tools::getMessage("OTHR_ALWAYS"),"N" => Tools::getMessage("OTHR_DELIVERY"));
                break;
            case 'prntActOrdr'    :
                $arVals = array("O" => Tools::getMessage('OTHR_ACTSORDRS'),"A" => Tools::getMessage('OTHR_ACTSONLY'));
                break;
            case 'formatOfStrihs' :
                $arVals = array('A4'=>'A4','A5'=>'A5','A6'=>'A6');
                break;
            case 'defMode'        :
                $arVals = array("O" => Tools::getMessage("LABEL_forOrder"),"G" => Tools::getMessage("LABEL_forGood"));
                break;
            case 'comment'        :
                $arVals = array('N'=>Tools::getMessage('OPT_comment_N'),'M'=>Tools::getMessage('OPT_comment_M'),'B'=>Tools::getMessage('OPT_comment_B'));
                break;
            case 'NDSGoods'       :
            case 'NDSDelivery'    :
                $arVals = array(
                    'VATX'  => Tools::getMessage('NDS_VATX'),
                    'VAT0'  => Tools::getMessage('NDS_VAT0'),
                    'VAT05' => Tools::getMessage('NDS_VAT05'),
                    'VAT07' => Tools::getMessage('NDS_VAT07'),
                    'VAT10' => Tools::getMessage('NDS_VAT10'),
                    'VAT12' => Tools::getMessage('NDS_VAT12'),
                    'VAT20' => Tools::getMessage('NDS_VAT20'),
                );
                break;
            case 'statusOK'        :
            case 'statusSTORE'     :
            case 'statusTRANZT'    :
            case 'statusCORIER'    :
            case 'statusPVZ'       :
            case 'statusDELIVD'    :
            case 'statusOTKAZ'     :
            case 'autoloadsStatus' :
                $arVals = self::getOrderStates();
                break;
            case 'stShipmentOK'  :
            case 'stShipmentSTORE'  :
            case 'stShipmentTRANZT' :
            case 'stShipmentCORIER' :
            case 'stShipmentPVZ'    :
            case 'stShipmentDELIVD' :
            case 'stShipmentOTKAZ'  :
                $arVals = self::getShipmentStates();
                break;

            case 'widjetVersion'  :
                $arVals = array('ipol.sdekPickup'=>Tools::getMessage('OPT_ipol.sdekPickup'),'ipol.sdekWidjet'=>Tools::getMessage('ipol.sdekWidjet'));
                break;
            case 'autoloadsMode' :
                $arVals = array('O'=>Tools::getMessage('OPT_autoloadsMode_O'),'S'=>Tools::getMessage('OPT_autoloadsMode_S'));
                break;
        }

        return $arVals;
    }

    protected static $orderStates = false;
    protected static function getOrderStates()
    {
        if(!self::$orderStates){
            self::$orderStates = array(''=>'');
            $tmpValue = \CSaleStatus::GetList(array("SORT" => "ASC"), array("LID" => LANGUAGE_ID));
            while($tmpVal=$tmpValue->Fetch()){
                if(!array_key_exists($tmpVal['ID'],self::$orderStates))
                    self::$orderStates[$tmpVal['ID']]=$tmpVal['NAME']." [".$tmpVal['ID']."]";
            }
        }

        return self::$orderStates;
    }

    protected static $shipmentStates = false;
    protected static function getShipmentStates()
    {
        if(!self::$shipmentStates){
            self::$shipmentStates = array(''=>'');
            $dbStatuses = \CSaleStatus::GetList(array('SORT' => 'asc'),array('TYPE'=>'D','LID'=>'ru'),false,false,array('ID','TYPE','NAME'));
            while($arStatus = $dbStatuses->Fetch())
                self::$shipmentStates[$arStatus['ID']] = $arStatus['NAME']." [{$arStatus['ID']}]";
        }

        return self::$shipmentStates;
    }
}