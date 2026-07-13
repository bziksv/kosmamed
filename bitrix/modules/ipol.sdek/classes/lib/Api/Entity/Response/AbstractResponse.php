<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class AbstractResponse
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class AbstractResponse extends AbstractEntity
{
    /**
     * @var string
     */
    protected $origin;

    /**
     * @var mixed
     */
    protected $decoded;
    /**
     * @var bool
     */
    protected $Success;

    /**
     * AbstractResponse constructor.
     * @param $json
     * @throws BadResponseException
     */
    function __construct($json)
    {
        parent::__construct();

        $this->origin = $json;

        if (empty($json)) {
            throw new BadResponseException('Empty server answer ' . __CLASS__);
        }

        $this->setDecoded(json_decode($json));

        if (is_null($this->decoded)) {
            throw new BadResponseException('Incorrect server answer (fail to decode) ' . __CLASS__);
        }
    }

    /**
     * @return mixed
     */
    public function getDecoded()
    {
        return $this->decoded;
    }

    /**
     * @param mixed $decoded
     * @return $this
     */
    public function setDecoded($decoded)
    {
        $this->decoded = $decoded;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return $this->Success;
    }

    /**
     * @param bool $Success
     * @return $this
     */
    public function setSuccess($Success)
    {
        $this->Success = $Success;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     * @return AbstractResponse
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;
        return $this;
    }

}