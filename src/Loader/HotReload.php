<?php

namespace PhpSlides\Loader;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class HotReload
{
	private $watchFiles = ['app', 'src', 'public']; // Directories to watch

	public function reload()
	{
		$modify_time = $this->getLatestModificationTime($this->watchFiles);

		if (!isset($_SESSION['__last_modify_time'])) {
			$_SESSION['__last_modify_time'] = $modify_time;
		}

		if ($modify_time > $_SESSION['__last_modify_time']) {
			echo 'reload';
		}
		$_SESSION['__last_modify_time'] = $modify_time;
	}

	private function getLatestModificationTime($dirs)
	{
		$latest = 0;
		foreach ($dirs as $dir) {
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($dir)
			);
			foreach ($files as $file) {
				if ($file->isFile()) {
					$latest = max($latest, $file->getMTime());
				}
			}
		}
		return $latest;
	}
}
