<?php
namespace Ipolh\SDEK\Bitrix\Admin;

// use Ipolh\SDEK\Bitrix\Tools;

class Logger
{
	protected static $logFile = false;
	
	protected static $MODULE_ID = IPOLH_SDEK;
	
	protected static $widjetAction = 'isdek_action';
	
	protected static function getLogFileName()
	{
		return $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::$MODULE_ID.'/log.txt';
	}
	
	protected static function getLogFile()
	{
		if(!self::$logFile){
			self::$logFile = fopen(self::getLogFileName(),self::getOption('debug_fileMode'));
		}

		return self::$logFile;
	}
	
	protected static function getOption($code,$default=false)
	{
		$arOptions = array(
			'debug_fileMode' => array(
				'allow'   => array('w','a'),
				'default' => 'w'
			),
			'debug_startLogging'  => array(
				'default' => 'Y'
			),
			'debug_calculation'   => array(
				'default' => 'Y'
			),
			'debug_turnOffWidget' => array(
				'default' => 'Y'
			),
			'debug_compability'   => array(
				'default' => 'Y'
			),
			'debug_calculate'     => array(
				'default' => 'Y'
			),
			'debug_shipments'     => array(
				'default' => 'Y'
			),
			'debug_orderSend'     => array(
				'default' => 'Y'
			),
			'debug_statusCheck'   => array(
				'default' => 'Y'
			)
		);
		
		if(array_key_exists($code,$arOptions)){
			$value = \Ipolh\SDEK\option::get($code);
			
			if(array_key_exists('allow',$arOptions[$code])){
				$value = in_array($value,$arOptions[$code]['allow']) ? $value : $arOptions[$code]['default'];
			} elseif(!$value){
				return $arOptions[$code]['default'];
			}
			
			return $value;
		}
		
		return false;
	}

	protected static function checkPermiss($event=false){
		if(self::getOption('debug_startLogging') == 'Y'){
			$subReturn = true;
			
			if($event){
				$optname = 'debug_'.$event;
				$subReturn = (self::getOption($optname) == 'Y');
			}

			// widget
			if(
				$subReturn &&
				self::getOption('debug_turnOffWidget') == 'Y' &&
				($_REQUEST[self::$widjetAction]=='countDelivery' || $_REQUEST['action']=='countDelivery')
			){
				return false;
			}

			return $subReturn;
		}
		return false;
	}
	
	protected static function fromJSON($wat){
		if(method_exists('sdekHelper','zaDEjsonit')){
			$wat = sdekHelper::zaDEjsonit($wat);
		}
		return $wat;
	}

    public static function toLog($wat,$sign=''){
		$lbl = '';
		if(!self::$logFile){
			$lbl = "\n" . date('H:i:s d.m.Y') . "\n";
		}
		
		if($sign){
			$sign .= "\n";
		}
		
		$file = self::getLogFile();
		
		return fwrite($file,$lbl.$sign.print_r($wat,true));
    }
	
	public static function getLog(){
		$fileName = self::getLogFileName();
		if(file_exists($fileName)){
			return file_get_contents($fileName);
		}
		
		return false;
	}
	
	public static function killLog(){
		$fileName = self::getLogFileName();
		if(file_exists($fileName)){
			unlink($fileName);
		}
	}
	
	public static function calculation($wat){
		if(self::checkPermiss('calculation')){
			self::toLog($wat,\getMessage('IPOLSDEK_LOGGING_calculation'));
		}
	}
	
	public static function compability($wat){
		if(self::checkPermiss('compability')){
			self::toLog($wat,\getMessage('IPOLSDEK_LOGGING_compability'));
		}
	}
	
	public static function calculate($wat){
		if(self::checkPermiss('calculate')){
			self::toLog($wat,\getMessage('IPOLSDEK_LOGGING_calculate'));
		}
	}

	public static function shipments($wat){
		if(self::checkPermiss('shipments')){
			self::toLog($wat,\getMessage('IPOLSDEK_LOGGING_shipments'));
		}
	}

	public static function orderSend($wat){
		if(self::checkPermiss('orderSend')){
			self::toLog($wat,\getMessage('IPOLSDEK_LOGGING_orderSend'));
		}
	}

	public static function statusCheck($wat){
		if(self::checkPermiss('statusCheck')){
			self::toLog($wat,\getMessage('IPOLSDEK_LOGGING_statusCheck'));
		}
	}


}