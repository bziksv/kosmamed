<?php


namespace DellinShipping;

use Bitrix\Main\Localization\Loc;
class ExclusionList
{

    public static function listForRegionName(){

       //От коллектора иногда приходят пустые значения.
        return [
            Loc::getMessage("SAHA") =>Loc::getMessage("SAHA_NEEDED"),
            Loc::getMessage("osetia") =>Loc::getMessage("osetia_nedded"),
            Loc::getMessage("marii_el") =>Loc::getMessage("marii_el_nedded"),
        ];
    }

    public static function listForPlaces(){

    //метод предназначен для городов федерального значения, которые иногда ошибочно приписывают к областям,
    //например, "севастополь крым" - это ошибка, севастополь это город федерального значения.

        return array(
            Loc::getMessage("moscow") => array(
                'cityName' => Loc::getMessage("moscow"),
                'regionName' => Loc::getMessage("moscow")
            ),
            Loc::getMessage("sevastopol") => array(
                'cityName' => Loc::getMessage("sevastopol") ,
                'regionName' => Loc::getMessage("sevastopol")
            ),
            Loc::getMessage("SAINT-PETERSBURG") => array(
                'cityName' => Loc::getMessage("SAINT-PETERSBURG"),
                'regionName' => Loc::getMessage("SAINT-PETERSBURG")
            ),
        );
    }

    public static function listForQuery(){
        return [
          Loc::getMessage("DELLINDEV_Q_BELGOROD") => [
              'q'=> Loc::getMessage("DELLINDEV_Q_BELGOROD_CHANGED"),
              'cityName' => Loc::getMessage("DELLINDEV_CITYNAME_BELGOROD"),
              'regionName'=> Loc::getMessage("DELLINDEV_REGIONNAME_BELGORODSKAYA")
          ]
        ];
    }
}