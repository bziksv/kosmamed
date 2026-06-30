<?
namespace R52\Event;
use Bitrix\Main\EventManager;

class EventHandler
{
    function eventAddQrCode(&$arFields, $arTemplate)
    {
        if($arTemplate['EVENT_NAME'] == 'SALE_NEW_ORDER') {

            if(file_exists($_SERVER["DOCUMENT_ROOT"].'/upload/qrcode/'.$arFields['ORDER_ID'].'.png')) {
                $arFields['QRCODE'] = '<img src="https://'.$_SERVER['HTTP_HOST'].'/upload/qrcode/'.$arFields['ORDER_ID'].'.png" />';
            }

        }

    }
}





