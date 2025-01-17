<?php declare(strict_types=1);

namespace PhpSlides\Core\Foundation;

use PhpSlides\Core\Controller\Controller;
use PhpSlides\Core\Foundation\Application;
use PhpSlides\Core\Traits\Resources\Resources;

/**
 * Handles the rendering of all registered routes.
 */
final class Render extends Controller
{
	use Resources;

	/**
	 * Loads the request URI for routing.
	 */
	private static function Load()
	{
		self::$request_uri = Application::$request_uri;
	}

	/**
	 * Handles rendering of web routes based on the registered routes.
	 * Loops through all registered web routes and processes actions like redirection,
	 * method handling, guards, view rendering, and others.
	 */
	public static function WebRoute()
	{
		self::Load();
		$reg_route = $GLOBALS['__registered_routes'] ?? [];

		foreach ($reg_route as $route) {
			$caseSensitive = $route['caseSensitive'];
			$handleInvalidParameterType =
				$route['handleInvalidParameterType'] ?? null;

			self::$redirect = $route['redirect'] ?? null;
			self::$method = $route['method'] ?? null;
			self::$guards = $route['guards'] ?? null;
			self::$action = $route['action'] ?? null;
			self::$view = $route['view'] ?? null;
			self::$file = $route['file'] ?? null;
			self::$any = $route['any'] ?? null;
			self::$use = $route['use'] ?? null;
			self::$map = $route['map'] ?? null;
			self::$mapRoute = $route['mapRoute'] ?? null;

			Application::$handleInvalidParameterType = $handleInvalidParameterType;
			Application::$caseInSensitive = !$caseSensitive;

			if (self::$map) {
				self::__map();
			}

			if (self::$mapRoute) {
				self::__mapRoute();
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

	/**
	 * Handles rendering of API routes based on the registered API routes.
	 * Loops through all registered API routes and processes their respective map and route actions.
	 */
	public static function ApiRoute()
	{
		self::Load();
		$static = new static();
		$reg_route = $GLOBALS['__registered_api_routes'] ?? [];

		foreach ($reg_route as $route) {
			$caseSensitive = $route['caseSensitive'] ?? false;
			$handleInvalidParameterType =
				$route['handleInvalidParameterType'] ?? null;

			self::$apiMap = $route['map'] ?? null;
			self::$route = $route['route'] ?? null;

			Application::$handleInvalidParameterType = $handleInvalidParameterType;
			Application::$caseInSensitive = !$caseSensitive;

			if (self::$route) {
				$static->__route();
			}

			if (self::$apiMap) {
				$static->__api_map();
			}
		}
	}

	/**
	 * Placeholder function for handling form routes.
	 * Currently, the implementation for form routes is not defined.
	 */
	public static function FormRoute()
	{
		self::Load();
		$reg_route = $GLOBALS['__registered_form_routes'] ?? null;

		// Future form handling can be implemented here.
	}
}
