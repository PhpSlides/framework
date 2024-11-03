<?php

declare(strict_types=1);

namespace PhpSlides\Controller;

use PhpSlides\Exception;
use PhpSlides\Http\Request;
use PhpSlides\Logger\Logger;
use PhpSlides\Loader\ViewLoader;
use PhpSlides\Foundation\Application;

class RouteController
{
	use Logger;

	/**
	 *    ----------------------------------------------------------------------------------
	 *    |
	 *    `config_file` allows you to write configurations in `phpslides.config.json` file.
	 *
	 *    @return array|bool an `array` data retrieve from json data gotten from the config files
	 *    |
	 *    ----------------------------------------------------------------------------------
	 */
	protected static function config_file(): array|bool
	{
		$file_path = Application::$basePath . 'config.json';

		// checks if the config file exist in project root directory
		if (file_exists($file_path)) {
			// get json files and convert it to an array
			$config_file = file_get_contents($file_path);
			$config_file = json_decode($config_file, true);

			return $config_file;
		} else {
			throw new Exception(
				'URL request failed. Configuration file for PhpSlides is not found in the root of your project'
			);
		}
	}

	/**
	 *    -----------------------------------------------------------
	 *    |
	 *    @param string $filename The file which to gets the contents
	 *    @param mixed ...$props Properties in which would be available in the file
	 *    @return mixed The executed included file received
	 *    |
	 *    -----------------------------------------------------------
	 */
	public static function slides_include(
		string $filename,
		mixed ...$props
	): mixed {
		$loaded = (new ViewLoader())->load($filename, ...$props);
		return $loaded->getLoad();
	}

	/**
	 *    ==============================
	 *    |    Don't use this function!!!
	 *    |    --------------------
	 *    ==============================
	 */
	protected static function routing(
		array|string $route,
		mixed $callback,
		string $method = '*'
	) {
		$uri = [];
		$str_route = '';
		$reqUri = strtolower(
			preg_replace("/(^\/)|(\/$)/", '', Application::$request_uri)
		);
		$reqUri = empty($reqUri) ? '/' : $reqUri;

		if (is_array($route)) {
			for ($i = 0; $i < count($route); $i++) {
				$each_route = preg_replace("/(^\/)|(\/$)/", '', $route[$i]);

				if (empty($each_route)) {
					array_push($uri, '/');
				} else {
					array_push($uri, strtolower($each_route));
				}
			}
		} else {
			$str_route = strtolower(preg_replace("/(^\/)|(\/$)/", '', $route));
			$str_route = empty($str_route) ? '/' : $str_route;
		}

		if (in_array($reqUri, $uri) || $reqUri === $str_route) {
			if (
				strtoupper($_SERVER['REQUEST_METHOD']) !== strtoupper($method) &&
				$method !== '*'
			) {
				http_response_code(405);
				self::log();
				exit('Method Not Allowed');
			}

			header('Content-Type: */*');
			http_response_code(200);

			return $callback;
		} else {
			return false;
		}
	}

	/**
	 *    Don't use this function!!!
	 *
	 *    @param object|string $class In implementing class constructor from controller
	 *    @param string $method In accessing methods to render to routes
	 *    @return mixed From class methods and __invoke function
	 */
	protected static function controller(
		object|string $class,
		string $method,
		array|null $param = null
	) {
		return ClassController::__class($class, $method, $param);
	}

	/**
	 *    ==============================
	 *    |    Don't use this function!!!
	 *    |    --------------------
	 *    ==============================
	 */
	protected static function class_info(array $class_info, array|null $param)
	{
		$method = $class_info['method'];
		$class_name = $class_info['class_name'];
		$class_methods = $class_info['class_methods'];

		$class = new $class_name();

		for ($i = 0; $i < count($class_methods); $i++) {
			if (empty($method) || $method === '__invoke') {
				return $class(new Request($param));
			} elseif ($method === $class_methods[$i]) {
				return $class->$method(new Request($param));
			} elseif (
				count($class_methods) - 1 === $i &&
				$method !== $class_methods
			) {
				throw new Exception(
					"No controller method found as '$method'. Try using __invoke method.",
					1
				);
			}
		}
	}
}
