<?php declare(strict_types=1);

namespace PhpSlides\Interface;

use Closure;

/**
 *   -------------------------------------------------------------------------------
 *
 *   HANDLE WEB ROUTINGS INTERFACE
 *
 *   Create web routes that accept different methods and render to the client area
 *
 *   @author Dave Conco <concodave@gmail.com>
 *   @link https://github.com/dconco/php_slides
 *   @category api, router, php router, php
 *   @copyright 2023 - 2024 Dave Conco
 *   @package PhpSlides
 *   @version ^1.0.0
 *   @return void
 * |
 *
 *   -------------------------------------------------------------------------------
 */
interface RouteInterface
{
	/**
	 *   ------------------------------------------------------
	 *   |
	 *   Get the file extension content-type with mime
	 *
	 *   @param string $filename File path or file resources
	 *   @return bool|string Returns the MIME content type for a file as determined by using information from the magic.mime file.
	 *   |
	 *   ------------------------------------------------------
	 */
	public static function file_type(string $filename): bool|string;

	/**
	 *   ---------------------------------------------------------------------------------------------------------
	 *
	 *   This function handles getting files request and describe the type of request to handle according to `phpslides.config.json` file in the root of the project,
	 *   for more security, it disallow users in navigating to wrong paths or files of the project.
	 *
	 *
	 *   This config method must be called before writing any other Route method or codes.
	 *
	 *   ---------------------------------------------------------------------------------------------------------
	 */
	public static function config(): void;

	/**
	 *   ------------------------------------------------------------------------
	 *
	 *   ANY REQUEST FROM ROUTE
	 *
	 *   Accept all type of request or any other method
	 *
	 *   Cannot evaluate `{?} URL parameters` in route if it's an array
	 *   |
	 *
	 *   @param array|string $route This describes the URL string to check if it matches the request URL, use array of URLs for multiple request
	 *   @param mixed $callback Can contain any types of data to return to the client side/browser.
	 *
	 *   ------------------------------------------------------------------------
	 */
	public static function any(
		array|string $route,
		mixed $callback,
		string $method = '*'
	): self;

	/**
	 * MAP method
	 * Check out documentation for using Map method
	 *
	 * @link https://github.com/dconco/php_slides
	 * @param string $method Can also be used as `$route` param if the `$route` param is not specified
	 * @param string|array|null $route Route parameter
	 */
	public static function map(string $method, string|array $route): self;

	/**
	 * name METHOD
	 * Give a route a name for later use
	 *
	 * @param string $name
	 */
	public function name(string $name): self;

	/**
	 * Action method
	 * In outputting information to the client area
	 *
	 * @param mixed $callback
	 */
	public function action(mixed $callback): self;

	/**
	 * Controller method
	 * Work with map controller route
	 *
	 * @param string $controller
	 * @return void
	 */
	public function use(string $controller): self;

	/**
	 * view method
	 * output view file directly
	 *
	 * @param string $file
	 */
	public function file(string $file): self;

	/**
	 *   ---------------------------------------------------------------------------
	 *
	 *   VIEW ROUTE METHOD
	 *
	 *   Route only needs to return a view; you may provide an array for multiple request
	 *
	 *   View Route does not accept `{?} URL parameters` in route, use GET method instead
	 *
	 *   @param array|string $route This describes the URL string to render, use array of strings for multiple request
	 *   @param string $view It renders this param, it can be functions to render, view:: to render or strings of text or documents
	 *   |
	 *
	 *   ---------------------------------------------------------------------------
	 */
	public static function view(array|string $route, string $view): self;

	/**
	 *   --------------------------------------------------------------
	 *
	 *   REDIRECT ROUTE METHOD
	 *
	 *   This method redirects the routes URL to the giving URL directly
	 *
	 *   @param string $route The requested url to redirect
	 *   @param string $new_url The new URL route to redirect to
	 *   @param int $code The code for redirect method, 301 for permanent redirecting & 302 for temporarily redirect.
	 *
	 * ---------------------------------------------------------------
	 */
	public static function redirect(
		string $route,
		string $new_url,
		int $code = 302
	): self;

	/**
	 *   --------------------------------------------------------------
	 *
	 *   GET ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function get(array|string $route, $callback): self;

	/**
	 *   --------------------------------------------------------------
	 *
	 *   POST ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function post(array|string $route, $callback): self;

	/**
	 *   --------------------------------------------------------------
	 *
	 *   PUT ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function put(array|string $route, $callback): self;

	/**
	 *   --------------------------------------------------------------
	 *
	 *   PATCH ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function patch(array|string $route, $callback): self;

	/**
	 *   --------------------------------------------------------------
	 *
	 *   DELETE ROUTE METHOD
	 *
	 *   Cannot evaluate {?} URL parameters in route if it's an array
	 *
	 *   --------------------------------------------------------------
	 */
	public static function delete(array|string $route, $callback): self;

	public function __destruct();
}
