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
	const PHPSLIDES_VERSION = '1.2.3';

	/**
	 * @var string $basePath
	 * The base path of the application.
	 */
	public static string $basePath;

	/**
	 * @var string $apiPath
	 * The path for API routes.
	 */
	public static string $apiPath;

	/**
	 * @var string $webPath
	 * The path for web routes.
	 */
	public static string $webPath;

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
	 * Configure the application with the base path.
	 *
	 * @param string $basePath The base path of the application.
	 * @return self Returns an instance of the Application class.
	 */
	public static function configure (string $basePath): self
	{
		self::$basePath = rtrim($basePath, '/') . '/';

		if (php_sapi_name() == 'cli-server')
		{
			self::$request_uri = urldecode($_SERVER['REQUEST_URI']);
		}
		else
		{
			self::$request_uri = urldecode(
			 $_REQUEST['uri'] ?? $_SERVER['REQUEST_URI']
			);
		}

		return new self();
	}

	/**
	 * Set up routing paths for the application.
	 *
	 * @param string $api The path for API routes.
	 * @param string $web The path for web routes.
	 * @return self Returns the current instance of the Application class.
	 */
	public function routing (string $api, string $web): self
	{
		self::$apiPath = $api;
		self::$webPath = $web;

		self::$configsDir = self::$basePath . 'src/configs/';
		self::$stylesDir = self::$basePath . 'src/resources/styles/';
		self::$scriptsDir = self::$basePath . 'src/resources/src/';
		self::$viewsDir = self::$basePath . 'src/resources/views/';

		return $this;
	}

	/**
	 * Create the application by loading configuration files and routes.
	 *
	 * @return void
	 */
	public function create (): void
	{
		$loader = new FileLoader();
		$loader->load(__DIR__ . '/../Config/env.config.php');
		$loader->load(__DIR__ . '/../Config/config.php');

		Route::config((bool) (getenv('APP_DEBUG') ?? true));
		$loader
		 ->load(__DIR__ . '/../Globals/Functions.php')
		 ->load(self::$apiPath)
		 ->load(self::$webPath);
	}
}
