<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

class CBoxberryPsend
{
    public static $moduleId = "up.boxberrydelivery";

    public function parselSend($orderId)
    {
        if (!CBoxberry::initApi()) {
            return array("ERROR" => Loc::getMessage('WRONG_API_CONNECT'));
        }

        $arBbOrder = [];
        $track = [];
        $checkForAct = [];
        $statusText = [];
        $i = 0;
        foreach ($orderId as $id) {
            $arBbOrder[] = CBoxberryOrder::GetByOrderId($id);
            $track[] = $arBbOrder[$i]['TRACKING_CODE'];
            $checkForAct[] = $arBbOrder[$i]['CHECK_PDF_LINK'];
            $statusText[] = $arBbOrder[$i]['STATUS_TEXT'];
            $i++;
        }

        if (isset($track) && in_array('CREATED', $statusText, true) && in_array('NEW', $statusText, true) === false && strpos(implode($checkForAct), 'act') === false) {
            $parselSend = CBoxberry::retryGetSticker(implode(',', $track));

            if (isset($parselSend['err'])) {
                return ['ERROR' => $parselSend['err']];
            }

            if (Option::get(self::$moduleId, 'BB_STORE_STICKERS_LOCALLY') === 'Y') {
                $stickerUrl = CBoxberry::saveFilesFromApi($parselSend['sticker'], $id);
            } else {
                $stickerUrl = $parselSend['sticker'];
            }

            if (isset($stickerUrl, $parselSend['label'])) {
                $dbPdfLink = $stickerUrl . ' ' . $parselSend['label'];
            } elseif (isset($stickerUrl)) {
                $dbPdfLink = $stickerUrl;
            } else {
                $dbPdfLink = '';
            }

            $arFields = array(
                'CHECK_PDF_LINK' => $dbPdfLink,
            );

            if (isset($parselSend['label'])){
                foreach ($orderId as $id){
                    CBoxberryOrder::Update($id, $arFields);
                }
            }

            return $parselSend;
        }
    }
}

