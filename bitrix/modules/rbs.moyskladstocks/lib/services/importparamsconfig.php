<?php
namespace Rbs\MoyskladStocks\Services;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Internals\Enums\OptionEntityParams;

class ImportParamsConfig
{
    private static function getFeature(string $entity, string $prefix, string $param): bool
    {
        return Config::getOption("im_{$entity}_{$prefix}_{$param}") === 'Y';
    }

    private static function getFeatureParams(string $entity, string $prefix, array $featureList): array
    {
        $result = [];
        foreach ($featureList as $prop) {
            $result[$prop] = self::getFeature($entity, $prefix, $prop);
        }
        return $result;
    }

    public static function getImportParams($entity = 'product'): array
    {
        $result = self::getFeatureParams($entity, 'p', OptionEntityParams::getImportFeatureList());

        foreach (OptionEntityParams::getImportParamsList() as $prop) {
            $result[$prop] = Config::getOption("im_{$entity}_p_{$prop}");
        }

        return $result;
    }

    public static function getImportPropList($entity = 'product'): array
    {
        $result = [];
        $optArray = Config::getOptionArray("im_{$entity}_p_proplist");
        if (Utils::is_count($optArray)) {
            foreach ($optArray as $optId) {
                $optionMs = Config::getOption("im_{$entity}_p_prop_{$optId}");
                if (!empty($optionMs) && !isset($result[$optId])) {
                    $result[$optId] = $optionMs;
                }
            }
        }
        return $result;
    }

    public static function getWhParams($entity = 'product'): array
    {
        return self::getFeatureParams($entity, 'wh', OptionEntityParams::getWhFeatureList());
    }

    public static function getUpParams($entity = 'product'): array
    {
        return self::getFeatureParams($entity, 'up', OptionEntityParams::getUpFeatureList());
    }

    public static function getImportFeature($entity = 'product', $param = ''): bool
    {
        return self::getFeature($entity, 'p', $param);
    }

    public static function getWhFeature($entity = 'product', $param = ''): bool
    {
        return self::getFeature($entity, 'wh', $param);
    }

    public static function getUpFeature($entity = 'product', $param = ''): bool
    {
        return self::getFeature($entity, 'up', $param);
    }
}