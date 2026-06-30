<?php
/**
 * @deprecated 2025-08-28
 * @see Logger
 */


namespace Rbs\Moysklad;

use \Bitrix\Main\Diag\Debug;

class Logger
{
    private static $traceDepth = 6;
    private static $maxFileSize = 512; //KB

    private static $fileDebugLog = 'debug.log';
    private static $fileExchangeLog = 'mainlog.log';

    public static function debugMsg($logVar = null, $fileName = '')
    {
        if(empty($fileName)){
            $fileName = self::$fileDebugLog;
        }
        
        $logFile = self::getLogFile($fileName);
        
        $date = new \DateTime();
        $backTrace = self::getBackTrace();

        $logPices = [
            $date->format('d.m.Y H:i:s'),
            print_r($logVar, true),
            $backTrace,
            '__________________________'
        ];

        $logStr = implode("\n\n", $logPices);
        $logStr = str_replace("\n\n\n", "\n\n", $logStr);

        Debug::writeToFile($logStr, '', $logFile);
    }
    
    public static function exchangeMsg($message = '', $type = 'debug', $logId = '0', $errorCode = 0)
    {
        if($type === 'error'){
            $notificator = new Notification($message, $errorCode);
            $notificator->checkNotifications();
        }

        $logFile = self::getLogFile(self::$fileExchangeLog);

        $date = new \DateTime();

        $logPices = [
            "<div class='log-message log-{$type}' data-log-id='{$logId}'>",
                "<span>",
                    $message,
                "</span>",
                "<span>",
                    $date->format('d.m.Y H:i:s'),
                "</span>",
            "</div>"
        ];

        $logStr = implode("", $logPices);
        Debug::writeToFile($logStr, '', $logFile);
    }

    public static function getExchangeLog()
    {
        $fileLog = self::getLogFullDir() . self::$fileExchangeLog;
        if(file_exists($fileLog)){
            return file_get_contents($fileLog);
        }
    }

    public static function getDirHref()
    {
        $href = str_replace('/lib/../', '/', self::getLogDir());
        return '/bitrix/admin/fileman_admin.php?lang=' . LANG . '&path=' . urlencode($href);
    }

    public static function getDebugLog()
    {
        $fileLog = self::getLogFullDir() . self::$fileDebugLog;
        if(file_exists($fileLog)){
            return file_get_contents($fileLog);
        }
    }

    public static function getLogFile($file = '')
    {
        if(empty($file)){
            $fileName = 'otherlog.log';
        } else {
            $fileName = $file;
        }
        
        $fullFileRealPath = self::getLogFullDir() . $fileName;
        $fullFilePath = self::getLogDir() . $fileName;

        if(\file_exists($fullFileRealPath)){
            $maxFileSize = self::$maxFileSize;
            if(filesize($fullFileRealPath) / 1024 >= $maxFileSize){
                rename($fullFileRealPath, str_replace($fileName, $fileName . "_" . time(), $fullFileRealPath));
            }
        }
        
        return $fullFilePath;
    }

    public static function getBackTrace()
    {
        $arBacktrace = \Bitrix\Main\Diag\Helper::getBackTrace(self::$traceDepth, DEBUG_BACKTRACE_IGNORE_ARGS);

        $strFunctionStack = "";
        $strFilesStack = "";
        $firstFrame = (Utils::count($arBacktrace) == 1? 0 : 2);
        $iterationsCount = min(Utils::count($arBacktrace), self::$traceDepth);
        for ($i = $firstFrame; $i < $iterationsCount; $i++)
        {
            if (mb_strlen($strFunctionStack)>0)
                $strFunctionStack .= " < ";

            if (isset($arBacktrace[$i]["class"])){
                $strFunctionStack .= $arBacktrace[$i]["class"]."::";
            }

            $strFunctionStack .= $arBacktrace[$i]["function"];

            if(isset($arBacktrace[$i]["file"])){
                $strFilesStack .= "\t".$arBacktrace[$i]["file"].":".$arBacktrace[$i]["line"]."\n";
            }
        }

        return $strFunctionStack."\n".$strFilesStack;
    }

    public static function getLogFullDir()
    {
        $directoryName = \realpath(__DIR__) . '/../logs/';
        
        if(Config::getProfileId() > 0){
            $directoryName = \realpath(__DIR__) . '/../logs/profile_' . Config::getProfileId() . '/';
        }

        if(!is_dir($directoryName)){
            mkdir($directoryName, 0755, true);
        }
        return $directoryName;
    }

    public static function getLogDir()
    {
        return \str_replace(realpath(dirname(__FILE__)."/../../../.."), '', self::getLogFullDir());
    }

    public static function clearLogsDir()
    {
        $files = glob(self::getLogFullDir() . "*");
        if (Utils::count($files) > 0) {
            foreach ($files as $file) {      
                if (file_exists($file)) {
                    unlink($file);
                }   
            }
        }
    }
}