<?php

use PhpSlides\Route;
use PhpSlides\Foundation\Render;

include_once __DIR__ . '/../../autoload.php';
include_once __DIR__ . '/../../../src/Globals/Functions.php';

Route::get('/test/manualTest/Router/RouteTest.php', callback: function ()
{
	return 'Hello World';
});


Render::WebRoute();
