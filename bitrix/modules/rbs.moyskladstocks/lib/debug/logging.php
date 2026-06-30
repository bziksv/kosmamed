<?php
namespace Rbs\MoyskladStocks\Debug;

trait Logging
{
	private $logger;
	
	public function setLogger(Loger $logger)
	{
		$this->logger = $logger;
	}
	
	public function addSuccessMessage($msg = '')
	{
		$this->logger->addMessage($msg, Message::TYPE_SUCCESS);
	}
	
	public function addWarningMessage($msg = '')
	{
		$this->logger->addMessage($msg, Message::TYPE_WARNING);
	}
	
	public function addInfoMessage($msg = '')
	{
		$this->logger->addMessage($msg, Message::TYPE_INFO);
	}
	
	public function addErrorMessage($msg = '')
	{
		$this->logger->addMessage($msg, Message::TYPE_ERROR);
	}

	public function addMessageArray($msgs = [], $type = Message::TYPE_INFO)
    {
		$this->logger->addMessageArray($msgs, $type);
	}

	public function getLogger()
	{
		return $this->logger;
	}
}
