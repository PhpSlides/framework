<?php

use PhpSlides\Loader\FileLoader;
use PhpSlides\Foundation\Application;

$middleware = (new FileLoader())
	->safeLoad(Application::$configsDir . 'middlewares.php')
	->getLoad() ?: [];

return $middleware;
