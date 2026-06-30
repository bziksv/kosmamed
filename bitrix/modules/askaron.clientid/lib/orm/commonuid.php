<?php
namespace Askaron\ClientId\ORM;


use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class CommonUidTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DATE_CREATE datetime mandatory default '0000-00-00 00:00:00'
 * <li> SHORT_CODE string(255) optional
 * <li> YM_CODE string(255) optional
 * <li> GA_CODE string(255) optional
 * </ul>
 *
 * @package Bitrix\Askaron
 **/

class CommonUidTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_askaron_clientid_session';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('CLIENTID_COMMON_ENTITY_ID_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('CLIENTID_COMMON_ENTITY_DATE_CREATE_FIELD'),
			),
			'SHORT_CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateShortCode'),
				'title' => Loc::getMessage('CLIENTID_COMMON_ENTITY_SHORT_CODE_FIELD'),
			),
			'YM_CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateYmCode'),
				'title' => Loc::getMessage('CLIENTID_COMMON_ENTITY_YM_CODE_FIELD'),
			),
			'GA_CODE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateGaCode'),
				'title' => Loc::getMessage('CLIENTID_COMMON_ENTITY_GA_CODE_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for SHORT_CODE field.
	 *
	 * @return array
	 */
	public static function validateShortCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for YM_CODE field.
	 *
	 * @return array
	 */
	public static function validateYmCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for GA_CODE field.
	 *
	 * @return array
	 */
	public static function validateGaCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}