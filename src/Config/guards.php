<?php

use PhpSlides\Core\Loader\FileLoader;
use PhpSlides\Core\Foundation\Application;

return (new FileLoader())
	->safeLoad(Application::$configsDir . 'guards.php')
	->getLoad() ?:
	[];
