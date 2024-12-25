<?php declare(strict_types=1);

namespace PhpSlides\Core\Foundation;

use PhpSlides\Exception;
use PhpSlides\Router\view;

trait Configuration
{
	use \PhpSlides\Core\Traits\FileHandler;

	/**
	 * The base path of the application.
	 * @var string $basePath
	 */
	public static string $basePath;

	/**
	 * The request Uri
	 * @var string $request_uri
	 */
	public static string $request_uri;

	/**
	 * Configures the application based on the request URI and configuration file.
	 *
	 * This method performs the following steps:
	 * 1. Retrieves the root directory and request URI.
	 * 2. Constructs the file URL and checks if the file exists.
	 * 3. Determines the file type if the file exists.
	 * 4. Loads the configuration file.
	 * 5. Checks if the request URI matches any deny rules in the configuration file.
	 *    - If a match is found, it denies the request, sets the HTTP response code,
	 *      and outputs the appropriate message or components.
	 * 6. If no deny rules match, it sets the content type header and outputs the file content.
	 *
	 * @throws Exception If the HTTP response code in the configuration is not an integer.
	 */
	protected static function config(): void
	{
		$root_dir = self::$basePath;

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
}
