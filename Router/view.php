<?php declare(strict_types=1);

namespace PhpSlides\Router;

use component;
use PhpSlides\Src\Foundation\Application;

/**
 * --------------------------------------------------------------
 * Router View
 *
 * This class controls public URL parsing and validation. It is responsible for rendering views
 * and parsing public URLs within views.
 *
 * --------------------------------------------------------------
 */
final class view
{
	/**
	 * --------------------------------------------------------------
	 * Render Views and Parse Public URL in Views
	 *
	 * This method is used to render a view by parsing the provided view name, validating
	 * the path, and passing any additional parameters to the view file.
	 * It returns the output from the view file as a result.
	 *
	 * @param string $view The name or path of the view to be rendered.
	 * @param mixed ...$props Additional parameters to be passed into the view.
	 *
	 * @return mixed The rendered content of the view.
	 *
	 * --------------------------------------------------------------
	 */
	final public static function render(string $view, mixed ...$props): mixed
	{
		// split :: into array and extract the folder and files
		$file = preg_replace('/(::)|::/', '/', $view);
		$file = strtolower(trim($file, '\/\/'));
		$file_uri = Application::$viewsDir . $file;
		header('Content-Type: text/html');

		return component($file_uri, ...$props);
	}
}
