<?php

namespace PhpSlides\Cache;

use PhpSlides\Foundation\Application;

/**
 * Handles Cache Performance
 */
class Cache
{
	/**
	 * Clear all cache
	 */
	public function clear()
	{
		if (is_dir(Application::$basePath . 'app/cache')) {
			rmdir(Application::$basePath . 'app/cache');
		}
	}

	/**
	 * Clear Hot Reload cache
	 */
	public function clearHotReload()
	{
		if (file_exists(Application::$basePath . 'app/cache/hot-reload.json')) {
			unlink(Application::$basePath . 'app/cache/hot-reload.json');
		}
	}
}
