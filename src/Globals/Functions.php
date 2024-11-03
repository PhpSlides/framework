<?php

use PhpSlides\Route;
use PhpSlides\Exception;
use PhpSlides\Loader\ViewLoader;
use PhpSlides\Loader\FileLoader;
use PhpSlides\Foundation\Application;

define('__ROOT__', Application::$basePath);

const GET = 'GET';
const PUT = 'PUT';
const POST = 'POST';
const PATCH = 'PATCH';
const DELETE = 'DELETE';
const RELATIVE_PATH = 'path';
const ABSOLUTE_PATH = 'root_path';

/**
 *    -----------------------------------------------------------
 *   |
 *   @param string $filename The file which to gets the contents
 *   @param mixed ...$props Properties in which would be available in the file
 *   @return mixed The executed included file received
 *   |
 *    -----------------------------------------------------------
 */
function slides_include(string $filename, mixed ...$props): mixed
{
	$loaded = (new ViewLoader())->load($filename, ...$props);
	return $loaded->getLoad();
}

/**
 * @var array $GLOBALS['__routes']
 * All routes names are stored in this variable
 */
$GLOBALS['__routes'] = [];

/**
 * Give route a name and value
 *
 * @param string $name Name of the given route to be specified
 * @param string|array $value Named route value
 * @return void
 */
function add_route_name(string $name, string|array $value): void
{
	$GLOBALS['__routes'][$name] = $value;
}

/**
 * Get Route results from named route
 *
 * @param string|null $name The name of the route to return
 * @param array|null $param If the route has parameter, give the parameter a value
 *
 * @return array|object|string returns the route value
 */
function route(
	string|null $name = null,
	array|null $param = null
): array|object|string {
	$routes = $GLOBALS['__routes'] ?? [];

	if ($name === null) {
		$route_class = new stdClass();

		foreach ($routes as $key => $value) {
			if (preg_match_all('/(?<={).+?(?=})/', $value)) {
				$route_class->$key = function (string ...$args) use (
					$routes,
					$value,
					$key
				) {
					$route = '';

					if (count($args) === 0) {
						$route = $routes[$key];
					} else {
						for ($i = 0; $i < count($args); $i++) {
							if ($i === 0) {
								$route = preg_replace(
									'/\{[^}]+\}/',
									$args[$i],
									$value,
									1
								);
							} else {
								$route = preg_replace(
									'/\{[^}]+\}/',
									$args[$i],
									$route,
									1
								);
							}
						}
					}
					return $route;
				};
			} else {
				$route_class->$key = $value;
			}
		}

		return $route_class;
	} else {
		if (!array_key_exists($name, $routes)) {
			return '';
		} else {
			if ($param === null) {
				return $routes[$name];
			} else {
				$route = '';

				for ($i = 0; $i < count($param); $i++) {
					if ($i === 0) {
						$route = preg_replace(
							'/\{[^}]+\}/',
							$param[$i],
							$routes[$name],
							1
						);
					} else {
						$route = preg_replace('/\{[^}]+\}/', $param[$i], $route, 1);
					}
				}
				return $route;
			}
		}
	}
}

/**
 * Getting public files
 *
 * @param string $filename The name of the file to get from public directory
 * @param string $path_type Path to start location which uses either `RELATIVE_PATH`
 * for path `../` OR `ABSOLUTE_PATH` starting with the root directory `/`
 * @return string The file path ABSOLUTE_PATH|RELATIVE_PATH
 */
function asset(string $filename, string $path_type = RELATIVE_PATH): string
{
	$filename = preg_replace('/(::)|::/', '/', $filename);
	$filename = strtolower(trim($filename, '\/\/'));

	if (php_sapi_name() == 'cli-server') {
		$root_path = '/';
	} else {
		$find = '/src/routes/render.php';
		$self = $_SERVER['PHP_SELF'];

		$root_path = substr_replace(
			$self,
			'/',
			strrpos($self, $find),
			strlen($find)
		);
	}

	$path = './';
	if (!empty(Application::$request_uri)) {
		$root_pathExp = explode('/', trim($root_path, '/'));
		$reqUri = explode('/', trim(Application::$request_uri, '/'));

		for ($i = 0; $i < count($reqUri) - count($root_pathExp); $i++) {
			$path .= '../';
		}
	}

	switch ($path_type) {
		case RELATIVE_PATH:
			return $path . $filename;
		case ABSOLUTE_PATH:
			return $root_path . $filename;
		default:
			return $filename;
	}
}

/**
 * IMPORTING FILES AS PART OF THE CODE
 *
 * @param $file The file location to import
 */
function import(string $file)
{
	if (!is_file($file)) {
		throw new Exception('File does not exist: ' . $file);
	}

	$file_type = Route::file_type($file);
	$contents = base64_encode(file_get_contents($file));

	$data = "data:$file_type;base64,$contents";
	return $data;
}

/**
 * Generate a PayLoad array for JWT authentication.
 * @return array The payload for JWT
 */
function payload(
	array $data,
	int $expires,
	int $issued_at = 0,
	string $issuer = ''
): array {
	$jwt = (new FileLoader())
		->load(__DIR__ . '/../Config/jwt.config.php')
		->getLoad();

	if ($issuer === '') {
		$issuer = $jwt['issuer'];
	}
	if ($issued_at === 0) {
		$issued_at = time();
	}

	return array_merge(
		[
			'iss' => $issuer,
			'iat' => $issued_at,
			'exp' => $expires
		],
		$data
	);
}

function ExceptionHandler(Throwable $exception)
{
	// Check if the exception is a CustomException to use its specific methods
	if ($exception instanceof Exception) {
		$message = $exception->getMessage();
		$file = $exception->getFilteredFile();
		$line = $exception->getFilteredLine();
		$trace = $exception->filterStackTrace();
		$codeSnippet = $exception->getCodeSnippet();
		$detailedMessage = $exception->getDetailedMessage();
	} else {
		// For base Exception, use default methods
		$message = $exception->getMessage();

		// Get code snippet manually
		(new FileLoader())->load(__DIR__ . '/Chunks/codeSnippets.php');
		(new FileLoader())->load(__DIR__ . '/Chunks/trace.php');

		$trace = filterTrace($exception->getTrace());
		$file = $trace[0]['file'] ?? '';
		$line = $trace[0]['line'] ?? 0;

		$codeSnippet = getCodeSnippet($file, $line, 10, 10);
		$detailedMessage = sprintf(
			'Error: %s in %s on line %d',
			$exception->getMessage(),
			$file,
			$line
		);
	}

	// Log the detailed error message
	error_log($detailedMessage);

	include_once __DIR__ . '/../Exception/template/index.php';
}

set_exception_handler('ExceptionHandler');
