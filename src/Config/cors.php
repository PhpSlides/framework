<?php

use PhpSlides\Logger\Logger;
use PhpSlides\Loader\FileLoader;
use PhpSlides\Foundation\Application;

$cors =
	(new FileLoader())
		->safeLoad(Application::$configsDir . 'cors.php')
		->getLoad() ?:
	[];

foreach ($cors as $key => $value) {
	$key = 'Access-Control-' . str_replace('_', '-', ucwords($key, '_'));
	$value = is_array($value) ? implode(', ', $value) : $value;

	$header_value =
		$key . ': ' . (is_bool($value) ? var_export($value, true) : $value);
	header($header_value);
}

class Log
{
	use Logger;
	public function __construct()
	{
		self::log();
	}
}

/**
 * Handle preflight requests (OPTIONS method)
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	new Log();
	http_response_code(200);
	exit();
}
