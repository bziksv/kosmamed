<?php
/**
 * Сборщик сведений о заказе. Domain object
 * Является прослойкой между сущностью заказа CMS/CRM/Framework.
 * Это первая итерация сущности для разработа для прототипа.
 * В дальнейшем возможем пересмотр на более внятную реализацию.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */
namespace DellinShipping\Entity\Order;

use DellinShipping\Entity\Order\Person;
use DellinShipping\Entity\Order\Product;
use DellinShipping\Kernel;
use Bitrix\Main\Localization\Loc;


class Order
{

    public $orderData;
    public $products = [];
    public Person $person;

    public $isErrors = false;
    public $errors = [];


    /**
     * OrderPrototype constructor.
     * @param $orderData
     * @param $products
     * @param $person
     */
//    protected function __construct()
//    {
//
//    }


    /**
     *
     * Метод задающий основные параметры заказа.
     *
     * @param int $order_id - Идентификатор заказа
     * @param int|null $shipment_id - идентификатор отгрузки (если отсутствует сущность отгрузки, приравняйте к номеру заказа)
     * @param int|null $shipping_method_id - метод доставки
     * @param int $payment_id - Идентификатор платёжного метода.
     * @param bool|null $isCashOnDelivery - Флаг определяющий наложный платёж.
     * @param float $order_total_price - Стоимость всего заказа.
     * @param float $order_shipping_cost - Стоимость доставки.
     * @param string $shipping_name - Имя доставки.
     * @param string $create_date - Дата создания.
     * @param \DellinShipping\Entity\Order\Person $person - данные о получателе.
     * @param string|null $workTimeStart - Начало диапазона для получения.
     * @param string|null $worktimeEnd - конец диапазона для получения.
     * @param bool|null $isCash - флаг опеределяющий тип оплаты, наличиный или без наличный.(при наложке) - ВНИМАНИЕ! ОТКЛЮЧЕНО! ТРЕБУЕТ РАЗБОРА!
     */


    public function setOrderData(?int $order_id, ?int $shipment_id, ?int $shipping_method_id, ?int $payment_id,
                                 ?bool $isCashOnDelivery,  ?float $order_total_price, ?float $order_shipping_cost,
                                 ?string $shipping_name, string $create_date, Person $person,
                                 ?string $workTimeStart, ?string $worktimeEnd, ?bool $isCash): void
    {

        $orderData = new \stdClass();
        $orderData->order_id = $order_id;
        $orderData->shipment_id = $shipment_id;
        $orderData->shipping_method_id = $shipping_method_id; //method
        $orderData->payment_id = $payment_id;
        $orderData->isCashOnDelivery = $isCashOnDelivery;
        $orderData->isCash = $isCash; // if cashondelivery then define card or cash
        $orderData->order_total_price = $order_total_price;
        $orderData->order_shipping_cost = $order_shipping_cost;
        $orderData->shipping_name = $shipping_name;
        $orderData->create_date = $create_date;
        $orderData->worktimeStart = ($workTimeStart == null)? '09:00' : $workTimeStart;
        $orderData->worktimeEnd = ($worktimeEnd == null) ? '18:00': $worktimeEnd;
        $this->setPerson($person);


        $this->orderData = $orderData;

    }

    public function validate(){

        //Валидация сущности person
        $poolErrors = [];


        if(!Kernel::validEmail($this->person->getEmail())){
            $this->setIsErrors(true);
            $poolErrors = array_merge($poolErrors,
                [Loc::getMessage("errors-person")] );


        }

        if( !Kernel::validPhone($this->person->getPhone())){

            $this->setIsErrors(true);
           $poolErrors = array_merge($poolErrors,
                [Loc::getMessage("errors-phone")]);
//            $poolErrors=
//                ['Произошла ошибка при валидации телефона в сущности Person. Уточните поле значение поля "телефон" у получателя.
//                Телефон должен содержать 11 символов и начинаться с "7".'];





        }

        if($this->orderData->order_total_price == '0'){
            $this->setIsErrors(true);
            $messageError = [Loc::getMessage("price_order_is_empty")];
            $poolErrors = array_merge($poolErrors, $messageError);

        }

        if($this->isErrors()){
            $this->setErrors($poolErrors);
        }



    }

    public function setPerson(Person $person)
    {
        $this->person = $person;
    }



    public function addProduct(Product $product): void
    {
        $this->products[] = $product;
    }

    /**
     * Метод для проверки данных.
     *
     * @return object
     */

    public function getOrderData(): object
    {

        $data = new \stdClass();

        $data->orderData = $this->orderData;
        $data->person = $this->person;
        $data->products = $this->products;


        return $data;

    }

    /**
     * @return bool
     */
    public function isErrors(): bool
    {
        return $this->isErrors;
    }

    /**
     * @param bool $isErrors
     */
    public function setIsErrors(bool $isErrors): void
    {
        $this->isErrors = $isErrors;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }








}