<?php
namespace Ipolh\SDEK\Admin;

use Ipolh\SDEK\Bitrix\Adapter;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\StoresTable;
use Ipolh\SDEK\Admin\Grid\DatabaseGrid;

/**
 * Class StoresGrid
 * @package Ipolh\SDEK\Admin
 */
class StoresGrid extends DatabaseGrid
{
    /**
     * @var string
     */
    protected $fetchMode = self::FETCH_AS_ARRAY;

    /**
     * @var array
     */
    protected $defaultSorting = ['ID' => 'DESC'];

    /**
     * @var array
     */
    protected $defaultButtons = [
        [
            'CAPTION' => 'TABLE_STORES_BTN_ADD',
            'TYPE'    => 'button',
            'ONCLICK' => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.newStore();',
        ],
    ];

    /**
     * @var array
     */
    protected $defaultColumns = [
        [
            'id'          => 'ID',
            'name'        => 'TABLE_STORES_ID',
            'sort'        => 'ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
            'type'        => 'number',
        ],
        [
            'id'          => 'IS_ACTIVE',
            'name'        => 'TABLE_STORES_IS_ACTIVE',
            'sort'        => 'IS_ACTIVE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'NAME',
            'name'        => 'TABLE_STORES_NAME',
            'sort'        => 'NAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
            'quickSearch' => '%',
        ],
        [
            'id'          => 'FROM_LOCATION_CODE',
            'name'        => 'TABLE_STORES_FROM_LOCATION_CODE',
            'sort'        => 'FROM_LOCATION_CODE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'FROM_LOCATION_STREET',
            'name'        => 'TABLE_STORES_FROM_LOCATION_STREET',
            'sort'        => 'FROM_LOCATION_STREET',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'FROM_LOCATION_HOUSE',
            'name'        => 'TABLE_STORES_FROM_LOCATION_HOUSE',
            'sort'        => 'FROM_LOCATION_HOUSE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'FROM_LOCATION_FLAT',
            'name'        => 'TABLE_STORES_FROM_LOCATION_FLAT',
            'sort'        => 'FROM_LOCATION_FLAT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'IS_DEFAULT_FOR_LOCATION',
            'name'        => 'TABLE_STORES_IS_DEFAULT_FOR_LOCATION',
            'sort'        => 'IS_DEFAULT_FOR_LOCATION',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'IS_ADDRESS_DATA_SENT',
            'name'        => 'TABLE_STORES_IS_ADDRESS_DATA_SENT',
            'sort'        => 'IS_ADDRESS_DATA_SENT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'SENDER_COMPANY',
            'name'        => 'TABLE_STORES_SENDER_COMPANY',
            'sort'        => 'SENDER_COMPANY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SENDER_NAME',
            'name'        => 'TABLE_STORES_SENDER_NAME',
            'sort'        => 'SENDER_NAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SENDER_PHONE_NUMBER',
            'name'        => 'TABLE_STORES_SENDER_PHONE_NUMBER',
            'sort'        => 'SENDER_PHONE_NUMBER',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SENDER_PHONE_ADDITIONAL',
            'name'        => 'TABLE_STORES_SENDER_PHONE_ADDITIONAL',
            'sort'        => 'SENDER_PHONE_ADDITIONAL',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'IS_SENDER_DATA_SENT',
            'name'        => 'TABLE_STORES_IS_SENDER_DATA_SENT',
            'sort'        => 'IS_SENDER_DATA_SENT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'SELLER_NAME',
            'name'        => 'TABLE_STORES_SELLER_NAME',
            'sort'        => 'SELLER_NAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SELLER_PHONE',
            'name'        => 'TABLE_STORES_SELLER_PHONE',
            'sort'        => 'SELLER_PHONE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SELLER_ADDRESS',
            'name'        => 'TABLE_STORES_SELLER_ADDRESS',
            'sort'        => 'SELLER_ADDRESS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'IS_SELLER_DATA_SENT',
            'name'        => 'TABLE_STORES_IS_SELLER_DATA_SENT',
            'sort'        => 'IS_SELLER_DATA_SENT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'NEED_CALL',
            'name'        => 'TABLE_STORES_NEED_CALL',
            'sort'        => 'NEED_CALL',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'POWER_OF_ATTORNEY',
            'name'        => 'TABLE_STORES_POWER_OF_ATTORNEY',
            'sort'        => 'POWER_OF_ATTORNEY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'IDENTITY_CARD',
            'name'        => 'TABLE_STORES_IDENTITY_CARD',
            'sort'        => 'IDENTITY_CARD',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'COMMENT',
            'name'        => 'TABLE_STORES_COMMENT',
            'sort'        => 'COMMENT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'INTAKE_TIME_FROM',
            'name'        => 'TABLE_STORES_INTAKE_TIME_FROM',
            'sort'        => 'INTAKE_TIME_FROM',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'INTAKE_TIME_TO',
            'name'        => 'TABLE_STORES_INTAKE_TIME_TO',
            'sort'        => 'INTAKE_TIME_TO',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'LUNCH_TIME_FROM',
            'name'        => 'TABLE_STORES_LUNCH_TIME_FROM',
            'sort'        => 'LUNCH_TIME_FROM',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'LUNCH_TIME_TO',
            'name'        => 'TABLE_STORES_LUNCH_TIME_TO',
            'sort'        => 'LUNCH_TIME_TO',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
    ];

    /**
     * @var array
     */
    protected $defaultRowActions = [
        // Acceptable system icon classes are in \bitrix\js\main\popup\dist\main.popup.bundle.css
        // menu-popup-item-copy for documents

        'EDIT' => [
            'ICONCLASS' => 'menu-popup-item-delegate',
            'TEXT'      => 'TABLE_STORES_ROW_EDIT',
            'ONCLICK'   => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.editStore("#ID#")',
        ],
        'DELETE' => [
            'ICONCLASS' => 'menu-popup-item-delete',
            'TEXT'      => 'TABLE_STORES_ROW_DELETE',
            'ONCLICK'   => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.deleteStore("#ID#")',
        ],
    ];

    /**
     * Return ORM data mapper for data selection
     *
     * @return \Bitrix\Main\ORM\Data\DataManager
     */
    public function getDataMapper()
    {
        return StoresTable::class;
    }

    /**
     * Get single data item in grid row format
     *
     * @param array $item
     * @return array
     */
    protected function getRow($item)
    {
        $ret = parent::getRow($item);

        // Add some human-readable texts instead of specific identifiers there
        // $ret['data'][__COLUMN_NAME__] = ... ;

        $city = \sqlSdekCity::getBySId($ret['data']['FROM_LOCATION_CODE']);
        if (is_array($city)) {
            $ret['data']['FROM_LOCATION_CODE'] = implode(', ', [$city['REGION'], $city['NAME']]).' ['.$ret['data']['FROM_LOCATION_CODE'].']';
        }

        $flagKeys = ['IS_ACTIVE', 'IS_SENDER_DATA_SENT', 'NEED_CALL', 'POWER_OF_ATTORNEY', 'IDENTITY_CARD', 'IS_SELLER_DATA_SENT', 'IS_DEFAULT_FOR_LOCATION', 'IS_ADDRESS_DATA_SENT'];
        foreach ($flagKeys as $key) {
            $ret['data'][$key] = ($ret['data'][$key] === 'Y') ? Tools::getMessage('TABLE_STORES_Y') : Tools::getMessage('TABLE_STORES_N');
        }

        /*
        // Rows coloring by current order status
        // Beware:
        // - undocumented param 'attrs' used, version compatibility unknown
        // - drop .main-grid-cell background color required
        $statusToColor = array(
            '1' => '#E2FCE2',
            '2' => '#FFEDED',
            '3' => '#FCFCBF',
            '4' => '#D9FFCE',
        );
        $color = array_key_exists($ret['data']['STATUS'], $statusToColor) ? $statusToColor[$ret['data']['STATUS']] : '#fff';
        $ret['attrs'] = ['style' => "background: {$color};"];
        */

        return $ret;
    }

    /**
     * Get row actions available for single row
     *
     * @param array $item
     * @return array
     */
    protected function getRowActions($item)
    {
        $ret = parent::getRowActions($item);

        foreach ($ret as $index => $action) {
            $ret[$index]['LINK']    = str_replace(['#ID#'], [$item['ID']], $action['LINK']);
            $ret[$index]['ONCLICK'] = str_replace(['#ID#'], [$item['ID']], $action['ONCLICK']);
        }

        return array_values($ret);
    }
}