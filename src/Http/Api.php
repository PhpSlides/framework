<?php declare(strict_types=1);

namespace PhpSlides\Http;

use PhpSlides\MapRoute;
use PhpSlides\Exception;
use PhpSlides\Controller\Controller;
use PhpSlides\Http\Interface\ApiInterface;

/**
 * The Api class provides a fluent interface to define API routes,
 * apply middleware, and manage route mapping.
 *
 * @category API
 * @license MIT
 * @since 1.2.2
 * @version 1.2.2
 * @author Dave Conco <concodave@gmail.com>
 */
class Api extends Controller implements ApiInterface
{
	/**
	 * The base URL for all API routes. Default is '/api/'
	 * @var string
	 */
	public static string $BASE_URL = '/api/';

	/**
	 * The API version. Default is 'v1'
	 * @var string
	 */
	private ?array $define = null;

	private static string $version = 'v1';

	private static array $allRoutes;

	private static array $regRoute = [];

	private static ?array $middleware = null;

	private static ?array $route = null;

	private static ?array $map = null;

	/**
	 * Handles static method calls to set the API version dynamically.
	 *
	 * @param string $method The method name which starts with 'v' followed by the version number. Use `_` in place of `.`
	 * @param mixed $args The arguments for the method (not used).
	 *
	 * @throws \Exception
	 * @return self
	 */
	public static function __callStatic($method, $args): self
	{
		if (str_starts_with($method, 'v')) {
			$method_v = str_replace('_', '.', $method);
			self::$version = $method_v;

			return new self();
		} else {
			throw new Exception('Version method starts with `v`');
		}
	}

	/**
	 * Assigns a name to the last registered route for easier reference.
	 *
	 * @param string $name The name to assign to the route.
	 * @return self
	 */
	public function name(string $name): self
	{
		if (is_array(end(self::$regRoute))) {
			for ($i = 0; $i < count(end(self::$regRoute)); $i++) {
				add_route_name($name . '::' . $i, end(self::$regRoute)[$i]);
				self::$allRoutes[$name . '::' . $i] = end(self::$regRoute)[$i];
			}
		}
		add_route_name($name, end(self::$regRoute));
		self::$allRoutes[$name] = end(self::$regRoute);

		return $this;
	}

	/**
	 * Defines a new route with a URL and a controller.
	 *
	 * @param string $url The Base URL of the route.
	 * @param string|array|null $controller The controller handling the route.
	 * @param ?string $controller The request method the route is going to accept,
	 * if null is given, then it's consider dynamic, accepts all methods.
	 * @return self
	 */
	public function route(
		string $url,
		string|array|null $controller = null,
		?string $req_method = null
	): self {
		$define = $this->define;

		// checks if $define is set, then assign $define methods to $url & $controller parameters
		$url =
			$define !== null
				? rtrim($define['url'], '/') . '/' . trim($url, '/')
				: trim($url, '/');
		$url = trim($url, '/');

		$uri = strtolower(self::$BASE_URL . self::$version . '/' . $url);
		self::$regRoute[] = $uri;

		self::$route = [
			'url' => $uri,
			'r_method' => $req_method,
			'controller' =>
				$define['controller'] ??
				(is_array($controller) ? $controller[0] : $controller)
		];

		if ($define !== null && $controller !== null) {
			self::$route['c_method'] = trim($controller, '@');
		}
		if (is_array($controller)) {
			self::$route['c_method'] = $controller[1];
		}

		$newInstance = new self();
		$newInstance->define = $define;
		return $newInstance;
	}

	/**
	 * Applies middleware to the current route.
	 *
	 * @param array $middleware An array of middleware classes.
	 * @return self
	 */
	public function middleware(array $middleware): self
	{
		self::$middleware = $middleware;
		return $this;
	}

	/**
	 * Defines a base URL and controller for subsequent route mappings.
	 *
	 * @param string $url The base URL for the routes.
	 * @param string $controller The controller handling the routes.
	 * @return self
	 */
	public function define(string $url, string $controller): self
	{
		$this->define = [
			'url' => $url,
			'controller' => $controller
		];

		return $this;
	}

	/**
	 * Maps multiple HTTP methods to a URL with their corresponding controller methods.
	 *
	 * @param array An associative array where the key is the route and the value is an array with the HTTP method and controller method.
	 * @return self
	 */
	public function map(array $rest_url): self
	{
		$define = $this->define;

		if ($define !== null) {
			/**
			 * Get the map value, keys as the route url
			 */
			$routes = array_keys($rest_url);
			$base = strtolower(
				self::$BASE_URL .
					self::$version .
					'/' .
					trim($define['url'], '/') .
					'/'
			);

			/**
			 * Map route url array to the full base url
			 */
			$full_url = array_map(function ($route) use ($base) {
				return $base . ltrim($route, '/');
			}, $routes);

			self::$regRoute[] = $full_url;

			$rest_url['base_url'] = $base;
			$rest_url['controller'] = $define['controller'];
			self::$map = $rest_url;
		}

		$newInstance = new self();
		$newInstance->define = $define;
		return $newInstance;
	}

	/**
	 * Automatically handles middleware, route, and map finalization when the object is destroyed.
	 */
	public function __destruct()
	{
		$route_index = end(self::$regRoute);
		$route_index = is_array($route_index) ? $route_index[0] : $route_index;

		if (self::$middleware !== null) {
			$GLOBALS['__registered_api_routes'][$route_index]['middleware'] =
				self::$middleware;
		}

		if (self::$route !== null) {
			$GLOBALS['__registered_api_routes'][$route_index]['route'] =
				self::$route;
		}

		if (self::$map !== null) {
			$GLOBALS['__registered_api_routes'][$route_index]['map'] = self::$map;
		}
	}

	/**
	 * Define an API route for version 1.
	 * Also in use for defining urls
	 *
	 * @return self
	 */
	public static function v1(): self
	{
		return self::__callStatic('v1', 0);
	}

	public static function v1_0(): self
	{
		return self::__callStatic('v1_0', 0);
	}
	public static function v1_1(): self
	{
		return self::__callStatic('v1_1', 0);
	}
	public static function v1_1_1(): self
	{
		return self::__callStatic('v1_1_1', 0);
	}
	public static function v1_1_2(): self
	{
		return self::__callStatic('v1_1_2', 0);
	}
	public static function v1_1_3(): self
	{
		return self::__callStatic('v1_1_3', 0);
	}
	public static function v1_1_4(): self
	{
		return self::__callStatic('v1_1_4', 0);
	}
	public static function v1_1_5(): self
	{
		return self::__callStatic('v1_1_5', 0);
	}
	public static function v1_1_6(): self
	{
		return self::__callStatic('v1_1_6', 0);
	}
	public static function v1_1_7(): self
	{
		return self::__callStatic('v1_1_7', 0);
	}
	public static function v1_1_8(): self
	{
		return self::__callStatic('v1_1_8', 0);
	}
	public static function v1_1_9(): self
	{
		return self::__callStatic('v1_1_9', 0);
	}
	public static function v1_2(): self
	{
		return self::__callStatic('v1_2', 0);
	}
	public static function v1_2_1(): self
	{
		return self::__callStatic('v1_2_1', 0);
	}
	public static function v1_2_2(): self
	{
		return self::__callStatic('v1_2_2', 0);
	}
	public static function v1_2_3(): self
	{
		return self::__callStatic('v1_2_3', 0);
	}
	public static function v1_2_4(): self
	{
		return self::__callStatic('v1_2_4', 0);
	}
	public static function v1_2_5(): self
	{
		return self::__callStatic('v1_2_5', 0);
	}
	public static function v1_2_6(): self
	{
		return self::__callStatic('v1_2_6', 0);
	}
	public static function v1_2_7(): self
	{
		return self::__callStatic('v1_2_7', 0);
	}
	public static function v1_2_8(): self
	{
		return self::__callStatic('v1_2_8', 0);
	}
	public static function v1_2_9(): self
	{
		return self::__callStatic('v1_2_9', 0);
	}
	public static function v1_3(): self
	{
		return self::__callStatic('v1_3', 0);
	}
	public static function v1_3_1(): self
	{
		return self::__callStatic('v1_3_1', 0);
	}
	public static function v1_3_2(): self
	{
		return self::__callStatic('v1_3_2', 0);
	}
	public static function v1_3_3(): self
	{
		return self::__callStatic('v1_3_3', 0);
	}
	public static function v1_3_4(): self
	{
		return self::__callStatic('v1_3_4', 0);
	}
	public static function v1_3_5(): self
	{
		return self::__callStatic('v1_3_5', 0);
	}
	public static function v1_3_6(): self
	{
		return self::__callStatic('v1_3_6', 0);
	}
	public static function v1_3_7(): self
	{
		return self::__callStatic('v1_3_7', 0);
	}
	public static function v1_3_8(): self
	{
		return self::__callStatic('v1_3_8', 0);
	}
	public static function v1_3_9(): self
	{
		return self::__callStatic('v1_3_9', 0);
	}
	public static function v1_4(): self
	{
		return self::__callStatic('v1_4', 0);
	}
	public static function v1_4_1(): self
	{
		return self::__callStatic('v1_4_1', 0);
	}
	public static function v1_4_2(): self
	{
		return self::__callStatic('v1_4_2', 0);
	}
	public static function v1_4_3(): self
	{
		return self::__callStatic('v1_4_3', 0);
	}
	public static function v1_4_4(): self
	{
		return self::__callStatic('v1_4_4', 0);
	}
	public static function v1_4_5(): self
	{
		return self::__callStatic('v1_4_5', 0);
	}
	public static function v1_4_6(): self
	{
		return self::__callStatic('v1_4_6', 0);
	}
	public static function v1_4_7(): self
	{
		return self::__callStatic('v1_4_7', 0);
	}
	public static function v1_4_8(): self
	{
		return self::__callStatic('v1_4_8', 0);
	}
	public static function v1_4_9(): self
	{
		return self::__callStatic('v1_4_9', 0);
	}
	public static function v1_5(): self
	{
		return self::__callStatic('v1_5', 0);
	}
	public static function v1_5_1(): self
	{
		return self::__callStatic('v1_5_1', 0);
	}
	public static function v1_5_2(): self
	{
		return self::__callStatic('v1_5_2', 0);
	}
	public static function v1_5_3(): self
	{
		return self::__callStatic('v1_5_3', 0);
	}
	public static function v1_5_4(): self
	{
		return self::__callStatic('v1_5_4', 0);
	}
	public static function v1_5_5(): self
	{
		return self::__callStatic('v1_5_5', 0);
	}
	public static function v1_5_6(): self
	{
		return self::__callStatic('v1_5_6', 0);
	}
	public static function v1_5_7(): self
	{
		return self::__callStatic('v1_5_7', 0);
	}
	public static function v1_5_8(): self
	{
		return self::__callStatic('v1_5_8', 0);
	}
	public static function v1_5_9(): self
	{
		return self::__callStatic('v1_5_9', 0);
	}
	public static function v1_6(): self
	{
		return self::__callStatic('v1_6', 0);
	}
	public static function v1_6_1(): self
	{
		return self::__callStatic('v1_6_1', 0);
	}
	public static function v1_6_2(): self
	{
		return self::__callStatic('v1_6_2', 0);
	}
	public static function v1_6_3(): self
	{
		return self::__callStatic('v1_6_3', 0);
	}
	public static function v1_6_4(): self
	{
		return self::__callStatic('v1_6_4', 0);
	}
	public static function v1_6_5(): self
	{
		return self::__callStatic('v1_6_5', 0);
	}
	public static function v1_6_6(): self
	{
		return self::__callStatic('v1_6_6', 0);
	}
	public static function v1_6_7(): self
	{
		return self::__callStatic('v1_6_7', 0);
	}
	public static function v1_6_8(): self
	{
		return self::__callStatic('v1_6_8', 0);
	}
	public static function v1_6_9(): self
	{
		return self::__callStatic('v1_6_9', 0);
	}
	public static function v1_7(): self
	{
		return self::__callStatic('v1_7', 0);
	}
	public static function v1_7_1(): self
	{
		return self::__callStatic('v1_7_1', 0);
	}
	public static function v1_7_2(): self
	{
		return self::__callStatic('v1_7_2', 0);
	}
	public static function v1_7_3(): self
	{
		return self::__callStatic('v1_7_3', 0);
	}
	public static function v1_7_4(): self
	{
		return self::__callStatic('v1_7_4', 0);
	}
	public static function v1_7_5(): self
	{
		return self::__callStatic('v1_7_5', 0);
	}
	public static function v1_7_6(): self
	{
		return self::__callStatic('v1_7_6', 0);
	}
	public static function v1_7_7(): self
	{
		return self::__callStatic('v1_7_7', 0);
	}
	public static function v1_7_8(): self
	{
		return self::__callStatic('v1_7_8', 0);
	}
	public static function v1_7_9(): self
	{
		return self::__callStatic('v1_7_9', 0);
	}
	public static function v1_8(): self
	{
		return self::__callStatic('v1_8', 0);
	}
	public static function v1_8_1(): self
	{
		return self::__callStatic('v1_8_1', 0);
	}
	public static function v1_8_2(): self
	{
		return self::__callStatic('v1_8_2', 0);
	}
	public static function v1_8_3(): self
	{
		return self::__callStatic('v1_8_3', 0);
	}
	public static function v1_8_4(): self
	{
		return self::__callStatic('v1_8_4', 0);
	}
	public static function v1_8_5(): self
	{
		return self::__callStatic('v1_8_5', 0);
	}
	public static function v1_8_6(): self
	{
		return self::__callStatic('v1_8_6', 0);
	}
	public static function v1_8_7(): self
	{
		return self::__callStatic('v1_8_7', 0);
	}
	public static function v1_8_8(): self
	{
		return self::__callStatic('v1_8_8', 0);
	}
	public static function v1_8_9(): self
	{
		return self::__callStatic('v1_8_9', 0);
	}
	public static function v1_9(): self
	{
		return self::__callStatic('v1_9', 0);
	}
	public static function v1_9_1(): self
	{
		return self::__callStatic('v1_9_1', 0);
	}
	public static function v1_9_2(): self
	{
		return self::__callStatic('v1_9_2', 0);
	}
	public static function v1_9_3(): self
	{
		return self::__callStatic('v1_9_3', 0);
	}
	public static function v1_9_4(): self
	{
		return self::__callStatic('v1_9_4', 0);
	}
	public static function v1_9_5(): self
	{
		return self::__callStatic('v1_9_5', 0);
	}
	public static function v1_9_6(): self
	{
		return self::__callStatic('v1_9_6', 0);
	}
	public static function v1_9_7(): self
	{
		return self::__callStatic('v1_9_7', 0);
	}
	public static function v1_9_8(): self
	{
		return self::__callStatic('v1_9_8', 0);
	}
	public static function v1_9_9(): self
	{
		return self::__callStatic('v1_9_9', 0);
	}

	public static function v2(): self
	{
		return self::__callStatic('v2', 0);
	}
	public static function v2_0(): self
	{
		return self::__callStatic('v2_0', 0);
	}
	public static function v2_1(): self
	{
		return self::__callStatic('v2_1', 0);
	}
	public static function v2_1_1(): self
	{
		return self::__callStatic('v2_1_1', 0);
	}
	public static function v2_1_2(): self
	{
		return self::__callStatic('v2_1_2', 0);
	}
	public static function v2_1_3(): self
	{
		return self::__callStatic('v2_1_3', 0);
	}
	public static function v2_1_4(): self
	{
		return self::__callStatic('v2_1_4', 0);
	}
	public static function v2_1_5(): self
	{
		return self::__callStatic('v2_1_5', 0);
	}
	public static function v2_1_6(): self
	{
		return self::__callStatic('v2_1_6', 0);
	}
	public static function v2_1_7(): self
	{
		return self::__callStatic('v2_1_7', 0);
	}
	public static function v2_1_8(): self
	{
		return self::__callStatic('v2_1_8', 0);
	}
	public static function v2_1_9(): self
	{
		return self::__callStatic('v2_1_9', 0);
	}
	public static function v2_2(): self
	{
		return self::__callStatic('v2_2', 0);
	}
	public static function v2_2_1(): self
	{
		return self::__callStatic('v2_2_1', 0);
	}
	public static function v2_2_2(): self
	{
		return self::__callStatic('v2_2_2', 0);
	}
	public static function v2_2_3(): self
	{
		return self::__callStatic('v2_2_3', 0);
	}
	public static function v2_2_4(): self
	{
		return self::__callStatic('v2_2_4', 0);
	}
	public static function v2_2_5(): self
	{
		return self::__callStatic('v2_2_5', 0);
	}
	public static function v2_2_6(): self
	{
		return self::__callStatic('v2_2_6', 0);
	}
	public static function v2_2_7(): self
	{
		return self::__callStatic('v2_2_7', 0);
	}
	public static function v2_2_8(): self
	{
		return self::__callStatic('v2_2_8', 0);
	}
	public static function v2_2_9(): self
	{
		return self::__callStatic('v2_2_9', 0);
	}
	public static function v2_3(): self
	{
		return self::__callStatic('v2_3', 0);
	}
	public static function v2_3_1(): self
	{
		return self::__callStatic('v2_3_1', 0);
	}
	public static function v2_3_2(): self
	{
		return self::__callStatic('v2_3_2', 0);
	}
	public static function v2_3_3(): self
	{
		return self::__callStatic('v2_3_3', 0);
	}
	public static function v2_3_4(): self
	{
		return self::__callStatic('v2_3_4', 0);
	}
	public static function v2_3_5(): self
	{
		return self::__callStatic('v2_3_5', 0);
	}
	public static function v2_3_6(): self
	{
		return self::__callStatic('v2_3_6', 0);
	}
	public static function v2_3_7(): self
	{
		return self::__callStatic('v2_3_7', 0);
	}
	public static function v2_3_8(): self
	{
		return self::__callStatic('v2_3_8', 0);
	}
	public static function v2_3_9(): self
	{
		return self::__callStatic('v2_3_9', 0);
	}
	public static function v2_4(): self
	{
		return self::__callStatic('v2_4', 0);
	}
	public static function v2_4_1(): self
	{
		return self::__callStatic('v2_4_1', 0);
	}
	public static function v2_4_2(): self
	{
		return self::__callStatic('v2_4_2', 0);
	}
	public static function v2_4_3(): self
	{
		return self::__callStatic('v2_4_3', 0);
	}
	public static function v2_4_4(): self
	{
		return self::__callStatic('v2_4_4', 0);
	}
	public static function v2_4_5(): self
	{
		return self::__callStatic('v2_4_5', 0);
	}
	public static function v2_4_6(): self
	{
		return self::__callStatic('v2_4_6', 0);
	}
	public static function v2_4_7(): self
	{
		return self::__callStatic('v2_4_7', 0);
	}
	public static function v2_4_8(): self
	{
		return self::__callStatic('v2_4_8', 0);
	}
	public static function v2_4_9(): self
	{
		return self::__callStatic('v2_4_9', 0);
	}
	public static function v2_5(): self
	{
		return self::__callStatic('v2_5', 0);
	}
	public static function v2_5_1(): self
	{
		return self::__callStatic('v2_5_1', 0);
	}
	public static function v2_5_2(): self
	{
		return self::__callStatic('v2_5_2', 0);
	}
	public static function v2_5_3(): self
	{
		return self::__callStatic('v2_5_3', 0);
	}
	public static function v2_5_4(): self
	{
		return self::__callStatic('v2_5_4', 0);
	}
	public static function v2_5_5(): self
	{
		return self::__callStatic('v2_5_5', 0);
	}
	public static function v2_5_6(): self
	{
		return self::__callStatic('v2_5_6', 0);
	}
	public static function v2_5_7(): self
	{
		return self::__callStatic('v2_5_7', 0);
	}
	public static function v2_5_8(): self
	{
		return self::__callStatic('v2_5_8', 0);
	}
	public static function v2_5_9(): self
	{
		return self::__callStatic('v2_5_9', 0);
	}
	public static function v2_6(): self
	{
		return self::__callStatic('v2_6', 0);
	}
	public static function v2_6_1(): self
	{
		return self::__callStatic('v2_6_1', 0);
	}
	public static function v2_6_2(): self
	{
		return self::__callStatic('v2_6_2', 0);
	}
	public static function v2_6_3(): self
	{
		return self::__callStatic('v2_6_3', 0);
	}
	public static function v2_6_4(): self
	{
		return self::__callStatic('v2_6_4', 0);
	}
	public static function v2_6_5(): self
	{
		return self::__callStatic('v2_6_5', 0);
	}
	public static function v2_6_6(): self
	{
		return self::__callStatic('v2_6_6', 0);
	}
	public static function v2_6_7(): self
	{
		return self::__callStatic('v2_6_7', 0);
	}
	public static function v2_6_8(): self
	{
		return self::__callStatic('v2_6_8', 0);
	}
	public static function v2_6_9(): self
	{
		return self::__callStatic('v2_6_9', 0);
	}
	public static function v2_7(): self
	{
		return self::__callStatic('v2_7', 0);
	}
	public static function v2_7_1(): self
	{
		return self::__callStatic('v2_7_1', 0);
	}
	public static function v2_7_2(): self
	{
		return self::__callStatic('v2_7_2', 0);
	}
	public static function v2_7_3(): self
	{
		return self::__callStatic('v2_7_3', 0);
	}
	public static function v2_7_4(): self
	{
		return self::__callStatic('v2_7_4', 0);
	}
	public static function v2_7_5(): self
	{
		return self::__callStatic('v2_7_5', 0);
	}
	public static function v2_7_6(): self
	{
		return self::__callStatic('v2_7_6', 0);
	}
	public static function v2_7_7(): self
	{
		return self::__callStatic('v2_7_7', 0);
	}
	public static function v2_7_8(): self
	{
		return self::__callStatic('v2_7_8', 0);
	}
	public static function v2_7_9(): self
	{
		return self::__callStatic('v2_7_9', 0);
	}
	public static function v2_8(): self
	{
		return self::__callStatic('v2_8', 0);
	}
	public static function v2_8_1(): self
	{
		return self::__callStatic('v2_8_1', 0);
	}
	public static function v2_8_2(): self
	{
		return self::__callStatic('v2_8_2', 0);
	}
	public static function v2_8_3(): self
	{
		return self::__callStatic('v2_8_3', 0);
	}
	public static function v2_8_4(): self
	{
		return self::__callStatic('v2_8_4', 0);
	}
	public static function v2_8_5(): self
	{
		return self::__callStatic('v2_8_5', 0);
	}
	public static function v2_8_6(): self
	{
		return self::__callStatic('v2_8_6', 0);
	}
	public static function v2_8_7(): self
	{
		return self::__callStatic('v2_8_7', 0);
	}
	public static function v2_8_8(): self
	{
		return self::__callStatic('v2_8_8', 0);
	}
	public static function v2_8_9(): self
	{
		return self::__callStatic('v2_8_9', 0);
	}
	public static function v2_9(): self
	{
		return self::__callStatic('v2_9', 0);
	}
	public static function v2_9_1(): self
	{
		return self::__callStatic('v2_9_1', 0);
	}
	public static function v2_9_2(): self
	{
		return self::__callStatic('v2_9_2', 0);
	}
	public static function v2_9_3(): self
	{
		return self::__callStatic('v2_9_3', 0);
	}
	public static function v2_9_4(): self
	{
		return self::__callStatic('v2_9_4', 0);
	}
	public static function v2_9_5(): self
	{
		return self::__callStatic('v2_9_5', 0);
	}
	public static function v2_9_6(): self
	{
		return self::__callStatic('v2_9_6', 0);
	}
	public static function v2_9_7(): self
	{
		return self::__callStatic('v2_9_7', 0);
	}
	public static function v2_9_8(): self
	{
		return self::__callStatic('v2_9_8', 0);
	}
	public static function v2_9_9(): self
	{
		return self::__callStatic('v2_9_9', 0);
	}

	public static function v3(): self
	{
		return self::__callStatic('v3', 0);
	}
	public static function v3_0(): self
	{
		return self::__callStatic('v3_0', 0);
	}
	public static function v3_1(): self
	{
		return self::__callStatic('v3_1', 0);
	}
	public static function v3_1_1(): self
	{
		return self::__callStatic('v3_1_1', 0);
	}
	public static function v3_1_2(): self
	{
		return self::__callStatic('v3_1_2', 0);
	}
	public static function v3_1_3(): self
	{
		return self::__callStatic('v3_1_3', 0);
	}
	public static function v3_1_4(): self
	{
		return self::__callStatic('v3_1_4', 0);
	}
	public static function v3_1_5(): self
	{
		return self::__callStatic('v3_1_5', 0);
	}
	public static function v3_1_6(): self
	{
		return self::__callStatic('v3_1_6', 0);
	}
	public static function v3_1_7(): self
	{
		return self::__callStatic('v3_1_7', 0);
	}
	public static function v3_1_8(): self
	{
		return self::__callStatic('v3_1_8', 0);
	}
	public static function v3_1_9(): self
	{
		return self::__callStatic('v3_1_9', 0);
	}
	public static function v3_2(): self
	{
		return self::__callStatic('v3_2', 0);
	}
	public static function v3_2_1(): self
	{
		return self::__callStatic('v3_2_1', 0);
	}
	public static function v3_2_2(): self
	{
		return self::__callStatic('v3_2_2', 0);
	}
	public static function v3_2_3(): self
	{
		return self::__callStatic('v3_2_3', 0);
	}
	public static function v3_2_4(): self
	{
		return self::__callStatic('v3_2_4', 0);
	}
	public static function v3_2_5(): self
	{
		return self::__callStatic('v3_2_5', 0);
	}
	public static function v3_2_6(): self
	{
		return self::__callStatic('v3_2_6', 0);
	}
	public static function v3_2_7(): self
	{
		return self::__callStatic('v3_2_7', 0);
	}
	public static function v3_2_8(): self
	{
		return self::__callStatic('v3_2_8', 0);
	}
	public static function v3_2_9(): self
	{
		return self::__callStatic('v3_2_9', 0);
	}
	public static function v3_3(): self
	{
		return self::__callStatic('v3_3', 0);
	}
	public static function v3_3_1(): self
	{
		return self::__callStatic('v3_3_1', 0);
	}
	public static function v3_3_2(): self
	{
		return self::__callStatic('v3_3_2', 0);
	}
	public static function v3_3_3(): self
	{
		return self::__callStatic('v3_3_3', 0);
	}
	public static function v3_3_4(): self
	{
		return self::__callStatic('v3_3_4', 0);
	}
	public static function v3_3_5(): self
	{
		return self::__callStatic('v3_3_5', 0);
	}
	public static function v3_3_6(): self
	{
		return self::__callStatic('v3_3_6', 0);
	}
	public static function v3_3_7(): self
	{
		return self::__callStatic('v3_3_7', 0);
	}
	public static function v3_3_8(): self
	{
		return self::__callStatic('v3_3_8', 0);
	}
	public static function v3_3_9(): self
	{
		return self::__callStatic('v3_3_9', 0);
	}
	public static function v3_4(): self
	{
		return self::__callStatic('v3_4', 0);
	}
	public static function v3_4_1(): self
	{
		return self::__callStatic('v3_4_1', 0);
	}
	public static function v3_4_2(): self
	{
		return self::__callStatic('v3_4_2', 0);
	}
	public static function v3_4_3(): self
	{
		return self::__callStatic('v3_4_3', 0);
	}
	public static function v3_4_4(): self
	{
		return self::__callStatic('v3_4_4', 0);
	}
	public static function v3_4_5(): self
	{
		return self::__callStatic('v3_4_5', 0);
	}
	public static function v3_4_6(): self
	{
		return self::__callStatic('v3_4_6', 0);
	}
	public static function v3_4_7(): self
	{
		return self::__callStatic('v3_4_7', 0);
	}
	public static function v3_4_8(): self
	{
		return self::__callStatic('v3_4_8', 0);
	}
	public static function v3_4_9(): self
	{
		return self::__callStatic('v3_4_9', 0);
	}
	public static function v3_5(): self
	{
		return self::__callStatic('v3_5', 0);
	}
	public static function v3_5_1(): self
	{
		return self::__callStatic('v3_5_1', 0);
	}
	public static function v3_5_2(): self
	{
		return self::__callStatic('v3_5_2', 0);
	}
	public static function v3_5_3(): self
	{
		return self::__callStatic('v3_5_3', 0);
	}
	public static function v3_5_4(): self
	{
		return self::__callStatic('v3_5_4', 0);
	}
	public static function v3_5_5(): self
	{
		return self::__callStatic('v3_5_5', 0);
	}
	public static function v3_5_6(): self
	{
		return self::__callStatic('v3_5_6', 0);
	}
	public static function v3_5_7(): self
	{
		return self::__callStatic('v3_5_7', 0);
	}
	public static function v3_5_8(): self
	{
		return self::__callStatic('v3_5_8', 0);
	}
	public static function v3_5_9(): self
	{
		return self::__callStatic('v3_5_9', 0);
	}
	public static function v3_6(): self
	{
		return self::__callStatic('v3_6', 0);
	}
	public static function v3_6_1(): self
	{
		return self::__callStatic('v3_6_1', 0);
	}
	public static function v3_6_2(): self
	{
		return self::__callStatic('v3_6_2', 0);
	}
	public static function v3_6_3(): self
	{
		return self::__callStatic('v3_6_3', 0);
	}
	public static function v3_6_4(): self
	{
		return self::__callStatic('v3_6_4', 0);
	}
	public static function v3_6_5(): self
	{
		return self::__callStatic('v3_6_5', 0);
	}
	public static function v3_6_6(): self
	{
		return self::__callStatic('v3_6_6', 0);
	}
	public static function v3_6_7(): self
	{
		return self::__callStatic('v3_6_7', 0);
	}
	public static function v3_6_8(): self
	{
		return self::__callStatic('v3_6_8', 0);
	}
	public static function v3_6_9(): self
	{
		return self::__callStatic('v3_6_9', 0);
	}
	public static function v3_7(): self
	{
		return self::__callStatic('v3_7', 0);
	}
	public static function v3_7_1(): self
	{
		return self::__callStatic('v3_7_1', 0);
	}
	public static function v3_7_2(): self
	{
		return self::__callStatic('v3_7_2', 0);
	}
	public static function v3_7_3(): self
	{
		return self::__callStatic('v3_7_3', 0);
	}
	public static function v3_7_4(): self
	{
		return self::__callStatic('v3_7_4', 0);
	}
	public static function v3_7_5(): self
	{
		return self::__callStatic('v3_7_5', 0);
	}
	public static function v3_7_6(): self
	{
		return self::__callStatic('v3_7_6', 0);
	}
	public static function v3_7_7(): self
	{
		return self::__callStatic('v3_7_7', 0);
	}
	public static function v3_7_8(): self
	{
		return self::__callStatic('v3_7_8', 0);
	}
	public static function v3_7_9(): self
	{
		return self::__callStatic('v3_7_9', 0);
	}
	public static function v3_8(): self
	{
		return self::__callStatic('v3_8', 0);
	}
	public static function v3_8_1(): self
	{
		return self::__callStatic('v3_8_1', 0);
	}
	public static function v3_8_2(): self
	{
		return self::__callStatic('v3_8_2', 0);
	}
	public static function v3_8_3(): self
	{
		return self::__callStatic('v3_8_3', 0);
	}
	public static function v3_8_4(): self
	{
		return self::__callStatic('v3_8_4', 0);
	}
	public static function v3_8_5(): self
	{
		return self::__callStatic('v3_8_5', 0);
	}
	public static function v3_8_6(): self
	{
		return self::__callStatic('v3_8_6', 0);
	}
	public static function v3_8_7(): self
	{
		return self::__callStatic('v3_8_7', 0);
	}
	public static function v3_8_8(): self
	{
		return self::__callStatic('v3_8_8', 0);
	}
	public static function v3_8_9(): self
	{
		return self::__callStatic('v3_8_9', 0);
	}
	public static function v3_9(): self
	{
		return self::__callStatic('v3_9', 0);
	}
	public static function v3_9_1(): self
	{
		return self::__callStatic('v3_9_1', 0);
	}
	public static function v3_9_2(): self
	{
		return self::__callStatic('v3_9_2', 0);
	}
	public static function v3_9_3(): self
	{
		return self::__callStatic('v3_9_3', 0);
	}
	public static function v3_9_4(): self
	{
		return self::__callStatic('v3_9_4', 0);
	}
	public static function v3_9_5(): self
	{
		return self::__callStatic('v3_9_5', 0);
	}
	public static function v3_9_6(): self
	{
		return self::__callStatic('v3_9_6', 0);
	}
	public static function v3_9_7(): self
	{
		return self::__callStatic('v3_9_7', 0);
	}
	public static function v3_9_8(): self
	{
		return self::__callStatic('v3_9_8', 0);
	}
	public static function v3_9_9(): self
	{
		return self::__callStatic('v3_9_9', 0);
	}

	public static function v4(): self
	{
		return self::__callStatic('v4', 0);
	}
	public static function v4_0(): self
	{
		return self::__callStatic('v4_0', 0);
	}
	public static function v4_1(): self
	{
		return self::__callStatic('v4_1', 0);
	}
	public static function v4_1_1(): self
	{
		return self::__callStatic('v4_1_1', 0);
	}
	public static function v4_1_2(): self
	{
		return self::__callStatic('v4_1_2', 0);
	}
	public static function v4_1_3(): self
	{
		return self::__callStatic('v4_1_3', 0);
	}
	public static function v4_1_4(): self
	{
		return self::__callStatic('v4_1_4', 0);
	}
	public static function v4_1_5(): self
	{
		return self::__callStatic('v4_1_5', 0);
	}
	public static function v4_1_6(): self
	{
		return self::__callStatic('v4_1_6', 0);
	}
	public static function v4_1_7(): self
	{
		return self::__callStatic('v4_1_7', 0);
	}
	public static function v4_1_8(): self
	{
		return self::__callStatic('v4_1_8', 0);
	}
	public static function v4_1_9(): self
	{
		return self::__callStatic('v4_1_9', 0);
	}
	public static function v4_2(): self
	{
		return self::__callStatic('v4_2', 0);
	}
	public static function v4_2_1(): self
	{
		return self::__callStatic('v4_2_1', 0);
	}
	public static function v4_2_2(): self
	{
		return self::__callStatic('v4_2_2', 0);
	}
	public static function v4_2_3(): self
	{
		return self::__callStatic('v4_2_3', 0);
	}
	public static function v4_2_4(): self
	{
		return self::__callStatic('v4_2_4', 0);
	}
	public static function v4_2_5(): self
	{
		return self::__callStatic('v4_2_5', 0);
	}
	public static function v4_2_6(): self
	{
		return self::__callStatic('v4_2_6', 0);
	}
	public static function v4_2_7(): self
	{
		return self::__callStatic('v4_2_7', 0);
	}
	public static function v4_2_8(): self
	{
		return self::__callStatic('v4_2_8', 0);
	}
	public static function v4_2_9(): self
	{
		return self::__callStatic('v4_2_9', 0);
	}
	public static function v4_3(): self
	{
		return self::__callStatic('v4_3', 0);
	}
	public static function v4_3_1(): self
	{
		return self::__callStatic('v4_3_1', 0);
	}
	public static function v4_3_2(): self
	{
		return self::__callStatic('v4_3_2', 0);
	}
	public static function v4_3_3(): self
	{
		return self::__callStatic('v4_3_3', 0);
	}
	public static function v4_3_4(): self
	{
		return self::__callStatic('v4_3_4', 0);
	}
	public static function v4_3_5(): self
	{
		return self::__callStatic('v4_3_5', 0);
	}
	public static function v4_3_6(): self
	{
		return self::__callStatic('v4_3_6', 0);
	}
	public static function v4_3_7(): self
	{
		return self::__callStatic('v4_3_7', 0);
	}
	public static function v4_3_8(): self
	{
		return self::__callStatic('v4_3_8', 0);
	}
	public static function v4_3_9(): self
	{
		return self::__callStatic('v4_3_9', 0);
	}
	public static function v4_4(): self
	{
		return self::__callStatic('v4_4', 0);
	}
	public static function v4_4_1(): self
	{
		return self::__callStatic('v4_4_1', 0);
	}
	public static function v4_4_2(): self
	{
		return self::__callStatic('v4_4_2', 0);
	}
	public static function v4_4_3(): self
	{
		return self::__callStatic('v4_4_3', 0);
	}
	public static function v4_4_4(): self
	{
		return self::__callStatic('v4_4_4', 0);
	}
	public static function v4_4_5(): self
	{
		return self::__callStatic('v4_4_5', 0);
	}
	public static function v4_4_6(): self
	{
		return self::__callStatic('v4_4_6', 0);
	}
	public static function v4_4_7(): self
	{
		return self::__callStatic('v4_4_7', 0);
	}
	public static function v4_4_8(): self
	{
		return self::__callStatic('v4_4_8', 0);
	}
	public static function v4_4_9(): self
	{
		return self::__callStatic('v4_4_9', 0);
	}
	public static function v4_5(): self
	{
		return self::__callStatic('v4_5', 0);
	}
	public static function v4_5_1(): self
	{
		return self::__callStatic('v4_5_1', 0);
	}
	public static function v4_5_2(): self
	{
		return self::__callStatic('v4_5_2', 0);
	}
	public static function v4_5_3(): self
	{
		return self::__callStatic('v4_5_3', 0);
	}
	public static function v4_5_4(): self
	{
		return self::__callStatic('v4_5_4', 0);
	}
	public static function v4_5_5(): self
	{
		return self::__callStatic('v4_5_5', 0);
	}
	public static function v4_5_6(): self
	{
		return self::__callStatic('v4_5_6', 0);
	}
	public static function v4_5_7(): self
	{
		return self::__callStatic('v4_5_7', 0);
	}
	public static function v4_5_8(): self
	{
		return self::__callStatic('v4_5_8', 0);
	}
	public static function v4_5_9(): self
	{
		return self::__callStatic('v4_5_9', 0);
	}
	public static function v4_6(): self
	{
		return self::__callStatic('v4_6', 0);
	}
	public static function v4_6_1(): self
	{
		return self::__callStatic('v4_6_1', 0);
	}
	public static function v4_6_2(): self
	{
		return self::__callStatic('v4_6_2', 0);
	}
	public static function v4_6_3(): self
	{
		return self::__callStatic('v4_6_3', 0);
	}
	public static function v4_6_4(): self
	{
		return self::__callStatic('v4_6_4', 0);
	}
	public static function v4_6_5(): self
	{
		return self::__callStatic('v4_6_5', 0);
	}
	public static function v4_6_6(): self
	{
		return self::__callStatic('v4_6_6', 0);
	}
	public static function v4_6_7(): self
	{
		return self::__callStatic('v4_6_7', 0);
	}
	public static function v4_6_8(): self
	{
		return self::__callStatic('v4_6_8', 0);
	}
	public static function v4_6_9(): self
	{
		return self::__callStatic('v4_6_9', 0);
	}
	public static function v4_7(): self
	{
		return self::__callStatic('v4_7', 0);
	}
	public static function v4_7_1(): self
	{
		return self::__callStatic('v4_7_1', 0);
	}
	public static function v4_7_2(): self
	{
		return self::__callStatic('v4_7_2', 0);
	}
	public static function v4_7_3(): self
	{
		return self::__callStatic('v4_7_3', 0);
	}
	public static function v4_7_4(): self
	{
		return self::__callStatic('v4_7_4', 0);
	}
	public static function v4_7_5(): self
	{
		return self::__callStatic('v4_7_5', 0);
	}
	public static function v4_7_6(): self
	{
		return self::__callStatic('v4_7_6', 0);
	}
	public static function v4_7_7(): self
	{
		return self::__callStatic('v4_7_7', 0);
	}
	public static function v4_7_8(): self
	{
		return self::__callStatic('v4_7_8', 0);
	}
	public static function v4_7_9(): self
	{
		return self::__callStatic('v4_7_9', 0);
	}
	public static function v4_8(): self
	{
		return self::__callStatic('v4_8', 0);
	}
	public static function v4_8_1(): self
	{
		return self::__callStatic('v4_8_1', 0);
	}
	public static function v4_8_2(): self
	{
		return self::__callStatic('v4_8_2', 0);
	}
	public static function v4_8_3(): self
	{
		return self::__callStatic('v4_8_3', 0);
	}
	public static function v4_8_4(): self
	{
		return self::__callStatic('v4_8_4', 0);
	}
	public static function v4_8_5(): self
	{
		return self::__callStatic('v4_8_5', 0);
	}
	public static function v4_8_6(): self
	{
		return self::__callStatic('v4_8_6', 0);
	}
	public static function v4_8_7(): self
	{
		return self::__callStatic('v4_8_7', 0);
	}
	public static function v4_8_8(): self
	{
		return self::__callStatic('v4_8_8', 0);
	}
	public static function v4_8_9(): self
	{
		return self::__callStatic('v4_8_9', 0);
	}
	public static function v4_9(): self
	{
		return self::__callStatic('v4_9', 0);
	}
	public static function v4_9_1(): self
	{
		return self::__callStatic('v4_9_1', 0);
	}
	public static function v4_9_2(): self
	{
		return self::__callStatic('v4_9_2', 0);
	}
	public static function v4_9_3(): self
	{
		return self::__callStatic('v4_9_3', 0);
	}
	public static function v4_9_4(): self
	{
		return self::__callStatic('v4_9_4', 0);
	}
	public static function v4_9_5(): self
	{
		return self::__callStatic('v4_9_5', 0);
	}
	public static function v4_9_6(): self
	{
		return self::__callStatic('v4_9_6', 0);
	}
	public static function v4_9_7(): self
	{
		return self::__callStatic('v4_9_7', 0);
	}
	public static function v4_9_8(): self
	{
		return self::__callStatic('v4_9_8', 0);
	}
	public static function v4_9_9(): self
	{
		return self::__callStatic('v4_9_9', 0);
	}

	public static function v5(): self
	{
		return self::__callStatic('v5', 0);
	}
	public static function v5_0(): self
	{
		return self::__callStatic('v5_0', 0);
	}
	public static function v5_1(): self
	{
		return self::__callStatic('v5_1', 0);
	}
	public static function v5_1_1(): self
	{
		return self::__callStatic('v5_1_1', 0);
	}
	public static function v5_1_2(): self
	{
		return self::__callStatic('v5_1_2', 0);
	}
	public static function v5_1_3(): self
	{
		return self::__callStatic('v5_1_3', 0);
	}
	public static function v5_1_4(): self
	{
		return self::__callStatic('v5_1_4', 0);
	}
	public static function v5_1_5(): self
	{
		return self::__callStatic('v5_1_5', 0);
	}
	public static function v5_1_6(): self
	{
		return self::__callStatic('v5_1_6', 0);
	}
	public static function v5_1_7(): self
	{
		return self::__callStatic('v5_1_7', 0);
	}
	public static function v5_1_8(): self
	{
		return self::__callStatic('v5_1_8', 0);
	}
	public static function v5_1_9(): self
	{
		return self::__callStatic('v5_1_9', 0);
	}
	public static function v5_2(): self
	{
		return self::__callStatic('v5_2', 0);
	}
	public static function v5_2_1(): self
	{
		return self::__callStatic('v5_2_1', 0);
	}
	public static function v5_2_2(): self
	{
		return self::__callStatic('v5_2_2', 0);
	}
	public static function v5_2_3(): self
	{
		return self::__callStatic('v5_2_3', 0);
	}
	public static function v5_2_4(): self
	{
		return self::__callStatic('v5_2_4', 0);
	}
	public static function v5_2_5(): self
	{
		return self::__callStatic('v5_2_5', 0);
	}
	public static function v5_2_6(): self
	{
		return self::__callStatic('v5_2_6', 0);
	}
	public static function v5_2_7(): self
	{
		return self::__callStatic('v5_2_7', 0);
	}
	public static function v5_2_8(): self
	{
		return self::__callStatic('v5_2_8', 0);
	}
	public static function v5_2_9(): self
	{
		return self::__callStatic('v5_2_9', 0);
	}
	public static function v5_3(): self
	{
		return self::__callStatic('v5_3', 0);
	}
	public static function v5_3_1(): self
	{
		return self::__callStatic('v5_3_1', 0);
	}
	public static function v5_3_2(): self
	{
		return self::__callStatic('v5_3_2', 0);
	}
	public static function v5_3_3(): self
	{
		return self::__callStatic('v5_3_3', 0);
	}
	public static function v5_3_4(): self
	{
		return self::__callStatic('v5_3_4', 0);
	}
	public static function v5_3_5(): self
	{
		return self::__callStatic('v5_3_5', 0);
	}
	public static function v5_3_6(): self
	{
		return self::__callStatic('v5_3_6', 0);
	}
	public static function v5_3_7(): self
	{
		return self::__callStatic('v5_3_7', 0);
	}
	public static function v5_3_8(): self
	{
		return self::__callStatic('v5_3_8', 0);
	}
	public static function v5_3_9(): self
	{
		return self::__callStatic('v5_3_9', 0);
	}
	public static function v5_4(): self
	{
		return self::__callStatic('v5_4', 0);
	}
	public static function v5_4_1(): self
	{
		return self::__callStatic('v5_4_1', 0);
	}
	public static function v5_4_2(): self
	{
		return self::__callStatic('v5_4_2', 0);
	}
	public static function v5_4_3(): self
	{
		return self::__callStatic('v5_4_3', 0);
	}
	public static function v5_4_4(): self
	{
		return self::__callStatic('v5_4_4', 0);
	}
	public static function v5_4_5(): self
	{
		return self::__callStatic('v5_4_5', 0);
	}
	public static function v5_4_6(): self
	{
		return self::__callStatic('v5_4_6', 0);
	}
	public static function v5_4_7(): self
	{
		return self::__callStatic('v5_4_7', 0);
	}
	public static function v5_4_8(): self
	{
		return self::__callStatic('v5_4_8', 0);
	}
	public static function v5_4_9(): self
	{
		return self::__callStatic('v5_4_9', 0);
	}
	public static function v5_5(): self
	{
		return self::__callStatic('v5_5', 0);
	}
	public static function v5_5_1(): self
	{
		return self::__callStatic('v5_5_1', 0);
	}
	public static function v5_5_2(): self
	{
		return self::__callStatic('v5_5_2', 0);
	}
	public static function v5_5_3(): self
	{
		return self::__callStatic('v5_5_3', 0);
	}
	public static function v5_5_4(): self
	{
		return self::__callStatic('v5_5_4', 0);
	}
	public static function v5_5_5(): self
	{
		return self::__callStatic('v5_5_5', 0);
	}
	public static function v5_5_6(): self
	{
		return self::__callStatic('v5_5_6', 0);
	}
	public static function v5_5_7(): self
	{
		return self::__callStatic('v5_5_7', 0);
	}
	public static function v5_5_8(): self
	{
		return self::__callStatic('v5_5_8', 0);
	}
	public static function v5_5_9(): self
	{
		return self::__callStatic('v5_5_9', 0);
	}
	public static function v5_6(): self
	{
		return self::__callStatic('v5_6', 0);
	}
	public static function v5_6_1(): self
	{
		return self::__callStatic('v5_6_1', 0);
	}
	public static function v5_6_2(): self
	{
		return self::__callStatic('v5_6_2', 0);
	}
	public static function v5_6_3(): self
	{
		return self::__callStatic('v5_6_3', 0);
	}
	public static function v5_6_4(): self
	{
		return self::__callStatic('v5_6_4', 0);
	}
	public static function v5_6_5(): self
	{
		return self::__callStatic('v5_6_5', 0);
	}
	public static function v5_6_6(): self
	{
		return self::__callStatic('v5_6_6', 0);
	}
	public static function v5_6_7(): self
	{
		return self::__callStatic('v5_6_7', 0);
	}
	public static function v5_6_8(): self
	{
		return self::__callStatic('v5_6_8', 0);
	}
	public static function v5_6_9(): self
	{
		return self::__callStatic('v5_6_9', 0);
	}
	public static function v5_7(): self
	{
		return self::__callStatic('v5_7', 0);
	}
	public static function v5_7_1(): self
	{
		return self::__callStatic('v5_7_1', 0);
	}
	public static function v5_7_2(): self
	{
		return self::__callStatic('v5_7_2', 0);
	}
	public static function v5_7_3(): self
	{
		return self::__callStatic('v5_7_3', 0);
	}
	public static function v5_7_4(): self
	{
		return self::__callStatic('v5_7_4', 0);
	}
	public static function v5_7_5(): self
	{
		return self::__callStatic('v5_7_5', 0);
	}
	public static function v5_7_6(): self
	{
		return self::__callStatic('v5_7_6', 0);
	}
	public static function v5_7_7(): self
	{
		return self::__callStatic('v5_7_7', 0);
	}
	public static function v5_7_8(): self
	{
		return self::__callStatic('v5_7_8', 0);
	}
	public static function v5_7_9(): self
	{
		return self::__callStatic('v5_7_9', 0);
	}
	public static function v5_8(): self
	{
		return self::__callStatic('v5_8', 0);
	}
	public static function v5_8_1(): self
	{
		return self::__callStatic('v5_8_1', 0);
	}
	public static function v5_8_2(): self
	{
		return self::__callStatic('v5_8_2', 0);
	}
	public static function v5_8_3(): self
	{
		return self::__callStatic('v5_8_3', 0);
	}
	public static function v5_8_4(): self
	{
		return self::__callStatic('v5_8_4', 0);
	}
	public static function v5_8_5(): self
	{
		return self::__callStatic('v5_8_5', 0);
	}
	public static function v5_8_6(): self
	{
		return self::__callStatic('v5_8_6', 0);
	}
	public static function v5_8_7(): self
	{
		return self::__callStatic('v5_8_7', 0);
	}
	public static function v5_8_8(): self
	{
		return self::__callStatic('v5_8_8', 0);
	}
	public static function v5_8_9(): self
	{
		return self::__callStatic('v5_8_9', 0);
	}
	public static function v5_9(): self
	{
		return self::__callStatic('v5_9', 0);
	}
	public static function v5_9_1(): self
	{
		return self::__callStatic('v5_9_1', 0);
	}
	public static function v5_9_2(): self
	{
		return self::__callStatic('v5_9_2', 0);
	}
	public static function v5_9_3(): self
	{
		return self::__callStatic('v5_9_3', 0);
	}
	public static function v5_9_4(): self
	{
		return self::__callStatic('v5_9_4', 0);
	}
	public static function v5_9_5(): self
	{
		return self::__callStatic('v5_9_5', 0);
	}
	public static function v5_9_6(): self
	{
		return self::__callStatic('v5_9_6', 0);
	}
	public static function v5_9_7(): self
	{
		return self::__callStatic('v5_9_7', 0);
	}
	public static function v5_9_8(): self
	{
		return self::__callStatic('v5_9_8', 0);
	}
	public static function v5_9_9(): self
	{
		return self::__callStatic('v5_9_9', 0);
	}
	public static function v6(): self
	{
		return self::__callStatic('v6', 0);
	}
	public static function v6_0(): self
	{
		return self::__callStatic('v6_0', 0);
	}
	public static function v6_1(): self
	{
		return self::__callStatic('v6_1', 0);
	}
	public static function v6_1_1(): self
	{
		return self::__callStatic('v6_1_1', 0);
	}
	public static function v6_1_2(): self
	{
		return self::__callStatic('v6_1_2', 0);
	}
	public static function v6_1_3(): self
	{
		return self::__callStatic('v6_1_3', 0);
	}
	public static function v6_1_4(): self
	{
		return self::__callStatic('v6_1_4', 0);
	}
	public static function v6_1_5(): self
	{
		return self::__callStatic('v6_1_5', 0);
	}
	public static function v6_1_6(): self
	{
		return self::__callStatic('v6_1_6', 0);
	}
	public static function v6_1_7(): self
	{
		return self::__callStatic('v6_1_7', 0);
	}
	public static function v6_1_8(): self
	{
		return self::__callStatic('v6_1_8', 0);
	}
	public static function v6_1_9(): self
	{
		return self::__callStatic('v6_1_9', 0);
	}
	public static function v6_2(): self
	{
		return self::__callStatic('v6_2', 0);
	}
	public static function v6_2_1(): self
	{
		return self::__callStatic('v6_2_1', 0);
	}
	public static function v6_2_2(): self
	{
		return self::__callStatic('v6_2_2', 0);
	}
	public static function v6_2_3(): self
	{
		return self::__callStatic('v6_2_3', 0);
	}
	public static function v6_2_4(): self
	{
		return self::__callStatic('v6_2_4', 0);
	}
	public static function v6_2_5(): self
	{
		return self::__callStatic('v6_2_5', 0);
	}
	public static function v6_2_6(): self
	{
		return self::__callStatic('v6_2_6', 0);
	}
	public static function v6_2_7(): self
	{
		return self::__callStatic('v6_2_7', 0);
	}
	public static function v6_2_8(): self
	{
		return self::__callStatic('v6_2_8', 0);
	}
	public static function v6_2_9(): self
	{
		return self::__callStatic('v6_2_9', 0);
	}
	public static function v6_3(): self
	{
		return self::__callStatic('v6_3', 0);
	}
	public static function v6_3_1(): self
	{
		return self::__callStatic('v6_3_1', 0);
	}
	public static function v6_3_2(): self
	{
		return self::__callStatic('v6_3_2', 0);
	}
	public static function v6_3_3(): self
	{
		return self::__callStatic('v6_3_3', 0);
	}
	public static function v6_3_4(): self
	{
		return self::__callStatic('v6_3_4', 0);
	}
	public static function v6_3_5(): self
	{
		return self::__callStatic('v6_3_5', 0);
	}
	public static function v6_3_6(): self
	{
		return self::__callStatic('v6_3_6', 0);
	}
	public static function v6_3_7(): self
	{
		return self::__callStatic('v6_3_7', 0);
	}
	public static function v6_3_8(): self
	{
		return self::__callStatic('v6_3_8', 0);
	}
	public static function v6_3_9(): self
	{
		return self::__callStatic('v6_3_9', 0);
	}
	public static function v6_4(): self
	{
		return self::__callStatic('v6_4', 0);
	}
	public static function v6_4_1(): self
	{
		return self::__callStatic('v6_4_1', 0);
	}
	public static function v6_4_2(): self
	{
		return self::__callStatic('v6_4_2', 0);
	}
	public static function v6_4_3(): self
	{
		return self::__callStatic('v6_4_3', 0);
	}
	public static function v6_4_4(): self
	{
		return self::__callStatic('v6_4_4', 0);
	}
	public static function v6_4_5(): self
	{
		return self::__callStatic('v6_4_5', 0);
	}
	public static function v6_4_6(): self
	{
		return self::__callStatic('v6_4_6', 0);
	}
	public static function v6_4_7(): self
	{
		return self::__callStatic('v6_4_7', 0);
	}
	public static function v6_4_8(): self
	{
		return self::__callStatic('v6_4_8', 0);
	}
	public static function v6_4_9(): self
	{
		return self::__callStatic('v6_4_9', 0);
	}
	public static function v6_5(): self
	{
		return self::__callStatic('v6_5', 0);
	}
	public static function v6_5_1(): self
	{
		return self::__callStatic('v6_5_1', 0);
	}
	public static function v6_5_2(): self
	{
		return self::__callStatic('v6_5_2', 0);
	}
	public static function v6_5_3(): self
	{
		return self::__callStatic('v6_5_3', 0);
	}
	public static function v6_5_4(): self
	{
		return self::__callStatic('v6_5_4', 0);
	}
	public static function v6_5_5(): self
	{
		return self::__callStatic('v6_5_5', 0);
	}
	public static function v6_5_6(): self
	{
		return self::__callStatic('v6_5_6', 0);
	}
	public static function v6_5_7(): self
	{
		return self::__callStatic('v6_5_7', 0);
	}
	public static function v6_5_8(): self
	{
		return self::__callStatic('v6_5_8', 0);
	}
	public static function v6_5_9(): self
	{
		return self::__callStatic('v6_5_9', 0);
	}
	public static function v6_6(): self
	{
		return self::__callStatic('v6_6', 0);
	}
	public static function v6_6_1(): self
	{
		return self::__callStatic('v6_6_1', 0);
	}
	public static function v6_6_2(): self
	{
		return self::__callStatic('v6_6_2', 0);
	}
	public static function v6_6_3(): self
	{
		return self::__callStatic('v6_6_3', 0);
	}
	public static function v6_6_4(): self
	{
		return self::__callStatic('v6_6_4', 0);
	}
	public static function v6_6_5(): self
	{
		return self::__callStatic('v6_6_5', 0);
	}
	public static function v6_6_6(): self
	{
		return self::__callStatic('v6_6_6', 0);
	}
	public static function v6_6_7(): self
	{
		return self::__callStatic('v6_6_7', 0);
	}
	public static function v6_6_8(): self
	{
		return self::__callStatic('v6_6_8', 0);
	}
	public static function v6_6_9(): self
	{
		return self::__callStatic('v6_6_9', 0);
	}
	public static function v6_7(): self
	{
		return self::__callStatic('v6_7', 0);
	}
	public static function v6_7_1(): self
	{
		return self::__callStatic('v6_7_1', 0);
	}
	public static function v6_7_2(): self
	{
		return self::__callStatic('v6_7_2', 0);
	}
	public static function v6_7_3(): self
	{
		return self::__callStatic('v6_7_3', 0);
	}
	public static function v6_7_4(): self
	{
		return self::__callStatic('v6_7_4', 0);
	}
	public static function v6_7_5(): self
	{
		return self::__callStatic('v6_7_5', 0);
	}
	public static function v6_7_6(): self
	{
		return self::__callStatic('v6_7_6', 0);
	}
	public static function v6_7_7(): self
	{
		return self::__callStatic('v6_7_7', 0);
	}
	public static function v6_7_8(): self
	{
		return self::__callStatic('v6_7_8', 0);
	}
	public static function v6_7_9(): self
	{
		return self::__callStatic('v6_7_9', 0);
	}
	public static function v6_8(): self
	{
		return self::__callStatic('v6_8', 0);
	}
	public static function v6_8_1(): self
	{
		return self::__callStatic('v6_8_1', 0);
	}
	public static function v6_8_2(): self
	{
		return self::__callStatic('v6_8_2', 0);
	}
	public static function v6_8_3(): self
	{
		return self::__callStatic('v6_8_3', 0);
	}
	public static function v6_8_4(): self
	{
		return self::__callStatic('v6_8_4', 0);
	}
	public static function v6_8_5(): self
	{
		return self::__callStatic('v6_8_5', 0);
	}
	public static function v6_8_6(): self
	{
		return self::__callStatic('v6_8_6', 0);
	}
	public static function v6_8_7(): self
	{
		return self::__callStatic('v6_8_7', 0);
	}
	public static function v6_8_8(): self
	{
		return self::__callStatic('v6_8_8', 0);
	}
	public static function v6_8_9(): self
	{
		return self::__callStatic('v6_8_9', 0);
	}
	public static function v6_9(): self
	{
		return self::__callStatic('v6_9', 0);
	}
	public static function v6_9_1(): self
	{
		return self::__callStatic('v6_9_1', 0);
	}
	public static function v6_9_2(): self
	{
		return self::__callStatic('v6_9_2', 0);
	}
	public static function v6_9_3(): self
	{
		return self::__callStatic('v6_9_3', 0);
	}
	public static function v6_9_4(): self
	{
		return self::__callStatic('v6_9_4', 0);
	}
	public static function v6_9_5(): self
	{
		return self::__callStatic('v6_9_5', 0);
	}
	public static function v6_9_6(): self
	{
		return self::__callStatic('v6_9_6', 0);
	}
	public static function v6_9_7(): self
	{
		return self::__callStatic('v6_9_7', 0);
	}
	public static function v6_9_8(): self
	{
		return self::__callStatic('v6_9_8', 0);
	}
	public static function v6_9_9(): self
	{
		return self::__callStatic('v6_9_9', 0);
	}
	public static function v7(): self
	{
		return self::__callStatic('v7', 0);
	}
	public static function v7_0(): self
	{
		return self::__callStatic('v7_0', 0);
	}
	public static function v7_0_0(): self
	{
		return self::__callStatic('v7_0_0', 0);
	}
}
