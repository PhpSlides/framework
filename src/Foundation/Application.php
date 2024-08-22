<?php declare(strict_types=1);

namespace PhpSlides\Foundation;

use PhpSlides\Route;
use PhpSlides\Loader\FileLoader;
use PhpSlides\Interface\ApplicationInterface;

/**
 * The Application class is the foundation of the PhpSlides project
 * and provides methods to configure and initialize the PhpSlides application.
 */
class Application implements ApplicationInterface
{
	/**
	 * The version of the PhpSlides application.
	 */
	const PHPSLIDES_VERSION = '1.2.8';

	/**
	 *  `$log` method prints logs in `.log` file in the root of the project each time any request has been received, when setted to true.
	 *   It's been setted to true by default, can be changed anytime.
	 *
	 *   @static $log
	 *   @var bool $log
	 *   @return bool
	 */
	public static bool $log;

	/**
	 * @var string $basePath
	 * The base path of the application.
	 */
	public static string $basePath;

	/**
	 * @var string $configsDir
	 * The directory path for configuration files.
	 */
	public static string $configsDir;

	/**
	 * @var string $viewsDir
	 * The directory path for view templates.
	 */
	public static string $viewsDir;

	/**
	 * @var string $stylesDir
	 * The directory path for style resources (e.g., CSS files).
	 */
	public static string $stylesDir;

	/**
	 * @var string $scriptsDir
	 * The directory path for script resources (e.g., JavaScript files).
	 */
	public static string $scriptsDir;

	/**
	 * @var string $request_uri
	 * The request Uri
	 */
	public static string $request_uri;

	/**
	 * @var string $registerRoutePath
	 * The file path for registering all routes
	 */
	public static string $renderRoutePath;

	/**
	 * Configure the application with the base path.
	 *
	 * @param string $basePath The base path of the application.
	 * @return self Returns an instance of the Application class.
	 */
	public static function configure(string $basePath): self
	{
		self::$basePath = rtrim($basePath, '/') . '/';
		self::routing();

		if (php_sapi_name() == 'cli-server') {
			self::$request_uri = urldecode(
				parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
			);
		} else {
			self::$request_uri = urldecode(
				$_REQUEST['uri'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
			);
		}

		return new self();
	}

	/**
	 * Set up routing paths for the application.
	 *
	 * @return void
	 */
	private static function routing(): void
	{
		self::$configsDir = self::$basePath . 'src/configs/';
		self::$viewsDir = self::$basePath . 'src/resources/views/';
		self::$scriptsDir = self::$basePath . 'src/resources/src/';
		self::$stylesDir = self::$basePath . 'src/resources/styles/';
		self::$renderRoutePath = self::$basePath . 'src/routes/render.php';
	}

	/**
	 * Create the application by loading configuration files and routes.
	 *
	 * @return void
	 */
	public function create(): void
	{
		session_start();
		$loader = new FileLoader();
		$loader->load(__DIR__ . '/../Config/env.config.php');
		$loader->load(__DIR__ . '/../Config/config.php');

		self::$log = getenv('APP_DEBUG') == 'true' ? true : false;
		Route::config();

		$loader
			->load(__DIR__ . '/../Globals/Functions.php')
			->load(self::$renderRoutePath);
	}
}
