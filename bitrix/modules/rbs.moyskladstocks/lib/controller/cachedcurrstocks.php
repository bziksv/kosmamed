<?php
namespace Rbs\MoyskladStocks\Controller;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config; 
use Rbs\MoyskladStocks\LangMsg; 
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Controller\FileCacheManager;

class CachedCurrStocks
{
    private $chunkSize;
    private $params;
    private $cacheManager;
    private $isFullUpdate;
    
    private $currentChunk = 0;
    private $totalChunks = 0;
    private $currChunkCacheKey = 'stocks_report_current_chunk';
    private $totalItemsKey = 'stocks_report_total_items';
    private $fullUpdateStartTimeKey = 'full_update_start_time';

    public function __construct($params, $isFullUpdate = false)
    {
        $this->params = $params;
        $this->chunkSize = (int)Config::getOption('curr_stocks_p_full_limit', 10000);
        if ($this->chunkSize <= 0) {
            $this->chunkSize = 10000;
        }
        $this->cacheManager = new FileCacheManager('stock_data_', 'stock_cache');
        $this->currentChunk = (int)Config::getOption($this->currChunkCacheKey, 0);
        $this->isFullUpdate = $isFullUpdate;
    }

    /**
     * Validates all cache files and clears cache if any file is invalid
     * 
     * @return bool Returns true if cache is valid, false otherwise
     */
    private function validateCache(): bool
    {
        if (!$this->hasCachedData()) {
            return true;
        }
        try {
            $totalFiles = $this->cacheManager->getCacheFileCount();
            for ($i = 0; $i < $totalFiles; $i++) {
                $this->readCacheChunk($i);
            }
            return true;
        } catch (\Throwable $e) {
            $this->clearCache();
            return false;
        }
    }

    public function getNextChunk()
    {
        if ($this->hasCachedData() && !$this->validateCache()) {
            throw new \RuntimeException(LangMsg::get('EXCEPTION_CACHE_INVALID'));
        }

        if ($this->hasCachedData()) {
            $this->totalChunks = $this->cacheManager->getCacheFileCount();
            return $this->getChunkFromCache();
        }

        $msResult = ApiNew::get('/report/stock/bystore/current', $this->params);

        if ($this->isFullUpdate) {
            $this->setFullUpdateStartTime();
        }

        $totalItems = 0;
        if(is_array($msResult)) {
            $totalItems = count($msResult);
        }

        Config::setOption($this->totalItemsKey, $totalItems);

        if (Utils::has_errors($msResult)) {
            $this->clearFullUpdateStartTime();
            return $msResult;
        }

        if (!is_array($msResult)) {
            $this->clearFullUpdateStartTime();
            $this->clearCache();
            throw new \Exception(LangMsg::get('EXCEPTION_API_ERROR'));
        }

        if ($totalItems > $this->chunkSize) {
            $this->cacheData($msResult);
            return $this->getChunkFromCache();
        }

        return $msResult;
    }

    public function getTotalItems()
    {
        return (int)Config::getOption($this->totalItemsKey, 0);
    }

    public function getCurrentStep()
    {
        $totalItems = $this->getTotalItems();
        $currentStep = $this->currentChunk * $this->chunkSize;
        return min($currentStep, $totalItems);
    }

    public function hasCachedData()
    {
        return $this->cacheManager->hasCacheData();
    }

    private function cacheData($data)
    {
        $this->totalChunks = ceil(count($data) / $this->chunkSize);
        for ($i = 0; $i < $this->totalChunks; $i++) {
            $chunk = array_slice($data, $i * $this->chunkSize, $this->chunkSize);
            $this->writeCacheChunk($i, $chunk);
        }
    }

    public function isLastChunk()
    {
        return $this->currentChunk >= $this->totalChunks;
    }

    private function getChunkFromCache()
    {
        if ($this->isLastChunk()) {
            return null;
        }

        $chunk = $this->readCacheChunk($this->currentChunk);
        $this->currentChunk++;
        Config::setOption($this->currChunkCacheKey, $this->currentChunk);

        return $chunk;
    }

    private function readCacheChunk($chunkNumber)
    {
        return $this->cacheManager->read('chunk_' . $chunkNumber);
    }
    
    private function writeCacheChunk($chunkNumber, $data)
    {
        $this->cacheManager->write('chunk_' . $chunkNumber, $data);
    }

    public function clearCache()
    {
        $this->cacheManager->clear();
        $this->currentChunk = 0;
        Config::setOption($this->currChunkCacheKey, 0);
        $this->totalChunks = 0;
    }

    private function setFullUpdateStartTime()
    {
        Config::setOption($this->fullUpdateStartTimeKey, time() - 5);
    }

    public function getFullUpdateStartTime()
    {
        return Config::getOption($this->fullUpdateStartTimeKey);
    }

    public function clearFullUpdateStartTime()
    {
        Config::setOption($this->fullUpdateStartTimeKey, null);
    }

    public function isLastChunkOfFullUpdate()
    {
        return $this->isFullUpdate && $this->isLastChunk();
    }
}