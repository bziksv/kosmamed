<?php
namespace Ipolh\SDEK;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\Validator\Length;

class StoresTable extends DataManager
{
    // Common table helpers
    use TableHelpers;

    /**
     * Field name for activity flag field. Default is 'SYNC_IS_ACTIVE'. Override possible in table class.
     * @var string
     */
    protected static $isActiveFieldName = 'IS_ACTIVE';

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ipol_sdek_stores';
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
                ]
            ),
            new BooleanField(
                'IS_ACTIVE',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                ]
            ),
            new StringField(
                'NAME',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateName'],
                ]
            ),
            new BooleanField(
                'IS_SENDER_DATA_SENT',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                ]
            ),
            new StringField(
                'SENDER_COMPANY',
                [
                    'validation' => [__CLASS__, 'validateSenderCompany'],
                ]
            ),
            new StringField(
                'SENDER_NAME',
                [
                    'validation' => [__CLASS__, 'validateSenderName'],
                ]
            ),
            new StringField(
                'SENDER_PHONE_NUMBER',
                [
                    'validation' => [__CLASS__, 'validateSenderPhoneNumber'],
                ]
            ),
            new StringField(
                'SENDER_PHONE_ADDITIONAL',
                [
                    'validation' => [__CLASS__, 'validateSenderPhoneAdditional'],
                ]
            ),
            new BooleanField(
                'NEED_CALL',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new BooleanField(
                'POWER_OF_ATTORNEY',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new BooleanField(
                'IDENTITY_CARD',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new BooleanField(
                'IS_SELLER_DATA_SENT',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                ]
            ),
            new StringField(
                'SELLER_NAME',
                [
                    'validation' => [__CLASS__, 'validateSellerName'],
                ]
            ),
            new StringField(
                'SELLER_PHONE',
                [
                    'validation' => [__CLASS__, 'validateSellerPhone'],
                ]
            ),
            new StringField(
                'SELLER_ADDRESS',
                [
                    'validation' => [__CLASS__, 'validateSellerAddress'],
                ]
            ),
            new BooleanField(
                'IS_DEFAULT_FOR_LOCATION',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new BooleanField(
                'IS_ADDRESS_DATA_SENT',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                ]
            ),
            new IntegerField(
                'FROM_LOCATION_CODE',
                [
                    'required' => true,
                ]
            ),
            new StringField(
                'FROM_LOCATION_STREET',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateFromLocationStreet'],
                ]
            ),
            new StringField(
                'FROM_LOCATION_HOUSE',
                [
                    'validation' => [__CLASS__, 'validateFromLocationHouse'],
                ]
            ),
            new StringField(
                'FROM_LOCATION_FLAT',
                [
                    'validation' => [__CLASS__, 'validateFromLocationFlat'],
                ]
            ),
            new StringField(
                'COMMENT',
                [
                    'validation' => [__CLASS__, 'validateComment'],
                ]
            ),
            new StringField(
                'INTAKE_TIME_FROM',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateIntakeTimeFrom'],
                ]
            ),
            new StringField(
                'INTAKE_TIME_TO',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateIntakeTimeTo'],
                ]
            ),
            new StringField(
                'LUNCH_TIME_FROM',
                [
                    'validation' => [__CLASS__, 'validateLunchTimeFrom'],
                ]
            ),
            new StringField(
                'LUNCH_TIME_TO',
                [
                    'validation' => [__CLASS__, 'validateLunchTimeTo'],
                ]
            ),
        ];
    }

    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for SENDER_COMPANY field.
     *
     * @return array
     */
    public static function validateSenderCompany()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for SENDER_NAME field.
     *
     * @return array
     */
    public static function validateSenderName()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for SENDER_PHONE_NUMBER field.
     *
     * @return array
     */
    public static function validateSenderPhoneNumber()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for SENDER_PHONE_ADDITIONAL field.
     *
     * @return array
     */
    public static function validateSenderPhoneAdditional()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for SELLER_NAME field.
     *
     * @return array
     */
    public static function validateSellerName()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for SELLER_PHONE field.
     *
     * @return array
     */
    public static function validateSellerPhone()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for SELLER_ADDRESS field.
     *
     * @return array
     */
    public static function validateSellerAddress()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for FROM_LOCATION_STREET field.
     *
     * @return array
     */
    public static function validateFromLocationStreet()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for FROM_LOCATION_HOUSE field.
     *
     * @return array
     */
    public static function validateFromLocationHouse()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for FROM_LOCATION_FLAT field.
     *
     * @return array
     */
    public static function validateFromLocationFlat()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for COMMENT field.
     *
     * @return array
     */
    public static function validateComment()
    {
        return [
            new Length(null, 255),
        ];
    }

    /**
     * Returns validators for INTAKE_TIME_FROM field.
     *
     * @return array
     */
    public static function validateIntakeTimeFrom()
    {
        return [
            new Length(null, 10),
        ];
    }

    /**
     * Returns validators for INTAKE_TIME_TO field.
     *
     * @return array
     */
    public static function validateIntakeTimeTo()
    {
        return [
            new Length(null, 10),
        ];
    }

    /**
     * Returns validators for LUNCH_TIME_FROM field.
     *
     * @return array
     */
    public static function validateLunchTimeFrom()
    {
        return [
            new Length(null, 10),
        ];
    }

    /**
     * Returns validators for LUNCH_TIME_TO field.
     *
     * @return array
     */
    public static function validateLunchTimeTo()
    {
        return [
            new Length(null, 10),
        ];
    }
}