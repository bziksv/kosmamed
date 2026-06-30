<?php
namespace Ipolh\SDEK\Admin;

use Ipolh\SDEK\Bitrix\Adapter;
use Ipolh\SDEK\Bitrix\Adapter\CourierCall;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\CourierCallsTable;
use Ipolh\SDEK\Admin\Grid\DatabaseGrid;

/**
 * Class CourierCallsGrid
 * @package Ipolh\SDEK\Admin
 */
class CourierCallsGrid extends DatabaseGrid
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
            'CAPTION' => 'TABLE_COURIER_CALLS_BTN_ADD_T_CONS',
            'TYPE'    => 'button',
            'ONCLICK' => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.newCall("'.CourierCall::TYPE_CONSOLIDATION.'");',
        ],
        [
            'CAPTION' => 'TABLE_COURIER_CALLS_BTN_ADD_T_ORDER',
            'TYPE'    => 'button',
            'ONCLICK' => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.newCall("'.CourierCall::TYPE_ORDER.'");',
        ],
        [
            'CAPTION' => 'TABLE_COURIER_CALLS_BTN_GET_STATUSES',
            'TYPE'    => 'button',
            'ONCLICK' => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.getStatuses(this);',
        ],
    ];

    /**
     * @var array
     */
    protected $defaultColumns = [
        [
            'id'          => 'ID',
            'name'        => 'TABLE_COURIER_CALLS_ID',
            'sort'        => 'ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
            'type'        => 'number',
        ],
        [
            'id'          => 'STATUS',
            'name'        => 'TABLE_COURIER_CALLS_STATUS',
            'sort'        => 'STATUS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'MESSAGE',
            'name'        => 'TABLE_COURIER_CALLS_MESSAGE',
            'sort'        => 'MESSAGE',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'INTAKE_NUMBER',
            'name'        => 'TABLE_COURIER_CALLS_INTAKE_NUMBER',
            'sort'        => 'INTAKE_NUMBER',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
            'type'        => 'number',
        ],
        [
            'id'          => 'TYPE',
            'name'        => 'TABLE_COURIER_CALLS_TYPE',
            'sort'        => 'TYPE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'CDEK_ORDER_ID',
            'name'        => 'TABLE_COURIER_CALLS_CDEK_ORDER_ID',
            'sort'        => 'CDEK_ORDER_ID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => true,
            'type'        => 'number',
        ],
        /*
        [
            'id'          => 'CDEK_ORDER_UUID',
            'name'        => 'TABLE_COURIER_CALLS_CDEK_ORDER_UUID',
            'sort'        => 'CDEK_ORDER_UUID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        */
        [
            'id'          => 'ACCOUNT',
            'name'        => 'TABLE_COURIER_CALLS_ACCOUNT',
            'sort'        => 'ACCOUNT',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'STORE_ID',
            'name'        => 'TABLE_COURIER_CALLS_STORE_ID',
            'sort'        => 'STORE_ID',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'NEED_CALL',
            'name'        => 'TABLE_COURIER_CALLS_NEED_CALL',
            'sort'        => 'NEED_CALL',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'POWER_OF_ATTORNEY',
            'name'        => 'TABLE_COURIER_CALLS_POWER_OF_ATTORNEY',
            'sort'        => 'POWER_OF_ATTORNEY',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'IDENTITY_CARD',
            'name'        => 'TABLE_COURIER_CALLS_IDENTITY_CARD',
            'sort'        => 'IDENTITY_CARD',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'COMMENT',
            'name'        => 'TABLE_COURIER_CALLS_COMMENT',
            'sort'        => 'COMMENT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'INTAKE_DATE',
            'name'        => 'TABLE_COURIER_CALLS_INTAKE_DATE',
            'sort'        => 'INTAKE_DATE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'INTAKE_TIME_FROM',
            'name'        => 'TABLE_COURIER_CALLS_INTAKE_TIME_FROM',
            'sort'        => 'INTAKE_TIME_FROM',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'INTAKE_TIME_TO',
            'name'        => 'TABLE_COURIER_CALLS_INTAKE_TIME_TO',
            'sort'        => 'INTAKE_TIME_TO',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'LUNCH_TIME_FROM',
            'name'        => 'TABLE_COURIER_CALLS_LUNCH_TIME_FROM',
            'sort'        => 'LUNCH_TIME_FROM',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'LUNCH_TIME_TO',
            'name'        => 'TABLE_COURIER_CALLS_LUNCH_TIME_TO',
            'sort'        => 'LUNCH_TIME_TO',
            'default'     => false,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'SENDER_COMPANY',
            'name'        => 'TABLE_COURIER_CALLS_SENDER_COMPANY',
            'sort'        => 'SENDER_COMPANY',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SENDER_NAME',
            'name'        => 'TABLE_COURIER_CALLS_SENDER_NAME',
            'sort'        => 'SENDER_NAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SENDER_PHONE_NUMBER',
            'name'        => 'TABLE_COURIER_CALLS_SENDER_PHONE_NUMBER',
            'sort'        => 'SENDER_PHONE_NUMBER',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'SENDER_PHONE_ADDITIONAL',
            'name'        => 'TABLE_COURIER_CALLS_SENDER_PHONE_ADDITIONAL',
            'sort'        => 'SENDER_PHONE_ADDITIONAL',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'FROM_LOCATION_CODE',
            'name'        => 'TABLE_COURIER_CALLS_FROM_LOCATION_CODE',
            'sort'        => 'FROM_LOCATION_CODE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'FROM_LOCATION_ADDRESS',
            'name'        => 'TABLE_COURIER_CALLS_FROM_LOCATION_ADDRESS',
            'sort'        => 'FROM_LOCATION_ADDRESS',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
            'quickSearch' => '%',
        ],
        [
            'id'          => 'PACK_NAME',
            'name'        => 'TABLE_COURIER_CALLS_PACK_NAME',
            'sort'        => 'PACK_NAME',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PACK_WEIGHT',
            'name'        => 'TABLE_COURIER_CALLS_PACK_WEIGHT',
            'sort'        => 'PACK_WEIGHT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PACK_LENGTH',
            'name'        => 'TABLE_COURIER_CALLS_PACK_LENGTH',
            'sort'        => 'PACK_LENGTH',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PACK_WIDTH',
            'name'        => 'TABLE_COURIER_CALLS_PACK_WIDTH',
            'sort'        => 'PACK_WIDTH',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'PACK_HEIGHT',
            'name'        => 'TABLE_COURIER_CALLS_PACK_HEIGHT',
            'sort'        => 'PACK_HEIGHT',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'INTAKE_UUID',
            'name'        => 'TABLE_COURIER_CALLS_INTAKE_UUID',
            'sort'        => 'INTAKE_UUID',
            'default'     => true,
            'editable'    => false,
            'filterable'  => '%',
        ],
        [
            'id'          => 'STATUS_CODE',
            'name'        => 'TABLE_COURIER_CALLS_STATUS_CODE',
            'sort'        => 'STATUS_CODE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'STATUS_DATE',
            'name'        => 'TABLE_COURIER_CALLS_STATUS_DATE',
            'sort'        => 'STATUS_DATE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'STATE_CODE',
            'name'        => 'TABLE_COURIER_CALLS_STATE_CODE',
            'sort'        => 'STATE_CODE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'STATE_DATE',
            'name'        => 'TABLE_COURIER_CALLS_STATE_DATE',
            'sort'        => 'STATE_DATE',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'OK',
            'name'        => 'TABLE_COURIER_CALLS_OK',
            'sort'        => 'OK',
            'default'     => true,
            'editable'    => false,
            'filterable'  => false,
        ],
        [
            'id'          => 'UPTIME',
            'name'        => 'TABLE_COURIER_CALLS_UPTIME',
            'sort'        => 'UPTIME',
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
            'TEXT'      => 'TABLE_COURIER_CALLS_ROW_EDIT',
            'ONCLICK'   => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.editCall("#ID#");',
        ],
        'STATUS' => [
            'ICONCLASS' => 'menu-popup-item-view',
            'TEXT'      => 'TABLE_COURIER_CALLS_ROW_STATUS',
            'ONCLICK'   => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.getStatus("#ID#");',
        ],
        'ERASE' => [
            'ICONCLASS' => 'menu-popup-item-delete',
            'TEXT'      => 'TABLE_COURIER_CALLS_ROW_ERASE',
            'ONCLICK'   => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.eraseCall("#ID#");',
        ],
        /*
        'DELETE' => [
            'ICONCLASS' => 'menu-popup-item-delete',
            'TEXT'      => 'TABLE_COURIER_CALLS_ROW_DELETE',
            'ONCLICK'   => IPOLH_SDEK_LBL.'controller.getPage("grid").actions.deleteCall("#ID#");',
        ],
        */
    ];

    /**
     * Return ORM data mapper for data selection
     *
     * @return \Bitrix\Main\ORM\Data\DataManager
     */
    public function getDataMapper()
    {
        return CourierCallsTable::class;
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

        $flagKeys = ['NEED_CALL', 'POWER_OF_ATTORNEY', 'IDENTITY_CARD'];
        foreach ($flagKeys as $key) {
            $ret['data'][$key] = ($ret['data'][$key] === 'Y') ? Tools::getMessage('TABLE_COURIER_CALLS_Y') : Tools::getMessage('TABLE_COURIER_CALLS_N');
        }

        switch($ret['data']['TYPE']) {
            case CourierCall::TYPE_ORDER:
                $ret['data']['TYPE'] = Tools::getMessage('LBL_callType_'.CourierCall::TYPE_ORDER);
                break;
            case CourierCall::TYPE_CONSOLIDATION:
                $ret['data']['TYPE'] = Tools::getMessage('LBL_callType_'.CourierCall::TYPE_CONSOLIDATION);
                break;
        }

        if (!empty($ret['data']['MESSAGE']))
            $ret['data']['MESSAGE'] = implode(', ', unserialize($ret['data']['MESSAGE'], ['allowed_classes' => false]));

        // Rows coloring by current order status
        // Beware:
        // - undocumented param 'attrs' used, version compatibility unknown
        // - drop .main-grid-cell background color required
        $statusToColor = array(
            // 'NEW'   => '#FFF',
            'OK'                    => '#E2FCE2',
            'WAIT'                  => '#FCFCBF',
            'ERROR'                 => '#FFEDED',

            'ACCEPTED'              => '#FCFCBF',
            'CREATED'               => '#E2FCE2',
            'REMOVED'               => '#E9E9E9',
            'READY_FOR_APPOINTMENT' => '#E2FCE2',
            'APPOINTED_COURIER'     => '#D9FFCE',

            'DONE'                  => '#ABFFAB',
            'PROBLEM_DETECTED'      => '#FFEDED',
            'PROCESSING_REQUIRED'   => '#E2FCE2',
            'INVALID'               => '#FFEDED',

            // #CACACA
            // #FCFFCE
        );
        $color = array_key_exists($ret['data']['STATUS'], $statusToColor) ? $statusToColor[$ret['data']['STATUS']] : '#fff';
        $ret['attrs'] = ['style' => "background: {$color};"];

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
        $status = $item['STATUS'];
        $ret = parent::getRowActions($item);

        if (in_array($status, ['NEW', 'REMOVED', 'DONE']))
            unset($ret['STATUS']);

        if (!in_array($status, ['NEW', 'ERROR', 'REMOVED']))
            unset($ret['ERASE']);

        /*
        if (!in_array($status, ['OK', 'CREATED']))
            unset($ret['DELETE']);
        */

        foreach ($ret as $index => $action) {
            $ret[$index]['LINK']    = str_replace(['#ID#'], [$item['ID']], $action['LINK']);
            $ret[$index]['ONCLICK'] = str_replace(['#ID#'], [$item['ID']], $action['ONCLICK']);
        }

        return array_values($ret);
    }
}