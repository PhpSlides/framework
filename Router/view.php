<?php declare(strict_types=1);

namespace PhpSlides;

use PhpSlides\Exception;
use PhpSlides\Logger\Logger;
use PhpSlides\Traits\FileHandler;
use PhpSlides\Controller\Controller;
use PhpSlides\Foundation\Application;

/**
 *   --------------------------------------------------------------
 *
 *   Router View
 *
 *   Which control the public URL and validating.
 *   This class is used in rendering views and parsing public URL in views.
 *
 *   --------------------------------------------------------------
 */
final class view extends Controller
{
	use Logger, FileHandler;

	/**
	 *   --------------------------------------------------------------
	 *
	 * Render views and parse public URL in views
	 *
	 * @param string $view
	 * @return mixed return the file gotten from the view parameters
	 *
	 *   --------------------------------------------------------------
	 */
	final public static function render(string $view): mixed
	{
		// split :: into array and extract the folder and files
		$file = preg_replace('/(::)|::/', '/', $view);
		$file = strtolower(trim($file, '\/\/'));
		$file_uri = Application::$viewsDir . $file;
		header('Content-Type: text/html');

		if (is_file($file_uri . '.view.php') && !preg_match('/(..\/)/', $view)) {
			return self::slides_include($file_uri . '.view.php');
		} elseif (is_file($file_uri) && !preg_match('/(..\/)/', $view)) {
			return self::slides_include($file_uri);
		} else {
			throw new Exception("No view file path found called `$file_uri`");
		}
	}
}
