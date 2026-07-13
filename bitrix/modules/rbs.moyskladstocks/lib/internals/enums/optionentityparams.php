<?php
namespace Rbs\MoyskladStocks\Internals\Enums;

class OptionEntityParams
{
    public static function getImportFeatureList(): array
    {
        return [
            'section_off',
            'section_keep',
            'translit',
            'trim',
            'uom',
            'ms_section_root',
            'descr',
            'descr_full',
            'descr_delete',
            'img',
            'img_full',
            'img_del',
            'img_more_all',
            'weight',
            'sort',
            'volume',
            'code',
            'code_uniq',
            'update_facet',
            'parent_weight',
            'parent_measure',
            'parent_sizes',
            'parent_tracking',
            'barcode',
            'code_from_pf',
            'code_uniq_parent',
            'emptyim',
            'img_prop',
            'sizes',
            'props',
            'workflow',
            'updsearch',
            'resizepic',
            'include_archived',
            'vat',
            'vat_inc',
            'ratio',
            'parent_ratio',
            'ignore_prodtype',
            'tracking_type',
            'ignore_section_active',
        ];
    }

    public static function getImportParamsList(): array
    {
        return [
            'descr_source',
            'descr_full_source',
            'descr_type',
            'descr_full_type',
            'img_more_prop',
            'width',
            'height',
            'length',
        ];
    }

    public static function getWhFeatureList(): array
    {
        return [
            'descr',
            'descr_full',
            'seocache',
            'uom',
            'img',
            'structure',
            'outer_sec',
            'folder',
            'img_full',
            'img_prop',
            'sizes',
            'props',
            'sort',
            'name',
            'code',
            'archived',
            'update_facet',
            'parent_weight',
            'parent_sizes',
            'parent_measure',
            'parent_tracking',
            'barcode',
            'active_by_filter',
            'vat',
            'vat_inc',
            'ratio',
            'parent_ratio',
            'tracking_type',
        ];
    }

    public static function getUpFeatureList(): array
    {
        return [
            'descr',
            'descr_full',
            'uom',
            'seocache',
            'date',
            'structure',
            'img',
            'folder',
            'img_full',
            'img_prop',
            'sizes',
            'props',
            'sort',
            'name',
            'code',
            'archived',
            'update_facet',
            'parent_weight',
            'parent_sizes',
            'parent_measure',
            'parent_tracking',
            'barcode',
            'active_by_filter',
            'vat',
            'vat_inc',
            'ratio',
            'parent_ratio',
            'tracking_type',
        ];
    }
}