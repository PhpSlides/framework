<?php

use PhpSlides\Router\Route;
use PhpSlides\Core\Http\Request;
use PhpSlides\Core\Foundation\Render;

include_once __DIR__ . '/../autoload.php';
include_once __DIR__ . '/../../../src/Globals/Functions.php';

$dir = '/tests/manualTests/Router/RouteTest.php';

Route::get(
	route: $dir,
	callback: function () {
		return 'Hello World';
	},
);

Route::map(
	GET,
	"$dir/User/{id: int<6, 10>|string<3,3>|bool|array<array<int<5,5>|bool>, string>}/{status: enum<success|failed|pending>}",
)
	->action(function (Request $req) {
		echo '<br>';
		return $req->url();
	})
	->route('/posts/{post_id: int}', function (Request $req, Closure $accept) {
		$accept(GET, fn($method) => "`$method` method is not allowed");

		return 'ddd';
	})
	/*->handleInvalidParameterType(function ($type) {
		return $type;
	})*/
	->caseSensitive();

Render::WebRoute();
