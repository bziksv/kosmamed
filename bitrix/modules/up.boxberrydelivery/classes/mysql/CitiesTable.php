<?php

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class CitiesTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> BB_CITY_CODE string(255) optional
 * <li> BB_COUNTRY_CODE string(255) optional
 * <li> BITRIX_CITY_CODE string(255) optional
 * </ul>
 *
 **/

class CitiesTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_boxberry_cities';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('CITIES_ENTITY_ID_FIELD')
                ]
            ),
            new StringField(
                'BB_CITY_CODE',
                [
                    'validation' => [__CLASS__, 'validateBbCityCode'],
                    'title' => Loc::getMessage('CITIES_ENTITY_BB_CITY_CODE_FIELD')
                ]
            ),
            new StringField(
                'BB_COUNTRY_CODE',
                [
                    'validation' => [__CLASS__, 'validateBbCountryCode'],
                    'title' => Loc::getMessage('CITIES_ENTITY_BB_COUNTRY_CODE_FIELD')
                ]
            ),
            new StringField(
                'BITRIX_CITY_CODE',
                [
                    'validation' => [__CLASS__, 'validateBitrixCityCode'],
                    'title' => Loc::getMessage('CITIES_ENTITY_BITRIX_CITY_CODE_FIELD')
                ]
            ),
        ];
    }

    /**
     * Returns validators for BB_CITY_CODE field.
     *
     * @return array
     */
    public static function validateBbCityCode()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for BB_COUNTRY_CODE field.
     *
     * @return array
     */
    public static function validateBbCountryCode()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for BITRIX_CITY_CODE field.
     *
     * @return array
     */
    public static function validateBitrixCityCode()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    public static function truncate(): void
    {
        $connection = Application::getConnection();
        $connection->truncateTable(self::getTableName());
    }
}