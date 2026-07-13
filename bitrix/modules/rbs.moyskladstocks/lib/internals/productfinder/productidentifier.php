<?php
namespace Rbs\MoyskladStocks\Internals\ProductFinder;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\HlCache\ExtCodes;
use Rbs\MoyskladStocks\Utils;

/**
 * Product identification: MoySklad entities ? BUS elements via XML_ID
 *
 * Handles: identifier extraction, filter building, composite XML_ID, href?xmlId resolution
 */
class ProductIdentifier
{
    /**
     * Returns identifier value (id or externalCode) from MoySklad entity
     *
     * @param object|null $msRow MoySklad API entity
     * @return string Identifier value or empty string
     */
    public static function getIdentifierValue($msRow): string
    {
        if (!is_object($msRow)) {
            return '';
        }

        if (Config::isIdentifyById()) {
            if (!empty($msRow->id)) {
                return (string)$msRow->id;
            }
            return '';
        }

        if (!empty($msRow->externalCode)) {
            return (string)$msRow->externalCode;
        }
        return '';
    }

    /**
     * Returns identifier value (id or externalCode) for section (productfolder) entities
     *
     * @param object|null $msRow MoySklad API entity
     * @return string Identifier value or empty string
     */
    public static function getSectionIdentifierValue($msRow): string
    {
        if (!is_object($msRow)) {
            return '';
        }

        if (Config::isIdentifySectionsById()) {
            if (!empty($msRow->id)) {
                return (string)$msRow->id;
            }
            return '';
        }

        if (!empty($msRow->externalCode)) {
            return (string)$msRow->externalCode;
        }
        return '';
    }

    /**
     * Whether ExtCodes table is needed (not needed when identifying by id)
     *
     * @return bool
     */
    public static function isExtCodesRequired(): bool
    {
        return !Config::isIdentifyById();
    }

    /**
     * Extracts UUID from MoySklad API href (last URL path segment)
     *
     * @param string $href MoySklad API URL
     * @return string UUID or empty string
     */
    public static function extractIdFromHref(string $href): string
    {
        if (empty($href)) {
            return '';
        }
        $path = parse_url($href, PHP_URL_PATH);
        if (empty($path)) {
            return '';
        }
        $segments = explode('/', rtrim($path, '/'));
        $id = end($segments);
        return !empty($id) ? $id : '';
    }

    /**
     * Extracts entity type from MoySklad API href (second-to-last URL path segment)
     *
     * @param string $href MoySklad API URL
     * @return string Entity type or empty string
     */
    public static function extractEntityFromHref(string $href): string
    {
        if (empty($href)) {
            return '';
        }
        $path = parse_url($href, PHP_URL_PATH);
        if (empty($path)) {
            return '';
        }
        $segments = explode('/', rtrim($path, '/'));
        $count = count($segments);
        return $count >= 2 ? $segments[$count - 2] : '';
    }

    /**
     * Resolves XML_ID from href (by id: extracts from URL, by externalCode: via ExtCodes)
     *
     * @param string $href MoySklad API URL
     * @return string|false XML_ID or false
     */
    public static function resolveXmlIdFromHref(string $href)
    {
        if (Config::isIdentifyById()) {
            $id = self::extractIdFromHref($href);
            return !empty($id) ? $id : false;
        }

        if (ExtCodes::isExsist()) {
            return ExtCodes::get($href);
        }
        return false;
    }

    /**
     * Batch resolves hrefs to xmlIds
     *
     * @param array $hrefs MoySklad API URLs
     * @return array [href => xmlId, ...]
     */
    public static function resolveXmlIdsFromHrefs(array $hrefs): array
    {
        if (Config::isIdentifyById()) {
            $result = [];
            foreach ($hrefs as $href) {
                $id = self::extractIdFromHref($href);
                if (!empty($id)) {
                    $result[$href] = $id;
                }
            }
            return $result;
        }

        if (ExtCodes::isExsist()) {
            return ExtCodes::getArray($hrefs);
        }
        return [];
    }

    /**
     * Groups assortment IDs by entity type via iblock lookup
     *
     * @param array $assortmentIds Assortment IDs from CurrentStocks
     * @return array [entity => [assortmentId => xmlId], 'unfinded' => [...]]
     */
    public static function groupAssortmentIdsByEntityType(array $assortmentIds): array
    {
        $result = [
            'product' => [],
            'variant' => [],
            'bundle' => [],
            'service' => [],
            'unfinded' => []
        ];

        if (!Utils::is_count($assortmentIds)) {
            return $result;
        }

        $unfinded = array_flip($assortmentIds);

        $entities = ['product', 'variant', 'bundle', 'service'];
        foreach ($entities as $entity) {
            $iblockId = Config::getIblockId($entity);
            if ((int)$iblockId <= 0) {
                continue;
            }

            $filter = self::buildBatchFilter($assortmentIds, $entity);
            $filter['IBLOCK_ID'] = $iblockId;

            $elements = \Bitrix\Iblock\ElementTable::getList([
                'filter' => $filter,
                'select' => ['ID', 'XML_ID']
            ])->fetchAll();

            if (Utils::is_count($elements)) {
                foreach ($elements as $element) {
                    $xmlId = self::extractXmlId($element['XML_ID'], $entity);
                    if (isset($unfinded[$xmlId])) {
                        $result[$entity][$xmlId] = $xmlId;
                        unset($unfinded[$xmlId]);
                    }
                }
            }
        }

        $result['unfinded'] = array_keys($unfinded);

        return $result;
    }

    /**
     * Whether composite XML_ID mode (parent#child) is active for this entity
     *
     * @param string $entity Entity type (product, variant, bundle, service)
     * @return bool
     */
    public static function isVariantCompositeMode(string $entity): bool
    {
        return $entity === 'variant' && !Config::isClearXmlId();
    }

    /**
     * Builds variant XML_ID respecting composite mode setting
     *
     * @param string $parentXmlId Parent product XML_ID
     * @param string $childXmlId Variant XML_ID (externalCode or id)
     * @return string "parent#child" or plain childXmlId
     */
    public static function buildVariantXmlId(string $parentXmlId, string $childXmlId): string
    {
        if (!Config::isClearXmlId()) {
            return $parentXmlId . '#' . $childXmlId;
        }
        return $childXmlId;
    }

    /**
     * Builds Bitrix filter for single element lookup by XML_ID
     *
     * Variants in composite mode use LIKE search (%#), others use exact match
     *
     * @param string $xmlId Identifier value
     * @param string $entity Entity type
     * @return array Bitrix API filter
     */
    public static function buildSingleFilter(string $xmlId, string $entity): array
    {
        if (self::isVariantCompositeMode($entity)) {
            return ['XML_ID' => '%#' . $xmlId];
        }
        return ['=XML_ID' => $xmlId];
    }

    /**
     * Builds Bitrix filter for batch element lookup by XML_ID
     *
     * Variants in composite mode use LIKE search (%#), others use exact match
     *
     * @param array $xmlIds Identifier values
     * @param string $entity Entity type
     * @return array Bitrix API filter
     */
    public static function buildBatchFilter(array $xmlIds, string $entity): array
    {
        if (self::isVariantCompositeMode($entity)) {
            $variantXmlIds = [];
            foreach ($xmlIds as $xmlId) {
                $variantXmlIds[] = '%#' . $xmlId;
            }
            return ['XML_ID' => $variantXmlIds];
        }
        return ['=XML_ID' => $xmlIds];
    }

    /**
     * Extracts clean identifier from full XML_ID ("parent#child" ? "child")
     *
     * @param string $fullXmlId Full XML_ID from BUS element
     * @param string $entity Entity type
     * @return string Clean identifier
     */
    public static function extractXmlId(string $fullXmlId, string $entity): string
    {
        if (self::isVariantCompositeMode($entity)) {
            $parts = explode('#', $fullXmlId);
            return count($parts) > 1 ? $parts[1] : $fullXmlId;
        }
        return $fullXmlId;
    }
}
