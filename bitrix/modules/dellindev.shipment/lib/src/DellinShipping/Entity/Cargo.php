<?php

/**
 * Переиспользуемая сущность Cargo. Определяет параметры для погрузки.
 * Объектно-ориентированное представление для работы с сборщиком параметров для отправки запросов.
 * Сущность Cargo используется в методах получения produceDate, CalculatorV2, RequestV2.
 * Текущая сущность может изменять параметр deliveryType внутри конфига на малогабарит(при пройденной валидации).
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */


namespace DellinShipping\Entity;


use Bitrix\Main\DB\Exception;
use DellinShipping\Entity\Config;
use DellinShipping\Entity\Order\Order as Order;
use DellinShipping\NetworkService;


class Cargo
{
    /**
     * Количество
     * @var $quantity
     */

    public $quantity;

    /**
     * Максимальная длина
     * @var $maxProductLength
     */

    public $maxProductLength = 0;

    /**
     * Максимальная высота
     * @var $maxProductHeight
     */

    public $maxProductHeight = 0;
    /**
     * Максимальная ширина
     * @var $maxProductWidth
     */
    public $maxProductWidth = 0;
    /**
     * Заявленная масса
     * @var $weight
     */
    public $weight = 0;
    /**
     * Максимальная строна. Необходима для кантовки места вдоль кузова.
     * @var $maxSide
     */
    public $maxSide = 0;
    /**
     * Общий погрузочный объём.
     * @var $totalWeight
     */
    public $totalWeight = 0;
    /**
     * Общий погрузочный объём.
     * @var $totalVolume
     */
    public $totalVolume = 0;
    /**
     * Параметр объёма негабарита относится к погрузочному месту.
     * Валидируется по свойству placeStricts.
     * @var $oversizedVolume
     */
    public $oversizedVolume = 0;
    /**
     * Параметр массы негабарита относится к погрузочному месту.
     * Валидируется по свойству placeStricts.
     * @var $oversizedWeight
     */
    public $oversizedWeight = 0;
    /**
     * Параметр наименование товара.
     * Ограничен 255 символами.
     * @var $freightName
     */
    public $freightName='';

    /**
     * Хардкод характера груза.   
     * @var $freightUID
     */
    public $freightUID='';


    /**
     * Параметр необходимый для валидации возможности погрузки.
     * Длина в метрах. Масса в кг. Здесь указаны параметры фуры.
     * @var array
     */

    private $globalStricts = [
        'length'=>13.6, 
        'width_height'=>2.4,
        'totalVolume' => 80,
        'totalWeight' => 20000
    ];

    /**
     * Параметр необходимый для валидации грузового места габарита или не габарита.
     * Длина в метрах. Масса в кг. Здесь указаны параметры габаритного места.
     * Если выше - это не габарит и мы должны добавить в запрос параметры oversizedWeight и oversizedVolume.
     * @var array
     */
    private $placeStricts = [
        'length' => 3,
        'width_height'=>3,
        'totalVolume'=>27,
        'totalWeight' => 100
        ];

    /**
     * Параметр необходимый для валидации малогабаритного груза.
     * Длина в метрах. Масса в кг. Здесь указаны параметры габаритного места.
     * Влияет на параметр deliveryType вероятнее всего отправим его в конфиг.
     * @var array
     */

    private $smallStricts = [
        'length' => 0.54,
        'width_height'=>0.39,
        'totalVolume'=>0.1,
        'totalWeight' => 30
        ];


    /**
     * Фасады основных сущностей.
     * @var Order  - сущность заказа
     * @var Config - сущность конфига.
     */

    private Order $order;
    private Config $config;

    public $deliveryType = 'auto';


    function __construct(Order $order, Config $config)
    {
        $this->config = $config;
        $this->order = $order;
        $this->setFrieghtTypeUID($config->frieghtTypeUID);
        $this->changeProductsWithEmptyDemensionsAndWieght($this->order->products);
        $this->setMaxDemensions();
        $this->changeDeliveryType();
        
    }

    function changeProductsWithEmptyDemensionsAndWieght(&$products){

        foreach ($products as $product){
            if(empty($product->length) && $this->config->isUseDefaultDemension()){
                $product->setLength($this->config->getDefaultLenght(), $product->getUnitDemensions());
            }

            if(empty($product->width) && $this->config->isUseDefaultDemension()){
                $product->setWidth($this->config->getDefaultWidth(), $product->getUnitDemensions());
            }

            if(empty($product->height) && $this->config->isUseDefaultDemension()){
                $product->setHeight($this->config->getDefaultHeight(), $product->getUnitDemensions());
            }

            if(empty($product->weight) && $this->config->isUseDefaultDemension()){
                $product->setWeight($this->config->getDefaultWeight(), $product->getUnitWeight());
            }
        }

    }

    /**
     * Определяем параметры груза которые в дальнейшем будем переопределять.
     */


    public function setMaxDemensions(){


        $products = $this->order->products;

        /**
         * Определяем самые крупные габариты для валидации возможности погрузки.
         *
         */

        foreach($products as $index=>$product){


            if($product->length > $this->maxProductLength) $this->maxProductLength = $product->length;
            if($product->height > $this->maxProductHeight) $this->maxProductHeight = $product->height;
            if($product->width  > $this->maxProductWidth) $this->maxProductWidth = $product->width;
            if($product->weight > $this->weight) $this->weight = $product->weight;
            // ---Start---
            // Выпилил эт параметр т.к. он применяюется ниже при группировании грузомест.
            $this->totalWeight += $product->weight*$product->quantity;
            $this->totalVolume += $product->length * $product->height * $product->width * $product->quantity;


            // ---END---
            $this->freightName .= (($index != 0) ? ',' : '') . $product->name;
        }

    }
    
    public  function  validationOversize($volumeCargoPlace, $weightCargoPlace, $dimensionsCargoPlace)
    {
        $stricts = $this->placeStricts;
        
        if($volumeCargoPlace >= $stricts['totalVolume'])
        {
            return true;
        }

        if($weightCargoPlace>= $stricts['totalWeight'])
        {
            return true;
        }

        if($dimensionsCargoPlace->length >= $stricts['length'])
        {
            return  true;
        }

        if($dimensionsCargoPlace->width >= $stricts['width_height'])
        {
            return true;
        }

        if($dimensionsCargoPlace->height >= $stricts['width_height'])
        {
            return true;
        }

        return  false;
        
        
        
    }

    /*
     * Расчёт оверсайзных параметров.
     */

        

    public function setFrieghtTypeUID($uid)
    {
        $this->freightUID = $uid;
    }

    /**
     * Общий метод для валидации данных.
     * Параметры для валидации находятся в stricts свойства класса.
     * @param $stricts
     * @return bool
     */
    public function productOversizeValidation($stricts){

        //ВАЖНО: ДЛИНА В STRICTS ДОЛЖНА БЫТЬ ЗАПОЛНЕНА САМЫМ БОЛЬШИМ ЗНАЧЕНИЕМ СТОРОНЫ (13.6м для фуры, к примеру).
        //смотрим на каждый товар и проверяем, можно ли уложить его в фуру как есть и с поворотом.
        // Если нет, вообще дальше не считаем
        
        $length = $this->maxProductLength;
        $width = $this->maxProductWidth;
        $height = $this->maxProductHeight;
        $totalWeight = $this->totalWeight;
        $totalVolume = $this->totalVolume;
        if($totalWeight >= $stricts['totalWeight']) return false;
        if($totalVolume >= $stricts['totalVolume']) return false;

        //если товар торчит из фуры/грузоместа по самой длинной стороне ('length'), то даже с поворотом груз не уложить
        if($length > $stricts['length'] || $width > $stricts['length']
            || $height > $stricts['length']){
            return false;
        }
        $isOversized = true;

        //Если все стороны меньше минимального значения одной стороны, то товар габаритный.
        // Если есть сторона больше минимального значения (но меньше stricts['length']),
        // то 2 остальные не могут быть больше минимального значения, поэтому вертим товар во всех трех плоскостях.
        // Если находим такое положение, где товар не выпирает из габаритов, значит товар габаритный.
        
        if(
            ($length <= $stricts['width_height'] && $length <= $stricts['width_height'] && $height<= $stricts['width_height'])||
            ($length > $stricts['width_height'] && $width <= $stricts['width_height'] && $height <= $stricts['width_height']) ||
            ($width > $stricts['width_height'] && $length <= $stricts['width_height'] && $height <= $stricts['width_height']) ||
            ($height > $stricts['width_height'] && $length <= $stricts['width_height'] && $width <= $stricts['width_height'])
        ){
            $isOversized = false;
        }
        if($isOversized) return false;

        //По итогу всех проверок, если общий вес/объем товаров вписывается в фуру/грузоместо
        // и товар можно положить так, чтобы он не выпирал из фуры/грузоместа,
        // значит он габаритный

        return true;
    }


    /**
     * Подготавливаем данные для создания сущности Cargo
     */

    protected function prepareCargoData()
    {

        $products = $this->order->products;
        /*
        * Обрабатываем параметры груза исходя из компоновки грузового места.
        */
        switch ($this->config->getCargoParams()->loadingGroupingOfGoods) {
            //Если весь товар в одно грузоместо
            case 'ONE_CARGO_SPACE':
                $this->quantity = 1;
                $this->weight = $this->totalWeight;
                $height = 0;
                
                foreach($products as $product)
                {
                    $height +=  $product->height * $product->quantity;
                }
                
                $this->maxProductHeight = $height;

                
                $dimensions = new \stdClass();
                //При группировке укладываем позиции в высоту.
                
                $dimensions->height = $height;
                $dimensions->weight = $this->totalWeight;
                $dimensions->length = $this->maxProductLength;
                $dimensions->width = $this->maxProductWidth;
                
                if($this->validationOversize($this->totalVolume, $this->totalWeight, $dimensions))
                {
                    $this->oversizedWeight = $this->totalWeight;
                    $this->oversizedVolume = $this->totalVolume;
                }
                
                $this->maxProductHeight = $height;

                break;
        // Если группируем каждый вид товара, как отдельное грузоместо.
            case 'SEPARATED_CARGO_SPACE':
                $this->quantity = count($products);

                foreach($products as $product)
                {
                    $volume = ($product->length * $product->height *
                        $product->width) * $product->quantity;
                    $weight = $product->weight * $product->quantity;
                    
                    $dimensions = new \stdClass();
                    //При группировке укладываем позиции в высоту.
                    $dimensions->height =  $product->height * $product->quantity;
                    $dimensions->weight = $weight;
                    $dimensions->length = $product->length;
                    $dimensions->width = $product->width;

                    
                    if($this->validationOversize($volume, $weight, $dimensions))
                    {
                        $this->oversizedWeight += $weight;
                        $this->oversizedVolume += $volume;
                    }
                }

                break;
            // Если каждая единица товара - отдельное грузоместо.
            case 'SINGLE_ITEM_SINGLE_SPACE':

                foreach ($products as $product) {
                    $this->quantity += $product->quantity;
                    $volume = $product->length * $product->height * $product->width;
                    $weight = $product->weight;
                    if($this->validationOversize($volume, $weight, $product))
                    {
                        $this->oversizedWeight += $product->weight*$product->quantity ;
                        $this->oversizedVolume += $volume * $product->quantity;
                    }
                
                }

                break;
            default :
                throw new \Exception(Loc::getMessage("METHOD_GROUPING_IS_UNDEF"));
                break;
        }
    }


    /**
     * Метод, который отвечает за кантовку груза внутри погрузочного места.
     *
     */
    public function switchToValidLWH()
    {
       // т.к. длина - это параметр погрузки вдоль кузова, принимаем его за макcимальный.

        $this->maxSide = $this->maxProductLength;
        if($this->maxProductWidth > $this->maxProductLength){
            $this->maxSide = $this->maxProductWidth;
            $this->maxProductWidth = $this->maxProductLength;
            $this->maxProductLength = $this->maxSide;
        }

        if($this->maxProductHeight > $this->maxProductLength){
            $this->maxSide = $this->maxProductHeight;
            $this->maxProductHeight = $this->maxProductLength;
            $this->maxProductLength = $this->maxSide;
        }
    }



    public function changeDeliveryType(){

        if(!$this->config->isSmallGoods()){ 
            return ;
        }

        $terminalsInDerivalPoint = NetworkService::GetTerminals($this->config->getAppkey(),
                                                            $this->config->getKladrCodeDeliveryFrom(), false);
        $terminalsInArrivalPoint = NetworkService::GetTerminals($this->config->getAppkey(),
                                                            $this->order->person->getKLADRToArrival(), false);

        $isTerminalsInCityDerival = (is_array($terminalsInDerivalPoint['terminals']))? (count($terminalsInDerivalPoint['terminals']) > 0) : false;

        $isTerminalsInCityArival = (is_array($terminalsInArrivalPoint['terminals'])) ? (count($terminalsInArrivalPoint['terminals']) > 0) : false;

        $isTerminalsInCities = $isTerminalsInCityDerival && $isTerminalsInCityArival;


        if(self::productOversizeValidation($this->smallStricts) && $this->config->isSmallGoods()
            && $this->config->isGoodsUnloading() && $this->config->isGoodsLoading() && $isTerminalsInCities){

            $this->deliveryType = 'small';

        }
    }

    /**
     * Метод формирующий результат мутирования данных для запросов.
     * Используется как конечный интерфейс для определения параметров для отправки сущности Cargo.
     * @return array
     * @throws \Exception
     */
    public function buildFullCargoInfo()
    {

        if(self::productOversizeValidation($this->globalStricts)){

            $this->prepareCargoData();
            $this->switchToValidLWH();

            if(empty($this->weight) || empty($this->totalVolume) || empty($this->totalWeight)
                || empty($this->maxProductLength) || empty($this->maxProductWidth) || empty($this->maxProductHeight)){
                throw new \Exception('Cargo is not valid');
            }

            $result = [
                'quantity'          =>$this->quantity,
                'length'            =>(floatval($this->maxProductLength) < 0.01)?
                                       0.01 : round($this->maxProductLength,2),
                'width'             =>(floatval($this->maxProductWidth) < 0.01)?
                                       0.01 : round($this->maxProductWidth,2),
                'height'            =>(floatval($this->maxProductHeight) < 0.01)?
                                       0.01 : round($this->maxProductHeight,2),
                'weight'            =>(floatval($this->weight) < 0.01)?
                                       0.01 : round($this->weight, 2),
                'totalVolume'       =>(floatval($this->totalVolume) < 0.01)?
                                       0.01 : round(floatval($this->totalVolume),2),
                'totalWeight'       =>(floatval($this->totalWeight) < 0.01)?
                                       0.01 : round(floatval($this->totalWeight),2),
                'insurance'         => [
                    'statedValue'   => ($this->config->isInsuranceGoodsWithDeclarePrice())?
                              $this->order->orderData->order_total_price - $this->order->orderData->order_shipping_cost : 0,
                    'payer'         => ($this->order->orderData->isCashOnDelivery)? 'receiver' : 'sender',
                    'term'          => false
                ]
               // 'freightUID'    => '0xa4a904cf9927043442973c854c077430'
            ];

            $oversizedWeight = ['oversizedWeight' =>($this->oversizedWeight < 0.01)?
                                0.01:round(floatval($this->oversizedWeight), 2)];
            $oversizedVolume = ['oversizedVolume' => ($this->oversizedVolume < 0.01)?
                                0.01:round(floatval($this->oversizedVolume), 2)];
            

            if(!empty($this->oversizedVolume) || !empty($this->oversizedWeight)){
               $result =  array_merge($result, $oversizedWeight, $oversizedVolume);
            }

            if(!empty($this->freightUID)){
                $result = array_merge($result, ['freightUID'=> $this->freightUID]);
            }

            return $result;
        } else {
            throw new \Exception(Loc::getMessage("CARGO_IS_NOT_VALID"));
        }
    }




}