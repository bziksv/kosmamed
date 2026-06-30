<?php

namespace Rbs\MoyskladStocks\Internals\Enums;

use Rbs\MoyskladStocks\LangMsg;

class Timezones
{
    public static function getTimeZoneList() {
        return [
            'Europe/Kaliningrad' => LangMsg::get('TIMEZONES_KALININGRAD_DESC'),
            'Europe/Moscow' => LangMsg::get('TIMEZONES_MOSCOW_DESC'), 
            'Europe/Samara' => LangMsg::get('TIMEZONES_SAMARA_DESC'),
            'Asia/Yekaterinburg' => LangMsg::get('TIMEZONES_YEKATERINBURG_DESC'),
            'Asia/Omsk' => LangMsg::get('TIMEZONES_OMSK_DESC'),
            'Asia/Krasnoyarsk' => LangMsg::get('TIMEZONES_KRASNOYARSK_DESC'),
            'Asia/Irkutsk' => LangMsg::get('TIMEZONES_IRKUTSK_DESC'),
            'Asia/Yakutsk' => LangMsg::get('TIMEZONES_YAKUTSK_DESC'),
            'Asia/Vladivostok' => LangMsg::get('TIMEZONES_VLADIVOSTOK_DESC'),
            'Asia/Magadan' => LangMsg::get('TIMEZONES_MAGADAN_DESC'),
            'Asia/Kamchatka' => LangMsg::get('TIMEZONES_KAMCHATKA_DESC')
        ];
    }
}

