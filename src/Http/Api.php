<?php declare(strict_types=1);

namespace PhpSlides\Http;

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
 * @author dconco <info@dconco.dev>
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
	private static string $version = 'v1';

	private ?array $define = null;

	private ?array $guards = null;

	private static array $regRoute = [];

	private static array $allRoutes;

	/**
	 * Handles static method calls to set the API version dynamically.
	 *
	 * @param string $method The method name which starts with 'v' followed by the version number. Use `_` in place of `.`
	 * @throws \PhpSlides\Exception
	 * @return self
	 */
	public static function __callStatic ($method, $args): self
	{
		if (str_starts_with($method, 'v'))
		{
			$method_v = str_replace('_', '.', $method);
			self::$version = $method_v;

			return new self();
		}
		else
		{
			throw new Exception('Version method starts with `v`');
		}
	}

	/**
	 * Assigns a name to the last registered route for easier reference.
	 *
	 * @param string $name The name to assign to the route.
	 * @return self
	 */
	public function name (string $name): self
	{
		if (is_array(end(self::$regRoute)))
		{
			for ($i = 0; $i < count(end(self::$regRoute)); $i++)
			{
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
	 * if null is given, then it's consider optional, accepts all methods.
	 * @return self
	 */
	public function route (
	 string $url,
	 string|array|null $controller = null,
	 ?string $req_method = null,
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

		$route = [
		 'url' => $uri,
		 'guards' => $this->guards ?? null,
		 'r_method' => $req_method,
		 'controller' =>
		  $define['controller'] ??
		  (is_array($controller) ? $controller[0] : $controller),
		];

		if ($define !== null && $controller !== null)
		{
			$route['c_method'] = trim($controller, '@');
		}
		if (is_array($controller))
		{
			$route['c_method'] = $controller[1];
		}

		$GLOBALS['__registered_api_routes'][$uri]['route'] = $route;

		$newInstance = new self();
		$newInstance->define = $define;
		$newInstance->guards = $this->guards ?? null;
		return $newInstance;
	}

	/**
	 * Applies Authentication Guard to the current route.
	 *
	 * @param ?string ...$guards String parameters of registered guards.
	 * @return self
	 */
	public function withGuard (?string ...$guards): self
	{
		if (!$guards[0])
		{
			$this->guards = null;
		}
		else
		{
			$this->guards = $guards;
		}
		return $this;
	}

	/**
	 * Defines a base URL and controller for subsequent route mappings.
	 *
	 * @param string $url The base URL for the routes.
	 * @param string $controller The controller handling the routes.
	 * @return self
	 */
	public function define (string $url, string $controller): self
	{
		$this->define = [
		 'url' => $url,
		 'controller' => $controller,
		];

		return $this;
	}

	/**
	 * Maps multiple HTTP methods to a URL with their corresponding controller methods.
	 *
	 * @param array An associative array where the key is the route and the value is an array with the HTTP method and controller method.
	 * @return self
	 */
	public function map (array $rest_url): self
	{
		$define = $this->define;

		if ($define !== null)
		{
			/**
			 * Get the map value, keys as the route url
			 */
			$routes = array_keys($rest_url);
			$base = strtolower(
			 self::$BASE_URL .
			  self::$version .
			  '/' .
			  trim($define['url'], '/') .
			  '/',
			);

			/**
			 * Map route url array to the full base url
			 */
			$full_url = array_map(
			 fn ($route) => $base . ltrim($route, '/'),
			 $routes,
			);

			$rest_url = array_map(function ($uri)
			{
				$uri[] = $this->guards ?? null;
				return $uri;
			}, $rest_url);

			self::$regRoute[] = $full_url;

			$rest_url['base_url'] = $base;
			$rest_url['controller'] = $define['controller'];

			$map = $rest_url;
			$GLOBALS['__registered_api_routes'][$full_url[0]]['map'] = $map;
		}

		$newInstance = new self();
		$newInstance->define = $define;
		$newInstance->guards = $this->guards ?? null;
		return $newInstance;
	}

	/**
	 * Define an API route for version 1.
	 * Also in use for defining urls
	 *
	 * @return self
	 */
	public static function v1 (): self
	{
		return self::__callStatic('v1', 0);
	}

	public static function v1_0 (): self
	{
		return self::__callStatic('v1_0', 0);
	}
}
