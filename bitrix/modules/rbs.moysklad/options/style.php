<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}
?>
<style>

	.despi-alert-info {
        background: #d8e2e5;
        color: #222;
    }

    .ui-alert,
    .despi-alert-info {
		border-radius: 10px;
        max-width: 790px;
        display: block !important;
		margin: 1em auto;
    }

	.save-alert-around-save-btn {
		display: inline-block;
		margin-left: 1em;
		background: #84ac00;
		padding: 0.4em 0.6em;
		border-radius: 5px;
		color: #fff;
		opacity: 1;

		transition: all .2s;
	}

	.alert-hide {
		opacity: 0;
	}
</style>