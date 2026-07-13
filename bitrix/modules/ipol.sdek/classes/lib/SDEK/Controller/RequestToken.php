<?php


namespace Ipolh\SDEK\SDEK\Controller;


use Ipolh\SDEK\Api\BadRequestException;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Request\Oauth;
use Ipolh\SDEK\SDEK\AppLevelException;
use Ipolh\SDEK\SDEK\Entity\TokenResult;

/**
 * Class RequestController
 * @package Ipolh\SDEK\SDEK\Controller
 */
class RequestToken extends RequestController
{
    /**
     * RequestToken constructor.
     * @param string $clientId
     * @param string $clientSecret
     * @throws BadRequestException
     */
	public function __construct($clientId, $clientSecret)
	{
		$data = new Oauth();
		$data->setClientId($clientId)->setClientSecret($clientSecret);

		$this->setRequestObj($data);
	}
    
    /**
     * @return TokenResult
     */
	public function execute()
    {
        $result = new TokenResult();

        try {
            $request = $this->getSdk()->oauth($this->getRequestObj());
            if ($request->getResponse()->getAccessToken()) {
                $result->setSuccess(true)->setResponse($request->getResponse());
            }

            if ($result->isSuccess()) {
                $result->parseFields();
            }

        } catch (BadRequestException $e) {
            $result->setError($e)->setSuccess(false);
        } catch (BadResponseException $e) {
            $result->setError($e)->setSuccess(false);
        } catch (AppLevelException $e) {
            $result->setError($e)->setSuccess(false);
        } finally {
            return $result;
        }
    }
}