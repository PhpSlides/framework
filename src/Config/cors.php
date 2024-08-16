<?php

use PhpSlides\Loader\FileLoader;
use PhpSlides\Foundation\Application;

$cors = (new FileLoader())
	->safeLoad(Application::$configsDir . 'cors.php')
	->getLoad() ?: [];

foreach ($cors as $key => $value) {
	$key = 'Access-Control-' . str_replace('_', '-', ucwords($key, '_'));
	$value = is_array($value) ? implode(', ', $value) : $value;

	$header_value =
		$key . ': ' . (is_bool($value) ? var_export($value, true) : $value);
	header($header_value);
}

/**
 * Handle preflight requests (OPTIONS method)
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
}
