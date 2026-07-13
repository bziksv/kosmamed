<?php
namespace Ipolh\SDEK;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\Entity\Validator\Length;

class CourierCallsTable extends DataManager
{
    // Common table helpers
    use TableHelpers;

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'ipol_sdek_courier_calls';
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
            new StringField(
                'INTAKE_UUID',
                [
                    'validation' => [__CLASS__, 'validateIntakeUuid'],
                ]
            ),
            new IntegerField(
                'INTAKE_NUMBER',
                [
                ]
            ),
            new StringField(
                'STATUS_CODE',
                [
                    'validation' => [__CLASS__, 'validateStatusCode'],
                ]
            ),
            new DatetimeField(
                'STATUS_DATE',
                [
                ]
            ),
            new StringField(
                'STATE_CODE',
                [
                    'validation' => [__CLASS__, 'validateStateCode'],
                ]
            ),
            new DatetimeField(
                'STATE_DATE',
                [
                ]
            ),
            new IntegerField(
                'TYPE',
                [
                    'required' => true,
                ]
            ),
            new StringField(
                'CDEK_ORDER_ID',
                [
                    'validation' => [__CLASS__, 'validateCdekOrderId'],
                ]
            ),
            new StringField(
                'CDEK_ORDER_UUID',
                [
                    'validation' => [__CLASS__, 'validateCdekOrderUuid'],
                ]
            ),
            new IntegerField(
                'ACCOUNT',
                [
                ]
            ),
            new IntegerField(
                'STORE_ID',
                [
                ]
            ),
            new DatetimeField(
                'INTAKE_DATE',
                [
                    'required' => true,
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
            new StringField(
                'PACK_NAME',
                [
                    'validation' => [__CLASS__, 'validatePackName'],
                ]
            ),
            new IntegerField(
                'PACK_WEIGHT',
                [
                ]
            ),
            new IntegerField(
                'PACK_LENGTH',
                [
                ]
            ),
            new IntegerField(
                'PACK_WIDTH',
                [
                ]
            ),
            new IntegerField(
                'PACK_HEIGHT',
                [
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
            new IntegerField(
                'FROM_LOCATION_CODE',
                [
                ]
            ),
            new StringField(
                'FROM_LOCATION_ADDRESS',
                [
                    'validation' => [__CLASS__, 'validateFromLocationAddress'],
                ]
            ),
            new StringField(
                'COMMENT',
                [
                    'validation' => [__CLASS__, 'validateComment'],
                ]
            ),
            new StringField(
                'STATUS',
                [
                    'validation' => [__CLASS__, 'validateStatus'],
                ]
            ),
            new TextField(
                'MESSAGE',
                [
                ]
            ),
            new BooleanField(
                'OK',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'N',
                ]
            ),
            new DatetimeField(
                'UPTIME',
                [
                    'required' => true,
                ]
            ),
        ];
    }

    /**
     * Returns validators for INTAKE_UUID field.
     *
     * @return array
     */
    public static function validateIntakeUuid()
    {
        return [
            new Length(null, 36),
        ];
    }

    /**
     * Returns validators for STATUS_CODE field.
     *
     * @return array
     */
    public static function validateStatusCode()
    {
        return [
            new Length(null, 30),
        ];
    }

    /**
     * Returns validators for STATE_CODE field.
     *
     * @return array
     */
    public static function validateStateCode()
    {
        return [
            new Length(null, 20),
        ];
    }

    /**
     * Returns validators for CDEK_ORDER_ID field.
     *
     * @return array
     */
    public static function validateCdekOrderId()
    {
        return [
            new Length(null, 20),
        ];
    }

    /**
     * Returns validators for CDEK_ORDER_UUID field.
     *
     * @return array
     */
    public static function validateCdekOrderUuid()
    {
        return [
            new Length(null, 36),
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

    /**
     * Returns validators for PACK_NAME field.
     *
     * @return array
     */
    public static function validatePackName()
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
     * Returns validators for FROM_LOCATION_ADDRESS field.
     *
     * @return array
     */
    public static function validateFromLocationAddress()
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
     * Returns validators for STATUS field.
     *
     * @return array
     */
    public static function validateStatus()
    {
        return [
            new Length(null, 30),
        ];
    }
}