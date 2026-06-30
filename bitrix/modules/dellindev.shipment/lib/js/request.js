BX.namespace('BX.DellinAdmin');

BX.DellinAdmin = {

    orderId: '',
    shipmentId: '',
    methodId: '',

    init: function () {

        console.log('Dellin handler loaded!');
         let block = this.getBlockBase();
       //let data = this.createCustomBlock();
       //     block.appendChild(data);
         let button = this.createButton();
             block.after(button);


        BX.addCustomEvent(this.popup, 'onWindowRegister',function(){


            let popupContainer = BX.DellinAdmin.getContainerPopup();
                popupContainer.innerHTML = '';
            let content = BX.DellinAdmin.createCustomBlock();
                popupContainer.appendChild(content);



        })

    },
   //NODE INNER BASE BLOCK METHODS
    getBlockBase: function () {
        return document.querySelector('[value="Добавить отгрузку"]');
      //  return document.querySelector('.adm-bus-pay-section-right');
    },
    valueIsNull: function(value){
        return (value == null);
    },
    getContainerPopup: function () {
            return document.querySelector('#app');
    },
    createButton: function(){
        let button = document.createElement('input');
            button.className = 'adm-order-block-add-button';
            button.type = 'button';
            button.value = 'Создать заявку';
            button.addEventListener('click', function () {
                console.log('Нажата кнопка');
                BX.DellinAdmin.popup.Show();
            });

            return button;
    },
    popup:  new BX.CDialog({

            'title': 'Форма оформления  заявки на отгрузку ГК "Деловые линии" ',

            'content': '<div id="app"></div>',

            'draggable': true,

            'resizable': true,

            'buttons': [BX.CDialog.btnSave, BX.CDialog.btnCancel]

        }),
    datepickerCreate: function(defaultData){

        let inputDate = document.createElement('input');
            inputDate.id ='produce-date';
            inputDate.type = 'text';
            inputDate.name = 'date';
            inputDate.value = defaultData;
            inputDate.addEventListener('click', function () {

                return BX.calendar({node: this, field: this, bTime: false});

            });

            return inputDate;
    },
    createButtonWithAjax: function(item){
        let button = document.createElement('input');
            button.className = 'adm-btn-save';
            button.type = 'button';
            button.value = 'Отправить заявку';
            button.style = (BX.DellinAdmin.valueIsNull(item.TRACKING_NUMBER))?'':'display:none;';

            button.addEventListener('click', function() {

          //  let value = document.querySelector('#produce_date').value;
                let postData = {
                    action: 'create_order',
                    order_id: BX.DellinAdmin.orderId,
                    shipment_id: item.ID,
                    is_order: (document.querySelector('#is_order').value == '0'),
                    produce_date:  document.querySelector('#produce-date').value,
                    sessid: BX.bitrix_sessid(),
                };


                BX.DellinAdmin.ajaxSend(postData);
            //     BX.ajax({
            //         timeout:    300,
            //         method:     'POST',
            //         dataType:   'json',
            //         url:        '/bitrix/tools/dellindev.shipping/dellin_ajax.php',
            //         data:       postData,
            //
            //         onsuccess: function(result)
            //         {
            //
            //             if (!result) return;
            //
            //             console.log(result);
            //             if(result.state == 'success'){
            //                 BX.DellinAdmin.alertIfsuccess(result.message, 'Продолжить');
            //             }
            //
            //         },
            //
            //         onfailure: function(status)
            //         {
            //             console.log("onfailture", status);
            //         }
            //     });
            //
            //     console.log(postData);
            //
            });

            return button;
    },
    createBlock: function(name){
        let divContainer = document.createElement('div');
        divContainer.className = 'adm-bus-table-container caption border';

        let divTitle = document.createElement('div');
        divTitle.className = 'adm-bus-table-caption-title';
        divTitle.style = 'background: #eef5f5;';
        divTitle.innerHTML = name;

        divContainer.appendChild(divTitle);
        return divContainer;
    },
    // getValueIsOrder: function(){
    //
    //    return setTimeout(function () {
    //       return
    //    }, 300);
    //
    // },
    // getValueProduceDate: function(){
    //
    //     return setTimeout(function () {
    //         return  document.querySelector('#produceDate').value;
    //     }, 300)
    //
    //   //  return document.querySelector('#produceDate').value;
    // },
    createCustomBlock: function () {


        let divContainer = BX.DellinAdmin.createBlock('Активные отгрузки ГК "Деловые линии"');
        let divPersonInfo = BX.DellinAdmin.createBlock('Данные о клиенте');
        let divInfo = BX.DellinAdmin.getBlockPersonInfo();
        divPersonInfo.appendChild(divInfo);
        divContainer.appendChild(divPersonInfo);


        Object.values(BX.DellinAdmin.shipmentData).map(function (item) {

            let name = 'Состав отгрузки номер:' +item.ID;
            let divShipment = BX.DellinAdmin.createBlock(name);


            let divAdditionInfo = BX.DellinAdmin.getBlockMethodInfo(item.IS_TERMINAL_ARRIVAL,
                                                                    item.IS_TERMINAL_DERIVAL,
                                                                    item.ADDRESS_ARIVAL,
                                                                    item.ADDRESS_DERIVAL);

                divShipment.appendChild(divAdditionInfo);

            let table = BX.DellinAdmin.createTableForCustomBlock();
            let tableHeader = BX.DellinAdmin.createTableHeader();
                table.appendChild(tableHeader);


            Object.values(item.BASKET).map(function (basketItem) {

                let props = {
                    height: basketItem.height,
                    lenght: basketItem.lenght,
                    width: basketItem.width,
                    weight: basketItem.weight,
                    unitDemension: basketItem.unitDemensions,
                    unitWeight: basketItem.unitWeight
                };

                let bodyItem = BX.DellinAdmin.createBasketItemRow(basketItem.productId,
                                                                  basketItem.productName,
                                                                  basketItem.quantity,
                                                                  props,
                                                                  basketItem.price);
                table.appendChild(bodyItem);
                //console.log(basketItem);

            });

               // console.log(item);


                divShipment.appendChild(table);
                divContainer.appendChild(divShipment);

                if(BX.DellinAdmin.valueIsNull(item.TRACKING_NUMBER)){
                    let inputBlock = document.createElement('div');
                    let span = document.createElement('span');
                    span.innerHTML = 'Выберите предполагаемую дату отгрузки:';

                    inputBlock.appendChild(span);
                    inputBlock.appendChild(BX.DellinAdmin.datepickerCreate(item.DEFAULT_DATE));

                    divShipment.appendChild(BX.DellinAdmin.getBlockIsOrder());
                    divShipment.appendChild(inputBlock);

                    let button = BX.DellinAdmin.createButtonWithAjax(item);
                    divShipment.appendChild(button);

                } else {
                    let trackingNumber = BX.DellinAdmin.createStrInfo('Трек номер', item.TRACKING_NUMBER);
                    let trackingStatus = BX.DellinAdmin.createStrInfo('Статус доставки', item.STATUS_TRACKING);
                    let dateOfArrival = BX.DellinAdmin.createStrInfo('Ориентировочная дата прибытия', item.DATE_ARRIVAL);

                    divShipment.appendChild(trackingNumber);
                    divShipment.appendChild(trackingStatus);
                    divShipment.appendChild(dateOfArrival);
                }



        });

            return divContainer;
    },
    getBlockIsOrder: function(){

        let block = document.createElement('div');
            block.innerHTML = 'Отправить как черновик?';
            block.id = 'is-order';
            block.style = 'display:none;';
        let select = document.createElement('select');
            select.id = 'is_order';

        let option1 = document.createElement('option');
            option1.value = 0;
            option1.innerHTML = 'Нет, отправить штатно';

        let option2 = document.createElement('option');
            option2.value = 1;
            option2.innerHTML = 'Да, отправить как черновик';

            select.appendChild(option1);
            select.appendChild(option2);

            block.appendChild(select);

        return block;
    },
    displayIsOrder: function(){
      let block = document.querySelector('#is-order');
          block.style = '';
    },

    getBlockPersonInfo: function(){
        let personInfo = document.createElement('div');

        let name = BX.DellinAdmin.createStrInfo('Имя клиента', BX.DellinAdmin.personData.personName);
        let type = BX.DellinAdmin.createStrInfo('Тип клиента', BX.DellinAdmin.personData.personType);
        let address = BX.DellinAdmin.createStrInfo('Адрес клиента', BX.DellinAdmin.personData.personAddress);
        let phone = BX.DellinAdmin.createStrInfo('Контактный телефон', BX.DellinAdmin.personData.personPhone);
        let email = BX.DellinAdmin.createStrInfo('Email', BX.DellinAdmin.personData.personEmail );

            personInfo.appendChild(name);
            personInfo.appendChild(type);
            personInfo.appendChild(phone);
            personInfo.appendChild(email);
            personInfo.appendChild(address);

        return personInfo;
    },
    createStrInfo: function(key, value){

        let div = document.createElement('div');
            div.innerHTML = key +': '+value;

        return div;

    },
    getBlockMethodInfo: function(isTerminalArival, isTerminalDerival, address_arival, address_derival){
        let block = BX.DellinAdmin.createBlock('Информация об отгрузке');

        let phraseArival = (isTerminalArival)? 'До терминала' : 'До адреса';
        let phraseDerival = (isTerminalDerival)? 'От терминала' : 'От адреса';

        let phraseAddressDerival = (isTerminalDerival)?'Адрес терминала отправки' : 'Адрес отправки';
        let phraseAddressArival = (isTerminalArival)?'Адрес терминала доставки' : 'Адрес доставки';

        let schemeValue = phraseDerival + ' - ' + phraseArival;

        let methodScheme = BX.DellinAdmin.createStrInfo('Схема доставки', schemeValue );
        let strAddressDerival = BX.DellinAdmin.createStrInfo(phraseAddressDerival , address_derival );
        let strAddressArival = BX.DellinAdmin.createStrInfo(phraseAddressArival, address_arival);


        block.appendChild(methodScheme);
        block.appendChild(strAddressDerival);
        block.appendChild(strAddressArival);

        return block;
    },
    createBasketItemRow: function(productId, name, amount, props, price){
        let tbody = document.createElement('tbody');
        let tr = document.createElement('tr');

        let tdId = document.createElement('td');
            tdId.innerHTML = productId;

        let tdName = document.createElement('td');
            tdName.innerHTML = name;

        let tdAmount = document.createElement('td');
            tdAmount.innerHTML = amount;

        let tdProps = document.createElement('td');
        let propChild = BX.DellinAdmin.basketTableProps(props);
            tdProps.appendChild(propChild);

        let tdPrice = document.createElement('td');
            tdPrice.innerHTML = price;

            tr.appendChild(tdId);
            tr.appendChild(tdName);
            tr.appendChild(tdAmount);
            tr.appendChild(tdProps);
            tr.appendChild(tdPrice);

            tbody.appendChild(tr);

            return tbody;

    },
    basketTableProps: function (props){
        let table = document.createElement('table');

        let lengthProp = BX.DellinAdmin.rowProp('Длина', props.unitDemension, props.lenght);
        let widthProp = BX.DellinAdmin.rowProp('Ширина', props.unitDemension, props.width);
        let heightProp = BX.DellinAdmin.rowProp('Высота', props.unitDemension, props.height);

        let weightProp = BX.DellinAdmin.rowProp('Масса', props.unitWeight, props.weight);

            table.appendChild(lengthProp);
            table.appendChild(widthProp);
            table.appendChild(heightProp);
            table.appendChild(weightProp);

        return table;
    },
    rowProp: function(nameProp, unit, value){

        let trProp = document.createElement('tr');

        let tdKey = document.createElement('td');
        let spanKey = document.createElement('div');
        spanKey.style = "color: gray; font-size: 11px";
        spanKey.innerHTML = ''+nameProp+' (' + unit + '): ';
        tdKey.appendChild(spanKey);

        let tdValue = document.createElement('td');
        let divValue = document.createElement('div');
        divValue.style = 'font-size: 9px; padding: 2px 5px; text-align: center; border: 1px solid gray;';
        divValue.innerHTML = value;
        tdValue.appendChild(divValue);
        trProp.appendChild(tdKey);
        trProp.appendChild(tdValue);

        return trProp;
    },
    createTableHeader: function (){

        let thead = document.createElement('thead');
        let tr = document.createElement('tr');

        let tdIdProduct = document.createElement('td');
            tdIdProduct.innerHTML = 'ID';

        let tdNameProduct = document.createElement('td');
            tdNameProduct.innerHTML = 'Наименование';

        let tdAmount = document.createElement('td');
            tdAmount.innerHTML = 'Количество в отгрузке';

        let tdProps = document.createElement('td');
            tdProps.innerHTML = 'ВГХ';

        let tdPrice = document.createElement('td');
            tdPrice.innerHTML = 'Цена';

            tr.appendChild(tdIdProduct);
            tr.appendChild(tdNameProduct);
            tr.appendChild(tdAmount);
            tr.appendChild(tdProps);
            tr.appendChild(tdPrice);

            thead.appendChild(tr);
        return thead;
    },
    showLoading: function (){

        BX.DellinAdmin.hideLoading();

        let contentCDialogBlock = document.querySelector('#app');

        let loadingWrap = BX.DellinAdmin.createLoadingWrap();
        let loading = BX.DellinAdmin.createLoadingBlock();

            contentCDialogBlock.appendChild(loadingWrap);
            contentCDialogBlock.appendChild(loading);
    },
    hideLoading: function(){
        let loadingWrap = document.querySelector('.loading-wrap');
        let loading = document.querySelector('.loading');

        if(!loadingWrap) return;
        if(!loading) return;

        loadingWrap.remove();
        loading.remove();
    },
    createLoadingWrap: function(){
        let loadingWrap = document.createElement('div');
            loadingWrap.className = 'loading-wrap';
            loadingWrap.style = '    width: 100%;' +
                                '    height: 100%;' +
                                '    position: absolute;' +
                                '    top: 0;' +
                                '    left: 0;' +
                                '    background: rgba(0,0,0,0.5);' +
                                '    z-index:99;';

            return loadingWrap;

    },
    createLoadingBlock: function(){

        let styleForBlocks = '    position: fixed;' +
                            '    top: 0px;' +
                            '    right: 0px;' +
                            '    z-index: 9999;' +
                            '    background: #fff;' +
                            '    padding: 20px;' +
                            '    border-radius: 10px;' +
                            '    box-shadow:0 0 5px rgba(0,0,0,0.5);';

        let loading = document.createElement('div');
            loading.className = 'loading';
            loading.style = styleForBlocks;

        let span = document.createElement('span');
            span.className = 'loading-content';
            span.style = styleForBlocks;

        let img = document.createElement('img');
            img.src = '/bitrix/js/main/core/images/wait.gif';

            span.appendChild(img);
            loading.appendChild(span);

            return loading;

    },
    createTableForCustomBlock: function () {

        let table = document.createElement('table');
            table.className = 'adm-s-order-table-ddi-table';
            table.width = '100%';

        return table;
    },
    ajaxSend: function(postData){

        BX.DellinAdmin.showLoading();

        BX.ajax({
            timeout:    300,
            method:     'POST',
            dataType:   'json',
            url:        '/bitrix/tools/dellindev.shipping/dellin_ajax.php',
            data:       postData,

            onsuccess: function(result)
            {

                if (!result) return;

    //            console.log(result);
                if(result.status == 'success'){
                    BX.DellinAdmin.hideLoading();
                    BX.DellinAdmin.alertIfsuccess(result.message, 'Продолжить');
                }

                if(result.status == 'price_changed'){
                    BX.DellinAdmin.hideLoading();
                    console.log('Ответ от сервера price_change', result);

                    BX.DellinAdmin.alertIfPriceChange(result.message, 'Продолжить', postData);
                }

                if(result.status == 'price_update'){
                    BX.DellinAdmin.hideLoading();
                    postData.price_changed = false;
                    BX.DellinAdmin.ajaxSend(postData)
                }

                if(result.status == 'error'){

                    BX.DellinAdmin.hideLoading();

                    let info = result.message + '<br/>' + BX.DellinAdmin.renderErrorsBlockInAlert(result);
                    BX.DellinAdmin.alertIfError(info, 'Продолжить');

                    console.error('Тип ошибок:',result.typeErrors);
                    Object.values(result.errors).map((item) => console.error(item));


                }

            },

            onfailure: function(status)
            {
                console.log("onfailture", status);
            }
        });

    },
    renderErrorsBlockInAlert: function(result){
        let block = document.createElement('div');
            block.style = 'color:red;';

            Object.values(result.errors).map((item) => {

                let span = document.createElement('span');
                    span.innerHTML = item;
                let br = document.createElement('br');
                    block.appendChild(span);
                    block.appendChild(br);

            });



            return block.outerHTML;
    },
    alertIfsuccess: function (message, action ) {

        BX.UI.Dialogs.MessageBox.show(
            {
                message: message,
                title: "Процесс создания заявки",
                buttons: [
                    new BX.UI.Button(
                        {
                            color: BX.UI.Button.Color.DANGER,
                            text: action,
                            onclick: function(button, event) {
                               setTimeout(()=> window.location.reload(), 3000);
                                button.context.close();
                            }
                        }
                    ),
                    new BX.UI.CancelButton()
                ],
            }
        );
    },
    alertIfPriceChange: function (message, action, postData) {

        BX.UI.Dialogs.MessageBox.show(
            {
                message: message,
                title: "Процесс создания заявки",
                buttons: [
                    new BX.UI.Button(
                        {
                            color: BX.UI.Button.Color.DANGER,
                            text: action,
                            onclick: function(button, event) {
                                postData.price_changed = true;

                                console.log('Data sended:', postData);
                                BX.DellinAdmin.ajaxSend(postData);
                                //TODO добавить загрузку
                                button.context.close();
                            }
                        }
                    ),
                    new BX.UI.CancelButton()
                ],
            }
        );

    },
    alertIfError: function (message, action) {

        BX.UI.Dialogs.MessageBox.show(
            {
                message: message,
                title: "Процесс создания заявки",
                buttons: [
                    new BX.UI.Button(
                        {
                            color: BX.UI.Button.Color.DANGER,
                            text: action,
                            onclick: function(button, event) {

                                button.context.close();
                            }
                        }
                    ),
                    new BX.UI.CancelButton()
                ],
            }
        );

    }


};