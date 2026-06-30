<?php
namespace Ipolh\SDEK\Core\Entity\Result;

/**
 * Class Result
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 */
class Result
{
    // TODO: Implement Fields trait

    /**
     * Separators for Result::getErrorsString(), Result::getWarningsString(), Result::getMessagesString()
     */
    const SEPARATOR_NEW_LINE = "\n\n";
    const SEPARATOR_COMMA    = ", ";

    /**
     * @var bool Success flag
     */
    protected $success = true;

    /**
     * @var ErrorCollection Critical errors
     */
    protected $errors;

    /**
     * @var WarningCollection Warnings and non-critical errors
     */
    protected $warnings;

    /**
     * @var MessageCollection Common messages
     */
    protected $messages;

    /**
     * @var mixed|null Arbitrary useful data can be stored here
     */
    protected $data;

    /**
     * @var mixed|null API response object
     */
    protected $response;

    /**
     * @var int|string|null HTTP status or some API identifier can be stored here
     */
    protected $code;

    public function __construct()
    {
        $this->errors   = new ErrorCollection();
        $this->warnings = new WarningCollection();
        $this->messages = new MessageCollection();
    }

    /**
     * Returns result status
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return Result
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * Adds error and sets success flag to false
     * @param Error $error
     * @return $this
     */
    public function addError($error)
    {
        $this->success = false;
        $this->errors->add($error);
        return $this;
    }

    /**
     * Adds errors from given collection and sets success flag to false
     * @param ErrorCollection $collection
     * @return $this
     */
    public function addErrors($collection)
    {
        $this->success = false;
        $this->errors->append($collection);
        return $this;
    }

    /**
     * Returns error collection
     * @return ErrorCollection
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Returns array of Error objects
     * @return Error[]
     */
    public function getErrorsArray()
    {
        return $this->errors->toArray();
    }

    /**
     * Returns error texts concatenated into one string
     * @param string $separator @see Result::SEPARATOR_NEW_LINE, Result::SEPARATOR_COMMA
     * @return string
     */
    public function getErrorsString($separator = self::SEPARATOR_COMMA)
    {
        return implode($separator, $this->errors->getMessages());
    }

    /**
     * Adds warning
     * @param Warning $warning
     * @return $this
     */
    public function addWarning($warning)
    {
        $this->warnings->add($warning);
        return $this;
    }

    /**
     * Adds warnings from given collection
     * @param WarningCollection $collection
     * @return $this
     */
    public function addWarnings($collection)
    {
        $this->warnings->append($collection);
        return $this;
    }

    /**
     * Returns warning collection
     * @return WarningCollection
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Returns array of Warning objects
     * @return Warning[]
     */
    public function getWarningsArray()
    {
        return $this->warnings->toArray();
    }

    /**
     * Returns warning texts concatenated into one string
     * @param string $separator @see Result::SEPARATOR_NEW_LINE, Result::SEPARATOR_COMMA
     * @return string
     */
    public function getWarningsString($separator = self::SEPARATOR_COMMA)
    {
        return implode($separator, $this->warnings->getMessages());
    }

    /**
     * Adds message
     * @param Message $message
     * @return $this
     */
    public function addMessage($message)
    {
        $this->messages->add($message);
        return $this;
    }

    /**
     * Adds messages from given collection
     * @param MessageCollection $collection
     * @return $this
     */
    public function addMessages($collection)
    {
        $this->messages->append($collection);
        return $this;
    }

    /**
     * Returns message collection
     * @return MessageCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Returns array of Message objects
     * @return Message[]
     */
    public function getMessagesArray()
    {
        return $this->messages->toArray();
    }

    /**
     * Returns message texts concatenated into one string
     * @param string $separator @see Result::SEPARATOR_NEW_LINE, Result::SEPARATOR_COMMA
     * @return string
     */
    public function getMessagesString($separator = self::SEPARATOR_COMMA)
    {
        return implode($separator, $this->messages->getMessages());
    }

    /**
     * Returns arbitrary data stored in result
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets arbitrary data of the result
     * @param mixed|null $data
     * @return Result
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns API response stored in result
     * @return mixed|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Sets API response of the result
     * @param mixed|null $response
     * @return Result
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * Returns code
     * @return int|string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets code
     * @param int|string|null $code
     * @return Result
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}