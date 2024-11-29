<?php declare(strict_types=1);

namespace PhpSlides\Loader;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PhpSlides\Foundation\Application;

/**
 * The HotReload class monitors specified directories for file changes,
 * triggering a reload when updates are detected. This functionality
 * supports development by allowing live updates to the application.
 */
class HotReload
{
	/**
	 * @var array Directories to watch for changes.
	 */
	private array $watchFiles = [ 'app', 'src', 'public' ];

	/**
	 * Scans watched directories for file modifications and updates a
	 * cache file with the latest modification time if any changes are found.
	 * Triggers a reload if a newer modification is detected.
	 *
	 * This method uses recursive directory iteration to monitor files in
	 * the specified directories, excluding any files in the cache directory.
	 */
	public function reload ()
	{
		$latest = 0;
		clearstatcache(); // Clear file status cache to ensure fresh data.

		foreach ($this->watchFiles as $dir)
		{
			// Set up a recursive iterator to scan files in the directory.
			$files = new RecursiveIteratorIterator(
			 new RecursiveDirectoryIterator(Application::$basePath . $dir),
			 RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ($files as $file)
			{
				// Skip files within the 'app/cache' directory.
				if (strpos(ltrim($file->getPathname(), './'), 'app/cache') !== false)
				{
					continue;
				}

				if ($file->isFile())
				{
					// Track the latest modification time of all files.
					$latest = max($latest, $file->getMTime());
					$cacheFile =
					 Application::$basePath . 'app/cache/hot-reload.json';

					// If cache file doesn't exist, create it with initial data.
					if (!file_exists($cacheFile))
					{
						!is_dir(dirname($cacheFile)) && mkdir(dirname($cacheFile));
						$initialCache = [
						 '__last_modify_time' => $latest,
						 'file' => ''
						];
						file_put_contents($cacheFile, json_encode($initialCache));
					}

					// Load the existing cache data.
					$cache = json_decode(file_get_contents($cacheFile), true);

					// If a newer modification is detected, update cache and trigger reload.
					if ($latest > $cache['__last_modify_time'] + 5)
					{
						$cache['__last_modify_time'] = $latest;
						$cache['file'] = $file->getPathname();
						file_put_contents($cacheFile, json_encode($cache));

						// Output reload signal for listeners.
						return 'reload';
					}
				}
			}
		}
	}
}
