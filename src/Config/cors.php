<?php

use PhpSlides\Foundations\Application;
use PhpSlides\Loader\FileLoader;

/**
 * Handle preflight requests (OPTIONS method)
 */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(200);
	exit();
}

$cors = (new FileLoader())
	->load(Application::$configsDir . 'cors.php')
	->getLoad()[0];

foreach ($cors as $key => $value) {
	$key = str_replace('_', '-', ucwords($key, '_'));
	$value = implode($value, ', ');

	$header_value = $key . ': ' . $value;
	echo $value;
}
