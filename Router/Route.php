<?php declare(strict_types=1);

/**
 * This file is the main entry point for the PhpSlides application.
 * It declares strict types and defines the namespace for the application.
 * It also imports the necessary classes and interfaces.
 *
 * @package Router
 * @version ^1.4.1
 * @since 1.0.0
 * @link https://github.com/PhpSlides/phpslides
 * @author Dave Conco <info@dconco.dev>
 * @license MIT
 */

namespace PhpSlides;

use PhpSlides\Exception;
use PhpSlides\Traits\FileHandler;
use PhpSlides\Controller\Controller;
use PhpSlides\Foundation\Application;
use PhpSlides\Interface\RouteInterface;

/**
 *   -------------------------------------------------------------------------------
 *
 *   CREATE A NEW ROUTE
 *
 *   Create route & api that accept different methods and render to the client area
 *
 *   @author Dave Conco <info@dconco.dev>
 *   @link https://github.com/PhpSlides/phpslides
 *   @category api, router, php router, php
 *   @copyright 2024 Dave Conco
 *   @package PhpSlides
 *   @version ^1.4.1
 *   @return self
 * |
 *
 *   -------------------------------------------------------------------------------
 */

class Route extends Controller implements RouteInterface
{
	use FileHandler;

	private ?array $guards = null;

	private mixed $action = null;

	private ?string $use = null;

	private ?string $file = null;

	private static array $routes;

	private static array $route;

	private static ?array $redirect = null;

	private static ?array $method = null;

	private static ?array $any = null;

	private static ?array $view = null;

	private static ?array $map = null;

	/**
	 * Get's all full request URL
	 *
	 * @static $request_uri
	 * @var string $request_uri
	 * @return string
	 */
	protected static string $request_uri;

	/**
	 *   ---------------------------------------------------------------------------------------------------------
	 *
	 *   This function handles getting files request and describe the type of request to handle according to `config.json` file in the root of the project,
	 *   for more security, it disallow users in navigating to wrong paths or files of the project.
	 *
	 *
	 *   This config method must be called before writing any other Route method or codes.
	 *
	 *   ---------------------------------------------------------------------------------------------------------
	 */
	public static function config(): void
	{
		$root_dir = Application::$basePath;
		self::$request_uri = Application::$request_uri;

		$req = preg_replace("/(^\/)|(\/$)/", '', self::$request_uri);
		$file_url = "{$root_dir}public/$req";

		$file = is_file($file_url) ? file_get_contents($file_url) : null;

		$file_type = $file !== null ? self::file_type($file_url) : null;

		$config_file = self::config_file();

		/**
		 *   ----------------------------------------------
		 *   Config File & Request Router configurations
		 *   ----------------------------------------------
		 */
		if ($file_type != null) {
			$config = $config_file['deny'] ?? [];
			$message = $config_file['message'] ?? [];
			$contents = $config_file['message']['contents'] ?? null;
			$http_code = $config_file['message']['http_code'] ?? 403;
			$components = $config_file['message']['components'] ?? null;
			$contentType = $config_file['message']['content-type'] ?? null;

			foreach ($config as $denyFile) {
				if (fnmatch(preg_replace("/(^\/)|(\/$)/", '', $denyFile), $req)) {
					/**
					 *  -----------------------------------------------
					 *    Deny Request to current file
					 *  -----------------------------------------------
					 */
					if (($t = gettype($http_code)) !== 'integer') {
						throw new Exception(
							"http_code in the PhpSlides configuration must be type int, $t given.",
						);
					}
					http_response_code($http_code);

					if ($contentType) {
						header("Content-Type: $contentType");
					}

					if ($components) {
						print_r(view::render($components));
					} elseif ($contents) {
						print_r($contents);
					}

					self::log();
					exit();
				}
			}

			/**
			 *  -----------------------------------------------
			 *    Proceed to accepts current file
			 *  -----------------------------------------------
			 */
			header("Content-Type: $file_type");

			print_r($file);
			self::log();
			exit();
		}
	}

	/**
	 *   ------------------------------------------------------------------------
	 *
	 *   ANY REQUEST FROM ROUTE
	 *
	 *   Accept all type of request or any other method
	 *   |
	 *
	 *   @param array|string $route This describes the URL string to check if it matches the request URL, use array of URLs for multiple request
	 *   @param mixed $callback Can contain any types of data to return to the client side/browser.
	 *
	 *   ------------------------------------------------------------------------
	 */
	public static function any(
		array|string $route,
		mixed $callback,
		string $method = '*',
	): self {
		self::$any = [
			'route' => $route,
			'method' => $method,
			'callback' => $callback,
		];

		self::$route[] = $route;
		return new self();
	}

	/**
	 * Route Mapping method
	 * Check out documentation for using Map method
	 *
	 * @link https://github.com/phpslides/phpslides
	 * @param string $method Request method
	 * @param string|array $route Route parameter
	 */
	public static function map(string $method, string|array $route): self
	{
		self::$map = [
			'method' => $method,
			'route' => $route,
		];
		self::$route[] = $route;
		return new self();
	}

	/**
	 * name METHOD
	 * Give a route a name for later use
	 *
	 * @param string $name Set the name of the route
	 */
	public function name(string $name): self
	{
		if (is_array(end(self::$route))) {
			for ($i = 0; $i < count(end(self::$route)); $i++) {
				add_route_name("$name::$i", end(self::$route)[$i]);
				self::$routes["$name::$i"] = end(self::$route)[$i];
			}
		} else {
			add_route_name($name, end(self::$route));
			self::$routes[$name] = end(self::$route);
		}

		return $this;
	}

	/**
	 * Action method
	 * In outputting information to the client area
	 *
	 * @param mixed $callback
	 */
	public function action(mixed $callback): self
	{
		if (self::$map) {
			$this->action = $callback;
		}
		return $this;
	}

	/**
	 * Controller method
	 * Work with map controller route
	 *
	 * @param string $controller
	 * @return void
	 */
	public function use(string $controller): self
	{
		if (self::$map) {
			$this->use = $controller;
		}
		return $this;
	}

	/**
	 * `file` method
	 * return view file directly
	 *
	 * @param string $file
	 */
	public function file(string $file): self
	{
		if (self::$map) {
			$this->file = $file;
		}
		return $this;
	}

	/**
	 * Applies Authentication Guard to the current route.
	 *
	 * @param string ...$guards String parameters of registered guards.
	 * @return self
	 */
	public function withGuard(string ...$guards): self
	{
		if (self::$map || self::$method || self::$view) {
			$this->guards = $guards;
		}
		return $this;
	}

	/**
	 *   ---------------------------------------------------------------------------
	 *
	 *   VIEW ROUTE METHOD
	 *
	 *   Route only needs to return a view; you may provide an array for multiple request
	 *
	 *   View Route does not accept `{?} URL parameters` in route, use GET method instead
	 *
	 *   @param array|string $route This describes the URL string to render, use array of strings for multiple request
	 *   @param string $view It renders this param, it can be functions to render, view:: to render or strings of text or documents
	 *   |
	 *
	 *   ---------------------------------------------------------------------------
	 */
	public static function view(array|string $route, string $view): self
	{
		self::$view = [
			'route' => $route,
			'view' => $view,
		];

		self::$route[] = $route;
		return new self();
	}

	/**
	 *   --------------------------------------------------------------
	 *
	 *   REDIRECT ROUTE METHOD
	 *
	 *   This method redirects the routes URL to the giving URL directly
	 *
	 *   @param string $route The requested url to redirect
	 *   @param string $new_url The new URL route to redirect to
	 *   @param int $code The code for redirect method, 301 for permanent redirecting & 302 for temporarily redirect.
	 *
	 * ---------------------------------------------------------------
	 */
	public static function redirect(
		string $route,
		string $new_url,
		int $code = 302,
	): self {
		self::$redirect = [
			'route' => $route,
			'new_url' => $new_url,
			'code' => $code,
		];

		self::$route[] = $route;
		return new self();
	}

	/**
	 *   --------------------------------------------------------------
	 *
	 *   GET ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function get(array|string $route, $callback): self
	{
		self::$method = [
			'route' => $route,
			'method' => 'GET',
			'callback' => $callback,
		];

		self::$route[] = $route;
		return new self();
	}

	/**
	 *   --------------------------------------------------------------
	 *
	 *   POST ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function post(array|string $route, $callback): self
	{
		self::$method = [
			'route' => $route,
			'method' => 'POST',
			'callback' => $callback,
		];

		self::$route[] = $route;
		return new self();
	}

	/**
	 *   --------------------------------------------------------------
	 *
	 *   PUT ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function put(array|string $route, $callback): self
	{
		self::$method = [
			'route' => $route,
			'method' => 'PUT',
			'callback' => $callback,
		];

		self::$route[] = $route;
		return new self();
	}

	/**
	 *   --------------------------------------------------------------
	 *
	 *   PATCH ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function patch(array|string $route, $callback): self
	{
		self::$method = [
			'route' => $route,
			'method' => 'PATCH',
			'callback' => $callback,
		];

		self::$route[] = $route;
		return new self();
	}

	/**
	 *   --------------------------------------------------------------
	 *
	 *   DELETE ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function delete(array|string $route, $callback): self
	{
		self::$method = [
			'route' => $route,
			'method' => 'DELETE',
			'callback' => $callback,
		];

		self::$route[] = $route;
		return new self();
	}

	public function __destruct()
	{
		$route_index = end(self::$route);
		$route_index = is_array($route_index) ? $route_index[0] : $route_index;

		if (self::$map !== null) {
			$GLOBALS['__registered_routes'][$route_index]['map'] = self::$map;
		}

		if ($this->guards !== null) {
			$GLOBALS['__registered_routes'][$route_index]['guards'] =
				$this->guards;
		}

		if (self::$redirect !== null) {
			$GLOBALS['__registered_routes'][$route_index]['redirect'] =
				self::$redirect;
		}

		if ($this->action !== null) {
			$GLOBALS['__registered_routes'][$route_index]['action'] =
				$this->action;
		}

		if (self::$any !== null) {
			$GLOBALS['__registered_routes'][$route_index]['any'] = self::$any;
		}

		if ($this->use !== null) {
			$GLOBALS['__registered_routes'][$route_index]['use'] = $this->use;
		}

		if ($this->file !== null) {
			$GLOBALS['__registered_routes'][$route_index]['file'] = $this->file;
		}

		if (self::$method !== null) {
			$GLOBALS['__registered_routes'][$route_index]['method'] =
				self::$method;
		}

		if (self::$view !== null) {
			$GLOBALS['__registered_routes'][$route_index]['view'] = self::$view;
		}
	}
}
