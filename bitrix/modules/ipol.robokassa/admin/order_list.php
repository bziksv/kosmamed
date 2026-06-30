<?php

    use Bitrix\Main\Localization\Loc;
    use Ipol\Robokassa;

    require_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_admin_before.php';

    \Bitrix\Main\Loader::includeModule('ipol.robokassa');
    \Bitrix\Main\Loader::includeModule('iblock');

    global $USER, $APPLICATION;

    if (!$USER->isAdmin())
        $APPLICATION->authForm('Nope');

    \Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

    $tableId = 'tbl_ipol_robokassa_order_list';

    $listOrderTable = new \CAdminList(
        $tableId,
        new CAdminSorting($tableId, "ID", "desc")
    );

    $listOrderTable->AddHeaders(
        [
            [
                'id' => 'ID',
                "content" => Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.ID'),
                "default" => true
            ],
            [
                'id' => 'CREATED_AT',
                "content" => Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.CREATED_AT'),
                "default" => true
            ],
            [
                'id' => 'EMAIL',
                "content" => Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.EMAIL'),
                "default" => true
            ],
            [
                'id' => 'NAME',
                "content" => Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.NAME'),
                "default" => true
            ],
            [
                'id' => 'PHONE',
                "content" => Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.PHONE'),
                "default" => true
            ],
            [
                'id' => 'BASKET',
                "content" => Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.BASKET'),
                "default" => true
            ],
            [
                'id' => 'PRICE',
                "content" => Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.PRICE'),
                "default" => true
            ],
            [
                'id' => 'PAYED',
                "content" => Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.PAYED'),
                "default" => true
            ],
        ]
    );

    $iblock = CIBlock::GetByID(
        \Bitrix\Main\Config\Option::get('ipol.robokassa', 'START_FUNCTION_IBLOCK_ID', 0)
    )->GetNext(true, false);

    $productBaseUrl = '/bitrix/admin/iblock_element_admin.php?'
        . \http_build_query(
            [
                'IBLOCK_ID' => $iblock['ID'],
                'type' => $iblock['IBLOCK_TYPE_ID'],
            ]
        )
    ;

    $orders = new CAdminResult(
        Robokassa\Internals\OrderTable::GetList(
            [
                'filter' => [],
                'order' => [
                    'ID' => 'DESC',
                ],
                'limit' => CAdminResult::GetNavSize($tableId),
            ]
        ),
        $tableId
    );

    $orders->NavStart(CAdminResult::GetNavSize($tableId));

    $listOrderTable->NavText($orders->GetNavPrint(GetMessage("IPOL_ROBOKASSA_ORDER_LIST_PAGE_PAGE_NAME")));

    while ($order = $orders->NavNext(true, false))
    {

        $basket = Robokassa\Internals\BasketItemTable::getList(
            [
                'filter' => [
                    'ORDER_ID' => $order['ID'],
                ],
            ]
        )->fetchAll();

        $row = &$listOrderTable->AddRow($order['ID'], $order);

        $row->AddViewField('PRICE', Robokassa\Start\Product::formatPrice($order['PRICE']));

        $row->AddViewField(
            'PAYED',
            $order['PAYED'] === 'Y'
            ? Loc::getMessage(
                'IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.PAYED_Y',
                [
                    '#DATE#' => $order['PAYED_DATE']->format('d.m.Y H:i'),
                ]
            )
            : Loc::getMessage('IPOL_ROBOKASSA_ORDER_LIST.LIST.HEADER.PAYED_N')
        );

        if(!empty($order['EMAIL']) && \check_email($order['EMAIL']))
        {
            $row->AddViewField(
                'EMAIL',
                '<a href="mailto:' . $order['EMAIL'] . '">' . $order['EMAIL'] . '</a>'
            );
        }

        if(!empty($order['PHONE']))
        {
            $row->AddViewField(
                'PHONE',
                '<a href="tel:' . $order['PHONE'] . '">' . $order['PHONE'] . '</a>'
            );
        }

        $row->AddViewField(
            'BASKET',
            \implode(
                '<hr>',
                \array_map(
                    static function(array $basket) use ($productBaseUrl, $iblock)
                    {

                        $productUrl = $productBaseUrl;

                        if(!empty(\CIBlockElement::GetByID($basket['PRODUCT_ID'])))
                        {
                            $productUrl = '/bitrix/admin/iblock_element_edit.php? '
                                . \http_build_query(
                                    [
                                        'IBLOCK_ID' => $iblock['ID'],
                                        'type' => $iblock['IBLOCK_TYPE_ID'],
                                        'ID' => $basket['PRODUCT_ID'],
                                    ]
                                )
                            ;
                        }

                        return Loc::getMessage(
                            'IPOL_ROBOKASSA_ORDER_LIST.LIST.BODY.BASKET_ITEM',
                            [
                                '#PRODUCT_ID#' => $basket['PRODUCT_ID'],
                                '#PRODUCT_NAME#' => $basket['PRODUCT_NAME'],
                                '#QUANTITY#' => $basket['QUANTITY'],
                                '#PRICE#' => Robokassa\Start\Product::formatPrice($basket['PRICE']),
                                '#URL#' => $productUrl,
                            ]
                        );
                    },
                    $basket
                )
            )
        );
    }

    $listOrderTable->CheckListMode();

    $APPLICATION->SetTitle(Loc::getMessage("IPOL_ROBOKASSA_ORDER_LIST_PAGE_TITLE"));

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

    $listOrderTable->DisplayList();

    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
