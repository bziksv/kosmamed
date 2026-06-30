<?php
namespace Ipolh\SDEK\Admin;

use Ipolh\SDEK\Bitrix\Adapter;
use Ipolh\SDEK\Bitrix\Adapter\Store;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\StoreHandler;

/**
 * Class StoresForm
 * @package Ipolh\SDEK\Admin
 */
class StoresForm
{
    protected static $MODULE_ID  = IPOLH_SDEK;
    protected static $MODULE_LBL = IPOLH_SDEK_LBL;

    protected static $arButtons = [];

    /**
     * Create da form window
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
        $data = StoreHandler::getStoreData($id);

        self::generateFormHtml($data);
        self::loadFormCSS();
        self::loadFormJS($data);
    }

    /**
     * Generate HTML for form window
     * @param Store $data
     */
    protected static function generateFormHtml($data)
    {
        $coreAddress = $data->getCoreAddress();
        $coreSender  = $data->getCoreSender();
        $coreSeller  = $data->getCoreSeller();

        ?>
        <div id="<?=self::$MODULE_LBL?>PLACEFORFORM">
            <table id="<?=self::$MODULE_LBL?>wndOrder">
                <tbody>
                <tr class="<?=self::$MODULE_LBL?>storeId <?php if($data->getId()) { echo self::$MODULE_LBL."hidden"; }?>">
                    <td><?=Tools::getMessage('LBL_ID')?></td><td id="<?=self::$MODULE_LBL?>storeId"><?=$data->getId();?></td>
                </tr>
                <?php Tools::placeFormRow('isActive', 'checkbox', $data->isActive());?>
                <?php Tools::placeFormRow('name', 'text', $coreAddress->getField('name'));?>
                <?php Tools::placeFormRow('isDefaultForLocation', 'checkbox', $coreAddress->getField('isDefaultForLocation'));?>
                <?php
                // Address
                ?>
                <?php Tools::placeFormHeaderRow('STORE_ADDRESS');?>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_ABOUT_ADDRESS')?></td></tr>
                <tr><td colspan="2"><hr></td></tr>
                <tr>
                    <td><label for="<?=self::$MODULE_LBL?>cityCode"><?=Tools::getMessage('LBL_cityCode')?></label></td>
                    <td>
                        <input type='text' id='<?=self::$MODULE_LBL?>cityCode_sign' value='<?=implode(', ', [$coreAddress->getRegion(), $coreAddress->getCity()])?>' style='max-width:300px;'>
                        <input type='hidden' id='<?=self::$MODULE_LBL?>cityCode' value='<?=$coreAddress->getCode()?>'>
                    </td>
                </tr>
                <?php Tools::placeFormRow('street', 'text', $coreAddress->getStreet());?>
                <?php Tools::placeFormRow('house', 'text', $coreAddress->getHouse());?>
                <?php Tools::placeFormRow('flat', 'text', $coreAddress->getFlat());?>
                <tr><td colspan="2">&nbsp;</td></tr>
                <?php Tools::placeFormRow('isAddressDataSent', 'checkbox', $coreAddress->getField('isAddressDataSent'));?>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_IS_ADDRESS_DATA_SENT')?></td></tr>
                <?php
                // Sender
                ?>
                <?php Tools::placeFormHeaderRow('STORE_SENDER');?>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_ABOUT_SENDER')?></td></tr>
                <tr><td colspan="2"><hr></td></tr>
                <?php Tools::placeFormRow('company', 'text', $coreSender->getCompany());?>
                <?php Tools::placeFormRow('fullName', 'text', $coreSender->getFullName());?>
                <?php Tools::placeFormRow('phone', 'text', $coreSender->getPhone());?>
                <tr><td></td><td><small><?=Tools::getMessage('SIGN_phone')?></small></td></tr>
                <?php Tools::placeFormRow('phoneAdditional', 'text', $coreSender->getField('phoneAdditional'));?>
                <tr><td colspan="2">&nbsp;</td></tr>
                <?php Tools::placeFormRow('isSenderDataSent', 'checkbox', $coreSender->getField('isSenderDataSent'));?>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_IS_SENDER_DATA_SENT')?></td></tr>
                <?php
                // Seller
                ?>
                <?php Tools::placeFormHeaderRow('STORE_SELLER');?>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_ABOUT_SELLER')?></td></tr>
                <tr><td colspan="2"><hr></td></tr>
                <?php Tools::placeFormRow('sellerCompany', 'text', $coreSeller->getCompany());?>
                <?php Tools::placeFormRow('sellerPhone', 'text', $coreSeller->getPhone());?>
                <tr><td></td><td><small><?=Tools::getMessage('SIGN_phone')?></small></td></tr>
                <?php Tools::placeFormRow('sellerAddress', 'text', $coreSeller->getField('address'));?>
                <tr><td colspan="2">&nbsp;</td></tr>
                <?php Tools::placeFormRow('isSellerDataSent', 'checkbox', $coreSeller->getField('isSellerDataSent'));?>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_IS_SELLER_DATA_SENT')?></td></tr>
                <?php
                // Courier
                ?>
                <?php Tools::placeFormHeaderRow('STORE_COURIER');?>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_ABOUT_COURIER')?></td></tr>
                <tr><td colspan="2"><hr></td></tr>
                <?php Tools::placeFormRow('needCall', 'checkbox', $coreSender->getField('needCall'));?>
                <?php Tools::placeFormRow('powerOfAttorney', 'checkbox', $coreSender->getField('powerOfAttorney'));?>
                <?php Tools::placeFormRow('identityCard', 'checkbox', $coreSender->getField('identityCard'));?>
                <?php Tools::placeFormRow('comment', 'textbox', $coreAddress->getComment());?>
                <tr><td colspan="2"><hr></td></tr>
                <tr><td colspan="2"><?=Tools::getMessage('MESS_TIME_AFTER_15')?></td></tr>
                <tr><td colspan="2"><hr></td></tr>
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
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Adds form JS
     * @param Store $data
     */
    protected static function loadFormJS($data)
    {
        /**
         * Admin interface JS controller added in \admin\ipol_sdek_stores.php
         */
        ?>
        <script type="text/javascript">
            <?=self::$MODULE_LBL?>controller.expander({
                storeId: '<?=$data->getId()?>',
                senderCities: [<?=StoreHandler::getSenderCitiesJS()?>],
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

                        this.mainWnd = new ipol_sdek_wndController({
                            title: '<?=Tools::getMessage('HDR_STORE_WND')?>',
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
                    this.events(this);
                    this.onSave(this);
                    this.onEdit(this);

                    this.events.onTimeChange();
                },
                open: function(){
                    if (this.mainWnd)
                        this.mainWnd.open();
                },
                save: function(){
                    $('#<?=self::$MODULE_LBL?>save').hide();
                    $('.<?=self::$MODULE_LBL?>errInput').removeClass('<?=self::$MODULE_LBL?>errInput');
                    var data = this.getInputs();

                    if (data.success) {
                        this.self.ajax({
                            data: this.self.concatObj(data.inputs, {
                                isdek_action: 'uploadStore',
                                isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                storeId:     this.self.storeId,
                            }),
                            dataType: 'json',
                            success: this.onSave
                        });
                    } else {
                        var alertStr = "<?=Tools::getMessage('MESS_STORE_NOT_SAVED')?>\n\n<?=Tools::getMessage('MESS_STORE_FILL')?>";
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
                                    checkVal = ($('#<?=self::$MODULE_LBL?>' + depths[i].link).prop('checked')) ? true : false;
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
                                    } else {
                                        if (preVal)
                                            data.inputs[i] = preVal;
                                    }
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
                        if (data.success) {
                            alert("<?=Tools::getMessage('MESS_STORE_SAVED')?>");
                            self.mainWnd.close();
                            window.location.reload();
                        } else {
                            $('#<?=self::$MODULE_LBL?>save').show();
                            alert('<?=Tools::getMessage('MESS_STORE_NOT_SAVED')?>' + "\n\n" + data.errors);
                        }
                    };
                }),
                onEdit: (function(self){
                    self.onEdit = function(data){
                        //console.log(data);
                        $('.<?=self::$MODULE_LBL?>errInput').removeClass('<?=self::$MODULE_LBL?>errInput');

                        let newId = !isNaN(parseInt(data.storeId, 10)) ? parseInt(data.storeId, 10) : '';
                        this.self.storeId = newId;
                        $('#<?=self::$MODULE_LBL?>storeId').html(newId);
                        if (newId) {
                            $('.<?=self::$MODULE_LBL?>storeId').removeClass('<?=self::$MODULE_LBL?>hidden');
                        } else {
                            if (!($('.<?=self::$MODULE_LBL?>storeId').hasClass('<?=self::$MODULE_LBL?>hidden')))
                                $('.<?=self::$MODULE_LBL?>storeId').addClass('<?=self::$MODULE_LBL?>hidden');
                        }

                        $('#<?=self::$MODULE_LBL?>isActive').prop('checked', data.isActive);
                        $('#<?=self::$MODULE_LBL?>name').val(data.name);
                        $('#<?=self::$MODULE_LBL?>isDefaultForLocation').prop('checked', data.isDefaultForLocation);

                        $('#<?=self::$MODULE_LBL?>cityCode_sign').val(data.region + ', ' + data.city);
                        $('#<?=self::$MODULE_LBL?>cityCode').val(data.cityCode);
                        $('#<?=self::$MODULE_LBL?>street').val(data.street);
                        $('#<?=self::$MODULE_LBL?>house').val(data.house);
                        $('#<?=self::$MODULE_LBL?>flat').val(data.flat);
                        $('#<?=self::$MODULE_LBL?>isAddressDataSent').prop('checked', data.isAddressDataSent);

                        $('#<?=self::$MODULE_LBL?>company').val(data.company);
                        $('#<?=self::$MODULE_LBL?>fullName').val(data.fullName);
                        $('#<?=self::$MODULE_LBL?>phone').val(data.phone);
                        $('#<?=self::$MODULE_LBL?>phoneAdditional').val(data.phoneAdditional);
                        $('#<?=self::$MODULE_LBL?>isSenderDataSent').prop('checked', data.isSenderDataSent);

                        $('#<?=self::$MODULE_LBL?>sellerCompany').val(data.sellerCompany);
                        $('#<?=self::$MODULE_LBL?>sellerPhone').val(data.sellerPhone);
                        $('#<?=self::$MODULE_LBL?>sellerAddress').val(data.sellerAddress);
                        $('#<?=self::$MODULE_LBL?>isSellerDataSent').prop('checked', data.isSellerDataSent);

                        $('#<?=self::$MODULE_LBL?>needCall').prop('checked', data.needCall);
                        $('#<?=self::$MODULE_LBL?>powerOfAttorney').prop('checked', data.powerOfAttorney);
                        $('#<?=self::$MODULE_LBL?>identityCard').prop('checked', data.identityCard);
                        $('#<?=self::$MODULE_LBL?>comment').html(data.comment);

                        $('#<?=self::$MODULE_LBL?>intakeTimeFrom').val(data.intakeTimeFrom);
                        $('#<?=self::$MODULE_LBL?>intakeTimeTo').val(data.intakeTimeTo);
                        $('#<?=self::$MODULE_LBL?>lunchTimeFrom').val(data.lunchTimeFrom);
                        $('#<?=self::$MODULE_LBL?>lunchTimeTo').val(data.lunchTimeTo);

                        if (this.mainWnd)
                            this.mainWnd.open();
                    };
                }),
                dependences: function(){
                    return {
                        isActive:             {need: false},
                        name:                 {need: true},
                        isDefaultForLocation: {need: false},

                        cityCode:             {need: true},
                        street:               {need: true},
                        house:                {need: false},
                        flat:                 {need: false},
                        isAddressDataSent:    {need: false},

                        company:              {need: 'sub', link: 'isSenderDataSent', checkVal: true},
                        fullName:             {need: 'sub', link: 'isSenderDataSent', checkVal: true},
                        phone:                {need: 'sub', link: 'isSenderDataSent', checkVal: true},
                        phoneAdditional:      {need: false},
                        isSenderDataSent:     {need: false},

                        sellerCompany:        {need: 'sub', link: 'isSellerDataSent', checkVal: true},
                        sellerPhone:          {need: 'sub', link: 'isSellerDataSent', checkVal: true},
                        sellerAddress:        {need: 'sub', link: 'isSellerDataSent', checkVal: true},
                        isSellerDataSent:     {need: false},

                        needCall:             {need: false},
                        powerOfAttorney:      {need: false},
                        identityCard:         {need: false},
                        comment:              {need: false},

                        intakeTimeFrom:       {need: true},
                        intakeTimeTo:         {need: true},
                        lunchTimeFrom:        {need: false},
                        lunchTimeTo:          {need: false},
                    };
                },
                events: (function(self){
                    self.events = {
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