<?php
//TODO Временно не используется. Реализация интерфейса требует отдельного обсуждения.
use CPHPCache;

class BitrixCache implements CacheInterface
{

    private $cachePath = 'dellindev';
    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        $cache = new CPHPCache();
        if($this->has($key)){
            return $cache->GetVars();
        }
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = 600)
    {
        $cache = new CPHPCache();
        $cache->StartDataCache($ttl, $key);
        $cache->EndDataCache(array('VALUE' => $value));
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        $cache = new CPHPCache();
      return $cache->InitCache(600, $key, $this->cachePath);
    }
}