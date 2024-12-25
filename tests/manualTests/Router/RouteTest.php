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
	"$dir/user/{id: int<6, 10>|bool|array<array<int<5,5>|bool>, string>}",
)
	->action(function (Request $req) {
		echo '<br>';
		return $req->urlParam('id');
	})
	->route('/posts/{id: int}', function (Request $req, Closure $accept) {
		$accept('POST');
	});

Render::WebRoute();
