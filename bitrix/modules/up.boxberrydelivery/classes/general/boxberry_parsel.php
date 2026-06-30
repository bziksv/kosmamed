<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;

Loader::includeModule('sale');
Loc::loadMessages(__FILE__);

class CBoxberryParsel
{
    private const EAEU_COURIER_DEFAULT_INDEX = '151';
    public static $moduleId = 'up.boxberrydelivery';

    public function parselCreate($orderId)
    {
        if (!CBoxberry::initApi()) {
            return array('ERROR' => Loc::getMessage('WRONG_API_CONNECT'));
        }

        if ((int)$orderId <= 0) {
            return array('ERROR' => 'Invalid argument.');
        }

        if (!$order = CBoxberry::GetFullOrderData($orderId)) {
            return array('ERROR' => 'Order not found.');
        }

        if (!$arProps = CBoxberry::MakePropsArray($order)) {
            return array('ERROR' => 'Wrong module settings.');
        }

        $orderNumber = (Option::get(self::$moduleId, 'BB_ACCOUNT_NUMBER') === 'Y' ? $order['ACCOUNT_NUMBER'] : $order['ID']);
        $location = $arProps['BB_LOCATION'];
        $arLocation = CSaleLocation::GetByID($location, 'ru');
        $cityName = $arLocation['CITY_NAME'];
        $regionName = $arLocation['REGION_NAME'];
        $package = CDeliveryBoxberry::getFullDimensions($order['ITEMS']);
        $city = CDeliveryBoxberry::getCity($arLocation['CODE']);
        $address = $cityName . ' ' . $arProps['BB_ADDRESS'];
        $receptionPoint = CBoxberry::getReceptionPointCode();

        $sdata = array(
            'source_platform' => 'bitrix',
            'order_id' => $orderNumber,
            'price' => $order['PRICE'],
            'payment_sum' => (string)CBoxberry::getPaymentSum($order['PRICE'], $order['PAYED'], $order['DELIVERY_ID']),
            'delivery_sum' => $order['PRICE_DELIVERY'],
            'vid' => $arProps['VID'],
        );

        if ($arProps['VID'] === CDeliveryBoxberry::COURIER_DELIVERY_TYPE_ID && $this->isExport($city)) {
            if ($export = $this->makeExportArray($city, $address)) {
                $sdata['export'] = $export;
            } else {
                return ['ERROR' => Loc::getMessage('BB_EXPORT_ADDRESS_SEARCH_ERROR')];
            }
        }

        $sdata['shop'] = array(
            'name' => ($order['PVZ_CODE'] ?: ''),
            'name1' => ''
        );

        if (!empty($receptionPoint)) {
            $sdata['shop']['name1'] = $receptionPoint;
        }

        $bxbOptions['bb_paid_person_jur'] = Option::get(self::$moduleId, 'BB_PAID_PERSON_JUR');
        $bxbOptions['bb_paid_person_jur'] = (!empty($bxbOptions['bb_paid_person_jur']) ? $bxbOptions['bb_paid_person_jur'] : 2);

        if ($order['PERSON_TYPE_ID'] === $bxbOptions['bb_paid_person_jur']) {
            $sdata['customer'] = array(
                'fio' => $arProps['BB_CONTACT_PERSON'],
                'phone' => $arProps['BB_PHONE'],
                'email' => $arProps['BB_EMAIL'],
                'name' => $arProps['BB_COMPANY_NAME'],
                'address' => $arProps['BB_JUR_ADDRESS'],
                'inn' => $arProps['BB_INN'],
                'kpp' => $arProps['BB_KPP']
            );
        } else {
            $sdata['customer'] = array(
                'fio' => $arProps['BB_FIO'],
                'phone' => $arProps['BB_PHONE'],
                'email' => $arProps['BB_EMAIL']
            );
        }

        $sdata['weights'] = array(
            'weight' => ($package['WEIGHT'] < 5 ? 5 : ceil($package['WEIGHT'])),
            'x' => ((is_null($package['HEIGHT']) || $package['HEIGHT'] < 1) ? '' : $package['HEIGHT']),
            'y' => ((is_null($package['WIDTH']) || $package['WIDTH'] < 1) ? '' : $package['WIDTH']),
            'z' => ((is_null($package['LENGTH']) || $package['LENGTH'] < 1) ? '' : $package['LENGTH'])
        );

        if ($arProps['VID'] === 2) {
            $sdata['kurdost'] = array(
                'index' => '',
                'citi' => $cityName . ' ' . $regionName,
                'addressp' => $arProps['BB_ADDRESS'],
                'timep' => '09:00 - 20:00'
            );
        }

        $sdata['items'] = array();
        foreach ($order['ITEMS'] as $item) {
            $sdata['items'][] =
                array(
                    'id' => $item['PRODUCT_ID'],
                    'name' => $item['NAME'],
                    'UnitName' => (!empty($item['MEASURE_NAME']) ? $item['MEASURE_NAME'] : '?'),
                    'nds' => !is_null($item['VAT_RATE']) ? (int)($item['VAT_RATE'] * 100) : (CBoxberry::isSourceBoxberry() ? '' : 0),
                    'price' => $item['PRICE'],
                    'quantity' => ($item['MEASURE_CODE'] !== '796' ? 1 : ceil($item['QUANTITY']))
                );
        }

        $data = CBoxberry::parselCreate($sdata);

        if (empty($data) || isset($data['err']) || !isset($data['track'])) {
            return array('ERROR' => $data['err']);
        }

        $track = $data['track'];
        $label = '';
        $act = '';

        $dbPdfLink = '';

        if (isset($data['label'])) {
            $label = $data['label'];
            $dbPdfLink = $label;
        } else {
            $labelRetry = CBoxberry::retryGetLabel($track);
            if (isset($labelRetry['label'])) {
                $label = $labelRetry['label'];
                $dbPdfLink = $label;
            }
        }

        if (Option::get(self::$moduleId, 'BB_PARSELSEND') === 'Y') {
            $parselSend = CBoxberry::parselSend($track);
            if (isset($parselSend['label'])) {
                $act = $parselSend['label'];
                $dbPdfLink .= ' ' . $act;
            }
        }

        if (Option::get(self::$moduleId, 'BB_STORE_STICKERS_LOCALLY') === 'Y') {
            $labelLocalUrl = CBoxberry::saveFilesFromApi($label, $orderId);
            $dbPdfLink = $labelLocalUrl . (!empty($act) ? ' ' . $act : '');
        }

        $arFields = array(
            'ORDER_ID' => $orderId,
            'DATE_CHANGE' => date('d.m.Y H:i:s'),
            'LID' => $order['LID'],
            'TRACKING_CODE' => $data['track'],
            'PVZ_CODE' => $order['PVZ_CODE'],
            'STATUS' => '1',
            'STATUS_TEXT' => 'CREATED',
            'STATUS_DATE' => date('d.m.Y H:i:s'),
            'CHECK_REQUEST' => 'Y',
            'CHECK_REQUEST_DATE' => date('d.m.Y H:i:s'),
            'CHECK_PDF_LINK' => $dbPdfLink,
        );

        if (Option::get(self::$moduleId, 'BB_ADD_TRACK_NUMBER_INTO_SHIPMENT') === 'Y') {
            $order = Order::load($orderId);
            $shipmentCollection = $order->getShipmentCollection();
            foreach ($shipmentCollection as $shipment) {
                if (!$shipment->isSystem()) {
                    $shipment->setFields(array(
                        'TRACKING_NUMBER' => $data['track']
                    ));
                }
            }
            $order->save();
        }

        CBoxberryOrder::Update($orderId, $arFields);
        return $data;

    }

    private function isExport($city)
    {
        if (!$city) {
            return false;
        }

        if ($city['BB_COUNTRY_CODE'] === '643') {
            return false;
        }

        return true;
    }

    private function makeExportArray($city, $address)
    {
        $dadataLocation = CBoxberry::callApiDadata($address);

        if (isset($dadataLocation['suggestions'][0]['data'])) {
            $dadataLocation = $dadataLocation['suggestions'][0]['data'];
        } else {
            return [];
        }

        return [
            'index' => self::EAEU_COURIER_DEFAULT_INDEX,
            'countryCode' => $city['BB_COUNTRY_CODE'],
            'cityCode' => $city['BB_CITY_CODE'],
            'area' => $dadataLocation['region'],
            'street' => $dadataLocation['street'],
            'house' => $dadataLocation['house'],
            'flat' => $dadataLocation['flat'],
            'transporterGuid' => 'fd85a8b6-4688-404f-9993-30b9e55d2950',
        ];
    }
}
?>