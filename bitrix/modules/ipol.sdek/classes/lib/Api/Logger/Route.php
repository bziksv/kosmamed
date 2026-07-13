<?php
namespace Ipolh\SDEK\Api\Logger;

/**
 * Class Route
 * @package Ipolh\SDEK\Api\Logger
 */
abstract class Route
{
    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return $this
     */
    public function enable()
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function disable()
    {
        $this->enabled = false;
        return $this;
    }

    abstract public function log($dataString);

    abstract public function read();
}