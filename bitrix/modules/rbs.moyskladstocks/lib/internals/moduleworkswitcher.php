<?php

namespace Rbs\MoyskladStocks\Internals;

use Rbs\MoyskladStocks\Debug\Loger;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Agent;

class ModuleWorkSwitcher
{
    public const REASON_AGENT_AUTO_ON = 'AGENT_AUTO_ON';
    public const REASON_IMPORT_ONCE = 'IMPORT_ONCE';
    public const REASON_OPTION_CHANGE = 'OPTION_CHANGE';
    public const REASON_MONITORING_CHANGE = 'MONITORING_CHANGE';
    private const AUTO_ENABLE_TIMEOUT = 120;

    public static function switchModuleWork(bool $isWork = true, string $reason = self::REASON_OPTION_CHANGE): void
    {
        $userId = ModifyUser::getUserId();

        $moduleWork = $isWork ? 'Y' : 'N';
        $currentState = Config::getOption('global_enabled', 'N');

        $logger = new Loger();

        $logger->addWarningMessage(LangMsg::get('MODULE_SWITCHER_REASON', [
            '#REASON#' => LangMsg::get('MODULE_SWITCHER_REASON_' . $reason),
            '#USER_ID#' => $userId
        ]));

        Config::setOption('global_enabled', $moduleWork);

        if ($currentState !== $moduleWork) {
            $logger->exportLog(LangMsg::get('MODULE_SWITCHER_ACTION', [
                '#STATE#' => LangMsg::get('MODULE_SWITCHER_ACTION_STATE_' . $moduleWork)
            ]));
        }
    }

    public static function checkModuleSwitchOn(): void
    {
        $logger = new Loger();        
        
        try {

            $disabledTime = (int)Config::getOption('last_import_once_step_time', 0);

            if ($disabledTime > 0) {

                $currentTime = time();
                $timePassed = $currentTime - $disabledTime;
                
                if ($timePassed > self::AUTO_ENABLE_TIMEOUT) {

                    self::switchModuleWork(true, self::REASON_AGENT_AUTO_ON);
                    $logger->addInfoMessage(LangMsg::get('MODULE_SWITCHER_AUTO_ENABLE', [
                        '#TIMEOUT#' => self::AUTO_ENABLE_TIMEOUT
                    ]));
                    Config::setOption('last_import_once_step_time', '');
                    self::disableDelaySetModuleWork();

                } else {
                    $logger->addWarningMessage(LangMsg::get('MODULE_SWITCHER_AUTO_ENABLE_NOT_ENOUGH_TIME', [
                        '#TIMEOUT#' => self::AUTO_ENABLE_TIMEOUT
                    ]));
                }
    
            } else {
                $logger->addWarningMessage(LangMsg::get('MODULE_SWITCHER_IMPORT_ONCE_DISABLED_TIME_NOT_FOUND'));
                self::disableDelaySetModuleWork();
            }

        } catch (\Throwable $e) {
            $logger->addErrorMessage($e->getMessage());
        }

        $logger->exportLog(LangMsg::get('MODULE_SWITCHER_CHECK_MODULE_SWITCH_ON'));
    }

    private static $agentName = "set_module_auto_on();";

    public static function delaySetModuleWork(): void
    {
        $logger = new Loger();
        
        Agent::set(self::$agentName, 60, 0);
        
        $agentInfo = Agent::getInfo(self::$agentName);
        
        if (!empty($agentInfo) && isset($agentInfo['ID']) && $agentInfo['ID'] > 0) {
            $logger->addWarningMessage(LangMsg::get('MODULE_SWITCHER_DELAY_SET_MODULE_WORK', [
                '#AGENT_ID#' => $agentInfo['ID']
            ]));
        } else {
            $logger->addWarningMessage(LangMsg::get('MODULE_SWITCHER_AGENT_NOT_FOUND'));
        }

        $logger->exportLog(LangMsg::get('MODULE_SWITCHER_DELAY_ACTION'));
    }

    public static function disableDelaySetModuleWork(): void
    {
        $logger = new Loger();
        
        Agent::delete(self::$agentName);

        if (!Agent::isEnabledAgent(self::$agentName)) {            
            $logger->addWarningMessage(LangMsg::get('MODULE_SWITCHER_DISABLE_DELAY_SET_MODULE_WORK'));
        } else {
            $logger->addWarningMessage(LangMsg::get('MODULE_SWITCHER_AGENT_NOT_DELETED'));
        }
        
        $logger->exportLog(LangMsg::get('MODULE_SWITCHER_DISABLE_DELAY_ACTION'));
    }
}