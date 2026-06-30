<style>
    #IPOLSDEK_MC_container {
        width: 100%;
        border-collapse: collapse;
    }
    #IPOLSDEK_MC_container th{
        background-color: #E0E8EA;
        padding: 5px;
        text-align: center;
    }
    #IPOLSDEK_MC_container td {
        text-align: center;
    }
    .IPOLSDEK_CisMarker{
        background-image: url(/bitrix/images/ipol.sdek/save.png);
        width  : 15px;
        height : 15px;
        cursor : pointer;
        position: relative;
        top: 20px;
        left: 175px;
    }
    .IPOLSDEK_QRSelector{
        margin-bottom: 5px;
        background-color: #f5f9f9;
        cursor : pointer;
        padding : 5px;
        word-wrap: break-word;
    }
    .IPOLSDEK_QRSelector:hover{
        background-color: #E0E8EA;
    }
</style>
<script>
    var IPOLSDEK_marks = {
        save : false,

        items : false,

        init : function(vals){
            if(vals){
                IPOLSDEK_marks.save = vals;
            }

            for (const [index, item] of Object.entries(IPOLSDEK_oExport.goods)) {
                if(
                    typeof(item.QR) !== 'undefined'
                ){
                    IPOLSDEK_marks.qrs.existed[item.PRODUCT_ID] = item.QR;
                }
            }

            this.qrs.current = IPOLSDEK_packs.srv.copyObj(this.qrs.existed);
        },

        load : function(){
            IPOLSDEK_marks.items = (IPOLSDEK_packs.getSaved()) ? IPOLSDEK_packs.getSaved() : IPOLSDEK_marks.autosplit();

            var container = $('#IPOLSDEK_MC_container');
            container.html('');

            container.append('<tr><th><?=GetMessage('IPOLSDEK_JS_SOD_MC_GOOD')?></th><th><?=GetMessage('IPOLSDEK_JS_SOD_Pack')?></th><th><?=GetMessage('IPOLSDEK_JS_SOD_MC_Mark')?></th></th></tr>' +
                '<tr><td><div id="IPOLSDEK_QRSelector" class="b-popup" style="display: none;"><div class="pop-text"></div><div class="close" onclick="$(this).closest(\'.b-popup\').hide();"></div></div></td></tr>');
            var rplcr = IPOLSDEK_marks.srv.replaceAll;

            for(var cargoId in IPOLSDEK_marks.items){
                for(var itemIndex in IPOLSDEK_marks.items[cargoId].goods){
                    for(var cnt = 0; cnt < IPOLSDEK_marks.items[cargoId].goods[itemIndex]; cnt++){
                        var val = (
                            IPOLSDEK_marks.save &&
                            typeof(IPOLSDEK_marks.save[cargoId]) !== 'undefined' &&
                            typeof(IPOLSDEK_marks.save[cargoId][itemIndex]) !== 'undefined' &&
                            typeof(IPOLSDEK_marks.save[cargoId][itemIndex].marks[cnt]) !== 'undefined'
                        ) ? IPOLSDEK_marks.save[cargoId][itemIndex].marks[cnt] : '';
                        var iid = IPOLSDEK_oExport.goods[itemIndex].PRODUCT_ID;
                        var dataStr = '_data_cargo="'+cargoId+'" _data_iid="'+iid+'" _data_iiq="'+itemIndex+'" _data_iic="'+cnt+'"';
                        container.append('<tr><td>'+IPOLSDEK_oExport.goods[itemIndex].NAME + ' ['+iid+']'+'</td>' +
                            '<td>'+cargoId+'</td><td><div class="IPOLSDEK_CisMarker" _data_iid="'+iid+'" onclick=\'IPOLSDEK_marks.qrs.load("'+iid+'","'+rplcr(dataStr,'"','\\"')+'")\'></div><textarea class="IPOLSDEK_MC_item" '+dataStr+'>'+val+'</textarea></td></tr>');
                    }
                }
            }

            let mcItems = document.getElementsByClassName('IPOLSDEK_MC_item');
            for (let mcItem of mcItems) {
                mcItem.addEventListener('keypress', IPOLSDEK_marks.qrs.keypress);
            }

            IPOLSDEK_marks.qrs.check();
        },

        wnd : {
            link : false,

            open : function () {
                if(!IPOLSDEK_marks.wnd.link){
                    IPOLSDEK_marks.wnd.link = new BX.CDialog({
                        title: "<?=GetMessage('IPOLSDEK_JS_SOD_MC_HEADER')?>",
                        content: '<table id="IPOLSDEK_MC_container"></table>',
                        icon: 'head-block',
                        resizable: false,
                        draggable: true,
                        height: '505',
                        width: '685',
                        buttons: [
                            '<input type=\"button\" id=\"IPOLSDEK_markApply\" value=\"<?=GetMessage('MAIN_APPLY')?>\" onclick=\"IPOLSDEK_marks.act.apply()\"/>',
                            '<input type=\"button\" id=\"IPOLSDEK_markCancel\" value=\"<?=GetMessage('MAIN_RESET')?>\"  onclick=\"IPOLSDEK_marks.act.cancel()\"/>',
                        ]
                    });
                }

                IPOLSDEK_marks.load();
                IPOLSDEK_marks.wnd.link.Show();
            },

            close : function () {
                IPOLSDEK_marks.wnd.link.Close();
            }
        },

        qrs : {
            existed : {},
            current : {},

            load : function (id,link) {
                link = link.split(' ');
                var newLink = '';
                link.forEach(function(el){
                    newLink += '['+el+']';
                });

                if(typeof(this.current[id]) === 'undefined' || !this.current[id].length){
                    $('#IPOLSDEK_QRSelector .pop-text').html('NO QR');
                } else {
                    $('#IPOLSDEK_QRSelector .pop-text').html('');
                    var rplcr = IPOLSDEK_marks.srv.replaceAll;
                    this.current[id].forEach(function(qr,index){
                        $('#IPOLSDEK_QRSelector .pop-text').append('<div class="IPOLSDEK_QRSelector" onclick=\'IPOLSDEK_marks.qrs.place('+index+',"'+rplcr(newLink,'"','\\"')+'","'+id+'")\'>'+qr+'</div>');
                    })
                }

                IPOLSDEK_oExport.popup('IPOLSDEK_QRSelector', $('.IPOLSDEK_MC_item'+newLink), '#IPOLSDEK_MC_container');
            },

            place : function (index,link,id) {
                var qr = this.current[id][index];
                $('.IPOLSDEK_MC_item'+link).val(qr);
                this.current[id].splice(index,1);
                $('#IPOLSDEK_QRSelector').hide();
                this.check();
            },

            check : function () {
                var existed = this.current;

                $('.IPOLSDEK_CisMarker').each(function(){
                    var id = $(this).attr('_data_iid');
                    if(typeof(existed[id]) === 'undefined'  || !existed[id].length){
                        $(this).hide();
                    }
                })
            },

            keypress : function (e) {
                if (e.charCode === 29) {
                    this.value += String.fromCharCode(e.which);
                }
            }
        },

        act : {
            apply : function () {
                IPOLSDEK_marks.save = {};
                $('.IPOLSDEK_MC_item').each(function(){
                    var val = $(this).val();
                    if(val) {
                        var cargo = $(this).attr('_data_cargo');
                        var indItem = $(this).attr('_data_iiq');
                        if (typeof(IPOLSDEK_marks.save[cargo]) === 'undefined') {
                            IPOLSDEK_marks.save[cargo] = {};
                        }
                        if (typeof(IPOLSDEK_marks.save[cargo][indItem]) === 'undefined') {
                            IPOLSDEK_marks.save[cargo][indItem] = {
                                id    : $(this).attr('_data_iid'),
                                marks : []
                            };
                        }
                        IPOLSDEK_marks.save[cargo][indItem].marks.push($(this).val());
                    }
                });

                if(IPOLSDEK_oExport.isEmpty(IPOLSDEK_marks.save)){
                    IPOLSDEK_marks.save = false;
                }
                IPOLSDEK_marks.wnd.close();
            },
            cancel : function () {
                IPOLSDEK_marks.flee();
                IPOLSDEK_marks.wnd.close();
            }
        },

        autosplit : function () {
            var obRet = {1:{goods:{}}};

            for (const [index, item] of Object.entries(IPOLSDEK_oExport.goods)) {
                obRet[1].goods[index] = item.QUANTITY;
            }

            return obRet;
        },

        flee : function () {
            IPOLSDEK_marks.save = false;
            IPOLSDEK_marks.qrs.current = IPOLSDEK_packs.srv.copyObj(IPOLSDEK_marks.qrs.existed);
        },

        srv : {
            replaceAll : function(string,search,replace){
                return string.split(search).join(replace);
            }
        }
    };
</script>