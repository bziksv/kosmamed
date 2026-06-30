<?php


namespace Askaron\ClientId;


class Tools
{
	public static function showString()
	{
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			'askaron:askaron.clientid.check',
			'',
			[]
		);
	}
}