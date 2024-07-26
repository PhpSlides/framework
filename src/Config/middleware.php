<?php

use PhpSlides\Loader\FileLoader;
use PhpSlides\Foundation\Application;

$middleware = (new FileLoader())
	->load(Application::$configsDir . 'middleware.php')
	->getLoad();

return $middleware;