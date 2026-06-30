<??>
<script>

function initLogFileArea()
{
    $('.log-message.log-main').off().on('click', function(){

        var logs = $(this).siblings('.log-message[data-log-id="'+$(this).data('log-id')+'"]');

        if(!!!$(this).data('sorted')){
            $(this).after(logs);
            $(this).attr({
                'data-sorted': '1'
            });
        }

        logs.toggleClass('opened');
    });

    $('.log-message.log-main').each(function(){

        var isSetStatus = !1;

        var logs = {
            error: !!$(this).siblings('.log-message.log-error[data-log-id="'+$(this).data('log-id')+'"]').length,
            success: !!$(this).siblings('.log-message.log-success[data-log-id="'+$(this).data('log-id')+'"]').length,
            warning: !!$(this).siblings('.log-message.log-warning[data-log-id="'+$(this).data('log-id')+'"]').length
        };

        if(logs.error){
            $(this).addClass('status-error');
            isSetStatus = !0;
        }

        if(logs.warning){
            $(this).addClass('status-warning');
            isSetStatus = !0;
        }

        if(logs.success){
            $(this).addClass('status-success');
            isSetStatus = !0;
        }

        if(!isSetStatus){
            $(this).addClass('status-info');
        }

    });

    $("#logsearcher").trigger('keyup');
}

$(document).ready(function(){
   $('.js-btn-ajax').on('click', function(){
       if($(this).hasClass('btn-option-active')){
           return;
       }
       var _this = $(this);
       
       $('.btn-option').addClass('btn-disabled');
       _this.addClass('btn-wait');

       $.ajax({
           url: '<?=$_SERVER['REQUEST_URI']?>',
           type: 'GET',
           data: {
               type: _this.data('log-type'),
               ajax: 'y',
               sessid: BX.bitrix_sessid()
           },
           success: function(result){

               $('.logger-area').removeClass('ov-hidden');
               switch(_this.data('log-type')){
                   case 'exchange':
                       $('.logger-area').empty().html(result);
                       initLogFileArea();
                   break;
                   case 'debug':
                       $('.logger-area').empty().html('<textarea class="debug-log-textarea">' + result + '</textarea>');
                       $('.logger-area').addClass('ov-hidden');
                   break;
                   case 'clear':
                       $('.logger-area').empty();
                   break;
               }

               $('.btn-option').removeClass('btn-disabled');
               _this.removeClass('btn-wait');
           }
       });
   });

   $('.js-btn-function').on('click', function(){
       if($(".logger-area textarea").length){
           $(".logger-area textarea").animate({ scrollTop: $(".logger-area textarea").prop('scrollHeight') }, 200);
       } else {
           $(".logger-area").animate({ scrollTop: $(".logger-area").prop('scrollHeight')}, 200);
       }
   });

   initLogFileArea();

   $("#logsearcher").on("keyup", function() {
        $('.log-message').not('.log-main').removeClass('opened');
        var  value = $(this).val().toLowerCase();
        $(".logger-area .log-main").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
        if($('#onlyerror').is(':checked')){
            $(".logger-area .log-main.status-success,.logger-area .log-main.status-info").hide();
        }
    });

    $('#onlyerror').on('change', function(){
        $('.log-message').not('.log-main').removeClass('opened');
        $("#logsearcher").trigger('keyup');
        if($(this).is(':checked')){
            $(".logger-area .log-main.status-success,.logger-area .log-main.status-info").hide();
        }
    });

});
</script>