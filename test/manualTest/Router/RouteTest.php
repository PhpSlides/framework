<?php

use PhpSlides\Route;
use PhpSlides\Http\Request;
use PhpSlides\Foundation\Render;

include_once __DIR__ . '/../../autoload.php';
include_once __DIR__ . '/../../../src/Globals/Functions.php';

$dir = '/test/manualTest/Router/RouteTest.php';

Route::get(route: $dir, callback: function ()
{
	return 'Hello World';
});

Route::map(GET, "$dir/user/{id: int|null}")->action(function (Request $req)
{
	echo "<br>";
	return $req->urlParam();
});

Render::WebRoute();