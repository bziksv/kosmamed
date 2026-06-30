<?php

namespace Rbs\MoyskladStocks\Controller;

use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Config;

class FileCacheManager
{
	private $cachePrefix;
	private $cacheDir;
	private $moduleDir;

	public function __construct($cachePrefix, $cacheDir)
	{
		$this->cachePrefix = $cachePrefix;
		$this->moduleDir = dirname(__DIR__, 2);
		$this->cacheDir = $this->moduleDir . '/cache/profile_' . Config::getProfileId() . '/' . trim($cacheDir, '/') . '/';
		$this->createCacheDirectory();
	}

	/**
	 * Creates the cache directory
	 *
	 * @throws \RuntimeException if failed to create the directory
	 */
	private function createCacheDirectory()
	{
		if (!is_dir($this->cacheDir)) {
			if (!mkdir($this->cacheDir, 0755, true)) {
				throw new \RuntimeException(LangMsg::get('EXCEPTION_CACHE_DIR_CREATE_ERROR'));
			}
		}

		if (!is_writable($this->cacheDir)) {
			throw new \RuntimeException(LangMsg::get('EXCEPTION_CACHE_DIR_NOT_WRITABLE'));
		}
	}

	/**
	 * Writes data to a cache file
	 *
	 * @param string $key Cache key
	 * @param mixed $data Data to write
	 * @throws \RuntimeException if failed to write data
	 */
	public function write($key, $data)
	{
		$filename = $this->getCacheFilename($key);
		$content = serialize($data);

		if (file_put_contents($filename, $content) === false) {
			throw new \RuntimeException(LangMsg::get('EXCEPTION_CACHE_WRITE_ERROR'));
		}
	}

	/**
	 * Returns the number of cache files considering the prefix
	 *
	 * @return int Number of cache files
	 */
	public function getCacheFileCount()
	{
		$files = glob($this->cacheDir . $this->cachePrefix . '*');
		return count($files);
	}

	public function hasCacheData()
	{
		return $this->getCacheFileCount() > 0;
	}

	/**
	 * Reads data from a cache file
	 *
	 * @param string $key Cache key
	 * @return mixed Data from cache or null if file not found
	 * @throws \RuntimeException if failed to read or unserialize data
	 */
	public function read($key)
	{
		$filename = $this->getCacheFilename($key);

		if (!file_exists($filename)) {
			return null;
		}

		$content = file_get_contents($filename);
		if ($content === false) {
			throw new \RuntimeException(LangMsg::get('EXCEPTION_CACHE_READ_ERROR'));
		}

		$data = unserialize($content);
		if ($data === false && $content !== 'b:0;') {
			throw new \RuntimeException(LangMsg::get('EXCEPTION_CACHE_UNSERIALIZE_ERROR'));
		}

		return $data;
	}

	/**
	 * Clears cache files considering the prefix
	 */
	public function clear()
	{
		$files = glob($this->cacheDir . $this->cachePrefix . '*');
		foreach ($files as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}
	}

	/**
	 * Generates the full cache filename
	 *
	 * @param string $key Cache key
	 * @return string Full filename
	 */
	private function getCacheFilename($key)
	{
		return $this->cacheDir . $this->cachePrefix . $key;
	}
}