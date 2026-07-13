<?php

namespace Viamodo\Telegramsalenotify;

use Bitrix\Main\Config\Option;

class Util
{

    public static function getLastUpdates(): array
    {
        $option = [];
        $params = [];

        $lastUpdate = Option::get('viamodo.telegramsalenotify', 'lastupdate', '');
        if (!empty($lastUpdate)) {
            $params['offset'] = $lastUpdate;
        }

        $request = new Request($option);

        $response = $request->setCommand('getUpdates')
            ->setData($params)
            ->exec();

        if (!empty($response['result']) && is_array($response['result'])) {
            $lastUpdateNew = end($response['result'])['update_id'];
            if (!empty($lastUpdateNew)) {
                Option::set('viamodo.telegramsalenotify', 'lastupdate', $lastUpdateNew);
            }
        }

        return $response;
    }

}
