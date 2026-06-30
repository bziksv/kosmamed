<?php
namespace Rbs\MoyskladStocks\Services;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Entity\File;
use Rbs\MoyskladStocks\LangMsg;

class MoyskladImportUtils
{
    public static function getAttrList($attributes = []): array
    {
        $attrList = [];
        if (!empty($attributes)) {
            foreach ($attributes as $attr) {
                switch ($attr->{'type'}) {
                    case 'string':
                    case 'text':
                        $attrList['string'][$attr->{'id'}] = $attr->{'value'};
                    break;
                    case 'double':
                        $attrList['double'][$attr->{'id'}] = $attr->{'value'};
                    break;
                    case 'boolean':
                        $attrList['boolean'][$attr->{'id'}] = (bool)$attr->{'value'};
                    break;
                }
            }
        }
        return $attrList;
    }

    public static function getAllAttrList($attributes = []): array
    {
        $attrList = [];
        if (!empty($attributes)) {
            foreach ($attributes as $attr) {
                $attrList[$attr->{'id'}] = $attr;
            }
        }
        return $attrList;
    }

    public static function getMsFilesArray($filesObject = null, $size = 0): array
    {
        $currentMsImagesArray = [];

        if ($filesObject->{'meta'}->{'size'} > 0) {
            if ($size <= 0) {
                $size = $filesObject->{'meta'}->{'size'};
            }
            if (property_exists($filesObject, 'rows')) {
                $filesObject->{'hasErrors'} = false;
            } else {
                $filesObject = ApiNew::get($filesObject->{'meta'}->{'href'});
            }
            if (Utils::is_success($filesObject)) {
                foreach ($filesObject->{'rows'} as $i => $row) {
                    $file = new File($row);
                    if ($file->isLoaded()) {
                        $currentMsImagesArray[] = $file;
                    }
                    if (intval($i + 1) === intval($size)) {
                        break;
                    }
                }
            }
        }

        return $currentMsImagesArray;
    }

    public static function getOptionAttributesArray(array $filter = [], int $cache = 0): array
    {
        $propsStrProducts = ApiNew::get('/entity/product/metadata/attributes', $filter, (int)$cache);
        
        $selectStrPropsMs = [];
        $selectGabbPropsMs = [];
        $selectBoolProps = [];
        $selectPropsForProps = [
            'S' => [],
            'N' => [],  
            'F' => [],
            'SD' => []
        ];

        if(Utils::is_success($propsStrProducts) && Utils::array_exists($propsStrProducts)){
            $attrNameLang = LangMsg::get('AJAX_ATTR');
            foreach ($propsStrProducts->rows as $attr) {

                $attrName = "[{$attrNameLang}] {$attr->name} ({$attr->type})";

                switch ($attr->type) {
                    case 'double':
                    case 'long':
                        $selectGabbPropsMs[$attr->id] = $attrName;
                        break;
                    case 'string':
                    case 'text':
                        $selectStrPropsMs[$attr->id] = $attrName;
                        $selectGabbPropsMs[$attr->id] = $attrName;
                        break;
                    case 'boolean':
                        $selectBoolProps[$attr->id] = $attrName;
                        break;
                }

                if ($attr->type !== 'file') {
                    $selectPropsForProps['S'][$attr->id] = $attrName;
                }

                if ($attr->type === 'time') {
                    $selectPropsForProps['SD'][$attr->id] = $attrName;
                }

                if ($attr->type === 'file') {
                    $selectPropsForProps['F'][$attr->id] = $attrName;
                }

                if ($attr->type === 'double' || $attr->type === 'long') {
                    $selectPropsForProps['N'][$attr->id] = $attrName;
                }
            }
        }

        return [
            'selectStrPropsMs' => $selectStrPropsMs,
            'selectGabbPropsMs' => $selectGabbPropsMs,
            'selectBoolProps' => $selectBoolProps,
            'selectPropsForProps' => $selectPropsForProps,
        ];
    }
}