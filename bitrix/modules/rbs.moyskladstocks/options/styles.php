<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<style scoped>
    .despi-save-alert-around-save-btn {
        display: inline-block;
        margin-left: 1em;
        background: #84ac00;
        padding: 0.4em 0.6em;
        border-radius: 5px;
        color: #fff;
        opacity: 1;

        transition: all .2s;
    }

    .despi-alert-hide {
        opacity: 0;
    }

    [data-silent-head-hr] {
        padding-top: 15px;
        border-top: 1px solid #c9cdce;
    }

    [name="main_options"] .ui-alert {
        border-radius: 10px;
    }

    .despi-alert-info {
        background: #d8e2e5;
        color: #222;
    }

    .ui-alert,
    .despi-alert-info {
        max-width: 600px;
        display: block !important;
    }

    /**AGENT BLOCK NODE */

    .despi-agent-block-node {
        text-align: left;
        margin: auto;
        padding: 1em;
        font-size: 1.05em;
        width: 380px;
        /* height: 200px; */
        border: 1px solid #ccc;
        border-radius: 10px;
        background-color: #fff;
    }

    .despi-agent-block-node .ui-alert {
        margin-top: 10px;
        text-align: center;
    }

    .despi-agent-block-node .despi-agent-block-head {
        font-size: 1.2em;
        margin-bottom: 1em;
    }

    .despi-agent-block-node .despi-agent-tab-block {
        text-align: center;
    }

    .despi-agent-block-node .despi-agent-tab-block .adm-detail-tab {
        margin: 0;
    }

    .despi-agent-block-node .despi-agent-tab-block .adm-detail-tab.despi-agent-tab-active {
        background-position: 0 -2473px;
        color: #000;
    }

    .despi-agent-tab-body-block-wrapper {
        margin-top: 1px;
        padding: 1em;
        border: 1px solid #ccc;
        border-radius: 10px;
    }

    .despi-agent-block-node .despi-agent-tab-body-block {
        display: none;
    }

    .despi-agent-function-param-head {
        font-weight: bold;
        margin-bottom: .5em;
    }

    .despi-agent-function-param-head+.despi-default-note-style {
        margin: 1em 0;
    }

    .despi-agent-function-param-list {
        margin-bottom: 1em;
    }

    .despi-agent-function-param-list>div {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: .4em 0;
        padding: .4em 0;
        border-bottom: 1px dashed #bbb6b6;
    }

    .despi-agent-param-row>div {
        display: flex;
        align-items: center;
    }

    .despi-agent-function-param-list input[type="number"] {

        max-width: 70px;

        font-size: 13px;
        height: 25px;
        padding: 0 5px;
        margin: 0;

        background: #fff;
        border: 1px solid;
        border-color: #87919c #959ea9 #9ea7b1 #959ea9;
        border-radius: 4px;
        color: #000;
        box-shadow: 0 1px 0 0 rgba(255, 255, 255, 0.3), inset 0 2px 2px -1px rgba(180, 188, 191, 0.7);
        display: inline-block;
        outline: none;
        vertical-align: middle;
        -webkit-font-smoothing: antialiased;

    }

    .despi-agent-function-param-list .despi-agent-block-sub-option {
        padding-left: 1em;
    }

    .despi-agent-tab-btn-area {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .despi-agent-tab-btn-area .despi-default-note-style {
        display: none;
        width: 100%;
        margin: 0 1em;
    }

    /**TOGGLE */
    .despi-toggle-isolated {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .despi-toggle-isolated .switch {
        margin-left: 1em;
        position: relative;
        display: inline-block;
        width: 48px;
        height: 28px;
    }

    .despi-toggle-isolated .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .despi-toggle-isolated .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #e0e8ea;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .despi-toggle-isolated .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .despi-toggle-isolated input:checked+.slider,
    .despi-color-success {
        background-color: #86ad00;
        background-image: -webkit-linear-gradient(bottom, #729e00, #97ba00);
    }

    .despi-toggle-isolated input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    .despi-toggle-isolated input:checked+.slider:before {
        -webkit-transform: translateX(20px);
        -ms-transform: translateX(20px);
        transform: translateX(20px);
    }

    /* Rounded sliders */
    .despi-toggle-isolated .slider.round {
        border-radius: 34px;
    }

    .despi-toggle-isolated .slider.round:before {
        border-radius: 50%;
    }

    /**TOGGLE */

    .rbs-ms-stocks-info-message-left {
        display: inline-block;
        padding: 1rem;
        background-color: #e0e8ea;
        border-radius: 5px;
        color: #4b6267;
        font-weight: bold;
    }

    .flex-admin-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-log-size {
        margin-right: 10px;
    }

    .mb-15 {
        margin-bottom: 15px;
    }

    a.btn-option:hover {
        text-decoration: none;
    }

    .btn-option {
        user-select: none;
        -webkit-border-radius: 4px;
        border-radius: 4px;
        border: none;
        -webkit-box-shadow: 0 0 1px rgba(0, 0, 0, .11), 0 1px 1px rgba(0, 0, 0, .3), inset 0 1px #fff, inset 0 0 1px rgba(255, 255, 255, .5);
        box-shadow: 0 0 1px rgba(0, 0, 0, .3), 0 1px 1px rgba(0, 0, 0, .3), inset 0 1px 0 #fff, inset 0 0 1px rgba(255, 255, 255, .5);
        background-color: #e0e9ec;
        background-image: -webkit-linear-gradient(bottom, #d7e3e7, #fff) !important;
        background-image: -moz-linear-gradient(bottom, #d7e3e7, #fff) !important;
        background-image: -ms-linear-gradient(bottom, #d7e3e7, #fff) !important;
        background-image: -o-linear-gradient(bottom, #d7e3e7, #fff) !important;
        background-image: linear-gradient(bottom, #d7e3e7, #fff) !important;
        color: #3f4b54;
        cursor: pointer;
        display: inline-block;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-weight: bold;
        font-size: 13px;
        height: 29px;
        text-shadow: 0 1px rgba(255, 255, 255, 0.7);
        text-decoration: none;
        position: relative;
        vertical-align: middle;
        -webkit-font-smoothing: antialiased;
        line-height: 28px;
        padding: 0 10px;
        margin-right: 5px;
    }

    .btn-option-active {
        color: #fff;
        background-color: #86ad00 !important;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, .25), inset 0 1px 0 #cbdc00;
        box-shadow: 0 1px 1px rgba(0, 0, 0, .25), inset 0 1px 0 #cbdc00;
        border-color: #97c004 #7ea502 #648900;
        background-image: -webkit-linear-gradient(bottom, #729e00, #97ba00) !important;
        background-image: -moz-linear-gradient(bottom, #729e00, #97ba00) !important;
        background-image: -ms-linear-gradient(bottom, #729e00, #97ba00) !important;
        background-image: -o-linear-gradient(bottom, #729e00, #97ba00) !important;
        background-image: linear-gradient(bottom, #729e00, #97ba00) !important;
    }

    .btn-wait {
        position: relative;
    }

    .btn-wait:before {
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

    .btn-disabled {
        pointer-events: none;
        opacity: .4;
    }

    .btn-area {
        width: 220px;
    }

    .log-button-line {
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
    }

    #loger.loger-area {
        width: 100%;
        height: 843px;
    }

    #loger.loger-area {
        overflow: auto;
    }

    #loger .log-message {
        padding: 7px 15px;
        border-radius: 10px;
        margin-bottom: 3px;

        display: flex;
        justify-content: space-between;

        cursor: pointer;
    }

    #loger .log-message span {
        display: inline-block;
    }

    #loger .log-more-text {
        padding-right: 3px;
    }

    #loger .log-message.log-main {
        position: relative;
        padding: 7px 15px 7px 35px;
        min-height: 20px;
        align-items: center;
        background: #d8e2e5;
    }

    #loger .log-main:before {
        content: '';
        width: 7px;
        height: 7px;
        background-color: #fff;
        border-radius: 50%;
        position: absolute;
        left: 15px;
    }

    #loger .log-main.status-error:before {
        background-color: #f16d81;
    }

    #loger .log-main.status-success:before {
        background-color: #73dd3f;
    }

    #loger .log-main.status-warning:before {
        background-color: #ffdd00;
    }

    #loger .log-message.log-success {
        display: none;
        background-color: #73dd3f;
    }

    #loger .log-message.log-error {
        display: none;
        background-color: #f16d81;
    }

    #loger .log-message.log-info {
        display: none;
        background-color: #e5e5e5;
    }

    #loger .log-message.log-warning {
        display: none;
        background-color: #ffdd00;
    }

    #loger .log-message.opened {
        display: flex !important;
        margin-left: 15px;
    }

    /**DEPRECATED STYLES */
    /**NOTE STYLES */
    .rbs-ms-stocks-info-message-right,
    .despi-default-note-style {
        background: #e6e5be;
        background: -webkit-linear-gradient(top, rgba(244, 233, 141, .3), rgba(232, 209, 62, .3), rgba(225, 194, 40, .3));
        background: -moz-linear-gradient(top, rgba(244, 233, 141, .3), rgba(232, 209, 62, .3), rgba(225, 194, 40, .3));
        background: -ms-linear-gradient(top, rgba(244, 233, 141, .3), rgba(232, 209, 62, .3), rgba(225, 194, 40, .3));
        background: -o-linear-gradient(top, rgba(244, 233, 141, .3), rgba(232, 209, 62, .3), rgba(225, 194, 40, .3));
        background: linear-gradient(top, rgba(244, 233, 141, .3), rgba(232, 209, 62, .3), rgba(225, 194, 40, .3));
        border-radius: 5px;
        border: 1px solid;
        border-color: #d3c6a3 #cabc90 #c1b37f #c9bc8f;
        -webkit-box-shadow: inset 0 1px 0 #fff;
        box-shadow: inset 0 1px 0 #fff;
        color: #716536;
        display: inline-block;
        font-size: 13px;
        line-height: 18px;
        text-shadow: 0 1px 0 rgb(255 255 255 / 70%);
        padding: 1rem;
    }

    .despi-default-note-style {
        padding: 1em;
        text-align: center;
    }

    /**table for fields params tabs: product, variant, service, bundle */

    table.despi-internal-table-params {
        max-width: 850px;
    }

    table.despi-internal-table-params tr:hover td {
        background-color: #c9dee6 !important;
    }

    table.despi-internal-table-params tr:first-child {
        position: sticky;
        top: 0;
    }
</style>