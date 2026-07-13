<?php


namespace Ipolh\SDEK\SDEK;


use Exception;
use Ipolh\SDEK\Api\Entity\EncoderInterface;
use Ipolh\SDEK\Api\Logger\Psr\Log\LoggerInterface;
use Ipolh\SDEK\Core\Entity\CacheInterface;

class GeneralApplication
{
    /**
     * @var bool - shows if api mode is test or productive
     */
    protected $testMode = false;
    /**
     * @var bool - true if using custom URL for requests is allowed
     */
    protected $customAllowed = false;
    /**
     * @var EncoderInterface|null
     */
    protected $encoder;
    /**
     * @var LoggerInterface|null
     */
    protected $logger;
    /**
     * @var int
     */
    protected $timeout;
    /**
     * @var CacheInterface|null
     */
    protected $cache;
    /**
     * @var array
     * saves results of calculation via hash
     */
    protected $abyss;
    /**
     * @var bool
     * set - data won't get into the abyss
     */
    protected $blockAbyss = true;
    /**
     * @var string
     * shows how was made last request: via cache, taken from abyss or by actual request to server
     */
    protected $lastRequestType = false;
    /**
     * @var string
     */
    protected $hash;
    /**
     * @var ExceptionCollection
     * empty if no errors occurred, error-info otherwise
     */
    protected $errorCollection;
    
    /**
     * @return bool
     */
    public function isTestMode()
    {
        return $this->testMode;
    }

    /**
     * @param bool $testMode
     * @return $this
     */
    public function setTestMode($testMode)
    {
        $this->testMode = $testMode;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return EncoderInterface|null
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param EncoderInterface|null $encoder
     * @return $this
     */
    protected function setEncoder($encoder)
    {
        $this->encoder = $encoder;
        return $this;
    }

    /**
     * @return CacheInterface|null
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param CacheInterface|null $cache
     * @return $this
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @param mixed $data
     * @param string $hash
     * @return $this
     */
    public function toCache($data, $hash)
    {
        if (!$hash || $data === null || !$this->getCache()) {
            return $this;
        }

        $this->getCache()->setCache($hash, $data);
        return $this;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface|null $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return $this
     */
    public function allowCustom()
    {
        $this->customAllowed = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disallowCustom()
    {
        $this->customAllowed = false;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return array
     */
    public function getAbyss()
    {
        return $this->abyss;
    }

    /**
     * @param array $abyss
     * @return $this
     */
    public function setAbyss($abyss)
    {
        $this->abyss = $abyss;
        return $this;
    }

    /**
     * @param string $hash
     * @return null|mixed
     * checks whether same request was already done
     */
    public function checkAbyss($hash = '')
    {
        $hash = ($hash) ?: $this->getHash();
        if (
            !$this->blockAbyss &&
            $hash &&
            array_key_exists($hash, $this->abyss)
        ) {
            return $this->abyss[$hash];
        }
        return null;
    }

    /**
     * @param mixed $val
     * @param string $hash
     * @return $this
     * returns saved request
     */
    public function toAbyss($val, $hash = '')
    {
        $hash = ($hash) ? $hash : $this->getHash();
        if (!$this->blockAbyss && $hash) {
            $this->abyss[$hash] = $val;
        }

        return $this;
    }

    /**
     * @param bool $blockAbyss
     * @return $this
     */
    public function setAbyssLock($blockAbyss)
    {
        $this->blockAbyss = $blockAbyss;
        return $this;
    }

    /**
     * @return ExceptionCollection
     */
    public function getErrorCollection()
    {
        return $this->errorCollection;
    }

    /**
     * @param Exception $error - throwable (Exceptions)
     * @return $this
     */
    protected function addError($error)
    {
        $this->errorCollection->add($error);
        return $this;
    }

    /**
     * @return string
     */
    public function getLastRequestType()
    {
        return $this->lastRequestType;
    }
}