<?php declare(strict_types=1);

namespace PhpSlides\Foundation;

use PhpSlides\Controller\Controller;
use PhpSlides\Foundation\Application;
use PhpSlides\Traits\Resources\RouteResources;

/**
 * Render all registered routes
 */
final class Render extends Controller
{
	use RouteResources;

	private static function Load()
	{
		self::$request_uri = Application::$request_uri;
	}

	public static function WebRoute()
	{
		self::Load();
		$reg_route = $GLOBALS['__registered_routes'];

		foreach ($reg_route as $route) {
			if ($route['middleware']) {
				self::$middleware = $route['middleware'];
				self::__middleware();
			}

			if ($route['redirect']) {
				self::$redirect = $route['redirect'];
				self::__redirect();
			}

			if ($route['action']) {
				self::$action = $route['action'];
				self::__action();
			}

			if ($route['any']) {
				self::$any = $route['any'];
				self::__any();
			}

			if ($route['use']) {
				self::$use = $route['use'];
				self::__use();
			}

			if ($route['file']) {
				self::$file = $route['file'];
				self::__file();
			}

			if ($route['method']) {
				self::$method = $route['method'];
				self::__method();
			}

			if ($route['view']) {
				self::$view = $route['view'];
				self::__view();
			}
		}
	}

	public static function ApiRoute()
	{
		self::Load();
		$reg_route = $GLOBALS['__registered_routes'];
	}

	public static function FormsRoute()
	{
		self::Load();
		$reg_route = $GLOBALS['__registered_routes'];
	}
}
