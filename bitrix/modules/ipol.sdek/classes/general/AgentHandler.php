<?php
namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Tools;

/**
 * Class AgentHandler
 * @package Ipolh\SDEK
 */
class AgentHandler extends abstractGeneral
{
    /**
     * @return string
     * checking cdek-number in sended invoices
     */
    public static function getSendedOrdersState()
    {
        \Ipolh\SDEK\StatusHandler::getSendedOrdersState();
        return self::getAgentPath().'::getSendedOrdersState();';
    }

    /**
     * @return string
     * agent for loading SDEK-info (PVZ, etc)
     */
    public static function updateList()
    {
        \sdekOption::agentUpdateList();
        return self::getAgentPath().'::updateList();';
    }

    /**
     * @return string
     * agent for checking statuses
     */
    public static function orderStates()
    {
        \sdekOption::agentOrderStates();
        return self::getAgentPath().'::orderStates();';
    }

    /**
     * Updates statuses of Courier calls
     * @return string
     */
    public static function getCourierCallStates()
    {
        \Ipolh\SDEK\CourierCallHandler::getCourierCallStates();
        return self::getAgentPath().'::getCourierCallStates();';
    }

    /**
     * Unmake old uploaded invoice and barcode files
     * @return string
     */
    public static function unmakeOldFiles()
    {
        \Ipolh\SDEK\PrintHandler::unmakeOldFiles('invoice', 86400);
        \Ipolh\SDEK\PrintHandler::unmakeOldFiles('barcode', 86400);

        return self::getAgentPath().'::unmakeOldFiles();';
    }

    /**
     * @return void
     * special for update with agents
     */
    public static function rebuildAgents()
    {
        \CAgent::RemoveModuleAgents(self::$MODULE_ID);
        self::addModuleAgents();
    }

    /**
    * SERVICE
    */

    /**
     * @return array[]
     * returns list of agents of type "name" => array(path to agent, moduleId, interval in secs)
     */
    public static function getAgentList()
    {
        $agents = array(
            Tools::getMessage('AGENT_UPDATELIST')    => array("updateList", self::$MODULE_ID),
            Tools::getMessage('AGENT_ORDERSTATES')   => array("orderStates", self::$MODULE_ID, option::get('orderStatusesAgentRollback') * 60),
            Tools::getMessage('AGENT_ORDERCHECKS')   => array("getSendedOrdersState", self::$MODULE_ID, 1800),
            Tools::getMessage('AGENT_COURIERSTATES') => array("getCourierCallStates", self::$MODULE_ID, 900),
            Tools::getMessage('AGENT_UNMAKEFILES')   => array('unmakeOldFiles', self::$MODULE_ID, 86400),
        );

        return $agents;
    }

    /**
     * @param $agent
     * @param $interval
     * @return false|mixed|null
     * adding one agent for corresponding module
     */
    public static function addAgent($agent, $interval = 86400)
    {
        $result = null;
        if(
            method_exists(self::getAgentPath(),$agent) &&
            !in_array($agent,array('addAgent','getAgentList','addModuleAgents','remakeStatusCheckAgent'))
        ){
            $result = \CAgent::AddAgent('\\'.self::getAgentPath().'::'.$agent.'();',self::$MODULE_ID,"N",$interval);
        }

        return $result;
    }

    /**
     * @return void
     * Adds all module agents, used while logging
     */
    public static function addModuleAgents()
    {
        $agents = self::getAgentList();

        foreach ($agents as $agent) {
            self::addAgent($agent[0],(array_key_exists(2,$agent)) ? $agent[2] : 86400);
        }
    }

    /**
     * @param int $newVal - interval in mins
     * @return void
     * Remaking agent for updating statuses if rollback is changed in options
     */
    public static function remakeStatusCheckAgent($newVal = 60)
    {
        $agents = self::getAgentList();
        $need   = (array_key_exists(Tools::getMessage('AGENT_ORDERSTATES'),$agents)) ? $agents[Tools::getMessage('AGENT_ORDERSTATES')] : false;
        if($need){
            \CAgent::RemoveAgent(self::getAgentPath().'::'.$need[0].'();',self::$MODULE_ID);
            self::addAgent($need[0],$newVal*60);
        }
    }

    public static function getAgentPath()
    {
        return 'Ipolh\SDEK\AgentHandler';
    }
}