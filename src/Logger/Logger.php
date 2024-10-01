<?php declare(strict_types=1);

namespace PhpSlides\Logger;

use DateTime;
use PhpSlides\Foundation\Application;

trait Logger
{
	protected static function log(): void
	{
		$log_path = 'requests.log';

		// set current date format
		$date = new DateTime('now');
		$date = date_format($date, 'D, d-m-Y H:i:s');

		// get request method type
		$method = $_SERVER['REQUEST_METHOD'];

		// get request url
		$uri = trim(Application::$request_uri, '/');

		// hot reload url
		$hot_reload_url = 'hot-reload-' . session_id();

		// get status response code for each request
		$http_code = http_response_code();

		// protocol code for request header
		$http_protocol = $_SERVER['SERVER_PROTOCOL'];

		// get remote address
		$remote_addr = $_SERVER['REMOTE_ADDR'];

		// all content messages to log
		$content = "$remote_addr - - [$date] \"$method /$uri $http_protocol\" $http_code\n";

		if (Application::$log === true && $uri !== $hot_reload_url) {
			$log = fopen($log_path, 'a');
			fwrite($log, $content);
			fclose($log);
		}
	}
}
