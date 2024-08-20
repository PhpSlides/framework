<?php declare(strict_types=1);

namespace PhpSlides\Foundation;

use PhpSlides\Controller\Controller;
use PhpSlides\Foundation\Application;
use PhpSlides\Traits\Resources\Resources;

/**
 * Render all registered routes
 */
final class Render extends Controller
{
	use Resources;

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
			self::$redirect = $route['redirect'];
			self::$method = $route['method'];
			self::$action = $route['action'];
			self::$view = $route['view'];
			self::$file = $route['file'];
			self::$any = $route['any'];
			self::$use = $route['use'];
			self::$map = $route['map'];

			if ($route['map']) {
				self::__map();
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
		}
	}

	public static function ApiRoute()
	{
		self::Load();
		$static = new static();
		$reg_route = $GLOBALS['__registered_api_routes'];

		foreach ($reg_route as $route) {
			self::$apiMap = $route['map'] ?? null;
			self::$route = $route['route'] ?? null;
			self::$apiMiddleware = $route['middleware'] ?? null;

			if ($route['route']) {
				$static->__route();
			}

			if ($route['map']) {
				$static->__api_map();
			}
		}
	}

	public static function FormsRoute()
	{
		self::Load();
		$reg_route = $GLOBALS['__registered_forms_routes'];
	}
}
