<?php

namespace PhpSlides\Cache;

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
		if (is_dir('app/cache')) {
			rmdir('app/cache');
		}
	}

	/**
	 * Clear Hot Reload cache
	 */
	public function clearHotReload()
	{
		if (file_exists('app/cache/hot-reload.json')) {
			unlink('app/cache/hot-reload.json');
		}
	}
}
