<?php

use PhpSlides\Exception;
use PhpSlides\Loader\FileLoader;
use PhpSlides\Foundation\Application;

	return (new FileLoader())
		->safeLoad(Application::$configsDir . 'jwt.php')
		->getLoad() ?:
		[];