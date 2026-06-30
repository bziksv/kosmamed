<?php
namespace Rbs\MoyskladStocks\Internals\ProductFinder;

/**
 * Finds BUS products during discount import from MoySklad
 *
 * Used in: Import\Discount
 */
class DiscountProductFinder
{
    /**
     * Builds XML_ID array for discount assortment lookup
     *
     * Variants with composite XML_ID get %# prefix for LIKE search
     *
     * @param array $assortment Discount assortment from MoySklad API
     * @return array XML_ID values for filter
     */
    public static function buildAssortmentXmlIds($assortment): array
    {
        $result = [];
        if (!empty($assortment)) {
            foreach ($assortment as $item) {
                $xmlId = ProductIdentifier::getIdentifierValue($item);
                if (!empty($xmlId)) {
                    $entity = !empty($item->meta->type) ? $item->meta->type : '';
                    if (ProductIdentifier::isVariantCompositeMode($entity)) {
                        $xmlId = '%#' . $xmlId;
                    }
                    $result[] = $xmlId;
                }
            }
        }
        return $result;
    }
}
