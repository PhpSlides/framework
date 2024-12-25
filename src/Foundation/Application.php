<?php declare(strict_types=1);

namespace PhpSlides\Core\Foundation;

use DB;
use Closure;
use PhpSlides\Router\Route;
use PhpSlides\Core\Http\Request;
use PhpSlides\Core\Forgery\Forge;
use PhpSlides\Core\Logger\Logger;
use PhpSlides\Core\Logger\DBLogger;
use PhpSlides\Core\Loader\HotReload;
use PhpSlides\Core\Database\Database;
use PhpSlides\Core\Loader\Autoloader;
use PhpSlides\Core\Loader\FileLoader;
use PhpSlides\Core\Database\Connection;
use PhpSlides\Core\Controller\Controller;
use PhpSlides\Core\Interface\ApplicationInterface;

/**
 * Class Application
 *
 * The Application class is the foundation of the PhpSlides project
 * and provides methods to configure and initialize the PhpSlides application.
 *
 * @author dconco <info@dconco.dev>
 * @version 1.4.0
 * @package PhpSlides\Foundation
 */
class Application extends Controller implements ApplicationInterface
{
	use \PhpSlides\Core\Cli\Configure;
	use Configuration;
	use Logger;
	use DBLogger {
		Logger::log insteadof DBLogger;
		DBLogger::log as db_log;
	}

	/**
	 * The version of the PhpSlides application.
	 */
	public const PHPSLIDES_VERSION = '1.4.3';

	/**
	 * @var string $REMOTE_ADDR The remote address of the client making the request.
	 */
	public static string $REMOTE_ADDR;

	/**
	 *  `$log` method prints logs in `requests.log` file in the root of the project each time any request has been received, when setted to true.
	 *   It's been setted to true by default, can be changed anytime.
	 *
	 *   @static $log
	 *   @var bool $log
	 */
	public static bool $log;

	/**
	 *  `$db_log` method prints logs in `db.log` file in the root of the project any time there's message from the database management.
	 *   It's been setted to true by default, can be changed anytime.
	 *
	 *   @static $db_log
	 *   @var bool $db_log
	 */
	public static bool $db_log;

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

	public static ?Closure $handleInvalidParameterType;

	/**
	 * Configure the application with the base path.
	 *
	 * @return self Returns an instance of the Application class.
	 */
	private static function configure(): void
	{
		if (php_sapi_name() == 'cli') {
			static::bootstrap();

			self::$request_uri = '/';
			self::$basePath = './tests/';
		} elseif (php_sapi_name() == 'cli-server') {
			self::$request_uri = urldecode(
				parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
			);
			self::$basePath = '';
		} else {
			self::$request_uri = urldecode(
				parse_url(
					$_REQUEST['uri'] ?? $_SERVER['REQUEST_URI'],
					PHP_URL_PATH,
				),
			);

			$find = '/src/routes/render.php';
			$self = $_SERVER['PHP_SELF'];

			self::$basePath = strrpos($self, $find)
				? substr_replace($self, '/', strrpos($self, $find), strlen($find))
				: '../../';
		}

		$req = new Request();
		$protocol = $req->isHttps() ? 'https://' : 'http://';

		self::$REMOTE_ADDR = $protocol . $req->server('HTTP_HOST') ?? 'localhost';
	}

	/**
	 * Set up routing paths for the application.
	 *
	 * @return void
	 */
	private static function paths(): void
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
		self::paths();

		$loader = new FileLoader();
		$loader
			->load(__DIR__ . '/../Config/env.config.php')
			->load(__DIR__ . '/../Config/config.php');

		session_start();

		self::$log = getenv('APP_DEBUG') == 'true' ? true : false;
		self::$db_log = getenv('DB_DEBUG') == 'true' ? true : false;

		$sid = session_id();

		if (getenv('HOT_RELOAD') == 'true') {
			Route::post("/hot-reload-a$sid", fn() => (new HotReload())->reload());
			Route::get("/hot-reload-a$sid/worker", function () use ($sid): string {
				$addr = self::$REMOTE_ADDR . "/hot-reload-a$sid";
				header('Content-Type: application/javascript');

				return "(function start() {'use strict';fetch('$addr',{method:'POST'}).then(e=>e.text()).then(e=>'reload'===e&&postMessage(e)).finally(()=>setTimeout(start,1000))})()";
			});
			Render::WebRoute();
		}

		try {
			Connection::init();
			DB::query('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA');
		} catch (\Exception $e) {
			Database::$_connect_error = $e->getMessage();
			goto EXECUTION;
		}
		new Forge();
		new Autoloader();

		EXECUTION:
		try {
			$loader->load(__DIR__ . '/../Globals/Functions.php');

			$config_file = self::config_file();
			$charset = $config_file['charset'] ?? 'UTF-8';

			http_response_code(200);
			header("Content-Type: text/html; charset=$charset");

			self::config();
		} catch (\Exception $e) {
			http_response_code(500);
			static::log();

			if (function_exists('ExceptionHandler')) {
				call_user_func('ExceptionHandler', $e);
			}
		}
	}
}
