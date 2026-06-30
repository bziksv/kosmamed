<??>
<style>

.flex-admin-row{
   display: flex;
   justify-content: space-between;
   align-items: center;
}

.info-log-size{
   margin-right: 10px;
}

.mb-15{
   margin-bottom: 15px;
}

.btn-option{
   -webkit-border-radius: 4px;
   border-radius: 4px;
   border: none;
   -webkit-box-shadow: 0 0 1px rgba(0,0,0,.11), 0 1px 1px rgba(0,0,0,.3), inset 0 1px #fff, inset 0 0 1px rgba(255,255,255,.5);
   box-shadow: 0 0 1px rgba(0,0,0,.3), 0 1px 1px rgba(0,0,0,.3), inset 0 1px 0 #fff, inset 0 0 1px rgba(255,255,255,.5);
   background-color: #e0e9ec;
   background-image: -webkit-linear-gradient(bottom, #d7e3e7, #fff)!important;
   background-image: -moz-linear-gradient(bottom, #d7e3e7, #fff)!important;
   background-image: -ms-linear-gradient(bottom, #d7e3e7, #fff)!important;
   background-image: -o-linear-gradient(bottom, #d7e3e7, #fff)!important;
   background-image: linear-gradient(bottom, #d7e3e7, #fff)!important;
   color: #3f4b54;
   cursor: pointer;
   display: inline-block;
   font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
   font-weight: bold;
   font-size: 13px;
   height: 29px;
   text-shadow: 0 1px rgba(255,255,255,0.7);
   text-decoration: none;
   position: relative;
   vertical-align: middle;
   -webkit-font-smoothing: antialiased;
   line-height: 28px;
   padding: 0 10px;
   margin-right: 5px;
}

.btn-option-active{
   color: #fff;
   background-color: #86ad00!important;
   -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.25), inset 0 1px 0 #cbdc00;
   box-shadow: 0 1px 1px rgba(0,0,0,.25), inset 0 1px 0 #cbdc00;
   border-color: #97c004 #7ea502 #648900;
   background-image: -webkit-linear-gradient(bottom, #729e00, #97ba00)!important;
   background-image: -moz-linear-gradient(bottom, #729e00, #97ba00)!important;
   background-image: -ms-linear-gradient(bottom, #729e00, #97ba00)!important;
   background-image: -o-linear-gradient(bottom, #729e00, #97ba00)!important;
   background-image: linear-gradient(bottom, #729e00, #97ba00)!important;
}

.btn-wait{
   position: relative;
}

.btn-wait:before{
   content: '';
   background: url('/bitrix/panel/main/images/waiter-button-light.gif');
   background-repeat: no-repeat;
   position: absolute;

   width: 20px;
   height: 20px;

   top: 5px;
   left: 50%;
   margin-left: -10px;
}

.btn-disabled{
   pointer-events:none;
   opacity: .4;
}

.btn-area{
   width: 220px;
}

.debug-log-textarea,.logger-area{
   width: 100%;
   height: 843px;
}

.logger-area{
   overflow: auto;
}

.ov-hidden{
   overflow: hidden;
}

.logger-area textarea{
   max-width: calc(100% - 20px);
}

.log-message{
   padding: 7px 15px;
   border-radius: 10px;
   margin-bottom: 3px;

   display: flex;
   justify-content: space-between;

   cursor: pointer;
}

.log-message span{
   display: inline-block;
}

   .log-message.log-main{
      position: relative;
      padding: 7px 15px 7px 35px;
      min-height: 20px;
      align-items: center;
      background: #d8e2e5;
   }

      .log-main:before{
         content: '';
         width: 7px;
         height: 7px;
         background-color: #fff;
         border-radius: 50%;
         position: absolute;
         left: 15px;
      }
            .log-main.status-error:before{
               background-color: #f16d81;
            }

            .log-main.status-success:before{
               background-color: #73dd3f;
            }

            .log-main.status-warning:before{
               background-color: #ffdd00;
            }

   .log-message.log-success{
      display: none;
      background-color: #73dd3f;
   }

   .log-message.log-error{
      display: none;
      background-color: #f16d81;
   }

   .log-message.log-info{
      display: none;
      background-color: #bbbbbb;
   }

   .log-message.log-warning{
      display: none;
      background-color: #ffdd00;
   }

   .log-message.opened{
        display: flex !important;
        margin-left: 15px;
    }

</style>