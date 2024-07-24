<?php

use PhpSlides\Foundation\Application;
use PhpSlides\Loader\FileLoader;

$cors = (new FileLoader())
	->load(Application::$configsDir . 'cors.php')
	->getLoad()[0];

foreach ($cors as $key => $value) {
	$key = str_replace('_', '-', ucwords($key, '_'));
	$value = is_array($value) ? implode(', ', $value) : $value;

	$header_value = $key . ': ' . $value;
	header($header_value);
}

/**
 * Handle preflight requests (OPTIONS method)
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
}
