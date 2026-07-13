<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\BadRequestException;

/**
 * Class Oauth
 * @package Ipolh\SDEK\Api
 * @subpackage Request
 */
class Oauth extends AbstractRequest
{
    /**
     * @var string
     */
    protected $client_id;
    /**
     * @var string
     */
    protected $client_secret;
    /**
     * @var string
     */
    protected $grant_type = 'client_credentials';

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param string $login
     * @return $this
     * @throws BadRequestException
     */
    public function setClientId($login)
    {
        if (strlen($login) > 50) {
            throw new BadRequestException('Wrong login');
        } elseif (!strlen($login)) {
            throw new BadRequestException('No login given');
        }
        $this->client_id = $login;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * @param mixed $password
     * @return $this
     * @throws BadRequestException (bad password)
     */
    public function setClientSecret($password)
    {
        if (!strlen($password)) {
            throw new BadRequestException('No password given');
        }
        $this->client_secret = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getGrantType()
    {
        return $this->grant_type;
    }
}