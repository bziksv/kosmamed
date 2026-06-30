<?php
namespace Ipolh\SDEK\Core\Entity\Result;

/**
 * Class Info - some information message like error or warning
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 */
class Info implements \JsonSerializable
{
    /**
     * @var int|string|null
     */
    protected $code;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param string $message
     * @param int|string|null $code
     */
    public function __construct($message, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * Returns code of the info
     * @return int|string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns text of the info
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (isset($this->code) ? '['.$this->code.'] ' : '').$this->message;
    }

    /**
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'message' => $this->getMessage(),
            'code'    => $this->getCode()
        ];
    }
}