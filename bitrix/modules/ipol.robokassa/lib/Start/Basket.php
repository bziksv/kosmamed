<?php

    namespace Ipol\Robokassa\Start;

    use Ipol\Robokassa;
    use Bitrix\Main\Config\Option;
    use Bitrix\Main\HttpApplication;
    use Bitrix\Main\Loader;

    /**
     * Class Basket
     * @package Ipol\Robokassa\Start
     */
    final class Basket
    {

        public const SESSION_ID = 'ROBOKASSA_BASKET';

        private static self|null $instance = null;

        private array $basket = [];

        private \Bitrix\Main\Session\SessionInterface $session;

        protected function __construct()
        {
            $this->session = HttpApplication::getInstance()->getSession();

            if(
                $this->session->has(self::SESSION_ID)
                && \is_array($this->session->get(self::SESSION_ID))
            )
            {
                $this->basket = $this->session->get(self::SESSION_ID);
            }

            if(!empty($this->basket))
            {
                $this->updateBasketPrices();
            }
        }

        public static function getInstance(): self
        {
            if (!isset(self::$instance))
            {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Обновление цен товаров в корзине
         *
         * @param int|null $productId
         * @return void
         *
         * @throws \Bitrix\Main\LoaderException
         */
        public function updateBasketPrices(null|int $productId = null): void
        {

            Loader::includeModule('iblock');

            if(empty($this->basket) && $productId === null)
            {
                return;
            }

            $filter = [
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'SECTION_ACTIVE' => 'Y',
                'SECTION_GLOBAL_ACTIVE' => 'Y',
                'IBLOCK_ID' => (int) Option::get(
                    Robokassa\RobokassaPaymentService::$moduleId,
                    'START_FUNCTION_IBLOCK_ID',
                    0
                ),
                'PROPERTY_' . Product::IBLOCK_QUANTITY_PROPERTY_CODE,
                'PROPERTY_' . Product::IBLOCK_PRICE_PROPERTY_CODE,
                '=ID' => $productId ?? \array_keys($this->basket),
            ];

            $select = [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'PREVIEW_PICTURE',
                'DETAIL_PICTURE',
                'PROPERTY_' . Product::IBLOCK_QUANTITY_PROPERTY_CODE,
                'PROPERTY_' . Product::IBLOCK_PRICE_PROPERTY_CODE
            ];

            $updated = [];

            $elements = \CIBlockElement::GetList([], $filter, false, false, $select);
            while($element = $elements->GetNext(true, false))
            {

                $updated[] = $element['ID'];

                $pictureId = empty($element['DETAIL_PICTURE']) ? $element['PREVIEW_PICTURE'] : $element['DETAIL_PICTURE'];

                $this->basket[$element['ID']] = [
                    'productId' => (int) $element['ID'],
                    'name' => $element['NAME'],
                    'quantity' => \min(
                        $this->basket[$element['ID']]['quantity'],
                        (int) $element['PROPERTY_' . Product::IBLOCK_QUANTITY_PROPERTY_CODE . '_VALUE']
                    ),
                    'price' => (float) $element['PROPERTY_' . Product::IBLOCK_PRICE_PROPERTY_CODE . '_VALUE'],
                    'picture' => (int) $pictureId > 0
                        ? \CFile::ResizeImageGet(
                            $pictureId,
                            [
                                'width' => 300,
                                'height' => 300,
                            ]
                        )['src']
                        : '',
                ];
            }

            if($productId === null)
            {
                foreach($this->basket as $key => $product)
                {

                    if(!\in_array($key, $updated, false))
                    {
                        unset($this->basket[$key]);
                        continue;
                    }

                    if(
                        (int) ($this->basket[$key]['quantity'] ?? 0) === 0
                        || ($this->basket[$key]['price'] ?? 0.0) === 0.0
                    )
                    {
                        unset($this->basket[$key]);
                    }
                }
            }
            else
            {
                if(
                    (int) ($this->basket[$productId]['quantity'] ?? 0) === 0
                    || ($this->basket[$productId]['price'] ?? 0.0) === 0.0
                )
                {
                    unset($this->basket[$productId]);
                }
            }
        }

        /**
         * Добавление товара в корзину
         *
         * @param int $productId
         * @param int $quantity
         *
         * @return void
         */
        public function add(int $productId, int $quantity = 1): void
        {
            if(isset($this->basket[$productId]))
            {
                $this->basket[$productId]['quantity'] += $quantity;
            }
            else
            {
                $this->basket[$productId] = [
                    'productId' => $productId,
                    'name' => '',
                    'quantity' => $quantity,
                    'price' => 0
                ];
            }

            $this->updateBasketPrices();
            $this->session->set(self::SESSION_ID, $this->basket);
        }

        /**
         * Удаление товара из корзины
         *
         * @param $productId
         * @return void
         */
        public function remove($productId): void
        {
            if(isset($this->basket[$productId]))
            {
                unset($this->basket[$productId]);
                $this->session->set(self::SESSION_ID, $this->basket);
            }
        }

        /**
         * Очитка корзины
         *
         * @return void
         */
        public function clear(): void
        {
            $this->basket = [];
            $this->session->set(self::SESSION_ID, $this->basket);
        }

        /**
         * @return array|null
         */
        public function list(): ?array
        {
            $this->updateBasketPrices();
            return $this->basket;
        }

        /**
         * @param int $productId
         * @param int $quantity
         * @return void
         * @throws \Bitrix\Main\LoaderException
         */
        public function updateQuantity(int $productId, int $quantity = 1): void
        {
            if(isset($this->basket[$productId]))
            {
                if($quantity === 1)
                {
                    $this->basket[$productId]['quantity']++;
                }
                else
                {
                    $this->basket[$productId]['quantity']--;

                    if($this->basket[$productId]['quantity'] === 0)
                    {
                        $this->remove($productId);
                    }
                }

                $this->updateBasketPrices();
                $this->session->set(self::SESSION_ID, $this->basket);
            }
        }
    }