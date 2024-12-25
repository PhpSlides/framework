<?php declare(strict_types=1);

namespace PhpSlides\Core\Logger;

use DateTime;
use PhpSlides\Core\Foundation\Application;

/**
 * DBLogger trait for logging database-related messages.
 *
 * This trait provides a mechanism for logging database events, errors,
 * and information with timestamps and log levels, helping track database
 * interactions within the application.
 */
trait DBLogger
{
	/**
	 * Logs a message related to database operations.
	 *
	 * This method creates a log entry containing a timestamp, log level,
	 * and message describing a database-related event. Logs are written to
	 * a `db.log` file, stored in the application's base path. The log is
	 * only recorded if database logging is enabled and the request URI
	 * does not match the hot-reload URI.
	 *
	 * @param string ...$message Accepts a log type (e.g., "ERROR", "INFO")
	 *                           as the first parameter, followed by the
	 *                           log message to be recorded.
	 * @return void
	 */
	protected static function log (string ...$message): void
	{
		// Extract the log type and message.
		$type = $message[0];
		$message = $message[1];

		// Define the log file path.
		$log_path = Application::$basePath . 'db.log';

		// Get the request URI and trim any leading/trailing slashes.
		$uri = trim(Application::$request_uri, '/');

		// Define the hot-reload URL for session-specific requests.
		$hot_reload_url = 'hot-reload-' . session_id();

		// Set the current date and time in a standard log format.
		$date = new DateTime('now');
		$date = date_format($date, 'D, d-m-Y H:i:s');

		// Construct the log entry with a timestamp, log type, and message.
		$content = "[$date] [$type] $message\n";

		// Log entry if database logging is enabled and the URI is not hot-reload.
		if (Application::$db_log === true && $uri !== $hot_reload_url)
		{
			$log = fopen($log_path, 'a');
			fwrite($log, $content);
			fclose($log);
		}
	}
}
