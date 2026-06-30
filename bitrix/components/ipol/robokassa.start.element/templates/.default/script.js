    /**
     * Фоформление заказа
     * @param fields
     */
    function robokassaCreateOrder(fields)
    {

        $('.order-form .response-errors').find('div').remove();
        $('.order-form .response-success').find('div').remove();

        BX.ajax
            .runComponentAction(
                'ipol:robokassa.start.element',
                'createOrder',
                {
                    mode: 'ajax',
                    method: 'post',
                    data: fields
                }
            )
            .then(
                function (response)
                {

                    document.location.href = response['data']['URL'];
                    $('.order-form .make-order .order-button').removeClass('disabled');
                }
            )
            .catch(
                function(response)
                {
                    $.each(
                        response.errors,
                        function(key, error)
                        {
                            if(error['message'] !== undefined && error['message'].length > 0)
                            {
                                $('.order-form .response-errors').append(
                                    $('<div />')
                                        .addClass(['order-form-create-error'])
                                        .text(error['message'])
                                );
                            }
                        }
                    );

                    $('.order-form .make-order .order-button').removeClass('disabled');
                }
            )
        ;
    }

    /**
     * Обновление корзины товаров
     */
    function robokassaRefreshBasket()
    {
        BX.ajax
            .runComponentAction(
                'ipol:robokassa.start.element',
                'basketList',
                {
                    mode: 'ajax',
                    method: 'post'
                }
            )
            .then(
                function(response)
                {

                    let basketListBlock = $('#robokassaBasket .basket-list');
                    let basketBlock = $('#robokassaBasket');

                    basketListBlock.find('table.basket-table').remove();
                    basketListBlock.find('.empty-basket').remove();

                    if(
                        response['data']['basket'] === undefined
                        || response['data']['basket'].length === 0
                    )
                    {
                        basketListBlock
                            .append(
                                $('<div />')
                                    .addClass(['empty-basket'])
                                    .text(robokassaBuyButtonBlock.lang.empty)
                            )
                        ;

                        basketBlock.find('.basket-price').hide();
                        basketBlock.find('.order-form').hide();
                    }
                    else
                    {

                        let table = $('<table />').addClass(['basket-table']);

                        $.each(
                            response['data']['basket'],
                            function(key, basketItem)
                            {
                                let tr = $('<tr />').addClass(['basket-item-data']);
                                tr
                                    .append(
                                        $('<td />')
                                            .addClass(['basket-item-image', basketItem['picture'] === '' ? 'no-photo' : ''])
                                            .css(
                                                {
                                                    'background-image': 'url("' + basketItem['picture'] + '")'
                                                }
                                            )
                                    )

                                    .append(
                                        $('<td />')
                                            .addClass(['basket-item-title'])
                                            .text(basketItem['name'])
                                    )

                                    .append(
                                        $('<td />')
                                            .addClass(['basket-item-quantity'])

                                            .append(
                                                $('<div />')
                                                    .addClass(['quantity-minus'])
                                                    .attr('data-product-id', basketItem['productId'])
                                            )

                                            .append(
                                                $('<div />')
                                                    .addClass(['quantity-text'])
                                                    .text(basketItem['quantity'])
                                            )

                                            .append(
                                                $('<div />')
                                                    .addClass(['quantity-plus'])
                                                    .attr('data-product-id', basketItem['productId'])
                                            )
                                    )

                                    .append(
                                        $('<td />')
                                            .addClass(['basket-item-price'])
                                            .html(basketItem['printSum'])
                                    )

                                    .append(
                                        $('<td />')
                                            .addClass(['basket-item-remove'])
                                            .append(
                                                $('<div />')
                                                    .attr('data-product-id', basketItem['productId'])
                                                    .addClass(['remove-button'])
                                                    .attr('title', robokassaBuyButtonBlock.lang.item_remove)
                                            )
                                    )
                                table.append(tr)
                            }
                        );

                        basketListBlock.append(table);

                        basketBlock.find('.basket-price').find('.value').html(response.data['basketPrice'])
                        basketBlock.find('.basket-price').show();
                        basketBlock.find('.order-form').show();
                    }
                }
            )
        ;
    }



    $(document).ready(
        function()
        {

            robokassaRefreshBasket();

            try
            {
                const phoneMask = IMask(
                    document.getElementById('robokassa-phone-number'),
                    {
                        mask: '+{7}(000)000-00-00'
                    }
                );
            }
            catch (error)
            {}

            $(document).on(
                'click',
                '.basket-list .basket-item-data > div.basket-item-remove .remove-button',
                function(e)
                {
                    BX.ajax
                        .runComponentAction(
                            'ipol:robokassa.start.element',
                            'deleteBasket',
                            {
                                mode: 'ajax',
                                method: 'post',
                                data : {
                                    productId: $(this).data('productId'),
                                }
                            }
                        )
                        .then(
                            function(response)
                            {
                                robokassaRefreshBasket();
                                $('#robokassaBasket').css({display: 'flex'});
                                $('body').addClass('hidden-school');
                            },
                            /**
                             * errors
                             * @param response
                             */
                            function(response)
                            {

                                if(response['status'] === 'error')
                                {
                                    new BX.CDialog(
                                        {
                                            title: response['errors'][0]['title'],
                                            content: response['errors'][0]['message'],
                                            height: 40,
                                            width: 300,
                                            resizable: false,
                                            buttons: [
                                                BX.CDialog.prototype.btnClose
                                            ]
                                        }
                                    ).Show();
                                }
                            }
                        )
                    ;

                    e.preventDefault();
                }
            );

            $(document).on(
                'click',
                '.basket-list .basket-item-data > div.basket-item-quantity .quantity-minus',
                function(e)
                {
                    BX.ajax
                        .runComponentAction(
                            'ipol:robokassa.start.element',
                            'subProductBasketQuantity',
                            {
                                mode: 'ajax',
                                method: 'post',
                                data : {
                                    productId: $(this).data('productId'),
                                }
                            }
                        )
                        .then(
                            function(response)
                            {
                                robokassaRefreshBasket();
                                $('#robokassaBasket').css({display: 'flex'});
                                $('body').addClass('hidden-school');
                            },
                            /**
                             * errors
                             * @param response
                             */
                            function(response)
                            {

                                if(response['status'] === 'error')
                                {
                                    new BX.CDialog(
                                        {
                                            title: response['errors'][0]['title'],
                                            content: response['errors'][0]['message'],
                                            height: 40,
                                            width: 300,
                                            resizable: false,
                                            buttons: [
                                                BX.CDialog.prototype.btnClose
                                            ]
                                        }
                                    ).Show();
                                }
                            }
                        )
                    ;

                    e.preventDefault();
                }
            );

            $(document).on(
                'click',
                '.basket-list .basket-item-data > div.basket-item-quantity .quantity-plus',
                function(e)
                {
                    BX.ajax
                        .runComponentAction(
                            'ipol:robokassa.start.element',
                            'addProductBasketQuantity',
                            {
                                mode: 'ajax',
                                method: 'post',
                                data : {
                                    productId: $(this).data('productId'),
                                }
                            }
                        )
                        .then(
                            function(response)
                            {
                                robokassaRefreshBasket();
                                $('#robokassaBasket').css({display: 'flex'});
                                $('body').addClass('hidden-school');
                            },
                            /**
                             * errors
                             * @param response
                             */
                            function(response)
                            {

                                if(response['status'] === 'error')
                                {
                                    new BX.CDialog(
                                        {
                                            title: response['errors'][0]['title'],
                                            content: response['errors'][0]['message'],
                                            height: 40,
                                            width: 300,
                                            resizable: false,
                                            buttons: [
                                                BX.CDialog.prototype.btnClose
                                            ]
                                        }
                                    ).Show();
                                }
                            }
                        )
                    ;

                    e.preventDefault();
                }
            );

            $(document).on(
                'click',
                '#openRobokassaAdd2basket',
                function(e)
                {

                    if($(this).hasClass('disabled-quantity'))
                    {
                        return;
                    }

                    BX.ajax
                        .runComponentAction(
                            'ipol:robokassa.start.element',
                            'add2basket',
                            {
                                mode: 'ajax',
                                method: 'post',
                                data : {
                                    productId: $(this).data('productId'),
                                    quantity: 1
                                }
                            }
                        )
                        .then(
                            function(response)
                            {
                                robokassaRefreshBasket();
                                $('#robokassaBasket').css({display: 'flex'});
                                $('body').addClass('hidden-school');
                            },
                            /**
                             * errors
                             * @param response
                             */
                            function(response)
                            {

                                if(response['status'] === 'error')
                                {
                                    new BX.CDialog(
                                        {
                                            title: response['errors'][0]['title'],
                                            content: response['errors'][0]['message'],
                                            height: 40,
                                            width: 300,
                                            resizable: false,
                                            buttons: [
                                                BX.CDialog.prototype.btnClose
                                            ]
                                        }
                                    ).Show();
                                }
                            }
                        )
                    ;

                    e.preventDefault();
                }
            );

            $(document).on(
                'click',
                '.js-create-order',
                function(e)
                {

                    if($(this).hasClass('disabled'))
                    {
                        return;
                    }

                    $(this).addClass('disabled');

                    let orderFormBlock = $('.order-form');
                    let hasError = false;

                    orderFormBlock.find('.robokassa-error-field').removeClass('robokassa-error-field');

                    let fields = {
                        buyerName: orderFormBlock.find('input[name="buyerName"]'),
                        buyerEmail: orderFormBlock.find('input[name="buyerEmail"]'),
                        buyerPhoneNumber: orderFormBlock.find('input[name="phoneNumber"]')
                    }

                    let params = {
                        buyerName: fields.buyerName.val(),
                        buyerEmail: fields.buyerEmail.val(),
                        buyerPhoneNumber: fields.buyerPhoneNumber.val(),
                        session_id: robokassaBuyButtonBlock.session_id
                    }

                    if(params.buyerName.length < 3)
                    {
                        hasError = true;
                        fields.buyerName.parent().addClass('robokassa-error-field');
                    }

                    if(params.buyerPhoneNumber.length !== 16)
                    {
                        hasError = true;
                        fields.buyerPhoneNumber.parent().addClass('robokassa-error-field');
                    }

                    if(
                        params.buyerEmail.length === 0
                        || /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/.test(params.buyerEmail) === false
                    )
                    {
                        hasError = true;
                        fields.buyerEmail.parent().addClass('robokassa-error-field');
                    }

                    if(!hasError)
                    {
                        robokassaCreateOrder(params)
                    }
                    else
                    {
                        $(this).removeClass('disabled');
                    }
                    e.preventDefault();
                }
            )


            $(document).on(
                'click',
                '#openRobokassaBasket',
                function(e)
                {
                    $('#robokassaBasket').css({display: 'flex'});
                    $('body').addClass('hidden-school');
                    e.preventDefault();
                }
            );

            $('#robokassaBasket .close-button').click(
                function(e)
                {
                    $('#robokassaBasket').hide();
                    $('body').removeClass('hidden-school');
                    e.preventDefault();
                }
            );

            $(document).keydown(
                function(e)
                {
                    if (e.keyCode === 27)
                    {
                        $('#robokassaBasket').hide();
                        $('#robokassaOrder').hide();
                        $('body').removeClass('hidden-school');
                    }
                }
            );
        }
    );