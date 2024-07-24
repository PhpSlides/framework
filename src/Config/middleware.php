<?php

use PhpSlides\Foundation\Application;
use PhpSlides\Loader\FileLoader;

$middleware = (new FileLoader())
	->load(Application::$configsDir . 'middleware.php')
	->getLoad()[0];

return $middleware;