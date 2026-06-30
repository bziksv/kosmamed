<?php
namespace Rbs\Moysklad;

class Helper
{
    public static function parsePhone($phoneStr = '', $withPlus = true)
    {
        $phoneStr = str_replace([' ', '+', '(', ')', '-'], '', $phoneStr);
        if (substr($phoneStr, 0, 1) == '8') {
            $phoneStr = substr_replace($phoneStr, '7', 0, 1);
        }
        if ($withPlus && !empty($phoneStr) && substr($phoneStr, 0, 1) != '+') {
            $phoneStr = "+" . $phoneStr;
        }
        return $phoneStr;
    }
    
    public static function getOrderAccountId($name = '')
    {
        return str_replace(Config::getOrderPrefix(), '', $name);
    }

    public static function hasManagerComment($comment = '')
    {
        return mb_strpos($comment, Config::getCommentDelimiter()) !== false;
    }

    public static function getManagerComment($comment = '')
    {
        return trim(explode(Config::getCommentDelimiter(), $comment)[1]);
    }

    public static function clearBaseHrefLink($href = '', $entity = '')
    {
        return str_replace(Config::getBaseHrefLinkNew($entity), '', $href);
    }
    
    public static function getTracksFromString($trackStr = '')
    {
        $tracks = [];
        $trackNumberStrs = explode("\n", $trackStr);
        foreach ($trackNumberStrs as $track) {
            $tmpTrack = explode(':', $track);
            if (self::count($tmpTrack) === 2) {
                $tracks[(string)trim($tmpTrack[0])] = (string)trim($tmpTrack[1]);
            }
        }
        return $tracks;
    }

    public static function clearAllSpaces($str = '')
    {
        return str_replace([' ', "\n"], "", $str);
    }
 
    public static function isDifferentCommentsText($commentBx = '', $commentMs = '')
    {
        $commentBx = (string)$commentBx;
        $commentMs = (string)$commentMs;
        return self::clearAllSpaces($commentBx) !== self::clearAllSpaces($commentMs);
    }
 
    public static function isDifferentStringFields($fieldBx = '', $fieldMs = ''): bool
    {
        $fieldBx = mb_strtolower((string)$fieldBx);
        $fieldMs = mb_strtolower((string)$fieldMs);
        return trim($fieldBx) !== trim($fieldMs);
    }

    public static function makeClearStringFromArray($array = [])
    {
        return str_replace(" ", "", implode("", $array));
    }

    public static function isChangedValues($changedFields, $arLookForChangeFields)
    {
        if (self::count($changedFields) > 0) {
            foreach ($changedFields as $field => $val) {
                if (in_array($field, $arLookForChangeFields)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getCurrentDeliveryTypeMs($customEntityFields = [], $deliveryProp = false)
    {
        if (!$deliveryProp || self::count($customEntityFields) <= 0 || !is_array($customEntityFields)) {
            return false;
        }

        foreach ($customEntityFields as $attrKey => $attr) {
            if ($attr->metadataHref === $deliveryProp) {
                return ['id' => $attrKey, 'value' => $attr];
            }
        }

        return false;
    }

    public static function getCurrentPayTypeMs($attributes = [], $propHref = false)
    {
        if (!$propHref || self::count($attributes) <= 0 || !is_array($attributes)) {
            return false;
        }

        foreach ($attributes as $attr) {
            if ($attr->type === 'customentity') {
                if ($attr->value->meta->metadataHref === $propHref) {
                    return ['id' => $attr->id, 'value' => $attr->value];
                }
            }
        }

        return false;
    }

    /** @deprecated */
    public static function getDateString()
    {
        return Utils::get_date_string();
    }

    /** @deprecated */
    public static function getDateMsString(string $dateBx = '', bool $withTime = false)
    {
        return Utils::get_date_ms_string($dateBx, $withTime, Config::checkFeature('is_eu_msk_timezone'));
    }

    /** @deprecated */
    public static function convertArray2Object($defs)
    {
        return Utils::array_to_object($defs);
    }

    /** @deprecated */
    public static function count($var = null): int
    {
        return Utils::count($var);
    }
}
