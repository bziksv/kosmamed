<?php

namespace Ipolh\SDEK\SDEK;


class Tools
{
    public static function getTrackLink($trackNumber='')
    {
        return 'https://cdek.ru/tracking?order_id='.$trackNumber;
    }
}