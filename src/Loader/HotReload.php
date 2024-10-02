<?php

namespace PhpSlides\Loader;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PhpSlides\Foundation\Application;

class HotReload
{
	private $watchFiles = ['app', 'src', 'public']; // Directories to watch

	public function reload()
	{
		$latest = 0;
		clearstatcache();

		foreach ($this->watchFiles as $dir) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(Application::$basePath . $dir),
				RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ($files as $file) {
				if (strpos($file->getPathname(), 'app/cache') !== false) {
					continue;
				}

				if ($file->isFile()) {
					$latest = max($latest, $file->getMTime());
					$cacheFile =
						Application::$basePath . 'app/cache/hot-reload.json';

					if (!file_exists($cacheFile)) {
						!is_dir(dirname($cacheFile)) && mkdir(dirname($cacheFile));
						$cc = ['__last_modify_time' => $latest, 'file' => ''];
						file_put_contents($cacheFile, json_encode($cc));
					}

					$cache = json_decode(file_get_contents($cacheFile), true);

					if ($latest > $cache['__last_modify_time']) {
						$cache['__last_modify_time'] = $latest;
						$cache['file'] = $file->getPathname();

						file_put_contents($cacheFile, json_encode($cache));

						echo 'reload';
					}
				}
			}
		}
	}
}
