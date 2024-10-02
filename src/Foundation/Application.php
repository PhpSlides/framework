<?php declare(strict_types=1);

namespace PhpSlides\Foundation;

use DB;
use PhpSlides\Route;
use PhpSlides\Forgery\Forge;
use PhpSlides\Logger\Logger;
use PhpSlides\Logger\DBLogger;
use PhpSlides\Loader\HotReload;
use PhpSlides\Loader\Autoloader;
use PhpSlides\Loader\FileLoader;
use PhpSlides\Database\Connection;
use PhpSlides\Interface\ApplicationInterface;

/**
 * The Application class is the foundation of the PhpSlides project
 * and provides methods to configure and initialize the PhpSlides application.
 */
class Application implements ApplicationInterface
{
	use Logger, DBLogger {
		Logger::log insteadof DBLogger;
		DBLogger::log as db_log;
	}

	/**
	 * The version of the PhpSlides application.
	 */
	const PHPSLIDES_VERSION = '1.3.4';

	/**
	 *  `$log` method prints logs in `requests.log` file in the root of the project each time any request has been received, when setted to true.
	 *   It's been setted to true by default, can be changed anytime.
	 *
	 *   @static $log
	 *   @var bool $log
	 *   @return bool
	 */
	public static bool $log;

	/**
	 *  `$db_log` method prints logs in `db.log` file in the root of the project any time there's message from the database management.
	 *   It's been setted to true by default, can be changed anytime.
	 *
	 *   @static $db_log
	 *   @var bool $db_log
	 *   @return bool
	 */
	public static bool $db_log;

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
	 * Configure the application with the base path.
	 *
	 * @return self Returns an instance of the Application class.
	 */
	private static function configure(): void
	{
		if (php_sapi_name() == 'cli-server') {
			self::$request_uri = urldecode(
				parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
			);
			self::$basePath = '';
		} else {
			self::$request_uri = urldecode(
				parse_url($_REQUEST['uri'] ?? $_SERVER['REQUEST_URI'], PHP_URL_PATH)
			);
			self::$basePath = '../../';
		}
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
	}

	/**
	 * Create the application by loading configuration files and routes.
	 *
	 * @return void
	 */
	public function create(): void
	{
		self::configure();
		self::routing();
		session_start();

		$loader = new FileLoader();
		$loader->load(__DIR__ . '/../Config/env.config.php');

		self::$log = getenv('APP_DEBUG') == 'true' ? true : false;
		self::$db_log = getenv('DB_DEBUG') == 'true' ? true : false;

		$sid = session_id();

		if (getenv('HOT_RELOAD') == 'true') {
			Route::post("/hot-reload-$sid", fn() => (new HotReload())->reload());
		}

		try {
			Connection::init();
			DB::query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA');
		} catch (\Exception $e) {
			static::db_log('WARNING', $e->getMessage());
			goto EXECUTION;
		}
		new Forge();
		new Autoloader();

		EXECUTION:
		try {
			$loader
				->load(__DIR__ . '/../Globals/Functions.php')
				->load(__DIR__ . '/../Config/config.php');
			Route::config();
		} catch (\Exception $e) {
			http_response_code(500);
		} finally {
			static::log();
		}
	}
}
