<?php
/**
 * @deprecated 2025-08-28
 * @see Logger
 */

namespace Rbs\Moysklad;

class Log
{
   
   private $logId = '';
   private $hasErrors = false;
   private $hasWarnings = false;
   private $isWrite = true;
   private $logStack = [];

   function getLogId()
   {
       if(empty($this->logId)){
           $this->logId = \randString();
       }
       return $this->logId;
   }

   function __construct($featureLog = '')
   {
      $this->logId = \randString();
   }

   function addLogMessage(LogMsg $message)
   {
      if($message->isValid()){

         if($message->getType() === LogMsg::TYPE_ERROR) {
            $this->hasErrors = true;
         }

         if ($message->getType() === LogMsg::TYPE_WARNING) {
            $this->hasWarnings = true;
         }

         if($this->isWrite) {
            Logger::exchangeMsg($message->getMessage(), $message->getType(), $this->getLogId(), $message->getErrorCode());
         } else {
            $this->logStack[] = $message;
         }
         
      }
   }

   function exportLog()
   {
      if(count($this->logStack) > 0){
         foreach($this->logStack as $message) {
            Logger::exchangeMsg($message->getMessage(), $message->getType(), $this->getLogId(), $message->getErrorCode());
         }
      }
   }

   function writerOff()
   {
      $this->isWrite = false;
   }

   function hasErrors()
   {
      return $this->hasErrors;
   }

   function hasWarnings()
   {
      return $this->hasWarnings;
   }

   function addLogMessageArray(array $messages)
   {
      foreach($messages as $message){
         $this->addLogMessage($message);
      }
   }

   function addDebugMessage($message)
   {
      if (!empty($message)) {
          Logger::debugMsg($message);
      }
   }
}