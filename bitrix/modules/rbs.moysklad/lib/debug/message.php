<?php
namespace Rbs\Moysklad\Debug;

class Message
{
   public const TYPE_MAIN = 'main';
   public const TYPE_ERROR = 'error';
   public const TYPE_INFO = 'info';
   public const TYPE_WARNING = 'warning';
   public const TYPE_SUCCESS = 'success';

   private $msg = '';
   private $type = '';

   function __construct($msg = '', $type = '')
   {
      $this->msg = $msg;
      $this->type = $type;
   }

   public static function addError($msg)
   {
      return new self($msg, self::TYPE_ERROR);
   }

   public static function addWarning($msg)
   {
      return new self($msg, self::TYPE_WARNING);
   }

   public static function addInfo($msg)
   {
      return new self($msg, self::TYPE_INFO);
   }

   public static function addMain($msg)
   {
      return new self($msg, self::TYPE_MAIN);
   }

   public static function addSuccess($msg)
   {
      return new self($msg, self::TYPE_SUCCESS);
   }

   public function getText()
   {
      return $this->msg;
   }

   public function getType()
   {
      return $this->type;
   }

   public function isValid()
   {
      return !empty($this->msg) && !empty($this->type);
   }
}
