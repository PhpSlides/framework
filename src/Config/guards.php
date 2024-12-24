<?php

use PhpSlides\Src\Loader\FileLoader;
use PhpSlides\Src\Foundation\Application;

return (new FileLoader())
	->safeLoad(Application::$configsDir . 'guards.php')
	->getLoad() ?:
	[];
