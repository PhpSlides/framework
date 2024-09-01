<?php declare(strict_types=1);

namespace PhpSlides\Logger;

use DateTime;
use PhpSlides\Foundation\Application;

trait Logger
{
	protected static function log (): void
	{
		$log_path = Application::$basePath . 'requests.log';

		// set current date format
		$date = new DateTime('now');
		$date = date_format($date, 'D, d-m-Y H:i:s');

		// get request method type
		$method = $_SERVER['REQUEST_METHOD'];

		// get request url
		$uri = trim(Application::$request_uri, '/');

		// get status response code for each request
		$http_code = http_response_code();

		// protocol code for request header
		$http_protocol = $_SERVER['SERVER_PROTOCOL'];

		// get remote address
		$remote_addr = $_SERVER['REMOTE_ADDR'];

		// all content messages to log
		$content = "$remote_addr - - [$date] \"$method /$uri $http_protocol\" $http_code\n";

		if (Application::$log === true)
		{
			$log = fopen($log_path, 'a');
			fwrite($log, $content);
			fclose($log);
		}
	}
}
