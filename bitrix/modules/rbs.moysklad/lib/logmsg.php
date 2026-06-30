<?php
/**
 * @deprecated 2025-08-28
 * @see Logger
 */


namespace Rbs\Moysklad;

class LogMsg
{
   const TYPE_MAIN = 'main';
   const TYPE_ERROR = 'error';
   const TYPE_INFO = 'info';
   const TYPE_WARNING = 'warning';
   const TYPE_SUCCESS = 'success';

   private $msg = '';
   private $type = '';
   private $errorCode = '';

   function __construct($msg = '', $type = '', $errorCode = '')
   {
      $this->msg = $msg;
      $this->type = $type;
      $this->errorCode = (int)$errorCode;
   }

   static function addError($msg = '', $code = '')
   {
      return new self($msg, self::TYPE_ERROR, $code);
   }

   static function addWarning($msg)
   {
      return new self($msg, self::TYPE_WARNING);
   }

   static function addInfo($msg)
   {
      return new self($msg, self::TYPE_INFO);
   }

   static function addMain($msg)
   {
      return new self($msg, self::TYPE_MAIN);
   }

   static function addSuccess($msg)
   {
      return new self($msg, self::TYPE_SUCCESS);
   }

   function getMessage()
   {
      return $this->msg;
   }

   function getErrorCode()
   {
      return $this->errorCode;
   }

   function getType()
   {
      return $this->type;
   }

   function isValid()
   {
      return !empty($this->msg) && !empty($this->type);
   }
}