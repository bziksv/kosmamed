<?php

    if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

    $arComponentParameters = array(
        "PARAMETERS" => array(
            "ELEMENT_ID" => array(
                "NAME" => GetMessage("ROBOKASSA_WIDGET_COMPONENT_ELEMENT_ID"),
                "TYPE" => "STRING",
                "DEFAULT" => "",
                "PARENT" => "BASE",
            ),
            "IBLOCK_ID" => array(
                "NAME" => GetMessage("ROBOKASSA_WIDGET_COMPONENT_IBLOCK_ID"),
                "TYPE" => "STRING",
                "DEFAULT" => "",
                "PARENT" => "BASE",
            ),
            "PRICE" => array(
                "NAME" => GetMessage("ROBOKASSA_WIDGET_COMPONENT_PRICE"),
                "TYPE" => "STRING",
                "DEFAULT" => "",
                "PARENT" => "BASE",
            ),
            "SKU_BLOCK_CODE" => array(
                "NAME" => GetMessage("ROBOKASSA_WIDGET_COMPONENT_SKU"),
                "TYPE" => "STRING",
                "DEFAULT" => "",
                "PARENT" => "BASE",
            ),
        ),
    );