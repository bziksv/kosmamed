<?php
namespace Rbs\MoyskladStocks\Debug;

class Writer
{
    private $headMessage = '';
    private $logFileName = '';
    private $logMessages = [];

    public function __construct($headMessage = '')
    {
        if (empty($headMessage)) {
            $headMessage = 'DEBUG';
        }
        $this->headMessage = $headMessage;
    }

    public function setLogerMessages(array $messages = [])
    {
        if (count($messages) > 0) {
            $this->logMessages = array_merge($this->logMessages, $messages);
        }
        return $this;
    }

    public function setLogFileName($logFileName = '')
    {
        $this->logFileName = $logFileName;
        return $this;
    }

    public function exportLog()
    {
        $dateTime = new \DateTime();

        $file = new File($this->logFileName);

        $file->open();
        $file->write($this->headMessage . ' >>> ' . $dateTime->format('d.m.Y H:i:s'));
        if (count($this->logMessages) > 0) {
            foreach($this->logMessages as $msg){
                if($msg instanceof Message && $msg->isValid()){
                    $file->write($msg->getText() . ' >>> ' . $msg->getType());
                } else if(gettype($msg) === 'string') {
                    $file->write($msg . ' >>> ' . Message::TYPE_INFO);
                }
            }
        }
        $file->write('<<<>>>');
        $file->close();

        if ($file->isMaxFileSize()) {
            $file->refreshFile();
        }
        
        return $this;
    }
}
