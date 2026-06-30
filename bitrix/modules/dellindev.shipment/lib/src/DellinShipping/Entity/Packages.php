<?php


namespace DellinShipping\Entity;


use DellinShipping\Entity\Order\Order as Order;

class Packages
{

    /**
     * Фасады основных сущностей.
     * @var Order  - сущность заказа
     * @var Config - сущность конфига.
     */
    private Order $order;


    private Config $config;


    public $state;

    //TODO Необходимо проработать варианты валидации согласно
    public $isValidateErros;
    public $validateErrors;

    public $resultPackages = [];

    /**
     * ВНИМАНИЕ! УСТАРЕЛО!
     * Пул хардкодов для API при сборке запросов к методу калькулятора.
     * @var array
     */
//    public $packagesArrayForCalc = [
//        'box' => '0x951783203a254a05473c43733c20fe72',
//        'crate' => '0x838FC70BAEB49B564426B45B1D216C15',
//        'cratePlus' => '0x8783b183e825d40d4eb5c21ef63fbbfb',
//        'type' => '0x9A7F11408F4957D7494570820FCF4549',
//        'bubble' => '0xA8B42AC5EC921A4D43C0B702C3F1C109',
//        'bag' => '0xAD22189D098FB9B84EEC0043196370D6',
//        'pallet' => '0xBAA65B894F477A964D70A4D97EC280BE',
//        'carGlass' => '0x9dd8901b0ecef10c11e8ed001199bf6f',
//        'carParts' => '0x9dd8901b0ecef10c11e8ed001199bf70',
//        'palletWithBubble' =>'0x9dd8901b0ecef10c11e8ed001199bf71',
//        'crateWithBubble' => '0x9dd8901b0ecef10c11e8ed001199bf6e'
//    ];

    /**
     * Пул хардкодов для API при сборке запросов к методу создания заявки и калькулятора.
     * @var array
     */

    public $packagesArrayForRequest = [
        'box'=>'0x82750921BC8128924D74F982DD961379',
        'crate' => '0xA6A7BD2BF950E67F4B2CF7CC3A97C111',
        'cratePlus' => '0xB26E3AE60BF5FB6646363AFC69A10819',
        'type' => '0xAE2EEA993230333043E719D4965D5D31',
        'bubble' => '0xB5FF5BC18E642C354556B93D7FBCDE2F',
        'bag' => '0x947845D9BDC69EFA49630D8C080C4FBE',
        'pallet' => '0xA0A820F33B2F93FE44C8058B65C77D0F',
        'carGlass' => '0xad97901b0ecef0f211e889fcf4624fed',
        'carParts' => '0xad97901b0ecef0f211e889fcf4624fea',
        'palletWithBubble' => '0xad97901b0ecef0f211e889fcf4624feb',
        'crateWithBubble' => '0xad97901b0ecef0f211e889fcf4624fec'
    ];


    /**
     * Точка входа для валидации кейсов.
     * Оценить возможность использования совместно с методом.
     * https://dev.dellin.ru/api/catalogs/available-packages/
     * @var
     */
    public $validateCase;

    /**
     * Packages constructor.
     * @param Order $order
     * @param Config $config
     * @param $isRequest
     */



    function __construct(Order $order, Config $config)
    {
        $this->config = $config;
        $this->order = $order;

        $this->buildPackagesForApi();
    }



    public function buildPackagesForApi(){

       $hardCodePull = $this->packagesArrayForRequest;

       $arrayActiveAdditionalService = $this->config->getArrayAdditionalServicePacking();

       foreach ($hardCodePull as $type => $hardCode){
           if(in_array($type, $arrayActiveAdditionalService, true)){
               $arrItem = [ 'uid' => $hardCode ];
               $this->addCountInPacking($type, $arrItem);

               $this->resultPackages[] = $arrItem;
           }
       }

    }

    private function addCountInPacking($type, &$arrItem){
        switch ($type){
            case 'box':
                $arrItem['count'] = $this->config->getPackingBoxCount();
                break;
            case 'bag':
                $arrItem['count'] = $this->config->getPackingBagCount();
                break;
            case 'carGlass':
                $arrItem['count'] = $this->config->getPackingCarGlass();
                break;
            case 'carParts':
                $arrItem['count'] = $this->config->getPackingCarParts();
                break;
        }
    }


}