<?php
namespace Rbs\MoyskladStocks\Diagnostic\Dashboard\Items\MsConnect;

use Rbs\MoyskladStocks\Diagnostic\Dashboard\Core\BaseItem;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Auth extends BaseItem
{
    protected function setTitle(): void
    {
        $this->title = Loc::getMessage('AVTORIZATSIJA_V_MOJSKLAD');
    }

    protected function setDescription(): void
    {
        $this->description = Loc::getMessage('PROVERKA_AVTORIZATSII_V_MOJSKLAD_');
    }

    protected function executeItemCheck(): void
    {
        $this->value = Loc::getMessage('NET_AVTORIZATSII');
        $this->status = self::STATUS_WARNING;

        $token = Config::getToken();
        if(empty($token)) {
            $login = Config::getLogin();
            $password = Config::getPass();
            if(empty($login) || empty($password)) {
                $this->valueDescription = Loc::getMessage('NE_ZADANY_LOGIN_I_PAROL_');
                return;
            }            
        } else {
            $this->valueDescription = Loc::getMessage('NE_ZADAN_TOKEN_AVTORIZATSII');
        }
        
        try {
            
            $testRequest = ApiNew::get('/entity/organization', ['limit' => 1]);
            
            if (isset($testRequest->meta) && isset($testRequest->meta->size)) {
                $this->value = Loc::getMessage('USPESHNO');
                $this->status = self::STATUS_SUCCESS;
            } else {
                
                $error = '';
                if (isset($testRequest->errors) && is_array($testRequest->errors) && count($testRequest->errors) > 0) {
                    $error = current($testRequest->errors) ?? Loc::getMessage('NEIZVESTNAJA_OSHIBKA');
                }
                
                $this->value = Loc::getMessage('OSHIBKA');
                $this->status = self::STATUS_ERROR;
                $this->valueDescription = $error;
                
            }
        } catch (\Exception $e) {
            $this->value = Loc::getMessage('OSHIBKA');
            $this->status = self::STATUS_ERROR;
            $this->valueDescription = $e->getMessage();
        }
    }
} 