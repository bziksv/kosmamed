<?php

namespace Askaron\ClientId\ORM;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class QueueTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> DATE_CREATE datetime mandatory default '0000-00-00 00:00:00'
 * <li> TIMESTAMP_X datetime mandatory default '0000-00-00 00:00:00'
 * <li> CONVERSION_DATE datetime mandatory default '0000-00-00 00:00:00'
 * <li> ENTITY_TYPE string(255) optional
 * <li> ENTITY_ID string(255) optional
 * <li> TARGET string(255) mandatory
 * <li> CLIENT_ID string(255) mandatory
 * <li> STATUS string(1) optional
 * <li> COMMENT string optional
 * </ul>
 *
 * @package Bitrix\Askaron
 **/
class QueueTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_askaron_clientid_queue';
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
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_ID_FIELD'),
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_DATE_CREATE_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'CONVERSION_DATE' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_CONVERSION_DATE_FIELD'),
			),
			'SITE_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSiteId'),
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_SITE_ID_FIELD'),
			),
			'ENTITY_TYPE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityType'),
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_ENTITY_TYPE_FIELD'),
			),
			'ENTITY_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateEntityId'),
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_ENTITY_ID_FIELD'),
			),
			'TARGET' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateTarget'),
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_TARGET_FIELD'),
			),
			'CLIENT_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateClientId'),
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_CLIENT_ID_FIELD'),
			),
			'STATUS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateStatus'),
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_STATUS_FIELD'),
			),
			'COMMENT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('CLIENTID_QUEUE_ENTITY_COMMENT_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 36),
		);
	}
	/**
	 * Returns validators for ENTITY_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEntityType()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for ENTITY_ID field.
	 *
	 * @return array
	 */
	public static function validateEntityId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for TARGET field.
	 *
	 * @return array
	 */
	public static function validateTarget()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CLIENT_ID field.
	 *
	 * @return array
	 */
	public static function validateClientId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for STATUS field.
	 *
	 * @return array
	 */
	public static function validateStatus()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
}