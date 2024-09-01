<?php declare(strict_types=1);

namespace PhpSlides\Logger;

use DateTime;
use PhpSlides\Foundation\Application;

trait DBLogger
{
	protected static function log (string ...$message): void
	{
		$type = $message[0];
		$message = $message[1];
		$log_path = Application::$basePath . 'db.log';

		// set current date format
		$date = new DateTime('now');
		$date = date_format($date, 'D, d-m-Y H:i:s');

		// all content messages to log
		$content = "[$date] [$type] $message\n";

		if (Application::$db_log === true)
		{
			$log = fopen($log_path, 'a');
			fwrite($log, $content);
			fclose($log);
		}
	}
}
