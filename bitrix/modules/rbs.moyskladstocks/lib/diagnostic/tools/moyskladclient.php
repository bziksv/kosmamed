<?php
namespace Rbs\MoyskladStocks\Diagnostic\Tools;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

class MoyskladClient
{
    public static function getExternalCodeList($entity = 'product', $limit = 100, $offset = 0): array
    {
        $importParamsList = ImportParamsConfig::getImportParams($entity);
        $customFilter = [];
        if ($importParamsList['include_archived']) {
            $customFilter = ['archived=true','archived=false'];
        }

        $isVariant = $entity === 'variant';
        if($isVariant) {
            $customFilter[] = 'type=variant';
        }

        $msApi = ApiNew::get(
            $isVariant ? '/entity/assortment' : '/entity/' . $entity, 
            [
                'limit' => $limit,
                'offset' => $offset,
                'filter' => \CRbsMoyskladStocks::getFilterString($entity, implode(';', $customFilter)),
                'expand' => 'product'
            ]
        );

        if(!Utils::is_success($msApi)) {
            throw new \Exception(current($msApi->errors));
        }

        $externalCodeList = [];
        foreach($msApi->rows as $row) {

            $xmlId = ProductIdentifier::getIdentifierValue($row);
            if($row->meta->type === 'variant') {
                $parentXmlId = ProductIdentifier::getIdentifierValue($row->product);
                $xmlId = ProductIdentifier::buildVariantXmlId($parentXmlId, $xmlId);
            }

            $externalCodeList[] = (object)[
                'id' => $row->id,
                'name' => $row->name,
                'externalCode' => $xmlId,
            ];
        }

        return [
            'items' => $externalCodeList,
            'total_count' => $msApi->meta->size,
        ];
    }
}