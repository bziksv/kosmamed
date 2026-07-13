<?php
namespace Ipolh\SDEK\Admin;

use Ipolh\SDEK\Bitrix\Adapter;
use Ipolh\SDEK\Bitrix\Adapter\CourierCall;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\CourierCallHandler;
use Ipolh\SDEK\StoreHandler;

/**
 * Class CourierCallsForm
 * @package Ipolh\SDEK\Admin
 */
class CourierCallsForm
{
    protected static $MODULE_ID  = IPOLH_SDEK;
    protected static $MODULE_LBL = IPOLH_SDEK_LBL;

    protected static $arButtons = [];

    /**
     * Create courier call form window
     */
    public static function makeFormWindow()
    {
        global $APPLICATION;
        $APPLICATION->SetAdditionalCSS(Tools::getJSPath().'jquery-ui.css');
        $APPLICATION->SetAdditionalCSS(Tools::getJSPath().'jquery-ui.structure.css');
        $APPLICATION->AddHeadScript(Tools::getJSPath().'jquery-ui.js');
        $APPLICATION->AddHeadScript(Tools::getJSPath().'wndController.js');
        $APPLICATION->AddHeadScript(Tools::getJSPath().'mask_input.js');

        $id = false;
        $data = CourierCallHandler::getCourierCallData($id);

        self::generateFormHtml($data);
        self::loadFormCSS();
        self::loadFormJS($data);
    }

    /**
     * Generate HTML for form window
     * @param CourierCall $data
     */
    protected static function generateFormHtml($data)
    {
        $coreAddress = $data->getStore()->getCoreAddress();
        $coreSender  = $data->getStore()->getCoreSender();

        // No merge because numeric keys used
        $storesList = [0 => ''] + \Ipolh\SDEK\StoreHandler::getActiveStores();
        $accountsList = [0 => ''] + \Ipolh\SDEK\CourierCallHandler::getActiveAccounts();

        ?>
        <div id="<?=self::$MODULE_LBL?>PLACEFORFORM">
            <table id="<?=self::$MODULE_LBL?>wndOrder">
                <tbody>
                <tr class="<?=self::$MODULE_LBL?>callId <?php if(empty($data->getId())) { echo self::$MODULE_LBL."hidden"; }?>">
                    <td><?=Tools::getMessage('LBL_ID')?></td><td id="<?=self::$MODULE_LBL?>callId"><?=$data->getId();?></td>
                </tr>
                <tr>
                    <td><?=Tools::getMessage('LBL_status')?></td><td id="<?=self::$MODULE_LBL?>status"><?=$data->getStatus();?></td>
                </tr>
                <tr>
                    <td></td><td id="<?=self::$MODULE_LBL?>statusSign"><?=Tools::getMessage('STATUS_COURIER_CALL_'.$data->getStatus());?></td>
                </tr>
                <tr class="<?=self::$MODULE_LBL?>intakeNumber <?php if(empty($data->getIntakeNumber())) { echo self::$MODULE_LBL."hidden"; }?>">
                    <td><?=Tools::getMessage('LBL_intakeNumber')?></td><td id="<?=self::$MODULE_LBL?>intakeNumber"><?=$data->getIntakeNumber();?></td>
                </tr>
                <tr class="<?=self::$MODULE_LBL?>message <?php if(empty($data->getMessage())) { echo self::$MODULE_LBL."hidden"; }?>">
                    <td colspan="2" class="<?=self::$MODULE_LBL?>warning" id="<?=self::$MODULE_LBL?>message"><?=implode('<br>', $data->getMessage() ?: [])?></td>
                </tr>
                <?php
                // Info
                ?>
                <?php Tools::placeFormHeaderRow('COURIER_CALL_INFO', self::$MODULE_LBL."controller.getPage('form').ui.toggleBlock('info')", empty($data->getIntakeUuid()) ? 'hidden' : '');?>
                <tr class="<?=self::$MODULE_LBL?>block_info">
                    <td><?=Tools::getMessage('LBL_intakeUuid')?></td><td id="<?=self::$MODULE_LBL?>intakeUuid"><?=$data->getIntakeUuid();?></td>
                </tr>
                <tr class="<?=self::$MODULE_LBL?>block_info">
                    <td><?=Tools::getMessage('LBL_statusCode')?></td><td id="<?=self::$MODULE_LBL?>statusCode"><?=$data->getStatusCode();?></td>
                </tr>
                <tr class="<?=self::$MODULE_LBL?>block_info">
                    <td><?=Tools::getMessage('LBL_statusDate')?></td><td id="<?=self::$MODULE_LBL?>statusDate"><?=$data->getStatusDate();?></td>
                </tr>
                <tr class="<?=self::$MODULE_LBL?>block_info">
                    <td><?=Tools::getMessage('LBL_stateCode')?></td><td id="<?=self::$MODULE_LBL?>stateCode"><?=$data->getStateCode();?></td>
                </tr>
                <tr class="<?=self::$MODULE_LBL?>block_info">
                    <td><?=Tools::getMessage('LBL_stateDate')?></td><td id="<?=self::$MODULE_LBL?>stateDate"><?=$data->getStateDate();?></td>
                </tr>
                <?php
                // Order
                ?>
                <?php Tools::placeFormHeaderRow('COURIER_CALL_ORDER');?>
                <?php
                $callTypes = [
                    CourierCall::TYPE_ORDER => Tools::getMessage('LBL_callType_'.CourierCall::TYPE_ORDER),
                    CourierCall::TYPE_CONSOLIDATION => Tools::getMessage('LBL_callType_'.CourierCall::TYPE_CONSOLIDATION)
                ];
                Tools::placeFormRow('callType', 'select', $data->getType(), $callTypes, "onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onCallTypeChange()\"");
                ?>
                <?php Tools::placeFormRow('orderId', 'text', $data->getOrderId());?>
                <tr class="<?=self::$MODULE_LBL?>type_order"><td colspan="2"><?=Tools::getMessage('MESS_TYPE_ORDER')?></td></tr>
                <tr class="<?=self::$MODULE_LBL?>type_consolidation"><td colspan="2"><?=Tools::getMessage('MESS_TYPE_CONSOLIDATION')?></td></tr>
                <tr><td colspan="2"><hr></td></tr>
                <?php Tools::placeFormRow('account', 'select', $data->getAccount() ?: 0, $accountsList);?>
                <?php Tools::placeFormRow('storeId', 'select', $data->getStore()->getId() ?: 0, $storesList, "onchange=\"".self::$MODULE_LBL."controller.getPage('form').act.changeStore()\"");?>
                <?php Tools::placeFormRow('needCall', 'checkbox', $coreSender->getField('needCall'));?>
                <?php Tools::placeFormRow('powerOfAttorney', 'checkbox', $coreSender->getField('powerOfAttorney'));?>
                <?php Tools::placeFormRow('identityCard', 'checkbox', $coreSender->getField('identityCard'));?>
                <?php Tools::placeFormRow('comment', 'textbox', $coreAddress->getComment());?>
                <?php
                // Date and time
                ?>
                <?php Tools::placeFormHeaderRow('COURIER_CALL_TIME');?>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_TIME_AFTER_15')?></td></tr>
                <tr><td colspan="2"><hr></td></tr>
                <tr>
                    <td><?=Tools::getMessage('LBL_intakeDate')?></td>
                    <td><?php
                        $intakeDate = \Bitrix\Main\Type\DateTime::createFromTimestamp($data->getIntakeDate());
                        $intakeDateSign = trim(substr($intakeDate, 0, strpos($intakeDate, ' ')));
                        ?>
                        <div class="adm-input-wrap adm-input-wrap-calendar">
                            <input type="hidden" id="<?=self::$MODULE_LBL?>intakeDate" name="<?=self::$MODULE_LBL?>intakeDate" value="<?=$data->getIntakeDate();?>">
                            <input class="adm-input adm-input-calendar" id="<?=self::$MODULE_LBL?>intakeDate_helper" disabled="" name="<?=self::$MODULE_LBL?>intakeDate_helper" size="22" type="text" value="<?=$intakeDateSign;?>">
                            <span class="adm-calendar-icon" onclick="BX.calendar({node: this, field: '<?=self::$MODULE_LBL?>intakeDate_helper', form: '', bTime: false, bHideTime: true, callback_after: <?=self::$MODULE_LBL?>controller.getPage('form').events.onIntakeDateChange}); <?=self::$MODULE_LBL?>controller.getPage('form').changeCalendar();"></span>
                        </div>
                    </td>
                </tr>
                <tr><td colspan="2"><span id="<?=self::$MODULE_LBL?>intakeTime_error" class="<?=self::$MODULE_LBL?>warning fat"></span></td></tr>
                <?php Tools::placeFormRow('intakeTimeFrom', 'text', $coreAddress->getField('intakeTimeFrom'), false, "style=\"max-width:50px;\" onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onTimeChange()\"");?>
                <tr>
                    <td></td><td><small><?=Tools::getMessage('SIGN_intakeTimeFrom')?></small></td>
                </tr>
                <?php Tools::placeFormRow('intakeTimeTo', 'text', $coreAddress->getField('intakeTimeTo'), false, "style=\"max-width:50px;\" onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onTimeChange()\"");?>
                <tr>
                    <td></td><td><small><?=Tools::getMessage('SIGN_intakeTimeTo')?></small></td>
                </tr>
                <tr><td colspan="2"><span id="<?=self::$MODULE_LBL?>lunchTime_error" class="<?=self::$MODULE_LBL?>warning fat"></span></td></tr>
                <?php Tools::placeFormRow('lunchTimeFrom', 'text', $coreAddress->getField('lunchTimeFrom'), false, "style=\"max-width:50px;\" onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onTimeChange()\"");?>
                <?php Tools::placeFormRow('lunchTimeTo', 'text', $coreAddress->getField('lunchTimeTo'), false, "style=\"max-width:50px;\" onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onTimeChange()\"");?>
                <?php
                // Sender
                ?>
                <?php Tools::placeFormHeaderRow('COURIER_CALL_SENDER', false, 'b_sender');?>
                <?php Tools::placeFormRow('company', 'text', $coreSender->getCompany(), false, false, 'b_sender');?>
                <?php Tools::placeFormRow('fullName', 'text', $coreSender->getFullName(), false, false, 'b_sender');?>
                <?php Tools::placeFormRow('phone', 'text', $coreSender->getPhone(), false, false, 'b_sender');?>
                <tr class="<?=self::$MODULE_LBL?>b_sender">
                    <td></td><td><small><?=Tools::getMessage('SIGN_phone')?></small></td>
                </tr>
                <?php Tools::placeFormRow('phoneAdditional', 'text', $coreSender->getField('phoneAdditional'), false, false, 'b_sender');?>
                <?php
                // Address
                ?>
                <?php Tools::placeFormHeaderRow('COURIER_CALL_ADDRESS', false, 'b_address');?>
                <tr class="<?=self::$MODULE_LBL?>b_address">
                    <td><label for="<?=self::$MODULE_LBL?>cityCode"><?=Tools::getMessage('LBL_cityCode')?></label></td>
                    <td>
                        <input type='text' id='<?=self::$MODULE_LBL?>cityCode_sign' value='<?=implode(', ', [$coreAddress->getRegion(), $coreAddress->getCity()])?>' style='max-width:300px;'>
                        <input type='hidden' id='<?=self::$MODULE_LBL?>cityCode' value='<?=$coreAddress->getCode()?>'>
                    </td>
                </tr>
                <?php Tools::placeFormRow('address', 'textbox', $coreAddress->getLine(), false, false, 'b_address');?>
                <?php
                // Pack
                ?>
                <?php Tools::placeFormHeaderRow('COURIER_CALL_PACK', false, 'b_pack');?>
                <?php Tools::placeFormRow('packName', 'text', $data->getPack()->getDetails(), false, false, 'b_pack');?>
                <?php Tools::placeFormRow('packWeight', 'text', $data->getPack()->getWeight(), false, "style=\"max-width:50px;\" onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onPackChange('packWeight')\"", 'b_pack');?>
                <?php Tools::placeFormRow('packLength', 'text', $data->getPack()->getLength(), false, "style=\"max-width:50px;\" onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onPackChange('packLength')\"", 'b_pack');?>
                <?php Tools::placeFormRow('packWidth', 'text', $data->getPack()->getWidth(), false, "style=\"max-width:50px;\" onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onPackChange('packWidth')\"", 'b_pack');?>
                <?php Tools::placeFormRow('packHeight', 'text', $data->getPack()->getHeight(), false, "style=\"max-width:50px;\" onchange=\"".self::$MODULE_LBL."controller.getPage('form').events.onPackChange('packHeight')\"", 'b_pack');?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Adds form JS
     * @param CourierCall $data
     */
    protected static function loadFormJS($data)
    {
        /**
         * Admin interface JS controller added in \admin\ipol_sdek_courier_calls.php
         */
        ?>
        <script type="text/javascript">
            <?=self::$MODULE_LBL?>controller.expander({
                callId: '<?=$data->getId()?>',
                status: '<?=$data->getStatus()?>',
                senderCities: [<?=StoreHandler::getSenderCitiesJS()?>],
                storeId: <?=($data->getStore()->getId() ?: 0)?>,
            });

            <?=self::$MODULE_LBL?>controller.addPage('form', {
                mainWnd: false,

                init: function(){
                    var html = $('#<?=self::$MODULE_LBL?>PLACEFORFORM').html();
                    $('#<?=self::$MODULE_LBL?>PLACEFORFORM').html(' ');

                    if (!html) {
                        this.self.log('Unable to load data');
                    } else {
                        <?php self::addButton("<input id='".self::$MODULE_LBL."save' type='button' onclick='".self::$MODULE_LBL."controller.getPage(\"form\").save()' value='".Tools::getMessage('BTN_SAVE')."'>");?>
                        <?php self::addButton("<input id='".self::$MODULE_LBL."check' type='button' onclick='".self::$MODULE_LBL."controller.getPage(\"form\").check()' value='".Tools::getMessage('BTN_CHECK')."'>");?>
                        <?php self::addButton("<input id='".self::$MODULE_LBL."close' type='button' onclick='".self::$MODULE_LBL."controller.getPage(\"form\").close()' value='".Tools::getMessage('BTN_CLOSE')."'>");?>

                        this.mainWnd = new ipol_sdek_wndController({
                            title: '<?=Tools::getMessage('HDR_COURIER_CALL_WND')?>',
                            content: html,
                            resizable: true,
                            draggable: true,
                            height: '500',
                            width: '515',
                            buttons: <?=\CUtil::PhpToJSObject(self::$arButtons)?>
                        });

                        $('#<?=self::$MODULE_LBL?>intakeTimeFrom').mask('29:59');
                        $('#<?=self::$MODULE_LBL?>intakeTimeTo').mask('29:59');
                        $('#<?=self::$MODULE_LBL?>lunchTimeFrom').mask('29:59');
                        $('#<?=self::$MODULE_LBL?>lunchTimeTo').mask('29:59');

                        $('#<?=self::$MODULE_LBL?>cityCode_sign').autocomplete({
                            source: this.self.senderCities,
                            select: function(ev, ui){
                                <?=self::$MODULE_LBL?>controller.getPage('form').events.onSenderCityChange(ev, ui);
                            }
                        });
                    }

                    // Reload methods: just to be sure
                    this.act(this);
                    this.events(this);
                    this.onSave(this);
                    this.onCheck(this);
                    this.onEdit(this);

                    this.events.onCallTypeChange();
                    this.events.onTimeChange();
                },
                open: function(){
                    let btnSave  = $('#<?=self::$MODULE_LBL?>save');
                    let btnCheck = $('#<?=self::$MODULE_LBL?>check');
                    let btnClose = $('#<?=self::$MODULE_LBL?>close');

                    switch(this.self.status) {
                        case 'NEW':
                            btnSave.show();
                            btnCheck.hide();
                            btnClose.hide();
                            break;
                        case 'ERROR':
                            btnSave.show();
                            btnCheck.show();
                            btnClose.hide();
                            break;
                        case 'WAIT':
                            btnSave.hide();
                            btnCheck.show();
                            btnClose.hide();
                            break;
                        case 'OK':
                        default:
                            btnSave.hide();
                            btnCheck.hide();
                            btnClose.show();
                            break;
                    }

                    if (this.mainWnd)
                        this.mainWnd.open();
                },
                close: function(){
                    this.mainWnd.close();
                },
                // BX calendar mod
                changeCalendar: function(){
                    let block = $('[id ^= "calendar_popup_"]'); // calendar
                    let links = block.find(".bx-calendar-cell"); // days elements

                    $('.bx-calendar-left-arrow').attr({'onclick': '<?=self::$MODULE_LBL?>controller.getPage("form").changeCalendar();',});
                    $('.bx-calendar-right-arrow').attr({'onclick': '<?=self::$MODULE_LBL?>controller.getPage("form").changeCalendar();',});
                    $('.bx-calendar-top-month').attr({'onclick': '<?=self::$MODULE_LBL?>controller.getPage("form").changeCalendarMonth();',});
                    $('.bx-calendar-top-year').attr({'onclick': '<?=self::$MODULE_LBL?>controller.getPage("form").changeCalendarYear();',});

                    let date = new Date();
                    for (let i = 0; i < links.length; i++) {
                        let atrDate = links[i].attributes['data-date'].value;
                        let linkDate = new Date(parseInt(atrDate));

                        if (date - atrDate > 24 * 60 * 60 * 1000) {
                            // Skip old good days
                            $('[data-date="' + atrDate +'"]').addClass("bx-calendar-date-hidden disabled");
                        }
                    }
                },
                changeCalendarMonth: function(){
                    let block = $('[id ^= "calendar_popup_month_"]');
                    let links = block.find(".bx-calendar-month");
                    let year = $('[id ^= "calendar_popup_"]').find('.bx-calendar-top-year').html();

                    let currentDate = new Date();

                    for (let i = 0; i < links.length; i++) {
                        let month = links[i].attributes['data-bx-month'].value;

                        if (currentDate.getFullYear() >= parseInt(year) && currentDate.getMonth() > month) {
                            $('[data-bx-month="' + month +'"]').addClass("disabled");
                        } else {
                            if ($('[data-bx-month="' + month +'"]').hasClass("disabled"))
                                $('[data-bx-month="' + month +'"]').removeClass("disabled");
                            $(links[i]).attr({'onclick': 'setTimeout(<?=self::$MODULE_LBL?>controller.getPage("form").changeCalendar, 200)',});
                        }
                    }
                },
                changeCalendarYear: function(){
                    let block = $('[id ^= "calendar_popup_year_"]');

                    let link = block.find(".bx-calendar-year-input"); // Hide year input
                    $(link).css('display', 'none');

                    let links = block.find(".bx-calendar-year-number");
                    let currentDate = new Date();

                    for (let i = 0; i < links.length; i++) {
                        let year = links[i].attributes['data-bx-year'].value;
                        if (year < currentDate.getFullYear())
                            $('[data-bx-year="' + year +'"]').addClass("disabled");
                        else
                            $(links[i]).attr({'onclick': 'setTimeout(<?=self::$MODULE_LBL?>controller.getPage("form").changeCalendar, 200)',});
                    }
                },
                save: function(){
                    $('#<?=self::$MODULE_LBL?>save').hide();
                    $('.<?=self::$MODULE_LBL?>errInput').removeClass('<?=self::$MODULE_LBL?>errInput');
                    var data = this.getInputs();

                    if (data.success) {
                        this.self.ajax({
                            data: this.self.concatObj(data.inputs, {
                                isdek_action: 'uploadCourierCall',
                                isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                callId:     this.self.callId,
                            }),
                            dataType: 'json',
                            success: this.onSave
                        });
                    } else {
                        var alertStr = "<?=Tools::getMessage('MESS_COURIER_CALL_NOT_SAVED')?>\n\n<?=Tools::getMessage('MESS_COURIER_CALL_FILL')?>";
                        var headerDiff = {};
                        for (var i in data.errors) {
                            var handler = $('#<?=self::$MODULE_LBL?>' + i);
                            handler.addClass('<?=self::$MODULE_LBL?>errInput');

                            switch (i) {
                                default:
                                    handler = handler.parent().parent();
                                    break;
                            }

                            var label = (handler.children(':first-child').find('label').length) ? handler.children(':first-child').find('label').text().trim() : handler.children(':first-child').text().trim();
                            var header = false;
                            var iter = 0;

                            while (!header && iter < 30) {
                                if (handler.prev('.heading').length)
                                    header = handler.prev('.heading').text().trim();
                                else
                                    handler = handler.prev();
                                iter++;
                            }
                            if (typeof(headerDiff[header]) === 'undefined')
                                headerDiff[header] = {};
                            headerDiff[header][label] = label;
                        }
                        for (var i in headerDiff) {
                            alertStr += "\n" + i + ": ";
                            for (var j in headerDiff[i]) {
                                alertStr += j + ", ";
                            }
                            alertStr = alertStr.substring(0, alertStr.length - 2);
                        }
                        alert(alertStr);
                        $('#<?=self::$MODULE_LBL?>save').css('display', '');
                    }
                },
                getInputs: function(giveAnyway){
                    var depths = this.dependences();

                    var data = {
                        inputs: {},
                        errors: {}
                    };

                    for (var i in depths) {
                        if (typeof(depths[i].need) !== 'undefined') {
                            var preVal = $('#<?=self::$MODULE_LBL?>' + i).val();
                            if ($('#<?=self::$MODULE_LBL?>' + i).attr('type') === 'checkbox')
                                preVal = ($('#<?=self::$MODULE_LBL?>' + i).prop('checked')) ? true : false;
                            if (typeof(depths[i].link) !== 'undefined') {
                                var checkVal = $('#<?=self::$MODULE_LBL?>' + depths[i].link).val();
                                if ($('#<?=self::$MODULE_LBL?>' + depths[i].link).attr('type') === 'checkbox')
                                    checkVal = ($('#<?=self::$MODULE_LBL?>' + i).prop('checked')) ? true : false;
                            }
                            switch (depths[i].need) {
                                case 'dep' :
                                    if (preVal)
                                        data.inputs[i] = preVal;
                                    else if (!checkVal)
                                        data.errors[i] = i;
                                    break;
                                case 'sub' :
                                    var need = (typeof(depths[i].checkVal) !== 'undefined') ? (checkVal === depths[i].checkVal) : checkVal;
                                    if (need) {
                                        if (preVal)
                                            data.inputs[i] = preVal;
                                        else
                                            data.errors[i] = i;
                                    }
                                    break;
                                case 'posInt' :
                                    let tmpVal = parseInt(preVal, 10);
                                    if (!isNaN(tmpVal) && tmpVal > 0)
                                        data.inputs[i] = tmpVal;
                                    else
                                        data.errors[i] = i;
                                    break;
                                case true :
                                    if (preVal)
                                        data.inputs[i] = preVal;
                                    else
                                        data.errors[i] = i;
                                    break;
                                case false :
                                    if (preVal)
                                        data.inputs[i] = preVal;
                                    break;
                            }
                        }
                    }

                    if (this.self.isEmpty(data.errors) || (typeof(giveAnyway) !== 'undefined' && giveAnyway))
                        return {success: true, inputs: data.inputs};
                    else
                        return {success: false, errors: data.errors};
                },
                onSave: (function(self){
                    self.onSave = function(data){
                        if (data.status === 'ERROR') {
                            alert('<?=Tools::getMessage('MESS_COURIER_CALL_NOT_SENDED')?>' + "\n\n" + data.errors);
                            $('#<?=self::$MODULE_LBL?>save').css('display', '');
                        } else {
                            let str = '';
                            if (data.errors.length) {
                                str = '<?=Tools::getMessage('MESS_COURIER_CALL_BAD_SENDED')?>' + "\n\n" + data.errors;
                            } else if (data.status === 'WAIT') {
                                str = '<?=Tools::getMessage('MESS_COURIER_CALL_WAIT_SENDED')?>';
                            } else if (data.intake_number !== false) {
                                str = '<?=Tools::getMessage('MESS_COURIER_CALL_SENDED')?>' + data.intake_number;
                            }

                            alert(str);
                            self.mainWnd.close();
                            window.location.reload();
                        }
                    };
                }),
                check: function() {
                    $('#<?=self::$MODULE_LBL?>check').hide();

                    this.self.ajax({
                        data: {
                            isdek_action: 'getCourierCallStateRequest',
                            isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                            callId:       this.self.callId,
                        },
                        dataType: 'json',
                        success: this.onCheck
                    });
                },
                onCheck: (function(self){
                    self.onCheck = function(data){
                        let str = '';

                        if (data.intake_number) {
                            str = '<?=Tools::getMessage('MESS_COURIER_CALL_CHECKED')?>' + data.intake_number;
                        } else {
                            if (data.errors.length) {
                                str = '<?=Tools::getMessage('MESS_COURIER_CALL_BAD_CHECKED')?>' + "\n\n" + data.errors;
                            } else {
                                str = '<?=Tools::getMessage('MESS_COURIER_CALL_WAIT_CHECKED')?>';
                            }
                        }

                        alert(str);
                        self.mainWnd.close();
                        window.location.reload();
                    };
                }),
                onEdit: (function(self){
                    self.onEdit = function(data){
                        //console.log(data);
                        $('.<?=self::$MODULE_LBL?>errInput').removeClass('<?=self::$MODULE_LBL?>errInput');

                        let newId = !isNaN(parseInt(data.callId, 10)) ? parseInt(data.callId, 10) : '';
                        this.self.callId = newId;
                        $('#<?=self::$MODULE_LBL?>callId').html(newId);
                        if (newId) {
                            $('.<?=self::$MODULE_LBL?>callId').removeClass('<?=self::$MODULE_LBL?>hidden');
                        } else {
                            if (!($('.<?=self::$MODULE_LBL?>callId').hasClass('<?=self::$MODULE_LBL?>hidden')))
                                $('.<?=self::$MODULE_LBL?>callId').addClass('<?=self::$MODULE_LBL?>hidden');
                        }

                        this.self.status = data.status;
                        $('#<?=self::$MODULE_LBL?>status').html(data.status);
                        $('#<?=self::$MODULE_LBL?>statusSign').html(data.statusSign);

                        $('#<?=self::$MODULE_LBL?>intakeNumber').html(data.intakeNumber);
                        if (data.intakeNumber) {
                            $('.<?=self::$MODULE_LBL?>intakeNumber').removeClass('<?=self::$MODULE_LBL?>hidden');
                        } else {
                            if (!($('.<?=self::$MODULE_LBL?>intakeNumber').hasClass('<?=self::$MODULE_LBL?>hidden')))
                                $('.<?=self::$MODULE_LBL?>intakeNumber').addClass('<?=self::$MODULE_LBL?>hidden');
                        }

                        let message = '';
                        if (typeof(data.message) !== 'undefined') {
                            for (var i in data.message)
                                message += data.message[i] + '<br>';
                        }
                        $('#<?=self::$MODULE_LBL?>message').html(message);
                        if (message) {
                            $('.<?=self::$MODULE_LBL?>message').removeClass('<?=self::$MODULE_LBL?>hidden');
                        } else {
                            if (!($('.<?=self::$MODULE_LBL?>message').hasClass('<?=self::$MODULE_LBL?>hidden')))
                                $('.<?=self::$MODULE_LBL?>message').addClass('<?=self::$MODULE_LBL?>hidden');
                        }

                        let infoHdr = $('.<?=self::$MODULE_LBL?>message').next();
                        if (data.intakeUuid) {
                            infoHdr.removeClass('<?=self::$MODULE_LBL?>hidden');
                        } else {
                            if (!(infoHdr.hasClass('<?=self::$MODULE_LBL?>hidden')))
                                infoHdr.addClass('<?=self::$MODULE_LBL?>hidden');
                        }

                        $('#<?=self::$MODULE_LBL?>intakeUuid').html(data.intakeUuid);
                        $('#<?=self::$MODULE_LBL?>statusCode').html(data.statusCode);
                        $('#<?=self::$MODULE_LBL?>statusDate').html(data.statusDateSign);
                        $('#<?=self::$MODULE_LBL?>stateCode').html(data.stateCode);
                        $('#<?=self::$MODULE_LBL?>stateDate').html(data.stateDateSign);

                        $('#<?=self::$MODULE_LBL?>callType').val(data.type);
                        $('#<?=self::$MODULE_LBL?>orderId').val(data.orderId);

                        $('#<?=self::$MODULE_LBL?>account').val(data.account);
                        $('#<?=self::$MODULE_LBL?>storeId').val(data.storeId);
                        $('#<?=self::$MODULE_LBL?>needCall').prop('checked', data.needCall);
                        $('#<?=self::$MODULE_LBL?>powerOfAttorney').prop('checked', data.powerOfAttorney);
                        $('#<?=self::$MODULE_LBL?>identityCard').prop('checked', data.identityCard);
                        $('#<?=self::$MODULE_LBL?>comment').html(data.comment);

                        $('#<?=self::$MODULE_LBL?>intakeDate').val(parseInt(data.intakeDate));
                        $('#<?=self::$MODULE_LBL?>intakeDate_helper').val(data.intakeDateSign);
                        $('#<?=self::$MODULE_LBL?>intakeTimeFrom').val(data.intakeTimeFrom);
                        $('#<?=self::$MODULE_LBL?>intakeTimeTo').val(data.intakeTimeTo);
                        $('#<?=self::$MODULE_LBL?>lunchTimeFrom').val(data.lunchTimeFrom);
                        $('#<?=self::$MODULE_LBL?>lunchTimeTo').val(data.lunchTimeTo);

                        $('#<?=self::$MODULE_LBL?>company').val(data.company);
                        $('#<?=self::$MODULE_LBL?>fullName').val(data.fullName);
                        $('#<?=self::$MODULE_LBL?>phone').val(data.phone);
                        $('#<?=self::$MODULE_LBL?>phoneAdditional').val(data.phoneAdditional);

                        $('#<?=self::$MODULE_LBL?>cityCode_sign').val(data.region + ', ' + data.city);
                        $('#<?=self::$MODULE_LBL?>cityCode').val(data.cityCode);
                        $('#<?=self::$MODULE_LBL?>address').html(data.address);

                        $('#<?=self::$MODULE_LBL?>packName').val(data.packName);
                        $('#<?=self::$MODULE_LBL?>packWeight').val(!isNaN(parseInt(data.packWeight, 10)) ? parseInt(data.packWeight, 10) : '');
                        $('#<?=self::$MODULE_LBL?>packLength').val(!isNaN(parseInt(data.packLength, 10)) ? parseInt(data.packLength, 10) : '');
                        $('#<?=self::$MODULE_LBL?>packWidth').val(!isNaN(parseInt(data.packWidth, 10)) ? parseInt(data.packWidth, 10) : '');
                        $('#<?=self::$MODULE_LBL?>packHeight').val(!isNaN(parseInt(data.packHeight, 10)) ? parseInt(data.packHeight, 10) : '');

                        self.events.onCallTypeChange();

                        this.open();
                    };
                }),
                dependences: function(){
                    return {
                        callType:        {need: true},
                        orderId:         {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_ORDER?>'},

                        account:         {need: 'posInt'},
                        storeId:         {need: false},
                        needCall:        {need: false},
                        powerOfAttorney: {need: false},
                        identityCard:    {need: false},
                        comment:         {need: false},

                        intakeDate:      {need: true},
                        intakeTimeFrom:  {need: true},
                        intakeTimeTo:    {need: true},
                        lunchTimeFrom:   {need: false},
                        lunchTimeTo:     {need: false},

                        company:         {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                        fullName:        {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                        phone:           {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                        phoneAdditional: {need: false},

                        cityCode:        {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                        address:         {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},

                        packName:        {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                        packWeight:      {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                        packLength:      {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                        packWidth:       {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                        packHeight:      {need: 'sub', link: 'callType', checkVal: '<?=CourierCall::TYPE_CONSOLIDATION?>'},
                    };
                },

                act: (function(self){
                    self.act = {
                        changeStore: function(){
                            self.self.ajax({
                                data: {
                                    isdek_action: 'loadStoreRequest',
                                    isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                    storeId:      $('#<?=self::$MODULE_LBL?>storeId').val(),
                                },
                                dataType: 'json',
                                success:  self.events.onChangeStore
                            });
                        },
                    }
                }),

                events: (function(self){
                    self.events = {
                        onChangeStore: function(data){
                            if (data.success){
                                let store = data.data;

                                $('.<?=self::$MODULE_LBL?>errInput').removeClass('<?=self::$MODULE_LBL?>errInput');

                                self.self.storeId = (store.storeId == null) ? 0 : parseInt(store.storeId);
                                $('#<?=self::$MODULE_LBL?>storeId').val(self.self.storeId);

                                $('#<?=self::$MODULE_LBL?>needCall').prop('checked', store.needCall);
                                $('#<?=self::$MODULE_LBL?>powerOfAttorney').prop('checked', store.powerOfAttorney);
                                $('#<?=self::$MODULE_LBL?>identityCard').prop('checked', store.identityCard);
                                $('#<?=self::$MODULE_LBL?>comment').html(store.comment);

                                $('#<?=self::$MODULE_LBL?>intakeTimeFrom').val(store.intakeTimeFrom);
                                $('#<?=self::$MODULE_LBL?>intakeTimeTo').val(store.intakeTimeTo);
                                $('#<?=self::$MODULE_LBL?>lunchTimeFrom').val(store.lunchTimeFrom);
                                $('#<?=self::$MODULE_LBL?>lunchTimeTo').val(store.lunchTimeTo);

                                $('#<?=self::$MODULE_LBL?>company').val(store.company);
                                $('#<?=self::$MODULE_LBL?>fullName').val(store.fullName);
                                $('#<?=self::$MODULE_LBL?>phone').val(store.phone);
                                $('#<?=self::$MODULE_LBL?>phoneAdditional').val(store.phoneAdditional);

                                $('#<?=self::$MODULE_LBL?>cityCode_sign').val(store.region + ', ' + store.city);
                                $('#<?=self::$MODULE_LBL?>cityCode').val(store.cityCode);
                                $('#<?=self::$MODULE_LBL?>address').html(store.address);
                            } else {
                                var str = '<?=Tools::getMessage('MESS_STORE_NOT_LOADED')?>';
                                if (data.errors.length) {
                                    str += "\n" + data.errors;
                                }
                                alert(str);
                            }
                        },
                        onCallTypeChange: function(){
                            var orderId = $('#<?=self::$MODULE_LBL?>orderId');

                            var company = $('#<?=self::$MODULE_LBL?>company');
                            var fullName = $('#<?=self::$MODULE_LBL?>fullName');
                            var phone = $('#<?=self::$MODULE_LBL?>phone');
                            var phoneAdditional = $('#<?=self::$MODULE_LBL?>phoneAdditional');

                            var cityCode_sign = $('#<?=self::$MODULE_LBL?>cityCode_sign');
                            var address = $('#<?=self::$MODULE_LBL?>address');

                            var packName = $('#<?=self::$MODULE_LBL?>packName');
                            var packWeight = $('#<?=self::$MODULE_LBL?>packWeight');
                            var packLength = $('#<?=self::$MODULE_LBL?>packLength');
                            var packWidth = $('#<?=self::$MODULE_LBL?>packWidth');
                            var packHeight = $('#<?=self::$MODULE_LBL?>packHeight');

                            switch ($('#<?=self::$MODULE_LBL?>callType').val()) {
                                case '<?=CourierCall::TYPE_ORDER?>':
                                default:
                                    orderId.removeAttr('readonly');
                                    orderId.parent().parent().show();

                                    $('.<?=self::$MODULE_LBL?>type_order').show();
                                    $('.<?=self::$MODULE_LBL?>type_consolidation').hide();

                                    company.attr('readonly', 'readonly');
                                    fullName.attr('readonly', 'readonly');
                                    phone.attr('readonly', 'readonly');
                                    phoneAdditional.attr('readonly', 'readonly');

                                    cityCode_sign.attr('readonly', 'readonly');
                                    address.attr('readonly', 'readonly');

                                    packName.attr('readonly', 'readonly');
                                    packWeight.attr('readonly', 'readonly');
                                    packLength.attr('readonly', 'readonly');
                                    packWidth.attr('readonly', 'readonly');
                                    packHeight.attr('readonly', 'readonly');

                                    $('.<?=self::$MODULE_LBL?>b_sender').hide();
                                    $('.<?=self::$MODULE_LBL?>b_address').hide();
                                    $('.<?=self::$MODULE_LBL?>b_pack').hide();

                                    break;
                                case '<?=CourierCall::TYPE_CONSOLIDATION?>':
                                    orderId.attr('readonly', 'readonly');
                                    orderId.parent().parent().hide();

                                    $('.<?=self::$MODULE_LBL?>type_order').hide();
                                    $('.<?=self::$MODULE_LBL?>type_consolidation').show();

                                    company.removeAttr('readonly');
                                    fullName.removeAttr('readonly');
                                    phone.removeAttr('readonly');
                                    phoneAdditional.removeAttr('readonly');

                                    cityCode_sign.removeAttr('readonly');
                                    address.removeAttr('readonly');

                                    packName.removeAttr('readonly');
                                    packWeight.removeAttr('readonly');
                                    packLength.removeAttr('readonly');
                                    packWidth.removeAttr('readonly');
                                    packHeight.removeAttr('readonly');

                                    $('.<?=self::$MODULE_LBL?>b_sender').show();
                                    $('.<?=self::$MODULE_LBL?>b_address').show();
                                    $('.<?=self::$MODULE_LBL?>b_pack').show();

                                    break;
                            }
                        },
                        onIntakeDateChange: function(val){
                            $('#<?=self::$MODULE_LBL?>intakeDate').val(parseInt(val.getTime()/1000));
                        },
                        onTimeChange: function(){
                            var intakeTimeError = $('#<?=self::$MODULE_LBL?>intakeTime_error');
                            var lunchTimeError  = $('#<?=self::$MODULE_LBL?>lunchTime_error');

                            var intakeTimeFrom = $('#<?=self::$MODULE_LBL?>intakeTimeFrom');
                            var intakeTimeTo   = $('#<?=self::$MODULE_LBL?>intakeTimeTo');
                            var lunchTimeFrom  = $('#<?=self::$MODULE_LBL?>lunchTimeFrom');
                            var lunchTimeTo    = $('#<?=self::$MODULE_LBL?>lunchTimeTo');

                            var intakeCheck = checkIntakeTime(intakeTimeFrom.val(), intakeTimeTo.val());
                            var intakeEmpty = !intakeTimeFrom.val() && !intakeTimeTo.val();

                            if (intakeCheck.success === true || intakeEmpty) {
                                intakeTimeFrom.removeClass('<?=self::$MODULE_LBL?>errInput');
                                intakeTimeTo.removeClass('<?=self::$MODULE_LBL?>errInput');

                                intakeTimeError.html("");
                                intakeTimeError.addClass('<?=self::$MODULE_LBL?>hidden');

                                if (intakeEmpty) {
                                    lunchTimeFrom.attr('disabled', 'disabled');
                                    lunchTimeTo.attr('disabled', 'disabled');
                                } else {
                                    lunchTimeFrom.removeAttr('disabled');
                                    lunchTimeTo.removeAttr('disabled');

                                    var lunchCheck = checkLunchTime(lunchTimeFrom.val(), lunchTimeTo.val(), intakeTimeFrom.val(), intakeTimeTo.val());
                                    if (lunchCheck.success === true || (!lunchTimeFrom.val() && !lunchTimeTo.val())) {
                                        lunchTimeFrom.removeClass('<?=self::$MODULE_LBL?>errInput');
                                        lunchTimeTo.removeClass('<?=self::$MODULE_LBL?>errInput');

                                        lunchTimeError.html("");
                                        lunchTimeError.addClass('<?=self::$MODULE_LBL?>hidden');
                                    } else {
                                        if (lunchCheck.error === 'from' || lunchCheck.error === 'both')
                                            lunchTimeFrom.addClass('<?=self::$MODULE_LBL?>errInput');

                                        if (lunchCheck.error === 'to' || lunchCheck.error === 'both')
                                            lunchTimeTo.addClass('<?=self::$MODULE_LBL?>errInput');

                                        lunchTimeError.html(lunchCheck.message);
                                        lunchTimeError.removeClass('<?=self::$MODULE_LBL?>hidden');
                                    }
                                }
                            } else {
                                if (intakeCheck.error === 'from' || intakeCheck.error === 'both')
                                    intakeTimeFrom.addClass('<?=self::$MODULE_LBL?>errInput');

                                if (intakeCheck.error === 'to' || intakeCheck.error === 'both')
                                    intakeTimeTo.addClass('<?=self::$MODULE_LBL?>errInput');

                                intakeTimeError.html(intakeCheck.message);
                                intakeTimeError.removeClass('<?=self::$MODULE_LBL?>hidden');

                                lunchTimeFrom.attr('disabled', 'disabled');
                                lunchTimeTo.attr('disabled', 'disabled');
                            }

                            function checkLunchTime(from, to, intakeFrom, intakeTo){
                                var result = {success: false, error: '', message: ''};

                                if (!from) {
                                    result.error = 'from';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_FILL_FROM')?>';
                                    return result;
                                }
                                if (!to) {
                                    result.error = 'to';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_FILL_TO')?>';
                                    return result;
                                }

                                intakeFrom = intakeFrom.split(':');
                                intakeFrom[0] = parseInt(intakeFrom[0]);
                                intakeFrom[1] = parseInt(intakeFrom[1]);
                                var intakeFromM = intakeFrom[0]*60 + intakeFrom[1];

                                intakeTo = intakeTo.split(':');
                                intakeTo[0] = parseInt(intakeTo[0]);
                                intakeTo[1] = parseInt(intakeTo[1]);
                                var intakeToM = intakeTo[0]*60 + intakeTo[1];

                                from = from.split(':');
                                from[0] = parseInt(from[0]);
                                from[1] = parseInt(from[1]);
                                var fromM = from[0]*60 + from[1];

                                if (fromM < intakeFromM || fromM > intakeToM) {
                                    result.error = 'from';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_BAD_LUNCH_FROM')?>';
                                    return result;
                                }

                                to = to.split(':');
                                to[0] = parseInt(to[0]);
                                to[1] = parseInt(to[1]);
                                var toM = to[0]*60 + to[1];
                                if (toM < intakeFromM || toM > intakeToM) {
                                    result.error = 'to';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_BAD_LUNCH_TO')?>';
                                    return result;
                                }

                                if (toM - fromM < 1) {
                                    result.error = 'both';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_BAD_LUNCH_BOTH')?>';
                                    return result;
                                }

                                result.success = true;
                                return result;
                            }

                            function checkIntakeTime(from, to){
                                var result = {success: false, error: '', message: ''};

                                if (!from) {
                                    result.error = 'from';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_FILL_FROM')?>';
                                    return result;
                                }
                                if (!to) {
                                    result.error = 'to';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_FILL_TO')?>';
                                    return result;
                                }

                                from = from.split(':');
                                from[0] = parseInt(from[0]);
                                from[1] = parseInt(from[1]);
                                if (from[0] < 9) {
                                    result.error = 'from';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_BAD_INTAKE_FROM')?>';
                                    return result;
                                }

                                to = to.split(':');
                                to[0] = parseInt(to[0]);
                                to[1] = parseInt(to[1]);
                                if (to[0] > 22 || (to[0] === 22 && to[1])) {
                                    result.error = 'to';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_BAD_INTAKE_TO')?>';
                                    return result;
                                }

                                if ((to[0] - from[0])*60 + to[1] - from[1] < 180) {
                                    result.error = 'both';
                                    result.message = '<?=Tools::getMessage('MESS_TIME_BAD_INTAKE_BOTH')?>';
                                    return result;
                                }

                                result.success = true;
                                return result;
                            }
                        },
                        onSenderCityChange: function(ev, ui){
                            window.setTimeout(function(){
                                $(arguments[0]).val(arguments[1]);
                            }, 100, ev.target, ui.item.label);

                            $(ev.target).siblings("[type='hidden']").val(ui.item.value);
                        },
                        onPackChange: function(id){
                            let packField = $('#<?=self::$MODULE_LBL?>' + id);
                            let tmpVal = parseInt(packField.val(), 10);

                            if (!isNaN(tmpVal) && tmpVal > 0)
                                packField.val(tmpVal);
                            else
                                packField.val('');
                        },
                    }
                }),
                ui: {
                    toggleBlock: function(code){
                        $('.<?=self::$MODULE_LBL?>block_' + code).toggle();
                    },
                    makeUnseen: function(wat, mode){
                        if (mode) {
                            wat.addClass('<?=self::$MODULE_LBL?>hidden');
                        } else {
                            wat.removeClass('<?=self::$MODULE_LBL?>hidden');
                        }
                    },
                },
            });
        </script>
        <?php
    }

    /**
     * Adds form CSS
     */
    protected static function loadFormCSS()
    {
        Tools::getCommonCss();
        ?>
        <style>
            /* BX calendar mod */
            .bx-calendar-month-content .disabled {
                pointer-events: none;
                color: #ccc;
            }
            .bx-calendar-year-content .disabled {
                pointer-events: none;
                color: #ccc;
            }
            .bx-calendar-range .disabled {
                pointer-events: none;
            }
            /* Form window */
            [class ^= "<?=self::$MODULE_LBL?>block_"] {
                display: none;
            }
            #<?=self::$MODULE_LBL?>wndOrder {
                width: 100%;
            }
            #<?=self::$MODULE_LBL?>wndOrder td:first-of-type {
                width: 45%;
            }
            #<?=self::$MODULE_LBL?>wndOrder select {
                width: 260px;
            }
            #<?=self::$MODULE_LBL?>wndOrder input[type="text"] {
                width: 250px;
            }
            #<?=self::$MODULE_LBL?>wndOrder textarea {
                width: 250px;
                height: 50px;
            }
            #<?=self::$MODULE_LBL?>statusSign {
                font-style: italic;
            }
            .<?=self::$MODULE_LBL?>message td {
                padding-top: 10px;
            }
            [class ^= "<?=self::$MODULE_LBL?>type_"] td {
                padding-top: 10px;
            }
            .fat {
                font-weight: bold !important;
            }
            ul.ui-autocomplete {
                /* Cause standard 1000 is too small fox BX dialog window */
                z-index: 10000;
            }
        </style>
        <?php
    }

    /**
     * Add buttons to form
     * @param string $html button HTML
     */
    protected static function addButton($html)
    {
        if (!isset(self::$arButtons))
            self::$arButtons = array();

        if (count(self::$arButtons) && count(self::$arButtons) % 3 === 0) {
            self::$arButtons[] = '<br><br>';
        }

        self::$arButtons[] = $html;
    }
}