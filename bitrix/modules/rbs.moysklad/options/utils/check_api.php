<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Rbs\Moysklad\ApiNew;
use \Rbs\Moysklad\Utils;

$tmpSkladOption = ApiNew::get('/entity/customerorder', ['limit' => 1]);
if (Utils::has_errors($tmpSkladOption)) {

    foreach ($tmpSkladOption->errors as $error) {
        CAdminMessage::ShowMessage([
            'MESSAGE' => $error,
            "HTML"=> true
        ]);
    }

    if($tmpSkladOption->isNull){
        $hideSaveBtn = true;
    }
    
} else {
    CAdminMessage::ShowMessage([
        'MESSAGE' => GetMessage('API_OK'),
        'TYPE' => 'OK'
    ]);
    $isApiAvailable = true;
}