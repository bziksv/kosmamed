<?php
namespace Rbs\MoyskladStocks\Internals\Enums;

use Rbs\MoyskladStocks\LangMsg;

class TrackingType
{
    public static function getTrackingTypeValue(string $key): string
    {
        return self::getTrackingTypeList()[$key] ?? '';
    }

	public static function getTrackingTypeList(): array
	{
		return [
			'BEER_ALCOHOL' => LangMsg::get('TRACKING_TYPE_BEER_ALCOHOL'),
			'BICYCLE' => LangMsg::get('TRACKING_TYPE_BICYCLE'),
			'ELECTRONICS' => LangMsg::get('TRACKING_TYPE_ELECTRONICS'),
			'FOOD_SUPPLEMENT' => LangMsg::get('TRACKING_TYPE_FOOD_SUPPLEMENT'),
			'LP_CLOTHES' => LangMsg::get('TRACKING_TYPE_LP_CLOTHES'),
			'LP_LINENS' => LangMsg::get('TRACKING_TYPE_LP_LINENS'),
			'MEDICAL_DEVICES' => LangMsg::get('TRACKING_TYPE_MEDICAL_DEVICES'),
			'MILK' => LangMsg::get('TRACKING_TYPE_MILK'),
			'NABEER' => LangMsg::get('TRACKING_TYPE_NABEER'),
			'NCP' => LangMsg::get('TRACKING_TYPE_NCP'),
			'NOT_TRACKED' => LangMsg::get('TRACKING_TYPE_NOT_TRACKED'),
			'OTP' => LangMsg::get('TRACKING_TYPE_OTP'),
			'PERFUMERY' => LangMsg::get('TRACKING_TYPE_PERFUMERY'),
			'SANITIZER' => LangMsg::get('TRACKING_TYPE_SANITIZER'),
			'SEAFOOD' => LangMsg::get('TRACKING_TYPE_SEAFOOD'),
			'SHOES' => LangMsg::get('TRACKING_TYPE_SHOES'),
			'SOFT_DRINKS' => LangMsg::get('TRACKING_TYPE_SOFT_DRINKS'),
			'TIRES' => LangMsg::get('TRACKING_TYPE_TIRES'),
			'TOBACCO' => LangMsg::get('TRACKING_TYPE_TOBACCO'),
			'VETPHARMA' => LangMsg::get('TRACKING_TYPE_VETPHARMA'),
			'WATER' => LangMsg::get('TRACKING_TYPE_WATER'),
		];
	}
}