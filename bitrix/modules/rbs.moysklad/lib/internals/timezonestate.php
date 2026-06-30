<?php

namespace Rbs\Moysklad\Internals;

use Rbs\Moysklad\Config;

class TimezoneState
{
    public static function setTimeZone()
    {
        $timezone = Config::getOption('global_timezone', 'N');
        if ($timezone === 'N') {
            return;
        }

        date_default_timezone_set($timezone);
    }   
}