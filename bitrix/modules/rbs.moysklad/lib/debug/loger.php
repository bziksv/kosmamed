<?php
namespace Rbs\Moysklad\Debug;

class Loger
{
    private $messageArray = [];
    private $errorMessageArray = [];
    private $timeStart = 0;

    public function __construct()
    {
        $this->timeStart = microtime(true);
    }

    public function addSuccessMessage($msg = '')
    {
        $this->addMessage($msg, Message::TYPE_SUCCESS);
    }

    public function addWarningMessage($msg = '')
    {
        $this->addMessage($msg, Message::TYPE_WARNING);
    }

    public function addInfoMessage($msg = '')
    {
        $this->addMessage($msg, Message::TYPE_INFO);
    }

    public function addErrorMessage($msg = '')
    {
        $this->addMessage($msg, Message::TYPE_ERROR);
    }

    public function addFinishMessage($msg = '')
    {
        if ($this->hasErrors()) {
            $this->addInfoMessage($msg);
        } else {
            $this->addSuccessMessage($msg);
        }
    }

    public function addMessage($msg = '', $type = Message::TYPE_INFO)
    {
        $message = new Message($msg, $type);
        if ($message->isValid()) {
            $this->messageArray[] = $message;
            if ($type === Message::TYPE_ERROR) {
                $this->errorMessageArray[] = $message;
            }
        }
        return $this;
    }

    public function addMessageArray($msgs = [], $type = Message::TYPE_INFO)
    {
        foreach ($msgs as $msg) {
            if ($msg instanceof Message && $msg->isValid()) {
                $this->messageArray[] = $msg;
                if ($type === Message::TYPE_ERROR) {
                    $this->errorMessageArray[] = $msg;
                }
            } else {
                $this->addMessage($msg, $type);
            }
        }
        return $this;
    }

    public function addErrorMessageArray($msgs = [])
    {
        $this->addMessageArray($msgs, Message::TYPE_ERROR);
    }

    public function getMessageArray(): array
    {
        return $this->messageArray;
    }

    public function getErrorMessageArray(): array
    {
        return $this->errorMessageArray;
    }

    public function hasMessages(): bool
    {
        return count($this->messageArray) > 0;
    }

    public function hasErrors(): bool
    {
        return count($this->errorMessageArray) > 0;
    }

    public function getLogTime()
    {
        return round(microtime(true) - $this->timeStart, 4);
    }

    public function exportLog(string $headMsg = '')
    {
        (new Writer($headMsg))->setLogerMessages($this->getMessageArray())->exportLog();
    }
    
    public static function debugMsg($var = null)
    {
        $logMsg = str_replace("\n", "///n", print_r($var, true));
        (new Writer('DEBUG'))->setLogerMessages([new Message("<pre>{$logMsg}</pre>", 'info')])->exportLog();
    }

}
