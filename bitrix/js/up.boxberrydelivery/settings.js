$(document).ready(function() {

    var $input = $('[name="RECEPTION_POINT_NAME"]');
    checkApiToken($input);

    $('[name="API_TOKEN"]').on('blur', function() {
        checkApiToken($input);
    });

    $('[name="RECEPTION_POINT_CODE"]').closest('tr').hide();

    var $error = $('<div class="error-message" style="color:red;margin-top:5px;"></div>').insertAfter($input).hide();
    var $dropdown = $('<ul class="autocomplete-dropdown" style="position:absolute;z-index:1000;background:#fff;border:1px solid #ccc;display:none;max-height:150px;overflow:auto;"></ul>').insertAfter($input);

    $input.on('input', function() {
        var value = $(this).val();
        $input.removeClass('input-error');
        $error.hide();
        $dropdown.hide().empty();

        if (value.length > 2) {
            $.post('/bitrix/js/up.boxberrydelivery/ajax.php', { bb_reception_point_search: value, sessid: BX.bitrix_sessid() })
                .done(function(response) {
                    if (response.status === 'error') {
                        $input.addClass('input-error');
                        $error.text(response.errors[0].message).show();
                    } else if (response.data && response.data.length) {
                        response.data.forEach(function(item) {
                            $('<li style="padding:5px;cursor:pointer;"></li>')
                                .text(item)
                                .on('mousedown', function(e) {
                                    $input.val(item);
                                    $dropdown.hide();
                                    $.post('/bitrix/js/up.boxberrydelivery/ajax.php', { bb_get_reception_code_by_name: item, sessid: BX.bitrix_sessid() })
                                        .done(function(response) {
                                            if (response.status === 'success') {
                                                $('[name="RECEPTION_POINT_CODE"]').val(response.data.code);
                                            } else {
                                                $input.addClass('input-error');
                                                $error.text('Код не найден').show();
                                            }
                                        })
                                        .fail(function() {
                                            $input.addClass('input-error');
                                            $error.text('Ошибка соединения с сервером').show();
                                        });
                                })
                                .appendTo($dropdown);
                        });
                        var offset = $input.offset();
                        $dropdown.css({ left: offset.left, top: offset.top + $input.outerHeight(), width: $input.outerWidth() }).show();
                    }
                })
                .fail(function() {
                    $input.addClass('input-error');
                    $error.text('Ошибка соединения с сервером').show();
                });
        }
    });

    $(document).on('mousedown', function(e) {
        if (!$(e.target).closest($input).length && !$(e.target).closest($dropdown).length) {
            $dropdown.hide();
        }
    });
});

$('<style>.input-error { border: 1px solid red !important; } .autocomplete-dropdown li:hover { background:#f0f0f0; }</style>').appendTo('head');

function checkApiToken($input) {
    var token = $('[name="API_TOKEN"]').val();
    $.post('/bitrix/js/up.boxberrydelivery/ajax.php', { bb_ya_delivery: token, sessid: BX.bitrix_sessid() })
        .done(function(response) {
            console.log(response);
            if (response.authorization === true) {
                $input.prop('disabled', false);
            } else {
                $input.prop('disabled', true);
            }

            if (response.reset_reception_point === true) {
                $('[name="RECEPTION_POINT_CODE"]').val('');
                $('[name="RECEPTION_POINT_NAME"]').val('');
                $('[name="RECEPTION_POINT_CODE"]').closest('tr').hide();
            }
        })
        .fail(function() {
            $input.prop('disabled', true);
        });
}