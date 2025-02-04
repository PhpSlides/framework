<?php declare(strict_types=1);

namespace PhpSlides\Core\Traits\Resources;

use Closure;
use PhpSlides\Core\view;
use PhpSlides\Exception;
use PhpSlides\Router\MapRoute;
use PhpSlides\Core\Http\Request;
use PhpSlides\Core\Loader\FileLoader;
use PhpSlides\Core\Foundation\Application;

trait RouteResources
{
	protected static mixed $action = null;

	protected static ?array $guards = null;

	protected static ?array $redirect = null;

	protected static ?array $method = null;

	protected static ?array $view = null;

	protected static ?array $any = null;

	protected static ?array $map = null;

	protected static ?array $mapRoute = null;

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

	protected static function __map(): void
	{
		$route = self::$map['route'] ?? '';
		$method = self::$map['method'] ?? '';
		$static = new static();

		$match = new MapRoute();
		self::$map_info = $match->match($method, $route);

		if (self::$map_info) {
			$static->__guards(self::$guards ?? null);

			if (self::$use !== null) {
				$static->__use();
			}

			if (self::$file !== null) {
				$static->__file();
			}

			if (self::$action !== null) {
				$static->__action();
			}
		}
	}

	protected static function __mapRoute()
	{
		$route = self::$mapRoute['route'] ?? '';
		$method = self::$mapRoute['method'] ?? '';
		$callback = self::$mapRoute['callback'] ?? function ($req, $accept) {};

		$match = new MapRoute();
		self::$map_info = $match->match($method, $route);

		if (self::$map_info) {
			extract(self::$map_info);
			(new static())->__guards(self::$guards ?? null);

			print_r(
				call_user_func($callback, new Request($params), function (
					$m,
					$func = null,
				) use ($method) {
					if (strpos($m, '|')) {
						$array_m = explode('|', $m);
						$array_m = array_map(fn($m) => trim($m), $array_m);
					}

					if ($m !== $method && !in_array($method, $array_m ?? [])) {
						http_response_code(405);

						if ($func) {
							print_r($func($method));
							exit();
						}
						print_r('Method Not Allowed');
						exit();
					}
				}),
			);
		}
	}

	protected static function __any(?Request $request = null): void
	{
		$route = self::$any['route'] ?? self::$method['route'];
		$method = self::$any['method'] ?? self::$method['method'];
		$callback = self::$any['callback'] ?? self::$method['callback'];

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
			http_response_code(404);

			$GLOBALS['request'] = $request;
			print_r(
				is_callable($callback)
					? $callback($request ?? new Request())
					: $callback,
			);

			self::log();
			exit();
		}

		// will store all the parameters value in this array
		$req = [];

		// will store all the parameters names in this array
		$paramKey = [];

		// finding if there is any {?} parameter in $route
		if (is_string($route)) {
			preg_match_all('/(?<={).+?(?=})/', $route, $paramMatches);
		}

		// if the route does not contain any param call routing();
		if (empty($paramMatches[0] ?? []) || is_array($route)) {
			/**
			 *   ------------------------------------------------------
			 *   Check if $callback is a callable function
			 *   or array of controller, and if not,
			 *   it's a string of text or html document
			 *   ------------------------------------------------------
			 */
			$callback = self::routing($route, $callback, $method);

			if ($callback) {
				if (self::$guards !== null) {
					(new static())->__guards(self::$guards ?? null);
				}

				if (
					is_array($callback) &&
					(preg_match('/(Controller)/', $callback[0], $matches) &&
						count($matches) > 1)
				) {
					print_r(
						self::controller(
							$callback[0],
							count($callback) > 1 ? $callback[1] : '',
						),
					);
				} else {
					$GLOBALS['request'] = new Request();
					print_r(
						is_callable($callback)
							? $callback($request ?? new Request())
							: $callback,
					);
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
				preg_replace("/(^\/)|(\/$)/", '', self::$request_uri),
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
			$req[$paramKey[$key]] = htmlspecialchars(
				$reqUri[$index],
				ENT_NOQUOTES,
			);
			$req_value[] = htmlspecialchars($reqUri[$index], ENT_NOQUOTES);

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
		if (preg_match("/$reqUri/", $route . '$')) {
			// checks if the requested method is of the given route
			if (
				strtoupper($_SERVER['REQUEST_METHOD']) !== strtoupper($method) &&
				$method !== '*'
			) {
				http_response_code(405);
				self::log();
				exit('Method Not Allowed');
			}

			if (self::$guards !== null) {
				(new static())->__guards(self::$guards ?? null);
			}

			if (
				is_array($callback) &&
				(preg_match('/(Controller)/', $callback[0], $matches) &&
					count($matches) > 1)
			) {
				print_r(
					self::controller(
						$callback[0],
						count($callback) > 1 ? $callback[1] : '',
						$req,
					),
				);
			} else {
				$GLOBALS['request'] = new Request($req);
				print_r(
					is_callable($callback)
						? $callback(new Request($req))
						: $callback,
				);
			}

			self::log();
			exit();
		}
	}

	protected static function __redirect(): void
	{
		$route = self::$redirect['route'];
		$new_url = self::$redirect['new_url'];
		$code = self::$redirect['code'];

		if (!empty(self::$request_uri)) {
			$route = preg_replace("/(^\/)|(\/$)/", '', $route);
			$new_url = preg_replace("/(^\/)|(\/$)/", '', $new_url);
			$reqUri = preg_replace("/(^\/)|(\/$)/", '', self::$request_uri);
		} else {
			$reqUri = '/';
			$new_url = preg_replace("/(^\/)|(\/$)/", '', $new_url);
		}

		if ($reqUri === $route) {
			http_response_code($code);
			self::log();

			header("Location: $new_url", true, $code);
			exit();
		}
	}

	protected static function __method(?Request $request = null): void
	{
		self::__any($request);
	}

	protected static function __view(?Request $request = null): void
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
			preg_replace("/(^\/)|(\/$)/", '', self::$request_uri),
		);
		$reqUri = empty($reqUri) ? '/' : $reqUri;

		if (is_array($route)) {
			for ($i = 0; $i < count($route); $i++) {
				$each_route = preg_replace("/(^\/)|(\/$)/", '', $route[$i]);

				empty($each_route)
					? array_push($uri, '/')
					: array_push($uri, strtolower($each_route));
			}
		} else {
			$str_route = strtolower(preg_replace("/(^\/)|(\/$)/", '', $route));
			$str_route = empty($str_route) ? '/' : $str_route;
		}

		if (in_array($reqUri, $uri) || $reqUri === $str_route) {
			if (
				strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET' &&
				strtoupper($_SERVER['REQUEST_METHOD']) !== 'VIEW'
			) {
				http_response_code(405);
				self::log();
				exit('Method Not Allowed');
			}

			(new static())->__guards(self::$guards ?? null);

			// render view page to browser
			$GLOBALS['request'] = $request;
			print_r(view::render($view));
			self::log();
			exit();
		}
	}

	protected function __guards(?array $guards): bool
	{
		if (!$guards) {
			return true;
		}
		$params = self::$map_info['params'] ?? null;
		$request = new Request($params);

		for ($i = 0; $i < count((array) $guards); $i++) {
			$registered_guards = (new FileLoader())
				->load(__DIR__ . '/../../Config/guards.php')
				->getLoad();

			if (array_key_exists($guards[$i], $registered_guards)) {
				$guard = $registered_guards[$guards[$i]];
			} else {
				throw new Exception(
					'No Registered AuthGuard as `' . $guards[$i] . '`',
				);
			}

			if (!class_exists($guard)) {
				throw new Exception("AuthGuard class does not exist: `{$guard}`");
			}
			$cl = new $guard($request);

			if ($cl->authorize() !== true) {
				self::log();
				exit();
			}
		}
		return true;
	}

	protected function __file(?Request $request = null): void
	{
		$file = self::$file;

		if (array_key_exists('params', self::$map_info)) {
			$GLOBALS['request'] = new Request(self::$map_info['params']);
		}
		if ($request) {
			$GLOBALS['request'] = $request;
		}

		print_r(view::render($file));
		self::log();
		exit();
	}

	protected function __use(?Request $request = null): void
	{
		$controller = self::$use;

		if (!preg_match('/(?=.*Controller)(?=.*::)/', $controller)) {
			throw new Exception(
				'Parameter $controller must match Controller named rule.',
			);
		}

		[$c_name, $c_method] = explode('::', $controller);

		$cc = 'App\\Http\\Controller\\' . $c_name;

		if (class_exists($cc)) {
			$params = self::$map_info['params'] ?? null;

			$cc = new $cc();
			print_r($cc->$c_method($request ?? new Request($params)));
		} else {
			throw new Exception("No class controller found as: '$cc'");
		}

		self::log();
		exit();
	}

	protected function __action(?Request $request = null): void
	{
		$action = self::$action;
		$params = self::$map_info['params'] ?? null;

		if (is_callable($action)) {
			$a = $action($request ?? new Request($params));
			print_r($a);
		} elseif (preg_match('/(?=.*Controller)(?=.*::)/', $action)) {
			self::$use = $action;
			$this->__use($request);
		} else {
			$GLOBALS['request'] = $request ?? new Request($params);
			print_r($action);
		}

		self::log();
		exit();
	}
}
