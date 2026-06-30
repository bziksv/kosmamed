<?php
/**
 * Переиспользуемая сущность Product. Определяет параметры для конкретного товара.
 * Сущность Product является частью сущности Order.
 * Текущая валидирует параметры для товара и переводит единицы измерения в систему СИ.
 * Внимание! В этой сущности отсутствует связь между товаром и упаковками.
 * Доп. сервисы перенесены в конфиг, мир ещё не готов к удельным значениям коэффицента перевода, но в будущем возможно
 * реализуем дополнительные упаковки в рамках проекта
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */

namespace DellinShipping\Entity\Order;


use BiaTech\Base\Composite\Field;
use BiaTech\Base\Composite\Container;
use Bitrix\Main\Localization\Loc;

class Product
{
    public $product_id;
    public $name;
    public $quantity;
    public $unit_weight;
    public $weight;
    public $unit_demensions;
    public $length;
    public $width;
    public $height;
    public $price;
    public $packages;
    public $isTaxIncluded;
    public $taxValue;

    /**
     * Product constructor.
     * @param $product_id
     * @param $name
     * @param $quantity
     * @param $unit_weight
     * @param $weight
     * @param $unit_demensions
     * @param $length
     * @param $width
     * @param $height
     * @param $packages
     */
    public function __construct($product_id, $name, $quantity, $unit_weight, $weight,
                                $unit_demensions, $length, $width, $height, $price, $isTaxIncluded, $taxValue)
    {
        $this->product_id = $product_id;
        $this->setName($name);
        $this->setQuantity($quantity);
        $this->setUnitWeight($unit_weight);
        $this->setWeight($weight);
        $this->setUnitDemensions($unit_demensions);
        $this->setLength($length);
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setPrice($price);
        $this->setIsTaxIncluded($isTaxIncluded);
        $this->setTaxValue($taxValue);
    }





    static function convertToM($value, $units){
        switch($units){
            case 'CM':
                return $value/100;
                break;
            case 'MM':
                return $value/1000;
                break;
            case 'M' :
                return $value;
                break;
            default:
                throw new \Exception(Loc::getMessage("Exception_dont_change_in_meters"));
                break;
        }
    }



    static function convertToKg($value, $units){
        switch($units){
            case 'G':
                return $value/1000;
                break;
            case 'T':
                return $value*1000;
                break;
            case 'KG' :
                return $value;
                break;
            default:
                throw new \Exception(Loc::getMessage("Exception_dont_change_in_kg"));
                break;
        }
    }
    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * @param mixed $product_id
     */
    public function setProductId($product_id): void
    {
        $this->product_id = $product_id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getUnitWeight()
    {
        return $this->unit_weight;
    }

    /**
     * @param mixed $unit_weight
     */
    public function setUnitWeight($unit_weight): void
    {
        $this->unit_weight = $unit_weight;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight): void
    {

        $this->weight = self::convertToKg($weight, $this->getUnitWeight());
    }

    /**
     * @return mixed
     */
    public function getUnitDemensions()
    {
        return $this->unit_demensions;
    }

    /**
     * @param mixed $unit_demensions
     */
    public function setUnitDemensions($unit_demensions): void
    {
        $this->unit_demensions = $unit_demensions;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param mixed $length
     */
    public function setLength($length): void
    {
        $this->length = self::convertToM($length, $this->unit_demensions);;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width): void
    {
        $this->width = self::convertToM($width, $this->unit_demensions);
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height): void
    {
        $this->height = self::convertToM($height, $this->unit_demensions);
    }

    /**
     * @return mixed
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param mixed $packages
     */
    public function setPackages($packages): void
    {
        $this->packages = $packages;
    }


    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }


    /**
     * @param mixed $price
     */
    public function getPrice()
    {
       return $this->price;
    }
    /**
     * @return mixed
     */
    public function isTaxIncluded()
    {
        return $this->isTaxIncluded;
    }

    /**
     * @param mixed $isTaxIncluded
     */
    public function setIsTaxIncluded($isTaxIncluded): void
    {
        $this->isTaxIncluded = $isTaxIncluded;
    }

    /**
     * @return mixed
     */
    public function getTaxValue()
    {
        return $this->taxValue;
    }

    /**
     * @param mixed $taxValue
     */
    public function setTaxValue($taxValue): void
    {
        $this->taxValue = $taxValue;
    }

    //TODO Добавить все 11 видов упаковок. На каждую по функцию.



}