<?php

    namespace Ipol\Robokassa\Controller;

    if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

    use Bitrix\Main;
    use Bitrix\Main\Localization\Loc;
    use Ipol\Robokassa;

    Loc::loadMessages(__FILE__);

    final class RobokassaStartElementAjaxController extends Main\Engine\Controller
    {


        public function __construct(Main\Request $request = null)
        {

            parent::__construct($request);

            try
            {
                Main\Loader::includeModule('ipol.robokassa');
            }
            catch (Main\LoaderException $e)
            {
                $this->errorCollection[] = new Main\Error($e->getMessage());
                return;
            }
        }

        public function configureActions()
        {
            return [
                'add2basket' => [
                    'prefilters' => [
                    ],
                    '-prefilters' => [
                        Main\Engine\ActionFilter\Authentication::class
                    ],
                ],
                'deleteBasket' => [
                    'prefilters' => [
                    ],
                    '-prefilters' => [
                        Main\Engine\ActionFilter\Authentication::class
                    ],
                ],
                'createOrder' => [
                    'prefilters' => [
                    ],
                    '-prefilters' => [
                        Main\Engine\ActionFilter\Authentication::class
                    ],
                ],
                'basketList' => [
                    'prefilters' => [
                    ],
                    '-prefilters' => [
                        Main\Engine\ActionFilter\Authentication::class
                    ],
                ],
                'subProductBasketQuantity' => [
                    'prefilters' => [
                    ],
                    '-prefilters' => [
                        Main\Engine\ActionFilter\Authentication::class
                    ],
                ],
                'addProductBasketQuantity' => [
                    'prefilters' => [
                    ],
                    '-prefilters' => [
                        Main\Engine\ActionFilter\Authentication::class
                    ],
                ],
            ];
        }

        /**
         * Добавление товара в корзину
         * @return array|null
         */
        public function add2basketAction()
        {

            Robokassa\Start\Basket::getInstance()->add(
                (int) $this->request->getPost('productId'),
                (int) $this->request->getPost('quantity')
            );

            return [];
        }

        /**
         * Удаление товара из корзины
         * @return array|null
         */
        public function deleteBasketAction()
        {
            Robokassa\Start\Basket::getInstance()->remove((int) $this->request->getPost('productId'));
            return [];
        }

        /**
         * Увеличение количества товара в корзине
         * @return array|null
         */
        public function addProductBasketQuantityAction()
        {
            Robokassa\Start\Basket::getInstance()->updateQuantity(
                (int) $this->request->getPost('productId'),
                1
            );
            return [];
        }

        /**
         * Уменьшение количества товара в корзине
         * @return array|null
         */
        public function subProductBasketQuantityAction()
        {
            Robokassa\Start\Basket::getInstance()->updateQuantity(
                (int) $this->request->getPost('productId'),
                -1
            );
            return [];
        }

        /**
         * Получение корзины
         * @return array
         */
        public function basketListAction()
        {

            $basket = Robokassa\Start\Basket::getInstance()->list();
            $basketPrice = 0;

            foreach($basket as $key => $item)
            {
                $basketPrice += (float) $item['price'] * (float) $item['quantity'];

                $basket[$key] = array_merge(
                    $item,
                    [
                        'printPrice' => Robokassa\Start\Product::formatPrice($item['price']),
                        'sum' => (float) $item['price'] * (float) $item['quantity'],
                        'printSum' => Robokassa\Start\Product::formatPrice((float) $item['price'] * (float) $item['quantity']),
                    ]
                );
            }

            return [
                'basket' => $basket,
                'basketPrice' => Robokassa\Start\Product::formatPrice($basketPrice),
            ];
        }

        /**
         * Оформление заказа
         * @return array|void
         */
        public function createOrderAction()
        {

            $fields = [
                'NAME' => strip_tags($this->request->getPost('buyerName')),
                'PHONE' => strip_tags($this->request->getPost('buyerPhoneNumber')),
                'EMAIL' => strip_tags($this->request->getPost('buyerEmail'))
            ];

            if(!check_bitrix_sessid($this->request->getPost('session_id')))
            {
                $this->errorCollection[] = new Main\Error(
                    Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.ORDER.BAD_SESSION')
                );
            }

            if(!check_email($fields['EMAIL']))
            {
                $this->errorCollection[] = new Main\Error(
                    Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.ORDER.BAD_EMAIL')
                );
            }

            if(strlen($fields['PHONE']) !== 16)
            {
                $this->errorCollection[] = new Main\Error(
                    Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.ORDER.BAD_PHONE')
                );
            }

            if(strlen($fields['NAME']) < 3)
            {
                $this->errorCollection[] = new Main\Error(
                    Loc::getMessage('IPOL_ROBOKASSA.START_ELEMENT.ORDER.BAD_NAME')
                );
            }

            if($this->errorCollection->isEmpty())
            {
                try
                {

                    $order = Robokassa\Start\Order::createOrder($fields);

                    return [
                        'ID' => $order['ID'],
                        'ORDER_ID' => $order['ORDER_ID'],
                        'MESSAGE' => Loc::getMessage(
                            'POL_ROBOKASSA.START_ELEMENT.ORDER.SUCCESS',
                            [
                                '#ORDER_ID#' => $order['ORDER_ID'],
                            ]
                        ),
                        'URL' => '/bitrix/tools/start_robokassa_payment.php?ORDER_ID=' . $order['ORDER_ID'],
                    ];
                }
                catch (\Exception $e)
                {
                    $this->errorCollection[] = new Main\Error(
                        $e->getMessage()
                    );
                }
            }
        }
    }