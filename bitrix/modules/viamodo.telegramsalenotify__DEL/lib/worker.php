<?php

namespace Viamodo\Telegramsalenotify;

use Bitrix\Sale\Order;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;

class Worker
{

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->request = new Request();
        $this->request->setCommand('sendMessage');
    }

    public function setAction(string $actionName): Worker
    {
        $this->actionName = $actionName;
        return $this;
    }

    public function exec(): void
    {
        if (!Loader::includeModule('sale')) {
            return;
        }

        switch ($this->actionName) {
            case 'newOrder':
                $this->newOrder();
                break;
            case 'updateOrder':
                $this->updateOrder();
                break;
            case 'updateOrderPaid':
                $this->updateOrderPaid();
                break;
        }
    }

    private function newOrder(): void
    {
        $message = Loc::getMessage('VIAMODO_TSN_MESSAGE_NEW_ORDER');
        $this->sendMessage($message);
    }

    private function updateOrder(): void
    {
        $message = Loc::getMessage('VIAMODO_TSN_MESSAGE_UPDATE_ORDER');
        $this->sendMessage($message);
    }

    private function updateOrderPaid(): void
    {
        $message = Loc::getMessage('VIAMODO_TSN_MESSAGE_ORDER_PAID');
        $this->sendMessage($message);
    }

    private function sendMessage(string $message): void
    {
        $request = Application::getInstance()->getContext()->getRequest();

        $sAccountNumber = $this->order->getField('ACCOUNT_NUMBER');

        $sOrderLink = ($request->isHttps() ? 'https' : 'http') . '://' . $request->getHttpHost(
            ) . '/bitrix/admin/sale_order_view.php?ID=' . $this->order->getId();
        $sOrderLink = '<a href="' . $sOrderLink . '">#' . $this->order->getId() . '</a>';
        $sOrderPrice = SaleFormatCurrency(
            $this->order->getPrice(),
            $this->order->getCurrency()
        );

        $text = str_replace(
            ['#ORDER_NUMBER#', '#ORDER_LINK#', '#ORDER_PRICE#'],
            [$sAccountNumber, $sOrderLink, $sOrderPrice],
            $message
        );

        $this->mutatorText($text);

        $params = [
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        $this->sendMessageToAllUsers($params);
    }

    private function sendMessageToAllUsers(array $params): void
    {
        $currentChats = Option::get('viamodo.telegramsalenotify', 'chat_ids', '');
        $arCurrentChats = explode(',', $currentChats);

        if (empty($arCurrentChats)) {
            return;
        }

        foreach ($arCurrentChats as $chatId) {
            $params['chat_id'] = $chatId;
            $responce = $this->request->setData($params)->exec();
            unset($params['chat_id']);
        }
    }

    private function mutatorText(&$text)
    {
        $text = str_replace('\n', chr(10), $text);
        $text = str_replace('&nbsp;', '', $text);
        $text = strip_tags($text, '<b>');
        if (SITE_CHARSET != 'UTF-8') {
            $text = Encoding::convertEncoding($text, 'windows-1251', 'UTF-8');
        }
    }

}
