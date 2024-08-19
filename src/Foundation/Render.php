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

	/**
	 * Render the web route - primary routing
	 */
	public static function WebRoute()
	{
		self::Load();
		$static = new static();
		$reg_route = $GLOBALS['__registered_routes'];

		foreach ($reg_route as $route) {
			self::$middleware = $route['middleware'];
			self::$map_info = $route['map_info'];
			self::$redirect = $route['redirect'];
			self::$method = $route['method'];
			self::$action = $route['action'];
			self::$view = $route['view'];
			self::$file = $route['file'];
			self::$any = $route['any'];
			self::$use = $route['use'];

			if ($route['middleware']) {
				$static->__middleware();
			}

			if ($route['redirect']) {
				self::__redirect();
			}

			if ($route['method']) {
				self::__method();
			}

			if ($route['view']) {
				self::__view();
			}

			if ($route['any']) {
				self::__any();
			}

			if ($route['use']) {
				$static->__use();
			}

			if ($route['file']) {
				$static->__file();
			}

			if ($route['action']) {
				$static->__action();
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
