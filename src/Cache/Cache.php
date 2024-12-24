<?php

namespace PhpSlides\Src\Cache;

use PhpSlides\Src\Foundation\Application;

/**
 * Class Cache
 *
 * This class handles cache management operations within the PhpSlides framework.
 * It provides functionality to clear all cache or clear specific cache items,
 * such as the hot reload cache.
 */
class Cache
{
	/**
	 * Clears all cache stored in the 'app/cache' directory.
	 *
	 * This method checks if the cache directory exists and removes it,
	 * effectively clearing all cached files. It is commonly used to
	 * reset the cache during development or when cache corruption occurs.
	 */
	public function clear()
	{
		// Check if the cache directory exists
		if (is_dir(Application::$basePath . 'app/cache')) {
			// Remove the cache directory
			rmdir(Application::$basePath . 'app/cache');
		}
	}

	/**
	 * Clears the Hot Reload cache specifically.
	 *
	 * This method checks if the 'hot-reload.json' cache file exists and removes it.
	 * It is typically used during development when hot reload functionality
	 * needs to be reset or reloaded.
	 */
	public function clearHotReload()
	{
		// Check if the hot-reload cache file exists
		if (file_exists(Application::$basePath . 'app/cache/hot-reload.json')) {
			// Delete the hot-reload cache file
			unlink(Application::$basePath . 'app/cache/hot-reload.json');
		}
	}
}
