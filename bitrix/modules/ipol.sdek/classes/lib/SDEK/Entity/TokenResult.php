<?php


namespace Ipolh\SDEK\SDEK\Entity;


use Ipolh\SDEK\Api\Entity\Response\Oauth as ObjResponse;

/**
 * Class TokenResult
 * @package Ipolh\SDEK\SDEK
 * @subpackage Entity
 * @method ObjResponse getResponse
 */
class TokenResult extends AbstractResult
{
	/**
	 * @var string
	 */
	protected $accessToken;
	/**
	 * @var int - sec
	 */
	protected $expiresIn;

	/**
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}

	/**
	 * @param string $accessToken
	 * @return TokenResult
	 */
	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getExpiresIn()
	{
		return $this->expiresIn;
	}

    /**
     * @param int $expiresIn
     * @return TokenResult
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }

	public function parseFields()
	{
	    if ($this->getResponse()) {
            $this->setAccessToken($this->getResponse()->getAccessToken())
                ->setExpiresIn($this->getResponse()->getExpiresIn());
        }
	}

}