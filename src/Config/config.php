<?php

use PhpSlides\Loader\FileLoader;

(new FileLoader())
	->load(__DIR__ . '/cors.php')
	->load(__DIR__ . '/middleware.php');
