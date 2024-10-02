<?php declare(strict_types=1);

namespace PhpSlides\Logger;

use DateTime;
use PhpSlides\Foundation\Application;

trait DBLogger
{
	protected static function log(string ...$message): void
	{
		$type = $message[0];
		$message = $message[1];
		$log_path = Application::$basePath . 'db.log';
		$uri = trim(Application::$request_uri, '/');

		// hot reload url
		$hot_reload_url = 'hot-reload-' . session_id();

		// set current date format
		$date = new DateTime('now');
		$date = date_format($date, 'D, d-m-Y H:i:s');

		// all content messages to log
		$content = "[$date] [$type] $message\n";

		if (Application::$db_log === true && $uri !== $hot_reload_url) {
			$log = fopen($log_path, 'a');
			fwrite($log, $content);
			fclose($log);
		}
	}
}
