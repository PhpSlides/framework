<?php declare(strict_types=1);

namespace PhpSlides\Logger;

use DateTime;
use PhpSlides\Foundation\Application;

/**
 * Logger trait for logging HTTP request details.
 *
 * This trait provides a lightweight logging mechanism for tracking incoming
 * HTTP requests, including method, URI, status code, timestamp, and remote
 * address. Log entries are written to a log file when logging is enabled.
 */
trait Logger
{
	/**
	 * Logs HTTP request details to a file.
	 *
	 * This method constructs a log entry for each HTTP request, including
	 * the request method, URI, timestamp, protocol, status code, and client
	 * IP address. Log entries are stored in the `requests.log` file located
	 * at the application's base path.
	 *
	 * Logging is only performed if the logging feature is enabled in the
	 * application settings, and hot-reload requests are excluded from logging.
	 *
	 * @return void
	 */
	protected static function log(): void
	{
		// Define the log file path.
		$log_path = Application::$basePath . 'requests.log';

		// Get the current date and time in a standard log format.
		$date = new DateTime('now');
		$date = date_format($date, 'D, d-m-Y H:i:s');

		// Retrieve the HTTP method used for the request.
		$method = $_SERVER['REQUEST_METHOD'];

		// Obtain the request URI, removing any leading/trailing slashes.
		$uri = trim(Application::$request_uri, '/');

		// Define the hot-reload URL for session-specific requests.
		$hot_reload_url = 'hot-reload-' . session_id();

		// Capture the HTTP response status code.
		$http_code = http_response_code();

		// Get the protocol used in the request (e.g., HTTP/1.1).
		$http_protocol = $_SERVER['SERVER_PROTOCOL'];

		// Retrieve the IP address of the client making the request.
		$remote_addr = $_SERVER['REMOTE_ADDR'];

		// Construct the log entry in the common log format.
		$content = "$remote_addr - - [$date] \"$method /$uri $http_protocol\" $http_code\n";

		// If logging is enabled and the request is not for hot-reload, log the entry.
		if (Application::$log === true && $uri !== $hot_reload_url) {
			$log = fopen($log_path, 'a');
			fwrite($log, $content);
			fclose($log);
		}
	}
}
