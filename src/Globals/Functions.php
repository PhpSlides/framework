<?php declare(strict_types=1);

use PhpSlides\Exception;
use PhpSlides\Router\Route;
use PhpSlides\Core\Loader\FileLoader;
use PhpSlides\Core\Loader\ViewLoader;
use PhpSlides\Core\Traits\FileHandler;
use PhpSlides\Core\Foundation\Application;

/**
 * Sets an environment variable '__DIR__' with the base path of the application.
 *
 * This function uses the `putenv` function to set the '__DIR__' environment variable
 * to the value of `Application::$basePath`.
 *
 * @param string $basePath The base path of the application.
 */
putenv(sprintf('__DIR__=%s', Application::$basePath));

/**
 * HTTP GET method constant.
 *
 * This constant represents the HTTP GET method, typically used to request data from a specified resource.
 *
 * @var string GET
 */
const GET = 'GET';

/**
 * HTTP PUT method constant.
 *
 * This constant represents the HTTP PUT method, typically used for updating
 * resources on a server.
 *
 * @var string PUT
 */
const PUT = 'PUT';

/**
 * HTTP POST method constant.
 *
 * This constant represents the HTTP POST method.
 *
 * @var string POST
 */
const POST = 'POST';

/**
 * HTTP PATCH method constant.
 *
 * This constant represents the HTTP PATCH method, which is used to apply partial modifications to a resource.
 *
 * @var string PATCH
 */
const PATCH = 'PATCH';

/**
 * HTTP DELETE method constant.
 *
 * This constant represents the HTTP DELETE method, typically used to delete a resource.
 *
 * @var string DELETE
 */
const DELETE = 'DELETE';

/**
 * Constant representing the remote path type.
 *
 * This constant is used to specify that a path is a remote path.
 *
 * @var int REMOTE_PATH
 */
const REMOTE_PATH = 2;

/**
 * Constant representing a relative path.
 *
 * This constant is used to indicate that a path is relative.
 *
 * @var int RELATIVE_PATH
 */
const RELATIVE_PATH = 0;

/**
 * Constant representing an absolute path.
 *
 * This constant is used to indicate that a path is absolute.
 *
 * @var int ABSOLUTE_PATH
 */
const ABSOLUTE_PATH = 1;

/**
 * Loads a component view file and returns its content.
 *
 * @param string $filename The name of the view file to load.
 * @param mixed ...$props Additional properties to pass to the view loader.
 * @return mixed The loaded view content.
 */
function component (string $filename, mixed ...$props): mixed
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
function add_route_name (string $name, string|array $value): void
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
function route (
 string|null $name = null,
 array|null $param = null,
): array|object|string {
	$routes = $GLOBALS['__routes'] ?? [];

	if ($name === null)
	{
		$route_class = new stdClass();

		foreach ($routes as $key => $value)
		{
			if (preg_match_all('/(?<={).+?(?=})/', $value))
			{
				$route_class->$key = function (string ...$args) use ($routes, $value, $key, )
				{
					$route = '';

					if (count($args) === 0)
					{
						$route = $routes[$key];
					}
					else
					{
						for ($i = 0; $i < count($args); $i++)
						{
							if ($i === 0)
							{
								$route = preg_replace(
								 '/\{[^}]+\}/',
								 $args[$i],
								 $value,
								 1,
								);
							}
							else
							{
								$route = preg_replace(
								 '/\{[^}]+\}/',
								 $args[$i],
								 $route,
								 1,
								);
							}
						}
					}
					return $route;
				};
			}
			else
			{
				$route_class->$key = $value;
			}
		}

		return $route_class;
	}
	else
	{
		if (!array_key_exists($name, $routes))
		{
			return '';
		}
		else
		{
			if ($param === null)
			{
				return $routes[$name];
			}
			else
			{
				$route = '';

				for ($i = 0; $i < count($param); $i++)
				{
					if ($i === 0)
					{
						$route = preg_replace(
						 '/\{[^}]+\}/',
						 $param[$i],
						 $routes[$name],
						 1,
						);
					}
					else
					{
						$route = preg_replace('/\{[^}]+\}/', $param[$i], $route, 1);
					}
				}
				return $route;
			}
		}
	}
}

/**
 * Generates a URL for an asset file based on the given filename and path type.
 *
 * @param string $filename The name of the asset file.
 * @param string $path_type The type of path to generate. Can be one of the following:
 *    - RELATIVE_PATH: Generates a relative path to the asset.
 *    - ABSOLUTE_PATH: Generates an absolute path to the asset.
 *    - REMOTE_PATH: Generates a remote path to the asset.
 *      Defaults to RELATIVE_PATH.
 *
 * @return string The generated URL for the asset file.
 */
function asset (string $filename, string $path_type = RELATIVE_PATH): string
{
	$filename = preg_replace('/(::)|::/', '/', $filename);
	$filename = strtolower(trim($filename, '\/\/'));

	switch (php_sapi_name())
	{
		case 'cli-server':
			$root_path = '/';
			break;
		default:
			$find = '/src/routes/render.php';
			$self = $_SERVER['PHP_SELF'];

			$root_path = substr_replace(
			 $self,
			 '/',
			 strrpos($self, $find),
			 strlen($find),
			);
			break;
	}

	$path = './';
	if (!empty(Application::$request_uri))
	{
		$root_pathExp = explode('/', trim($root_path, '/'));
		$reqUri = explode('/', trim(Application::$request_uri, '/'));

		for ($i = 0; $i < count($reqUri) - count($root_pathExp); $i++)
		{
			$path .= '../';
		}
	}

	return match ($path_type)
	{
			RELATIVE_PATH => "$path$filename",
			ABSOLUTE_PATH => "$root_path$filename",
			REMOTE_PATH => Application::$REMOTE_ADDR . '/' . $filename,
			default => $filename,
	};
}

/**
 * Imports a file and returns its contents encoded in base64 format.
 *
 * @param string $file The path to the file to be imported.
 *
 * @return string The base64 encoded contents of the file, prefixed with the file's MIME type.
 *
 * @throws Exception If the file does not exist.
 */
function import (string $file)
{
	if (!is_file($file))
	{
		throw new Exception("File does not exist: $file");
	}

	$file_type = FileHandler::file_type($file);
	$contents = base64_encode(file_get_contents($file));

	$data = "data:$file_type;base64,$contents";
	return $data;
}

/**
 * Generates a JWT payload array with the given data and timestamps.
 *
 * @param array $data The data to include in the payload.
 * @param string $expires The expiration time of the token in a format accepted by DateTimeImmutable.
 * @param string $issued_at The issued at time of the token in a format accepted by DateTimeImmutable. Defaults to 'now'.
 * @param string $issuer The issuer of the token. If not provided, it will be loaded from the JWT configuration.
 *
 * @return array{
 * 	iss: string,
 * 	iat: int,
 * 	exp: int
 * }
 */
function payload (
 array $data,
 string $expires,
 string $issued_at = 'now',
 string $issuer = '',
): array {
	$jwt = (new FileLoader())
	 ->load(__DIR__ . '/../Config/jwt.config.php')
	 ->getLoad();

	if ($issuer === '')
	{
		$issuer = $jwt['issuer'];
	}

	$expires = (new DateTimeImmutable($expires))->getTimestamp();
	$issued_at = (new DateTimeImmutable($issued_at))->getTimestamp();

	return array_merge(
	 [
	  'iss' => $issuer,
	  'iat' => $issued_at,
	  'exp' => $expires,
	 ],
	 $data,
	);
}

/**
 * Handles properties in view files.
 *
 * Retrieves a specific property or all properties from the `\PhpSlides\Props` class.
 * - If a name is provided, it returns the corresponding property.
 * - If no name is given, it returns all properties, with special handling for properties starting with an underscore.
 *
 * @param ?string $name The property name to retrieve. If null, all properties are returned.
 *
 * @return array|mixed The value of the specified property, or all properties if no name is given.
 */
function Props (?string $name = null)
{
	if ($name === null)
	{
		$allProperties = \PhpSlides\Core\Props::all();
		$filteredProperties = [];

		foreach ($allProperties as $key => $value)
		{
			if (str_starts_with($key, '_'))
			{
				$numericKey = substr($key, 1);
				if (is_numeric($numericKey))
				{
					$filteredProperties[$numericKey] = $value;
				}
			}
			else
			{
				$filteredProperties[$key] = $value;
			}
		}

		return $filteredProperties;
	}

	if (is_numeric($name))
	{
		$name = "_$name";
	}

	return (new \PhpSlides\Core\Props())->$name;
}

function ExceptionHandler (Throwable $exception)
{
	// Check if the exception is a CustomException to use its specific methods
	if ($exception instanceof Exception)
	{
		$message = $exception->getMessage();
		$file = $exception->getFilteredFile();
		$line = $exception->getFilteredLine();
		$trace = $exception->filterStackTrace();
		$codeSnippet = $exception->getCodeSnippet();
		$detailedMessage = $exception->getDetailedMessage();
	}
	else
	{
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
		 $line,
		);
	}

	// Log the detailed error message
	error_log($detailedMessage);

	if (Exception::$IS_API === true)
	{
		echo json_encode([
		 'exception' => $message,
		 'file' => $file,
		 'line' => $line,
		]);
		exit();
	}
	include_once __DIR__ . '/../Exception/template/index.php';
}

set_exception_handler('ExceptionHandler');
