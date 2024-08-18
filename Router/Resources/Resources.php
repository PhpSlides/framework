<?php declare(strict_types=1);

namespace PhpSlides\Resources;

use PhpSlides\view;
use PhpSlides\Route;
use PhpSlides\Exception;
use PhpSlides\Http\Request;
use PhpSlides\Loader\FileLoader;
use PhpSlides\Controller\Controller;
use PhpSlides\Foundation\Application;
use PhpSlides\Interface\MiddlewareInterface;

class Resources extends Controller
{
	protected static mixed $action = null;

	protected static ?array $middleware = null;

	protected static ?array $redirect = null;

	protected static ?array $method = null;

	protected static ?array $view = null;

	protected static ?array $any = null;

	protected static ?string $use = null;

	protected static ?string $file = null;

	protected static array|bool $map_info = false;

	/**
	 * Get's all full request URL
	 *
	 * @static $request_uri
	 * @var string $request_uri
	 * @return string
	 */
	protected static string $request_uri;

	protected static function __any(): void
	{
		$route = self::$any['route'];
		$method = self::$any['method'];
		$callback = self::$any['callback'];

		/**
		 *   --------------------------------------------------------------
		 *
		 *   Not Found Error
		 *
		 *   This * route serves as 404, which executes whenever there're no matching routes from the request url
		 *   which takes a callback parameter that is rendered to the webpage
		 *
		 * --------------------------------------------------------------
		 */

		if ((is_array($route) && in_array('*', $route)) || $route === '*') {
			header('HTTP/1.0 404 Not Found');
			header('Content-Type: text/html');

			print_r(is_callable($callback) ? $callback() : $callback);
			self::log();
			exit();
		}

		// will store all the parameters value in this array
		$req = [];
		$req_value = [];

		// will store all the parameters names in this array
		$paramKey = [];

		// finding if there is any {?} parameter in $route
		if (is_string($route)) {
			preg_match_all('/(?<={).+?(?=})/', $route, $paramMatches);
		}

		// if the route does not contain any param call routing();
		if (empty($paramMatches[0]) || is_array($route)) {
			/**
			 *   ------------------------------------------------------
			 *   Check if $callback is a callable function
			 *   or array of controller, and if not,
			 *   it's a string of text or html document
			 *   ------------------------------------------------------
			 */
			$callback = self::routing($route, $callback, $method);

			if ($callback) {
				if (
					is_array($callback) &&
					(preg_match('/(Controller)/', $callback[0], $matches) &&
						count($matches) > 1)
				) {
					print_r(
						self::controller(
							$callback[0],
							count($callback) > 1 ? $callback[1] : ''
						)
					);
				} else {
					print_r(is_callable($callback) ? $callback() : $callback);
				}

				self::log();
				exit();
			} else {
				return;
			}
		}

		// setting parameters names
		foreach ($paramMatches[0] as $key) {
			$paramKey[] = $key;
		}

		/**
		 *   ----------------------------------------------
		 *   Replacing first and last forward slashes
		 *   $_SERVER['REQUEST_URI'] will be empty if req uri is /
		 *   ----------------------------------------------
		 */

		if (!empty(self::$request_uri)) {
			$route = strtolower(preg_replace("/(^\/)|(\/$)/", '', $route));
			$reqUri = strtolower(
				preg_replace("/(^\/)|(\/$)/", '', self::$request_uri)
			);
		} else {
			$reqUri = '/';
		}

		// exploding route address
		$uri = explode('/', $route);

		// will store index number where {?} parameter is required in the $route
		$indexNum = [];

		// storing index number, where {?} parameter is required with the help of regex
		foreach ($uri as $index => $param) {
			if (preg_match('/{.*}/', $param)) {
				$indexNum[] = $index;
			}
		}

		/**
		 *   ----------------------------------------------------------------------------------
		 *   Exploding request uri string to array to get the exact index number value of parameter from $_SERVER['REQUEST_URI']
		 *   ----------------------------------------------------------------------------------
		 */
		$reqUri = explode('/', $reqUri);

		/**
		 *   ----------------------------------------------------------------------------------
		 *   Running for each loop to set the exact index number with reg expression this will help in matching route
		 *   ----------------------------------------------------------------------------------
		 */
		foreach ($indexNum as $key => $index) {
			/**
			 *   --------------------------------------------------------------------------------
			 *   In case if req uri with param index is empty then return because URL is not valid for this route
			 *   --------------------------------------------------------------------------------
			 */

			if (empty($reqUri[$index])) {
				return;
			}

			// setting params with params names
			$req[$paramKey[$key]] = htmlspecialchars($reqUri[$index]);
			$req_value[] = htmlspecialchars($reqUri[$index]);

			// this is to create a regex for comparing route address
			$reqUri[$index] = '{.*}';
		}

		// converting array to string
		$reqUri = implode('/', $reqUri);

		/**
		 *   -----------------------------------
		 *   replace all / with \/ for reg expression
		 *   regex to match route is ready!
		 *   -----------------------------------
		 */
		$reqUri = str_replace('/', '\\/', $reqUri);

		// now matching route with regex
		if (preg_match("/$reqUri/", $route)) {
			// checks if the requested method is of the given route
			if (
				strtoupper($_SERVER['REQUEST_METHOD']) !== strtoupper($method) &&
				$method !== '*'
			) {
				http_response_code(405);
				self::log();
				exit('Method Not Allowed');
			}

			http_response_code(200);
			header('Content-Type: text/html');

			if (
				is_array($callback) &&
				(preg_match('/(Controller)/', $callback[0], $matches) &&
					count($matches) > 1)
			) {
				print_r(
					self::controller(
						$callback[0],
						count($callback) > 1 ? $callback[1] : '',
						$req_value
					)
				);
			} else {
				print_r(
					is_callable($callback) ? $callback(...$req_value) : $callback
				);
			}

			self::log();
			exit();
		}
	}

	protected static function __redirect(): void
	{
		$route = self::$redirect['route'];
		$new_url = self::$redirect['method'];
		$reqUri = self::$redirect['callback'];

		if (!empty(self::$request_uri)) {
			$route = preg_replace("/(^\/)|(\/$)/", '', $route);
			$new_url = preg_replace("/(^\/)|(\/$)/", '', $new_url);
			$reqUri = preg_replace("/(^\/)|(\/$)/", '', self::$request_uri);
		} else {
			$reqUri = '/';
			$new_url = preg_replace("/(^\/)|(\/$)/", '', $new_url);
		}

		if (strtolower($reqUri) === strtolower($route)) {
			http_response_code($code);
			self::log();
			header("Location: $new_url", true, $code);
			exit();
		}
	}

	protected static function __method(): void
	{
		$route = self::$method['route'];
		$method = self::$method['method'];
		$callback = self::$method['callback'];

		Route::any($route, $callback, $method);
	}

	protected static function __view(): void
	{
		$route = self::$view['route'];
		$view = self::$view['view'];

		/**
		 *   ----------------------------------------
		 *   |   Replacing first and last forward slashes
		 *   |   $_REQUEST['uri'] will be empty if req uri is /
		 *   ----------------------------------------
		 */
		$uri = [];
		$str_route = '';
		$reqUri = strtolower(
			preg_replace("/(^\/)|(\/$)/", '', self::$request_uri)
		);

		if (is_array($route)) {
			for ($i = 0; $i < count($route); $i++) {
				$each_route = preg_replace("/(^\/)|(\/$)/", '', $route[$i]);
				array_push($uri, strtolower($each_route));
			}
		} else {
			$str_route = strtolower(preg_replace("/(^\/)|(\/$)/", '', $route));
		}

		if (in_array($reqUri, $uri) || $reqUri === $str_route) {
			if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET') {
				http_response_code(405);
				self::log();
				exit('Method Not Allowed');
			}

			// render view page to browser
			print_r(view::render($view));
			self::log();
			exit();
		}
	}

	protected function __middleware(): void
	{
		$use = self::$use;
		$file = self::$use;
		$action = self::$action;

		$view = self::$view;
		$method = self::$method;
		$middleware = self::$middleware ?? [];

		$params = self::$map_info['params'] ?? null;
		$request = new Request($params);

		for ($i = 0; $i < count((array) $middleware); $i++) {
			$middlewares = (new FileLoader())
				->load(__DIR__ . '/../../src/Config/middleware.php')
				->getLoad();

			if (array_key_exists($middleware[$i], $middlewares)) {
				$middleware = $middlewares[$middleware[$i]];
			} else {
				self::log();
				throw new Exception(
					'No Registered Middleware as `' . $middleware[$i] . '`'
				);
			}

			if (!class_exists($middleware)) {
				self::log();
				throw new Exception(
					"Middleware class does not exist: `{$middleware}`"
				);
			}
			$mw = new $middleware();

			if ($mw instanceof MiddlewareInterface) {
				$next = function () use ($use, $file, $action, $view, $method) {
					if ($use !== null) {
						self::__use();
					} elseif ($file !== null) {
						self::__file();
					} elseif ($action !== null) {
						self::__action();
					} elseif ($view !== null) {
						self::__view();
					} elseif ($method !== null) {
						self::__method();
					} else {
						self::log();
						throw new Exception('Cannot use middleware with this method');
					}
				};

				$mw->handle($request, $next);
			} else {
				self::log();
				throw new Exception(
					'Middleware class must implements `MiddlewareInterface`'
				);
			}
		}
	}

	protected function __file(): void
	{
		$file = self::$file;

		if (array_key_exists('params', self::$map_info)) {
			$GLOBALS['params'] = self::$map_info['params'];
		}

		print_r(view::render($file));
		self::log();
		exit();
	}

	protected function __use(): void
	{
		$controller = self::$use;

		if (!preg_match('/(?=.*Controller)(?=.*::)/', $controller)) {
			self::log();
			throw new Exception(
				'Parameter $controller must match Controller named rule.'
			);
		}

		[$c_name, $c_method] = explode('::', $controller);

		$cc = 'App\\Controllers\\' . $c_name;

		if (class_exists($cc)) {
			$params = self::$map_info['params'];

			$cc = new $cc();
			print_r($cc->$c_method(new Request($params)));
		} else {
			self::log();
			throw new Exception("No class controller found as: '$cc'");
		}

		self::log();
		exit();
	}

	protected function __action(): void
	{
		$action = self::$action;
		$params = self::$map_info['params'];

		if (is_callable($action)) {
			$a = $action(new Request($GLOBALS['params']));
			print_r($a);
		} elseif (preg_match('/(?=.*Controller)(?=.*::)/', $action)) {
			self::$use = $action;
			$this->__use();
		} else {
			$GLOBALS['params'] = $params;
			print_r($action);
		}

		self::log();
		exit();
	}
}
