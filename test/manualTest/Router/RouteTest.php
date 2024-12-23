<?php

use PhpSlides\Router\Route;
use PhpSlides\Src\Http\Request;
use PhpSlides\Src\Foundation\Render;

include_once __DIR__ . '/../../autoload.php';
include_once __DIR__ . '/../../../src/Globals/Functions.php';

$dir = '/test/manualTest/Router/RouteTest.php';

Route::get(
 route: $dir,
 callback: function ()
 {
	 return 'Hello World';
 },
);

Route::map(GET, "$dir/user/{id: int|bool|array<string|int, string>|alnum}")
 ->action(function (Request $req)
 {
	 echo '<br>';
	 return $req->urlParam();
 })
 ->route('/posts/{id: int}', function (Request $req, Closure $accept)
 {
	 $accept('POST');
 });

Render::WebRoute();
