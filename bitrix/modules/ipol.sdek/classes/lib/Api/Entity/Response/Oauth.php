<?php
namespace Ipolh\SDEK\Api\Entity\Response;

/**
 * Class Oauth
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class Oauth extends AbstractResponse
{
    /**
     * @var string $access_token
     */
    protected $access_token;
    /**
     * @var string $token_type
     */
    protected $token_type;
    /**
     * @var int $expires_in
     */
    protected $expires_in;
    /**
     * @var string $scope
     */
    protected $scope;
    /**
     * @var string $jti
     */
    protected $jti;

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @param string $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->token_type;
    }

    /**
     * @param string $token_type
     */
    public function setTokenType($token_type)
    {
        $this->token_type = $token_type;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }

    /**
     * @param int $expires_in
     */
    public function setExpiresIn($expires_in)
    {
        $this->expires_in = $expires_in;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getJti()
    {
        return $this->jti;
    }

    /**
     * @param string $jti
     */
    public function setJti($jti)
    {
        $this->jti = $jti;
    }

}