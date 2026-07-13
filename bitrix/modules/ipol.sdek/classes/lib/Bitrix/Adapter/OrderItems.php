<?php

namespace Ipolh\SDEK\Bitrix\Adapter;


use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Core\Order\Item;
use Ipolh\SDEK\Core\Order\ItemCollection;

class OrderItems
{
    protected $coreItems;
    protected $options;

    public function __construct(Options $options)
    {
        $this->options   = $options;
        $this->coreItems = new ItemCollection();
        return $this;
    }

    public function fromOrder($bitrixId)
    {
        /*
        $arGoods = GoodsPicker::fromOrder($bitrixId);

        $articul = $this->options->fetchArticul();
        //$barcode = $this->options->fetchBarcode();
        $nameFromProp = trim($this->options->fetchNameFromProp());

        GoodsPicker::addBasketGoodProperties($arGoods,array($articul, $nameFromProp,$barcode));
        GoodsPicker::addGoodsQRs($arGoods,$bitrixId);

        $defGabarites = new DefaultGabarites();

        foreach($arGoods as $arGood)
        {
            $arDimensions = array(
                'WEIGHT' => ($defGabarites->getMode() == 'G' && !floatval($arGood['WEIGHT'])) ? $defGabarites->getWeight() : $arGood['WEIGHT'],
                'HEIGHT' => ($defGabarites->getMode() == 'G' && !floatval($arGood['HEIGHT'])) ? $defGabarites->getWeight() : $arGood['HEIGHT'],
                'WIDTH'  => ($defGabarites->getMode() == 'G' && !floatval($arGood['WIDTH']))  ? $defGabarites->getWeight() : $arGood['WIDTH'],
                'LENGTH' => ($defGabarites->getMode() == 'G' && !floatval($arGood['LENGTH'])) ? $defGabarites->getWeight() : $arGood['LENGTH']
            );
            $obItem = new Item();
            $obItem->setName($arGood['NAME'])
                ->setQuantity($arGood['QUANTITY'])
                ->setId($arGood['PRODUCT_ID'])
                ->setWeight($arDimensions['WEIGHT'])
                ->setHeight($arDimensions['HEIGHT'])
                ->setWidth($arDimensions['WIDTH'])
                ->setLength($arDimensions['LENGTH'])
                ->setField('IsDangerous',false);

            // Some VAT magic
            $vatRate = intval($arGood['VAT_RATE'] * 100);
            if ($vatRate > 0 && $arGood['VAT_INCLUDED'] !== 'Y')
            {
                // VAT not included in good's price, add it, cause OZON API does not know this BX differences
                $realPrice   = new Money($arGood['PRICE']);
                $realVat     = Money::multiply($realPrice, floatval($arGood['VAT_RATE']));
                $resultPrice = Money::sum($realPrice, $realVat);

                $obItem->setPrice($resultPrice->getAmount())->setCost($resultPrice->getAmount());
            }
            else
            {
                $obItem->setPrice($arGood['PRICE'])->setCost($arGood['PRICE']);
            }
            $obItem->setVatRate($vatRate);

            if($articul){
                $obItem->setArticul($arGood['PROPERTIES'][$articul]);
            }

            if ($this->options->fetchUseIdAsArticul() == 'Y' && empty($obItem->getArticul()))
            {
                $obItem->setArticul($obItem->getId());
            }

            // Use prop value instead of item's name if exists
            if ($nameFromProp) {
                if (!empty($arGood['PROPERTIES'][$nameFromProp]))
                    $obItem->setName($arGood['PROPERTIES'][$nameFromProp]);
            }

            /*
            if($barcode){
                $obItem->setBarcode($arGood['PROPERTIES'][$barcode]);
            }

            if($arGood['QR']){
                $obItem->setProperty('QR',$arGood['QR']);
            }

            $this->getCoreItems()->add($obItem);
        }
        */

        return $this;
    }

    public function fromArray($arItems)
    {
        foreach ($arItems as $item) {
            $obItem = new Item();
            $obItem->setName($item['name'])
                ->setPrice($item['price'])
                ->setCost($item['cost'])
                ->setQuantity($item['quantity'])
                ->setId($item['id'])
                ->setWeight($item['weight'])
                ->setHeight($item['height'])
                ->setWidth($item['width'])
                ->setLength($item['length'])
                ->setVatRate($item['vatRate'])
                ->setArticul($item['articul'])
                ->setBarcode($item['barcode']);

            foreach(array('IsDangerous'/*'oc','ccd','tnved'*/) as $property){
                switch ($property){
                    case 'IsDangerous' : $item[$property] = ($item[$property] === true || $item[$property] === 'true') ? true : false; break; // fk types
                }
                $obItem->setField($property,$item[$property]);
            }

            $this->getCoreItems()->add($obItem);
        }

        return $this;
    }

    /**
     * @return ItemCollection
     */
    public function getCoreItems()
    {
        return $this->coreItems;
    }

    /**
     * @param mixed $coreItems
     * @return $this
     */
    public function setCoreItems($coreItems)
    {
        $this->coreItems = $coreItems;

        return $this;
    }
}