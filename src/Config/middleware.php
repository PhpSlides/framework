<?php

use PhpSlides\Loader\FileLoader;
use PhpSlides\Foundation\Application;

return (new FileLoader())
	->safeLoad(Application::$configsDir . 'middlewares.php')
	->getLoad() ?: [];