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
		$reg_route = $GLOBALS['__registered_routes'] ?? null;

		foreach ($reg_route as $route) {
			self::$middleware = $route['middleware'] ?? null;
			self::$redirect = $route['redirect'] ?? null;
			self::$method = $route['method'] ?? null;
			self::$action = $route['action'] ?? null;
			self::$view = $route['view'] ?? null;
			self::$file = $route['file'] ?? null;
			self::$any = $route['any'] ?? null;
			self::$use = $route['use'] ?? null;
			self::$map = $route['map'] ?? null;

			if (self::$map) {
				self::__map();
			}

			if (self::$redirect) {
				self::__redirect();
			}

			if (self::$method) {
				self::__method();
			}

			if (self::$view) {
				self::__view();
			}

			if (self::$any) {
				self::__any();
			}
		}
	}

	public static function ApiRoute()
	{
		self::Load();
		$static = new static();
		$reg_route = $GLOBALS['__registered_api_routes'] ?? null;

		foreach ($reg_route as $route) {
			self::$apiMap = $route['map'] ?? null;
			self::$route = $route['route'] ?? null;
			self::$apiMiddleware = $route['middleware'] ?? null;

			if (self::$route) {
				$static->__route();
			}

			if (self::$apiMap) {
				$static->__api_map();
			}
		}
	}

	public static function FormsRoute()
	{
		self::Load();
		$reg_route = $GLOBALS['__registered_forms_routes'] ?? null;
	}
}
