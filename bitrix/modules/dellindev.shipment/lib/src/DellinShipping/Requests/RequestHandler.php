<?php
/**
 * ������� ������� ��� API.
 * ������ RequestHandler �������� ������ ������� ��������� �����������.
 * �������� �������� ������ �������� ������ �� ���������������, �.�. ��������� ������������� �������� ��������������
 * �������� �������������.
 * TODO ��������� �����������. ������������ �� ���������� �������� �������. �������� ������� ������������ � �������.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */

namespace DellinShipping\Requests;


use BiaTech\Base\Composite\Container;
use BiaTech\Base\Composite\Field;
use BiaTech\Base\Log\LoggerInterface;
use Bitrix\Sale\Basket\FullRefreshStrategy;
use DellinShipping\Entity\Config;
use DellinShipping\Entity\Order\Order;
use DellinShipping\Entity\Cargo;
use DellinShipping\Entity\Packages;
use DellinShipping\Entity\Requirements;
use DellinShipping\Kernel;
use Bitrix\Main\Localization\Loc;


class RequestHandler
{
    /**
     * ��������� ������ �������.
     */

    /**
     * �������� ��������.
     * �������� ��������� ������� ������ (������ �� �������), ����(������ �� ������),
     * ��������� ������ � ��������� ������.
     *
     * @var Container delivery
     */

    public $delivery;

    /**
     * �������� ���������� ��������.
     * �������� ��������� ���������� ��������.
     * @var Container $members
     */


    public $members;

    /**
     * �������� �������� ����������.
     * �������� ��������� ����� � ����������� �� ���� ���������� ������.
     * ��������� ��������� ���������� �������� ������-������.
     * @var Cargo
     */

    public Cargo $cargo;

    /**
     * �������� �������� �� �������������� �����.
     * @var Packages
     */


    public Packages $packages;

    /**
     * �������� ���������� ������.
     * �������� ��������� ������ ������ �� ������ ������ � ������� ��� ���������� ��������� �������.
     * @var Container $payment
     */


    public $payment;

    /**
     * �������� ����������� ��� ����������.
     * ������������ �������� ��� ��������� ������������� ������������.
     * @var Container $productInfo
     */


    public $productInfo;

    /**
     * ��������� ��������� � ������.
     */

    /**
     * �������� ������
     * @var Order
     */

   // private Order $order;

     /**
     * �������� ���������� ���������
     * @var Config
     */

   // private Config $config;

    /**
     * ���� ���������� �� �������� ������ ��� ������ �������� ������
     * �� ���� ��� ���� ������������ �����.
     * @var bool
     */

    private $isRequest;

    /**
     * ���� ���������� �� �������� ������ ��� ������ �������� ������
     * ������������ ��� ������������ � ������ �������.
     * @var bool
     */


    private $isOrder;

    /**
     * ���������� �������� ���� ������.
     * ����� ���������� � ����������� �� ������� ������� ��������� � NetworkService
     * @var produceDate
     */


    public $produceDate;

    public $sessionID;


    public $isValidateError;
    public $errors;

    /**
     * RequestHandler constructor.
     * @param Order $order
     * @param Config $config
     * @param bool $isRequest
     * @param bool $isOrder
     * @throws \Exception
     */

    public function __construct(Order $order, Config $config, LoggerInterface $logger, bool $isRequest, bool $isOrder)
    {

        $this->order = $order;
        $this->config = $config;
        $this->logger = $logger;
        $this->isRequest = $isRequest;
        $this->isOrder  = $isOrder;
        $this->buildCargo();
        $this->buildPackages();

        /**
         * ����������� ���� ������ ����� �� ������ ��� ����� �� ��������.
         * ������������ ���������� "���������� ��������" � ����������� �����.
         * ��������! "���������� ��������" ����������� �� ������ ���.
         * ��� �������������� "�� ������" � ������ ����� "���� � ����" ����������� � ���������.
         */

        $deliveryDelay = $config->getSenderData()->deliveryDelay;
        $produceDate = date("Y-m-d",
                            mktime(0, 0, 0, date("m"),
                        date("d") + $deliveryDelay, date("Y")));

        $this->setProduceDate($produceDate);


    }

    /**
     * ����� �������� �������� delivery ��� �������.
     * ���������� ��������� ������������.
     * ��������� � ��������� : https://dev.dellin.ru/api/calculation/calculator/
     * @return Container
     * @throws \Exception
     */


    public function buildDeliveryData()
    {
        $delivery = new Container();


        $type = new Field(['type', $this->cargo->deliveryType]);//��� �������� ������������ ������.
        $deliveryType = new Field(['deliveryType', $type]);
        $arrival = new Field(['arrival', $this->setDeliveryArrival()]);
        $derival = new Field(['derival',$this->buildDerrival()]);
        $packages = new Field(['packages', $this->packages->resultPackages]);
        $delivery->add($deliveryType);
        $delivery->add($arrival);
        $delivery->add($derival);
        $delivery->add($packages);


        return $delivery;
    }


    /**
     * TODO ��������� ����� �� �������� �� �������� ����� ��������.
     */


    public function getCurrentProduceDate(){

    }

    /**
     * ����� �������� �������� derival.
     * ���������� ��������� ������������.
     * ��������� � ��������� : https://dev.dellin.ru/api/calculation/calculator/
     * @return Container
     * @throws \Exception
     */


    function buildDerrival()
    {
       $derrival = new Container();
       $produceDate = new Field(['produceDate', $this->getProduceDate()]);


       $derrival->add($this->getDerrivalAddressOrTerminal());
       $derrival->add($this->getDerrivalVariantField());

        ($this->config->isGoodsLoading())? $derrival->add($this->getTimeToDerrival()) : '';

       $derrival->add($produceDate);
       $derrival->add($this->getRequirementsDerival());

        return $derrival;
    }



    /**
     * ����� ������������ ����� ��������� ��� ������ �����.
     * ���������� ���� time � ��������� �����������.
     * ��������� � ��������� : https://dev.dellin.ru/api/calculation/calculator/
     * @return Field
     * @throws \Exception
     */

    function getTimeToDerrival()
    {

        $timeBody = new Container();

        $worktimeStart = new Field(['worktimeStart', $this->config->getCargoParams()->workStart]);
        $workBreakStart = new Field(['breakStart', $this->config->getCargoParams()->workBreakStart]);
        $workBreakEnd = new Field(['breakEnd', $this->config->getCargoParams()->workBreakEnd]);
        $worktimeEnd  = new Field(['worktimeEnd', $this->config->getCargoParams()->workEnd]);

        $timeBody->add($worktimeStart);
        $timeBody->add($worktimeEnd);
        (!empty($this->config->getCargoParams()->workBreakStart))?$timeBody->add($workBreakStart) : '';
        (!empty($this->config->getCargoParams()->workBreakEnd))?$timeBody->add($workBreakEnd) : '';


        return ($this->config->isGoodsLoading())? new Field(['time', $timeBody]) : '';

    }


    /**
     * ����� ������������ �������� ����� �������� ����� �� ��������.
     * ���������� ���� time � ��������� �����������.
     * ��������� � ��������� : https://dev.dellin.ru/api/calculation/calculator/
     * @return Field
     * @throws \Exception
     */


    function getTimeToArival()
    {
        $timeBody = new Container();

        $worktimeStart = new Field(['worktimeStart', $this->order->getOrderData()->orderData->worktimeStart]);
        $worktimeEnd = new Field(['worktimeEnd', $this->order->getOrderData()->orderData->worktimeEnd]);

        $timeBody->add($worktimeStart);
        $timeBody->add($worktimeEnd);


        return ($this->config->isGoodsUnloading())? new Field(['time', $timeBody]): '';

    }


    /**
     * ����� ������������ ��������� �����.
     * ���������� ������ � ������������ �����������.
     * ��������� � ��������� : https://dev.dellin.ru/api/calculation/calculator/
     * @return array
     * @throws \Exception
     */

    function buildCargo()
    {

        $this->cargo = new Cargo($this->order, $this->config);

    }

    function buildPackages()
    {
        $this->packages = new Packages($this->order, $this->config);
    }

    /**
     * ����� ������������ ����� ��������.
     * ���������� ���� address � ��������� ����������� ��� ���� terminalID.
     * ��������� � ��������� : https://dev.dellin.ru/api/calculation/calculator/
     * @return Field
     * @throws \Exception
     */


    function getDerrivalAddressOrTerminal()
    {
        $terminalID = new Field(['terminalID', $this->config->getLoadingData()->terminal_id]);

        $addressBody = new Container();

        $search = new Field(['search', $this->config->getLoadingData()->loadingAddress]);
        $addressBody->add($search);
        $address = new Field(['address', $addressBody]);


        return (!$this->config->getLoadingData()->isGoodsLoading)?$terminalID : $address;
    }

    /**
     * ����� ��� ��������� ��������� produceDate ��� ������ �����.
     * @return
     */

    public function getProduceDate(): ?string
    {
        return $this->produceDate;
    }

    /**
     * �������� ������� ������������� �������� produceDate ��� ������ �����.
     * @param  $produceDate
     */

    public function setProduceDate($produceDate): void
    {
        $this->produceDate = $produceDate;
    }



    function getArivalVariantField()
    {
        $variantType = ($this->config->isGoodsUnloading())?'address':'terminal';
        $variant = new Field(['variant', $variantType]);
        return $variant;
    }

    function getDerrivalVariantField()
    {
        $variantType = ($this->config->isGoodsLoading())?'address':'terminal';
        $variant = new Field(['variant', $variantType]);
        return $variant;
    }


    function setDeliveryArrival()
    {
        $arrival = new Container();
        $dataKLADR = $this->order->person->getKLADRToArrival();
        $city = new Field(['city', $dataKLADR]);
        $search = new Field(['search', $this->order->person->getFullAddress()]);
        $addressBody = new Container();
        $addressBody->add(($this->isRequest)?$search:$this->getFakeStreetAndRealCity($dataKLADR));

        $terminal = new Field(['terminalID', $this->order->person->getTerminalId()]);

        $arrival->add($this->getArivalVariantField());
        $address = new Field(['address', $addressBody]);



        if($this->config->isGoodsUnloading()){
            $arrival->add($address);
        }


        if(!$this->config->isGoodsUnloading() && $this->isRequest){
            $arrival->add($terminal);
        }


        if(!$this->config->isGoodsUnloading() && !$this->isRequest){
            $arrival->add($city);
        }

        ($this->config->isGoodsUnloading())? $arrival->add($this->getTimeToArival()): '';

        $arrival->add($this->getRequirementsArrival());


        return $arrival;
    }

    function getFakeStreetAndRealCity($city)
    {

        $fieldcity = new Field(['city', $city]);
        $fieldstreet = new Field(['street',$city]);

        $containerAddress = new Container();
        $containerAddress->add($fieldcity);
        $containerAddress->add($fieldstreet);

        return $containerAddress;

    }


    function buildSearchOrKLADRForCalc()
    {
        $terminalID = new Field(['terminalID', $this->order->person->terminal_id]);
        $addressBody = new Container();
        $city = new Field(['city', $this->order->person->getKLADRToArrival()]);
        $search = new Field(['search', $this->order->person->getFullAddress()]);
    }

    function buildEntityMembersForCalc()
    {
        $members = new Container();

        $requester = new Field(['requester', $this->getMembersRequester()]);
        $members->add($requester);

        $this->members = $members;
        return $members->toArray();
    }

    function buildEntityMembersForRequest()
    {
        $members = new Container();

        $requester = new Field(['requester', $this->getMembersRequester()]);
        $sender = new Field(['sender', $this->getMembersSender()]);
        $counteragent = new Field(['counteragent', $this->getMemberSenderCounteragentContainer()]);
        $receiver = new Field(['receiver', $this->getMembersReceiver()]);

        $members->add($requester);
        $members->add($sender);
        $members->add($counteragent);
        $members->add($receiver);

        return $members;
    }




    public function buildPaymentData()
    {

        $payment = new Container();

        $paymentCity = ($this->isRequest && $this->order->orderData->isCashOnDelivery)?
                       $this->order->person->getKLADRToArrival() : $this->config->getSenderData()->kladrCodeDeliveryFrom;

        $primaryPayer = ($this->order->orderData->isCashOnDelivery)? 'receiver' : 'sender';


        //TODO �������� ���������� �� �������, ��� ��������� ����������.
        $fieldPaymentCity = new Field(['paymentCity', $paymentCity]);
        $typeField = new Field(['type', ($this->isRequest && $this->order->orderData->isCashOnDelivery)?'cash':'noncash']);
        $primaryPayment = new Field(['primaryPayer', $primaryPayer]);

        $payment->add($fieldPaymentCity);
        $payment->add($typeField);
        $payment->add($primaryPayment);

        ($this->isRequest && $this->order->orderData->isCashOnDelivery)?$payment->add($this->buildCashOnDelivery()):null;

        return $payment;

    }

    public function buildCashOnDelivery(){

        if(empty($this->sessionID)){
            throw new \Exception(Loc::getMessage("session_ID_is_empty"));
        }

        //type = 4
        //productType = 11


        $fieldCashOnDelivery = new Field(['cashOnDelivery', [
            [
             'orderNumber'=> $this->order->orderData->shipment_id,
             'orderData' => date('Y-m-d',strtotime($this->order->orderData->create_date)),
             'paymentType' => 'cash', 
             'products' =>  $this->buildProductsArray()]
            ]
        ]);

        return $fieldCashOnDelivery;
    }



    public function buildProductInfo()
    {
        $productInfo = new Container();
        $type = new Field(['type', 4]);
        $productType = new Field(['productType', 5 ]);
        $param = new Field(['param', 'module version']);
        $value = new Field(['value', '1.1.3']);
        $infoContainer = new Container();
        $infoContainer->add($param);
        $infoContainer->add($value);
        $productInfo->add($type);
        $productInfo->add($productType);

        $infoField = new Field(['info', array($infoContainer->toArray())]);

        $productInfo->add($infoField);

        return $productInfo;
    }

    public function getRequirementsArrival(){

        $entityRequirements = new Requirements($this->config);

        return new Field(['requirements', $entityRequirements->requirementsArrival]);
    }

    public function getMembersRequester(){

        $containerRequester = new Container();

        $fieldRole = new Field(['role', 'sender']);
        $fieldEmail = new Field(['email', $this->config->getSenderContactEmail()]);
        $fieldUID = new Field(['uid', $this->config->getCouteragent()]);

        $containerRequester->add($fieldRole);
        $containerRequester->add($fieldEmail);
        (!empty($this->config->getCouteragent()))? $containerRequester->add($fieldUID) : '';

        return $containerRequester;

    }

    public function getMembersSender(){

        $containerSender = new Container();

        $fieldCounteragent = new Field(['counteragent', $this->getMemberSenderCounteragentContainer()]);
        $fieldContactPerson = new Field(['contactPersons', [
                                                    ['name' => $this->config->getSenderContactName(),
                                                     'save' => false]
                                                    ]
            ]);
        $fieldPhoneNumbers = new Field(['phoneNumbers', [['number'=> $this->config->getSenderContactPhone()]]]);


        $containerSender->add($fieldCounteragent);
        $containerSender->add($fieldContactPerson);
        $containerSender->add($fieldPhoneNumbers);


        return $containerSender;

    }

    public function getMembersReceiver()
    {
        $container = new Container();

        $fieldCounteragent = new Field(['counteragent', $this->getMembersReceiverCounteragent()]);
        $fieldContactPerson = new Field(['contactPersons', [
                                                            ['name' => $this->order->person->getFullName(),
                                                             'save' => false]
                                                        ]
                                        ]);
        $fieldPhoneNumbers = new Field(['phoneNumbers', [['number'=> $this->order->person->getPhone()]]]);
        $fieldEmail = new Field(['email', $this->order->person->getEmail()]);
        $fieldDataForReceipt = new Field(['dataForReceipt', ['send' => false]]);

        $container->add($fieldCounteragent);
        $container->add($fieldContactPerson);
        $container->add($fieldPhoneNumbers);
        $container->add($fieldEmail);
        $container->add($fieldDataForReceipt);

        return $container;

    }

    public function getMembersReceiverCounteragent(){

        $containerCounteragent = new Container();

      //TODO ������������ ��� ������ ����
      //������ ������� ������ �� ������������.


        $fieldForm = new Field(['form', '0xab91feea04f6d4ad48df42161b6c2e7a'/*$this->config->getSenderForm()*/]);// TODO ������� ��� ������ ����...
        $fieldName = new Field(['name', $this->order->person->getFullName()]);
        $fieldPhone = new Field(['phone', $this->order->person->getPhone()]);
        $fieldIsAnonim = new Field(['isAnonym', true]);

       // $fieldYuriAddress = new Field(['juridicalAddress', ['search' => $this->config->getSenderJuridicalAddress()]]);

        $containerCounteragent->add($fieldForm);
        $containerCounteragent->add($fieldName);
        $containerCounteragent->add($fieldPhone);
        $containerCounteragent->add($fieldIsAnonim);
     //   $containerCounteragent->add($fieldYuriAddress);

        return $containerCounteragent;

    }

    public function getMemberSenderCounteragentContainer(){
        $containerCounteragent = new Container();

        $fieldForm = new Field(['form', $this->config->getSenderForm()]);
        $fieldName = new Field(['name', $this->config->getSenderName()]);
        $fieldINN = new Field(['inn', $this->config->getSenderInn()]);
        $fieldIsAnonim = new Field(['isAnonym', false]);

        $fieldYuriAddress = new Field(['juridicalAddress', ['search' => $this->config->getSenderJuridicalAddress()]]);

        $containerCounteragent->add($fieldForm);
        $containerCounteragent->add($fieldName);
        $containerCounteragent->add($fieldINN);
        $containerCounteragent->add($fieldIsAnonim);
        $containerCounteragent->add($fieldYuriAddress);

        return $containerCounteragent;
    }

    public function getRequirementsDerival(){

        $entityRequirements = new Requirements($this->config);

        return new Field(['requirements', $entityRequirements->requirementsDerival]);
    }

    public function buildProductsArray(){
        $arProducts = [];

        foreach( $this->order->products as $product){

            $taxValueForApi = $product->getTaxValue() *100;//TODO ����� ��������� ��������.

            if($product->isTaxIncluded()){

                $price = $product->getPrice();

            } else {
                $price = $product->getPrice() + ($product->getPrice * floatval($product->getTaxValue()));
            }


            $arProducts[] = $this->buildProductItem($product->getName(), $product->getProductId(), $product->getQuantity(),
                                                    $price, $taxValueForApi);

        }

        return $arProducts;
    }


    public function buildProductItem($productName, $productID, $productQuantity, $productPrice, $VATRate)
    {
        $productContainer = new Container();

        $productName = new Field(['productName', $productName]);
        $productCode = new Field(['productCode', $productID]);
        $productAmount = new Field(['productAmount', intval($productQuantity)]);
        $costWithVAT = new Field(['costWithVAT', round(floatval($productPrice), 2)]);
        $productVATRate = new Field(['VATRate', intval($VATRate)]);

        $productContainer->add($productName);
        $productContainer->add($productCode);
        $productContainer->add($productAmount);
        $productContainer->add($costWithVAT);
        $productContainer->add($productVATRate);

        return $productContainer->toArray();
    }

    public function setSessionID($sessionID): void
    {
        $this->sessionID = $sessionID;
    }

    public function getRequestData($sessionId):object
    {


        $this->setSessionID($sessionId);
        $root = new Container();
        $sessionIdField = new Field(['sessionID', $sessionId]);
        $appkeyField = new Field(['appkey', $this->config->getAppkey()]);
        $fieldOrder = new Field(['inOrder', $this->isOrder]);
        $delivery = new Field(['delivery', $this->buildDeliveryData()]);
        $members = ($this->isRequest)? new Field(['members', $this->buildEntityMembersForRequest()]) :
                                       new Field(['members', $this->buildEntityMembersForCalc()]);
        $cargo = new Field(['cargo', $this->cargo->buildFullCargoInfo()]);
        $payment = new Field(['payment', $this->buildPaymentData()]);
        $productInfo = new Field(['productInfo', $this->buildProductInfo()]);


//        var_dump($this->packages->resultPackages);
//        die();

        (!empty($sessionId))?$root->add($sessionIdField) : '';
        $root->add($appkeyField);
        $root->add($delivery);
        $root->add($members);
        $root->add($cargo);
        $root->add($payment);
        $root->add($productInfo);

        ($this->isRequest)?$root->add($fieldOrder):null;

        $this->validate();

        return $root;

    }



    public function validate(){

        $errors = [];

   
        //���������� ��������� ����� �� ���������� ������.

       if($this->isCitiesCase()){

           $this->setIsValidateError(true);
           $message = Loc::getMessage("delivery_in_city").PHP_EOL;
           $context = [];

           $errors = array_merge($errors, [$message]);

           $this->logger->error($message, $context);


       }

       if($this->isValidateError()){
           $this->setErrors($errors);
       }

    }

    private function isCitiesCase(){

        $citiesIsEqual = ($this->config->getKladrCodeDeliveryFrom() == $this->order->person->getKLADRToArrival());
        $schemeNotAddressToAddress = $this->config->isGoodsLoading() ||  $this->config->isGoodsUnloading();

        return ($citiesIsEqual && $schemeNotAddressToAddress);
    }


    /**
     * @return mixed
     */
    public function isValidateError()
    {
        return $this->isValidateError;
    }

    /**
     * @param mixed $isValidateError
     */
    public function setIsValidateError($isValidateError): void
    {
        $this->isValidateError = $isValidateError;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors): void
    {
        $this->errors = $errors;
    }


}